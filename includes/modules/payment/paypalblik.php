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
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');


class paypalblik extends PayPalPaymentV2 {

  var $code;
  var $description;
  var $enabled;
  var $tmpOrders;
  var $paypal_code;
  var $allowed_zones;

  function __construct() {
    global $order;
  
    $this->allowed_zones = array('PL');

    PayPalPaymentV2::__construct('paypalblik');
    $this->tmpOrders = false;
  }


  function update_status() {
    global $order;
  
    $this->enabled = false;
    if (isset($order->billing['country']['iso_code_2'])
        && in_array($order->billing['country']['iso_code_2'], $this->allowed_zones)
        && in_array($order->info['currency'], array('PLN'))
        )
    {
      $this->enabled = true;
    }
  
    parent::update_status();	  
  }


  function confirmation() {
    return array ('title' => $this->description);
  }


  function process_button() {
    global $order;
  
    $payment_source = array(
      'payment_source' => array(
        'blik' => array(
          'country_code' => $this->encode_utf8($order->delivery['country']['iso_code_2']),
          'name' => $this->encode_utf8($order->delivery['firstname'].' '.$order->delivery['lastname']),
          'email' => $this->encode_utf8($order->customer['email_address']),
        )
      )
    );

    $_SESSION['paypal'] = array(
      'cartID' => $_SESSION['cart']->cartID,
      'OrderID'=> $this->CreateOrder($payment_source),
      'payerID' => ''
    );

    if ($_SESSION['paypal']['OrderID'] == '') {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
  }


  function before_process() {	  
    $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);

    if ($PayPalOrder->status == 'PAYER_ACTION_REQUIRED') {
      foreach ($PayPalOrder->links as $links) {
        if ($links->rel == 'payer-action') {
          xtc_redirect($links->href);
          break;
        }
      }
    }
  
    if (isset($PayPalOrder->payer->payer_id)) {
      $_SESSION['paypal']['payerID'] = $PayPalOrder->payer->payer_id;
    }
  
    if (!in_array($PayPalOrder->status, array('COMPLETED', 'APPROVED'))) {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
  }


  function before_send_order() {
    global $insert_id;
  
    $this->FinishOrder($insert_id);    
  }


  function after_process() {
    unset($_SESSION['paypal']);
  }


  function success() {    
    return false;
  }


  function install() {	
    parent::install();	  
  }


  function keys() {
    return array(
      'MODULE_PAYMENT_PAYPALBLIK_STATUS', 
      'MODULE_PAYMENT_PAYPALBLIK_ALLOWED', 
      'MODULE_PAYMENT_PAYPALBLIK_ZONE',
      'MODULE_PAYMENT_PAYPALBLIK_SORT_ORDER'
    );
  }

}
