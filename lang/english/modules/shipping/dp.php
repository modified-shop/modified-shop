<?php
/* -----------------------------------------------------------------------------------------
   $Id: dp.php 5118 2013-07-18 10:58:36Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(dp.php,v 1.4 2003/02/18 04:28:00); www.oscommerce.com
   (c) 2003 nextcommerce (dp.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   German Post (Deutsche Post WorldNet)         	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   enhanced on 2010-12-08 18:17:30Z franky_n
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_DP_TEXT_TITLE', 'German Post');
define('MODULE_SHIPPING_DP_TEXT_DESCRIPTION', 'German Post - Worldwide Shipping Module');
define('MODULE_SHIPPING_DP_TEXT_WAY', 'Dispatch to');
define('MODULE_SHIPPING_DP_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_DP_INVALID_ZONE', 'Unfortunately it is not possible to dispatch into this country');
define('MODULE_SHIPPING_DP_UNDEFINED_RATE', 'Shipping costs cannot be calculated for the moment');

define('MODULE_SHIPPING_DP_STATUS_TITLE' , 'German Post WorldNet');
define('MODULE_SHIPPING_DP_STATUS_DESC' , 'Would you like to offer shipping with German Post WorldNet?');
define('MODULE_SHIPPING_DP_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_DP_HANDLING_DESC' , 'Handling Fee in Euro');
define('MODULE_SHIPPING_DP_TAX_CLASS_TITLE' , 'Tax');
define('MODULE_SHIPPING_DP_TAX_CLASS_DESC' , 'Please choose the tax rate for shipping.');
define('MODULE_SHIPPING_DP_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_DP_ZONE_DESC' , 'If you choose a zone only this shipping zones used.');
define('MODULE_SHIPPING_DP_SORT_ORDER_TITLE' , 'Display order');
define('MODULE_SHIPPING_DP_SORT_ORDER_DESC' , 'Lowermost shown first.');
define('MODULE_SHIPPING_DP_ALLOWED_TITLE' , 'Individual shipping zones');
define('MODULE_SHIPPING_DP_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_SHIPPING_DP_NUMBER_ZONES_TITLE' , 'Number of zones');
define('MODULE_SHIPPING_DP_NUMBER_ZONES_DESC' , 'Number of zones to use');
define('MODULE_SHIPPING_DP_DISPLAY_TITLE' , 'Enable Display');
define('MODULE_SHIPPING_DP_DISPLAY_DESC' , 'Do you want to display, if shipping to destination is not possible or if shipping costs cannot be calculated?');

for ($module_shipping_dp_i = 1; $module_shipping_dp_i <= MODULE_SHIPPING_DP_NUMBER_ZONES; $module_shipping_dp_i ++) {
  define('MODULE_SHIPPING_DP_COUNTRIES_'.$module_shipping_dp_i.'_TITLE' , '<hr/>DP Zone '.$module_shipping_dp_i.' Countries');
  define('MODULE_SHIPPING_DP_COUNTRIES_'.$module_shipping_dp_i.'_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone '.$module_shipping_dp_i.' (Enter WORLD for the rest of the world.).');
  define('MODULE_SHIPPING_DP_COST_'.$module_shipping_dp_i.'_TITLE' , 'DP Zone '.$module_shipping_dp_i.' Shipping Table');
  define('MODULE_SHIPPING_DP_COST_'.$module_shipping_dp_i.'_DESC' , 'Shipping rates to Zone '.$module_shipping_dp_i.' destinations based on a range of order weights. Example: 3:8.50,7:10.50,99999:12.00... Weights greater than 0 and less than 3 would cost 8.50, less than 7 would cost 10.50 for Zone '.$module_shipping_dp_i.' destinations.');
}
?>
