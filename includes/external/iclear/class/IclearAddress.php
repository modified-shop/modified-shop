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

class IclearAddress extends IclearBase {
	
	var $address = false;
	
	var $id = '';
	
	var  $keys = array(
    IC_SOAP_ADDRESS_SALUTATION => array('obligate' => false),
    IC_SOAP_ADDRESS_FIRSTNAME => false,
    IC_SOAP_ADDRESS_LASTNAME =>  array('obligate' => true),
    IC_SOAP_ADDRESS_COMPANY => false,
    IC_SOAP_ADDRESS_STREET =>  array('obligate' => true),
    IC_SOAP_ADDRESS_STREET_NO =>  array('obligate' => false),
    IC_SOAP_ADDRESS_ZIPCODE =>  array('obligate' => true),
    IC_SOAP_ADDRESS_CITY =>  array('obligate' => true),
    IC_SOAP_ADDRESS_COUNTRY =>  array('obligate' => true),
	);
	
  function IclearAddress(&$icCore) {
  	$this->icVersion = '$Id$';
    parent::IclearBase($icCore);
  }
  
  function id() {
  	return $this->id;
  }
  
  function validateAddress(&$address) {
  	$rc = true;
  	if(!is_array($address)) {
  		$rc = false;
  	} else {
  		foreach($this->keys AS $key => $rec) {
  			if(!isset($address[$key])) {
  				break;
  			} elseif(isset($rec['obligate']) && $rec['obligate'] && !$address[$key]) {
  				$rc = false;
  			} else {
  				$address[$key] = $this->encodeUTF8($address[$key]);
  			}
  		}
  	}
  	return $rc;
  }
  
  function address($address = false) {
  	if($address && $this->validateAddress($address)) {
  		$this->address = $address;
  		$this->id = md5(implode('|', array_values($address)));
  	}
  	return $this->address;
  }
}
?>