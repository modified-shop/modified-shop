<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// error messages
define('NOTE_ADDRESS_CHANGED', 'Die Adresse hat sich ge&auml;ndert.');
define('ADDRESSES_MUST_BE_EQUAL','Bei der gew&auml;hlten Zahlungsart m&uuml;ssen Rechnungs- und Lieferadresse &uuml;bereinstimmen!');
define('INSTALLMENT_TYPE_NOT_SELECTED', 'Keinen Typ gew&auml;hlt.');
define('PAYDATA_INCOMPLETE', 'Die Angaben zur Zahlungsweise sind unvollst&auml;ndig.');
define('PAYMENT_ERROR', 'Es ist ein Fehler bei der Verarbeitung aufgetreten.');
define('ERROR_MUST_CONFIRM_MANDATE', 'Bitte best&auml;tigen Sie, dass Sie das SEPA-Lastschriftmandat erteilen m&ouml;chten.');

// credit risk check
define('CREDIT_RISK_HEADING', 'Bonit&auml;tspr&uuml;fung');
define('BUTTON_CONFIRM', 'Ja, Pr&uuml;fung durchf&uuml;hren');
define('BUTTON_NOCONFIRM', 'Nein, keine Pr&uuml;fung durchf&uuml;hren');
define('TEXT_CREDIT_RISK_INFO', 'Es wird eine Bonit&auml;tsabfrage durchgef&uuml;hrt.');
define('TEXT_CREDIT_RISK_COMFIRM', 'M&ouml;chten Sie dem zustimmen?');
define('CREDIT_RISK_FAILED', 'Bitte w&auml;hlen Sie eine andere Zahlungsweise.');
define('CREDIT_RISK_CONFIGURATION', 'Bonit&auml;tspr&uuml;fung');
define('CR_ACTIVE', 'aktiv');
define('CR_OPERATING_MODE', 'Betriebsmodus');
define('CR_TIMEOFCHECK', 'Zeitpunkt der Pr&uuml;fung');
define('CR_TIMEOFCHECK_BEFORE', 'vor der Zahlartauswahl');
define('CR_TIMEOFCHECK_AFTER', 'nach der Zahlartauswahl');
define('CR_TYPEOFCHECK', 'Pr&uuml;fungsart');
define('CR_TYPEOFCHECK_ISCOREHARD', 'Infoscore (harte Kriterien)');
define('CR_TYPEOFCHECK_ISCOREALL', 'Infoscore (alle Merkmale)');
define('CR_TYPEOFCHECK_ISCOREBSCORE', 'Infoscore (alle Merkmale + Boniscore');
define('CR_NEWCLIENTDEFAULT', 'Standardwert f&uuml;r Neukunden');
define('CR_VALIDITY', 'G&uuml;ltigkeit');
define('CR_MIN_CART_VALUE', 'minimaler Warenwert');
define('CR_MAX_CART_VALUE', 'maximaler Warenwert');
define('CR_CHECKFORGENRE', 'Pr&uuml;fung bei');
define('CR_ERROR_MODE', 'Fehlerverhalten');
define('CR_ERROR_MODE_ABORT', 'Vorgang abbrechen');
define('CR_ERROR_MODE_CONTINUE', 'fortfahren');
define('CR_NOTICE', 'Hinweistext');
define('CR_CONFIRMATION', 'Zustimmungsabfrage');
define('CR_ABTEST', 'A/B-Test');

