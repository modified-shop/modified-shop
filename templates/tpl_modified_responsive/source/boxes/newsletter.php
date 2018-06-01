<?php
/* -----------------------------------------------------------------------------------------
   $Id: newsletter.php 4203 2013-01-10 20:36:14Z Tomcraft1980 $

   XTC-NEWSLETTER_RECIPIENTS RC1 - Contribution for XT-Commerce http://www.xt-commerce.com
   by Matthias Hinsche http://www.gamesempire.de

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce www.oscommerce.com 
   (c) 2003	 nextcommerce www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// set cache id
$cache_id = md5($_SESSION['language']);

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_newsletter.html', $cache_id) || !$cache) {
  $box_smarty->assign('FORM_ACTION', xtc_draw_form('sign_in', xtc_href_link(FILENAME_NEWSLETTER, '', $request_type)));
  $box_smarty->assign('FIELD_EMAIL',xtc_draw_input_field('email', '', 'maxlength="50"'));
  $box_smarty->assign('BUTTON',xtc_image_submit('button_login_newsletter.png', IMAGE_BUTTON_LOGIN));
  $box_smarty->assign('FORM_END','</form>');
}

if (!$cache) {
  $box_newsletter = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_newsletter.html');
} else {
  $box_newsletter = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_newsletter.html', $cache_id);
}

$smarty->assign('box_NEWSLETTER',$box_newsletter);
?>