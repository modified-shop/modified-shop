<?php
/* -----------------------------------------------------------------------------------------
   $Id: orders_klarna.php 11169 2018-05-30 13:57:32Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (isset($_GET['subaction']) 
    && $_GET['subaction'] == 'klarnaaction'
    ) 
{
  require_once (DIR_WS_CLASSES.'order.php');
  require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaPayment.php');

  $order = new order((int)$_GET['oID']);
  $klarna = new KlarnaPayment($order->info['payment_method']);
  $order_id = $klarna->get_klarna_order($order->info['order_id']);
  $amount = str_replace(',', '.', preg_replace('/[^0-9,.%]/', '', $_POST['amount']));
  
  if (isset($_POST['cancel_submit'])) {
    $_SESSION['klarna_success'] = $klarna->cancelOrder($order_id);
  } else {
    if ($amount > 0) {
      switch ($_POST['cmd']) {
        case 'refund':
          $_SESSION['klarna_success'] = $klarna->refundOrder($amount, $order_id);
          break;

        case 'capture':
          $_SESSION['klarna_success'] = $klarna->captureOrder($amount, $order_id);
          break;
      }
    } else {
      $_SESSION['klarna_error'] = TEXT_KLARNA_TRANSACTION_ERROR_AMOUNT;
    }
  }
  xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'subaction')).'action=edit'));
}
