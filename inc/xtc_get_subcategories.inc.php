<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_subcategories.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_subcategories(&$subcategories_array, $parent_id = 0) {
    global $modified_cache;
    static $subcategories_cache;
    
    if (!isset($subcategories_cache)) {
      $subcategories_cache = array();
    }

    $cache_enabled = defined('DB_CACHE') && DB_CACHE == 'true';
    if ($cache_enabled) {
      if (!is_object($modified_cache)) {
        include(DIR_FS_CATALOG.'includes/modified_cache.php');
      }

      $cache_id = 'sc_'.md5(
        'parent:'.(int)$parent_id
        .'|language:'.(int)$_SESSION['languages_id']
        .'|category_conditions:'.(defined('RUN_MODE_ADMIN') ? 'admin' : CATEGORIES_CONDITIONS_C)
      );
      $modified_cache->setId($cache_id);
      if ($modified_cache->isHit() !== false) {
        $subcategories_cache[$parent_id] = $modified_cache->get();
      }
    }
    
    if (!isset($subcategories_cache[$parent_id])) {
      $subcategories_cache_array = array();
      xtc_get_subcategories_data($subcategories_cache_array, $parent_id);
      $subcategories_cache[$parent_id] = $subcategories_cache_array;

      if ($cache_enabled) {
        $modified_cache->setId($cache_id);
        $modified_cache->set($subcategories_cache[$parent_id]);
        $modified_cache->setTags(array('categories', 'subcategories'));
      }
    }
    
    $subcategories_array = $subcategories_cache[$parent_id];
  }
  
  
  function xtc_get_subcategories_data(&$subcategories_cache_array, $parent_id = 0) {
    static $child_categories_array;

    if (!isset($child_categories_array)) {
      $child_categories_array = array();
    }

    $parent_id = (int)$parent_id;

    // read the requested subtree level by level, one query per level
    // instead of one query per node
    if (!isset($child_categories_array[$parent_id])) {
      $join = '';
      $conditions = '';
      if (!defined('RUN_MODE_ADMIN')) {
        $join = " AND trim(cd.categories_name) != '' ";
        $conditions .= " AND c.categories_status = 1 ";
        $conditions .= CATEGORIES_CONDITIONS_C;
      }

      $pending_categories = array($parent_id => $parent_id);
      while (count($pending_categories) > 0) {
        foreach ($pending_categories as $pending_id) {
          $child_categories_array[$pending_id] = array();
        }

        $subcategories_query = xtDBquery("SELECT c.categories_id,
                                                 c.parent_id
                                            FROM " . TABLE_CATEGORIES . " c
                                            JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                                 ON c.categories_id = cd.categories_id
                                                    AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                                    " . $join . "
                                           WHERE c.parent_id IN ('" . implode("', '", $pending_categories) . "')
                                                 " . $conditions);

        $pending_categories = array();
        while ($subcategories = xtc_db_fetch_array($subcategories_query, true)) {
          $categories_id = (int)$subcategories['categories_id'];
          $child_categories_array[(int)$subcategories['parent_id']][] = $categories_id;

          if (!isset($child_categories_array[$categories_id])) {
            $pending_categories[$categories_id] = $categories_id;
          }
        }
      }
    }

    foreach ($child_categories_array[$parent_id] as $categories_id) {
      $subcategories_cache_array[count($subcategories_cache_array)] = $categories_id;

      if ($categories_id != $parent_id) {
        xtc_get_subcategories_data($subcategories_cache_array, $categories_id);
      }
    }
  }
?>
