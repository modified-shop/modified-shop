<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneModified.php');
require_once (DIR_FS_EXTERNAL.'payone/lang/'.$_SESSION['language'].'.php');

class PayonePayment {
	var $code, $title, $description, $enabled;
	var $form_action_url;
	var $tmpOrders = true;
	var $tmpStatus;
	var $payone;
	var $config;
	var $global_config;
	var $pg_config;
	
	function PayonePayment() {
		global $order;

		$this->payone = new PayoneModified();
		$this->config = $this->payone->getConfig();
		$this->pg_config = $this->config[$this->_getActiveGenreIdentifier()];
		$this->global_config = $this->pg_config['global_override'] == 'true' ? $this->pg_config['global'] : $this->config['global'];
		$this->tmpStatus = $this->config['orders_status']['tmp'];
    
		!empty($this->code) OR $this->code = 'payone';
		$this->title = @constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE'); 
		$this->description = @constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION').((defined('_VALID_XTC')) ? MODULE_PAYMENT_PAYONE_LP : ''); 
		$this->sort_order = @constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER');
		$this->enabled = ((@constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True') ? true : false);
		$this->info = @constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO'); 
		$this->order_status = $this->config['orders_status']['paid'];

		if (is_object($order)) {
			$this->update_status();
		}
	}
	
	function _updateOrdersStatus($orders_id, $txid, $txaction, $comment = '') {
		if (in_array($txaction, $this->payone->getStatusNames())) {
		  $sql_data_orders_array = array('orders_status' => $this->config['orders_status'][$txaction],
		                                 'last_modified' => 'now()');
		  xtc_db_perform(TABLE_ORDERS, $sql_data_orders_array, 'update', "orders_id = '".(int)$orders_id."'");
		                            
      $sql_data_array = array('orders_id' => (int)$orders_id,
                              'orders_status_id' => $this->config['orders_status'][$txaction],
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => xtc_db_input($comment),
                              'comments_sent' => '0'
                              );
      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		}
	}

	function _checkRequirements() {
		$out = @constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SYSTEM_REQUIREMENTS').':<br>';
		if (defined('DIR_WS_ADMIN') && strpos($_SERVER['REQUEST_URI'], constant('DIR_WS_ADMIN')) !== false) {
			$has_curl = in_array('curl', get_loaded_extensions());
			$out .= "cURL: ". ($has_curl ? '<span style="color:green">'.@constant('MODULE_PAYMENT_'.strtoupper($this->code).'_OK').'</span>' : '<span style="color:red">'.@constant('MODULE_PAYMENT_'.strtoupper($this->code).'_MISSING').'</span><br>');
		}
		return $out;		
	}
	
	function update_status() {
		global $order;
    
    $forbidden_array = array('selfpickup_selfpickup' => 'payone_cod');
    if (isset($forbidden_array[$_SESSION['shipping']['id']]) &&  $forbidden_array[$_SESSION['shipping']['id']] == $this->code) {
      $this->enabled = false;
    }
		
		if (($this->enabled == true) && ((int) @constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE') > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("SELECT zone_id 
			                               FROM ".TABLE_ZONES_TO_GEO_ZONES." 
			                              WHERE geo_zone_id = '".@constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')."' 
			                                AND zone_country_id = '".$order->billing['country']['id']."' 
			                           ORDER BY zone_id");
			while ($check = xtc_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				}
				elseif ($check['zone_id'] == $order->billing['zone_id']) {
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

	function _getActiveGenreIdentifier() {
		$available_genres = $this->payone->getAvailablePaymentGenres();
		$active_genre = false;
		foreach($available_genres as $identifier => $ag) {
			if ($ag['genre'] == $this->payone_genre) {
				$active_genre = $identifier;
			}
		}
		return $active_genre;
	}

	function _addressesAreValidated() {
		if ($this->config['address_check']['active'] == 'true') {
		  $addresses_are_validated = ($this->payone->getAddressHash($_SESSION['billto']) == $_SESSION['payone_ac_billing_hash'] 
		                              && $this->payone->getAddressHash($_SESSION['sendto']) == $_SESSION['payone_ac_delivery_hash']);
		}
		else {
			// address check is inactive, treat addresses as validated
			$addresses_are_validated = true;
		}
		return $addresses_are_validated;
	}

  function _credit_risk_check() {
    global $smarty, $breadcrumb, $request_type;
    
    $active_genre = $this->_getActiveGenreIdentifier();
        
    require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneCreditRisk.php');
    $payone_cr = new PayoneCreditRisk($this->code);
  
    // do check
    if (isset($_POST['p1crcheck']) || $this->config['credit_risk']['confirmation']['active'] == 'false') {    
      $payone_cr->credit_risk_check();
      
      if ($this->config['credit_risk']['timeofcheck'] == 'after') {
        $paymentgenre_allowed = false;
        foreach($this->config['credit_risk']['checkforgenre'] as $checkforgenre) {
          if ($checkforgenre == $active_genre) {
            $paymentgenre_allowed = $this->config[$active_genre]['allow_'.$_SESSION['payone_cr_result']] == 'true';
            break;
          }
        }
        if ($paymentgenre_allowed == false) {
          $this->payone->log("credit_risk, after-selection mode: fail");
          $_SESSION['payone_error'] = CREDIT_RISK_FAILED;
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
        }
      }
    }
    // show notice for check
    else 
    {
      $main_content = $payone_cr->get_html();
  
      require (DIR_WS_INCLUDES . 'header.php');
      
      $smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
      $smarty->assign('language', $_SESSION['language']);
      $smarty->assign('main_content', $main_content);
      $smarty->caching = 0;
      if (!defined('RM')) $smarty->load_filter('output', 'note');
      $smarty->display(CURRENT_TEMPLATE . '/index.html');

      include ('includes/application_bottom.php');
      exit();		  
    }
  }
  
	function selection() {
		$active_genre = $this->_getActiveGenreIdentifier();
		if ($active_genre === false) {
			return false;
		}
        
    // delete canceled order
    if (isset($_SESSION['tmp_payone_oID']) && is_numeric($_SESSION['tmp_payone_oID'])) {
      $this->_remove_order($_SESSION['tmp_payone_oID']);
    }
    unset($_SESSION['tmp_payone_oID']);
       
		$pg_cart_min = (int)$this->config[$active_genre]['min_cart_value'];
		$pg_cart_max = (int)$this->config[$active_genre]['max_cart_value'];
		$cart_total = $_SESSION['cart']->show_total();
		if ($cart_total < $pg_cart_min || $cart_total > $pg_cart_max) {
			return false;
		}

		// address check
		$_SESSION['payone_ac_billing_hash'] = ((isset($_SESSION['payone_ac_billing_hash'])) ? $_SESSION['payone_ac_billing_hash'] : '');
		$_SESSION['payone_ac_delivery_hash'] = ((isset($_SESSION['payone_ac_delivery_hash'])) ? $_SESSION['payone_ac_delivery_hash'] : '');
		if (!$this->_addressesAreValidated()) {
			if ($cart_total >= $this->config['address_check']['min_cart_value'] && $cart_total <= $this->config['address_check']['max_cart_value']) {
				$check_required = false;

				if ($this->config['address_check']['billing_address'] != 'none' && $_SESSION['payone_ac_billing_hash'] != $this->payone->getAddressHash($_SESSION['billto'])) {
					$check_required = true;
				}

				if ($this->config['address_check']['delivery_address'] != 'none' && $_SESSION['payone_ac_delivery_hash'] != $this->payone->getAddressHash($_SESSION['sendto'])) {
					$check_required = true;
				}

				if ($check_required) {
					$this->payone->log('selection() redirecting customer '.$_SESSION['customer_id'].' to address check');
					
					global $smarty, $breadcrumb, $request_type;
					
          require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneAddressCheck.php');
          $payone_ac = new PayoneAddressCheck();
          $main_content = $payone_ac->get_html();

          require (DIR_WS_INCLUDES . 'header.php');
          
          $smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
          $smarty->assign('language', $_SESSION['language']);
          $smarty->assign('main_content', $main_content);
          $smarty->caching = 0;
          if (!defined('RM')) $smarty->load_filter('output', 'note');
          $smarty->display(CURRENT_TEMPLATE . '/index.html');

          include ('includes/application_bottom.php');
          exit();          
				}
			}
			else {
				// skip address check, assume addresses as validated
				$_SESSION['payone_ac_billing_hash'] = $this->payone->getAddressHash($_SESSION['billto']);
				$_SESSION['payone_ac_delivery_hash'] = $this->payone->getAddressHash($_SESSION['sendto']);
			}
		}

		// credit check
		if ($this->config['credit_risk']['operating_mode'] == 'test' && isset($_GET['resetcr'])) {
			unset($_SESSION['payone_cr_result']);
			unset($_SESSION['payone_cr_hash']);
		}
		
		$_SESSION['payone_cr_result'] = ((isset($_SESSION['payone_cr_result'])) ? $_SESSION['payone_cr_result'] : $this->config['credit_risk']['newclientdefault']);
		if ($this->config['credit_risk']['active'] == 'true' && $this->config['credit_risk']['timeofcheck'] == 'before') {
			$_SESSION['payone_cr_hash'] = ((isset($_SESSION['payone_cr_hash'])) ? $_SESSION['payone_cr_hash'] : '');
			$credit_risk_checked = ($_SESSION['payone_cr_hash'] == $this->payone->getAddressHash($_SESSION['billto']));
			
			if (!$credit_risk_checked && !isset($_GET['p1crskip'])) {
			  $this->_credit_risk_check();
			}
		}
	
		if ($this->config['credit_risk']['active'] == 'true') {
			if ($this->config[$active_genre]['allow_'.$_SESSION['payone_cr_result']] != 'true') {
				// payment genre not allowed with user's credit score
				return false;
			}
		}

		$selection = array(
			'id' => $this->code,
			'module' => $this->title,
			'description' => $this->description,
			'fields' => array(),
		);
		if (method_exists($this, '_paymentDataForm')) {
			$selection['fields'] = $this->_paymentDataForm($active_genre);
		}

    // delete old session
    unset($_SESSION[$this->code]);
		
		return $selection;
	}

	function pre_confirmation_check() {
    // delete tmp order
    if (isset($_SESSION['tmp_payone_oID']) && is_numeric($_SESSION['tmp_payone_oID'])) {
      $this->_remove_order($_SESSION['tmp_payone_oID']);
    }
	  unset($_SESSION['tmp_payone_oID']);
	  unset($_SESSION['tmp_oID']);

		if ($this->config['address_check']['active'] == 'true' && !$this->_addressesAreValidated()) {
			$_SESSION['payone_error'] = 'address_changed';
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
		}

		// credit risk check
		$active_genre = $this->_getActiveGenreIdentifier();
    $credit_risk_checked = ($_SESSION['payone_cr_hash'] == $this->payone->getAddressHash($_SESSION['billto']));
		if (!$credit_risk_checked && in_array($active_genre, $this->config['credit_risk']['checkforgenre']) && $this->config['credit_risk']['active'] == 'true' && $this->config['credit_risk']['timeofcheck'] == 'after') {
      $this->_credit_risk_check();
      if ($this->config[$active_genre]['allow_'.$_SESSION['payone_cr_result']] != 'true') {
        $_SESSION['payone_error'] = CREDIT_RISK_FAILED;
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));				
      }
		}
		return false;
	}

	function confirmation() {
		$confirmation = array(
			'title' => @constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION').' conf_DESC',
		);
		return $confirmation;
	}
	
	function refresh() {
	}

	function process_button() {
		$order = $GLOBALS['order'];
		$pb = '';
		return $pb;
	}
	
	function payment_action() {
	}
  
	function _getInvoicingTransaction($orders_id) {
		global $order;
    
    // first make object
    $this->payone->order = $order;
    $this->payone->invtrans = new Payone_Api_Request_Parameter_Invoicing_Transaction();
    
		$products = $order->products;
		$products_item = $this->payone->_getInvoicingTransaction_products($products);
		
		$totaldata = $order->getTotalData($orders_id);
		$totals_item = $this->payone->_getInvoicingTransaction_totals($totaldata);
				
		return $this->payone->invtrans;
	}

	function before_process() {
		$tmporder_exists = (isset($_SESSION['tmp_oID']) && is_numeric($_SESSION['tmp_oID']));
		if ($this->config['address_check']['active'] == 'true' && !$tmporder_exists && !$this->_addressesAreValidated()) {
			// user changed billto/sendto address since we last checked -> go back to payment selection
			$this->payone->log("address change during checkout detected");
			$_SESSION['payone_error'] = 'address_changed';
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=payone', 'SSL'));
		}
		$returning_ok = !empty($_GET['status']) && !empty($_GET['txid']) && !empty($_GET['userid']);
		$returning_error = !empty($_GET['status']) && !empty($_GET['errorcode']);
		if ($tmporder_exists && $returning_ok) {
			$this->payone->saveTransaction($_SESSION['tmp_oID'], $_GET['status'], $_GET['txid'], $_GET['userid']);
			if (strtoupper($_GET['status']) == 'REDIRECT' && !empty($_GET['redirecturl'])) {
				$this->payone->log("redirecting to ".$_GET['redirecturl']);
				xtc_redirect($_GET['redirecturl']);
			}
		}
		if ($tmporder_exists && $returning_error) {
			$this->payone->log($_GET['status']." for orders_id ".$_SESSION['tmp_oID'].": ".$_GET['errorcode']." - ".$_GET['errormessage']." - ".$_GET['customermessage']);
			$_SESSION['payone_error_message'] = strip_tags($_GET['customermessage']);
			unset($_SESSION['tmp_oID']);
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=payone', 'SSL'));
		}
		return false;
	}

	function after_process() {
	  unset($_SESSION['tmp_payone_oID']);
	}

	function get_error() {
		if (isset($_SESSION['payone_error'])) {
			$error = array('error' => $_SESSION['payone_error']);
			unset($_SESSION['payone_error']);
			return $error;
		}
		if (isset($_GET['payment_error']) && $_GET['payment_error'] = 'payone_error' && isset($_GET['customermessage']) && $_GET['customermessage'] != '') {
		  $error = array('error' => strip_tags(utf8_decode($_GET['customermessage'])));
		  return $error;
		}
		
		return false;
	}

	function check() {
		if (!isset ($this->_check)) {
			$check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_".  strtoupper($this->code) ."_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {
		$config = $this->_configuration();
		$sort_order = 0;
		foreach($config as $key => $data) {
			$install_query = "insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) ".
					"values ('MODULE_PAYMENT_".strtoupper($this->code)."_".$key."', '".$data['configuration_value']."', '6', '".$sort_order."', '".xtc_db_input($data['set_function'])."', '".xtc_db_input($data['use_function'])."', now())";
			xtc_db_query($install_query);
			$sort_order++;
		}
	}

	function remove() {
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
	}

	function keys() {
		$ckeys = array_keys($this->_configuration());
		$keys = array();
		foreach($ckeys as $k) {
			$keys[] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_'.$k;
		}
		return $keys;
	}
	
	function isInstalled() {
		foreach($this->keys() as $key) {
			if (!defined($key)) {
				return false;
			}
		}
		return true;
	}
	
	function _configuration() {
		$config = array(
			'STATUS' => array(
				'configuration_value' => 'True',
				'set_function' => 'xtc_cfg_select_option(array(\'True\', \'False\'), ',
			),
			'ALLOWED' => array(
				'configuration_value' => '',
			),
			'ZONE' => array(
				'configuration_value' => '0',
				'use_function' => 'xtc_get_zone_class_title',
				'set_function' => 'xtc_cfg_pull_down_zone_classes(',
			),
			'SORT_ORDER' => array(
				'configuration_value' => '0',
			),
		);
		
		return $config;
	}

  function _remove_order($order_id) {  
    $check_query = xtc_db_query("SELECT * FROM ".TABLE_ORDERS." WHERE orders_id = '".(int)$order_id."'");
    if (xtc_db_num_rows($check_query) > 0) {
      $check = xtc_db_fetch_array($check_query);
      if ($_SESSION['customer_id'] == $check['customers_id']) {
        require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
        require_once(DIR_FS_INC.'xtc_restock_order.inc.php');
        xtc_remove_order((int)$order_id, true);
      }
    }
  }
  
  function _get_customers_dob($customers_id) {
    $customers_query = xtc_db_query("SELECT customers_dob FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".$customers_id."'");
    $customers = xtc_db_fetch_array($customers_query);
        
    if ($customers['customers_dob'] != '0000-00-00 00:00:00') {
      return date('Ymd', strtotime($customers['customers_dob']));
    }
  }
  
  function _set_customers_standard_params() {
    global $order;

    $this->payone->log("orders:\n".print_r($order, true));

    $this->personal_data->setCustomerid($_SESSION['customer_id']);
    $this->personal_data->setFirstname($order->billing['firstname']);
    $this->personal_data->setLastname($order->billing['lastname']);
    $this->personal_data->setCompany($order->billing['company']);
    $this->personal_data->setStreet($order->billing['street_address']);
    if (method_exists($this->personal_data, setAddressaddition)) {
      $this->personal_data->setAddressaddition($order->billing['suburb']);
    }
    $this->personal_data->setZip($order->billing['postcode']);
    $this->personal_data->setCity($order->billing['city']);
    if (isset($order->billing['country_iso_2'])) {
      $this->personal_data->setCountry($order->billing['country_iso_2']);
      $order->billing['country']['iso_code_2'] = $order->billing['country_iso_2'];
    } else {
      $this->personal_data->setCountry($order->billing['country']['iso_code_2']);
    }
    if (method_exists($this->personal_data, setState) && in_array($order->billing['country']['iso_code_2'], array('US', 'CA'))) {
      $this->personal_data->setState($order->billing['state']);
    }
    if (method_exists($this->personal_data, setVatid)) {
      $this->personal_data->setVatid($_SESSION['customer_vat_id']);
    }
    if (method_exists($this->personal_data, setIp)) {
      $this->personal_data->setIp($_SESSION['tracking']['ip']);
    }
    if (method_exists($this->personal_data, setTelephonenumber)) {
      $this->personal_data->setTelephonenumber($order->customer['telephone']);
    }
    if (method_exists($this->personal_data, setGender)) {
      $this->personal_data->setGender($order->customer['gender']);
    }
    if (method_exists($this->personal_data, setBirthday)) {
      $this->personal_data->setBirthday($this->_get_customers_dob($_SESSION['customer_id']));
    }
    $this->personal_data->setEmail($order->customer['email_address']);
    $this->personal_data->setLanguage($_SESSION['language_code']);
  }
  
  function _set_customers_shipping_params() {
    global $order;

		$this->delivery_data->setShippingFirstname($order->delivery['firstname']);
		$this->delivery_data->setShippingLastname($order->delivery['lastname']);
		$this->delivery_data->setShippingCompany($order->delivery['company']);
		$this->delivery_data->setShippingStreet($order->delivery['street_address']);
    $this->delivery_data->setShippingAddressaddition($order->billing['suburb']);
		$this->delivery_data->setShippingZip($order->delivery['postcode']);
		$this->delivery_data->setShippingCity($order->delivery['city']);
    if (isset($order->billing['country_iso_2'])) {
      $this->delivery_data->setShippingCountry($order->billing['country_iso_2']);
      $order->billing['country']['iso_code_2'] = $order->billing['country_iso_2'];
    } else {
      $this->delivery_data->setShippingCountry($order->billing['country']['iso_code_2']);
    }
    if (in_array($order->billing['country']['iso_code_2'], array('US', 'CA'))) {
      $this->delivery_data->setShippingState($order->billing['state']);
    }
	}
	
	function _standard_parameters($request='') {
		$genre_identifier = $this->_getActiveGenreIdentifier();
		$genre_config = $this->config[$genre_identifier];
		$this->global_config = (($genre_config['global_override'] == 'true') ? $genre_config['global'] : $this->config['global']);
		
		if ($request == '') {
		  $request = $this->global_config['authorization_method'] == 'auth' ? 'authorization' : 'preauthorization';
		}
		
		$standard_parameters = $this->payone->getStandardParameters($request, $this->global_config);
		unset($standard_parameters['responsetype']);
		unset($standard_parameters['successurl']);
		unset($standard_parameters['errorurl']);
		unset($standard_parameters['hash']);
    
    return $standard_parameters;
  }
  
  function _request_parameters($clearingtype) {
	  global $order, $insert_id;

		$request_parameters = array(
			'aid' => $this->global_config['subaccount_id'],
			'key' => $this->global_config['key'],
			'clearingtype' => $clearingtype,
			'reference' => $insert_id,
			'amount' => round($order->info['total'], 2),
			'currency' => $order->info['currency'],
			'personal_data' => $this->personal_data,
			'delivery_data' => $this->delivery_data,
			'payment' => $this->payment_method,
		);
    
    if ($this->global_config['send_cart'] == 'true') {
      $request_parameters['invoicing'] = $this->_getInvoicingTransaction($insert_id);
    }
    
    return $request_parameters;
  }
  
	function _build_service_authentification($type) {
		if ($this->params['request'] == 'authorization') {
			$this->service = $this->builder->buildServicePaymentAuthorize();
			$this->params['request'] = 'authorization';
			$this->request = new Payone_Api_Request_Authorization($this->params);
			$this->payone->log("$type authorize request:\n".print_r($this->request, true));
			$this->response = $this->service->authorize($this->request);
			$this->payone->log("$type authorize response:\n".print_r($this->response, true));
		}
		else { // pre-auth
			$this->service = $this->builder->buildServicePaymentPreauthorize();
			$this->params['request'] = 'preauthorization';
			$this->request = new Payone_Api_Request_Preauthorization($this->params);
			$this->payone->log("$type preauthorize request:\n".print_r($this->request, true));
			$this->response = $this->service->preauthorize($this->request);
			$this->payone->log("$type preauthorize response:\n".print_r($this->response, true));
		}	
	}
	
	function _parse_response_payone_api($redirect=true) {
	  global $insert_id;
	  
		if ($this->response instanceof Payone_Api_Response_Preauthorization_Approved || $this->response instanceof Payone_Api_Response_Authorization_Approved) {
			$sql_data_array = array(
				'bankaccountholder' => $this->response->getClearingBankaccountholder(),
				'bankcountry' => $this->response->getClearingBankcountry(),
				'bankaccount' => $this->response->getClearingBankaccount(),
				'bankcode' => $this->response->getClearingBankcode(),
				'bankiban' => $this->response->getClearingBankiban(),
				'bankbic' => $this->response->getClearingBankbic(),
				'bankcity' => $this->response->getClearingBankcity(),
				'bankname' => $this->response->getClearingBankname(),
				'orders_id' => (int)$insert_id
			);
			xtc_db_perform('payone_clearingdata', $sql_data_array);
		}

		if ($this->response instanceof Payone_Api_Response_Preauthorization_Approved) {
			$this->payone->log("preauthorization approved");
			$this->payone->saveTransaction($insert_id, $this->response->getStatus(), $this->response->getTxid(), $this->response->getUserid());
			$this->_updateOrdersStatus($insert_id, $this->response->getTxid(), strtolower((string)$this->response->getStatus()), COMMENT_PREAUTH_APPROVED);
		}
		elseif ($this->response instanceof Payone_Api_Response_Authorization_Approved) {
			$this->payone->log("authorization approved");
			$this->payone->saveTransaction($insert_id, $this->response->getStatus(), $this->response->getTxid(), $this->response->getUserid());
			$this->_updateOrdersStatus($insert_id, $this->response->getTxid(), strtolower((string)$this->response->getStatus()), COMMENT_AUTH_APPROVED);
		}
		elseif ($this->response instanceof Payone_Api_Response_Authorization_Redirect) {
			$this->payone->log("authorization for order ".$insert_id." initiated, txid = ".$this->response->getTxid());
			if ($this->response->getStatus() == 'REDIRECT') {
				$this->payone->saveTransaction($insert_id, $this->response->getStatus(), $this->response->getTxid(), $this->response->getUserid());
				$this->payone->log("redirecting to payment service");
				$this->_updateOrdersStatus($insert_id, $this->response->getTxid(), strtolower((string)$this->response->getStatus()), COMMENT_REDIRECTION_INITIATED);
				$redirect_url = $this->response->getRedirecturl();
				if ($redirect_url != '') {
				  xtc_redirect($redirect_url);
				}
			}
		}
		elseif ($this->response instanceof Payone_Api_Response_Error) {
			$this->payone->log("authorization for order ".$insert_id." failed, status ".$this->response->getStatus().", code ".$this->response->getErrorcode().", message ".$this->response->getErrormessage());
			$this->_updateOrdersStatus($insert_id, '', strtolower((string)$this->response->getStatus()), COMMENT_ERROR);
			$_SESSION['payone_error'] = $this->response->getCustomermessage();
			$this->_remove_order($insert_id);
			if ($_SESSION[$this->code]['installment_type'] == 'klarna') {
			  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true&payment_error='.$this->code));
			} else {
			  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code));
			}
		}
		else {
			die('unhandled response type');
		}
  }	
	
}
?>