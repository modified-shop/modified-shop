<?php
/******** SHOPGATE **********/
if(defined('MODULE_PAYMENT_SHOPGATE_STATUS') && MODULE_PAYMENT_SHOPGATE_STATUS=='True' && strpos($_SESSION['customers_status']['customers_status_payment_unallowed'], 'shopgate') === false){
  include_once (DIR_FS_CATALOG.'includes/external/shopgate/base/includes/header.php');
}
/******** SHOPGATE **********/
?>
