<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


  function xtc_count_products_in_category_array($parent_id, $category_tree_array) {
    $products_in_category = 0;
    foreach ($category_tree_array[$parent_id] as $categories) {
      $products_in_category += mod_count_products_in_category($categories['id']);
    }
    
    return $products_in_category;
  }
  
  
  function mod_count_products_in_category($categories_id) {
    if (!defined('CATEGORIES_HIDE_EMPTY') || CATEGORIES_HIDE_EMPTY === false) {
      return 1;
    }
    
    return xtc_count_products_in_category($categories_id);
  }
  
  
  function xtc_get_category_tree_array($parent_id = 0, $max_depth = CATEGORIES_MAX_DEPTH, $level = 1, $category_tree_array = array()) {
    $categories_data_array = xtc_get_categories_tree_data($parent_id, $level);

    if (count($categories_data_array) > 0) {
      $category_tree_array[$parent_id] =  $categories_data_array;
      
      foreach ($categories_data_array as $categories_data) {
        $category_tree_array[$parent_id][$categories_data['id']]['level'] = $level;
        
        if ($categories_data['level'] < $max_depth) {
          $category_tree_array = xtc_get_category_tree_array($categories_data['id'], $max_depth, $level + 1, $category_tree_array);
        }
      }
    }
    
    return $category_tree_array;
  }


  function xtc_get_categories_tree_data($parent_id, $level) {
    static $category_data_array;
    
    if (!isset($category_data_array)) $category_data_array = array();
    
    if (!isset($category_data_array[$parent_id])) {
      $category_data_array[$parent_id] = array();
      
      $categories_query = xtDBquery("SELECT c.categories_id,
                                            cd.categories_name,
                                            c.parent_id
                                       FROM ".TABLE_CATEGORIES." c
                                       JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                            ON c.categories_id = cd.categories_id
                                               AND cd.language_id = '".(int)$_SESSION['languages_id']."'
                                               AND trim(cd.categories_name) != ''
                                      WHERE c.parent_id = '".(int)$parent_id."'
                                        AND c.categories_status = '1'
                                            ".CATEGORIES_CONDITIONS_C."
                                   ORDER BY c.sort_order, cd.categories_name");
      if (xtc_db_num_rows($categories_query, true) > 0) {
        while ($categories = xtc_db_fetch_array($categories_query, true)) {
          $category_data_array[$parent_id][$categories['categories_id']] = array(
            'name' => $categories['categories_name'],
            'parent' => $categories['parent_id'],
            'id' => $categories['categories_id'],
            'level' => $level,
          );
        }
      }
    }
        
    return $category_data_array[$parent_id];
  }
  
  
  function xtc_show_category($parent_id = 0, $path = '', $category_tree_array = array()) {
    global $categories_string, $cPath;
    
    foreach ($category_tree_array[$parent_id] as $categories) {
      if (mod_count_products_in_category($categories['id']) > 0) {
        $level = $categories['level'];
        $tab = str_repeat("\t", $level);
        $category_path = explode('_', $cPath);
        $link_path = $path . (($path != '') ? '_' : '') . $categories['id'];      
        $link = xtc_href_link(FILENAME_DEFAULT, 'cPath='.$link_path, 'NONSSL');
        
        $cat_active_parent = '';
        if (in_array($categories['id'], $category_path)) {
          $cat_active_parent = " activeparent".$level;
        }
    
        $cat_active = '';
        if (end($category_path) == $categories['id']) {
          // Selected for mmenulight
          $cat_active = " Selected active".$level;
        }

        // mark subs
        $hasSubs = '';
        if (defined('CATEGORIES_CHECK_SUBS') && (CATEGORIES_CHECK_SUBS == true)) {
          require_once (DIR_FS_INC . 'xtc_has_category_subcategories.inc.php');
          $tmpCheck = xtc_has_category_subcategories($categories['id']);
          if ($tmpCheck) {
            $hasSubs = ' has_sub_cats';
          }
        }
        $categories_string .= $tab.'<li class="level'.$level.$cat_active.$cat_active_parent.$hasSubs.'">';
        $categories_string .= '<a href="'.$link.'" title="'.encode_htmlentities($categories['name']).'">';

        $categories_string .= $categories['name'];
        if ($level == 1) {
          if ($hasSubs != '') {
            $categories_string .= '<span class="sub_cats_arrow"></span>';
          }
        }

        if (SHOW_COUNTS == 'true') {
          $products_in_category = xtc_count_products_in_category($categories['id']);
          if ($products_in_category > 0) {
            $categories_string .= '<span class="counts">(' . $products_in_category . ')</span>';
          }
        }  

        $categories_string .= '</a>';
        if (isset($category_tree_array[$categories['id']])) {
          if (xtc_count_products_in_category_array($categories['id'], $category_tree_array) > 0) {
            $categories_string .= "\n";
            xtc_show_sub_category($level, true);

            // show all
            $categories_string .= $tab.'<li class="overview level'.($level + 1).$cat_active.'">';
            $categories_string .= '<a href="'.$link.'" title="'.encode_htmlentities($categories['name']).'">';
            $categories_string .= '<i class="fa-solid fa-circle-chevron-right"></i>' . TEXT_SHOW_CATEGORY . ' ' . $categories['name'];
            if (SHOW_COUNTS == 'true') {
              $products_in_category = xtc_count_products_in_category($categories['id']);
              if ($products_in_category > 0) {
                $categories_string .= '<span class="counts">(' . $products_in_category . ')</span>';
              }
            }  
            $categories_string .= '</a>';
            $categories_string .= '</li>';

            $categories_string .= "\n";
            xtc_show_category($categories['id'], $link_path, $category_tree_array);
            xtc_show_sub_category($level, false);
            $categories_string .= "\n".$tab;            
          }
        }
        $categories_string .= '</li>';
        $categories_string .= "\n";
      }
    }
  }
  
  
  function xtc_show_sub_category($level, $open = true) {
    global $categories_string, $tab;
    
    defined('CATEGORIES_CASE') OR define('CATEGORIES_CASE', 1);

    switch (CATEGORIES_CASE) {
      case '1':
        if ($open === true) {
          if ($level == 1) {
            $categories_string .= $tab.'<div class="mega_menu">';
          }
          $categories_string .= '<ul class="cf">';
        } else {
          $categories_string .= $tab.'</ul>';
          if ($level == 1) {
            $categories_string .= '</div>';
          }
        }
        break;
    
      case '2':
        if ($open === true) {
          $categories_string .= $tab.'<ul class="dropdown_menu">';
        } else {
          $categories_string .= $tab.'</ul>';
        }
        break;
        
      default:
        if ($open === true) {
          $categories_string .= $tab.'<ul>';
        } else {
          $categories_string .= $tab.'</ul>';
        }
        break;
    }
  }
