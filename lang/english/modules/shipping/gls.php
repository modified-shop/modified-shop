<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(dp.php,v 1.4 2003/02/18 04:28:00); www.oscommerce.com 
   (c) 2003	nextcommerce (dp.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2009	shd-media (gls.php 899 27.05.2009);

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   GLS (German Logistic Service) based on DP (Deutsche Post)        
   (c) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at
    
   GLS contribution made by shd-media (c) 2009 shd-media - www.shd-media.de
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_GLS_TEXT_TITLE', 'GLS');
define('MODULE_SHIPPING_GLS_TEXT_DESCRIPTION', 'GLS - European Shipping Module');
define('MODULE_SHIPPING_GLS_TEXT_WAY', 'deliver to');
define('MODULE_SHIPPING_GLS_POSTCODE_INFO_TEXT', 'incl. island surchage');
define('MODULE_SHIPPING_GLS_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_GLS_INVALID_ZONE', 'Unfortunately it is not possible to deliver to this country');
define('MODULE_SHIPPING_GLS_UNDEFINED_RATE', 'Shipping costs cannot be calculated at the moment');

define('MODULE_SHIPPING_GLS_STATUS_TITLE' , 'GLS');
define('MODULE_SHIPPING_GLS_STATUS_DESC' , 'Do you want to offer shipping via GLS?');
define('MODULE_SHIPPING_GLS_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_GLS_HANDLING_DESC' , 'Handling fee for this shipping method');
define('MODULE_SHIPPING_GLS_TAX_CLASS_TITLE' , 'Tax Rate');
define('MODULE_SHIPPING_GLS_TAX_CLASS_DESC' , 'Choose the tax rate for this shipping type');
define('MODULE_SHIPPING_GLS_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_GLS_ZONE_DESC' , 'If you choose a zone, the shipping type will be offered only in this zone.');
define('MODULE_SHIPPING_GLS_SORT_ORDER_TITLE' , 'Order of display');
define('MODULE_SHIPPING_GLS_SORT_ORDER_DESC' , 'Lowerst will be shown first.');
define('MODULE_SHIPPING_GLS_ALLOWED_TITLE' , 'Single Shipping Zones');
define('MODULE_SHIPPING_GLS_ALLOWED_DESC' , 'Enter the zones <b>one by one</b>, in which ones shipping should be possible, e.g.: AT,DE');
define('MODULE_SHIPPING_GLS_DISPLAY_TITLE' , 'Enable Display');
define('MODULE_SHIPPING_GLS_DISPLAY_DESC' , 'Do you want to display, if shipping to destination is not possible or if shipping costs cannot be calculated?');

define('MODULE_SHIPPING_GLS_POSTCODE_TITLE' , 'GLS island surchage - zip codes');
define('MODULE_SHIPPING_GLS_POSTCODE_DESC' , 'Zip code areas');
define('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST_TITLE' , 'GLS island surchage - costs');
define('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST_DESC' , 'Island surchage: Enter the amount, how much should be added to the shipping costs, when the shipping address is located on one of the German islands.');

for ($module_shipping_gls_i = 1; $module_shipping_gls_i <= 6; $module_shipping_gls_i ++) {
  define('MODULE_SHIPPING_GLS_COUNTRIES_'.$module_shipping_gls_i.'_TITLE' , '<hr/>Zone '.$module_shipping_gls_i.' Countries');
  define('MODULE_SHIPPING_GLS_COUNTRIES_'.$module_shipping_gls_i.'_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone '.$module_shipping_gls_i.' (Enter WORLD for the rest of the world.).');
  define('MODULE_SHIPPING_GLS_COST_'.$module_shipping_gls_i.'_TITLE' , 'Zone '.$module_shipping_gls_i.' Shipping Table');
  define('MODULE_SHIPPING_GLS_COST_'.$module_shipping_gls_i.'_DESC' , 'Shipping rates to Zone '.$module_shipping_gls_i.' destinations based on a group of maximum order weights. Example: 3:8.50,7:10.50,... Weights less than or equal to 3 would cost 8.50 for Zone '.$module_shipping_gls_i.' destinations.');
}