// address check
define('TEXT_ADDRESS_CHECK_HEADING', 'Adresse korrigieren');
define('TEXT_ADDRESS_CHECK_CHANGED', 'korrigiert');
define('AC_ACTIVE', 'aktiv');
define('AC_OPERATING_MODE', 'Betriebsmodus');
define('AC_BILLING_ADDRESS', 'Rechnungsadresse');
define('AC_DELIVERY_ADDRESS', 'Lieferadresse');
define('AC_AUTOMATIC_CORRECTION', 'Automatische Korrektur');
define('AC_ERROR_MODE', 'Fehlerverhalten');
define('AC_MIN_CART_VALUE', 'Minimaler Warenwert');
define('AC_MAX_CART_VALUE', 'Maximaler Warenwert');
define('AC_VALIDITY', 'G&uuml;ltigkeit');
define('AC_ERROR_MESSAGE', 'Fehlermeldung');
define('AC_PSTATUS_MAPPING', 'Personenstatusmapping');
define('AC_BACHECK_NONE', 'nicht pr&uuml;fen');
define('AC_BACHECK_BASIC', 'Basis');
define('AC_BACHECK_PERSON', 'Person (nur DE!)');
define('AC_AUTOMATIC_CORRECTION_NO', 'nein');
define('AC_AUTOMATIC_CORRECTION_YES', 'ja');
define('AC_AUTOMATIC_CORRECTION_USER', 'Benutzerentscheidung');
define('AC_ERROR_MODE_ABORT', 'Vorgang abbrechen');
define('AC_ERROR_MODE_REENTER', 'Neueingabe');
define('AC_ERROR_MODE_CHECK', 'Anschließende Bonit&auml;tspr&uuml;fung durchf&uuml;hren');
define('AC_ERROR_MODE_CONTINUE', 'fortfahren');
define('DAYS', 'Tage');
define('ERROR_MESSAGE_INFO', 'Verwenden Sie {payone_error} als Platzhalter f&uuml;r die R&uuml;ckmeldung der PAYONE-Plattform');
define('AC_PSTATUS_NOPCHECK', 'keine Personenpr&uuml;fung durchgef&uuml;hrt');
define('AC_PSTATUS_FULLNAMEKNOWN', 'Vor- und Nachname bekannt');
define('AC_PSTATUS_LASTNAMEKNOWN', 'Nachname bekannt');
define('AC_PSTATUS_NAMEUNKNOWN', 'Vor- und Nachname nicht bekannt');
define('AC_PSTATUS_NAMEADDRAMBIGUITY', 'Mehrdeutigkeit bei Name zu Anschrift');
define('AC_PSTATUS_UNDELIVERABLE', 'nicht (mehr) zustellbar');
define('AC_PSTATUS_DEAD', 'Person verstorben');
define('AC_PSTATUS_POSTALERROR', 'Adresse postalisch falsch');

// api
define('STATUS_UPDATED_BY_PAYONE', 'Status aktualisiert durch PAYONE');
define('COMMENT_ERROR', 'comment_error');
define('COMMENT_REDIRECTION_INITIATED', 'comment_redirection_initiated');
define('COMMENT_AUTH_APPROVED', 'Zahlung bewilligt');
define('COMMENT_PREAUTH_APPROVED', 'Zahlung bewilligt');
define('VOUCHER_OR_DISCOUNT', 'voucher_or_discount');
define('MISC_HANDLING', 'misc_handling');
define('SHIPPING_COST', 'shipping_cost');

// payment
define('paymenttype_visa', 'Visa');
define('paymenttype_mastercard', 'Mastercard');
define('paymenttype_amex', 'American Express');
define('paymenttype_cartebleue', 'Carte Bleue');
define('paymenttype_dinersclub', 'Diners Club');
define('paymenttype_discover', 'Discover');
define('paymenttype_jcb', 'JCB');
define('paymenttype_maestro', 'Maestro');
define('paymenttype_billsafe', 'BillSAFE');
define('paymenttype_klarna', 'Klarna');
define('paymenttype_commerzfinanz', 'CommerzFinanz');
define('paymenttype_lastschrift', 'Lastschrift');
define('paymenttype_invoice', 'Rechnung');
define('paymenttype_prepay', 'Vorkasse');
define('paymenttype_cod', 'Nachnahme');
define('paymenttype_paypal', 'PayPal');
define('paymenttype_sofortueberweisung', 'Sofort&uuml;berweisung');
define('paymenttype_giropay', 'GiroPay');
define('paymenttype_eps', 'EPS');
define('paymenttype_pfefinance', 'Post-Finance EFinance');
define('paymenttype_pfcard', 'Post-Finance Card');
define('paymenttype_ideal', 'iDEAL');

