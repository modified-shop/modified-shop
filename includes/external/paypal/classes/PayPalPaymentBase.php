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
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalCommon.php');


class PayPalPaymentBase extends PayPalCommon {


  function __construct() {

  }


  function init($class) {
    global $order;

    $this->code = $class;

    $this->title = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE') : '');
    $this->info = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO') : '');
    $this->description = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION').((defined('_VALID_XTC') && defined('MODULE_PAYMENT_'.strtoupper($this->code).'_LP')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_LP') : '') : '');
    $this->extended_description = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_EXTENDED_DESCRIPTION')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_EXTENDED_DESCRIPTION') : '');
    
    $this->sort_order = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER') : '');
    $this->enabled = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True') ? true : false);
    
    if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS')) {
      $this->order_status_success = (($this->get_config('PAYPAL_ORDER_STATUS_SUCCESS_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_SUCCESS_ID') : DEFAULT_SHIPPING_STATUS_ID);
      $this->order_status_rejected = (($this->get_config('PAYPAL_ORDER_STATUS_REJECTED_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_REJECTED_ID') : DEFAULT_SHIPPING_STATUS_ID);
      $this->order_status_pending = (($this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') : DEFAULT_SHIPPING_STATUS_ID);
      $this->order_status_capture = (($this->get_config('PAYPAL_ORDER_STATUS_CAPTURED_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_CAPTURED_ID') : DEFAULT_SHIPPING_STATUS_ID);
      $this->order_status_tmp = (($this->get_config('PAYPAL_ORDER_STATUS_TMP_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_TMP_ID') : DEFAULT_SHIPPING_STATUS_ID);
      $this->tmpStatus = $this->order_status_tmp;
      $this->tmpOrders = true;
      $this->loglevel = $this->get_config('PAYPAL_LOG_LEVEL');
    
      $payment_sale = array(
        'paypalplus',
        'paypalpluslink',
      );
      $this->transaction_type = $this->get_config('PAYPAL_TRANSACTION_TYPE');
      if (in_array($this->code, $payment_sale)) {
        $this->transaction_type = 'sale';
      }
    }
    
    if (is_object($order)) {
      $this->update_status();
    }
  }


	function update_status() {
		global $order;

		if ($this->enabled == true && ((int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE') > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("SELECT zone_id 
			                               FROM ".TABLE_ZONES_TO_GEO_ZONES." 
			                              WHERE geo_zone_id = '".(int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')."' 
			                                AND zone_country_id = '".$order->billing['country']['id']."' 
			                           ORDER BY zone_id");
			while($check = xtc_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
			}
			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}


	function javascript_validation() {
		return false;
	}


  function selection() {    
    return array(
      'id' => $this->code, 
      'module' => $this->title, 
      'description' => $this->info,
    );
  }


	function payment_action() {
		return;
	}


  function pre_confirmation_check() {
    return false;
  }


  function confirmation() {
    return false;
  }


  function process_button() {
    return false;
  }


  function before_process() {
    return false;
  }


  function after_process() {
    global $insert_id;

    $check_query = xtc_db_query("SELECT orders_status
                                   FROM ".TABLE_ORDERS." 
                                  WHERE orders_id = '".(int)$insert_id."'");
    $check = xtc_db_fetch_array($check_query);
    
    if ($check['orders_status'] != $this->order_status_pending) {
      $this->update_order('', $this->order_status_pending, $insert_id);    
    }
		unset($_SESSION['paypal']);
  }


  function success($orders_id = '') {
    global $last_order;
    
    if ($orders_id == '') {
      $orders_id = $last_order;
    }
    
    // include needed function
    if (!function_exists('xtc_date_short')) {
      require_once(DIR_FS_INC.'xtc_date_short.inc.php');
    }

    $payment = $this->get_order_details($orders_id);
    
    if (isset($payment['instruction'])) {  
      $fields = array(
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_AMOUNT,
          'field' => $payment['instruction']['amount']['total'].' '.$payment['instruction']['amount']['currency'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_REFERENCE,
          'field' => $payment['instruction']['reference'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_PAYDATE,
          'field' => $payment['instruction']['date'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_ACCOUNT,
          'field' => $payment['instruction']['bank']['name'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_HOLDER,
          'field' => $payment['instruction']['bank']['holder'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_IBAN,
          'field' => $payment['instruction']['bank']['iban'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_BIC,
          'field' => $payment['instruction']['bank']['bic'],
        ),
      );
    
      $success = array(
        array ('title' => sprintf(TEXT_PAYPAL_INSTRUCTIONS_CHECKOUT, $payment['instruction']['amount']['total'].' '.$payment['instruction']['amount']['currency'], xtc_date_short($payment['instruction']['date'])),
               'class' => $this->code,
               'fields' => $fields
               ),
        );
    
      return $success;
    }
  }


	function admin_order($oID) {
		return false;
	}


	function get_error() {
		$error = false;
		if (isset($_GET['payment_error']) && $_GET['payment_error'] != '') {
			$error = array(
			  'title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_HEADING'),
        'error' => decode_htmlentities(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_MESSAGE'))
      );
		}
		return $error;
	}


	function output_error() {
		return false;
	}


  function check() {
    if(!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM ".TABLE_CONFIGURATION." 
                                    WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($this->code)."_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }


  function checkout_button() {
    global $PHP_SELF;
    
    if ($this->enabled === true
        && $_SESSION['allow_checkout'] == 'true'
        && $_SESSION['cart']->show_total() > 0
        ) 
    {
      $unallowed_modules = explode(',', $_SESSION['customers_status']['customers_status_payment_unallowed']);
      if (!in_array($this->code, $unallowed_modules)) {
        $image = ((strtoupper($_SESSION['language_code']) == 'DE') ? 'epaypal_de.gif' : 'epaypal_en.gif');
        $image = xtc_image_button(DIR_WS_ICONS.$image, '', 'id="paypalcartbutton"');
        $checkout_button = '<a href="'.xtc_href_link(basename($PHP_SELF), 'action=paypal_cart_checkout').'">'.$image.'</a>';

        return $checkout_button;
      }
    }
  }


  function product_checkout_button() {    
    if ($this->enabled === true) {
      $unallowed_modules = explode(',', $_SESSION['customers_status']['customers_status_payment_unallowed']);
      if (!in_array($this->code, $unallowed_modules)) {
        $image = ((strtoupper($_SESSION['language_code']) == 'DE') ? 'epaypal_de.gif' : 'epaypal_en.gif');
        $checkout_button = xtc_image_submit(DIR_WS_BASE.DIR_WS_ICONS.$image, IMAGE_BUTTON_IN_CART, 'id="paypalcartexpress" name="paypalcartexpress"');

        return $checkout_button;
      }
    }
  }


  function create_paypal_link($orders_id = '', $cleanlink = false) {
    global $last_order, $PHP_SELF;
    
    if ($orders_id == '') {
      $orders_id = $last_order;
    }
        
    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_PAYPAL_PAYMENT."
                                  WHERE orders_id = '".(int)$orders_id."'");
    
    if (xtc_db_num_rows($check_query) < 1) {
      require_once (DIR_WS_CLASSES . 'order.php');
      $order = new order($orders_id);
      $hash = md5($order->customer['email_address']);
      if (defined('RUN_MODE_ADMIN')) {
        $link = xtc_catalog_href_link('callback/paypal/'.$this->code.'.php', 'oID='.$orders_id.'&key='.$hash, 'SSL');
      } else {
        $link = xtc_href_link('callback/paypal/'.$this->code.'.php', 'oID='.$orders_id.'&key='.$hash, 'SSL');
      }
      
      if ($cleanlink === true) {
        return $link;
      }
      
      $image = ((strtoupper($_SESSION['language_code']) == 'DE') ? 'epaypal_de.gif' : 'epaypal_en.gif');
      if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS) {
        $image = xtc_image_button(DIR_WS_ICONS.$image, '', 'id="paypalcartbutton"');
      } else {
        $image = '<img src="'.((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.DIR_WS_ICONS.$image.'" id="paypalcartbutton" />';
      }
      $checkout_button = '<a href="'.$link.'">'.$image.'</a>';

      return $checkout_button;
    }
  }


  function update_order($comment, $orders_status, $orders_id) {
    
    $order_history_data = array(
      'orders_id' => (int)$orders_id,
      'orders_status_id' => (int)$orders_status,
      'date_added' => 'now()',
      'customer_notified' => '0',
      'comments' => $comment,
    );
    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_history_data);
    
    xtc_db_query("UPDATE ".TABLE_ORDERS."
                     SET orders_status = '".(int)$orders_status."', 
                         last_modified = now() 
                   WHERE orders_id = '".(int)$orders_id."'");   
  }
  

  function remove_order($orders_id) {
  
    $check_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ORDERS." 
                                  WHERE orders_id = '".(int)$orders_id."'");
    if (xtc_db_num_rows($check_query) > 0) {
      $check = xtc_db_fetch_array($check_query);
      if ($_SESSION['customer_id'] == $check['customers_id']) {
        require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
        xtc_remove_order((int)$orders_id, ((STOCK_LIMITED == 'true') ? 'on' : false));
      }
    }
  }


	function install() {
	
		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_STATUS', 'True', '6', '1', NULL, now(), '', 'xtc_cfg_select_option(array(\'True\', \'False\'),' )");
		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_SORT_ORDER', '0', '6', '2', NULL, now(), '', '')");
		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ALLOWED', '', '6', '3', NULL, now(), '', '')");
		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ZONE', '0', '6', '4', NULL, now(), 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(')");
		
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_PAYMENT." ( 
                    paypal_id int(11) NOT NULL auto_increment, 
                    orders_id int(11) NOT NULL default '0', 
                    payment_id varchar(64) NOT NULL default '', 
                    payer_id varchar(64) NOT NULL default '', 
                    PRIMARY KEY (paypal_id), 
                    KEY idx_orders_id (orders_id)
                  ) ENGINE = MYISAM;");
		
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_CONFIG." (
                    config_key varchar(128) NOT NULL,
                    config_value text NOT NULL,
                    KEY idx_config_key (config_key)
                  ) ENGINE = MYISAM;");

    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_IPN." (
                    orders_id int(11) NOT NULL,
                    transaction_id varchar(64) NOT NULL default '',
                    payment_status varchar(64) NOT NULL default '',
                    KEY idx_orders_id (orders_id)
                  ) ENGINE = MYISAM;");
	
	  $admin_access_array = array(
	    'paypal_config',
	    'paypal_module',
	    'paypal_payment',
	    'paypal_profile',
	    'paypal_webhook',
	  );
	  
    $admin_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ADMIN_ACCESS."
                                  LIMIT 1");
    $admin = xtc_db_fetch_array($admin_query);
    foreach ($admin_access_array as $admin_access) {
      if (!isset($admin[$admin_access])) {
        xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD `".$admin_access."` INT(1) DEFAULT '0' NOT NULL");
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '1' LIMIT 1");        
        if ($_SESSION['customer_id'] > 1) {
          xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '".$_SESSION['customer_id']."' LIMIT 1") ;
        }
      }
    }
    
    $status_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_ORDERS_STATUS."
                                   LIMIT 1");
    $status = xtc_db_fetch_array($status_query);
    if (!isset($status['sort_order'])) {
      xtc_db_query("ALTER TABLE ".TABLE_ORDERS_STATUS." ADD `sort_order` int(11) NOT NULL DEFAULT '0'");
    }
	  
	}


	function remove() {
	
	  $admin_access_array = array(
	    'paypal_config',
	    'paypal_module',
	    'paypal_payment',
	    'paypal_profile',
	    'paypal_webhook',
	  );

		$check_query = xtc_db_query("SELECT configuration_key 
		                               FROM ".TABLE_CONFIGURATION." 
		                              WHERE configuration_key LIKE 'MODULE_PAYMENT_PAYPAL%_STATUS'");
		if (xtc_db_num_rows($check_query) == 1) {			
			xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_PAYMENT);
			xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_CONFIG);
			xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_IPN);


	  
      $admin_query = xtc_db_query("SELECT * 
                                     FROM ".TABLE_ADMIN_ACCESS."
                                    LIMIT 1");
      $admin = xtc_db_fetch_array($admin_query);
      foreach ($admin_access_array as $admin_access) {
        if (isset($admin[$admin_access])) {
          xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP COLUMN `".$admin_access."`");
        }
      }
		}

		xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE_PAYMENT_".strtoupper($this->code)."\_%'");
	}


  function status_install() {

		// install order status
		$stati = array('PAYPAL_INST_ORDER_STATUS_TMP_NAME' => 'PAYPAL_ORDER_STATUS_TMP_ID',
                   'PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME' => 'PAYPAL_ORDER_STATUS_SUCCESS_ID',
                   'PAYPAL_INST_ORDER_STATUS_PENDING_NAME' => 'PAYPAL_ORDER_STATUS_PENDING_ID',
                   'PAYPAL_INST_ORDER_STATUS_CAPTURED_NAME' => 'PAYPAL_ORDER_STATUS_CAPTURED_ID',
                   'PAYPAL_INST_ORDER_STATUS_REFUNDED_NAME' => 'PAYPAL_ORDER_STATUS_REFUNDED_ID',
                   'PAYPAL_INST_ORDER_STATUS_REJECTED_NAME' => 'PAYPAL_ORDER_STATUS_REJECTED_ID');
		foreach($stati as $statusname => $statusid) {
			$languages_query = xtc_db_query("SELECT * 
			                                   FROM " . TABLE_LANGUAGES . " 
			                               ORDER BY sort_order");
			while($languages = xtc_db_fetch_array($languages_query)) {
				if (file_exists(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal_config.php')) {
					include(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal_config.php');
				}
				if ($$statusname != '') {
					$check_query = xtc_db_query("SELECT orders_status_id 
					                               FROM " . TABLE_ORDERS_STATUS . " 
					                              WHERE orders_status_name = '" .xtc_db_input($$statusname). "' 
					                                AND language_id = '".(int)$languages['languages_id']."' 
					                              LIMIT 1");
					$status = xtc_db_fetch_array($check_query);
					if (xtc_db_num_rows($check_query) < 1 || ($$statusid && $status['orders_status_id'] != $$statusid) ) {
						if (!$$statusid) {
							$status_query = xtc_db_query("SELECT max(orders_status_id) as status_id FROM " . TABLE_ORDERS_STATUS);
							$status = xtc_db_fetch_array($status_query);
							$$statusid = $status['status_id'] + 1;
						}
						$check_query = xtc_db_query("SELECT orders_status_id 
						                               FROM " . TABLE_ORDERS_STATUS . " 
						                              WHERE orders_status_id = '".(int)$$statusid ."' 
						                                AND language_id='".(int)$languages['languages_id']."'");
						if (xtc_db_num_rows($check_query) < 1) {
						  $sql_data_array = array(
						    'orders_status_id' => (int)$$statusid,
						    'language_id' => (int)$languages['languages_id'],
						    'orders_status_name' => $$statusname,
						  );
              xtc_db_perform(TABLE_ORDERS_STATUS, $sql_data_array);
              $sql_data_array = array(
                array(
                  'config_key' => $statusid,
                  'config_value' => (int)$$statusid,
                )
              );
              $this->save_config($sql_data_array);
						}
					} else {
						$$statusid = $status['orders_status_id'];
					}
				}
			}
		}		
	}
    
}
?>