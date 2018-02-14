<?php
/* -----------------------------------------------------------------------------------------
   $Id: hermes.php 5121 2013-07-18 11:38:19Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(flat.php,v 1.6 2003/02/16); www.oscommerce.com
   (c) 2003	 nextcommerce (flat.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_HERMES_TEXT_TITLE', 'Shipping with Hermes');
define('MODULE_SHIPPING_HERMES_TEXT_DESCRIPTION', 'Hermes Paket Service');
define('MODULE_SHIPPING_HERMES_TEXT_WAY_DE', 'Throughout Germany: ');
define('MODULE_SHIPPING_HERMES_TEXT_WAY_EU', 'International: ');
define('MODULE_SHIPPING_HERMES_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_HERMES_TAX_CLASS_TITLE' , 'Tax Rate');
define('MODULE_SHIPPING_HERMES_TAX_CLASS_DESC' , 'Choose the tax rate for this shipping type.');

define('MODULE_SHIPPING_HERMES_STATUS_TITLE' , 'Enable Hermes Paket Shop');
define('MODULE_SHIPPING_HERMES_STATUS_DESC' , 'Modul by Leonid Lezner');

define('MODULE_SHIPPING_HERMES_NATIONAL_TITLE' , 'National shipping (DE)');
define('MODULE_SHIPPING_HERMES_NATIONAL_DESC' , 'Price for Classes: S;M;L');

define('MODULE_SHIPPING_HERMES_INTERNATIONAL_TITLE' , 'International shipping (all except DE)');
define('MODULE_SHIPPING_HERMES_INTERNATIONAL_DESC' , 'Price for Classes: S;M;L');

define('MODULE_SHIPPING_HERMES_GEWICHT_TITLE' , 'Class Definition');
define('MODULE_SHIPPING_HERMES_GEWICHT_DESC' , 'Max. Weight (kg) for Classes: S;M;L');

define('MODULE_SHIPPING_HERMES_MAXGEWICHT_TITLE' , 'Maximum Weight');
define('MODULE_SHIPPING_HERMES_MAXGEWICHT_DESC' , 'Max. Weight for this shipping method (kg)');

define('MODULE_SHIPPING_HERMES_SORT_ORDER_TITLE' , 'Display order');
define('MODULE_SHIPPING_HERMES_SORT_ORDER_DESC' , 'Lowermost shown first.');

define('MODULE_SHIPPING_HERMES_ALLOWED_TITLE' , 'Individual shipping zones');
define('MODULE_SHIPPING_HERMES_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
?>