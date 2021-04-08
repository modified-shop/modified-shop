<?php

/* Default Messages */
define('MODULE_PAYMENT_BILLPAY_TEXT_TITLE', 'BillPay - Rechnung');
define('MODULE_PAYMENT_BILLPAY_TEXT_DESCRIPTION', 'BillPay - Rechnung');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_MESSAGE', 'BillPay Error Message');
define('MODULE_PAYMENT_BILLPAY_TEXT_INFO', '<div style="margin-top:6px"><img src="https://www.billpay.de/wp-content/uploads/2011/04/LogoSmall_0.png" alt="BillPay Logo" title="BillPay Logo" /></div>');

define('MODULE_PAYMENT_BILLPAY_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_BILLPAY_ALLOWED_DESC' , 'Geben Sie einzeln die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

define('MODULE_PAYMENT_BILLPAY_LOGGING_TITLE' , 'Absoluter Pfad zur Logdatei');
define('MODULE_PAYMENT_BILLPAY_LOGGING_DESC' , 'Wenn kein Wert eingestellt ist, wird standardm&auml;&szlig;ig in das Verzeichnis /includes/external/billpay/log geschrieben (Schreibrechte m&uuml;ssen verf&uuml;gbar sein).');

define('MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID_TITLE' , 'Verk&auml;ufer ID');
define('MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAY_ORDER_STATUS_TITLE' , 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BILLPAY_ORDER_STATUS_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');

define('MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID_TITLE' , 'Portal ID');
define('MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAY_GS_SECURE_TITLE' , 'Security Key');
define('MODULE_PAYMENT_BILLPAY_GS_SECURE_DESC' , 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY_TITLE', '&Ouml;ffentlicher API Schl&uuml;ssel');
define('MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY_DESC', 'Diese Daten erhalten Sie von BillPay');

define('MODULE_PAYMENT_BILLPAY_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BILLPAY_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

define('MODULE_PAYMENT_BILLPAY_STATUS_TITLE' , 'Aktiviert');
define('MODULE_PAYMENT_BILLPAY_STATUS_DESC' , 'M&ouml;chten Sie den Rechnungskauf mit BillPay erlauben?');

define('MODULE_PAYMENT_BILLPAY_GS_TESTMODE_TITLE' , 'Transaktionsmodus');
define('MODULE_PAYMENT_BILLPAY_GS_TESTMODE_DESC' , 'Im Testmodus werden detailierte Fehlermeldungen angezeigt. F&uuml;r den Produktivbetrieb muss der Livemodus aktiviert werden.');

define('MODULE_PAYMENT_BILLPAY_ZONE_TITLE' , 'Steuerzone');
define('MODULE_PAYMENT_BILLPAY_ZONE_DESC' , '');

define('MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE_TITLE' , 'API url base');
define('MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE_DESC' , 'Diese Daten erhalten Sie von BillPay (Achtung! Die URLs f&uuml;r das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE_TITLE' , 'Test-API url base');
define('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE_DESC' , 'Diese Daten erhalten Sie von BillPay (Achtung! Die URLs f&uuml;r das Test- bzw. das Livesystem unterscheiden sich!)');

define('MODULE_PAYMENT_BILLPAY_LOGGING_ENABLE_TITLE' , 'Logging aktiviert');
define('MODULE_PAYMENT_BILLPAY_LOGGING_ENABLE_DESC' , 'Sollen Anfragen an die BillPay-Zahlungsschnittstelle in die Logdatei geschrieben werden?');

define('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT_TITLE', 'Mindestbestellwert');
define('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT_DESC', 'Ab diesem Bestellwert wird die Zahlungsart eingeblendet.');

define('MODULE_PAYMENT_BILLPAY_LOGPATH_TITLE', 'Logging Pfad');
define('MODULE_PAYMENT_BILLPAY_LOGPATH_DESC', '');

define('MODULE_PAYMENT_BILLPAY_GS_HTTP_X_TITLE', 'X_FORWARDED_FOR erlauben');
define('MODULE_PAYMENT_BILLPAY_GS_HTTP_X_DESC', 'Aktivieren Sie diese Funktion wenn Ihr Shop in einem Cloud System l&auml;uft.');

define('MODULE_PAYMENT_BILLPAY_HTTP_X_TITLE', 'X_FORWARDED_FOR erlauben');
define('MODULE_PAYMENT_BILLPAY_HTTP_X_DESC', 'Aktivieren Sie diese Funktion wenn Ihr Shop in einem Cloud System l&auml;uft.');

// Payment selection texts
define('MODULE_PAYMENT_BILLPAY_TEXT_BIRTHDATE', 'Geburtsdatum');
define('MODULE_PAYMENT_BILLPAY_TEXT_PHONE', 'Telefonnummer');
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK',    'Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung des Rechnungskaufs und einer Identit&auml;ts und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href="https://www.billpay.de/endkunden/" target="blank">BillPay GmbH</a> bin ich einverstanden. Es gelten die <a href="%s" target="_blank">Datenschutzbestimmungen</a> von BillPay.');
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK_CH', '<label for="billpay_eula">Hiermit best&auml;tige ich die <a href="https://www.billpay.de/kunden/agb-ch" target="_blank">AGB</a> und die <a href="https://www.billpay.de/kunden/agb-ch#datenschutz" target="_blank">Datenschutzbestimmungen</a> der BillPay GmbH</label> <br />');

define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK_SEPA', "Ich bin mit der &Uuml;bermittlung der f&uuml;r die Abwicklung der Zahlung und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a> einverstanden. Die <a href='%s' target='_blank'>Datenschutzbestimmungen</a> von BillPay.<br/><br/> Ich erteile BillPay ein SEPA-Lastschriftmandat (<a href='#' class='bpy-btn-details'>Details</a>) zum Einzug f&auml;lliger Zahlungen und weise mein Kreditinstitut an, die Lastschriften einzuziehen.");
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK_SEPA_AT', "Ich bin mit der &Uuml;bermittlung der f&uuml;r die Abwicklung der Zahlung und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an die <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a> einverstanden. Die <a href='%s' target='_blank'>Datenschutzbestimmungen</a> von BillPay.<br/><br/> Ich erteile BillPay und der <a href='https://www.privatbank1891.com/' target='_blank'>net-m privatbank 1891 AG</a> ein SEPA-Lastschriftmandat (<a href='#' class='bpy-btn-details'>Details</a>) zum Einzug offener Zahlungen und weise mein Kreditinstitut an, die Lastschriften einzuziehen.");

define('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE_TITLE', 'UTF8-Kodierung aktivieren');
define('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE_DESC', 'Deaktivieren Sie diese Option, wenn Sie in Ihrem Online-Shop die UTF-8 Kodierung einsetzen.');

define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE', 'Bitte geben Sie Ihr Geburtsdatum ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_GENDER', 'Bitte geben Sie Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_TITLE', 'Bitte geben Sie Ihre Anrede ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE_AND_GENDER', 'Bitte geben Sie Ihr Geburtsdatum und Ihr Geschlecht ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_PHONE', 'Bitte geben Sie Ihre Telefonnummer ein');
define('MODULE_PAYMENT_BILLPAY_TEXT_NOTE', '');
define('MODULE_PAYMENT_BILLPAY_TEXT_REQ', '');
define('MODULE_PAYMENT_BILLPAY_TEXT_GENDER', 'Geschlecht');
define('MODULE_PAYMENT_BILLPAY_TEXT_SALUTATION', 'Anrede');
define('MODULE_PAYMENT_BILLPAY_TEXT_MALE', 'm&auml;nnlich');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEMALE', 'weiblich');
define('MODULE_PAYMENT_BILLPAY_TEXT_MR', 'Herr');
define('MODULE_PAYMENT_BILLPAY_TEXT_MRS', 'Frau');

define('JS_BILLPAY_EULA', '* Bitte best&auml;tigen Sie die BillPay AGB!\n\n');
define('JS_BILLPAY_DOBDAY', '* Bitte geben Sie Ihr Geburtstag ein.\n\n');
define('JS_BILLPAY_DOBMONTH', '* Bitte geben Sie Ihr Geburtsmonat.\n\n');
define('JS_BILLPAY_DOBYEAR', '* Bitte geben Sie Ihr Geburtsjahr ein.\n\n');
define('JS_BILLPAY_GENDER', '* Bitte geben Sie Ihr Geschlecht ein.\n\n');

define('JS_BILLPAY_CODE', '* Bitte geben Sie die BIC ein.\n\n');
define('JS_BILLPAY_NUMBER', '* Bitte geben Sie die IBAN ein.\n\n');
define('JS_BILLPAY_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.\n\n');
define('JS_BILLPAY_PHONE', '* Bitte geben Sie Ihre Telefonnummer ein.\n\n');

define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_NUMBER', '* Bitte geben Sie die IBAN ein.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_CODE', '* Bitte geben Sie die BIC ein.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_NAME', '* Bitte geben Sie den Namen des Kontoinhabers ein.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_PHONE', '* Bitte geben Sie Ihre Telefonnummer ein.');

define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_EULA', '* Bitte akzeptieren Sie die BillPay AGB!');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DOB', '* Bitte geben Sie Ihr Geburtsdatum ein.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DOB_UNDER', 'Sie m&uuml;ssen &uuml;ber 18 Jahre alt sein um BillPay zu nutzen.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT', 'Es ist ein interner Fehler aufgetreten. Bitte w&auml;len Sie eine andere Zahlart');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_SHORT', 'Es ist ein interner Fehler aufgetreten!');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_CREATED_COMMENT', 'Das Zahlungsziel der Bestellung wurde erfolgreich bei BillPay gestartet.');
define('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_COMMENT', 'Die Bestellung wurde erfolgreich bei BillPay storniert');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DUEDATE', 'Das Zahlungsziel konnte nicht gestartet werden, weil das F&auml;lligkeitsdatum leer ist!');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_NO_RATEPLAN', 'Bitte fordern Sie einen Ratenplan f&uuml;r die ausgew&auml;lte Anzahl Raten an.');

define('MODULE_PAYMENT_BILLPAY_TEXT_CREATE_INVOICE', 'BillPay Zahlungsziel jetzt aktivieren?');
define('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_ORDER', 'BillPay Bestellung jetzt stornieren?');

define('MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER', 'Kontoinhaber');
define('MODULE_PAYMENT_BILLPAY_TEXT_IBAN', 'IBAN');
define('MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME', 'Bank');
define('MODULE_PAYMENT_BILLPAY_TEXT_BIC', 'BIC');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_REFERENCE', 'Rechnungsnummer');

define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO',              'Bitte &uuml;berweisen Sie den Gesamtbetrag unter Angabe der BillPay-Transaktionsnummer im Verwendungszweck (%1$s) innerhalb der Zahlungsfrist bis zum %2$02s.%3$02s.%4$04s auf das folgende Konto:');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_NO_DUEDATE',   'Bitte &uuml;berweisen Sie den Gesamtbetrag unter Angabe der BillPay-Transaktionsnummer im Verwendungszweck (%1$s) innerhalb der Zahlungsfrist bis zum F&auml;lligkeitsdatum, das Sie mit der Rechnung erhalten auf das folgende Konto:');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO1', 'Sie haben sich f&uuml;r den Kauf auf Rechnung mit BillPay entschieden. Bitte &uuml;berweisen Sie den Gesamtbetrag bis zum ');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO2', ' auf folgendes Konto: ');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO3', 'F&auml;lligkeitsdatum, das Sie mit der Rechnung erhalten');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL', '<br/>Bitte &uuml;berweisen Sie den Gesamtbetrag unter Angabe der BillPay-Transaktionsnummer im Verwendungszweck (%s) bis zum F&auml;lligkeitsdatum, das Sie mit der Rechnung erhalten, auf das folgende Konto:');

