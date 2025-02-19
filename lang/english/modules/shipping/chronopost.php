<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(chronopost.php,v 1.0 2002/04/01 07:07:45); www.oscommerce.com 
   (c) 2003	 nextcommerce (chronopost.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions: 
   chronopost-1.0.1       	Autor:	devteam@e-network.fr | www.oscommerce-fr.info

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   

define('MODULE_SHIPPING_CHRONOPOST_TEXT_TITLE', 'Chronopost');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_DESCRIPTION', 'Chronopost Zone Based Rates');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_WAY', 'Dispatch to');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_CHRONOPOST_INVALID_ZONE', 'Unfortunately it is not possible to dispatch into this country');
define('MODULE_SHIPPING_CHRONOPOST_UNDEFINED_RATE', 'Shipping costs cannot be calculated for the moment');

define('MODULE_SHIPPING_CHRONOPOST_STATUS_TITLE' , 'DHL WORLDWIDE EXPRESS');
define('MODULE_SHIPPING_CHRONOPOST_STATUS_DESC' , 'Do you want to offer DHL WORLDWIDE EXPRESS shipping?');
define('MODULE_SHIPPING_CHRONOPOST_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_CHRONOPOST_HANDLING_DESC' , 'Handling fee for this shipping method');
define('MODULE_SHIPPING_CHRONOPOST_TAX_CLASS_TITLE' , 'Tax Rate');
define('MODULE_SHIPPING_CHRONOPOST_TAX_CLASS_DESC' , 'Use the following tax class on the shipping fee');
define('MODULE_SHIPPING_CHRONOPOST_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_CHRONOPOST_ZONE_DESC' , 'If a zone is selected, only enable this shipping method for that zone');
define('MODULE_SHIPPING_CHRONOPOST_SORT_ORDER_TITLE' , 'Sort Order');
define('MODULE_SHIPPING_CHRONOPOST_SORT_ORDER_DESC' , 'Sort order of display');
define('MODULE_SHIPPING_CHRONOPOST_ALLOWED_TITLE' , 'Allowed Shipping Zones');
define('MODULE_SHIPPING_CHRONOPOST_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');

for ($module_shipping_chp_i = 1; $module_shipping_chp_i <= 10; $module_shipping_chp_i ++) {
  define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_'.$module_shipping_chp_i.'_TITLE' , '<hr/>Chronopost Zone '.$module_shipping_chp_i.' Countries');
  define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_'.$module_shipping_chp_i.'_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone '.$module_shipping_chp_i.' (Enter WORLD for the rest of the world.).');
  define('MODULE_SHIPPING_CHRONOPOST_COST_'.$module_shipping_chp_i.'_TITLE' , 'Chronopost Zone '.$module_shipping_chp_i.' Shipping Table');
  define('MODULE_SHIPPING_CHRONOPOST_COST_'.$module_shipping_chp_i.'_DESC' , 'Shipping rates to Zone '.$module_shipping_chp_i.' destinations based on a range of order weights. Example: 0-2000:28.71,2000-5000:34.38... Weights greater than 0 and less than 2 would cost 28,71, less than 5 would cost 34.38 for Zone '.$module_shipping_chp_i.' destinations.');
}
