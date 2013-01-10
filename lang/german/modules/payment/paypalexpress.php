<?php
/* -----------------------------------------------------------------------------------------
   $Id: paypalexpress.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(paypal.php,v 1.7 2002/04/17); www.oscommerce.com
   (c) 2003	 nextcommerce (paypal.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
---------------------------------------------------------------------------------------*/
define('MODULE_PAYMENT_PAYPALEXPRESS_TEXT_TITLE', 'PayPal Express Kaufabwicklung (Warenkorb)');
define('MODULE_PAYMENT_PAYPALEXPRESS_TEXT_DESCRIPTION', 'PayPal Express Kaufabwicklung');
define('MODULE_PAYMENT_PAYPAL_TEXT_EXTENDED_DESCRIPTION', '<strong><font color="red">ACHTUNG:</font></strong> Bitte nehmen Sie noch die Einstellungen unter Erw. Konfiguration -> Partner -> <a href="'.xtc_href_link(FILENAME_CONFIGURATION, 'gID=111125').'">PayPal</a> vor!');
define('MODULE_PAYMENT_PAYPALEXPRESS_TEXT_INFO', '');
define('MODULE_PAYMENT_PAYPALEXPRESS_STATUS_TITLE', 'PayPal-Express Modul aktivieren');
define('MODULE_PAYMENT_PAYPALEXPRESS_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per PayPal-Express akzeptieren?');
define('MODULE_PAYMENT_PAYPALEXPRESS_ALT_BUTTON', ' Bezahlen mit PayPal ');

// Hendrik - 15.07.2010 - exlusion config for shipping modules 
define('MODULE_PAYMENT_PAYPALEXPRESS_NEG_SHIPPING_TITLE', 'Ausschlu&szlig; bei Versandmodulen'); 
define('MODULE_PAYMENT_PAYPALEXPRESS_NEG_SHIPPING_DESC', 'Dieses Zahlungsmodul deaktivieren wenn Versandmodul gew&auml;hlt (Komma separierte Liste)');
?>