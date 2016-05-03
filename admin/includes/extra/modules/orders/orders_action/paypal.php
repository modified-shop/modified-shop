<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  ## Paypal
  if (isset($_GET['subaction']) 
      && $_GET['subaction'] == 'paypalaction'
      ) 
  {
    include (DIR_FS_EXTERNAL.'paypal/modules/orders_paypal_action.php');
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'subaction')).'action=edit'));
  }
?>