<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function check_specials() {
  static $specials_check;
  
  if (!isset($specials_check)) {
    $specials_check = false;
    
    if ($_SESSION['customers_status']['customers_status_specials'] == '1') {
      $products_specials_query = xtc_db_query("SELECT p.products_id
                                                 FROM ".TABLE_PRODUCTS." p
                                                 JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                                      ON p.products_id = pd.products_id
                                                         AND pd.language_id = ".(int)$_SESSION['languages_id']."
                                                         AND trim(pd.products_name) != ''
                                                 JOIN ".TABLE_SPECIALS." s
                                                      ON p.products_id = s.products_id
                                                         ".SPECIALS_CONDITIONS_S."
                                                WHERE p.products_status = '1'
                                                      ".PRODUCTS_CONDITIONS_P."
                                                  AND EXISTS (
                                                        SELECT 1
                                                          FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                                          JOIN ".TABLE_CATEGORIES." c
                                                            ON c.categories_id = p2c.categories_id
                                                           AND c.categories_status = 1
                                                               ".CATEGORIES_CONDITIONS_C."
                                                         WHERE p2c.products_id = p.products_id
                                                      )
                                                LIMIT 1");
      if (xtc_db_num_rows($products_specials_query) > 0) {
        $specials_check = true;
      }
    }
  }
  
  return $specials_check;
}
