<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

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
   
  function xtc_get_categories($categories_array = '', $parent_id = '0', $indent = '', $space = '&nbsp;&nbsp;') {

    $parent_id = xtc_db_prepare_input($parent_id);

    if (!is_array($categories_array)) $categories_array = array();

    $cat_cond_c = defined('CATEGORIES_CONDITIONS_C') ? CATEGORIES_CONDITIONS_C : '';

    $categories_query = "SELECT c.categories_id, cd.categories_name
                           FROM " . TABLE_CATEGORIES . " c,
                                " . TABLE_CATEGORIES_DESCRIPTION . " cd
                          WHERE parent_id = " . (int)$parent_id . "
                            AND c.categories_id = cd.categories_id
                            AND c.categories_status != 0
                            AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                            " . $cat_cond_c . "
                       ORDER BY sort_order, cd.categories_name";

    $categories_query  = xtDBquery($categories_query);

    while ($categories = xtc_db_fetch_array($categories_query,true)) {

      $categories_array[] = array(
          'id' => $categories['categories_id'],
          'text' => $indent . $categories['categories_name']
        );

      if ($categories['categories_id'] != $parent_id) {
        $categories_array = xtc_get_categories($categories_array, $categories['categories_id'], $indent . $space);
      }
    }

    return $categories_array;
  }
 ?>