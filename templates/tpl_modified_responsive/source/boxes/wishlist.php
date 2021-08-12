<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC.'get_wishlist_content.inc.php');

  $box_smarty = new Smarty;
  $box_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

  // define defaults
  $products_in_wishlist = array ();

  if ($_SESSION['wishlist']->count_contents() > 0) {

    // build array with wishlist content and count quantity  
    if (strpos($PHP_SELF, FILENAME_LOGOFF) === false) {
      $wishlist_content_array = get_wishlist_content();
      $products_in_wishlist = $wishlist_content_array['DATA'];
    }
  }
  
  $count_wishlist = count($products_in_wishlist);
  
  $box_smarty->assign('deny_cart', strpos($PHP_SELF, 'checkout') !== false ? 'true' : 'false'); // no cart at the checkout
  $box_smarty->assign('products', $products_in_wishlist);
  $box_smarty->assign('PRODUCTS', $count_wishlist);
  $box_smarty->assign('empty', $count_wishlist > 0 ? 'false' : 'true');

  $box_smarty->assign('LINK_WISHLIST', xtc_href_link(FILENAME_WISHLIST, '', 'NONSSL'));
  $box_smarty->caching = 0;
  $box_smarty->assign('language', $_SESSION['language']);
  $box_wishlist = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_wishlist.html');
  $smarty->assign('box_WISHLIST', $box_wishlist);
?>