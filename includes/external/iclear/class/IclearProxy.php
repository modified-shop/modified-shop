<?php
// $Id$

/*
  iclear payment system - because secure is simply secure
  http://www.iclear.de

  Copyright (c) 2004 - 2009 iclear GmbH

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
  
  define('IC_WRAPPER_EXT', '.config.php');
  
  
  global $icCore, $icLang;

  if(!class_exists('IclearCore', false)) {
    require_once dirname(__FILE__) .'/IclearCore.php';
    $icCore = new IclearCore($icLang);
  }
  
  if(!class_exists('IclearWrapperBase', false)) {
    require_once $icCore->getPath('class') . 'IclearWrapperBase.php';
  }
  
  if(!class_exists('IclearLanguage', false)) {
    require_once $icCore->getPath('class') . 'IclearLanguage.php';
  }
  
  if(!class_exists('IclearSOAP', false)) {
    require_once $icCore->getPath('class') . 'IclearSOAP.php';
  }
  
  if(!class_exists('IclearBasket', false)) {
    require_once $icCore->getPath('class') . 'IclearBasket.php';
  }
  
  if(!class_exists('IclearAddress', false)) {
    require_once $icCore->getPath('class') . 'IclearAddress.php';
  }
  
  class IclearProxy extends IclearBase {
    var $wrapper = false;
    
    function IclearProxy(&$icCore) {
    	$this->icVersion = '$Id$';
    	parent::IclearBase($icCore);
      $this->loadWrapper();
      
      // static value here 2 prevent subsequent DB access
      if($this->perform('enabled')) {
	      static $basket;
	      if(!$basket && isset($_SESSION['icBasketID'])) {
	        // needed 4 session based basket recreation
	        $this->perform('loadBasket', array($_SESSION['icBasketID']));
	      }
      }
      
    }
    
    function loadWrapper() {
    	$icCore =& $this->icCore;
    	$this->wrapper = false;
    	
    	$path = $icCore->getPath('wrapper'). $icCore->systemID();
    	
    	if($cloneID = $icCore->cloneID()) {
    		$proof = $path . '.' . $cloneID . IC_WRAPPER_EXT;

    		if(file_exists($proof)) {
    			$path = $proof;
    		} else {
    			$path .= IC_WRAPPER_EXT;
    		}
    	} else {
    		$path .= IC_WRAPPER_EXT;
    	}
    	
	    if(file_exists($path)) {
	    	$this->wrapper =& $this->icCore->getObject('IclearWrapper', false, $path);
	    }
	    return $this->wrapper;
    }
    
    
    function perform($function = '', $params = false) {
    	return $this->icCore->perform($this->wrapper, $function, $params);
    }
    
    function sessionID($sessionID = '') {
    	$rv = '';
    	if(is_object($this->wrapper) && method_exists($this->wrapper, 'sessionID')) {
    		$rv = $this->wrapper->sessionID($sessionID);
    	}
    	return $rv;
    }
    
    function httpHost($https = false) {
    	$rv = '';
    	if($this->wrapper && method_exists($this->wrapper, 'httpHost')) {
    		$rv = $this->wrapper->httpHost($https);
    	} else {
    		$rv = $_SERVER['HTTP_HOST'];
    	}
    	return $rv;
    }
    
    
    
    
  }

?>