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

class payone_otrans extends PayonePayment {

	var $payone_genre = 'onlinetransfer';

  var $code;
  var $form_action_url;
  var $banktransfertypes;
  var $banktransfercountries;

  var $config;
  var $payone;
  var $personal_data;
  var $delivery_data;
  var $payment_method;
  var $params;
  var $builder;

	function __construct() {
		global $order;

		$this->code = 'payone_otrans';
		parent::__construct();

		$this->form_action_url = '';
		$this->banktransfertypes = array(
			'sofortueberweisung' => 'PNT',
			'giropay' => 'GPY',
			'eps' => 'EPS',
			'pfefinance' => 'PFF',
			'pfcard' => 'PFC',
			'ideal' => 'IDL',
		);
		$this->banktransfercountries = array(
			'sofortueberweisung' => array('DE', 'AT', 'CH', 'NL', 'PL', 'BE'),
			'giropay' => array('DE'),
			'eps' => array('AT'),
			'pfefinance' => array('CH'),
			'pfcard' => array('CH'),
			'ideal' => array('NL'),
		);

		if ($this->enabled && is_object($order)) {
			$active_genre = $this->_getActiveGenreIdentifier();
			$type_available = false;
			foreach(array_keys($this->banktransfertypes) as $type) {
				if ($active_genre !== false && $this->_isOnlineTransferTypeAvailable($type, $active_genre)) {
					$type_available = true;
					break;
				}
			}
			if ($type_available === false) {
				$this->enabled = false;
			}
		}
	}

	function _isOnlineTransferTypeAvailable($type, $active_genre_identifier) {
		global $order;

		$billing_country = ((is_object($order) && isset($order->billing['country']['iso_code_2'])) ? $order->billing['country']['iso_code_2'] : '');
		return is_string($type)
		       && isset($this->banktransfertypes[$type])
		       && isset($this->banktransfercountries[$type])
		       && in_array($billing_country, $this->banktransfercountries[$type], true)
		       && isset($this->config[$active_genre_identifier]['types'][$type]['active'])
		       && $this->config[$active_genre_identifier]['types'][$type]['active'] == 'true';
	}

	function _getOnlineTransferBankCountry($type) {
		global $order;

		if ($type === 'sofortueberweisung') {
			return $order->billing['country']['iso_code_2'];
		}
		return $this->banktransfercountries[$type][0];
	}

	function _paymentDataFormProcess($active_genre_identifier) {
	  global $order;
	  
	  $payment_smarty = new Smarty();
	  $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';
    
    $otrans_type = ((isset($_SESSION[$this->code]['otrans_type'])) ? $_SESSION[$this->code]['otrans_type'] : '');
    if (!$this->_isOnlineTransferTypeAvailable($otrans_type, $active_genre_identifier)) {
      $_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
    }

    $bank_group = '';
    $bgroups = $this->payone->getBankGroups();
    switch ($otrans_type) {
        case 'sofortueberweisung':
        case 'giropay':
          $required_fields = array('bankaccountholder' => $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'], 
                                   'iban' => '', 
                                   'bic' => '', 
                                   );
          break;
        case 'eps':
          $required_fields = array('bankaccountholder' => $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'],
                                   );
          $bank_group = ((isset($bgroups['eps']) && is_array($bgroups['eps'])) ? $bgroups['eps'] : array());
          break;
        case 'pfefinance':
        case 'pfcard':
          $required_fields = array();
          break;
        case 'ideal':
          $required_fields = array('bankaccountholder' => $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'],
                                   );
          $bank_group = ((isset($bgroups['ideal']) && is_array($bgroups['ideal'])) ? $bgroups['ideal'] : array());
          break;        
    }
    
    $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
    $payment_smarty->assign('otrans_type', $otrans_type);
    $payment_smarty->assign('required_fields', $required_fields);
    $payment_smarty->assign('bank_group', $bank_group);
        
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_otrans_form.html');
		
		return $module_form;
	}

	function _paymentDataForm($active_genre_identifier) {
	  $payment_smarty = new Smarty();
    $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';
    
		$genre_config = $this->config[$active_genre_identifier];
		foreach($genre_config['types'] as $type => $type_config) {
			if (!$this->_isOnlineTransferTypeAvailable($type, $active_genre_identifier)) {
				unset($genre_config['types'][$type]);
			}
		}
    $payment_smarty->assign('genre_config', $genre_config['types']);
    $payment_smarty->assign('code', $this->code);
    
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_type_selection.html');
		
		$return = array(
			array('title' => '', 
			      'field' => $module_form),
		);
		return $return;
	}

