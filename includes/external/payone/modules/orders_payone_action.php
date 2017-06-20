<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset($oID) && $oID != '') {
  $order = new order($oID);

  // action
  $payone_payment_methods = array('payone', 
                                  'payone_cc', 
                                  'payone_otrans', 
                                  'payone_installment', 
                                  'payone_wlt', 
                                  'payone_elv', 
                                  'payone_prepay', 
                                  'payone_cod', 
                                  'payone_paydirekt', 
                                  'payone_invoice');
                                
  if (in_array($order->info['payment_method'], $payone_payment_methods)) {

    require_once (DIR_FS_EXTERNAL.'payone/lang/'.$order->info['language'].'.php');
    require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneModified.php');
    $payone = new PayoneModified();

    if (!is_array($_SESSION['orders_payone_messages'])) {
      $_SESSION['orders_payone_messages'] = array();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if ($_POST['cmd'] == 'capture') {
        if (isset($_POST['positions'])) {
          $_POST['capture']['positions'] = $_POST['positions'];
        }
        $response = $payone->captureAmount($_POST['capture']);
        if ($response->getStatus() == 'ERROR') {
          $_SESSION['orders_payone_messages'][] = ERROR_OCCURED.": ".$response->getErrorcode().' '.$response->getErrormessage();
        } else {
          $_SESSION['orders_payone_messages'][] = AMOUNT_CAPTURED;
        }
      }
      if ($_POST['cmd'] == 'refund') {
        if (isset($_POST['positions'])) {
          $_POST['refund']['positions'] = $_POST['positions'];
        }
        $response = $payone->refundAmount($_POST['refund']);
        if ($response->getStatus() == 'ERROR') {
          $_SESSION['orders_payone_messages'][] = ERROR_OCCURED.": ".$response->getErrorcode().' '.$response->getErrormessage();
        } else {
          $_SESSION['orders_payone_messages'][] = AMOUNT_REFUNDED;
        }
      }
    }
  }
}
?>