define('MODULE_PAYMENT_BILLPAY_TEXT_BANKDATA', 'Bitte geben Sie Ihre Bankverbindung ein.');

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
define('MODULE_PAYMENT_BILLPAY_B2B_LEGALFORM_VALUES', 'ag:AG (Aktiengesellschaft)|eg:eG (eingetragene Genossenschaft)|ek:EK (eingetragener Kaufmann)|ev:e.V. (eingetragener Verein)|freelancer:Freiberufler/Kleingewerbetreibender/Handelsvertreter|gbr:GbR/BGB (Gesellschaft b&uuml;rgerlichen Rechts)|gmbh:GmbH (Gesellschaft mit beschr&auml;nkter Haftung)|gmbh_ig:GmbH in Gr&uuml;ndung|gmbh_co_kg:GmbH &amp; Co. KG|kg:KG (Kommanditgesellschaft)|ltd:Limited|ltd_co_kg:Limited &amp; Co. KG|ohg:OHG (offene Handelsgesellschaft)|public_inst:&Ouml;ffentliche Einrichtung|misc_capital:Sonstige Kapitalgesellschaft|misc:Sonstige Personengesellschaft|foundation:Stiftung|ug:UG (Unternehmensgesellschaft haftungsbeschr&auml;nkt)');
define('MODULE_PAYMENT_BILLPAY_B2B_REGISTER_NUMBER_TEXT', 'Handelsregisternummer');
define('MODULE_PAYMENT_BILLPAY_B2B_TAX_NUMBER_TEXT', 'Umsatzsteuer-ID');
define('MODULE_PAYMENT_BILLPAY_B2B_HOLDER_NAME_TEXT', 'Name des Inhabers');
define('MODULE_PAYMENT_BILLPAY_B2B_CONTACT_PERSON_TEXT', 'Kontaktperson');

