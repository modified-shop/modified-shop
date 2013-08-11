<?php

/* Default Messages */
 define('MODULE_PAYMENT_BILLPAY_TEXT_TITLE', 'Fattura (Billpay)');
 define('MODULE_PAYMENT_BILLPAY_TEXT_DESCRIPTION', 'Rechnung (Billpay)');
 define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_MESSAGE', 'BillPay Error Message');
 define('MODULE_PAYMENT_BILLPAY_TEXT_INFO', '<img src="https://www.billpay.de/sites/all/themes/billpay/images/header_logo.png"  alt="billpay" title="billpay" width="190px" /><br /><br />');

 define('MODULE_PAYMENT_BILLPAY_ALLOWED_TITLE' , 'Erlaubte Zonen');
 define('MODULE_PAYMENT_BILLPAY_ALLOWED_DESC' , 'Geben Sie einzeln die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

 define('MODULE_PAYMENT_BILLPAY_LOGGING_TITLE' , 'Absoluter Pfad zur Logdatei');
 define('MODULE_PAYMENT_BILLPAY_LOGGING_DESC' , 'Wenn kein Wert eingestellt ist, wird standardm&auml;ssig in das Verzeichnis includes/external/billpay/log geschrieben (Schreibrechte m&uuml;ssen verf&uuml;gbar sein).');

 define('MODULE_PAYMENT_BILLPAY_MERCHANT_ID_TITLE' , 'Verk&auml;ufer ID');
 define('MODULE_PAYMENT_BILLPAY_MERCHANT_ID_DESC' , 'Diese Daten erhalten Sie von Billpay');

 define('MODULE_PAYMENT_BILLPAY_ORDER_STATUS_TITLE' , 'Bestellstatus festlegen');
 define('MODULE_PAYMENT_BILLPAY_ORDER_STATUS_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');

 define('MODULE_PAYMENT_BILLPAY_PORTAL_ID_TITLE' , 'Portal ID');
 define('MODULE_PAYMENT_BILLPAY_PORTAL_ID_DESC' , 'Diese Daten erhalten Sie von Billpay');

 define('MODULE_PAYMENT_BILLPAY_SECURE_TITLE' , 'Security Key');
 define('MODULE_PAYMENT_BILLPAY_SECURE_DESC' , 'Diese Daten erhalten Sie von Billpay');

 define('MODULE_PAYMENT_BILLPAY_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
 define('MODULE_PAYMENT_BILLPAY_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

 define('MODULE_PAYMENT_BILLPAY_STATUS_TITLE' , 'Aktiviert');
 define('MODULE_PAYMENT_BILLPAY_STATUS_DESC' , 'M&ouml;chten Sie den Rechnungskauf mit Billpay erlauben?');

 define('MODULE_PAYMENT_BILLPAY_TESTMODE_TITLE' , 'Transaktionsmodus');
 define('MODULE_PAYMENT_BILLPAY_TESTMODE_DESC' , 'Im Testmodus werden detailierte Fehlermeldungen angezeigt. F&uuml;r den Produktivbetrieb muss der Livemodus aktiviert werden.');

 define('MODULE_PAYMENT_BILLPAY_ZONE_TITLE' , 'Steuerzone');
 define('MODULE_PAYMENT_BILLPAY_ZONE_DESC' , '');

 define('MODULE_PAYMENT_BILLPAY_API_URL_BASE_TITLE' , 'API url base');
 define('MODULE_PAYMENT_BILLPAY_API_URL_BASE_DESC' , 'Diese Daten erhalten Sie von Billpay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

 define('MODULE_PAYMENT_BILLPAY_TESTAPI_URL_BASE_TITLE' , 'Test-API url base');
 define('MODULE_PAYMENT_BILLPAY_TESTAPI_URL_BASE_DESC' , 'Diese Daten erhalten Sie von Billpay (Achtung! Die URLs f&uuml; das Test- bzw. das Livesystem unterscheiden sich!)');

 define('MODULE_PAYMENT_BILLPAY_LOGGING_ENABLE_TITLE' , 'Logging aktiviert');
 define('MODULE_PAYMENT_BILLPAY_LOGGING_ENABLE_DESC' , 'Sollen Anfragen an die Billpay-Zahlungsschnittstelle in die Logdatei geschrieben werden?');

 define('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT_TITLE', 'Mindestbestellwert');
 define('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT_DESC', 'Ab diesem Bestellwert wird die Zahlungsart eingeblendet.');

 define('MODULE_PAYMENT_BILLPAY_LOGPATH_TITLE', 'Logging Pfad');
 define('MODULE_PAYMENT_BILLPAY_LOGPATH_DESC', '');
 
 define('MODULE_PAYMENT_BILLPAY_HTTP_X_TITLE', 'X_FORWARDED_FOR erlauben');
define('MODULE_PAYMENT_BILLPAY_HTTP_X_DESC', 'Aktivieren Sie dieses Funktion wenn Ihr Shop in einem Cloud System l&auml;uft.');

// Payment selection texts
define('MODULE_PAYMENT_BILLPAY_TEXT_BIRTHDATE', 'Data di nascita');
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK', 'Con la presente accetto le <a href="https://www.billpay.de/kunden/agb?lang=it" target="_blank"> condizioni generali di contratto </a>e<a href="https://www.billpay.de/kunden/agb?lang=it#datenschutz" target="_blank"> l&agrave;informativa sulla privacy </a> di Billpay GmbH');
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK_CH', '<label for="billpay_eula"> Con la presente accetto le <a href="https://www.billpay.de/kunden/agb-ch?lang=it" target="_blank"> condizioni generali di contratto </a>e<a href="https://www.billpay.de/kunden/agb-ch?lang=it#datenschutz" target="_blank"> l&agrave;informativa sulla privacy </a> di Billpay GmbH </label> <br />');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE', 'Inserire la data di nascita');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_GENDER', 'Inserire il sesso');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_TITLE', 'Inserire il titolo');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE_AND_GENDER', 'Inserire la data di nascita e il sesso');
define('MODULE_PAYMENT_BILLPAY_TEXT_NOTE', '');
define('MODULE_PAYMENT_BILLPAY_TEXT_REQ', '');
define('MODULE_PAYMENT_BILLPAY_TEXT_GENDER', ' Sesso ');
define('MODULE_PAYMENT_BILLPAY_TEXT_SALUTATION', 'Titolo');
define('MODULE_PAYMENT_BILLPAY_TEXT_MALE', 'uomo');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEMALE', 'donna');
define('MODULE_PAYMENT_BILLPAY_TEXT_MR', 'Sig.');
define('MODULE_PAYMENT_BILLPAY_TEXT_MRS', 'Sig.ra');

define('JS_BILLPAY_EULA', '* Accettare le condizioni generali di contratto di Billpay!\n\n');
define('JS_BILLPAY_DOBDAY', '* Bitte Inserire il giorno di nascita.\n\n');
define('JS_BILLPAY_DOBMONTH', '* Inserire il mese di nascita.\n\n');
define('JS_BILLPAY_DOBYEAR', '* Inserire l&agrave;anno di nascita.\n\n');
define('JS_BILLPAY_GENDER', '* Inserire il sesso.\n\n');

define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_EULA', '* Accettare le condizioni generali di contratto di Billpay!');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT', 'Si &egrave; verificato un errore interno. Scegliere un&rsquo;altra modalità di pagamento');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_SHORT', 'Si &egrave; verificato un errore interno!');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_CREATED_COMMENT', 'Das Zahlungsziel der Bestellung wurde erfolgreich bei Billpay gestartet.');
define('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_COMMENT', 'Die Bestellung wurde erfolgreich bei Billpay storniert');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DUEDATE', 'Das Zahlungsziel konnte nicht gestartet werden, weil das F%E4lligkeitsdatum leer ist!');

