<?php
/****************************************************** 
 * Masterpayment Modul for modified eCommerce Shopsoftware 
 * Version 3.5.1
 * Copyright (c) 2010-2012 by K-30 | Florian Ressel 
 *
 * support@k-30.de | www.k-30.de
 * ----------------------------------------------------
 *
 * $Id: MasterpaymentResponse.class.php 02.07.2013 - 16:00 $
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
 
class MasterpaymentResponse
{
	var $response_string;
	
	function MasterpaymentResponse($_var)
	{
		$this->__construct($_var);
	}
	
	
	function __construct($var)
	{		
		if(!isset($_var) && !is_array($var))
		{			
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'NONSSL'));			
		} else {			
			$this->response_string = array();
			
			$this->response_string['response'] = $var['response'];
			$this->response_string['order_id'] = $var['order_id'];
			$this->response_string['payment_method'] = $var['payment_method'];
			$this->response_string['lang'] = $var['lang'];
			$this->response_string['controlkey'] = $var['controlkey'];			
			
			if($this->checkResponse())
			{		
				if(isset($_SESSION['customer_id']) && (isset($_SESSION['cart_Masterpayment_ID']) && !empty($_SESSION['cart_Masterpayment_ID'])) && (substr($_SESSION['payment'], 0, strpos($_SESSION['payment'], '_')) == 'masterpayment'))
				{		
					unset($_SESSION['cart_Masterpayment_ID']);
							
					if($this->response_string['response'] == 'success')
					{
						xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'NONSSL'));
					} elseif($this->response_string['response'] == 'failed') {
						xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=masterpayment_' . $this->response_string['payment_method'], 'NONSSL'));
					} elseif($this->response_string['response'] == 'cancelled') {
						xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'NONSSL'));
					}															
				} elseif(isset($_SESSION['customer_id']) && (!isset($_SESSION['cart_Masterpayment_ID']) && empty($_SESSION['cart_Masterpayment_ID'])) && (substr($_SESSION['payment'], 0, strpos($_SESSION['payment'], '_')) != 'masterpayment')) {
					xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'NONSSL'));
				} elseif($this->response_string['response'] == 'success') {					
					$this->sendMail();
				}
			} else {
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'NONSSL'));
			}			
		}		
	}
	
	
	function checkResponse()
	{		
		$string = md5($this->response_string['response'] . '|' . $this->response_string['order_id'] . '|' . $this->response_string['payment_method'] . '|' . $this->response_string['lang'] . '|' . constant('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SECRETKEY'));
		
		if($this->response_string['controlkey'] == $string)
		{
			return true;
		} else {
			return false;
		}		
	}
	
	
	function sendMail()
	{
		global $smarty;
		
		$select_data = xtc_db_query("select o.customers_id, o.masterpayment_status, oh.customer_notified from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_STATUS_HISTORY . " oh on oh.orders_id = o.orders_id and oh.orders_status_id = '".MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_SUCCESS."' where o.orders_id = '".xtc_db_input($this->response_string['order_id'])."'");
		$result = xtc_db_fetch_array($select_data);
		
		if(isset($result['customers_id']) && $result['customer_notified'] != 1)
		{
			$insert_id = $this->response_string['order_id'];
			$_SESSION['customer_id']= $result['customers_id']; 
		    include("send_order.php");
			
			if(SEND_EMAILS == 'true')
			{
				xtc_db_query("update " . TABLE_ORDERS_STATUS_HISTORY . " set customer_notified = 1 where orders_id = '".xtc_db_input($this->response_string['order_id'])."' and orders_status_id = '".MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_SUCCESS."'");
			}
			
			unset($_SESSION['customer_id']);
		}		
	}
		
}

?>