define('MODULE_PAYMENT_BILLPAY_B2B_CHOOSE_CLIENT_TEXT', 'Kundentyp');
define('MODULE_PAYMENT_BILLPAY_B2B_PRIVATE_CLIENT_TEXT', 'Privatkunde');
define('MODULE_PAYMENT_BILLPAY_B2B_BUSINESS_CLIENT_TEXT', 'Gesch&auml;ftskunde');

define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_FIELD_EMPTY', 'Bitte geben Sie den Firmenname ein');
define('MODULE_PAYMENT_BILLPAY_B2B_LEGAL_FORM_FIELD_EMPTY', 'Bitte geben Sie die Rechtsform der Firma ein');
define('MODULE_PAYMENT_BILLPAY_B2B_HOLDER_NAME_EMPTY', 'Bitte geben Sie den Namen des Inhabers ein');
define('MODULE_PAYMENT_BILLPAY_B2B_REGISTER_NUMBER_EMPTY', 'Bitte geben Sie die Handelsregisternummer ein');
define('MODULE_PAYMENT_BILLPAY_B2B_TAX_NUMBER_EMPTY', 'Bitte geben Sie die Umsatzsteuer-ID ein');


defined('MODULE_ORDER_TOTAL_BILLPAY_FEE_FROM_TOTAL') OR define('MODULE_ORDER_TOTAL_BILLPAY_FEE_FROM_TOTAL', 'vom Rechnungsbetrag');

