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
    (c) 2010 Payment Network AG - http://www.payment-network.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('MODULE_PAYMENT_'.$sofort_code.'_TEXT_TITLE') OR define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_TITLE', 'Sofort.');
defined('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION') OR define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION', 'Sofort. ist der kostenlose, T&Uuml;V-zertifizierte Zahlungsdienst der SOFORT GmbH.');
defined('MODULE_PAYMENT_'.$sofort_code.'_TEXT_INFO') OR define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_INFO', 'Zahlen Sie bequem mit dem zertifizierten und gepr&uuml;ften Online Banking System Sofort. der SOFORT GmbH.');

// checkout
defined('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE') OR define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td valign="bottom">
	      <a onclick="javascript:window.open(\'https://images.sofort.com/de/su/landing.php\',\'Kundeninformationen\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1020, height=900\');" style="float:left; width:auto;">{{image}}</a>
	    </td>
	  </tr>
	  <tr>
	    <td class="main">{{text}}</td>
	  </tr>
	</table>');
defined('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT') OR define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'Sofort.');
defined('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT') OR define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', '
  <ul>
    <li>Einfach und direkt bezahlen per Online Banking.</li>
  </ul>');

// admin
defined('MODULE_PAYMENT_'.$sofort_code.'_STATUS_TITLE') OR define('MODULE_PAYMENT_'.$sofort_code.'_STATUS_TITLE', 'Sofort. aktivieren');
defined('MODULE_PAYMENT_'.$sofort_code.'_STATUS_DESC') OR define('MODULE_PAYMENT_'.$sofort_code.'_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per Sofort. akzeptieren?');
define('MODULE_PAYMENT_'.$sofort_code.'_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_'.$sofort_code.'_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_'.$sofort_code.'_TMP_ORDER_TITLE', 'Tempor&auml;re Bestellung');
define('MODULE_PAYMENT_'.$sofort_code.'_TMP_ORDER_DESC', 'M&ouml;chten Sie eine tempor&auml;re Bestellung anlegen?');
define('MODULE_PAYMENT_'.$sofort_code.'_LOGGING_TITLE', 'Logging aktivieren');
define('MODULE_PAYMENT_'.$sofort_code.'_LOGGING_DESC', 'M&ouml;chten Sie das Logging aktivieren?<br/>Die Logfiles werden im Ordner /log abgelegt.');
define('MODULE_PAYMENT_'.$sofort_code.'_KEY_TITLE', 'Konfigurationsschl&uuml;ssel');
define('MODULE_PAYMENT_'.$sofort_code.'_KEY_DESC', 'Den Konfigurationsschl&uuml;ssel finden Sie in den Einstellungen von Sofort.');
define('MODULE_PAYMENT_'.$sofort_code.'_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_'.$sofort_code.'_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt');
define('MODULE_PAYMENT_'.$sofort_code.'_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_'.$sofort_code.'_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_'.$sofort_code.'_CURRENCY_TITLE', 'Transaktionsw&auml;hrung');
define('MODULE_PAYMENT_'.$sofort_code.'_CURRENCY_DESC', 'Empfangende W&auml;hrung laut Sofort. Projekteinstellung');
define('MODULE_PAYMENT_'.$sofort_code.'_ORDER_STATUS_ID_TITLE', 'best&auml;tigter Bestellstatus');
define('MODULE_PAYMENT_'.$sofort_code.'_ORDER_STATUS_ID_DESC', 'Order Status nach Eingang einer Bestellung, bei der eine erfolgreiche Zahlungsbest&auml;tigung &uuml;bermittelt wurde');
define('MODULE_PAYMENT_'.$sofort_code.'_TMP_STATUS_ID_TITLE', 'Tempor&auml;rer Bestellstatus');
define('MODULE_PAYMENT_'.$sofort_code.'_TMP_STATUS_ID_DESC', 'Bestellstatus f&uuml;r noch nicht abgeschlossene Transaktionen');
define('MODULE_PAYMENT_'.$sofort_code.'_UNC_STATUS_ID_TITLE', 'Zu &uuml;berpr&uuml;fender Bestellstatus');
define('MODULE_PAYMENT_'.$sofort_code.'_UNC_STATUS_ID_DESC', 'Order Status nach Eingang einer Bestellung bei der eine fehlerhafte Zahlungsbest&auml;tigung &uuml;bermittelt wurde');
define('MODULE_PAYMENT_'.$sofort_code.'_REC_STATUS_ID_TITLE', 'Bestellstatus nach Geldeingang');
define('MODULE_PAYMENT_'.$sofort_code.'_REC_STATUS_ID_DESC', 'Status der Bestellung nachdem das Geld auf Ihrem Konto eingegangen ist. (Voraussetzung: Konto bei der <u><a href="https://www.handelsbank.com/" target="_blank">Deutsche Handelsbank</a></u>)');
define('MODULE_PAYMENT_'.$sofort_code.'_REF_STATUS_ID_TITLE', 'Bestellstatus nach R&uuml;ckbuchung');
define('MODULE_PAYMENT_'.$sofort_code.'_REF_STATUS_ID_DESC', 'Status der Bestellung nachdem eine R&uuml;ckbuchung erfolgt ist.');
define('MODULE_PAYMENT_'.$sofort_code.'_LOSS_STATUS_ID_TITLE', 'Bestellstatus wenn kein Geld angekommen ist');
define('MODULE_PAYMENT_'.$sofort_code.'_LOSS_STATUS_ID_DESC', 'Status der Bestellung falls kein Geld auf Ihrem Konto eingegangen ist. (Voraussetzung: Konto bei der <u><a href="https://www.handelsbank.com/" target="_blank">Deutsche Handelsbank</a></u>)');
define('MODULE_PAYMENT_'.$sofort_code.'_REASON_1_TITLE', 'Verwendungszweck Zeile 1');
define('MODULE_PAYMENT_'.$sofort_code.'_REASON_1_DESC', 'Wenn keine Tempor&auml;re Bestellung angelegt wird, steht die Bestellnummer nicht zur Verf&uuml;gung. Deshalb sollte dann auf -TRANSACTION- gestellt werden.');
define('MODULE_PAYMENT_'.$sofort_code.'_REASON_2_TITLE', 'Verwendungszweck Zeile 2');
define('MODULE_PAYMENT_'.$sofort_code.'_REASON_2_DESC', 'Im Verwendungszweck (maximal 27 Zeichen) werden folgende Platzhalter ersetzt:<br /> {{order_id}}<br />{{order_date}}<br />{{customer_id}}<br />{{customer_name}}<br />{{customer_company}}<br />{{customer_email}}');
define('MODULE_PAYMENT_'.$sofort_code.'_IMAGE_TITLE', 'Zahlungsauswahl Grafik / Text');
define('MODULE_PAYMENT_'.$sofort_code.'_IMAGE_DESC', 'Angezeigte Grafik / Text bei der Auswahl Zahlungsoptionen');
define('MODULE_PAYMENT_'.$sofort_code.'_USER_ID_TITLE', 'Kundennummer');
define('MODULE_PAYMENT_'.$sofort_code.'_USER_ID_DESC', 'Ihre Kundennummer bei Sofort.');
define('MODULE_PAYMENT_'.$sofort_code.'_PROJECT_ID_TITLE', 'Projektnummer');
define('MODULE_PAYMENT_'.$sofort_code.'_PROJECT_ID_DESC', 'Die verantwortliche Projektnummer bei Sofort., zu der die Zahlung geh&ouml;rt');
define('MODULE_PAYMENT_'.$sofort_code.'_PROJECT_PASS_TITLE', 'Projekt-Passwort');
define('MODULE_PAYMENT_'.$sofort_code.'_PROJECT_PASS_DESC', 'Das Projekt-Passwort (unter Erweiterte Einstellungen / Passw&ouml;rter und Hashfunktionen)');
define('MODULE_PAYMENT_'.$sofort_code.'_NOTIFY_PASS_TITLE', 'Benachrichtigungspasswort');
define('MODULE_PAYMENT_'.$sofort_code.'_NOTIFY_PASS_DESC', 'Das Benachrichtigungspasswort (unter Erweiterte Einstellungen / Passw&ouml;rter und Hashfunktionen)');
define('MODULE_PAYMENT_'.$sofort_code.'_HASH_ALGORITHM_TITLE', 'Hash-Algorithmus:');
define('MODULE_PAYMENT_'.$sofort_code.'_HASH_ALGORITHM_DESC', 'Der Hash-Algorithmus (unter Erweiterte Einstellungen / Passw&ouml;rter und Hashfunktionen)');
define('MODULE_PAYMENT_'.$sofort_code.'_DESCRIPTION_INSTALL', '<br/><br/>Wollen Sie geeignete Bestellstatus installieren?<br/>Dabei werden die aktuell eingestellten Status &uuml;berschrieben.');

