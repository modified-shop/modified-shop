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

  class IclearWSDL extends IclearBase {
  	
  	var $_outfile = '';
  	
  	function IclearWSDL(&$icCore) {
  		parent::IclearBase($icCore);
      if(defined('IC_SID') && IC_SID == 'ICINSTALL') {
        // installer mode
        $this->_outfile = IC_INSTALL_DIR . IC_WSDL_CUSTOMER;
      } else {
        // live system
        $this->_outfile = $this->getPath('iclear') . IC_WSDL_CUSTOMER;
      }
  	}
  	
  	function enabled() {
  		return $this->_enabled;
  	}
  	
    function compare($force = false) {
    	$rv = false;
    	if(isset($_SESSION['ic_update']['files']['wsdl'])) {
    		$rv = $_SESSION['ic_update']['files']['wsdl']->equals;
    	} else {
    		$icUpdate = $this->icCore->getInstance('IclearUpdate');
	      $local = file_get_contents($this->_outfile);
	      $local = preg_replace('/soap:address location=".*?"/', 'soap:address location=""', $local);
	      $localmd5 = md5($local);
	      if($remotemd5 = $icUpdate->getRemoteContent(IC_URI_WSDL_CUSTOMER_MD5)) {
		      $update = new stdClass();
		      $update->type = 'wsdl';
		      $update->file = basename($this->_outfile);
		      $update->pathlocal = $this->_outfile;
		      $update->equals = $localmd5 == $remotemd5;
		      $update->localmd5 = $localmd5;
		      $update->remotemd5 = $remotemd5;
          $update->classname = $this->_classname;
		      $icUpdate->addUpdate($update);
		      $_SESSION['ic_update']['files']['wsdl'] = $update;
	      }
    	}
      return $rv;
    }
    
    function update($obj = false) {
    	$rv = false;
    	if($obj) {
	    	$icCore =& $this->icCore;
	      $icUpdate = $icCore->getInstance('IclearUpdate');
	    	$url = IC_URI_WSDL_CUSTOMER . 'actor=' . base64_encode($icCore->getBaseURL() . IC_FILE_LOCAL_ENDPOINT);
	    	
	    	if($obj->content = $icUpdate->getRemoteContent($url)) {
	    		$rv = $icUpdate->write($obj);
	    	}
    	}
      return $rv;
    }
  }
