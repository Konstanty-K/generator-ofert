<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="inż. arch. Konstanty Kaszubski">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SILOSY KONSIL - KONFIGURATOR WYCENY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --main-navy: #0b2239;
            --accent-soft: #ced4da;
            --bg-light: #f4f7f6;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
        }

        .konsil-header {
            background-color: var(--main-navy);
            color: white;
            padding: 30px 0;
            border-bottom: 3px solid var(--accent-soft);
        }
        .header-logo { max-height: 100px; width: auto; margin-bottom: 10px; }
        .header-subtext { font-size: 0.75rem; font-weight: 600; letter-spacing: 1.5px; margin: 0; }
        .icon-gray { color: var(--accent-soft) !important; margin-right: 8px; }

        /* Wymuszenie stałego układu tabel i łamania długich tekstów */
        .table-fixed-layout {
            table-layout: fixed;
            width: 100%;
        }
        .table-fixed-layout td {
            vertical-align: middle;
            word-wrap: break-word; /* Długi opis złamie się do nowej linii */
        }

        .category-tile {
            border: 2px solid transparent;
            border-radius: 0;
            transition: all 0.2s;
            cursor: pointer;
            min-height: 250px;
        }
        .category-tile:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        .category-tile.active { border-color: var(--main-navy); background-color: white; }
        .category-img {
            height: 180px;
            width: 100%;
            object-fit: contain;
            margin-bottom: 10px;
        }
        /* Styl dla zablokowanej kategorii */
        .category-tile.disabled {
            opacity: 0.7;
            filter: grayscale(80%); /* Lekkie wyszarzenie obrazka */
            cursor: not-allowed;
            position: relative;
            pointer-events: none; /* Całkowicie blokuje kliknięcia w JS */
        }

        /* Nakładka "Wkrótce" */
        .coming-soon-badge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            background-color: var(--main-navy);
            color: white;
            padding: 5px 15px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
            z-index: 5;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            letter-spacing: 1px;
        }

        .section-title { color: var(--main-navy); border-left: 5px solid var(--main-navy); padding-left: 15px; font-weight: bold; }
        .table-dark { background-color: var(--main-navy) !important; border: none; }
        .btn-konsil {
            background-color: var(--main-navy); color: white; border-radius: 0;
            padding: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-konsil:hover { background-color: #162e4a; color: white; transform: translateY(-2px); }

        .sidebar-summary { position: sticky; top: 20px; z-index: 1000; }
        .form-switch .form-check-input:checked { background-color: var(--main-navy); border-color: var(--main-navy); }

        .step-icon { font-size: 1.5rem; color: var(--main-navy); transition: 0.3s; }
        .card-header:hover { background-color: #f8f9fa !important; }
    </style>
</head>
<body>

<?php

$is_local = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['HTTP_HOST'] === 'localhost');

// Definiujemy tryb debugowania (możesz też ustawić to ręcznie na true/false)
$debug_mode = $is_local;

require_once 'env_loader.php';
loadEnv(__DIR__ . '/.env');

if(!$debug_mode) require_once 'maintenance.php'; // Wyświetl nakładkę "W budowie" jeśli tryb jest włączony

// 1. DANE MASTER
$cenyMaster = [];
if (($handle = @fopen("produkty.csv", "r")) !== FALSE) {
    fgetcsv($handle);
    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if(count($data) > 41) {
            $kod = trim($data[0]);
            $nazwa = trim($data[1]);
            $cena = (float)str_replace(',', '.', trim($data[41]));
            $cenyMaster[$kod] = ['nazwa' => $nazwa, 'cena' => $cena];
        }
    }
    fclose($handle);
}

// 1b. SŁOWNIK OPISÓW I NAZW WŁASNYCH (opisy.csv)
$slownikOpisow = [];
if (file_exists('opisy.csv') && ($handle = @fopen('opisy.csv', "r")) !== FALSE) {
    fgetcsv($handle); // Pomijamy nagłówek
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (count($data) >= 2) {
            $kod = trim($data[0]);
            $wlasnaNazwa = trim($data[1]);
            $dlugiOpis = isset($data[2]) ? trim($data[2]) : '';

            if (!empty($kod)) {
                $slownikOpisow[$kod] = [
                        'nazwa' => $wlasnaNazwa,
                        'opis'  => $dlugiOpis
                ];
            }
        }
    }
    fclose($handle);
}

