<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /*
   * define template specific defines
   */
  
  // paths
  define('DIR_FS_BOXES', DIR_FS_CATALOG .'templates/'.CURRENT_TEMPLATE. '/source/boxes/');
  define('DIR_FS_BOXES_INC', DIR_FS_CATALOG .'templates/'.CURRENT_TEMPLATE. '/source/inc/');

  // popup
  define('TPL_POPUP_SHIPPING_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
  define('TPL_POPUP_SHIPPING_LINK_CLASS', 'thickbox');
  define('TPL_POPUP_CONTENT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
  define('TPL_POPUP_CONTENT_LINK_CLASS', 'thickbox');
  define('TPL_POPUP_PRODUCT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=450&width=750');
  define('TPL_POPUP_PRODUCT_LINK_CLASS', 'thickbox');
  define('TPL_POPUP_COUPON_HELP_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
  define('TPL_POPUP_COUPON_HELP_LINK_CLASS', 'thickbox');
  define('TPL_POPUP_PRODUCT_PRINT_SIZE', 'width=640, height=600');
  define('TPL_POPUP_PRINT_ORDER_SIZE', 'width=640, height=600');

  // template output
  define('TEMPLATE_ENGINE', 'smarty_4'); // smarty_4 or smarty_3 or smarty_2
  define('TEMPLATE_HTML_ENGINE', 'xhtml'); // html5 or xhtml
  define('TEMPLATE_RESPONSIVE', 'false'); // 'true' oder 'false' -> Nicht ändern!
  defined('COMPRESS_JAVASCRIPT') or define('COMPRESS_JAVASCRIPT', true); // 'true' kombiniert & komprimiert die zusätzliche JS-Dateien / 'false' bindet alle JS-Dateien einzeln ein
  
  
  // set base
  defined('DIR_WS_BASE') OR define('DIR_WS_BASE', xtc_href_link('', '', $request_type, false, false));
