<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-13 16:24:37 +0200 (Thu, 13 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortrechnung.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */


//include language-constants used in all Multipay Projects
require_once 'sofort_general.php';

define('MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '');
define('MODULE_PAYMENT_SOFORT_SR_CHECKOUT_CONDITIONS', '
	<script type="text/javascript">
		function showSrConditions() {
			srOverlay = new sofortOverlay(jQuery(".srOverlay"), "callback/sofort/ressources/scripts/getContent.php", "https://documents.sofort.com/de/sr/privacy_de");
			srOverlay.trigger();
		}
		document.write(\'<a id="srNotice" href="javascript:void(0)" onclick="showSrConditions();">Ich habe die Datenschutzhinweise gelesen.</a>\');
	</script>
	
	<div style="display:none; z-index: 1001;filter: alpha(opacity=92);filter: progid :DXImageTransform.Microsoft.Alpha(opacity=92);-moz-opacity: .92;-khtml-opacity: 0.92;opacity: 0.92;background-color: black;position: fixed;top: 0px;left: 0px;width: 100%;height: 100%;text-align: center;vertical-align: middle;" class="srOverlay">
		<div class="loader" style="z-index: 1002;position: relative;background-color: #fff;top: 40px;overflow: scroll;padding: 4px;border-radius: 7px;-moz-border-radius: 7px;-webkit-border-radius: 7px;margin: auto;width: 620px;height: 400px;overflow: scroll; overflow-x: hidden;">
			<div class="closeButton" style="position: fixed; top: 54px; background: url(callback/sofort/ressources/images/close.png) right top no-repeat;cursor:pointer;height: 30px;width: 30px;"></div>
			<div class="content"></div>
		</div>
	</div>
	<noscript>
		<a href="https://documents.sofort.com/de/sr/privacy_de" target="_blank">Ich habe die Datenschutzhinweise gelesen.</a>
	</noscript>
');

define('MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'checkout.sr.description');

define('MODULE_PAYMENT_SOFORT_SR_TEXT_TITLE', 'Rechnung by SOFORT <br /><img src="https://images.sofort.com/de/sr/logo_90x30.png"  alt="Logo Rechnung by SOFORT"/>');
define('MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_TEXT_TITLE', 'Kauf auf Rechnung');
define('MODULE_PAYMENT_SOFORT_SR_TEXT_ERROR_MESSAGE', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_SR', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_SR_CHECKOUT_TEXT', '');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_CONFIRM_SR', 'Rechnung hier bestätigen:');
define('MODULE_PAYMENT_SOFORT_SR_STATUS_TITLE', 'sofort.de Modul aktivieren');
define('MODULE_PAYMENT_SOFORT_SR_STATUS_DESC', 'Aktiviert/deaktiviert das komplette Modul');
define('MODULE_PAYMENT_SOFORT_SR_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_SOFORT_SR_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION', 'Kauf auf Rechnung mit Zahlungsgarantie.');
define('MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche für dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_SOFORT_SR_ZONE_TITLE', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE);
define('MODULE_PAYMENT_SOFORT_SR_ZONE_DESC', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC);
define('MODULE_PAYMENT_SOFORT_SR_ORDER_STATUS_ID_TITLE', 'Bestätigter Bestellstatus');
define('MODULE_PAYMENT_SOFORT_SR_ORDER_STATUS_ID_DESC', 'Bestellstatus nach erfolgreicher und bestätigter Transaktion und Freigabe der Rechnung durch den Händler.');
define('MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID_TITLE', 'Unbestätigter Bestellstatus');
define('MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID_DESC', 'Bestellstatus nach erfolgreicher Zahlung. Die Rechnung wurde noch nicht durch den Händler freigegeben.');
define('MODULE_PAYMENT_SOFORT_SR_TMP_STATUS_ID_TITLE', 'Temporärer Bestellstatus');
define('MODULE_PAYMENT_SOFORT_SR_TMP_STATUS_ID_DESC', 'Bestellstatus für nicht abgeschlossene Transaktionen. Die Bestellung wurde erstellt aber die Transaktion von der SOFORT AG noch nicht bestätigt.');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_STATUS_ID_TITLE', 'Bestellstatus bei kompletter Stornierung');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_STATUS_ID_DESC', 'Stornierter Bestellstatus<br />Bestellstatus nach einer vollen Stornierung der Rechnung.');

define('MODULE_PAYMENT_SOFORT_SR_PENDINIG_NOT_CONFIRMED_COMMENT', 'Bestellung mit Kauf auf Rechnung erfolgreich übermittelt. Bestätigung durch Händler noch nicht erfolgt. Ihre Transaktions-ID:');

define('MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT_TITLE', 'Empfohlene Zahlungsweise');
define('MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT_DESC', 'Diese Zahlart als "empfohlene Zahlungsart" markieren. Auf der Bezahlseite erfolgt ein Hinweis direkt hinter der Zahlungsart.');
define('MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT_TEXT', '(Empfohlene Zahlungsweise)');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TIME', 'Zeit');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_DATE', 'Datum');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_AMOUNT', 'Betrag');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_COMMENT', 'Kommentar');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_ORDER_HISTORY', 'Bestellhistorie');
define('MODULE_PAYMENT_SOFORT_SR_PRICE_CHANGED_CUSTOMERINFO', 'Durch Preisrundungen hat sich ein neuer, minimal abweichender Rechnungspreis ergeben. Bitte beachten Sie dies bei Erhalt der Rechnung! Neuer Rechnungsbetrag:');

/////////////////////////////////////////////////
//////// Seller-Backend and callback.php ////////
/////////////////////////////////////////////////

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_BACK', 'zurück');

define('MODULE_PAYMENT_SOFORT_SR_CONFIRM_INVOICE', 'Rechnung bestätigen');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_INVOICE', 'Rechnung stornieren');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_CONFIRMED_INVOICE', 'Rechnung gutschreiben');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_INVOICE_QUESTION', 'Sind Sie sicher, dass Sie die Rechnung wirklich stornieren wollen? Dieser Vorgang kann nicht rückgängig gemacht werden.');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_CONFIRMED_INVOICE_QUESTION', 'Sind Sie sicher, dass Sie die Rechnung wirklich gutschreiben wollen? Dieser Vorgang kann nicht rückgängig gemacht werden.');

