<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class paypal_plan_cart {  //Important same name as filename
    
    //--- BEGIN DEFAULT CLASS METHODS ---//
    function __construct()
    {
        $this->code = 'paypal_plan_cart'; //Important same name as class name
        $this->title = 'PayPal Plan';
        $this->description = 'PayPal Plan';
        $this->name = 'MODULE_SHOPPING_CART_'.strtoupper($this->code);
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
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('".$this->name."_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$this->name."_SORT_ORDER', '10','6', '2', now())");
        
        xtc_db_query("CREATE TABLE IF NOT EXISTS customers_basket_plans (
                        customers_basket_plans_id INT(11) NOT NULL AUTO_INCREMENT,
                        customers_id int(11) NOT NULL,
                        products_id tinytext NOT NULL,
                        plans_id varchar(64) NOT NULL,
                        PRIMARY KEY (customers_basket_plans_id),
                        KEY idx_customers_id (customers_id)
                      );");

        xtc_db_query("CREATE TABLE IF NOT EXISTS customers_wishlist_plans (
                        customers_wishlist_plans_id INT(11) NOT NULL AUTO_INCREMENT,
                        customers_id int(11) NOT NULL,
                        products_id tinytext NOT NULL,
                        plans_id varchar(64) NOT NULL,
                        PRIMARY KEY (customers_wishlist_plans_id),
                        KEY idx_customers_id (customers_id)
                      );");
                      
        xtc_db_query("CREATE TABLE IF NOT EXISTS paypal_plan (
                        plan_id varchar(64) NOT NULL,
                        products_id int(11) NOT NULL,
                        plan_status int(1) NOT NULL,
                        plan_name varchar(256) NOT NULL,
                        plan_interval varchar(16) NOT NULL,
                        plan_cycle int(11) NOT NULL,
                        plan_price double(15,4) NOT NULL,
                        plan_fee double(15,4) NOT NULL,
                        plan_tax int(11) NOT NULL,
                        plan_tax_included int(1) NOT NULL,
                        PRIMARY KEY (plan_id),
                        KEY idx_products_id (products_id)
                      );");

        xtc_db_query("CREATE TABLE IF NOT EXISTS paypal_subscription (
                        orders_id int(11) NOT NULL,
                        subscription_id varchar(64) NOT NULL,
                        payer_id varchar(64) NOT NULL,
                        plan_id varchar(64) NOT NULL,
                        PRIMARY KEY (orders_id)
                      );");

  	    require_once(DIR_FS_INC.'update_module_configuration.inc.php');
  
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

        require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'checkout/paypal_plan_checkout.php');
        $paypal_plan_checkout = new paypal_plan_checkout();
        if ($paypal_plan_checkout->check() < 1) {
          $paypal_plan_checkout->install();
    	    update_module_configuration('checkout');
        }

        require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/paypalsubscription.php');
        $paypalsubscription = new paypalsubscription();
        if ($paypalsubscription->check() < 1) {
          $paypalsubscription->install();
    	    update_module_configuration('payment');
        }
    }

    function remove() {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '".$this->name."_%'");

  	    require_once(DIR_FS_INC.'update_module_configuration.inc.php');

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

        require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'checkout/paypal_plan_checkout.php');
        $paypal_plan_checkout = new paypal_plan_checkout();
        if ($paypal_plan_checkout->check() > 0) {
          $paypal_plan_checkout->remove();
    	    update_module_configuration('checkout');
        }

        require_once(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/paypalsubscription.php');
        $paypalsubscription = new paypalsubscription();
        if ($paypalsubscription->check() > 0) {
          $paypalsubscription->remove();
    	    update_module_configuration('payment');
        }
    }
    
    
    //--- BEGIN CUSTOM  CLASS METHODS ---//
    function restore_contents_products_db($sql_data_array,$products_id,$table_basket,$qty,$type) {
      if (property_exists($_SESSION[$type], 'plans')) {
        if (isset($_SESSION[$type]->plans[$products_id])) {
          xtc_db_query("DELETE FROM customers_".(($type == 'cart') ? 'basket' : 'wishlist')."_plans
                              WHERE customers_id = '".(int)$_SESSION['customer_id']."' 
                                AND products_id = '".xtc_db_input($products_id)."'");
                                
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'products_id' => $products_id,
            'plans_id' => $_SESSION[$type]->plans[$products_id],
          );
          xtc_db_perform('customers_'.(($type == 'cart') ? 'basket' : 'wishlist').'_plans', $sql_data_array);
        }
      }
      
      return $sql_data_array;
    }
    
    function restore_contents_products_session($products,$table_basket,$type) {
      $check_query = xtc_db_query("SELECT *
                                     FROM customers_".(($type == 'cart') ? 'basket' : 'wishlist')."_plans
                                    WHERE customers_id = '".(int)$_SESSION['customer_id']."' 
                                      AND products_id = '".xtc_db_input($products['products_id'])."'");
      if (xtc_db_num_rows($check_query) > 0) {
        if (!property_exists($_SESSION[$type], 'plans')) {
          $_SESSION[$type]->plans = array();
        }
        while ($check = xtc_db_fetch_array($check_query)) {
          $_SESSION[$type]->plans[$products['products_id']] = $check['plans_id'];
        }
      }
    }
    
    function add_cart_products_session($products_id, $type, $qty, $attributes) {
      if (isset($_POST['plan_id'])) {
        if (!property_exists($_SESSION[$type], 'plans')) {
          $_SESSION[$type]->plans = array();
        }
        $_SESSION[$type]->plans[$products_id] = $_POST['plan_id'];

        // insert into database
        if (isset($_SESSION['customer_id'])) {
          xtc_db_query("DELETE FROM customers_".(($type == 'cart') ? 'basket' : 'wishlist')."_plans
                              WHERE customers_id = '".(int)$_SESSION['customer_id']."' 
                                AND products_id = '".xtc_db_input($products_id)."'");
                                
          $sql_data_array = array(
            'customers_id' => (int)$_SESSION['customer_id'],
            'products_id' => $products_id,
            'plans_id' => $_POST['plan_id'],
          );
          xtc_db_perform('customers_'.(($type == 'cart') ? 'basket' : 'wishlist').'_plans', $sql_data_array);
        }
      }
    }
    
    function remove_custom_inputs_session($products_id, $type) {
      if (property_exists($_SESSION[$type], 'plans')
          && isset($_SESSION[$type]->plans[$products_id])
          )
      {
        unset($_SESSION[$type]->plans[$products_id]);
      }

      // remove from database
      if (isset($_SESSION['customer_id'])) { 
        xtc_db_query("DELETE FROM customers_".(($type == 'cart') ? 'basket' : 'wishlist')."_plans
                            WHERE customers_id = '".(int)$_SESSION['customer_id']."' 
                              AND products_id = '".xtc_db_input($products_id)."'");
      }
    }
    
    function update_cart_products_session($products_id, $type, $quantity, $attributes) {
      global $messageStack;
      
      $check_query = xtc_db_query("SELECT *
                                     FROM customers_".(($type == 'cart') ? 'basket' : 'wishlist')."_plans
                                    WHERE customers_id = '".(int)$_SESSION['customer_id']."' 
                                      AND products_id = '".xtc_db_input($products_id)."'");
      if (xtc_db_num_rows($check_query) > 0
          && $quantity > 1
          )
      {
        require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
        $messageStack->add_session('shopping_cart', TEXT_PAYPAL_ERROR_MAX_PRODUCTS);
        
        $_SESSION[$type]->contents[$products_id]['qty'] = 1;
        if (isset($_SESSION['customer_id'])) { 
          $sql_data_array = array('customers_basket_quantity' => 1);
          xtc_db_perform('customers_'.(($type == 'cart') ? 'basket' : 'wishlist').'_plans', $sql_data_array, 'update', "customers_id = '".(int)$_SESSION['customer_id']."' AND products_id = '".xtc_db_input($products_id)."'");
        }
      }
    }
    
}