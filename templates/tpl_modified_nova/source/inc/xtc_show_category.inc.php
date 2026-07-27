<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


  function mod_count_products_in_category($categories_id, $product_counts = array()) {
    if (!defined('CATEGORIES_HIDE_EMPTY') || CATEGORIES_HIDE_EMPTY === false) {
      return 1;
    }
    
    return isset($product_counts[$categories_id]) ? $product_counts[$categories_id] : 0;
  }
  
  
  function xtc_get_category_tree_array($parent_id = 0, $max_depth = CATEGORIES_MAX_DEPTH, $level = 1, $category_tree_array = array()) {
    $categories_data_array = xtc_get_categories_tree_data($parent_id, $level);

    if (!empty($categories_data_array)) {
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
    static $category_data_array = null;
    
    if ($category_data_array === null) {
      $category_data_array = array();
      
      $categories_query = xtDBquery("SELECT c.categories_id,
                                            cd.categories_name,
                                            c.parent_id
                                       FROM ".TABLE_CATEGORIES." c
                                       JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                            ON cd.categories_id = c.categories_id
                                               AND cd.language_id = '".(int)$_SESSION['languages_id']."'
                                               AND trim(cd.categories_name) != ''
                                      WHERE c.categories_status = '1'
                                            ".CATEGORIES_CONDITIONS_C."
                                   ORDER BY c.sort_order, cd.categories_name");

      while ($categories = xtc_db_fetch_array($categories_query, true)) {
        $category_data_array[$categories['parent_id']][$categories['categories_id']] = array(
          'name' => $categories['categories_name'],
          'parent' => $categories['parent_id'],
          'id' => $categories['categories_id'],
        );
      }
    }
        
    $result = array();
    if (isset($category_data_array[$parent_id])) {
      foreach ($category_data_array[$parent_id] as $id => $category) {
        $category['level'] = $level;
        $result[$id] = $category;
      }
    }
    
    return $result;
  }


  function xtc_get_category_product_counts($category_tree_array) {
    global $modified_cache;

    if ((!defined('CATEGORIES_HIDE_EMPTY') || CATEGORIES_HIDE_EMPTY === false)
        && SHOW_COUNTS != 'true'
        )
    {
      return array();
    }

    $displayed_category_ids = array();
    foreach ($category_tree_array as $categories) {
      foreach ($categories as $category) {
        $displayed_category_ids[(int)$category['id']] = true;
      }
    }

    if (empty($displayed_category_ids)) {
      return array();
    }

    $cache_enabled = defined('DB_CACHE') && DB_CACHE == 'true';
    if ($cache_enabled && !is_object($modified_cache)) {
      include(DIR_FS_CATALOG.'includes/modified_cache.php');
    }

    if ($cache_enabled) {
      $aggregate_cache_id = 'category_product_totals_'.md5(
        'language:'.(int)$_SESSION['languages_id']
        .'|product_conditions:'.PRODUCTS_CONDITIONS_P
        .'|category_conditions:'.CATEGORIES_CONDITIONS_C
      );
      $modified_cache->setId($aggregate_cache_id);
      if ($modified_cache->isHit() === true) {
        $all_product_counts = $modified_cache->get();
        if (is_array($all_product_counts)) {
          $product_counts = array();
          foreach ($displayed_category_ids as $category_id => $unused) {
            $product_counts[$category_id] = isset($all_product_counts[$category_id])
              ? $all_product_counts[$category_id]
              : 0;
          }

          return $product_counts;
        }
      }
    }

    // Product counts include all visible descendants. Walking the cached
    // adjacency list preserves the existing behavior that traversal stops at
    // hidden, unnamed, or customer-group-inaccessible nodes.
    $visible_category_ids = array();
    $category_parent_ids = array();
    $category_depths = array();
    $pending_categories = array(
      array(
        'parent_id' => 0,
        'depth' => 0,
      ),
    );

    while (!empty($pending_categories)) {
      $pending = array_pop($pending_categories);
      $categories = xtc_get_categories_tree_data($pending['parent_id'], 1);

      foreach ($categories as $category) {
        $category_id = (int)$category['id'];

        if (isset($visible_category_ids[$category_id])) {
          continue;
        }

        $visible_category_ids[$category_id] = true;
        $category_parent_ids[$category_id] = (int)$category['parent'];
        $category_depths[$category_id] = $pending['depth'] + 1;
        $pending_categories[] = array(
          'parent_id' => $category_id,
          'depth' => $pending['depth'] + 1,
        );
      }
    }

    $direct_product_counts = array();
    $counts_cached = false;

    if ($cache_enabled) {
      $cache_id = 'category_product_counts_'.md5(
        'language:'.(int)$_SESSION['languages_id']
        .'|product_conditions:'.PRODUCTS_CONDITIONS_P
      );
      $modified_cache->setId($cache_id);
      if ($modified_cache->isHit() === true) {
        $direct_product_counts = $modified_cache->get();
        $counts_cached = is_array($direct_product_counts);
      }
    }

    if ($counts_cached === false) {
      $direct_product_counts = array();
      $products_query = xtDBquery(
        "SELECT p2c.categories_id,
                COUNT(*) AS total
           FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
  STRAIGHT_JOIN ".TABLE_PRODUCTS." p
             ON p.products_id = p2c.products_id
            AND p.products_status = '1'
  STRAIGHT_JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
             ON pd.products_id = p.products_id
            AND pd.language_id = '".(int)$_SESSION['languages_id']."'
            AND TRIM(pd.products_name) != ''
          WHERE 1 = 1
                ".PRODUCTS_CONDITIONS_P."
       GROUP BY p2c.categories_id"
      );
      while ($category = xtc_db_fetch_array($products_query, true)) {
        $direct_product_counts[(int)$category['categories_id']] = (int)$category['total'];
      }

      if ($cache_enabled) {
        $modified_cache->setId($cache_id);
        $modified_cache->set($direct_product_counts);
      }
    }

    $all_product_counts = array();
    foreach ($visible_category_ids as $category_id => $unused) {
      $all_product_counts[$category_id] = isset($direct_product_counts[$category_id])
        ? $direct_product_counts[$category_id]
        : 0;
    }

    arsort($category_depths);
    foreach ($category_depths as $category_id => $depth) {
      $parent_id = $category_parent_ids[$category_id];
      if ($parent_id > 0 && isset($all_product_counts[$parent_id])) {
        $all_product_counts[$parent_id] += $all_product_counts[$category_id];
      }
    }

    if ($cache_enabled) {
      $modified_cache->setId($aggregate_cache_id);
      $modified_cache->set($all_product_counts);
    }

    $product_counts = array();
    foreach ($displayed_category_ids as $category_id => $unused) {
      $product_counts[$category_id] = isset($all_product_counts[$category_id])
        ? $all_product_counts[$category_id]
        : 0;
    }

    return $product_counts;
  }
  
  
  function xtc_show_category($parent_id = 0, $path = '', $category_tree_array = array(), $product_counts = null) {
    global $categories_string, $cPath;

    if ($product_counts === null) {
      $product_counts = xtc_get_category_product_counts($category_tree_array);
    }

    foreach ($category_tree_array[$parent_id] as $categories) {
      if (mod_count_products_in_category($categories['id'], $product_counts) > 0) {
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
        $children = xtc_get_categories_tree_data($categories['id'], $level + 1);
        $count_children = !empty($children);
        if (defined('CATEGORIES_CHECK_SUBS') && (CATEGORIES_CHECK_SUBS == true)) {
          if($count_children === true) {
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
          $products_in_category = isset($product_counts[$categories['id']]) ? $product_counts[$categories['id']] : 0;
          if ($products_in_category > 0) {
            $categories_string .= '<span class="counts">(' . $products_in_category . ')</span>';
          }
        }  

        $categories_string .= '</a>';
        if (isset($category_tree_array[$categories['id']])) {
          if ($count_children === true) {
            $categories_string .= "\n";
            xtc_show_sub_category($level, true);

            // show all
            $categories_string .= $tab.'<li class="overview level'.($level + 1).$cat_active.'">';
            $categories_string .= '<a href="'.$link.'" title="'.encode_htmlentities($categories['name']).'">';
            $categories_string .= '<i class="fa-solid fa-circle-chevron-right"></i>' . TEXT_SHOW_CATEGORY . ' ' . $categories['name'];
            if (SHOW_COUNTS == 'true') {
              $products_in_category = isset($product_counts[$categories['id']]) ? $product_counts[$categories['id']] : 0;
              if ($products_in_category > 0) {
                $categories_string .= '<span class="counts">(' . $products_in_category . ')</span>';
              }
            }  
            $categories_string .= '</a>';
            $categories_string .= '</li>';

            $categories_string .= "\n";
            xtc_show_category($categories['id'], $link_path, $category_tree_array, $product_counts);
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
