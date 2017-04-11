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


class paypalinstallment extends PayPalPayment {
	var $code, $title, $description, $extended_description, $enabled;


	function __construct() {
		global $order;
    
    PayPalPayment::__construct('paypalinstallment');

		$this->tmpOrders = false;
	}


  function update_status() {
    global $order;
    
    if ($this->enabled === true
        && (!defined('MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_STATUS')
            || MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_STATUS == 'false'
            )
        )
    {
      $this->enabled = false;
    }
    
    if ($this->enabled === true
        && $order->billing['country']['iso_code_2'] != 'DE'
        )
    {
      $this->enabled = false;
    }

    if ($this->enabled === true
        && ($_SESSION['customers_status']['customers_status_show_price_tax'] != '1'
            || $_SESSION['customers_status']['customers_status_add_tax_ot'] != '0'
            )
        )
    {
      $this->enabled = false;
    }
    
    if ($this->enabled === true) {
      if (!class_exists('order_total')) {
        require_once(DIR_WS_CLASSES.'order_total.php');
      }
      $order_total_modules = new order_total();
      $order_totals = $order_total_modules->process();
      
      $this->total_amount = 0;
      for ($i=0, $n=count($order_totals); $i<$n; $i++) {
        if ($order_totals[$i]['code'] == 'ot_total') {
          $this->total_amount = $order_totals[$i]['value'];
        }
      }
      
      $this->presentment_array = $this->get_presentment($this->total_amount, $order->info['currency'], $order->billing['country']['iso_code_2']);
      if (count($this->presentment_array) < 1) {
        $this->enabled = false;
      }
    }
  }
  
  
  function selection() {
    global $order, $request_type;
    
    $presentment = $this->get_presentment_details($this->total_amount, $order->info['currency'], $order->billing['country']['iso_code_2'], 'payment');

    return array(
      'id' => $this->code, 
      'module' => $this->title, 
      'description' => $presentment,
    );
  }


  function confirmation() {
    return array ('title' => $this->description);
  }


	function pre_confirmation_check() {
		// confirmed
		if (isset($_GET['PayerID']) && $_GET['PayerID'] != '' 
		    && isset($_GET['token']) && $_GET['token'] != '' 
		    && isset($_GET['paymentId']) && $_GET['paymentId'] != '' 
		    && $_GET['paymentId'] == $_SESSION['paypal']['paymentId']		
		    ) 
		{
		  $this->validate_paypal_installment();
   		return;
		}
		
    // load the selected shipping module
    require_once (DIR_WS_CLASSES . 'shipping.php');
    $shipping_modules = new shipping($_SESSION['shipping']);

 		$this->payment_redirect();
	}


  function before_send_order() {
		$this->complete_payment_paypal_installment();
  }


	function after_process() {
		unset($_SESSION['paypal']);
	}

  
	function install() {
	  parent::install();

    $stati = array(
      'PAYPAL_INST_ORDER_STATUS_ACCEPTED_NAME' => 'PAYPAL_ORDER_STATUS_ACCEPTED_ID'
    );
	  $this->status_install($stati);
	  
	  require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'order_total/ot_paypalinstallment_fee.php');
	  $pp_fee = new ot_paypalinstallment_fee();
	  if ($pp_fee->check() != 1) {
	    $pp_fee->install();
	  }
	}


	function keys() {
		return array('MODULE_PAYMENT_PAYPALINSTALLMENT_STATUS', 
		             'MODULE_PAYMENT_PAYPALINSTALLMENT_SORT_ORDER'
		             );
	}

}
?>