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

require_once (DIR_FS_EXTERNAL.'payone/classes/PayonePayment.php');

class payone_invoice extends PayonePayment {
	var $payone_genre = 'accountbased';

	function __construct() {
		$this->code = 'payone_invoice';
		parent::__construct();
		$this->form_action_url = '';

		$this->invoicetypes = array(
			'payolution_invoice' => 'PYV',
		);
	}

	function _paymentDataFormProcess($active_genre_identifier) {
	  global $order;
	  
	  $payment_smarty = new Smarty();
	  $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';

		$error = parent::get_error();
		if ($error != '') {
		  $payment_smarty->assign('error', $error['error']);
		}
    
    $genre_config = $this->config[$active_genre_identifier];
    $global_config = $genre_config['global_override'] == 'true' ? $genre_config['global'] : $this->config['global'];
        
    foreach ($genre_config['types'] as $type_name => $type_config) {
      if ($type_config['active'] == 'true') {
        if ($type_name == 'payolution_invoice') {
          if ($order->billing['company'] != '' || $order->customer['company'] != '') {
            $required_fields = array(
              'customers_telephone' => $_SESSION[$this->code]['invoice_customers_telephone'],
              'company_uid' => $_SESSION[$this->code]['invoice_company_uid'],
              'company_trade_registry_number' => $_SESSION[$this->code]['invoice_company_trade_registry_number'],
              'company_register_key' => $_SESSION[$this->code]['invoice_company_register_key'],
            );
          } else {
            $required_fields = array(
              'customers_dob' => $_SESSION[$this->code]['invoice_customers_dob'], 
              'customers_telephone' => $_SESSION[$this->code]['invoice_customers_telephone'],
            );
          }
          $payment_smarty->assign('required_fields', $required_fields);                        
          $payment_smarty->assign('confirm_text', TEXT_PAYOLUTION_CONFIRM);
        }
      }
    }
		
    $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
        
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_installment_form.html');
		
		return $module_form;
	}

	function selection() {
		if ($this->pg_config['types']['invoice']['active'] == 'true'
		    || $this->pg_config['types']['payolution_invoice']['active'] == 'true'
		    ) 
		{
			$selection = parent::selection();
		} else {
			$selection = false;
		}

		return $selection;
	}

	function pre_confirmation_check() {
	  global $order;

		parent::pre_confirmation_check();

    $_SESSION[$this->code]['invoice_type'] = 'invoice';
    if ($this->pg_config['types']['payolution_invoice']['active'] == 'true') {
      $_SESSION[$this->code]['invoice_type'] = 'payolution_invoice';
    }

		if ($_SESSION['sendto'] != $_SESSION['billto']) {
			$_SESSION['payone_error'] = ADDRESSES_MUST_BE_EQUAL; 
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
		}
	}

