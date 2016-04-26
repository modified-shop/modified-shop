<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_query_installer.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.2 2002/03/02); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_db_query_installer.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_db_query_installer.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_db_query_installer($query, $type, $link = 'db_link') {
    global ${$link};
    if (!$type) echo 'TYPE ERROR: xtc_db_query_installer<br>';
    switch ($type) {
      case 'mysql':
        return mysql_query($query, ${$link});
        break;
      case 'mysqli':
        return mysqli_query(${$link}, $query);
        break;
    }        
  }

  function xtc_db_error_installer($type, $link = 'db_link') {
    global ${$link};
    
    switch ($type) {
      case 'mysql':
        return mysql_error();
        break;
      case 'mysqli':
        return mysqli_error(${$link});
        break;
    }        
  }
  
  function xtc_db_get_server_info($type, $link = 'db_link') {
    global ${$link};
    
    switch ($type) {
      case 'mysql':
        return mysql_get_server_info();
        break;
      case 'mysqli':
        return mysqli_get_server_info(${$link});
        break;
    }        
  }

  function xtc_db_get_client_info($type, $link = 'db_link') {
    global ${$link};
    
    switch ($type) {
      case 'mysql':
        return mysql_get_client_info();
        break;
      case 'mysqli':
        return mysqli_get_client_info(${$link});
        break;
    }        
  }

  function xtc_db_input_installer($string, $type, $link = 'db_link') {
    global ${$link};

    switch ($type) {
      case 'mysql':
        if (function_exists('mysql_real_escape_string')) {
          return mysql_real_escape_string($string, ${$link});
        } elseif (function_exists('mysql_escape_string')) {
          return mysql_escape_string($string);
        }
        break;
      case 'mysqli':
        if (function_exists('mysqli_real_escape_string')) {
          return mysqli_real_escape_string(${$link}, $string);
        }
        break;
    }        

    return addslashes($string);
  }

  function xtc_db_num_row_installer($db_query, $type) {
    switch ($type) {
      case 'mysql':
        return mysql_num_rows($db_query);
        break;
      case 'mysqli':
        return mysqli_num_rows($db_query);
        break;
    }        
  }
 ?>