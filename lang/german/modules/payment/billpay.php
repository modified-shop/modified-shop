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

/* Default Messages */
define('MODULE_PAYMENT_BILLPAY_TEXT_TITLE', 'Rechnung (Billpay)');
define('MODULE_PAYMENT_BILLPAY_TEXT_DESCRIPTION', 'Rechnung (Billpay)');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_MESSAGE', 'BillPay Error Message');
define('MODULE_PAYMENT_BILLPAY_TEXT_INFO', '<div style="margin-top:6px"><img src="https://www.billpay.de/wp-content/uploads/2011/04/LogoSmall_0.png" alt="Billpay Logo" title="Billpay Logo" /></div>');

define('MODULE_PAYMENT_BILLPAY_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_BILLPAY_ALLOWED_DESC' , 'Geben Sie einzeln die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

define('MODULE_PAYMENT_BILLPAY_LOGGING_TITLE' , 'Absoluter Pfad zur Logdatei');
define('MODULE_PAYMENT_BILLPAY_LOGGING_DESC' , 'Wenn kein Wert eingestellt ist, wird standardm&auml;ssig in das Verzeichnis includes/external/billpay/log geschrieben (Schreibrechte m&uuml;ssen verf&uuml;gbar sein).');

define('MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID_TITLE' , 'Verk&auml;ufer ID');
define('MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID_DESC' , 'Diese Daten erhalten Sie von Billpay');

define('MODULE_PAYMENT_BILLPAY_ORDER_STATUS_TITLE' , 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BILLPAY_ORDER_STATUS_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');

define('MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID_TITLE' , 'Portal ID');
define('MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID_DESC' , 'Diese Daten erhalten Sie von Billpay');

define('MODULE_PAYMENT_BILLPAY_GS_SECURE_TITLE' , 'Security Key');
define('MODULE_PAYMENT_BILLPAY_GS_SECURE_DESC' , 'Diese Daten erhalten Sie von Billpay');

define('MODULE_PAYMENT_BILLPAY_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BILLPAY_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

define('MODULE_PAYMENT_BILLPAY_STATUS_TITLE' , 'Aktiviert');
define('MODULE_PAYMENT_BILLPAY_STATUS_DESC' , 'M&ouml;chten Sie den Rechnungskauf mit Billpay erlauben?');

define('MODULE_PAYMENT_BILLPAY_GS_TESTMODE_TITLE' , 'Transaktionsmodus');
define('MODULE_PAYMENT_BILLPAY_GS_TESTMODE_DESC' , 'Im Testmodus werden detailierte Fehlermeldungen angezeigt. F&uuml;r den Produktivbetrieb muss der Livemodus aktiviert werden.');

define('MODULE_PAYMENT_BILLPAY_ZONE_TITLE' , 'Steuerzone');
define('MODULE_PAYMENT_BILLPAY_ZONE_DESC' , '');

define('MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE_TITLE' , 'API url base');
define('MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE_DESC' , 'Diese Daten erhalten Sie von Billpay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE_TITLE' , 'Test-API url base');
define('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE_DESC' , 'Diese Daten erhalten Sie von Billpay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAY_LOGGING_ENABLE_TITLE' , 'Logging aktiviert');
define('MODULE_PAYMENT_BILLPAY_LOGGING_ENABLE_DESC' , 'Sollen Anfragen an die Billpay-Zahlungsschnittstelle in die Logdatei geschrieben werden?');

define('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT_TITLE', 'Mindestbestellwert');
define('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT_DESC', 'Ab diesem Bestellwert wird die Zahlungsart eingeblendet.');

define('MODULE_PAYMENT_BILLPAY_LOGPATH_TITLE', 'Logging Pfad');
define('MODULE_PAYMENT_BILLPAY_LOGPATH_DESC', '');

define('MODULE_PAYMENT_BILLPAY_GS_HTTP_X_TITLE', 'X_FORWARDED_FOR erlauben');
define('MODULE_PAYMENT_BILLPAY_GS_HTTP_X_DESC', 'Aktivieren Sie dieses Funktion wenn Ihr Shop in einem Cloud System l&auml;uft.');

if (defined('MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT_TITLE') === false) {
    define('MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT_TITLE', 'SEPA Zahlungsverkehr aktivieren');
    define('MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT_DESC', 'Diese Option muss ab 1.2.2014 auf "True" gestellt sein');
}

// Payment selection texts
define('MODULE_PAYMENT_BILLPAY_TEXT_BIRTHDATE', 'Geburtsdatum');
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK', 'Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung des Rechnungskaufs und einer Identit&auml;ts und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href="https://www.billpay.de/endkunden" target="blank">Billpay GmbH</a> bin ich einverstanden. Es gelten die <a href="%s" target="_blank">Datenschutzbestimmungen</a> von Billpay.');
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK_CH', '<label style="line-height:1.8em" for="billpay_eula">Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung des Rechnungskaufs und einer Identit&auml;ts und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href="https://www.billpay.de/endkunden" target="blank">Billpay GmbH</a> bin ich einverstanden. Es gelten die <a href="https://www.billpay.de/kunden/agb-ch#datenschutz" target="_blank">Datenschutzbestimmungen</a> von Billpay.</label> <br />');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE', 'Bitte geben Sie Ihr Geburtsdatum ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_GENDER', 'Bitte geben Sie Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_TITLE', 'Bitte geben Sie Ihre Anrede ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE_AND_GENDER', 'Bitte geben Sie Ihr Geburtsdatum und Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_NOTE', '');
define('MODULE_PAYMENT_BILLPAY_TEXT_REQ', '');
define('MODULE_PAYMENT_BILLPAY_TEXT_GENDER', 'Geschlecht');
define('MODULE_PAYMENT_BILLPAY_TEXT_SALUTATION', 'Anrede');
define('MODULE_PAYMENT_BILLPAY_TEXT_MALE', 'm&auml;nnlich');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEMALE', 'weiblich');
define('MODULE_PAYMENT_BILLPAY_TEXT_MR', 'Herr');
define('MODULE_PAYMENT_BILLPAY_TEXT_MRS', 'Frau');

define('JS_BILLPAY_EULA', '* Bitte best%E4tigen Sie die Billpay AGB!\n\n');
define('JS_BILLPAY_DOBDAY', '* Bitte geben Sie Ihr Geburtstag ein.\n\n');
define('JS_BILLPAY_DOBMONTH', '* Bitte geben Sie Ihr Geburtsmonat.\n\n');
define('JS_BILLPAY_DOBYEAR', '* Bitte geben Sie Ihr Geburtsjahr ein.\n\n');
define('JS_BILLPAY_GENDER', '* Bitte geben Sie Ihr Geschlecht ein.\n\n');

define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_EULA', '* Bitte akzeptieren Sie die Billpay AGB!');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT', 'Es ist ein interner Fehler aufgetreten. Bitte w&auml;len Sie eine andere Zahlart');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_SHORT', 'Es ist ein interner Fehler aufgetreten!');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_CREATED_COMMENT', 'Das Zahlungsziel der Bestellung wurde erfolgreich bei Billpay gestartet.');
define('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_COMMENT', 'Die Bestellung wurde erfolgreich bei Billpay storniert');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DUEDATE', 'Das Zahlungsziel konnte nicht gestartet werden, weil das F%E4lligkeitsdatum leer ist!');

