<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003 nextcommerce (xtc_get_subcategories.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_subcategories(&$subcategories_array, $parent_id = 0) {
    $subcategories_query = "select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . $parent_id . "'";
    $subcategories_query  = xtDBquery($subcategories_query);
    while ($subcategories = xtc_db_fetch_array($subcategories_query,true)) {
      $subcategories_array[sizeof($subcategories_array)] = $subcategories['categories_id'];
      if ($subcategories['categories_id'] != $parent_id) {
        xtc_get_subcategories($subcategories_array, $subcategories['categories_id']);
      }
    }
  }
 ?>