<?php

/* -----------------------------------------------------------------------------------------
   $Id: application_top.php 1323 2005-10-27 17:58:08Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_top.php,v 1.273 2003/05/19); www.oscommerce.com
   (c) 2003	 nextcommerce (application_top.php,v 1.54 2003/08/25); www.nextcommerce.org

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Add A Quickie v1.0 Autor  Harald Ponce de Leon

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
// start the timer for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());

// set the level of error reporting
error_reporting(E_ALL & ~E_NOTICE);
//  error_reporting(E_ALL);

// Set the local configuration parameters - mainly for developers - if exists else the mainconfigure
if (file_exists('../../includes/local/configure.php')) {
	include dirname ( __FILE__ ) . '/../../includes/local/configure.php';
} else {
	include dirname ( __FILE__ ) . '/../../includes/configure.php';
}

$php4_3_10 = (0 == version_compare(phpversion(), "4.3.10"));
define('PHP4_3_10', $php4_3_10);
// define the project version
define('PROJECT_VERSION', 'xt:Commerce v3.0.4 SP2.1');

// set the type of request (secure or not)
$request_type = (getenv('HTTPS') == '1' || getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
$PHP_SELF = $_SERVER['PHP_SELF'];

// include the list of project filenames
require (DIR_WS_INCLUDES.'filenames.php');

// include the list of project database tables
require (DIR_WS_INCLUDES.'database_tables.php');

// SQL caching dir
define('SQL_CACHEDIR', DIR_FS_CATALOG.'cache/');

// Below are some defines which affect the way the discount coupon/gift voucher system work
// Be careful when editing them.
//
// Set the length of the redeem code, the longer the more secure
define('SECURITY_CODE_LENGTH', '10');
//
// The settings below determine whether a new customer receives an incentive when they first signup
//
// Set the amount of a Gift Voucher that the new signup will receive, set to 0 for none
//  define('NEW_SIGNUP_GIFT_VOUCHER_AMOUNT', '10');  // placed in the admin configuration mystore
//
// Set the coupon ID that will be sent by email to a new signup, if no id is set then no email :)
//  define('NEW_SIGNUP_DISCOUNT_COUPON', '3'); // placed in the admin configuration mystore

// Store DB-Querys in a Log File
define('STORE_DB_TRANSACTIONS', 'false');

// graduated prices model or products assigned ?
define('GRADUATED_ASSIGN', 'true');

// include used functions

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
require_once (DIR_FS_INC.'xtc_get_top_level_domain.inc.php');

// html basics
require_once (DIR_FS_INC.'xtc_href_link.inc.php');
require_once (DIR_FS_INC.'xtc_draw_separator.inc.php');
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

// make a connection to the database... now
xtc_db_connect() or die('Unable to connect to database server!');

$configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from '.TABLE_CONFIGURATION);
while ($configuration = xtc_db_fetch_array($configuration_query)) {
	define($configuration['cfgKey'], $configuration['cfgValue']);
}

require_once (DIR_WS_CLASSES.'class.phpmailer.php');
if (EMAIL_TRANSPORT == 'smtp')
	require_once (DIR_WS_CLASSES.'class.smtp.php');
require_once (DIR_FS_INC.'xtc_Security.inc.php');

// set the application parameters

function xtDBquery($query) {
	if (DB_CACHE == 'true') {
//			echo  'cached query: '.$query.'<br>';
		$result = xtc_db_queryCached($query);
	} else {
//				echo '::'.$query .'<br>';
		$result = xtc_db_query($query);

	}
	return $result;
}

function CacheCheck() {
	if (USE_CACHE == 'false') return false;
	if (!isset($_COOKIE['XTCsid'])) return false;
	return true;
}

// if gzip_compression is enabled, start to buffer the output
if ((GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded = extension_loaded('zlib')) && (PHP_VERSION >= '4')) {
	if (($ini_zlib_output_compression = (int) ini_get('zlib.output_compression')) < 1) {
		ob_start('ob_gzhandler');
	} else {
		ini_set('zlib.output_compression_level', GZIP_LEVEL);
	}
}

// set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
	if (strlen(getenv('PATH_INFO')) > 1) {
		$GET_array = array ();
		$PHP_SELF = str_replace(getenv('PATH_INFO'), '', $PHP_SELF);
		$vars = explode('/', substr(getenv('PATH_INFO'), 1));
		for ($i = 0, $n = sizeof($vars); $i < $n; $i ++) {
			if (strpos($vars[$i], '[]')) {
				$GET_array[substr($vars[$i], 0, -2)][] = $vars[$i +1];
			} else {
				$_GET[$vars[$i]] = htmlspecialchars($vars[$i +1]);
			}
			$i ++;
		}

		if (sizeof($GET_array) > 0) {
			while (list ($key, $value) = each($GET_array)) {
				$_GET[$key] = htmlspecialchars($value);
			}
		}
	}
}
// check GET/POST/COOKIE VARS
require (DIR_WS_CLASSES.'class.inputfilter.php');

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



?>