// payment form
define('selection_type', 'Zahlungsweise:');
define('customers_dob', 'Geburtsdatum (TT.MM.JJJJ):');
define('customers_telephone', 'Telefon:');
define('personalid', 'Personal ID:');
define('addressaddition', 'Adresszusatz:');

// installment
define('TEXT_KLARNA_CONFIRM', 'Mit der Datenverarbeitung der f&uuml;r die Abwicklung des Rechnungskaufes und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten durch Klarna bin ich einverstanden. Meine %s kann ich jederzeit mit Wirkung f&uuml;r die Zukunft widerrufen. Es gelten die AGB des H&auml;ndlers.');
define('TEXT_KLARNA_ERROR_CONDITIONS', 'Sofern Sie Rechnungsbedingungen von Klarna nicht akzeptieren, können wir Ihre Bestellung bedauerlicherweise nicht entgegennehmen!');
define('TEXT_KLARNA_INVOICE', 'Weitere Informationen zum Rechnungskauf finden sie in den');
define('KLARNA_STOREID', 'Klarna SoreID');
define('KLARNA_COUNTRIES', 'Klarna L&auml;nder');

// otrans
define('onlinetransfer_type', 'Typ:');
define('bankaccountholder', 'Kontoinhaber:');
define('iban', 'IBAN:');
define('bic', 'BIC:');
define('ideal', 'Bankgruppe:');
define('eps', 'Bankgruppe:');
define('bankaccount', 'Kontonummer:');
define('bankcode', 'Bankleitzahl:');

// ELV
define('SEPA_MANDATE_HEADING', 'SEPA-Lastschrift');
define('SEPA_MANDATE_INFO', 'Damit wir den Betrag per Lastschrift von Ihrem Konto einziehen k&ouml;nnen, ben&ouml;tigen wir von Ihnen ein SEPA-Lastschriftmandat.');
define('SEPA_MANDATE_CONFIRM_LABEL', 'Ich m&ouml;chte das Mandat erteilen (elektronische &Uuml;bermittlung)');
define('NOTE_GERMAN_ACCOUNT', 'oder bezahlen Sie wie gewohnt mit ihren bekannten Kontodaten (nur f&uuml;r deutsche Kontoverbindungen)');
define('ELV_IBAN', 'IBAN:');
define('ELV_BIC', 'BIC:');
define('ELV_ACCOUNT_HOLDER', 'Kontoinhaber:');
define('ELV_BANKCODE', 'Bankleitzahl:');
define('ELV_ACCOUNT_NUMBER', 'Kontonummer:');
define('ELV_COUNTRY', 'Land:');
define('ELV_COUNTRY_DE', 'Deutschland');
define('ELV_COUNTRY_AT', '&Ouml;sterreich');
define('ELV_COUNTRY_NL', 'Niederlande');
define('SEPA_COUNTRIES', 'Kontol&auml;nder f&uuml;r SEPA-Lastschrift');
define('SEPA_DISPLAY_KTOBLZ', 'Zusatzfelder Konto/BLZ');
define('SEPA_DISPLAY_KTOBLZ_NOTE', 'Bei SEPA-Lastschrift zus&auml;tzlich Felder f&uuml;r Kontonummer/Bankleitzahl anzeigen (nur dt. Konten)');
define('SEPA_USE_MANAGEMANDATE', 'Mandatserteilung aktivieren');
define('SEPA_USE_MANAGEMANDATE_NOTE', 'Die Mandatserteilung erfolgt mit dem kostenpflichtigen Request "managemandate". Der Request beinhaltet einen bankaccountcheck. Allerdings ist hier keine Abfrage der POS-Sperrliste m&ouml;glich.');
define('SEPA_DOWNLOAD_PDF', 'Download Mandat als PDF');
define('SEPA_DOWNLOAD_PDF_NOTE', 'Download des SEPA-Lastschriftmandats als PDF-Datei anbieten (nur, wenn bei PAYONE das Produkt "SEPA-Mandate als PDF" gebucht wurde)');
define('DOWNLOAD_MANDATE_HERE', 'Das im Zuge der SEPA-Lastschriftzahlung erteilte Mandat k&ouml;nnen Sie jetzt herunterladen: ');
define('MANDATE_PDF', 'PDF-Datei');
define('CHECK_BANKDATA', 'Kontodaten pr&uuml;fen');
define('DONT_CHECK', 'nicht pr&uuml;fen');
define('CHECK_BASIC', 'Basis');
define('CHECK_POS', 'mit POS-Sperrliste');