define('MODULE_PAYMENT_BILLPAY_TEXT_CREATE_INVOICE', 'Billpay Zahlungsziel jetzt aktivieren?');
define('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_ORDER', 'Billpay Bestellung jetzt stornieren?');

define('MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER', 'Kontoinhaber');
define('MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER', 'Kontonummer');
define('MODULE_PAYMENT_BILLPAY_TEXT_IBAN', 'IBAN');
define('MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE', 'Bankleitzahl');
define('MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME', 'Bank');
define('MODULE_PAYMENT_BILLPAY_TEXT_BIC', 'BIC');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_REFERENCE', 'Rechnungsnummer');

define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO', 'Bitte &uuml;berweisen Sie den Gesamtbetrag unter Angabe der Billpay-Transaktionsnummer im Verwendungszweck (%1$s) innerhalb der Zahlungsfrist bis zum %2$02s.%3$02s.%4$04s auf das folgende Konto:');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO1', 'Sie haben sich f&uuml;r den Kauf auf Rechnung mit Billpay entschieden. Bitte &uuml;berweisen Sie den Gesamtbetrag bis zum ');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO2', ' auf folgendes Konto: ');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO3', 'F&auml;lligkeitsdatum, das Sie mit der Rechnung erhalten');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL', '<br/>Bitte &uuml;berweisen Sie den Gesamtbetrag unter Angabe der Billpay-Transaktionsnummer im Verwendungszweck (%s) bis zum F&auml;lligkeitsdatum, das Sie mit der Rechnung erhalten, auf das folgende Konto:');

