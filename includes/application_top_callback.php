<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_top.php,v 1.273 2003/05/19); www.oscommerce.com
   (c) 2003	 nextcommerce (application_top.php,v 1.54 2003/08/25); www.nextcommerce.org
   (c) 2003 XT-Commerce

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Add A Quickie v1.0 Autor  Harald Ponce de Leon

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

//Run Mode
define('RUN_MODE_CALLBACK',true);

// start the timer for the page parse time log
defined('PAGE_PARSE_START_TIME') OR define('PAGE_PARSE_START_TIME', microtime(true));

// set the level of error reporting
@ini_set('display_errors', false);
error_reporting(0);

// Set the local configuration parameters - mainly for developers - if exists else the mainconfigure
if (file_exists(dirname(__FILE__).'/local/configure.php')) {
  include_once(dirname(__FILE__).'/local/configure.php');
} else {
  include_once(dirname(__FILE__).'/configure.php');
}

// minimum requirement
if (version_compare(PHP_VERSION, '8.0', '<')) {
  die('<h1>Minimum requirement PHP Version 8.0</h1>');
}

// default time zone
defined('DEFAULT_TIMEZONE') OR define('DEFAULT_TIMEZONE', 'Europe/Berlin');
date_default_timezone_set(DEFAULT_TIMEZONE);

// set request uri
$_SERVER['REQUEST_URI'] = ((isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '');

// new error handling
if (is_file(DIR_WS_INCLUDES.'error_reporting.php')) {
  require_once (DIR_WS_INCLUDES.'error_reporting.php');
}

// security inputfilter for GET/POST/COOKIE
require_once (DIR_FS_INC.'html_encoding.php');
require_once (DIR_WS_CLASSES.'class.inputfilter.php');
$InputFilter = new InputFilter();

$_GET = $InputFilter->process($_GET);
$_POST = $InputFilter->process($_POST);
$_REQUEST = $InputFilter->process($_REQUEST);
$_GET = $InputFilter->safeSQL($_GET);
$_POST = $InputFilter->safeSQL($_POST);
$_REQUEST = $InputFilter->safeSQL($_REQUEST);

// auto include
require_once (DIR_FS_INC . 'auto_include.inc.php');

// include the list of project filenames
require_once (DIR_WS_INCLUDES.'filenames.php');

// project version
define('PROJECT_VERSION', 'modified eCommerce Shopsoftware');

define('TAX_DECIMAL_PLACES', 0);

// set the type of request (secure or not)
if (file_exists(DIR_WS_INCLUDES.'request_type.php')) {
  include_once (DIR_WS_INCLUDES.'request_type.php');
} else {
  $request_type = 'NONSSL';
}

// Base/PHP_SELF/SSL-PROXY
require_once (DIR_FS_INC . 'set_php_self.inc.php');
$PHP_SELF = set_php_self();

// list of project database tables
require_once (DIR_WS_INCLUDES.'database_tables.php');

// Store DB-Querys in a Log File
define('STORE_DB_TRANSACTIONS', 'false');

// Database
require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
require_once (DIR_FS_INC.'db_functions.inc.php');

// html basics
require_once (DIR_FS_INC.'xtc_href_link.inc.php');
require_once (DIR_FS_INC.'xtc_draw_separator.inc.php');
require_once (DIR_FS_INC.'xtc_php_mail.inc.php');

require_once (DIR_FS_INC.'xtc_product_link.inc.php');
require_once (DIR_FS_INC.'xtc_category_link.inc.php');
require_once (DIR_FS_INC.'xtc_manufacturer_link.inc.php');
require_once (DIR_FS_INC.'xtc_content_link.inc.php');

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

require_once (DIR_FS_INC.'xtc_get_parent_categories.inc.php');
require_once (DIR_FS_INC.'xtc_redirect.inc.php');
require_once (DIR_FS_INC.'xtc_get_uprid.inc.php');
require_once (DIR_FS_INC.'xtc_get_all_get_params.inc.php');
require_once (DIR_FS_INC.'xtc_has_product_attributes.inc.php');
require_once (DIR_FS_INC.'xtc_image.inc.php');
require_once (DIR_FS_INC.'xtc_currency_exists.inc.php');
require_once (DIR_FS_INC.'xtc_remove_non_numeric.inc.php');
require_once (DIR_FS_INC.'xtc_get_ip_address.inc.php');
require_once (DIR_FS_INC.'xtc_setcookie.inc.php');
require_once (DIR_FS_INC.'xtc_check_agent.inc.php');
require_once (DIR_FS_INC.'xtc_get_qty.inc.php');
require_once (DIR_FS_INC.'create_coupon_code.inc.php');
require_once (DIR_FS_INC.'xtc_gv_account_update.inc.php');
require_once (DIR_FS_INC.'xtc_get_tax_rate_from_desc.inc.php');
require_once (DIR_FS_INC.'xtc_get_tax_rate.inc.php');
require_once (DIR_FS_INC.'xtc_input_validation.inc.php');

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/functions/','php') as $file) require_once ($file);

// make a connection to the database... now
xtc_db_connect() or die('Unable to connect to database server!');

// load configuration
$configuration_query = xtc_db_query("SELECT configuration_key, configuration_value FROM ".TABLE_CONFIGURATION);
while ($configuration = xtc_db_fetch_array($configuration_query)) {
  if (function_exists('extra_configuration')) extra_configuration();
  defined($configuration['configuration_key']) OR define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/application_top_callback/application_top_callback_begin/','php') as $file) require ($file);

// set the top level domains
$http_domain_arr = xtc_get_top_level_domain(HTTP_SERVER);
$https_domain_arr = xtc_get_top_level_domain(HTTPS_SERVER);
$http_domain = $http_domain_arr['domain'];
$https_domain = $https_domain_arr['domain'];
$current_domain = (($request_type == 'NONSSL') ? $http_domain : $https_domain);

// set the top level domains to delete
$current_domain_delete = (($request_type == 'NONSSL') ? $http_domain_arr['delete'] : $https_domain_arr['delete']);

// include shopping cart class
require_once (DIR_WS_CLASSES.'shopping_cart.php');

// define how the session functions will be used
require_once (DIR_WS_FUNCTIONS.'sessions.php');

// set the session name and save path
// set the session cookie parameters
// set the session ID if it exists
// start the session
// Redirect search engines with session id to the same url without session id to prevent indexing session id urls
// check for Cookie usage
// check the Agent
include_once (DIR_WS_MODULES.'set_session_and_cookie_parameters.php');

// verify the ssl_session_id if the feature is enabled
// verify the browser user agent if the feature is enabled
// verify the IP address if the feature is enabled
include_once (DIR_WS_MODULES.'verify_session.php');

// user tracking
include_once (DIR_WS_INCLUDES.'tracking.php');

// set the language
include_once (DIR_WS_MODULES.'set_language_sessions.php');

// currency
include_once (DIR_WS_MODULES.'set_currency_session.php');

// write customers status in session
require_once (DIR_WS_INCLUDES.'write_customers_status.php');

// content, product, category - sql group_check/fsk_lock
require_once (DIR_WS_INCLUDES.'define_conditions.php');

// add_select
require_once (DIR_WS_INCLUDES.'define_add_select.php');

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/application_top_callback/application_top_callback_end/','php') as $file) require ($file);

//compatibility for modified eCommerce Shopsoftware 1.06 files
defined('DIR_WS_BASE') OR define('DIR_WS_BASE', '');
