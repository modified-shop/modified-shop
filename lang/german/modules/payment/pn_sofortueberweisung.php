<?php
/**
 * @version sofortueberweisung.de 4.0 - $Date: 2010-09-09 17:18:09 +0200 (Do, 09 Sep 2010) $
 * @author Payment Network AG (integration@payment-network.com)
 * @link http://www.payment-network.com/
 *
 * @copyright 2006 - 2007 Henri Schmidhuber
 * @link http://www.in-solution.de
 *
 * @link http://www.xt-commerce.com
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 *
 ***********************************************************************************
 * this file contains code based on:
 * (c) 2000 - 2001 The Exchange Project
 * (c) 2001 - 2003 osCommerce, Open Source E-Commerce Solutions
 * (c) 2003	 nextcommerce (account_history_info.php,v 1.17 2003/08/17); www.nextcommerce.org
 * (c) 2003 - 2006 XT-Commerce
 * Released under the GNU General Public License
 ***********************************************************************************
 *
 * $Id: pn_sofortueberweisung.php 304 2010-09-09 15:18:09Z poser $
 *
 */

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_TITLE', 'sofort&uuml;berweisung.de');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_TEXT_TITLE', 'sofort&uuml;berweisung.de mit K&auml;uferschutz');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION', '<div>' . (MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS != 'True' ? '<div align = "center"><a class="button" href=' . xtc_href_link(FILENAME_MODULES, 'set=payment&module=pn_sofortueberweisung&action=install&autoinstall=1', 'SSL') . '>Autoinstaller (empfohlen)</a></div><br />' : ''). '<br /><b>sofort&uuml;berweisung.de</b><br>Sobald der Kunde sofort&uuml;berweisung.de ausgew&auml;hlt hat und auf Bestellen klickt, wird eine tempor&auml;re Bestellung angelegt. Ist die Zahlung erfolgreich, wird die Bestellung fest in die Datenbank eingetragen. Bei Abbruch wird die Bestellung r&uuml;ckg&auml;ngig gemacht und die Bestellnummer verworfen, so dass bei der n&auml;chsten Bestellung die Bestellnummer um eins erh&ouml;ht wird.</div>');

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '
     <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td valign="bottom"><a href="https://www.sofortueberweisung.de/funktionsweise" target="_blank">{{image}}</a></td>
      </tr>
      <tr>
	 <td class="main"><br />%s</td>
      </tr>
    </table>');
	
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', 'Online-&Uuml;berweisung mit T&Uuml;V gepr&uuml;ftem Datenschutz ohne Registrierung. Bitte halten Sie Ihre Online-Banking-Daten (PIN/TAN) bereit.  Dienstleistungen/Waren werden bei Verf&uuml;gbarkeit SOFORT geliefert bzw. versendet!');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', 'Direkt&uuml;berweisung mit K&auml;uferschutz: Dieser K&auml;uferschutz sichert Ihnen als Kunde bei Nichtlieferung eine Geld-zurück-Garantie zu. sofortüberweisung.de ist T&Uuml;V zertifiziert (Gepr&uuml;fter Datenschutz, Gepr&uuml;ftes Zahlungssystem). Bitte halten Sie Ihre Online-Banking-Daten (PIN/TAN) bereit. Dienstleistungen/Waren werden bei Verf&uuml;gbarkeit SOFORT geliefert bzw. versendet!');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'sofort&uuml;berweisung.de ist der kostenlose, T&Uuml;V-zertifizierte Zahlungsdienst der Payment Network AG. Ihre Vorteile: keine zus&auml;tzliche Registrierung, automatische Abbuchung von Ihrem Online-Bankkonto, h&ouml;chste Sicherheitsstandards und sofortiger Versand von Lagerware. F&uuml;r die Bezahlung mit sofort&uuml;berweisung.de ben&ouml;tigen Sie Ihre eBanking Zugangsdaten, d.h. Bankverbindung, Kontonummer, PIN und TAN.');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS_TITLE', 'sofort&uuml;berweisung.de Modul aktivieren');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per sofort&uuml;berweisung.de akzeptieren?');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_USER_ID_TITLE', 'Kundennummer');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_USER_ID_DESC', 'Ihre Kundennummer bei der sofort&uuml;berweisung.de');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID_TITLE', 'Projektnummer');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID_DESC', 'Die verantwortliche Projektnummer bei der sofort&uuml;berweisung.de, zu der die Zahlung geh&ouml;rt');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_PASSWORD_TITLE', 'Projekt-Passwort');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_PASSWORD_DESC', 'Das Projekt-Passwort (unter Erweiterte Einstellungen / Passw&ouml;rter und Hashfunktionen)');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD_TITLE', 'Benachrichtigungspasswort');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD_DESC', 'Das Benachrichtigungspasswort (unter Erweiterte Einstellungen / Passw&ouml;rter und Hashfunktionen)');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM_TITLE', 'Hash-Algorithmus:');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM_DESC', 'Der Hash-Algorithmus (unter Erweiterte Einstellungen / Passw&ouml;rter und Hashfunktionen)');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE_TITLE', 'Hash-Algorithmus: '.MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM. '<br /><br />Zahlungszone');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_CURRENCY_TITLE', 'Transaktionsw&auml;hrung');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_CURRENCY_DESC', 'Empfangende W&auml;hrung laut sofort&uuml;berweisung.de Projekteinstellung');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ORDER_STATUS_ID_TITLE', 'best&auml;tigter Bestellstatus');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ORDER_STATUS_ID_DESC', 'Order Status nach Eingang einer Bestellung, bei der von sofort&uuml;berweisung.de eine erfolgreiche Zahlungsbest&auml;tigung &uuml;bermittelt wurde');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID_TITLE', 'Tempor&auml;rer Bestellstatus');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID_DESC', 'Bestellstatus f&uuml;r noch nicht abgeschlossene Transaktionen');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_UNC_STATUS_ID_TITLE', 'Zu &uuml;berpr&uuml;fender Bestellstatus');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_UNC_STATUS_ID_DESC', 'Order Status nach Eingang einer Bestellung bei der eine fehlerhafte Zahlungsbest&auml;tigung &uuml;bermittelt wurde');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_RECEIVED_STATUS_ID_TITLE', 'Bestellstatus nach Geldeingang');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_RECEIVED_STATUS_ID_DESC', 'Status der Bestellung nachdem das Geld auf Ihrem Konto eingegangen ist. (Voraussetzung: Konto bei der <u><a href="http://www.sofort-bank.com" target="_blank">Sofort Bank</a></u>)');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_LOSS_STATUS_ID_TITLE', 'Bestellstatus wenn kein Geld angekommen ist');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_LOSS_STATUS_ID_DESC', 'Status der Bestellung falls kein Geld auf Ihrem Konto eingegangen ist. (Voraussetzung: Konto bei der <u><a href="http://www.sofort-bank.com" target="_blank">Sofort Bank</a></u>)');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_REASON_1_TITLE', 'Verwendungszweck Zeile 1');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_REASON_1_DESC', 'Im Verwendungszweck 1 k&ouml;nnen folgende Optionen ausgew&auml;hlt werden');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_REASON_2_TITLE', 'Verwendungszweck Zeile 2');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_REASON_2_DESC', 'Im Verwendungszweck (maximal 27 Zeichen) werden folgende Platzhalter ersetzt:<br /> {{order_id}}<br />{{order_date}}<br />{{customer_id}}<br />{{customer_name}}<br />{{customer_company}}<br />{{customer_email}}');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_IMAGE_TITLE', 'Zahlungsauswahl Grafik / Text');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_IMAGE_DESC', 'Angezeigte Grafik / Text bei der Auswahl Zahlungsoptionen');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS_TITLE', 'K&auml;uferschutz aktiviert');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS_DESC', 'Voraussetzung: Konto bei der <u><a href="http://www.sofort-bank.com" target="_blank">Sofort Bank</a></u> und Aktivierung in Ihren Projekteinstellungen. Bitte unbedingt &uuml;ber <u><a href="https://kaeuferschutz.sofort-bank.com/consumerProtections/index/'.MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID.'">diesen Link</a></u> vorher &uuml;berpr&uuml;fen und hier nur aktivieren wenn auch Ihr Projekt daf&uuml;r freigeschaltet wurde und damit verbunden die H&auml;ndlerbedingungen zum K&auml;uferschutz akzeptiert wurden.');

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_ERROR_HEADING', 'Folgender Fehler wurde von sofort&uuml;berweisung.de w&auml;hrend des Prozesses gemeldet:');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_ERROR_MESSAGE', 'Zahlung via sofort&uuml;berweisung.de ist leider nicht m&ouml;glich oder wurde auf Kundenwunsch abgebrochen. Bitte w&auml;hlen Sie eine andere Zahlungsweise.');

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_INFO', 'Zahlen Sie bequem mit dem zertifizierten und gepr&uuml;ften Online Banking System Sofort&uuml;berweisung');
?>
