<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_count_products_in_category.inc.php 1009 2005-07-11 16:19:29Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_count_products_in_category.inc.php,v 1.3 2003/08/13); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_count_products_in_category($category_id, $include_inactive = false) {
    
    $group_check = ''; //DokuMan - 2010-03-12 - set undefined variable
    
    //BOF - Dokuman - 2009-09-02: do not count products when GROUP_CHECK disabled
		if(GROUP_CHECK == 'true') {
			$group_check = " AND p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
		}
    //EOF - Dokuman - 2009-09-02: do not count products when GROUP_CHECK disabled

    $products_count = 0;
    if ($include_inactive == true) {
    //BOF - Dokuman - 2009-09-02: do not count products when GROUP_CHECK disabled   
    //$products_query = "select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p2c.categories_id = '" . $category_id . "'";
			$products_query = "select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p2c.categories_id = '" . $category_id . "'".$group_check;
    //EOF - Dokuman - 2009-09-02: do not count products when GROUP_CHECK disabled         

    } else {
    //BOF - Dokuman - 2009-09-02: do not count products when GROUP_CHECK disabled     
    //$products_query = "select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p.products_status = '1' and p2c.categories_id = '" . $category_id . "'";
      $products_query = "select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p.products_status = '1' and p2c.categories_id = '" . $category_id . "'".$group_check;   
    //EOF - Dokuman - 2009-09-02: do not count products when GROUP_CHECK disabled         
    }

    $products_query = xtDBquery($products_query);

    $products = xtc_db_fetch_array($products_query,true);
    $products_count += $products['total'];

    //BOF - Dokuman - 2009-09-02: do not count products when GROUP_CHECK disabled         
		if(GROUP_CHECK == 'true') {
			$group_check = " AND group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
		}
    //$child_categories_query = "select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . $category_id . "'";
    $child_categories_query = "select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . $category_id . "'".$group_check;
    //EOF - Dokuman - 2009-09-02: do not count products when GROUP_CHECK disabled         

    $child_categories_query = xtDBquery($child_categories_query);
    if (xtc_db_num_rows($child_categories_query,true)) {
      while ($child_categories = xtc_db_fetch_array($child_categories_query,true)) {
        $products_count += xtc_count_products_in_category($child_categories['categories_id'], $include_inactive);
      }
    }


    return $products_count;
  }
 ?>