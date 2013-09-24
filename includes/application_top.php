<?php
/* -----------------------------------------------------------------------------------------
   $Id: application_top.php 3121 2012-06-23 19:29:57Z franky-n-xtcm $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_top.php,v 1.273 2003/05/19); www.oscommerce.com
   (c) 2003 nextcommerce (application_top.php,v 1.54 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce (application_top.php 1194 2010-08-22)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Add A Quickie v1.0 Autor  Harald Ponce de Leon

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// start the timer for the page parse time log
define('PAGE_PARSE_START_TIME', microtime(true));

// configuration parameters
if (file_exists('includes/local/configure.php')) {
  include ('includes/local/configure.php');
} else {
  include ('includes/configure.php');
}

// call Installer
if (DB_DATABASE == '' && is_dir('./_installer')) {
  header("Location: ./_installer");
  exit();
}

/**
 * set the level of error reporting
 */
@ini_set('display_errors', true);
if (is_file(DIR_FS_CATALOG.'export/_error_reporting.shop')) {
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT); //exlude E_STRICT on PHP 5.4
} elseif (is_file(DIR_FS_CATALOG.'export/_error_reporting.all')) {
  error_reporting(E_ALL); //exlude E_STRICT on PHP 5.4
} elseif (is_file(DIR_FS_CATALOG.'export/_error_reporting.dev')) {
  error_reporting(-1); // Development value
} else {
  @ini_set('display_errors', false);
  error_reporting(0);
}

/**
 * new error handling
 */
require_once (DIR_WS_INCLUDES.'error_reporting.php');

