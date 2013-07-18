<?php
/* -----------------------------------------------------------------------------------------
   $Id: ap.php 5118 2013-07-18 10:58:36Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ap.php,v 1.02 2003/02/18); www.oscommerce.com 
   (c) 2003	 nextcommerce (ap.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   austrian_post_1.05       	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   

define('MODULE_SHIPPING_AP_TEXT_TITLE', 'Austrian Post AG');
define('MODULE_SHIPPING_AP_TEXT_DESCRIPTION', 'Austrian Post AG - Worldwide Dispatch');
define('MODULE_SHIPPING_AP_TEXT_WAY', 'Dispatch to');
define('MODULE_SHIPPING_AP_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_AP_INVALID_ZONE', 'Unfortunately it is not possible to dispatch into this country');
define('MODULE_SHIPPING_AP_UNDEFINED_RATE', 'Forwarding expenses cannot be calculated for the moment');

define('MODULE_SHIPPING_AP_STATUS_TITLE' , 'Austrian Post AG');
define('MODULE_SHIPPING_AP_STATUS_DESC' , 'Do you want to offer Austrian Post shipping?');
define('MODULE_SHIPPING_AP_ALLOWED_TITLE' , 'Allowed Zones');
define('MODULE_SHIPPING_AP_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_SHIPPING_AP_TAX_CLASS_TITLE' , 'Tax Class');
define('MODULE_SHIPPING_AP_TAX_CLASS_DESC' , 'Use the following tax class on the shipping fee.');
define('MODULE_SHIPPING_AP_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_AP_ZONE_DESC' , 'If a zone is selected, only enable this shipping method for that zone.');
define('MODULE_SHIPPING_AP_SORT_ORDER_TITLE' , 'Sort Order');
define('MODULE_SHIPPING_AP_SORT_ORDER_DESC' , 'Sort order of display.');
define('MODULE_SHIPPING_AP_NUMBER_ZONES_TITLE' , 'Number of zones');
define('MODULE_SHIPPING_AP_NUMBER_ZONES_DESC' , 'Number of zones to use');
define('MODULE_SHIPPING_AP_DISPLAY_TITLE' , 'Enable Display');
define('MODULE_SHIPPING_AP_DISPLAY_DESC' , 'Do you want to display, if shipping to destination is not possible or if shipping costs cannot be calculated?');
define('MODULE_SHIPPING_AP_NUMBER_ZONES_TITLE' , 'Number of zones');
define('MODULE_SHIPPING_AP_NUMBER_ZONES_DESC' , 'Number of zones to use');
define('MODULE_SHIPPING_AP_DISPLAY_TITLE' , 'Enable Display');
define('MODULE_SHIPPING_AP_DISPLAY_DESC' , 'Do you want to display, if shipping to destination is not possible or if shipping costs cannot be calculated?');

for ($module_shipping_ap_i = 1; $module_shipping_ap_i <= MODULE_SHIPPING_AP_NUMBER_ZONES; $module_shipping_ap_i ++) {
  define('MODULE_SHIPPING_AP_COUNTRIES_'.$module_shipping_ap_i.'_TITLE' , '<hr/>Zone '.$module_shipping_ap_i.' Countries');
  define('MODULE_SHIPPING_AP_COUNTRIES_'.$module_shipping_ap_i.'_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone '.$module_shipping_ap_i.' (Enter WORLD for the rest of the world.).');
  define('MODULE_SHIPPING_AP_COST_'.$module_shipping_ap_i.'_TITLE' , 'Zone '.$module_shipping_ap_i.' Shipping Table');
  define('MODULE_SHIPPING_AP_COST_'.$module_shipping_ap_i.'_DESC' , 'Shipping rates to Zone '.$module_shipping_ap_i.' will be multiplied by the number of items in an order that uses this shipping method.');
  define('MODULE_SHIPPING_AP_HANDLING_'.$module_shipping_ap_i.'_TITLE' , 'Zone '.$module_shipping_ap_i.' Handling Fee');
  define('MODULE_SHIPPING_AP_HANDLING_'.$module_shipping_ap_i.'_DESC' , 'Handling Fee for this shipping zone');
}
?>
