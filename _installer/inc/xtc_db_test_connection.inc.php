<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_test_connection.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.2 2002/03/02); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_db_test_connection.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function xtc_db_test_connection($database, $type) {
    global $db_error;
    
    if (!$type) echo 'TYPE ERROR: xtc_db_test_connection<br>';
    
    $db_error = false;

    if (!$db_error) {
      if (!@xtc_db_select_db($database, $type)) {
        $db_error = xtc_db_error_installer($type);
      } else {
        if (!@xtc_db_query_installer("SELECT count(*) FROM configuration",$type)) {
          $db_error = xtc_db_error_installer($type);
        }
      }
    }

    if ($db_error) {
      return false;
    } else {
      return true;
    }
  }
 ?>