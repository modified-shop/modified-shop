<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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
  $amount = preg_replace('/[^0-9,.%]/', '', ((isset($_POST['amount'])) ? $_POST['amount'] : ''));
  // the last separator marks the decimals, anything left non-numeric like % is rejected
  $amount_comma = strrpos($amount, ',');
  $amount_dot = strrpos($amount, '.');
  if ($amount_comma !== false && ($amount_dot === false || $amount_comma > $amount_dot)) {
    $amount = str_replace(array('.', ','), array('', '.'), $amount);
  } else {
    $amount = str_replace(',', '', $amount);
  }
  $amount = ((is_numeric($amount)) ? (float)$amount : 0);
  
  if (isset($_POST['cancel_submit'])) {
    $_SESSION['klarna_success'] = $klarna->cancelOrder($order_id);
  } else {
    if ($amount > 0) {
      switch ($_POST['cmd']) {
        case 'refund':
          $_SESSION['klarna_success'] = $klarna->refundOrder($amount, $order_id, xtc_db_prepare_input($_POST['description']));
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
