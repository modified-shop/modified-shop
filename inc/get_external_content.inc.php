<?php
/* -----------------------------------------------------------------------------------------
   $Id: get_external_content.inc.php 4202 2013-01-10 20:27:44Z Tomcraft1980 $

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function get_external_content($url, $timeout='3') {
    if (function_exists('curl_version') && is_array(curl_version())) {
      $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $data = curl_exec($ch);
              curl_close($ch);
    }elseif (function_exists('file_get_contents')) {
      $opts = array('http' => array('method'=>"GET", 'header'=>"Content-Type: text/html; charset=UTF-8", 'timeout' => $timeout));
      $context = stream_context_create($opts); 
      $data = @file_get_contents($url, false, $context);
    } elseif (function_exists('fopen')) {
      ini_set('default_socket_timeout', $timeout);  
      $handle = fopen($url, 'r');   
      $data = @stream_get_contents($handle);
      fclose($handle);
    }  
    return $data;
  }
?>