// 2. KATEGORIE
$categories_config = [
        ['id' => 'lejowe', 'name' => 'Silosy Lejowe', 'file' => 'silosy lejowe.csv', 'img' => 'img/cat_lejowe.png', 'info' => 'pszenicy o gęstości 750 kg/m³'],
        ['id' => 'lejowe_faliste', 'name' => 'Silosy Lejowe Faliste', 'file' => 'WYCENA- ONLINE - silosy lejowe faliste.csv', 'img' => 'img/cat_lejowe_faliste.png', 'info' => 'pszenicy o gęstości 750 kg/m³', 'disabled' => true],
        ['id' => 'plaskodenne', 'name' => 'Silosy Płaskodenne', 'file' => 'silosy płaskodenne.csv', 'img' => 'img/cat_plaskodenne.png', 'info' => 'pszenicy o gęstości 750 kg/m³'],
        ['id' => 'plaskodenne_faliste', 'name' => 'Silosy Płaskodenne Faliste', 'file' => 'WYCENA- ONLINE - silosy płaskodenne faliste.csv', 'img' => 'img/cat_plaskodenne_faliste.png', 'info' => 'pszenicy o gęstości 750 kg/m³', 'disabled' => true],
        ['id' => 'paszowe', 'name' => 'Silosy Paszowe', 'file' => 'silosy paszowe.csv', 'img' => 'img/cat_paszowe.png', 'info' => 'paszy o gęstości 650 kg/m³', 'disabled' => false]
];

