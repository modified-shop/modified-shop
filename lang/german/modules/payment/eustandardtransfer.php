<?php
/* -----------------------------------------------------------------------------------------
   $Id: eustandardtransfer.php 998 2005-07-07 14:18:20Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ptebanktransfer.php,v 1.4.1 2003/09/25 19:57:14); www.oscommerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_EUTRANSFER_TEXT_TITLE', 'EU-Standard Bank Transfer');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_TITLE', 'EU-Standard Bank Transfer');
  define('MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION', 
          '<br />Die billigste und einfachste Zahlungsmethode innerhalb der EU ist die &Uuml;berweisung mittels IBAN und BIC.' .
					'<br />Bitte verwenden Sie folgende Daten f&uuml;r die &Uuml;berweisung des Gesamtbetrages:<br />' .
          '<br />Name der Bank: ' . MODULE_PAYMENT_EUTRANSFER_BANKNAM .
          '<br />Empf&auml;nger: ' . MODULE_PAYMENT_EUTRANSFER_BRANCH .
          '<br />Bankleitzahl: ' . MODULE_PAYMENT_EUTRANSFER_ACCNAM .
          '<br />Kontonummer: ' . MODULE_PAYMENT_EUTRANSFER_ACCNUM .
          '<br />IBAN: ' . MODULE_PAYMENT_EUTRANSFER_ACCIBAN .
          '<br />BIC/SWIFT: ' . MODULE_PAYMENT_EUTRANSFER_BANKBIC .
//        '<br />Sort Code: ' . MODULE_PAYMENT_EUTRANSFER_SORTCODE .
          '<br /><br />Die Ware wird erst ausgeliefert, wenn der Betrag auf unserem Konto eingegangen ist.<br />');

  define('MODULE_PAYMENT_EUTRANSFER_TEXT_INFO','Bitte &uuml;berweisen Sie den f&auml;lligen Rechnungsbetrag auf unser Konto. Die Kontodaten erhalten Sie nach Bestellannahme per E-Mail');
  define('MODULE_PAYMENT_EUTRANSFER_STATUS_TITLE','Allow Bank Transfer Payment');
  define('MODULE_PAYMENT_EUTRANSFER_STATUS_DESC','M&ouml;chten Sie &Uuml;berweisungen akzeptieren?');

  define('MODULE_PAYMENT_EUTRANSFER_BRANCH_TITLE','Empf&auml;nger');
  define('MODULE_PAYMENT_EUTRANSFER_BRANCH_DESC','Der Empf&auml;nger f&uuml;r die &Uuml;berweisung.');

  define('MODULE_PAYMENT_EUTRANSFER_BANKNAM_TITLE','Name der Bank');
  define('MODULE_PAYMENT_EUTRANSFER_BANKNAM_DESC','Der volle Name der Bank');

  define('MODULE_PAYMENT_EUTRANSFER_ACCNAM_TITLE','Bankleitzahl');
  define('MODULE_PAYMENT_EUTRANSFER_ACCNAM_DESC','Die Bankleitzahl des angegebenen Kontos.');

  define('MODULE_PAYMENT_EUTRANSFER_ACCNUM_TITLE','Kontonummer');
  define('MODULE_PAYMENT_EUTRANSFER_ACCNUM_DESC','Ihre Kontonummer.');

  define('MODULE_PAYMENT_EUTRANSFER_ACCIBAN_TITLE','Bank Account IBAN');
  define('MODULE_PAYMENT_EUTRANSFER_ACCIBAN_DESC','International account id.<br />(Fragen Sie Ihre Bank, wenn Sie nicht sicher sind.)');

  define('MODULE_PAYMENT_EUTRANSFER_BANKBIC_TITLE','Bank Bic');
  define('MODULE_PAYMENT_EUTRANSFER_BANKBIC_DESC','International bank id.<br />(Fragen Sie Ihre Bank, wenn Sie nicht sicher sind.)');

  define('MODULE_PAYMENT_EUTRANSFER_SORT_ORDER_TITLE','Anzeigereihenfolge');
  define('MODULE_PAYMENT_EUTRANSFER_SORT_ORDER_DESC','Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED_TITLE' , 'Erlaubte Zonen');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

?>
