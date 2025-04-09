<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2025 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function xtc_update_products_viewed_count($products_id, $language_id) {    
    if (defined('MODULE_PRODUCTS_HISTORY_STATUS') && MODULE_PRODUCTS_HISTORY_STATUS == 'false') {
      return;
    }

    xtc_db_query("UPDATE ".TABLE_PRODUCTS_DESCRIPTION."
                     SET products_viewed = products_viewed+1
                   WHERE products_id = '".(int)$products_id."'
                     AND language_id = '".(int)$language_id."'");
  }
