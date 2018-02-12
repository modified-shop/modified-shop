<?php
/* -----------------------------------------------------------------------------------------
   $Id: ups.php 5121 2013-07-18 11:38:19Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(UPS.php,v 1.4 2003/02/18 04:28:00); www.oscommerce.com 
   (c) 2003	 nextcommerce (UPS.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   German Post (Deutsche Post WorldNet)
   Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at
   Changes for personal use: Copyright (C) 2004 Comm4All, Bernd Blazynski | http://www.comm4all.com & http://www.cheapshirt.de

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


define('MODULE_SHIPPING_UPS_TEXT_TITLE', 'United Parcel Service Standard');
define('MODULE_SHIPPING_UPS_TEXT_DESCRIPTION', 'United Parcel Service Standard - Shipping Module');
define('MODULE_SHIPPING_UPS_TEXT_WAY', 'Dispatch to');
define('MODULE_SHIPPING_UPS_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_UPS_TEXT_FREE', 'Free Shipping minimum order: EUR ' . MODULE_SHIPPING_UPS_FREEAMOUNT);
define('MODULE_SHIPPING_UPS_TEXT_LOW', 'From EUR ' . MODULE_SHIPPING_UPS_FREEAMOUNT . ' order value we ship your order at reduced shipping costs!');
define('MODULE_SHIPPING_UPS_INVALID_ZONE', 'Unfortunately it is not possible to dispatch into this country');
define('MODULE_SHIPPING_UPS_UNDEFINED_RATE', 'Shipping costs cannot be calculated for the moment');

define('MODULE_SHIPPING_UPS_STATUS_TITLE' , 'UPS Standard');
define('MODULE_SHIPPING_UPS_STATUS_DESC' , 'Would you like to offer shipping with UPS Standard?');
define('MODULE_SHIPPING_UPS_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_UPS_HANDLING_DESC' , 'Handling Fee in Euro');
define('MODULE_SHIPPING_UPS_TAX_CLASS_TITLE' , 'Tax');
define('MODULE_SHIPPING_UPS_TAX_CLASS_DESC' , 'Please choose the tax rate for shipping.');
define('MODULE_SHIPPING_UPS_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_UPS_ZONE_DESC' , 'If you choose a zone only this shipping zones used.');
define('MODULE_SHIPPING_UPS_SORT_ORDER_TITLE' , 'Display order');
define('MODULE_SHIPPING_UPS_SORT_ORDER_DESC' , 'Lowermost shown first.');
define('MODULE_SHIPPING_UPS_ALLOWED_TITLE' , 'Individual shipping zones');
define('MODULE_SHIPPING_UPS_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_SHIPPING_UPS_FREEAMOUNT_TITLE' , 'Free Shipping For national Orders Over');
define('MODULE_SHIPPING_UPS_FREEAMOUNT_DESC' , 'Minimum order value for free national shipping and reduced shipping abroad.');

define('MODULE_SHIPPING_UPS_COUNTRIES_1_TITLE' , 'UPS Standard Zone 1 Countries');
define('MODULE_SHIPPING_UPS_COUNTRIES_1_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 1 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPS_COST_1_TITLE' , 'UPS Standard Zone 1 Shipping Table');
define('MODULE_SHIPPING_UPS_COST_1_DESC' , 'Shipping rates to Zone 1 destinations based on a range of order weights. Example: 4:5.15,... Weights/Total less than or equal to 4 would cost 5.15 for Zone 1 destinations.');

define('MODULE_SHIPPING_UPS_COUNTRIES_2_TITLE' , 'UPS Standard Zone 3 Countries');
define('MODULE_SHIPPING_UPS_COUNTRIES_2_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 3 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPS_COST_2_TITLE' , 'UPS Standard Zone 3 Shipping Table');
define('MODULE_SHIPPING_UPS_COST_2_DESC' , 'Shipping rates to Zone 3 destinations based on a range of order weights. Example: 4:13.75,... Weights/Total less than or equal to 4 would cost 13.75 for Zone 3 destinations.');

define('MODULE_SHIPPING_UPS_COUNTRIES_3_TITLE' , 'UPS Standard Zone 31 Countries');
define('MODULE_SHIPPING_UPS_COUNTRIES_3_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 31 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPS_COST_3_TITLE' , 'UPS Standard Zone 31 Shipping Table');
define('MODULE_SHIPPING_UPS_COST_3_DESC' , 'Shipping rates to Zone 31 destinations based on a range of order weights. Example: 4:23.50,... Weights/Total less than or equal to 4 would cost 23.50 for Zone 31 destinations.');

define('MODULE_SHIPPING_UPS_COUNTRIES_4_TITLE' , 'UPS Standard Zone 4 Countries');
define('MODULE_SHIPPING_UPS_COUNTRIES_4_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 4 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPS_COST_4_TITLE' , 'UPS Standard Zone 4 Shipping Table');
define('MODULE_SHIPPING_UPS_COST_4_DESC' , 'Shipping rates to Zone 4 destinations based on a range of order weights. Example: 4:25.40,... Weights/Total less than or equal to 4 would cost 25.40 for Zone 4 destinations.');

define('MODULE_SHIPPING_UPS_COUNTRIES_5_TITLE' , 'UPS Standard Zone 41 Countries');
define('MODULE_SHIPPING_UPS_COUNTRIES_5_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 41 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPS_COST_5_TITLE' , 'UPS Standard Zone 41 Shipping Table');
define('MODULE_SHIPPING_UPS_COST_5_DESC' , 'Shipping rates to Zone 41 destinations based on a range of order weights. Example: 4:30.00,... Weights/Total less than or equal to 4 would cost 30.00 for Zone 41 destinations.');

define('MODULE_SHIPPING_UPS_COUNTRIES_6_TITLE' , 'UPS Standard Zone 5 Countries');
define('MODULE_SHIPPING_UPS_COUNTRIES_6_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 5 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPS_COST_6_TITLE' , 'UPS Standard Zone 5 Shipping Table');
define('MODULE_SHIPPING_UPS_COST_6_DESC' , 'Shipping rates to Zone 5 destinations based on a range of order weights. Example: 4:34.35,... Weights/Total less than or equal to 4 would cost 34.35 for Zone 5 destinations.');

define('MODULE_SHIPPING_UPS_COUNTRIES_7_TITLE' , 'UPS Standard Zone 6 Countries');
define('MODULE_SHIPPING_UPS_COUNTRIES_7_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 6 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_UPS_COST_7_TITLE' , 'UPS Standard Zone 6 Shipping Table');
define('MODULE_SHIPPING_UPS_COST_7_DESC' , 'Shipping rates to Zone 6 destinations based on a range of order weights. Example: 4:37.10,... Weights/Total less than or equal to 4 would cost 37.10 for Zone 6 destinations.');
?>