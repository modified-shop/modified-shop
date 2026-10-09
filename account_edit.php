<?php
/* -----------------------------------------------------------------------------------------
   $Id$   
   
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(account_edit.php,v 1.63 2003/05/19); www.oscommerce.com 
   (c) 2003  nextcommerce (account_edit.php,v 1.14 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce - www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_image_button.inc.php');
require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
require_once (DIR_FS_INC.'xtc_get_geo_zone_code.inc.php');
require_once (DIR_FS_INC.'xtc_get_customers_country.inc.php');
require_once (DIR_FS_INC.'get_customers_gender.inc.php');
require_once (DIR_FS_INC.'secure_form.inc.php');
require_once (DIR_FS_INC.'write_customers_session.inc.php');
require_once (DIR_FS_INC.'clear_checkout_session.inc.php');
require_once (DIR_FS_INC.'xtc_validate_password.inc.php');
require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
require_once (DIR_FS_INC.'xtc_datetime_short.inc.php');
require_once (DIR_FS_INC.'xtc_email_address_lock.inc.php');

define('EMAIL_CHANGE_VALID_TIME', 60*60);

$email_change_password = (defined('ACCOUNT_EMAIL_CHANGE_PASSWORD') && ACCOUNT_EMAIL_CHANGE_PASSWORD == 'true');
$email_change_verify = (defined('ACCOUNT_EMAIL_CHANGE_VERIFY') && ACCOUNT_EMAIL_CHANGE_VERIFY == 'true');

// confirm the new email address, the link must work without a login
if (isset($_GET['action']) && $_GET['action'] == 'verify_email') {
  // the redirect after the next login must not open the used link again
  if (isset($_SESSION['tracking']['pageview_history'])) {
    $_SESSION['tracking']['pageview_history'] = array_values(array_filter($_SESSION['tracking']['pageview_history'], function ($url) {
      return strpos($url, 'action=verify_email') === false;
    }));
  }

  $verify_customers_id = isset($_GET['customers_id']) ? (int)$_GET['customers_id'] : 0;
  $verify_key = (isset($_GET['key']) && is_string($_GET['key'])) ? $_GET['key'] : '';
  $verify_ok = false;

  if ($verify_customers_id > 0 && $verify_key != '') {
    $check_customer_query = xtc_db_query("SELECT customers_id,
                                                 customers_email_address,
                                                 customers_email_address_new,
                                                 customers_email_request_key,
                                                 customers_email_request_time
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".$verify_customers_id."'
                                             AND account_type = '0'
                                             AND customers_email_request_key != ''
                                             AND customers_email_address_new != ''");
    $check_customer = xtc_db_fetch_array($check_customer_query);

    if (is_array($check_customer)
        && hash_equals($check_customer['customers_email_request_key'], hash('sha256', $verify_key))
        && time() <= (int)strtotime((string)$check_customer['customers_email_request_time']) + EMAIL_CHANGE_VALID_TIME
        )
    {
      // no other request may take the same address between the check and the change
      if (xtc_email_address_lock($check_customer['customers_email_address_new']) === true) {
        $check_email_query = xtc_db_query("SELECT count(*) as total
                                             FROM ".TABLE_CUSTOMERS."
                                            WHERE customers_email_address = '".xtc_db_input($check_customer['customers_email_address_new'])."'
                                              AND account_type = '0'
                                              AND customers_id != '".$verify_customers_id."'");
        $check_email = xtc_db_fetch_array($check_email_query);
        if ($check_email['total'] == 0) {
          // reset links sent to the old address must not work any longer
          xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                           SET customers_email_address = '".xtc_db_input($check_customer['customers_email_address_new'])."',
                               customers_email_address_new = '',
                               customers_email_request_key = '',
                               customers_email_request_time = NULL,
                               password_request_key = '',
                               password_request_time = NULL,
                               customers_password_time = '".time()."',
                               customers_last_modified = now()
                         WHERE customers_id = '".$verify_customers_id."'
                           AND customers_email_request_key = '".xtc_db_input($check_customer['customers_email_request_key'])."'");

          // only one of parallel requests with the same link changes the address
          $verify_ok = (xtc_db_affected_rows() == 1);
        }
        xtc_email_address_lock('', true);
      }
    }
  }

  if ($verify_ok === true) {
    $email_old = $check_customer['customers_email_address'];
    $email_new = $check_customer['customers_email_address_new'];

    xtc_db_query("UPDATE ".TABLE_CUSTOMERS_INFO."
                     SET customers_info_date_account_last_modified = now()
                   WHERE customers_info_id = '".$verify_customers_id."'");

    // notify the previous address, without links or codes
    $smarty = new Smarty();
    $smarty->assign('language', $_SESSION['language']);
    $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
    $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
    $smarty->assign('EMAIL_OLD', $email_old);
    $smarty->assign('EMAIL_NEW', $email_new);
    $smarty->assign('CHANGE_TIME', xtc_datetime_short(date('Y-m-d H:i:s')));
    $smarty->caching = 0;

    $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/email_change_notify_mail.html');
    $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/email_change_notify_mail.txt');

    xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                 EMAIL_SUPPORT_NAME,
                 $email_old,
                 '',
                 '',
                 EMAIL_SUPPORT_REPLY_ADDRESS,
                 EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                 '',
                 '',
                 TEXT_EMAIL_CHANGE_NOTIFY_SUBJECT,
                 $html_mail,
                 $txt_mail,
                 1
                 );

    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/account_edit_email_before_redirect/','php') as $file) require ($file);

    // other sessions end through customers_password_time, the own login ends here so the message survives
    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] == $verify_customers_id) {
      $_SESSION['cart']->reset();
      if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
        $_SESSION['wishlist']->reset();
      }
      xtc_session_reset();
    }

    $messageStack->add_session('login', SUCCESS_EMAIL_CHANGE_VERIFIED, 'success');
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  if (isset($_SESSION['customer_id'])) {
    $messageStack->add_session('account', ERROR_EMAIL_CHANGE_LINK_INVALID);
    xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  }
  $messageStack->add_session('login', ERROR_EMAIL_CHANGE_LINK_INVALID);
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

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
    'gender',
    'firstname',
    'lastname',
    'dob',
    'vat',
    'email_address',
    'confirm_email_address',
    'password_current',
    'telephone',
    'fax',
  );

  // prepare variables
  foreach ($_POST as $key => $value) {
    if ((!isset(${$key}) || !is_object(${$key})) && in_array($key , $valid_params)) {
      ${$key} = xtc_db_prepare_input($value);
    }
  }

  $error = false;

  if (mb_strlen($firstname, $_SESSION['language_charset']) < ENTRY_FIRST_NAME_MIN_LENGTH) {
    $error = true;
    $messageStack->add('account_edit', ENTRY_FIRST_NAME_ERROR);
  }

  if (mb_strlen($lastname, $_SESSION['language_charset']) < ENTRY_LAST_NAME_MIN_LENGTH) {
    $error = true;
    $messageStack->add('account_edit', ENTRY_LAST_NAME_ERROR);
  }

  if (ACCOUNT_DOB == 'true') {
    $date = xtc_date_raw($dob);
    if (is_numeric($date) == false
        || strlen($date) != 8
        || checkdate(substr($date, 4, 2), substr($date, 6, 2), substr($date, 0, 4)) == false
        )
    {
      $error = true;
      $messageStack->add('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
    }
  }

  // New VAT Check
  if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
    if (!isset($vat)) $vat = '';
    $country = xtc_get_customers_country($_SESSION['customer_id']);
    require_once(DIR_WS_CLASSES.'vat_validation.php');
    $vatID = new vat_validation($vat, $_SESSION['customer_id'], '', $country, ($_SESSION['account_type'] != '0'));
    if (ACCOUNT_COMPANY_VAT_GROUP == 'true' 
        && $_SESSION['customers_status']['customers_status'] != '0'
        && $vat != '' 
        )
    {
      $customers_status = $vatID->vat_info['status'];
    }
    $customers_vat_id_status = isset($vatID->vat_info['vat_id_status']) ? $vatID->vat_info['vat_id_status'] : '';
    if (isset($vatID->vat_info['error']) && $vatID->vat_info['error']==1){
      $messageStack->add('account_edit', ENTRY_VAT_ERROR);
      $error = true;
    }
  }

  if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
    $error = true;
    $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR);
  }

  if (xtc_validate_email($email_address) == false) {
    $error = true;
    $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
  } else { 
    $check_email_query = xtc_db_query("SELECT count(*) as total 
                                         FROM ".TABLE_CUSTOMERS." 
                                        WHERE customers_email_address = '".xtc_db_input($email_address)."' 
                                          AND account_type = '0' 
                                          AND customers_id != '".(int)$_SESSION['customer_id']."'"); 
    $check_email = xtc_db_fetch_array($check_email_query); 
    if ($check_email['total'] > 0) { 
        $error = true; 
        $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS); 
    }
  }

  if ($email_address != $confirm_email_address) {
      $error = true;    
      $messageStack->add('account_edit', ENTRY_EMAIL_ERROR_NOT_MATCHING);
  }
  
  if (ACCOUNT_TELEPHONE_OPTIONAL == 'false' && strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
    $error = true;
    $messageStack->add('account_edit', ENTRY_TELEPHONE_NUMBER_ERROR);
  }

  if (check_secure_form($_POST) === false) {
    $messageStack->add('account_edit', ENTRY_TOKEN_ERROR);
    $error = true;
  }

  // regular accounts confirm a new email address with the current password, if enabled
  $email_changed = false;
  if ($_SESSION['account_type'] == '0') {
    $stored_customer_query = xtc_db_query("SELECT customers_email_address,
                                                  customers_password
                                             FROM ".TABLE_CUSTOMERS."
                                            WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
    $stored_customer = xtc_db_fetch_array($stored_customer_query);
    $email_changed = (mb_strtolower(trim($email_address)) != mb_strtolower(trim($stored_customer['customers_email_address'])));
  }

  $email_change_confirmed = false;

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/account_edit_check_data/','php') as $file) require ($file);

  if ($email_changed === true && $email_change_password === true && $email_change_confirmed !== true) {
    $password_current = isset($password_current) ? $password_current : '';
    if (strlen($password_current) < 1) {
      $error = true;
      $messageStack->add('account_edit', ENTRY_PASSWORD_CURRENT_ERROR);
    } elseif (!xtc_validate_password($password_current, $stored_customer['customers_password'], $_SESSION['customer_id'])) {
      $error = true;
      $messageStack->add('account_edit', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
    }
  }

  if ($error == false) {
    $sql_data_array = array(
      'customers_vat_id' => $vat, 
      'customers_vat_id_status' => $customers_vat_id_status, 
      'customers_firstname' => $firstname, 
      'customers_lastname' => $lastname, 
      'customers_email_address' => $email_address, 
      'customers_telephone' => ((isset($telephone)) ? $telephone : ''),
      'customers_fax' => ((isset($fax)) ? $fax : ''),
      'customers_last_modified' => 'now()'
    );

    if (isset($customers_status) && $_SESSION['account_type'] == '0') {
      if ((int)$customers_status == 0) {
        if (DEFAULT_CUSTOMERS_STATUS_ID != 0) {
          $customers_status = DEFAULT_CUSTOMERS_STATUS_ID;
        } else {
          $customers_status = 2;
        }
      }
      $sql_data_array['customers_status'] = (int)$customers_status;
    }

    if (ACCOUNT_GENDER == 'true') {
      $sql_data_array['customers_gender'] = $gender;
    }
    if (ACCOUNT_DOB == 'true') {
      $sql_data_array['customers_dob'] = xtc_date_raw($dob);
    }

    if ($_SESSION['account_type'] == '0') {
      if ($email_change_verify === true) {
        // regular accounts get the new address only after the confirmation link
        unset($sql_data_array['customers_email_address']);
      } elseif ($email_changed === true) {
        // an older link must not overwrite the immediate change, reset links to the old address must not work
        $sql_data_array['customers_email_address_new'] = '';
        $sql_data_array['customers_email_request_key'] = '';
        $sql_data_array['customers_email_request_time'] = 'null';
        $sql_data_array['password_request_key'] = '';
        $sql_data_array['password_request_time'] = 'null';
      }
    }

    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/account_edit_customer_data/','php') as $file) require ($file);
    
    // an immediate change must not take an address that a parallel request takes
    $email_locked = false;
    if ($_SESSION['account_type'] == '0' && $email_changed === true && $email_change_verify !== true) {
      $check_email = array('total' => 1);
      if (xtc_email_address_lock($email_address) === true) {
        $email_locked = true;
        $check_email_query = xtc_db_query("SELECT count(*) as total
                                             FROM ".TABLE_CUSTOMERS."
                                            WHERE customers_email_address = '".xtc_db_input($email_address)."'
                                              AND account_type = '0'
                                              AND customers_id != '".(int)$_SESSION['customer_id']."'");
        $check_email = xtc_db_fetch_array($check_email_query);
      }
      if ($check_email['total'] > 0) {
        xtc_email_address_lock('', true);
        $messageStack->add_session('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
        xtc_redirect(xtc_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));
      }
    }

    // a reset or password change in the meantime ended this session, then nothing is stored
    xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".(int)$_SESSION['customer_id']."' AND customers_password_time = '".(int)$_SESSION['customer_time']."'");

    if ($email_locked === true) {
      xtc_email_address_lock('', true);
    }

    if ($email_changed === true && $email_change_verify === true) {
      $email_token = xtc_random_charcode(32);

      xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                       SET customers_email_address_new = '".xtc_db_input($email_address)."',
                           customers_email_request_key = '".xtc_db_input(hash('sha256', $email_token))."',
                           customers_email_request_time = '".date('Y-m-d H:i:s')."'
                     WHERE customers_id = '".(int)$_SESSION['customer_id']."'
                       AND customers_password_time = '".(int)$_SESSION['customer_time']."'");

      // only a still valid session may store the request and send the link
      if (xtc_db_affected_rows() == 1) {
        $smarty->assign('language', $_SESSION['language']);
        $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
        $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
        $smarty->assign('EMAIL', $email_address);
        $smarty->assign('LINK', xtc_href_link(FILENAME_ACCOUNT_EDIT, 'action=verify_email&customers_id='.(int)$_SESSION['customer_id'].'&key='.$email_token, 'SSL', false));
        $smarty->assign('VALID_REQUEST_TIME', (EMAIL_CHANGE_VALID_TIME / 60));
        $smarty->caching = 0;

        $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/email_change_verify_mail.html');
        $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/email_change_verify_mail.txt');

        xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                     EMAIL_SUPPORT_NAME,
                     $email_address,
                     '',
                     '',
                     EMAIL_SUPPORT_REPLY_ADDRESS,
                     EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                     '',
                     '',
                     TEXT_EMAIL_CHANGE_VERIFY_SUBJECT,
                     $html_mail,
                     $txt_mail,
                     1
                     );

        $messageStack->add_session('account', sprintf(SUCCESS_EMAIL_CHANGE_REQUESTED, (EMAIL_CHANGE_VALID_TIME / 60)), 'success');
      }
    }

    xtc_db_query("UPDATE ".TABLE_CUSTOMERS_INFO." 
                     SET customers_info_date_account_last_modified = now() 
                   WHERE customers_info_id = '".(int)$_SESSION['customer_id']."'");

    // write customers session
    write_customers_session((int)$_SESSION['customer_id']);

    $messageStack->add_session('account', SUCCESS_ACCOUNT_UPDATED, 'success');
    xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  }
} else {
  $account_query = xtc_db_query("SELECT *,
                                        customers_vat_id as vat,
                                        customers_email_address as confirm_email_address
                                   FROM ".TABLE_CUSTOMERS." 
                                  WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
  $account = xtc_db_fetch_array($account_query);
  
  foreach ($account as $key => $value) {
    ${str_replace('customers_', '', $key)} = (($key == 'customers_dob') ? xtc_date_short($value) : $value);
  }
}

// build breadcrumb
$breadcrumb->add(NAVBAR_TITLE_1_ACCOUNT_EDIT, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_ACCOUNT_EDIT, xtc_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));

// include header
require (DIR_WS_INCLUDES.'header.php');

// include boxes
$display_mode = 'account';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$smarty->assign('FORM_ACTION', xtc_draw_form('account_edit', xtc_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL')).xtc_draw_hidden_field('action', 'process').secure_form('account_edit'));

if ($messageStack->size('account_edit') > 0)
  $smarty->assign('error_message', $messageStack->output('account_edit'));

if (ACCOUNT_GENDER == 'true') {
  $smarty->assign('gender', '1');
  $smarty->assign('INPUT_GENDER', xtc_draw_pull_down_menuNote(array('name' => 'gender', 'text' => (xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">'.ENTRY_GENDER_TEXT.'</span>' : '')), get_customers_gender(), ((isset($gender)) ? $gender : ''), 'autocomplete="sex"'));
} else {
  $smarty->assign('gender', '0');
}

if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
  $smarty->assign('vat', '1');
  $smarty->assign('INPUT_VAT', xtc_draw_input_fieldNote(array('name' => 'vat', 'text' => (xtc_not_null(ENTRY_VAT_TEXT) ? '<span class="inputRequirement">'.ENTRY_VAT_TEXT.'</span>' : ''))));
  $smarty->assign('TEXT_VAT_NOTE', ENTRY_VAT_NOTE);
} else {
  $smarty->assign('vat', '0');
}

$smarty->assign('INPUT_FIRSTNAME', xtc_draw_input_fieldNote(array('name' => 'firstname', 'text' => (xtc_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_FIRST_NAME_TEXT.'</span>' : '')), '', 'autocomplete="given-name"'));
$smarty->assign('INPUT_LASTNAME', xtc_draw_input_fieldNote(array('name' => 'lastname', 'text' => (xtc_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_LAST_NAME_TEXT.'</span>' : '')), '', 'autocomplete="family-name"'));
$smarty->assign('csID', $_SESSION['customer_cid']);

if (ACCOUNT_DOB == 'true') {
  $smarty->assign('birthdate', '1');
  $smarty->assign('INPUT_DOB', xtc_draw_input_fieldNote(array('name' => 'dob', 'text' => (xtc_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">'.ENTRY_DATE_OF_BIRTH_TEXT.'</span>' : '')), '', 'autocomplete="bday"'));
  $smarty->assign('TEXT_DOB_NOTE', ENTRY_DATE_OF_BIRTH_NOTE);
} else {
  $smarty->assign('birthdate', '0');
}

if (ACCOUNT_FAX == 'true') {
  $smarty->assign('fax', '1');
  $smarty->assign('INPUT_FAX', xtc_draw_input_fieldNote(array('name' => 'fax', 'text' => (xtc_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">'.ENTRY_FAX_NUMBER_TEXT.'</span>' : '')), '', 'autocomplete="fax"'));
} else {
  $smarty->assign('fax', '0');
}

$smarty->assign('INPUT_EMAIL', xtc_draw_input_fieldNote(array('name' => 'email_address', 'text' => (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), '', 'autocomplete="email"'));
$smarty->assign('INPUT_CONFIRM_EMAIL', xtc_draw_input_fieldNote(array('name' => 'confirm_email_address', 'text' => (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), '', 'autocomplete="email"'));
if ($_SESSION['account_type'] == '0' && $email_change_password === true) {
  $smarty->assign('INPUT_PASSWORD_CURRENT', xtc_draw_password_fieldNote(array('name' => 'password_current', 'text' => ''), '', 'autocomplete="current-password"'));
}

$smarty->assign('INPUT_TEL', xtc_draw_input_fieldNote(array('name' => 'telephone', 'text' => ((ACCOUNT_TELEPHONE_OPTIONAL == 'false' && xtc_not_null(ENTRY_TELEPHONE_NUMBER_TEXT)) ? '<span class="inputRequirement">'.ENTRY_TELEPHONE_NUMBER_TEXT.'</span>' : '')), '', 'autocomplete="tel"'));

$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('BUTTON_SUBMIT_SAVE', xtc_image_submit('button_save.gif', IMAGE_BUTTON_SAVE));
$smarty->assign('BUTTON_SUBMIT_UPDATE', xtc_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE));
$smarty->assign('FORM_END', '</form>');
$smarty->assign('language', $_SESSION['language']);

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/account_edit_smarty_data/','php') as $file) require ($file);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_edit.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
