<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-13 11:51:22 +0200 (Thu, 13 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortvorkasse.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */
//include language-constants used in all Multipay Projects
require_once 'sofort_general.php';

define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_CONDITIONS', '
	<script type="text/javascript">
		function showSvConditions() {
			svOverlay = new sofortOverlay(jQuery(".svOverlay"), "callback/sofort/ressources/scripts/getContent.php", "https://documents.sofort.com/de/sv/privacy_de");
			svOverlay.trigger();
		}
		document.write(\'<a id="svNotice" href="javascript:void(0)" onclick="showSvConditions()">Ich habe die Datenschutzhinweise gelesen.</a>\');
	</script>
	<div style="display:none; z-index: 1001;filter: alpha(opacity=92);filter: progid :DXImageTransform.Microsoft.Alpha(opacity=92);-moz-opacity: .92;-khtml-opacity: 0.92;opacity: 0.92;background-color: black;position: fixed;top: 0px;left: 0px;width: 100%;height: 100%;text-align: center;vertical-align: middle;" class="svOverlay">
		<div class="loader" style="z-index: 1002;position: relative;background-color: #fff;border: 5px solid #C0C0C0;top: 40px;overflow: scroll;padding: 4px;border-radius: 7px;-moz-border-radius: 7px;-webkit-border-radius: 7px;margin: auto;width: 620px;height: 400px;overflow: scroll; overflow-x: hidden;">
			<div class="closeButton" style="position: fixed; top: 54px; background: url(callback/sofort/ressources/images/close.png) right top no-repeat;cursor:pointer;height: 30px;width: 30px;"></div>
			<div class="content"></div>
		</div>
	</div>
	<noscript>
		<a href="https://documents.sofort.com/de/sv/privacy_de" target="_blank">Ich habe die Datenschutzhinweise gelesen.</a>
	</noscript>
');
define('MODULE_PAYMENT_SOFORT_SV_TEXT_TITLE', 'Vorkasse by SOFORT <br /> <img src="https://images.sofort.com/de/sv/logo_90x30.png" alt="Logo Vorkasse by SOFORT"/>');
define('MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_TEXT_TITLE', 'Vorkasse');
define('MODULE_PAYMENT_SOFORT_SV_KS_TEXT_TITLE', 'Vorkasse mit Käuferschutz');
define('MODULE_PAYMENT_SOFORT_SV_TEXT_ERROR_MESSAGE', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_SV', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_SV_CHECKOUT_TEXT', '');
define('MODULE_PAYMENT_SOFORT_SV_STATUS_TITLE', 'sofort.de Modul aktivieren');
define('MODULE_PAYMENT_SOFORT_SV_STATUS_DESC', 'Aktiviert/deaktiviert das komplette Modul');
define('MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION', 'Vorkasse mit automatisiertem Zahlungsabgleich.');
define('MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche für dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_SOFORT_SV_ZONE_TITLE', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE);
define('MODULE_PAYMENT_SOFORT_SV_ZONE_DESC', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC);
define('MODULE_PAYMENT_SOFORT_SV_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_SOFORT_SV_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_SOFORT_SV_TMP_COMMENT', 'Vorkasse by SOFORT als Zahlungsart gewählt. Transaktion nicht abgeschlossen.');
define('MODULE_PAYMENT_SOFORT_SV_REASON_2_TITLE','Verwendungszweck 2');
define('MODULE_PAYMENT_SOFORT_SV_REASON_2_DESC','Im Verwendungszweck (maximal 27 Zeichen) werden folgende Platzhalter ersetzt:<br />{{transaction_id}}<br />{{order_date}}<br />{{customer_id}}<br />{{customer_name}}<br />{{customer_company}}<br />{{customer_email}}');

define('MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '');



define('MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'Zahlung mit Vorkasse by SOFORT: Keine Registrierung notwendig. Sie veranlassen die Überweisung bei Ihrer Bank selbst.');

define('MODULE_PAYMENT_SOFORT_SV_ORDER_STATUS_ID_TITLE', 'Bestätigter Bestellstatus');
define('MODULE_PAYMENT_SOFORT_SV_ORDER_STATUS_ID_DESC', 'Bestätigter Bestellstatus<br />Bestellstatus nach Geldeingang.');
define('MODULE_PAYMENT_SOFORT_SV_TMP_STATUS_ID_TITLE', 'Temporärer Bestellstatus');
define('MODULE_PAYMENT_SOFORT_STATUS_SV_LOSS', 'Der Zahlungseingang konnte bis dato noch nicht festgestellt werden. {{time}}');
define('MODULE_PAYMENT_SOFORT_SV_TMP_STATUS_ID_DESC', 'Bestellstatus für nicht abgeschlossene Transaktionen. Die Bestellung wurde erstellt aber die Transaktion von der SOFORT AG noch nicht bestätigt.');

define('MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT_TITLE', 'Empfohlene Zahlungsweise');
define('MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT_DESC', 'Diese Zahlart als "empfohlene Zahlungsart" markieren. Auf der Bezahlseite erfolgt ein Hinweis direkt hinter der Zahlungsart.');
define('MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT_TEXT', '(Empfohlene Zahlungsweise)');

define('MODULE_PAYMENT_SOFORT_SV_KS_STATUS_TITLE', 'Käuferschutz aktiviert');
define('MODULE_PAYMENT_SOFORT_SV_KS_STATUS_DESC', 'Käuferschutz für Vorkasse by SOFORT aktivieren');
define('MODULE_PAYMENT_SOFORT_SV_KS_STATUS_TEXT', 'Käuferschutz aktiviert');

define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HEADING_TEXT', 'Kontoverbindung');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_TEXT', 'Bitte benutzen Sie folgende Überweisungdaten:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HOLDER_TEXT', 'Kontoinhaber:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_ACCOUNT_NUMBER_TEXT', 'Kontonummer:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BANK_CODE_TEXT', 'BLZ:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_IBAN_TEXT', 'IBAN:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BIC_TEXT', 'BIC:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_AMOUNT_TEXT', 'Betrag:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_1_TEXT', 'Verwendungszweck:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_2_TEXT', '');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_HINT','Bitte achten Sie darauf, bei der Überweisung den hier angegebenen Verwendungszweck zu übernehmen, damit wir Ihre Zahlung zuordnen können.');


?>