define('MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE', 'Zahlungsziel');

define('MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE', 'Verwendungszweck');

define('MODULE_PAYMENT_BILLPAY_TEXT_ADD', 'zzgl.');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE', 'Geb&uuml;hr');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE_INFO1', 'F&uuml;r diese Bestellung per Rechnung wird eine Geb&uuml;hr von ');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE_INFO2', ' erhoben');

define('MODULE_PAYMENT_BILLPAY_TEXT_SANDBOX', 'Sie befinden sich im Sandbox-Modus:');
define('MODULE_PAYMENT_BILLPAY_TEXT_CHECK', 'Sie befinden sich im Abnahme-Modus:');
define('MODULE_PAYMENT_BILLPAY_UNLOCK_INFO', 'Informationen zur Live-Schaltung');

define('MODULE_PAYMENT_BILLPAY_B2BCONFIG_TITLE', 'Erlaubte Kundenarten');
define('MODULE_PAYMENT_BILLPAY_B2BCONFIG_DESC', 'Wollen Sie die Zahlart f&uuml;r Privatkunden (B2C), Gesch&auml;ftskunden (B2B) oder f&uuml;r beide (BOTH) aktivieren?');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_NAME_TEXT', 'Firmenname');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_LEGAL_FORM_TEXT', 'Rechtsform');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_LEGAL_FORM_SELECT_HTML', "");
define('MODULE_PAYMENT_BILLPAY_B2B_LEGALFORM_VALUES', 'ag:AG (Aktiengesellschaft)|eg:eG (eingetragene Genossenschaft)|ek:EK (eingetragener Kaufmann)|ev:e.V. (eingetragener Verein)|freelancer:Freiberufler/Kleingewerbetreibender/Handelsvertreter|gbr:GbR/BGB (Gesellschaft b&uuml;rgerlichen Rechts)|gmbh:GmbH (Gesellschaft mit beschr&auml;nkter Haftung)|gmbh_ig:GmbH in Gr&uuml;ndung|gmbh_co_kg:GmbH & Co. KG|kg:KG (Kommanditgesellschaft)|ltd:Limited|ltd_co_kg:Limited & Co. KG|ohg:OHG (offene Handelsgesellschaft)|public_inst:&Ouml;ffentliche Einrichtung|misc_capital:Sonstige Kapitalgesellschaft|misc:Sonstige Personengesellschaft|foundation:Stiftung|ug:UG (Unternehmensgesellschaft haftungsbeschr&auml;nkt)');
define('MODULE_PAYMENT_BILLPAY_B2B_REGISTER_NUMBER_TEXT', 'Handelsregisternummer');
define('MODULE_PAYMENT_BILLPAY_B2B_TAX_NUMBER_TEXT', 'Umsatzsteuer-ID');
define('MODULE_PAYMENT_BILLPAY_B2B_HOLDER_NAME_TEXT', 'Name des Inhabers');
define('MODULE_PAYMENT_BILLPAY_B2B_CONTACT_PERSON_TEXT', 'Kontaktperson');

define('MODULE_PAYMENT_BILLPAY_B2B_PRIVATE_CLIENT_TEXT', 'Privatkunde');
define('MODULE_PAYMENT_BILLPAY_B2B_BUSINESS_CLIENT_TEXT', 'Gesch&auml;ftskunde');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_FIELD_EMPTY', 'Bitte geben Sie den Firmenname ein');
define('MODULE_PAYMENT_BILLPAY_B2B_LEGAL_FORM_FIELD_EMPTY', 'Bitte geben Sie die Rechtsform der Firma ein');

define('MODULE_ORDER_TOTAL_BILLPAY_FEE_FROM_TOTAL', 'vom Rechnungsbetrag');

