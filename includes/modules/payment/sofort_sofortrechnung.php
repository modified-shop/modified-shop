<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-13 11:51:22 +0200 (Thu, 13 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortrechnung.php 4204 2013-01-10 21:05:48Z Tomcraft1980 $
 */


require_once(DIR_FS_CATALOG.'callback/sofort/sofort.php');
require_once(DIR_FS_CATALOG.'callback/sofort/library/sofortLib.php');

class sofort_sofortrechnung extends sofort{

	var $code, $title, $description, $enabled, $invoice, $paymentMethod;

	function sofort_sofortrechnung() {
		global $order;
		
		parent::sofort();
		
		$this->_checkExistingSofortConstants('sr');
		
		if(isset($_SESSION['sofort']['sofort_conditions_sr'])) unset($_SESSION['sofort']['sofort_conditions_sr']);
		
		$this->code = 'sofort_sofortrechnung';
		$this->title = MODULE_PAYMENT_SOFORT_SR_TEXT_TITLE ;
		$this->title_extern = MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_TEXT_TITLE;
		$this->paymentMethod = 'SR';

		if (MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT == 'True') {
			$this->title_extern .= ' ' . MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT_TEXT;
		}

		$this->description = MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION.'<br />'.MODULE_PAYMENT_SOFORT_MULTIPAY_VERSIONNUMBER.': '.HelperFunctions::getSofortmodulVersion();
		$this->sort_order = MODULE_PAYMENT_SOFORT_SR_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_SOFORT_SR_STATUS == 'True') ? true : false);
		$this->icons_available = '';
		
		if (is_object($order)) {
			$this->update_status();
		}
		$this->defaultCurrency = DEFAULT_CURRENCY;

		$this->invoice = new PnagInvoice(MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY, '');
		$this->invoice->setVersion(HelperFunctions::getSofortmodulVersion());
	}


	function selection () {
		global $order;
		
		if (!parent::selection()) {
			$this->enabled = false;
			return false;
		}
		
		//virtual content with SR is not allowed 
		if ($this->_orderHasVirtualProducts($order) || $this->_deliveryAddressDoesNotExist($order)) {
			$this->enabled = false;
			return false;
		}
		
		if(!isset($_SESSION['sofort']['sofort_sofortboxjs'])){
			$title = MODULE_PAYMENT_SOFORT_MULTIPAY_JS_LIBS;
			$_SESSION['sofort']['sofort_sofortboxjs'] = true;
		} else {
			$title = '';
		}
		
		switch (MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE) {
			case 'Logo & Text':
				$title .= $this->setImageText('logo_155x50.png', MODULE_PAYMENT_SOFORT_MULTIPAY_SR_CHECKOUT_TEXT);
				break;
			case 'Infographic':
				$title .= $this->setImageText('banner_300x100.png', '');
				break;
		}

		$cost = '';
		if(array_key_exists('ot_sofort',  $GLOBALS)) {
			$cost = $GLOBALS['ot_sofort']->get_percent($this->code, 'price');
		}

		$conditionsChecked = false;
		if (isset($_SESSION['sofort']['sofort_conditions_sr']) && $_SESSION['sofort']['sofort_conditions_sr'] == 'sofort_conditions_sr') {
			$conditionsChecked = true;
		}

		$fields = array(
			array('title' => MODULE_PAYMENT_SOFORT_SR_CHECKOUT_CONDITIONS,
					'field' => xtc_draw_checkbox_field('sofort_conditions_sr', 'sofort_conditions_sr', $conditionsChecked))
			);
			
		//commerce:SEO - Bugfix
		if (isset($_REQUEST['xajax']) && !empty($_REQUEST['xajax'])) {
			$fields[0]['title'] = utf8_decode($fields[0]['title']);
			return array('id' => $this->code , 'module' => utf8_decode($this->title_extern), 'fields' => $fields, 'description' => utf8_decode($title), 'module_cost' => utf8_decode($cost));
		}else{
			return array('id' => $this->code , 'module' => $this->title_extern , 'fields' => $fields, 'description' => $title, 'module_cost' => $cost);
		}
	}


	function setImageText($image, $text) {
		$lng = HelperFunctions::getShortCode($_SESSION['language']);
		$image = 'https://images.sofort.com/'.$lng.'/sr/'.$image;

		$image = xtc_image($image, MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
		$title = MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE;
		$title = str_replace('{{image}}', $image, $title);
		$title = str_replace('{{text}}', $text, $title);
		return $title;
	}


	function pre_confirmation_check ($vars = '') {

		parent::pre_confirmation_check ($vars);

		//in CommerceSEO check is sometimes done with Ajax
		if (isset ($_POST['xajax']) && $_POST['xajax'] == 'updatePaymentModule' ) {
			$data_arr = $vars;
			if (!$data_arr['sofort_conditions_sr'] == 'sofort_conditions_sr') {
				unset($_SESSION['sofort']['sofort_conditions_sr']);
			}
			$is_ajax = true;
		} else {
			$data_arr = $_POST;
		}

		if ($data_arr['sofort_conditions_sr']) {
			$_SESSION['sofort']['sofort_conditions_sr'] = $data_arr['sofort_conditions_sr'];
		}

		if ($data_arr['sofort_conditions_sr'] != 'sofort_conditions_sr' && $_SESSION['sofort']['sofort_conditions_sr'] != 'sofort_conditions_sr') {
			if ($is_ajax) {
				$payment_error_return = 'payment_error='.$this->code.'&error='.urlencode(MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10002);
				$_SESSION['checkout_payment_error'] = $payment_error_return;
			} else {
				$payment_error_return = 'payment_error='.$this->code.'&error_codes=10002';
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
			}
		}

		return false;
	}


	
	/**
	 * send data to SOFORT and check SOFORT-response
	 * @return array with paymentUrl, api-errors, trans-id, payment-secret
	 */
	function _makeSofortApiCall(){
		global $order;

		$customer_id = $_SESSION['customer_id'];

		$currency = $_SESSION['currency'];

		$reasons = $this->_getReasons($this->paymentMethod, $customer_id, $order);

		$user_variable_0 = '';
		$user_variable_1 = $customer_id;

/*
		$session = session_name() . '=' . session_id();

		if (ENABLE_SSL == true)
			$server = HTTPS_SERVER;
		else
			$server = HTTP_SERVER;
*/
		
		$paymentSecret = md5(mt_rand().microtime());

/*		
		$success_url = $server.DIR_WS_CATALOG.'callback/sofort/ressources/scripts/sofortReturn.php?sofortaction=success&sofortcode='.$this->code;
		$cancel_url = $server.DIR_WS_CATALOG.'callback/sofort/ressources/scripts/sofortReturn.php?sofortaction=cancel&sofortcode='.$this->code;
		$notification_url = $server . DIR_WS_CATALOG . 'callback/sofort/callback.php?paymentSecret='.$paymentSecret.'&action=multipay';
*/
		$success_url = xtc_href_link('callback/sofort/ressources/scripts/sofortReturn.php', 'sofortaction=success&sofortcode='.$this->code, 'SSL');
		$cancel_url = xtc_href_link('callback/sofort/ressources/scripts/sofortReturn.php', 'sofortaction=cancel&sofortcode='.$this->code, 'SSL');
		$notification_url = xtc_href_link('callback/sofort/callback.php', 'paymentSecret='.$paymentSecret.'&action=multipay', 'SSL');
		
		$user_variable_2 = $_SESSION['cart']->cartID;

		$this->invoice->setCurrency($currency);  //others than EUR will currently not be accepted by API!
		$this->invoice->setReason(HelperFunctions::convertEncoding($reasons[0],3), HelperFunctions::convertEncoding($reasons[1],3));
		$this->invoice->setSuccessUrl(HelperFunctions::convertEncoding($success_url,4));
		$this->invoice->setAbortUrl(HelperFunctions::convertEncoding($cancel_url,4));
		$this->invoice->setTimeoutUrl(HelperFunctions::convertEncoding($cancel_url,4));
		$this->invoice->setNotificationUrl(HelperFunctions::convertEncoding($notification_url,4));
		$this->invoice->addUserVariable(HelperFunctions::convertEncoding($user_variable_0,3));
		$this->invoice->addUserVariable(HelperFunctions::convertEncoding($user_variable_1,3));
		$this->invoice->addUserVariable(HelperFunctions::convertEncoding($user_variable_2,3));
		$this->invoice->setEmailCustomer(HelperFunctions::convertEncoding($order->customer['email_address'],3));
		$this->invoice->setPhoneNumberCustomer($order->customer['telephone']);

		$this->invoice->setSofortrechnung();
		$this->invoice->setCustomerId(HelperFunctions::convertEncoding($customer_id,3));
		
		//important notice: following lines modifies also the $ot_shipping
		$orderTotals = array();
		if (MODULE_ORDER_TOTAL_INSTALLED) {
			require_once (DIR_WS_CLASSES.'order_total.php');
			$orderTotalModules = new order_total();
			//Following function-call manipulates variables (e.g. prices) within $order! Never call more than once!
			$orderTotals = $orderTotalModules->process();
		}
		
		$this->_addCustomerAddressesToInvoice();
		
		$this->_addProductsToInvoice($customer_id);
		
		$this->_addShippingToInvoice($customer_id);
		
		$this->_addPriceModificatorsToInvoice($customer_id, $orderTotals);
		
		//check shopTotal against the invoiceclassTotal
		$shopTotal = $this->_getShopTotal($orderTotals);
		$invoiceTotal = $this->invoice->getAmount();
		if (!$this->_checkShopTotalVsInvoiceTotal ($shopTotal, $invoiceTotal) ) {
			$this->invoice->log('ShopTotal is not near InvoiceTotal! Customer-ID '.$customer_id.'. Are you using price-modification-tools 
								which are not supported by this "Rechnung bei sofort"-module? Was the tax-rate of all affected articles, 
								shipping options and other positions set correctly? Did you set the currency-exchange-rate correctly?');
			xtc_redirect($cancel_url);
		}
		
		//send all data to the API and place an order at SOFORT if no errors
		$this->invoice->checkout();
		
		$return = array();
		$return['apiCallErrors'] = $this->invoice->getErrors();
		$return['paymentUrl'] = $this->invoice->getPaymentUrl();
		$return['transactionId'] = $this->invoice->getTransactionId();
		$return['paymentSecret'] = $paymentSecret;
		$return['orderTotalModules'] = $orderTotalModules;
		$return['orderTotals'] = $orderTotals;
		return $return;
	}
	
	
	function _addCustomerAddressesToInvoice() {
		global $order;

		//split address into street and number at last dot or space
		if(!preg_match('#(.+)[ .](.+)#i', trim($order->billing['street_address']), $billing)) {
			$billing = array();
			$billing[1] = trim($order->billing['street_address']);
			$billing[2] = '';
		}
		if(!preg_match('#(.+)[ .](.+)#i', trim($order->delivery['street_address']), $delivery)) {
			$delivery = array();
			$delivery[1] = trim($order->delivery['street_address']);
			$delivery[2] = '';
		}
		
		$billingCompanyName = trim($order->billing['company']);
		$billingNameAdditive = '';
		
		//get billing-salutation: 2=masculine, 3=feminine
		//modified eCommerce Shopsoftware Bug: only $order->customer has a gender
		$billingSalutation = $this->_getGenderFromAddressBook($order->billing['firstname'], $order->billing['lastname'], $order->billing['company'], $order->billing['street_address'],
			$order->billing['postcode'], $order->billing['city'], $order->billing['country_id'], $order->billing['zone_id']);
		
		$deliveryCompanyName = trim($order->delivery['company']);
		$deliveryNameAdditive = '';
		
		//get deliver-salutation: 2=masculine, 3=feminine
		$deliverSalutation = $this->_getGenderFromAddressBook($order->delivery['firstname'], $order->delivery['lastname'], $order->delivery['company'], $order->delivery['street_address'],
				$order->delivery['postcode'], $order->delivery['city'], $order->delivery['country_id'], $order->delivery['zone_id']);

		$this->invoice->addInvoiceAddress(HelperFunctions::convertEncoding($order->billing['firstname'],3), HelperFunctions::convertEncoding($order->billing['lastname'],3),
			HelperFunctions::convertEncoding($billing[1],3), HelperFunctions::convertEncoding($billing[2],3), $order->billing['postcode'], HelperFunctions::convertEncoding($order->billing['city'],3), 
			$billingSalutation, $order->billing['country']['iso_code_2'], HelperFunctions::convertEncoding($billingNameAdditive, 3), HelperFunctions::convertEncoding($order->billing['suburb'],3),
			HelperFunctions::convertEncoding($billingCompanyName,3));
		$this->invoice->addShippingAddress(HelperFunctions::convertEncoding($order->delivery['firstname'],3), HelperFunctions::convertEncoding($order->delivery['lastname'],3),
			HelperFunctions::convertEncoding($delivery[1],3), HelperFunctions::convertEncoding($delivery[2],3), $order->delivery['postcode'], HelperFunctions::convertEncoding($order->delivery['city'],3), $deliverSalutation, 
			$order->delivery['country']['iso_code_2'], HelperFunctions::convertEncoding($deliveryNameAdditive, 3), HelperFunctions::convertEncoding($order->delivery['suburb'],3),
			HelperFunctions::convertEncoding($deliveryCompanyName,3));
		
		$this->invoice->setDebitorVatNumber(HelperFunctions::convertEncoding($_SESSION['customer_vat_id'], 3));
	}
	
	
	/**
	 * add all bought products to $this->invoice
	 */
	function _addProductsToInvoice($customer_id) {
		global $order;
		
		foreach($order->products as $product) {
			//get attributes and add as description to item
			$description = '';
			if ((isset ($product['attributes'])) && (sizeof($product['attributes']) > 0)) {
				foreach ($product['attributes'] as $attribute) {
						$description .= $attribute['option'] . ": " . $attribute['value'] . "\n";
				}
				$description = trim($description);
			}
			$this->invoice->addItemToInvoice($product['id'], HelperFunctions::convertEncoding($product['model'],3), HelperFunctions::convertEncoding($product['name'],3), $product['price'], 0, HelperFunctions::convertEncoding($description,3), $product['qty'], $product['tax']);
		}
	}
	
	/**
	 * add shippinginfo to $this->invoice
	 */
	function _addShippingToInvoice($customer_id) {
		global $order;
		
		list ($shippingModule, $shippingMethod) = explode('_', $_SESSION['shipping']['id']); //e.g. "dp_dp" or "freeamount_freeamount"
		if($shippingModule) {
			global $$shippingModule; //notice $$
			$shippingObject = $$shippingModule; //notice $$
			$shippingAmount = $order->info['shipping_cost'] * $order->info['currency_value'];
			
			if (isset($shippingObject->tax_class)) {
				$shippingTaxClass = xtc_get_tax_rate($shippingObject->tax_class);
			}else{
				$shippingTaxClass = 0; //e.g. freeamount_freeamount
			}
			
			$itemId =  'shipping|'.substr($shippingModule.'|'.$shippingMethod,0,22);
			$this->invoice->addItemToInvoice($itemId, '', HelperFunctions::convertEncoding(html_entity_decode($order->info['shipping_method'],ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding')),3), $shippingAmount, 1, '', 1, (double)$shippingTaxClass);
		}
	}
	
	
	/**
	 * add discounts or agio (e.g. ot_sofort, loworderfee, discount...) to $this->invoice
	 */
	function _addPriceModificatorsToInvoice($customer_id, $orderTotals) {
		
		//check optional price-modificators 
		if(is_array($orderTotals) ) {
			foreach($orderTotals as $totalModule) {
				$itemId =  'discount|'.substr($totalModule['code'],0,22);
				
				if($totalModule['code'] == 'ot_sofort') {
					$tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_SOFORT_TAX_CLASS);
					$amountValue = $totalModule['value'];
					$this->invoice->addItemToInvoice($itemId, '',HelperFunctions::convertEncoding(html_entity_decode($totalModule['title'],ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding')),3), $amountValue, 2, '', 1, $tax);
					continue;
				}
				if($totalModule['code'] == 'ot_discount') {
					$tax = 19;
					$amountValue = ($totalModule['value'] > 0) ? ($totalModule['value'] * -1) : $totalModule['value'];
					$this->invoice->addItemToInvoice($itemId, '',HelperFunctions::convertEncoding(html_entity_decode($totalModule['title'],ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding')),3), $amountValue, 2, '', 1, $tax);
					continue;
				}
				if($totalModule['code'] == 'ot_gv') {
					$tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_GV_TAX_CLASS);
					$amountValue = ($totalModule['value'] > 0) ? ($totalModule['value'] * -1) : $totalModule['value'];
					$this->invoice->addItemToInvoice($itemId, '',HelperFunctions::convertEncoding(html_entity_decode($totalModule['title'],ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding')),3), $amountValue, 2, '', 1, $tax);
					continue;
				}
				if($totalModule['code'] == 'ot_coupon') {
					$tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_COUPON_TAX_CLASS);
					$amountValue = ($totalModule['value'] > 0) ? ($totalModule['value'] * -1) : $totalModule['value'];
					$this->invoice->addItemToInvoice($itemId, '',HelperFunctions::convertEncoding(html_entity_decode($totalModule['title'],ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding')),3), $amountValue, 2, '', 1, $tax);
					continue;
				}
				
				$itemId =  'agio|'.substr($totalModule['code'],0,26);
				
				if($totalModule['code'] == 'ot_loworderfee') {
					$tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS);
					$amountValue = $totalModule['value'];
					$this->invoice->addItemToInvoice($itemId, '',HelperFunctions::convertEncoding(html_entity_decode($totalModule['title'],ENT_QUOTES,HelperFunctions::getIniValue('shopEncoding')),3), $amountValue, 2, '', 1, $tax);
					continue;
				}
			}
		}
	}
	
	
	function install() {
		$sofortStatuses = $this->_insertAndReturnSofortStatus();
		
		$unconfirmedStatus =(isset($sofortStatuses['unconfirmed'])	&& !empty($sofortStatuses['unconfirmed']))	? $sofortStatuses['unconfirmed'] : '';
		$confirmedStatus = 	(isset($sofortStatuses['invoice_confirmed']) 	&& !empty($sofortStatuses['invoice_confirmed']))	? $sofortStatuses['invoice_confirmed'] : '';
		$canceledStatus = 	(isset($sofortStatuses['canceled']) 	&& !empty($sofortStatuses['canceled']))		? $sofortStatuses['canceled'] : '';
		
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SR_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SR_SORT_ORDER', '0', '6', '20', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_SR_ORDER_STATUS_ID', '".HelperFunctions::escapeSql($confirmedStatus)."',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID', '".HelperFunctions::escapeSql($unconfirmedStatus)."', '6', '8', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_SOFORT_SR_CANCEL_STATUS_ID', '".HelperFunctions::escapeSql($canceledStatus)."',  '6', '8', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_ALLOWED', '', '6', '0', now())");
		xtc_db_query("INSERT INTO " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_SOFORT_SR_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
		
		$this->_setSrToNotAllowedDownloads();
		
		//install shared keys, that are used by all/most multipay-modules
		parent::install();
	}
	
	
	function remove() {
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_SR%'");
		xtc_db_query("delete from " . HelperFunctions::escapeSql(TABLE_CONFIGURATION) . " where configuration_key LIKE 'MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG%'");
		
		//if this is the last removing of a multipay-paymentmethod --> we also remove all shared keys, that are used by all/most multipay-modules
		parent::remove();
	}
	
	
	function keys() {
		
		parent::keys();
		
		return array('MODULE_PAYMENT_SOFORT_SR_STATUS',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY',
		'MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH',
		'MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT',
		'MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_ALLOWED' ,
		'MODULE_PAYMENT_SOFORT_SR_ZONE' ,
		'MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID',
		'MODULE_PAYMENT_SOFORT_SR_ORDER_STATUS_ID',
		'MODULE_PAYMENT_SOFORT_SR_CANCEL_STATUS_ID',
		'MODULE_PAYMENT_SOFORT_SR_SORT_ORDER');
	}


	/**
	 * if there is more than 1% difference this function returns false
	 * @return true/false
	 */
	function _checkShopTotalVsInvoiceTotal ($shopTotal, $invoiceTotal){
		if ($shopTotal < $invoiceTotal) {
			$percent = $shopTotal/$invoiceTotal;
		} else {
			$percent = $invoiceTotal/$shopTotal;
		}

		if ($percent < 0.99) {
			return false;
		} else {
			return true;
		}
	}
	
	
	function _addTax ($amount, $tax) {
		$tax = $tax/100;
		return $amount + ($amount * $tax);
	}
	
	
	function _setSrToNotAllowedDownloads() {
		$query = xtc_db_query('SELECT configuration_value FROM '.TABLE_CONFIGURATION.' WHERE configuration_key = "DOWNLOAD_UNALLOWED_PAYMENT"');
		$result = xtc_db_fetch_array($query);
		$configurationValue = $result['configuration_value'];
		$configurationValues = explode(',', $configurationValue);
		foreach ($configurationValues as $key => $value) {
			$configurationValues[$key] = trim($value);
		}
		if (in_array('sofort_sofortrechnung', $configurationValues)) {
		} else {
			$configurationValues[] = 'sofort_sofortrechnung';
			$newConfigurationValue = implode(',', $configurationValues);
			xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '".$newConfigurationValue."' WHERE configuration_key = 'DOWNLOAD_UNALLOWED_PAYMENT'");
		}
		return true;
	}
}
?>