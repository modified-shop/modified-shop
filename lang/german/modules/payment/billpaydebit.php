<?php
require_once('billpay.php');

/* Default Messages */
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_TITLE', 'BillPay - Lastschrift');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_DESCRIPTION', 'BillPay - Lastschrift');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_MESSAGE', 'BillPay Fehlernachricht');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INFO', '');

define('MODULE_PAYMENT_BILLPAYDEBIT_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_BILLPAYDEBIT_ALLOWED_DESC' , 'Geben Sie einzeln die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

define('MODULE_PAYMENT_BILLPAYDEBIT_LOGGING_TITLE' , 'Absoluter Pfad zur Logdatei');
define('MODULE_PAYMENT_BILLPAYDEBIT_LOGGING_DESC' , 'Wenn kein Wert eingestellt ist, wird standardm&auml;ssig in das Verzeichnis /includes/external/billpay/log geschrieben (Schreibrechte m&uuml;ssen verf&uuml;gbar sein).\'');

define('MODULE_PAYMENT_BILLPAYDEBIT_MERCHANT_ID_TITLE' , 'Verk&auml;ufer ID');
define('MODULE_PAYMENT_BILLPAYDEBIT_MERCHANT_ID_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYDEBIT_ORDER_STATUS_TITLE' , 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BILLPAYDEBIT_ORDER_STATUS_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');

define('MODULE_PAYMENT_BILLPAYDEBIT_PORTAL_ID_TITLE' , 'Portal ID');
define('MODULE_PAYMENT_BILLPAYDEBIT_PORTAL_ID_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYDEBIT_SECURE_TITLE' , 'Security Key');
define('MODULE_PAYMENT_BILLPAYDEBIT_SECURE_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYDEBIT_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BILLPAYDEBIT_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

define('MODULE_PAYMENT_BILLPAYDEBIT_STATUS_TITLE' , 'Aktiviert');
define('MODULE_PAYMENT_BILLPAYDEBIT_STATUS_DESC' , 'M&ouml;chten Sie den Rechnungskauf mit BillPay erlauben?');

define('MODULE_PAYMENT_BILLPAYDEBIT_TESTMODE_TITLE' , 'Transaktionsmodus');
define('MODULE_PAYMENT_BILLPAYDEBIT_TESTMODE_DESC' , 'Im Testmodus werden detailierte Fehlermeldungen angezeigt. F&uuml;r den Produktivbetrieb muss der Livemodus aktiviert werden.');

define('MODULE_PAYMENT_BILLPAYDEBIT_ZONE_TITLE' , 'Steuerzone');
define('MODULE_PAYMENT_BILLPAYDEBIT_ZONE_DESC' , '');

define('MODULE_PAYMENT_BILLPAYDEBIT_API_URL_BASE_TITLE' , 'API url base');
define('MODULE_PAYMENT_BILLPAYDEBIT_API_URL_BASE_DESC' , 'Diese Daten erhalten Sie von BillPay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAYDEBIT_TESTAPI_URL_BASE_TITLE' , 'Test API url base');
define('MODULE_PAYMENT_BILLPAYDEBIT_TESTAPI_URL_BASE_DESC' , 'Diese Daten erhalten Sie von BillPay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAYDEBIT_LOGGING_ENABLE_TITLE' , 'Logging aktiviert');
define('MODULE_PAYMENT_BILLPAYDEBIT_LOGGING_ENABLE_DESC' , 'Sollen Anfragen an die BillPay-Zahlungsschnittstelle in die Logdatei geschrieben werden?');

define('MODULE_PAYMENT_BILLPAYDEBIT_MIN_AMOUNT_TITLE', 'Mindestbestellwert');
define('MODULE_PAYMENT_BILLPAYDEBIT_MIN_AMOUNT_DESC', 'Ab diesem Bestellwert wird die Zahlungsart eingeblendet.');

define('MODULE_PAYMENT_BILLPAYDEBIT_LOGPATH_TITLE', 'Logging Pfad');
define('MODULE_PAYMENT_BILLPAYDEBIT_LOGPATH_DESC', '');

define('MODULE_PAYMENT_BILLPAY_HTTP_X_TITLE', 'X_FORWARDED_FOR erlauben');
define('MODULE_PAYMENT_BILLPAY_HTTP_X_DESC', 'Aktivieren Sie dieses Funktion wenn Ihr Shop in einem Cloud System l&auml;uft.');

// Payment selection texts
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BIRTHDATE', 'Geburtsdatum');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ENTER_BIRTHDATE', 'Bitte geben Sie Ihr Geburtsdatum ein');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ENTER_GENDER', 'Bitte geben Sie Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ENTER_BIRTHDATE_AND_GENDER', 'Bitte geben Sie Ihr Geburtsdatum und Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_NOTE', '');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_REQ', '');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_GENDER', 'Geschlecht');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_MALE', 'm&auml;nnlich');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_FEMALE', 'weiblich');

