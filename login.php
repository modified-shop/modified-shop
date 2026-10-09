<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(login.php,v 1.79 2003/05/19); www.oscommerce.com 
   (c) 2003 nextcommerce (login.php,v 1.13 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   guest account idea by Ingo T. <xIngox@web.de>
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

defined('MODULE_CAPTCHA_LOGIN_NUM') OR define('MODULE_CAPTCHA_LOGIN_NUM', 2);
defined('MODULE_CAPTCHA_CODE_LENGTH') OR define('MODULE_CAPTCHA_CODE_LENGTH', 6);

if (isset ($_SESSION['customer_id'])) {
	xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
if ($session_started == false) {
  xtc_redirect(xtc_href_link(FILENAME_COOKIE_USAGE, xtc_get_all_get_params(array('return_to')).'return_to='.basename($PHP_SELF)));
}

// include needed functions
require_once (DIR_FS_INC.'xtc_validate_password.inc.php');
require_once (DIR_FS_INC.'xtc_write_user_info.inc.php');
require_once (DIR_FS_INC.'write_customers_session.inc.php');

// include needed classes
require_once (DIR_WS_CLASSES.'modified_captcha.php');

// create smarty elements
$smarty = new Smarty();

$mod_captcha = $_mod_captcha_class::getInstance();

$account_options = ACCOUNT_OPTIONS;
$products = $_SESSION['cart']->get_products();
for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
  if (preg_match('/^GIFT/', addslashes($products[$i]['model']))) {
    $account_options = 'account';
    break;
  }
}

if (!isset($_SESSION['customers_login_tries'])) {
  $_SESSION['customers_login_tries'] = 0;
}

if (isset($_GET['action']) 
    && $_GET['action'] == 'process'
    && $_SERVER['REQUEST_METHOD'] == 'POST'
    )
{
	$email_address = ((isset($_POST['email_address'])) ? xtc_db_prepare_input($_POST['email_address']) : '');
	$password = ((isset($_POST['password'])) ? xtc_db_prepare_input($_POST['password']) : '');
  $captcha_validation = $mod_captcha->validate((isset($_POST['vvcode'])) ? $_POST['vvcode'] : '');
  
  // brute force
  $check_login_query = xtc_db_query("SELECT customers_login_tries
                                       FROM ".TABLE_CUSTOMERS_LOGIN."
                                      WHERE (customers_email_address = '".xtc_db_input($email_address)."'
                                             OR customers_ip = '".xtc_db_input($_SESSION['tracking']['ip'])."')
                                        AND customers_email_address != ''");
  if (xtc_db_num_rows($check_login_query) > 0) {
    while ($check_login = xtc_db_fetch_array($check_login_query)) {
      if ($check_login['customers_login_tries'] > $_SESSION['customers_login_tries']) {
        $_SESSION['customers_login_tries'] = $check_login['customers_login_tries'];
      }
    }
    // update login tries
    xtc_db_query("UPDATE ".TABLE_CUSTOMERS_LOGIN." 
                     SET customers_login_tries = '".($_SESSION['customers_login_tries'] + 1)."',
                         last_modified = NOW()
                   WHERE (customers_email_address = '".xtc_db_input($email_address)."'
                          OR customers_ip = '".xtc_db_input($_SESSION['tracking']['ip'])."')");
  } else {
    $sql_data_array = array(
      'customers_ip' => $_SESSION['tracking']['ip'],
      'customers_email_address' => $email_address,
      'customers_login_tries' => ($_SESSION['customers_login_tries'] + 1),
      'date_added' => 'now()',
    );
    xtc_db_perform(TABLE_CUSTOMERS_LOGIN, $sql_data_array);
  }

  // captcha
  $captcha_error = false;	
  if ($_SESSION['customers_login_tries'] >= MODULE_CAPTCHA_LOGIN_NUM) {
    $captcha_error = (($captcha_validation !== true) ? true : false);
  }
    
  // increment login tries
  $_SESSION['customers_login_tries'] ++;

	$check_customer = null;

	// check if email exists
	$check_customer_query = xtc_db_query("SELECT *
	                                        FROM ".TABLE_CUSTOMERS." 
	                                       WHERE customers_email_address = '".xtc_db_input($email_address)."' 
	                                         AND account_type = '0'");

	if (xtc_db_num_rows($check_customer_query) < 1) {
		$messageStack->add('login', TEXT_LOGIN_ERROR);
		foreach(auto_include(DIR_FS_CATALOG.'includes/extra/login/login_failed/','php') as $file) require ($file);
		if (isset($_POST['login']) && $_POST['login'] == 'admin') {
		  xtc_redirect(xtc_href_link('login_admin.php', '', 'SSL'));
		}
	} else {
		$check_customer = xtc_db_fetch_array($check_customer_query);
    		
		$error = false;
		$login_messages = $messageStack->size('login');
		foreach(auto_include(DIR_FS_CATALOG.'includes/extra/login/login_check_data/','php') as $file) require ($file);

		// Check that password is good
		if (!$error && xtc_validate_password($password, $check_customer['customers_password'], $check_customer['customers_id']) !== true) {
			$error = true;
		}
		if ($error) {
			// keep a message a hook has set
			if ($messageStack->size('login') == $login_messages) {
				$messageStack->add('login', TEXT_LOGIN_ERROR);
			}
			foreach(auto_include(DIR_FS_CATALOG.'includes/extra/login/login_failed/','php') as $file) require ($file);
      if (isset($_POST['login']) && $_POST['login'] == 'admin') {
        xtc_redirect(xtc_href_link('login_admin.php', '', 'SSL'));
      }
		} elseif ($captcha_error === false
		          && defined('ACCOUNT_EMAIL_VERIFY')
		          && ACCOUNT_EMAIL_VERIFY == 'required'
		          && SEND_EMAILS == 'true'
		          && empty($check_customer['customers_email_verified'])
		          && !empty($check_customer['customers_email_verify_key'])
		          )
		{
			// the password is right, the login waits for the confirmation of the email address
			// a later password change withdraws this permission
			$_SESSION['email_verify_pending'] = (int)$check_customer['customers_id'];
			$_SESSION['email_verify_pending_time'] = (int)$check_customer['customers_password_time'];
		} elseif ($captcha_error === false) {		
			foreach(auto_include(DIR_FS_CATALOG.'includes/extra/login/login_before_session/','php') as $file) require ($file);

			require (DIR_WS_INCLUDES.'login_customer.php');
		} else {
			// right password, but the captcha failed
			foreach(auto_include(DIR_FS_CATALOG.'includes/extra/login/login_failed/','php') as $file) require ($file);
		}
	}
}

if (isset($captcha_error) && $captcha_error === true) {	
  $messageStack->add('login', TEXT_WRONG_CODE);
}

if (isset($_GET['action']) && $_GET['action'] === 'relogin') {
  $messageStack->add('login', TEXT_RELOGIN_NEEDED);
}

// a blocked login waits for the confirmation, offer a new link or a corrected address
if (isset($_SESSION['email_verify_pending'])
    && defined('ACCOUNT_EMAIL_VERIFY')
    && ACCOUNT_EMAIL_VERIFY == 'required'
    && SEND_EMAILS == 'true'
    )
{
  $pending_query = xtc_db_query("SELECT customers_id
                                   FROM ".TABLE_CUSTOMERS."
                                  WHERE customers_id = '".(int)$_SESSION['email_verify_pending']."'
                                    AND account_type = '0'
                                    AND customers_email_verified IS NULL
                                    AND customers_email_verify_key != ''
                                    AND customers_password_time = '".(isset($_SESSION['email_verify_pending_time']) ? (int)$_SESSION['email_verify_pending_time'] : -1)."'");
  if (xtc_db_num_rows($pending_query) == 1) {
    require_once (DIR_FS_INC.'secure_form.inc.php');

    $verify_action = xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL');
    $smarty->assign('FORM_EMAIL_VERIFY_RESEND', xtc_draw_form('email_verify_resend', $verify_action, 'post').xtc_draw_hidden_field('action', 'verify_resend').secure_form('email_verify'));
    $smarty->assign('BUTTON_EMAIL_VERIFY_RESEND', xtc_image_submit('button_send.gif', IMAGE_BUTTON_SEND));
    $smarty->assign('FORM_EMAIL_VERIFY_CORRECT', xtc_draw_form('email_verify_correct', $verify_action, 'post').xtc_draw_hidden_field('action', 'verify_correct').secure_form('email_verify'));
    $smarty->assign('INPUT_EMAIL_VERIFY_NEW', xtc_draw_input_field('email_address', '', 'autocomplete="email"', 'email', false));
    $smarty->assign('INPUT_EMAIL_VERIFY_CONFIRM', xtc_draw_input_field('confirm_email_address', '', 'autocomplete="email"', 'email', false));
    $smarty->assign('BUTTON_EMAIL_VERIFY_CORRECT', xtc_image_submit('button_save.gif', IMAGE_BUTTON_SAVE));
    $messageStack->add('login', ERROR_EMAIL_VERIFY_PENDING);
  } else {
    unset($_SESSION['email_verify_pending'], $_SESSION['email_verify_pending_time']);
  }
}

// build breadcrumb
$breadcrumb->add(NAVBAR_TITLE_LOGIN, xtc_href_link(FILENAME_LOGIN, '', 'SSL'));

// include header
require (DIR_WS_INCLUDES . 'header.php');

// include boxes
$display_mode = 'login';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

if ($messageStack->size('login') > 0) {
	$smarty->assign('error_message', $messageStack->output('login'));
}
if ($messageStack->size('login', 'success') > 0) {
	$smarty->assign('success_message', $messageStack->output('login', 'success'));
}

$smarty->assign('account_option', $account_options);
$smarty->assign('BUTTON_NEW_ACCOUNT', '<a href="'.xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
$smarty->assign('BUTTON_LOGIN', xtc_image_submit('button_login.gif', IMAGE_BUTTON_LOGIN));
$smarty->assign('BUTTON_GUEST', '<a href="'.xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
$smarty->assign('FORM_ACTION', xtc_draw_form('login', xtc_href_link(FILENAME_LOGIN, xtc_get_all_get_params(array('action')).'action=process', 'SSL')));
$smarty->assign('INPUT_MAIL', xtc_draw_input_field('email_address', '', 'autocomplete="email"'));
$smarty->assign('INPUT_PASSWORD', xtc_draw_password_field('password', '', 'autocomplete="current-password"'));
$smarty->assign('LINK_LOST_PASSWORD', xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, '', 'SSL'));
$smarty->assign('FORM_END', '</form>');

// captcha
if ($_SESSION['customers_login_tries'] >= MODULE_CAPTCHA_LOGIN_NUM) {
  $smarty->assign('VVIMG', $mod_captcha->get_image_code());
  $smarty->assign('INPUT_CODE', $mod_captcha->get_input_code());
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/login/login_smarty_data/','php') as $file) require ($file);

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/login.html');
$smarty->assign('main_content', $main_content);

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');