// status
defined('TEXT_NO_STATUSUPDATE') OR define('TEXT_NO_STATUSUPDATE', 'keine Statusaktualisierung');

// error
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_ERROR_HEADING', 'Folgender Fehler wurde von Sofort. w&auml;hrend des Prozesses gemeldet:');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_ERROR_MESSAGE', 'Zahlung via Sofort. ist leider nicht m&ouml;glich oder wurde auf Kundenwunsch abgebrochen. Bitte w&auml;hlen Sie eine andere Zahlungsweise.');

// callback
defined('TEXT_SOFORT_NOT_CREDITED_YET') OR define('TEXT_SOFORT_NOT_CREDITED_YET', 'Sofort. erfolgreich abgeschlossen');
defined('TEXT_SOFORT_NOT_CREDITED') OR define('TEXT_SOFORT_NOT_CREDITED', 'Geld nicht auf Konto eingegangen');
defined('TEXT_SOFORT_LOSS') OR define('TEXT_SOFORT_LOSS', 'Bestellung pr&uuml;fen');
defined('TEXT_SOFORT_RECEIVED') OR define('TEXT_SOFORT_RECEIVED', 'Geld auf Konto eingegangen');
defined('TEXT_SOFORT_CREDITED') OR define('TEXT_SOFORT_CREDITED', TEXT_SOFORT_RECEIVED);
defined('TEXT_SOFORT_REFUNDED') OR define('TEXT_SOFORT_REFUNDED', 'Geld wurde komplett zur&uuml;ckerstattet');
defined('TEXT_SOFORT_CANCELED') OR define('TEXT_SOFORT_CANCELED', 'Sofort. abgebrochen');
defined('TEXT_SOFORT_WAIT_FOR_MONEY') OR define('TEXT_SOFORT_WAIT_FOR_MONEY', 'Auf Zahlungseingang warten');
defined('TEXT_SOFORT_CONFIRMATION_PERIOD_EXPIRED') OR define('TEXT_SOFORT_CONFIRMATION_PERIOD_EXPIRED', 'Sofort. timeout');
defined('TEXT_SOFORT_REJECTED') OR define('TEXT_SOFORT_REJECTED', 'Sofort. abgelehnt');
defined('TEXT_SOFORT_SOFORT_BANK_ACCOUNT_NEEDED') OR define('TEXT_SOFORT_SOFORT_BANK_ACCOUNT_NEEDED', TEXT_SOFORT_NOT_CREDITED_YET);

define('MODULE_PAYMENT_'.$sofort_code.'_ERROR_TRANSACTION', "Fehler beim Callback\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_ERROR_PAYMENT', "Zahlung noch nicht erhalten\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_ERROR_UNEXPECTED_STATUS', "Fehler (SU204): Unerwarteter Status\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_SUCCESS_TRANSACTION', "Zahlung erfolgreich\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_SUCCESS_PAYMENT', "Zahlung erhalten\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_SUCCESS_REFUNDED', "Zahlung erstattet\nTransaction-ID: %s");

// order status
$SOFORT_INST_ORDER_STATUS_TMP_NAME = 'Temp';
$SOFORT_INST_ORDER_STATUS_UNC_NAME = 'Warten';
$SOFORT_INST_ORDER_STATUS_LOSS_NAME = 'Warten';
$SOFORT_INST_ORDER_STATUS_REC_NAME = 'Zahlungseingang';
$SOFORT_INST_ORDER_STATUS_REF_NAME = 'Zahlung erstattet';
$SOFORT_INST_ORDER_STATUS_ORDER_NAME = 'Bezahlt';
?>