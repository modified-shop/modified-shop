<?php
require_once('billpay.php');

/* Default Messages */
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_TITLE', 'BillPay - Ratenkauf');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_DESCRIPTION', 'BillPay - Ratenkauf');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_MESSAGE', 'BillPay Error Message');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INFO', '');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ALLOWED_DESC' , 'Geben Sie einzeln die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_LOGGING_TITLE' , 'Absoluter Pfad zur Logdatei');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_LOGGING_DESC' , 'Wenn kein Wert eingestellt ist, wird standardm&auml;ssig in das Verzeichnis /includes/external/billpay/log geschrieben (Schreibrechte m&uuml;ssen verf&uuml;gbar sein).');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_MERCHANT_ID_TITLE' , 'Verk&auml;ufer ID');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_MERCHANT_ID_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ORDER_STATUS_TITLE' , 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ORDER_STATUS_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_PORTAL_ID_TITLE' , 'Portal ID');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_PORTAL_ID_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_SECURE_TITLE' , 'Security Key');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_SECURE_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_STATUS_TITLE' , 'Aktiviert');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_STATUS_DESC' , 'M&ouml;chten Sie den Ratenkauf mit BillPay erlauben?');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TESTMODE_TITLE' , 'Transaktionsmodus');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TESTMODE_DESC' , 'Im Testmodus werden detailierte Fehlermeldungen angezeigt. F&uuml;r den Produktivbetrieb muss der Livemodus aktiviert werden.');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ZONE_TITLE' , 'Steuerzone');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ZONE_DESC' , '');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_API_URL_BASE_TITLE' , 'API url base');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_API_URL_BASE_DESC' , 'Diese Daten erhalten Sie von BillPay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TESTAPI_URL_BASE_TITLE' , 'Test API url base');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TESTAPI_URL_BASE_DESC' , 'Diese Daten erhalten Sie von BillPay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_LOGGING_ENABLE_TITLE' , 'Logging aktiviert');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_LOGGING_ENABLE_DESC' , 'Sollen Anfragen an die BillPay-Zahlungsschnittstelle in die Logdatei geschrieben werden?');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_MIN_AMOUNT_TITLE', 'Mindestbestellwert');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_MIN_AMOUNT_DESC', 'Ab diesem Bestellwert wird die Zahlungsart eingeblendet.');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_LOGPATH_TITLE', 'Logging Pfad');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_LOGPATH_DESC', '');

define('MODULE_PAYMENT_BILLPAY_HTTP_X_TITLE', 'X_FORWARDED_FOR erlauben');
define('MODULE_PAYMENT_BILLPAY_HTTP_X_DESC', 'Aktivieren Sie dieses Funktion wenn Ihr Shop in einem Cloud System l&auml;uft.');

// Payment selection texts
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BIRTHDATE', 'Bitte geben Sie Ihr Geburtsdatum ein');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ENTER_BIRTHDATE', 'Bitte geben Sie Ihr Geburtsdatum ein');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ENTER_GENDER', 'Bitte geben Sie Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ENTER_BIRTHDATE_AND_GENDER', 'Bitte geben Sie Ihr Geburtsdatum und Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_NOTE', '');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_REQ', '');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_GENDER', 'Geschlecht');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_MALE', 'm&auml;nnlich');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_FEMALE', 'weiblich');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ENTER_PHONE', 'Bitte geben Sie Ihre Telefonnummer ein');

