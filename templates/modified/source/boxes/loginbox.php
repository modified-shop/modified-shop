<?php
  /* -----------------------------------------------------------------------------------------
   $Id: loginbox.php 139 2012-12-02 18:35:41Z web28 $

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercebased on original files from OSCommerce CVS 2.2 2002/08/28 02:14:35 www.oscommerce.com 
   (c) 2003 nextcommerce (loginbox.php,v 1.10 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Loginbox V1.0          Aubrey Kilian <aubrey@mycon.co.za>

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (!isset($_SESSION['customer_id'])) {
    require_once (DIR_FS_INC.'xtc_image_submit.inc.php');
    require_once (DIR_FS_INC.'xtc_draw_password_field.inc.php');
    $box_smarty = new smarty;
    $box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
    $box_smarty->assign('FORM_ACTION', '<form id="loginbox" method="post" action="'.xtc_href_link(FILENAME_LOGIN, 'action=process', 'SSL').'">');
    $box_smarty->assign('FIELD_EMAIL', xtc_draw_input_field('email_address', '', 'maxlength="50" class="boxfield_lang"'));
    $box_smarty->assign('FIELD_PWD', xtc_draw_password_field('password', '', 'maxlength="30" class="boxfield_kurz"'));
    $box_smarty->assign('BUTTON', xtc_image_submit('button_login_small.gif', IMAGE_BUTTON_LOGIN,'class="box_button"'));
    $box_smarty->assign('LINK_LOST_PASSWORD', xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, '', 'SSL'));
    $box_smarty->assign('FORM_END', '</form>');
    $box_smarty->assign('BOX_CONTENT', '');
    $box_smarty->caching = 0;
    $box_smarty->assign('language', $_SESSION['language']);
    $box_loginbox = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_login.html');
    $smarty->assign('box_LOGIN', $box_loginbox);
  }
?>