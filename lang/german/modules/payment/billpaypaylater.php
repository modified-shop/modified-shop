<?php
require_once('billpay.php');

/* Default Messages */
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_MESSAGE', 'BillPay Fehlernachricht');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INFO', '');

define('MODULE_PAYMENT_BILLPAYPAYLATER_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_BILLPAYPAYLATER_ALLOWED_DESC' , 'Geben Sie einzeln die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

define('MODULE_PAYMENT_BILLPAYPAYLATER_LOGGING_TITLE' , 'Absoluter Pfad zur Logdatei');
define('MODULE_PAYMENT_BILLPAYPAYLATER_LOGGING_DESC' , 'Wenn kein Wert eingestellt ist, wird standardm&auml;ssig in das Verzeichnis includes/external/billpay/log geschrieben (Schreibrechte m&uuml;ssen verf&uuml;gbar sein).\'');

define('MODULE_PAYMENT_BILLPAYPAYLATER_MERCHANT_ID_TITLE' , 'Verk&auml;ufer ID\'');
define('MODULE_PAYMENT_BILLPAYPAYLATER_MERCHANT_ID_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYPAYLATER_ORDER_STATUS_TITLE' , 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BILLPAYPAYLATER_ORDER_STATUS_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');

define('MODULE_PAYMENT_BILLPAYPAYLATER_PORTAL_ID_TITLE' , 'Portal ID');
define('MODULE_PAYMENT_BILLPAYPAYLATER_PORTAL_ID_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYPAYLATER_SECURE_TITLE' , 'Security Key');
define('MODULE_PAYMENT_BILLPAYPAYLATER_SECURE_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYPAYLATER_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BILLPAYPAYLATER_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

define('MODULE_PAYMENT_BILLPAYPAYLATER_STATUS_TITLE' , 'Aktiviert');
define('MODULE_PAYMENT_BILLPAYPAYLATER_STATUS_DESC' , 'M&ouml;chten Sie den Rechnungskauf mit BillPay erlauben?');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TESTMODE_TITLE' , 'Transaktionsmodus');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TESTMODE_DESC' , 'Im Testmodus werden detailierte Fehlermeldungen angezeigt. F&uuml;r den Produktivbetrieb muss der Livemodus aktiviert werden.');

define('MODULE_PAYMENT_BILLPAYPAYLATER_ZONE_TITLE' , 'Steuerzone');
define('MODULE_PAYMENT_BILLPAYPAYLATER_ZONE_DESC' , '');

define('MODULE_PAYMENT_BILLPAYPAYLATER_API_URL_BASE_TITLE' , 'API url base');
define('MODULE_PAYMENT_BILLPAYPAYLATER_API_URL_BASE_DESC' , 'Diese Daten erhalten Sie von BillPay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TESTAPI_URL_BASE_TITLE' , 'Test API url base');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TESTAPI_URL_BASE_DESC' , 'Diese Daten erhalten Sie von BillPay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAYPAYLATER_LOGGING_ENABLE_TITLE' , 'Logging aktiviert');
define('MODULE_PAYMENT_BILLPAYPAYLATER_LOGGING_ENABLE_DESC' , 'Sollen Anfragen an die BillPay-Zahlungsschnittstelle in die Logdatei geschrieben werden?');

define('MODULE_PAYMENT_BILLPAYPAYLATER_MIN_AMOUNT_TITLE', 'Mindestbestellwert');
define('MODULE_PAYMENT_BILLPAYPAYLATER_MIN_AMOUNT_DESC', 'Ab diesem Bestellwert wird die Zahlungsart eingeblendet.');

define('MODULE_PAYMENT_BILLPAYPAYLATER_LOGPATH_TITLE', 'Logging Pfad');
define('MODULE_PAYMENT_BILLPAYPAYLATER_LOGPATH_DESC', '');

define('MODULE_PAYMENT_BILLPAY_HTTP_X_TITLE', 'X_FORWARDED_FOR erlauben');
define('MODULE_PAYMENT_BILLPAY_HTTP_X_DESC', 'Aktivieren Sie dieses Funktion wenn Ihr Shop in einem Cloud System l&auml;uft.');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_BIRTHDATE', 'Geburtsdatum');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_EULA_CHECK', '');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_EULA_CHECK_DE', '');


define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_EULA_CHECK_SEPA',    '');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_EULA_CHECK_SEPA_AT', '');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_SEPA_INFORMATION',    '');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_SEPA_INFORMATION_AT', '');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ENTER_BIRTHDATE', 'Bitte geben Sie Ihr Geburtsdatum ein');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ENTER_GENDER', 'Bitte geben Sie Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ENTER_BIRTHDATE_AND_GENDER', 'Bitte geben Sie Ihr Geburtsdatum und Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_NOTE', '');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_REQ', '');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_GENDER', 'Geschlecht');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_MALE', 'm&auml;nnlich');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_FEMALE', 'weiblich');
define('MODULE_PAYMENT_BILLPAYPAYLATER_SALUTATION_MALE', '');
define('MODULE_PAYMENT_BILLPAYPAYLATER_SALUTATION_FEMALE', '');


define('JS_BILLPAYPAYLATER_EULA', '* Bitte best%E4tigen Sie die BillPay AGB!\n\n');
define('JS_BILLPAYPAYLATER_DOBDAY', '* Bitte geben Sie Ihr Geburtstag ein.\n\n');
define('JS_BILLPAYPAYLATER_DOBMONTH', '* Bitte geben Sie Ihr Geburtsmonat.\n\n');
define('JS_BILLPAYPAYLATER_DOBYEAR', '* Bitte geben Sie Ihr Geburtsjahr ein.\n\n');
define('JS_BILLPAYPAYLATER_GENDER', '* Bitte geben Sie Ihr Geschlecht ein.\n\n');

