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

/**
 *
 * @package IC
 */
if(!defined('IC_SEC')) {
  die('No external calls allowed!');
}

require realpath(dirname(__FILE__) . '/../config/IclearConfig.php');

/**
 *
 * IclearCore
 * The core object is the central object in this interface
 * It's a mixture of a factory and a proxy
 * A reference to this object can be found in nearby each iclear related object, thus, if one does a change here,
 * this change can be found elsewhere!
 *
 * @author dabr
 * @package IC
 *
 */
class IclearCore {
  /**
   * Identifies shop system, xtc304sp2, e.g.
   *
   * @var string
   * @access private
   */
  var $systemID = '';
  /**
   * Indentifies clone (fork) of main shop system, modified-shop-v102, e.g.
   *
   * @var string
   * @access private
   */
  var $cloneID = '';

  /**
   * Cache 4 directory pathways
   *
   * @var array
   * @access private
   */
  var $dirCache = array();
  /**
   * Cache 4 URLs
   *
   * @var array
   * @access private
   */
  var $urlCache = array();
  /**
   * Cache 4 factory objects
   * @var array
   * @access private
   */
  var $objectCache = array();
  /**
   * Uniform names for tags in preprocessed WSDL files
   * @var array
   * @access private
   */
  var $soapVarMap = false;
  /**
   * SoapClient object: IclearSoapClient.[nusoap|php5].php class
   * @var IclearSoapClient
   * @access private
   */
  var $proxy = false;
  /**
   * IclearLanguage object
   * @var IclearLanguage
   * @access private
   */
  var $lang = false;

  /**
   * IclearError object
   * @var IclearError
   * @access private
   */
  var $error = false;
  /**
   * IC version string (subversion $Id$)
   * @var string
   * @access private
   */
  var $icVersion = '';

  /**
   * filepointer to log file (defined in IclearConfig.ini)
   * @var ressource_handle
   * @access private
   */
  var $fp;

  /**
   * Constructor: receives language ISO2 as param (de, e.g.)
   * calls detectSystem and assigns language object if language is given
   * @param string $icLang
   */
  function IclearCore($icLang = false) {
    $this->icVersion = '$Id$';

    $this->detectSystem();
    if(!class_exists('IclearBase', false)) {
      require_once $this->getPath('class') . 'IclearBase.php';
    }

    if($icLang) {
      $this->lang =& $icLang;
    }

    $this->error = $this->getObject('IclearError');

  }
  /**
   * Return version of IclearCore object (subversion $Id$)
   */
  function version() {
    return $this->icVersion;
  }
  /**
   * Returns current debug level - see setDebugLevel()
   */
  function debug() {
    return IC_DEBUG;
  }
  /**
   * Enables PHP's debugging output
   * $level parameter expects PHP E_* constant values, E_ALL, e.g.
   * @param int $level
   */
  function setDebugLevel($level = 0) {
    if($level) {
      function_exists('error_reporting') && error_reporting($level);
      function_exists('ini_set') && ini_set('display_errors', 1);
    } else {
      function_exists('error_reporting') && error_reporting(0);
      function_exists('ini_set') && ini_set('display_errors', 0);
    }
  }
  /**
   * Returns systemID of detected shop system
   *
   * @return systemID
   */
  function systemID() {
    return $this->systemID;
  }
  /**
   * Returns cloneID (forkID) of detected shop system
   *
   * @return string cloneID
   */
  function cloneID() {
    return $this->cloneID;
  }
  /**
   * Returns wrapperID of detected system
   * Used 2 load correct wrapper in IclearProxy.
   * wrapperID = 'systemID.cloneID'
   *
   * @return string wrapperID
   */
  function wrapperID() {
    $rv = $this->systemID;
    if($this->cloneID) {
      $rv .= '.' . $this->cloneID;
    }
    return $rv;
  }

