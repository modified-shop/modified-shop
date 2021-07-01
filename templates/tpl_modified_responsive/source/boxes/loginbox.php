<?php
/* -----------------------------------------------------------------------------------------
   $Id: loginbox.php 11146 2018-05-29 15:49:08Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(search.php,v 1.22 2003/02/10); www.oscommerce.com 
   (c) 2003	 nextcommerce (search.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce - TPC Loginbox V1 - Aubrey Kilian <aubrey@mycon.co.za>

   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

if (!isset($_SESSION['customer_id'])) {
  // include needed functions
  require_once (DIR_FS_INC.'xtc_draw_password_field.inc.php');
  
  $box_smarty->assign('FORM_ACTION', xtc_draw_form('loginbox', xtc_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'class="box-login"'));
  $box_smarty->assign('FIELD_EMAIL', xtc_draw_input_field('email_address', '', 'maxlength="50"'));
  $box_smarty->assign('FIELD_PWD', xtc_draw_password_field('password'));
  $box_smarty->assign('BUTTON', xtc_image_submit('button_login_small.gif', IMAGE_BUTTON_LOGIN));
  $box_smarty->assign('LINK_LOST_PASSWORD', xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, '', 'SSL'));
  $box_smarty->assign('FORM_END', '</form>');

  $box_smarty->caching = 0;
  $box_loginbox = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_login.html');
  $smarty->assign('box_LOGIN', $box_loginbox);
}
?>