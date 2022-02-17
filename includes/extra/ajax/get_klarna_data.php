<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once(DIR_FS_INC.'xtc_datetime_short.inc.php');

if (isset($_REQUEST['speed'])) {
  // auto include
  require_once (DIR_FS_INC.'auto_include.inc.php');

  require_once (DIR_FS_INC.'xtc_not_null.inc.php');
  require_once (DIR_FS_INC.'xtc_input_validation.inc.php');
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');
  require_once (DIR_FS_INC.'html_encoding.php');
  
  require_once (DIR_WS_INCLUDES.'database_tables.php');
}

function get_klarna_data() {
  require_once (DIR_WS_CLASSES.'order.php');

  xtc_db_connect() or die('Unable to connect to database server!');

  $configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION . '');
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    if (!defined($configuration['cfgKey'])) {
      define($configuration['cfgKey'], stripslashes($configuration['cfgValue']));
    }
  }
  
  if (!isset($_GET['sec'])
      || $_GET['sec'] != MODULE_PAYMENT_KLARNA_AJAX_SECRET
      )
  {
    return;
  }
  $order = new order((int)$_GET['oID']);
  
  ob_start();
  include(DIR_FS_EXTERNAL.'klarna/modules/orders_klarna_data.php');
  $output = ob_get_contents();
  ob_end_clean();  
    
  $output = encode_htmlentities($output);
  $output = base64_encode($output);

  return $output;
}
