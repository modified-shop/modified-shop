<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (configure.php,v 1.13 2003/02/10); www.oscommerce.com
   (c) 2003 XT-Commerce (configure.php)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  // Define the webserver and path parameters
  // * DIR_FS_* = Filesystem directories (local/physical)
  // * DIR_WS_* = Webserver directories (virtual/URL)

  // global defines
  defined('DIR_FS_DOCUMENT_ROOT') OR define('DIR_FS_DOCUMENT_ROOT', '/var/www/html/modified-shop/'); // absolute path
  defined('DIR_FS_CATALOG') OR define('DIR_FS_CATALOG', DIR_FS_DOCUMENT_ROOT); // absolute path
  defined('DIR_WS_CATALOG') OR define('DIR_WS_CATALOG', '/modified-shop/'); // relative path

  if (is_file(DIR_FS_CATALOG.'inc/auto_include.inc.php')
      && is_dir(DIR_FS_CATALOG.'includes/extra/configure/')
      )
  {
    // auto include
    require_once (DIR_FS_CATALOG.'inc/auto_include.inc.php');

    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/configure/','php') as $file) require_once ($file);
  }

  // define our database connection
  defined('DB_MYSQL_TYPE') OR define('DB_MYSQL_TYPE', 'mysqli'); // define mysql type set to 'mysql' or 'mysqli'
  defined('DB_SERVER') OR define('DB_SERVER', 'localhost'); // eg, localhost - should not be empty for productive servers
  defined('DB_SERVER_USERNAME') OR define('DB_SERVER_USERNAME', '');
  defined('DB_SERVER_PASSWORD') OR define('DB_SERVER_PASSWORD', '');
  defined('DB_DATABASE') OR define('DB_DATABASE', '');
  defined('DB_SERVER_CHARSET') OR define('DB_SERVER_CHARSET', 'latin1'); // set db charset 'utf8' or 'latin1'
  defined('DB_SERVER_ENGINE') OR define('DB_SERVER_ENGINE', 'MyISAM');
  defined('USE_PCONNECT') OR define('USE_PCONNECT', 'false'); // use persistent connections?

  // server
  defined('HTTP_SERVER') OR define('HTTP_SERVER', 'http://localhost'); // eg, http://localhost - should not be empty for productive servers
  defined('HTTPS_SERVER') OR define('HTTPS_SERVER', 'https://localhost'); // eg, https://localhost - should not be empty for productive servers

  // secure SSL
  defined('ENABLE_SSL') OR define('ENABLE_SSL', false); // secure webserver for checkout procedure?

  // session handling
  defined('STORE_SESSIONS') OR define('STORE_SESSIONS', 'mysql'); // leave empty '' for default handler or set to 'mysql'

  // timezone
  defined('DEFAULT_TIMEZONE') OR define('DEFAULT_TIMEZONE', 'Europe/Berlin');

  // password pepper (store additionally in a safe place!)
  defined('PASSWORD_HMAC') OR define('PASSWORD_HMAC', ''); // to be used to encrypt customer passwords hashes - ATTENTION: The loss of this password pepper leads to the fact that no customer can log in the store with his password any more, so store this password in a safe place if used!

  if (DB_DATABASE != '') {
    // set admin directory DIR_ADMIN
    require_once(DIR_FS_CATALOG.'inc/set_admin_directory.inc.php');

    // include standard settings
    require_once(DIR_FS_CATALOG.(defined('RUN_MODE_ADMIN')? DIR_ADMIN : '').'includes/paths.php');
  }
?>