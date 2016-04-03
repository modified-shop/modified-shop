<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_query.inc.php 5463 2013-09-03 13:52:45Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_db_query.inc.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_db_query.inc.php 1195 2005-08-28)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  die('Deprecated File: '.basename(__FILE__).'. Use db_functions_mysql(i) instead.');
/*
  // include needed functions
  include_once(DIR_FS_INC . 'xtc_db_error.inc.php');

  function xtc_db_query($query, $link = 'db_link') {
    global $$link;

    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
      $queryStartTime = array_sum(explode(" ",microtime()));
    }
    
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

    return $result;
  }
*/
?>