define('JS_BILLPAYTRANSACTIONCREDIT_EULA', '* Bitte best&auml;tigen Sie die BillPay AGB!\n\n');
define('JS_BILLPAYTRANSACTIONCREDIT_DOBDAY', '* Bitte geben Sie Ihr Geburtstag ein.\n\n');
define('JS_BILLPAYTRANSACTIONCREDIT_DOBMONTH', '* Bitte geben Sie Ihr Geburtsmonat.\n\n');
define('JS_BILLPAYTRANSACTIONCREDIT_DOBYEAR', '* Bitte geben Sie Ihr Geburtsjahr ein.\n\n');
define('JS_BILLPAYTRANSACTIONCREDIT_GENDER', '* Bitte geben Sie Ihr Geschlecht ein.\n\n');
define('JS_BILLPAYTRANSACTIONCREDIT_CODE', '* Bitte geben Sie die BIC ein.\n\n');
define('JS_BILLPAYTRANSACTIONCREDIT_NUMBER', '* Bitte geben Sie die IBAN ein.\n\n');
define('JS_BILLPAYTRANSACTIONCREDIT_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.\n\n');
define('JS_BILLPAYTRANSACTIONCREDIT_PHONE', '* Bitte geben Sie Ihre Telefonnummer ein.\n\n');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_EULA', '* Bitte best&auml;tigen Sie die BillPay AGB!');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_DEFAULT', 'Es ist ein interner Fehler aufgetreten. Bitte w&auml;len Sie eine andere Zahlart');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_BOD' ,'You have entered an incorrect date of birth!');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_SHORT', 'Es ist ein interner Fehler aufgetreten!');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_CREATED_COMMENT', 'Das Zahlungsziel der Bestellung wurde erfolgreich bei BillPay gestartet');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_CANCEL_COMMENT', 'Die Bestellung wurde erfolgreich bei BillPay storniert');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_DUEDATE', 'Das Zahlungsziel konnte nicht gestartet werden, weil das F%E4lligkeitsdatum leer ist!');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NUMBER', '* Bitte geben Sie die IBAN ein.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_CODE', '* Bitte geben Sie die BIC ein.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NO_RATEPLAN', '* Bitte fordern Sie einen Ratenplan f&uuml;r die ausgew&auml;lte Anzahl Raten an.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_PHONE', '* Bitte geben Sie Ihre Telefonnummer ein.');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_CREATE_INVOICE', 'BillPay Zahlungsziel jetzt aktivieren?');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_CANCEL_ORDER', 'BillPay Bestellung jetzt stornieren?');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ACCOUNT_HOLDER', 'Kontoinhaber');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_IBAN', 'IBAN');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANK_NAME', 'Bank');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BIC', 'BIC');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_REFERENCE', 'Rechnungsnummer');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_PHONE', 'Telefonnummer');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANKDATA', 'Bitte geben Sie Ihre Bankverbindung ein.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO2', 'Bestellung wurde abgegeben mittels ');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO3', '');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_DUEDATE_TITLE', 'F&auml;lligkeitsdatum');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_PURPOSE', 'Verwendungszweck');


define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_SANDBOX', 'Sie befinden sich im Sandbox-Modus:');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_CHECK', 'Sie befinden sich im Abnahme-Modus:');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_UNLOCK_INFO', 'Informationen zur Live-Schaltung');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_UTF8_ENCODE_TITLE', 'UTF8-Kodierung aktivieren');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_UTF8_ENCODE_DESC', 'Deaktivieren Sie diese Option, wenn Sie in Ihrem Online-Shop die UTF-8 Kodierung einsetzen.');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ACTIVATE_ORDER', 'Die Bestellung wurde noch nicht bei BillPay aktiviert. Bitte aktivieren Sie die Bestellung unmittelbar vor der Versendung, in dem Sie den entsprechenden Status setzen.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ACTIVATE_ORDER_WARNING', "<strong style='color:red'>Achtung: Das Zahlungsziel wurde noch nicht bei BillPay gestartet!</strong><br/>");

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_SALUTATION_MALE', 'Herr');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_SALUTATION_FEMALE', 'Frau');

