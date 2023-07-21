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

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/functions/','php') as $file) require_once ($file);

// make a connection to the database... now
xtc_db_connect() or die('Unable to connect to database server!');

// load configuration
$configuration_query = xtc_db_query("SELECT configuration_key, configuration_value FROM ".TABLE_CONFIGURATION);
while ($configuration = xtc_db_fetch_array($configuration_query)) {
  if (function_exists('extra_configuration')) extra_configuration();
  defined($configuration['configuration_key']) OR define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/application_top_export/application_top_export_begin/','php') as $file) require ($file);

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/application_top_export/application_top_export_end/','php') as $file) require ($file);

//compatibility for modified eCommerce Shopsoftware 1.06 files
defined('DIR_WS_BASE') OR define('DIR_WS_BASE', '');
?>