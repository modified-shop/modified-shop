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

class IclearSoapClient extends IclearBase {

	var $client = false;

	/**
	 * constructor
	 * expects at least the type of the WSDL client, which should be created (user/order)
	 * in case of order a shopID must be provided
	 * is in case of user a name and a password is given, a login will be performed
	 *
	 * @param [user|order] $type
	 * @param int $shopID
	 * @param string $sessionID
	 * @param string $user
	 * @param string $pass
	 * @return iclearWSDL
	 */
	function IclearSoapClient(&$icCore) {
		parent::IclearBase($icCore);
	}

	/**
	 * returns proxy object or false
	 *
	 * @return object NuSOAP proxy
	 *
	 */
	function getClient($uri, $deleteCache = false) {
		$this->client = false;
		try  {
			$params = array(
  		  'soap_version' => SOAP_1_1,
  		  'features' => SOAP_USE_XSI_ARRAY_TYPE,
			);
			
			if ($deleteCache) {
				$params['cache_wsdl'] = WSDL_CACHE_NONE;
			}

			if($this->icCore->debug() || IC_DEBUG_LOG) {
				$params['cache_wsdl'] = WSDL_CACHE_NONE;
				$params['trace'] = true;
			}

			if(IC_PROXY_HOST) {
				$params['proxy_host'] = IC_PROXY_HOST;
				if(IC_PROXY_PORT) {
					$params['proxy_port'] = IC_PROXY_PORT;
				}
			}

			$this->client = new SoapClient($uri, $params);
		} catch(Exception $e) {
			print "<!-- IcSOAP Exception: '" . $e->__toString() . "' -->";
		}
		return $this->client;
	}
}
?>
