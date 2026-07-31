# Review: Checkout Idempotency Processing

Branch: `feat/checkout-idempotency-processing`

Review-Basis: `479f90c586921cb452d85e76fcedba09a754f31d`

Stand des Reviews: 2026-07-30

## Findings

### P0: Idempotency greift erst nach `before_process()`

In `checkout_process.php` wird das Zahlungsmodul ausgeführt, bevor der Request
den Checkout mit `claim()` beansprucht.

Ein konkurrierender Request kann daher bereits Nebenwirkungen auslösen, bevor
`claim()` ihn stoppt.

Beispiele:

- PayPal ACDC kann in `before_process()` einen externen PayPal-Auftrag erzeugen.
- WorldPay verändert in `before_process()` Bestellstatus und Bestand.

Der Branch verhindert damit häufig die zweite lokale Bestellung, aber nicht
zwingend doppelte Zahlungs- oder Bestandsaktionen.

Betroffene Stellen:

- `checkout_process.php:88`
- `checkout_process.php:91`
- `includes/modules/payment/paypalacdc.php:246`
- `includes/modules/payment/worldpay_junior.php:359`

### P0: Temporäre Orders sind nicht gegen parallele Finalisierung geschützt

Bei `$creates_tmp_order` oder bestehender `tmp_oID` wird `claim()` vollständig
übersprungen.

Beim Rücksprung wird mit derselben `tmp_oID` trotzdem erneut die komplette
Finalisierung ausgeführt:

- Bestandsänderungen
- Einfügen der Bestellpositionen
- E-Mail-Versand
- Payment-`after_process()`

Ein zweiter Callback nach dem Session-Lock-Timeout kann deshalb dieselbe
temporäre Bestellung mehrfach finalisieren. Die stabile Bestell-ID verhindert
nur einen zweiten Order-Header, nicht doppelte Positionen oder
Bestandsänderungen.

Betroffene Stellen:

- `checkout_process.php:90`
- `checkout_process.php:103`
- `checkout_process.php:252`
- `checkout_process.php:329`
- `checkout_process.php:464`
- `checkout_process.php:496`

### P1: Payone CC kann dauerhaft auf `processing` bleiben

`$creates_tmp_order` wird vor `before_process()` berechnet. Payone CC setzt
`tmpOrders` jedoch erst innerhalb von `before_process()`.

Dadurch kann der erste Request den Checkout beanspruchen und anschließend doch
eine temporäre Order erzeugen. Beim Rücksprung existiert `tmp_oID`, weshalb kein
Owner gesetzt und `complete()` nicht aufgerufen wird.

Die Bestellung kann erfolgreich sein, während der Processing-Datensatz
dauerhaft auf `processing` bleibt. Ein späterer Checkout desselben Kunden kann
dann nicht mehr übernommen werden.

Betroffene Stellen:

- `checkout_process.php:82`
- `checkout_process.php:88`
- `checkout_process.php:90`
- `checkout_process.php:522`
- `includes/modules/payment/payone_cc.php:43`
- `includes/modules/payment/payone_cc.php:200`
- `includes/modules/payment/payone_cc.php:209`

### P1: Ein Request ohne Session-Lock schreibt eine veraltete Session zurück

Nach einem Lock-Timeout liest der Session-Handler die vorhandene Session weiter.
Wenn `claim()` fehlschlägt, ruft `checkout_process.php`
`session_write_close()` auf.

Der Session-Handler führt `write()` unabhängig von `$lock_acquired` aus. Der
zweite Request kann dadurch Warenkorb, `tmp_oID`, Payment-Daten oder andere
Session-Änderungen des besitzenden Requests überschreiben.

Betroffene Stellen:

- `checkout_process.php:93`
- `includes/functions/sessions.php:61`
- `includes/functions/sessions.php:63`
- `includes/functions/sessions.php:84`
- `includes/functions/sessions.php:99`

### P1: Fehler hinterlassen Checkout-Sperren ohne Recovery

Die Checkout-Klasse besitzt nur `claim()`, `set_order()` und `complete()`.
Es gibt keinen Fehlerstatus, Timeout oder Wiederherstellungsmechanismus.

Ein Fehler nach erfolgreichem `claim()` kann den Datensatz dauerhaft auf
`processing` lassen. Mögliche Fehlerstellen sind unter anderem:

- Verarbeitung der Order Totals
- Schreiben der Bestellung
- E-Mail-Versand
- Zahlungsmodul-Hooks
- Checkout-End-Hooks

`checkout_confirmation.php` erzeugt nur bei `completed` oder `failed` einen
neuen Schlüssel. Der Status `failed` wird derzeit nirgends gesetzt. Das
Frontend pollt alle anderen Zustände unbegrenzt weiter.

Betroffene Stellen:

- `includes/classes/checkout.php:58`
- `includes/classes/checkout.php:71`
- `includes/classes/checkout.php:81`
- `checkout_confirmation.php:50`
- `templates/tpl_modified_nova/javascript/extra/checkout_processing.js.php:53`

