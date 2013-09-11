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
   (c) 2003   nextcommerce (application_top.php,v 1.54 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce (application_top_callback.php 149 2007-01-24); www.xt-commerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Add A Quickie v1.0 Autor  Harald Ponce de Leon

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime(true));

  // set the level of error reporting
  //error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT); //exlude E_STRICT on PHP 5.4
  error_reporting(0);
  //  error_reporting(E_ALL);
  
  /*
   * turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
   */
  if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
  if (version_compare(PHP_VERSION, 5.4, '<') && @ini_get('magic_quotes_sybase') != 0) @ini_set('magic_quotes_sybase', 0);

  // Set the local configuration parameters - mainly for developers - if exists else the mainconfigure
  if (file_exists('../../includes/local/configure.php')) {
    include('../../includes/local/configure.php');
  } else {
    include('../../includes/configure.php');
  }

  // BOF - Tomcraft - 2009-11-08 - FIX for PHP5.3 date_default_timezone_set
  if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
    date_default_timezone_set('Europe/Berlin');
  }
  // EOF - Tomcraft - 2009-11-08 - FIX for PHP5.3 date_default_timezone_set

  // define the project version
  define('PROJECT_VERSION', 'modified eCommerce Shopsoftware');

  // BOF - Tomcraft - 2009-11-09 - Added missing definition for TAX_DECIMAL_PLACES
  define('TAX_DECIMAL_PLACES', 0);
  // EOF - Tomcraft - 2009-11-09 - Added missing definition for TAX_DECIMAL_PLACES

  // set the type of request (secure or not)
  //BOF - web28 - 2010-09-03 - added native support for SSL-proxy connections
  //$request_type = (getenv('HTTPS') == '1' || getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';
  if (file_exists('includes/request_type.php')) {
    include ('includes/request_type.php');
  } else {
    $request_type = 'NONSSL';
  }
  //EOF - web28 - 2010-09-03 - added native support for SSL-proxy connections

  // set php_self in the local scope
  //BOF - GTB - 2010-11-26 - Security Fix - PHP_SELF
  require_once(DIR_FS_INC . 'set_php_self.inc.php'); 
  $PHP_SELF = set_php_self();
  //$PHP_SELF = $_SERVER['PHP_SELF'];
  //EOF - GTB - 2010-11-26 - Security Fix - PHP_SELF

  //BOF - GTB/web28 - 2010-09-15 - Security Fix - Base
  $ssl_proxy = '';
  if ($request_type == 'SSL' && ENABLE_SSL == true && defined('USE_SSL_PROXY') && USE_SSL_PROXY == true) {
    $ssl_proxy = '/' . $_SERVER['HTTP_HOST'];
  }
  define('DIR_WS_BASE', $ssl_proxy . preg_replace('/\\' . DIRECTORY_SEPARATOR . '\/|\/\//', '/', dirname($PHP_SELF) . '/'));
  //EOF - GTB/web28 - 2010-09-15 - Security Fix - Base

  // include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

  // include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

  // include used functions
  require_once (DIR_FS_INC . 'xtc_db_connect.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_close.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_error.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_perform.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_query.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_fetch_array.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_num_rows.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_data_seek.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_insert_id.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_free_result.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_fetch_fields.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_output.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_input.inc.php');
  require_once (DIR_FS_INC . 'xtc_db_prepare_input.inc.php');

  require_once (DIR_FS_INC.'xtc_href_link.inc.php');
  require_once (DIR_FS_INC.'xtc_draw_separator.inc.php');
  require_once (DIR_FS_INC.'xtc_php_mail.inc.php');

  require_once (DIR_FS_INC.'xtc_product_link.inc.php');
  require_once (DIR_FS_INC.'xtc_category_link.inc.php');
  //require_once (DIR_FS_INC.'xtc_manufacturer_link.inc.php');

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

  //require_once (DIR_FS_INC.'xtc_get_category_path.inc.php');

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
  require_once (DIR_FS_INC.'html_encoding.php'); //new function for PHP5.4
  //require_once (DIR_FS_INC.'xtc_js_lang.php');

  // modification for new graduated system

  // make a connection to the database... now
  xtc_db_connect() or die('Unable to connect to database server!');

  // set the application parameters
  $configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], stripslashes($configuration['cfgValue'])); //Web28 - 2012-08-09 - fix slashes
  }

  // if gzip_compression is enabled, start to buffer the output
  if ( (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded = extension_loaded('zlib')) && (PHP_VERSION >= '4') ) {
    if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
      ob_start('ob_gzhandler');
    } else {
      ini_set('zlib.output_compression_level', GZIP_LEVEL);
    }
  }

// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul
  require (DIR_WS_FUNCTIONS.'sessions.php');
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul

  // Smarty Template Engine
  //require(DIR_FS_EXTERNAL.'smarty/smarty_2/Smarty.class.php'); // Tomcraft - 2011-01-31 - Not needed in apllication_top_callback.php
?>
