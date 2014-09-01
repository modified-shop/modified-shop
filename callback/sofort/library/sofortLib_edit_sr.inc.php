<?php
/**
 * This class is for confirming and changing statuses of invoices
 *
 * eg: $confirmObj = new SofortLib_EditSr('yourapikey');
 *
 * $confirmObj->confirmInvoice('1234-456-789654-31321')->sendRequest();
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-05-21 16:53:26 +0200 (Mo, 21 Mai 2012) $
 * @version SofortLib 1.5.0  $Id: sofortLib_edit_sr.inc.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
 */
class SofortLib_EditSr extends SofortLib_Abstract {
	
	protected $_apiVersion = '1.0';
	
	protected $_parameters = array();
	
	protected $_response = array();
	
	protected $_xmlRootTag = 'edit_sr';
	
	private $_file;
	
	/**
	 * create new confirm object
	 *
	 * @param String $apikey your API-key
	 */
	public function __construct($configKey = '') {
		list($userId, $projectId, $apiKey) = explode(':', $configKey);
		$apiUrl = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://api.sofort.com/api/xml';
		parent::__construct($userId, $apiKey, $apiUrl);
	}
	
	
	public function setTransaction($transaction, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['transaction'] = $transaction;
		return $this;
	}
	
	
	public function setInvoiceNumber($invoiceNumber, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['invoice_number'] = $invoiceNumber;
		return $this;
	}
	
	
	public function setCustomerNumber($customerNumber, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['customer_id'] = $customerNumber;
		return $this;
	}
	
	
	public function setOrderNumber($orderNumber, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['order_id'] = $orderNumber;
		return $this;
	}
	
	
	/**
	 * set a comment for refunds
	 * just useable with api version 1.0
	 * @param string $arg
	 */
	public function setComment($comment, $invoice = 0) {
		$this->_parameters['invoice'][$invoice]['comment'] = $comment;
		return $this;
	}
	
	
	/**
	 * add one item to the cart if you want to change the invoice
	 *
	 * @param string $itemId itemId
	 * @param double $unit_price gross price of one item
	 * @param int $quantity default 1
	 */
	public function addItem($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax, $invoice = 0) {
		$unitPrice = number_format($unitPrice, 2, '.', '');
		$tax = number_format($tax, 2, '.', '');
		$quantity = intval($quantity);
		$this->_parameters['invoice'][$invoice]['items']['item'][] = array(
			'item_id' => $itemId,
			'product_number' => $productNumber,
			'product_type' => $productType,
			'title' => $title,
			'description' => $description,
			'quantity' => $quantity,
			'unit_price' => $unitPrice,
			'tax' => $tax,
		);
	}
	
	
	function updateCart($cartItems = array(), $invoice = 0) {
		$i = 0;
		
		foreach ($cartItems as $cartItem) {
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['item_id'] = $cartItem['itemId'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['product_number'] = $cartItem['productNumber'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['title'] = $cartItem['title'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['description'] = $cartItem['description'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['quantity'] = $cartItem['quantity'];
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['unit_price'] = number_format($cartItem['unitPrice'], 2, '.', '') ;
			$this->_parameters['invoice'][$invoice]['items']['item'][$i]['tax'] = $cartItem['tax'];
			$i++;
		}
	}
	
	
	/**
	 * after you you changed/confirmed an invoice you
	 * can download the new invoice-pdf with this function
	 * @return string url
	 */
	public function getInvoiceUrl() {
		return $this->_file;
	}
	
	
	protected function _parseXml() {
		$this->_file = isset($this->_response['invoice']['download_url']['@data']) ? $this->_response['invoice']['download_url']['@data'] : '';
	}
	
	
	protected function _handleErrors() {
		if (!isset($this->_response['invoices']['invoice'][0])) {
			$tmp = $this->_response['invoices']['invoice'];
			unset($this->_response['invoices']['invoice']);
			$this->_response['invoices']['invoice'][0] = $tmp;
		}
		
		foreach ($this->_response['invoices']['invoice'] as $response) {
			//handle errors
			if (isset($response['errors']['error'])) {
				if (!isset($response['errors']['error'][0])) {
					$tmp = $response['errors']['error'];
					unset($response['errors']['error']);
					$response['errors']['error'][0] = $tmp;
				}
				
				foreach ($response['errors']['error'] as $error) {
					$this->errors['sr'][] = $this->_getErrorBlock($error);
				}
			}
			
			//handle warnings
			if (isset($response['warnings']['warning'])) {
				if (!isset($response['warnings']['warning'][0])) {
					$tmp = $response['warnings']['warning'];
					unset($response['warnings']['warning']);
					$response['warnings']['warning'][0] = $tmp;
				}
			
				foreach ($response['warnings']['warning'] as $error) {
					$this->warnings['sr'][] = $this->_getErrorBlock($error);
				}
			}
		}
	}
}
?>