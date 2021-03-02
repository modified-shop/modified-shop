<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class paypal_plan_checkout {  //Important same name as filename
  
    //--- BEGIN DEFAULT CLASS METHODS ---//
    function __construct()
    {
        $this->code = 'paypal_plan_checkout'; //Important same name as class name
        $this->title = 'PayPal Plan';
        $this->description = 'PayPal Plan';
        $this->name = 'MODULE_CHECKOUT_'.strtoupper($this->code);
        $this->enabled = defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true' ? true : false;
        $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : '';        
    }
    
    function check() {
        if (!isset($this->_check)) {
          $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '".$this->name."_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }
    
    function keys() {
        define($this->name.'_STATUS_TITLE', TEXT_DEFAULT_STATUS_TITLE);
        define($this->name.'_STATUS_DESC', TEXT_DEFAULT_STATUS_DESC);
        define($this->name.'_SORT_ORDER_TITLE', TEXT_DEFAULT_SORT_ORDER_TITLE);
        define($this->name.'_SORT_ORDER_DESC', TEXT_DEFAULT_SORT_ORDER_DESC);
        
        return array(
            $this->name.'_STATUS', 
            $this->name.'_SORT_ORDER'
        );
    }

    function install() {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$this->name."_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$this->name."_SORT_ORDER', '10','6', '2', now())");

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

        require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'order/paypal_plan_order.php');
        $paypal_plan_order = new paypal_plan_order();
        if ($paypal_plan_order->check() < 1) {
          $paypal_plan_order->install();
    	    update_module_configuration('order');
        }

        require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/paypalsubscription.php');
        $paypalsubscription = new paypalsubscription();
        if ($paypalsubscription->check() < 1) {
          $paypalsubscription->install();
    	    update_module_configuration('payment');
        }
    }

    function remove() {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE '".$this->name."_%'");

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

        require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'order/paypal_plan_order.php');
        $paypal_plan_order = new paypal_plan_order();
        if ($paypal_plan_order->check() > 0) {
          $paypal_plan_order->remove();
    	    update_module_configuration('order');
        }

        require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/paypalsubscription.php');
        $paypalsubscription = new paypalsubscription();
        if ($paypalsubscription->check() > 0) {
          $paypalsubscription->remove();
    	    update_module_configuration('payment');
        }
    }
    
    
    //--- BEGIN CUSTOM  CLASS METHODS ---//
    function payment_modules($modules) {      
      if (isset($_SESSION['cart']->plans) 
          && is_array($_SESSION['cart']->plans)
          && count($_SESSION['cart']->plans) > 0
          )
      {
        foreach ($_SESSION['cart']->contents as $products_id => $data) {
          if (array_key_exists($products_id, $_SESSION['cart']->plans)) {
            $modules = array('paypalsubscription.php');
          }
        }
      } elseif (in_array('paypalsubscription.php', $modules)) {
        $key = array_search('paypalsubscription.php', $modules);
        if ($key !== false) {
          unset($modules[$key]);
          $modules = array_values($modules);
        }
      }
      
      return $modules;
    }
}