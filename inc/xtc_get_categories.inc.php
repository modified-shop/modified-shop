<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_get_categories.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_categories.inc.php 1009 2005-07-11)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_get_categories($categories_array = '', $parent_id = '0', $indent = '', $indent_original = '') {
    //BOF - Dokuman - 2011-06-01 - save indention for recursive usage, otherwise it won't be inherited - thx to RossiRat
    if (!$indent_original) {
      $indent_original = $indent;
    }
    //EOF - Dokuman - 2011-06-01 - save indention for recursive usage, otherwise it won't be inherited - thx to RossiRat

    $parent_id = xtc_db_prepare_input($parent_id);

    if (!is_array($categories_array)) $categories_array = array();
    $group_check = '';
    //BOF - DokuMan - 2011-05-31 - added group check permission, thanks to ThYpHoOn
    /*
    $categories_query = "select
                                      c.categories_id,
                                      cd.categories_name
                                      from " . TABLE_CATEGORIES . " c,
                                       " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                       where parent_id = '" . xtc_db_input($parent_id) . "'
                                       and c.categories_id = cd.categories_id
                                       and c.categories_status != 0
                                       and cd.language_id = '" . $_SESSION['languages_id'] . "'
                                       order by sort_order, cd.categories_name";
    */
    if (GROUP_CHECK == 'true') {
      $group_check = "and c.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
    }
    $categories_query = "select
                              c.categories_id,
                              cd.categories_name
                              from " . TABLE_CATEGORIES . " c,
                                   " . TABLE_CATEGORIES_DESCRIPTION . " cd
                              where parent_id = '" . xtc_db_input($parent_id) . "'
                              and c.categories_id = cd.categories_id
                              and c.categories_status != 0
                              and cd.language_id = '" . (int) $_SESSION['languages_id'] . "'
                              ".$group_check."
                              order by sort_order, cd.categories_name";
    //EOF - DokuMan - 2011-05-31 - added group check permission, thanks to ThYpHoOn

    $categories_query  = xtDBquery($categories_query);

    while ($categories = xtc_db_fetch_array($categories_query,true)) {
      $categories_array[] = array('id' => $categories['categories_id'],
                                  'text' => $indent . $categories['categories_name']);

      if ($categories['categories_id'] != $parent_id) {
        //BOF - Dokuman - 2011-06-01 - save indention for recursive usage, otherwise it won't be inherited - thx to RossiRat
        //$categories_array = xtc_get_categories($categories_array, $categories['categories_id'], $indent . '&nbsp;&nbsp;');
        $categories_array = xtc_get_categories($categories_array, $categories['categories_id'], $indent . $indent_original, $indent_original);
        //BOF - Dokuman - 2011-06-01 - save indention for recursive usage, otherwise it won't be inherited - thx to RossiRat
      }
    }

    return $categories_array;
  }
?>