define('JS_BILLPAYPAYLATER_CODE', '* Bitte geben Sie die BIC ein.\n\n');
define('JS_BILLPAYPAYLATER_NUMBER', '* Bitte geben Sie die IBAN ein.\n\n');
define('JS_BILLPAYPAYLATER_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.\n\n');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_EULA', '* Bitte best%E4igen Sie die BillPay AGB!');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_DOB' , 'Sie haben ein inkorrektes Geburtsdatum angegeben');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_DEFAULT', 'Es ist ein interner Fehler aufgetreten. Bitte w&auml;len Sie eine andere Zahlart');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_SHORT', 'Es ist ein interner Fehler aufgetreten!');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_CREATED_COMMENT', 'Das Zahlungsziel der Bestellung wurde erfolgreich bei BillPay gestartet');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_CANCEL_COMMENT', 'Die Bestellung wurde erfolgreich bei BillPay storniert');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_DUEDATE', 'Das Zahlungsziel konnte nicht gestartet werden, weil das F%E4lligkeitsdatum leer ist!');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NO_RATEPLAN', '');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_CODE', '* Bitte geben Sie die BIC ein.');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NUMBER', '* Bitte geben Sie die IBAN ein.');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ERROR_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_CREATE_INVOICE', 'BillPay Zahlungsziel jetzt aktivieren?');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_CANCEL_ORDER', 'BillPay Bestellung jetzt stornieren?');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ACCOUNT_HOLDER', 'Kontoinhaber');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_IBAN', 'IBAN');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_BANK_NAME', 'Bank');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_BIC', 'BIC');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_REFERENCE', 'Rechnungsnummer');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_BANKDATA', 'Bitte geben Sie Ihre Bankverbindung ein.');

define('MODULE_PAYMENT_BILLPAYPAYLATER_DUEDATE_TITLE', 'F&auml;lligkeitsdatum');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_PURPOSE', 'Verwendungszweck');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_ADD', 'zzgl.');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_FEE', 'Geb&uuml;hr');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_SANDBOX', 'Sie befinden sich im Sandbox-Modus:');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_CHECK', 'Sie befinden sich im Abnahme-Modus:');
define('MODULE_PAYMENT_BILLPAYPAYLATER_UNLOCK_INFO', 'Informationen zur Live-Schaltung');

define('MODULE_PAYMENT_BILLPAYPAYLATER_UTF8_ENCODE_TITLE', 'UTF8-Kodierung aktivieren');
define('MODULE_PAYMENT_BILLPAYPAYLATER_UTF8_ENCODE_DESC', 'Deaktivieren Sie diese Option, wenn Sie in Ihrem Online-Shop die UTF-8 Kodierung einsetzen.');

define('MODULE_PAYMENT_BILLPAYPAYLATER_ACTIVATE_ORDER', 'Die Bestellung wurde noch nicht bei BillPay aktiviert. Bitte aktivieren Sie die Bestellung unmittelbar vor der Versendung, in dem Sie den entsprechenden Status setzen.');
define('MODULE_PAYMENT_BILLPAYPAYLATER_ACTIVATE_ORDER_WARNING', "<strong style='color:red'>Achtung: Das Zahlungsziel wurde noch nicht bei BillPay gestartet!</strong><br/>");

# only this payment

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_INFO1', 'Vielen Dank, dass Sie sich f&uuml;r die Zahlung mit PayLater entschieden haben. Die f&auml;lligen Betr&auml;ge werden von dem bei der Bestellung angegebenen Konto abgebucht. Zus&auml;tzlich zu dieser Rechnung bekommen Sie von BillPay in K&uuml;rze einen Teilzahlungsplan mit detaillierten Informationen &uuml;ber Ihre Teilzahlung.');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_INFO2', 'Bestellen Sie wurde ausgestellt von ');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_INFO3', '');

define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_FEE_INFO1', 'PayLater Geb&uuml;hr f&uuml;r diesen Auftrag ist ');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_FEE_INFO2', '');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_PROMO', 'Klicken Sie auf das Optionsfeld, um mehr zu zeigen.');

// OT
define('MODULE_PAYMENT_BILLPAY_OT_PAYLATER_FEE', 'Servicegeb&uuml;hr');
define('MODULE_PAYMENT_BILLPAY_OT_PAYLATER_TOTAL', 'Gesamtsumme PayLater');

define('MODULE_PAYMENT_BILLPAY_GIROPAY_CANCELED', 'Giropay abgebrochen');

define('MODULE_PAYMENT_BILLPAYPAYLATER_VISUAL_MODE_TITLE' , 'Visual Mode');
define('MODULE_PAYMENT_BILLPAYPAYLATER_VISUAL_MODE_DESC' , '');

// Plugin 1.7
define('MODULE_PAYMENT_BILLPAYPAYLATER_THANK_YOU_TEXT', 'Vielen Dank, dass Sie sich beim Kauf der Ware f&uuml;r die BillPay PayLater - Teilzahlung entschieden haben.');
define('MODULE_PAYMENT_BILLPAYPAYLATER_PAY_UNTIL_TEXT', 'Die f&auml;lligen Betr&auml;ge werden monatlich von dem bei der Bestellung angegebenen Konto abgebucht.');
define('MODULE_PAYMENT_BILLPAYPAYLATER_EMAIL_TEXT', 'Zus&auml;tzlich zu der Rechnung erhalten Sie in K&uuml;rze einen Teilzahlungsplan mit detaillierten Informationen zu Ihren Teilzahlungen.');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_TITLE', 'PayLater | Ratenkauf');
define('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_DESCRIPTION', 'PayLater | Ratenkauf');
