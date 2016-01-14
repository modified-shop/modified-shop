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


// used classes
use PayPal\Api\Amount; 
use PayPal\Api\Capture;
use PayPal\Api\Details; 
use PayPal\Api\Item; 
use PayPal\Api\ItemList; 
use PayPal\Api\Payer; 
use PayPal\Api\Payment; 
use PayPal\Api\RedirectUrls; 
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use PayPal\Api\PatchRequest;
use PayPal\Api\Patch;
use PayPal\Api\Address;
use PayPal\Api\BaseAddress;
use PayPal\Api\ShippingAddress;
use PayPal\Api\PotentialPayerInfo;
use PayPal\Api\Refund;


class PayPalInfo extends PayPalPayment {


	function __construct($class) {
    PayPalPayment::__construct($class);
	}


  function refund_payment($oID, $total = '', $comment = '') {
    global $order;
    
    $orders_query = xtc_db_query("SELECT p.*,
                                         o.customers_address_format_id
                                    FROM ".TABLE_PAYPAL_PAYMENT." p
                                    JOIN ".TABLE_ORDERS." o
                                         ON p.orders_id = o.orders_id
                                   WHERE p.orders_id = '".(int)$oID."'");
    if (xtc_db_num_rows($orders_query) > 0) {
      $orders = xtc_db_fetch_array($orders_query);

      // auth
      $apiContext = $this->apiContext();

      try {
        // Get the payment Object by passing paymentId
        $payment = Payment::get($orders['payment_id'], $apiContext);
        $valid = true;
      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
        $valid = false;
      }
      
      if ($valid === true) {
      
        // transaction
        $transactions = $payment->getTransactions();
        $transaction = $transactions[0];

        $relatedResources = $transaction->getRelatedResources();
        for ($i=0, $n=count($relatedResources); $i<$n; $i++) {
      
          $relatedResource = $relatedResources[$i];
                
          if ($relatedResource->__isset('sale')) {
            $resource = $relatedResource->getSale($relatedResource);
            break;
          }
          if ($relatedResource->__isset('capture')) {
            $resource = $relatedResource->getCapture($relatedResource);
            break;
          }
          if ($relatedResource->__isset('order')) {
            continue;
          }
          if ($relatedResource->__isset('authorization')) {
            continue;
          }
          if ($relatedResource->__isset('refund')) {
            continue;
          }
        }

        if (is_object($resource)) {
          // get amount
          $amount = $resource->getAmount();
          $amount->__unset('details');
          
          if ($total != '' && $total > 0) {
            $amount->setTotal($total);
          }
          
          // set refund
          $refund = new Refund();
          $refund->setAmount($amount);
          
          if ($comment != '') {
            $refund->setDescription($this->encode_utf8($comment));
          }
                    
          try {
            $resource->refund($refund, $apiContext);
            $success = true;
          } catch (Exception $ex) {
            $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
            
            if ($ex instanceof \PayPal\Exception\PayPalConnectionException) {
              $error_json = $ex->getData();
              $error = json_decode($error_json, true);
            
              $_SESSION['pp_error'] = $error['message'];
            }
          }
        }
      }
    }  
  }
  

  function capture_payment_admin($oID, $total = '', $final = false) {
    global $order;
  
    $orders_query = xtc_db_query("SELECT p.*,
                                         o.customers_address_format_id
                                    FROM ".TABLE_PAYPAL_PAYMENT." p
                                    JOIN ".TABLE_ORDERS." o
                                         ON p.orders_id = o.orders_id
                                   WHERE p.orders_id = '".(int)$oID."'");
    if (xtc_db_num_rows($orders_query) > 0) {
      $orders = xtc_db_fetch_array($orders_query);

      // auth
      $apiContext = $this->apiContext();

      try {
        // Get the payment Object by passing paymentId
        $payment = Payment::get($orders['payment_id'], $apiContext);
        $valid = true;
      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
        $valid = false;
      }
    
      if ($valid === true) {
        $this->capture_payment($payment, $oID, $total, $final);
      }
    }  
  }

  
  function order_info($oID) {
    
    // set payment_array
    $payment_array = array();
    
    $orders_query = xtc_db_query("SELECT p.*
                                    FROM ".TABLE_PAYPAL_PAYMENT." p
                                   WHERE p.orders_id = '".(int)$oID."'");
    if (xtc_db_num_rows($orders_query) > 0) {
      $orders = xtc_db_fetch_array($orders_query);

       // auth
      $apiContext = $this->apiContext();
    
      try {
        // Get the payment Object by passing paymentId
        $payment = Payment::get($orders['payment_id'], $apiContext);

        $payment_array =  $this->get_payment_details($payment);    
      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      }
    }
    
    return $payment_array;
  }


  function get_payments($count, $offset) {

     // auth
    $apiContext = $this->apiContext();

    $params = array('count' => $count, 'start_index' => $offset);

    try {
      // Get the payment Object by passing paymentId
      $PaymentHistory = Payment::all($params, $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }

    $payment_array = array();
    if ($valid === true) {
      $payments = $PaymentHistory->getPayments();
    
      for ($p=0, $x=count($payments); $p<$x; $p++) {      
        $payment_array[$p] = $this->get_payment_details($payments[$p], true);
      }
    }
    
    return $payment_array;
  }
  
  
}
?>