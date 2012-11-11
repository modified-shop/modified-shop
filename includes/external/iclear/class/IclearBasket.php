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

class IclearBasket extends IclearBase {

	var $id = '';

	var $iclearID = 0;

	var $basket = false;

	var $deliveryAddress = false;

	var $currency = 'EUR'; // default currency is euro

	var $language = 'DE'; // default language is german

	var $order = false;

	var $items = array();

	var $session = '';

	var $shopData = array();

	/**
	 * this var is alway set to false in accept()
	 * it only becomes true @ runtime and reflects the actual accept process
	 */
	var $orderAccepted = false;

	var $submitOK = false;

	var $submitTS = 0;

	var $acceptOK = false;

	var $status = IC_ORDER_OPEN;

	var $errorCode = IC_ERROR_UNKNOWN_ICLEAR_STATE;

	var $keysItem = false;

	function IclearBasket(&$icCore) {
		$this->icVersion = '$Id$';
		parent::IclearBase($icCore);
		$this->update();
	}

	function id() {
		return $this->id;
	}

	function update() {
		// update basket item keys
		$this->keysItem = array(
		IC_SOAP_ITEM_NO,
		IC_SOAP_ITEM_TITLE,
		IC_SOAP_ITEM_QTY,
		IC_SOAP_ITEM_PRICE_NET,
		IC_SOAP_ITEM_PRICE_GROS,
		IC_SOAP_ITEM_VAT_RATE
		);

		// update existings items...
		if(is_array($this->basket['items'])) {
			foreach($this->basket['items'] AS $id => $item) {
				$values = array_values($item);
				foreach($this->keysItem AS $pos => $key) {
					$tmp[$key] = $values[$pos];
				}
				$this->basket['items'][$id] = $tmp;
			}
		}
	}

	function iclearID($iclearID = 0) {
		if($iclearID) {
			$this->iclearID = $iclearID;
		}
		return $this->iclearID;
	}

	/**
	 * setter 4 the currency. defaults to EUR
	 *
	 * @param string $currency
	 * @return string
	 */
	function currency($currency = '') {
		if($currency) {
			$this->currency = $currency;
		}
		return $this->currency;
	}

	/**
	 * setter 4 the language. defaults 2 DE
	 *
	 * @param string $language
	 * @return string
	 */
	function language($language = '') {
		if($language) {
			$this->language = strtoupper($language);
		}
		return $this->language;
	}

	/**
	 * set the sessionID which is used during wsdl requests
	 *
	 * @param string $sessionID
	 */
	function sessionID ($sessionID = '') {
		$rv = '';
		if($this->basket) {
			if($sessionID) {
				$this->basket[IC_SOAP_SESSION_ID] = $sessionID;
				// php4 patch 2 update core cache
				$this->updateCache();
			}
			$rv = $this->basket[IC_SOAP_SESSION_ID];
		}
		return $rv;
	}

	function session($set = false) {
		$rv = '';
		$proxy =& $this->icCore->getProxy();
		if($set) {
			$proxy->perform('beforeSessionSave');
			$this->session = serialize($_SESSION);
			$proxy->perform('afterSessionSave');
			// php4 patch 2 update core cache
			$this->updateCache();
		} elseif($this->session) {
			$rv = unserialize($this->session);
		}
		return $rv;
	}

	function requestID () {
		$rv = '';
		if(isset($this->basket['accepted'])) {
			$rv = $this->basket['accepted']->{IC_SOAP_REQUEST_ID};
		}
		return $rv;
	}

	function submitRequestID() {
		$rv = '';
		if($this->basket && isset($this->basket['submitted']->{IC_SOAP_REQUEST_ID})) {
			$rv = $this->basket['submitted']->{IC_SOAP_REQUEST_ID};
		}
		return $rv;
	}


	function customerID ($customerID = 0) {
		$rv = 0;
		if(is_array($this->basket)) {
			if($customerID) {
				$this->basket['customerID'] = $customerID;
				// php4 patch 2 update core cache
				$this->updateCache();
			}
			$rv = $this->basket['customerID'];
		}
		return $rv;
	}

	function orderID ($orderID = 0) {
		$rv = 0;
		if(is_array($this->basket)) {
			if($orderID) {
				$this->basket['orderID'] = $orderID;
				// php4 patch 2 update core cache
				$this->updateCache();
			}
			$rv = $this->basket['orderID'];
		}
		return $rv;
	}

