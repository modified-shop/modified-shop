<?php
/* -----------------------------------------------------------------------------------------
   $Id: table.php 5118 2013-07-18 10:58:36Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (table.php,v 1.6 2003/02/16); www.oscommerce.com 
   (c) 2003 nextcommerce (table.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_TABLE_TEXT_TITLE', 'Table Rate');
define('MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION', 'Table Rate');
define('MODULE_SHIPPING_TABLE_TEXT_WAY', 'Best Way (%01.2f kg)');
define('MODULE_SHIPPING_TABLE_TEXT_WEIGHT', 'Weight');
define('MODULE_SHIPPING_TABLE_TEXT_AMOUNT', 'Amount');
define('MODULE_SHIPPING_TABLE_INVALID_ZONE', 'No shipping available to the selected country!');
define('MODULE_SHIPPING_TABLE_UNDEFINED_RATE', 'The shipping rate cannot be determined at this time.');

define('MODULE_SHIPPING_TABLE_STATUS_TITLE' , 'Enable Table Method');
define('MODULE_SHIPPING_TABLE_STATUS_DESC' , 'Do you want to offer table rate shipping?');
define('MODULE_SHIPPING_TABLE_ALLOWED_TITLE' , 'Allowed Zones');
define('MODULE_SHIPPING_TABLE_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_SHIPPING_TABLE_MODE_TITLE' , 'Table Method');
define('MODULE_SHIPPING_TABLE_MODE_DESC' , 'The shipping cost is based on the order total or the total weight of the items ordered.');
define('MODULE_SHIPPING_TABLE_TAX_CLASS_TITLE' , 'Tax Class');
define('MODULE_SHIPPING_TABLE_TAX_CLASS_DESC' , 'Use the following tax class on the shipping fee.');
define('MODULE_SHIPPING_TABLE_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_TABLE_ZONE_DESC' , 'If a zone is selected, only enable this shipping method for that zone.');
define('MODULE_SHIPPING_TABLE_SORT_ORDER_TITLE' , 'Sort Order');
define('MODULE_SHIPPING_TABLE_SORT_ORDER_DESC' , 'Sort order of display.');
define('MODULE_SHIPPING_TABLE_NUMBER_ZONES_TITLE' , 'Number of zones');
define('MODULE_SHIPPING_TABLE_NUMBER_ZONES_DESC' , 'Number of zones to use');
define('MODULE_SHIPPING_TABLE_DISPLAY_TITLE' , 'Enable Display');
define('MODULE_SHIPPING_TABLE_DISPLAY_DESC' , 'Do you want to display, if shipping to destination is not possible or if shipping costs cannot be calculated?');

for ($module_shipping_table_i = 1; $module_shipping_table_i <= MODULE_SHIPPING_TABLE_NUMBER_ZONES; $module_shipping_table_i ++) {
  define('MODULE_SHIPPING_TABLE_COUNTRIES_'.$module_shipping_table_i.'_TITLE' , '<hr/>Zone '.$module_shipping_table_i.' Countries');
  define('MODULE_SHIPPING_TABLE_COUNTRIES_'.$module_shipping_table_i.'_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone '.$module_shipping_table_i.' (Enter WORLD for the rest of the world.).');
  define('MODULE_SHIPPING_TABLE_COST_'.$module_shipping_table_i.'_TITLE' , 'Zone '.$module_shipping_table_i.' Shipping Table');
  define('MODULE_SHIPPING_TABLE_COST_'.$module_shipping_table_i.'_DESC' , 'Shipping rates to Zone '.$module_shipping_table_i.' destinations based on a group of maximum order weights or order total. Example: 3:8.50,7:10.50,... Weights/Total less than or equal to 3 would cost 8.50 for Zone '.$module_shipping_table_i.' destinations.');
  define('MODULE_SHIPPING_TABLE_HANDLING_'.$module_shipping_table_i.'_TITLE' , 'Zone '.$module_shipping_table_i.' Handling Fee');
  define('MODULE_SHIPPING_TABLE_HANDLING_'.$module_shipping_table_i.'_DESC' , 'Handling Fee for this shipping zone');
}
?>
