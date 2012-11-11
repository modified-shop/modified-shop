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

if(!defined('IC_SEC')) {
	die('No external calls allowed!');
}

// 20110908 CA fixed Version for MAC OS Server
if(stristr(PHP_OS, 'WIN') && !stristr(PHP_OS, 'DARWIN')) { 
	define('IC_DS', '\\');
} else {
	define('IC_DS', '/');
}


define('IC_CONFIG_VERSION', '$Id$');
define('IC_SERVICE_VERSION', '1.0');

define('IC_URI_BASE', 'https://services.iclear.de/');
define('IC_URI_SERVICES', IC_URI_BASE . 'services/dl/' . IC_SERVICE_VERSION . '/');

define('IC_OPERATION_STD', 1);
define('IC_OPERATION_DIRECT', 2);
define('IC_OPERATION_TYPE', IC_OPERATION_STD);

/*
 * load API configuration parameters
 */
icLoadConfig('IclearConfig');
icLoadConfig('IclearSOAP', true);
icLoadErrors();

if( defined('IC_DEBUG_DISPLAY_ERRORS') && IC_DEBUG_DISPLAY_ERRORS ) {
	function_exists('error_reporting') && error_reporting(E_ALL);
	function_exists('ini_set') && ini_set('display_errors', 1);
}

// Patch 2 circumvent usage of constants in ini files under PHP4
// endpoint WSDL declaration files
define('IC_WSDL_ACCEPT_ORDER', 'ICAcceptOrder.wsdl');
define('IC_XSD_ACCEPT_ORDER', 'ICAcceptOrder.xsd');
define('IC_WSDL_ORDER', 'ICOrderPort?wsdl');
define('IC_WSDL_USER', IC_WSDL_ORDER);

define('IC_URI_ORDER_SERVICES', IC_URI_SERVICES . IC_WSDL_ORDER);
define('IC_URI_USER_SERVICES', IC_URI_SERVICES . IC_WSDL_USER);

/**
 * Internal constants follows
 *
 */

define('IC_DEBUG_DIRECT', 1);
define('IC_DEBUG_TRANS', 2);
define('IC_URI_DEBUG_HOST', 'http://api.iclear.de/EndpointDebugger-2.0/');
define('IC_URI_DEBUG_ORDER', IC_URI_DEBUG_HOST  . 'services/' . IC_SERVICE_VERSION . '/' . basename(IC_WSDL_USER, '.wsdl') . '.php?wsdl');
define('IC_URI_DEBUG_USER', IC_URI_DEBUG_ORDER);

// this const is used 4 alternative endpoints, used by IclearSOAP extension
define('IC_LOCAL_SOAP_ENDPOINT', '');
define('IC_VERIFY_LOCAL_ENDPOINT', true);

define('TABLE_IC_ORDERS', 'orders_iclear');
define('IC_TABLE_ORDERS', 'iclear');

// ORDER STATES
define('IC_ORDER_OPEN', -1);
define('IC_ORDER_OK', 0);
define('IC_ORDER_WAIT', 1);
define('IC_ORDER_CANCEL', 2);

/**
 * config loaders
 */
function icLoadConfig($name = '', $buildArray = false) {
	$rv = false;
	$path = dirname(__FILE__) . IC_DS . $name . '.ini';
	if(file_exists($path)) {
		foreach(parse_ini_file($path) AS $key => $val) {
			define($key, $val);
			if($buildArray) {
				$keys[] = $key;
				$search[] = '/\b' . $key . '\b/';
				$replace[] = $val;
			}
		}
		if($buildArray) {
			$rv = array('keys' => $keys, 'search' => $search, 'replace' => $replace);
		}
	}
	return $rv;
}

function icLoadErrors() {
	$path = dirname(__FILE__) . IC_DS . 'IclearError.ini';
	if(file_exists($path)) {
		foreach(parse_ini_file($path) AS $key => $val) {
			list($id, $msg) = explode(':', $val);
			define($key, $id);
			define($key.'_MSG', $msg);
		}
	}
}
