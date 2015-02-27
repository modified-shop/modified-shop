<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_queryCached.inc.php 5463 2013-09-03 13:52:45Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2006 XT-Commerce (xtcPrice.php 1316 2005-10-21)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  die('Deprecated File: '.basename(__FILE__).'. Use db_functions_mysql(i) instead.');
/*
  function xtc_db_queryCached($query, $link = 'db_link') {
    global $$link;

    // get HASH ID for filename
    $id = md5($query);

    // cache File Name
    $file = SQL_CACHEDIR . $id . '.mod.cache';

    // file life time
    $expire = DB_CACHE_EXPIRE;

    if (file_exists($file) && filemtime($file) > (time() - $expire)) {

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
        $queryStartTime = array_sum(explode(" ",microtime()));
      }

      // get cached resulst
      $result = unserialize(base64_decode(file_get_contents($file)));

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
        $queryEndTime = array_sum(explode(" ",microtime())); 
        $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
        if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY CACHED ' . $query . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
        $result_error = mysql_error();
        if ($result_error) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR CACHED ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
      }

    } else {

      if (file_exists($file)) @unlink($file);

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
        $queryStartTime = array_sum(explode(" ",microtime()));
      }

      // get result from DB and create new file
      $result = mysql_query($query, $$link) or xtc_db_error($query, mysql_errno(), mysql_error());

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
        $queryEndTime = array_sum(explode(" ",microtime())); 
        $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
        if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY ' . $query . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
        $result_error = mysql_error();
        if ($result_error) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
      }

      // fetch data into array
      $records = array();
      while ($record = xtc_db_fetch_array($result)) {
        $records[]=$record;
      }
      
      // safe result into file.
      $stream = base64_encode(serialize($records));
      $fp = fopen($file,"w");
            fwrite($fp, $stream);
            fclose($fp);
      $result = unserialize(base64_decode(file_get_contents($file)));

   }

    return $result;
  }
*/
?>