// cc
define('TEXT_CARDOWNER', 'Karteninhaber:');
define('TEXT_CARDTYPE', 'Kartentyp:');
define('TEXT_CARDNO', 'Kartennummer:');
define('TEXT_CARDEXPIRES', 'G&uuml;ltig bis (Monat / Jahr):');
define('TEXT_CARDCHECKNUM', 'Pr&uuml;fziffer:');

// orders status
define('ORDERS_STATUS_CONFIGURATION', 'Konfiguration der Bestellstatus');
define('ORDERS_STATUS_TMP', 'tempor&auml;rer Status');
define('ORDERS_STATUS_PENDING', 'Zahlungseingang unsicher/erwartet');
define('ORDERS_STATUS_PAID', 'Zahlung erfolgreich');
define('ORDERS_STATUS_DENIED', 'Zahlung fehlgeschlagen/abgelehnt');
define('ORDERS_STATUS_APPROVED', 'Zahlung bewilligt');
define('ORDERS_STATUS_APPOINTED', 'Zahlung angek&uuml;ndigt');
define('ORDERS_STATUS_CAPTURE', 'Zahlung Capture');
define('ORDERS_STATUS_UNDERPAID', 'Zahlung zu gering');
define('ORDERS_STATUS_CANCELATION', 'Zahlung storniert');
define('ORDERS_STATUS_REFUND', 'Zahlung Gutschrift');
define('ORDERS_STATUS_DEBIT', 'Zahlung Einzug');
define('ORDERS_STATUS_TRANSFER', 'Zahlung &Uuml;berweisung');
define('ORDERS_STATUS_REMINDER', 'Zahlung Erinnerung');
define('ORDERS_STATUS_VAUTHORIZATION', 'Zahlung vAuth');
define('ORDERS_STATUS_VSETTLEMENT', 'Zahlung vSettlement');
define('ORDERS_STATUS_INVOICE', 'Zahlung Rechnung');
define('TEXT_EXTERN_CALLBACK_URL', 'URL Statusweiterleitung');
define('TEXT_EXTERN_CALLBACK_TIMEOUT', 'Timeout');

// global
define('TEXT_YES', 'Ja');
define('TEXT_NO', 'Nein');
define('ERROR_OCCURED', 'Fehler aufgetreten');
define('BOX_PAYONE_CONFIG', 'PAYONE Konfiguration');
define('BOX_PAYONE_LOGS', 'PAYONE API Log');
define('PAYONE_CONFIG_TITLE', 'PAYONE Konfiguration');
define('PAYMENT_CONFIGURATION', 'Zahlungskonfiguration');
define('GLOBAL_CONFIGURATION', 'Globale Parameter');
define('MERCHANT_ID', 'Merchant-ID');
define('PORTAL_ID', 'Portal-ID');
define('SUBACCOUNT_ID', 'Unterkonto-ID');
define('KEY', 'Schl&uuml;ssel');
define('OPERATING_MODE', 'Betriebsmodus');
define('OPMODE_TEST', 'Testmodus');
define('OPMODE_LIVE', 'Live-Modus');
define('AUTHORIZATION_METHOD', 'Autorisierungsmethode');
define('AUTHMETHOD_AUTH', 'Sofortautorisierung');
define('AUTHMETHOD_PREAUTH', 'Vorautorisierung');
define('SEND_CART', 'Warenkorb &uuml;bertragen');
define('SENDCART_TRUE', 'ja');
define('SENDCART_FALSE', 'nein');

