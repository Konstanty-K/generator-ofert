# 🚜 Silosy Konsil - Inteligentny Konfigurator Ofert (CPQ System)

Profesjonalne narzędzie typu **Configure, Price, Quote (CPQ)** zaprojektowane dla branży agro, umożliwiające błyskawiczną konfigurację silosów zbożowych i paszowych, wycenę osprzętu oraz automatyczne generowanie ofert PDF.

System jest w pełni zintegrowany z plikami eksportowymi z oprogramowania ERP, działając jako "tłumacz" między techniczną bazą danych a przyjaznym interfejsem dla rolnika.

## 🚀 Kluczowe Funkcjonalności i Architektura

- **Dynamiczny Silnik Logiczny (CSV Engine):** System zarządza bazą produktów i relacjami między nimi bezpośrednio z plików CSV, co pozwala na aktualizację cennika bez ingerencji w kod PHP.
- **Autorski Język Znaczników (DSL):** Zaprojektowano unikalny system prefiksów w komórkach arkusza, pozwalający nietechnicznym pracownikom programować logikę sprzedaży:
  - `+` (Merge): Łączenie wielu produktów w jedną pozycję handlową z automatycznym sumowaniem cen.
  - `!` (Exclude): Zarządzanie grupami produktów wzajemnie się wykluczających (np. różne typy napędów lub podłóg).
  - `*` (Multiplier): Automatyczne mnożenie sztuk ukrytych w kodzie produktu (np. `KOD * 15`).
  - `$` (Quote): Zmiana statusu elementu na "wycena na zapytanie" (nie wlicza się do automatycznej sumy).
  - `-` / `@` (Tombstone): Flagi ukrywające techniczne nazwy z systemu ERP na rzecz estetycznych opisów.
- **Separacja Obaw (Dictionary Pattern):** Wdrożenie niezależnego słownika `opisy.csv`, który "w locie" nadpisuje surowe kody z systemu magazynowego przyjaznymi opisami dla klienta, nie psując przy tym logiki liczącej.
- **Wzorzec PRG (Post/Redirect/Get):** Formularz zamówienia zabezpieczony przed podwójnym wysłaniem (Double-Submit Problem) i odświeżaniem strony.
- **Moduł Logistyczny:** Dynamiczne wyliczanie kosztów transportu i montażu bazujące na współczynnikach procentowych oraz twardych stawkach minimalnych za zestaw.
- **Automatyzacja PDF i Email:** Generowanie profesjonalnych zestawień w locie (Dompdf) i wysyłka dwutorowa (klient + biuro) przez PHPMailer.

## 🛠 Stack Techniczny

- **Backend:** PHP 8.x
- **Frontend:** Vanilla JS, HTML5, CSS3 (Bootstrap 5)
- **Baza Danych:** Flat-file database (System plików CSV bazujący na eksporcie z ERP)
- **Biblioteki:** PHPMailer, Dompdf
- **Wzorce Projektowe:** PRG (Post/Redirect/Get), Separation of Concerns

## 🧠 Rozwiązania Inżynierskie

Głównym wyzwaniem projektu było stworzenie elastycznego silnika, który pozwoli nietechnicznemu personelowi na zarządzanie skomplikowaną logiką sprzedaży (zależności między akcesoriami, minimalne marże transportowe). Rozwiązanie oparte na inżynierii wyrażeń regularnych (Regex) i własnych prefiksach pozwoliło na pełną separację logiki biznesowej od warstwy prezentacji, zachowując przy tym zerowy czas kompilacji (Hot Reload poprzez podmianę pliku CSV).

## 📈 Cel Biznesowy
Optymalizacja procesu ofertowania poprzez automatyzację powtarzalnych obliczeń. System skraca czas generowania gotowej, spersonalizowanej oferty z kilkunastu minut (proces manualny) do kilkudziesięciu sekund, przy jednoczesnej eliminacji błędów wynikających z ręcznego wprowadzania danych o osprzęcie.

---
**Autor:** inż. arch. Konstanty Kaszubski  
**Status Projektu:** Zakończony / Produkcyjny (v1.0.0)

## 🏁 Etapy Wdrożenia
- [x] Implementacja silnika logicznego (Merge/Exclude/Multiply/Quote)
- [x] System generowania PDF i powiadomień Email
- [x] Słownik translacji nazw marketingowych (ERP -> Web)
- [x] Logika biznesowa kosztów minimalnych i zabezpieczenie PRG
- [x] Deployment na serwer produkcyjny klienta
- [x] Testy UAT i oficjalne wdrożenie (Wersja 1.0.0)