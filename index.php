<?php
$test = fgetcsv(fopen("produkty.csv", "r"), 2000, ",");
echo "<!-- Kolumn: " . count($test) . " -->";

// Pre-load akcesoriów z CSV do bezpiecznej tablicy JSON
$accessoriesData = [];
$accFile = fopen("WYCENA-ONLINE-Arkusz1.csv", "r");
if ($accFile) {
    $rowIdx = 0;
    $headers = [];
    while (($row = fgetcsv($accFile, 4000, ",")) !== FALSE) {
        $rowIdx++;
        if ($rowIdx == 5) {
            $headers = $row; // Wiersz 5 to nazwy akcesoriów (kolumny)
        } elseif ($rowIdx >= 6) {
            $modelCode = trim($row[0] ?? '');
            if (!empty($modelCode)) {
                $accessoriesData[$modelCode] = [];
                for ($col = 1; $col < count($row); $col++) {
                    $val = trim($row[$col]);
                    $headerName = trim($headers[$col] ?? '');
                    // Jeśli nie jest puste, nie jest pauzą, i nie jesteśmy za pustym nagłówkiem
                    if ($val !== '' && $val !== '-' && !empty($headerName)) {
                        $accessoriesData[$modelCode][] = [
                                'name' => $headerName,
                                'type' => $val // kod produktu, 'S-STANDARD' itp.
                        ];
                    }
                }
            }
        }
    }
    fclose($accFile);
}
$accessoriesJson = json_encode($accessoriesData, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silosy Konsil - Generator Ofert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sticky-summary { position: sticky; top: 20px; }
        .product-image { width: 60px; height: 60px; object-fit: cover; }

        :root {
            --main-navy: #0b2239;
            --accent-soft: #ced4da;
            --accent-gray: #dee2e6;
            --bg-light: #f8f9fa;
        }

        .konsil-header {
            background-color: var(--main-navy);
            color: white;
            padding: 30px 0;
            border-bottom: 3px solid var(--accent-soft);
            margin-bottom: 10px;
        }

        .header-logo { max-height: 100px; width: auto; margin-bottom: 10px; }
        .header-subtext {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            color: white;
            text-transform: uppercase;
            margin: 0;
        }

        .icon-gray { color: var(--accent-gray) !important; margin-right: 8px; }

        /* Style dla kart kategorii */
        .cursor-pointer { cursor: pointer; }
        .category-card { transition: all 0.3s ease; border: 2px solid transparent !important; }
        .category-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
        .category-card.selected { border: 2px solid var(--main-navy) !important; box-shadow: 0 5px 15px rgba(11, 34, 57, 0.2) !important; }
        .category-card.selected .selection-indicator { display: block !important; background-color: var(--main-navy) !important; }

        .fade-element {
            opacity: 1;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }
        .fade-element.d-none {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>
<body class="bg-light">

<header class="konsil-header shadow-sm">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column align-items-start">
                <img src="konsil_logo_main.png" alt="Konsil Logo" class="header-logo">
                <p class="header-subtext mb-0">PRZEDSIĘBIORSTWO OBSŁUGI ROLNICTWA KONSIL</p>
            </div>
            <div class="text-end d-none d-md-block">
                <div class="mb-1">
                    <i class="bi bi-telephone-fill icon-gray"></i>
                    <span class="fw-bold">52 385-78-59</span>
                </div>
                <div>
                    <i class="bi bi-envelope icon-gray"></i>
                    <span class="opacity-75">silosy@konsil.pl</span>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container py-2">
    <h1 class="mb-4 text-center">Generator Zapytań Ofertowych - Silosy Konsil</h1>

    <form action="wyslij.php" method="POST" class="row g-4" id="mainForm">

        <!-- Przechowujemy dane wybranego silosa -->
        <input type="hidden" name="selected_silo_code" id="selectedSiloCode" value="">
        <input type="hidden" name="selected_silo_name" id="selectedSiloName" value="">
        <input type="hidden" name="selected_silo_price" id="selectedSiloPrice" value="0">

        <!-- Kolumna lewa (Produkty / Kategorie / Akcesoria) -->
        <div class="col-lg-8">

            <!-- KROK 1: KARTY KATEGORII -->
            <div id="step-category" class="fade-element">
                <h3 class="mb-4 text-center" style="color: var(--main-navy);">Wybierz typ silosu</h3>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100 category-card shadow-sm cursor-pointer border-0" onclick="selectCategory('BIN', 'Silosy Płaskodenne')">
                            <img src="plaskodenne.jpg" class="card-img-top" alt="Płaskodenne" style="height: 250px; object-fit: cover; background:#e9ecef;">
                            <div class="card-body text-center p-4">
                                <h4 class="card-title fw-bold" style="color: var(--main-navy);">Silosy Płaskodenne</h4>
                                <p class="text-muted mb-0">Wybierz, aby zobaczyć systemy typu BIN i dedykowane akcesoria.</p>
                                <button type="button" class="btn btn-outline-primary w-100 mt-3" style="border-color: var(--main-navy); color: var(--main-navy);">
                                    Przejdź do produktów →
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 category-card shadow-sm cursor-pointer border-0" onclick="selectCategory('KONSIL', 'Silosy Lejowe')">
<!--                            <img src="img/lejowe.webp" class="card-img-top" alt="Lejowe" style="height: 250px; object-fit: cover; background:#e9ecef;">-->
                            <img src="lejowe.png" class="card-img-top" alt="Lejowe" style="height: 250px; object-fit: cover; background:#e9ecef;">
                            <div class="card-body text-center p-4">
                                <h4 class="card-title fw-bold" style="color: var(--main-navy);">Silosy Lejowe</h4>
                                <p class="text-muted mb-0">Wybierz, aby zobaczyć systemy typu KONSIL i dedykowane akcesoria.</p>
                                <button type="button" class="btn btn-outline-primary w-100 mt-3" style="border-color: var(--main-navy); color: var(--main-navy);">
                                    Przejdź do produktów →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KROK 2: TABELA PRODUKTÓW (Domylnie ukryta) -->
            <div id="step-products" class="d-none fade-element">
                <div class="card shadow p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h3 class="mb-0" id="dynamicCategoryTitle" style="color: var(--main-navy);">Wybierz Produkty</h3>
                            <small class="text-muted">Dodaj wybrane pozycje do zamówienia</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetCategory()">
                            ← Zmień typ silosu
                        </button>
                    </div>

                    <div class="mb-4">
                        <input type="text" id="searchProductsInput" class="form-control form-control-lg bg-light"
                               placeholder="🔍 Szukaj produktu w tej kategorii...">
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle" id="productTable">
                            <thead class="table-dark" style="background-color: var(--main-navy) !important;">
                            <tr>
                                <th style="width: 80px;">Zdjęcie</th>
                                <th>Produkt</th>
                                <th class="text-end">Cena netto</th>
                                <th>Opis</th>
                                <th style="width: 140px;" class="text-center">Akcja</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $file = fopen("produkty.csv", "r");
                            if ($file) {
                                fgetcsv($file, 2000, ",");
                                while (($data = fgetcsv($file, 2000, ",")) !== FALSE) {
                                    $kod = $data[0] ?? '';
                                    $nazwa = $data[1] ?? 'Brak nazwy';
                                    $cena = (float)str_replace(',', '.', $data[39]);
                                    $opis = $data[19] ?? '';

                                    if (empty($kod) || empty($nazwa)) continue;

                                    $rozszerzenia = ['webp', 'jpg', 'jpeg', 'png', 'avif'];
                                    $sciezka_foto = 'img/brakfoto.webp';
                                    foreach ($rozszerzenia as $ext) {
                                        $test_path = "img/" . $kod . "." . $ext;
                                        if (file_exists($test_path)) {
                                            $sciezka_foto = $test_path;
                                            break;
                                        }
                                    }

                                    echo "<tr class='product-row'>
                                                <td><img src='$sciezka_foto' width='60' height='60' style='object-fit:contain;' class='rounded border bg-white'></td>
                                                <td><strong>$nazwa</strong><br><small class='text-muted'>Kod: <span class='product-code'>$kod</span></small></td>
                                                <td class='price-value text-end fw-bold' data-price='$cena'>" . number_format($cena, 2, ',', ' ') . " zł</td>
                                                <td class='small text-muted'>$opis</td>
                                                <td class='text-center'>
                                                    <!-- Zamiast qty-input jest przycisk -->
                                                    <button type='button' class='btn btn-sm btn-primary' onclick='selectSiloModel(this, \"$kod\", \"$nazwa\", $cena)'>Wybierz</button>
                                                </td>
                                              </tr>";
                                }
                                fclose($file);
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- KROK 3: AKCESORIA DO WYBRANEGO SILOSU -->
            <div id="step-accessories" class="d-none fade-element">
                <div class="card shadow p-4 mb-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0 text-primary">Wybrano: <span id="selectedSiloLabel"></span></h4>
                            <div class="fw-bold" id="selectedSiloPriceLabel">0,00 zł</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="backToSiloList()">
                            ← Zmień model silosu
                        </button>
                    </div>

                    <!-- WYBÓR ILOŚCI PAKIETÓW (Silos + Akcesoria) -->
                    <div class="alert alert-info d-flex align-items-center">
                        <label class="mb-0 me-3 fw-bold">Liczba kompletów (Silos + Wybrane Akcesoria):</label>
                        <input type="number" id="siloPackageQty" name="silo_quantity" class="form-control w-25 qty-input" value="1" min="1" onchange="updateTotals()">
                    </div>
                </div>

                <div class="card shadow p-4">
                    <h3 class="mb-3" style="color: var(--main-navy);">Dostępne akcesoria i opcje</h3>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Akcesorium</th>
                                <th>Typ / Kod</th>
                                <th class="text-center">Wybór (do 1 kompletu)</th>
                            </tr>
                            </thead>
                            <tbody id="accessoriesTableBody">
                            <!-- Generowane dynamicznie w JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kolumna podsumowania (Prawa Strona - ZAWSZE WIDOCZNA) -->
        <div class="col-lg-4">
            <div class="sticky-summary">
                <div class="card shadow mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Podsumowanie</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Silos + Akcesoria:</span>
                            <span id="productTotal" class="fw-bold">0,00 zł</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Montaż:</span>
                            <span id="montazTotal" class="fw-bold">0,00 zł</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Transport:</span>
                            <span id="transportTotal" class="fw-bold">0,00 zł</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="h5">Razem:</span>
                            <span id="totalValue" class="h4 text-primary fw-bold">0,00 zł</span>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Dodatkowe Usługi</h5>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="montaz" id="montazCheck" value="TAK">
                            <label class="form-check-label" for="montazCheck">
                                <strong>Montaż</strong>
                                <small class="d-block text-muted">Wycena indywidualna</small>
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="transport" id="transportCheck" value="TAK">
                            <label class="form-check-label" for="transportCheck">
                                <strong>Transport</strong>
                                <small class="d-block text-muted">Wycena indywidualna</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Twoje Dane</h5>
                        <div class="mb-3">
                            <input type="text" name="klient_nazwa" class="form-control" placeholder="Imię i Nazwisko / Firma*" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="klient_email" class="form-control" placeholder="E-mail*" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="klient_nip" class="form-control" placeholder="NIP">
                        </div>
                        <div class="mb-3">
                            <input type="tel" name="klient_telefon" class="form-control" placeholder="Telefon*" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="uwagi" class="form-control" rows="3" placeholder="Dodatkowe uwagi (adres dostawy, termin)"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            📧 Wyślij Zapytanie Ofertowe
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Przekazanie danych JSON z PHP do JavaScript -->
<script>
    const accessoriesData = <?php echo $accessoriesJson; ?>;
    let currentCategoryFilter = '';

    // KROK 1 -> KROK 2 (Kategoria -> Lista Silosów)
    function selectCategory(keyword, title) {
        currentCategoryFilter = (keyword || '').toLowerCase();
        document.getElementById('dynamicCategoryTitle').innerText = title;

        document.getElementById('step-category').classList.add('d-none');
        document.getElementById('step-products').classList.remove('d-none');
        document.getElementById('step-accessories').classList.add('d-none');

        const inp = document.getElementById('searchProductsInput');
        if (inp) inp.value = '';

        applyFilters();
        updateTotals();
    }

    // KROK 2 -> KROK 1 (Powrót do Kategorii)
    function resetCategory() {
        document.getElementById('step-products').classList.add('d-none');
        document.getElementById('step-category').classList.remove('d-none');
        document.getElementById('step-accessories').classList.add('d-none');
        clearSelectedSilo();
    }

    // FILTROWANIE TABELI SILOSÓW
    function applyFilters() {
        const inp = document.getElementById('searchProductsInput');
        const searchTerm = (inp ? inp.value : '').toLowerCase();

        document.querySelectorAll('.product-row').forEach(row => {
            const text = row.cells[1].textContent.toLowerCase(); // nazwa + kod
            const matchesCategory = currentCategoryFilter ? text.includes(currentCategoryFilter) : true;
            const matchesSearch = searchTerm ? text.includes(searchTerm) : true;
            row.style.display = (matchesCategory && matchesSearch) ? '' : 'none';
        });
    }

    // KROK 2 -> KROK 3 (Wybór pojedynczego Silosa i Akcesoria)
    function selectSiloModel(btn, kod, nazwa, cena) {
        // Zapisz w ukrytych polach
        document.getElementById('selectedSiloCode').value = kod;
        document.getElementById('selectedSiloName').value = nazwa;
        document.getElementById('selectedSiloPrice').value = cena;
        document.getElementById('siloPackageQty').value = 1;

        // Aktualizuj etykiety
        document.getElementById('selectedSiloLabel').innerText = nazwa + " (" + kod + ")";
        document.getElementById('selectedSiloPriceLabel').innerText = "Cena netto: " + cena.toLocaleString('pl-PL', {minimumFractionDigits: 2}) + " zł";

        // Budowanie tabeli akcesoriów
        const tbody = document.getElementById('accessoriesTableBody');
        tbody.innerHTML = '';

        // Szukamy akcesoriów po kodzie (może zawierać się w nazwie)
        // Jeśli nie ma dokładnego kodu w CSV, na razie pokażemy pustą tabelę
        const accList = accessoriesData[kod] || [];

        if (accList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Brak zdefiniowanych akcesoriów dla tego modelu lub kod w systemie nie zgadza się z plikiem.</td></tr>';
        } else {
            accList.forEach((acc, index) => {
                let actionHtml = '';

                if (acc.type === 'S-STANDARD') {
                    actionHtml = '<span class="badge bg-success">W Standardzie</span>';
                } else {
                    // Tutaj można dodać pobieranie ceny akcesorium z pliku produkty.csv,
                    // ale zrobimy input na ilość per pakiet.
                    actionHtml = `<input type="number" name="akcesoria[${acc.name}]" class="form-control w-75 mx-auto acc-qty-input" value="0" min="0" data-acc-code="${acc.type}">`;
                }

                tbody.innerHTML += `
                    <tr>
                        <td><strong>${acc.name}</strong></td>
                        <td><small class="text-muted">${acc.type}</small></td>
                        <td class="text-center">${actionHtml}</td>
                    </tr>
                `;
            });
        }

        // Ukryj tabelę silosów, pokaż akcesoria
        document.getElementById('step-products').classList.add('d-none');
        document.getElementById('step-accessories').classList.remove('d-none');

        updateTotals();
    }

    // KROK 3 -> KROK 2
    function backToSiloList() {
        document.getElementById('step-accessories').classList.add('d-none');
        document.getElementById('step-products').classList.remove('d-none');
        clearSelectedSilo();
    }

    function clearSelectedSilo() {
        document.getElementById('selectedSiloCode').value = '';
        document.getElementById('selectedSiloName').value = '';
        document.getElementById('selectedSiloPrice').value = '0';
        updateTotals();
    }

    // SUMOWANIE
    function updateTotals() {
        let total = 0;

        // Bierzemy cenę bazy (Silosa)
        const basePrice = parseFloat(document.getElementById('selectedSiloPrice').value) || 0;

        // Ilość "Kompletów"
        const packageQtyInput = document.getElementById('siloPackageQty');
        const packageQty = packageQtyInput ? (parseInt(packageQtyInput.value, 10) || 0) : 0;

        // Jeśli wybrano silos, dodajemy do sumy: (Cena_Bazy + Cena_Akcesoriów) * packageQty
        if (basePrice > 0) {
            let accPriceSum = 0;
            // Tutaj w przyszłości można dołożyć ściąganie cen akcesoriów.
            // Na razie mnożymy tylko Silos przez ilość pakietów.
            total = (basePrice + accPriceSum) * packageQty;
        }

        const montaz = document.getElementById('montazCheck')?.checked ? ' (do wyceny)' : '';
        const transport = document.getElementById('transportCheck')?.checked ? ' (do wyceny)' : '';

        document.getElementById('productTotal').textContent = total.toLocaleString('pl-PL', {minimumFractionDigits: 2}) + ' zł';
        document.getElementById('montazTotal').textContent = montaz || '0,00 zł';
        document.getElementById('transportTotal').textContent = transport || '0,00 zł';
        document.getElementById('totalValue').textContent = total.toLocaleString('pl-PL', {minimumFractionDigits: 2}) + ' zł' +
            (montaz || transport ? ' + usługi' : '');
    }

    // Event Listeners (Wyszukiwarka krok 2)
    document.addEventListener('keyup', (e) => {
        if (e.target.id === 'searchProductsInput') applyFilters();
    });

</script>
</body>
</html>
