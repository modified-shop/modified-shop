<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortueberweisung.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

//include language-constants used in all Multipay Projects
require_once 'sofort_general.php';

define('MODULE_PAYMENT_SOFORT_SU_TEXT_TITLE', 'SOFORT Überweisung <br /> <img src="https://images.sofort.com/de/su/logo_90x30.png" alt="Logo SOFORT Überweisung"/>');
define('MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_TEXT_TITLE', 'SOFORT Überweisung');
define('MODULE_PAYMENT_SOFORT_SU_KS_TEXT_TITLE', 'SOFORT Überweisung mit Käuferschutz');
define('MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION', 'SOFORT Überweisung ist der kostenlose, TÜV-zertifizierte Zahlungsdienst der SOFORT AG.');


define('MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '     <table border="0" cellspacing="0" cellpadding="0">      <tr>        <td valign="bottom">
	<a onclick="javascript:window.open(\'https://images.sofort.com/de/su/landing.php\',\'Kundeninformationen\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1020, height=900\');" style="float:left; width:auto;">
		{{image}}
	</a>
	</td>      </tr>      <tr> <td class="main">{{text}}</td>      </tr>      </table>');

define('MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'SOFORT Überweisung');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_TEXT', '<ul><li>Zahlungssystem mit TÜV-geprüftem Datenschutz</li><li>Keine Registrierung notwendig</li><li>Ware/Dienstleistung wird bei Verfügbarkeit SOFORT versendet</li><li>Bitte halten Sie Ihre Online-Banking-Daten (PIN/TAN) bereit</li></ul>');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_TEXT_KS', '<ul><li>Bei Bezahlung mit SOFORT Überweisung genießen Sie Käuferschutz! [[link_beginn]]Mehr Informationen[[link_end]]</li><li>Zahlungssystem mit TÜV-geprüftem Datenschutz</li><li>Keine Registrierung notwendig</li><li>Ware/Dienstleistung wird bei Verfügbarkeit SOFORT versendet</li><li>Bitte halten Sie Ihre Online-Banking-Daten (PIN/TAN) bereit</li></ul>');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_INFOLINK_KS', 'https://www.sofort-bank.com/de/kaeuferbereich/kaeuferschutz');
define('MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche für dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_SOFORT_SU_ZONE_TITLE', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE);
define('MODULE_PAYMENT_SOFORT_SU_ZONE_DESC', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC);
define('MODULE_PAYMENT_SOFORT_SU_STATUS_TITLE', 'sofort.de Modul aktivieren');
define('MODULE_PAYMENT_SOFORT_SU_STATUS_DESC', 'Aktiviert/deaktiviert das komplette Modul');

define('MODULE_PAYMENT_SOFORT_SU_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_SOFORT_SU_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_SOFORT_SU_KS_STATUS_TITLE', 'Käuferschutz aktiviert');
define('MODULE_PAYMENT_SOFORT_SU_KS_STATUS_DESC', 'Käuferschutz für SOFORT Überweisung aktivieren');

define('MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID_TITLE', 'Bestätigter Bestellstatus');
define('MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID_DESC', 'Bestätigter Bestellstatus<br />Bestellstatus nach abgeschlossener Transaktion.');

define('MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT_TITLE', 'Empfohlene Zahlungsweise');
define('MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT_DESC', 'Diese Zahlart als "empfohlene Zahlungsart" markieren. Auf der Bezahlseite erfolgt ein Hinweis direkt hinter der Zahlungsart.');
define('MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT_TEXT', '(Empfohlene Zahlungsweise)');

define('MODULE_PAYMENT_SOFORT_SU_TEXT_ERROR_MESSAGE', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_SU', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');
define('MODULE_PAYMENT_SOFORT_STATUS_CONFIRM_INVOICE', 'Bestellung mit {{paymentMethodStr}} erfolgreich übermittelt. Transaktions-ID: {{tId}} {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_SU_LOSS', 'Der Zahlungseingang konnte bis dato noch nicht festgestellt werden. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_DEBIT_RETURNED', 'Zu dieser Transaktion liegt eine Rücklastschrift vor. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_REFUNDED', 'Rechnungsbetrag wird zurückerstattet. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_INVOICE_CANCELED', 'Die Rechnung wurde vom Händler storniert{{time}}');

?>