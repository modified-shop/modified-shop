<?php
$file_contents = 
'<?php' . PHP_EOL .
'/* --------------------------------------------------------------' . PHP_EOL .
'' . PHP_EOL .
'  modified eCommerce Shopsoftware' . PHP_EOL .
'  http://www.modified.org-shop' . PHP_EOL .
'' . PHP_EOL .
'   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware' . PHP_EOL .
'   Released under the GNU General Public License (Version 2)' . PHP_EOL .
'   [http://www.gnu.org/licenses/gpl-2.0.html]' . PHP_EOL .
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
'  define(\'HTTP_SERVER\', \'' . $http_server . '\'); // eg, http://localhost - should not be empty for productive servers' . PHP_EOL .
'  define(\'HTTPS_SERVER\', \'' . $https_server . '\'); // eg, https://localhost - should not be empty for productive servers' . PHP_EOL .
'  define(\'ENABLE_SSL\', ' . (($_POST['ENABLE_SSL'] == 'true') ? 'true' : 'false') . '); // secure webserver for checkout procedure?' . PHP_EOL .
//BOF - web28 - 2010.09.15 - using SSL proxy
'  define(\'USE_SSL_PROXY\', ' . (($_POST['USE_SSL_PROXY'] == 'true') ? 'true' : 'false') . '); // using SSL proxy?' . PHP_EOL .
//EOF - web28 - 2010.09.15 - using SSL proxy
'  define(\'DIR_WS_CATALOG\', \'' . $_POST['DIR_WS_CATALOG'] . '\'); // absolute path required' . PHP_EOL .
//BOF - web28 - 2010.02.18 - STRATO ROOT PATCH
'  define(\'DIR_FS_DOCUMENT_ROOT\', \'' . DIR_FS_DOCUMENT_ROOT . '\');' . PHP_EOL .
'  define(\'DIR_FS_CATALOG\', \'' . DIR_FS_DOCUMENT_ROOT . '\');' . PHP_EOL .
//EOF - web28 - 2010.02.18 - STRATO ROOT PATCH
'  define(\'DIR_WS_IMAGES\', \'images/\');' . PHP_EOL .
'  define(\'DIR_WS_ORIGINAL_IMAGES\', DIR_WS_IMAGES .\'product_images/original_images/\');' . PHP_EOL .
'  define(\'DIR_WS_THUMBNAIL_IMAGES\', DIR_WS_IMAGES .\'product_images/thumbnail_images/\');' . PHP_EOL .
'  define(\'DIR_WS_INFO_IMAGES\', DIR_WS_IMAGES .\'product_images/info_images/\');' . PHP_EOL .
'  define(\'DIR_WS_POPUP_IMAGES\', DIR_WS_IMAGES .\'product_images/popup_images/\');' . PHP_EOL .
'  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . PHP_EOL .
'  define(\'DIR_WS_INCLUDES\',DIR_FS_DOCUMENT_ROOT. \'includes/\');' . PHP_EOL .
'  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . PHP_EOL .
'  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . PHP_EOL .
'  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . PHP_EOL .
'  define(\'DIR_WS_LANGUAGES\', DIR_FS_CATALOG . \'lang/\');' . PHP_EOL .
'' . PHP_EOL .
'  define(\'DIR_WS_DOWNLOAD_PUBLIC\', DIR_WS_CATALOG . \'pub/\');' . PHP_EOL .
'  define(\'DIR_FS_DOWNLOAD\', DIR_FS_CATALOG . \'download/\');' . PHP_EOL .
'  define(\'DIR_FS_DOWNLOAD_PUBLIC\', DIR_FS_CATALOG . \'pub/\');' . PHP_EOL .
'  define(\'DIR_FS_INC\', DIR_FS_CATALOG . \'inc/\');' . PHP_EOL .
'' . PHP_EOL .
'// define our database connection' . PHP_EOL .
'  define(\'DB_SERVER\', \'' . $_POST['DB_SERVER'] . '\'); // eg, localhost - should not be empty for productive servers' . PHP_EOL .
'  define(\'DB_SERVER_USERNAME\', \'' . $_POST['DB_SERVER_USERNAME'] . '\');' . PHP_EOL .
'  define(\'DB_SERVER_PASSWORD\', \'' . $_POST['DB_SERVER_PASSWORD']. '\');' . PHP_EOL .
'  define(\'DB_DATABASE\', \'' . $_POST['DB_DATABASE']. '\');' . PHP_EOL .
'  define(\'USE_PCONNECT\', \'' . (($_POST['USE_PCONNECT'] == 'true') ? 'true' : 'false') . '\'); // use persistent connections?' . PHP_EOL .
'  define(\'STORE_SESSIONS\', \'' . (($_POST['STORE_SESSIONS'] == 'files') ? '' : 'mysql') . '\'); // leave empty \'\' for default handler or set to \'mysql\'' . PHP_EOL .                     
'  define(\'DB_SERVER_CHARSET\', \'' . DB_SERVER_CHARSET . '\'); // set db charset utf8 or latin1' . PHP_EOL . 
'?>';