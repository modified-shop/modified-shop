<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  ## Payone
  if (isset($_GET['subaction']) 
      && $_GET['subaction'] == 'payoneaction'
      ) 
  {
    include (DIR_FS_EXTERNAL.'payone/modules/orders_payone_action.php');
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'subaction')).'action=edit'));
  }
?>