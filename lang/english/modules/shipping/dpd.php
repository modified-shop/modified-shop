<?php
/*------------------------------------------------------------------------------
   v 1.0
   XTC-DPD Shipping Module - Contribution for XT-Commerce http://xt-commerce.com
   modified by http://www.hwangelshop.de

   Copyrigt (c) 2004 cigamth
   ------------------------------------------------------------------------------
   $Id: dpd.php 2666 2012-02-23 11:38:17Z dokuman $

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
define('MODULE_SHIPPING_DPD_TEXT_TITLE', 'DPD Dynamic Parcel Distribution');
define('MODULE_SHIPPING_DPD_TEXT_DESCRIPTION', 'DPD Dynamic Parcel Distribution - Worldwide Shipping Module');
define('MODULE_SHIPPING_DPD_TEXT_WAY', 'Delivery to');
define('MODULE_SHIPPING_DPD_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_DPD_INVALID_ZONE', 'Sorry, this carrier can not ship into this country');
define('MODULE_SHIPPING_DPD_UNDEFINED_RATE', 'The shipping costs can not be calculated at the moment.');
define('MODULE_SHIPPING_DPD_FREE_SHIPPING', 'Free shipping');
define('MODULE_SHIPPING_DPD_SUBSIDIZED_SHIPPING', 'We subsidized the shipping.');

define('MODULE_SHIPPING_DPD_STATUS_TITLE', 'DPD Dynamic Parcel Distribution');
define('MODULE_SHIPPING_DPD_STATUS_DESC', 'Would you like to offer shipping with DPD Dynamic Parcel Distribution?');
define('MODULE_SHIPPING_DPD_HANDLING_TITLE', 'Handling Fee');
define('MODULE_SHIPPING_DPD_HANDLING_DESC', 'Handling fee for this shipping method');
define('MODULE_SHIPPING_DPD_ALLOWED_TITLE' , 'Allowed Zones');
define('MODULE_SHIPPING_DPD_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones)');
define('MODULE_SHIPPING_DPD_SORT_ORDER_TITLE', 'Sort order');
define('MODULE_SHIPPING_DPD_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');
define('MODULE_SHIPPING_DPD_TAX_CLASS_TITLE', 'Tax Class');
define('MODULE_SHIPPING_DPD_TAX_CLASS_DESC', 'Use the following tax class on the shipping fee.');
define('MODULE_SHIPPING_DPD_ZONE_TITLE', 'Shipping Zone');
define('MODULE_SHIPPING_DPD_ZONE_DESC', 'If a zone is selected, only enable this shipping method for that zone.');

?>