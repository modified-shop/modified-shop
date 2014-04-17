<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once (DIR_WS_CLASSES.'order.php');
  $order = new order((int)$_SESSION['tmp_oID']);
  $smarty->assign('language', $_SESSION['language']);
  // Delivery Info
  if ($order->delivery != false) {
    $smarty->assign('DELIVERY_LABEL', xtc_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'));
    if ($order->info['shipping_method']) { $smarty->assign('SHIPPING_METHOD', $order->info['shipping_method']); }
  }
  // Payment Method
  if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {
    include (DIR_WS_LANGUAGES.'/'.$_SESSION['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
    $smarty->assign('PAYMENT_METHOD', constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_TEXT_TITLE'));
  }
  // order total
  $order_total = $order->getTotalData((int)$_SESSION['tmp_oID']);
  $smarty->assign('order_data', $order->getOrderData((int)$_SESSION['tmp_oID']));
  $smarty->assign('order_total', $order_total['data']);

  $smarty->assign('BILLING_LABEL', xtc_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'));
  $smarty->assign('ORDER_NUMBER', $_SESSION['tmp_oID']);
  $smarty->assign('ORDER_DATE', xtc_date_long($order->info['date_purchased']));
  $smarty->assign('ORDER_STATUS', $order->info['orders_status']);
  $smarty->assign('BUTTON_PAYPAL', '<br />'.$o_paypal->build_express_fehler_button().'<br />'.PAYPAL_NEUBUTTON);
  $order_details = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_history_info.html');
  
  $smarty->assign('MODULE_order_details', $order_details);
  
  // new template for second PayPal call
  if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal_express_cart.html')) {
    $template_cart = 'paypal_express_cart.html';
  }
?>