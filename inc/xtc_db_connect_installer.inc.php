<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_connect_installer.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.2 2002/03/02); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_db_connect_installer.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_db_connect_installer.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_db_connect_installer($server, $username, $password, $link = 'db_link') {
    global $$link, $db_error;

    $db_error = false;

    if (!$server) {
      $db_error = 'No Server selected.';
      return false;
    }

    $$link = @mysql_connect($server, $username, $password) or $db_error = mysql_error();

    //vr - 2010-01-01 - Disable "STRICT" mode for MySQL 5!
    if(version_compare(@mysql_get_server_info(), '5.0.0', '>=')) {
      @mysql_query("SET SESSION sql_mode=''");
    }

    // set charset defined in configure.php
    if(!defined('DB_SERVER_CHARSET')) {
      define('DB_SERVER_CHARSET','latin1');
    }
 
    if(function_exists('mysql_set_charset')) { //requires MySQL 5.0.7 or later
      mysql_set_charset(DB_SERVER_CHARSET);
    } else {
      $collation = DB_SERVER_CHARSET == 'utf8' ? 'utf8_general_ci' : 'latin1_german1_ci';      
      mysql_query('SET NAMES '.DB_SERVER_CHARSET. ' COLLATE '. $collation );      
    }

    return $$link;
  }
 ?>