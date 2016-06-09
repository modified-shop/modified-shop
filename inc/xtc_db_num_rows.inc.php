<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_num_rows.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_db_num_rows.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_db_num_rows.inc.php 1212 2005-09-12)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  die('Deprecated File: '.basename(__FILE__).'. Use db_functions_mysql(i) instead.');
/*
  function xtc_db_num_rows($db_query,$cq=false) {
  
    //BOF - DokuMan - 2010-08-30 - if db_query not a valid result, return false
    if ($db_query === false) {
      return false;
    }
    //EOF - DokuMan - 2010-08-30 - if db_query not a valid result, return false

    if (defined('DB_CACHE') && DB_CACHE == 'true' && $cq) { //Dokuman - 2011-02-11 - check for defined DB_CACHE
       if (!count($db_query)) {
         return false;
       }
       return count($db_query);
    } else {
       if (!is_array($db_query)) {
         return mysql_num_rows($db_query);
       }
    }
    //if (!is_array($db_query)) return mysql_num_rows($db_query);
    //if (!count($db_query)) return false;
    // return count($db_query);
  }
*/
?>