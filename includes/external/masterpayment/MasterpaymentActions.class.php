<?php
/****************************************************** 
 * Masterpayment Modul for modified eCommerce Shopsoftware
 * Version 3.5.1
 * Copyright (c) 2010-2012 by K-30 | Florian Ressel 
 *
 * support@k-30.de | www.k-30.de
 * ----------------------------------------------------
 *
 * $Id: MasterpaymentActions.class.php 02.07.2013 - 15:59 $
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

class MasterpaymentActions
{
	
	var $masterpaymentLanguages;
	var $masterpaymentDefaultLanguage;
	
	function MasterpaymentActions()
	{
		$this->__construct();
	}
	
	
	function __construct()
	{				
		$this->masterpaymentLanguages = array('de' => 'german', 'en' => 'english');	
		$this->masterpaymentDefaultLanguage = 'EN';			
	}
	
	
	function getShopURL() 
	{		
		if((ENABLE_SSL == true) or (getenv('HTTPS') == 'on' || getenv('HTTPS') == '1'))
		{
			$_url = HTTPS_SERVER;
		} else {
			$_url = HTTP_SERVER;
		}
		
		return $_url . DIR_WS_CATALOG;		
	}
	
	
	function getActionURL()
	{	
		if(MODULE_PAYMENT_MASTERPAYMENT_CONFIG_INFRAME == 'True') {            
      		return $this->getShopURL() . 'checkout_masterpayment.php?' . session_name() . '=' . session_id();		
	  	} else {
	  		return $this->getShopURL() . 'checkout_masterpayment.php?action=request&' . session_name() . '=' . session_id();
	  	}		
	}
	
	
	function getRequestURL()
	{
		return $this->getShopURL() . 'checkout_masterpayment.php?action=request&' . session_name() . '=' . session_id();
	}
	
	
	function getOrderId()
	{
		if(isset($_SESSION['cart_Masterpayment_ID']) && !empty($_SESSION['cart_Masterpayment_ID']))
		{
			return (int)substr($_SESSION['cart_Masterpayment_ID'], strpos($_SESSION['cart_Masterpayment_ID'], '-')+1);
		} else {
			return false;
		}
	}
	
	
	function deleteTempOrder()
	{		
		if(MODULE_PAYMENT_MASTERPAYMENT_CONFIG_DELETE_TEMP_ORDER == 'True')
		{		
			if($this->getOrderId()) 
			{				
				$order_id = $this->getOrderId();
	
				$check_query = xtc_db_query('select masterpayment_status from ' . TABLE_ORDERS . ' where orders_id = "' . xtc_db_input($order_id) . '" limit 1');
				$num_check = xtc_db_num_rows($check_query);
				
				if($num_check > 0)
				{
					$check_result = xtc_db_fetch_array($check_query);
	
					if ($check_result['masterpayment_status'] != 1) 
					{
            require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
            xtc_remove_order((int)$order_id, ((STOCK_LIMITED == 'true') ? 'on' : false));
					} else {					
						xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'NONSSL'));	
						exit();				
					}					
				}
			}		
		}
		
		unset($_SESSION['cart_Masterpayment_ID']);
		unset($_SESSION['tmp_oID']);				
	}
	
	
	function checkoutBeforeProcess()
	{		
		if($this->getOrderId()) 
		{			
			$_order_id = (int)$this->getOrderId();
			
			$check_payment = xtc_db_query("select masterpayment_status from " . TABLE_ORDERS . " where orders_id = '".xtc_db_input($_order_id)."' limit 1");
			$num_check = xtc_db_num_rows($check_payment);
			
			if($num_check < 1)
			{
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'NONSSL'));
			} else {				
				$row_check = xtc_db_fetch_array($check_payment);
				
				if($row_check['masterpayment_status'] != 1)
				{					
					$mail_text = 'Bei der Bestellung mit der OrderID ' . $_order_id . ' konnte aus technischen Gruenden leider nicht automatisch ueberprueft werden, ob die Zahlung bei Masterpayment eingegangen ist. Bitte ueberpruefen Sie dies manuell.';
					@mail(EMAIL_BILLING_ADDRESS, 'Masterpayment - OrderID ' . $_order_id, $mail_text, 'from: ' . EMAIL_BILLING_ADDRESS);					
				} 
				
				$_SESSION['tmp_oID'] = $_order_id;				
			}			
		}		
	}
	
	
	function checkoutAfterProcess()
	{		
		global $insert_id;
		
		if($this->getOrderId()) 
		{			
			$o_ID = $this->getOrderId();
			
			if(SEND_EMAILS == 'true')
			{
				xtc_db_query("update " . TABLE_ORDERS_STATUS_HISTORY . " set customer_notified = 1 where orders_id = '".xtc_db_input($o_ID)."' and orders_status_id = '".MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_SUCCESS."'");
			}
			
			unset($_SESSION['cart_Masterpayment_ID']);
			unset($_SESSION['tmp_oID']);			
		}		
	}
	
	
	/*
	
		Funktion die beim Installieren vom Modul verwendet werden
		
	*/	
	function installProcess()
	{		
		$this->setSuccessStatus();
	  	$this->setFailureStatus();
	  	$this->setCancelStatus();
	  	$this->setProcessStatus();
		$this->addDBFields();		
		
		$this->setLogsWriteable();				
	}
	
	
	function uninstallProcess()
	{		
		$order_c = array();
		$str = "SHOW COLUMNS FROM orders";    	
    	$dbFields	= xtc_db_query($str);  	
       	while ($field_names = xtc_db_fetch_array($dbFields)) 
		{          		
                   $order_c[] = $field_names['Field'];
       	}
		
		if(in_array("masterpayment_status", $order_c))
		{	
    		$str = "ALTER TABLE " .  TABLE_ORDERS . " DROP masterpayment_status";
    		xtc_db_query($str);   
    	}
		
		if(in_array("masterpayment_invoiceNo", $order_c))
		{	
    		$str = "ALTER TABLE " .  TABLE_ORDERS . " DROP masterpayment_invoiceNo";
    		xtc_db_query($str);   
    	}
		
		if(in_array("masterpayment_customerNo", $order_c))
		{	
    		$str = "ALTER TABLE " .  TABLE_ORDERS . " DROP COLUMN masterpayment_customerNo";
    		xtc_db_query($str);   
    	}		
	}
	
	
	function setSuccessStatus() 
	{
    	$successID = $this->getSuccessStatusID();
    	
    	if(empty($successID))
		{
    		$insertID	= $this->getNextStatusID();
    		$languages	= $this->getMaxLanguageStatusID();
    		
    		for($i = 1; $i <= $languages; $i++)
			{
    			$status_query	= xtc_db_query("insert into " . TABLE_ORDERS_STATUS . "(orders_status_id, language_id, orders_status_name) VALUES (" . $insertID . ", " . $i . ", 'masterpayment successful')");    		
    		}    		
    	}
    }
    

    function setFailureStatus() 
	{
    	$failureId = $this->getFailureStatusID();
    	
    	if(empty($failureId))
		{
    		$insertID	= $this->getNextStatusID();
    		$languages	= $this->getMaxLanguageStatusID();
    		
    		for($i = 1; $i <= $languages; $i++)
			{
    			$status_query	= xtc_db_query("insert into " . TABLE_ORDERS_STATUS . "(orders_status_id, language_id, orders_status_name) VALUES (" . $insertID . ", " . $i . ", 'masterpayment failed')");    		
    		}    		
    	}
    }
	
	
	function setCancelStatus() 
	{
    	$cancelId = $this->getCancelStatusID();
    	
    	if(empty($cancelId))
		{
    		$insertID	= $this->getNextStatusID();
    		$languages	= $this->getMaxLanguageStatusID();
    		
    		for($i = 1; $i <= $languages; $i++)
			{
    			$status_query	= xtc_db_query("insert into " . TABLE_ORDERS_STATUS . "(orders_status_id, language_id, orders_status_name) VALUES (" . $insertID . ", " . $i . ", 'masterpayment cancelled')");    		
    		}    		
    	}
    }
	
	
	function setProcessStatus() 
	{
    	$processId = $this->getProcessStatusID();
    	
    	if(empty($processId))
		{
    		$insertID	= $this->getNextStatusID();
    		$languages	= $this->getMaxLanguageStatusID();
    		
    		for($i = 1; $i <= $languages; $i++)
			{
    			$status_query	= xtc_db_query("insert into " . TABLE_ORDERS_STATUS . "(orders_status_id, language_id, orders_status_name) VALUES (" . $insertID . ", " . $i . ", 'masterpayment in process')");    		
    		}    		
    	}
    }
		

    function getNextStatusID() 
	{
		$status_query	= xtc_db_query("select max(orders_status_id) as freeID from " . TABLE_ORDERS_STATUS . "");    	
		$status_id		= xtc_db_fetch_array($status_query); 
		
		if(!$status_id['freeID'] && empty($status_id['freeID']))
		{
			return 1;
		} else {
			return (int)$status_id['freeID'] + 1; 
		}
    }


    function getMaxLanguageStatusID() 
	{
		$status_query	= xtc_db_query("select max(language_id) as langID from " . TABLE_ORDERS_STATUS . "");    	
		$status_id		= xtc_db_fetch_array($status_query);  	  
		return (int)$status_id['langID']; 
    }

    
   	function getSuccessStatusID() 
	{
   		$status_query	= xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name like 'masterpayment successful' group by orders_status_id");
   	    $status_id		= xtc_db_fetch_array($status_query);  	  
   	    return $status_id['orders_status_id'];
   	}
    

   	function getFailureStatusID() 
	{
   		$status_query	= xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name like 'masterpayment failed' group by orders_status_id");
   	    $status_id		= xtc_db_fetch_array($status_query);  	  
   	    return $status_id['orders_status_id'];
   	}
	
	
	function getCancelStatusID() 
	{
   		$status_query	= xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name like 'masterpayment cancelled' group by orders_status_id");
   	    $status_id		= xtc_db_fetch_array($status_query);  	  
   	    return $status_id['orders_status_id'];
   	}
	
	
	function getProcessStatusID() 
	{
   		$status_query	= xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name like 'masterpayment in process' group by orders_status_id");
   	    $status_id		= xtc_db_fetch_array($status_query);  	  
   	    return $status_id['orders_status_id'];
   	}		
	

	function addDBFields()
	{		
		$order_c = array();
		$str = "SHOW COLUMNS FROM orders";    	
    	$dbFields	= xtc_db_query($str);  	
       	while ($field_names = xtc_db_fetch_array($dbFields)) 
		{          		
                   $order_c[] = $field_names['Field'];
       	}
		
		if(!in_array("masterpayment_status", $order_c))
		{	
    		$str = "ALTER TABLE " .  TABLE_ORDERS . " ADD COLUMN masterpayment_status int(1)";
    		xtc_db_query($str);   
    	}
		
		if(!in_array("masterpayment_invoiceNo", $order_c))
		{	
    		$str = "ALTER TABLE " .  TABLE_ORDERS . " ADD COLUMN masterpayment_invoiceNo varchar(20)";
    		xtc_db_query($str);   
    	}
		
		if(!in_array("masterpayment_customerNo", $order_c))
		{	
    		$str = "ALTER TABLE " .  TABLE_ORDERS . " ADD COLUMN masterpayment_customerNo varchar(20)";
    		xtc_db_query($str);   
    	} 
	}
	
	
	function setLogsWriteable() 
	{		
		@chmod(DIR_FS_CATALOG . 'includes/external/masterpayment/logs/callbacks.log', 0777);
		@chmod(DIR_FS_CATALOG . 'includes/external/masterpayment/logs/requests.log', 0777);		
	}
	
}

?>