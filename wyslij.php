<?php
/**
 * Realizacja: inż. arch. Konstanty Kaszubski
 * Data: Marzec 2026
 * Projekt: Konfigurator Konsil
 * Wersja: 1.3.5 (...)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$config = require 'config.php';
require 'vendor/autoload.php';
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("BŁĄD: Nie znaleziono pliku autoload.php w " . __DIR__ . "/vendor/");
} else {
    echo "Autoloader załadowany poprawnie.";
}
//require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. POBIERANIE DANYCH
    $payload = json_decode($_POST['payload_json'], true);

    $klient = [
            'nazwa' => $_POST['klient_nazwa'] ?? '',
            'email' => $_POST['klient_email'] ?? '',
            'nip'   => $_POST['klient_nip'] ?? '',
            'tel'   => $_POST['klient_telefon'] ?? '',
            'uwagi' => $_POST['uwagi'] ?? ''
    ];

    $adres = [
            'aktywny' => isset($_POST['chce_adres']),
            'miejscowosc' => $_POST['adr_miejscowosc'] ?? '',
            'kod' => $_POST['adr_kod'] ?? '',
            'ulica' => $_POST['adr_ulica'] ?? '',
            'nr' => $_POST['adr_nr'] ?? '',
            'poczta' => $_POST['adr_poczta'] ?? ''
    ];

    if (!$payload || !$payload['silo']) {
        die("Błąd: Nie wybrano silosu.");
    }

    // 1b. PRZYGOTOWANIE LOGO DO PDF (Base64)
    $logoPath = __DIR__ . '/konsil_logo_main.png';
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $data = file_get_contents($logoPath);
        $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // 1c. LOGIKA STATUSU PODATKOWEGO I ANKIETY
    $statusy = [];
    if (!empty($payload['isVat'])) $statusy[] = "Czynny podatnik VAT";
    if (!empty($payload['isRyczalt'])) $statusy[] = "Rolnik ryczałtowy";
    $status_text = !empty($statusy) ? implode(", ", $statusy) : "Nie określono";
    $skad = !empty($payload['skadInfo']) ? htmlspecialchars($payload['skadInfo']) : "Nie podano";

    // 2. GENEROWANIE HTML DLA PDF
    $html = '
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { background-color: #0b2239; color: white; padding: 20px; border-bottom: 4px solid #ced4da; text-align: center; }
        .logo-pdf { max-height: 60px; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; letter-spacing: 1px; }
        .header p { margin: 5px 0 0 0; font-size: 10px; opacity: 0.8; text-transform: uppercase; }
        .section-title { color: #0b2239; border-bottom: 2px solid #0b2239; margin-top: 20px; padding-bottom: 3px; text-transform: uppercase; font-weight: bold; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f2f2f2; text-align: left; padding: 8px; border-bottom: 2px solid #0b2239; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .total-box { margin-top: 30px; padding: 15px; background-color: #f8f9fa; border: 1px solid #0b2239; text-align: right; }
        .footer { margin-top: 40px; font-size: 9px; color: #777; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
        .address-box { background-color: #f1f3f5; padding: 10px; margin-top: 5px; border-left: 3px solid #0b2239; }
    </style>

    <div class="header">';

    if ($logoBase64) {
        $html .= '<img src="' . $logoBase64 . '" class="logo-pdf"><br>';
    }

    $html .= '
        <h1>ZAPYTANIE OFERTOWE</h1>
        <p>Przedsiębiorstwo Obsługi Rolnictwa KONSIL</p>
    </div>

    <div class="section-title">Dane Klienta</div>
    <p>
        <strong>Firma/Imię:</strong> ' . htmlspecialchars($klient['nazwa']) . '<br>
        <strong>Email:</strong> ' . htmlspecialchars($klient['email']) . ' | <strong>Tel:</strong> ' . htmlspecialchars($klient['tel']) . '<br>
        ' . ($klient['nip'] ? '<strong>NIP:</strong> ' . htmlspecialchars($klient['nip']) . '<br>' : '') . '
        <strong>Status podatkowy:</strong> ' . $status_text . '<br>
        <strong>Źródło kontaktu:</strong> ' . $skad . '
    </p>';

    if ($adres['aktywny']) {
        $html .= '
        <div class="section-title">Lokalizacja montażu / dostawy</div>
        <div class="address-box">
            ' . htmlspecialchars($adres['ulica']) . ' ' . htmlspecialchars($adres['nr']) . ',<br>
            ' . htmlspecialchars($adres['kod']) . ' ' . htmlspecialchars($adres['miejscowosc']) . '<br>
            Poczta: ' . htmlspecialchars($adres['poczta']) . '
        </div>';
    }

    $html .= '
    <div class="section-title">Wybrana Konfiguracja (' . $payload['qty'] . ' szt.)</div>
    <table>
        <thead>
            <tr>
                <th>Element zestawu</th>
                <th>Kod produktu</th>
                <th style="text-align: right;">Cena jedn. netto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Silos: ' . htmlspecialchars($payload['silo']['nazwa']) . '</strong></td>
                <td><code>' . htmlspecialchars($payload['silo']['kod']) . '</code></td>
                <td style="text-align: right;">' . number_format($payload['silo']['cena'], 2, ',', ' ') . ' zł</td>
            </tr>';

    foreach ($payload['akcesoria'] as $acc) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($acc['nazwa']) . '</td>
                <td><code>' . htmlspecialchars($acc['kod']) . '</code></td>
                <td style="text-align: right;">' . number_format($acc['cena'], 2, ',', ' ') . ' zł</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>

    <div class="section-title">Usługi dodatkowe (wliczone w sumę)</div>
    <p>
        <strong>Zlecenie transportu:</strong> ' . ($payload['transport'] > 0 ? 'TAK' : 'NIE') . '<br>
        <strong>Zlecenie montażu:</strong> ' . ($payload['montaz'] > 0 ? 'TAK' : 'NIE') . '
    </p>

    <div class="total-box">
        <span style="font-size: 13px; color: #666; text-transform: uppercase;">Łączna wartość zapytania:</span><br>
        <strong style="font-size: 22px; color: #0b2239;">' . number_format($payload['total'], 2, ',', ' ') . ' zł NETTO</strong>
    </div>

    <div class="section-title">Uwagi dodatkowe</div>
    <p>' . nl2br(htmlspecialchars($klient['uwagi'] ?: 'Brak dodatkowych uwag.')) . '</p>

    <div class="footer">
        Dokument wygenerowany ' . date('d.m.Y H:i') . ' przez konfigurator online Konsil.<br>
        © 2026 P.O.R. KONSIL - Bydgoszcz. Niniejszy dokument nie stanowi oferty w rozumieniu KC.
    </div>';

    // 3. GENEROWANIE PDF
    // 3. GENEROWANIE PDF
    try {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        // Definiujemy ścieżkę do folderu temp w sposób bezwzględny
        $tmpDir = __DIR__ . '/temp';

        // Jeśli folder nie istnieje, spróbujmy go stworzyć
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        // Ustawiamy ścieżki dla czcionek i plików tymczasowych
        $options->set('tempDir', $tmpDir);
        $options->set('fontDir', $tmpDir);
        $options->set('fontCache', $tmpDir);
        $options->set('chroot', __DIR__); // Zabezpieczenie ścieżek

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();
    } catch (\Exception $e) {
        die("Błąd generowania PDF: " . $e->getMessage());
    }

    // 4. WYSYŁKA MAILA (PHPMailer)
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp_user'];
        $mail->Password   = $config['smtp_pass'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port       = $config['smtp_port'];
        $mail->CharSet    = 'UTF-8';
        $mail->isHTML(true);

        $firmMailSent = false;
        $clientMailSent = false;

        // --- MAIL 1: DO FIRMY ---
        try {
            $mail->setFrom($config['email_from'], 'Konfigurator Konsil');
            $mail->addAddress($config['email_to']);
            $mail->addReplyTo($klient['email'], $klient['nazwa']);

            $mail->Subject = 'Zapytanie ofertowe: ' . $payload['silo']['nazwa'] . ' - ' . $klient['nazwa'];
            $mail->Body = "Pojawiło się nowe zapytanie ofertowe.<br><br>
                          <b>Klient:</b> {$klient['nazwa']}<br>
                          <b>Wartość:</b> " . number_format($payload['total'], 2, ',', ' ') . " zł netto<br>";

            if (!empty($payload['kodRabatowy'])) {
                $mail->Body .= "<b>KOD RABATOWY:</b> <span style='color:red; font-weight:bold;'>" . htmlspecialchars($payload['kodRabatowy']) . "</span><br>";
            }

            $mail->Body .= "<br>PDF w załączniku.";

            $mail->addStringAttachment($pdfOutput, 'Oferta_Konsil_' . date('Ymd_Hi') . '.pdf');

            $mail->send();
            $firmMailSent = true; // WAŻNE: Aktywujemy status wysyłki
        } catch (\Exception $e) {
            die("Błąd krytyczny: Nie udało się wysłać zapytania do biura. Błąd: {$mail->ErrorInfo}");
        }

        // --- MAIL 2: DO KLIENTA (ROLNIKA) ---
        if ($firmMailSent) {
            try {
                $mail->clearAddresses();
                if (filter_var($klient['email'], FILTER_VALIDATE_EMAIL)) {
                    $mail->addAddress($klient['email']);
                    $mail->Subject = 'Podsumowanie Twojej konfiguracji silosu - Konsil';
                    $mail->Body = "
                    <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px;'>
                        <h2 style='color: #0b2239;'>Dzień dobry!</h2>
                        <p>Dziękujemy za skorzystanie z konfiguratora ofert online na stronie 
                           <a href='https://www.konsil.pl' style='color: #0b2239; font-weight: bold; text-decoration: none;'>www.konsil.pl</a>.
                        </p>
                        <p>Otrzymaliśmy Twoje zapytanie dotyczące modelu: <b>" . htmlspecialchars($payload['silo']['nazwa']) . "</b>.</p>
                        <p>Nasi doradcy przeanalizują Twoją konfigurację i skontaktują się z Tobą wkrótce.</p>
                        <div style='background-color: #f8f9fa; padding: 20px; border-left: 4px solid #0b2239; margin: 20px 0;'>
                            <p style='margin: 0; font-weight: bold; color: #0b2239;'>Masz pytania? Zadzwoń do nas:</p>
                            <p style='margin: 10px 0 0 0; font-size: 1.2rem;'>
                                <a href='tel:+48523857859' style='color: #d9534f; text-decoration: none; font-weight: bold;'>52 385-78-59</a>
                            </p>
                        </div>
                        <p>Szczegółowe podsumowanie znajdziesz w <b>załączonym pliku PDF</b>.</p>
                        <hr style='border: 0; border-top: 1px solid #eee; margin: 30px 0;'>
                        <p style='font-size: 0.9rem; color: #777;'>
                            Z poważaniem,<br>
                            <strong>Zespół P.O.R. KONSIL</strong><br>
                            ul. Nakielska, Ślesin<br>
                            <a href='https://www.konsil.pl' style='color: #777;'>www.konsil.pl</a>
                        </p>
                    </div>";

                    $mail->send();
                    $clientMailSent = true;
                }
            } catch (\Exception $e) {
                $clientMailSent = false;
            }
        }

        // --- KROK 5: WIDOK SUKCESU ---
        ?>
        <!DOCTYPE html>
        <html lang="pl">
        <head>
            <meta charset="UTF-8">
            <title>Status zapytania - Konsil</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
            <style>
                body { background-color: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; }
                .success-card { background: white; padding: 50px; border-top: 5px solid #0b2239; text-align: center; max-width: 600px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
            </style>
        </head>
        <body>
        <div class="success-card">
            <?php if ($clientMailSent): ?>
                <i class="bi bi-check2-circle text-success" style="font-size: 5rem;"></i>
                <h1 class="mt-4" style="color: #0b2239; font-weight: bold;">WYSŁANO!</h1>
                <p class="lead text-muted">Zapytanie trafiło do naszych doradców. Kopię wysłaliśmy na Twój e-mail.</p>
            <?php else: ?>
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
                <h1 class="mt-4" style="color: #0b2239; font-weight: bold;">PRZYJĘTO ZAPYTANIE</h1>
                <p class="lead text-muted">Twoja wycena została zapisana. Skontaktujemy się telefonicznie.</p>
                <div class="alert alert-warning small text-start">
                    Błąd dostarczenia kopii na adres: <?php echo htmlspecialchars($klient['email']); ?>. Sprawdź poprawność maila.
                </div>
            <?php endif; ?>
            <hr class="my-4">
            <a href="index.php" class="btn btn-dark btn-lg px-5" style="background-color: #0b2239; border-radius: 0;">Powrót</a>
        </div>
        </body>
        </html>
        <?php

    } catch (Exception $e) {
        die("Błąd krytyczny PHPMailer: {$mail->ErrorInfo}");
    }
}