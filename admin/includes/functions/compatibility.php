<?php
  /* --------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(compatibility.php,v 1.8 2003/04/09); www.oscommerce.com
   (c) 2003 nextcommerce (compatibility.php,v 1.6 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (compatibility.php 950 2005-05-14)

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  ////
  // Recursively handle magic_quotes_gpc turned off.
  // This is due to the possibility of have an array in
  // $HTTP_xxx_VARS
  // Ie, products attributes
  function do_magic_quotes_gpc(&$ar) {
    if (!is_array($ar)) return false;

    reset($ar); //DokuMan - 2011-05-09 - Reset compatibility array indexes when working through its elements
    while (list($key, $value) = each($ar)) {
      if (is_array($value)) {
        do_magic_quotes_gpc($value);
      } else {
        $ar[$key] = addslashes($value);
      }
    }
    reset($ar); //DokuMan - 2011-05-09 - Reset compatibility array indexes when working through its elements
  }

  // $HTTP_xxx_VARS are always set on php4
  if (!is_array($_GET))
    $_GET = array();
  if (!is_array($_POST))
    $_POST = array();
  if (!is_array($_COOKIE))
    $_COOKIE = array();

  // handle magic_quotes_gpc turned off.
  if (!get_magic_quotes_gpc()) {
    do_magic_quotes_gpc($_GET);
    do_magic_quotes_gpc($_POST);
    do_magic_quotes_gpc($_COOKIE);
  }

  //BOF - DokuMan - 2010-01-06 set default timezone if none exists (PHP 5.3 throws an E_WARNING)
  if ((strlen(ini_get('date.timezone')) < 1) && function_exists('date_default_timezone_set')) {
    date_default_timezone_set(@date_default_timezone_get());
  }
  //EOF - DokuMan - 2010-01-06 set default timezone if none exists (PHP 5.3 throws an E_WARNING)

  //BOF - DokuMan - 2011-01-06 - remove PHP3 compatiblity code
  //is_numeric()
  //is_uploaded_file()
  //move_uploaded_file()
  //EOF - DokuMan - 2011-01-06 - remove PHP3 compatiblity code

  //checkdnsrr on Windows plattforms available from PHP <= 5.3.0
  if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
      if(xtc_not_null($host) && xtc_not_null($type)) {
        @exec("nslookup -type=" . escapeshellarg($type) . " " . escapeshellarg($host), $output); // DokuMan - 2011-01-06 - added escapeshellarg
        while(list($k, $line) = each($output)) {
          if(preg_match("/^$host/i", $line)) { // Hetfield - 2009-08-19 - replaced deprecated function eregi with preg_match to be ready for PHP >= 5.3
            return true;
          }
        }
      }
      return false;
    }
  }

//BOF - DokuMan - 2011-05-25 - Clone implementation of wddx_deserialize
  if (!function_exists('wddx_deserialize')) {
    function wddx_deserialize($xmlpacket) {
      if ($xmlpacket instanceof SimpleXMLElement) {
          if (!empty($xmlpacket->struct)) {
              $struct = array();
              foreach ($xmlpacket->xpath("struct/var") as $var) {
                if (!empty($var["name"])) {
                  $key = (string) $var["name"];
                  $struct[$key] = wddx_deserialize($var);
                }
              }
              return $struct;
          } else if (!empty($xmlpacket->array)) {
              $array = array();
              foreach ($xmlpacket->xpath("array/*") as $var) {
                  array_push($array, wddx_deserialize($var));
              }
              return $array;
          } else if (!empty($xmlpacket->string)) {
              return (string) $xmlpacket->string;
          } else if (!empty($xmlpacket->number)) {
              return (int) $xmlpacket->number;
          } else {
              if (is_numeric((string) $xmlpacket)) {
                  return (int) $xmlpacket;
              } else {
                  return (string) $xmlpacket;
              }
          }
      } else {
        $sxe = simplexml_load_string($xmlpacket);
        $datanode = $sxe->xpath("/wddxPacket[@version='1.0']/data");
        return wddx_deserialize($datanode[0]);
      }
    }
  }
//EOF - DokuMan - 2011-05-25 - Clone implementation of wddx_deserialize
?>