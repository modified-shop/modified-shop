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
use PayPal\Api\Tracker;
use PayPal\Api\Shipping;
use PayPal\Api\TrackerIdentifier;


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
        $this->LoggingManager->log('DEBUG', 'Payment', array('exception' => $ex));
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
            $response = $resource->refund($refund, $apiContext);
            return $response;
          } catch (Exception $ex) {
            $this->LoggingManager->log('DEBUG', 'Transactions', array('exception' => $ex));
            
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
        $this->LoggingManager->log('DEBUG', 'Payment', array('exception' => $ex));
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
        $this->LoggingManager->log('DEBUG', 'Payment', array('exception' => $ex));
      }
    }
    
    return $payment_array;
  }


  function subscription_info($oID) {
    
    // set payment_array
    $payment_array = array();
    
    $orders_query = xtc_db_query("SELECT p.*
                                    FROM `paypal_subscription` p
                                   WHERE p.orders_id = '".(int)$oID."'");
    if (xtc_db_num_rows($orders_query) > 0) {
      $orders = xtc_db_fetch_array($orders_query);
      
      $subscription = $this->get_subscription_details($orders['subscription_id']);
      $subscriber = $subscription->getSubscriber();
      $shipping_address = $subscriber->getShippingAddress();
      $billing_info = $subscription->getBillingInfo();
      
      
      $payment_array = array(
        'id' => $subscription->getId(),
        'payment_method' => 'Subscription',
        'email_address' => ((isset($subscriber) && is_object($subscriber)) ? $subscriber->getEmailAddress() : ''),
        'account_status' => '',
        'intent' => '',
        'total' => 0,
        'state' => $subscription->getStatus(),
        'address' => array(
          'name' => ((is_object($shipping_address->getName())) ? $shipping_address->getName()->getFullName() : ''),
          'company' => '',
          'firstname' => '',
          'lastname' => '',
          'street_address' => $shipping_address->getAddress()->getAddressLine1(),
          'suburb' => $shipping_address->getAddress()->getAddressLine2(),
          'city' => $shipping_address->getAddress()->getAdminArea2(),
          'state' => $shipping_address->getAddress()->getAdminArea1(),
          'postcode' => $shipping_address->getAddress()->getPostalCode(),
          'country_iso_code_2' => $shipping_address->getAddress()->getCountryCode(),
        ),
        'billing' => array(
          'outstanding_balance' => $billing_info->getOutstandingBalance()->getValue(),
          'currency' => $billing_info->getOutstandingBalance()->getCurrencyCode(),
          'next_billing_time' => $billing_info->getNextBillingTime(),
          'final_payment_time' => $billing_info->getFinalPaymentTime(),
          'failed_payments_count' => $billing_info->getFailedPaymentsCount(),
          'cycle_executions' => array(
            'tenure_type' => $billing_info->getCycleExecutions()[0]->getTenureType(),
            'sequence' => $billing_info->getCycleExecutions()[0]->getSequence(),
            'cycles_completed' => $billing_info->getCycleExecutions()[0]->getCyclesCompleted(),
            'cycles_remaining' => $billing_info->getCycleExecutions()[0]->getCyclesRemaining(),
            'total_cycles' => $billing_info->getCycleExecutions()[0]->getTotalCycles(),
          ),
        ),
        'transactions' => array(),
      );
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
      $this->LoggingManager->log('DEBUG', 'Payment', array('exception' => $ex));
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
  
  
  function getTrackingStatus($transaction_id, $tracking_number) {

    // auth
    $apiContext = $this->apiContext();

    // set shipping
    $shipping = new Shipping();

    try {
      $tracking = $shipping->get($transaction_id, $tracking_number, $apiContext);
      return $tracking->getStatus();
    } catch (Exception $ex) {
      $this->LoggingManager->log('DEBUG', 'getTracking', array('exception' => $ex));
    }
  }
  
  
  function addTracking($order_id, $tracking_id) {
    $tracking_query = xtc_db_query("SELECT pp.*,
                                           ot.*,
                                           c.carrier_name
                                      FROM ".TABLE_PAYPAL_PAYMENT." pp
                                      JOIN ".TABLE_ORDERS_TRACKING." ot
                                           ON ot.orders_id = pp.orders_id
                                              AND ot.tracking_id = '".xtc_db_input($tracking_id)."'
                                      JOIN ".TABLE_CARRIERS." c
                                           ON c.carrier_id = ot.carrier_id
                                     WHERE pp.orders_id = '".(int)$order_id."'");
    if (xtc_db_num_rows($tracking_query) > 0) {
      $tracking = xtc_db_fetch_array($tracking_query);

      // auth
      $apiContext = $this->apiContext();

      // set shipping
      $shipping = new Shipping();

      // set tracker
      $tracker = new Tracker();
      $tracker->setTransactionId($tracking['transaction_id'])
              ->setTrackingNumber($tracking['parcel_id'])
              ->setStatus('SHIPPED')
              ->setCarrier(strtoupper($tracking['carrier_name']));

      $shipping->addTracker($tracker);

      try {
        $shipping->create($apiContext);
        
        if (count($shipping->getErrors()) > 0) {
          $message = array();
          foreach ($shipping->getErrors() as $error) {
            $message[] = $error->getMessage();
            if (count($error->getDetails()) > 0) {
              foreach ($error->getDetails() as $detail) {
                $message[] = $detail->getDescription();
              }
            }
          }
          return $message;
        }

        $sql_data_array = array(
          'tracking_id' => $tracking['tracking_id'],
          'orders_id' => $tracking['orders_id'],
          'transaction_id' => $tracking['transaction_id'],
          'tracking_number' => $tracking['parcel_id'],
          'carrier' => strtoupper($tracking['carrier_name']),
          'date_added' => 'now()',
        );
        xtc_db_perform(TABLE_PAYPAL_TRACKING, $sql_data_array);

      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'addTracking', array('exception' => $ex));
      }
    }
  }


  function cancelTracking($order_id, $tracking_id) {
    $tracking_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_PAYPAL_TRACKING."
                                     WHERE orders_id = '".(int)$order_id."'
                                       AND tracking_id = '".(int)$tracking_id."'");
    if (xtc_db_num_rows($tracking_query) > 0) {
      $tracking = xtc_db_fetch_array($tracking_query);

      // auth
      $apiContext = $this->apiContext();

      // set shipping
      $shipping = new Shipping();
      $shipping->setTransactionId($tracking['transaction_id'])
               ->setTrackingNumber($tracking['tracking_number'])
               ->setStatus('CANCELLED');

      try {
        $shipping->replace($tracking['transaction_id'], $tracking['tracking_number'], $apiContext);

        xtc_db_query("DELETE FROM ".TABLE_PAYPAL_TRACKING."
                            WHERE tracking_id = '".(int)$tracking_id."'");
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'cancelTracking', array('exception' => $ex));
      }
    }
  }

}
?>