<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_check_categories_status($categories_id) {
    $category_query = xtDBquery("SELECT parent_id,
                                        categories_status
                                   FROM ".TABLE_CATEGORIES."
                                  WHERE categories_id = '".(int)$categories_id."'
                                        ".CATEGORIES_CONDITIONS);
    if (xtc_db_num_rows($category_query, true) > 0) {
      $category = xtc_db_fetch_array($category_query, true);
      if ($category['categories_status'] == 1) {
        if ($category['parent_id'] != 0) {
          return xtc_check_categories_status($category['parent_id']);
        }
        return true;
      }
    }
    
    return false;
  }
?>