define('MODULE_PAYMENT_BILLPAY_TEXT_CREATE_INVOICE', 'Attivare ora il termine di pagamento Billpay?');
define('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_ORDER', 'Cancellare ora l&rsquo;ordine Billpay?');

define('MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER', 'Intestatario del conto corrente');
define('MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER', 'Numero del conto corrente');
define('MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE', 'Codice identificativo della banca (BIC)');
define('MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME', 'Banca');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_REFERENCE', 'Causale di pagamento');

define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO', 'Versare l&rsquo;importo totale indicando il numero di transazione Billpay nella causale di pagamento (%s) entro la data di scadenza del pagamento %02s.%02s.%04s sul seguente conto corrente.');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO1', 'Avete scelto il pagamento alla consgena della merce con Billpay. Versare l&rsquo;importo totale entro il');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO2', ' sul seguente conto: ');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO3', 'Data di scadenza, che riceverete con la fattura');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL', '<br/>Versare l&rsquo;importo totale indicando il numero di transazione Billpay nella causale di pagamento (%s) entro la data di scadenza, che riceverete con la fattura, sul seguente conto:');

define('MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE', 'Termine di pagamento');

define('MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE', 'Causale di pagamento');

define('MODULE_PAYMENT_BILLPAY_TEXT_ADD', 'pi&ugrave;.');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE', 'la tassa ');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE_INFO1', 'Per questo ordine via fattura è previsto un supplemento di');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE_INFO2', 'alzato');

define('MODULE_PAYMENT_BILLPAY_TEXT_SANDBOX', 'Siete in modalit&agrave; sandbox:');
define('MODULE_PAYMENT_BILLPAY_TEXT_CHECK', 'Siete in modalit&agrave; acquisto:');
define('MODULE_PAYMENT_BILLPAY_UNLOCK_INFO',  'Informazioni sul Go  Live ');

define('MODULE_PAYMENT_BILLPAY_B2BCONFIG_TITLE', 'Erlaubte Kundenarten');
define('MODULE_PAYMENT_BILLPAY_B2BCONFIG_DESC', 'Wollen Sie die Zahlart f&uuml;r Privatkunden (B2C), Gesch&auml;ftskunden (B2B) oder f&uuml;r beide (BOTH) aktivieren?');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_NAME_TEXT', 'Nome dell&agrave;azienda');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_LEGAL_FORM_TEXT', 'Forma giuridica');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_LEGAL_FORM_SELECT_HTML', "");
define('MODULE_PAYMENT_BILLPAY_B2B_PRIVATE_CLIENT_TEXT', 'Privato');
define('MODULE_PAYMENT_BILLPAY_B2B_BUSINESS_CLIENT_TEXT', 'Cliente business');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_FIELD_EMPTY', 'Inserire il nome dell\'azienda ');
define('MODULE_PAYMENT_BILLPAY_B2B_LEGAL_FORM_FIELD_EMPTY', 'Inserire la forma giuridica dell\'azienda');

define('MODULE_ORDER_TOTAL_BILLPAY_FEE_FROM_TOTAL', 'dell\'importo della fattura ');

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

define('MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_TEST' , 'Modalit&agrave; test');
define('MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_LIVE' , 'Modalit&agrave; live');

define('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_TITLE' , 'Billpay attivato');
define('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_TITLE' , 'Billpay cancellato');
define('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_TITLE' , 'Errore Billpay!');

define('MODULE_PAYMENT_BILLPAY_SALUTATION_MALE', 'Signor');
define('MODULE_PAYMENT_BILLPAY_SALUTATION_FEMALE', 'Signora');

?>