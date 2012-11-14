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
   (c) 2003 nextcommerce (xtc_get_product_path.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_product_path.inc.php 1009 2005-07-11)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// Construct a category path to the product
// TABLES: products_to_categories
  function xtc_get_product_path($products_id) {
    $cPath = '';

    $category_query = "select p2c.categories_id 
                        from " . TABLE_PRODUCTS . " p,
                        " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                        where p.products_id = '" . (int)$products_id . "'
                        and p.products_status = '1'
                        and p.products_id = p2c.products_id
                        and p2c.categories_id != 0
                        limit 1";
    $category_query  = xtDBquery($category_query);
    //BOF - DokuMan - 2010-08-30 - check if there's at least one row
    //if (xtc_db_num_rows($category_query,true)) {
    if (xtc_db_num_rows($category_query,true) > 0) {
    //EOF - DokuMan - 2010-08-30 - check if there's at least one row
      $category = xtc_db_fetch_array($category_query);

      $categories = array();
      xtc_get_parent_categories($categories, $category['categories_id']);

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);

      if (xtc_not_null($cPath)) $cPath .= '_';
      $cPath .= $category['categories_id'];
  }
//BOF - Dokuman - 2009-10-02 - removed feature, due to wrong links in category on "last viewed"  
/*
  if($_SESSION['lastpath']!=''){
    $cPath = $_SESSION['lastpath'];
  }
*/
//EOF - Dokuman - 2009-10-02 - removed feature, due to wrong links in category on "last viewed"  
  return $cPath;
}
?>