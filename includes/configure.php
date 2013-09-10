<?php
/* --------------------------------------------------------------
   $Id: configure.php 4649 2013-04-26 10:46:04Z GTB $

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
  define('HTTP_SERVER', 'http://localhost'); // eg, http://localhost - should not be empty for productive servers
  define('HTTPS_SERVER', 'https://localhost'); // eg, https://localhost - should not be empty for productive servers
  define('DIR_FS_DOCUMENT_ROOT', '/var/www/modified-shop-2.00/'); // absolute path required
  define('DIR_WS_CATALOG', '/modified-shop-2.00/'); // relative path required
  define('DIR_FS_CATALOG', DIR_FS_DOCUMENT_ROOT);

  // defines for admin
  define('HTTP_CATALOG_SERVER', HTTP_SERVER);
  define('HTTPS_CATALOG_SERVER', HTTPS_SERVER);

  // secure SSL
  define('ENABLE_SSL', false); // secure webserver for checkout procedure?
  define('ENABLE_SSL_CATALOG', ((ENABLE_SSL === true) ? 'true' : 'false'));
  define('USE_SSL_PROXY', false); // using SSL proxy?
  
  // define our database connection
  define('DB_MYSQL_TYPE', 'mysql'); // define mysql type set to 'mysql' or 'mysqli'
  define('DB_SERVER', 'localhost'); // eg, localhost - should not be empty for productive servers
  define('DB_SERVER_USERNAME', '');
  define('DB_SERVER_PASSWORD', '');
  define('DB_DATABASE', '');
  define('USE_PCONNECT', 'false'); // use persistent connections?
  define('STORE_SESSIONS', 'mysql'); // leave empty '' for default handler or set to 'mysql'
  define('DB_SERVER_CHARSET', 'latin1'); // set db charset utf8 or latin1

?>