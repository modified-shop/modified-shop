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


class paypalexpress extends PayPalPaymentV2 {
  var $code, $title, $description, $extended_description, $enabled;


  function __construct() {
    global $order;
  
    $this->paypal_code = 'paypal';
    PayPalPaymentV2::__construct('paypalexpress');  
    $this->tmpOrders = false;
  
		if (!defined('RUN_MODE_ADMIN') && is_object($order)) {
			$this->update_status();
		}

    if (isset($_POST['comments'])) {
      $_SESSION['comments'] = xtc_db_prepare_input($_POST['comments']);
    }
  }


  function update_status() {
    global $order, $PHP_SELF;
  
    parent::update_status();
  
    if (($this->enabled == false 
         || !isset($_SESSION['paypal']['cartID']) 
         || $_SESSION['paypal']['cartID'] != $_SESSION['cart']->cartID
         ) && !defined('RUN_MODE_ADMIN')
        )
    {
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(), 'SSL'));
    }
  }


  function selection() {
    unset($_SESSION['paypal']);
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
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
      'MODULE_PAYMENT_PAYPALEXPRESS_STATUS', 
      'MODULE_PAYMENT_PAYPALEXPRESS_ALLOWED', 
      'MODULE_PAYMENT_PAYPALEXPRESS_ZONE',
      'MODULE_PAYMENT_PAYPALEXPRESS_SORT_ORDER'
    );
  }

}
?>