<?php
/* --------------------------------------------------------------
   $Id: modules.php 2567 2011-12-29 10:44:32Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(modules.php,v 1.8 2002/04/09); www.oscommerce.com
   (c) 2003 nextcommerce (modules.php,v 1.5 2003/08/14); www.nextcommerce.org
   (c) 2006 XT-Commerce (modules.php 899 2005-04-29)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE_MODULES_PAYMENT', 'Payment Modules');
define('HEADING_TITLE_MODULES_SHIPPING', 'Shipping Modules');
define('HEADING_TITLE_MODULES_ORDER_TOTAL', 'Order Total Modules');

define('TABLE_HEADING_MODULES', 'Modules');
define('TABLE_HEADING_SORT_ORDER', 'Sort Order');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_MODULE_DIRECTORY', 'Module Directory:');
define('TEXT_MODULE_FILE_MISSING', '<b>Language file "%s" is missing, module "%s" will not be displayed!</b>');
define('TABLE_HEADING_FILENAME','Module name (for internal usage)');

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
define('TEXT_INFO_DELETE_PAYPAL', 'If you uninstall this module now, the PayPal transaction data are deleted!<br />If you want to receive these data, press on abort now and only deactivate the module. (Activate module = False)');
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul
define('TABLE_HEADING_MODULES_INSTALLED', 'Modules installed');
define('TABLE_HEADING_MODULES_PREFERRED', 'Popular modules');
define('TABLE_HEADING_MODULES_NOT_INSTALLED', 'Modules not installed');
define('TEXT_MODULE_UPDATE_NEEDED', 'The following modules have been updated and need to update the database. For this please save the settings and reinstall these modules.');
?>