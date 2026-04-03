<?php
// Plik: szablon_maila.php

function pobierzTrescMaila($nazwaKlienta, $nazwaSilosu) {
    // Zmienna $nazwaKlienta nie jest obecnie używana w szablonie (zgodnie ze wzorem),
    // ale zostawiamy ją w parametrach funkcji, żeby nie popsuć wywołania w wyslij.php

    return "
    <div style='font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333; max-width: 650px;'>
        <p style='margin-top: 0;'>Dzień dobry,</p>
        <p>serdecznie dziękujemy za zainteresowanie.</p>
        <p>W nawiązaniu do przygotowanej konfiguracji silosu <strong>$nazwaSilosu</strong>, przesyłamy szczegółowe zestawienie w załączniku PDF.</p>
        
        <p style='margin-top: 30px;'>Pozdrawiamy,<br>Zespół</p>
        <p style='font-weight: bold; margin-bottom: 40px;'>KONSIL</p>

        <div style='background-color: #eaf4fc; padding: 15px 20px; border-left: 4px solid #0b2239; margin: 30px 0;'>
            JEŚLI DO MONTAŻU LUB SERWISU POTRZEBNY BĘDZIE DŹWIG, TO ZAPEWNIA GO INWESTOR NA SWÓJ KOSZT.<br>
            DODATKOWO KOSZTY NOCLEGU I WYŻYWIENIA DLA EKIPY PO STRONIE INWESTORA.
        </div>

        <p>
            <strong>NASZA STRONA:</strong><br>
            <a href='http://www.konsil.pl' style='color: #0b2239; text-decoration: none; font-weight: bold;'>www.konsil.pl</a><br>
            <strong>TEL:</strong> <a href='tel:+48523857859' style='color: #333; text-decoration: none;'>52 385 78 59</a> | 
            <a href='tel:+48573076159' style='color: #333; text-decoration: none;'>+48 573 076 159</a>
        </p>

        <div style='background-color: #eaf4fc; padding: 15px 20px; margin: 20px 0;'>
            Zapraszamy do odwiedzenia naszej strony na <strong>Facebooku</strong>, gdzie zapoznacie się Państwo z najnowszą ofertą, wydarzeniami i ciekawostkami:<br>
            <a href='https://www.facebook.com/profile.php?id=61576214794816' style='color: #3b5998; font-weight: bold; text-decoration: underline;'>Odwiedź nasz profil na Facebooku</a>
        </div>

        <br>

        <div style='font-size: 11px; color: #777; line-height: 1.4; margin-top: 20px;'>
            <strong>Klauzula informacyjna (RODO):</strong><br>
            Administratorem danych jest: P.O.R. &quot;KONSIL&quot; z siedzibą w Ślesinie 89-121, Nakielska 10, NIP: 953-101-65-82. 
            Pani/Pana dane zbierane są w celu nawiązania kontaktu, prowadzenia rozmów handlowych, realizacji zamówień. 
            Dane pozyskiwane są jedynie przez dobrowolne podanie przez Panią/Pana. 
            Ma Pani/Pan prawo dostępu do swoich danych osobowych, prawo do żądania poprawienia, usunięcia lub ograniczenia przetwarzania danych, prawo do przenoszenia danych.
        </div>
    </div>
    ";
}