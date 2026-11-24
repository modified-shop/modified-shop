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
require_once (DIR_FS_INC.'xtc_create_password.inc.php');

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
    ${$key} = (is_array($value) ? '' : xtc_db_prepare_input($value));
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
  // the amount as the currency of the session shows it, checked in the default currency as it will be booked
  $gv_amount = number_format((float)xtc_input_validation($amount, 'amount'), (int)$xtPrice->currencies[$xtPrice->actualCurr]['decimal_places'], '.', '');
  $gv_debit = number_format((float)$xtPrice->xtcRemoveCurr($gv_amount), 4, '.', '');

  if ((float)$gv_debit <= 0
      || !is_array($gv_result)
      || (float)$gv_debit > (float)$gv_result['amount']
      )
  {
    $error = true;
    $messageStack->add('gv_send', ERROR_ENTRY_AMOUNT_CHECK);
  }

  if (isset($modified_session_handler) && $modified_session_handler->holds_lock() === false) {
    // without the lock the token of the confirmation would not be saved
    $error = true;
    $messageStack->add('gv_send', (defined('TEXT_GV_SEND_ALREADY_RUNNING') ? TEXT_GV_SEND_ALREADY_RUNNING : ENTRY_TOKEN_ERROR));
  }
}

if (isset($_GET['action']) && $_GET['action'] == 'process') {
  $process_error = ENTRY_TOKEN_ERROR;
  if (isset($modified_session_handler) && $modified_session_handler->holds_lock() === false) {
    // a session read without its lock may still hold a token that a running transfer used up
    $process_error = (defined('TEXT_GV_SEND_ALREADY_RUNNING') ? TEXT_GV_SEND_ALREADY_RUNNING : ENTRY_TOKEN_ERROR);
  } elseif (isset($_SESSION['gv_send_tokens'])
            && isset($_POST['gv_send_token'])
            && is_string($_POST['gv_send_token'])
            )
  {
    foreach ($_SESSION['gv_send_tokens'] as $key => $token) {
      if (hash_equals($token, $_POST['gv_send_token'])) {
        // one transfer per confirmation
        unset($_SESSION['gv_send_tokens'][$key]);
        $process_error = '';
        break;
      }
    }
  }

  if ($process_error != '') {
    $error = true;
    $messageStack->add('gv_send', $process_error);
    $_GET['action'] = 'send';
  }
}

if (isset($_GET['action']) && $_GET['action'] == 'process') {
  $gv_code = create_coupon_code(10);
  $gv_amount = number_format((float)xtc_input_validation($amount, 'amount'), (int)$xtPrice->currencies[$xtPrice->actualCurr]['decimal_places'], '.', '');
  // the balance is kept in the default currency, like the amount of the voucher;
  // one decimal string for both, rounded as round() does, a float would be written with fewer digits
  $gv_debit = number_format((float)$xtPrice->xtcRemoveCurr($gv_amount), 4, '.', '');
  // one statement takes the amount only while the balance covers it, so two transfers at the same time cannot both spend it
  $gv_debited = false;
  $gv_debit_failed = false;
  $gv_booking_error = (defined('ERROR_GV_SEND_BOOKING') ? ERROR_GV_SEND_BOOKING : 'The voucher could not be booked. Please try again later.');
  if ((float)$gv_debit > 0) {
    // count of this statement, a query of the query log or of its hooks after it does not change it
    $gv_debit_query = xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER."
                                       SET amount = amount - ".$gv_debit."
                                     WHERE customer_id = '".(int)$_SESSION['customer_id']."'
                                       AND amount >= ".$gv_debit, 'db_link', $gv_debit_info);
    $gv_debit_failed = ($gv_debit_query === false);
    $gv_debited = ($gv_debit_failed === false && is_array($gv_debit_info) && $gv_debit_info['affected_rows'] == 1);
  }
  if ($gv_debited === true) {
    $gv_insert_query = xtc_db_query("INSERT INTO ".TABLE_COUPONS." (coupon_type, coupon_code, date_created, coupon_amount)
                                     VALUES ('G', '".xtc_db_input($gv_code)."', now(), '".$gv_debit."')", 'db_link', $gv_insert_info);
    $insert_id = (($gv_insert_query !== false && is_array($gv_insert_info)) ? (int)$gv_insert_info['insert_id'] : 0);
    if ($insert_id < 1) {
      // no voucher: the debit goes back, nothing is tracked, mailed or shown as sent
      xtc_db_query("UPDATE ".TABLE_COUPON_GV_CUSTOMER."
                       SET amount = amount + ".$gv_debit."
                     WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
      $gv_debited = false;
      $gv_debit_failed = true;
    }
  }
  if ($gv_debited !== true) {
    $error = true;
    // a refused statement is no matter of the amount, the balance is unchanged
    $messageStack->add('gv_send', (($gv_debit_failed === true) ? $gv_booking_error : ERROR_ENTRY_AMOUNT_CHECK));
    $_GET['action'] = 'send';
  } else {
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
    // the value of the voucher in the currency of the session, as its redemption shows it
    $gv_shown = $xtPrice->xtcFormat($xtPrice->xtcCalculateCurr($gv_debit), true);
    $smarty->assign('AMMOUNT', $gv_shown);
    $smarty->assign('AMOUNT', $gv_shown);
    $smarty->assign('GIFT_CODE', $gv_code);
    $smarty->assign('MESSAGE', encode_htmlspecialchars($message, ENT_NOQUOTES));
    $smarty->assign('NAME', encode_htmlspecialchars($to_name, ENT_NOQUOTES));
    $smarty->assign('FROM_NAME', encode_htmlspecialchars($send_name, ENT_NOQUOTES));

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

  // a token per confirmation, apart from the shared form token of other pages; the ten newest stay valid
  $gv_send_token = xtc_RandomString(32);
  $_SESSION['gv_send_tokens'] = array_slice(array_merge((isset($_SESSION['gv_send_tokens']) ? $_SESSION['gv_send_tokens'] : array()), array($gv_send_token)), -10);
  $smarty->assign('FORM_ACTION', xtc_draw_form('gv_process', xtc_href_link(FILENAME_GV_SEND, 'action=process', 'NONSSL'), 'post').xtc_draw_hidden_field('gv_send_token', $gv_send_token));
  // the value of the voucher in the currency of the session, as its redemption shows it
  $gv_shown = $xtPrice->xtcFormat($xtPrice->xtcCalculateCurr($gv_debit), true);
  $smarty->assign('MAIN_MESSAGE', sprintf(MAIN_MESSAGE, $gv_shown, $to_name, $to_email, $to_name, $gv_shown, $send_name));
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