$categories_data = [];
foreach ($categories_config as $cat) {
    $silos = [];
    if (file_exists($cat['file']) && ($handle = @fopen($cat['file'], "r")) !== FALSE) {
        $model_row_found = false;
// ... wewnątrz pętli foreach ($categories_config as $cat) ...
        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            $first_col = trim(strtoupper($data[0] ?? ''));
            if ($first_col === 'MODEL' || $first_col === 'KATEGORIA') {
                $model_row_found = true;
                $has_blocks_logic = (isset($data[4]) && strtoupper(trim($data[4])) === 'XO1');
                continue;
            }

            if ($model_row_found && $first_col !== '' && $first_col !== 'NAN') {
                $silo_code = trim($data[0]);
                $custom_desc = trim($data[1] ?? '');
                $ladownosc = trim($data[2] ?? '');

                $accs_detailed = [];
                $acc_start_idx = 3;

                // --- 1. WSTECZNA KOMPATYBILNOŚĆ - LOGIKA BLOCZKÓW (Kolumny D i E z XO1) ---
                if ($has_blocks_logic) {
                    $raw_bl_val = trim($data[3] ?? '');
                    $szt_bl = (int)trim($data[4] ?? 0);
                    $acc_start_idx = 5;

                    if ($raw_bl_val && $szt_bl > 0) {

                        // NIEZAWODNE ODCINANIE MNOŻNIKA Z KODU BAZOWEGO
                        if (strpos($raw_bl_val, '*') !== false) {
                            $bl_parts_star = explode('*', $raw_bl_val);
                            $raw_bl_val = trim($bl_parts_star[0]); // Zostawia samo "ELEM.CERAM."
                        }

                        $is_quote_bl = (strpos($raw_bl_val, '$') === 0);
                        $proc_bl = $is_quote_bl ? trim(substr($raw_bl_val, 1)) : $raw_bl_val;

                        $bl_group = '';
                        $clean_bl_kod = $proc_bl;

                        if (strpos($proc_bl, '!') === 0) {
                            $bl_parts = explode(':', substr($proc_bl, 1));
                            if (count($bl_parts) > 1) {
                                $bl_group = trim($bl_parts[0]);
                                $clean_bl_kod = trim($bl_parts[1]);
                            }
                        }

                        $m = $cenyMaster[$clean_bl_kod] ?? ['nazwa' => $clean_bl_kod, 'cena' => 0];

                        $nazwaWyswietlana = $slownikOpisow[$clean_bl_kod]['nazwa'] ?? $m['nazwa'];
                        $nazwaWyswietlana .= " (komplet $szt_bl szt.)";
                        $opisDodatkowy = $slownikOpisow[$clean_bl_kod]['opis'] ?? '';

                        if (!empty($opisDodatkowy)) {
                            $nazwaWyswietlana .= "<div class='text-muted small fst-italic mt-1 lh-sm' style='font-size: 0.75rem; white-space: normal;'>{$opisDodatkowy}</div>";
                        }

                        // W szarym polu będzie np. "ELEM.CERAM. x28"
                        $kodDoWyswietlenia = $szt_bl > 1 ? $clean_bl_kod . " x" . $szt_bl : $clean_bl_kod;

                        $accs_detailed[] = [
                                'kod'      => $kodDoWyswietlenia,
                                'nazwa'    => $nazwaWyswietlana,
                                'cena'     => $is_quote_bl ? 0 : ($m['cena'] * $szt_bl),
                                'group'    => $bl_group,
                                'is_quote' => $is_quote_bl
                        ];
                    }
                }

                // --- 2. ZUNIFIKOWANY SILNIK AKCESORIÓW ($, +, !, *) ---
                for ($i = $acc_start_idx; $i < count($data); $i++) {
                    $val = trim($data[$i] ?? '');
                    if ($val && !in_array(strtoupper($val), ['NAN', '-', 'S-STANDARD', ''])) {

                        $is_quote = (strpos($val, '$') === 0);
                        $proc_val = $is_quote ? trim(substr($val, 1)) : $val;

                        $is_merge = (strpos($proc_val, '+') === 0);
                        $clean_cell = $is_merge ? trim(substr($proc_val, 1)) : $proc_val;

                        $bundle_parts = explode('+', $clean_cell);
                        $temp_names = []; $temp_codes = []; $temp_price = 0;
                        $group_id = '';

                        foreach ($bundle_parts as $part) {
                            $code = trim($part);
                            $sztuki = 1;

                            // Niezawodne cięcie mnożnika w nowym systemie
                            if (strpos($code, '*') !== false) {
                                $star_parts = explode('*', $code);
                                $potencjalne_sztuki = (int)trim(end($star_parts));
                                if ($potencjalne_sztuki > 0) {
                                    $sztuki = $potencjalne_sztuki;
                                    array_pop($star_parts); // Usuwamy liczbę z tablicy
                                    $code = trim(implode('*', $star_parts)); // Zostawiamy czysty kod
                                }
                            }

                            if (strpos($code, '!') === 0) {
                                $g_data = explode(':', substr($code, 1));
                                if (count($g_data) > 1) {
                                    $group_id = trim($g_data[0]);
                                    $code = trim($g_data[1]);
                                }
                            }

                            $master = $cenyMaster[$code] ?? ['nazwa' => $code, 'cena' => 0];
                            $temp_price += ($master['cena'] * $sztuki);

                            $nazwaWyswietlana = $slownikOpisow[$code]['nazwa'] ?? $master['nazwa'];
                            if ($sztuki > 1) {
                                $nazwaWyswietlana .= " (komplet $sztuki szt.)";
                            }

                            $opisDodatkowy = $slownikOpisow[$code]['opis'] ?? '';
                            if (!empty($opisDodatkowy)) {
                                $nazwaWyswietlana .= "<div class='text-muted small fst-italic mt-1 lh-sm' style='font-size: 0.75rem; white-space: normal;'>{$opisDodatkowy}</div>";
                            }

                            $temp_names[] = $nazwaWyswietlana;

                            // Transformacja gwiazdki na "x" (np. KOD x28)
                            $temp_codes[] = $sztuki > 1 ? $code . " x" . $sztuki : $code;
                        }

                        $last_idx = count($accs_detailed) - 1;

                        if ($is_merge && $last_idx >= 0) {
                            $accs_detailed[$last_idx]['kod']   .= ' + ' . implode(' + ', $temp_codes);
                            $accs_detailed[$last_idx]['nazwa'] .= ' + ' . implode(' + ', $temp_names);
                            $accs_detailed[$last_idx]['cena']  += $is_quote ? 0 : $temp_price;
                            if ($group_id) $accs_detailed[$last_idx]['group'] = $group_id;
                            if ($is_quote) $accs_detailed[$last_idx]['is_quote'] = true;
                        } else {
                            $accs_detailed[] = [
                                    'kod'      => implode(' + ', $temp_codes),
                                    'nazwa'    => implode(' + ', $temp_names),
                                    'cena'     => $is_quote ? 0 : $temp_price,
                                    'group'    => $group_id,
                                    'is_quote' => $is_quote
                            ];
                        }
                    }
                }
                $silo_master = $cenyMaster[$silo_code] ?? ['nazwa' => $silo_code, 'cena' => 0];
                $final_name = !empty($custom_desc) ? $custom_desc : $silo_master['nazwa'];

                $silos[] = [
                        'kod'       => $silo_code,
                        'nazwa'     => $final_name,
                        'cena'      => $silo_master['cena'], // Cena bazowa silosu WRACA DO NORMY
                        'ladownosc' => $ladownosc,
                        'akcesoria' => $accs_detailed // Bloczki są teraz tutaj!
                ];
            }
        }    }
    $categories_data[$cat['id']] = $silos;
}

// 3. KONFIGURACJA
$konfiguracja = ['koszt_transportu' => 0, 'koszt_montazu' => 0, 'termin' => '', 'note' => ''];
if (file_exists('konfiguracja.csv') && ($handle = @fopen('konfiguracja.csv', "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if(count($data) >= 2) {
            $klucz = trim($data[0]);
            $wartosc = trim($data[1]);
            if (in_array($klucz, ['termin', 'note'])) {
                $konfiguracja[$klucz] = $wartosc;
            } else {
                $konfiguracja[$klucz] = (float)str_replace(',', '.', $wartosc);
            }
        }
    }
    fclose($handle);
}
?>

