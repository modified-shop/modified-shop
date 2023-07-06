<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include smarty
  include(DIR_FS_BOXES_INC . 'smarty_default.php');

  // set cache id
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|cP:'.$cPath.(((defined('SPECIALS_CATEGORIES') && SPECIALS_CATEGORIES === true) || (defined('WHATSNEW_CATEGORIES') && WHATSNEW_CATEGORIES === true)) ? '|self:'.basename($PHP_SELF) : ''));

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_categories.html', $cache_id) || !$cache) {

    // include needed functions
    require_once (DIR_FS_BOXES_INC.'xtc_show_category.inc.php');
    require_once (DIR_FS_INC.'xtc_count_products_in_category.inc.php');
  
    $categories_string = '';
    $category_tree_array = xtc_get_category_tree_array();
  
    if (count($category_tree_array) > 0) {
      xtc_show_category(0, '', $category_tree_array);    
    }
  
    $box_smarty->assign('BOX_CONTENT', $categories_string);
  }

  $box_categories = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_categories.html', $cache_id);

  $smarty->assign('box_CATEGORIES', $box_categories);
