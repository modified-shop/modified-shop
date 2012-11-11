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

define('IC_SEC', true);
// $icCore is instantiated in IclearProxy inclusion
global $icCore;

chdir(realpath(dirname(__FILE__) . '/..'));

require('./iclear/class/IclearProxy.php');

/***************************************************************
 * Local configuration:
 * If there are special modules or constant needed in the shop
 * system rename / copy iclear/config.local.sample.php to
 * config.local.php and add the needed code there
 ***************************************************************/
define('IC_CONFIG_LOCAL', $icCore->getPath('config') . 'config.local.php');
if(file_exists(IC_CONFIG_LOCAL)) {
	require_once IC_CONFIG_LOCAL;
}

if (isset ( $_GET ['wsdl'] ) || isset ( $_GET ['xsd'] )) {
	$endpoint = $icCore->getBaseURL () . 'iclear/';
	if (isset ( $_GET ['wsdl'] )) {
		$file = $icCore->getPath ( 'wsdl' ) . IC_WSDL_ACCEPT_ORDER;
	} elseif (isset ( $_GET ['xsd'] ) && $_GET ['xsd'] == 1) {
		$file = $icCore->getPath ( 'wsdl' ) . IC_XSD_ACCEPT_ORDER;
	}
	if (is_file ( $file )) {
		header ( 'Content-Type: text/xml' );
		$content = file_get_contents ( $file );
		$content = str_replace ( '#endpoint#', $endpoint, $content );
		print $content;
	} else {
		print IC_WSDL_ACCEPT_ORDER . ' not found - iclear API isn&apos;t correct installed! Please run installer first!';
	}
	exit ();
}

$icProxy = new IclearProxy($icCore);

$HTTP_RAW_POST_DATA = file_get_contents("php://input");

if($HTTP_RAW_POST_DATA) {
	$icCore->log($HTTP_RAW_POST_DATA, true, 'w');
}

// SOAP stuff goes here
$server =& $icCore->getSoapServer('IclearSoapServer');
$server->init($icCore->getBaseURL() . 'iclear/' . '?wsdl');

// disable error reporting if it's not enabled in config
if(IC_DEBUG_DISPLAY_ERRORS) {
	$icCore->setDebugLevel(E_ALL);
} else {
	$icCore->setDebugLevel(0);
}

// removing bogus namespace definitions - nusoap doesn't like it!
$HTTP_RAW_POST_DATA  = preg_replace('/xmlns=""/', '', $HTTP_RAW_POST_DATA);
$server->handle($HTTP_RAW_POST_DATA);

// mapped SOAP functions follows

/**
 * patch 2 intercept broken SOAP definition of basketItemList
 * If there's only one item in basket, BasketItem is an array with all fields of that item
 * If there are multiple items in basket, BasketItem is a numerical array of arrays, each one a item
 *
 * @param array $basketItemList
 * @return array $basketItemList
 */
function checkBasketItemList($basketItemList = false) {
	if($basketItemList && is_array($basketItemList)) {
		if(isset($basketItemList['BasketItem'][IC_SOAP_ITEM_NO])) {
			$basketItemList[0] = $basketItemList['BasketItem'];
			unset($basketItemList['BasketItem']);
		}elseif(isset($basketItemList['BasketItem']) && is_array($basketItemList['BasketItem'])) {
			$basketItemList = $basketItemList['BasketItem'];
		}
	}
	return $basketItemList;
}

/**
 * loads the basket by it's ID and checks integrity of it
 * @param stdClass $arg0
 */
function validateBasket($arg0) {
	global $icCore;

	$basket = false;

	if($proxy = $icCore->getProxy()) {
		$basket = $proxy->perform('loadBasket', array($arg0->basketID));
	}

	if(!$basket) {
		$proxy->addError('Specified basket not found');
	} else {
		$basket->acceptOK($arg0);
	}

}


/**
 * SOAP acceptOrder function
 *
 * @param stdClass $req
 * @return array (SOAP acceptOrderReturn)
 */
