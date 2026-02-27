<?php
echo "<pre>"; print_r($_POST); echo "</pre>";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "TWÓJ_EMAIL@DOMENA.PL"; // <--- WPISZ TUTAJ SWÓJ ADRES
    $subject = "Nowe zamówienie ze strony";
    
    $klient = $_POST['klient_nazwa'];
    $email = $_POST['klient_email'];
    $uwagi = $_POST['uwagi'];
    $produkty = $_POST['zamowienie'];

    $message = "Nowe zamówienie od: $klient ($email)\n\n";
    $message .= "ZAMÓWIONE PRODUKTY:\n";
    $message .= "--------------------------\n";

    foreach ($produkty as $nazwa => $ilosc) {
        if ($ilosc > 0) {
            $message .= "- $nazwa: $ilosc szt.\n";
        }
    }

    $message .= "--------------------------\n";
    $message .= "Uwagi: $uwagi\n";

    $headers = "From: sklep@twojadomena.pl";

    if(mail($to, $subject, $message, $headers)) {
        echo "<h1>Dziękujemy! Zamówienie zostało wysłane.</h1><a href='index.php'>Wróć do sklepu</a>";
    } else {
        echo "Błąd podczas wysyłki. Skontaktuj się z administratorem.";
    }
}
?>