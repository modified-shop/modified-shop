<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_data_seek.inc.php 5468 2013-09-03 14:07:28Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_db_data_seek.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_db_data_seek.inc.php 899 2005-04-29 02);

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  die('Deprecated File: '.basename(__FILE__).'. Use db_functions_mysql(i) instead.');
/*
  function xtc_db_data_seek($db_query, $row_number,$cq=false) {

    if (defined('DB_CACHE') && DB_CACHE == 'true' && $cq) { //Dokuman - 2011-02-11 - check for defined DB_CACHE
      if (!count($db_query)) { 
        return;
      }
      return $db_query[$row_number];
    } else {
      if (!is_array($db_query)) {
        return mysql_data_seek($db_query, $row_number);
      }
    }
  }
*/
?>