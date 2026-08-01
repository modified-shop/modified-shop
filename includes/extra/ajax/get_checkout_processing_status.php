<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset($_REQUEST['speed'])) {
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');
  require_once (DIR_WS_INCLUDES.'database_tables.php');
}

require_once (DIR_WS_CLASSES.'checkout.php');

function get_checkout_processing_status()
{
  $response = array('status' => 'unknown');

  if (isset($_REQUEST['speed'])) {
    xtc_db_connect() or die('Unable to connect to database server!');
  }

  $processing_key = isset($_POST['checkout_key']) ? $_POST['checkout_key'] : '';
  $status_token = isset($_POST['status_token']) ? $_POST['status_token'] : '';
  $processing = checkout::find_status($processing_key, $status_token);
  if (is_array($processing)) {
    $response['status'] = $processing['processing_status'];
  }

  return $response;
}
