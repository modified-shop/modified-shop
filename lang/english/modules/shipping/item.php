<?php
/* -----------------------------------------------------------------------------------------
   $Id: item.php 5118 2013-07-18 10:58:36Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(item.php,v 1.6 2003/02/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (item.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_ITEM_TEXT_TITLE', 'Per Item');
define('MODULE_SHIPPING_ITEM_TEXT_DESCRIPTION', 'Per Item');
define('MODULE_SHIPPING_ITEM_TEXT_WAY', 'Best Way');
define('MODULE_SHIPPING_ITEM_INVALID_ZONE', 'Unfortunately it is not possible to dispatch into this country');

define('MODULE_SHIPPING_ITEM_STATUS_TITLE' , 'Enable Item Shipping');
define('MODULE_SHIPPING_ITEM_STATUS_DESC' , 'Do you want to offer per item rate shipping?');
define('MODULE_SHIPPING_ITEM_ALLOWED_TITLE' , 'Allowed Zones');
define('MODULE_SHIPPING_ITEM_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_SHIPPING_ITEM_TAX_CLASS_TITLE' , 'Tax Class');
define('MODULE_SHIPPING_ITEM_TAX_CLASS_DESC' , 'Use the following tax class on the shipping fee.');
define('MODULE_SHIPPING_ITEM_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_ITEM_ZONE_DESC' , 'If a zone is selected, only enable this shipping method for that zone.');
define('MODULE_SHIPPING_ITEM_SORT_ORDER_TITLE' , 'Sort Order');
define('MODULE_SHIPPING_ITEM_SORT_ORDER_DESC' , 'Sort order of display.');
define('MODULE_SHIPPING_ITEM_NUMBER_ZONES_TITLE' , 'Number of zones');
define('MODULE_SHIPPING_ITEM_NUMBER_ZONES_DESC' , 'Number of zones to use');
define('MODULE_SHIPPING_ITEM_DISPLAY_TITLE' , 'Enable Display');
define('MODULE_SHIPPING_ITEM_DISPLAY_DESC' , 'Do you want to display, if shipping to destination is not possible or if shipping costs cannot be calculated?');

for ($module_shipping_item_i = 1; $module_shipping_item_i <= MODULE_SHIPPING_ITEM_NUMBER_ZONES; $module_shipping_item_i ++) {
  define('MODULE_SHIPPING_ITEM_COUNTRIES_'.$module_shipping_item_i.'_TITLE' , '<hr/>Zone '.$module_shipping_item_i.' Countries');
  define('MODULE_SHIPPING_ITEM_COUNTRIES_'.$module_shipping_item_i.'_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone '.$module_shipping_item_i.' (Enter WORLD for the rest of the world.).');
  define('MODULE_SHIPPING_ITEM_COST_'.$module_shipping_item_i.'_TITLE' , 'Zone '.$module_shipping_item_i.' Shipping Table');
  define('MODULE_SHIPPING_ITEM_COST_'.$module_shipping_item_i.'_DESC' , 'Shipping rates to Zone '.$module_shipping_item_i.' will be multiplied by the number of items in an order that uses this shipping method.');
  define('MODULE_SHIPPING_ITEM_HANDLING_'.$module_shipping_item_i.'_TITLE' , 'Zone '.$module_shipping_item_i.' Handling Fee');
  define('MODULE_SHIPPING_ITEM_HANDLING_'.$module_shipping_item_i.'_DESC' , 'Handling Fee for this shipping zone');
}
?>
