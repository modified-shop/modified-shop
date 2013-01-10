<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2012 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once('billpay.php');

/* Default Messages */
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_TITLE', 'Lastschrift (Billpay)');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_DESCRIPTION', 'Lastschrift (Billpay)');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_MESSAGE', 'BillPay Error Message');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INFO', '<img src="https://www.billpay.de/sites/all/themes/billpay/images/header_logo.png"  alt="billpay" title="billpay" width="190px" /><br /><br />');

define('MODULE_PAYMENT_BILLPAYDEBIT_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_BILLPAYDEBIT_ALLOWED_DESC' , 'Geben Sie einzeln die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

define('MODULE_PAYMENT_BILLPAYDEBIT_LOGGING_TITLE' , 'Absoluter Pfad zur Logdatei');
define('MODULE_PAYMENT_BILLPAYDEBIT_LOGGING_DESC' , 'Wenn kein Wert eingestellt ist, wird standardm&auml;ssig in das Verzeichnis includes/billpay/log geschrieben (Schreibrechte m&uuml;ssen verf&uuml;gbar sein).');

define('MODULE_PAYMENT_BILLPAYDEBIT_MERCHANT_ID_TITLE' , 'Verk&auml;ufer ID');
define('MODULE_PAYMENT_BILLPAYDEBIT_MERCHANT_ID_DESC' , 'Diese Daten erhalten Sie von Billpay');

define('MODULE_PAYMENT_BILLPAYDEBIT_ORDER_STATUS_TITLE' , 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BILLPAYDEBIT_ORDER_STATUS_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');

define('MODULE_PAYMENT_BILLPAYDEBIT_PORTAL_ID_TITLE' , 'Portal ID');
define('MODULE_PAYMENT_BILLPAYDEBIT_PORTAL_ID_DESC' , 'Diese Daten erhalten Sie von Billpay');

define('MODULE_PAYMENT_BILLPAYDEBIT_SECURE_TITLE' , 'Security Key');
define('MODULE_PAYMENT_BILLPAYDEBIT_SECURE_DESC' , 'Diese Daten erhalten Sie von Billpay');

define('MODULE_PAYMENT_BILLPAYDEBIT_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BILLPAYDEBIT_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

define('MODULE_PAYMENT_BILLPAYDEBIT_STATUS_TITLE' , 'Aktiviert');
define('MODULE_PAYMENT_BILLPAYDEBIT_STATUS_DESC' , 'M&ouml;chten Sie den Rechnungskauf mit Billpay erlauben?');

define('MODULE_PAYMENT_BILLPAYDEBIT_TESTMODE_TITLE' , 'Transaktionsmodus');
define('MODULE_PAYMENT_BILLPAYDEBIT_TESTMODE_DESC' , 'Im Testmodus werden detailierte Fehlermeldungen angezeigt. F&uuml;r den Produktivbetrieb muss der Livemodus aktiviert werden.');

define('MODULE_PAYMENT_BILLPAYDEBIT_ZONE_TITLE' , 'Steuerzone');
define('MODULE_PAYMENT_BILLPAYDEBIT_ZONE_DESC' , '');

define('MODULE_PAYMENT_BILLPAYDEBIT_API_URL_BASE_TITLE' , 'API url base');
define('MODULE_PAYMENT_BILLPAYDEBIT_API_URL_BASE_DESC' , 'Diese Daten erhalten Sie von Billpay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAYDEBIT_TESTAPI_URL_BASE_TITLE' , 'Test API url base');
define('MODULE_PAYMENT_BILLPAYDEBIT_TESTAPI_URL_BASE_DESC' , 'Diese Daten erhalten Sie von Billpay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAYDEBIT_LOGGING_ENABLE_TITLE' , 'Logging aktiviert');
define('MODULE_PAYMENT_BILLPAYDEBIT_LOGGING_ENABLE_DESC' , 'Sollen Anfragen an die Billpay-Zahlungsschnittstelle in die Logdatei geschrieben werden?');

define('MODULE_PAYMENT_BILLPAYDEBIT_MIN_AMOUNT_TITLE', 'Mindestbestellwert');
define('MODULE_PAYMENT_BILLPAYDEBIT_MIN_AMOUNT_DESC', 'Ab diesem Bestellwert wird die Zahlungsart eingeblendet.');

define('MODULE_PAYMENT_BILLPAYDEBIT_LOGPATH_TITLE', 'Logging Pfad');
define('MODULE_PAYMENT_BILLPAYDEBIT_LOGPATH_DESC', '');

define('MODULE_PAYMENT_BILLPAY_HTTP_X_TITLE', 'X_FORWARDED_FOR erlauben');
define('MODULE_PAYMENT_BILLPAY_HTTP_X_DESC', 'Aktivieren Sie dieses Funktion wenn Ihr Shop in einem Cloud System l&auml;uft.');

// Payment selection texts
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BIRTHDATE', 'Geburtsdatum');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_EULA_CHECK', 'Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung des Rechnungskaufs und einer Identit&auml;ts und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href="https://www.billpay.de/endkunden" target="blank">Billpay GmbH</a> bin ich einverstanden. Es gelten die <a href="https://www.billpay.de/kunden/agb#datenschutz" target="_blank">Datenschutzbestimmungen</a> von Billpay. <br />');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_EULA_CHECK_DE', '<label style="line-height:1.8em" for="billpay_eula">Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung des Rechnungskaufs und einer Identit&auml;ts und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href="https://www.billpay.de/endkunden" target="blank">Billpay GmbH</a> bin ich einverstanden. Es gelten die <a href="https://www.billpay.de/kunden/agb#datenschutz" target="_blank">Datenschutzbestimmungen</a> von Billpay.</label> <br />');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ENTER_BIRTHDATE', 'Bitte geben Sie Ihr Geburtsdatum ein');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ENTER_GENDER', 'Bitte geben Sie Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ENTER_BIRTHDATE_AND_GENDER', 'Bitte geben Sie Ihr Geburtsdatum und Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_NOTE', '');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_REQ', '');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_GENDER', 'Geschlecht');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_MALE', 'm&auml;nnlich');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_FEMALE', 'weiblich');

