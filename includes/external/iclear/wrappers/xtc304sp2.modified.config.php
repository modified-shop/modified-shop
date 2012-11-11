<?php
/*************************************************************************

  $Id$

  iclear payment system - because secure is simply secure
  http://www.iclear.de

  Copyright (c) 2001 - 2009 iclear

  Released under the GNU General Public License

************************************************************************

                    All rights reserved.

  This program is free software licensed under the GNU General Public License (GPL).

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  USA

*************************************************************************/

  if(!defined('IC_SEC')) {
    die('No external calls allowed!');
  }

  global $icCore;
  
  if(!defined('DIR_WS_CATALOG')) {
    $icSysPath = $icCore->getPath();
    require $icSysPath . 'includes/configure.php';
      
    define('PROJECT_VERSION', 'xt:Commerce v3.0.4 SP2');
    $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';
  
  if (version_compare(PHP_VERSION, '5.0.0', '>'))
    if (!isset($PHP_SELF)) $PHP_SELF = $HTTP_SERVER_VARS['PHP_SELF'];
  else
    if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
  
    // include the list of project filenames
    require (DIR_WS_INCLUDES.'filenames.php');
  
    // include the list of project database tables
    require (DIR_WS_INCLUDES.'database_tables.php');
  
    // SQL caching dir
    define('SQL_CACHEDIR', DIR_FS_CATALOG.'cache/');
  
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
    
  // make a connection to the database... now
    xtc_db_connect() or die('Unable to connect to database server!');
  
    $configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from '.TABLE_CONFIGURATION);
    while ($configuration = xtc_db_fetch_array($configuration_query)) {
      define($configuration['cfgKey'], $configuration['cfgValue']);
    }
    
// extra stuff 2 load 4 subsequent classes
    require_once (DIR_FS_INC.'xtc_not_null.inc.php');
    require_once (DIR_WS_CLASSES.'shopping_cart.php');
    require_once DIR_FS_INC . 'xtc_calculate_tax.inc.php';
    require_once DIR_FS_INC . 'xtc_add_tax.inc.php';
    require_once DIR_FS_INC . 'xtc_get_tax_rate.inc.php';
    require_once DIR_FS_INC . 'xtc_href_link.inc.php';
    require_once DIR_FS_INC . 'xtc_php_mail.inc.php';
    require_once DIR_FS_INC . 'xtc_redirect.inc.php';
    require_once DIR_FS_INC . 'xtc_round.inc.php';
    // DB iclear patch 090624 - intercept missing function xtc_image used by sofort?berweisung
    require_once DIR_FS_INC . 'xtc_image.inc.php';
    
    require_once DIR_WS_FUNCTIONS . 'sessions.php';
    require_once (DIR_WS_CLASSES.'xtcPrice.php');
    require_once (DIR_WS_CLASSES.'Smarty_2.6.26/Smarty.class.php');
    require_once (DIR_WS_CLASSES.'class.phpmailer.php');
    if (EMAIL_TRANSPORT == 'smtp')
      require_once (DIR_WS_CLASSES.'class.smtp.php');
      
  //20110222 CA added xtc_get_all_get_params bugfix
  if (file_exists(DIR_FS_INC.'xtc_get_all_get_params.inc.php'))
    require_once (DIR_FS_INC.'xtc_get_all_get_params.inc.php');
    
	//20111019 CA added object product
	if (file_exists(DIR_WS_CLASSES.'product.php'))
		require_once (DIR_WS_CLASSES.'product.php');
    
    // set the application parameters - taken from application_top.php#174++
    
    function xtDBquery($query) {
      if (DB_CACHE == 'true') {
    //      echo  'cached query: '.$query.'<br>';
        $result = xtc_db_queryCached($query);
      } else {
    //        echo '::'.$query .'<br>';
        $result = xtc_db_query($query);
    
      }
      return $result;
    }
    
    function CacheCheck() {
      if (USE_CACHE == 'false') return false;
      if (!isset($_COOKIE['MODsid'])) return false;
      return true;
    }
    
    require (DIR_WS_CLASSES.'main.php');
    global $main;
    $main = new main();
    
    
    
  }
  
  $lang =& $icCore->getLanguage();
  // define XT:C admin backoffice labels
  define('IC_LABEL', 'MODULE_PAYMENT_ICLEAR_');
  
  foreach( array('STATUS', 'ID', 'ZONE', 'SORT_ORDER', 'ORDER_STATUS_ID', 'ALLOWED') AS $key) {
    define(IC_LABEL . $key . '_TITLE', $lang->getParam($key . '_TITLE'));
    define(IC_LABEL . $key . '_DESC', $lang->getParam($key . '_DESC'));
  }
  if (!defined('MODULE_PAYMENT_ICLEAR_TEXT_TITLE')) {
    define('MODULE_PAYMENT_ICLEAR_TEXT_TITLE', $lang->getParam('MODULE_TITLE'));
  }
  
  define('IC_SESSION_NAME', 'MODsid');
// load wrapper
  require_once dirname(__FILE__) . '/' . basename(preg_replace('/.' . $icCore->cloneID() . '/', '', __FILE__), '.config.php') . '.php';
  
?>