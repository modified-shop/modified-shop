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
  
    $orders_array = array(
      'easycredit',
      'easyinvoice',
    );
    
    if (in_array($order->info['payment_method'], $orders_array)) {
      require_once(DIR_FS_EXTERNAL.'Teambank/classes/TeambankPayment.php');
  
      $TeambankPayment = new TeambankPayment();
      $TeambankPayment->init($order->info['payment_method']);
  
      // action
      if (isset($_POST['cmd'])) {
        switch ($_POST['cmd']) {
          case 'capture':
            try {
              $TeambankPayment->ecMerchant->confirmShipment($_POST['transactionId'], ((isset($_POST['tracking'])) ? $_POST['tracking'] : null));
              $messageStack->add_session(TEXT_TEAMBANK_CAPTURED_SUCCESS, 'success');
            } catch (Exception $e) {
              $messageStack->add_session(TEXT_TEAMBANK_CAPTURED_ERROR);
            }
            break;
            
          case 'refund':
            if ($_POST['refund_price'] > 0) {
              $TransactionInformation = $TeambankPayment->ecMerchant->getTransaction($_POST['transactionId']);
              if ($_POST['refund_price'] <= $TransactionInformation->getOrderDetails()->getCurrentOrderValue()) {
                try {
                  $TeambankPayment->ecMerchant->cancelOrder($_POST['transactionId'], $_POST['refund_price']);
                  $messageStack->add_session(TEXT_TEAMBANK_REFUND_SUCCESS, 'success');
                } catch (Exception $e) {
                  $messageStack->add_session(TEXT_TEAMBANK_REFUND_ERROR);
                }
              } else {
                $messageStack->add_session(TEXT_TEAMBANK_ERROR_AMOUNT);
              }
            } else {
              $messageStack->add_session(TEXT_TEAMBANK_ERROR_AMOUNT);
            }
            break;
        }
      }
    }
  }
