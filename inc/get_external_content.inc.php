<?php
/* -----------------------------------------------------------------------------------------
   $Id: get_external_content.inc.php 4202 2013-01-10 20:27:44Z Tomcraft1980 $

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  define('RSS_FEED_CACHEFILE', DIR_FS_CATALOG.'export/rss_cache.txt');
  
  function get_external_content($url, $timeout='3', $rss=true) {
    $data = '';
    if (($rss && (!file_exists(RSS_FEED_CACHEFILE) || filemtime(RSS_FEED_CACHEFILE)<(time()-86400))) || !$rss) {
      if (function_exists('curl_version') && is_array(curl_version())) {
        $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
              curl_setopt($ch, CURLOPT_HEADER, FALSE);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $data = curl_exec($ch);
                curl_close($ch);

        if ($data && !check_valid_xml($data, $rss))
          $data='';
      }
      if ($data=='' && function_exists('file_get_contents')) {
        $opts = array('http' => array('method'=>"GET", 'header'=>"Content-Type: text/html; charset=UTF-8", 'timeout' => $timeout));
        $context = stream_context_create($opts); 
        $data = @file_get_contents($url, false, $context);

        if ($data && !check_valid_xml($data, $rss))
          $data='';
      }
      if ($data=='' && function_exists('fopen')) {
        ini_set('default_socket_timeout', $timeout);  
        $fp = @fopen($url, 'r');
        if (is_resource($fp)) {
          $data = @stream_get_contents($fp);
          fclose($fp);
        }

        if ($data && !check_valid_xml($data, $rss))
          $data='';
      }
      if ($rss && $data) {
        $fp = @fopen(RSS_FEED_CACHEFILE, "w+");
        if (is_resource($fp)) {
          fputs($fp, $data);
          fclose($fp);
        }
      }
    } else {
      $fp = @fopen(RSS_FEED_CACHEFILE, "rb");
      if (is_resource($fp)) {
        $data = fread($fp, filesize(RSS_FEED_CACHEFILE));
        fclose($fp);
      }
    }
        
    return $data;
  }
  
  function check_valid_xml($data, $rss) {
    $valid = true;
    
    if (!$rss)
      return $valid;
      
    libxml_use_internal_errors(true);
    libxml_clear_errors();
    
    if (class_exists('SimpleXmlElement')) {
      $xml = simplexml_load_string($data);
      if (sizeof(libxml_get_errors()) > 0) {
        $valid = false;
      }
    } else {
      $xml = new DOMDocument;
      $xml->load($data);
      if (sizeof(libxml_get_errors()) > 0) {      
        $valid = false;
      }
    }
    libxml_clear_errors();
    
    return $valid;
  }
?>