	function orderAccepted() {
		return $this->orderAccepted;
	}

	function iclearURL() {
		$rv = '';
		if($this->basket && isset($this->basket['submitted']->{IC_SOAP_URL_ICLEAR})) {
			$rv = $this->basket['submitted']->{IC_SOAP_URL_ICLEAR};
		}
		return $rv;
	}

	function errorCode() {
		return $this->errorCode;
	}

	function status() {
		return $this->status;
	}

	function submit($result) {
		$rv = false;
		if($this->basket) {
			if(is_object($result) && isset($result->{IC_SOAP_STATUS_ID})) {
				$this->basket['submitted'] = $result;
				$proxy =& $this->icCore->getProxy();
				$proxy->perform('storeBasket', array($this));
			}
			$rv = isset($this->basket['submitted']) ? $this->basket['submitted'] : false;
		}
		return $rv;
	}

	function submitOK($result = false) {
		$rc = false;
		if($result) {
			$this->submit($result);
		}
		if(isset($this->basket['submitted']->{IC_SOAP_STATUS_ID})) {
			$status = (int) $this->basket['submitted']->{IC_SOAP_STATUS_ID};
			if($status === 0 || $status === 1) {
				$rc = true;
			}
		}
		return $rc;
	}

	function accept($result) {
		$this->orderAccepted = false;

		$icCore =& $this->icCore;
		$proxy = $icCore->getProxy();
		$this->icCore->setLanguage($this->language());

		$status = IC_ORDER_OPEN;
		if(isset($result->{IC_SOAP_STATUS_ID}) && preg_match('/^[0-9]$/', $result->{IC_SOAP_STATUS_ID})) {
			$status = (int) $result->{IC_SOAP_STATUS_ID};
		}

		if($status == IC_ORDER_OPEN) {
			$this->errorCode = IC_ERROR_NO_ICLEAR_RESULT;
			$icCore->log(IC_ERROR_NO_ICLEAR_RESULT . ':No result');
		} elseif(!isset($this->basket['submitted'])) {
			$this->errorCode = IC_ERROR_BASKET_NOT_SUBMITTED;
			$icCore->log($this->addError(IC_ERROR_BASKET_NOT_SUBMITTED . ':' . IC_ERROR_BASKET_NOT_PROCESSED_MSG));
		} elseif($this->status != IC_ORDER_OPEN && $this->status != IC_ORDER_WAIT) {
			$icCore->log(IC_ERROR_BASKET_ACCEPTED . ':' . $this->addError('Basket already processed!'));
			$this->errorCode = IC_ERROR_BASKET_ACCEPTED;
			if($this->status == IC_ORDER_OK) {
				$this->errorCode = IC_ERROR_BASKET_ACCEPTED;
				$icCore->log(IC_ERROR_BASKET_ACCEPTED . ': Basket already accepted!');
			} elseif($this->status == IC_ORDER_CANCEL) {
				$this->errorCode = IC_ERROR_BASKET_CANCELLED;
				$icCore->log(IC_ERROR_BASKET_CANCELLED . ':' .  $this->addError(IC_ERROR_BASKET_CANCELLED_MSG));
			} else {
				$this->errorCode = IC_ERROR_UNKNOWN_LOCAL_STATE;
				$icCore->log(IC_ERROR_UNKNOWN_ICLEAR_STATE . ':' . $this->addError(IC_ERROR_UNKNOWN_LOCAL_STATE_MSG));
					
			}
		} elseif($status == IC_ORDER_CANCEL && $this->status != IC_ORDER_WAIT) {
			$this->errorCode = IC_ERROR_ORDER_NOT_IN_WAITING_STATE;
			$this->addError(IC_ERROR_ORDER_NOT_IN_WAITING_STATE_MSG);
			$icCore->log(IC_ERROR_ORDER_NOT_IN_WAITING_STATE .  ': Basket already accepted!');
		} elseif($status == IC_ORDER_WAIT && $this->status == IC_ORDER_WAIT) {
			$this->errorCode = IC_ERROR_BASKET_WAITING;
			$this->addError(IC_ERROR_BASKET_WAITING_MSG);
			$icCore->log(IC_ERROR_BASKET_WAITING .  ': Basket already accepted!');
		} else {

			switch($status) {
				case IC_ORDER_WAIT:
					// in this case status must set first -> used 4 masking email in storeOrder()
					$this->status = IC_ORDER_WAIT;
					// php4 patch 2 update core cache
					$this->updateCache();
					$proxy->perform('storeOrder');
					$icCore->log(IC_ORDER_WAIT .  ': Waiting basket accepted!');
					break;

				case IC_ORDER_OK:
					// php4 patch 2 update core cache
					$this->updateCache();
					if($this->status == IC_ORDER_WAIT) {
						$proxy->perform('unmaskOrder');
						$icCore->log(IC_ORDER_OK .  ': Waiting basket switched to active!');
					} else {
						$proxy->perform('storeOrder');
						$icCore->log(IC_ORDER_OK .  ': Basket accepted!');
					}
					$this->status = IC_ORDER_OK;
					break;

				case IC_ORDER_CANCEL:
					if($this->cancel()) {
						$this->status = IC_ORDER_CANCEL;
						$icCore->log(IC_ORDER_CANCEL .  ': Waiting basket cancelled!');
					}
					break;

				default:
					$this->status = $this->errorCode = IC_ERROR_UNKNOWN_ICLEAR_STATE;
					$icCore->log(IC_ERROR_UNKNOWN_ICLEAR_STATE .  ': Incoming basket state unknown!');
			}

			if($this->status == IC_ERROR_UNKNOWN_ICLEAR_STATE) {
				$this->addError('Unknown iclear state!');
			} else if ( $this->isError( ) ) {
				$icCore->log('999: Error at accept');
				$this->errorCode = IC_ERROR_UNKNOWN_ICLEAR_STATE;
				$this->addError('Error in accept (iclearBasket)!');
			} else {
				$this->basket['accepted'] = $result;
				$this->errorCode = IC_ORDER_OK;
				$this->updateCache();
				// TODO enable basket storing
				$proxy->perform('storeBasket', array($this));
				$icCore->log('Basket object successfully stored in DB');
				// $this->orderAccepted only becomes true here! Always stored as false!
				$this->orderAccepted = true;
			}
		}
		 
		// Patch 4 PHP4 to update core basket update
		$this->updateCache();
		return $this->orderAccepted;
	}

