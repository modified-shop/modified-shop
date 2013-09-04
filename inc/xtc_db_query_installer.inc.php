<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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
    global $$link;

    // BOF - Dokuman - 2011-03-01 - get ready for UTF8
    /*
    mysql_query("SET names 'utf8'");
    mysql_query("SET CHARACTER SET 'utf8'");
    */
    // EOF - Dokuman - 2011-03-01 - get ready for UTF8
    
    switch ($type) {
      case 'mysql':
        return mysql_query($query, $$link);
        break;
      case 'mysqli':
        return mysqli_query($$link, $query);
        break;
    }        
  }

  function xtc_db_error_installer($type, $link = 'db_link') {
    global $$link;
    
    switch ($type) {
      case 'mysql':
        return mysql_error();
        break;
      case 'mysqli':
        return mysqli_error($$link);
        break;
    }        
  }
  
  function xtc_db_get_server_info($type, $link = 'db_link') {
    global $$link;
    
    switch ($type) {
      case 'mysql':
        return mysql_get_server_info();
        break;
      case 'mysqli':
        return mysqli_get_server_info($$link);
        break;
    }        
  }

  function xtc_db_get_client_info($type, $link = 'db_link') {
    global $$link;
    
    switch ($type) {
      case 'mysql':
        return mysql_get_client_info();
        break;
      case 'mysqli':
        return mysqli_get_client_info($$link);
        break;
    }        
  }
 ?>
