<?php
/* -----------------------------------------------------------------------------------------
   $Id: paypalsubscription.php 11597 2019-03-21 15:04:31Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');


class paypalsubscription extends PayPalPayment {
	var $code, $title, $description, $extended_description, $enabled;


	function __construct() {
		global $order;
    
    PayPalPayment::__construct('paypalsubscription');

		$this->tmpOrders = false;
	}


  function confirmation() {
    return array ('title' => $this->description);
  }


	function before_process() {
	  if (isset($_GET['subscription_id']) 
		    && isset($_GET['token']) && $_GET['token'] != '' 
	      && $_GET['subscription_id'] == $_SESSION['paypal']['paymentId']
	      )
	  {
   		return;
		}
		
 		$this->create_subscription();
	}


  function before_send_order() {
    global $insert_id;
    
    $subscription = $this->get_subscription_details($_SESSION['paypal']['paymentId']);
    $subscriber = $subscription->getSubscriber();
        
		$sql_data_array = array(
		  'orders_id' => $insert_id,
		  'subscription_id' => $_SESSION['paypal']['paymentId'],
		  'payer_id' => $subscriber->getPayerId(),
		  'plan_id' => $subscription->getPlanId(),
		);
		xtc_db_perform('paypal_subscription', $sql_data_array);
  }


	function after_process() {
		unset($_SESSION['paypal']);
		$_SESSION['cart']->plans = array();
	}


  function remove() {
	  parent::remove();

    require_once(DIR_FS_INC.'update_module_configuration.inc.php');
 
    require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'shopping_cart/paypal_plan_cart.php');
    $paypal_plan_cart = new paypal_plan_cart();
    if ($paypal_plan_cart->check() > 0) {
      $paypal_plan_cart->remove();
	    update_module_configuration('shopping_cart');
    }

    require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'xtcPrice/paypal_plan_price.php');
    $paypal_plan_price = new paypal_plan_price();
    if ($paypal_plan_price->check() > 0) {
      $paypal_plan_price->remove();
	    update_module_configuration('xtcPrice');
    }

    require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'checkout/paypal_plan_checkout.php');
    $paypal_plan_checkout = new paypal_plan_checkout();
    if ($paypal_plan_checkout->check() > 0) {
      $paypal_plan_checkout->remove();
	    update_module_configuration('checkout');
    }

    require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'order/paypal_plan_order.php');
    $paypal_plan_order = new paypal_plan_order();
    if ($paypal_plan_order->check() > 0) {
      $paypal_plan_order->remove();
	    update_module_configuration('order');
    }
  }


	function install() {
	  parent::install();

    require_once(DIR_FS_INC.'update_module_configuration.inc.php');

    require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'shopping_cart/paypal_plan_cart.php');
    $paypal_plan_cart = new paypal_plan_cart();
    if ($paypal_plan_cart->check() < 1) {
      $paypal_plan_cart->install();
	    update_module_configuration('shopping_cart');
    }

    require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'xtcPrice/paypal_plan_price.php');
    $paypal_plan_price = new paypal_plan_price();
    if ($paypal_plan_price->check() < 1) {
      $paypal_plan_price->install();
	    update_module_configuration('xtcPrice');
    }

    require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'checkout/paypal_plan_checkout.php');
    $paypal_plan_checkout = new paypal_plan_checkout();
    if ($paypal_plan_checkout->check() < 1) {
      $paypal_plan_checkout->install();
	    update_module_configuration('checkout');
    }

    require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'order/paypal_plan_order.php');
    $paypal_plan_order = new paypal_plan_order();
    if ($paypal_plan_order->check() < 1) {
      $paypal_plan_order->install();
	    update_module_configuration('order');
    }
	}


	function keys() {
		return array('MODULE_PAYMENT_PAYPALSUBSCRIPTION_STATUS', 
		             'MODULE_PAYMENT_PAYPALSUBSCRIPTION_ALLOWED', 
		             'MODULE_PAYMENT_PAYPALSUBSCRIPTION_ZONE',
		             'MODULE_PAYMENT_PAYPALSUBSCRIPTION_SORT_ORDER'
		             );
	}

}
?>