// payment genre
define('PAYMENT_GENRE', 'Zahlungsart');
define('PAYMENTGENRE_CONFIGURATION', 'Konfiguration der Zahlungsarten');
define('PG_ACTIVE', 'aktiv');
define('PG_ORDER', 'Reihenfolge');
define('PG_NAME', 'Interner Name');
define('PG_MIN_CART_VALUE', 'minimaler Warenwert');
define('PG_MAX_CART_VALUE', 'maximaler Warenwert');
define('PG_OPERATING_MODE', 'Betriebsmodus');
define('PG_GLOBAL_OVERRIDE', 'globale Parameter &uuml;berschreiben');
define('PG_COUNTRIES', 'aktive L&auml;nder');
define('PG_SCORING_ALLOWED', 'zugelassene Ampelwerte');
define('PG_RED', 'rot');
define('PG_YELLOW', 'gelb');
define('PG_GREEN', 'gr&uuml;n');
define('PG_PAYMENT_TYPES', 'Zahlarttypen');
define('PG_PAYMENTTYPE_VISA', 'Visa');
define('PG_PAYMENTTYPE_MASTERCARD', 'Mastercard');
define('PG_PAYMENTTYPE_AMEX', 'American Express');
define('PG_PAYMENTTYPE_CARTEBLEUE', 'Carte Bleue');
define('PG_PAYMENTTYPE_DINERSCLUB', 'Diners Club');
define('PG_PAYMENTTYPE_DISCOVER', 'Discover');
define('PG_PAYMENTTYPE_JCB', 'JCB');
define('PG_PAYMENTTYPE_MAESTRO', 'Maestro');
define('PG_PAYMENTTYPE_LASTSCHRIFT', 'Lastschrift');
define('PG_PAYMENTTYPE_INVOICE', 'Rechnung');
define('PG_PAYMENTTYPE_PREPAY', 'Vorkasse');
define('PG_PAYMENTTYPE_COD', 'Nachnahme');
define('PG_PAYMENTTYPE_PAYPAL', 'PayPal');
define('PG_PAYMENTTYPE_BILLSAFE', 'BillSAFE');
define('PG_PAYMENTTYPE_COMMERZFINANZ', 'CommerzFinanz');
define('PG_TYPE_ACTIVE', 'aktiv');
define('PG_CHECK_CAV', 'Abfrage Kartenpr&uuml;fziffer');
define('PG_PAYMENTTYPE_SOFORTUEBERWEISUNG', 'Sofort&uuml;berweisung (&Uuml;berweisung by SOFORT)');
define('PG_PAYMENTTYPE_GIROPAY', 'GiroPay');
define('PG_PAYMENTTYPE_EPS', 'EPS');
define('PG_PAYMENTTYPE_PFEFINANCE', 'Post-Finance EFinance');
define('PG_PAYMENTTYPE_PFCARD', 'Post-Finance Card');
define('PG_PAYMENTTYPE_IDEAL', 'iDEAL');
define('OVERRIDE_DATA', 'Lokale Parameter');
define('ADD_PAYMENT_GENRE', 'Zahlungsart hinzuf&uuml;gen');
define('PAYGENRE_CREDITCARD', 'Kreditkarten');
define('PAYGENRE_ONLINETRANSFER', 'Online-&Uuml;berweisungen');
define('PAYGENRE_EWALLET', 'e-Wallet');
define('PAYGENRE_ACCOUNTBASED', 'Kontobasierte Zahlarten');
define('PAYGENRE_INSTALLMENT', 'Finanzierung/Ratenkauf');

