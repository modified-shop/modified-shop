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
      }
      if ($data=='' && function_exists('file_get_contents')) {
        $opts = array('http' => array('method'=>"GET", 'header'=>"Content-Type: text/html; charset=UTF-8", 'timeout' => $timeout));
        $context = stream_context_create($opts); 
        $data = @file_get_contents($url, false, $context);
      }
      if ($data=='' && function_exists('fopen')) {
        ini_set('default_socket_timeout', $timeout);  
        $handle = fopen($url, 'r');   
        $data = @stream_get_contents($handle);
        fclose($handle);
      }
      if ($rss) {
        $fp = fopen(RSS_FEED_CACHEFILE, "w+");
        fputs($fp, $data);
        fclose($fp);
      }
    } else {
      $fp = fopen(RSS_FEED_CACHEFILE, "rb");
      $data = fread($fp, filesize(RSS_FEED_CACHEFILE));
      fclose($fp);
    }
    
    return $data;
  }
?>