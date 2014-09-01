<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_lastschrift.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

//include language-constants used in all Multipay Projects
require_once 'sofort_general.php';

define('MODULE_PAYMENT_SOFORT_LS_CHECKOUT_CONDITIONS', '
<script type="text/javascript">
		function showLsConditions() {
			lsOverlay = new sofortOverlay(jQuery(".lsOverlay"), "callback/sofort/ressources/scripts/getContent.php", "https://documents.sofort.com/de/ls/privacy_de");
			lsOverlay.trigger();
		}
		document.write(\'<a id="lsNotice" href="javascript:void(0)" onclick="showLsConditions()">Ich habe die Datenschutzhinweise gelesen.</a>\');
	</script>
	<div style="display:none; z-index: 1001;filter: alpha(opacity=92);filter: progid :DXImageTransform.Microsoft.Alpha(opacity=92);-moz-opacity: .92;-khtml-opacity: 0.92;opacity: 0.92;background-color: black;position: fixed;top: 0px;left: 0px;width: 100%;height: 100%;text-align: center;vertical-align: middle;" class="lsOverlay">
		<div class="loader" style="z-index: 1002; position: relative;background-color: #fff;border: 5px solid #C0C0C0;top: 40px;overflow: scroll;padding: 4px;border-radius: 7px;-moz-border-radius: 7px;-webkit-border-radius: 7px;margin: auto;width: 620px;height: 400px;overflow: scroll; overflow-x: hidden;">
			<div class="closeButton" style="position: fixed; top: 54px; background: url(callback/sofort/ressources/images/close.png) right top no-repeat;cursor:pointer;height: 30px;width: 30px;"></div>
			<div class="content"></div>
		</div>
	</div>
	<noscript>
		<a href="https://documents.sofort.com/de/ls/privacy_de" target="_blank">Ich habe die Datenschutzhinweise gelesen.</a>
	</noscript>
');
define('MODULE_PAYMENT_SOFORT_LS_TEXT_TITLE', 'Lastschrift by SOFORT <br /><img src="https://images.sofort.com/de/ls/logo_90x30.png" alt="Logo Lastschrift"/>');
define('MODULE_PAYMENT_SOFORT_LASTSCHRIFT_TEXT_TITLE', 'Bankeinzug (Lastschrift)');
define('MODULE_PAYMENT_SOFORT_LS_TEXT_ERROR_MESSAGE', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');
define('MODULE_PAYMENT_SOFORT_LS_CHECKOUT_TEXT', '');
define('MODULE_PAYMENT_SOFORT_LS_STATUS_TITLE', 'sofort.de Modul aktivieren');
define('MODULE_PAYMENT_SOFORT_LS_STATUS_DESC', 'Aktiviert/deaktiviert das komplette Modul');
define('MODULE_PAYMENT_SOFORT_LS_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_SOFORT_LS_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_SOFORT_LS_TEXT_DESCRIPTION', 'Zahlungsmodul für Lastschrift by SOFORT');
define('MODULE_PAYMENT_SOFORT_LS_TEXT_ERROR_HEADING', 'Fehler bei der Bestellung aufgetreten.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_LS', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');

define('MODULE_PAYMENT_SOFORT_LS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '');
define('MODULE_PAYMENT_SOFORT_LS_TEXT_HOLDER', 'Kontoinhaber: ');
define('MODULE_PAYMENT_SOFORT_LS_TEXT_ACCOUNT_NUMBER', 'Kontonummer: ');
define('MODULE_PAYMENT_SOFORT_LS_TEXT_BANK_CODE', 'Bankleitzahl: ');
define('MODULE_PAYMENT_SOFORT_LS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'Zahlung mit Lastschrift by SOFORT: TÜV-geprüfter Datenschutz. Keine Registrierung notwendig. Ware/Dienstleistung kann SOFORT versendet werden.');
define('MODULE_PAYMENT_SOFORT_LASTSCHRIFT_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_SOFORT_LASTSCHRIFT_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche für dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_PROJECT_ID_TITLE', 'Projekt-Nummer');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_PROJECT_ID_DESC', 'Von SOFORT AG zugewiesene Projekt-Nummer');
define('MODULE_PAYMENT_SOFORT_LS_ZONE_TITLE', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE);
define('MODULE_PAYMENT_SOFORT_LS_ZONE_DESC', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC);

define('MODULE_PAYMENT_SOFORT_LS_ORDER_STATUS_ID_TITLE', 'Bestätigter Bestellstatus');
define('MODULE_PAYMENT_SOFORT_LS_ORDER_STATUS_ID_DESC', 'Bestätigter Bestellstatus<br />Bestellstatus nach abgeschlossener Transaktion.');

define('MODULE_PAYMENT_SOFORT_LS_LOGO', 'logo_155x50.png');
define('MODULE_PAYMENT_SOFORT_LS_BANNER', 'banner_300x100.png');

define('MODULE_PAYMENT_SOFORT_LS_RECOMMENDED_PAYMENT_TITLE', 'Empfohlene Zahlungsweise');
define('MODULE_PAYMENT_SOFORT_LS_RECOMMENDED_PAYMENT_DESC', 'Diese Zahlart als "empfohlene Zahlungsart" markieren. Auf der Bezahlseite erfolgt ein Hinweis direkt hinter der Zahlungsart.');
define('MODULE_PAYMENT_SOFORT_LS_RECOMMENDED_PAYMENT_TEXT', '(Empfohlene Zahlungsweise)');

?>