<header class="konsil-header shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex flex-column align-items-center text-center">
            <img src="konsil_logo_main.png" alt="Konsil Logo" class="header-logo">
            <p class="header-subtext">PRZEDSIĘBIORSTWO OBSŁUGI ROLNICTWA "KONSIL"</p>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="mb-1"><i class="bi bi-telephone-fill icon-gray"></i><span class="fw-bold">52 385-78-59</span></div>
            <div><i class="bi bi-envelope icon-gray"></i><span class="opacity-75">silosy@konsil.pl</span></div>
        </div>
    </div>
</header>

<div class="container pb-5 mt-4">
    <form action="wyslij.php" method="POST" id="mainForm" onsubmit="return validateForm()">
        <input type="hidden" name="payload_json" id="hidden-payload">

        <div class="row g-5">
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm mb-4" id="step1-card" style="border-radius: 0;">
                    <div class="card-header bg-white border-bottom-0 py-3" style="cursor: pointer;" onclick="toggleStep(1)">
                        <h3 class="section-title m-0 d-flex justify-content-between align-items-center">
                            <span>1. Typ silosu</span> <i id="step1-icon" class="bi bi-chevron-down step-icon"></i>
                        </h3>
                    </div>
                    <div class="card-body p-3" id="step1-content">
                        <div class="row g-3" id="categories-container"></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 d-none" id="step2-card" style="border-radius: 0;">
                    <div class="card-header bg-white border-bottom-0 py-3" style="cursor: pointer;" onclick="toggleStep(2)">
                        <h3 class="section-title m-0 d-flex justify-content-between align-items-center">
                            <span>2. Model silosu</span>
                            <i id="step2-icon" class="bi bi-dash step-icon text-muted"></i>
                        </h3>
                    </div>
                    <div class="card-body d-none pt-0" id="step2-content">
                        <div class="table-responsive border border-top-0">
                            <table class="table align-middle m-0 table-hover table-fixed-layout">
                                <thead class="table-dark">
                                <tr>
                                    <th style="width: 5%;" class="text-center">#</th>
                                    <th style="width: 55%;">Model silosu</th>
                                    <th style="width: 15%;" class="text-center">Ładowność*</th>
                                    <th style="width: 25%;" class="text-end">Cena netto</th>                             </tr>
                                </thead>
                                <tbody id="silos-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-5 d-none" id="step3-card" style="border-radius: 0;">
                    <div class="card-header bg-white border-bottom-0 py-3" style="cursor: pointer;" onclick="toggleStep(3)">
                        <h3 class="section-title m-0 d-flex justify-content-between align-items-center">
                            <span>3. Konfiguracja zestawu</span>
                            <i id="step3-icon" class="bi bi-dash step-icon text-muted"></i>
                        </h3>
                    </div>
                    <div class="card-body d-none pt-0" id="step3-content">
                        <h6 class="fw-bold mb-3 text-muted text-uppercase">Opcjonalne akcesoria</h6>
                        <div class="table-responsive border mb-4">
                            <table class="table align-middle m-0 table-fixed-layout">
                                <thead class="table-dark">
                                <tr>
                                    <th style="width: 10%;" class="text-center"><i class="bi bi-check-square"></i></th>
                                    <th style="width: 65%;">Akcesorium i Kod</th>
                                    <th style="width: 25%;" class="text-end">Cena netto</th>
                                </tr>
                                </thead>
                                <tbody id="accessories-tbody"></tbody>
                            </table>
                        </div>

                        <div class="d-flex align-items-center justify-content-between bg-light p-3 border">
                            <label class="fw-bold m-0 text-uppercase" style="color: var(--main-navy);">Ilość zamawianych zestawów:</label>
                            <input type="number" id="silo-qty" aria-label="Ilość zestawów" class="form-control text-center fw-bold fs-5" value="1" min="1" style="width: 100px; border-radius: 0; border: 2px solid var(--main-navy);">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-5" id="dane-klienta" style="border-radius: 0;">
                    <h3 class="section-title mb-4">4. Dane kontaktowe do wyceny</h3>
                    <div class="row g-4">
                        <div class="col-md-6"><input type="text" name="klient_nazwa" class="form-control form-control-lg" placeholder="Imię i Nazwisko / Firma" style="border-radius:0;" required></div>
                        <div class="col-md-6"><input type="email" name="klient_email" class="form-control form-control-lg" placeholder="Adres E-mail" style="border-radius:0;" required></div>
                        <div class="col-md-6"><input type="text" name="klient_nip" class="form-control form-control-lg" placeholder="NIP (opcjonalnie)" style="border-radius:0;"></div>
                        <div class="col-md-6"><input type="tel" name="klient_telefon" class="form-control form-control-lg" placeholder="Numer telefonu" style="border-radius:0;" required></div>
                        <div class="col-12"><textarea name="uwagi" class="form-control" rows="3" placeholder="Dodatkowe uwagi do oferty..." style="border-radius:0;"></textarea></div>
                        <div class="col-12 mt-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input tax-status" type="checkbox" name="klient_vat" id="klient_vat" value="1" onchange="checkTaxStatus()">
                                        <label class="form-check-label small text-muted" for="klient_vat">Jestem płatnikiem VAT</label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input tax-status" type="checkbox" name="klient_ryczalt" id="klient_ryczalt" value="1" onchange="checkTaxStatus()">
                                        <label class="form-check-label small text-muted" for="klient_ryczalt">Jestem na ryczałcie</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0" style="border-radius:0;">
                                        <i class="bi bi-tag-fill text-muted"></i>
                                        </span>
                                        <input type="text" name="kod_rabatowy" id="kod_rabatowy"
                                               class="form-control form-control-lg border-start-0"
                                               placeholder="KOD RABATOWY (OPCJONALNIE)"
                                               style="border-radius:0; font-size: 0.9rem; text-transform: uppercase;">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div id="tax-error" class="text-danger small d-none mt-1 fw-bold">Błąd: Nie można wybrać obu statusów jednocześnie.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="small text-muted mb-1">Skąd dowiedziałeś się o firmie KONSIL?</label>
                            <select name="skad_info" id="skad_info" class="form-select" style="border-radius:0;">
                                <option value="" selected>-- Wybierz opcję (opcjonalnie) --</option>
                                <option value="Jestem stałym klientem">Jestem stałym klientem</option>
                                <option value="Internet (Google/Strona www)">Internet (Google/Strona www)</option>
                                <option value="Social Media (Facebook)">Social Media (Facebook)</option>
                                <option value="Polecenie od innego rolnika">Polecenie od innego rolnika</option>
                                <option value="Targi rolnicze">Targi rolnicze</option>
                                <option value="Prasa rolnicza / Radio">Prasa rolnicza / Radio</option>
                                <option value="Widziałem silosy u sąsiada">Widziałem silosy u sąsiada</option>
                                <option value="Inne">Inne</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <hr class="my-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="zgoda_rodo" name="zgoda_rodo" required>
                                <label class="form-check-label small text-muted" for="zgoda_rodo">
                                    Oświadczam, że zapoznałem się z klauzulą informacyjną RODO i wyrażam zgodę na przetwarzanie moich danych w celu przygotowania oferty. [Wymagane]
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="chce_adres" name="chce_adres" onchange="toggleAddressFields()">
                                <label class="form-check-label fw-bold" for="chce_adres" style="color: var(--main-navy);">
                                    Chcę otrzymać szczegółową wycenę transportu i montażu na podany adres
                                </label>
                            </div>
                        </div>

                        <div id="address-fields" class="col-12 d-none">
                            <div class="card card-body bg-light border-0 rounded-0 p-4">
                                <h6 class="fw-bold mb-3 text-uppercase small" style="color: var(--main-navy);">Adres dostawy i montażu:</h6>
                                <div class="row g-3">
                                    <div class="col-md-8"><input type="text" name="adr_miejscowosc" class="form-control" placeholder="Miejscowość"></div>
                                    <div class="col-md-4"><input type="text" name="adr_kod" class="form-control" placeholder="Kod pocztowy"></div>
                                    <div class="col-md-6"><input type="text" name="adr_ulica" class="form-control" placeholder="Ulica"></div>
                                    <div class="col-md-2"><input type="text" name="adr_nr" class="form-control" placeholder="Nr domu"></div>
                                    <div class="col-md-4"><input type="text" name="adr_poczta" class="form-control" placeholder="Poczta"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-summary">
                    <div class="card border-0 shadow p-4" style="border-radius: 0; border-top: 5px solid var(--main-navy);">
                        <h4 class="fw-bold mb-3" style="color: var(--main-navy);">Twoja konfiguracja</h4>
                        <hr>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Wybrany silos:</span>
                                <span id="summary-silo-name" class="fw-bold text-dark text-end">-</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>Opcje dodatkowe:</span>
                                <span class="fw-bold text-dark"><span id="summary-accs-count">0</span> szt.</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Ilość zestawów:</span>
                                <span class="fw-bold text-dark"><span id="summary-qty">1</span>x</span>
                            </div>
                        </div>

                        <h5 class="fw-bold mt-4 mb-3" style="color: var(--main-navy); font-size: 1.1rem;">Usługi dodatkowe</h5>
                        <div class="form-check form-switch mb-3 p-3 bg-light border">
                            <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" id="usluga_montaz">
                            <label class="form-check-label fw-bold" for="usluga_montaz">Dodaj orientacyjną cenę montażu</label>
                        </div>
                        <div class="form-check form-switch mb-4 p-3 bg-light border">
                            <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" id="usluga_transport" checked>
                            <label class="form-check-label fw-bold" for="usluga_transport">Dodaj orientacyjną cenę transportu</label>
                        </div>
                        <div class="text-muted small mb-3" style="font-size: 0.85rem;">
                            *orientacyjna cena transportu nie dotyczy silosów paszowych.
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase fw-bold text-muted small">Wartość orientacyjna:</span>
                            <span class="badge" style="background-color: var(--main-navy);">NETTO</span>
                        </div>
                        <div id="totalValue" class="display-6 fw-bold mb-0" style="color: var(--main-navy);">0,00 zł</div>
                        <div id="totalValueGross" class="text-muted small mb-3" style="font-size: 0.85rem;">
                            w tym VAT (23%): 0,00 zł brutto
                        </div>

                        <?php if(!empty($konfiguracja['termin'])): ?>
                            <div class="small text-muted fst-italic mb-4 text-center">
                                * <?php echo htmlspecialchars($konfiguracja['termin']); ?>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-konsil btn-lg w-100 shadow">
                            Wyślij do wyceny <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div> </form>

    <?php if(!empty($konfiguracja['note'])): ?>
        <footer class="mt-5 pb-5">
            <div class="container border-top pt-4 text-center">
                <?php if(!empty($konfiguracja['note'])): ?>
                    <p class="text-muted fst-italic mb-4" style="font-size: 0.75rem; line-height: 1.4;">
                        <?php echo htmlspecialchars($konfiguracja['note']); ?>
                    </p>
                <?php endif; ?>

                <div class="opacity-50">
                    <p class="mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 1.5px;">
                        © 2026 P.O.R. KONSIL - Konfigurator Oferty Online
                    </p>
                    <p class="mb-0 text-muted" style="font-size: 0.55rem; letter-spacing: 0.5px;">
                        Projekt i realizacja:
                        <a href="#" class="text-decoration-none text-dark fw-bold" style="border-bottom: 1px solid #ccc;">
                            inż. arch.  Konstanty Kaszubski
                        </a>
                    </p>
                </div>
            </div>
        </footer>
    <?php endif; ?>
