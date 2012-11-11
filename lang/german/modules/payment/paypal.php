<?php
/* -----------------------------------------------------------------------------------------
   $Id: paypal.php 998 2005-07-07 14:18:20Z mz $
   XT-Commerce - community made shopping
   http://www.xt-commerce.com
   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(paypal.php,v 1.7 2002/04/17); www.oscommerce.com
   (c) 2003         nextcommerce (paypal.php,v 1.4 2003/08/13); www.nextcommerce.org
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
define('MODULE_PAYMENT_PAYPAL_TEXT_TITLE', 'PayPal Kaufabwicklung');
define('MODULE_PAYMENT_PAYPAL_TEXT_INFO','<img src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x47.gif" />');
define('MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION', 'Sie werden nach dem "Best&auml;tigen" zu PayPal geleitet um hier Ihre Bestellung zu bezahlen.<br />Danach gelangen Sie zur&uuml;ck in den Shop und erhalten Ihre Bestell-Best&auml;tigung.');
define('MODULE_PAYMENT_PAYPAL_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_PAYPAL_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_PAYPAL_STATUS_TITLE' , 'PayPal Modul aktivieren');
define('MODULE_PAYMENT_PAYPAL_STATUS_DESC' , 'M&ouml;chten Sie Zahlungen per PayPal akzeptieren?');
define('MODULE_PAYMENT_PAYPAL_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_PAYPAL_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt');
define('MODULE_PAYMENT_PAYPAL_ZONE_TITLE' , 'Zahlungszone');
define('MODULE_PAYMENT_PAYPAL_ZONE_DESC' , 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
?>
