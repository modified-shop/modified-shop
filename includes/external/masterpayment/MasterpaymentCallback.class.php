 <?php
 /****************************************************** 
 * Masterpayment Modul for modified eCommerce Shopsoftware
 * Version 3.5.1
 * Copyright (c) 2010-2012 by K-30 | Florian Ressel 
 *
 * support@k-30.de | www.k-30.de
 * ----------------------------------------------------
 *
 * $Id: MasterpaymentCallback.class.php 19.06.2013 11:10 $
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

class MasterpaymentCallback extends MasterpaymentActions
{
	
	var $status, $order_ID, $customer_lang, $callbackData;
	
	
	function MasterpaymentCallback($_var)
	{
		$this->__construct($_var);
	}
	
	
	function __construct($_var)
	{
		parent::__construct();		
		$this->init($_var);
	}
	
	
	function init($_var) 
	{
		$this->callbackData = $_var;
		$this->writeCallbackLog();
		$this->checkCallback();	
	}
	
	
	function checkCallback()
	{
		if($this->checkControlKey() && $this->checkTransaction())
		{
			$this->handleCallback();
		}	
	}
		
	
	function checkControlKey() 
	{
		$retval = false;
		
		$_controlKey = md5(str_replace($this->callbackData['CTRL_KEY'], '', implode('|', $this->callbackData)) . constant('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SECRETKEY'));
			
		if($_controlKey == $this->callbackData['CTRL_KEY']) 
		{			
			$this->status 			= trim($this->callbackData['STATUS']);
			$this->order_ID 		= trim($this->callbackData['TX_ID']);
			$this->customer_lang 	= $this->getCustomerLanguage();
			$retval = true;						
		}
			
		return $retval;
	}
	
	
	function checkTransaction() 
	{	
		$retval = false;
			
		$check_order = xtc_db_query("select count(orders_id) as a_orders from " . TABLE_ORDERS . " where orders_id = '".xtc_db_input($this->order_ID)."' limit 1");
		$result_check = xtc_db_fetch_array($check_order);
			
		if($result_check['a_orders'] == 1)
		{			
			$retval = true;		
		}
		
		return $retval;
	}
		
		
	function handleCallback() 
	{		
		switch($this->status)
		{
			case 'SUCCESS':
			case 'SCHEDULED':
			case 'PENDING':					
					$this->transactionSuccess();
					break;
			case 'FAILED':
			case 'REFUSED_RISK':
					$this->transactionFailure();
					break;
			case 'CANCELLED':
			case 'TIMED_OUT':
			case 'REVOKED':
			default:
					$this->transactionCancel();
					break;
		}			
	}
	
	
	function transactionSuccess() 
	{	
		$_successId = constant('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_SUCCESS');
		
		if($this->callbackData['METHOD'] == 'rechnungskauf')
		{			
			$this->setInvoiceData($this->callbackData['CUSTOMER_NO'], $this->callbackData['INVOICE_NO']);			
		}

		$this->changeStatus($_successId, 1);			
		$this->writeStatusHistory($_successId);
		$this->deleteCustomersBasket();	
	}
	
	
	function transactionFailure() 
	{
		if(!$this->deleteTempOrder())
		{
			$this->updateProductsQty();
			
			$_failureId = constant('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_FAILURE');
			$this->changeStatus($_failureId, 2);
			$this->writeStatusHistory($_failureId);
		}		
	}

	
	function transactionCancel() 
	{
		if(!$this->deleteTempOrder())
		{
			$this->updateProductsQty();		
			
			$_cancelId = constant('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_CANCEL');		
			$this->changeStatus($_cancelId, 3);
			$this->writeStatusHistory($_cancelId);
		}		
	}
	
	
	function changeStatus($_statusID, $m_status) 
	{
		xtc_db_query("update ". TABLE_ORDERS . " set orders_status = '" . (int)$_statusID . "', masterpayment_status = '" . (int)$m_status . "'  where orders_id='" . xtc_db_input($this->order_ID) . "'");		
	}
	
	
	function setInvoiceData($invoice_co, $invoice_no) 
	{
		xtc_db_query("update " . TABLE_ORDERS . " set masterpayment_customerNo = '".$invoice_co."', masterpayment_invoiceNo = '".$invoice_no."' where orders_id = '". xtc_db_input($this->order_ID) . "'");		
	}
	
	
	function deleteTempOrder() 
	{		
		if(MODULE_PAYMENT_MASTERPAYMENT_CONFIG_DELETE_TEMP_ORDER == 'True')
		{
			$this->updateProductsQty();
				
			xtc_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . xtc_db_input($this->order_ID) . '"');
			xtc_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . xtc_db_input($this->order_ID) . '"');
			xtc_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . xtc_db_input($this->order_ID) . '"');
			xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . xtc_db_input($this->order_ID) . '"');
			xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . xtc_db_input($this->order_ID) . '"');
			xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . xtc_db_input($this->order_ID) . '"');
			
			return true;		
		} else {			
			return false;			
		}		
	}	
	
	
	function updateProductsQty()
	{
		if (STOCK_LIMITED == 'true') 
		{			
			$order_query = xtc_db_query("select products_id, products_quantity from ".TABLE_ORDERS_PRODUCTS." where orders_id = '".xtc_db_input($this->order_ID)."'");
			
			while ($order = xtc_db_fetch_array($order_query)) 
			{
				xtc_db_query("update ".TABLE_PRODUCTS." set products_quantity = products_quantity + ".$order['products_quantity'].", products_ordered = products_ordered - ".$order['products_quantity']." where products_id = '".$order['products_id']."'");
			}				
		}
	}
		
	
	function getCustomerId() 
	{		
		$select_customerid = xtc_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '".xtc_db_input($this->order_ID)."' limit 1");
		$_customer_id = xtc_db_fetch_array($select_customerid);
			
		return $_customer_id['customers_id'];		
	}
	
	
	function getCustomerLanguage() 
	{	
		$_mlanguage = 'english';
			
		$select_olanguage_query = xtc_db_query("select language from " . TABLE_ORDERS . " where orders_id = '".xtc_db_input($this->order_ID)."'");
		$fetch_olanguage = xtc_db_fetch_array($select_olanguage_query);
		
		if(isset($fetch_olanguage['language']) && !empty($fetch_olanguage['language'])) 
		{	
			if(in_array(strtolower($fetch_olanguage['language']), $this->masterpaymentLanguages)) {				
				$_mlanguage = strtolower($fetch_olanguage['language']);					
			} 			
		}
		
		return $_mlanguage;		
	}
	
	
	function deleteCustomersBasket() 
	{		
		$cId = $this->getCustomerId();
    	  
	    if (isset($cId) && !empty($cId)) 
		{
        	xtc_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . xtc_db_input($cId) . "'");
        	xtc_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . xtc_db_input($cId) . "'");        	
      	}		
    }
	
	
	function writeStatusHistory($_statusID) 
	{
		require_once(DIR_FS_CATALOG . 'lang/' . $this->customer_lang . '/masterpayment_callback.php');
		
		$history_message = "status: " . $this->status . "\r";
		$history_message .= $_masterpaymentCallbackMessages[$this->status];
	
		$sql_data_array = array('orders_id' => $this->order_ID,
                                  'orders_status_id' => $_statusID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $history_message);
								  
		xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);		
	}
	
	
	function writeCallbackLog()
    {
		if(MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SAVE_LOGS == 'True')
		{
			$logfile = @fopen(DIR_FS_CATALOG . 'includes/external/masterpayment/logs/callbacks.log', 'a+');
			@fwrite($logfile, "------------------------------------------------------------------------\n");	
			@fwrite($logfile, "transactions-id / order-id: " . $this->callbackData['TX_ID'] . "\n");	
			@fwrite($logfile, "status: " . $this->callbackData['STATUS'] . "\n");	
			@fwrite($logfile, "called: " . date('Y-m-d  H:i:s') . "\n");
			@fwrite($logfile, "request=" . print_r($this->callbackData, true)."\n");	
			@fwrite($logfile, "------------------------------------------------------------------------\n\r");			
			@fclose($logfile);
		}
    }
	
}

?>