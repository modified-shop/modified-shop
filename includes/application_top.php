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

/**
 * set the level of error reporting
 */
if (file_exists(DIR_FS_CATALOG.'export/_error_reporting.all') || file_exists(DIR_FS_CATALOG.'export/_error_reporting.shop')) {
  @ini_set('display_errors', true);
  error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT); //exlude E_STRICT on PHP 5.4
} elseif (file_exists(DIR_FS_CATALOG.'export/_error_reporting.dev')) {
  @ini_set('display_errors', true);
  error_reporting(-1); // Development value
} else {
  error_reporting(0);
}

/*
 * turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
if (version_compare(PHP_VERSION, 5.4, '<') && @ini_get('magic_quotes_sybase') != 0) @ini_set('magic_quotes_sybase', 0);

// include the list of project filenames
require (DIR_WS_INCLUDES.'filenames.php');
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

//compatibility for modified eCommerce Shopsoftware 1.06 files
define('DIR_WS_BASE', '');

// list of project database tables
require (DIR_WS_INCLUDES.'database_tables.php');

// SQL caching dir
define('SQL_CACHEDIR', DIR_FS_CATALOG.'cache/');

// graduated prices model or products assigned ?
define('GRADUATED_ASSIGN', 'true');

// Database
require_once (DIR_FS_INC.'xtc_db_connect.inc.php');
require_once (DIR_FS_INC.'xtc_db_close.inc.php');
require_once (DIR_FS_INC.'xtc_db_error.inc.php');
require_once (DIR_FS_INC.'xtc_db_perform.inc.php');
require_once (DIR_FS_INC.'xtc_db_query.inc.php');
require_once (DIR_FS_INC.'xtc_db_queryCached.inc.php');
require_once (DIR_FS_INC.'xtc_db_fetch_array.inc.php');
require_once (DIR_FS_INC.'xtc_db_num_rows.inc.php');
require_once (DIR_FS_INC.'xtc_db_data_seek.inc.php');
require_once (DIR_FS_INC.'xtc_db_insert_id.inc.php');
require_once (DIR_FS_INC.'xtc_db_free_result.inc.php');
require_once (DIR_FS_INC.'xtc_db_fetch_fields.inc.php');
require_once (DIR_FS_INC.'xtc_db_output.inc.php');
require_once (DIR_FS_INC.'xtc_db_input.inc.php');
require_once (DIR_FS_INC.'xtc_db_prepare_input.inc.php');

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
// make a connection to the database... now
xtc_db_connect() or die('Unable to connect to database server!');

// load configuration
$configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from '.TABLE_CONFIGURATION);
while ($configuration = xtc_db_fetch_array($configuration_query)) {
  define($configuration['cfgKey'], stripslashes($configuration['cfgValue'])); //Web28 - 2012-08-09 - fix slashes
}
// Set the length of the redeem code, the longer the more secure
// Kommt eigentlich schon aus der Table configuration
if(SECURITY_CODE_LENGTH=='')
  define('SECURITY_CODE_LENGTH', '10');

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
session_name('MODsid');
if (STORE_SESSIONS != 'mysql') session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
if (function_exists('session_set_cookie_params')) {
  session_set_cookie_params(0, '/', (xtc_not_null($current_domain) ? '.'.$current_domain : ''));
} elseif (function_exists('ini_set')) {
  ini_set('session.cookie_lifetime', '0');
  ini_set('session.cookie_path', '/');
  ini_set('session.cookie_domain', (xtc_not_null($current_domain) ? '.'.$current_domain : ''));
}
// set the session ID if it exists
if (isset ($_POST[session_name()])) {
  session_id($_POST[session_name()]);
}
elseif (($request_type == 'SSL') && isset ($_GET[session_name()])) {
  session_id($_GET[session_name()]);
}

// start the session
$session_started = false;
if (SESSION_FORCE_COOKIE_USE == 'True') {
  xtc_setcookie('cookie_test', 'please_accept_for_session', time() + 60 * 60 * 24 * 30, '/', $current_domain);
  if (isset ($_COOKIE['cookie_test'])) {
    session_start();
    $session_started = true;
  }
} else {
  session_start();  
  $session_started = true;
}
include (DIR_WS_INCLUDES.'tracking.php');
// check the Agent
$truncate_session_id = false;
if (CHECK_CLIENT_AGENT && xtc_check_agent() == 1) {
  $truncate_session_id = true;
}

// verify the ssl_session_id if the feature is enabled
if (($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true)) {
  $ssl_session_id = getenv('SSL_SESSION_ID');
  if (!isset($_SESSION['SSL_SESSION_ID'])) {
    $_SESSION['SESSION_SSL_ID'] = $ssl_session_id;
  }
  if ($_SESSION['SESSION_SSL_ID'] != $ssl_session_id) {
    session_destroy();
    xtc_redirect(xtc_href_link(FILENAME_SSL_CHECK));
  }
}

// verify the browser user agent if the feature is enabled
if (SESSION_CHECK_USER_AGENT == 'True') {
  $http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
  $http_user_agent2 = strtolower(getenv("HTTP_USER_AGENT"));
  $http_user_agent = ($http_user_agent == $http_user_agent2) ? $http_user_agent : $http_user_agent.';'.$http_user_agent2;
  if (!isset ($_SESSION['SESSION_USER_AGENT'])) {
    $_SESSION['SESSION_USER_AGENT'] = $http_user_agent;
  } elseif ($_SESSION['SESSION_USER_AGENT'] != $http_user_agent) {
    session_destroy();
    xtc_redirect(xtc_href_link(FILENAME_LOGIN));
  }
}

// verify the IP address if the feature is enabled
if (SESSION_CHECK_IP_ADDRESS == 'True') {
  $ip_address = xtc_get_ip_address();
  if (!isset($_SESSION['SESSION_IP_ADDRESS'])) {
    $_SESSION['SESSION_IP_ADDRESS'] = $ip_address;
  } elseif ($_SESSION['SESSION_IP_ADDRESS'] != $ip_address) {
    session_destroy();
    xtc_redirect(xtc_href_link(FILENAME_LOGIN));
  }
}

// Redirect search engines with session id to the same url without session id to prevent indexing session id urls
if ( $truncate_session_id == true ) {
  if (preg_match('/' . xtc_session_name() . '/i', $_SERVER['REQUEST_URI']) ){
    $location = xtc_href_link(basename($_SERVER['SCRIPT_NAME']), xtc_get_all_get_params(array(xtc_session_name())), 'NONSSL', false);
    header("HTTP/1.0 301 Moved Permanently");
    header("Location: $location");
  }
}

if (!(preg_match('/^[a-z0-9]{26}$/i', session_id()) || preg_match('/^[a-z0-9]{32}$/i', session_id()))) {
  session_regenerate_id(true); // Thanks to HHGAG ;-)
}

// set the language
include (DIR_WS_MODULES.'set_language_sessions.php');

// language translations
require (DIR_WS_LANGUAGES.$_SESSION['language'].'/'.$_SESSION['language'].'.php');

// currency
if (!isset ($_SESSION['currency']) || isset ($_GET['currency']) || ((USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $_SESSION['currency']))) {
  if (isset ($_GET['currency'])) {
    $_GET['currency'] = xtc_input_validation($_GET['currency'], 'char', '');
    if (!$_SESSION['currency'] = xtc_currency_exists($_GET['currency']))
      $_SESSION['currency'] = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
  } else {
    $_SESSION['currency'] = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
  }
}
if (isset ($_SESSION['currency']) && $_SESSION['currency'] == '') {
  $_SESSION['currency'] = DEFAULT_CURRENCY;
}

// write customers status in session
require (DIR_WS_INCLUDES.'write_customers_status.php');

//BOC web28 2011-11-30 - Versandkosten im Warenkorb
if (strpos($PHP_SELF, FILENAME_SHOPPING_CART) === false) {
  unset($_SESSION['country']);
}
//EOC web28 2011-11-30 - Versandkosten im Warenkorb

// main class
require (DIR_WS_CLASSES.'main.php');
$main = new main();

// price class
require (DIR_WS_CLASSES.'xtcPrice.php');
$xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);

// econda tracking
if (TRACKING_ECONDA_ACTIVE=='true') {
  require(DIR_WS_INCLUDES . 'econda/class.econda304SP2.php');
  $econda = new econda();
}

// PayPal Express
if (defined('PAYPAL_API_VERSION')) {
  require_once (DIR_WS_CLASSES . 'paypal_checkout.php');
  $o_paypal = new paypal_checkout();
}

// create the shopping cart & fix the cart if necesary
if (!isset($_SESSION['cart']) || !is_object($_SESSION['cart'])) {
  $_SESSION['cart'] = new shoppingCart();
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

// Smarty Template Engine 
require (DIR_WS_CLASSES.'Smarty_2.6.27/Smarty.class.php');

if (isset ($_SESSION['customer_id'])) {
$account_type_query = xtc_db_query("-- /includes/application_top.php
                                      SELECT account_type,
                                             customers_default_address_id
                                        FROM ".TABLE_CUSTOMERS."
                                       WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
  $account_type = xtc_db_fetch_array($account_type_query);

  // check if zone id is unset bug
  if (!isset ($_SESSION['customer_country_id'])) {
    $zone_query = xtc_db_query("-- /includes/application_top.php
                            SELECT entry_country_id
                              FROM ".TABLE_ADDRESS_BOOK."
                             WHERE customers_id='".(int) $_SESSION['customer_id']."'
                               AND address_book_id='".$account_type['customers_default_address_id']."'");

    $zone = xtc_db_fetch_array($zone_query);
    $_SESSION['customer_country_id'] = $zone['entry_country_id'];
  }
  $_SESSION['account_type'] = $account_type['account_type'];
} else {
  $_SESSION['account_type'] = '0';
}

// modification for nre graduated system
unset ($_SESSION['actual_content']);

// econda tracking
if (TRACKING_ECONDA_ACTIVE == 'true') {
  require(DIR_WS_INCLUDES . 'econda/emos.php');
}

// BOF - Tomcraft - 2011-06-17 - Added janolaw AGB hosting service
require_once(DIR_FS_CATALOG.'includes/janolaw/janolaw.php');
$coo_janolaw = new janolaw();
if($coo_janolaw->get_status() == true) {
  $coo_janolaw->get_page_content('agb', true, true, 'checkout-agb');
  $coo_janolaw->get_page_content('datenschutzerklaerung', true, true, 'checkout-datenschutzerklaerung');
  $coo_janolaw->get_page_content('impressum', true, true, 'checkout-impressum');
  $coo_janolaw->get_page_content('widerrufsbelehrung', true, true, 'checkout-widerrufsbelehrung');
}
// EOF - Tomcraft - 2011-06-17 - Added janolaw AGB hosting service

xtc_count_cart();
?>