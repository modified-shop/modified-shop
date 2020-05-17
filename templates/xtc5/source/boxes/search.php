<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(search.php,v 1.22 2003/02/10); www.oscommerce.com 
   (c) 2003	 nextcommerce (search.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce

   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

if (defined('SEARCH_AC_CATEGORIES')
    && SEARCH_AC_CATEGORIES == 'true'
    )
{
  $categories_array = array(array(
    'id' => '',
    'text' => TEXT_AC_ALL_CATEGORIES,
  ));
  $categories_query = xtDBquery("SELECT c.categories_id,
                                        cd.categories_name
                                   FROM ".TABLE_CATEGORIES." c
                                   JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                        ON c.categories_id = cd.categories_id
                                           AND cd.language_id='".(int)$_SESSION['languages_id']."'
                                           AND trim(cd.categories_name) != ''
                                  WHERE c.categories_status = '1'
                                    AND c.parent_id = '0'
                                        ".CATEGORIES_CONDITIONS_C."
                               ORDER BY c.sort_order, cd.categories_name");
  if (xtc_db_num_rows($categories_query, true) > 0) {
    while ($categories = xtc_db_fetch_array($categories_query, true)) {
      $categories_array[] = array(
        'id' => $categories['categories_id'],
        'text' => $categories['categories_name'],
      );
    }
  }
  $box_smarty->assign('CATEGORIES', xtc_draw_pull_down_menu('categories_id', $categories_array, isset($_GET['categories_id']) ? (int)$_GET['categories_id'] : '', 'id="cat_search"').xtc_draw_hidden_field('inc_subcat', '1'));
}
$box_smarty->assign('FORM_ACTION', xtc_draw_form('quick_find', xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get', 'class="box-search"') . xtc_hide_session_id());
$box_smarty->assign('INPUT_SEARCH', xtc_draw_input_field('keywords', '', 'placeholder="'.IMAGE_BUTTON_SEARCH.'" id="inputString" maxlength="30" autocomplete="off" '.((SEARCH_AC_STATUS == 'true') ? 'onkeyup="ac_lookup(this.value);" ' : '')));
$box_smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_quick_find.gif', IMAGE_BUTTON_SEARCH,''));
$box_smarty->assign('FORM_END', '</form>');
$box_smarty->assign('LINK_ADVANCED', xtc_href_link(FILENAME_ADVANCED_SEARCH));

$box_search = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_search.html');

$smarty->assign('box_SEARCH',$box_search);
?>