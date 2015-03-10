<?php
/* --------------------------------------------------------------
   $Id: start.php 2585 2012-01-03 14:25:49Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (start.php,v 1.1 2003/08/19); www.nextcommerce.org
   (c) 2006 xt:Commerce (start.php 890 2005-04-27); www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('ATTENTION_TITLE','! ATTENTION !');

// text for Warnings:
if (!defined('APS_INSTALL')) { //DokuMan - use alternative text for TEXT_FILE_WARNING when using APS package installation
  define('TEXT_FILE_WARNING_WRITABLE','<b>WARNING:</b><br />Following files are writeable. Please change the permissions of these files for security reasons. <b>(444)</b> in unix, <b>(read-only)</b> in Win32.');
} else {
  define('TEXT_FILE_WARNING_WRITABLE','<b>WARNING:</b><br />Following files are writeable. Please change the permissions of these files for security reasons. <b>(444)</b> in unix, <b>(read-only)</b> in Win32.<br />In case that this software was installed by a software package of your webhoster, the file permissions may have to be set differently (HostEurope: <b>CHMOD 400</b> or <b>CHMOD 440</b>)');
}
define('TEXT_FILE_WARNING','<b>WARNING:</b><br />Following files must be writeable. Please change the permissions of these files. <b>(777)</b> in unix, <b>(read-write)</b> in Win32.');
define('TEXT_FOLDER_WARNING','<b>WARNING:</b><br />Following folders must be writeable. Please change the permissions of these folders. <b>(777)</b> in unix, <b>(read-write)</b> in Win32.');
define('REPORT_GENERATED_FOR','Report For:');
define('REPORT_GENERATED_ON','Generated On:');
define('FIRST_VISIT_ON','First Visit:');
define('HEADING_QUICK_STATS','Quick stats');
define('VISITS_TODAY','Visits Today:');
define('UNIQUE_TODAY','Unique Today:');
define('DAILY_AVERAGE','Daily Average:');
define('TOTAL_VISITS','Total Visits:');
define('TOTAL_UNIQUE','Total Unique:');
define('TOP_REFFERER','Top Referring Host:');
define('TOP_ENGINE','Top Search Engine:');
define('DAY_SUMMARY','30 Day Summary:');
define('VERY_LAST_VISITORS','Last 10 Visitors:');
define('TODAY_VISITORS','Visitors of today:');
define('LAST_VISITORS','Last 100 Visitors:');
define('ALL_LAST_VISITORS','All Visitors:');
define('DATE_TIME','Date / Time:');
define('IP_ADRESS','IP Adress:');
define('OPERATING_SYSTEM','Operating System:');
define('REFFERING_HOST','Referring Host:');
define('ENTRY_PAGE','Entry Page:');
define('HOURLY_TRAFFIC_SUMMARY','Hourly Traffic Summary');
define('WEB_BROWSER_SUMMARY','Web Browser Summary');
define('OPERATING_SYSTEM_SUMMARY','Operatin System Summary');
define('TOP_REFERRERS','Top 10 Referrers');
define('TOP_HOSTS','Top Ten Hosts');
define('LIST_ALL','List all');
define('SEARCH_ENGINE_SUMMARY','Search Engine Summary');
define('SEARCH_ENGINE_SUMMARY_TEXT',' ( Percentage is based on total visits referred from search engines. )');
define('SEARCH_QUERY_SUMMARY','Search Query Summary');
define('SEARCH_QUERY_SUMMARY_TEXT',' ) ( Percentage is based on total search query strings logged. )');
define('REFERRING_URL','Reffering Url');
define('HITS','Hits');
define('PERCENTAGE','Percentage');
define('HOST','Host');

// NEU HINZUGEFUEGT 04.12.2008 - Neue Startseite im Admin BOF

// BOF - vr 2010-04-01 -  Added missing definitions, see below
// define('HEADING_TITLE', 'Orders');
// EOF - vr 2010-04-01 -  Added missing definitions
define('HEADING_TITLE_SEARCH', 'Order-Nr.:');
define('HEADING_TITLE_STATUS', 'Status:');
define('TABLE_HEADING_AFTERBUY', 'Afterbuy'); //Dokuman - 2009-05-27 - added missing definition
define('TABLE_HEADING_CUSTOMERS', 'Customers');
define('TABLE_HEADING_ORDER_TOTAL', 'Total value');
define('TABLE_HEADING_DATE_PURCHASED', 'Order Date');
define('TABLE_HEADING_STATUS', 'Status');
//define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_QUANTITY', 'Quantity');
define('TABLE_HEADING_PRODUCTS_MODEL', 'Product-Nr.');
define('TABLE_HEADING_PRODUCTS', 'Product');
define('TABLE_HEADING_TAX', 'VAT');
define('TABLE_HEADING_TOTAL', 'Total sum');
define('TABLE_HEADING_DATE_ADDED', 'added on:');
define('ENTRY_CUSTOMER', 'Customer:');
define('TEXT_DATE_ORDER_CREATED', 'Order Date:');
define('TEXT_INFO_PAYMENT_METHOD', 'Payment method:');
define('TEXT_VALIDATING','Not validated');
define('TEXT_ALL_ORDERS', 'All orders');
define('TEXT_NO_ORDER_HISTORY', 'No order history available');
define('TEXT_DATE_ORDER_LAST_MODIFIED','Last change');

// BOF - Tomcraft - 2009-11-25 - Added missing definitions for /admin/start.php/
define('TOTAL_CUSTOMERS','Customers total');
define('TOTAL_SUBSCRIBERS','Newsletter 	subscriptions');
define('TOTAL_PRODUCTS_ACTIVE','Active products');
define('TOTAL_PRODUCTS_INACTIVE','Inactive products');
define('TOTAL_PRODUCTS','Products total');
define('TOTAL_SPECIALS','Specials');
// EOF - Tomcraft - 2009-11-25 - Added missing definitions for /admin/start.php/
// BOF - Tomcraft - 2009-11-30 - Added missing definitions for /admin/start.php/
define('UNASSIGNED', 'Unassigned');
define('TURNOVER_TODAY', 'Turnover today');
define('TURNOVER_YESTERDAY', 'Turnover yesterday');
define('TURNOVER_THIS_MONTH', 'this month');
define('TURNOVER_LAST_MONTH', 'last month (all)');
define('TURNOVER_LAST_MONTH_PAID', 'last month (paid)');
define('TOTAL_TURNOVER', 'Total turnover');
// EOF - Tomcraft - 2009-11-30 - Added missing definitions for /admin/start.php/

// BOF - vr 2010-04-01 -  Added missing definitions
// main heading
define('HEADING_TITLE', 'Welcome to the Admin Area');
// users online
define('TABLE_CAPTION_USERS_ONLINE', 'Online');
define('TABLE_CAPTION_USERS_ONLINE_HINT', '***Please click user name for details***');
define('TABLE_HEADING_USERS_ONLINE_SINCE', 'Online Since');
define('TABLE_HEADING_USERS_ONLINE_NAME', 'Name');
define('TABLE_HEADING_USERS_ONLINE_LAST_CLICK', 'Last Click');
define('TABLE_HEADING_USERS_ONLINE_INFO', 'Info');
define('TABLE_CELL_USERS_ONLINE_INFO', 'More...');
// new customers
define('TABLE_CAPTION_NEW_CUSTOMERS', 'Customers');
define('TABLE_CAPTION_NEW_CUSTOMERS_COMMENT', '(Last 15)');
define('TABLE_HEADING_NEW_CUSTOMERS_LASTNAME', 'Last Name');
define('TABLE_HEADING_NEW_CUSTOMERS_FIRSTNAME', 'First Name');
define('TABLE_HEADING_NEW_CUSTOMERS_REGISTERED', 'Registered');
define('TABLE_HEADING_NEW_CUSTOMERS_EDIT', 'Edit');
define('TABLE_HEADING_NEW_CUSTOMERS_ORDERS', 'Orders');
define('TABLE_CELL_NEW_CUSTOMERS_EDIT', 'Edit...');
define('TABLE_CELL_NEW_CUSTOMERS_DELETE', 'Delete...');
define('TABLE_CELL_NEW_CUSTOMERS_ORDERS', 'Show...');
// new orders
define('TABLE_CAPTION_NEW_ORDERS', 'Orders');
define('TABLE_CAPTION_NEW_ORDERS_COMMENT', '(Last 20)');
define('TABLE_HEADING_NEW_ORDERS_ORDER_NUMBER', 'Order #');
define('TABLE_HEADING_NEW_ORDERS_ORDER_DATE', 'Order Date');
define('TABLE_HEADING_NEW_ORDERS_CUSTOMERS_NAME', 'Customer\'s Name');
define('TABLE_HEADING_NEW_ORDERS_EDIT', 'Edit');
define('TABLE_HEADING_NEW_ORDERS_DELETE', 'Delete');
// newsfeed
define('TABLE_CAPTION_NEWSFEED', 'Vitit the');
// birthdays
define('TABLE_CAPTION_BIRTHDAYS', 'Birthdays');
define('TABLE_CELL_BIRTHDAYS_TODAY', 'Customers Whose Birthday is Today');
define('TABLE_CELL_BIRTHDAYS_THIS_MONTH', 'Upcoming Birthdays Of Customers This Month');
// EOF - vr 2010-04-01 -  Added missing definitions
define('HEADING_CAPTION_STATISTIC', 'Statistics');
// security check

// DB Version check
define('ERROR_DB_VERSION_UPDATE', 'WARNING: Your DB needs to be updated, please start the <a href="'.DIR_WS_CATALOG.'_installer/">installer</a>:');
define('ERROR_DB_VERSION_UPDATE_INFO', 'DB needs update from release %s to %s.');

// EMail check
define('ERROR_EMAIL_CHECK', '<strong>WARNING:</strong> The following EMail-Address looks incorrect:');
define('ERROR_EMAIL_CHECK_INFO', '%s: &lt;%s&gt;');

// security check DB FILE permission
define('WARNING_DB_FILE_PRIVILEGES', '<strong>WARNING:</strong> FILE-Privileges are enabled in the database &rsquo;'.DB_DATABASE.'&lsquo; for user &rsquo;'.DB_SERVER_USERNAME.'&lsquo;!');

// register_globals check
define('WARNING_REGISTER_GLOBALS', '<strong>WARNING:</strong> This feature has been <strong>DEPRECATED</strong> as of PHP 5.3.0 and <strong>REMOVED</strong> as of PHP 5.4.0. Please contact your hoster to deactivate &quot;register_globals&quot;.');
?>