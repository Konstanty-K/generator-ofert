<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silosy Konsil - Konfigurator Oferty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --main-navy: #0b2239;
            --accent-soft: #ced4da;
            --bg-light: #f8f9fa;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333;
        }

        /* NAGŁÓWEK KONSIL */
        .konsil-header {
            background-color: var(--main-navy);
            color: white;
            padding: 30px 0;
            border-bottom: 3px solid var(--accent-soft);
        }
        .header-logo { max-height: 100px; width: auto; margin-bottom: 10px; }
        .header-subtext { font-size: 0.75rem; font-weight: 600; letter-spacing: 1.5px; margin: 0; }
        .icon-gray { color: var(--accent-soft) !important; margin-right: 8px; }

        /* KAFELKI KATEGORII */
        .category-tile { border: 2px solid transparent; border-radius: 0; transition: all 0.2s; cursor: pointer; }
        .category-tile:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        .category-tile.active { border-color: var(--main-navy); background-color: white; }
        .category-img { height: 90px; object-fit: contain; margin-bottom: 15px; }

        /* UI STRUKTURA */
        .section-title { color: var(--main-navy); border-left: 5px solid var(--main-navy); padding-left: 15px; font-weight: bold; }
        .table-dark { background-color: var(--main-navy) !important; border: none; }
        .btn-konsil {
            background-color: var(--main-navy); color: white; border-radius: 0;
            padding: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-konsil:hover { background-color: #162e4a; color: white; transform: translateY(-2px); }

        /* BOCZNY PANEL */
        .sidebar-summary { position: sticky; top: 20px; z-index: 1000; }
        .form-switch .form-check-input:checked { background-color: var(--main-navy); border-color: var(--main-navy); }
    </style>
</head>
<body>

<?php
// =========================================================
// 1. ZACIĄGANIE DANYCH Z PLIKU GŁÓWNEGO (MASTER)
// =========================================================
$cenyMaster = [];
if (($handle = fopen("produkty.csv", "r")) !== FALSE) {
    fgetcsv($handle);
    while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
        if(count($data) > 39) {
            $kod = trim($data[0]);
            $nazwa = trim($data[1]);
            $cena = (float)str_replace(',', '.', trim($data[39]));
            $cenyMaster[$kod] = ['nazwa' => $nazwa, 'cena' => $cena];
        }
    }
    fclose($handle);
}

// =========================================================
// 2. ŁADOWANIE KATEGORII Z PLIKÓW KONSIL
// =========================================================
$categories_config = [
        ['id' => 'lejowe', 'name' => 'Silosy Lejowe', 'file' => 'WYCENA- ONLINE - silosy lejowe.csv', 'img' => 'img/cat_lejowe.png'],
        ['id' => 'lejowe_faliste', 'name' => 'Silosy Lejowe Faliste', 'file' => 'WYCENA- ONLINE - silosy lejowe faliste.csv', 'img' => 'img/cat_lejowe_faliste.png'],
        ['id' => 'plaskodenne', 'name' => 'Silosy Płaskodenne', 'file' => 'WYCENA- ONLINE - silosy płaskodenne.csv', 'img' => 'img/cat_plaskodenne.png']
];

$categories_data = [];
foreach ($categories_config as $cat) {
    $silos = [];
    if (file_exists($cat['file']) && ($handle = fopen($cat['file'], "r")) !== FALSE) {
        $model_row_found = false;
        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            $first_col = trim(strtoupper($data[0] ?? ''));
            // Szukamy wiersza startowego
            if ($first_col === 'MODEL' || $first_col === 'KATEGORIA') { $model_row_found = true; continue; }

            if ($model_row_found && $first_col !== '' && $first_col !== 'NAN') {
                $silo_code = trim($data[0]);
                $accs = [];
                for ($i = 1; $i < count($data); $i++) {
                    $val = trim($data[$i] ?? '');
                    // Pomijanie standardów, kresek i pustych
                    if ($val && !in_array(strtoupper($val), ['NAN', '-', 'S-STANDARD', ''])) {
                        // Rozdzielanie wielokrotnych kodów z jednej komórki (np. PPZ, PPZ2)
                        $parts = explode(',', $val);
                        foreach ($parts as $part) {
                            $p = trim($part);
                            if($p) $accs[] = $p;
                        }
                    }
                }

                // Mapowanie Ceny Głównej Silosa
                $silo_master = $cenyMaster[$silo_code] ?? ['nazwa' => $silo_code, 'cena' => 0];

                // Mapowanie Cen Akcesoriów
                $accs_detailed = [];
                foreach ($accs as $ac) {
                    $ac_master = $cenyMaster[$ac] ?? ['nazwa' => $ac, 'cena' => 0];
                    $accs_detailed[] = ['kod' => $ac, 'nazwa' => $ac_master['nazwa'], 'cena' => $ac_master['cena']];
                }

                $silos[] = [
                        'kod' => $silo_code, 'nazwa' => $silo_master['nazwa'],
                        'cena' => $silo_master['cena'], 'akcesoria' => $accs_detailed
                ];
            }
        }
        fclose($handle);
    }
    $categories_data[$cat['id']] = $silos;
}

