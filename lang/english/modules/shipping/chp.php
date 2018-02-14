<?php
/* -----------------------------------------------------------------------------------------
   $Id: chp.php 5123 2013-07-18 11:49:11Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(chp.php,v 1.01 2003/02/18 03:30:00); www.oscommerce.com 
   (c) 2003	 nextcommerce (chp.php,v 1.4 2003/08/1); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   swiss_post_1.02       	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


define('MODULE_SHIPPING_CHP_TEXT_TITLE', 'The Swiss Post');
define('MODULE_SHIPPING_CHP_TEXT_DESCRIPTION', 'The Swiss Post');
define('MODULE_SHIPPING_CHP_TEXT_WAY', 'Dispatch to');
define('MODULE_SHIPPING_CHP_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_CHP_INVALID_ZONE', 'Unfortunately it is not possible to dispatch into this country');
define('MODULE_SHIPPING_CHP_UNDEFINED_RATE', 'Forwarding expenses cannot be calculated for the moment');

define('MODULE_SHIPPING_CHP_STATUS_TITLE' , 'The Swiss Post');
define('MODULE_SHIPPING_CHP_STATUS_DESC' , 'Would you like to offer shipping with The Swiss Post?');
define('MODULE_SHIPPING_CHP_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_CHP_HANDLING_DESC' , 'Handling Fee in CHF');
define('MODULE_SHIPPING_CHP_TAX_CLASS_TITLE' , 'Tax');
define('MODULE_SHIPPING_CHP_TAX_CLASS_DESC' , 'Please choose the tax rate for shipping.');
define('MODULE_SHIPPING_CHP_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_CHP_ZONE_DESC' , 'If you choose a zone only this shipping zones used.');
define('MODULE_SHIPPING_CHP_SORT_ORDER_TITLE' , 'Display order');
define('MODULE_SHIPPING_CHP_SORT_ORDER_DESC' , 'Lowermost shown first.');
define('MODULE_SHIPPING_CHP_ALLOWED_TITLE' , 'Individual shipping zones');
define('MODULE_SHIPPING_CHP_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');

define('MODULE_SHIPPING_CHP_COUNTRIES_1_TITLE' , 'Zone 0 Countries');
define('MODULE_SHIPPING_CHP_COUNTRIES_1_DESC' , 'Inland Zone');
define('MODULE_SHIPPING_CHP_COST_ECO_1_TITLE' , 'Zone 0 Table for shipping up to 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_1_DESC' , 'Shipping Table for Inland Zone, based on <b>\'ECO\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_PRI_1_TITLE' , 'Zone 0 Table for shipping up to 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_1_DESC' , 'Shipping Table for Inland Zone, based on <b>\'PRI\'</b> up to 30 kg Shipping Weight.');

define('MODULE_SHIPPING_CHP_COUNTRIES_2_TITLE' , 'Zone 1 Countries');
define('MODULE_SHIPPING_CHP_COUNTRIES_2_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 1 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHP_COST_ECO_2_TITLE' , 'Zone 1 Table for shipping up to 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_2_DESC' , 'Shipping Table for Zone 1, based on <b>\'ECO\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_PRI_2_TITLE' , 'Zone 1 Table for shipping up to 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_2_DESC' , 'Shipping Table for Zone 1, based on <b>\'PRI\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_URG_2_TITLE' , 'Zone 1 Table for shipping up to 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_2_DESC' , 'Shipping Table for Zone 1, based on <b>\'URG\'</b> up to 30 kg Shipping Weight.');

define('MODULE_SHIPPING_CHP_COUNTRIES_3_TITLE' , 'Zone 2 Countries');
define('MODULE_SHIPPING_CHP_COUNTRIES_3_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 2 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHP_COST_ECO_3_TITLE' , 'Zone 2 Table for shipping up to 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_3_DESC' , 'Shipping Table for Zone 2, based on <b>\'ECO\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_PRI_3_TITLE' , 'Zone 2 Table for shipping up to 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_3_DESC' , 'Shipping Table for Zone 2, based on <b>\'PRI\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_URG_3_TITLE' , 'Zone 2 Table for shipping up to 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_3_DESC' , 'Shipping Table for Zone 2, based on <b>\'URG\'</b> up to 30 kg Shipping Weight.');

define('MODULE_SHIPPING_CHP_COUNTRIES_4_TITLE' , 'Zone 3 Countries');
define('MODULE_SHIPPING_CHP_COUNTRIES_4_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 3 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHP_COST_ECO_4_TITLE' , 'Zone 3 Table for shipping up to 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_4_DESC' , 'Shipping Table for Zone 3, based on <b>\'ECO\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_PRI_4_TITLE' , 'Zone 3 Table for shipping up to 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_4_DESC' , 'Shipping Table for Zone 3, based on <b>\'PRI\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_URG_4_TITLE' , 'Zone 3 Table for shipping up to 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_4_DESC' , 'Shipping Table for Zone 3, based on <b>\'URG\'</b> up to 30 kg Shipping Weight.');

define('MODULE_SHIPPING_CHP_COUNTRIES_5_TITLE' , 'Zone 4 Countries');
define('MODULE_SHIPPING_CHP_COUNTRIES_5_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 4 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHP_COST_ECO_5_TITLE' , 'Zone 4 Table for shipping up to 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_5_DESC' , 'Shipping Table for Zone 4, based on <b>\'ECO\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_PRI_5_TITLE' , 'Zone 4 Table for shipping up to 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_5_DESC' , 'Shipping Table for Zone 4, based on <b>\'PRI\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_URG_5_TITLE' , 'Zone 4 Table for shipping up to 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_5_DESC' , 'Shipping Table for Zone 4, based on <b>\'URG\'</b> up to 30 kg Shipping Weight.');

define('MODULE_SHIPPING_CHP_COUNTRIES_6_TITLE' , 'Zone 4 Countries');
define('MODULE_SHIPPING_CHP_COUNTRIES_6_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 4 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHP_COST_ECO_6_TITLE' , 'Zone 4 Table for shipping up to 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_6_DESC' , 'Shipping Table for Zone 4, based on <b>\'ECO\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_PRI_6_TITLE' , 'Zone 4 Table for shipping up to 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_6_DESC' , 'Shipping Table for Zone 4, based on <b>\'PRI\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_URG_6_TITLE' , 'Zone 4 Table for shipping up to 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_6_DESC' , 'Shipping Table for Zone 4, based on <b>\'URG\'</b> up to 30 kg Shipping Weight.');

define('MODULE_SHIPPING_CHP_COUNTRIES_7_TITLE' , 'Zone 5 Countries');
define('MODULE_SHIPPING_CHP_COUNTRIES_7_DESC' , 'Comma separated list of two character ISO country codes that are part of Zone 5 (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHP_COST_ECO_7_TITLE' , 'Zone 5 Table for shipping up to 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_7_DESC' , 'Shipping Table for Zone 5, based on <b>\'ECO\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_PRI_7_TITLE' , 'Zone 5 Table for shipping up to 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_7_DESC' , 'Shipping Table for Zone 5, based on <b>\'PRI\'</b> up to 30 kg Shipping Weight.');
define('MODULE_SHIPPING_CHP_COST_URG_7_TITLE' , 'Zone 5 Table for shipping up to 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_7_DESC' , 'Shipping Table for Zone 5, based on <b>\'URG\'</b> up to 30 kg Shipping Weight.');
?>