define('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE_TITLE', 'Locale-Kodierung');
define('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE_DESC', 'Nutzt Ihre Seite Locale-Kodierung (anders als UTF-8)?');


define('MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER', 'Die Bestellung wurde noch nicht bei BillPay aktiviert. Bitte aktivieren Sie die Bestellung unmittelbar vor der Versendung, in dem Sie den entsprechenden Status setzen.');
define('MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER_WARNING', "Achtung: Das Zahlungsziel wurde noch nicht bei BillPay gestartet!");

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADDRESS', 'Anpassen der Adresse ist bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRODUCT', 'Nachbestellen von Artikeln ist bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PAYMENT', 'Anpassen der Zahlungsart ist bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_CURRENCY', 'Anpassen der Waehrung ist bei Bestellungen mit BillPay nicht erlaubt');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_HIGHER_QUANTITY', 'Sie k&ouml;nnen mehr Produkte als in Originalreihenfolge mit BillPay nicht bestellen');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_QUANTITY', 'Bei Bestellungen mit BillPay darf Artikelmenge nicht negativ sein');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_TAX', 'Anpassen des Steuersatzes bei Bestellungen mit BillPay ist nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRICE', 'Anpassen des Produktpreises bei Bestellungen mit BillPay ist nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ID', 'Anpassen der Produkt-ID bei Bestellungen mit BillPay ist nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ZERO_REDUCTION', 'Bitte geben Sie eine zu stornierende Menge ein');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_REDUCTION', 'Nachbestellen von Artikeln ist bei Bestellungen mit BillPay nicht erlaubt');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_SHIPPING', 'Negative Lieferkosten bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_INCREASED_SHIPPING', 'Erhoehung der Lieferkosten bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADDED_SHIPPING', 'Hinzufuegen von Lieferkosten bei Bestellungen mit BillPay nicht erlaubt');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_FORBIDDEN', 'Aktion bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_PARTIAL_CANCEL_NOT_PROCESSED', 'Achtung! Die Anpassung von Bestellungen ohne Artikelsteuer werden aufgrund eines Fehlers in der Shopsoftware nicht automatisch an BillPay gesendet. Bitte nehmen Sie die Betragsanpassung stattdessen manuell im BillPay-Backoffice (https://admin.billpay.de) vor!');
define('MODULE_PAYMENT_BILLPAY_PARTIAL_CANCEL_ERROR_CUSTOMER_CARE', 'Die Anpassung der Bestellung bei BillPay ist fehlgeschlagen. Bitte wenden Sie sich umgehend an unseren Kundendienst (haendler@billpay.de)!');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADJUST_CHARGEABLE', 'Anpassen einer kostenpflichtigen Produktoption bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADD_CHARGEABLE', 'Hinzufuegen einer kostenpflichtigen Produktoption bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_REMOVE_CHARGEABLE', 'Enfernen einer kostenpflichtigen Produktoption bei Bestellungen mit BillPay nicht erlaubt');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_GENERAL', 'Sie k&ouml;nnen das nicht f&uuml;r BillPay Zahlungsmethode.');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_CONTACT_BILLPAY', 'Es ist ein Fehler aufgetreten! Bitte kontaktieren Sie BillPay.');

