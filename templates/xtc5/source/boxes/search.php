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

// set cache id
$cache_id = md5($_SESSION['language']);

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_search.html', $cache_id) || !$cache) {
  $filename = FILENAME_ADVANCED_SEARCH_RESULT;
  if (defined('MODULE_FINDOLOGIC_STATUS') && MODULE_FINDOLOGIC_STATUS == 'True') {
    $filename = FILENAME_FINDOLOGIC;
  }
  $box_smarty->assign('FORM_ACTION', xtc_draw_form('quick_find', xtc_href_link($filename, '', $request_type, false), 'get', 'class="box-search"') . xtc_hide_session_id());
  $box_smarty->assign('INPUT_SEARCH', xtc_draw_input_field('keywords', IMAGE_BUTTON_SEARCH, 'maxlength="30" onfocus="if(this.value==this.defaultValue) this.value=\'\';" onblur="if(this.value==\'\') this.value=this.defaultValue;"'));
  $box_smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_quick_find.gif', IMAGE_BUTTON_SEARCH));
  $box_smarty->assign('FORM_END', '</form>');
  $box_smarty->assign('LINK_ADVANCED', xtc_href_link(FILENAME_ADVANCED_SEARCH));
}

if (!$cache) {
  $box_search = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_search.html');
} else {
  $box_search = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_search.html', $cache_id);
}

$smarty->assign('box_SEARCH',$box_search);
?>