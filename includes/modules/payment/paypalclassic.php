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


class paypalclassic extends PayPalPayment {
	var $code, $title, $description, $extended_description, $enabled;


	function __construct() {
		global $order;
    
    PayPalPayment::__construct('paypalclassic');

		$this->tmpOrders = false;
	}


  function confirmation() {
    return array ('title' => $this->description);
  }


	function before_process() {
		// confirmed
		if (isset($_GET['PayerID']) && $_GET['PayerID'] != '' 
		    && isset($_GET['token']) && $_GET['token'] != '' 
		    && isset($_GET['paymentId']) && $_GET['paymentId'] != '' 
		    && $_GET['paymentId'] == $_SESSION['paypal']['paymentId']		
		    ) 
		{
   		return;
		}
 		$this->payment_redirect();
	}


	function after_process() {
		$this->validate_payment_paypal();
		unset($_SESSION['paypal']);
	}


	function keys() {
		return array('MODULE_PAYMENT_PAYPALCLASSIC_STATUS', 
		             'MODULE_PAYMENT_PAYPALCLASSIC_ALLOWED', 
		             'MODULE_PAYMENT_PAYPALCLASSIC_ZONE',
		             'MODULE_PAYMENT_PAYPALCLASSIC_SORT_ORDER'
		             );
	}

}
?>