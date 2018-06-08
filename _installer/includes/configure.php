<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // include functions
  require_once('includes/functions.php');

  // global defines
  define('DIR_MODIFIED_INSTALLER', '_installer');
  define('DIR_FS_DOCUMENT_ROOT', get_document_root());
  define('DIR_FS_CATALOG', DIR_FS_DOCUMENT_ROOT);
  define('DIR_WS_CATALOG', rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/').'/');

  // server
  define('HTTP_SERVER', 'http'.(($request_type == 'SSL') ? 's' : '').'://'.$_SERVER['HTTP_HOST']);
  define('HTTPS_SERVER', 'https://'.$_SERVER['HTTP_HOST']);

  // session handling
  define('STORE_SESSIONS', '');
  define('SESSION_WRITE_DIRECTORY', sys_get_temp_dir());
  define('SESSION_FORCE_COOKIE_USE', 'False');
  define('CHECK_CLIENT_AGENT', 'False');
  
  // set admin directory DIR_ADMIN
  require_once(DIR_FS_CATALOG.'inc/set_admin_directory.inc.php');

  // include standard settings
  require_once(DIR_FS_CATALOG.'includes/paths.php');

  define('DIR_WS_INSTALLER', basename(dirname($_SERVER['PHP_SELF'])).'/');
  define('DIR_FS_INSTALLER', DIR_FS_CATALOG.DIR_WS_INSTALLER);
    
  if (basename($_SERVER['PHP_SELF']) == 'install_step1.php') {
    define('DIR_FS_BACKUP', DIR_FS_INSTALLER.'includes/sql/');
  } else {
    define('DIR_FS_BACKUP', DIR_FS_CATALOG.DIR_ADMIN.'backups/');
  }
?>