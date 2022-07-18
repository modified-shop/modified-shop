<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_category_data($categories_id, $languages_id = '') {
    static $categories_array_cache;
  
    if (!isset($categories_array_cache)) {
      $categories_array_cache = array();
    }
    
    if ($languages_id == '') {
      $languages_id = (int)$_SESSION['languages_id'];
    }
    
    if (!isset($categories_array_cache[$languages_id][$categories_id])) {
      $categories_array_cache[$languages_id][$categories_id] = array();
      
      $join = '';
      $conditions = '';
      if (!defined('RUN_MODE_ADMIN')) {
        $join = " AND trim(cd.categories_name) != '' ";
        $conditions .= " AND c.categories_status = 1 ";
        $conditions .= CATEGORIES_CONDITIONS_C;
      }

      $category_query = xtDBquery("SELECT *
                                     FROM ".TABLE_CATEGORIES." c
                                     JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd 
                                          ON cd.categories_id = c.categories_id
                                             AND cd.language_id = '".(int)$languages_id."'
                                             ".$join."
                                    WHERE c.categories_id = '".(int)$categories_id."'
                                          ".$conditions);
      if (xtc_db_num_rows($category_query, true) > 0) {
        $categories_array_cache[$languages_id][$categories_id] = xtc_db_fetch_array($category_query, true);
      }
    }
    
    return $categories_array_cache[$languages_id][$categories_id];
  }
