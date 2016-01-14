<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');

if (is_object($order) && strpos($order->info['payment_method'], 'paypal') !== false) {
  $paypal = new PayPalPayment($order->info['payment_method']);
  
  if ($order->info['payment_method'] == 'paypallink'
      || $order->info['payment_method'] == 'paypalpluslink'
      ) 
  {
    $payapl_payment_info = array(
      array ('title' => $paypal->title.': ', 
             'class' => $paypal->code,
             'fields' => array(array('title' => '',
                                     'field' => sprintf(constant('MODULE_PAYMENT_'.strtoupper($paypal->code).'_TEXT_SUCCESS'), $paypal->create_paypal_link($order->info['order_id'])),
                                     )
                               )
             )
    );

    $paypal_smarty = new Smarty;
    $paypal_smarty->caching = 0;
    $paypal_smarty->assign('PAYMENT_INFO', $payapl_payment_info);
    $paypal_smarty->assign('language', $_SESSION['language']);
    $payment_info_content = $paypal_smarty->fetch(DIR_FS_EXTERNAL.'/paypal/templates/payment_info.html');

    $smarty->assign('PAYMENT_INFO_HTML', $payment_info_content);
    $smarty->assign('PAYMENT_INFO_TXT', sprintf(constant('MODULE_PAYMENT_'.strtoupper($paypal->code).'_TEXT_SUCCESS'), $paypal->create_paypal_link($order->info['order_id'], true)));

  } else {
    $payapl_payment_info = $paypal->success($order->info['order_id']);
  
    if (is_array($payapl_payment_info)) {
      $paypal_smarty = new Smarty;
      $paypal_smarty->caching = 0;
      $paypal_smarty->assign('PAYMENT_INFO', $payapl_payment_info);
      $paypal_smarty->assign('language', $_SESSION['language']);
      $payment_info_content = $paypal_smarty->fetch(DIR_FS_EXTERNAL.'/paypal/templates/payment_info.html');
  
      $smarty->assign('PAYMENT_INFO_HTML', $payment_info_content);
      $smarty->assign('PAYMENT_INFO_TXT', $payment_info_content);
    }
  }
  
}
?>