<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_ORIGINAL_IMAGES', DIR_WS_IMAGES .'product_images/original_images/');
  define('DIR_WS_THUMBNAIL_IMAGES', DIR_WS_IMAGES .'product_images/thumbnail_images/');
  define('DIR_WS_INFO_IMAGES', DIR_WS_IMAGES .'product_images/info_images/');
  define('DIR_WS_POPUP_IMAGES', DIR_WS_IMAGES .'product_images/popup_images/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_INCLUDES',DIR_FS_DOCUMENT_ROOT. 'includes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_LANGUAGES', DIR_FS_CATALOG . 'lang/');
  define('DIR_WS_DOWNLOAD_PUBLIC', DIR_WS_CATALOG . 'pub/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
  define('DIR_FS_INC', DIR_FS_CATALOG . 'inc/');

   // Base/PHP_SELF/SSL-PROXY
  require_once(DIR_FS_INC . 'set_php_self.inc.php'); 
  $PHP_SELF = set_php_self();

 //compatibility for modified eCommerce Shopsoftware 1.06 files
  $ssl_proxy = '';
  if (isset($request_type) && $request_type == 'SSL' && ENABLE_SSL == true && defined('USE_SSL_PROXY') && USE_SSL_PROXY == true) {
    $ssl_proxy = '/' . $_SERVER['HTTP_HOST'];
  }
  define('DIR_WS_BASE', $ssl_proxy . preg_replace('/\\' . DIRECTORY_SEPARATOR . '\/|\/\//', '/', dirname($PHP_SELF) . '/'));

  // SQL caching dir
  define('SQL_CACHEDIR', DIR_FS_CATALOG . 'cache/');

  // LOG dir
  define('DIR_FS_LOG', DIR_FS_CATALOG . 'log/');
  
  // external
  define('DIR_WS_EXTERNAL', DIR_WS_CATALOG . 'includes/external/');
  define('DIR_FS_EXTERNAL', DIR_FS_CATALOG . 'includes/external/');
?>