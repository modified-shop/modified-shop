<?php


/* -----------------------------------------------------------------------------------------
   $Id: search.php 1262 2005-09-30 10:00:32Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(search.php,v 1.22 2003/02/10); www.oscommerce.com 
   (c) 2003	 nextcommerce (search.php,v 1.9 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
$box_smarty = new smarty;
$box_smarty->assign('tpl_path', 'templates/' . CURRENT_TEMPLATE . '/');
$box_content = '';

require_once (DIR_FS_INC . 'xtc_image_submit.inc.php');
require_once (DIR_FS_INC . 'xtc_hide_session_id.inc.php');

// BOF - GTB - 2010-09-20 - correct the Formular in dependences of the request type SSL / NONSSL
$box_smarty->assign('FORM_ACTION', xtc_draw_form('quick_find', xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get') . xtc_hide_session_id());
//$box_smarty->assign('FORM_ACTION', xtc_draw_form('quick_find', xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get') . xtc_hide_session_id());
// BOF - GTB - 2010-09-20 - correct the Formular in dependences of the request type SSL / NONSSL
//BOF - Dokuman - 14.08.2009 - Put dynamic "search"-text into box
//$box_smarty->assign('INPUT_SEARCH', xtc_draw_input_field('keywords', '', 'size="20" maxlength="30"'));
//BOF - web28 - 2010-04-11 - change input html size to css width
//$box_smarty->assign('INPUT_SEARCH', xtc_draw_input_field('keywords', IMAGE_BUTTON_SEARCH, 'size="20" maxlength="30" onfocus="if(this.value==this.defaultValue) this.value=\'\';" onblur="if(this.value==\'\') this.value=this.defaultValue;"'));
$box_smarty->assign('INPUT_SEARCH', xtc_draw_input_field('keywords', IMAGE_BUTTON_SEARCH, 'style="width:160px;" maxlength="30" onfocus="if(this.value==this.defaultValue) this.value=\'\';" onblur="if(this.value==\'\') this.value=this.defaultValue;"'));
//EOF - web28 - 2010-04-11 - change input html size to css width
//EOF - Dokuman - 14.08.2009 - Put dynamic "search"-text into box
$box_smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_quick_find.gif', IMAGE_BUTTON_SEARCH));
$box_smarty->assign('FORM_END', '</form>');
$box_smarty->assign('LINK_ADVANCED', xtc_href_link(FILENAME_ADVANCED_SEARCH));
$box_smarty->assign('BOX_CONTENT', $box_content);

$box_smarty->assign('language', $_SESSION['language']);
$box_smarty->caching = 0;
$box_search = $box_smarty->fetch(CURRENT_TEMPLATE . '/boxes/box_search.html');

$smarty->assign('box_SEARCH', $box_search);
?>
