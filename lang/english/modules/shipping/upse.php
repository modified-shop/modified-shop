<?php
/* -----------------------------------------------------------------------------------------
   $Id: upse.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce( fedexeu.php,v 1.01 2003/02/18 03:25:00); www.oscommerce.com 
   (c) 2003	 nextcommerce (fedexeu.php,v 1.5 2003/08/1); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   fedex_europe_1.02        	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/



define('MODULE_SHIPPING_UPSE_TEXT_TITLE', 'United Parcel Service Express');
define('MODULE_SHIPPING_UPSE_TEXT_DESCRIPTION', 'United Parcel Service Express - Shipping Module');
define('MODULE_SHIPPING_UPSE_TEXT_WAY', 'Dispatch to');
define('MODULE_SHIPPING_UPSE_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_UPSE_INVALID_ZONE', 'Unfortunately it is not possible to dispatch into this country');
define('MODULE_SHIPPING_UPSE_UNDEFINED_RATE', 'Shipping costs cannot be calculated for the moment');

define('MODULE_SHIPPING_UPSE_STATUS_TITLE' , 'UPS Express');
define('MODULE_SHIPPING_UPSE_STATUS_DESC' , 'Would you like to offer shipping with UPS Express?');
define('MODULE_SHIPPING_UPSE_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_UPSE_HANDLING_DESC' , 'Handling Fee in Euro');
define('MODULE_SHIPPING_UPSE_TAX_CLASS_TITLE' , 'Tax');
define('MODULE_SHIPPING_UPSE_TAX_CLASS_DESC' , 'Please choose the tax rate for shipping.');
define('MODULE_SHIPPING_UPSE_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_UPSE_ZONE_DESC' , 'If you choose a zone only this shipping zones used.');
define('MODULE_SHIPPING_UPSE_SORT_ORDER_TITLE' , 'Display order');
define('MODULE_SHIPPING_UPSE_SORT_ORDER_DESC' , 'Lowermost shown first.');
define('MODULE_SHIPPING_UPSE_ALLOWED_TITLE' , 'Individual shipping zones');
define('MODULE_SHIPPING_UPSE_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');




/* UPS Express

*/

define('MODULE_SHIPPING_UPSE_COUNTRIES_1_TITLE' , 'UPS Express Zone 1 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_1_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 1 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_1_TITLE' , 'UPS Express Zone 1 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_1_DESC' , 'Shipping rates to Zone 1 destinations based on a range of order weights. Example: 0.5:22.7,... Weights/Total less than or equal to 0.5 would cost 22.70 for Zone 1 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_2_TITLE' , 'UPS Express Zone 2 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_2_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 2 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_2_TITLE' , 'UPS Express Zone 2 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_2_DESC' , 'Shipping rates to Zone 2 destinations based on a range of order weights. Example: 0.5:51.55,... Weights/Total less than or equal to 0.5 would cost 51.55 for Zone 2 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_3_TITLE' , 'UPS Express Zone 3 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_3_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 3 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_3_TITLE' , 'UPS Express Zone 3 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_3_DESC' , 'Shipping rates to Zone 3 destinations based on a range of order weights. Example: 0.5:60.70,... Weights/Total less than or equal to 0.5 would cost 60.70 for Zone 3 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_4_TITLE' , 'UPS Express Zone 4 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_4_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 4 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_4_TITLE' , 'UPS Express Zone 4 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_4_DESC' , 'Shipping rates to Zone 4 destinations based on a range of order weights. Example: 0.5:66.90,... Weights/Total less than or equal to 0.5 would cost 66.90 for Zone 4 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_5_TITLE' , 'UPS Express Zone 41 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_5_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 41 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_5_TITLE' , 'UPS Express Zone 41 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_5_DESC' , 'Shipping rates to Zone 41 destinations based on a range of order weights. Example: 0.5:82.10,... Weights/Total less than or equal to 0.5 would cost 82.10 for Zone 41 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_6_TITLE' , 'UPS Express Zone 42 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_6_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 42 (Enter WORLD for the rest of the world.):');
define('MODULE_SHIPPING_UPSE_COST_6_TITLE' , 'UPS Express Zone 42 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_6_DESC' , 'Shipping rates to Zone 42 destinations based on a range of order weights. Example: 0.5:82.90,... Weights/Total less than or equal to 0.5 would cost 82.90 for Zone 42 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_7_TITLE' , 'UPS Express Zone 5 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_7_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 5 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_7_TITLE' , 'UPS Express Zone 5 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_7_DESC' , 'Shipping rates to Zone 5 destinations based on a range of order weights. Example: 0.5:59.00,... Weights/Total less than or equal to 0.5 would cost 59.00 for Zone 5 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_8_TITLE' , 'UPS Express Zone 6 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_8_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 6 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_8_TITLE' , 'UPS Express Zone 6 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_8_DESC' , 'Shipping rates to Zone 6 destinations based on a range of order weights. Example: 0.5:84.50,... Weights/Total less than or equal to 0.5 would cost 84.50 for Zone 6 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_9_TITLE' , 'UPS Express Zone 7 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_9_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 7 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_9_TITLE' , 'UPS Express Zone 7 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_9_DESC' , 'Shipping rates to Zone 7 destinations based on a range of order weights. Example: 0.5:71.85,... Weights/Total less than or equal to 0.5 would cost 71.85 for Zone 7 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_10_TITLE' , 'UPS Express Zone 8 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_10_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 8 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_10_TITLE' , 'UPS Express Zone 8 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_10_DESC' , 'Shipping rates to Zone 8 destinations based on a range of order weights. Example: 0.5:80.05,... Weights/Total less than or equal to 0.5 would cost 80.05 for Zone 8 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_11_TITLE' , 'UPS Express Zone 9 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_11_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 9 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_11_TITLE' , 'UPS Express Zone 9 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_11_DESC' , 'Shipping rates to Zone 9 destinations based on a range of order weights. Example: 0.5:85.20,... Weights/Total less than or equal to 0.5 would cost 85.20 for Zone 9 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_12_TITLE' , 'UPS Express Zone 10 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_12_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 10 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_12_TITLE' , 'UPS Express Zone 10 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_12_DESC' , 'Shipping rates to Zone 10 destinations based on a range of order weights. Example: 0.5:93.10,... Weights/Total less than or equal to 0.5 would cost 93.10 for Zone 10 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_13_TITLE' , 'UPS Express Zone 11 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_13_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 11 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_13_TITLE' , 'UPS Express Zone 11 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_13_DESC' , 'Shipping rates to Zone 11 destinations based on a range of order weights. Example: 0.5:103.50,... Weights/Total less than or equal to 0.5 would cost 103.50 for Zone 11 destinations.');

define('MODULE_SHIPPING_UPSE_COUNTRIES_14_TITLE' , 'UPS Express Zone 12 Countries');
define('MODULE_SHIPPING_UPSE_COUNTRIES_14_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 12 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPSE_COST_14_TITLE' , 'UPS Express Zone 12 Shipping Table');
define('MODULE_SHIPPING_UPSE_COST_14_DESC' , 'Shipping rates to Zone 12 destinations based on a range of order weights. Example: 0.5:105.20,... Weights/Total less than or equal to 0.5 would cost 105.20 for Zone 12 destinations.');
?>