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
  define('POPUP_SHIPPING_LINK_PARAMETERS', '');
  define('POPUP_SHIPPING_LINK_CLASS', 'iframe');
  define('POPUP_CONTENT_LINK_PARAMETERS', '');
  define('POPUP_CONTENT_LINK_CLASS', 'iframe');
  define('POPUP_PRODUCT_LINK_PARAMETERS', '');
  define('POPUP_PRODUCT_LINK_CLASS', 'iframe');
  define('POPUP_COUPON_HELP_LINK_PARAMETERS', '');
  define('POPUP_COUPON_HELP_LINK_CLASS', 'iframe');
  define('POPUP_PRODUCT_PRINT_SIZE', '');
  define('POPUP_PRINT_ORDER_SIZE', '');
  
  // listing
  define('PRODUCT_LIST_ROW', 'true'); // 'true' or 'false'
  define('PRODUCT_INFO_ROW', 'false'); // 'true' or 'false'
  
  // categories
  define('SPECIALS_CATEGORIES', true);
  define('WHATSNEW_CATEGORIES', true);

  // template output
  define('TEMPLATE_ENGINE', 'smarty_3'); // smarty_3 or smarty_2
  define('TEMPLATE_HTML_ENGINE', 'html5'); // html5 or xhtml

?>