	function acceptOK($result = false) {
		$rc = false;
		if($result) {
			$this->accept($result);
		}
		 
		if(isset($this->basket['accepted']->{IC_SOAP_ORDER_STATUS_ID}) && !$this->errorCode) {
			$status = preg_replace('/[^0-9\-]/', '', $this->basket['accepted']->{IC_SOAP_ORDER_STATUS_ID});
			if($status !== '') {
				$status = (int) $status;
			}
			if( $status === IC_ORDER_OPEN ||
			$status === IC_ORDER_WAIT ||
			$status === IC_ORDER_CANCEL
			) {
				$rc = true;
			}
		}
		return $rc;
	}

	function cancel() {
		$rc = false;
		if(! ($orderID = $this->orderID()) ) {
			$this->errorCode = IC_ERROR_ORDER_NOT_FOUND_IN_SHOP;
			$this->addError(IC_ERROR_ORDER_NOT_FOUND_IN_SHOP_MSG);
		} elseif(!isset($this->basket['accepted']->{IC_SOAP_STATUS_ID})) {
			$this->errorCode = IC_ERROR_BASKET_NOT_PROCESSED;
			$this->addError(IC_ERROR_BASKET_NOT_PROCESSED_MSG);
		} else {
			$status = (int) $this->basket['accepted']->{IC_SOAP_STATUS_ID};
			if($status !== IC_ORDER_WAIT) {
				$this->errorCode = IC_ERROR_ORDER_NOT_IN_WAITING_STATE;
				$this->addError(IC_ERROR_ORDER_NOT_IN_WAITING_STATE_MSG);
			} else {
				$proxy =& $this->icCore->getProxy();
				if(!$proxy->perform('cancelOrder', array($orderID))) {
					$this->errorCode = IC_ERROR_ORDER_CANCEL_FAILED;
					$this->addError(IC_ERROR_ORDER_CANCEL_FAILED_MSG);
				} else {
					$rc = true;
				}
			}
		}
		 
		return $rc;
		 
	}

