<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_category_path.inc.php 868 2005-04-23 19:28:27Z hhgag $   

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
    
    if (!isset($cPath_cache[$cID])) {       
      $categories = array();
      xtc_get_parent_categories($categories, $cID);

      $categories = array_reverse($categories);
    
      $categories[] = $cID;
      $cPath = implode('_', $categories);
    
      $cPath_cache[$cID] = $cPath;
    }
    
    return $cPath_cache[$cID];
  }
?>