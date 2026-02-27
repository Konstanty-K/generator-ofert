<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Silosy Konsil - Oferta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4 text-center">Nasza Oferta Handlowa</h1>

    <div class="mb-4">
        <input type="text" id="searchInput" class="form-control form-control-lg shadow-sm"
               placeholder="🔎 Szukaj produktu po nazwie lub kodzie...">
    </div>

    <form action="wyslij.php" method="POST" class="card shadow p-4">
        <table class="table align-middle" id="productTable">
            <thead class="table-dark">
            <tr>
                <th style="width: 80px;"> </th>
                <th>Produkt</th>
                <th>Cena</th>
                <th>Opis</th>
                <th style="width: 100px;">Ilość</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $file = fopen("produkty.csv", "r");
            fgetcsv($file); // Pomiń nagłówek CSV

            while (($data = fgetcsv($file, 2000, ",")) !== FALSE) {
                $kod   = $data[0];
                $nazwa = $data[1];
                $cena  = (float)str_replace(',', '.', $data[39]); // Konwersja na liczbę pod JS
                $opis  = $data[20];

//                $sciezka_foto = "img/{$kod}.jpg";
//                if (!file_exists($sciezka_foto)) { $sciezka_foto = "img/brak_foto.jpg"; }
                $sciezka_foto = "img/" . $kod . ".webp";
                $rozszerzenia = ['webp', 'jpg', 'jpeg', 'png', 'avif'];
                $sciezka_foto = '';
                foreach ($rozszerzenia as $ext) {
                    $test_path = "img/" . $kod . "." . $ext;
                    if (file_exists($test_path)) {
                        $sciezka_foto = $test_path;
                        break;
                    }
                }
                if (!$sciezka_foto) $sciezka_foto = "img/brakfoto.webp";  // lub jpg/png


                echo "<tr class='product-row'>
                            <td><img src='$sciezka_foto' width='50' class='rounded border'></td>
                            <td>
                                <strong>$nazwa</strong><br>
                                <small class='text-muted'>Kod: $kod</small>
                            </td>
                            <td class='price-value' data-price='$cena'>" . number_format($cena, 2, ',', ' ') . " zł</td>
                            <td class='small'>$opis</td>
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

        <div class="alert alert-info d-flex justify-content-between align-items-center mt-3 shadow-sm sticky-bottom">
            <span class="h5 mb-0">Razem do zapłaty:</span>
            <span id="totalValue" class="h4 mb-0 fw-bold text-primary">0,00 zł</span>
        </div>

        <hr>
        <h3>Dane do zamówienia</h3>
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" name="klient_nazwa" class="form-control" placeholder="Imię i Nazwisko / Firma" required>
            </div>
            <div class="col-md-6">
                <input type="email" name="klient_email" class="form-control" placeholder="Twój E-mail" required>
            </div>

            <div class="col-md-6">
                <input type="text" name="klient_nip" class="form-control" placeholder="NIP" required>
            </div>
            <div class="col-md-6">
                <input type="tel" name="klient_telefon" class="form-control" placeholder="Numer telefonu" required>
            </div>

            <div class="col-12">
                <textarea name="uwagi" class="form-control" placeholder="Dodatkowe uwagi (np. adres dostawy)"></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg mt-4 w-100">Wyślij zamówienie</button>
    </form>
</div>

<script>
    // FUNKCJA WYSZUKIWANIA
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('.product-row');

        rows.forEach(row => {
            let text = row.cells[1].textContent.toLowerCase(); // Szukaj w nazwie i kodzie
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

    // Nasłuchiwanie zmian w polach ilości
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
</script>

</body>
</html>