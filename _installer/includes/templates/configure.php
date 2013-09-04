<?php
$file_contents = 
'<?php' . PHP_EOL .
'/* --------------------------------------------------------------' . PHP_EOL .
'' . PHP_EOL .
'  modified eCommerce Shopsoftware' . PHP_EOL .
'  http://www.modified-shop.org' . PHP_EOL .
'' . PHP_EOL .
'   Copyright (c) 2009 - 2013 [www.modified-shop.org]' . PHP_EOL .
'  --------------------------------------------------------------' . PHP_EOL .
'  based on:' . PHP_EOL .
'  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)' . PHP_EOL .
'  (c) 2002-2003 osCommerce (configure.php,v 1.13 2003/02/10); www.oscommerce.com' . PHP_EOL .
'  (c) 2003 XT-Commerce (configure.php)' . PHP_EOL .
'' . PHP_EOL .
'  Released under the GNU General Public License' . PHP_EOL .
'  --------------------------------------------------------------*/' . PHP_EOL .
'' . PHP_EOL .
'// Define the webserver and path parameters' . PHP_EOL .
'// * DIR_FS_* = Filesystem directories (local/physical)' . PHP_EOL .
'// * DIR_WS_* = Webserver directories (virtual/URL)' . PHP_EOL .
'' . PHP_EOL .
'// global defines' . PHP_EOL .
'  define(\'HTTP_SERVER\', \'' . $http_server . '\'); // eg, http://localhost - should not be empty for productive servers' . PHP_EOL .
'  define(\'HTTPS_SERVER\', \'' . $https_server . '\'); // eg, https://localhost - should not be empty for productive servers' . PHP_EOL .
'  define(\'DIR_FS_DOCUMENT_ROOT\', \'' . DIR_FS_DOCUMENT_ROOT . '\'); // absolut path required' . PHP_EOL .
'  define(\'DIR_WS_CATALOG\', \'' . $_POST['DIR_WS_CATALOG'] . '\'); // relative path required' . PHP_EOL .
'  define(\'DIR_FS_CATALOG\', DIR_FS_DOCUMENT_ROOT);' . PHP_EOL .
'' . PHP_EOL .
'// defines for admin' . PHP_EOL .
'  define(\'HTTP_CATALOG_SERVER\', HTTP_SERVER);' . PHP_EOL .
'  define(\'HTTPS_CATALOG_SERVER\', HTTPS_SERVER);' . PHP_EOL .
'  define(\'DIR_WS_ADMIN\', DIR_WS_CATALOG.\'admin/\');' . PHP_EOL .
'  define(\'DIR_FS_ADMIN\', DIR_FS_DOCUMENT_ROOT.\'admin/\');' . PHP_EOL .
'' . PHP_EOL .
'// secure SSL' . PHP_EOL .
'  define(\'ENABLE_SSL\', ' . (($_POST['ENABLE_SSL'] == 'true') ? 'true' : 'false') . '); // secure webserver for checkout procedure?' . PHP_EOL .
'  define(\'ENABLE_SSL_CATALOG\', ((ENABLE_SSL === true) ? \'true\' : \'false\'));' . PHP_EOL .
'  define(\'USE_SSL_PROXY\', ' . (($_POST['USE_SSL_PROXY'] == 'true') ? 'true' : 'false') . '); // using SSL proxy?' . PHP_EOL .
'' . PHP_EOL .
'// define our database connection' . PHP_EOL .
'  define(\'DB_MYSQL_TYPE\', \'' . $_POST['DB_MYSQL_TYPE'] . '\'); // define mysql type set to \'mysql\' or \'mysqli\'' . PHP_EOL .
'  define(\'DB_SERVER\', \'' . $_POST['DB_SERVER'] . '\'); // eg, localhost - should not be empty for productive servers' . PHP_EOL .
'  define(\'DB_SERVER_USERNAME\', \'' . $_POST['DB_SERVER_USERNAME'] . '\');' . PHP_EOL .
'  define(\'DB_SERVER_PASSWORD\', \'' . $_POST['DB_SERVER_PASSWORD']. '\');' . PHP_EOL .
'  define(\'DB_DATABASE\', \'' . $_POST['DB_DATABASE']. '\');' . PHP_EOL .
'  define(\'USE_PCONNECT\', \'' . (($_POST['USE_PCONNECT'] == 'true') ? 'true' : 'false') . '\'); // use persistent connections?' . PHP_EOL .
'  define(\'STORE_SESSIONS\', \'' . (($_POST['STORE_SESSIONS'] == 'files') ? '' : 'mysql') . '\'); // leave empty \'\' for default handler or set to \'mysql\'' . PHP_EOL .                     
'  define(\'DB_SERVER_CHARSET\', \'' . DB_SERVER_CHARSET . '\'); // set db charset \'utf8\' or \'latin1\'' . PHP_EOL . 
'' . PHP_EOL .
'// include standard settings' . PHP_EOL .
'  if (defined(\'RUN_MODE_ADMIN\')) {' . PHP_EOL .
'    require (DIR_FS_ADMIN.\'includes/paths.php\');' . PHP_EOL .
'  } else {' . PHP_EOL .
'    require (DIR_FS_CATALOG.\'includes/paths.php\');' . PHP_EOL .
'  }' . PHP_EOL .
'?>';
?>