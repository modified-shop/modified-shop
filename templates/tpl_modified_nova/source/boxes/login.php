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

  if (!isset($_SESSION['customer_id'])) {
    // include needed functions
    require_once (DIR_FS_INC.'xtc_draw_password_field.inc.php');
  
    $box_smarty->assign('FORM_ACTION', xtc_draw_form('loginbox', xtc_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'class="box-login"'));
    $box_smarty->assign('FIELD_EMAIL', xtc_draw_input_field('email_address', '', 'maxlength="50"'));
    $box_smarty->assign('FIELD_PWD', xtc_draw_password_field('password'));
    $box_smarty->assign('BUTTON', xtc_image_submit('button_login_small.gif', IMAGE_BUTTON_LOGIN));
    $box_smarty->assign('LINK_LOST_PASSWORD', xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, '', 'SSL'));
    $box_smarty->assign('LINK_CREATE_ACCOUNT', xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
    $box_smarty->assign('LINK_CREATE_GUEST_ACCOUNT', xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL'));
    $box_smarty->assign('FORM_END', '</form>');
  } else {
    $box_smarty->assign('IS_CUSTOMER', ($_SESSION['customers_status']['customers_status_id'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST) ? 1 : 0);
    $box_smarty->assign('LINK_ACCOUNT', xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    $box_smarty->assign('LINK_EDIT', xtc_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));
    $box_smarty->assign('LINK_ADDRESS', xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    $box_smarty->assign('LINK_PASSWORD', xtc_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'));
    $box_smarty->assign('LINK_ORDERS', xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
    if (isset($_SESSION['customer_id']) && $_SESSION['customer_id'] != '1') {
      $box_smarty->assign('LINK_DELETE', xtc_href_link(FILENAME_ACCOUNT_DELETE, '', 'SSL'));
    }
    if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
      $box_smarty->assign('LINK_EXPRESS', xtc_href_link(FILENAME_ACCOUNT_CHECKOUT_EXPRESS, '', 'SSL'));
    }
    if (defined('MODULE_NEWSLETTER_STATUS') && MODULE_NEWSLETTER_STATUS == 'true') {
      $box_smarty->assign('LINK_NEWSLETTER', xtc_href_link(FILENAME_NEWSLETTER, '', 'SSL'));
    }
    $box_smarty->assign('LINK_LOGOFF', xtc_href_link(FILENAME_LOGOFF, '', 'SSL'));
  }

  $box_smarty->caching = 0;
  $box_login = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_login.html');
  
  $smarty->assign('box_LOGIN', $box_login);
