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

class IclearSoapServer extends IclearBase {

	var $icCore = false;

	var $server = false;

	function IclearSoapServer(&$icCore) {
		$this->icVersion = '$Id$';
		parent::IclearBase($icCore);
	}

	function init($url = '') {
		$this->server = false;
		if(IC_DEBUG) {
			$params = array(
  		  'trace' => 1,
  		  'cache_wsdl' => WSDL_CACHE_NONE,
			);
		} else {
			$params = array();
		}

		$server = new SoapServer($url, $params);

		if(is_object($server)) {
			$server->addFunction('acceptOrder');
			$this->server =& $server;
		}
		return $this->server;
	}

	function addFunction($name = '') {
		// nothing 2 do - compat with nusoap
	}

	function handle($input = '') {
		if($this->server) {
			$this->server->handle($input);
		}
	}

}



?>
