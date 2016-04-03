<?php
/* --------------------------------------------------------------
   $Id: application_top.php 4308 2013-01-14 07:58:14Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_top.php,v 1.158 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (application_top.php,v 1.46 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (application_top.php 1323 2005-10-27) ; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:

   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   --------------------------------------------------------------*/

// DB version, used for updates (_installer)
define('DB_VERSION', 'MOD_2.0.0.0');

//Run Mode
define('RUN_MODE_ADMIN',true);

// Start the clock for the page parse time log
define('PAGE_PARSE_START_TIME', microtime(true));

// security
define('_VALID_XTC',true);

// Disable use_trans_sid as xtc_href_link() does this manually
if (function_exists('ini_set')) {
  @ini_set('session.use_trans_sid', 0);
}

// configuration parameters
if (file_exists('../includes/local/configure.php')) {
  include_once('../includes/local/configure.php');
} else {
  include_once('../includes/configure.php');
}

// default time zone
if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
  date_default_timezone_set('Europe/Berlin');
}

// set the level of error reporting
@ini_set('display_errors', true);
if (is_file(DIR_FS_CATALOG.'export/_error_reporting.admin')) {
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT); //exlude E_STRICT on PHP 5.4
  define('LOGGING_LEVEL', 'INFO');
} elseif (is_file(DIR_FS_CATALOG.'export/_error_reporting.all')) {
  error_reporting(E_ALL); //exlude E_STRICT on PHP 5.4
  define('LOGGING_LEVEL', 'FINE');
} elseif (is_file(DIR_FS_CATALOG.'export/_error_reporting.dev')) {
  error_reporting(-1); // Development value
  define('LOGGING_LEVEL', 'DEBUG');
} else {
  @ini_set('display_errors', false);
  error_reporting(0);
  define('LOGGING_LEVEL', 'WARN');
}

// new error handling
if (is_file(DIR_FS_CATALOG.DIR_WS_INCLUDES.'error_reporting.php')) {
  require_once (DIR_FS_CATALOG.DIR_WS_INCLUDES.'error_reporting.php');
}

// turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
if (version_compare(PHP_VERSION, 5.4, '<') && @ini_get('magic_quotes_sybase') != 0) @ini_set('magic_quotes_sybase', 0);

// security inputfilter for GET/POST/COOKIE
require (DIR_FS_CATALOG.DIR_WS_CLASSES.'inputfilter.php');
$inputfilter = new Inputfilter();
$_GET = $inputfilter->validate($_GET);
$_POST = $inputfilter->validate($_POST);
$_REQUEST = $inputfilter->validate($_REQUEST);

// auto include
require_once (DIR_FS_INC . 'auto_include.inc.php');

// solve compatibility issues
require_once (DIR_WS_FUNCTIONS.'compatibility.php');

// project versison
require_once (DIR_WS_INCLUDES.'version.php');

// Base/PHP_SELF/SSL-PROXY
require_once(DIR_FS_INC . 'set_php_self.inc.php');
$PHP_SELF = set_php_self();

define('TAX_DECIMAL_PLACES', 0);

// Used in the "Backup Manager" to compress backups
define('LOCAL_EXE_GZIP', '/usr/bin/gzip');
define('LOCAL_EXE_GUNZIP', '/usr/bin/gunzip');
define('LOCAL_EXE_ZIP', '/usr/local/bin/zip');
define('LOCAL_EXE_UNZIP', '/usr/local/bin/unzip');

// include the list of project filenames
require (DIR_FS_ADMIN.DIR_WS_INCLUDES.'filenames.php');

// list of project database tables
require_once(DIR_FS_CATALOG.DIR_WS_INCLUDES.'database_tables.php');

// Database
require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
require_once (DIR_FS_INC.'db_functions.inc.php');

// include needed functions
require_once(DIR_FS_INC . 'xtc_get_ip_address.inc.php');
require_once(DIR_FS_INC . 'xtc_setcookie.inc.php');
require_once(DIR_FS_INC . 'xtc_validate_email.inc.php');
require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
require_once(DIR_FS_INC . 'xtc_add_tax.inc.php');
require_once(DIR_FS_INC . 'xtc_get_tax_rate.inc.php');
require_once(DIR_FS_INC . 'xtc_get_qty.inc.php');
require_once(DIR_FS_INC . 'xtc_product_link.inc.php');
require_once(DIR_FS_INC . 'xtc_cleanName.inc.php');
require_once(DIR_FS_INC . 'xtc_get_top_level_domain.inc.php');
require_once(DIR_FS_INC . 'html_encoding.php'); //new function for PHP5.4
require_once(DIR_FS_INC . 'xtc_backup_restore_configuration.php');
require_once(DIR_FS_INC . 'xtc_check_agent.inc.php');
require_once(DIR_FS_INC . 'xtc_parse_category_path.inc.php');
require_once(DIR_FS_INC . 'xtc_input_validation.inc.php');
require_once(DIR_FS_INC . 'xtc_get_category_path.inc.php');

foreach(auto_include(DIR_FS_ADMIN.'includes/extra/functions/','php') as $file) require ($file);

// design layout (wide of boxes in pixels) (default: 125)
define('BOX_WIDTH', 125);

// Define how do we update currency exchange rates
// Possible values are 'yahooapis' 'cryptonator' or ''
define('CURRENCY_SERVER_PRIMARY', 'yahooapis');
define('CURRENCY_SERVER_BACKUP', 'cryptonator');

// make a connection to the database... now
xtc_db_connect() or die('Unable to connect to database server!');