define('JS_BILLPAYDEBIT_EULA', '* Bitte best%E4tigen Sie die Billpay AGB!\n\n');
define('JS_BILLPAYDEBIT_DOBDAY', '* Bitte geben Sie Ihr Geburtstag ein.\n\n');
define('JS_BILLPAYDEBIT_DOBMONTH', '* Bitte geben Sie Ihr Geburtsmonat.\n\n');
define('JS_BILLPAYDEBIT_DOBYEAR', '* Bitte geben Sie Ihr Geburtsjahr ein.\n\n');
define('JS_BILLPAYDEBIT_GENDER', '* Bitte geben Sie Ihr Geschlecht ein.\n\n');
define('JS_BILLPAYDEBIT_CODE', '* Bitte geben Sie die Bankleitzahl ein.\n\n');
define('JS_BILLPAYDEBIT_NUMBER', '* Bitte geben Sie die Kontonummer ein.\n\n');
define('JS_BILLPAYDEBIT_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.\n\n');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_EULA', '* Bitte best%E4igen Sie die Billpay AGB!');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_BOD' ,'You have entered an incorrect date of birth!');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_DEFAULT', 'Es ist ein interner Fehler aufgetreten. Bitte w&auml;hlen Sie eine andere Zahlart');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_SHORT', 'Es ist ein interner Fehler aufgetreten!');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_CREATED_COMMENT', 'Das Zahlungsziel der Bestellung wurde erfolgreich bei Billpay gestartet');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_CANCEL_COMMENT', 'Die Bestellung wurde erfolgreich bei Billpay storniert');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_DUEDATE', 'Das Zahlungsziel konnte nicht gestartet werden, weil das F%E4lligkeitsdatum leer ist!');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NUMBER', '* Bitte geben Sie die Kontonummer ein.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_CODE', '* Bitte geben Sie die Bankleitzahl ein.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_CREATE_INVOICE', 'Billpay Zahlungsziel jetzt aktivieren?');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_CANCEL_ORDER', 'Billpay Bestellung jetzt stornieren?');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ACCOUNT_HOLDER', 'Kontoinhaber');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ACCOUNT_NUMBER', 'Kontonummer');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BANK_CODE', 'Bankleitzahl');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BANK_NAME', 'Bank');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_REFERENCE', 'Rechnungsnummer');

define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BANKDATA', 'Bitte geben Sie Ihre Bankverbindung ein.');
define('MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1', 'Vielen Dank, dass Sie sich f&uuml;r die Zahlung per Lastschrift mit Billpay entschieden haben.');
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

define('MODULE_PAYMENT_BILLPAYDEBIT_ACTIVATE_ORDER', 'Die Bestellung wurde noch nicht bei Billpay aktiviert. Bitte aktivieren Sie die Bestellung unmittelbar vor der Versendung, in dem Sie den entsprechenden Status setzen.');
define('MODULE_PAYMENT_BILLPAYDEBIT_ACTIVATE_ORDER_WARNING', "<strong style='color:red'>Achtung: Das Zahlungsziel wurde noch nicht bei Billpay gestartet!</strong><br/>");

define('MODULE_PAYMENT_BILLPAYDEBIT_SALUTATION_MALE', 'Herr');
define('MODULE_PAYMENT_BILLPAYDEBIT_SALUTATION_FEMALE', 'Frau');

// BOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
define('MODULE_PAYMENT_BILLPAYDEBIT_NEG_SHIPPING_TITLE', 'Ausschlu&szlig; bei Versandmodulen');
define('MODULE_PAYMENT_BILLPAYDEBIT_NEG_SHIPPING_DESC', 'Dieses Zahlungsmodul deaktivieren wenn Versandmodul gew&auml;hlt (Komma separierte Liste)');
// EOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
?>