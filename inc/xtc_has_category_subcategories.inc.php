<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_has_category_subcategories.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_has_category_subcategories($category_id) {
    $child_category_query = "select count(*) as count from " . TABLE_CATEGORIES . " where parent_id = '" . $category_id . "'";
    $child_category_query = xtDBquery($child_category_query);
    $child_category = xtc_db_fetch_array($child_category_query,true);

    if ($child_category['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }
  
 ?>