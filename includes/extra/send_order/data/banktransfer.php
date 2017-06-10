<?php   
  // add SEPA info
  if ($order->info['payment_method'] == 'banktransfer') {
    $oID = $order->info['order_id'];
    if(isset($send_by_admin)) {
      require (DIR_FS_CATALOG_MODULES.'payment/banktransfer.php');
      include(DIR_FS_LANGUAGES.$order->info['language'].'/modules/payment/banktransfer.php');
      $payment_modules = new banktransfer();
    }
    $rec = $payment_modules->info();
    // SEPA info required?
    if (!empty($rec['banktransfer_iban'])) {
      require_once (DIR_FS_INC.'xtc_date_short.inc.php');
      $smarty->assign('PAYMENT_BANKTRANSFER_CREDITOR_ID', MODULE_PAYMENT_BANKTRANSFER_CI);
      // set due date based on date_purchased and due_delay
      $due_date = date('Y-m-d', strtotime($order->info['date_purchased'] . ' + ' . MODULE_PAYMENT_BANKTRANSFER_DUE_DELAY . ' days'));
      $smarty->assign('PAYMENT_BANKTRANSFER_DUE_DATE',  xtc_date_short($due_date));
      $total = $xtPrice->xtcFormat($order_total['total'], true);
      $smarty->assign('PAYMENT_BANKTRANSFER_TOTAL', $total);
      $smarty->assign('PAYMENT_BANKTRANSFER_MANDATE_REFERENCE', MODULE_PAYMENT_BANKTRANSFER_REFERENCE_PREFIX . $oID);
      $smarty->assign('PAYMENT_BANKTRANSFER_IBAN', substr($rec['banktransfer_iban'], 0, 8) . str_repeat('*', (strlen($rec['banktransfer_iban']) - 10)) . substr($rec['banktransfer_iban'], -2));
      $smarty->assign('PAYMENT_BANKTRANSFER_BANKNAME', $rec['banktransfer_bankname']);
      
      $smarty->caching = 0;
      $sepa_info = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/sepa_info.html');
    
      $smarty->assign('PAYMENT_INFO_HTML', $sepa_info);
      $smarty->assign('PAYMENT_INFO_TXT', str_replace("<br />", "\n", $sepa_info));
    
      // separate pre-notification necessary?
      if ($rec['banktransfer_owner_email'] != $order->customer['email_address']) {
        $banktransfer_owner_email = $rec['banktransfer_owner_email'];
        $sepa_html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/sepa_mail.html');
        $sepa_txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/sepa_mail.txt');
      
        // no pre-notification in order mail
        $smarty->clear_assign('PAYMENT_INFO_HTML');
        $smarty->clear_assign('PAYMENT_INFO_TXT');
      }
    }
  }
?>