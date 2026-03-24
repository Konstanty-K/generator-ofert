# 🚜 Silosy Konsil - Inteligentny Konfigurator Ofert (CPQ System)

Profesjonalne narzędzie typu **Configure, Price, Quote (CPQ)** zaprojektowane dla branży agro, umożliwiające błyskawiczną konfigurację silosów zbożowych i paszowych, wycenę osprzętu oraz automatyczne generowanie ofert PDF.

## 🚀 Kluczowe Funkcjonalności

- **Dynamiczne Parsowanie Logiki (CSV Engine):** System zarządza bazą produktów i relacjami między nimi bezpośrednio z plików CSV, co pozwala na aktualizację cennika bez ingerencji w kod.
- **Autorska Logika Relacji:** - `+` (Operator Pakietów): Łączenie wielu produktów w jedną pozycję handlową z sumowaniem cen.
  - `!` (Operator Wykluczeń): Zarządzanie grupami produktów wzajemnie się wykluczających (np. różne typy napędów lub podłóg).
- **Automatyzacja PDF:** Generowanie profesjonalnych zestawień ofertowych przy użyciu biblioteki Dompdf (z uwzględnieniem tonacji pszenicy/paszy, kodów rabatowych i klauzul prawnych).
- **System Powiadomień:** Integracja z PHPMailer do automatycznego przesyłania ofert do klienta oraz powiadomień dla działu handlowego.
- **Interfejs Responsive Design:** UI zbudowany na Bootstrap 5, zoptymalizowany pod kątem pracy rolnika w terenie (urządzenia mobilne).

## 🛠 Stack Techniczny

- **Backend:** PHP 8.x
- **Frontend:** JavaScript (Vanilla JS), HTML5, CSS3 (Bootstrap 5)
- **Dane:** Flat-file database (CSV)
- **Biblioteki:** PHPMailer, Dompdf

## 🧠 Rozwiązania Inżynierskie

Głównym wyzwaniem projektu było stworzenie elastycznego silnika, który pozwoli nietechnicznemu personelowi na zarządzanie skomplikowaną logiką sprzedaży (zależności między akcesoriami). Rozwiązanie oparte na prefiksach operatorów w plikach CSV pozwoliło na pełną separację logiki biznesowej od warstwy prezentacji.

## 📈 Cel Biznesowy
Optymalizacja procesu ofertowania poprzez automatyzację powtarzalnych obliczeń. System ma na celu skrócenie czasu generowania oferty z kilkunastu minut (proces manualny) do kilkunastu sekund, przy jednoczesnej eliminacji błędów wynikających z ręcznego wprowadzania danych o osprzęcie.

---
**Autor:** Konstanty Kaszubski  
## 🛠 Status Projektu: WIP (Work In Progress) / Beta
Projekt jest obecnie w fazie końcowych testów przedwdrożeniowych. 

- [x] Implementacja silnika logicznego (Merge/Exclude)
- [x] System generowania PDF
- [x] Integracja PHPMailer
- [ ] Deployment na serwer produkcyjny (w trakcie)
- [ ] Testy akceptacyjne użytkownika (UAT)