	function orderStatus() {
		$rv = false;

		if(isset($this->basket['accepted']->{IC_SOAP_ORDER_STATUS_ID})) {
			$rv = preg_replace('/[^0-9\-]/', '', $this->basket['accepted']->{IC_SOAP_ORDER_STATUS_ID});
			$rv = (int) $rv;
		}
		return $rv;
	}

	function basket($sessionID) {
		if (true) {
			$this->update();
			$this->status = IC_ORDER_OPEN;
			$this->basket = array(
        'sessionID' => $sessionID,
        'customerID' => 0,
        'orderID' => 0,
        'ts' => microtime(),
        'items' => array(),
        'subtotal' => 0,
        'submitted' => false,
        'accepted' => false,
        'md5' => '',
        'version' => IC_SERVICE_VERSION,
			);
			$this->basket['id'] = md5($this->basket['ts'] . $this->basket['sessionID']. microtime(1));
		}

		return $this->basket;
	}

	function basketMD5() {
		$rv = '';
		if(isset($this->basket['md5'])) {
			$rv = $this->basket['md5'];
		}
		return $rv;
	}

	function basketID($id = '') {
		$rv = 0;
		if($id) {
			$rv = $this->basket['id'] = $id;
		} elseif(isset($this->basket['id'])) {
			$rv = $this->basket['id'];
		}
		return $rv;
	}

	function addBasketItemList($items) {
		$rc = true;
		if(is_array($items)) {
			foreach($items AS $item) {
				if(! ($rc = $this->addBasketItem($item)) ) {
					break;
				}
			}
		}
		return $rc;
	}

	function addBasketItem($item) {
		$rc = true;
		 
		if(is_array($item)) {
			foreach($this->keysItem AS $key) {
				if(!isset($item[$key])) {
					$rc = false;
					break;
				}
			}

			if($rc) {
				array_push($this->basket['items'], $item);
				$this->basket['subtotal'] += $item[IC_SOAP_ITEM_PRICE_GROS] * $item[IC_SOAP_ITEM_QTY];
				$this->basket['id'] = md5($this->sessionID() . serialize($this->basket['items']) . microtime(1));
			}
		}
		return $rc;
	}

	function setBasketItem($itemID = 0, $item = false) {
		$rc = false;
		if($this->basketItemCount() && $itemID && is_array($item)) {
			for($x = 0, $y = sizeof($this->basket['items']); $x < $y; $x++) {
				if($this->basket['items'][$x]['itemID'] == $itemID) {
					$this->basket['items'][$x] = $item;
					$rc = true;
					break;
				}
			}
		}
		return $rc;
	}

	function basketItems() {
		$rv = array();
		if($this->basketItemCount()) {
			$rv = $this->basket['items'];
		}
		return $rv;
	}

	function basketTotalNet() {
		$rv = 0;
		if($this->basketItemCount()) {
			foreach($this->basket['items'] AS $item) {
				$rv += $item[IC_SOAP_ITEM_PRICE_NET];
			}
		}
		return $rv;
	}

	function basketTotalGros() {
		$rv = 0;
		if($this->basketItemCount()) {
			foreach($this->basket['items'] AS $item) {
				$rv += $item[IC_SOAP_ITEM_PRICE_GROS];
			}
		}
		return $rv;
	}

	function basketItemCount() {
		$rv = 0;
		if(isset($this->basket['items'])) {
			$rv = sizeof($this->basket['items']);
		}
		return $rv;
	}

	function basketFlushItems() {
		if($this->basketItemCount()) {
			$this->basket['items'] = array();
		}
	}

	function compare($items = false) {
		if(!is_array($items)) {
			$this->addError(IC_ERROR_BASKET_EMPTY_MSG);
		} elseif(!isset($this->basket['items'])) {
			$this->addError('Local basket not loaded!');
		} elseif(!$this->basket['subtotal']) {
			$this->addError(IC_ERROR_ZERO_BASKET_VALUE_MSG);
		} else {
			$subtotal = 0;
			foreach($items AS $item) {
				$subtotal += $item[IC_SOAP_ITEM_PRICE_GROS] * $item[IC_SOAP_ITEM_QTY];
			}

			$delta = $subtotal - $this->basket['subtotal'];
			if($delta > IC_MAX_BASKET_DELTA) {
				$this->errorCode = IC_ERROR_BASKET_MISMATCH;
				$this->addError(IC_ERROR_BASKET_MISMATCH_MSG . ' ' . IC_MAX_BASKET_DELTA . ': ' . $delta);
			}
		}
		 
		return $this->errorCode ? false : true;
	}

