<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce www.oscommerce.com 
   (c) 2003	 nextcommerce www.nextcommerce.org
   (c) 2003 xt:Commerce www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_categories($categories_array = '', $parent_id = '0', $include_sub = true, $indent = '', $space = '&nbsp;&nbsp;') {

    if (!is_array($categories_array)) $categories_array = array();

    $conditions = '';
    if (!defined('RUN_MODE_ADMIN')) {
      $conditions = CATEGORIES_CONDITIONS_C;
    }

    $categories_query = xtDBquery("SELECT c.categories_id, 
                                          cd.categories_name
                                     FROM " . TABLE_CATEGORIES . " c
                                     JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                          ON c.categories_id = cd.categories_id
                                             AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                             AND trim(cd.categories_name) != ''
                                    WHERE c.categories_status = 1
                                      AND c.parent_id = " . (int)$parent_id . "
                                          " . $conditions . "
                                 ORDER BY c.sort_order, cd.categories_name");
    
    if (xtc_db_num_rows($categories_query, true) > 0) {
      while ($categories = xtc_db_fetch_array($categories_query, true)) {
        $categories_array[] = array(
          'id' => $categories['categories_id'],
          'text' => $indent . $categories['categories_name']
        );

        if ($include_sub === true && $categories['categories_id'] != $parent_id) {
          $categories_array = xtc_get_categories($categories_array, $categories['categories_id'], $include_sub, $indent . $space);
        }
      }
    }
    
    return $categories_array;
  }
?>