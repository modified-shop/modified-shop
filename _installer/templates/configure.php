<?php
$file_contents = 
'<?php' . PHP_EOL .
'/* --------------------------------------------------------------' . PHP_EOL .
'   $Id$' . PHP_EOL .
'' . PHP_EOL .
'   modified eCommerce Shopsoftware' . PHP_EOL .
'   http://www.modified-shop.org' . PHP_EOL .
'' . PHP_EOL .
'   Copyright (c) 2009 - 2013 [www.modified-shop.org]' . PHP_EOL .
'   --------------------------------------------------------------' . PHP_EOL .
'   based on:' . PHP_EOL .
'   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)' . PHP_EOL .
'   (c) 2002-2003 osCommerce (configure.php,v 1.13 2003/02/10); www.oscommerce.com' . PHP_EOL .
'   (c) 2003 XT-Commerce (configure.php)' . PHP_EOL .
'' . PHP_EOL .
'   Released under the GNU General Public License' . PHP_EOL .
'   --------------------------------------------------------------*/' . PHP_EOL .
'' . PHP_EOL .
'  // Define the webserver and path parameters' . PHP_EOL .
'  // * DIR_FS_* = Filesystem directories (local/physical)' . PHP_EOL .
'  // * DIR_WS_* = Webserver directories (virtual/URL)' . PHP_EOL .
'' . PHP_EOL .
'  // global defines' . PHP_EOL .
'  defined(\'DIR_FS_DOCUMENT_ROOT\') OR define(\'DIR_FS_DOCUMENT_ROOT\', \'' . DIR_FS_DOCUMENT_ROOT . '\'); // absolut path' . PHP_EOL .
'  defined(\'DIR_FS_CATALOG\') OR define(\'DIR_FS_CATALOG\', DIR_FS_DOCUMENT_ROOT); // absolut path' . PHP_EOL .
'  defined(\'DIR_WS_CATALOG\') OR define(\'DIR_WS_CATALOG\', \'' . DIR_WS_CATALOG . '\'); // relative path' . PHP_EOL .
'' . PHP_EOL .
'  if (is_file(DIR_FS_CATALOG.\'inc/auto_include.inc.php\')' . PHP_EOL . 
'      && is_dir(DIR_FS_CATALOG.\'includes/extra/configure/\')' . PHP_EOL . 
'      )' . PHP_EOL . 
'  {' . PHP_EOL . 
'    // auto include' . PHP_EOL .
'    require_once (DIR_FS_CATALOG.\'inc/auto_include.inc.php\');' . PHP_EOL .
'' . PHP_EOL .
'    foreach(auto_include(DIR_FS_CATALOG.\'includes/extra/configure/\',\'php\') as $file) require_once ($file);' . PHP_EOL .
'  }'. PHP_EOL .
'' . PHP_EOL .
'  // define our database connection' . PHP_EOL .
'  defined(\'DB_MYSQL_TYPE\') OR define(\'DB_MYSQL_TYPE\', \'' . $db_type . '\'); // define mysql type set to \'mysql\' or \'mysqli\'' . PHP_EOL .
'  defined(\'DB_SERVER\') OR define(\'DB_SERVER\', \'' . $db_server . '\'); // eg, localhost - should not be empty for productive servers' . PHP_EOL .
'  defined(\'DB_SERVER_USERNAME\') OR define(\'DB_SERVER_USERNAME\', \'' . $db_username . '\');' . PHP_EOL .
'  defined(\'DB_SERVER_PASSWORD\') OR define(\'DB_SERVER_PASSWORD\', \'' . $db_password. '\');' . PHP_EOL .
'  defined(\'DB_DATABASE\') OR define(\'DB_DATABASE\', \'' . $db_database. '\');' . PHP_EOL .
'  defined(\'DB_SERVER_CHARSET\') OR define(\'DB_SERVER_CHARSET\', \'' . $db_charset . '\'); // set db charset \'utf8\', \'utf8mb4\' or \'latin1\'' . PHP_EOL .
'  defined(\'DB_SERVER_ENGINE\') OR define(\'DB_SERVER_ENGINE\', \'' . $db_engine . '\'); // set db engine \'InnoDB\' or \'MyISAM\'' . PHP_EOL .
'  defined(\'USE_PCONNECT\') OR define(\'USE_PCONNECT\', \'' . $db_pconnect . '\'); // use persistent connections?' . PHP_EOL .
'' . PHP_EOL .
'  // server' . PHP_EOL .
'  defined(\'HTTP_SERVER\') OR define(\'HTTP_SERVER\', \'' . $http_server . '\'); // eg, http://localhost - should not be empty for productive servers' . PHP_EOL .
'  defined(\'HTTPS_SERVER\') OR define(\'HTTPS_SERVER\', \'' . $https_server . '\'); // eg, https://localhost - should not be empty for productive servers' . PHP_EOL .
'' . PHP_EOL .
'  // secure SSL' . PHP_EOL .
'  defined(\'ENABLE_SSL\') OR define(\'ENABLE_SSL\', ' . $use_ssl . '); // secure webserver for checkout procedure?' . PHP_EOL .
'' . PHP_EOL .
'  // session handling' . PHP_EOL .
'  defined(\'STORE_SESSIONS\') OR define(\'STORE_SESSIONS\', \'' . (($session == 'files') ? '' : 'mysql') . '\'); // leave empty \'\' for default handler or set to \'mysql\'' . PHP_EOL .                     
'' . PHP_EOL .
'  // timezone' . PHP_EOL .
'  defined(\'DEFAULT_TIMEZONE\') OR define(\'DEFAULT_TIMEZONE\', \'Europe/Berlin\');' . PHP_EOL .                     
'' . PHP_EOL .
'  // password pepper (store additionally in a safe place!)' . PHP_EOL .
'  defined(\'PASSWORD_HMAC\') OR define(\'PASSWORD_HMAC\', \'' . $password_hmac . '\'); // to be used to encrypt customer passwords hashes - ATTENTION: The loss of this password pepper leads to the fact that no customer can log in the store with his password any more, so store this password in a safe place if used!' . PHP_EOL .                     
'' . PHP_EOL .
'  if (DB_DATABASE != \'\') {' . PHP_EOL . 
'    // set admin directory DIR_ADMIN' . PHP_EOL . 
'    require_once(DIR_FS_CATALOG.\'inc/set_admin_directory.inc.php\');' . PHP_EOL .
'' . PHP_EOL .
'    // include standard settings' . PHP_EOL .
'    require_once(DIR_FS_CATALOG.(defined(\'RUN_MODE_ADMIN\')? DIR_ADMIN : \'\').\'includes/paths.php\');'. PHP_EOL .
'  }'. PHP_EOL .
'';
