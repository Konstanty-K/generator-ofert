<?php
// Plik: szablon_maila.php

function pobierzTrescMaila($nazwaKlienta, $nazwaSilosu) {
    return "
    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 650px;'>
        <p>Dzień dobry,</p>
        <p>W nawiązaniu do przygotowanej konfiguracji silosu <strong>$nazwaSilosu</strong>, przesyłamy szczegółowe zestawienie w załączniku PDF.</p>
        
        <p style='margin-top: 20px;'>Pozdrawiamy,<br><strong>Zespół KONSIL</strong></p>
        
        <p style='color: #d9534f; font-weight: bold; text-transform: uppercase; border: 1px solid #d9534f; padding: 10px; display: inline-block;'>
            ZAMÓWIENIE I ZLECENIE PODPISANE PROSZĘ ODESŁAĆ.
        </p>

        <div style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0b2239; margin: 20px 0; font-size: 0.95rem;'>
            CENA MONTAŻU DOTYCZY TYLKO WSKAZANEGO MIESIĄCA - W INNYM TERMINIE NIE ZAPEWNIAMY MONTAŻU. <br><br>
            JEŚLI DO MONTAŻU LUB SERWISU POTRZEBNY BĘDZIE DŹWIG, TO ZAPEWNIA GO INWESTOR NA SWÓJ KOSZT. <br>
            DODATKOWO KOSZTY NOCLEGU I WYŻYWIENIA DLA EKIPY PO STRONIE INWESTORA.
        </div>

        <p>
            <strong>NASZA STRONA:</strong><br>
            <a href='http://www.konsil.pl/' style='color: #0b2239; text-decoration: none; font-weight: bold;'>www.konsil.pl</a><br>
            <strong>TEL:</strong> <a href='tel:+48523857859' style='color: #333; text-decoration: none;'>52 385 78 59</a> | 
            <a href='tel:+48573076159' style='color: #333; text-decoration: none;'>+48 573 076 159</a>
        </p>

        <p style='background: #e7f3ff; padding: 10px;'>
            Zapraszamy do odwiedzenia naszej strony na <strong>Facebooku</strong>, gdzie zapoznacie się Państwo z najnowszą ofertą, wydarzeniami i ciekawostkami:<br>
            <a href='https://www.facebook.com/profile.php?id=61576214794816' style='color: #3b5998; font-weight: bold;'>Odwiedź nasz profil na Facebooku</a>
        </p>

        <hr style='border: 0; border-top: 1px solid #eee; margin: 30px 0;'>

        <div style='font-size: 0.75rem; color: #777; line-height: 1.4;'>
            <strong>Klauzula informacyjna (RODO):</strong><br>
            Administratorem danych jest: P.O.R. \"KONSIL\" z siedzibą w Ślesinie 89-121, Nakielska 10, NIP: 953-101-65-82. 
            Pani/Pana dane zbierane są w celu nawiązania kontaktu, prowadzenia rozmów handlowych, realizacji zamówień. 
            Dane pozyskiwane są jedynie przez dobrowolne podanie przez Panią/Pana. 
            Ma Pani/Pan prawo dostępu do swoich danych osobowych, prawo do żądania poprawienia, usunięcia lub ograniczenia przetwarzania danych, prawo do przenoszenia danych.
        </div>
    </div>
    ";
}