define('JS_BILLPAYDEBIT_EULA', '* Bitte best%E4tigen Sie die BillPay AGB!\n\n');
define('JS_BILLPAYDEBIT_DOBDAY', '* Bitte geben Sie Ihr Geburtstag ein.\n\n');
define('JS_BILLPAYDEBIT_DOBMONTH', '* Bitte geben Sie Ihr Geburtsmonat.\n\n');
define('JS_BILLPAYDEBIT_DOBYEAR', '* Bitte geben Sie Ihr Geburtsjahr ein.\n\n');
define('JS_BILLPAYDEBIT_GENDER', '* Bitte geben Sie Ihr Geschlecht ein.\n\n');
define('JS_BILLPAYDEBIT_CODE', '* Bitte geben Sie die BIC ein.\n\n');
define('JS_BILLPAYDEBIT_NUMBER', '* Bitte geben Sie die IBAN ein.\n\n');
define('JS_BILLPAYDEBIT_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.\n\n');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_EULA', '* Bitte best%E4igen Sie die BillPay AGB!');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_DEFAULT', 'Es ist ein interner Fehler aufgetreten. Bitte w&auml;len Sie eine andere Zahlart');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_DOB' ,'You have entered an incorrect date of birth!');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_SHORT', 'Es ist ein interner Fehler aufgetreten!');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_CREATED_COMMENT', 'Das Zahlungsziel der Bestellung wurde erfolgreich bei BillPay gestartet');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_CANCEL_COMMENT', 'Die Bestellung wurde erfolgreich bei BillPay storniert');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_DUEDATE', 'Das Zahlungsziel konnte nicht gestartet werden, weil das F%E4lligkeitsdatum leer ist!');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NUMBER', '* Bitte geben Sie die IBAN ein.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_CODE', '* Bitte geben Sie die BIC ein.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_CREATE_INVOICE', 'BillPay Zahlungsziel jetzt aktivieren?');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_CANCEL_ORDER', 'BillPay Bestellung jetzt stornieren?');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ACCOUNT_HOLDER', 'Kontoinhaber');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_IBAN', 'IBAN');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BANK_NAME', 'Bank');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BIC', 'BIC');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_REFERENCE', 'Rechnungsnummer');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BANKDATA', 'Bitte geben Sie Ihre Bankverbindung ein.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1', 'Vielen Dank, dass Sie sich f&uuml;r die Zahlung per Lastschrift mit BillPay entschieden haben.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO2', 'Wir buchen den f&auml;lligen Betrag in den n&auml;chsten Tagen von dem bei der Bestellung angegebenen Konto ab.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO3', '');

define('MODULE_PAYMENT_BILLPAYDEBIT_DUEDATE_TITLE', 'F&auml;lligkeitsdatum');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_PURPOSE', 'Verwendungszweck');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ADD', 'zzgl.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_FEE', 'Geb&uuml;hr');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_FEE_INFO1', 'F&uuml;r diese Bestellung per Lastschrift wird eine Geb&uuml;hr von ');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_FEE_INFO2', ' erhoben');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_SANDBOX', 'Sie befinden sich im Sandbox-Modus:');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_CHECK', 'Sie befinden sich im Abnahme-Modus:');
define('MODULE_PAYMENT_BILLPAYDEBIT_UNLOCK_INFO', 'Informationen zur Live-Schaltung');

define('MODULE_PAYMENT_BILLPAYDEBIT_UTF8_ENCODE_TITLE', 'UTF8-Kodierung aktivieren');
define('MODULE_PAYMENT_BILLPAYDEBIT_UTF8_ENCODE_DESC', 'Deaktivieren Sie diese Option, wenn Sie in Ihrem Online-Shop die UTF-8 Kodierung einsetzen.');

