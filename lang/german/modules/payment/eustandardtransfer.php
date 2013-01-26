<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ptebanktransfer.php,v 1.4.1 2003/09/25 19:57:14); www.oscommerce.com
   (c) 2006 XT-Commerce (eustandardtransfer.php 998 2005-07-07)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_TITLE', 'EU-Standard Bank&uuml;berweisung');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION',
          '<br />Die billigste und einfachste Zahlungsmethode innerhalb der EU ist die &Uuml;berweisung mittels IBAN und BIC.' .
          '<br />Sobald der Betrag auf unserem Konto eingegangen ist, werden wir Ihre Bestellung versenden.<br />'.
          '<br /><br />Bitte verwenden Sie folgende Daten f&uuml;r die &Uuml;berweisung des Gesamtbetrages:<br />' .
          '<br />Name der Bank: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM .
          '<br />Empf&auml;nger: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH .
          '<br />Bankleitzahl: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM .
          '<br />Kontonummer: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM .
          '<br />IBAN: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN .
          '<br />BIC/SWIFT: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC .
//        '<br />Sort Code: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_SORTCODE .
          '');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO','Bitte &uuml;berweisen Sie den f&auml;lligen Rechnungsbetrag auf unser Konto.<br />Die Kontodaten erhalten Sie im n&auml;chsten Bestellschritt und nach Bestellannahme per E-Mail');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS_TITLE','Bank&uuml;berweisung erlauben');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS_DESC','M&ouml;chten Sie &Uuml;berweisungen akzeptieren?');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH_TITLE','Empf&auml;nger');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH_DESC','Der Empf&auml;nger f&uuml;r die &Uuml;berweisung.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM_TITLE','Name der Bank');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM_DESC','Der volle Name der Bank');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM_TITLE','Bankleitzahl');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM_DESC','Die Bankleitzahl des angegebenen Kontos.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM_TITLE','Kontonummer');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM_DESC','Ihre Kontonummer.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN_TITLE','Bank Account IBAN');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN_DESC','International account id.<br />(Fragen Sie Ihre Bank, wenn Sie nicht sicher sind.)');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC_TITLE','Bank Bic');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC_DESC','International bank id.<br />(Fragen Sie Ihre Bank, wenn Sie nicht sicher sind.)');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER_TITLE','Anzeigereihenfolge');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER_DESC','Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED_TITLE' , 'Erlaubte Zonen');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

  // BOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_NEG_SHIPPING_TITLE', 'Ausschlu&szlig; bei Versandmodulen');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_NEG_SHIPPING_DESC', 'Dieses Zahlungsmodul deaktivieren wenn Versandmodul gew&auml;hlt (Komma separierte Liste)');
  // EOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
?>