// =========================================================
// 3. ŁADOWANIE KONFIGURACJI (koszty stałe)
// =========================================================
$konfiguracja = ['koszt_transportu' => 0, 'koszt_montazu' => 0];
if (file_exists('konfiguracja.csv') && ($handle = fopen('konfiguracja.csv', "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if(count($data) >= 2) $konfiguracja[trim($data[0])] = (float)str_replace(',', '.', trim($data[1]));
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

                <div class="mb-5">
                    <h3 class="section-title mb-4">1. Wybierz kategorię zbiorników</h3>
                    <div class="row g-3" id="categories-container"></div>
                </div>

                <div id="step-silo" class="mb-5 d-none">
                    <h3 class="section-title mb-4">2. Wybierz model silosu</h3>
                    <select id="silo-select" class="form-select form-select-lg shadow-sm" style="border-radius: 0; border: 2px solid #ddd;">
                        <option value="">-- Najpierw wybierz model z listy --</option>
                    </select>
                </div>

                <div id="step-accessories" class="mb-5 d-none">
                    <h3 class="section-title mb-4">3. Opcjonalne akcesoria</h3>
                    <div class="table-responsive shadow-sm border mb-4">
                        <table class="table align-middle m-0">
                            <thead class="table-dark">
                            <tr>
                                <th style="width: 60px;" class="text-center">Wybór</th>
                                <th>Akcesorium i Kod</th>
                                <th class="text-end">Cena netto</th>
                            </tr>
                            </thead>
                            <tbody id="accessories-tbody">
                            </tbody>
                        </table>
                    </div>

                    <h3 class="section-title mt-5 mb-4">4. Ilość konfiguracji</h3>
                    <div class="d-flex align-items-center bg-white p-4 shadow-sm border">
                        <label class="fw-bold me-4">Ilość zamawianych zestawów (Silos + Akcesoria):</label>
                        <input type="number" id="silo-qty" class="form-control form-control-lg text-center fw-bold" value="1" min="1" style="width: 120px; border-radius: 0; border: 2px solid var(--main-navy);">
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-5" id="dane-klienta" style="border-radius: 0;">
                    <h3 class="section-title mb-4">Dane do wyceny i kontaktu</h3>
                    <div class="row g-4">
                        <div class="col-md-6"><input type="text" name="klient_nazwa" class="form-control form-control-lg" placeholder="Imię i Nazwisko / Firma" style="border-radius:0;" required></div>
                        <div class="col-md-6"><input type="email" name="klient_email" class="form-control form-control-lg" placeholder="Adres E-mail" style="border-radius:0;" required></div>
                        <div class="col-md-6"><input type="text" name="klient_nip" class="form-control form-control-lg" placeholder="NIP (opcjonalnie)" style="border-radius:0;"></div>
                        <div class="col-md-6"><input type="tel" name="klient_telefon" class="form-control form-control-lg" placeholder="Numer telefonu" style="border-radius:0;" required></div>
                        <div class="col-12"><textarea name="uwagi" class="form-control" rows="3" placeholder="Dodatkowe uwagi do oferty lub lokalizacja montażu..." style="border-radius:0;"></textarea></div>
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
                                <span id="summary-silo-name" class="fw-bold text-dark">-</span>
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

                        <h5 class="fw-bold mt-4 mb-3" style="color: var(--main-navy);">Usługi dodatkowe</h5>
                        <div class="form-check form-switch mb-3 p-3 bg-light border">
                            <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" id="usluga_montaz" checked>
                            <label class="form-check-label fw-bold" for="usluga_montaz">Zlecam Montaż</label>
                        </div>
                        <div class="form-check form-switch mb-4 p-3 bg-light border">
                            <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" id="usluga_transport" checked>
                            <label class="form-check-label fw-bold" for="usluga_transport">Zlecam Transport</label>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase fw-bold text-muted small">Wartość orientacyjna:</span>
                            <span class="badge" style="background-color: var(--main-navy);">NETTO</span>
                        </div>
                        <div id="totalValue" class="display-6 fw-bold mb-4" style="color: var(--main-navy);">0,00 zł</div>

                        <button type="submit" class="btn btn-konsil btn-lg w-100 shadow">
                            Wyślij do wyceny <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const config = <?php echo json_encode($konfiguracja); ?>;
    const categories = <?php echo json_encode($categories_config); ?>;
    const data = <?php echo json_encode($categories_data); ?>;

    let selectedCategory = null;
    let selectedSilo = null;

    // 1. Rysowanie kategorii
    function renderCategories() {
        document.getElementById('categories-container').innerHTML = categories.map(cat => `
            <div class="col-md-4">
                <div class="card category-tile h-100 shadow-sm text-center" onclick="selectCategory('${cat.id}', this)">
                    <div class="card-body p-4">
                        <img src="${cat.img}" alt="${cat.name}" class="category-img" onerror="this.src='img/brakfoto.webp'">
                        <h6 class="fw-bold m-0" style="color: var(--main-navy); text-transform:uppercase;">${cat.name}</h6>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // 2. Obsługa wyboru kategorii
    window.selectCategory = function(id, element) {
        selectedCategory = id;
        selectedSilo = null;

        document.querySelectorAll('.category-tile').forEach(el => el.classList.remove('active'));
        element.classList.add('active');

        const silos = data[id] || [];
        const select = document.getElementById('silo-select');
        select.innerHTML = '<option value="">-- Wybierz model silosu z listy --</option>' + silos.map((s, i) => `
            <option value="${i}">${s.nazwa} (Kod: ${s.kod}) - ${formatPrice(s.cena)} zł</option>
        `).join('');

        document.getElementById('step-silo').classList.remove('d-none');
        document.getElementById('step-accessories').classList.add('d-none');
        calculateTotal();
    };

    // 3. Obsługa wyboru Silosu
    document.getElementById('silo-select').addEventListener('change', function() {
        if(this.value === "") {
            selectedSilo = null;
            document.getElementById('step-accessories').classList.add('d-none');
            calculateTotal();
            return;
        }

        selectedSilo = data[selectedCategory][this.value];
        const tbody = document.getElementById('accessories-tbody');

        if(selectedSilo.akcesoria.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4 fw-bold">Brak płatnych opcji dodatkowych dla tego modelu (wyposażenie standardowe w cenie).</td></tr>';
        } else {
            tbody.innerHTML = selectedSilo.akcesoria.map((a, i) => `
                <tr class="product-row bg-white">
                    <td class="text-center">
                        <input class="form-check-input acc-checkbox" type="checkbox" value="${i}" style="width:25px; height:25px; cursor:pointer;">
                    </td>
                    <td>
                        <div class="fw-bold" style="color: var(--main-navy);">${a.nazwa}</div>
                        <code class="text-muted small">${a.kod}</code>
                    </td>
                    <td class="price-value fw-bold text-end text-nowrap">${formatPrice(a.cena)} zł</td>
                </tr>
             `).join('');

            document.querySelectorAll('.acc-checkbox').forEach(cb => cb.addEventListener('change', calculateTotal));
        }

        document.getElementById('step-accessories').classList.remove('d-none');
        calculateTotal();
    });

    // 4. Nasłuchiwanie zmian ilości i usług
    ['silo-qty', 'usluga_montaz', 'usluga_transport'].forEach(id => {
        document.getElementById(id).addEventListener('change', calculateTotal);
        document.getElementById(id).addEventListener('input', calculateTotal);
    });

    function formatPrice(val) { return val.toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    // 5. Główny silnik przeliczający koszyk
    function calculateTotal() {
        let totalSiloPrice = 0, totalAccPrice = 0, accsCount = 0;

        if (selectedSilo) {
            totalSiloPrice = selectedSilo.cena;
            document.querySelectorAll('.acc-checkbox:checked').forEach(cb => {
                totalAccPrice += selectedSilo.akcesoria[cb.value].cena;
                accsCount++;
            });
        }

        const qty = parseInt(document.getElementById('silo-qty').value) || 1;
        let baseTotal = (totalSiloPrice + totalAccPrice) * qty;

        // Usługi dodatkowe mnożymy przez ilość silosów (jeśli płatne)
        let montazCost = config.koszt_montazu || 0;
        let transportCost = config.koszt_transportu || 0;

        if(document.getElementById('usluga_montaz').checked) baseTotal += (montazCost * qty);
        if(document.getElementById('usluga_transport').checked) baseTotal += (transportCost * qty);

        // Aktualizacja Sidebara
        document.getElementById('summary-silo-name').innerText = selectedSilo ? selectedSilo.nazwa : 'Brak';
        document.getElementById('summary-accs-count').innerText = accsCount;
        document.getElementById('summary-qty').innerText = qty;
        document.getElementById('totalValue').innerText = formatPrice(baseTotal) + " zł";

        // Tworzenie "paczki danych" (JSON) do skryptu wyslij.php
        let payload = {
            silo: selectedSilo,
            akcesoria: [],
            qty: qty,
            montaz: document.getElementById('usluga_montaz').checked,
            transport: document.getElementById('usluga_transport').checked,
            total: baseTotal
        };
        if (selectedSilo) {
            document.querySelectorAll('.acc-checkbox:checked').forEach(cb => {
                payload.akcesoria.push(selectedSilo.akcesoria[cb.value]);
            });
        }
        document.getElementById('hidden-payload').value = JSON.stringify(payload);
    }

    // Zabezpieczenie przed wysłaniem pustego formularza
    function validateForm() {
        if(!selectedSilo) {
            alert("Proszę wybrać kategorię oraz model silosu przed przejściem do wyceny.");
            return false;
        }
        return true;
    }

    renderCategories();
</script>

</body>
</html>