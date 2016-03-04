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


class paypallink extends PayPalPayment {
	var $code, $title, $description, $extended_description, $enabled;


	function __construct() {
		global $order;
    
    PayPalPayment::__construct('paypallink');

		$this->tmpOrders = false;
	}


  function confirmation() {
    return array ('title' => $this->description);
  }


  function success() {
    global $last_order;
    
    if ($this->get_config('MODULE_PAYMENT_'.strtoupper($this->code).'_SUCCESS') == '1') {
      $success = array(
        array ('title' => $this->title.': ', 
               'class' => $this->code,
               'fields' => array(array('title' => '',
                                       'field' => sprintf(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_SUCCESS'), $this->create_paypal_link($last_order)),
                                       )
                                 )
               )
      );
    
      return $success;
    }
  }
  

	function keys() {
		return array('MODULE_PAYMENT_PAYPALLINK_STATUS', 
		             'MODULE_PAYMENT_PAYPALLINK_ALLOWED', 
		             'MODULE_PAYMENT_PAYPALLINK_ZONE',
		             'MODULE_PAYMENT_PAYPALLINK_SORT_ORDER'
		             );
	}

}
?>