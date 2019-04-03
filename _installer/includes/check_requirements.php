<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  $requirement_array = array();
  
  $php_flag = true;
  if(version_compare(phpversion(), PHP_VERSION_MIN, "<")){
    $error = true;
    $php_flag = false;
  }
  if(version_compare(phpversion(), PHP_VERSION_MAX, ">")){
    $php_flag = false;
    $error = true;
  }
  
  $requirement_array[] = array(
    'name' => 'PHP VERSION',
    'version' => phpversion(),
    'version_min' => PHP_VERSION_MIN,
    'version_max' => PHP_VERSION_MAX,
    'status' => $php_flag
  );
  
  
  $status = false;
  $status_tls = false;
  $ssl_version = 'undefined';
  $curl_version = array(
    'version' => 'undefined'
  );
  if (function_exists('curl_init')) {
    $status = true;
    $curl_version = curl_version();
    $remote_address = xtc_get_ip_address();
    
    if (substr($remote_address, 0, 4) != '127.' 
        && $remote_address != '::1' 
        && strpos($_SERVER['SERVER_NAME'], 'localhost') === false
        )
    {
      // check for SSL Version
      $ch = curl_init('https://www.howsmyssl.com/a/check');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
      curl_close($ch);
      $json = json_decode($data);
      if (is_object($json)) {
        $ssl_version = $json->tls_version;
      }
      if (version_compare(preg_replace('/[^0-9.]/', '', $ssl_version), SSL_VERSION_MIN, "<")) {
        $status_tls = false;
        $error = true;
      } else {
        $status_tls = true;
      }
    } else {
      $status_tls = false;
    }
  } else {
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'CURL VERSION',
    'version' => $curl_version['version'],
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );

  $requirement_array[] = array(
    'name' => 'SSL VERSION',
    'version' => $ssl_version,
    'version_min' => SSL_VERSION_MIN,
    'version_max' => '',
    'status' => $status_tls
  );

  
  if (function_exists('fsockopen')) {
    $status = true;
  } else {
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'FSOCKOPEN',
    'version' => '',
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );
  
  
  $status = false;
  $gd = gd_info();
  if ($gd['GD Version'] == '') {
    $gd['GD Version'] = 'undefined';
  }
  if ($gd['GIF Read Support'] == 1 || $gd['GIF Support'] == 1) {
    $status = true;
  } else {
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'GDlib VERSION',
    'version' => $gd['GD Version'],
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );
