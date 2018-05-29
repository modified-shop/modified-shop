<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
   
  if(!defined('DIR_MODIFIED_INSTALLER')) {
    define('DIR_MODIFIED_INSTALLER', '_installer');
  }
  
  define('MODIFIED_SQL', 'includes/sql/modified.sql');
  
  // config
  define('EMAIL_SQL_ERRORS', 'false');
  define('TEMPLATE_ENGINE','smarty_3');
  define('SEARCH_ENGINE_FRIENDLY_URLS', 'false');

  // min / max  
  define('SSL_VERSION_MIN', '1.2');
  define('PHP_VERSION_MIN', '5.6.0');
  define('PHP_VERSION_MAX', '7.1.99');
  
  // permission
  define('CHMOD_WRITEABLE', 0775);
  
  // DB Backup / Restore
  define('MAX_RELOADS', 600);
  define('ANZAHL_ZEILEN_BKUP', 20000);
  define('ANZAHL_ZEILEN', 500);
  define('RESTORE_TEST', false);

  define('RM', true);
?>