define('MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE', 'Rechnung herunterladen');
define('MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE_HINT', 'Laden Sie hier das entsprechende Dokument (Rechnungsvorschau, Rechnung, Gutschrift) herunter.');
define('MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_CREDIT_MEMO', 'Gutschrift herunterladen');
define('MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE_PREVIEW', 'Rechnungsvorschau herunterladen');

define('MODULE_PAYMENT_SOFORT_SR_EDIT_CART', 'Warenkorb anpassen');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CART', 'Warenkorb speichern');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CART_QUESTION', 'Wollen Sie den Warenkorb wirklich anpassen?');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CART_ERROR', 'Beim Bearbeiten des Warenkorbs ist ein Fehler aufgetreten.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CART_HINT', 'Speichern Sie hier Ihre Änderungen am Warenkorb. Bei bereits bestätigten Rechnung führt ein mengenmäßig reduzierter sowie ein von der Rechnung gelöschter Artikel zu einer Gutschrift.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_DISCOUNTS_HINT', 'Sie können Rabatte oder Aufschläge anpassen. Aufschläge dürfen nicht erhöht werden und Rabatte keine Beträge größer Null erhalten. Der Gesamtbetrag der Rechnung darf durch die Anpassung nicht erhöht werden.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_DISCOUNTS_GTZERO_HINT', 'Rabatte dürfen keinen Betrag größer Null erhalten.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY', 'Menge anpassen');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_HINT', 'Sie können die Anzahl der Artikel pro Position anpassen. Es dürfen lediglich Mengen reduziert, nicht jedoch hinzugefügt werden.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_TOTAL_GTZERO', 'Die Anzahl des Artikels kann nicht reduziert werden, da die Gesamtsumme der Rechnung nicht negativ sein darf.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_ZERO_HINT', 'Anzahl muss größer 0 sein. Zum Löschen markieren Sie den Artikel bitte am Ende der Zeile.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE', 'Preis anpassen');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_HINT', 'Sie können den Preis der einzelnen Artikel pro Position anpassen. Preise können lediglich reduziert, nicht erhöht werden.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_TOTAL_GTZERO', 'Der Preis kann nicht reduziert werden, da die Gesamtsumme der Rechnung nicht negativ sein darf.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_AND_QUANTITY_HINT', 'Es können nicht gleichzeitig Preis und Menge angepasst werden.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_AND_QUANTITY_NAN', 'Sie haben ungültige Zeichen eingegeben. Bei diesen Anpassungen sind nur Zahlen zulässig.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_VALUE_LTZERO_HINT', 'Wert darf nicht kleiner 0 sein.');

