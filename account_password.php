<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(account_password.php,v 1.1 2003/05/19); www.oscommerce.com
   (c) 2003	 nextcommerce (account_password.php,v 1.14 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce - www.xt-commerce.com
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_validate_password.inc.php');
require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
require_once (DIR_FS_INC.'secure_form.inc.php');
require_once (DIR_FS_INC.'clear_checkout_session.inc.php');

require_once (DIR_FS_EXTERNAL.'password_policy/password_policy.php');

if (!isset($_SESSION['customer_id'])) { 
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
} elseif (isset($_SESSION['customer_id']) 
          && $_SESSION['customers_status']['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST
          && GUEST_ACCOUNT_EDIT != 'true'
          )
{ 
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', 'SSL'));
}

// create smarty elements
$smarty = new Smarty();

// clear session
clear_checkout_session();

if (isset ($_POST['action']) && ($_POST['action'] == 'process')) {

  $valid_params = array(
    'password_current',
    'password_new',
    'password_confirmation',
  );

  // prepare variables
  foreach ($_POST as $key => $value) {
    if ((!isset(${$key}) || !is_object(${$key})) && in_array($key , $valid_params)) {
      ${$key} = xtc_db_prepare_input($value);
    }
  }

	$error = false;
	if (strlen($password_current) < 1) {
		$error = true;
		$messageStack->add('account_password', ENTRY_PASSWORD_CURRENT_ERROR);
	}

  $policy = new password_policy();
  if (!$policy->validate($password_new)) {
    $error = true;
    foreach ($policy->get_errors() as $k => $error) {
      $messageStack->add('account_password', $error);
    }
  }
  elseif ($password_new != $password_confirmation) {
    $error = true;
    $messageStack->add('account_password', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
  }

  if (check_secure_form($_POST) === false) {
    $messageStack->add('account_password', ENTRY_TOKEN_ERROR);
    $error = true;
  }

	if ($error === false) {
		$check_customer_query = xtc_db_query("SELECT customers_password 
		                                        FROM ".TABLE_CUSTOMERS." 
		                                       WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
		$check_customer = xtc_db_fetch_array($check_customer_query);

		if (xtc_validate_password($password_current, $check_customer['customers_password'], $_SESSION['customer_id'])) {
		  $_SESSION['customer_time'] = time();
		  
			xtc_db_query("UPDATE ".TABLE_CUSTOMERS." 
			                 SET customers_password = '".xtc_encrypt_password($password_new)."', 
			                     customers_password_time = '".(int)$_SESSION['customer_time']."', 
			                     customers_last_modified = now() 
			               WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
			               
			xtc_db_query("UPDATE ".TABLE_CUSTOMERS_INFO." 
			                 SET customers_info_date_account_last_modified = now() 
			               WHERE customers_info_id = '".(int) $_SESSION['customer_id']."'");
			               
			$messageStack->add_session('account', SUCCESS_PASSWORD_UPDATED, 'success');
			
			xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
		} else {
			$messageStack->add('account_password', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
		}
	}
}

// build breadcrumb
$breadcrumb->add(NAVBAR_TITLE_1_ACCOUNT_PASSWORD, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_ACCOUNT_PASSWORD, xtc_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'));

// include header
require (DIR_WS_INCLUDES.'header.php');

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

if ($messageStack->size('account_password') > 0) {
	$smarty->assign('error_message', $messageStack->output('account_password'));
}

$smarty->assign('FORM_ACTION', xtc_draw_form('account_password', xtc_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL')).xtc_draw_hidden_field('action', 'process').secure_form('account_password'));
$smarty->assign('INPUT_ACTUAL', xtc_draw_password_fieldNote(array('name' => 'password_current', 'text' => (xtc_not_null(ENTRY_PASSWORD_CURRENT_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_CURRENT_TEXT.'</span>' : ''))));
$smarty->assign('INPUT_NEW', xtc_draw_password_fieldNote(array('name' => 'password_new', 'text' => (xtc_not_null(ENTRY_PASSWORD_NEW_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_NEW_TEXT.'</span>' : ''))));
$smarty->assign('INPUT_CONFIRM', xtc_draw_password_fieldNote(array('name' => 'password_confirmation', 'text' => (xtc_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_CONFIRMATION_TEXT.'</span>' : ''))));
$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('BUTTON_SUBMIT_SAVE', xtc_image_submit('button_save.gif', IMAGE_BUTTON_SAVE));
$smarty->assign('BUTTON_SUBMIT_UPDATE', xtc_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE));
$smarty->assign('FORM_END', '</form>');

$smarty->assign('language', $_SESSION['language']);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_password.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');