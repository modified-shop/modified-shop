<?php   
  // add SEPA info
  if ($order->info['payment_method'] == 'banktransfer') {
    if (isset($send_by_admin)) {
      require (DIR_FS_CATALOG_MODULES.'payment/banktransfer.php');
      include(DIR_FS_LANGUAGES.$order->info['language'].'/modules/payment/banktransfer.php');
      $payment_modules = new banktransfer();
    }
    
    // banktransfer info
    $banktransfer_data = $payment_modules->info();

    if (!empty($banktransfer_data['banktransfer_iban'])) {
      require_once (DIR_FS_INC.'xtc_date_short.inc.php');
      
      $smarty->caching = 0;
      $smarty->assign('language', $order->info['language']);
      $smarty->assign('PAYMENT_BANKTRANSFER_CREDITOR_ID', MODULE_PAYMENT_BANKTRANSFER_CI);
      $smarty->assign('PAYMENT_BANKTRANSFER_DUE_DATE',  xtc_date_short(date('Y-m-d', strtotime($order->info['date_purchased'] . ' + ' . MODULE_PAYMENT_BANKTRANSFER_DUE_DELAY . ' days'))));     
      $smarty->assign('PAYMENT_BANKTRANSFER_TOTAL', $xtPrice->xtcFormat($order_total['total'], true));
      $smarty->assign('PAYMENT_BANKTRANSFER_MANDATE_REFERENCE', MODULE_PAYMENT_BANKTRANSFER_REFERENCE_PREFIX . $insert_id);
      $smarty->assign('PAYMENT_BANKTRANSFER_IBAN', substr($banktransfer_data['banktransfer_iban'], 0, 8) . str_repeat('*', (strlen($banktransfer_data['banktransfer_iban']) - 10)) . substr($banktransfer_data['banktransfer_iban'], -2));
      $smarty->assign('PAYMENT_BANKTRANSFER_BANKNAME', $banktransfer_data['banktransfer_bankname']);
      
      $sepa_info = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/sepa_info.html');
    
      $smarty->assign('PAYMENT_INFO_HTML', $sepa_info);
      $smarty->assign('PAYMENT_INFO_TXT', strip_tags(str_replace(array('<br />', '<br/>', '<br>'), "\n", $sepa_info)));
    
      // separate pre-notification necessary?
      if ($banktransfer_data['banktransfer_owner_email'] != $order->customer['email_address']) {
        $banktransfer_owner_email = $banktransfer_data['banktransfer_owner_email'];
        $banktransfer_owner = $banktransfer_data['banktransfer_owner'];
        
        $sepa_html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/sepa_mail.html');
        $sepa_txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/sepa_mail.txt');
      
        // no pre-notification in order mail
        $smarty->clear_assign('PAYMENT_INFO_HTML');
        $smarty->clear_assign('PAYMENT_INFO_TXT');
      }
    }
  }
?>