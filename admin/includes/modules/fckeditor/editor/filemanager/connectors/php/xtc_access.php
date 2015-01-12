<?php
/*-----------------------------------------------/
$Id: xtc_access.php 3336 2012-07-27 11:38:05Z web28 $

FCKEditor Filemanger secure_access v.1.00 (c)2014 by web28 - www.rpa-com.de
/-----------------------------------------------*/

// set the level of error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT); //exlude E_STRICT on PHP 5.4

define('_IS_FILEMANAGER',true);

$current_cwd = dirname(__FILE__);

// AdminDir Shop
$rootDir = '../../../../../../../';
chdir($rootDir);
require('includes/application_top.php'); // AdminDir Shop
chdir($current_cwd);

$Config['Enabled'] = false;
if (isset($_SESSION) && $_SESSION['customers_status']['customers_status_id'] == '0') {
  $access_permission_query = xtc_db_query("SELECT * FROM ".TABLE_ADMIN_ACCESS." WHERE customers_id = '".$_SESSION['customer_id']."'");
  $access_permission = xtc_db_fetch_array($access_permission_query);
  if (!isset($access_permission['fck_wrapper']) || ($access_permission['fck_wrapper'] != '1')) {
      die('Direct Access to this location is not allowed.');
  }
  $Config['Enabled'] = true;
}
?>