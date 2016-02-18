<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  ## afterbuy
  if (isset($_GET['subaction']) && $_GET['subaction'] == 'afterbuy_send') {
    require_once (DIR_FS_CATALOG.'includes/classes/afterbuy.php');
    $aBUY = new xtc_afterbuy_functions($oID);
    if ($aBUY->order_send()) {
      $aBUY->process_order();
    }
  }
?>