// set application wide parameters
define('DB_CACHE', 'false');
$duplicate_configuration = array();
$configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION . '');
while ($configuration = xtc_db_fetch_array($configuration_query)) {
  if ($configuration['cfgKey'] != 'DB_CACHE' && $configuration['cfgKey'] != 'STORE_DB_TRANSACTIONS') {
    if (!defined($configuration['cfgKey'])) {
      define($configuration['cfgKey'], stripslashes($configuration['cfgValue']));
    } else {
      $duplicate_configuration[] = $configuration['cfgKey'];
    }
  }
}

foreach(auto_include(DIR_FS_ADMIN.'includes/extra/application_top/application_top_begin/','php') as $file) require ($file);

define('FILENAME_IMAGEMANIPULATOR',IMAGE_MANIPULATOR);

// initialize the logger class
require(DIR_WS_CLASSES . 'logger.php');

// shopping cart class
require(DIR_WS_CLASSES . 'shopping_cart.php');

// todo
require(DIR_WS_FUNCTIONS . 'general.php');

// define how the session functions will be used
require(DIR_WS_FUNCTIONS . 'sessions.php');

  // define our general functions used application-wide
require(DIR_WS_FUNCTIONS . 'html_output.php');

// set the type of request (secure or not)
if (file_exists(DIR_WS_INCLUDES . 'request_type.php')) {
  include (DIR_WS_INCLUDES . 'request_type.php');
} else {
  $request_type = 'NONSSL';
}

// set the top level domains
$http_domain_arr = xtc_get_top_level_domain(HTTP_SERVER);
$https_domain_arr = xtc_get_top_level_domain(HTTPS_SERVER);
$http_domain = $http_domain_arr['new'];
$https_domain = $https_domain_arr['new'];
$current_domain = (($request_type == 'NONSSL') ? $http_domain : $https_domain);

// set the top level domains - old
$http_domain_old = $http_domain_arr['old'];
$https_domain_old = $https_domain_arr['old'];
$current_domain_old = (($request_type == 'NONSSL') ? $http_domain_old : $https_domain_old);

// set the session name and save path
// set the session cookie parameters
// set the session ID if it exists
// start the session
// Redirect search engines with session id to the same url without session id to prevent indexing session id urls
// check for Cookie usage
// check the Agent
include (DIR_FS_CATALOG.DIR_WS_MODULES.'set_session_and_cookie_parameters.php');

// verify the ssl_session_id if the feature is enabled
// verify the browser user agent if the feature is enabled
// verify the IP address if the feature is enabled
include (DIR_FS_CATALOG.DIR_WS_MODULES.'verify_session.php');

// set the language
include (DIR_FS_CATALOG.DIR_WS_MODULES.'set_language_sessions.php');

// include the language translations
require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'.$_SESSION['language'] . '.php');
require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/buttons.php');
$current_page = basename($PHP_SELF);
if (is_file(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/' . $current_page)) {
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/' . $current_page);
}

// write customers status in session
require(DIR_FS_CATALOG.DIR_WS_INCLUDES.'write_customers_status.php');

// call from filemanager
if (defined('_IS_FILEMANAGER')) return;

// check permission
if (is_file(DIR_FS_ADMIN.$current_page) == false || $_SESSION['customers_status']['customers_status_id'] !== '0') {
  xtc_redirect(xtc_catalog_href_link(FILENAME_LOGIN));
}

// define our localization functions
require(DIR_WS_FUNCTIONS . 'localization.php');

// setup our boxes
require(DIR_WS_CLASSES . 'table_block.php');
require(DIR_WS_CLASSES . 'box.php');

// initialize the message stack for output messages
require(DIR_WS_CLASSES . 'message_stack.php');
$messageStack = new messageStack();

// verfiy CSRF Token
if (CSRF_TOKEN_SYSTEM == 'true') {
  require_once(DIR_FS_INC . 'csrf_token.inc.php');
}

// split-page-results
require(DIR_WS_CLASSES . 'split_page_results.php');

// entry/item info classes
require(DIR_WS_CLASSES . 'object_info.php');

// file uploading class
require(DIR_WS_CLASSES . 'upload.php');

// content, product, category - sql group_check/fsk_lock
require (DIR_FS_CATALOG.DIR_WS_INCLUDES.'define_conditions.php');

// add_select
require (DIR_FS_CATALOG.DIR_WS_INCLUDES.'define_add_select.php');

// calculate category path
$cPath = isset($_GET['cPath']) ? $_GET['cPath'] : '';
if (strlen($cPath) > 0) {
  $cPath_array = xtc_parse_category_path($cPath);
  $current_category_id = end($cPath_array);
} else {
  $current_category_id = 0;
}

// check if a default currency is set
if (!defined('DEFAULT_CURRENCY')) {
  $messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED, 'error');
}

// check if a default language is set
if (!defined('DEFAULT_LANGUAGE')) {
  $messageStack->add(ERROR_NO_DEFAULT_LANGUAGE_DEFINED, 'error');
}

// for Customers Status
xtc_get_customers_statuses();

$pagename = strtok($current_page, '.');
if (!isset($_SESSION['customer_id'])) {
  xtc_redirect(xtc_catalog_href_link(FILENAME_LOGIN));
}

xtc_check_permission($pagename);

foreach(auto_include(DIR_FS_ADMIN.'includes/extra/application_top/application_top_end/','php') as $file) require ($file);

//compatibility for modified eCommerce Shopsoftware 1.06 files
defined('DIR_WS_BASE') OR define('DIR_WS_BASE', '');
?>