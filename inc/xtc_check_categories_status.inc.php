<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_check_categories_status($categories_id) {
    $categorie_query = xtDBquery("SELECT parent_id,
                                         categories_status
                                    FROM ".TABLE_CATEGORIES."
                                   WHERE categories_id = '".(int)$categories_id."'
                                         ".CATEGORIES_CONDITIONS);
    $categorie_data = xtc_db_fetch_array($categorie_query, true);

    if ($categorie_data['categories_status'] == 0) {
      return false;
    } else {
      if ($categorie_data['parent_id'] != 0) {
        return xtc_check_categories_status($categorie_data['parent_id']);
      }
      return true;
    }
  }
?>