// Text fragments needed for payment details of transaction credit orders
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO1',       'Vielen Dank, dass Sie sich f&uuml;r den BillPay Ratenkauf entschieden haben. Die f&auml;lligen Betr&auml;ge werden monatlich von dem bei der Bestellung angegebenen Konto abgebucht.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO_MANUAL', 'Vielen Dank, dass Sie sich f&uuml;r den BillPay Ratenkauf entschieden haben. Sie bekommen in K&uuml;rze einen Ratenplan von uns zu gesendet. Bitte &uuml;berweisen Sie uns die f&auml;lligen Betr&auml;ge zum vereinbarten F&auml;lligkeitsdatum auf folgendes Konto.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICEPDF_INFO', 'Sie haben sich f&uuml;r die Zahlung per Ratenkauf entschieden. Bitte beachten Sie, dass zus&auml;tzlich zu dem auf dieser Rechnung genannten Rechnungsbetrag weitere Kosten im Zusammenhang mit dem Teilzahlungsgesch&auml;ft (Ratenkauf) entstehen. Diese Kosten wurden Ihnen vor Abschluss der Bestellung angezeigt. Die vollst&auml;ndige Berechnung der zu leistenden Betr&auml;ge im Zusammenhang mit dem Ratenkauf sowie s&auml;mtliche dazugeh&ouml;rige Informationen haben Sie bereits per Email an die bei der Bestellung angegebene Email-Adresse erhalten.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_RATE', 'Rate');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_RATEDUE_TEXT', 'f&auml;llig am');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_PRICE_CALC_TEXT', 'Gesamtpreisberechnung');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CART_AMOUNT_TEXT', 'Warenkorbwert');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CART_AMOUNT_AFTER_PREPAYMENT_TEXT', 'Warenkorbwert nach Anzahlung');
define('MODULE_PAYMENT_BILLPAYTC_SURCHARGE_TEXT', 'Zinsaufschlag');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TEXT', 'Bearbeitungsgeb&uuml;hr');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_OTHER_COSTS_TEXT', 'weitere Geb&uuml;hren (z.B. Versandgeb&uuml;hr)');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_AMOUNT_TEXT', 'Gesamtsumme Ratenkauf');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ANUAL_RATE_TEXT', 'Effektiver Jahreszins');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_DIVIDED_BY_RATES', 'Geteilt durch die Anzahl der Raten');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_FIRST_RATE', 'Die erste Rate inkl. Geb&uuml;hren betr&auml;gt');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_FOLLOWING_RATES', 'Jede folgende Rate betr&auml;gt');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_UNIQUE_RATE', 'Jede Rate betr&auml;gt');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CAPTION_TEXT1', 'Ihre Teilzahlung in');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CAPTION_TEXT2', 'Monatsraten');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ENTER_NUMBER_RATES', 'Bitte w&auml;hlen Sie die gew&uuml;nschte Laufzeit in Monaten');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_RATES', 'Raten');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CALCULATE_RATES', 'Ratenplan berechnen');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_FINANCE_DETAILS_LINK_TEXT', 'Finanzierungsdetails');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_PREPAYMENT_TEXT', 'Anzahlung');

define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE', 'Bearbeitungsgeb&uuml;hr');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TAX1', 'inkl.');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TAX2', 'MwSt.');
define('MODULE_ORDER_TOTAL_BILLPAYTC_SURCHARGE', 'Zinsaufschlag');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TOTAL', 'Gesamtsumme Ratenkauf');

define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_FIRST_RATE',    'Die erste Rate inkl. Bearbeitungs- und Versandgeb&uuml;hren betr&auml;gt <span>%s</span>.');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_NEXT_RATE',     'Jede folgende Rate betr&auml;gt <span>%s</span>.');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_TOTAL',         'Insgesamt bezahlen Sie <span>%s</span>.');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_BASE',          'Darin ist der Bestellwert von <span>%s</span>,');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_RATES',         'Zinsen von <span>%s</span>,');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_PROCESSING',    'eine Bearbeitungsgeb&uuml;hr von <span>%s</span>');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_SHIPPING',      'und Versandkosten von <span>%s</span> enthalten.');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_DETAILS',       'Zahlungsdetails');

//define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_', '');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_THANK_YOU', 'Vielen Dank, dass Sie sich f&uuml;r den BillPay Ratenkauf entschieden haben.');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_RATE_PLAN_EMAIL', 'Sie bekommen in K&uuml;rze einen Ratenplan von uns zu gesendet.');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_MANUAL_TRANSFER', 'Bitte &uuml;berweisen Sie uns die f&auml;lligen Betr&auml;ge zum vereinbarten F&auml;lligkeitsdatum auf folgendes Konto.');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_MANUAL_RATE_PLAN', 'Bitte &uuml;berweisen Sie uns die f&auml;lligen Raten zu den folgenden Daten:');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_PAYEE', 'Zahlungsempf&auml;nger:');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_AMOUNT', 'Betrag');
define('MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_DATES', 'Datum');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_EULA_CHECK', 'Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung des Ratenkaufs und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an BillPay bin ich einverstanden. Es gelten die <a href="%s" target="_blank">AGB</a>, <a href="%s" target="_blank">Zahlungsbedinungen</a> und <a href="%s" target="_blank">Datenschutzbestimmungen</a>');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_EULA_CHECK_DE', '<label for="billpay_eula">Hiermit best&auml;tige ich die <a href="https://www.billpay.de/kunden/agb-ch" target="_blank">AGB</a> und die <a href="https://www.billpay.de/kunden/agb-ch#datenschutz" target="_blank">Datenschutzbestimmungen</a> der BillPay GmbH </label> <br />');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_LINK1', 'AGB Ratenkauf');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_LINK2', 'Datenschutz-<br/>bestimmungen');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_LINK3', 'Zahlungs-<br>bedingungen');

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_EULA_CHECK_SEPA',    "Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung der Zahlung und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a> bin ich einverstanden. Es gelten die <a href='%s' target='_blank'>AGB</a>, <a href='%s' target='_blank'>Zahlungsbedingungen</a> und <a href='%s' target='_blank'>Datenschutzbestimmungen</a> von BillPay.<br/><br/>Ich erteile BillPay und der <a href='https://www.privatbank1891.com' target='_blank'>net-m privatbank 1891 AG</a> ein SEPA-Lastschriftmandat (<a href='#' class='bpy-btn-details'>Einzelheiten</a>) zur Einziehung f&auml;lliger Zahlungen und weise mein Geldinstitut an, die Lastschriften einzul&ouml;sen.");
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_EULA_CHECK_SEPA_AT', "Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung der Zahlung und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a> bin ich einverstanden. Es gelten die <a href='%s' target='_blank'>AGB</a>, <a href='%s' target='_blank'>Zahlungsbedingungen</a> und <a href='%s' target='_blank'>Datenschutzbestimmungen</a> von BillPay.<br/><br/>Ich erteile BillPay und der <a href='https://www.privatbank1891.com' target='_blank'>net-m privatbank 1891 AG</a> ein SEPA-Lastschriftmandat (<a href='#' class='bpy-btn-details'>Einzelheiten</a>) zur Einziehung f&auml;lliger Zahlungen und weise mein Geldinstitut an, die Lastschriften einzul&ouml;sen.");
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_EULA_CHECK_SEPA_CH', "Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung des Ratenkaufs und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung enforderlichen Daten an die <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a> bin ich einverstanden. Es gelten die <a href='%s' target='_blank'>AGB</a>, <a href='%s' target='_blank'>Zahlungsbedingungen</a> und <a href='%s' target='_blank'>Datenschutzbestimmungen</a>.");

