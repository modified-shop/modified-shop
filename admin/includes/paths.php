<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // server
  defined('HTTP_CATALOG_SERVER') or define('HTTP_CATALOG_SERVER', HTTP_SERVER);
  defined('HTTPS_CATALOG_SERVER') or define('HTTPS_CATALOG_SERVER', HTTPS_SERVER);
  
  // ssl
  defined('ENABLE_SSL_CATALOG') or define('ENABLE_SSL_CATALOG', ((ENABLE_SSL === true) ? 'true' : 'false'));
  
  // admin
  define('DIR_WS_ADMIN', DIR_WS_CATALOG.DIR_ADMIN);
  define('DIR_FS_ADMIN', DIR_FS_DOCUMENT_ROOT.DIR_ADMIN); 

  // images
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');
  define('DIR_FS_CATALOG_ORIGINAL_IMAGES', DIR_FS_CATALOG_IMAGES .'product_images/original_images/');
  define('DIR_FS_CATALOG_THUMBNAIL_IMAGES', DIR_FS_CATALOG_IMAGES .'product_images/thumbnail_images/');
  define('DIR_FS_CATALOG_INFO_IMAGES', DIR_FS_CATALOG_IMAGES .'product_images/info_images/');
  define('DIR_FS_CATALOG_POPUP_IMAGES', DIR_FS_CATALOG_IMAGES .'product_images/popup_images/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_CATALOG_IMAGES', DIR_WS_CATALOG . 'images/');
  define('DIR_WS_CATALOG_ORIGINAL_IMAGES', DIR_WS_CATALOG_IMAGES .'product_images/original_images/');
  define('DIR_WS_CATALOG_THUMBNAIL_IMAGES', DIR_WS_CATALOG_IMAGES .'product_images/thumbnail_images/');
  define('DIR_WS_CATALOG_INFO_IMAGES', DIR_WS_CATALOG_IMAGES .'product_images/info_images/');
  define('DIR_WS_CATALOG_POPUP_IMAGES', DIR_WS_CATALOG_IMAGES .'product_images/popup_images/');
  
  // includes
  define('DIR_WS_INCLUDES', 'includes/');
  
  // functions
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');

  // classes
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');

  // modules
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');

  // languages
  define('DIR_WS_LANGUAGES', DIR_WS_CATALOG. 'lang/');
  define('DIR_FS_LANGUAGES', DIR_FS_CATALOG. 'lang/');

  // catalog modules
  define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/');

  // backup
  define('DIR_FS_BACKUP', DIR_FS_ADMIN . 'backups/');

  // inc
  define('DIR_FS_INC', DIR_FS_CATALOG . 'inc/');

  // filemanager
  define('DIR_WS_FILEMANAGER', DIR_WS_MODULES . 'fckeditor/editor/filemanager/browser/default/');

  // cache
  define('SQL_CACHEDIR', DIR_FS_CATALOG . 'cache/');

  // log
  define('DIR_FS_LOG', DIR_FS_CATALOG . 'log/');

  // api
  define('DIR_FS_API', DIR_FS_CATALOG.'api/');

  // external
  define('DIR_WS_EXTERNAL', DIR_WS_CATALOG . 'includes/external/');
  define('DIR_FS_EXTERNAL', DIR_FS_CATALOG . 'includes/external/');

  // extra paths
  foreach(auto_include(DIR_FS_ADMIN.'includes/extra/paths/','php') as $file) require_once ($file);
?>