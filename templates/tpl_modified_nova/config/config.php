<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // paths
  define('DIR_FS_BOXES', DIR_FS_CATALOG .'templates/'.CURRENT_TEMPLATE. '/source/boxes/');
  define('DIR_FS_BOXES_INC', DIR_FS_CATALOG .'templates/'.CURRENT_TEMPLATE. '/source/inc/');

  // popup
  define('TPL_POPUP_SHIPPING_LINK_PARAMETERS', '');
  define('TPL_POPUP_SHIPPING_LINK_CLASS', 'iframe');
  define('TPL_POPUP_CONTENT_LINK_PARAMETERS', '');
  define('TPL_POPUP_CONTENT_LINK_CLASS', 'iframe');
  define('TPL_POPUP_PRODUCT_LINK_PARAMETERS', '');
  define('TPL_POPUP_PRODUCT_LINK_CLASS', 'iframe');
  define('TPL_POPUP_COUPON_HELP_LINK_PARAMETERS', '');
  define('TPL_POPUP_COUPON_HELP_LINK_CLASS', 'iframe');
  define('TPL_POPUP_PRODUCT_PRINT_SIZE', '');
  define('TPL_POPUP_PRINT_ORDER_SIZE', '');
  
  // view
  define('PRODUCT_LIST_BOX', ((isset($_SESSION['listbox'])) ? $_SESSION['listbox'] : 'true'));
  define('PRODUCT_LIST_BOX_STARTPAGE', 'true');
  define('PRODUCT_INFO_BOX', 'true');
  
  // template output
  define('TEMPLATE_ENGINE', 'smarty_4'); 
  define('TEMPLATE_HTML_ENGINE', 'html5');
  define('TEMPLATE_RESPONSIVE', 'true');
  defined('COMPRESS_JAVASCRIPT') or define('COMPRESS_JAVASCRIPT', true);

  // categories
  defined('SPECIALS_CATEGORIES') or define('SPECIALS_CATEGORIES', false); 
  defined('WHATSNEW_CATEGORIES') or define('WHATSNEW_CATEGORIES', false);

  // check specials
  if (SPECIALS_CATEGORIES === true) {
    require_once (DIR_FS_INC.'check_specials.inc.php');
    define('SPECIALS_EXISTS', check_specials());
  }
      
  // check whats new
  if (WHATSNEW_CATEGORIES === true) {
    require_once (DIR_FS_INC.'check_whatsnew.inc.php');
    define('WHATSNEW_EXISTS', check_whatsnew());
  }      
      
  // picture set listing box
  define('PICTURESET_ACTIVE', defined('DIR_WS_MINI_IMAGES'));
  define('PICTURESET_BOX', '360:thumbnail,460:midi');
  define('PICTURESET_ROW', '985:midi');
  
  // Sumo select  
  define('ADVANCED_SUMOSELECT_SEARCHFIELD', true);

  // Add quickie  
  define('SHOW_ADD_QUICKIE', true);

  // manufacturer
  define('HEADER_SHOW_MANUFACTURERS', true);
  
  // max products
  define('MAX_PRODUCTS_BOX', 10);

  // categories menu
  // 1 - (Megamenu)
  // 2 - (Dropdown)
  define('CATEGORIES_CASE', 1);
  define('CATEGORIES_HIDE_EMPTY', false);
  define('CATEGORIES_MAX_DEPTH', 3);
  define('CATEGORIES_CHECK_SUBS', true);  
  
  // asterisk
  define('TEXT_ICON_ASTERISK', '<i class="fa-solid fa-asterisk"></i>');  
    
  // theme color
  // default, blue, green, modified
  // after a change, the template cache must be deleted.
  // admin -> adv. configuration -> cache options -> delete templatecache
  define('THEME_COLOR', 'default');  

  // set base
  defined('DIR_WS_BASE') OR define('DIR_WS_BASE', xtc_href_link('', '', $request_type, false, false));

  // css buttons
  if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/inc/css_button.inc.php')) {
    require_once(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/inc/css_button.inc.php');
  }