	function pre_confirmation_check() {
		parent::pre_confirmation_check();

		$active_genre = $this->_getActiveGenreIdentifier();
		$otrans_type = ((isset($_POST[$this->code.'_type']))
		                ? $_POST[$this->code.'_type']
		                : ((isset($_SESSION[$this->code]['otrans_type'])) ? $_SESSION[$this->code]['otrans_type'] : ''));
		if ($active_genre === false || !$this->_isOnlineTransferTypeAvailable($otrans_type, $active_genre)) {
			$_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
		}
		$_SESSION[$this->code]['otrans_type'] = $otrans_type;
	}

	function confirmation() {
		$otrans_type = ((isset($_SESSION[$this->code]['otrans_type'])) ? $_SESSION[$this->code]['otrans_type'] : '');
    $confirmation = array('title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE'),
                          'fields' => array(array('title' => '',
                                                  'field' => constant('paymenttype_'.$otrans_type),
                                            )));

		return $confirmation;
	}

	function process_button() {
		$active_genre = $this->_getActiveGenreIdentifier();
		if ($active_genre === false) {
			return false;
		}
		
    return $this->_paymentDataFormProcess($active_genre);
	}

	function before_process() {
		parent::before_process();
        
    $valid_request = array('bankaccountholder', 'bankgrouptype', 'bankcode', 'bankaccount', 'bankcountry', 'iban', 'bic');

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		  foreach ($valid_request as $key) {
		    if (isset($_POST[$key])) {
					if (!is_scalar($_POST[$key])) {
						$_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
						xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
					}
		      $_SESSION[$this->code]['otrans_'.$key] = (string)$_POST[$key];
			  }
			}
		}

		$otrans_type = ((isset($_SESSION[$this->code]['otrans_type'])) ? $_SESSION[$this->code]['otrans_type'] : '');
		if ($otrans_type === 'eps' || $otrans_type === 'ideal') {
			$bankgroups = $this->payone->getBankGroups();
			$selected_bankgroup = ((isset($_SESSION[$this->code]['otrans_bankgrouptype'])) ? $_SESSION[$this->code]['otrans_bankgrouptype'] : '');
			if ($selected_bankgroup !== '' && !isset($bankgroups[$otrans_type][$selected_bankgroup])) {
				$_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
			}
		}
	}

	function payment_action() {
	  global $order, $insert_id;
   
    if (!isset($insert_id) || $insert_id == '') {
		  $insert_id = $_SESSION['tmp_oID'];
		}

		$active_genre = $this->_getActiveGenreIdentifier();
		$otrans_type = ((isset($_SESSION[$this->code]['otrans_type'])) ? $_SESSION[$this->code]['otrans_type'] : '');
		if ($active_genre === false || !$this->_isOnlineTransferTypeAvailable($otrans_type, $active_genre)) {
			$_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
			$this->_remove_order($insert_id);
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
		}
		
		$this->payone->log("(pre-)authorizing $this->code payment");
		$standard_parameters = parent::_standard_parameters();

		$this->personal_data = new Payone_Api_Request_Parameter_Authorization_PersonalData();
		parent::_set_customers_standard_params();

		$this->delivery_data = new Payone_Api_Request_Parameter_Authorization_DeliveryData();
		parent::_set_customers_shipping_params();
    
    $bankgroup = '';
		if ($otrans_type == 'eps' || $otrans_type == 'ideal') {
			$bankgroup = ((isset($_SESSION[$this->code]['otrans_bankgrouptype'])) ? $_SESSION[$this->code]['otrans_bankgrouptype'] : '');
		}
    $_SESSION[$this->code]['otrans_bankcountry'] = $this->_getOnlineTransferBankCountry($otrans_type);
    
		$this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_OnlineBankTransfer();
		$this->payment_method->setOnlinebanktransfertype($this->banktransfertypes[$otrans_type]);
		$this->payment_method->setBankcountry($_SESSION[$this->code]['otrans_bankcountry']);
		if (!empty($_SESSION[$this->code]['otrans_iban'])) {
			$this->payment_method->setIban($_SESSION[$this->code]['otrans_iban']);
		}
		if (!empty($_SESSION[$this->code]['otrans_bic'])) {
			$this->payment_method->setBic($_SESSION[$this->code]['otrans_bic']);
		}
		if ($bankgroup !== '') {
			$this->payment_method->setBankgrouptype($bankgroup);
		}
		$this->payment_method->setSuccessurl($this->getCheckoutSuccessUrl($insert_id));
		$this->payment_method->setBackurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id());
		$this->payment_method->setErrorurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&payment_error='.$this->code);

    // set order_id for deleting canceld order
    $_SESSION['tmp_payone_oID'] = $_SESSION['tmp_oID'];
    
    $request_parameters = parent::_request_parameters('sb');
    
		$this->params = array_merge($standard_parameters, $request_parameters);
		$this->builder = new Payone_Builder($this->payone->getPayoneConfig());
    
    parent::_build_service_authentification('sb');
    parent::_parse_response_payone_api();
  }
  
  function after_process() {        
		parent::after_process();
		unset($_SESSION[$this->code]);
	}
}
