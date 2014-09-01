<?php
/* -----------------------------------------------------------------------------------------
   $Id: account_delete.php 4220 2013-01-11 09:57:28Z gtb-modified $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(account_password.php,v 1.1 2003/05/19); www.oscommerce.com 
   (c) 2003 nextcommerce (account_password.php,v 1.14 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
//BOF - 2009-08-25 - Require password to disable account
require_once (DIR_FS_INC.'xtc_validate_password.inc.php');
//EOF - 2009-08-25 - Require password to disable account

// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

if (!isset ($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}
//BOF - DokuMan - 2010-03-16 - do not delete the admin user (ID=1)
if ($_SESSION['customer_id'] == 1) {    
  //BOF - web28.de - FIX redirect to NONSSL
  //xtc_redirect(xtc_href_link(FILENAME_DEFAULT, ''));
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT),'NONSSL');
  //EOF - web28.de - FIX redirect to NONSSL
}
//EOF - DokuMan - 2010-03-16 - do not delete the admin user (ID=1)

if (isset ($_POST['action']) && ($_POST['action'] == 'process')) {
//BOF - 2009-08-25 - Require password to disable account
    $password = xtc_db_prepare_input($_POST['password']);
    $check_customer_query = xtc_db_query("select customers_password from ".TABLE_CUSTOMERS." where customers_id = '".(int) $_SESSION['customer_id']."'");
    $check_customer = xtc_db_fetch_array($check_customer_query);

    if (!xtc_validate_password($password, $check_customer['customers_password'])) {
      $messageStack->add('account_delete', TEXT_LOGIN_ERROR);
    } else {
//EOF - 2009-08-25 - Require password to disable account
    
  // delete account and logout customer  
  xtc_db_query("delete from ".TABLE_ADDRESS_BOOK." where customers_id = '".(int) $_SESSION['customer_id']."'");
  xtc_db_query("delete from ".TABLE_CUSTOMERS." where customers_id = '".(int) $_SESSION['customer_id']."'");
  xtc_db_query("delete from ".TABLE_CUSTOMERS_INFO." where customers_info_id = '".(int) $_SESSION['customer_id']."'");
  
  xtc_session_destroy();

  unset ($_SESSION['customer_id']);
  unset ($_SESSION['customer_default_address_id']);
  unset ($_SESSION['customer_first_name']);
  unset ($_SESSION['customer_country_id']);
  unset ($_SESSION['customer_zone_id']);
  unset ($_SESSION['comments']);
  unset ($_SESSION['user_info']);
  unset ($_SESSION['customers_status']);
  unset ($_SESSION['selected_box']);
  unset ($_SESSION['navigation']);
  unset ($_SESSION['shipping']);
  unset ($_SESSION['payment']);
  unset ($_SESSION['ccard']);
  // GV Code Start
  unset ($_SESSION['gv_id']);
  unset ($_SESSION['cc_id']);
  // GV Code End
  $_SESSION['cart']->reset();

  $smarty->assign('BUTTON_CONTINUE', '<a href="'.xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL').'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
//BOF - 2009-08-25 - Require password to disable account
  }
//EOF - 2009-08-25 - Require password to disable account
}

$breadcrumb->add(NAVBAR_TITLE_1_ACCOUNT_DELETE, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_ACCOUNT_DELETE, xtc_href_link(FILENAME_ACCOUNT_DELETE, '', 'SSL'));

require (DIR_WS_INCLUDES.'header.php');

if ($messageStack->size('account_delete') > 0) {
  $smarty->assign('error', $messageStack->output('account_delete'));
}
$smarty->assign('FORM_ACTION', xtc_draw_form('account_delete', xtc_href_link(FILENAME_ACCOUNT_DELETE, '', 'SSL'), 'post'). xtc_draw_hidden_field('action', 'process'));
$smarty->assign('INPUT_PASSWORD', xtc_draw_password_field('password'));

$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('FORM_END', '</form>');

$smarty->assign('language', $_SESSION['language']);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_delete.html');

$smarty->assign('main_content', $main_content);
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>
