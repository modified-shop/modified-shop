<?php
/****************************************************** 
 * Masterpayment Modul for modified eCommerce Shopsoftware 
 * Version 3.5.1
 * Copyright (c) 2010-2012 by K-30 | Florian Ressel 
 *
 * support@k-30.de | www.k-30.de
 * ----------------------------------------------------
 *
 * $Id: MasterpaymentRequest.class.php 02.07.2013 16:00 $
 *	
 *	The Modul based on:
 *  XT-Commerce - community made shopping
 *  http://www.xt-commerce.com
 *
 *  Copyright (c) 2003 XT-Commerce
 *
 *	Released under the GNU General License
 *
 ******************************************************/
 
require_once(DIR_FS_CATALOG . 'includes/external/masterpayment/MasterpaymentActions.class.php');

class MasterpaymentRequest extends MasterpaymentActions 
{

	var $order_ID;
	var $masterpaymentPaymentMethod;
	var $masterpaymentNotInControlkeyParameters;
	var $masterpaymentGatewayURL = 'https://www.masterpayment.com/en/payment/gateway';
	
	/*
		Initialisieren der Klasse
	*/	
	function init() 
	{		
		$retval = false;
		
		if(isset($_SESSION['cart_Masterpayment_ID']) && !empty($_SESSION['cart_Masterpayment_ID']) && (substr($_SESSION['payment'], 0, strpos($_SESSION['payment'], '_')) == 'masterpayment')) 
		{		
			parent::__construct();
		
			$this->order_ID = substr($_SESSION['cart_Masterpayment_ID'], strpos($_SESSION['cart_Masterpayment_ID'], '-')+1);
			$this->masterpaymentNotInControlkeyParameters = array('sex', 'installmentsCount', 'installmentsFreq', 'installmentsPeriod', 'paymentDelay', 'dueDays', 'createAsPending', 'customerNo', 'invoiceNo');
			
			if($this->checkRequestAccess())
			{
				$this->masterpaymentPaymentMethod = $this->getPaymentMethod();							
				$retval = true;
			}			
		}
		
		return $retval;			
	}
	
	
	/*
		Überprüft, ob Bestellung mit order_ID existiert
	*/
	function checkRequestAccess() 
	{		
		$check_order = xtc_db_query("select count(orders_id) as a_orders from " . TABLE_ORDERS . " where orders_id = '".xtc_db_input($this->order_ID)."' limit 1");
		$result_check = xtc_db_fetch_array($check_order);
			
		if($result_check['a_orders'] > 0)
		{				
			return true;				
		} else {				
			return false;				
		}		
	}		
	
	
	/*
		Generierung sämtlicher Parameter, die mit dem Request an Masterpayment gesendet werden
	*/
	function prepareParams() 
	{
		global $order;
		
		$params = array();
		
		$params['merchantName'] = MODULE_PAYMENT_MASTERPAYMENT_CONFIG_MERCHANTID;
  		$params['txId'] = $this->order_ID;
  		$params['basketDescription'] = MODULE_PAYMENT_MASTERPAYMENT_CONFIG_BASKETDESCRIPTION . ' OrderID ' . $this->order_ID;
		$params['basketValue'] = $this->getBasketValue();
  		$params['currency'] = $order->info['currency'];
  		$params['language'] = $this->getCustomerLanguage();
		
		if(MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SEND_CUSTOMER_DATA == 'True')
		{
			$params['userId'] = $_SESSION['customer_id'];
			$params['sex'] = $this->getCustomerGender();
			$params['firstname'] = $order->billing['firstname'];
			$params['lastname'] = $order->billing['lastname'];
			$params['street'] = $this->getCustomerStreet();
			$params['houseNumber'] = $this->getCustomerHouseNumber();
			$params['zipCode'] = $order->billing['postcode'];
			$params['city'] = $order->billing['city'];
			$params['country'] = $order->billing['country_iso_2'];
			$params['birthdate'] = $this->getCustomerBirthdate();  		
			$params['mobile'] = $this->getCustomerTelephone();
			$params['email'] = $order->customer['email_address'];
			$params['userIp'] = $this->getCustomerIpAddress();
		}
		
		$params['paymentType'] = $this->masterpaymentPaymentMethod;
		$params['gatewayStyle'] = 'standard';		

		$params['UrlPatternSuccess'] = $this->getShopURL() . 'callback/masterpayment/masterpayment_mec_callback.php';
		$params['UrlPatternFailure'] = $this->getShopURL() . 'callback/masterpayment/masterpayment_mec_callback.php';
		
		$params['UrlRedirectSuccess'] = 'target-parent:' . $this->getShopURL() . 'checkout_masterpayment.php?action=response&' . session_name() . '=' . session_id() . '&' . $this->generateResponseURL('success');
		$params['UrlRedirectCancel'] = 'target-parent:' . $this->getShopURL() . 'checkout_masterpayment.php?action=response&' . session_name() . '=' . session_id() . '&' . $this->generateResponseURL('cancelled');
		$params['UrlRedirectFailure'] = 'target-parent:' . $this->getShopURL() . 'checkout_masterpayment.php?action=response&'. session_name() . '=' . session_id() . '&' . $this->generateResponseURL('failed');
		
		$params['showCancelOption'] = MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SHOW_CANCEL_BUTTON;
		
		if($this->masterpaymentPaymentMethod == 'ratenzahlung')
		{			
			$params['installmentsCount'] = MODULE_PAYMENT_MASTERPAYMENT_RATENZAHLUNG_INSTALLMENTS_COUNT;			
			if (constant("MODULE_PAYMENT_MASTERPAYMENT_RATENZAHLUNG_INSTALLMENTS_FREQ") > 0) {
				$params['installmentsFreq'] = MODULE_PAYMENT_MASTERPAYMENT_RATENZAHLUNG_INSTALLMENTS_FREQ;
			} else {
				$params['installmentsPeriod'] = MODULE_PAYMENT_MASTERPAYMENT_RATENZAHLUNG_INSTALLMENTS_PERIOD;
			}			
		} elseif($this->masterpaymentPaymentMethod == 'finanzierung') {
			$params['installmentsCount'] = MODULE_PAYMENT_MASTERPAYMENT_FINANZIERUNG_INSTALLMENTS_COUNT;
			if (MODULE_PAYMENT_MASTERPAYMENT_FINANZIERUNG_INSTALLMENTS_PERIOD > 0) {
				$params['installmentsFreq'] = MODULE_PAYMENT_MASTERPAYMENT_FINANZIERUNG_INSTALLMENTS_PERIOD;
			} else {
				$params['installmentsPeriod'] = MODULE_PAYMENT_MASTERPAYMENT_FINANZIERUNG_INSTALLMENTS_PERIOD;
			}
		} elseif($this->masterpaymentPaymentMethod == 'deferred_debit') {
			$params['paymentDelay'] = MODULE_PAYMENT_MASTERPAYMENT_DEFERRED_DEBIT_PAYMENT_DELAY;
		} elseif($this->masterpaymentPaymentMethod == 'rechnungskauf') {		
			$params['customerNo'] = $_SESSION['customer_id'];
			$params['dueDays'] = MODULE_PAYMENT_MASTERPAYMENT_RECHNUNGSKAUF_DUEDAYS;			
			if(MODULE_PAYMENT_MASTERPAYMENT_RECHNUNGSKAUF_SEND_ORDERID == 'True')
			{
				$params['invoiceNo'] = $this->order_ID;
			}
			$params['createAsPending'] = 1;		
		} elseif($this->masterpaymentPaymentMethod == 'anzahlungskauf') {
			$params['customerNo'] = $_SESSION['customer_id'];
			if(MODULE_PAYMENT_MASTERPAYMENT_ANZAHLUNGSKAUF_SEND_ORDERID == 'True')
			{
				$params['invoiceNo'] = $this->order_ID;
			}
		}
		
		
		if(sizeof($order->products) > 0 && MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SEND_PRODUCTS_DATA == 'True') 
		{		
			for ($i=0, $n=sizeof($order->products); $i<$n; $i++) 
			{			
				if($order->products[$i]['name'] != '') 
				{		
					$params['items['.$i.'][itemDescription]'] = $this->escapeItemDescription($order->products[$i]['name']);					
				} else {
					$params['items['.$i.'][itemDescription]'] = 'ItemID ' . $order->products[$i]['id'];
				}				
					
				$params['items['.$i.'][itemAmount]'] = number_format($order->products[$i]['qty'], 2, '.', '');
				$params['items['.$i.'][itemPrice]'] = number_format($order->products[$i]['price'], 2, '', '');			
			}			
		}	

		return $params;		
	}
	
	
	/*
		Sonderzeichen bei der Artikelbeschreibung entfernen, damit Fehler vermieden werden
	*/
	function escapeItemDescription($_name) 
	{		
		$string = strip_tags($_name);		
    	$string = preg_replace('~&#x([0-9a-f]+);~ei', '', $string);
    	$string = preg_replace('~&#([0-9]+);~e', '', $string);
		$string = preg_replace('/[^a-zA-Z0-9äÄöÖüÜß ]/', '', $string);		
    
    	return substr($string, 0, 100);
	}
	
	
	/*
		Liefert den Warenkorbwert in Cent zurück
	*/
	function getBasketValue()
	{
		global $xtPrice;
		
		$order_total_query = xtc_db_query("select text, value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . xtc_db_input($this->order_ID) . "' and class = 'ot_total'");
      	$order_total = xtc_db_fetch_array($order_total_query);		
		
		$order_tax_query = xtc_db_query("select text, value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . xtc_db_input($this->order_ID) . "' and class = 'ot_tax'");
      	$order_tax = xtc_db_fetch_array($order_tax_query);			
		
		if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
			$total = $order_total['value'] + $order_tax['value'];
		} else {
			$total = $order_total['value'];
		}
		
		$amount = round($total, $xtPrice->get_decimal_places($_SESSION['currency']));
		$amount = number_format($amount, 2, '', '');
	
		return $amount;	
	}
	
	
	/*
		Liefert die gewählte Zahlart von Masterpayment zurück
	*/
	function getPaymentMethod() 
	{
		return str_replace('masterpayment_', '', $_SESSION['payment']);			
	}		
	
	
	/*
		Liefert das Geschlecht vom Kunden zurück
	*/
	function getCustomerGender()
	{
		$gender = 'unknown';
		
		if(isset($_SESSION['customer_gender']) && !empty($_SESSION['customer_gender']))
		{
			switch($_SESSION['customer_gender'])
			{
				case 'f':
					$gender = 'women';
					break;
				case 'm':
					$gender = 'man';
					break;
				default:
					$gender = 'unknown';
			}
		}
		
		return $gender;		
	}
	
	
	/*
		Liefert das Geburtsdatum vom Kunden
	*/
	function getCustomerBirthdate() 
	{
		$birthdate = '';
				
		if(isset($_SESSION['customer_id']) && $_SESSION['customer_id'] != 0) 
		{		
			$customer_query = xtc_db_query("select customers_dob from customers where customers_id = '".xtc_db_input($_SESSION['customer_id'])."'");
			$c_vals = xtc_db_fetch_array($customer_query);
			
			$split = explode(" ", $c_vals['customers_dob']);		    
		    $ymd = explode("-", $split[0]);
		    
		    if($ymd[0] != 0 && $ymd[1] != 0 && $ymd[2] != 0) 
			{
		    	$birthdate = substr($c_vals['customers_dob'], 0, strpos($c_vals['customers_dob'], " "));
		    }			
		} else {			
			if(isset($_SESSION['pwa_array_customer']['customers_dob']) && !empty($_SESSION['pwa_array_customer']['customers_dob']))
			{						
				$bjahr = substr($_SESSION['pwa_array_customer']['customers_dob'], 0, 4);
				$bmonat = substr($_SESSION['pwa_array_customer']['customers_dob'], 4, 2);
				$btag = substr($_SESSION['pwa_array_customer']['customers_dob'], 6, 2);

				$birthdate = $bjahr . '-' . $bmonat . '-' . $btag;				
			}		
		}
		
		return $birthdate;		
	}
	
	
	/*
		Liefert die Telefonnummer vom Kunden
	*/
	function getCustomerTelephone() 
	{
		global $order;
		
		if(preg_match("#^[0-9+]{0,}[-/]{0,1}[0-9]{0,}$#", $order->customer['telephone']))
    	{
        	return $order->customer['telephone'];
    	} else {			
			return '';	
		}		
	}
	
	
	/*
		Liefert die Strasse vom Kunden zurück
	*/
	function getCustomerStreet() 
	{
		global $order;
		
		$_address = $order->billing['street_address'];			
	
		for($i = strlen($_address); $i >=0; $i--)
		{
			if($_address[$i] == " " || $_address[$i] == ".")
			{	
				break;
			}				
				
			if(is_numeric($_address[$i]) && !is_numeric($_address[$i-1]))
			{
				break;
			}
		}
	
		if ($i == 0) 
		{
			return '';
		} else {
			return trim(substr($_address, 0, $i));
		}		
	}
	
	
	/*
		Liefert die dazugehörige Hausnummer von der Strasse des Kunden zurück
	*/
	function getCustomerHouseNumber() 
	{
		global $order;
		
		$_address = $order->billing['street_address'];				
	
		for($i = strlen($_address); $i >=0; $i--)
		{
			if($_address[$i] == " " || $_address[$i] == ".")
			{	
				break;
			}				
				
			if(is_numeric($_address[$i]) && !is_numeric($_address[$i-1]))
			{
				break;
			}
		}
	
		if ($i == 0) 
		{
			return '';
		} else {
			return substr($_address, $i);
		}		
	}	
	
	
	/*
		Ermittelt die IP-Adresse des Kunden
	*/
	function getCustomerIpAddress() 
	{	
		if (isset($_SERVER)) 
		{    
			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) 
			{
        		$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
       		} elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
        		$realip = $_SERVER["HTTP_CLIENT_IP"];
			} else {     
        		$realip = $_SERVER["REMOTE_ADDR"];
       
	   		}   
   		} else {
        	if(getenv('HTTP_X_FORWARDED_FOR')) 
			{
          		$realip = getenv('HTTP_X_FORWARDED_FOR');
     		} elseif(getenv('HTTP_CLIENT_IP')) {
          		$realip = getenv('HTTP_CLIENT_IP');
     		} else {
          		$realip = getenv('REMOTE_ADDR');
     		}	 
   		}
		
