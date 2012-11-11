<?php
/*************************************************************************

$Id$

iclear payment system - because secure is simply secure
http://www.iclear.de

Copyright (c) 2001 - 2009 iclear

Released under the GNU General Public License

************************************************************************

All rights reserved.

This program is free software licensed under the GNU General Public License (GPL).

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
USA

*************************************************************************/

class IclearSOAP extends IclearBase {
	 
	var $client = false;
	 
	var $proxy = false;
	 
	var $url = array();
	 
	var $credentials = false;
	 
	var $order = false;

	var $result = array();

	var $requestID = '';
	 
	var $orderMethod = '';
	 
	function IclearSOAP(&$icCore) {
		$this->icVersion = '$Id$';
		parent::IclearBase($icCore);
	}
	 
	function flush() {
		$this->result = array();
		$this->requestID = 0;
	}

	function init() {
		$this->localEndpointURI();
	}
	 
	function initServer() {
		$this->server = false;
	}

	function initClient($uri) {
		$this->client = false;
	  
		if($uri) {
			if($client = $this->icCore->getSoapClient()) {
				$this->client = $client->getClient($uri);
			}
		}
	  
		return $this->client ? true : false;
	}

	/**
	 * Check if a connection error occured
	 *
	 * @return string errorMsg
	 */
	function soapError() {
		$rc = '';
		if(is_object($this->proxy) && ($rc = $this->proxy->getError())) {
			$this->addError('SOAP proxy: ' . $rc);
		}
		return $rc;
	}

	function perform($function = '', $params = false) {
		return $this->icCore->perform($this->client, $function, $params);
	}

	function serviceURL($type = '', $url = '') {
		$rv = '';
		if($type) {
			if($url) {
				$this->url[$type] = $url;
			}
			if(isset($this->url[$type])) {
				$rv = $this->url[$type];
			}

		}
		return $rv;
	}

	function requestID($reqID = '') {
		if($reqID) {
			$this->requestID = $reqID;
		}
		return $this->requestID;
	}

	function submitRequest($function, $params = false) {
		$res = false;
		$icCore =& $this->icCore;
		$basket =& $icCore->getBasket();

		// check if result is already present
		if(isset($this->result[$function]) && $this->result[$function]) {
			$res = $this->result[$function];
		} else {
			// debug mode: (only sendorder family)
			$perform = strtolower($function);
			if($icCore->debug()) {
				switch($perform) {
					case 'sendorder':
					case 'sendorderb2c':
						$params[IC_SOAP_SESSION_ID] = base64_encode($basket->sessionID() . '@' . $icCore->getBaseURL() . 'iclear.php?wsdl');
						if($this->initClient(IC_URI_DEBUG_ORDER)) {
							$res = $this->perform($function, array_values($params));
							$params[IC_SOAP_SESSION_ID] = $basket->sessionID();
						}
						// use parallel request mode - reset SOAP client to live system
						if($icCore->debug() == IC_DEBUG_TRANS) {
							$res = false;
						}
						break;

					case 'regcustomer':
						if($this->initClient(IC_URI_DEBUG_USER)) {
							$res = $this->perform($function, array_values($params));
						}
						// use parallel request mode - reset SOAP client to live system
						if($icCore->debug() == IC_DEBUG_TRANS) {
							$res = false;
						}
						break;
						 
				}
			}
			// $res is populated if IC_DEBUG == 1 (direct endpoint debugger mode)
			if(!$res) {
				switch($perform) {
					case 'sendorder':
					case 'sendorderb2c':
						$this->initClient(IC_URI_ORDER_SERVICES);
						break;

						//  @TODO: Fix debug case
					case 'regcustomer':
						$this->initClient(IC_URI_USER_SERVICES);
						break;
				}

				if($res = $this->perform($function, array('arg0' => $params))) {
					if ( is_array( $res ) ) {
						$item = new stdClass();
						foreach( $res['return'] AS $key => $val ) {
							$item->$key = $val;
						}
						$res = $item;
					} else
					$res = $res->return;
				} else { //20110203 CA - added simple error recognizing for SOAP faults
					$this->icCore->log($this->lastError());
					print $this->lastError();
				}
			}
			$this->result[$function] = $res;

			if(method_exists($this->client, '__getLastRequest')) {
				$msg = date('Y-m-d H:i:s') . "\n$function\n" .
       	       "REQUEST:\n" . $this->client->__getLastRequest() . "\n" . 
       	       "RESPONSE:\n" . $this->client->__getLastResponse() . "\n";
				$this->icCore->log($msg);
			}
		}
		 
		return $res;
	}

	/* Implemented SOAP operations follows */
	 
	function sendOrderB2C () {
		$rc = false;
		$proxy =& $this->icCore->getProxy();
		if(! ($shopID = $proxy->perform('shopID')) ) {
			$this->addError('No ShopID found!');
		} elseif(!preg_match('/^[0-9]+$/', $shopID)) {
			$this->addError('Iclear ShopID not numerical!');
		} elseif (! ($proxy->perform('getOrder')) ) {
			$this->addError('Order not present!');
		} elseif ( !$proxy->perform('orderItemCount') ) {
			$this->addError('Order has no items!');
		} else {
			$icCore =& $this->icCore;
			$basket =& $icCore->getBasket();
			$basket->processOrder();
			 		 
			$params = array (
				IC_SOAP_SHOP_ID => $shopID,
				IC_SOAP_SESSION_ID => $basket->sessionID(),
				IC_SOAP_BASKET_ID => $basket->basketID(),
				IC_SOAP_CURRENCY_ISO => $basket->currency(),
				IC_SOAP_LANGUAGE_ISO => $basket->language(),
				IC_SOAP_DELIVERY_ADDRESS => $basket->deliveryAddress->address(),
				IC_SOAP_BASKET_ITEMS => $basket->basketItems(),
			);

			//20110126 CA added new object pageparams
			if ( $pageParam = $proxy->perform( 'pageparams' ) ) {
				$params[IC_SOAP_PAGEPARAM] =  $pageParam;
			}
				
			//20110414 CA added new object paymentType
			if ( $paymentType  = $proxy->perform( 'paymentType' ) ) {
				$params[IC_SOAP_PAYMENTTYPE] =  $paymentType ;
			}
			
			//20110506 CA added new object conditionsAccepted
			if ( $conditionsAccepted = $proxy->perform( 'conditionsAccepted' ) ) {
				$params[IC_SOAP_CONDITIONSACCEPTED] =  $conditionsAccepted ;
			}

			$res = $this->submitRequest(__FUNCTION__, $params);
			if($rc = $basket->submitOK($res)) {
				$this->registerCustomer();
			}
		}
		return $rc;
	}

	function registerCustomer() {
		$rc = false;
		$icCore =& $this->icCore;
		$proxy =& $icCore->getProxy();
		$basket =& $icCore->getBasket();
		$this->initClient(IC_URI_USER_SERVICES);
		if($info = $proxy->perform('customerInfo')) {
			$params = array(
  		  'requestID' => $basket->submitRequestID(),
  		  'sessionID' => $basket->sessionID(),
  		  'customerInfo' => $info,
			);
			$res = $this->submitRequest(__FUNCTION__, $params);
		}
		return $rc;
	}


}



?>