define('MODULE_PAYMENT_BILLPAY_HISTORY_INFO_PARTIAL_CANCEL', 'Teilstornierung erfolgreich an BillPay gesendet');
define('MODULE_PAYMENT_BILLPAY_HISTORY_INFO_EDIT_CART_CONTENT', '&Auml;nderung der Bestellung wurde erfolgreich an BillPay gesendet');

define('MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_TEST' , 'Testmodus');
define('MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_LIVE' , 'Livemodus');

// -- Order States
// waiting for prepayment or decision
define('MODULE_PAYMENT_BILLPAY_STATUS_PENDING_TITLE_EN' , 'BillPay pending');
define('MODULE_PAYMENT_BILLPAY_STATUS_PENDING_TITLE_DE' , 'BillPay nicht abgeschlossen');

// ready to activate
define('MODULE_PAYMENT_BILLPAY_STATUS_APPROVED_TITLE_EN' , 'BillPay approved');
define('MODULE_PAYMENT_BILLPAY_STATUS_APPROVED_TITLE_DE' , 'BillPay genehmigt');

// invoice created
define('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_TITLE_EN' , 'BillPay activated');
define('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_TITLE_DE' , 'BillPay aktiviert');

// order cancelled or timed out from pending
define('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_TITLE_EN' , 'BillPay cancelled');
define('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_TITLE_DE' , 'BillPay storniert');

// error in order
define('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_TITLE_EN' , 'BillPay error!');
define('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_TITLE_DE' , 'BillPay Fehler!');
// -- end of Order States


define('MODULE_PAYMENT_BILLPAY_STATUS_PENDING_DESC', 'BillPay - Warte auf Best&auml;tigung');
define('MODULE_PAYMENT_BILLPAY_STATUS_APPROVED_DESC', 'BillPay - Best&auml;tigt');
define('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_DESC', 'BillPay - Aktiviert');
define('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_DESC', 'BillPay - Storniert');
define('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_DESC', 'BillPay - Aufgrund eines Fehlers ben&ouml;tigt diese Bestellung eine manuelle Korrektur. Bitte kontaktieren Sie den BillPay Support.');


