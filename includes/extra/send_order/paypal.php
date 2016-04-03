<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$paypal_payment_method = array(
  'paypalplus',
  'paypalclassic',
  'paypalcart',
  'paypallink',
  'paypalpluslink',
);

if (is_object($order) && in_array($order->info['payment_method'], $paypal_payment_method)) {

  // include needed classes
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');

  $paypal = new PayPalPayment($order->info['payment_method']);
  
  if (strpos($order->info['payment_method'], 'link') !== false) {
    $paypal_payment_info = array(
      array ('title' => $paypal->title.': ', 
             'class' => $paypal->code,
             'fields' => array(array('title' => '',
                                     'field' => sprintf(constant('MODULE_PAYMENT_'.strtoupper($paypal->code).'_TEXT_SUCCESS'), $paypal->create_paypal_link($order->info['order_id'])),
                                     )
                               )
             )
    );
    
    $paypal_smarty = new Smarty;
    if (defined('RUN_MODE_ADMIN')) {
      $paypal_smarty->template_dir = DIR_FS_CATALOG.'templates';
      $paypal_smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
      $paypal_smarty->config_dir = DIR_FS_CATALOG.'lang';
    }
    $paypal_smarty->caching = 0;
    $paypal_smarty->assign('PAYMENT_INFO', $paypal_payment_info);
    $paypal_smarty->assign('language', $_SESSION['language']);
    $payment_info_content = $paypal_smarty->fetch(DIR_FS_EXTERNAL.'paypal/templates/payment_info.html');

    $smarty->assign('PAYMENT_INFO_HTML', $payment_info_content);
    $smarty->assign('PAYMENT_INFO_TXT', sprintf(constant('MODULE_PAYMENT_'.strtoupper($paypal->code).'_TEXT_SUCCESS'), $paypal->create_paypal_link($order->info['order_id'], true)));

  } else {
    $paypal_payment_info = $paypal->success($order->info['order_id']);
  
    if (is_array($paypal_payment_info)) {
      $paypal_smarty = new Smarty;
      if (defined('RUN_MODE_ADMIN')) {
        $paypal_smarty->template_dir = DIR_FS_CATALOG.'templates';
        $paypal_smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
        $paypal_smarty->config_dir = DIR_FS_CATALOG.'lang';
      }
      $paypal_smarty->caching = 0;
      $paypal_smarty->assign('PAYMENT_INFO', $paypal_payment_info);
      $paypal_smarty->assign('language', $_SESSION['language']);
      $payment_info_content = $paypal_smarty->fetch(DIR_FS_EXTERNAL.'paypal/templates/payment_info.html');
  
      $smarty->assign('PAYMENT_INFO_HTML', $payment_info_content);
      $smarty->assign('PAYMENT_INFO_TXT', $payment_info_content);
    }
  }
  
}

if (isset($_SESSION['paypal_express_new_customer']) 
    && $_SESSION['paypal_express_new_customer'] == 'true'
    && !isset($send_by_admin)
    ) 
{
  require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
  
  $vlcode = xtc_random_charcode(32);
  $link = xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, 'action=verified&customers_id='.$check_customer['customers_id'].'&key='.$vlcode, 'SSL');

  $sql_data_array = array('password_request_key' => $vlcode);
  xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . $check_customer['customers_id'] . "'");
  
  $smarty->assign('NEW_PASSWORD', $link);
}
?>