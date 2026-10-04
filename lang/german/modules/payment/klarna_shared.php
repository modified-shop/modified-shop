<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$lang_array = array(
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE' => '',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_DESCRIPTION' => 'Bevor Sie die Klarna Payments Zahlungsarten einrichten k&ouml;nnen, ist die Er&ouml;ffnung eines Kontos f&uuml;r H&auml;ndler bei Klarna erforderlich. Sie erhalten im Anschluss Informationen sowie Zugangsdaten, die Sie f&uuml;r das Einrichten ben&ouml;tigen. Sollten Sie bereits eine Kundennummer bei Klarna haben, diese aber nicht nach Schema Kxxxxxx ist, senden Sie bitte eine E-Mail an <a href="mailto:vertrieb@klarna.com">vertrieb@klarna.com</a>.<br /><br />
    <img src="../lang/german/admin/images/icon.gif" border="0" />
    <a href="https://www.klarna.com/de/verkaeufer/" target="_blank" style="text-decoration: underline; font-weight: bold;">Jetzt Klarna Konto hier erstellen.</a>
    <img src="images/icon_popup.gif" border="0" />',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_INFO' => '',
  'MODULE_PAYMENT_'.$klarna_code.'_ALLOWED_TITLE' => 'Erlaubte Zonen',
  'MODULE_PAYMENT_'.$klarna_code.'_ALLOWED_DESC' => 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))',
  'MODULE_PAYMENT_'.$klarna_code.'_STATUS_TITLE' => 'Modul aktivieren',
  'MODULE_PAYMENT_'.$klarna_code.'_STATUS_DESC' => 'M&ouml;chten Sie Zahlungen mit diesem Modul akzeptieren?',
  'MODULE_PAYMENT_'.$klarna_code.'_SORT_ORDER_TITLE' => 'Anzeigereihenfolge',
  'MODULE_PAYMENT_'.$klarna_code.'_SORT_ORDER_DESC' => 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt',
  'MODULE_PAYMENT_'.$klarna_code.'_ZONE_TITLE' => 'Zahlungszone',
  'MODULE_PAYMENT_'.$klarna_code.'_ZONE_DESC' => 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.',
  'MODULE_PAYMENT_'.$klarna_code.'_ORDER_STATUS_ID_TITLE' => 'Bestellstatus festlegen',
  'MODULE_PAYMENT_'.$klarna_code.'_ORDER_STATUS_ID_DESC' => 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen',
  'MODULE_PAYMENT_'.$klarna_code.'_CAPTURE_TITLE' => 'Aktivieren',
  'MODULE_PAYMENT_'.$klarna_code.'_CAPTURE_DESC' => 'Soll die Bestellung automatisch aktiviert werden?',

  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_HEADING' => 'Klarna',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE' => 'Die Zahlung wurde abgebrochen.',

  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_VERSION' => '<b>Modul Version</b><br/>',

  // how the customer paid inside Klarna, appended to the title of the order
  'MODULE_PAYMENT_KLARNA_METHOD_INVOICE' => 'Rechnung',
  'MODULE_PAYMENT_KLARNA_METHOD_FINANCING' => 'Finanzierung',
  'MODULE_PAYMENT_KLARNA_METHOD_DIRECT_DEBIT' => 'Lastschrift',
  'MODULE_PAYMENT_KLARNA_METHOD_BANK_TRANSFER' => '&Uuml;berweisung',
  'MODULE_PAYMENT_KLARNA_METHOD_CARD' => 'Karte',

  'MODULE_PAYMENT_KLARNA_MERCHANT_ID_TITLE' => 'Benutzername',
  'MODULE_PAYMENT_KLARNA_MERCHANT_ID_DESC' => 'Klarna API Benutzername',
  'MODULE_PAYMENT_KLARNA_SHARED_SECRET_TITLE' => 'Passwort',
  'MODULE_PAYMENT_KLARNA_SHARED_SECRET_DESC' => 'Klarna API Passwort',
  'MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID_TITLE' => 'Bestellstatus Betrugspr&uuml;fung',
  'MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID_DESC' => 'Bestellungen, die Klarna noch auf Betrug pr&uuml;ft, auf diesen Status setzen. Erst nach der Freigabe durch Klarna wird die Zahlung aktiviert.',
  'MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID_TITLE' => 'Bestellstatus Ablehnung',
  'MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID_DESC' => 'Bestellungen, die Klarna nach der Pr&uuml;fung ablehnt, auf diesen Status setzen.',
  'MODULE_PAYMENT_KLARNA_MODE_TITLE' => 'Mode',
  'MODULE_PAYMENT_KLARNA_MODE_DESC' => 'Klarna Mode',
  'MODULE_PAYMENT_KLARNA_TEXT' => 'Klarna',
  'MODULE_PAYMENT_KLARNA_CHECK_BUTTON' => 'Klarna Zahlarten pr&uuml;fen',
  'MODULE_PAYMENT_KLARNA_CHECK_HEADING' => 'Klarna Zahlarten-Kategorien',
  'MODULE_PAYMENT_KLARNA_CHECK_SCOPE' => 'Das Ergebnis gilt nur f&uuml;r das Shop-Land %s, die W&auml;hrung %s, den Testbetrag %s und den Modus %s.',
  'MODULE_PAYMENT_KLARNA_CHECK_COL_CATEGORY' => 'Kategorie von Klarna',
  'MODULE_PAYMENT_KLARNA_CHECK_COL_USED' => 'Verwendung im Checkout',
  'MODULE_PAYMENT_KLARNA_CHECK_USED_YES' => 'wird verwendet',
  'MODULE_PAYMENT_KLARNA_CHECK_USED_NO' => 'wird nicht verwendet',
  'MODULE_PAYMENT_KLARNA_CHECK_STATE_ACTIVE' => 'installiert und aktiv',
  'MODULE_PAYMENT_KLARNA_CHECK_STATE_INACTIVE' => 'installiert, aber nicht aktiv',
  'MODULE_PAYMENT_KLARNA_CHECK_STATE_MISSING' => 'nicht installiert',
  'MODULE_PAYMENT_KLARNA_CHECK_MODULE' => 'Modul klarna: %s',
  'MODULE_PAYMENT_KLARNA_CHECK_CATEGORY' => 'Das Modul verwendet die Kategorie &quot;%s&quot;. Es nimmt &quot;klarna&quot;, wenn Klarna diese Kategorie liefert, sonst die erste gelieferte.',
  'MODULE_PAYMENT_KLARNA_CHECK_NONE' => 'Klarna liefert keine Zahlarten-Kategorie zur&uuml;ck. Das Modul klarna erscheint im Checkout nicht.',
  'MODULE_PAYMENT_KLARNA_CHECK_WARN_OLD' => '<b>Warnung:</b> klarna und mindestens ein altes Klarna Kategorie-Modul sind gleichzeitig aktiv (%s). Die Zahlungsseite zeigt Klarna dann doppelt. Deaktivieren Sie die alten Module.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_CREDENTIALS' => 'Benutzername oder Passwort der Klarna API fehlen. Tragen Sie beide in den Moduleinstellungen ein.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_COUNTRY' => 'Das Shop-Land ist nicht in der Landesliste zu finden.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_API' => 'Die Pr&uuml;fung ist fehlgeschlagen. Klarna meldet: %s',
);
