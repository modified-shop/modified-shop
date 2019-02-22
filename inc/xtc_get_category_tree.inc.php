<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce www.oscommerce.com
   (c) 2003 nextcommerce www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function xtc_get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $cPath = '') {
    if ($parent_id == 0){ 
      $cPath = ''; 
    } else { 
      $cPath .= $parent_id . '_';
    }
  
    if (!is_array($category_tree_array)) { 
      $category_tree_array = array(); 
    }
  
    if ((sizeof($category_tree_array) < 1) && ($exclude != '0') ) {
      $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);
    }
  
    $conditions = '';
    if (!defined('RUN_MODE_ADMIN')) {
      $conditions = CATEGORIES_CONDITIONS_C;
    }
  
    if ($include_itself) {
      $category_query = xtDBquery("SELECT cd.categories_name
                                     FROM " . TABLE_CATEGORIES . " c
                                     JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                          ON c.categories_id = cd.categories_id
                                             AND cd.language_id = ".(int)$_SESSION['languages_id']."
                                             AND trim(cd.categories_name) != ''
                                    WHERE c.categories_status = 1
                                          ".$conditions."
                                      AND c.categories_id = '".(int)$parent_id."'
                                    LIMIT 1");
      $category = xtc_db_fetch_array($category_query, true);

      $link = '';
      if (!defined('RUN_MODE_ADMIN')) {
        $link = xtc_href_link(FILENAME_DEFAULT, xtc_category_link($category['categories_id'], $category['categories_name']));
      }

      $category_tree_array[] = array(
        'id' => $parent_id, 
        'text' => $category['categories_name'],
        'link' => $link,
      );
    }

    $categories_query = xtDBquery("SELECT c.categories_id, 
                                          cd.categories_name, 
                                          c.parent_id
                                     FROM " . TABLE_CATEGORIES . " c
                                     JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                          ON c.categories_id = cd.categories_id
                                             AND cd.language_id = ".(int)$_SESSION['languages_id']."
                                             AND trim(cd.categories_name) != ''
                                    WHERE c.parent_id = '".(int)$parent_id."'
                                      AND c.categories_status = '1'
                                          ".$conditions."
                                 ORDER BY c.sort_order, cd.categories_name");
    while ($categories = xtc_db_fetch_array($categories_query,true)) {
      if ($exclude != $categories['categories_id']) {
     
        $link = '';
        if (!defined('RUN_MODE_ADMIN')) {
          $link = xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories['categories_id'], $categories['categories_name']));
        }
      
        $category_tree_array[] = array(
          'id' => $categories['categories_id'],
          'text' => $spacing . $categories['categories_name'],
          'link' => $link,
        );
      }
    
      $category_tree_array = xtc_get_category_tree($categories['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, false, $cPath);
    }

    return $category_tree_array;
  }
?>