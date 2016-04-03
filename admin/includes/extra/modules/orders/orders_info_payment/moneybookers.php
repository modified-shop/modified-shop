<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  ## Moneybookers
  if ($order->info['payment_method'] == 'amoneybookers') {
    if (file_exists(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/'.$order->info['payment_method'].'.php')) {
      include(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/'.$order->info['payment_method'].'.php');
      include(DIR_FS_CATALOG.'lang/'.$order->info['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
      $class = $order->info['payment_method'];
      $payment = new $class();
      $payment->admin_order($oID);
    }
  }
?>