define('MODULE_PAYMENT_BILLPAY_SALUTATION_MALE', MODULE_PAYMENT_BILLPAY_TEXT_MR);
define('MODULE_PAYMENT_BILLPAY_SALUTATION_FEMALE', MODULE_PAYMENT_BILLPAY_TEXT_MRS);

define('MODULE_PAYMENT_BILLPAY_TEXT_SEPA_INFORMATION',    "Die Gl&auml;ubiger-Identifikationsnummer von BillPay ist DE19ZZZ00000237180. Die Mandatsreferenznummer wird mir zu einem sp&auml;teren Zeitpunkt per Email zusammen mit einer Vorlage f&uuml;r ein schriftliches Mandat mitgeteilt. Ich werde zus&auml;tzlich dieses schriftliche Mandat unterschreiben und an BillPay senden.<br/><br/>Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Geldinstitut vereinbarten Bedingungen. Bitte beachten Sie, dass die f&auml;llige Forderung auch bei einer R&uuml;cklastschrift bestehen bleibt. For more information visit <a href='https://www.billpay.de/sepa' target='_blank'>https://www.billpay.de/sepa</a>.");
define('MODULE_PAYMENT_BILLPAY_TEXT_SEPA_INFORMATION_AT', "Die Gl&auml;ubiger-Identifikationsnummer von BillPay ist DE19ZZZ00000237180, die Gl&auml;ubiger-Identifikationsnummer der net-m privatbank AG ist DE62ZZZ00000009232. Die Mandatsreferenznummer wird mir zu einem sp&auml;teren Zeitpunkt per Email zusammen mit einer Vorlage f&uuml;r ein schriftliches Mandat mitgeteilt. Ich werde zus&auml;tzlich dieses schriftliche Mandat unterschreiben und an BillPay senden.<br/><br/>Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Geldinstitut vereinbarten Bedingungen. Bitte beachten Sie, dass die f&auml;llige Forderung auch bei einer R&uuml;cklastschrift bestehen bleibt. F&uuml;r weitere Informationen besuchen <a href='https://www.billpay.de/sepa' target='_blank'>https://www.billpay.de/sepa</a>.");

// Plugin 1.7
define('MODULE_PAYMENT_BILLPAY_THANK_YOU_TEXT', 'Vielen Dank, dass Sie sich beim Kauf der Ware f&uuml;r die BillPay Rechnung entschieden haben.');
define('MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT', 'Bitte &uuml;berweisen Sie den Betrag von %1$s %2$s bis zum %3$s unter Angabe des Verwendungszwecks auf folgendes Konto:');
define('MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT_NO_DUE_DATE', 'Bitte &uuml;berweisen Sie den Betrag von %1$s %2$s innerhalb der Zahlungsfrist unter Angabe des Verwendungszwecks auf folgendes Konto.');
define('MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT_ADD_CH', 'Bei Einzahlung am Postschalter entstehen zus&auml;tzliche Geb&uuml;hren. Bitte &uuml;berweisen Sie bei Einzahlung per Einzahlungsschein zus&auml;tzlich %1$s %2$s.');
define('MODULE_PAYMENT_BILLPAY_TEXT_PAYEE', 'Zahlungsempf&auml;nger');
define('MODULE_PAYMENT_BILLPAY_TEXT_PAYEE_CH', 'Zweigniederlassung Schweiz (Regensdorf)');
define('MODULE_PAYMENT_BILLPAY_TEXT_IBAN_CH', 'Kontonummer');
define('MODULE_PAYMENT_BILLPAY_TEXT_BIC_CH', 'BC-Nummer');
define('MODULE_PAYMENT_BILLPAY_TEXT_BANK', 'Kreditinstitut');
define('MODULE_PAYMENT_BILLPAY_TEXT_TOTAL_AMOUNT', 'Betrag');
define('MODULE_PAYMENT_BILLPAY_UPDATE_AVAILABLE', 'Version %2s$ des BillPay Zahlarten Plugins ist verf&uuml;gbar (aktuell installiert: %1s$). Klicken Sie <a href="%3s$">hier</a> zum Download.');
