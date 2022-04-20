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


class paypalpluslink extends PayPalPayment {
	var $code, $title, $description, $extended_description, $enabled;


	function __construct() {
		global $order;
    
    PayPalPayment::__construct('paypalpluslink');

		$this->tmpOrders = false;
	}


  function confirmation() {
    return array ('title' => $this->description);
  }


  function success() {
    global $last_order, $PHP_SELF, $smarty, $messageStack;
    
    if ((basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS && $this->get_config('MODULE_PAYMENT_'.strtoupper($this->code).'_SUCCESS') == '1')
         || (basename($PHP_SELF) == FILENAME_ACCOUNT_HISTORY_INFO && $this->get_config('MODULE_PAYMENT_'.strtoupper($this->code).'_USE_ACCOUNT') == '1')
        )
    {
      if ($messageStack->size($this->code) > 0) {
        $smarty->assign('error_message', $messageStack->output($this->code));
      }    
      if ($messageStack->size($this->code, 'success') > 0) {
        $smarty->assign('success_message', $messageStack->output($this->code, 'success'));
      }    

      $button = $this->create_paypal_link($last_order);
      if ($button != '') {
        $success = array(
          array ('title' => ((basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS) ? $this->title : ''), 
                 'class' => $this->code,
                 'fields' => array(array('title' => '',
                                         'field' => sprintf(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_SUCCESS'), $button),
                                         )
                                   )
                 )
        );
    
        return $success;
      }
    }
  }
  

	function keys() {
		return array('MODULE_PAYMENT_PAYPALPLUSLINK_STATUS', 
		             'MODULE_PAYMENT_PAYPALPLUSLINK_ALLOWED', 
		             'MODULE_PAYMENT_PAYPALPLUSLINK_ZONE',
		             'MODULE_PAYMENT_PAYPALPLUSLINK_SORT_ORDER'
		             );
	}

}
?>