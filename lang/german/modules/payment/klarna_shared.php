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
  'MODULE_PAYMENT_KLARNA_CHECK_BUTTON' => 'Klarna pr&uuml;fen',
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
  'MODULE_PAYMENT_KLARNA_CHECK_WARN_OLD' => '<b>Hinweis:</b> Neben Klarna sind alte Klarna-Module aktiv (%s). Der Checkout blendet sie aus, solange Klarna aktiv ist. Sie k&ouml;nnen sie deaktivieren oder deinstallieren.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_CREDENTIALS' => 'Benutzername oder Passwort der Klarna API fehlen. Tragen Sie beide in den Moduleinstellungen ein.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_COUNTRY' => 'Das Shop-Land ist nicht in der Landesliste zu finden.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_API' => 'Die Pr&uuml;fung ist fehlgeschlagen. Klarna meldet: %s',

  'MODULE_PAYMENT_KLARNA_CHECK_WARN_PENDING' => '<b>Hinweis:</b> Der Bestellstatus Betrugspr&uuml;fung steht auf 0. Bestellungen, die Klarna noch pr&uuml;ft, sehen dann aus wie normale neue Bestellungen. Legen Sie einen eigenen Status fest.',
  'MODULE_PAYMENT_KLARNA_CHECK_WARN_REJECTED' => '<b>Hinweis:</b> Der Bestellstatus Ablehnung steht auf 0. Eine abgelehnte Bestellung beh&auml;lt dann ihren Status und erh&auml;lt nur einen Eintrag im Verlauf.',
  'MODULE_PAYMENT_KLARNA_CHECK_RULES_HEADING' => '<b>Hinweis:</b> Diese Regeln nennen alte Klarna-Module, aber nicht klarna. F&uuml;r klarna gelten sie nicht. Tragen Sie klarna zus&auml;tzlich ein:',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_GROUP' => 'Kundengruppen, Nicht erlaubte Zahlungsweisen: %1$s (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_CUSTOMERS' => 'Kunden, Nicht erlaubte Zahlungsmodule: %1$s Kunden (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_DOWNLOAD' => 'Unerlaubte Download-Zahlungsmodule (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_GV' => 'Gutschein, Unerlaubte Zahlungsmodule (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_SHIPPING' => 'Zahlarten abh&auml;ngig von der Versandart, Zahlungsarten Nr. %1$s (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_FEE' => 'Rabatt &amp; Zuschlag auf Zahlungsarten, Zahlungsart Nr. %1$s (%2$s)',

  // shown in the backup, restore and uninstall dialog of the module list
  'MODULE_PAYMENT_KLARNA_BACKUP_NOTE' => '<b>Hinweis:</b> Die Sicherung enth&auml;lt auch die Zugangsdaten und die gemeinsamen Einstellungen aller Klarna-Module.',
  'MODULE_PAYMENT_KLARNA_RESTORE_NOTE' => '<b>Hinweis:</b> Die Wiederherstellung setzt auch die Zugangsdaten und die gemeinsamen Einstellungen aller Klarna-Module auf den Stand der Sicherung zur&uuml;ck.',
  'MODULE_PAYMENT_KLARNA_REMOVE_NOTE' => '<b>Hinweis:</b> Offene Klarna-Bestellungen brauchen den Bestellstatus und die Capture-Einstellung von klarna, auch die Bestellungen alter, bereits deinstallierter Klarna-Module. Deinstallieren Sie klarna erst, wenn keine Klarna-Bestellung mehr offen ist.',
);
