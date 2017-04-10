<?php namespace kcfinder\cms;

/*---------------------------------
KCFinder Version 3.12 Integration Modul for modified shop

Version 1.00 by  web www.rpa-com.de
-----------------------------------*/

class modifiedshop {
  protected static $authenticated = false;
  
  static function checkAuth() {

    if (self::$authenticated === false) {
      
      //Adminverzeichnis Shop
      $adminDir = '../../../'; 
      $current_cwd = getcwd();
      chdir($adminDir);
      define('_IS_FILEMANAGER',true);
      require_once('includes/application_top.php');
      chdir($current_cwd);
      
      if (isset($_SESSION) && $_SESSION['customers_status']['customers_status_id'] == '0') {
        $access_permission_query = xtc_db_query("SELECT * 
                                                   FROM ".TABLE_ADMIN_ACCESS." 
                                                  WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
        $access_permission = xtc_db_fetch_array($access_permission_query);
        if (!isset($access_permission['filemanager']) || ($access_permission['filemanager'] != '1')) {
          die('Direct Access to this location is not allowed.');
        }
        
        // active 
        self::$authenticated = true;
        
        if (!isset($_SESSION['KCFINDER'])) {
          $_SESSION['KCFINDER'] = array();
        }
        if(!isset($_SESSION['KCFINDER']['disabled'])) {
          $_SESSION['KCFINDER']['disabled'] = false;
        }
        
        // settings
        $_SESSION['KCFINDER']['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
        $_SESSION['KCFINDER']['uploadURL'] = DIR_WS_CATALOG;
        //$_SESSION['KCFINDER']['uploadURL'] = '/';
        
        if (strpos($_SERVER['DOCUMENT_ROOT'],'strato') !== false) {
          $_SESSION['KCFINDER']['DOCUMENT_ROOT'] =  DIR_WS_CATALOG == '/' ? DIR_FS_DOCUMENT_ROOT : $_SERVER['DOCUMENT_ROOT'];
        }
        
        $_SESSION['KCFINDER']['uploadDir'] = '';
        $_SESSION['KCFINDER']['thumbsDir'] = 'images/.thumbs';
        $_SESSION['KCFINDER']['theme'] = 'default';
        $_SESSION['KCFINDER']['jpegQuality'] = IMAGE_QUALITY;
        
        // processing
        $_SESSION['KCFINDER']['maxImageWidth'] = (defined('MODULE_KCFINDER_MAXIMAGEWIDTH') && MODULE_KCFINDER_MAXIMAGEWIDTH > 0 ? MODULE_KCFINDER_MAXIMAGEWIDTH : 0);
        $_SESSION['KCFINDER']['maxImageHeight'] = (defined('MODULE_KCFINDER_MAXIMAGEHEIGHT') && MODULE_KCFINDER_MAXIMAGEHEIGHT > 0 ? MODULE_KCFINDER_MAXIMAGEHEIGHT : 0);
      
        // thumbnail
        $_SESSION['KCFINDER']['thumbWidth'] = (defined('MODULE_KCFINDER_THUMBSWIDTH') && MODULE_KCFINDER_THUMBSWIDTH > 0 ? MODULE_KCFINDER_THUMBSWIDTH : PRODUCT_IMAGE_THUMBNAIL_WIDTH);
        $_SESSION['KCFINDER']['thumbHeight'] = (defined('MODULE_KCFINDER_THUMBSWIDTH') && MODULE_KCFINDER_THUMBSHEIGHT > 0 ? MODULE_KCFINDER_THUMBSHEIGHT : PRODUCT_IMAGE_THUMBNAIL_HEIGHT);
      
        // default type
        $_SESSION['KCFINDER']['types'] = array('images'  =>  "*img");
        
        // type paths
        if (isset($_GET['type'])) {
          switch ($_GET['type']) {
            case 'images':
              $_SESSION['KCFINDER']['types'] = array('images' => "*img");
              break;
            case 'flash':
              $_SESSION['KCFINDER']['types'] = array('media' => "swf");
              break;
            case 'media':
              $_SESSION['KCFINDER']['types'] = array('media' => "");
              break;
            case 'files':
              $_SESSION['KCFINDER']['types'] = array('media' => "");
              break;
            default:
              $_SESSION['KCFINDER']['types'] = array('images' => "*img");
              break;
          }
        }
      } else {
        die('Direct Access to this location is not allowed.');  
      }
    }
    return self::$authenticated;
  }
}

// call checkAuth
modifiedshop::checkAuth();