define('MODULE_PAYMENT_BILLPAYDEBIT_ACTIVATE_ORDER', 'Die Bestellung wurde noch nicht bei BillPay aktiviert. Bitte aktivieren Sie die Bestellung unmittelbar vor der Versendung, in dem Sie den entsprechenden Status setzen.');
define('MODULE_PAYMENT_BILLPAYDEBIT_ACTIVATE_ORDER_WARNING', "<strong style='color:red'>Achtung: Das Zahlungsziel wurde noch nicht bei BillPay gestartet!</strong><br/>");

define('MODULE_PAYMENT_BILLPAYDEBIT_SALUTATION_MALE', 'Herr');
define('MODULE_PAYMENT_BILLPAYDEBIT_SALUTATION_FEMALE', 'Frau');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_EULA_CHECK_SEPA',    "Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung der Zahlung und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a> bin ich einverstanden. Es gelten die <a href='%s' target='_blank'>Datenschutzbestimmungen</a> von BillPay.<br/><br/>Ich erteile BillPay ein SEPA-Lastschriftmandat (<a href='#' class='bpy-btn-details'>Einzelheiten</a>) zur Einziehung f&auml;lliger Zahlungen und weise mein Geldinstitut an, die Lastschriften einzul&ouml;sen.");
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_EULA_CHECK_SEPA_AT', "Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung der Zahlung und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a> bin ich einverstanden. Es gelten die <a href='%s' target='_blank'>Datenschutzbestimmungen</a> von BillPay.<br/><br/>Ich erteile BillPay und der <a href='https://www.privatbank1891.com/' target='_blank'>net-m privatbank 1891 AG</a> ein SEPA-Lastschriftmandat (<a href='#' class='bpy-btn-details'>Einzelheiten</a>) zur Einziehung f&auml;lliger Zahlungen und weise mein Geldinstitut an, die Lastschriften einzul&ouml;sen.");

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_SEPA_INFORMATION',    'Die Gl&auml;ubiger-Identifikationsnummer von BillPay ist DE19ZZZ00000237180. Die Mandatsreferenznummer wird mir zu einem sp&auml;teren Zeitpunkt per Email mitgeteilt.<br/><br/>Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Geldinstitut vereinbarten Bedingungen. Bitte beachten Sie, dass die f&auml;llige Forderung auch bei einer R&uuml;cklastschrift bestehen bleibt. Weitere Informationen finden Sie auf <a href="https://www.billpay.de/sepa" target="_blank">https://www.billpay.de/sepa</a>.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_SEPA_INFORMATION_AT', "Die Gl&auml;ubiger-Identifikationsnummer von BillPay ist DE19ZZZ00000237180, die Gl&auml;ubiger-Identifikationsnummer der net-m privatbank AG ist DE62ZZZ00000009232. Die Mandatsreferenznummer wird mir zu einem sp&auml;teren Zeitpunkt per Email zusammen mit einer Vorlage f&uuml;r ein schriftliches Mandat mitgeteilt. Ich werde zus&auml;tzlich dieses schriftliche Mandat unterschreiben und an BillPay senden.<br/><br/>Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Geldinstitut vereinbarten Bedingungen. Bitte beachten Sie, dass die f&auml;llige Forderung auch bei einer R&uuml;cklastschrift bestehen bleibt. Weitere Informationen finden Sie auf <a href='https://www.billpay.de/sepa' target='_blank'>https://www.billpay.de/sepa</a>.");

// Plugin 1.7
define('MODULE_PAYMENT_BILLPAYDEBIT_THANK_YOU_TEXT', 'Vielen Dank, dass Sie sich beim Kauf der Ware f&uuml;r die BillPay Lastschrift entschieden haben.');
define('MODULE_PAYMENT_BILLPAYDEBIT_PAY_UNTIL_TEXT', 'Wir buchen den Betrag von %1$s %2$s in den n&auml;chsten Tagen von dem bei der Bestellung angegebenen Konto ab.');
define('MODULE_PAYMENT_BILLPAYDEBIT_EMAIL_TEXT', '&Uuml;ber das Einzugsdatum informiert BillPay Sie vorab per E-Mail.');