		return $realip;		
	}
	
		
	/*
		Ermittelt die aktuelle Sprache des Kunden
	*/
	function getCustomerLanguage() 
	{
		global $order;	
		
		if(isset($_SESSION['language']) && !empty($_SESSION['language'])) 
		{			
			$_iso_code = array_search(strtolower($_SESSION['language']), $this->masterpaymentLanguages);
			
			if($_iso_code != false) 
			{				
				$_mlanguage = strtoupper($_iso_code);					
			}			
		}		
				
		if(isset($_mlanguage)) 
		{
			return $_mlanguage;
		} else {
			return $this->defaultLanguage;
		}					
	}	
	
	
	/* 
		Genierung der URL-Parameter für den Response
	*/
	function generateResponseURL($method)
	{		
		$string = array();
		
		$string['response'] = $method;
		$string['order_id'] = $this->order_ID;
		$string['payment_method'] = $this->masterpaymentPaymentMethod;
		$string['lang'] = $this->getCustomerLanguage();
	
		$link_parameter = '';
	
		foreach($string as $parameter => $value)
		{
			$link_parameter .= $parameter . '=' . $value . '&';
		}
		
		$link_parameter .= 'controlkey=' . md5(implode('|', $string) . '|' . MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SECRETKEY);
		
		return $link_parameter;		
	}	
	
	
	/*
		Generierung vom Request welcher an Masterpayment gesendet wird
	*/
	function generateRequest() 
	{	
		$prepareParams = $this->prepareParams();
		$queryString = array();

		foreach($prepareParams as $param => &$paramValue) 
		{	
			if($paramValue != '')
			{
				$queryString[$param] = $this->convertToUTF8($paramValue);
			}		
		}			
			
		$queryString['controlKey'] = $this->generateControlKey($prepareParams);
			
		$this->writeRequestLog($this->masterpaymentGatewayURL, $this->order_ID, $queryString);	
			
		return $queryString;		
	}
	
	
	/*
		Genierung vom Controlkey für den Request an Masterpayment
	*/
	function generateControlKey(&$params) 
	{	
		$tempString = array();	
		
		foreach($params as $param => &$value) 
		{			
			if($value != '' && !in_array($param, $this->masterpaymentNotInControlkeyParameters))	
			{				
				$tempString[] = $this->convertToUTF8($value);				
			}			
		}
	
		$controlKey = md5(implode("|", $tempString) . '|' . MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SECRETKEY);
		
		return $controlKey;		
	}	
	
	
	/*
		Konvertiert einen String korrekt nach UTF8
	*/
	function convertToUTF8($string)
	{
		if(mb_detect_encoding($string, 'UTF-8', true) === 'UTF-8')
		{
			// do nothing
		} else {
			$string = mb_convert_encoding($string, 'UTF-8');
		}	
		
		return $string;
	}
	
	
	/*
		Erzeugt einen Eintrag in der Logdatei
	*/	
	function writeRequestLog($_url, $_txID, $_requestString)
    {
		if(MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SAVE_LOGS == 'True')
		{
			$logfile = @fopen(DIR_FS_CATALOG . 'includes/external/masterpayment/logs/requests.log', 'a+');
			@fwrite($logfile, "------------------------------------------------------------------------\n\r");	
			@fwrite($logfile, "transactions-id: " . $_txID . "\n\r");		
			@fwrite($logfile, "called    " . date('Y-m-d  H:i:s') . "\n\r");
			@fwrite($logfile, "masterpaymentURL=" . $_url ."\n\r");
			@fwrite($logfile, "request=" . utf8_decode(print_r($_requestString, true)) . "\n\r");
			@fwrite($logfile, "server_url=" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\n\r");	
			@fwrite($logfile, "------------------------------------------------------------------------\n\r");			
			@fclose($logfile);
		}
    }    
		
}
		
?>