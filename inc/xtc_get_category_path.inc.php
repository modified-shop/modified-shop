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
   (c) 2003	 nextcommerce (xtc_get_product_path.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // Construct a category path
  function xtc_get_category_path($cID) {
    static $cPath_cache;
  
    if (!is_array($cPath_cache)) {
      $cPath_cache = array();
    }
  
    if (defined('DB_CACHE') && DB_CACHE == 'true') {
      require_once(DIR_FS_CATALOG.'includes/classes/modified_cache.php');
      
      $modified_cache->setId('cp_'.$cID);
      if ($modified_cache->isHit() !== false) {
        $cPath_cache[$cID] = $modified_cache->get();
      }
    }
  
    if (!isset($cPath_cache[$cID])) {
      $categories = array();
      xtc_get_parent_categories($categories, $cID);

      $categories = array_reverse($categories);

      $categories[] = $cID;
      $cPath_cache[$cID] = implode('_', $categories);
      
      if (defined('DB_CACHE') && DB_CACHE == 'true') {
        $modified_cache->setId('cp_'.$cID);
        $modified_cache->set($cPath_cache[$cID]);
      }
    }
    
    return $cPath_cache[$cID];
  }
?>