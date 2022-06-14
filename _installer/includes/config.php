<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
   
  define('MODIFIED_SQL', 'includes/sql/modified.sql');
  
  // config
  define('EMAIL_SQL_ERRORS', 'false');
  define('TEMPLATE_ENGINE','smarty_3');
  define('SEARCH_ENGINE_FRIENDLY_URLS', 'false');
  define('DEFAULT_TEMPLATE', 'tpl_modified_responsive');

  // min / max  
  define('SSL_VERSION_MIN', '1.2');
  define('PHP_VERSION_MIN', '7.4.0');
  define('PHP_VERSION_MAX', '8.1.99');
  
  // permission
  define('CHMOD_WRITEABLE', 0775);
  
  // update
  define('UPDATE_MAX_RELOADS', 100000000);
    
  define('ENTRY_FIRST_NAME_MIN_LENGTH', 2);
  define('ENTRY_LAST_NAME_MIN_LENGTH', 2);
  define('ENTRY_EMAIL_ADDRESS_MIN_LENGTH', 6);
  define('ENTRY_STREET_ADDRESS_MIN_LENGTH', 4);
  define('ENTRY_POSTCODE_MIN_LENGTH', 4);
  define('ENTRY_CITY_MIN_LENGTH', 3);
  define('ENTRY_PASSWORD_MIN_LENGTH', 8);
  
  define('RM', true);
  define('RUN_MODE_INSTALLER', true);

  $blacklist_array = array(
    DIR_FS_CATALOG.'_installer/',
    DIR_FS_CATALOG.'cache/',
    DIR_FS_CATALOG.'download/',
    DIR_FS_CATALOG.'images/',
    DIR_FS_CATALOG.'import/',
    DIR_FS_CATALOG.'includes/extra/',
    DIR_FS_CATALOG.'includes/external/',
    DIR_FS_CATALOG.'media/',
    DIR_FS_CATALOG.'log/',
    DIR_FS_CATALOG.'pub/',
    DIR_FS_CATALOG.'templates/',
    DIR_FS_CATALOG.'templates_c/',
    DIR_FS_CATALOG.DIR_ADMIN.'archives/',
    DIR_FS_CATALOG.DIR_ADMIN.'backups/',
    DIR_FS_CATALOG.DIR_ADMIN.'images/',
    DIR_FS_CATALOG.DIR_ADMIN.'includes/extra/',
    DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/ckeditor/',
    DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/filemanager/',

    DIR_FS_CATALOG.'includes/configure.php',
    DIR_FS_CATALOG.'includes/local/configure.php',
  );

  $whitelist_array = array(
    DIR_FS_CATALOG.DIR_ADMIN,
    DIR_FS_CATALOG.'api/',
    DIR_FS_CATALOG.'callback/',
    DIR_FS_CATALOG.'export/',
    DIR_FS_CATALOG.'inc/',
    DIR_FS_CATALOG.'includes/',
    DIR_FS_CATALOG.'lang/',
    DIR_FS_CATALOG.'templates/tpl_modified/',
    DIR_FS_CATALOG.'templates/tpl_modified_responsive/',
    DIR_FS_CATALOG.'templates/xtc5/',
  );
?>