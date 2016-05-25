<?php
  if(stripos($order->info['payment_method'], 'billpay') !== false) {
    require_once(DIR_FS_EXTERNAL . 'billpay/utils/billpay_mail.php'); #BILLPAY payment module
  }
?>