	function deliveryAddress($address = false) {
		 
		if(is_object($address)) {
			$this->deliveryAddress =& $address;
		} elseif(!$this->deliveryAddress) {
			$proxy =& $this->icCore->getProxy();
			$this->deliveryAddress =& $proxy->perform(__FUNCTION__);
		}
		return $this->deliveryAddress;
	}

	function shopData($key, $value = false) {
		$rv = false;
		if($key) {
			if(!empty($value)) {
				$this->shopData[$key] = $value;
			}

			if(isset($this->shopData[$key])) {
				$rv = $this->shopData[$key];
			}
		}
		return $rv;
	}

	function setOrder() {
		$this->order = $vars = false;
		$proxy = $this->icCore->getProxy();
		$order = $proxy->perform('getOrder');
		if(is_object($order)) {
			$vars = get_object_vars($order);
		}elseif (is_array($order)) {
			$vars = $order;
		}
		 
		if(is_array($vars)) {
			$obj = new stdClass();
			foreach($vars AS $key => $val) {
				if(!is_object($val)) {
					$obj->$key = $val;
				}
			}
			$this->order = $obj;
		}
		return $this->order ? true : false;
	}

	function check() {
		$rc = true;
		if(!$this->sessionID()) {
			$this->addError(IC_ERROR_NO_SESSIONID_MSG);
			$rc = false;
		} elseif( !$this->basketID() ) {
			$this->addError(IC_ERROR_NO_BASKETID_MSG);
			$rc = false;
		} elseif( !$this->customerID() ) {
			$this->addError(IC_ERROR_NO_CUSTOMERID_MSG);
			$rc = false;
		} elseif( !$this->basketItemCount() ) {
			$this->addError(IC_ERROR_BASKET_EMPTY_MSG);
			$rc = false;
		} elseif( !$this->deliveryAddress()) {
			$this->addError(IC_ERROR_NO_DELIVERY_ADDRESS_MSG);
			$rc = false;
		}
		return $rc;
	}

	function getOrder() {
		return $this->order;
	}

	function processOrder() {
		$rc = false;

		if($proxy =& $this->icCore->getProxy()) {
			// reset basket if it's from an ancient session
			if($this->orderID() || $this->status() != IC_ORDER_OPEN) {
				$this->basket = false;
			}
			$this->basket($proxy->perform('sessionID'));
			$this->basketFlushItems();
			$this->language($proxy->perform('languageISO'));
			$this->currency($proxy->perform('currency'));
			$this->deliveryAddress($proxy->perform('deliveryAddress'));
			$this->customerID($proxy->perform('customerID'));
			$this->setOrder();
			$this->session(true);

			$proxy->perform('rewindOrder');
			while($item = $proxy->perform('nextOrderItem')) {
				$this->addBasketItem($item);
			}

			$this->addBasketItemList($proxy->perform('specialItems'));
			$id = $this->deliveryAddress->id() . '.' . $this->basketID();

			$proxy->perform('finalizeBasket');

			if($id != $this->id()) {
				if($this->check()) {
					$this->id = $id;
					$proxy->perform('deleteBasket', $this->iclearID);
					$rc = true;
				} else {
					$this->addError(IC_ERROR_BASKET_CHECK_FAILED);
				}
			}
			$_SESSION['icSessionID'] = $this->sessionID();
			$_SESSION['icBasketID'] = $this->basketID();
		}
		return $rc;
	}

	// php4 patch 2 update core cache
	function updateCache() {
		$this->icCore->setObject('IclearBasket', $this);
	}

	/**
	 * removes the core object from $this and property objects
	 * which contains a core, deliverAddress, e.g.
	 */
	function unsetCore() {
		parent::unsetCore();
		if(isset($this->deliveryAddress) && $this->deliveryAddress) {
			$this->deliveryAddress->unsetCore();
		}
	}

	/**
	 * set the core object of $this and property objects
	 * which contains a core, deliverAddress, e.g.
	 */
	function setCore(&$icCore) {
		parent::setCore($icCore);
		if(isset($this->deliveryAddress) && $this->deliveryAddress) {
			$this->deliveryAddress->unsetCore();
		}
		 
	}
}



?>