define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_SEPA_INFORMATION',    "Die Gl&auml;ubiger-Identifikationsnummer von BillPay ist DE19ZZZ00000237180, die Gl&auml;ubiger-Identifikationsnummer der net-m privatbank AG ist DE62ZZZ00000009232. Die Mandatsreferenznummer wird mir zu einem sp&auml;teren Zeitpunkt per Email zusammen mit einer Vorlage f&uuml;r ein schriftliches Mandat mitgeteilt. Ich werde zus&auml;tzlich dieses schriftliche Mandat unterschreiben und an BillPay senden.<br/><br/>Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Geldinstitut vereinbarten Bedingungen. Bitte beachten Sie, dass die f&auml;llige Forderung auch bei einer R&uuml;cklastschrift bestehen bleibt. Weitere Informationen finden Sie auf <a href='https://www.billpay.de/sepa' target='_blank'>https://www.billpay.de/sepa</a>.");
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_SEPA_INFORMATION_AT', "Die Gl&auml;ubiger-Identifikationsnummer von BillPay ist DE19ZZZ00000237180, die Gl&auml;ubiger-Identifikationsnummer der net-m privatbank AG ist DE62ZZZ00000009232. Die Mandatsreferenznummer wird mir zu einem sp&auml;teren Zeitpunkt per Email zusammen mit einer Vorlage f&uuml;r ein schriftliches Mandat mitgeteilt. Ich werde zus&auml;tzlich dieses schriftliche Mandat unterschreiben und an BillPay senden.<br/><br/>Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Geldinstitut vereinbarten Bedingungen. Bitte beachten Sie, dass die f&auml;llige Forderung auch bei einer R&uuml;cklastschrift bestehen bleibt. Weitere Informationen finden Sie auf <a href='https://www.billpay.de/sepa' target='_blank'>https://www.billpay.de/sepa</a>.");
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ADD', 'zzgl.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_FEE', 'Geb&uuml;hr');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_FEE_INFO1', 'F&uuml;r diese Bestellung per Ratenkauf wird eine Geb&uuml;hr von ');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_FEE_INFO2', ' erhoben');

// Plugin 1.7
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_THANK_YOU_TEXT', 'Vielen Dank, dass Sie sich beim Kauf der Ware f&uuml;r den BillPay Ratenkauf entschieden haben.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_PAY_UNTIL_TEXT', 'Die f&auml;lligen Betr&auml;ge werden monatlich von dem bei der Bestellung angegebenen Konto abgebucht.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_PAY_UNTIL_TEXT_CH', 'Bitte &uuml;berweisen Sie uns die f&auml;lligen Betr&auml;ge zum vereinbarten F&auml;lligkeitsdatum auf folgendes Konto.');
define('MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_EMAIL_TEXT', 'Zus&auml;tzlich zu der Rechnung erhalten Sie in K&uuml;rze einen Ratenplan mit detaillierten Informationen zu Ihrem Ratenkauf.');