<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  $db_server = DB_SERVER;
  $db_username = DB_SERVER_USERNAME;
  $db_password = DB_SERVER_PASSWORD;
  $db_database = DB_DATABASE;

  $db_type = get_mysql_type();
  $db_charset = DB_SERVER_CHARSET;
  $db_pconnect = USE_PCONNECT;

  $http_server = HTTP_SERVER;
  $https_server = HTTPS_SERVER;
  $use_ssl = ((ENABLE_SSL == true) ? 'true' : 'false');
  
  //create  includes/configure.php
  include (DIR_FS_INSTALLER.'templates/configure.php');
  
  $error = true;
  if (is_file(DIR_FS_CATALOG.'/includes/local/configure.php')) {
    if (is_make_writeable(DIR_FS_CATALOG.'/includes/local/configure.php')) {
      $fp = fopen(DIR_FS_CATALOG . 'includes/local/configure.php', 'w');
      $error = false;
    }
  } else {
    if (is_make_writeable(DIR_FS_CATALOG.'/includes/configure.php')) {
      $fp = fopen(DIR_FS_CATALOG . 'includes/configure.php', 'w');
      $error = false;
    }
  }
  fputs($fp, $file_contents);
  fclose($fp);
  
  if (is_file(DIR_FS_CATALOG.'/includes/local/configure.php')) {
    chmod(DIR_FS_CATALOG.'/includes/local/configure.php', 0444);
  }
  if (is_file(DIR_FS_CATALOG.'/includes/configure.php')) {
    chmod(DIR_FS_CATALOG.'/includes/configure.php', 0444);
  }