  /**
   * This function is used to detect the enclosing shop system type.
   * Detection is performed on filesystem level by existence of shop specific files
   * <b>If a shop system isn't detected correct, this function will be
   * the subject of tailoring</b>
   *
   * @access public
   * @return string $systemID
   */
  function detectSystem() {
    $this->systemID = '';
    $this->cloneID = '';
    $root = $this->getPath();

    if(defined('IC_SYSTEM_TYPE') && IC_SYSTEM_TYPE) {
      $this->systemID = IC_SYSTEM_TYPE;
    } else {
      // BOF - DokuMan - 2011-09-08 - set systemid & cloneid for modified eCommerce Shopsoftware
      /*
      if(is_dir($root.'includes/languages')) {
        // looks like an osc version
        if(file_exists($root.'includes/filenames.php')) {
          // it's a ms2 version - or higher
          $this->systemID = 'osc22ms2';
          if(file_exists($root.'includes/functions/affiliate_functions.php')) {
            $this->cloneID = 'xonic15';
          }
          elseif(file_exists($root.'includes/template_bottom.php')) {
            $this->cloneID = 'osc231';
          }
        } else {
          $this->systemID = 'osc22ms1';
        }
      } else if(file_exists($root.'inc/xtc_add_tax.inc.php')) {
        // looks like a xtc version
        if(is_dir($root.'includes/classes/Smarty_2.6.10')) {
          // XT:C 3.0.4 SP1
          $this->systemID = 'xtc304sp1';
        } elseif(is_dir($root.'includes/classes/Smarty_2.6.14')) {
          // XT:C 3.0.4 SP2
          $this->systemID = 'xtc304sp2';
          if (is_dir($root.'gm')) {
            if (is_file($root.'system/core/MainFactory.inc.php')) { // 20110330 CA added support for gambiogx2
              $this->cloneID = 'gambiogx2';
            } else {
              $this->cloneID = 'gambio1014';
            }
          }
        } else if (is_dir($root.'shopstat') && (is_dir($root.'includes/classes/Smarty_2.6.22') || is_dir($root.'includes/classes/Smarty_2.6.26'))) {
          // modified eCommerce Shopsoftware
          $this->systemID = 'xtc304sp2';
          $this->cloneID = 'modified';
        }else if(is_dir($root.'includes/classes/Smarty_2.6.26')) {
          // commerce:seo
          $this->systemID = 'xtc304sp2';
        } else if (is_file($root.'admin/includes/ecbDbVersion.inc')) {
          $this->cloneID = 'ecb108';
        }
      } else if(is_dir($root.'xtAdmin')) {
        // seems 2B veyton
        $this->systemID = 'veyton4';
      } else if(is_dir($root.'app/code/core/Mage')) {
        // looks like Magento version 1.x
        if(is_dir($root . 'app/code/core/Mage/Centinel')) {
          $this->systemID = 'magento-1.4.x';
        } else if(is_dir($root . 'app/code/core/Mage')) {
          // TODO: check if this dir is already present in 1.2.x and below - in 1.0.x it isn't (there called Varien)
          $this->systemID = 'magento-1.3.x';
        } else {
          // this magento version is unknown!
        }
      } else if(is_file($root.'core/oxfunctions.php')) {
        //Oxid eShop
        $this->systemID = 'oxid-4.x';
        if (is_dir($root.'out/azure')) {
          $this->cloneID = 'azure';
        }
      } else if(is_file($root.'includes/config.JTL-Shop.ini.php')) {
            //JTL Shop 3.x
            $this->systemID = 'jtl-3.x';
      } else if(is_dir($root.'engine/Enlight')) {
        //ShopWare 3.5.x
        $this->systemID = 'shopware-3.5.x';
      }
    */
    // EOF - DokuMan - 2011-09-08 - set systemid & cloneid for modified eCommerce Shopsoftware
    }
    $this->systemID = 'xtc304sp2';
    $this->cloneID = 'modified';
    return $this->systemID;
  }

  /**
   * Returns the absolute or relative path of a directory.
   * location has one following possible values:
   * root - shop system root: ../iclear/
   * iclear - iclear dir
   * lib - iclear/lib
   * wrappers - iclear/wrappers
   * wsdl - iclear/wsdl
   * config - iclear/config
   *
   * @param string $location [root]
   * @param boolean $abs [true]
   */
  function getPath($location = 'root', $abs = true) {
    $pathout = '';
    if(!$location) {
      $location = 'root';
    }
    if(!isset($this->dirCache[$location])) {

      if( !isset($this->dirCache['root']) ) {
        // TODO: remove static root dir assignment!!! Or allow 2 specifiy alternative location...
        $root = preg_replace('!class$!', "", str_replace('\\', '/', dirname(__FILE__)));

        $this->dirCache['docrootarr'] = explode('/', $root);
        //array_shift($this->dirCache['docrootarr']);
        $this->dirCache['docroot'] = $root;
        $this->dirCache['root'] = preg_replace('!iclear/$!', "", $root);
        $this->dirCache['rootarr'] = explode('/', $this->dirCache['root']);
        unset($path);
      }

      if($location && $location != 'root') {
        $path[] = preg_replace('!/$!', '', $this->dirCache['root']); // remove trailing slash -> implode action below!
        switch($location) {
          case 'root':
            // TODO: actually nothing...
            break;

          case 'iclear':
            array_push($path, 'iclear');
            break;

          case 'lib':
            $path = array_merge($path, array('iclear', 'lib'));
            break;

          case 'class':
            $path = array_merge($path, array('iclear', 'class'));
            break;

          case 'config':
            $path = array_merge($path, array('iclear', 'config'));
            break;

          case 'wrapper':
            $path = array_merge($path, array('iclear', 'wrappers'));
            break;

          case 'wsdl':
            $path = array_merge($path, array('iclear', 'wsdl'));
            break;

          case 'config':
            $path = $this->dirCache['docrootarr'];
            array_pop($path);
            break;

        }
        $pathout = implode(IC_DS, $path) . IC_DS;
        $this->dirCache[$location] = $pathout;
      } else {
        $pathout = $this->dirCache['root'];
      }
    } else {
      $pathout = $this->dirCache[$location];
    }

    if(!$abs && $pathout) {
      $pathrel = preg_replace('!' . str_replace('\\', '/', $this->dirCache['root']) . '!', '', $this->dirCache[$location]);
      $pathout = IC_DS . implode(IC_DS, array($this->dirCache['rootarr'][sizeof($this->dirCache['rootarr']) - 2], $pathrel));
    }
    return $pathout;
  }

