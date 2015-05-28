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

// xss secure
if (is_file('includes/xss_secure.php')) {
  include ('includes/xss_secure.php');
}

// start the timer for the page parse time log
define('PAGE_PARSE_START_TIME', microtime(true));

// Set the local configuration parameters - mainly for developers - if exists else the mainconfigure
if (file_exists('../../includes/local/configure.php')) {
	include dirname ( __FILE__ ) . '/../../includes/local/configure.php';
} else {
	include dirname ( __FILE__ ) . '/../../includes/configure.php';
}

// default time zone
if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
  date_default_timezone_set('Europe/Berlin');
}

// set the level of error reporting
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

// new error handling
if (is_file(DIR_WS_INCLUDES.'error_reporting.php')) {
  require_once (DIR_WS_INCLUDES.'error_reporting.php');
}

// turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
if (version_compare(PHP_VERSION, 5.4, '<') && @ini_get('magic_quotes_sybase') != 0) @ini_set('magic_quotes_sybase', 0);

// include the list of project filenames
require (DIR_WS_INCLUDES.'filenames.php');

// Debug-Log-Class - thx to franky
include_once(DIR_WS_CLASSES.'class.debug.php');
$log = new debug;

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
require_once (DIR_FS_INC.'xtc_check_stock.inc.php');
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
require_once (DIR_FS_INC.'xtc_backup_restore_configuration.php');
require_once (DIR_FS_INC.'xtc_hide_session_id.inc.php');
require_once (DIR_FS_INC.'get_messages.inc.php');

// make a connection to the database... now
xtc_db_connect() or die('Unable to connect to database server!');

// load configuration
$configuration_query = xtc_db_query('SELECT configuration_key, configuration_value FROM '.TABLE_CONFIGURATION);
while ($configuration = xtc_db_fetch_array($configuration_query)) {
  define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
}

//compatibility for modified eCommerce Shopsoftware 1.06 files
if (!defined('DIR_WS_BASE')) {
  define('DIR_WS_BASE', '');
}

// Set the length of the redeem code, the longer the more secure
// Kommt eigentlich schon aus der Table configuration
if(!defined('SECURITY_CODE_LENGTH')) {
  define('SECURITY_CODE_LENGTH', '10');
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
$http_domain_arr = xtc_get_top_level_domain(HTTP_SERVER);
$https_domain_arr = xtc_get_top_level_domain(HTTPS_SERVER);
$http_domain = $http_domain_arr['new'];
$https_domain = $https_domain_arr['new'];
$current_domain = (($request_type == 'NONSSL') ? $http_domain : $https_domain);

// set the top level domains - old
$http_domain_old = $http_domain_arr['old'];
$https_domain_old = $https_domain_arr['old'];
$current_domain_old = (($request_type == 'NONSSL') ? $http_domain_old : $https_domain_old);

// some code to solve compatibility issues
require (DIR_WS_FUNCTIONS.'compatibility.php');

?>