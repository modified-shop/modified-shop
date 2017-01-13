<?php
/* -----------------------------------------------------------------------------------------
   $Id: moneybookers_wlt.php 3598 2012-09-06 06:22:36Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_TEXT_TITLE', 'Skrill E-Wallet');
$_var = 'Skrill E-Wallet &uuml;ber Skrill';
if (_PAYMENT_MONEYBOOKERS_EMAILID=='') {
  $_var.='<br /><br /><b><font color="red">Bitte nehmen Sie zuerst die Einstellungen unter<br /> Erw. Konfiguration -> Partner -> Skrill.com vor!</font></b>';
}
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_TEXT_DESCRIPTION', $_var);
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_NOCURRENCY_ERROR', 'Es ist keine von Skrill akzeptierte W&auml;hrung installiert!');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_ERRORTEXT1', 'payment_error=');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_TEXT_INFO', '');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_ERRORTEXT2', '&error=Fehler w&auml;hrend Ihrer Bezahlung bei Skrill!');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_ORDER_TEXT', 'Bestelldatum: ');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_TEXT_ERROR', 'Fehler bei Zahlung!');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_CONFIRMATION_TEXT', 'Danke f&uuml;r Ihre Bestellung!');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_TRANSACTION_FAILED_TEXT', 'Ihre Zahlungstransaktion bei skrill.com ist fehlgeschlagen. Bitte versuchen Sie es nochmal, oder w&auml;hlen Sie eine andere Zahlungsm&ouml;glichkeit!');

define('MODULE_PAYMENT_MONEYBOOKERS_WLT_STATUS_TITLE', 'Skrill aktivieren');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per Skrill akzeptieren?');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_MONEYBOOKERS_WLT_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
?>