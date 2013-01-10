<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(paypal.php,v 1.7 2002/04/17); www.oscommerce.com
   (c) 2003         nextcommerce (paypal.php,v 1.4 2003/08/13); www.nextcommerce.org
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
define('MODULE_PAYMENT_PAYPAL_TEXT_TITLE', 'PayPal');
define('MODULE_PAYMENT_PAYPAL_TEXT_INFO','<img src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x47.gif" />');
define('MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION', 'After "confirm" your will be routet to PayPal to pay your order.<br />Back in shop you will get your order-mail.<br />PayPal is the safer way to pay online. We keep your details safe from others and can help you get your money back if something ever goes wrong.');
define('MODULE_PAYMENT_PAYPAL_TEXT_EXTENDED_DESCRIPTION', '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration! (Adv. Configuration -> Partner -> <a href="'.xtc_href_link(FILENAME_CONFIGURATION, 'gID=111125').'"><strong>PayPal</strong></a>)!');
define('MODULE_PAYMENT_PAYPAL_ALLOWED_TITLE' , 'Allowed zones');
define('MODULE_PAYMENT_PAYPAL_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_PAYMENT_PAYPAL_STATUS_TITLE', 'Enable PayPal module');
define('MODULE_PAYMENT_PAYPAL_STATUS_DESC', 'Do you want to accept PayPal payments?');
define('MODULE_PAYMENT_PAYPAL_SORT_ORDER_TITLE' , 'Sort order');
define('MODULE_PAYMENT_PAYPAL_SORT_ORDER_DESC' , 'Sort order of the view. Lowest numeral will be displayed first');
define('MODULE_PAYMENT_PAYPAL_ZONE_TITLE' , 'Payment zone');
define('MODULE_PAYMENT_PAYPAL_ZONE_DESC' , 'If a zone is choosen, the payment method will be valid for this zone only.');

// BOF - Hendrik - 2010-08-11 - exlusion config for shipping modules
define('MODULE_PAYMENT_PAYPAL_NEG_SHIPPING_TITLE', 'Exclusion in case of shipping');
define('MODULE_PAYMENT_PAYPAL_NEG_SHIPPING_DESC', 'deactivate this payment if one of these shippingtypes are selected (list separated by comma)');
// EOF - Hendrik - 2010-08-11 - exlusion config for shipping modules
?>