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

class payone_cc extends PayonePayment {
	var $payone_genre = 'creditcard';

	function __construct() {
		$this->code = 'payone_cc';		
		parent::PayonePayment();
		
		$this->tmpOrders = '';
		$this->form_action_url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, 'payone_cc=true', 'SSL');		
	}

	function selection() {
		$selection = parent::selection();

		return $selection;
	}
  
	function _paymentDataFormProcess($active_genre_identifier) {
	  $payment_smarty = new Smarty();
	  $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';
	  	  
		$genre_config = $this->config[$active_genre_identifier];
    $payment_smarty->assign('genre_specific', $genre_config['genre_specific']);

    $standard_parameters = parent::_standard_parameters('creditcardcheck');
		$standard_parameters['responsetype'] = 'JSON';
		$standard_parameters['storecarddata'] = 'yes';
		$standard_parameters['encoding'] = 'UTF-8';
		$standard_parameters['hash'] = $this->payone->computeHash($standard_parameters, $this->global_config['key']);
    $payment_smarty->assign('parameters', $standard_parameters);
    
		$cctypes = $this->payone->getTypesForGenre($active_genre_identifier);		   
    $payment_smarty->assign('cctypes', $cctypes);
        
    $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_cc_form.html');
		
		return $module_form;
	}

	function pre_confirmation_check() {
		parent::pre_confirmation_check();
	}

	function confirmation() {
		parent::confirmation();
	}
	
	function process_button() {
		$active_genre = $this->_getActiveGenreIdentifier();
		if ($active_genre === false) {
			return false;
		}
		
    return $this->_paymentDataFormProcess($active_genre);
	}	
  
  function before_process() {
		if (isset($_POST['pseudocardpan'])) {
			$_SESSION[$this->code]['pseudocardpan'] = $_POST['pseudocardpan'];
		}
		if (isset($_POST['truncatedcardpan'])) {
			$_SESSION[$this->code]['truncatedcardpan'] = $_POST['truncatedcardpan'];
		}
		
		if (isset($_GET['payone_cc']) && $_GET['payone_cc'] == 'true') {
		  $this->tmpOrders = $this->config['orders_status']['tmp'];
		}
  }

  function payment_action() {
	  global $order, $insert_id, $tmp;
    
    if (!isset($insert_id) || $insert_id == '') {
		  $insert_id = $_SESSION['tmp_oID'];
		}
		
		if (!isset($_SESSION['tmp_payone_oID'])) {
      $this->payone->log("(pre-)authorizing $this->code payment");
      $standard_parameters = parent::_standard_parameters();

      $this->personal_data = new Payone_Api_Request_Parameter_Authorization_PersonalData();
      parent::_set_customers_standard_params();

      $this->delivery_data = new Payone_Api_Request_Parameter_Authorization_DeliveryData();
      parent::_set_customers_shipping_params();

      $this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_CreditCard();
      $this->payment_method->setSuccessurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PROCESS.'?'.xtc_session_name().'='.xtc_session_id());
      $this->payment_method->setBackurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id());
      $this->payment_method->setErrorurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&payment_error='.$this->code);
      $this->payment_method->setPseudocardpan($_SESSION[$this->code]['pseudocardpan']);

      // set order_id for deleting canceld order
      $_SESSION['tmp_payone_oID'] = $_SESSION['tmp_oID'];

      $request_parameters = parent::_request_parameters('cc');
    
      $this->params = array_merge($standard_parameters, $request_parameters);
      $this->builder = new Payone_Builder($this->payone->getPayoneConfig());
    
      parent::_build_service_authentification('cc');
      parent::_parse_response_payone_api();
 
      $tmp = false;
    } 
  }
  
	function after_process() {
	  global $order, $insert_id;
     
		parent::after_process();
		unset($_SESSION[$this->code]);
	}

}
?>