/*
 * turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
if (version_compare(PHP_VERSION, 5.4, '<') && @ini_get('magic_quotes_sybase') != 0) @ini_set('magic_quotes_sybase', 0);

require_once (DIR_FS_INC . 'auto_require.inc.php');

// include the list of project filenames
require (DIR_WS_INCLUDES.'filenames.php');

// default time zone
if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
  date_default_timezone_set('Europe/Berlin');
}

// Debug-Log-Class - thx to franky
include_once(DIR_WS_CLASSES.'class.debug.php');
$log = new debug;

// for xtc_db_perform
$php4_3_10 = (0 == version_compare(phpversion(), "4.3.10"));
define('PHP4_3_10', $php4_3_10);

// project version
define('PROJECT_VERSION', 'modified eCommerce Shopsoftware');

define('TAX_DECIMAL_PLACES', 0);

// set the type of request (secure or not)
if (file_exists('includes/request_type.php')) {
  include ('includes/request_type.php');
} else {
  $request_type = 'NONSSL';
}
// Base/PHP_SELF/SSL-PROXY
require_once(DIR_FS_INC . 'set_php_self.inc.php');
$PHP_SELF = set_php_self();

// list of project database tables
require (DIR_WS_INCLUDES.'database_tables.php');

// graduated prices model or products assigned ?
define('GRADUATED_ASSIGN', 'true');

// Database
require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
require_once (DIR_FS_INC.'db_functions.inc.php');

// html basics
require_once (DIR_FS_INC.'xtc_href_link.inc.php');
require_once (DIR_FS_INC.'xtc_php_mail.inc.php');

require_once (DIR_FS_INC.'xtc_product_link.inc.php');
require_once (DIR_FS_INC.'xtc_category_link.inc.php');
require_once (DIR_FS_INC.'xtc_manufacturer_link.inc.php');

// html functions
require_once (DIR_FS_INC.'xtc_draw_checkbox_field.inc.php');
require_once (DIR_FS_INC.'xtc_draw_form.inc.php');
require_once (DIR_FS_INC.'xtc_draw_hidden_field.inc.php');
require_once (DIR_FS_INC.'xtc_draw_input_field.inc.php');
require_once (DIR_FS_INC.'xtc_draw_password_field.inc.php');
require_once (DIR_FS_INC.'xtc_draw_pull_down_menu.inc.php');
require_once (DIR_FS_INC.'xtc_draw_radio_field.inc.php');
require_once (DIR_FS_INC.'xtc_draw_selection_field.inc.php');
require_once (DIR_FS_INC.'xtc_draw_separator.inc.php');
require_once (DIR_FS_INC.'xtc_draw_textarea_field.inc.php');
require_once (DIR_FS_INC.'xtc_image_button.inc.php');

require_once (DIR_FS_INC.'xtc_not_null.inc.php');
require_once (DIR_FS_INC.'xtc_update_whos_online.inc.php');
require_once (DIR_FS_INC.'xtc_activate_banners.inc.php');
require_once (DIR_FS_INC.'xtc_expire_banners.inc.php');
require_once (DIR_FS_INC.'xtc_expire_specials.inc.php');
require_once (DIR_FS_INC.'xtc_parse_category_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_product_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_top_level_domain.inc.php');
require_once (DIR_FS_INC.'xtc_get_category_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_content_path.inc.php');

require_once (DIR_FS_INC.'xtc_get_parent_categories.inc.php');
require_once (DIR_FS_INC.'xtc_redirect.inc.php');
require_once (DIR_FS_INC.'xtc_get_uprid.inc.php');
require_once (DIR_FS_INC.'xtc_get_all_get_params.inc.php');
require_once (DIR_FS_INC.'xtc_has_product_attributes.inc.php');
require_once (DIR_FS_INC.'xtc_image.inc.php');
require_once (DIR_FS_INC.'xtc_check_stock_attributes.inc.php');
require_once (DIR_FS_INC.'xtc_currency_exists.inc.php');
require_once (DIR_FS_INC.'xtc_remove_non_numeric.inc.php');
require_once (DIR_FS_INC.'xtc_get_ip_address.inc.php');
require_once (DIR_FS_INC.'xtc_setcookie.inc.php');
require_once (DIR_FS_INC.'xtc_check_agent.inc.php');
require_once (DIR_FS_INC.'xtc_count_cart.inc.php');
require_once (DIR_FS_INC.'xtc_get_qty.inc.php');
require_once (DIR_FS_INC.'create_coupon_code.inc.php');
require_once (DIR_FS_INC.'xtc_gv_account_update.inc.php');
require_once (DIR_FS_INC.'xtc_get_tax_rate_from_desc.inc.php');
require_once (DIR_FS_INC.'xtc_get_tax_rate.inc.php');
require_once (DIR_FS_INC.'xtc_add_tax.inc.php');
require_once (DIR_FS_INC.'xtc_cleanName.inc.php');
require_once (DIR_FS_INC.'xtc_calculate_tax.inc.php');
require_once (DIR_FS_INC.'xtc_input_validation.inc.php');
require_once (DIR_FS_INC.'xtc_js_lang.php');
require_once (DIR_FS_INC.'html_encoding.php'); //new function for PHP5.4

foreach(auto_require(DIR_FS_CATALOG.'includes/extra/functions/','php') as $file) require ($file);

// make a connection to the database... now
xtc_db_connect() or die('Unable to connect to database server!');

// load configuration
$configuration_query = xtc_db_query('SELECT configuration_key, configuration_value FROM '.TABLE_CONFIGURATION);
while ($configuration = xtc_db_fetch_array($configuration_query)) {
  define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
}

foreach(auto_require(DIR_FS_CATALOG.'includes/extra/application_top_begin/','php') as $file) require ($file);

// Set the length of the redeem code, the longer the more secure
// Kommt eigentlich schon aus der Table configuration
if(!defined('SECURITY_CODE_LENGTH')) {
  define('SECURITY_CODE_LENGTH', '10');
}

// PHPMailer
require_once (DIR_WS_CLASSES.'class.phpmailer.php');
if (EMAIL_TRANSPORT == 'smtp') {
  require_once (DIR_WS_CLASSES.'class.smtp.php');
}

require_once (DIR_FS_INC.'xtc_Security.inc.php');

// move to xtc_db_queryCached.inc.php
function xtDBquery($query) {
  if (defined('DB_CACHE') && DB_CACHE == 'true') {
    $result = xtc_db_queryCached($query);
  } else {
    $result = xtc_db_query($query);
  }
  return $result;
}

function CacheCheck() {
  if (USE_CACHE == 'false') return false;
  if (!isset($_COOKIE['MODsid'])) return false;
  return true;
}

// if gzip_compression is enabled and gzip_off is not set, start to buffer the output
if ((!isset($gzip_off) || !$gzip_off) && (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded = extension_loaded('zlib')) && (PHP_VERSION >= '4')) {
  if (($ini_zlib_output_compression = (int) ini_get('zlib.output_compression')) < 1) {
    ob_start('ob_gzhandler');
  } else {
    ini_set('zlib.output_compression_level', GZIP_LEVEL);
  }
}

// security inputfilter for GET/POST/COOKIE
require (DIR_WS_CLASSES.'class.inputfilter.php');
$InputFilter = new InputFilter();

$_GET = $InputFilter->process($_GET);
$_POST = $InputFilter->process($_POST);
$_REQUEST = $InputFilter->process($_REQUEST);
$_GET = $InputFilter->safeSQL($_GET);
$_POST = $InputFilter->safeSQL($_POST);
$_REQUEST = $InputFilter->safeSQL($_REQUEST);


// set the top level domains
$http_domain = xtc_get_top_level_domain(HTTP_SERVER);
$https_domain = xtc_get_top_level_domain(HTTPS_SERVER);
$current_domain = (($request_type == 'NONSSL') ? $http_domain : $https_domain);

// include shopping cart class
require (DIR_WS_CLASSES.'shopping_cart.php');

// include navigation history class
require (DIR_WS_CLASSES.'navigation_history.php');

// some code to solve compatibility issues
require (DIR_WS_FUNCTIONS.'compatibility.php');

// define how the session functions will be used
require (DIR_WS_FUNCTIONS.'sessions.php');

// set the session name and save path
// set the session cookie parameters
// set the session ID if it exists
// start the session
// Redirect search engines with session id to the same url without session id to prevent indexing session id urls
// check for Cookie usage
// check the Agent
include (DIR_WS_MODULES.'set_session_and_cookie_parameters.php');

// user tracking
include (DIR_WS_INCLUDES.'tracking.php');

// verify the ssl_session_id if the feature is enabled
// verify the browser user agent if the feature is enabled
// verify the IP address if the feature is enabled
include (DIR_WS_MODULES.'verify_session.php');


// set the language
include (DIR_WS_MODULES.'set_language_sessions.php');

// language translations
require (DIR_WS_LANGUAGES.$_SESSION['language'].'/'.$_SESSION['language'].'.php');

// currency
include (DIR_WS_MODULES.'set_currency_session.php');

// write customers status in session
require (DIR_WS_INCLUDES.'write_customers_status.php');

// main class
require (DIR_WS_CLASSES.'main.php');
$main = new main();

// price class
require (DIR_WS_CLASSES.'xtcPrice.php');
$xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);

// create the shopping cart & fix the cart if necesary
if (!isset($_SESSION['cart']) || !is_object($_SESSION['cart'])) {
  $_SESSION['cart'] = new shoppingCart();
}

// PayPal Express
if (defined('PAYPAL_API_VERSION')) {
  require_once (DIR_WS_CLASSES . 'paypal_checkout.php');
  $o_paypal = new paypal_checkout();
}

// econda tracking
if (TRACKING_ECONDA_ACTIVE == 'true') {
  require(DIR_FS_EXTERNAL . 'econda/class.econda.php');
  require(DIR_FS_EXTERNAL . 'econda/emos.php');
  $econda = new econda();
}

require (DIR_WS_INCLUDES.FILENAME_CART_ACTIONS);

// who's online functions
xtc_update_whos_online();

// split-page-results
require (DIR_WS_CLASSES.'split_page_results.php');

// infobox
require (DIR_WS_CLASSES.'boxes.php');

// auto activate and expire banners
xtc_activate_banners();
xtc_expire_banners();

// auto expire special products
xtc_expire_specials();

// class product
require (DIR_WS_CLASSES.'product.php');

// set $actual_products_id,  $current_category_id, $ cPath, $_GET['manufacturers_id']
include (DIR_WS_MODULES.'set_ids_by_url_parameters.php');

// breadcrumb class and start the breadcrumb trail
require (DIR_WS_CLASSES.'breadcrumb.php');
$breadcrumb = new breadcrumb;
include (DIR_WS_MODULES.'create_breadcrumb.php');

// initialize the message stack for output messages
require (DIR_WS_CLASSES.'message_stack.php');
$messageStack = new messageStack;

// set which precautions should be checked
define('WARN_INSTALL_EXISTENCE', 'true');
define('WARN_CONFIG_WRITEABLE', 'true');
define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
define('WARN_SESSION_AUTO_START', 'true');
define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');

// set account_type
include (DIR_WS_MODULES.'set_account_type.php');

// modification for nre graduated system
unset ($_SESSION['actual_content']);
xtc_count_cart();

foreach(auto_require(DIR_FS_CATALOG.'includes/extra/application_top_end/','php') as $file) require ($file);

?>