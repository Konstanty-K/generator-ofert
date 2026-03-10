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

        /* KAFELKI KATEGORII (PIONOWE) */
        .category-tile {
            border: 2px solid transparent;
            border-radius: 0;
            transition: all 0.2s;
            cursor: pointer;
            min-height: 250px; /* Wymuszenie proporcji pionowych */
        }
        .category-tile:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        .category-tile.active { border-color: var(--main-navy); background-color: white; }
        .category-img {
            height: 180px; /* Wyższe zdjęcie */
            width: 100%;
            object-fit: contain;
            margin-bottom: 10px;
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
// 1. DANE MASTER
$cenyMaster = [];
if (($handle = @fopen("produkty.csv", "r")) !== FALSE) {
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

// 2. KATEGORIE
$categories_config = [
        ['id' => 'lejowe', 'name' => 'Silosy Lejowe', 'file' => 'WYCENA- ONLINE - silosy lejowe.csv', 'img' => 'img/cat_lejowe.png'],
        ['id' => 'lejowe_faliste', 'name' => 'Silosy Lejowe Faliste', 'file' => 'WYCENA- ONLINE - silosy lejowe faliste.csv', 'img' => 'img/cat_lejowe_faliste.png'],
        ['id' => 'plaskodenne', 'name' => 'Silosy Płaskodenne', 'file' => 'WYCENA- ONLINE - silosy płaskodenne.csv', 'img' => 'img/cat_plaskodenne.png']
];

$categories_data = [];
foreach ($categories_config as $cat) {
    $silos = [];
    if (file_exists($cat['file']) && ($handle = @fopen($cat['file'], "r")) !== FALSE) {
        $model_row_found = false;
        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            $first_col = trim(strtoupper($data[0] ?? ''));
            if ($first_col === 'MODEL' || $first_col === 'KATEGORIA') { $model_row_found = true; continue; }

            if ($model_row_found && $first_col !== '' && $first_col !== 'NAN') {
                $silo_code = trim($data[0]);
                $accs = [];
                for ($i = 1; $i < count($data); $i++) {
                    $val = trim($data[$i] ?? '');
                    if ($val && !in_array(strtoupper($val), ['NAN', '-', 'S-STANDARD', ''])) {
                        $parts = explode(',', $val);
                        foreach ($parts as $part) {
                            $p = trim($part);
                            if($p) $accs[] = $p;
                        }
                    }
                }

                $silo_master = $cenyMaster[$silo_code] ?? ['nazwa' => $silo_code, 'cena' => 0];
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

// 3. KONFIGURACJA
$konfiguracja = ['koszt_transportu' => 0, 'koszt_montazu' => 0, 'termin' => ''];
if (file_exists('konfiguracja.csv') && ($handle = @fopen('konfiguracja.csv', "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if(count($data) >= 2) {
            $klucz = trim($data[0]);
            $wartosc = trim($data[1]);
            if ($klucz === 'termin') {
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
                            <table class="table align-middle m-0 table-hover">
                                <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>Model silosu</th>
                                    <th class="text-end">Cena netto</th>
                                </tr>
                                </thead>
                                <tbody id="silos-tbody">
                                </tbody>
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
                            <table class="table align-middle m-0">
                                <thead class="table-dark">
                                <tr>
                                    <th style="width: 60px;" class="text-center"><i class="bi bi-check-square"></i></th>
                                    <th>Akcesorium i Kod</th>
                                    <th class="text-end">Cena netto</th>
                                </tr>
                                </thead>
                                <tbody id="accessories-tbody">
                                </tbody>
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
                        <div class="col-md-6"><input type="text" name="klient_nazwa" aria-label="Imię i Nazwisko / Firma" class="form-control form-control-lg" placeholder="Imię i Nazwisko / Firma" style="border-radius:0;" required></div>
                        <div class="col-md-6"><input type="email" name="klient_email" aria-label="Adres E-mail" class="form-control form-control-lg" placeholder="Adres E-mail" style="border-radius:0;" required></div>
                        <div class="col-md-6"><input type="text" name="klient_nip" aria-label="NIP" class="form-control form-control-lg" placeholder="NIP (opcjonalnie)" style="border-radius:0;"></div>
                        <div class="col-md-6"><input type="tel" name="klient_telefon" aria-label="Numer telefonu" class="form-control form-control-lg" placeholder="Numer telefonu" style="border-radius:0;" required></div>
                        <div class="col-12"><textarea name="uwagi" aria-label="Dodatkowe uwagi" class="form-control" rows="3" placeholder="Dodatkowe uwagi do oferty lub lokalizacja montażu..." style="border-radius:0;"></textarea></div>
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
                            <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" id="usluga_montaz" checked>
                            <label class="form-check-label fw-bold" for="usluga_montaz">Zlecam Montaż</label> </div>
                        <div class="form-check form-switch mb-4 p-3 bg-light border">
                            <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" id="usluga_transport" checked>
                            <label class="form-check-label fw-bold" for="usluga_transport">Zlecam Transport</label> </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-uppercase fw-bold text-muted small">Wartość orientacyjna:</span>
                            <span class="badge" style="background-color: var(--main-navy);">NETTO</span>
                        </div>
                        <div id="totalValue" class="display-6 fw-bold mb-2" style="color: var(--main-navy);">0,00 zł</div>

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
        </div>
    </form>
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

    function renderCategories() {
        document.getElementById('categories-container').innerHTML = categories.map(cat => `
            <div class="col-md-4">
                <div class="card category-tile h-100 shadow-sm text-center" onclick="selectCategory('${cat.id}', this)">
                    <div class="card-body p-2 d-flex flex-column justify-content-center">
                        <img src="${cat.img}" alt="${cat.name}" class="category-img" onerror="this.onerror=null; this.src='';">
                        <h6 class="fw-bold m-0 mt-2" style="color: var(--main-navy); text-transform:uppercase; font-size: 0.85rem;">${cat.name}</h6>
                    </div>
                </div>
            </div>
        `).join('');
        toggleStep(1);
    }

    window.selectCategory = function(id, element) {
        selectedCategory = id;
        selectedSilo = null;

        document.querySelectorAll('.category-tile').forEach(el => el.classList.remove('active'));
        element.classList.add('active');

        const silos = data[id] || [];
        document.getElementById('silos-tbody').innerHTML = silos.map((s, i) => `
            <tr style="cursor: pointer;" onclick="selectSilo(${i})">
                <td class="text-center"><input type="radio" aria-label="Wybierz silos" name="silo_radio" id="silo_r_${i}" class="form-check-input" style="width:20px; height:20px;"></td>
                <td>
                    <div class="fw-bold" style="color: var(--main-navy);">${s.nazwa}</div>
                    <code class="text-muted small">Kod: ${s.kod}</code>
                </td>
                <td class="fw-bold text-end">${formatPrice(s.cena)} zł</td>
            </tr>
        `).join('');

        toggleStep(2);
        calculateTotal();
    };

    window.selectSilo = function(index) {
        selectedSilo = data[selectedCategory][index];

        document.querySelectorAll('input[name="silo_radio"]').forEach(r => r.checked = false);
        document.getElementById('silo_r_' + index).checked = true;

        const tbody = document.getElementById('accessories-tbody');
        if(selectedSilo.akcesoria.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4 fw-bold">Brak płatnych opcji dodatkowych dla tego modelu.</td></tr>';
        } else {
            tbody.innerHTML = selectedSilo.akcesoria.map((a, i) => `
                <tr class="bg-white">
                    <td class="text-center">
                        <input class="form-check-input acc-checkbox" aria-label="Wybierz akcesorium" type="checkbox" value="${i}" style="width:25px; height:25px; cursor:pointer;">
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

        toggleStep(3);
        calculateTotal();
    };

    ['silo-qty', 'usluga_montaz', 'usluga_transport'].forEach(id => {
        document.getElementById(id).addEventListener('change', calculateTotal);
        if(id === 'silo-qty') document.getElementById(id).addEventListener('input', calculateTotal);
    });

    function formatPrice(val) { return val.toLocaleString('pl-PL', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

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
        let baseCost = (totalSiloPrice + totalAccPrice) * qty;

        let multiplier = 1.0;
        if(document.getElementById('usluga_montaz').checked) {
            multiplier += (parseFloat(config.koszt_montazu) || 0);
        }
        if(document.getElementById('usluga_transport').checked) {
            multiplier += (parseFloat(config.koszt_transportu) || 0);
        }

        let finalTotal = baseCost * multiplier;

        document.getElementById('summary-silo-name').innerText = selectedSilo ? selectedSilo.nazwa : '-';
        document.getElementById('summary-accs-count').innerText = accsCount;
        document.getElementById('summary-qty').innerText = qty;
        document.getElementById('totalValue').innerText = formatPrice(finalTotal) + " zł";

        let payload = {
            silo: selectedSilo, akcesoria: [], qty: qty, baseCost: baseCost,
            montaz: document.getElementById('usluga_montaz').checked ? config.koszt_montazu : 0,
            transport: document.getElementById('usluga_transport').checked ? config.koszt_transportu : 0,
            total: finalTotal
        };
        if (selectedSilo) {
            document.querySelectorAll('.acc-checkbox:checked').forEach(cb => {
                payload.akcesoria.push(selectedSilo.akcesoria[cb.value]);
            });
        }
        document.getElementById('hidden-payload').value = JSON.stringify(payload);
    }

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