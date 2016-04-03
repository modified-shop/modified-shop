<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_has_product_attributes.inc.php 1589 2010-12-24 14:10:13Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_has_product_attributes.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_has_product_attributes.inc.php 1009 2005-07-11)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Check if product has attributes
  function xtc_has_product_attributes($products_id) {
  
    $attributes_query = "SELECT count(*) as count 
                           FROM " . TABLE_PRODUCTS_ATTRIBUTES . " 
                          WHERE products_id = '" . (int)$products_id . "'";
    $attributes_query = xtDBquery($attributes_query);
    $attributes = xtc_db_fetch_array($attributes_query,true);

    if ($attributes['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }
 ?>