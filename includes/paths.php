<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // images
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_ORIGINAL_IMAGES', DIR_WS_IMAGES .'product_images/original_images/');
  define('DIR_WS_THUMBNAIL_IMAGES', DIR_WS_IMAGES .'product_images/thumbnail_images/');
  define('DIR_WS_INFO_IMAGES', DIR_WS_IMAGES .'product_images/info_images/');
  define('DIR_WS_POPUP_IMAGES', DIR_WS_IMAGES .'product_images/popup_images/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');

  // includes
  define('DIR_WS_INCLUDES', DIR_FS_CATALOG. 'includes/');

  // functions
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');

  // classes
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');

  // modules
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');

  // languages
  define('DIR_WS_LANGUAGES', DIR_FS_CATALOG . 'lang/');

  // download
  define('DIR_WS_DOWNLOAD_PUBLIC', DIR_WS_CATALOG . 'pub/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');

  // inc
  define('DIR_FS_INC', DIR_FS_CATALOG . 'inc/');

  // cache
  define('SQL_CACHEDIR', DIR_FS_CATALOG . 'cache/');

  // log
  define('DIR_FS_LOG', DIR_FS_CATALOG . 'log/');
  
  // api
  define('DIR_FS_API', DIR_FS_CATALOG.'api/');

  // external
  define('DIR_WS_EXTERNAL', 'includes/external/');
  define('DIR_FS_EXTERNAL', DIR_FS_CATALOG . 'includes/external/');

  // installer
  defined('DIR_MODIFIED_INSTALLER') OR define('DIR_MODIFIED_INSTALLER', '_installer');
  
  // extra paths
  require_once(DIR_FS_INC.'auto_include.inc.php');
  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/paths/','php') as $file) require_once ($file);
?>