### P1: Processing-Seite und Polling blockieren erneut am Session-Lock

`checkout_processing.php` und `ajax.php` starten regulär die Session. Dadurch
warten beide erneut am Lock des laufenden Checkout-Requests.

Bei MySQL-Sessions kann folgende Kette entstehen:

1. Der zweite `checkout_process` wartet bis zu 30 Sekunden.
2. `checkout_processing.php` wartet erneut bis zu 30 Sekunden.
3. Das AJAX-Polling wartet wiederum bis zu 30 Sekunden.

Bei Datei-Sessions existiert der MySQL-Lock-Timeout-Pfad nicht. Die
Processing-Seite ist damit während der laufenden Bestellung nicht zuverlässig
erreichbar.

Betroffene Stellen:

- `checkout_processing.php:13`
- `ajax.php:40`
- `includes/functions/sessions.php:32`
- `includes/functions/sessions.php:61`
- `templates/tpl_modified_nova/javascript/extra/checkout_processing.js.php:53`

### P1: Das globale Overlay kollidiert mit asynchronen Zahlungsmodulen

Das Checkout-JavaScript zeigt das Overlay bei jedem Klick beziehungsweise
Submit sofort an.

Zahlungsarten wie PayPal ACDC und Klarna verhindern den Submit zunächst und
führen eine asynchrone Autorisierung aus. Bleibt der Benutzer bei einem Fehler
auf der Seite, wird das globale Checkout-Overlay nicht wieder geschlossen.
Dadurch kann der Checkout vollständig verdeckt und unbedienbar bleiben.

Betroffene Stellen:

- `templates/tpl_modified_nova/javascript/extra/checkout_processing.js.php:25`
- `templates/tpl_modified_nova/javascript/extra/checkout_processing.js.php:35`
- `includes/modules/payment/paypalacdc.php:191`
- `includes/external/klarna/classes/KlarnaPaymentBase.php:186`

Die JavaScript-Datei ist in allen vier Templates identisch vorhanden.

### P2: Keine Bereinigung der Processing-Tabelle

Jeder abgeschlossene reguläre Checkout hinterlässt dauerhaft einen Datensatz.
Es gibt keine TTL, Cron-Bereinigung oder Löschung bei Kunden- oder
Bestellbereinigung.

Die Tabelle wächst dadurch unbegrenzt.

Betroffene Stellen:

- `_installer/includes/sql/modified.sql`
- `_installer/update/update_3.3.0_to_3.3.1.sql`
- `includes/classes/checkout.php`

## Durchgeführte Prüfungen

- Alle 18 geänderten PHP- und PHP-Template-Dateien bestehen `php -l`.
- Beide JavaScript-Pfade bestehen `node --check`.
- Installations- und Update-Schema sind konsistent.
- Alle vier Templates besitzen eigenständige Processing-Assets.
- Die CSS-, JavaScript- und Processing-Template-Dateien sind in allen vier
  Templates inhaltlich identisch.
- `git diff --check` ist unter Berücksichtigung der bestehenden CRLF-Dateien
  sauber.
- Die lokale Datenbank enthielt beim Review fünf erfolgreiche
  `completed`-Versuche.
- Lokal installiert und getestet waren `moneyorder` und normales `paypal`.
- Temp-Order-, Fehler- und Lock-Timeout-Pfade sind nicht automatisiert
  abgedeckt.
- Im Repository existiert keine Checkout-spezifische automatisierte Testsuite.

## Empfohlene Bearbeitungsreihenfolge

1. Ownership vor alle potenziell irreversiblen Payment-Hooks bringen oder die
   Payment-Hooks explizit in einen idempotenten Vorbereitungs- und
   Verarbeitungsabschnitt aufteilen.
2. Temporäre Orders in dasselbe Ownership-Modell aufnehmen und die Finalisierung
   einer vorhandenen `tmp_oID` atomar beanspruchen.
3. Session-Schreibvorgänge ohne erfolgreich erworbenen Lock verhindern.
4. Einen Recovery-Pfad mit `failed`, Timeout und kontrolliertem Retry ergänzen.
5. Processing-Seite und Statusabfrage lock-frei beziehungsweise read-only
   ausführen.
6. Das Overlay erst anzeigen, wenn die Zahlungsautorisierung abgeschlossen und
   die tatsächliche Navigation zur Bestellverarbeitung gestartet wird.
7. Eine Aufbewahrungs- und Bereinigungsstrategie für `checkout_processing`
   definieren.
8. Automatisierte Tests für parallele Requests, Temp-Orders, Payment-Fehler und
   Session-Lock-Timeouts ergänzen.

## Review-Fazit

Der normale Checkout mit `moneyorder` und normalem `paypal` funktioniert. Die
P0- und P1-Findings sind jedoch Merge-Blocker, weil sie Zahlungs-, Bestands-,
Bestell- oder Sessiondaten beschädigen beziehungsweise Kunden dauerhaft im
Checkout blockieren können.
