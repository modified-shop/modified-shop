<?php
/* -----------------------------------------------------------------------------------------
   $Id: gls.php 5121 2013-07-18 11:38:19Z Tomcraft $   

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
define('MODULE_SHIPPING_GLS_HANDLING_DESC' , 'Handling Fee for this shipping type in Euro');
define('MODULE_SHIPPING_GLS_TAX_CLASS_TITLE' , 'Tax Rate');
define('MODULE_SHIPPING_GLS_TAX_CLASS_DESC' , 'Choose the tax rate for this shipping type');
define('MODULE_SHIPPING_GLS_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_GLS_ZONE_DESC' , 'If you choose a zone, the shipping type will be offered only in this zone.');
define('MODULE_SHIPPING_GLS_SORT_ORDER_TITLE' , 'Order of display');
define('MODULE_SHIPPING_GLS_SORT_ORDER_DESC' , 'Lowerst will be shown first.');
define('MODULE_SHIPPING_GLS_ALLOWED_TITLE' , 'Single Shipping Zones');
define('MODULE_SHIPPING_GLS_ALLOWED_DESC' , 'Enter the zones <b>one by one</b>, in which ones shipping should be possible, e.g.: AT,DE');

define('MODULE_SHIPPING_GLS_COUNTRIES_1_TITLE' , 'GLS Zone 1 Countries');
define('MODULE_SHIPPING_GLS_COUNTRIES_1_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 1 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_GLS_COST_1_TITLE' , 'GLS Zone 1 Shipping Table');
define('MODULE_SHIPPING_GLS_COST_1_DESC' , 'Shipping rates to Zone 1 destinations based on a range of order weights. Example: 0-3:8.50,3-7:10.50,... Weights greater than 0 and less than or equal to 3 would cost 8.50 for Zone 1 destinations.');

define('MODULE_SHIPPING_GLS_COUNTRIES_2_TITLE' , 'GLS Zone 2 Countries');
define('MODULE_SHIPPING_GLS_COUNTRIES_2_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 2 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_GLS_COST_2_TITLE' , 'GLS Zone 2 Shipping Table');
define('MODULE_SHIPPING_GLS_COST_2_DESC' , 'Shipping rates to Zone 2 destinations based on a range of order weights. Example: 0-3:8.50,3-7:10.50,... Weights greater than 0 and less than or equal to 3 would cost 8.50 for Zone 2 destinations.');

define('MODULE_SHIPPING_GLS_COUNTRIES_3_TITLE' , 'GLS Zone 3 Countries');
define('MODULE_SHIPPING_GLS_COUNTRIES_3_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 3 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_GLS_COST_3_TITLE' , 'GLS Zone 3 Shipping Table');
define('MODULE_SHIPPING_GLS_COST_3_DESC' , 'Shipping rates to Zone 3 destinations based on a range of order weights. Example: 0-3:8.50,3-7:10.50,... Weights greater than 0 and less than or equal to 3 would cost 8.50 for Zone 3 destinations.');

define('MODULE_SHIPPING_GLS_COUNTRIES_4_TITLE' , 'GLS Zone 4 Countries');
define('MODULE_SHIPPING_GLS_COUNTRIES_4_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 4 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_GLS_COST_4_TITLE' , 'GLS Zone 4 Shipping Table');
define('MODULE_SHIPPING_GLS_COST_4_DESC' , 'Shipping rates to Zone 4 destinations based on a range of order weights. Example: 0-3:8.50,3-7:10.50,... Weights greater than 0 and less than or equal to 3 would cost 8.50 for Zone 4 destinations.');

define('MODULE_SHIPPING_GLS_COUNTRIES_5_TITLE' , 'GLS Zone 5 Countries');
define('MODULE_SHIPPING_GLS_COUNTRIES_5_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 5 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_GLS_COST_5_TITLE' , 'GLS Zone 5 Shipping Table');
define('MODULE_SHIPPING_GLS_COST_5_DESC' , 'Shipping rates to Zone 5 destinations based on a range of order weights. Example: 0-3:8.50,3-7:10.50,... Weights greater than 0 and less than or equal to 3 would cost 8.50 for Zone 5 destinations.');

define('MODULE_SHIPPING_GLS_COUNTRIES_6_TITLE' , 'GLS Zone 6 Countries');
define('MODULE_SHIPPING_GLS_COUNTRIES_6_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 6 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_GLS_COST_6_TITLE' , 'GLS Zone 6 Shipping Table');
define('MODULE_SHIPPING_GLS_COST_6_DESC' , 'Shipping rates to Zone 6 destinations based on a range of order weights. Example: 0-3:8.50,3-7:10.50,... Weights greater than 0 and less than or equal to 3 would cost 8.50 for Zone 6 destinations.');

define('MODULE_SHIPPING_GLS_POSTCODE_TITLE' , 'GLS island surchage - zip codes');
define('MODULE_SHIPPING_GLS_POSTCODE_DESC' , 'Zip code areas');
define('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST_TITLE' , 'GLS island surchage - costs');
define('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST_DESC' , 'Island surchage: Enter the amount, how much should be added to the shipping costs, when the shipping address is located on one of the German islands.');
?>