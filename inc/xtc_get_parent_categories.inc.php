<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_parent_categories.inc.php 1009 2005-07-11 16:19:29Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_parent_categories.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // Recursively go through the categories and retreive all parent categories IDs

  function xtc_get_parent_categories(&$categories, $categories_id, $cID = '') {
    static $parent_id_cache;
    static $parent_categories_cache;
    
    if ($cID == '') {
      $cID = $categories_id;
    }
    
    if (!is_array($parent_id_cache)) {
      $parent_id_cache = array();
    }

    if (!is_array($parent_categories_cache)) {
      $parent_categories_cache = array();
    }
    
    if (isset($parent_categories_cache[$cID])) {
      $categories = $parent_categories_cache[$cID];
      return true;
    }
  
    if (!isset($parent_id_cache[$categories_id])) {
      $parent_categories_query = "SELECT parent_id 
                                    FROM " . TABLE_CATEGORIES . " 
                                   WHERE categories_id = '" . (int)$categories_id . "'";
      $parent_categories_query  = xtDBquery($parent_categories_query);
      while ($parent_categories = xtc_db_fetch_array($parent_categories_query, true)) {
        $parent_id_cache[$categories_id] = $parent_categories['parent_id'];
      
        if ($parent_categories['parent_id'] == 0) {
          $parent_categories_cache[$cID] = $categories;
          return true;
        }
        $categories[sizeof($categories)] = $parent_categories['parent_id'];
      
        if ($parent_categories['parent_id'] != $categories_id) {
          xtc_get_parent_categories($categories, $parent_categories['parent_id'], $cID);
        }
      }
    } else {
      $parent_id = $parent_id_cache[$categories_id];
      if ($parent_id == 0) {
        $parent_categories_cache[$cID] = $categories;
        return true;
      }
      $categories[sizeof($categories)] = $parent_id;
      
      if ($parent_id != $categories_id) {
        xtc_get_parent_categories($categories, $parent_id, $cID);
      }
    }
  }
?>