define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CONFIRMED_INVOICE', 'Bitte Kommentar eingeben');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CONFIRMED_INVOICE_HINT', 'Bei Anpassung einer bereits bestätigten Rechnung muss eine entsprechende Begründung hinterlegt werden. Diese erscheint später auf der Gutschrift als Kommentar zum entsprechenden Artikel.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_HINT', 'Sie können den Preis der Versandkosten anpassen. Der Preis kann lediglich reduziert, nicht erhöht werden.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_COSTS_HINT', 'Bei Retouren dürfen Versandkosten nicht als alleinstehender Posten auf einer Rechnung erscheinen.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_TOTAL_GTZERO', 'Die Versandkosten können nicht reduziert werden, da die Gesamtsumme der Rechnung nicht negativ sein darf.');

define('MODULE_PAYMENT_SOFORT_SR_RECALCULATION', 'wird neu berechnet');

define('MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_TOTAL_GTZERO','Dieser Artikel kann nicht gelöscht werden, da die Gesamtsumme der Rechnung nicht negativ sein darf.');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_ARTICLE_FROM_INVOICE', 'Artikel entfernen');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE', 'Position löschen');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_QUESTION', 'Sie möchten folgende Artikel wirklich löschen: %s ?');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_HINT', 'Markieren Sie Artikel um sie zu löschen. Bei einer bereits bestätigten Rechnung führt das Löschen eines Artikels zu einer Gutschrift.');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_LAST_ARTICLE_HINT', 'Durch das Reduzieren der Anzahl aller bzw. durch Entfernen des letzten Artikels wird die Rechnung komplett storniert.');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED', 'Die Rechnung wurde storniert.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CONFIRMED', 'Die Ware wird zum Versand bereit gestellt.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_PENDINIG_NOT_CONFIRMED', 'Kauf auf Rechnung als Zahlungsart gewählt. Transaktion nicht abgeschlossen.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED_REFUNDED', 'Die Rechnung wurde storniert. Gutschrift erstellt.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REANIMATED', 'Die Stornierung der Rechnung wurde rückgängig gemacht.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCEL_30_DAYS', 'Rechnung wurde automatisch storniert. Bestätigungszeitraum von 30 Tagen abgelaufen.');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CURRENT_TOTAL', 'Aktueller Rechnungsbetrag:');
define('MODULE_PAYMENT_SOFORT_SR_SUCCESS_ADDRESS_UPDATED', 'Liefer- und Rechnungsaddresse erfolgreich upgedated.');
define('MODULE_PAYMENT_SOFORT_SR_STATUSUPDATE_UNNECESSARY', 'Statusupdate unnötig.');
define('MODULE_PAYMENT_SOFORT_SR_UNKNOWN_STATUS', 'Unbekannten Zahlungs-/Rechnungsstatus gefunden.');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_DOWNLOAD_INVOICE', 'Rechnung herunterladen');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_DOWNLOAD_INVOICE_CREDITMEMO', 'Rechnung/Gutschrift herunterladen');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CLOSE_WINDOW', 'Fenster schließen');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CONFIRMATION_CANCEL', 'Sind Sie sicher, dass Sie die Rechnung wirklich stornieren wollen? Dieser Vorgang kann nicht rückgängig gemacht werden.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_YES', 'Ja');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_NO', 'Nein');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_REFRESH_WINDOW', 'Fenster neu laden');

define('MODULE_PAYMENT_SOFORT_SR_GLOBAL_ERROR', 'Fehler! Bitte kontaktieren Sie den Administrator.');

