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

class IclearBase {

	var $icCore = false;
	var $icError = false;

	var $icVersion = '';

	var $serviceVersion = IC_SERVICE_VERSION;

	function IclearBase(&$icCore) {
		if(!is_object($icCore)) {
			die("No Core object detected!");
		}
		$this->icCore =& $icCore;
	}

	function version() {
		return $this->icVersion;
	}

	/**
	 * returns the IclearCore object
	 *
	 * @return IclearCore
	 */
	function &getCore() {
		return $this->icCore;
	}

	function getLanguage() {
		return $this->icCore->getLanguage();
	}

	function isError() {
		$fnc = __FUNCTION__;
		return $this->icError->$fnc();
	}

	function addError($msg = '') {
		$fnc = __FUNCTION__;
		return $this->icError->$fnc($msg);
	}

	function getErrorCount() {
		$fnc = __FUNCTION__;
		return $this->icError->$fnc();
	}

	function dumpErrorList() {
		$fnc = __FUNCTION__;
		return $this->icError->$fnc();
	}

	function getErrorString($lineBreak = "\n") {
		$fnc = __FUNCTION__;
		return $this->icError->$fnc($lineBreak);
	}

	function lastError() {
		$fnc = __FUNCTION__;
		return $this->icError->$fnc();
	}
	
	function dropError() {
		$fnc = __FUNCTION__;
		return $this->icError->$fnc();
	}

	function encodeUTF8($string = '', $decode = true) {
		if($string) {
			if($decode) {
				$string = html_entity_decode($string);
			}
			/*if (function_exists(mb_detect_encoding)) {
				if(mb_detect_encoding($str) != 'UTF-8'){
					$string = utf8_encode($string);
				}
			}
			else*/ if(!$this->isUTF8($string)) {
				$string = utf8_encode($string);
			}
		}
		return $string;
	}
	 
	function isUTF8($string = '') {
		return  $string ? (utf8_encode(utf8_decode($string)) == $string) : true;
	}
	 
	/**
	 * removes the core object from instance
	 */
	function unsetCore() {
		$this->icCore = null;
	}
	 
	/**
	 * links the core object 2 this instance
	 */
	function setCore(&$icCore) {
		$this->icCore =& $icCore;
	}


}// class



?>
