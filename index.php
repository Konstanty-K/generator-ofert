<?php
$test = fgetcsv(fopen("produkty.csv", "r"), 2000, ",");
echo "<!-- Kolumn: " . count($test) . " --!>";
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

        /* Style kart */
        .cursor-pointer { cursor: pointer; }
        .category-card { transition: all 0.3s ease; border: 2px solid transparent !important; }
        .category-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(11, 34, 57, 0.15) !important; border-color: var(--accent-soft) !important; }
        .category-card:hover .btn-outline-primary { background-color: var(--main-navy); color: white !important; }

        /* Animacja Fade In/Out */
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

<!-- HEADER KONSIL NAVY (LOGO LEWO) -->
<header class="konsil-header shadow-sm">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Logo LEWO -->
            <div class="d-flex flex-column align-items-start">
                <img src="konsil_logo_main.png" alt="Konsil Logo" class="header-logo">
                <p class="header-subtext mb-0">PRZEDSIĘBIORSTWO OBSŁUGI ROLNICTWA KONSIL</p>
            </div>

            <!-- Kontakt PRAWO -->
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


    <form action="wyslij.php" method="POST" class="row g-4">
        <!-- Kolumna produktów -->
        <!-- Kolumna lewa (Produkty / Kategorie) -->
        <div class="col-lg-8">

            <!-- KROK 1: KARTY KATEGORII -->
            <div id="step-category" class="fade-element">
                <h3 class="mb-4 text-center" style="color: var(--main-navy);">Wybierz typ silosu</h3>
                <div class="row g-4 mb-4">
                    <!-- Karta: Płaskodenne (szuka słowa BIN) -->
                    <div class="col-md-6">
                        <div class="card h-100 category-card shadow-sm cursor-pointer border-0" onclick="selectCategory('BIN', 'Silosy Płaskodenne')">
                            <img src="img/plaskodenne.webp" class="card-img-top" alt="Płaskodenne" style="height: 250px; object-fit: cover; background:#e9ecef;">
                            <div class="card-body text-center p-4">
                                <h4 class="card-title fw-bold" style="color: var(--main-navy);">Silosy Płaskodenne</h4>
                                <p class="text-muted mb-0">Wybierz, aby zobaczyć systemy typu BIN i dedykowane akcesoria.</p>
                                <button type="button" class="btn btn-outline-primary w-100 mt-3" style="border-color: var(--main-navy); color: var(--main-navy);">
                                    Przejdź do produktów →
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Karta: Lejowe (szuka słowa KONSIL) -->
                    <div class="col-md-6">
                        <div class="card h-100 category-card shadow-sm cursor-pointer border-0" onclick="selectCategory('KONSIL', 'Silosy Lejowe')">
                            <img src="img/lejowe.webp" class="card-img-top" alt="Lejowe" style="height: 250px; object-fit: cover; background:#e9ecef;">
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

            <!-- KROK 2: TABELA PRODUKTÓW (Domyślnie ukryta) -->
            <div id="step-products" class="d-none fade-element">
                <div class="card shadow p-4">

                    <!-- Nagłówek z przyciskiem powrotu -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <div>
                            <h3 class="mb-0" id="dynamicCategoryTitle" style="color: var(--main-navy);">Wybierz Produkty</h3>
                            <small class="text-muted">Dodaj wybrane pozycje do zamówienia</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetCategory()">
                            ← Zmień typ silosu
                        </button>
                    </div>

                    <!-- Wyszukiwarka -->
                    <div class="mb-4">
                        <input type="text" id="searchProductsInput" class="form-control form-control-lg bg-light" placeholder="🔍 Szukaj produktu w tej kategorii...">

                    </div>

                    <!-- Tabela -->
                    <div class="table-responsive">
                        <table class="table align-middle" id="productTable">
                            <thead class="table-dark" style="background-color: var(--main-navy) !important;">
                            <tr>
                                <th style="width: 80px;">Zdjęcie</th>
                                <th>Produkt</th>
                                <th class="text-end">Cena netto</th>
                                <th>Opis</th>
                                <th style="width: 100px;">Ilość</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- PĘTLA PHP Z TWOJEGO KODU - NIC NIE ZMIENIAMY -->
                            <?php
                            $file = fopen("produkty.csv", "r");
                            if ($file) {
                                fgetcsv($file, 2000, ",");
                                while (($data = fgetcsv($file, 2000, ",")) !== FALSE) {
                                    $kod = $data[0] ?? '';
                                    $nazwa = $data[1] ?? 'Brak nazwy';
                                    $cena_str = isset($data[35]) ? $data[35] : (isset($data[2]) ? $data[2] : '0');
                                    $cena = floatval(str_replace(",", ".", $cena_str));
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
                                        <td><strong>$nazwa</strong><br><small class='text-muted'>Kod: $kod</small></td>
                                        <td class='price-value text-end fw-bold' data-price='$cena'>" . number_format($cena, 2, ',', ' ') . " zł</td>
                                        <td class='small text-muted'>$opis</td>
                                        <td><input type='number' name='zamowienie[$nazwa]' class='form-control qty-input' value='0' min='0' data-price='$cena'></td>
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
        </div>
        <!-- Koniec kolumny lewej -->

        <!-- Prawa kolumna zostaje nietknięta poniżej... -->

        <!-- Kolumna podsumowania -->
        <div class="col-lg-4">
            <div class="sticky-summary">
                <!-- Podsumowanie -->
                <div class="card shadow mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Podsumowanie</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Produkty:</span>
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

                <!-- Montaż i Transport -->
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

                <!-- Dane Klienta -->
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Twoje Dane</h5>
                        <div class="mb-3">
                            <input type="text" name="klient_nazwa" class="form-control"
                                   placeholder="Imię i Nazwisko / Firma*" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" name="klient_email" class="form-control"
                                   placeholder="E-mail*" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="klient_nip" class="form-control"
                                   placeholder="NIP">
                        </div>
                        <div class="mb-3">
                            <input type="tel" name="klient_telefon" class="form-control"
                                   placeholder="Telefon*" required>
                        </div>
                        <div class="mb-3">
                                <textarea name="uwagi" class="form-control" rows="3"
                                          placeholder="Dodatkowe uwagi (adres dostawy, termin)"></textarea>
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

<script>
    let currentCategoryFilter = '';   // 'bin' albo 'konsil'

    // KROK 1 -> KROK 2
    function selectCategory(keyword, title) {
        currentCategoryFilter = (keyword || '').toLowerCase();

        document.getElementById('dynamicCategoryTitle').innerText = title;
        document.getElementById('step-category').classList.add('d-none');
        document.getElementById('step-products').classList.remove('d-none');

        const inp = document.getElementById('searchProductsInput');
        if (inp) inp.value = '';

        applyFilters();
        updateTotals(); // przelicz po zmianie widoczności
    }

    // KROK 2 -> KROK 1
    function resetCategory() {
        document.getElementById('step-products').classList.add('d-none');
        document.getElementById('step-category').classList.remove('d-none');

        // Opcjonalnie: wyczyść wyszukiwarkę i pokaż wszystkie wiersze
        const inp = document.getElementById('searchProductsInput');
        if (inp) inp.value = '';
        document.querySelectorAll('.product-row').forEach(row => row.style.display = '');
        updateTotals();
    }

    // Filtrowanie (kategoria + wyszukiwarka)
    function applyFilters() {
        const inp = document.getElementById('searchProductsInput');
        const searchTerm = (inp ? inp.value : '').toLowerCase();

        document.querySelectorAll('.product-row').forEach(row => {
            const text = row.cells[1].textContent.toLowerCase(); // nazwa + kod
            const matchesCategory = currentCategoryFilter ? text.includes(currentCategoryFilter) : true;
            const matchesSearch = searchTerm ? text.includes(searchTerm) : true;
            row.style.display = (matchesCategory && matchesSearch) ? '' : 'none';
        });

        updateTotals(); // suma tylko z widocznych
    }

    // SUMOWANIE – liczy TYLKO widoczne wiersze
    function updateTotals() {
        let productTotal = 0;

        document.querySelectorAll('.product-row').forEach(row => {
            if (row.style.display === 'none') return; // ignoruj ukryte po filtrze

            const input = row.querySelector('.qty-input');
            const qty = parseInt(input?.value, 10) || 0;
            const price = parseFloat(input?.getAttribute('data-price')) || 0;
            productTotal += qty * price;
        });

        const montazChecked = document.getElementById('montazCheck')?.checked;
        const transportChecked = document.getElementById('transportCheck')?.checked;

        document.getElementById('productTotal').textContent =
            productTotal.toFixed(2).replace('.', ',') + ' zł';

        document.getElementById('montazTotal').textContent =
            montazChecked ? ' (do wyceny)' : '0,00 zł';

        document.getElementById('transportTotal').textContent =
            transportChecked ? ' (do wyceny)' : '0,00 zł';

        document.getElementById('totalValue').textContent =
            productTotal.toFixed(2).replace('.', ',') + ' zł' + ((montazChecked || transportChecked) ? ' + usługi' : '');
    }

    // Eventy – delegacja (działa nawet po “przełączaniu kroków”)
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('qty-input')) updateTotals();
    });
    document.addEventListener('change', (e) => {
        if (e.target.id === 'montazCheck' || e.target.id === 'transportCheck') updateTotals();
    });

    // Wyszukiwarka w tabeli
    document.addEventListener('keyup', (e) => {
        if (e.target.id === 'searchProductsInput') applyFilters();
    });
</script>


</body>
</html>
