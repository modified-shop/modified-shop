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
  var $code, $title, $description, $extended_description, $enabled;


  function __construct() {
    global $order;
  
    PayPalPaymentV2::__construct('paypalblik');
    $this->tmpOrders = false;
  }


  function update_status() {
    global $order;
  
    $this->enabled = false;
    if (in_array($order->billing['country']['iso_code_2'], array('PL'))
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


  function pre_confirmation_check() {
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
    return false;
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
?>