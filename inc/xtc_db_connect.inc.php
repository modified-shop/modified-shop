<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_connect.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_db_connect.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_db_connect.inc.php 1248 2005-09-27)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  include_once(DIR_FS_INC . 'xtc_db_error.inc.php');

  function xtc_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $$link;

    if (!function_exists('mysql_connect')) {
      die ('Call to undefined function: mysql_connect(). Please install the MySQL Connector for PHP');
    }

    if (USE_PCONNECT == 'true') {
      $$link = @mysql_pconnect($server, $username, $password);
    } else {
      $$link = @mysql_connect($server, $username, $password);
    }

    //vr - 2010-01-01 - Disable "STRICT" mode for MySQL 5!
    if(version_compare(@mysql_get_server_info(), '5.0.0', '>=')) {
      @mysql_query("SET SESSION sql_mode=''");
    }

    // BOF - Dokuman - 2010-11-23 - revised database connection for error reporting
    if ($$link) {
      if (!@mysql_select_db($database, $$link)) {
        xtc_db_error('', mysql_errno($$link), mysql_error($$link));
        die();
      }
    } else {
      xtc_db_error('', mysql_errno(), mysql_error());
      die();
    }
    // EOF - Dokuman - 2010-11-23 - revised database connection for error reporting

    // set charset defined in configure.php
    if(!defined('DB_SERVER_CHARSET')) {
      define('DB_SERVER_CHARSET','latin1');
    }
    if(function_exists('mysql_set_charset')) { //requires MySQL 5.0.7 or later
      mysql_set_charset(DB_SERVER_CHARSET);
    } else {
      mysql_query('SET NAMES '.DB_SERVER_CHARSET);
    }    

    return $$link;
  }
?>