define('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE_TITLE', 'UTF8-Kodierung aktivieren');
define('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE_DESC', 'Deaktivieren Sie diese Option, wenn Sie in Ihrem Online-Shop die UTF-8 Kodierung einsetzen.');

define('MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER', 'Die Bestellung wurde noch nicht bei Billpay aktiviert. Bitte aktivieren Sie die Bestellung unmittelbar vor der Versendung, in dem Sie den entsprechenden Status setzen.');
define('MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER_WARNING', "<strong style='color:red'>Achtung: Das Zahlungsziel wurde noch nicht bei Billpay gestartet!</strong><br/>");

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADDRESS', 'Anpassen der Adresse ist bei Bestellungen mit Billpay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRODUCT', 'Nachbestellen von Artikeln ist bei Bestellungen mit Billpay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PAYMENT', 'Anpassen der Zahlungsart ist bei Bestellungen mit Billpay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_CURRENCY', 'Anpassen der Waehrung ist bei Bestellungen mit Billpay nicht erlaubt');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_QUANTITY', 'Bei Bestellungen mit Billpay darf Artikelmenge nicht negativ sein');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_TAX', 'Anpassen des Steuersatzes bei Bestellungen mit Billpay ist nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRICE', 'Anpassen des Produktpreises bei Bestellungen mit Billpay ist nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ID', 'Anpassen der Produkt-ID bei Bestellungen mit Billpay ist nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ZERO_REDUCTION', 'Bitte geben Sie eine zu stornierende Menge ein');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_REDUCTION', 'Nachbestellen von Artikeln ist bei Bestellungen mit Billpay nicht erlaubt');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_SHIPPING', 'Negative Lieferkosten bei Bestellungen mit Billpay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_INCREASED_SHIPPING', 'Erhoehung der Lieferkosten bei Bestellungen mit Billpay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADDED_SHIPPING', 'Hinzufuegen von Lieferkosten bei Bestellungen mit Billpay nicht erlaubt');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_FORBIDDEN', 'Aktion bei Bestellungen mit Billpay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_PARTIAL_CANCEL_NOT_PROCESSED', 'Achtung! Die Anpassung von Bestellungen ohne Artikelsteuer werden aufgrund eines Fehlers in der Shopsoftware nicht automatisch an Billpay gesendet. Bitte nehmen Sie die Betragsanpassung stattdessen manuell im Billpay-Backoffice (https://admin.billpay.de) vor!');
define('MODULE_PAYMENT_BILLPAY_PARTIAL_CANCEL_ERROR_CUSTOMER_CARE', 'Die Anpassung der Bestellung bei Billpay ist fehlgeschlagen. Bitte wenden Sie sich umgehend an unseren Kundendienst (haendler@billpay.de)!');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADJUST_CHARGEABLE', 'Anpassen einer kostenpflichtigen Produktoption bei Bestellungen mit Billpay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADD_CHARGEABLE', 'Hinzufuegen einer kostenpflichtigen Produktoption bei Bestellungen mit Billpay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_REMOVE_CHARGEABLE', 'Enfernen einer kostenpflichtigen Produktoption bei Bestellungen mit Billpay nicht erlaubt');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_CONTACT_BILLPAY', 'Es ist ein Fehler aufgetreten! Bitte kontaktieren Sie Billpay.');

define('MODULE_PAYMENT_BILLPAY_HISTORY_INFO_PARTIAL_CANCEL', 'Teilstornierung erfolgreich an Billpay gesendet');

define('MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_TEST' , 'Testmodus');
define('MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_LIVE' , 'Livemodus');

define('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_TITLE' , 'Billpay aktiviert');
define('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_TITLE' , 'Billpay storniert');
define('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_TITLE' , 'Billpay Fehler!');

define('MODULE_PAYMENT_BILLPAY_SALUTATION_MALE', 'Herr');
define('MODULE_PAYMENT_BILLPAY_SALUTATION_FEMALE', 'Frau');

// BOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
define('MODULE_PAYMENT_BILLPAY_NEG_SHIPPING_TITLE', 'Ausschlu&szlig; bei Versandmodulen');
define('MODULE_PAYMENT_BILLPAY_NEG_SHIPPING_DESC', 'Dieses Zahlungsmodul deaktivieren wenn Versandmodul gew&auml;hlt (Komma separierte Liste)');
// EOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
?>