<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_queryCached.inc.php 782 2005-02-19 19:26:00Z khan_thep $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/



  function xtc_db_queryCached($query, $link = 'db_link') {
    global $$link;

    // get HASH ID for filename
    $id=md5($query);


    // cache File Name
    $file=SQL_CACHEDIR.$id.'.xtc';

    // file life time
    $expire = DB_CACHE_EXPIRE; // 24 hours

    if (STORE_DB_TRANSACTIONS == 'true') {
      error_log('QUERY ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }

    if (file_exists($file) && filemtime($file) > (time() - $expire)) {

     // get cached resulst
        $result = unserialize(implode('',file($file)));

        } else {

         if (file_exists($file)) @unlink($file);

        // get result from DB and create new file
        $result = mysql_query($query, $$link) or xtc_db_error($query, mysql_errno(), mysql_error());

        if (STORE_DB_TRANSACTIONS == 'true') {
                $result_error = mysql_error();
                error_log('RESULT ' . $result . ' ' . $result_error . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
        }

        // fetch data into array
        $records = array();
        while ($record = xtc_db_fetch_array($result))
                $records[]=$record;


        // safe result into file.
        $stream = serialize($records);
        $fp = fopen($file,"w");
        fwrite($fp, $stream);
        fclose($fp);
        $result = unserialize(implode('',file($file)));

   }

    return $result;
  }
?>
