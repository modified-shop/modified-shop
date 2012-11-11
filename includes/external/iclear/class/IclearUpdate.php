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

class IclearUpdate extends IclearBase {
	
	var $_enabled = false;
	var $_updates = array();
	
	function IclearUpdate(&$icCore) {
		parent::IclearBase($icCore);
		
		if(!isset($_SESSION['ic_update']) || !is_array($_SESSION['ic_update'])) {
			$_SESSION['ic_update'] = array();
		} else {
			foreach($_SESSION['ic_update'] AS $rec) {
				if(is_object($rec)) {
					$this->_updates[$rec->type] =& $rec;
				}
			}
		}
		
    $this->_enabled(true);
	}
	
	function enabled() {
		return $this->_enabled;
	}
	
  function count() {
    return sizeof($this->_updates);
  }
   
  function addUpdate($update = false) {
    if($update && !isset($this->_updates[$update->localmd5])) {
      $this->_updates[$update->localmd5] = $update;
    }
  }
  
    function getRemoteContent($url = '', $force = false) {
      $content = '';
      if($url && ($this->_enabled || $force)) {
        if(function_exists('curl_init')) {
          $cu = curl_init();
          curl_setopt($cu, CURLOPT_URL, $url);
          curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($cu, CURLOPT_BINARYTRANSFER, 1);
          $content = curl_exec($cu);
          curl_close($cu);
        } elseif(function_exists('fopen')) {
          if($fp = fopen($url, 'rb')) {
            $content = '';
            while(!feof($fp)) {
              $content .= fread($fp, 1024);
            }
            fclose($fp);
          }
        }
      }      
      return trim($content);
    }
    
  function updatecount() {
  	return sizeof($this->_updates);
  }
  
  function filelist() {
  	if(!$this->_updates && isset($_SESSION['ic_update']['files'])) {
  		$this->_updates = $_SESSION['ic_update']['files'];
  	}
  	return $this->_updates;
  }
  
  function install($updates = false) {
  	if(is_array($updates)) {
  		$icCore =& $this->icCore;
  		$icError =& $icCore->getInstance('IclearError');
  		$icLang =& $icCore->getLanguage();
  		foreach($updates AS $id) {
  			if(isset($this->_updates[$id])) {
  				$obj =& $this->_updates[$id];
  				if($class = $this->icCore->getObject($obj->classname)) {
  					if($class->update($obj)) {
  						$icError->addInfo($obj->file . ': ' . $icLang->getParam('UPDATE_OK'));
  					}
  				}
  			}
  		}
  	}
  }
  
	function write($obj = '') {
		$rv = false;
		if($obj && $obj->content) {
			if($fp = fopen($obj->pathlocal, 'wb')) {
				fputs($fp, $obj->content);
				fclose($fp);
				$rv = true;
			}
		}
		return $rv;
	}
    
  
  
  
  function _enabled($force = false) {
    if($force || !isset($_SESSION['ic_update']['enabled'])) {
      $this->_enabled = $this->getRemoteContent(IC_URI_WSDL_CUSTOMER_MD5, $force) ? true : false;
      $_SESSION['ic_update']['enabled'] = $this->_enabled;
    } else {
      $this->_enabled = $_SESSION['ic_update']['enabled'];
    }
    return $this->_enabled;
  }
	
}