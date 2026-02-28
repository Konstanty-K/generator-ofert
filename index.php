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

    <!-- Wyszukiwarka -->
    <div class="mb-4">
        <input type="text" id="searchInput" class="form-control form-control-lg shadow-sm"
               placeholder="🔍 Szukaj produktu po nazwie lub kodzie...">
    </div>

    <form action="wyslij.php" method="POST" class="row g-4">
        <!-- Kolumna produktów -->
        <div class="col-lg-8">
            <div class="card shadow p-4">
                <h3 class="mb-3">Wybierz Produkty</h3>
                <table class="table align-middle" id="productTable">
                    <thead class="table-dark">
                    <tr>
                        <th style="width: 80px;">Zdjęcie</th>
                        <th>Produkt</th>
                        <th>Cena netto</th>
                        <th>Opis</th>
                        <th style="width: 100px;">Ilość</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $file = fopen("produkty.csv", "r");
                    if ($file) {
                        fgetcsv($file, 2000, ","); // Nagłówek przecinek Optima

                        while (($data = fgetcsv($file, 2000, ",")) !== FALSE) {
                            // BEZPIECZNIE jak w Twoim kodzie (bez rygorystycznych continue)
                            $kod = $data[0] ?? '';
                            $nazwa = $data[1] ?? 'Brak nazwy';
                            $cena_str = isset($data[35]) ? $data[35] : (isset($data[2]) ? $data[2] : '0'); // Cena kol 35 lub 2
                            $opis = $data[19] ?? '';

                            $cena = floatval(str_replace(",", ".", $cena_str));

                            if (empty($kod) || empty($nazwa)) continue;

                            // Multi-format foto (jak miałeś)
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
    // Wyszukiwarka produktów
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.product-row').forEach(row => {
            const name = row.getAttribute('data-name');
            row.style.display = name.includes(searchTerm) ? '' : 'none';
        });
    });

    // Przeliczanie sum
    function updateTotals() {
        let productTotal = 0;
        document.querySelectorAll('.qty-input').forEach(input => {
            const qty = parseInt(input.value) || 0;
            const price = parseFloat(input.getAttribute('data-price')) || 0;
            productTotal += qty * price;
        });

        const montaz = document.getElementById('montazCheck').checked ? ' (do wyceny)' : '';
        const transport = document.getElementById('transportCheck').checked ? ' (do wyceny)' : '';

        document.getElementById('productTotal').textContent = productTotal.toFixed(2).replace('.', ',') + ' zł';
        document.getElementById('montazTotal').textContent = montaz || '0,00 zł';
        document.getElementById('transportTotal').textContent = transport || '0,00 zł';
        document.getElementById('totalValue').textContent = productTotal.toFixed(2).replace('.', ',') + ' zł' +
            (montaz || transport ? ' + usługi' : '');
    }

    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('input', updateTotals);
    });
    document.getElementById('montazCheck').addEventListener('change', updateTotals);
    document.getElementById('transportCheck').addEventListener('change', updateTotals);
</script>
</body>
</html>