define('MODULE_PAYMENT_SOFORT_SR_INVOICE_CONFIRMED', 'Rechnung wurde bestätigt');
define('MODULE_PAYMENT_SOFORT_SR_INVOICE_CANCELED', 'Die Rechnung wurde storniert.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_DETAILS', 'Rechnungsdetails');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TRANSACTION_ID', 'Transaktions-ID');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_ORDER_NUMBER', 'Bestellnummer');
define('MODULE_PAYMENT_SOFORT_SR_ADMIN_TITLE', 'Rechnung by SOFORT');
define('MODULE_PAYMENT_SOFORT_SR_CONFIRM_CANCEL', 'Sind Sie sicher, dass Sie die Rechnung wirklich stornieren wollen? Dieser Vorgang kann nicht rückgängig gemacht werden.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REMINDER', 'Mahnstufe {{d}}');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_DELCREDERE', 'Inkassoübergabe');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CREDITED_TO_SELLER', 'Zahlungseingang auf Händlerkonto ist erfolgt.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CREDITED_TO_SELLER_CUSTOMER_PENDING', 'Zahlungseingang auf Händlerkonto ist erfolgt. Kundenzahlung ausstehend.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CANCELED_REFUNDED', 'Die Rechnung wurde storniert. Gutschrift erstellt. {{time}}');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_RECEIVED', 'Zahlungseingang.');
define('MODULE_PAYMENT_SOFORT_SR_PENDINIG_NOT_CONFIRMED_COMMENT_ADMIN', 'Bestellung mit Kauf auf Rechnung erfolgreich übermittelt. Bestätigung durch Händler noch nicht erfolgt.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_EDITED', 'Der Warenkorb wurde angepasst.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_RESET', 'Der Warenkorb wurde zurückgesetzt.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CONFIRMED_SELLER', 'Transaktionsstatus: Die Rechnung wurde bestätigt, warte auf Geldeingang. Rechnungsstatus: Rechnung noch offen.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED_REFUNDED_SELLER', 'Transaktionsstatus: Das Geld wird zurückerstattet. Rechnungsstatus: Rechnung wird gutgeschrieben.');
define('MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_RECEIVED_SELLER', 'Transaktionsstatus: Die Rechnung wurde bestätigt, warte auf Geldeingang. Rechnungsstatus: Käufer hat Rechnung bezahlt.');
define('MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_RECEIVED_SELLER', 'Transaktionsstatus: Die Rechnung wurde ausbezahlt. Rechnungsstatus: Käufer hat Rechnung bezahlt.');
define('MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_REMINDER_SELLER', 'Transaktionsstatus: Die Rechnung wurde bestätigt, warte auf Geldeingang. Rechnungsstatus: Mahnstufe {{d}}');
define('MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_REMINDER_SELLER', 'Transaktionsstatus: Die Rechnung wurde ausbezahlt. Rechnungsstatus: Mahnstufe {{d}}');
define('MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_DELCREDERE_SELLER', 'Transaktionsstatus: Die Rechnung wurde bestätigt, warte auf Geldeingang. Rechnungsstatus: Inkassoübergabe');
define('MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_DELCREDERE_SELLER', 'Transaktionsstatus: Die Rechnung wurde ausbezahlt. Rechnungsstatus: Inkassoübergabe');
define('MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_PENDING_SELLER', 'Transaktionsstatus: Die Rechnung wurde ausbezahlt. Rechnungsstatus: Kundenzahlung ausstehend.');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9000', 'Keine Rechnungs-Transaktion gefunden.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9001', 'Die Rechnung konnte nicht bestätigt werden.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9002', 'Die Übergebene Rechnungssumme übersteigt das Kreditlimit.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9003', 'Die Rechnung konnte nicht storniert werden.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9004', 'Die Anfrage enthielt ungültige Warenkorbpositionen.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9005', 'Der Warenkorb konnte nicht angepasst werden.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9006', 'Der Zugriff zur Schnittstelle ist 30 Tage nach Zahlungseingang nicht mehr möglich.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9007', 'Die Rechnung wurde bereits storniert.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9008', 'Der Betrag der übergebenen Mehrwertsteuer ist zu hoch.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9009', 'Die Beträge der übergeben Mehrwertsteuersätze der Artikel stehen in Konflikt zueinander.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9010', 'Die Anpassung des Warenkorbs ist nicht möglich.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9011', 'Es wurde kein Kommentar für die Anpassung des Warenkorbs übergeben.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9012', 'Es können keine Positionen zum Warenkorb hinzugefügt werden. Ebenso kann die Menge pro Rechnungsposition nicht heraufgesetzt werden. Beträge einzelner Positionen dürfen den Ursprungsbetrag nicht überschreiten.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9013', 'Es befinden sich ausschließlich nichtfakturierbare Artikel im Warenkorb.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9014', 'Die übergebene Rechnungsnummer wird bereits verwendet.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9015', 'Die übergebene Nummer der Gutschrift wird bereits verwendet.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9016', 'Die übergebene Bestellnummer wird bereits verwendet.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9017', 'Die Rechnung wurde bereits bestätigt.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9018', 'Es wurden keine Daten der Rechnung angepasst.');