function acceptOrder($req) {
	global $icCore;

	$arg0 = _preprocess($req);

	validateBasket($arg0);

	$proxy = $icCore->getProxy();
	$basket = $icCore->getBasket();


	$res = array(
	IC_SOAP_SESSION_ID 	=> $arg0->sessionID,
	IC_SOAP_STATUS_ID 		=> $basket->errorCode(),
	IC_SOAP_STATUS_MESSAGE => $basket->errorCode() ? $basket->lastError() : 'OK',
	IC_SOAP_BASKET_ID => $arg0->basketID,
	IC_SOAP_URL_SHOP => $proxy->perform('shopURL', array($basket->orderAccepted()))
	);
	$icCore->log(__FUNCTION__ . ' result' . print_r($res, true));
	return array('return' => $res);
}

function apiInfo($infoType = '') {
	global $icCore;
	$params = array();

	$iniFile = $icCore->getPath('iclear') . 'IclearConfig.ini';
	foreach(parse_ini_file($iniFile) AS $key => $val) {
		$params[] = array(
	    'key' => $key,
	    'value' => $val,
	    'label' => '',
		);
	}

	$proxy =& $icCore->getProxy();
	$params[] = array(
	  'key' => 'IC_API_ENABLED',
	  'value' => $proxy->perform('enabled') ? 'yes' : 'no',
	  'label' => '',
	);
	 
	$params[] = array(
	  'key' => 'IC_TABLE_EXIST',
	  'value' => $proxy->perform('tableExist') ? 'yes' : 'no',
	  'label' => '',
	);
	 
	$params[] = array(
	  'key' => 'IC_WRAPPER_ID',
	  'value' => $proxy->perform('id'),
	  'label' => '',
	);
	 
	$params[] = array(
	  'key' => 'IC_WRAPPER_VERSION',
	  'value' => $proxy->perform('version'),
	  'label' => '',
	);

	$params[] = array(
    'key' => 'IC_CORE_VERSION',
    'value' => $icCore->version(),
    'label' => '',
	);

	$params[] = array(
    'key' => 'IC_CONFIG_LOCAL',
    'value' => defined('IC_CONFIG_LOCAL_LOADED') ? 1 : 0,
    'label' => '',
	);

	$basket =& $icCore->getBasket();
	$out[] = array(
    'key' => 'IC_BASKET_VERSION',
    'value' => $basket->version(),
    'label' => '',
	);

	$icCore->log(__FUNCTION__ . ' result' . print_r($out, true));
	return $out;
}

/**
 * Preprocess incoming SOAP request object
 * Trancribes an array (given if NuSOAP server is in use) to
 * a stdClass object with same properties as the array keys
 *
 * @param stdClass $req
 * @return stdClass
 */
function _preprocess($req) {
	$order = null;
	if(is_object($req)) {
		$order = $req->arg0;
	} elseif(is_array($req)) {
		$order = new stdClass();
		foreach($req AS $key => $val) {
			if(is_scalar($val)) {
			 $order->$key = $val;
			}
		}

		// transcribe delivery address
		$address = new stdClass();
		foreach($req[IC_SOAP_DELIVERY_ADDRESS] AS $key => $val) {
			$address->$key = $val;
		}
		$order->{IC_SOAP_DELIVERY_ADDRESS} = $address;

		/* transcribe basket items:
		 * there's an bug in php - if there's only one item, it's assigned
		 * directly below $req[IC_SOAP_BASKET_ITEMS] and not in an indexed
		 * array!
		 */
		if(isset($req[IC_SOAP_BASKET_ITEMS][0])) {
			// multiple items
			foreach($req[IC_SOAP_BASKET_ITEMS] AS $rec) {
				$item = new stdClass();
				foreach($rec AS $key => $val) {
					$item->$key = $val;
				}
				$order->{IC_SOAP_BASKET_ITEMS}[] = $item;
			}
		} else {
			$item = new stdClass();
			foreach($req[IC_SOAP_BASKET_ITEMS] AS $key => $val) {
				$item->$key = $val;
			}
			$order->{IC_SOAP_BASKET_ITEMS}[] = $item;
		}
	}
	return $order;
}
?>