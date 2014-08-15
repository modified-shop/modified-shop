<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(currencies.php,v 1.15 2003/05/02); www.oscommerce.com
   (c) 2003 nextcommerce (currencies.php,v 1.4 2003/08/14); www.nextcommerce.org
   (c) 2006 XT-Commerce (currencies.php 899 2005-04-29)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Currencies');

define('TABLE_HEADING_CURRENCY_NAME', 'Currency name');
define('TABLE_HEADING_CURRENCY_CODES', 'Currency Code');
define('TABLE_HEADING_CURRENCY_VALUE', 'Exchange rate');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_INFO_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_INFO_CURRENCY_TITLE', 'Currency name:');
define('TEXT_INFO_CURRENCY_CODE', 'Currency code ISO 4217:');
define('TEXT_INFO_CURRENCY_SYMBOL_LEFT', 'Currency Symbol Left:');
define('TEXT_INFO_CURRENCY_SYMBOL_RIGHT', 'Currency Symbol Right:');
define('TEXT_INFO_CURRENCY_DECIMAL_POINT', 'Symbol for Decimal Point:');
define('TEXT_INFO_CURRENCY_THOUSANDS_POINT', 'Symbol for Thousands Point:');
define('TEXT_INFO_CURRENCY_DECIMAL_PLACES', 'Decimal Places:');
define('TEXT_INFO_CURRENCY_LAST_UPDATED', 'Last Updated:');
define('TEXT_INFO_CURRENCY_VALUE', 'Exchange rate (base currency is 1.0000):');
define('TEXT_INFO_CURRENCY_EXAMPLE', 'Example Output:');
define('TEXT_INFO_INSERT_INTRO', 'Please enter the new currency with its related data');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this currency?');
define('TEXT_INFO_HEADING_NEW_CURRENCY', 'New Currency');
define('TEXT_INFO_HEADING_EDIT_CURRENCY', 'Edit Currency');
define('TEXT_INFO_HEADING_DELETE_CURRENCY', 'Delete Currency');
define('TEXT_INFO_SET_AS_DEFAULT', TEXT_SET_DEFAULT . ' (requires a manual update of currency values)');
define('TEXT_INFO_CURRENCY_UPDATED', 'The exchange rate for %s (%s) was updated successfully');

define('ERROR_REMOVE_DEFAULT_CURRENCY', 'Error: The default currency can not be removed. Please set another currency as default, and try again.');
define('ERROR_CURRENCY_INVALID', 'Error: The exchange rate for %s (%s) was not updated. Is it a valid currency code?');
define('WARNING_PRIMARY_SERVER_FAILED','Warning: The primary exchange rate server (%s) failed for %s (%s) - trying the secondary exchange rate server');
?>