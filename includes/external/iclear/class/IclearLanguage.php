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

  class IclearLanguage {
    var $lang = false;
    
    var $iso = '';
    
    var $ini = array();
    
    function IclearLanguage($languageISO = '') {
      $this->lang = false;
      if($languageISO) {
        $this->setLanguage($languageISO);
      }
      return $this->lang ? true : false;
    }
    
    function flush() {
      $this->ini = array();
    }
    
    function getISO() {
      return $this->iso;
    }
    
    function setLanguage($languageISO = 'de', $file = '') {
      $rc = false;
      $languageISO = strtolower($languageISO);
      if($this->iso != $languageISO && isset($this->ini[$languageISO]) && $this->ini[$languageISO]) {
        $this->lang =& $this->ini[$languageISO];
        $rc = true;
      } else if($languageISO) {
        if($file) {
          $path = $file;
        } else {
          $path = realpath(dirname(__FILE__) . '/../language/' . $languageISO . '.ini');
        }
        
        if(is_file($path)) {
          $this->ini[$languageISO] = parse_ini_file($path);
          $this->lang =& $this->ini[$languageISO];
          $rc = false; 
        }
      }
      if($rc) {
        $this->iso = $languageISO;
      }
      return $rc;
    }
    
    function getParam($key = '') {
      $val = '';
      if($key && isset($this->lang[$key])){
        if (defined('IC_OPERATION_TYPE') && defined('IC_OPERATION_DIRECT')) {
          if(IC_OPERATION_TYPE == IC_OPERATION_DIRECT && preg_match('/^INFO_EXTENDED/i', $key)) {
            $key .= '_DIRECT';
          }
        }
        $val = $this->lang[$key];
      }
      return $val;
    }
  }
?>