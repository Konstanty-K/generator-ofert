<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silosy Konsil - Oferta Handlowa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* NOWA KOLORYSTYKA NAVY BLUE */
        :root {
            --main-dark: #0b2239; /* Twój wybrany kolor */
            --accent-red: #e30613;
            --bg-light: #f4f7f6;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Roboto, sans-serif;
            color: #333;
        }

        /* NAGŁÓWEK Z LOGO */
        .konsil-header {
            background-color: var(--main-dark);
            color: white;
            padding: 20px 0;
            border-bottom: 5px solid var(--accent-red);
            margin-bottom: 40px;
        }
        .header-logo {
            max-height: 70px; /* Dopasuj wysokość logo */
            width: auto;
        }

        /* TABELA I STYLIZACJA */
        .table-dark { background-color: var(--main-dark) !important; border: none; }
        .table thead th {
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            padding: 15px;
        }
        .product-row { background-color: white; transition: 0.2s; }
        .product-row:hover { background-color: #f8f9fa; }

        .price-value { font-weight: 700; color: var(--main-dark); }
        .qty-input {
            border: 2px solid #ddd;
            font-weight: bold;
            text-align: center;
            border-radius: 0;
        }
        .qty-input:focus { border-color: var(--main-dark); box-shadow: none; }

        /* PŁYWAJĄCE PODSUMOWANIE */
        .sticky-bottom-custom {
            position: sticky;
            bottom: 15px;
            background-color: white;
            border: 2px solid var(--main-dark);
            border-left: 10px solid var(--main-dark);
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            z-index: 1020;
            padding: 20px;
            margin-bottom: 30px;
        }

        /* PRZYCISKI */
        .btn-konsil {
            background-color: var(--main-dark);
            color: white;
            border: none;
            border-radius: 0;
            padding: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn-konsil:hover { background-color: #1a3a5a; color: white; transform: translateY(-2px); }

        #searchInput {
            border-radius: 0;
            border: 2px solid #ddd;
            padding: 15px;
        }
        #searchInput:focus { border-color: var(--main-dark); box-shadow: none; }
    </style>
</head>
<body>

<header class="konsil-header shadow">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <img src="konsil_logo_main.png" alt="Konsil Logo" class="header-logo">
        </div>
        <div class="text-end d-none d-md-block">
            <div class="fw-bold">📞 52 385-78-59</div>
            <div class="small opacity-75">📧 silosy@konsil.pl</div>
        </div>
    </div>
</header>

<div class="container pb-5">
    <div class="mb-4">
        <label class="form-label fw-bold text-muted small">WYSZUKAJ PRODUKT:</label>
        <input type="text" id="searchInput" class="form-control form-control-lg shadow-sm"
               placeholder="Wyszukaj silos lub wyposażenie...">
    </div>

    <form action="wyslij.php" method="POST">
        <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 0;">
            <div class="table-responsive">
                <table class="table align-middle" id="productTable">
                    <thead class="table-dark">
                    <tr>
                        <th style="width: 80px;">Foto</th>
                        <th>Produkt i Kod</th>
                        <th class="text-end">Cena netto</th>
                        <th>Opis</th>
                        <th style="width: 120px;">Ilość</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $file = fopen("produkty.csv", "r");
                    fgetcsv($file);

                    while (($data = fgetcsv($file, 2000, ",")) !== FALSE) {
                        $kod   = $data[0];
                        $nazwa = $data[1];
                        $cena  = (float)str_replace(',', '.', $data[39]);
                        $opis  = $data[20];

                        // TWOJA SPRAWDZONA LOGIKA ZDJĘĆ
                        $rozszerzenia = ['webp', 'jpg', 'jpeg', 'png', 'avif'];
                        $sciezka_foto = '';
                        foreach ($rozszerzenia as $ext) {
                            $test_path = "img/" . $kod . "." . $ext;
                            if (file_exists($test_path)) {
                                $sciezka_foto = $test_path;
                                break;
                            }
                        }
                        if (!$sciezka_foto) $sciezka_foto = "img/brakfoto.webp";

                        echo "<tr class='product-row'>
                                <td><img src='$sciezka_foto' width='60' height='60' style='object-fit: contain;' class='border rounded bg-white'></td>
                                <td>
                                    <div class='fw-bold'>$nazwa</div>
                                    <small class='text-muted'>$kod</small>
                                </td>
                                <td class='price-value text-end text-nowrap' data-price='$cena'>" . number_format($cena, 2, ',', ' ') . " zł</td>
                                <td class='small text-muted'>$opis</td>
                                <td>
                                    <input type='number' name='zamowienie[$nazwa]' 
                                           class='form-control qty-input' value='0' min='0'>
                                </td>
                              </tr>";
                    }
                    fclose($file);
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="sticky-bottom-custom d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Wartość Twojej oferty:</span>
                    <div id="totalValue" class="h2 mb-0 fw-bold" style="color: var(--main-dark);">0,00 zł</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-dark px-3 py-2">NETTO</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4 mb-5" style="border-radius: 0;">
            <h3 class="fw-bold mb-4" style="color: var(--main-dark); border-left: 5px solid var(--accent-red); padding-left: 15px;">
                Dane zamawiającego
            </h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <input type="text" name="klient_nazwa" class="form-control form-control-lg" placeholder="Imię i Nazwisko / Firma" style="border-radius:0;" required>
                </div>
                <div class="col-md-6">
                    <input type="email" name="klient_email" class="form-control form-control-lg" placeholder="Adres E-mail" style="border-radius:0;" required>
                </div>
                <div class="col-md-6">
                    <input type="text" name="klient_nip" class="form-control form-control-lg" placeholder="NIP (opcjonalnie)" style="border-radius:0;">
                </div>
                <div class="col-md-6">
                    <input type="tel" name="klient_telefon" class="form-control form-control-lg" placeholder="Numer telefonu" style="border-radius:0;" required>
                </div>
                <div class="col-12">
                    <textarea name="uwagi" class="form-control" rows="3" placeholder="Dodatkowe uwagi lub adres montażu silosów..." style="border-radius:0;"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-konsil btn-lg mt-5 w-100 shadow">
                PRZEŚLIJ ZAPYTANIE DO WYCENY
            </button>
        </div>
    </form>
</div>

<script>
    // WYSZUKIWANIE
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('.product-row');
        rows.forEach(row => {
            let text = row.cells[1].textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });

    // SUMOWANIE
    function calculateTotal() {
        let total = 0;
        let rows = document.querySelectorAll('.product-row');
        rows.forEach(row => {
            let price = parseFloat(row.querySelector('.price-value').dataset.price);
            let qty = parseInt(row.querySelector('.qty-input').value) || 0;
            total += price * qty;
        });
        document.getElementById('totalValue').innerText =
            total.toLocaleString('pl-PL', { minimumFractionDigits: 2 }) + " zł";
    }

    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
</script>

</body>
</html>