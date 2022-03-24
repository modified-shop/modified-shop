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


class paypalsofort extends PayPalPaymentV2 {
  var $code, $title, $description, $extended_description, $enabled;


  function __construct() {
    global $order;
  
    PayPalPaymentV2::__construct('paypalsofort');
    $this->tmpOrders = false;
  }


  function update_status() {
    global $order;
  
    $this->enabled = false;
    if (in_array($order->delivery['country']['iso_code_2'], array('AT', 'BE', 'DE', 'ES', 'IT', 'NL', 'GB'))
        && in_array($order->info['currency'], array('EUR', 'GBP'))
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
        'sofort' => array(
          'country_code' => $this->encode_utf8($order->delivery['country']['iso_code_2']),
          'name' => $this->encode_utf8($order->delivery['firstname'].' '.$order->delivery['lastname']),
        )
      )
    );

    $_SESSION['paypal'] = array(
      'cartID' => $_SESSION['cart']->cartID,
      'orderID' => $this->CreateOrder($payment_source),
      'PayerID' => ''
    );

    if ($_SESSION['paypal']['orderID'] == '') {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
  }


  function before_process() {	  
    $PayPalOrder = $this->GetOrder($_SESSION['paypal']['orderID']);

    if ($PayPalOrder->status == 'PAYER_ACTION_REQUIRED') {
      foreach ($PayPalOrder->links as $links) {
        if ($links->rel == 'payer-action') {
          xtc_redirect($links->href);
          break;
        }
      }
    }
  
    if (isset($PayPalOrder->payer->payer_id)) {
      $_SESSION['paypal']['PayerID'] = $PayPalOrder->payer->payer_id;
    }
  
    if (!in_array($PayPalOrder->status, array('COMPLETED', 'APPROVED'))) {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
  }


  function after_process() {
    global $insert_id;
  
    $this->FinishOrder($insert_id);    
  }


  function success() {    
    return false;
  }


  function install() {	
    parent::install();	  
  }


  function keys() {
    return array(
      'MODULE_PAYMENT_PAYPALSOFORT_STATUS', 
      'MODULE_PAYMENT_PAYPALSOFORT_ALLOWED', 
      'MODULE_PAYMENT_PAYPALSOFORT_ZONE',
      'MODULE_PAYMENT_PAYPALSOFORT_SORT_ORDER'
    );
  }

}
?>