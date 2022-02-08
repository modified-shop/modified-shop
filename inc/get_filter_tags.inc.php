<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function get_filter_tags() {
    static $filter_tags;
    
    if (!isset($filter_tags)) {
      $filter_tags = array();
    }
    
    $index = isset($_GET['filter']) && is_array($_GET['filter']) ? md5(serialize($_GET['filter'])) : 0;
    
    if (!isset($filter_tags[$index])) {
      $tags_array = array();
      if (isset($_GET['filter']) && is_array($_GET['filter'])) {     
        foreach ($_GET['filter'] as $options_id => $values_id) {
          if ($values_id != '') {
            $tags_array = get_filter_products($tags_array, $options_id, $values_id);          
          }
        }
      }
      $filter_tags[$index] = $tags_array;
    }
    
    return $filter_tags[$index];
  }

  function get_filter_products($tags_array, $options_id, $values_id) {
    $where = '';
    if (count($tags_array) > 0) {
      $where .= " AND products_id IN (".implode(', ', $tags_array).") ";
    }
  
    $tags_array = array();
    $tags_query = xtDBquery("SELECT products_id 
                               FROM ".TABLE_PRODUCTS_TAGS."
                              WHERE options_id = '".(int)$options_id."' 
                                AND values_id = '".(int)$values_id."'
                                    ".$where);
    while ($tags = xtc_db_fetch_array($tags_query, true)) {
      $tags_array[$tags['products_id']] = $tags['products_id'];
    }
  
    return $tags_array;
  }
