<?php
/* -----------------------------------------------------------------------------------------
   $Id: fedexeu.php 5121 2013-07-18 11:38:19Z Tomcraft $   

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



define('MODULE_SHIPPING_FEDEXEU_TEXT_TITLE', 'FedEx Express Europe');
define('MODULE_SHIPPING_FEDEXEU_TEXT_DESCRIPTION', 'FedEx Express Europe');
define('MODULE_SHIPPING_FEDEXEU_TEXT_WAY', 'Dispatch to');
define('MODULE_SHIPPING_FEDEXEU_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_FEDEXEU_INVALID_ZONE', 'Unfortunately it is not possible to dispatch into this country');
define('MODULE_SHIPPING_FEDEXEU_UNDEFINED_RATE', 'Forwarding expenses cannot be calculated for the moment');

define('MODULE_SHIPPING_FEDEXEU_STATUS_TITLE' , 'FedEx Express Europe');
define('MODULE_SHIPPING_FEDEXEU_STATUS_DESC' , 'Do you want to offer FedEx Express Europe shipping?');
define('MODULE_SHIPPING_FEDEXEU_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_FEDEXEU_HANDLING_DESC' , 'Handlingfee for this shipping method in Euro');
define('MODULE_SHIPPING_FEDEXEU_TAX_CLASS_TITLE' , 'Tax Rate');
define('MODULE_SHIPPING_FEDEXEU_TAX_CLASS_DESC' , 'Use the following tax class on the shipping fee.');
define('MODULE_SHIPPING_FEDEXEU_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_FEDEXEU_ZONE_DESC' , 'If a zone is selected, only enable this shipping method for that zone.');
define('MODULE_SHIPPING_FEDEXEU_SORT_ORDER_TITLE' , 'Sort Order');
define('MODULE_SHIPPING_FEDEXEU_SORT_ORDER_DESC' , 'Sort order of display.');
define('MODULE_SHIPPING_FEDEXEU_ALLOWED_TITLE' , 'Allowed Shipping Zones');
define('MODULE_SHIPPING_FEDEXEU_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_1_TITLE' , 'Europe Zone 1 Countries');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_1_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 1 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_1_TITLE' , 'Shipping Table for Zone 1 up to 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_1_DESC' , 'Shipping Table for zone 1, based on <b>\'PAK\'</b> up to 2.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_1_TITLE' , 'Shipping Table for Zone 1 up to 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_1_DESC' , 'Shipping Table for zone 1, based on <b>\'ENV\'</b> up to 60 DIN A4 pages & 0.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_1_TITLE' , 'Shipping Table for 1 up to 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_1_DESC' , 'Shipping Table for zone 1, based on <b>\'BOX\'</b> up to 10 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_1_TITLE' , 'Extra charge up to 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_1_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_1_TITLE' , 'Extra charge up to 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_1_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_1_TITLE' , 'Extra charge up to 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_1_DESC' , 'Extra charge for each additional 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_2_TITLE' , 'Europe Zone 2 Countries');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_2_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 2 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_2_TITLE' , 'Shipping Table for Zone 2 up to 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_2_DESC' , 'Shipping Table for die Zone 2, based on <b>\'PAK\'</b> up to 2.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_2_TITLE' , 'Shipping Table for Zone 2 up to 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_2_DESC' , 'Shipping Table for die Zone 2, based on <b>\'ENV\'</b> up to 60 DIN A4 pages & 0.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_2_TITLE' , 'Shipping Table for Zone 2 up to 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_2_DESC' , 'Shipping Table for Zone 2, based on <b>\'BOX\'</b> up to 10 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_2_TITLE' , 'Extra charge up to 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_2_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_2_TITLE' , 'Extra charge up to 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_2_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_2_TITLE' , 'Extra charge up to 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_2_DESC' , 'Extra charge for each additional 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_3_TITLE' , 'Europe Zone 3 Countries');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_3_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 3 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_3_TITLE' , 'Shipping Table for Zone 3 up to 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_3_DESC' , 'Shipping Table for Zone 3, based on <b>\'PAK\'</b> up to 2.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_3_TITLE' , 'Shipping Table for Zone 3 up to 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_3_DESC' , 'Shipping Table for Zone 3, based on <b>\'ENV\'</b> up to 60 DIN A4 pages & 0.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_3_TITLE' , 'Shipping Table for Zone 3 up to 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_3_DESC' , 'Shipping Table for Zone 3, based on <b>\'BOX\'</b> up to 10 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_3_TITLE' , 'Extra charge up to 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_3_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_3_TITLE' , 'Extra charge up to 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_3_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_3_TITLE' , 'Extra charge up to 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_3_DESC' , 'Extra charge for each additional 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_4_TITLE' , 'World Zone A Countries');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_4_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone World A (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_4_TITLE' , 'Shipping Table for Zone A up to 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_4_DESC' , 'Shipping Table for Zone A, based on <b>\'PAK\'</b> up to 2.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_4_TITLE' , 'Shipping Table for Zone A up to 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_4_DESC' , 'Shipping Table for Zone A, based on <b>\'ENV\'</b> up to 60 DIN A4 pages & 0.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_4_TITLE' , 'Shipping Table for Zone A up to 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_4_DESC' , 'Shipping Table for Zone A, based on <b>\'BOX\'</b> up to 10 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_4_TITLE' , 'Extra charge up to 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_4_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_4_TITLE' , 'Extra charge up to 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_4_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_4_TITLE' , 'Extra charge up to 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_4_DESC' , 'Extra charge for each additional 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_5_TITLE' , 'World Zone B Countries');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_5_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone World B (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_5_TITLE' , 'Shipping Table for Zone B up to 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_5_DESC' , 'Shipping Table for Zone B, based on <b>\'PAK\'</b> up to 2.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_5_TITLE' , 'Shipping Table for Zone B up to 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_5_DESC' , 'Shipping Table for Zone B, based on <b>\'ENV\'</b> up to 60 DIN A4 pages & 0.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_5_TITLE' , 'Shipping Table for Zone B up to 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_5_DESC' , 'Shipping Table for Zone B, based on <b>\'BOX\'</b> up to 10 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_5_TITLE' , 'Extra charge up to 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_5_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_5_TITLE' , 'Extra charge up to 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_5_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_5_TITLE' , 'Extra charge up to 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_5_DESC' , 'Extra charge for each additional 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_6_TITLE' , 'World Zone C Countries');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_6_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone World C (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_6_TITLE' , 'Shipping Table for Zone C up to 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_6_DESC' , 'Shipping Table for Zone C, based on <b>\'PAK\'</b> up to 2.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_6_TITLE' , 'Shipping Table for Zone C up to 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_6_DESC' , 'Shipping Table for Zone C, based on <b>\'ENV\'</b> up to 60 DIN A4 pages & 0.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_6_TITLE' , 'Shipping Table for Zone C up to 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_6_DESC' , 'Shipping Table for Zone C, based on <b>\'BOX\'</b> up to 10 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_6_TITLE' , 'Extra charge up to 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_6_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_6_TITLE' , 'Extra charge up to 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_6_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_6_TITLE' , 'Extra charge up to 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_6_DESC' , 'Extra charge for each additional 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_7_TITLE' , 'World Zone D Countries');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_7_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone World D (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_7_TITLE' , 'Shipping Table for Zone D up to 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_7_DESC' , 'Shipping Table for Zone D, based on <b>\'PAK\'</b> up to 2.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_7_TITLE' , 'Shipping Table for Zone D up to 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_7_DESC' , 'Shipping Table for Zone D, based on <b>\'ENV\'</b> up to 60 DIN A4 pages & 0.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_7_TITLE' , 'Shipping Table for Zone D up to 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_7_DESC' , 'Shipping Table for Zone D, based on <b>\'BOX\'</b> up to 10 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_7_TITLE' , 'Extra charge up to 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_7_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_7_TITLE' , 'Extra charge up to 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_7_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_7_TITLE' , 'Extra charge up to 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_7_DESC' , 'Extra charge for each additional 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_8_TITLE' , 'World Zone E Countries');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_8_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone World E (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_8_TITLE' , 'Shipping Table for Zone E up to 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_8_DESC' , 'Shipping Table for Zone E, based on <b>\'PAK\'</b> up to 2.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_8_TITLE' , 'Shipping Table for Zone E up to 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_8_DESC' , 'Shipping Table for Zone E, based on <b>\'ENV\'</b> up to 60 DIN A4 pages & 0.50 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_8_TITLE' , 'Shipping Table for Zone E up to 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_8_DESC' , 'Shipping Table for Zone E, based on <b>\'BOX\'</b> up to 10 kg shipping weight.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_8_TITLE' , 'Extra charge up to 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_8_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_8_TITLE' , 'Extra charge up to 30 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_8_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_8_TITLE' , 'Extra charge up to 50 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_8_DESC' , 'Extra charge for each additional 0,50 kg in EUR');
?>