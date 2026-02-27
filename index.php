<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silosy Konsil - Oferta Handlowa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* DEFINICJA KOLORYSTYKI KONSIL */
        :root {
            --main-dark: #0b2239;
            --accent-red: #e30613;
            --bg-light: #f4f7f6;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* NAGŁÓWEK STRONY */
        .konsil-header {
            background-color: var(--main-dark);
            color: white;
            padding: 25px 0;
            border-bottom: 5px solid var(--accent-red);
            margin-bottom: 40px;
        }

        /* STYLIZACJA TABELI */
        .table-dark { background-color: var(--main-dark) !important; border: none; }
        .table thead th {
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            padding: 15px;
        }
        .product-row { background-color: white; transition: 0.2s; }
        .product-row:hover { background-color: #fcfcfc; }

        .price-value { font-weight: 700; color: var(--main-dark); }
        .qty-input {
            border: 2px solid #ddd;
            font-weight: bold;
            text-align: center;
            border-radius: 0;
        }
        .qty-input:focus { border-color: var(--main-dark); box-shadow: none; }

        /* TWOJA DZIAŁAJĄCA SUMA W NOWYM WYDANIU */
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

        /* FORMULARZ I PRZYCISKI */
        .card-custom { border: none; border-radius: 0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
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
        .btn-konsil:hover { background-color: #333; color: white; transform: translateY(-2px); }

        #searchInput {
            border-radius: 0;
            border: 2px solid #ddd;
            padding: 15px;
        }
        #searchInput:focus { border-color: var(--main-dark); box-shadow: none; }

        .img-container img {
            border: 1px solid #eee;
            padding: 2px;
            background: white;
        }
    </style>
</head>
<body>

<header class="konsil-header shadow">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h2 m-0 fw-bold">KONSIL</h1>
            <p class="m-0 small text-uppercase opacity-75">Przedsiębiorstwo Obsługi Rolnictwa</p>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="fw-bold">📞 52 385-78-59</div>
            <div class="small">📧 silosy@konsil.pl</div>
        </div>
    </div>
</header>

<div class="container">
    <div class="mb-4">
        <label class="form-label fw-bold text-muted small uppercase">Szybkie wyszukiwanie produktu:</label>
        <input type="text" id="searchInput" class="form-control form-control-lg shadow-sm"
               placeholder="Wpisz nazwę silosu, części lub kod produktu...">
    </div>

    <form action="wyslij.php" method="POST">
        <div class="card card-custom p-4 shadow-sm mb-4">
            <div class="table-responsive">
                <table class="table align-middle" id="productTable">
                    <thead class="table-dark">
                    <tr>
                        <th style="width: 80px;">Foto</th>
                        <th>Produkt i Kod</th>
                        <th>Cena Netto</th>
                        <th>Opis Techniczny</th>
                        <th style="width: 120px;">Ilość</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $file = fopen("produkty.csv", "r");
                    fgetcsv($file); // Pomiń nagłówek CSV

                    while (($data = fgetcsv($file, 2000, ",")) !== FALSE) {
                        $kod   = $data[0];
                        $nazwa = $data[1];
                        $cena  = (float)str_replace(',', '.', $data[39]);
                        $opis  = $data[20];

                        // TWOJA LOGIKA SPRAWDZANIA ROZSZERZEŃ
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
                                <td class='img-container'><img src='$sciezka_foto' width='60' height='60' style='object-fit: contain;'></td>
                                <td>
                                    <div class='fw-bold'>$nazwa</div>
                                    <code class='text-muted small'>$kod</code>
                                </td>
                                <td class='price-value text-nowrap' data-price='$cena'>" . number_format($cena, 2, ',', ' ') . " zł</td>
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

            <div class="sticky-bottom-custom d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Wstępna wartość zapytania:</span>
                    <div id="totalValue" class="h2 mb-0 fw-bold" style="color: var(--main-dark);">0,00 zł</div>
                </div>
                <div class="text-end">
                    <span class="badge bg-dark px-3 py-2">CENY NETTO</span>
                </div>
            </div>
        </div>

        <div class="card card-custom p-4 shadow-sm mb-5">
            <h3 class="fw-bold mb-4" style="color: var(--main-dark); border-left: 5px solid var(--accent-red); padding-left: 15px;">
                Dane do oferty
            </h3>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Imię i Nazwisko / Nazwa Firmy</label>
                    <input type="text" name="klient_nazwa" class="form-control" placeholder="np. Jan Kowalski" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Adres E-mail</label>
                    <input type="email" name="klient_email" class="form-control" placeholder="np. kontakt@rolnik.pl" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">NIP (pozostaw puste dla os. prywatnej)</label>
                    <input type="text" name="klient_nip" class="form-control" placeholder="000-000-00-00">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Numer telefonu</label>
                    <input type="tel" name="klient_telefon" class="form-control" placeholder="+48 000 000 000" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-bold">Dodatkowe informacje / Adres dostawy</label>
                    <textarea name="uwagi" class="form-control" rows="3" placeholder="Wpisz tutaj dodatkowe pytania lub miejsce montażu silosów..."></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-konsil btn-lg mt-5 w-100 shadow">
                Generuj zapytanie ofertowe
            </button>
        </div>
    </form>
</div>

<script>
    // FUNKCJA WYSZUKIWANIA
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('.product-row');
        rows.forEach(row => {
            let text = row.cells[1].textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });

    // FUNKCJA SUMOWANIA CEN
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