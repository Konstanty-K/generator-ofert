<?php
$config = require 'config.php';
require 'vendor/autoload.php';

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

    if (!$payload || !$payload['silo']) {
        die("Błąd: Nie wybrano silosu.");
    }

    // 2. GENEROWANIE HTML DLA PDF
    $html = '
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { background-color: #0b2239; color: white; padding: 30px; border-bottom: 4px solid #ced4da; text-align: center; }
        .section-title { color: #0b2239; border-bottom: 2px solid #0b2239; margin-top: 20px; padding-bottom: 5px; text-transform: uppercase; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f2f2f2; text-align: left; padding: 8px; border-bottom: 2px solid #0b2239; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .total-box { margin-top: 30px; padding: 20px; background-color: #f8f9fa; border: 1px solid #0b2239; text-align: right; }
        .footer { margin-top: 50px; font-size: 10px; color: #777; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>

    <div class="header">
        <h1 style="margin:0;">ZAPYTANIE OFERTOWE - KONSIL</h1>
        <p style="margin:5px 0 0 0;">Systemy Przechowywania Zbóż</p>
    </div>

    <div class="section-title">Dane Klienta</div>
    <p>
        <strong>Firma/Imię:</strong> ' . htmlspecialchars($klient['nazwa']) . '<br>
        <strong>Email:</strong> ' . htmlspecialchars($klient['email']) . ' | <strong>Tel:</strong> ' . htmlspecialchars($klient['tel']) . '<br>
        ' . ($klient['nip'] ? '<strong>NIP:</strong> ' . htmlspecialchars($klient['nip']) : '') . '
    </p>

    <div class="section-title">Wybrana Konfiguracja (' . $payload['qty'] . ' szt.)</div>
    <table>
        <thead>
            <tr>
                <th>Element</th>
                <th>Kod</th>
                <th style="text-align: right;">Cena jedn. netto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Silos: ' . $payload['silo']['nazwa'] . '</strong></td>
                <td>' . $payload['silo']['kod'] . '</td>
                <td style="text-align: right;">' . number_format($payload['silo']['cena'], 2, ',', ' ') . ' zł</td>
            </tr>';

    foreach ($payload['akcesoria'] as $acc) {
        $html .= '<tr>
                    <td>' . $acc['nazwa'] . '</td>
                    <td>' . $acc['kod'] . '</td>
                    <td style="text-align: right;">' . number_format($acc['cena'], 2, ',', ' ') . ' zł</td>
                  </tr>';
    }

    $html .= '</tbody>
    </table>

    <div class="section-title">Usługi dodatkowe</div>
    <p>
        Transport: ' . ($payload['transport'] > 0 ? 'TAK' : 'NIE') . '<br>
        Montaż: ' . ($payload['montaz'] > 0 ? 'TAK' : 'NIE') . '
    </p>

    <div class="total-box">
        <span style="font-size: 14px; color: #777;">SZACUNKOWA WARTOŚĆ NETTO:</span><br>
        <strong style="font-size: 24px; color: #0b2239;">' . number_format($payload['total'], 2, ',', ' ') . ' zł</strong>
    </div>

    <div class="section-title">Uwagi klienta</div>
    <p>' . nl2br(htmlspecialchars($klient['uwagi'] ?: 'Brak uwag.')) . '</p>

    <div class="footer">
        Dokument wygenerowany automatycznie przez konfigurator online Konsil. <br>
        © 2026 P.O.R. KONSIL - Bydgoszcz
    </div>';

    // 3. GENEROWANIE PDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfOutput = $dompdf->output();

    // 4. WYSYŁKA MAILA (PHPMailer)
    $mail = new PHPMailer(true);
    try {
        // Tu wpisz swoje dane SMTP (np. z XAMPP / Mailtrap / Gmail)
        $mail->isSMTP();
        $mail->Host       = $config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp_user'];
        $mail->Password   = $config['smtp_pass'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port       = $config['smtp_port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('konsil@interia.pl', 'Konfigurator Konsil');
        $mail->addAddress('silosy@konsil.pl'); // Adres firmy
        $mail->addReplyTo($klient['email'], $klient['nazwa']);

        $mail->isHTML(true);
        $mail->Subject = 'Nowe zapytanie ofertowe: ' . $payload['silo']['nazwa'];
        $mail->Body    = 'W załączniku znajduje się oferta przygotowana przez klienta: ' . $klient['nazwa'];

        // ZAŁĄCZNIK PDF
        $mail->addStringAttachment($pdfOutput, 'Oferta_Konsil_' . date('Ymd_His') . '.pdf');

        $mail->send();

        // 5. KOMUNIKAT DLA UŻYTKOWNIKA
        echo '
        <!DOCTYPE html>
        <html lang="pl">
        <head>
            <meta charset="UTF-8">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background-color: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; }
                .success-card { background: white; padding: 50px; border-top: 5px solid #0b2239; shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
            </style>
        </head>
        <body>
            <div class="success-card shadow">
                <i class="bi bi-check2-circle text-success" style="font-size: 4rem;"></i>
                <h1 class="mt-4" style="color: #0b2239;">WYSŁANO!</h1>
                <p class="lead text-muted">Twoje zapytanie ofertowe zostało przesłane do naszych doradców.<br>Czekaj na odpowiedź, skontaktujemy się z Tobą niebawem.</p>
                <hr>
                <a href="index.php" class="btn btn-outline-dark px-4 mt-3">Wróć do konfiguratora</a>
            </div>
        </body>
        </html>';

    } catch (Exception $e) {
        echo "Błąd wysyłki: {$mail->ErrorInfo}";
    }
}