// config
define('ACTIVE', 'aktiv');
define('CONFIG_SAVE', 'Konfiguration speichern');
define('NO_PAYMENTGENRE_CONFIGURED', 'Es ist noch keine Zahlungsart konfiguriert.');
define('ADDRESS_CHECK_CONFIGURATION', 'Adress&uuml;berpr&uuml;fung');
define('SELECT_ALL_COUNTRIES', 'alle L&auml;nder aktivieren');
define('SELECT_NO_COUNTRY', 'alle L&auml;nder deaktivieren');
define('REMOVE_PAYMENT_GENRE', 'Zahlart entfernen');
define('REMOVE_THIS_GENRE', 'Diese Zahlart beim Speichern der Konfiguration entfernen');
define('CONFIGURATION_SAVED', 'Konfiguration gespeichert');
define('PAYMENTGENRE_ADDED', 'Zahlart hinzugef&uuml;gt');
define('PAYONE_ORDERS_HEADING', 'PAYONE-Zahlung');
define('TRANSACTIONS', 'Transaktionen');
define('TXID', 'Transaktions-ID');
define('USERID', 'Benutzer-ID');
define('CREATED', 'angelegt');
define('LAST_MODIFIED', 'letzte &Auml;nderung');
define('STATUS', 'Status');
define('TRANSACTION_STATUS', 'Transaktionsstatus');
define('NO_TRANSACTION_STATUS_RECEIVED', 'noch kein Transaktionsstatus empfangen');
define('ERROR_OCCURRED', 'Fehler aufgetreten');
define('ERROR_ADDRESSES_MUST_BE_EQUAL', 'Bei der gew&auml;hlten Zahlungsart m&uuml;ssen Rechnungs- und Lieferadresse &uuml;bereinstimmen!');
define('TABLE_HEADING_CHECK', 'Ausw&auml;hlen');
define('DUMP_CONFIG', 'Konfiguration exportieren');
define('CONFIGURATION_DUMPED_TO', 'Konfiguration gespeichert in Datei');
define('ERROR_DUMPING_CONFIGURATION', 'Beim Exportieren der Konfiguration ist ein Fehler aufgetreten.');
define('INSTALL_CONFIG', 'PAYONE Installieren');

// Capture
define('CAPTURE_TRANSACTION', 'Zahlung einfordern');
define('CAPTURE_AMOUNT', 'Betrag');
define('CAPTURE_SUBMIT', 'Jetzt einfordern');
define('AMOUNT_CAPTURED', 'Betrag eingefordert');

// Clearing
define('CLEARING_INTRO', 'Bitte &uuml;berweisen Sie den Rechnungsbetrag auf das folgende Konto:');
define('CLEARING_OUTRO', 'Die Ware wird erst ausgeliefert, wenn der Betrag auf dem Konto eingegangen ist.');
define('CLEARING_ACCOUNTHOLDER', 'Kontoinhaber: ');
define('CLEARING_ACCOUNT', 'Kontonummer: ');
define('CLEARING_BANKCODE', 'Bankleitzahl: ');
define('CLEARING_IBAN', 'IBAN: ');
define('CLEARING_BIC', 'BIC: ');
define('CLEARING_BANK', 'Bank: ');
define('CLEARING_AMOUNT', 'Betrag');
define('CLEARING_TEXT', 'Verwendungszweck: ');

// Refund
define('REFUND_TRANSACTION', 'Gutschrift');
define('REFUND_SUBMIT', 'Gutschrift ausf&uuml;hren');
define('REFUND_AMOUNT', 'Betrag');
define('REFUND_BANKCOUNTRY', 'Land');
define('REFUND_COUNTRY_DE', 'Deutschland');
define('REFUND_COUNTRY_FR', 'Frankreich');
define('REFUND_COUNTRY_NL', 'Niederlande');
define('REFUND_COUNTRY_AT', '&Ouml;sterreich');
define('REFUND_COUNTRY_CH', 'Schweiz');
define('REFUND_BANKACCOUNT', 'Kto.Nr.');
define('REFUND_BANKCODE', 'BLZ');
define('REFUND_BANKBRANCHCODE', 'Filiale');
define('REFUND_BANKCHECKDIGIT', 'Pr&uuml;fziffer');
define('REFUND_IBAN', 'IBAN');
define('REFUND_BIC', 'BIC');
define('AMOUNT_REFUNDED', 'Betrag gutgeschrieben');

// Log
define('PAYONE_LOGS_TITLE', 'PAYONE API Log');
define('EVENT_ID', 'Ereignis-ID');
define('DATETIME', 'Zeitpunkt');
define('CUSTOMER', 'Kunde (sofern erfasst)');
define('START_DATE', 'Anfang');
define('END_DATE', 'Ende');
define('PAGE', 'Seite');
define('SEARCH', 'Suche');
define('SHOW', 'anzeigen');
define('EVENT_LOG_COUNT', 'Unterereignis-Nr.');
define('NO_LOGS', 'F&uuml;r den gew&auml;hlten Zeitraum liegen keine Eintr&auml;ge dieser Art vor.');
define('API', 'API');

?>