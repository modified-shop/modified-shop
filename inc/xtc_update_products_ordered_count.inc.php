<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2025 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function xtc_update_products_ordered_count($products_id, qty) {    
    if (defined('MODULE_PRODUCTS_PURCHASED_HISTORY_STATUS') && MODULE_PRODUCTS_PURCHASED_HISTORY_STATUS == 'false') {
      return;
    }

    xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                     SET products_ordered = products_ordered + ".sprintf('%d', $qty)."
                   WHERE products_id = '".xtc_get_prid($products_id)."'");
  }
