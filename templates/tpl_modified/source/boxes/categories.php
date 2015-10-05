<?php
/* -----------------------------------------------------------------------------------------
   $Id: categories.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(categories.php,v 1.23 2002/11/12); www.oscommerce.com
   (c) 2003 nextcommerce (categories.php,v 1.10 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Enable_Disable_Categories 1.3          Autor: Mikel Williams | mikel@ladykatcostumes.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// set cache id
$cache_id = md5($_SESSION['language'].$_SESSION['customers_status']['customers_status'].'-'.$cPath.((SPECIALS_CATEGORIES === true || WHATSNEW_CATEGORIES === true) ? '-'.basename($PHP_SELF) : ''));

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_categories.html', $cache_id) || !$cache) {

  // include needed functions
  require_once (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/inc/xtc_show_category.inc.php');
  require_once (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/inc/close_ul_tags.inc.php');
  require_once (DIR_FS_INC.'xtc_has_category_subcategories.inc.php');
  require_once (DIR_FS_INC.'xtc_count_products_in_category.inc.php');
  
  $foo = array();
  $categories_string = '';
  unset($prev_id);
  
  $categories_query = xtDBquery("SELECT c.categories_id,
                                        cd.categories_name,
                                        c.parent_id
                                   FROM ".TABLE_CATEGORIES." c
                                   JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                        ON c.categories_id = cd.categories_id
                                           AND cd.language_id='".(int)$_SESSION['languages_id']."'
                                           AND trim(cd.categories_name) != ''
                                  WHERE c.categories_status = '1'
                                    AND c.parent_id = '0'
                                        ".CATEGORIES_CONDITIONS_C."
                               ORDER BY c.sort_order, cd.categories_name");
  if (xtc_db_num_rows($categories_query, true) > 0) {
    while ($categories = xtc_db_fetch_array($categories_query, true)) {
      $categories['cat_link'] = xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories['categories_id'],$categories['categories_name']));
      $foo[$categories['categories_id']] = array (
          'name' => $categories['categories_name'],
          'link' => $categories['cat_link'],
          'parent' => $categories['parent_id'],
          'level' => 0,
          'path' => $categories['categories_id'],
          'next_id' => false
        );

      if (isset($prev_id)) {
        $foo[$prev_id]['next_id'] = $categories['categories_id'];
      }

      $prev_id = $categories['categories_id'];

      if (!isset ($first_element)) {
        $first_element = $categories['categories_id'];
      }
    }

    if ($cPath) {
      $new_path = '';
      $id = explode('_', $cPath);
      reset($id);
      while (list ($key, $value) = each($id)) {
        unset ($prev_id);
        unset ($first_id);
        $categories_query = xtDBquery("SELECT c.categories_id,
                                              cd.categories_name,
                                              c.parent_id
                                         FROM ".TABLE_CATEGORIES." c
                                         JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                              ON c.categories_id = cd.categories_id
                                                 AND cd.language_id='".(int)$_SESSION['languages_id']."'
                                                 AND trim(cd.categories_name) != ''
                                        WHERE c.categories_status = '1'
                                          AND c.parent_id = '".$value."'
                                              ".CATEGORIES_CONDITIONS_C."
                                     ORDER BY c.sort_order, cd.categories_name");
                                      
        if (xtc_db_num_rows($categories_query, true) > 0) {
          $new_path .= $value;
          while ($row = xtc_db_fetch_array($categories_query, true)) {
            $row['cat_link'] = xtc_href_link(FILENAME_DEFAULT, xtc_category_link($row['categories_id'], $row['categories_name']));
            $foo[$row['categories_id']] = array (
                'name' => $row['categories_name'],
                'link' => $row['cat_link'],
                'parent' => $row['parent_id'],
                'level' => $key +1,
                'path' => $new_path.'_'.$row['categories_id'],
                'next_id' => false
              );
            if (isset ($prev_id)) {
              $foo[$prev_id]['next_id'] = $row['categories_id'];
            }
            $prev_id = $row['categories_id'];
            if (!isset ($first_id)) {
              $first_id = $row['categories_id'];
            }
            $last_id = $row['categories_id'];
          }
          $foo[$last_id]['next_id'] = isset($foo[$value]['next_id']) ? $foo[$value]['next_id'] : 0;
          $foo[$value]['next_id'] = $first_id;
          $new_path .= '_';
        } else {
          break;
        }
      }
    }
    
    if (!empty($first_element)) {
      xtc_show_category($first_element);
    }
    
    $box_smarty->assign('BOX_CONTENT', $categories_string);
  }
}

if (!$cache) {
  $box_categories = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_categories.html');
} else {
  $box_categories = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_categories.html', $cache_id);
}

$smarty->assign('box_CATEGORIES', $box_categories);
?>