<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// database tables
defined('TABLE_PAYPAL_PAYMENT') OR define('TABLE_PAYPAL_PAYMENT', 'paypal_payment');
defined('TABLE_PAYPAL_CONFIG') OR define('TABLE_PAYPAL_CONFIG', 'paypal_config');
defined('TABLE_PAYPAL_IPN') OR define('TABLE_PAYPAL_IPN', 'paypal_ipn');


// include needed functions
include_once(DIR_FS_EXTERNAL.'paypal/functions/PayPalFunctions.php');
if (!function_exists('xtc_get_zone_code')) {
  require_once(DIR_FS_INC.'xtc_get_zone_code.inc.php');
}


// include needed classes
include_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentBase.php');
require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');


// language
require_once(DIR_FS_EXTERNAL.'paypal/lang/'.$_SESSION['language'].'.php');


// used classes
use PayPal\Api\Sale;
use PayPal\Api\Capture;
use PayPal\Api\Authorization;
use PayPal\Api\Refund;
use PayPal\Api\Amount; 
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


class PayPalPayment extends PayPalPaymentBase {


  function __construct($class) {
    PayPalPaymentBase::init($class);
    
    $config = array(
      'LogEnabled' => ((defined('MODULE_PAYMENT_'.strtoupper($class).'_STATUS') && $this->get_config('PAYPAL_LOG_ENALBLED') == '1') ? true : false),
      'SplitLogging' => true,
      'LogLevel' => $this->loglevel,
      'LogThreshold' => '2MB',
      'FileName' => DIR_FS_LOG.'paypal_error_' .date('Y-m-d') .'.log',
      'FileName.debug' => DIR_FS_LOG.'paypal_debug_' .date('Y-m-d') .'.log',
      'FileName.fine' => DIR_FS_LOG.'paypal_fine_' .date('Y-m-d') .'.log',
      'FileName.info' => DIR_FS_LOG.'paypal_info_' .date('Y-m-d') .'.log',
      'FileName.warning' => DIR_FS_LOG.'paypal_warning_' .date('Y-m-d') .'.log',
      'FileName.error' => DIR_FS_LOG.'paypal_error_' .date('Y-m-d') .'.log',
    );
    $this->LoggingManager = new LoggingManager($config);
  }
   
  
  function payment_redirect($cart = false, $approval = false, $order_exists = false) {
    global $order, $xtPrice;
    
    // auth
    $apiContext = $this->apiContext();
  
    // set payment
    $payer = new Payer(); 
    $payer->setPaymentMethod('paypal');
  
    // set items
    $item = array();

    // set details
    $this->details = new Details(); 

    // set amount 
    $this->amount = new Amount(); 

    // set ItemList 
    $itemList = new ItemList(); 
    
    // set redirect
    $redirectUrls = new RedirectUrls(); 
    
    // set address
    $shipping_address = new ShippingAddress();      

    if ($cart === true) {
    
      $products = $_SESSION['cart']->get_products();
      for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
        $item[$i] = new Item(); 
        $item[$i]->setName($this->encode_utf8($products[$i]['name']))
                 ->setCurrency($_SESSION['currency']) 
                 ->setQuantity($products[$i]['quantity']) 
                 ->setPrice($products[$i]['price'])
                 ->setSku($products[$i]['model']); 

        $this->details->setSubtotal($this->details->getSubtotal() + $products[$i]['final_price']);
      }    
    
      $total = $price = $_SESSION['cart']->show_total();
      if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1 
          && $_SESSION['customers_status']['customers_status_ot_discount'] != '0.00'
          ) 
      {
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
            && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
            ) 
        {
          $price = $total - $_SESSION['cart']->show_tax(false);
        }
        $this->details->setShippingDiscount($this->details->getShippingDiscount() + ($xtPrice->xtcGetDC($price, $_SESSION['customers_status']['customers_status_ot_discount']) * (-1)));
      }

      $this->amount->setTotal($total);

      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
          && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
          && MODULE_SMALL_BUSINESS != 'true'
          ) 
      {
        foreach ($_SESSION['cart']->tax as $tax) {
          $this->details->setTax($this->details->getTax() + $tax['value']);
        }
      }
          
      // set amount 
      $this->amount->setCurrency($_SESSION['currency'])
                   ->setDetails($this->details); 

      // set redirect
      $redirectUrls->setReturnUrl($this->link_encoding(xtc_href_link('callback/paypal/paypalcart.php', '', 'SSL')))
                   ->setCancelUrl($this->link_encoding(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'SSL')));

    } else {
      
      $shipping_address->setRecipientName($this->encode_utf8($order->delivery['firstname'].' '.$order->delivery['lastname']))
                       ->setLine1($this->encode_utf8($order->delivery['street_address']))
                       ->setCity($this->encode_utf8($order->delivery['city']))
                       ->setCountryCode($this->encode_utf8((($order_exists === false) ? $order->delivery['country']['iso_code_2'] : $order->delivery['country_iso_2'])))
                       ->setPostalCode($this->encode_utf8($order->delivery['postcode']))
                       ->setState($this->encode_utf8((($order->delivery['state'] != '') ? xtc_get_zone_code($order->delivery['country_id'], $order->delivery['zone_id'], $order->delivery['state']) : '')));

      if ($order->delivery['suburb'] != '') {
        $shipping_address->setLine2($this->encode_utf8($order->delivery['suburb']));
      }
      
      for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
        $item[$i] = new Item(); 
        $item[$i]->setName($this->encode_utf8($order->products[$i]['name']))
                 ->setCurrency($order->info['currency']) 
                 ->setQuantity($order->products[$i]['qty']) 
                 ->setPrice($order->products[$i]['price'])
                 ->setSku($order->products[$i]['model']); 
      }  
      
      // set totals
      if ($order_exists === false) {
        if (!class_exists('order_total')) {
          require_once(DIR_WS_CLASSES.'order_total.php');
        }
        $order_total_modules = new order_total();
        $order_totals = $order_total_modules->process();
        $this->get_totals($order_totals, true);
      } else {
        $this->get_totals($order->totals);
      }
             
      // set amount 
      $this->amount->setCurrency($order->info['currency'])
                   ->setDetails($this->details);

      // set redirect
      if ($order_exists === false) {
        $redirectUrls->setReturnUrl($this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')))
                     ->setCancelUrl($this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL')));
      } else {
        $redirectUrls->setReturnUrl($this->link_encoding(xtc_href_link('callback/paypal/'.$this->code.'.php', 'oID='.$order->info['order_id'].'&key='.md5($order->customer['email_address']), 'SSL')))
                     ->setCancelUrl($this->link_encoding(xtc_href_link('callback/paypal/'.$this->code.'.php', 'payment_error='.$this->code.'&oID='.$order->info['order_id'].'&key='.md5($order->customer['email_address']), 'SSL')));
      }
    }

    // set ItemList
    if ($this->get_config('PAYPAL_ADD_CART_DETAILS') == '0') { 
      $item = array();
      $item[0] = new Item(); 
      $item[0]->setName($this->encode_utf8(MODULE_PAYMENT_PAYPAL_TEXT_ORDER))
              ->setCurrency($_SESSION['currency']) 
              ->setQuantity(1) 
              ->setPrice($this->details->getSubtotal()); 
    }
    $itemList->setItems($item);
    
    // profile
    $address_override = false;
    $profile_id = $this->get_config('PAYPAL_'.strtoupper($this->code.'_'.$_SESSION['language_code']).'_PROFILE');
    if ($profile_id == '') {
      $profile_id = $this->get_config('PAYPAL_STANDARD_PROFILE');
    }
    if ($profile_id != '') {
      if ($this->get_config(strtoupper($profile_id).'_TIME') < (time() - (3600 * 24))) {
        $profile = $this->get_profile($profile_id);
        $sql_data_array = array(
          array(
            'config_key' => strtoupper($profile_id).'_TIME', 
            'config_value' => time(),
          ),
          array(
            'config_key' => strtoupper($profile_id).'_ADDRESS', 
            'config_value' => $profile[0]['input_fields']['address_override'],
          ),          
        );
        $this->save_config($sql_data_array);
        $address_override = (($profile[0]['input_fields']['address_override'] == '0') ? true : false);
      } else {
        $address_override = (($this->get_config(strtoupper($profile_id).'_ADDRESS') == '0') ? true : false);
      }
    }

    if (($cart === false 
         //&& $order_exists === false 
         && $address_override === false) || ($order_exists === true)
        ) 
    {
      $itemList->setShippingAddress($shipping_address);
    }
    
    // set transaction
    $transaction = new Transaction(); 
    $transaction->setAmount($this->amount) 
                ->setItemList($itemList) 
                ->setDescription($this->encode_utf8(STORE_NAME)) 
                ->setInvoiceNumber(uniqid());
    
    // set payment
    $payment = new Payment(); 
    $payment->setIntent($this->transaction_type) 
            ->setPayer($payer) 
            ->setRedirectUrls($redirectUrls) 
            ->setTransactions(array($transaction))
            ->setCreateTime(time());
            
    if (isset($profile_id) && $profile_id != '') {
      $payment->setExperienceProfileId($profile_id);
    }
       
    try { 
    
      $payment->create($apiContext);
      $_SESSION['paypal']['paymentId'] = $payment->getId();
      
      $approval_link = $payment->getApprovalLink();
      if ($approval === false) {
        xtc_redirect($approval_link);
      } else {
        return $approval_link;
      }
      
    } catch (Exception $ex) { 
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      
      unset($_SESSION['paypal']);
      if ($cart === true) {
        xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'SSL'));
      } elseif ($this->code != 'paypalplus') {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }
    }
  }

  
  function patch_payment_paypalplus() {
    global $order, $order_total_modules;
        
    // auth
    $apiContext = $this->apiContext();
       
    try {
      // Get the payment Object by passing paymentId
      $payment = Payment::get($_SESSION['paypal']['paymentId'], $apiContext);
  
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
    
    $patches_array = array();
    $patchRequest = new PatchRequest();
    
    // set details
    $this->details = new Details(); 

    // set amount 
    $this->amount = new Amount(); 
    
    // set totals      
    $order_totals = $order_total_modules->output_array();
    $this->get_totals($order_totals);
          
    $this->amount->setCurrency($_SESSION['currency'])
                 ->setDetails($this->details);
            
    $patch_amount = new Patch();
    $patch_amount->setOp('replace')
                 ->setPath('/transactions/0/amount')
                 ->setValue($this->amount);
    $patches_array[] = $patch_amount;

    // set ItemList
    if ($this->get_config('PAYPAL_ADD_CART_DETAILS') == '0') { 
      $item = array();
      $item[0] = new Item(); 
      $item[0]->setName($this->encode_utf8(MODULE_PAYMENT_PAYPAL_TEXT_ORDER))
              ->setCurrency($_SESSION['currency']) 
              ->setQuantity(1) 
              ->setPrice($this->details->getSubtotal()); 
    } else {
      for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
        $item[$i] = new Item(); 
        $item[$i]->setName($this->encode_utf8($order->products[$i]['name']))
                 ->setCurrency($order->info['currency']) 
                 ->setQuantity($order->products[$i]['qty']) 
                 ->setPrice($order->products[$i]['price'])
                 ->setSku($order->products[$i]['model']); 
      }  
    }

    $patch_items = new Patch();
    $patch_items->setOp('replace')
                ->setPath('/transactions/0/item_list/items')
                ->setValue($item);
    $patches_array[] = $patch_items;
             
    // set payment address
    $payment_address = new Address();
    $payment_address->setLine1($this->encode_utf8($order->billing['street_address']))
                    ->setCity($this->encode_utf8($order->billing['city']))
                    ->setState($this->encode_utf8((($order->billing['state'] != '') ? xtc_get_zone_code($order->billing['country_id'], $order->billing['zone_id'], $order->billing['state']) : '')))
                    ->setPostalCode($this->encode_utf8($order->billing['postcode']))
                    ->setCountryCode($this->encode_utf8($order->billing['country']['iso_code_2']));

    if ($order->billing['suburb'] != '') {
      $payment_address->setLine2($this->encode_utf8($order->billing['suburb']));
    }

    $patch_payment = new Patch();
    $patch_payment->setOp('add')
                  ->setPath('/potential_payer_info/billing_address')
                  ->setValue($payment_address);
    $patches_array[] = $patch_payment;

    
    // set shipping address
    $shipping_address = new ShippingAddress();      

    $shipping_address->setRecipientName($this->encode_utf8($order->delivery['firstname'].' '.$order->delivery['lastname']))
                     ->setLine1($this->encode_utf8($order->delivery['street_address']))
                     ->setCity($this->encode_utf8($order->delivery['city']))
                     ->setCountryCode($this->encode_utf8($order->delivery['country']['iso_code_2']))
                     ->setPostalCode($this->encode_utf8($order->delivery['postcode']))
                     ->setState($this->encode_utf8((($order->delivery['state'] != '') ? xtc_get_zone_code($order->delivery['country_id'], $order->delivery['zone_id'], $order->delivery['state']) : '')));

    if ($order->delivery['suburb'] != '') {
      $shipping_address->setLine2($this->encode_utf8($order->billdeliverying['suburb']));
    }

    $patch_shipping = new Patch();
    $patch_shipping->setOp('add')
                   ->setPath('/transactions/0/item_list/shipping_address')
                   ->setValue($shipping_address);
    $patches_array[] = $patch_shipping;

    $patchRequest->setPatches($patches_array);
                    
    try {
      // update payment
      $payment->update($patchRequest, $apiContext);      
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
    
    $payment = Payment::get($_SESSION['paypal']['paymentId'], $apiContext);
  }
    
  
  function validate_payment_paypalcart() {
    
    if (isset($_GET['paymentId']) 
        && isset($_GET['PayerID']) 
        && $_SESSION['paypal']['paymentId'] == $_GET['paymentId']
        ) 
    {
      // auth
      $apiContext = $this->apiContext();
         
      try {
        // Get the payment Object by passing paymentId
        $payment = Payment::get($_GET['paymentId'], $apiContext);
        $valid = true;
    
      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
        $valid = false;
      }
      
      if ($valid === true) {
      
        // PaymentExecution
        $execution = new PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);
        
        // get customer
        $customer = $this->get_customer_data($payment);
                
        if (count($customer) > 0) {
          if (!isset($_SESSION['customer_id'])
              && isset($customer['info']['email_address']) 
              && $customer['info']['email_address'] != ''
              ) 
          {
            $this->login_customer($customer);
          } elseif (!isset($_SESSION['customer_id'])) {
            // redirect
            unset($_SESSION['paypal']);
            xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
          }
          
          // sendto
          $_SESSION['sendto'] = $this->get_shipping_address($_SESSION['customer_id'], $customer['delivery']);

        } elseif (!isset($_SESSION['customer_id'])) {
          // redirect
          unset($_SESSION['paypal']);
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
        }
        
        // payer
        $_SESSION['paypal']['PayerID'] = $_GET['PayerID'];
        $_SESSION['paypal']['payment_modules'] = 'paypalcart.php';
      } else {
        // redirect
        unset($_SESSION['paypal']);
        xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
      }
    } else {
      // redirect
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
    }
  }


  function validate_payment_paypal() {
    global $insert_id;
 
    if (isset($_GET['paymentId']) 
        && isset($_GET['PayerID']) 
        && $_SESSION['paypal']['paymentId'] == $_GET['paymentId']
        ) 
    {
       // auth
      $apiContext = $this->apiContext();
      
      try {
        // Get the payment Object by passing paymentId
        $payment = Payment::get($_SESSION['paypal']['paymentId'], $apiContext);       
          
      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');

        // redirect
        unset($_SESSION['paypal']);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));      
      }
      
      $patchRequest = new PatchRequest();
      
      $patch_invoice = new Patch();
      $patch_invoice->setOp('replace')
                    ->setPath('/transactions/0/invoice_number')
                    ->setValue($this->get_config('PAYPAL_CONFIG_INVOICE_PREFIX').$insert_id);

      $patchRequest->setPatches(array($patch_invoice));     
      
      try {
        // update payment
        $payment->update($patchRequest, $apiContext);      

        // Get the payment Object by passing paymentId
        $payment = Payment::get($_SESSION['paypal']['paymentId'], $apiContext);       

      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      }
    
      // payer
      $_SESSION['paypal']['PayerID'] = $_GET['PayerID'];
    
      // PaymentExecution
      $execution = new PaymentExecution();
      $execution->setPayerId($_SESSION['paypal']['PayerID']);
      
      // profile
      $profile_id = $this->get_config('PAYPAL_'.strtoupper($this->code.'_'.$_SESSION['language_code']).'_PROFILE');
      if ($profile_id == '') {
        $profile_id = $this->get_config('PAYPAL_STANDARD_PROFILE');
      }
      if ($profile_id != '') {
        $address_override = '0';
        if ($this->get_config(strtoupper($profile_id).'_TIME') < (time() - (3600 * 24))) {
          $profile = $this->get_profile($profile_id);
          $address_override = $profile[0]['input_fields']['address_override'];
        } else {
          $address_override = $this->get_config(strtoupper($profile_id).'_ADDRESS');
        }
        if ($address_override == '0') {
          // customer details    
          $sql_data_array = $this->get_customer_data($payment);
      
          $sql_data_array['delivery']['delivery_country'] = $sql_data_array['delivery']['delivery_country']['title'];
          unset($sql_data_array['delivery']['delivery_country_id']);
          unset($sql_data_array['delivery']['delivery_zone_id']);
                
          if (count($sql_data_array) > 0) {
            xtc_db_perform(TABLE_ORDERS, $sql_data_array['delivery'], 'update', "orders_id = '".$insert_id."'");
          }
        }
      }
      
      try {
        // Execute the payment
        $payment->execute($execution, $apiContext);
        
      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');          

        $this->remove_order($insert_id);
        unset($_SESSION['paypal']);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      // capture
      if (($this->transaction_type == 'order'
          || $this->transaction_type == 'authorize'
          ) && $this->get_config('PAYPAL_CAPTURE_MANUELL') == '0')
      {
        $this->capture_payment($payment);
      }
  
      $sql_data_array = array(
        'orders_id' => $insert_id,
        'payment_id' => $_SESSION['paypal']['paymentId'],
        'payer_id' => $_SESSION['paypal']['PayerID'],
      );
      xtc_db_perform(TABLE_PAYPAL_PAYMENT, $sql_data_array);

      try {
        // Get the payment Object by passing paymentId
        $payment = Payment::get($_SESSION['paypal']['paymentId'], $apiContext);
  
      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');

        $this->remove_order($insert_id);
        unset($_SESSION['paypal']);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      $status = $this->get_orders_status($payment);
      if ($status['status_id'] < 0) {
        $check_query = xtc_db_query("SELECT orders_status
                                       FROM ".TABLE_ORDERS." 
                                      WHERE orders_id = '".(int)$insert_id."'");
        $check = xtc_db_fetch_array($check_query);
        $status['status_id'] = $check['orders_status'];
      }
      $this->update_order($status['comment'], $status['status_id'], $insert_id);    

    } else {
      // redirect
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
  }


  function complete_cart($order_exists = true) {    
    global $insert_id;

    // check
    $check_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_PAYPAL_PAYMENT."
                                  WHERE payment_id = '".xtc_db_input($_SESSION['paypal']['paymentId'])."'");
    if (xtc_db_num_rows($check_query) > 0) {
        $status_id = $this->order_status_tmp;
        if ($status_id < 0) {
          $check_query = xtc_db_query("SELECT orders_status
                                         FROM ".TABLE_ORDERS." 
                                        WHERE orders_id = '".(int)$insert_id."'");
          $check = xtc_db_fetch_array($check_query);
          $status_id = $check['orders_status'];
        }
        $this->update_order('duplicate call, cancel', $status_id, $insert_id);    

      return;    
    }

     // auth
    $apiContext = $this->apiContext();
    
    try {
      // Get the payment Object by passing paymentId
      $payment = Payment::get($_SESSION['paypal']['paymentId'], $apiContext);
      
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      
      $this->remove_order($insert_id);
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
    }
    
    // set order
    $order = new order($insert_id);
    
    $patches_array = array();
    $patchRequest = new PatchRequest();

    $payment_address = new Address();
    $payment_address->setLine1($this->encode_utf8($order->billing['street_address']))
                    ->setCity($this->encode_utf8($order->billing['city']))
                    ->setState($this->encode_utf8((($order->billing['state'] != '') ? xtc_get_zone_code($order->billing['country_id'], $order->billing['zone_id'], $order->billing['state']) : '')))
                    ->setPostalCode($this->encode_utf8($order->billing['postcode']))
                    ->setCountryCode($this->encode_utf8(((isset($order->billing['country_iso_2'])) ? $order->billing['country_iso_2'] : $order->billing['country']['iso_code_2'])));

    if ($order->billing['suburb'] != '') {
      $payment_address->setLine2($this->encode_utf8($order->billing['suburb']));
    }

    $patch_payment = new Patch();
    $patch_payment->setOp('add')
                  ->setPath('/potential_payer_info/billing_address')
                  ->setValue($payment_address);
    $patches_array[] = $patch_payment;

    // set address
    $shipping_address = new ShippingAddress();      

    $shipping_address->setRecipientName($this->encode_utf8($order->delivery['firstname'].' '.$order->delivery['lastname']))
                     ->setLine1($this->encode_utf8($order->delivery['street_address']))
                     ->setCity($this->encode_utf8($order->delivery['city']))
                     ->setCountryCode($this->encode_utf8(((isset($order->delivery['country_iso_2'])) ? $order->delivery['country_iso_2'] : $order->delivery['country']['iso_code_2'])))
                     ->setPostalCode($this->encode_utf8($order->delivery['postcode']))
                     ->setState($this->encode_utf8((($order->delivery['state'] != '') ? xtc_get_zone_code($order->delivery['country_id'], $order->delivery['zone_id'], $order->delivery['state']) : '')));

    if ($order->delivery['suburb'] != '') {
      $shipping_address->setLine2($this->encode_utf8($order->delivery['suburb']));
    }

    $patch_shipping = new Patch();
    $patch_shipping->setOp('add')
                   ->setPath('/transactions/0/item_list/shipping_address')
                   ->setValue($shipping_address);
    $patches_array[] = $patch_shipping;
   
    $patch_invoice = new Patch();
    $patch_invoice->setOp('replace')
                  ->setPath('/transactions/0/invoice_number')
                  ->setValue($this->get_config('PAYPAL_CONFIG_INVOICE_PREFIX').$insert_id);
    $patches_array[] = $patch_invoice;
       
    // set details
    $this->details = new Details(); 

    // set amount 
    $this->amount = new Amount(); 
    
    // set totals
    $this->get_totals($order->totals);
          
    $this->amount->setCurrency($order->info['currency'])
                 ->setDetails($this->details);
    
    $patch_amount = new Patch();
    $patch_amount->setOp('replace')
                 ->setPath('/transactions/0/amount')
                 ->setValue($this->amount);
    $patches_array[] = $patch_amount;
    
    $patchRequest->setPatches($patches_array);     
    
    try {
      // update payment
      $payment->update($patchRequest, $apiContext);      

    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');

      if ($order_exists === false) {
        unset($_SESSION['paypal']);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      $this->remove_order($insert_id);
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
    }
    
    $payment = Payment::get($_SESSION['paypal']['paymentId'], $apiContext);

    // PaymentExecution
    $execution = new PaymentExecution();
    $execution->setPayerId($_SESSION['paypal']['PayerID']);

    try {
      // Execute the payment
      $payment->execute($execution, $apiContext);      

    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');

      $this->remove_order($insert_id);
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
    }

    // capture
    if (($this->transaction_type == 'order'
        || $this->transaction_type == 'authorize'
        ) && $this->get_config('PAYPAL_CAPTURE_MANUELL') == '0')
    {
      $this->capture_payment($payment);
    }

    $sql_data_array = array(
      'orders_id' => $insert_id,
      'payment_id' => $_SESSION['paypal']['paymentId'],
      'payer_id' => $_SESSION['paypal']['PayerID'],
    );
    xtc_db_perform(TABLE_PAYPAL_PAYMENT, $sql_data_array);

    try {
      // Get the payment Object by passing paymentId
      $payment = Payment::get($_SESSION['paypal']['paymentId'], $apiContext);

    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');

      $this->remove_order($insert_id);
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
    }

    $status = $this->get_orders_status($payment);
    if ($status['status_id'] < 0) {
      $check_query = xtc_db_query("SELECT orders_status
                                     FROM ".TABLE_ORDERS." 
                                    WHERE orders_id = '".(int)$insert_id."'");
      $check = xtc_db_fetch_array($check_query);
      $status['status_id'] = $check['orders_status'];
    }
    $this->update_order($status['comment'], $status['status_id'], $insert_id);    
  }
  
  
  function get_orders_status($payment) {
     // auth
    $apiContext = $this->apiContext();

    try {
      // get transaction
      $transactions = $payment->getTransactions();
      $transaction = $transactions[0];
      $relatedResources = $transaction->getRelatedResources();
      $relatedResource = end($relatedResources);

      if ($relatedResource->__isset('sale')) {
        $resource = $relatedResource->getSale($relatedResource);
      }
      if ($relatedResource->__isset('capture')) {
        $resource = $relatedResource->getCapture($relatedResource);
      }
      if ($relatedResource->__isset('order')) {
        $resource = $relatedResource->getOrder($relatedResource);
      }
      if ($relatedResource->__isset('authorization')) {
        $resource = $relatedResource->getAuthorization($relatedResource);
      }
      if ($relatedResource->__isset('refund')) {
        $resource = $relatedResource->getRefund($relatedResource);
      }
            
      switch ($resource->getState()) {
        case 'completed':
          $status_id = $this->order_status_success;
          break;
        default:
          $status_id = $this->order_status_pending;
          break;
      }
      
      return array(
        'status_id' => $status_id,
        'comment' => 'Transaction ID: '.$resource->getId(),
      );
      
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
    }
  }
  
  
  function capture_payment($payment, $order_id = '', $total = '', $final = true) {    
    global $insert_id;
    
    if ($order_id == '') {
      $order_id = $insert_id;
    }
    
     // auth
    $apiContext = $this->apiContext();

    try {
      // get transaction
      $transactions = $payment->getTransactions();
      $transaction = $transactions[0];
      $relatedResources = $transaction->getRelatedResources();
      
      for ($i=0, $n=count($relatedResources); $i<$n; $i++) {
        $relatedResource = $relatedResources[$i];
        if ($relatedResource->__isset('sale')) {
          $resource = $relatedResource->getSale($relatedResource);
          break;
        }
        if ($relatedResource->__isset('order')) {
          $resource = $relatedResource->getOrder($relatedResource);
          break;
        }
        if ($relatedResource->__isset('authorization')) {
          $resource = $relatedResource->getAuthorization($relatedResource);
          break;
        }
      }
      
      if (is_object($resource)) {
        $this->amount = $resource->getAmount();
        $this->amount->__unset('details');

        if ($total != '' && $total > 0) {
          $this->amount->setTotal($total);
        }
  
        // set capture
        $capture = new Capture();
        $capture->setIsFinalCapture($final);
        $capture->setAmount($this->amount);

        try {
          // capture
          $resource->capture($capture, $apiContext);
          $success = true;
        } catch (Exception $ex) {
          $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
          $success = false;

          if (defined('RUN_MODE_ADMIN') && $ex instanceof \PayPal\Exception\PayPalConnectionException) {
            $error_json = $ex->getData();
            $error = json_decode($error_json, true);
        
            $_SESSION['pp_error'] = $error['message'];
          }
        }
      
        if ($success === true) {
          if ($this->order_status_capture < 0) {
            $check_query = xtc_db_query("SELECT orders_status
                                           FROM ".TABLE_ORDERS." 
                                          WHERE orders_id = '".(int)$order_id."'");
            $check = xtc_db_fetch_array($check_query);
            $this->order_status_capture = $check['orders_status'];
          }
          $this->update_order(TEXT_PAYPAL_CAPTURED, $this->order_status_capture, $order_id);      
        }
      }
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
    }
  }


  function get_order_details($oID) {
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
        return $this->get_payment_details($payment);
      }
    } 
  }
  
  
  function get_transaction($id) {
    
    // auth
    $apiContext = $this->apiContext();
        
    try {
      $payment = Sale::get($id, $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
    
    if ($valid === true) {
      return $payment;
    }

    try {
      $payment = Authorization::get($id, $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
    
    if ($valid === true) {
      return $payment;
    }

    try {
      $payment = Capture::get($id, $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
    
    if ($valid === true) {
      return $payment;
    }

    try {
      $payment = Refund::get($id, $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
    
    if ($valid === true) {
      return $payment;
    }
  }
  
  
  function get_payment_details($payment, $order = false) {

    // auth
    $apiContext = $this->apiContext();

    // customer details
    $payer = $payment->getPayer();
    $payerinfo = $payer->getPayerInfo();

    $customer_data = $this->get_customer_data($payment);

    $message = '';
    if (isset($_SESSION['pp_error']) && $_SESSION['pp_error'] != '') {
      $message = $_SESSION['pp_error'];
      unset($_SESSION['pp_error']);
    }
        
    $payment_array = array(
      'id' => $payment->getId(),
      'payment_method' => $payer->getPaymentMethod(),
      'email_address' => $payerinfo->getEmail(),
      'account_status' => $payer->getStatus(),
      'intent' => $payment->getIntent(),
      'state' => $payment->getState(),
      'message' => $message,
      'address' => $customer_data['plain'],
      'transactions' => array(),
    );

    if ($order === true) {
      $orders_query = xtc_db_query("SELECT orders_id
                                      FROM ".TABLE_PAYPAL_PAYMENT."
                                     WHERE payment_id = '".xtc_db_input($payment->getId())."'");
      $orders = xtc_db_fetch_array($orders_query);
      $payment_array['orders_id'] = $orders['orders_id'];
    }

    // set instruction
    $instruction = $payment->getPaymentInstruction();
    if (is_object($instruction)) {
      $payment_array['instruction'] = $this->parsePaymentInstruction($instruction);
    }
        
    // transaction
    $transactions = $payment->getTransactions();

    for ($t=0, $z=count($transactions); $t<$z; $t++) {
      $transaction = $transactions[$t];
      $relatedResources = $transaction->getRelatedResources();
      
      $x = 0;
      for ($i=0, $n=count($relatedResources); $i<$n; $i++) {

        $relatedResource = $relatedResources[$i];

        if ($relatedResource->__isset('sale')) {
          $resource = $relatedResource->getSale($relatedResource);
        }
        if ($relatedResource->__isset('capture')) {
          $resource = $relatedResource->getCapture($relatedResource);
        }
        if ($relatedResource->__isset('order')) {
          $resource = $relatedResource->getOrder($relatedResource);
        }
        if ($relatedResource->__isset('authorization')) {
          $resource = $relatedResource->getAuthorization($relatedResource);
        }
        if ($relatedResource->__isset('refund')) {
          $resource = $relatedResource->getRefund($relatedResource);
        }
        
        try {
          $object = $resource->get($resource->getId(), $apiContext);
          $valid = true;
        } catch (Exception $ex) {
          $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
          $valid = false;
        }
        
        if ($valid === true) {
          // set amount
          $amount = $object->getAmount();
      
          // set reflect
          $reflect = new ReflectionClass($object);
          
          $type = strtolower($reflect->getShortName());
          
          if ($type == 'sale'
              || $type == 'order'
              || $type == 'authorization'
              )
          {
            $payment_array['total'] = $amount->getTotal();
          }
          
          $payment_array['transactions'][$t]['relatedResource'][$x] = array(
            'id' => $object->getId(),
            'type' => $type,
            'date' => date('Y-m-d H:i:s', strtotime($object->getCreateTime())),
            'state' => $object->getState(),
            'total' => $amount->getTotal(),
            'currency' => $amount->getCurrency(),
            'valid' => ((method_exists($object, 'getValidUntil')) ? date('Y-m-d H:i:s', strtotime($object->getValidUntil())) : ''),          
            'payment' => ((method_exists($object, 'getPaymentMode')) ? $object->getPaymentMode() : ''),          
            'reason' => ((method_exists($object, 'getReasonCode')) ? $object->getReasonCode() : ''),  
          );
          
          $x ++;
        }
      }
    }
  
    return $payment_array;
  }
  

  function parsePaymentInstruction($instruction) {
    
    // set amount
    $amount = $instruction->getAmount();
    
    // set banking
    $banking = $instruction->getRecipientBankingInstruction();
    
    $payment_array = array(
      'reference' => $instruction->getReferenceNumber(),
      'type' => $instruction->getInstructionType(),
      'amount' => array(
        'total' => $amount->getValue(),
        'currency' => $amount->getCurrency(),
      ),
      'date' => $instruction->getPaymentDueDate(),
      'note' => $instruction->getNote(),
      'bank' => array(
        'name' => $banking->getBankName(),
        'holder' => $banking->getAccountHolderName(),
        'account' => $banking->getAccountNumber(),
        'iban' => $banking->getInternationalBankAccountNumber(),
        'bic' => $banking->getBankIdentifierCode(),
      ),
    );
    
    return $payment_array;
  }

  
  function get_customer_data($payment) {
    
    $sql_data_array = array();
    
    try {
      // customer details
      $payer = $payment->getPayer();
      $customer = $payer->getPayerInfo();
      $address = $customer->getShippingAddress();
      
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
        
    if ($valid === true && is_object($address)) {
      $data = array(
        'name' => $customer->getFirstName() . ' ' . $customer->getLastName(),
        'company' => '',
        'firstname' => $customer->getFirstName(),
        'lastname' => $customer->getLastName(),
        'street_address' => $address->getLine1(),
        'suburb' => $address->getLine2(),
        'city' => $address->getCity(),
        'state' => $address->getState(),
        'postcode' => $address->getPostalCode(),
        'country_iso_code_2' => $address->getCountryCode(),
      );

      $country_iso_query = xtc_db_query("SELECT countries_id,
                                                countries_name,
                                                countries_iso_code_2,
                                                countries_iso_code_3
                                           FROM ".TABLE_COUNTRIES." 
                                          WHERE countries_iso_code_2 = '".xtc_db_input($data['country_iso_code_2'])."'");
      $country_iso = xtc_db_fetch_array($country_iso_query);
      $data['country_id'] = $country_iso['countries_id'];
      $data['country'] = array(
        'id' => $country_iso['countries_id'],
        'title' => $country_iso['countries_name'],
        'iso_code_2' => $country_iso['countries_iso_code_2'],
        'iso_code_3' => $country_iso['countries_iso_code_3'],
      );

      $data['zone_id'] = 0;
      $check_query = xtc_db_query("SELECT count(*) AS total 
                                     FROM ".TABLE_ZONES." 
                                    WHERE zone_country_id = '".(int)$data['country_id']."'");
      $check = xtc_db_fetch_array($check_query);
      $entry_state_has_zones = ($check['total'] > 0);
      if ($entry_state_has_zones == true) {
          $zone_query = xtc_db_query("SELECT DISTINCT zone_id
                                                 FROM ".TABLE_ZONES."
                                                WHERE zone_country_id = '".(int)$data['country_id'] ."'
                                                  AND (zone_id = '" . (int)$data['state'] . "'
                                                       OR zone_code = '" . xtc_db_input($data['state']) . "'
                                                       OR zone_name LIKE '" . xtc_db_input($data['state']) . "%'
                                                       )");
        if (xtc_db_num_rows($zone_query) == 1) {
          $zone = xtc_db_fetch_array($zone_query);
          $data['zone_id'] = $zone['zone_id'];
        } else {
          $data['state'] = '';
        }
      }
    
      foreach ($data as $key => $value) {
        $sql_data_array['customers']['customers_'.$key] = $value;
        $sql_data_array['delivery']['delivery_'.$key] = $value;
        $sql_data_array['payment']['payment_'.$key] = $value;
        $sql_data_array['plain'][$key] = $value;
      }
      $sql_data_array['info']['email_address'] = $customer->getEmail();
      $sql_data_array['info']['gender'] = $customer->getSalutation();
      $sql_data_array['info']['telephone'] = $customer->getPhone();
      $sql_data_array['info']['dob'] = $customer->getBirthDate();    
 
      if ($address->getRecipientName() != '') {
        $name = explode(' ', $address->getRecipientName());
        $sql_data_array['delivery']['delivery_name'] = $address->getRecipientName();
        $sql_data_array['delivery']['delivery_firstname'] = array_shift($name);
        $sql_data_array['delivery']['delivery_lastname'] = implode(' ', $name);
      }

      $sql_data_array = array_map(array($this, 'decode_utf8'), $sql_data_array);
    }
    
    return $sql_data_array;
  }
    
    
}
?>