	function confirmation() {
    $confirmation = array('title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE'),
                          'fields' => '');
		return $confirmation;
	}

	function payment_action() {
	  global $order, $insert_id;
    
    if (!isset($insert_id) || $insert_id == '') {
		  $insert_id = $_SESSION['tmp_oID'];
		}

		$this->payone->log("(pre-)authorizing $this->code payment");
		$standard_parameters = parent::_standard_parameters();

		$this->personal_data = new Payone_Api_Request_Parameter_Authorization_PersonalData();
		parent::_set_customers_standard_params();

		$this->delivery_data = new Payone_Api_Request_Parameter_Authorization_DeliveryData();
		parent::_set_customers_shipping_params();

		$this->payment_method = array();
		
		if ($_SESSION[$this->code]['invoice_type'] == 'invoice') {
      $request_parameters = parent::_request_parameters('rec');
      unset($request_parameters['payment']);
      $request_parameters['invoicing'] = $this->_getInvoicingTransaction($insert_id);

      $this->params = array_merge($standard_parameters, $request_parameters);
      $this->builder = new Payone_Builder($this->payone->getPayoneConfig());
    
      parent::_build_service_authentification('rec');
    } else {
  		$standard_parameters = parent::_standard_parameters('preauthorization');
      
      if (isset($_SESSION[$this->code]['invoice_customers_dob'])) {
        $this->personal_data->setBirthday(xtc_date_raw($_SESSION[$this->code]['invoice_customers_dob']));
      }
      $this->personal_data->setTelephonenumber($_SESSION[$this->code]['invoice_customers_telephone']);
    
      $this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_Financing();
      $this->payment_method->setSuccessurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PROCESS.'?'.xtc_session_name().'='.xtc_session_id());
      $this->payment_method->setBackurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id());
      $this->payment_method->setErrorurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&payment_error='.$this->code);

      // set order_id for deleting canceld order
      $_SESSION['tmp_payone_oID'] = $_SESSION['tmp_oID'];
    
      $financingtype = $this->invoicetypes[$_SESSION[$this->code]['invoice_type']];
      $this->payment_method->setFinancingtype($financingtype);

      $paydata_item = array(
        array('key' => 'b2b', 'data' => (($order->billing['company'] != '' || $order->customer['company'] != '') ? 'yes' : 'no')),
        array('key' => 'company_uid', 'data' => $_SESSION[$this->code]['invoice_company_uid']),
        array('key' => 'company_trade_registry_number', 'data' => $_SESSION[$this->code]['invoice_company_trade_registry_number']),
        array('key' => 'company_register_key', 'data' => $_SESSION[$this->code]['invoice_company_register_key']),
      );
      $paydata = new Payone_Api_Request_Parameter_Paydata_Paydata();
      foreach ($paydata_item as $item) {
        $paydata->addItem(
          new Payone_Api_Request_Parameter_Paydata_DataItem($item)
        );
      }
      $this->payment_method->setPaydata($paydata);

      $request_parameters = parent::_request_parameters('fnc');
      $this->params = array_merge($standard_parameters, $request_parameters);
            
      $this->builder = new Payone_Builder($this->payone->getPayoneConfig());
        
      parent::_build_service_authentification('fnc');
    }
    parent::_parse_response_payone_api();

		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
	}

	function process_button() {
	  if ($_SESSION[$this->code]['invoice_type'] == 'payolution_invoice') {
      $active_genre = $this->_getActiveGenreIdentifier();
      if ($active_genre === false) {
        return false;
      }
          
      return $this->_paymentDataFormProcess($active_genre);
    }
	}

	function before_process() {
		parent::before_process();    

    $valid_request = array(
      'customers_dob', 
      'customers_telephone', 
      'conditions',
      'company_uid', 
      'company_trade_registry_number', 
      'company_register_key',
    );
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		  foreach ($valid_request as $key) {
		    if (isset($_POST[$key])) {
		      $_SESSION[$this->code]['invoice_'.$key] = $_POST[$key];
		    }
		  }
		}
		
	  if ($_SESSION[$this->code]['invoice_type'] == 'payolution_invoice') {
		  //check
		  if (isset($_SESSION[$this->code]['invoice_customers_dob'])) {
        if (is_numeric(xtc_date_raw($_SESSION[$this->code]['invoice_customers_dob'])) == false || (@checkdate(substr(xtc_date_raw($_SESSION[$this->code]['invoice_customers_dob']), 4, 2), substr(xtc_date_raw($_SESSION[$this->code]['invoice_customers_dob']), 6, 2), substr(xtc_date_raw($_SESSION[$this->code]['invoice_customers_dob']), 0, 4)) == false)) {
          $_SESSION['payone_error'] = ENTRY_DATE_OF_BIRTH_ERROR;
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true&payment_error='.$this->code, 'SSL', true));		
        }
      }
      if (strlen($_SESSION[$this->code]['invoice_customers_telephone']) < ENTRY_TELEPHONE_MIN_LENGTH) {
        $_SESSION['payone_error'] = ENTRY_TELEPHONE_NUMBER_ERROR;
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true&payment_error='.$this->code, 'SSL', true));		
      }
      if ((!isset($_SESSION[$this->code]['invoice_conditions']) || $_SESSION[$this->code]['invoice_conditions'] == false)) {
        $_SESSION['payone_error'] = TEXT_KLARNA_ERROR_CONDITIONS;
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true&payment_error='.$this->code, 'SSL', true));		
      }
    }		
  }

	function after_process() {
		parent::after_process();
		unset($_SESSION[$this->code]);
	}
}
?>