</div>

<script>
    const config = <?php echo json_encode($konfiguracja); ?>;
    const categories = <?php echo json_encode($categories_config); ?>;
    const data = <?php echo json_encode($categories_data); ?>;

    let selectedCategory = null;
    let selectedSilo = null;

    function toggleStep(stepNumber) {
        const setIcon = (id, state) => {
            const icon = document.getElementById(id);
            if(!icon) return;
            if(state === 'open') { icon.className = "bi bi-chevron-up step-icon text-primary"; }
            if(state === 'done') { icon.className = "bi bi-check2-circle step-icon text-success"; }
            if(state === 'pending') { icon.className = "bi bi-dash step-icon text-muted"; }
        };

        const content1 = document.getElementById('step1-content');
        if (stepNumber === 1) {
            content1.classList.remove('d-none');
            setIcon('step1-icon', 'open');
        } else {
            content1.classList.add('d-none');
            setIcon('step1-icon', selectedCategory ? 'done' : 'pending');
        }

        const card2 = document.getElementById('step2-card');
        const content2 = document.getElementById('step2-content');
        if (selectedCategory) card2.classList.remove('d-none');

        if (stepNumber === 2 && selectedCategory) {
            content2.classList.remove('d-none');
            setIcon('step2-icon', 'open');
        } else {
            content2.classList.add('d-none');
            setIcon('step2-icon', selectedSilo ? 'done' : 'pending');
        }

        const card3 = document.getElementById('step3-card');
        const content3 = document.getElementById('step3-content');
        if (selectedSilo) card3.classList.remove('d-none');

        if (stepNumber === 3 && selectedSilo) {
            content3.classList.remove('d-none');
            setIcon('step3-icon', 'open');
        } else {
            content3.classList.add('d-none');
            setIcon('step3-icon', selectedSilo ? 'done' : 'pending');
        }
    }

    function checkTaxStatus() {
        const vat = document.getElementById('klient_vat');
        const ryc = document.getElementById('klient_ryczalt');
        const error = document.getElementById('tax-error');
        if (vat.checked && ryc.checked) {
            error.classList.remove('d-none');
            return false;
        } else {
            error.classList.add('d-none');
            calculateTotal();
            return true;
        }
    }

    function toggleAddressFields() {
        const checkbox = document.getElementById('chce_adres');
        const fields = document.getElementById('address-fields');
        if (checkbox.checked) {
            fields.classList.remove('d-none');
            fields.querySelectorAll('input').forEach(i => i.required = true);
        } else {
            fields.classList.add('d-none');
            fields.querySelectorAll('input').forEach(i => { i.required = false; i.value = ''; });
        }
    }

    function renderCategories() {
        document.getElementById('categories-container').innerHTML = categories.map(cat => {
            const isDisabled = cat.disabled === true;
            return `
            <div class="col-md-3">
                <div class="card category-tile h-100 shadow-sm text-center ${isDisabled ? 'disabled' : ''}"
                     onclick="${isDisabled ? '' : `selectCategory('${cat.id}', this)`}">
                    ${isDisabled ? '<div class="coming-soon-badge"><i class="bi bi-clock me-1"></i> WKRÓTCE</div>' : ''}
                    <div class="card-body p-2 d-flex flex-column justify-content-center">
                        <img src="${cat.img}" alt="${cat.name}" class="category-img" onerror="this.onerror=null; this.src='img/placeholder.png';">
                        <h6 class="fw-bold m-0 mt-2" style="color: var(--main-navy); text-transform:uppercase; font-size: 0.8rem;">${cat.name}</h6>
                    </div>
                </div>
            </div>`;
        }).join('');
        toggleStep(1);
    }

    window.selectCategory = function(id, element) {
        selectedCategory = id;
        selectedSilo = null;
        document.querySelectorAll('.category-tile').forEach(el => el.classList.remove('active'));
        element.classList.add('active');

        const silos = data[id] || [];

        document.getElementById('silos-tbody').innerHTML = silos.map((s, i) => {
            const ladownoscDisplay = s.ladownosc ? `<strong>${s.ladownosc} t</strong>` : '-';
            return `
            <tr style="cursor: pointer;" onclick="selectSilo(${i})">
                <td class="text-center">
                    <input type="radio" name="silo_radio" id="silo_r_${i}" class="form-check-input">
                </td>
                <td>
                    <div class="fw-bold" style="color: var(--main-navy);">${s.nazwa}</div>
                    <code class="text-muted small">Kod: ${s.kod}</code>
                </td>
                <td class="text-center text-muted">${ladownoscDisplay}</td>
                <td class="fw-bold text-end">${formatPrice(s.cena)} zł</td>
            </tr>`;
        }).join('');

        // Szukamy danych o wybranej kategorii, żeby pobrać przypis
        const catInfo = categories.find(c => c.id === id);
        const opisGestosci = catInfo ? catInfo.info : 'surowca';

        // AKTUALIZACJA PRZYPISU POD TABELĄ
        let note = document.getElementById('ladownosc-note');
        if(!note) {
            note = document.createElement('div');
            note.id = 'ladownosc-note';
            note.className = 'p-2 text-muted';
            note.style.fontSize = '0.7rem';
            document.getElementById('step2-content').appendChild(note);
        }
        note.innerHTML = `* Ładowność obliczona dla ${opisGestosci}.`;

        toggleStep(2);
        calculateTotal();
    };

    window.selectSilo = function(index) {
        selectedSilo = data[selectedCategory][index];
        const radios = document.querySelectorAll('input[name="silo_radio"]');
        if(radios[index]) radios[index].checked = true;

        const tbody = document.getElementById('accessories-tbody');
        if(!selectedSilo.akcesoria || selectedSilo.akcesoria.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4 fw-bold">Brak płatnych opcji dodatkowych.</td></tr>';
        } else {
            tbody.innerHTML = selectedSilo.akcesoria.map((a, i) => {
                const groupAttr = a.group ? `data-group="${a.group}"` : '';
                const groupClass = a.group ? `acc-grouped` : '';

                // Jeśli element ma flagę is_quote, nie pokazujemy ceny 0.00 zł
                const cenaHtml = a.is_quote
                    ? '<span class="text-danger small fw-bold" style="font-style:italic;">* wycena na zapytanie</span>'
                    : formatPrice(a.cena) + ' zł';

                return `
                <tr class="bg-white">
                    <td class="text-center">
                        <input class="form-check-input acc-checkbox ${groupClass}" type="checkbox" value="${i}" ${groupAttr} style="width:25px; height:25px;">
                    </td>
                    <td>
                        <div class="fw-bold" style="color: var(--main-navy);">${a.nazwa}</div>
                        <code>${a.kod}</code>
                    </td>
                    <td class="fw-bold text-end">${cenaHtml}</td>
                </tr>`;
            }).join('');

            // Podpinamy listenery
            document.querySelectorAll('.acc-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    if (this.classList.contains('acc-grouped') && this.checked) {
                        const group = this.getAttribute('data-group');
                        document.querySelectorAll(`.acc-grouped[data-group="${group}"]`).forEach(other => {
                            if (other !== this) other.checked = false;
                        });
                    }
                    calculateTotal();
                });
            });
        }
        toggleStep(3);
        calculateTotal();
    };

    function calculateTotal() {
        let totalSiloPrice = 0, totalAccPrice = 0, accsCount = 0, hasQuoteItem = false;

        if (selectedSilo) {
            totalSiloPrice = selectedSilo.cena;
            document.querySelectorAll('.acc-checkbox:checked').forEach(cb => {
                const a = selectedSilo.akcesoria[cb.value];
                totalAccPrice += a.cena;
                accsCount++;
                if (a.is_quote) hasQuoteItem = true;
            });
        }

        const qty = parseInt(document.getElementById('silo-qty').value) || 1;
        let baseCost = (totalSiloPrice + totalAccPrice) * qty;

// --- NOWA ZAAWANSOWANA LOGIKA TRANSPORTU I MONTAŻU ---
        let transportCost = 0;
        let montazCost = 0;

        // Obliczanie Transportu: (Baza * Współczynnik) + Stała, ale nie mniej niż (Minimum * Ilość)
        if (document.getElementById('usluga_transport').checked && baseCost > 0) {
            const tWsp = parseFloat(config.koszt_transportu_wsp) || 0;
            const tStala = parseFloat(config.koszt_transportu_stala) || 0;
            const tMinBaza = parseFloat(config.koszt_transportu_min) || 990;

            const tMin = tMinBaza * qty; // <--- ZMIANA: Mnożymy minimum przez ilość zestawów

            let calcT = (baseCost * tWsp) + tStala;
            transportCost = Math.max(calcT, tMin); // Wybiera większą kwotę
        }

        // Obliczanie Montażu: (Baza * Współczynnik) + Stała, ale nie mniej niż (Minimum * Ilość)
        if (document.getElementById('usluga_montaz').checked && baseCost > 0) {
            const mWsp = parseFloat(config.koszt_montazu_wsp) || parseFloat(config.koszt_montazu) || 0;
            const mStala = parseFloat(config.koszt_montazu_stala) || 0;
            const mMinBaza = parseFloat(config.koszt_montazu_min) || 0;

            const mMin = mMinBaza * qty; // <--- ZMIANA: Mnożymy minimum przez ilość zestawów

            let calcM = (baseCost * mWsp) + mStala;
            montazCost = Math.max(calcM, mMin);
        }

        // --- PODSUMOWANIE ---
        let finalTotal = baseCost + transportCost + montazCost;

        document.getElementById('summary-silo-name').innerText = selectedSilo ? selectedSilo.nazwa : '-';
        document.getElementById('summary-accs-count').innerText = accsCount;
        document.getElementById('summary-qty').innerText = qty;

        // Dopisek o wycenie indywidualnej, jeśli wybrano coś ze znakiem $
        const extraInfo = hasQuoteItem ? ' <small style="font-size:0.9rem; color:#d9534f;">+ wycena indyw.</small>' : '';
        document.getElementById('totalValue').innerHTML = formatPrice(finalTotal) + " zł" + extraInfo;

        let finalTotalGross = finalTotal * 1.23;
        document.getElementById('totalValueGross').innerText = "w tym VAT (23%): " + formatPrice(finalTotalGross) + " zł brutto";

        // Aktualizacja paczki danych do PDF-a
        let payload = {
            silo: selectedSilo,
            akcesoria: [],
            qty: qty,
            total: finalTotal,
            montaz: montazCost,         // Teraz przekazujemy dokładną kwotę do wyslij.php
            transport: transportCost,   // Teraz przekazujemy dokładną kwotę do wyslij.php
            kodRabatowy: document.getElementById('kod_rabatowy').value,
            infoGestosc: selectedCategory ? categories.find(c => c.id === selectedCategory).info : ''
        };

        document.querySelectorAll('.acc-checkbox:checked').forEach(cb => {
            payload.akcesoria.push(selectedSilo.akcesoria[cb.value]);
        });

        document.getElementById('hidden-payload').value = JSON.stringify(payload);
    }

    function formatPrice(val) {
        return val.toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function validateForm() {
        if(!selectedSilo) {
            alert("Proszę wybrać kategorię oraz model silosu.");
            return false;
        }
        if (!checkTaxStatus()) {
            alert("Proszę poprawić status podatkowy.");
            return false;
        }
        return true;
    }

    // Dodanie nasłuchiwaczy zdarzeń
    ['silo-qty', 'usluga_montaz', 'usluga_transport', 'klient_vat', 'klient_ryczalt', 'kod_rabatowy'].forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            el.addEventListener('change', calculateTotal);
            if(id === 'silo-qty' || id === 'kod_rabatowy') el.addEventListener('input', calculateTotal);
        }
    });

    // Start aplikacji
    renderCategories();
</script>
</body>
</html>