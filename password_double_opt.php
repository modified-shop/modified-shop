<?php
/* -----------------------------------------------------------------------------------------
  $Id: password_double_opt.php 3072 2012-06-18 15:01:13Z hhacker $

  modified eCommerce Shopsoftware
  http://www.modified-shop.org

  Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------------------------------------
  based on:
  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
  (c) 2002-2003 osCommerce www.oscommerce.com
  (c) 2003 nextcommerce www.nextcommerce.org
  (c) 2006 XT-Commerce (password_double_opt.php,v 1.0)

  Released under the GNU General Public License
  ---------------------------------------------------------------------------------------*/

define('VALID_REQUEST_TIME', 60*60);

require ('includes/application_top.php');

if (isset ($_SESSION['customer_id'])) {
	xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

// captcha
$use_captcha = array('password');
if (defined('MODULE_CAPTCHA_ACTIVE')) {
  $use_captcha = explode(',', MODULE_CAPTCHA_ACTIVE);
}
defined('MODULE_CAPTCHA_CODE_LENGTH') or define('MODULE_CAPTCHA_CODE_LENGTH', 6);

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');

// include needed classes
require_once (DIR_FS_EXTERNAL.'password_policy/password_policy.php');

// default case
$case = 'double_opt';

if (isset ($_GET['action']) && ($_GET['action'] == 'first_opt_in') && isset($_POST) && count($_POST) > 0) {
  $check_customer_query = xtc_db_query("SELECT customers_email_address, 
                                               customers_id 
                                          FROM ".TABLE_CUSTOMERS." 
                                         WHERE customers_email_address = '".xtc_db_input($_POST['email'])."' 
                                           AND account_type != '1'");
  $check_customer = xtc_db_fetch_array($check_customer_query);

  $vlcode = xtc_random_charcode(32);
  $link = xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, 'action=verified&customers_id='.$check_customer['customers_id'].'&key='.$vlcode, 'SSL');

  // assign language to template for caching
  $smarty->assign('language', $_SESSION['language']);
  $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
  $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

  // assign vars
  $smarty->assign('EMAIL', $check_customer['customers_email_address']);
  $smarty->assign('LINK', $link);
  $smarty->assign('VALID_REQUEST_TIME', (VALID_REQUEST_TIME / 60));
  
  // dont allow cache
  $smarty->caching = false;
  $smarty->assign('language', $_SESSION['language']);

  // create mails
  $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/new_password_mail.html');
  $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/new_password_mail.txt');

  if (!in_array('password', $use_captcha) || (isset($_POST['vvcode']) && isset($_SESSION['vvcode']) && strtoupper($_POST['vvcode']) == strtoupper($_SESSION['vvcode']))) {
    if (!xtc_db_num_rows($check_customer_query)) {
      $case = 'wrong_mail';
      if (!in_array('password', $use_captcha)) {
        $messageStack->add('password_double_opt_in', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
      } else {
        $messageStack->add('password_double_opt_in', TEXT_EMAIL_ERROR);
      }
    } else {
      $case = 'first_opt_in';
      xtc_db_query("UPDATE ".TABLE_CUSTOMERS." 
                       SET password_request_key = '".xtc_db_input($vlcode)."',
                           password_request_time = now()
                     WHERE customers_id = '".$check_customer['customers_id']."'");
      
      // send email
      xtc_php_mail(EMAIL_SUPPORT_ADDRESS, 
                   EMAIL_SUPPORT_NAME, 
                   $check_customer['customers_email_address'], 
                   '', 
                   '', 
                   EMAIL_SUPPORT_REPLY_ADDRESS, 
                   EMAIL_SUPPORT_REPLY_ADDRESS_NAME, 
                   '', 
                   '', 
                   TEXT_EMAIL_PASSWORD_FORGOTTEN, 
                   $html_mail, 
                   $txt_mail);
    }
  } else {
    $case = 'code_error';
    $messageStack->add('password_double_opt_in', TEXT_CODE_ERROR);
  }
}

// Verification
if (isset ($_GET['action']) && $_GET['action'] == 'verified' && isset($_GET['key']) && $_GET['key'] != '') {
  $case = 'second_opt_in';

  $valid_params = array(
    'customers_id',
    'key',
  );
      
  // prepare variables
  foreach ($_GET as $gkey => $value) {
    if (!is_object(${$gkey}) && in_array($gkey , $valid_params)) {
      ${$gkey} = xtc_db_prepare_input($value);
    }
  }
  
  $check_customer_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_CUSTOMERS." 
                                         WHERE customers_id = '".(int)$customers_id."' 
                                           AND password_request_key = '".xtc_db_input($key)."'");
  $check_customer = xtc_db_fetch_array($check_customer_query);
  if (!xtc_db_num_rows($check_customer_query) || $key == '') {
    $case = 'no_account';
    $messageStack->add('password_double_opt_in', TEXT_NO_ACCOUNT);
  } elseif (time() > (strtotime($check_customer['password_request_time']) + VALID_REQUEST_TIME)) {
    $case = 'double_opt';
    $messageStack->add('password_double_opt_in', TEXT_REQUEST_NOT_VALID);
  } else {
  
    if (isset ($_POST['action']) && ($_POST['action'] == 'process')) {

      $valid_params = array(
        'password_new',
        'password_confirmation',
      );

      // prepare variables
      foreach ($_POST as $key => $value) {
        if (!is_object(${$key}) && in_array($key , $valid_params)) {
          ${$key} = xtc_db_prepare_input($value);
        }
      }

      $error = false;
      $policy = new password_policy();
      if (!$policy->validate($password_new)) {
        $error = true;
        foreach ($policy->get_errors() as $k => $error) {
          $messageStack->add('password_double_opt_in', $error);
        }
      }
      elseif ($password_new != $password_confirmation) {
        $error = true;
        $messageStack->add('password_double_opt_in', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
      }

      if ($error === false) {
        $sql_data_array = array('customers_password' => xtc_encrypt_password($password_new),
                                'password_request_key' => '',
                                'password_request_time' => '',
                                'customers_last_modified' => 'now()',
                                );
        xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".(int) $check_customer['customers_id']."'");
        
        // redirect to login
        $messageStack->add_session('login', SUCCESS_PASSWORD_UPDATED);
        xtc_redirect(xtc_href_link(FILENAME_LOGIN, 'info=1', 'SSL'));
      }
    }
  }
}

$breadcrumb->add(NAVBAR_TITLE_PASSWORD_DOUBLE_OPT, xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, '', 'SSL'));

require (DIR_WS_INCLUDES.'header.php');

switch ($case) {
  case 'second_opt_in':
    if ($messageStack->size('password_double_opt_in') > 0) {
      $smarty->assign('error', $messageStack->output('password_double_opt_in'));
    }
    $smarty->assign('FORM_ACTION', xtc_draw_form('password_double_opt_in', xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, xtc_get_all_get_params(), 'SSL'), 'post').xtc_draw_hidden_field('action', 'process'));
    $smarty->assign('INPUT_NEW', xtc_draw_password_fieldNote(array ('name' => 'password_new', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_PASSWORD_NEW_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_NEW_TEXT.'</span>' : ''))));
    $smarty->assign('INPUT_CONFIRM', xtc_draw_password_fieldNote(array ('name' => 'password_confirmation', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_CONFIRMATION_TEXT.'</span>' : ''))));
    $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
    $smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
    $smarty->assign('FORM_END', '</form>');
    
    // dont allow cache
    $smarty->caching = 0;
    $smarty->assign('language', $_SESSION['language']);
    $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_password.html');
    break;
      
  case 'first_opt_in' :
    $smarty->assign('text_heading', HEADING_PASSWORD_FORGOTTEN);
    $smarty->assign('info_message', sprintf(TEXT_LINK_MAIL_SENDED, (VALID_REQUEST_TIME / 60)));
    
    // dont allow cache
    $smarty->caching = 0;
    $smarty->assign('language', $_SESSION['language']);
    $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/password_messages.html');
    break;

  case 'code_error' :
  case 'wrong_mail' :
  case 'no_account' :
  case 'double_opt' :
    if (in_array('password', $use_captcha)) {
      $smarty->assign('VVIMG', '<img src="'.xtc_href_link(FILENAME_DISPLAY_VVCODES, '', 'SSL').'" alt="Captcha" />');
      $smarty->assign('INPUT_CODE', xtc_draw_input_field('vvcode', '', 'size="'.MODULE_CAPTCHA_CODE_LENGTH.'" maxlength="'.MODULE_CAPTCHA_CODE_LENGTH.'"', 'text', false));
    }
    if ($messageStack->size('password_double_opt_in') > 0) {
      $smarty->assign('info_message', $messageStack->output('password_double_opt_in'));
    }
    $smarty->assign('text_heading', HEADING_PASSWORD_FORGOTTEN);
    $smarty->assign('message', TEXT_PASSWORD_FORGOTTEN);
    $smarty->assign('SHOP_NAME', STORE_NAME);
    $smarty->assign('FORM_ACTION', xtc_draw_form('sign', xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, 'action=first_opt_in', 'SSL')));
    $smarty->assign('INPUT_EMAIL', xtc_draw_input_field('email', xtc_db_input(isset($_POST['email']) ? $_POST['email'] : ''), '', 'text', false));
    $smarty->assign('BUTTON_SEND', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
    $smarty->assign('FORM_END', '</form>');

    // dont allow cache
    $smarty->caching = 0;
    $smarty->assign('language', $_SESSION['language']);
    $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/password_double_opt_in.html');
    break;
}

$smarty->assign('main_content', $main_content);

// dont allow cache
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>