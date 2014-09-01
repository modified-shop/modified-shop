<?php
/*------------------------------------------------------------------------------
   $Id: gls.php,v 1.1 2004/08/13 10:00:13 HHGAG Exp $

   XTC-GLS Shipping Module - Contribution for XT-Commerce http://www.xt-commerce.com
   modified by http://www.hhgag.com

   Copyright (c) 2004 H.H.G.
   -----------------------------------------------------------------------------
   based on:
   (c) 2003 Deutsche Post Module
   Original written by Marcel Bossert-Schwab (webmaster@wernich.de), Version 1.2b
   Addon Released under GLSL V2.0 by Gunter Sammet (Gunter@SammySolutions.com)

   Contribution based on:

   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2002 - 2003 osCommerce

   Released under the GNU General Public License

   ---------------------------------------------------------------------------*/
define('MODULE_SHIPPING_GLS_TEXT_TITLE', 'German Logistics Systems');
define('MODULE_SHIPPING_GLS_TEXT_DESCRIPTION', 'German Logistics Systems - Global Delivery Module');
define('MODULE_SHIPPING_GLS_TEXT_WAY', 'Delivery to');
define('MODULE_SHIPPING_GLS_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_GLS_INVALID_ZONE', 'Sorry, this carrier can not ship into this country');
define('MODULE_SHIPPING_GLS_UNDEFINED_RATE', 'The shipping costs can not be calculated at the moment.');
define('MODULE_SHIPPING_GLS_FREE_SHIPPING', 'Free shipping');
define('MODULE_SHIPPING_GLS_SUBSIDIZED_SHIPPING', 'We subsidized the shipping.');

define('MODULE_SHIPPING_GLS_STATUS_TITLE', 'German Logistics Systems');
define('MODULE_SHIPPING_GLS_STATUS_DESC', 'Would you like to offer shipping with German Logistics Systems?');
define('MODULE_SHIPPING_GLS_HANDLING_TITLE', 'Handling Fee');
define('MODULE_SHIPPING_GLS_HANDLING_DESC', 'Handling fee for this shipping method');
define('MODULE_SHIPPING_GLS_ALLOWED_TITLE' , 'Allowed Zones');
define('MODULE_SHIPPING_GLS_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones)');
define('MODULE_SHIPPING_GLS_SORT_ORDER_TITLE', 'Sort order');
define('MODULE_SHIPPING_GLS_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');
define('MODULE_SHIPPING_GLS_TAX_CLASS_TITLE', 'Tax Class');
define('MODULE_SHIPPING_GLS_TAX_CLASS_DESC', 'Use the following tax class on the shipping fee.');
define('MODULE_SHIPPING_GLS_ZONE_TITLE', 'Shipping Zone');
define('MODULE_SHIPPING_GLS_ZONE_DESC', 'If a zone is selected, only enable this shipping method for that zone.');

?>