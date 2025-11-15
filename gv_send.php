<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (gv_send.php,v 1.1.2.3 2003/05/12); www.oscommerce.com
   (c) 2006 XT-Commerce (gv_send.php 1034 2005-07-15)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');

if (ACTIVATE_GIFT_SYSTEM != 'true') {
  xtc_redirect(FILENAME_DEFAULT);
}

// if the customer is not logged on, redirect them to the login page
if (!isset ($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// include needed functions
require_once (DIR_FS_INC.'xtc_validate_email.inc.php');

// smarty
$smarty = new Smarty();

if (isset($_POST['back_x']) || isset($_POST['back_y']) || isset($_POST['back'])) {
  $_GET['action'] = '';
}

$valid_params = array(
  'to_name',
  'to_email',
  'amount',
  'message',
  'send_name',
);

// prepare variables
foreach ($_POST as $key => $value) {
  if ((!isset(${$key}) || !is_object(${$key})) && in_array($key , $valid_params)) {
    ${$key} = xtc_db_prepare_input($value);
  }
}

$error = false;
if (isset($_GET['action']) && $_GET['action'] == 'send') {
  if (xtc_validate_email($to_email) == false) {
    $error = true;
    $messageStack->add('gv_send', ERROR_ENTRY_EMAIL_ADDRESS_CHECK);
  }
  
  $gv_query = xtc_db_query("SELECT amount 
                              FROM ".TABLE_COUPON_GV_CUSTOMER." 
                             WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
  $gv_result = xtc_db_fetch_array($gv_query);
  $customer_amount = $xtPrice->xtcCalculateCurr($gv_result['amount']);
  $gv_amount = xtc_input_validation($amount, 'amount');
  
  if ((double)$gv_amount <= 0
      || (double)$gv_amount > (double)$customer_amount
      )
  {
    $error = true;
    $messageStack->add('gv_send', ERROR_ENTRY_AMOUNT_CHECK);
  }
}

if (isset($_GET['action']) && $_GET['action'] == 'process') {
  $gv_code = create_coupon_code(10);
  $gv_query = xtc_db_query("SELECT amount 
                              FROM ".TABLE_COUPON_GV_CUSTOMER." 
                             WHERE customer_id='".(int)$_SESSION['customer_id']."'");
  $gv_result = xtc_db_fetch_array($gv_query);
  $gv_amount = xtc_input_validation($amount, 'amount');
  $new_amount = $xtPrice->xtcCalculateCurr($gv_result['amount']) - (double)$gv_amount;
  if ($new_amount < 0) {
    $error = true;
    $messageStack->add('gv_send', ERROR_ENTRY_AMOUNT_CHECK);
    $_GET['action'] = 'send';
  } else {
    xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER." 
                     SET amount = '".xtc_db_input($new_amount)."' 
                   WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
    
    $sql_data_array = array(
      'coupon_type' => 'G',
      'coupon_code' => $gv_code,
      'date_created' => 'now()',
      'coupon_amount' => $xtPrice->xtcRemoveCurr($gv_amount)
    );
    xtc_db_perform(TABLE_COUPONS, $sql_data_array);
    $insert_id = xtc_db_insert_id();

    $sql_data_array = array(
      'coupon_id' => $insert_id,
      'customer_id_sent' => $_SESSION['customer_id'],
      'sent_firstname' => $_SESSION['customer_first_name'],
      'sent_lastname' => $_SESSION['customer_last_name'],
      'emailed_to' => $to_email,
      'date_sent' => 'now()'
    );
    xtc_db_perform(TABLE_COUPON_EMAIL_TRACK, $sql_data_array);
    
    $gv_email_subject = sprintf(EMAIL_GV_TEXT_SUBJECT, $send_name);

    $smarty->assign('language', $_SESSION['language']);
    $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');    
    $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
    $smarty->assign('GIFT_LINK', xtc_href_link(FILENAME_GV_REDEEM, 'gv_no='.$gv_code, 'NONSSL', false));
    $smarty->assign('AMMOUNT', $xtPrice->xtcFormat($gv_amount, true));
    $smarty->assign('AMOUNT', $xtPrice->xtcFormat($gv_amount, true));
    $smarty->assign('GIFT_CODE', $gv_code);
    $smarty->assign('MESSAGE', $message);
    $smarty->assign('NAME', $to_name);
    $smarty->assign('FROM_NAME', $send_name);

    // dont allow cache
    $smarty->caching = 0;

    $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/send_gift_to_friend.html');
    $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/send_gift_to_friend.txt');

    // send mail
    xtc_php_mail(EMAIL_BILLING_ADDRESS, 
                 EMAIL_BILLING_NAME, 
                 $to_email, 
                 $to_name, 
                 '', 
                 EMAIL_BILLING_REPLY_ADDRESS, 
                 EMAIL_BILLING_REPLY_ADDRESS_NAME, 
                 '', 
                 '', 
                 $gv_email_subject, 
                 $html_mail, 
                 $txt_mail);

  }
}

// build breadcrumb
$breadcrumb->add(NAVBAR_GV_SEND);

// include header
require (DIR_WS_INCLUDES . 'header.php');

// include boxes
$display_mode = 'gv';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

if (isset($_GET['action']) && $_GET['action'] == 'process') {
  $smarty->assign('action', 'process');
  $smarty->assign('LINK_DEFAULT', '<a href="'.xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL').'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
}

elseif (isset($_GET['action']) && $_GET['action'] == 'send' && $error === false) {
  $smarty->assign('action', 'send');
  $send_name = $_SESSION['customer_first_name'].' '.$_SESSION['customer_last_name'];

  $smarty->assign('FORM_ACTION', xtc_draw_form('gv_process', xtc_href_link(FILENAME_GV_SEND, 'action=process', 'NONSSL'), 'post'));
  $smarty->assign('MAIN_MESSAGE', sprintf(MAIN_MESSAGE, $xtPrice->xtcFormat($gv_amount, true), $to_name, $to_email, $to_name, $xtPrice->xtcFormat((double)$gv_amount, true), $send_name));
  if ($message != '') {
    $smarty->assign('PERSONAL_MESSAGE', sprintf(PERSONAL_MESSAGE, $send_name));
    $smarty->assign('POST_MESSAGE', $message);
  }
  $smarty->assign('HIDDEN_FIELDS', xtc_draw_hidden_field('send_name', $send_name).xtc_draw_hidden_field('to_name', $to_name).xtc_draw_hidden_field('to_email', $to_email).xtc_draw_hidden_field('amount', $gv_amount).xtc_draw_hidden_field('message', $message));
  $smarty->assign('LINK_BACK', xtc_image_submit('button_back.gif', IMAGE_BUTTON_BACK, 'name=back').'</a>');
  $smarty->assign('LINK_SUBMIT', xtc_image_submit('button_send.gif', IMAGE_BUTTON_CONTINUE));
}

elseif (!isset($_GET['action']) || $_GET['action'] == '' || $error !== false) {
  $smarty->assign('action', '');
  $smarty->assign('FORM_ACTION', xtc_draw_form('gv_send', xtc_href_link(FILENAME_GV_SEND, 'action=send', 'NONSSL'), 'post'));
  $smarty->assign('LINK_SEND', xtc_href_link(FILENAME_GV_SEND, 'action=send', 'NONSSL'));
  $smarty->assign('INPUT_TO_NAME', xtc_draw_input_field('to_name', '', 'autocomplete="name"'));
  $smarty->assign('INPUT_EMAIL', xtc_draw_input_field('to_email', '', 'autocomplete="email"'));
  $smarty->assign('INPUT_AMOUNT', xtc_draw_input_field('amount', '', '', 'text', false));
  $smarty->assign('TEXTAREA_MESSAGE', xtc_draw_textarea_field('message', 'soft', 50, 15));
  $smarty->assign('LINK_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
}

if ($messageStack->size('gv_send') > 0) {
  $smarty->assign('error_message', $messageStack->output('gv_send'));
}

$smarty->assign('GV_FAQ_LINK', $main->getContentLink(6, MORE_INFO,'NONSSL'));
$smarty->assign('FORM_END', '</form>');
$smarty->assign('language', $_SESSION['language']);

$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/gv_send.html');
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');