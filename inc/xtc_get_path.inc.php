<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_path.inc.php 1009 2005-07-11 16:19:29Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_path.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_path($current_category_id = '') {
    global $cPath_array;

    if (xtc_not_null($current_category_id)) {
      $cp_size = sizeof($cPath_array);
      if ($cp_size == 0) {
        $cPath_new = $current_category_id;
      } else {
        $cPath_new = '';
        $last_category_query = "SELECT parent_id 
                                  FROM " . TABLE_CATEGORIES . " 
                                 WHERE categories_id = '" . (int)$cPath_array[($cp_size-1)] . "'";
        $last_category_query  = xtDBquery($last_category_query);
        $last_category = xtc_db_fetch_array($last_category_query,true);

        $current_category_query = "SELECT parent_id 
                                     FROM " . TABLE_CATEGORIES . " 
                                    WHERE categories_id = '" . (int)$current_category_id . "'";
        $current_category_query  = xtDBquery($current_category_query);
        $current_category = xtc_db_fetch_array($current_category_query,true);

        if ($last_category['parent_id'] == $current_category['parent_id']) {
          for ($i=0; $i<($cp_size-1); $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        } else {
          for ($i=0; $i<$cp_size; $i++) {
            $cPath_new .= '_' . $cPath_array[$i];
          }
        }
        $cPath_new .= '_' . $current_category_id;

        if (substr($cPath_new, 0, 1) == '_') {
          $cPath_new = substr($cPath_new, 1);
        }
      }
    } else {
       $cPath_new = (xtc_not_null($cPath_array)) ? implode('_', $cPath_array) : '';
    }

    return 'cPath=' . $cPath_new;
  }
 ?>