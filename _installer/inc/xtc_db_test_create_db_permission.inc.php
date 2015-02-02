<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_test_create_db_permission.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.2 2002/03/02); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_db_test_create_db_permission.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function xtc_db_test_create_db_permission($database, $type) {
  global $db_error;
  if (!$type) echo 'TYPE ERROR: xtc_db_test_create_db_permission<br>';
  $db_created = false;
  $db_error = false;

  if (!$database) {
    $db_error = 'No Database selected.';
    return false;
  }

  if (!$db_error) {
    if (!@xtc_db_select_db($database, $type)) {
      $db_created = true;
      if (!@xtc_db_query_installer("CREATE DATABASE '" . xtc_db_input_installer($database) . "'", $type)) {
        $db_error = xtc_db_error_installer($type);
      }
    } else {
      $db_error = xtc_db_error_installer($type);
    }

    if (!$db_error) {
      if (@xtc_db_select_db($database, $type)) {
        if (@xtc_db_query_installer("CREATE TABLE temp ( temp_id int(5) )", $type)) {
          if (@xtc_db_query_installer("DROP TABLE temp", $type)) {
            if ($db_created) {
              if (@xtc_db_query_installer("DROP DATABASE '" . xtc_db_input_installer($database) . "'", $type)) {
              } else {
                $db_error = xtc_db_error_installer($type);
              }
            }
          } else {
            $db_error = xtc_db_error_installer($type);
          }
        } else {
          $db_error = xtc_db_error_installer($type);
        }
      } else {
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