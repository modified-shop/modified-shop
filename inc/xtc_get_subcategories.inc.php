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
  
    if (defined('DB_CACHE') && DB_CACHE == 'true') {
      require_once(DIR_FS_CATALOG.'includes/classes/modified_cache.php');
      
      if (!is_object($modified_cache)) {
        $_mod_cache_class = strtolower(DB_CACHE_TYPE).'_cache';
        if (!class_exists($_mod_cache_class)) {
          $_mod_cache_class = 'modified_cache';
        }
        $modified_cache = $_mod_cache_class::getInstance();
      }

      $modified_cache->setId('sc_'.$parent_id);
      if ($modified_cache->isHit() !== false) {
        $subcategories_cache[$parent_id] = $modified_cache->get();
      }
    }
    
    if (!isset($subcategories_cache[$parent_id])) {
      $subcategories_cache_array = array();
      xtc_get_subcategories_data($subcategories_cache_array, $parent_id);
      $subcategories_cache[$parent_id] = $subcategories_cache_array;
    }

    if (defined('DB_CACHE') && DB_CACHE == 'true') {
      $modified_cache->setId('sc_'.$parent_id);
      $modified_cache->set($subcategories_cache[$parent_id]);
    }
    
    $subcategories_array = $subcategories_cache[$parent_id];
  }
  
  
  function xtc_get_subcategories_data(&$subcategories_cache_array, $parent_id = 0) {
    $subcategories_query = xtDBquery("SELECT c.categories_id 
                                        FROM " . TABLE_CATEGORIES . " c
                                        JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                             ON c.categories_id = cd.categories_id
                                                AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                                AND trim(cd.categories_name) != ''
                                       WHERE c.parent_id = '" . (int)$parent_id . "'
                                             ".CATEGORIES_CONDITIONS_C);
    
    if (xtc_db_num_rows($subcategories_query, true) > 0) {
      while ($subcategories = xtc_db_fetch_array($subcategories_query, true)) {
        $subcategories_cache_array[count($subcategories_cache_array)] = $subcategories['categories_id'];
      
        if ($subcategories['categories_id'] != $parent_id) {
          xtc_get_subcategories_data($subcategories_cache_array, $subcategories['categories_id']);
        }
      }
    }
  }
?>