  /**
   * Returns the base URL of the shop system with protocol
   * Protocol is auto select by current request type (http/https) or forced by second param
   * If location is given, it's appended to the URL
   * URL is always slash terminated
   * Detects (visible) proxy requests and uses proxy host, if given
   *
   * @param string $location
   * @param boolean $https
   */
  function getBaseURL($location = '', $https = false) {
    // IIS patch - HTTPS is always present, but contains of in case of none secure calls
    if((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') || $https==true) {
      $protocol="https";
    } else {
      $protocol="http";
    }

    $requestURI = $_SERVER['PHP_SELF'];

    if( ($offset = strpos($requestURI, 'iclear')) === false ) {
      $offset = strrpos($requestURI, '/') + 1;
    }

    $path = substr($requestURI, 0,$offset);

    if(!defined('IC_URI_SHOP') || !IC_URI_SHOP) {
      if($this->proxy) {
        $server = $this->proxy->httpHost($https || isset($_SERVER['HTTPS']));
      } elseif(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $server = $_SERVER['HTTP_X_FORWARDED_HOST'];
      } else {
        $server = $_SERVER['SERVER_NAME'];
      }
      $url = $protocol . '://' . $server  . $path;
    } else {
      $url = IC_URI_SHOP;
    }
    $url = preg_replace('!//$!', '/', $url);

    return $url;
  }

  /**
   * Returns currently loaded IclearLanguage object or false
   *
   * @return IclearLanguage obect
   */
  function getLanguage() {
    $rv = false;
    if($this->lang) {
      $rv =& $this->lang;
    } else {
      $rv = $this->setLanguage();
    }
    return $rv;
  }

  /**
   * Load IclearLanguage object with given language
   *
   * @param string $languageISO [DE]
   * @return IclearLanguage object
   */
  function setLanguage($languageISO = 'DE') {
    if($languageISO) {
      $languageISO = strtolower($languageISO);
      if($this->lang) {
        $this->lang->setLanguage($languageISO);
      } else {
        $class = 'IclearLanguage';
        if(!class_exists($class, false)) {
          require_once $this->getPath('class') . $class.'.php';
        }
        $this->lang = new $class($languageISO);
      }
    }
    return $this->lang;
  }

  /**
   * returns IclearProxy object
   * works per default in singleton fashion
   *
   * @param boolean $instance [true]
   */
  function &getProxy($instance = true) {
    return $this->getObject('IclearProxy', $instance);
  }

  /**
   * returns IclearBasket object
   * works per default in singleton fashion
   *
   * @param boolean $instance [true]
   */
  function &getBasket($instance = true) {
    return $this->getObject('IclearBasket', $instance);
  }

  /**
   * inject an (altered) object into $this->objectCache[]
   * works only if class parameter matches object type (security)
   *
   * @param string $class
   * @param object $obj
   */
  function setObject($class ='', &$obj) {
    $rc = false;
    if(class_exists($class, false) && is_object($obj)) {
      $this->objectCache[$class] =& $obj;
      $rc = true;
    }
    return $rc;
  }

  /**
   * main factory method:
   * creates objects or returns instances of already created object
   * per default always a new object is created
   * @todo change basedir of classes 2 iclear/class
   * @todo add detection of soap types
   * tries to find object class files in ICLEAR basedir (iclear)
   * if filename is given (absolute or relative), it is used as base 4 require
   * if called object is a successor of IclearBase ($iclearError property exists) a reference of
   * $this and $this->iclearError is assigend 2 the object
   *
   * @param string $class
   * @param boolean $instance [false]
   * @param string $filename ['']
   * @return object
   */
  function &getObject($class, $instance = false, $filename = '') {
    $rv = false;
    if($instance && isset($this->objectCache[$class])) {
      $rv =& $this->objectCache[$class];
    } else {
      if(!class_exists($class, false)) {
        if($filename) {
          $path = $filename;
        } else {
          $path = $this->getPath('class') . $class.'.php';
        }
        require_once $path;
      }
      $rv = new $class($this);
      if(is_subclass_of($rv, 'IclearBase')) {
        $rv->icError =& $this->getObject('IclearError', true);
      }
      $this->objectCache[$class] =& $rv;
    }
    return $rv;
  }

  /**
   * returns an IclearSoapClient object
   * either IclearSoapClient.php5.php or IclearSoapClient.nusoap.php (if PHP5 extension isn't available)
   * per default always new client objects created (non singleton)
   *
   * @param boolean $instance
   * @return IclearSoapClient object
   */
  function &getSoapClient($instance = false) {

    $class = 'IclearSoapClient';
    if(! IC_INTERNAL_SOAP_CLIENT && class_exists('SoapClient', false)) {

      $classfile = $class.'.php5.php';
    } else {
      $classfile = $class.'.nusoap.php';
    }

    return $this->getObject($class, $instance, $classfile);
  }

  /**
   * returns an IclearSoapServer object
   * either IclearSoapServer.php5.php or IclearSoapServer.nusoap.php (if PHP5 extension isn't available)
   * per default always new client objects created (non singleton)
   *
   * @param boolean $instance
   * @return IclearSoapServer object
   */
  function &getSoapServer($instance = false) {
    $class = 'IclearSoapServer';

    // check if we can use PHP5 extension
    if(class_exists('SoapServer', false)) {
      $classfile = $class.'.php5.php';
    } else {
      $classfile = $class.'.nusoap.php';
    }
    return $this->getObject($class, $instance, $classfile);
  }

  /**
   * proxy function
   * encapsulates method calls of the underlying wrappers (and subsequently IclearProxy)
   * expects in $params an array which can be used with call_user_func_array()
   * in case of SOAP calls (key 'arg0' exists in array) $function->($params) is performed on $object
   *
   * @param object $object
   * @param string $function
   * @param array $params
   * @return mixed - result of $function()
   */

  function perform($object = false, $function = '', $params = false) {
    $rv = false;

    if(is_object($object) && $function) {
      if(!$params) {
        $params = array();
      }

      if( is_callable(array($object, $function)) ) {
        if(version_compare(PHP_VERSION, '5.0.0', '>')) {
          if(isset($params['arg0'])) {
            //@TODO handle exception case
            eval('
                try {
                  $rv = $object->$function($params);
                } catch(Exception $e) {
                  $this->error->addError("iclearCore perform(): object <".get_class($object)."> thorws an exception:".$e->getMessage()); //20110203 CA - added Error handling
                }');
          } else {
            eval('
                try {
                  $rv = call_user_func_array(array($object, $function), $params);
                } catch(Exception $e) {
                  $this->error->addError("iclearCore perform(): object <".get_class($object)."> thorws an exception:".$e->getMessage()); //20110203 CA - added Error handling
                }');
          }
        } else {
          $rv = call_user_func_array(array($object, $function), $params);
        }
      }
    }
    return $rv;
  }

  /**
   * write informations to iclear/soap-log.txt
   *
   * @param string $msg
   * @param string $xmlcomment
   * @param string $mode [a]
   */
  function log($msg = '', $xmlcomment = false, $mode = 'a') {
    if(IC_DEBUG_LOG && IC_DEBUG_LOG_FILE) {
      if( $fp = @fopen($this->getPath('iclear') . IC_DEBUG_LOG_FILE, $mode) ) {
        if(is_array($msg) || is_object($msg)) {
          $out = print_r($msg, true);
        } elseif($msg === null) {
          $msg = 'NULL';
        } else {
          $out = $msg;
        }
        $caller = debug_backtrace();
        $xmlcomment && fputs($fp, '<!-- start ');
        $self = $caller[0];
        if(isset($caller[1])) {
          $parent = $caller[1];
        } else {
          $parent = false;
        }
      
        $logmsg =   date('Y-m-d H:i:s') . "\n" .
        $self['file'] . '#' . $self['line'] . ' - ' . $self['function'] ."\n";
        if($parent && isset($parent['file'])) {
          $logmsg .= $parent['file'] . '#' . $parent['line'] . ' - ' . $parent['function'] ."\n";
        }
        $logmsg .= $out . "\n";

        fputs($fp, $logmsg);
        $xmlcomment && fputs($fp,  "  END -->\n");
        fclose($fp);
      }
    }
  }
}

global $icCore, $icLang;
if(!is_object($icCore)) {
  $icCore = new IclearCore($icLang);
}

?>
