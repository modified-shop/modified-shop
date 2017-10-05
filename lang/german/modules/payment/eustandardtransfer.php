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
   (c) 2003 xtCommerce www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_TITLE', 'EU-Standard Bank Transfer');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION', 
          '<br />Die g&uuml;nstigste und einfachste Zahlungsmethode innerhalb der EU ist die &Uuml;berweisung mittels IBAN und BIC.' .
					'<br />Bitte verwenden Sie folgende Daten f&uuml;r die &Uuml;berweisung des Gesamtbetrages:<br />' .
          '<br />Name der Bank: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM : '') .
          '<br />Empf&auml;nger: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH : '') .
          '<br />Bankleitzahl: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM : '') .
          '<br />Kontonummer: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM : '') .
          '<br />IBAN: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN : '') .
          '<br />BIC/SWIFT: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC : '') .
          '<br /><br />Die Ware wird erst ausgeliefert, wenn der Betrag auf unserem Konto eingegangen ist.<br />');

  if (MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS == 'True') {
    define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO','Bitte &uuml;berweisen Sie den f&auml;lligen Rechnungsbetrag auf unser Konto. Die Kontodaten erhalten Sie im letzten Schritt der Bestellung.');
  } else {
    define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO','Bitte &uuml;berweisen Sie den f&auml;lligen Rechnungsbetrag auf unser Konto. Die Kontodaten erhalten Sie nach Bestellannahme per E-Mail.');
  }
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS_TITLE','EU-Standard Bank Transfer Modul aktivieren');
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

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ZONE_TITLE' , 'Zahlungszone');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ZONE_DESC' , 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
  
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ORDER_STATUS_ID_TITLE' , 'Bestellstatus festlegen');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ORDER_STATUS_ID_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS_TITLE' , 'Bankdaten anzeigen');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS_DESC' , 'Sollen auf der Erfolgsseite die Bankdaten angezeigt werden?');
?>