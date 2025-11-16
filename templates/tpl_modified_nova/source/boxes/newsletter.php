<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include smarty
  include(DIR_FS_BOXES_INC . 'smarty_default.php');

  // set cache id
  $cache_id = md5('lID:'.$_SESSION['language']);

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_newsletter.html', $cache_id) || !$cache) {
    $box_smarty->assign('FORM_ACTION', xtc_draw_form('sign_in', xtc_href_link(FILENAME_NEWSLETTER, '', $request_type)));
    $box_smarty->assign('FIELD_EMAIL',xtc_draw_input_field('email', '', 'id="input_sign_in" autocomplete="email"'));
    $box_smarty->assign('BUTTON',xtc_image_submit('button_login_newsletter.png', IMAGE_BUTTON_LOGIN));
    $box_smarty->assign('FORM_END','</form>');
  }

  $box_newsletter = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_newsletter.html', $cache_id);

  $smarty->assign('box_NEWSLETTER',$box_newsletter);
