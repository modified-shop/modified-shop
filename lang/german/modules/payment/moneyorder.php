<?php
/* -----------------------------------------------------------------------------------------
   $Id: moneyorder.php 998 2005-07-07 14:18:20Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(moneyorder.php,v 1.8 2003/02/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (moneyorder.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_MONEYORDER_TEXT_TITLE', 'Vorkasse/Bank&uuml;berweisung');
  define('MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION', 'Bankverbindung:<br />' . (defined('MODULE_PAYMENT_MONEYORDER_PAYTO') ? nl2br(MODULE_PAYMENT_MONEYORDER_PAYTO) : '') . '<br /><br />Kontoinhaber:<br />' . nl2br(STORE_OWNER) . '<br /><br />' . 'Ihre Bestellung wird nach Geldeingang auf unserem Konto an Sie versendet.');
  define('MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER', "Bankverbindung: ". (defined('MODULE_PAYMENT_MONEYORDER_PAYTO') ? MODULE_PAYMENT_MONEYORDER_PAYTO : '') . "\n\Kontoinhaber:\n" . STORE_OWNER . "\n\n" . 'Ihre Bestellung wird nach Geldeingang auf unser Konto an Sie versendet.');
  if (MODULE_PAYMENT_MONEYORDER_SUCCESS == 'True') {
    define('MODULE_PAYMENT_MONEYORDER_TEXT_INFO','Wir versenden Ihre Bestellung nach Zahlungseingang. Die Kontodaten erhalten Sie im letzten Schritt der Bestellung.');
  } else {
    define('MODULE_PAYMENT_MONEYORDER_TEXT_INFO','Wir versenden Ihre Bestellung nach Zahlungseingang. Die Kontodaten erhalten Sie nach Bestellannahme per E-Mail.');
  }
  define('MODULE_PAYMENT_MONEYORDER_STATUS_TITLE' , 'Check/Money Order Modul aktivieren');
  define('MODULE_PAYMENT_MONEYORDER_STATUS_DESC' , 'M&ouml;chten Sie Zahlungen per Check/Money Order akzeptieren?');
  define('MODULE_PAYMENT_MONEYORDER_ALLOWED_TITLE' , 'Erlaubte Zonen');
  define('MODULE_PAYMENT_MONEYORDER_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
  define('MODULE_PAYMENT_MONEYORDER_PAYTO_TITLE' , 'Zahlbar an:');
  define('MODULE_PAYMENT_MONEYORDER_PAYTO_DESC' , 'An wen sollen Zahlungen erfolgen?');
  define('MODULE_PAYMENT_MONEYORDER_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
  define('MODULE_PAYMENT_MONEYORDER_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
  define('MODULE_PAYMENT_MONEYORDER_ZONE_TITLE' , 'Zahlungszone');
  define('MODULE_PAYMENT_MONEYORDER_ZONE_DESC' , 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
  define('MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID_TITLE' , 'Bestellstatus festlegen');
  define('MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
  define('MODULE_PAYMENT_MONEYORDER_SUCCESS_TITLE' , 'Bankdaten anzeigen');
  define('MODULE_PAYMENT_MONEYORDER_SUCCESS_DESC' , 'Sollen auf der Erfolgsseite die Bankdaten angezeigt werden?');
?>
