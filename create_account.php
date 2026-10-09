<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(create_account.php,v 1.63 2003/05/28); www.oscommerce.com
   (c) 2003 nextcommerce (create_account.php,v 1.27 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (create_account.php 307 2007-03-30)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

defined('DISPLAY_PRIVACY_CHECK') or define('DISPLAY_PRIVACY_CHECK', 'true');

// captcha
$use_captcha = array();
if (defined('MODULE_CAPTCHA_ACTIVE')) {
  $use_captcha = explode(',', MODULE_CAPTCHA_ACTIVE);
}
defined('MODULE_CAPTCHA_CODE_LENGTH') or define('MODULE_CAPTCHA_CODE_LENGTH', 6);
defined('MODULE_CAPTCHA_LOGGED_IN') or define('MODULE_CAPTCHA_LOGGED_IN', 'True');

define('EMAIL_VERIFY_VALID_TIME', 24*60*60);

// without outgoing mails nobody could confirm
$email_verify = (defined('ACCOUNT_EMAIL_VERIFY') && in_array(ACCOUNT_EMAIL_VERIFY, array('optional', 'required')) && SEND_EMAILS == 'true') ? ACCOUNT_EMAIL_VERIFY : 'false';

// confirm the email address, the link must work without a login and in any browser
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
    $check_customer_query = xtc_db_query("SELECT customers_email_verify_key,
                                                 customers_email_verify_time
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".$verify_customers_id."'
                                             AND account_type = '0'
                                             AND customers_email_verified IS NULL
                                             AND customers_email_verify_key != ''");
    $check_customer = xtc_db_fetch_array($check_customer_query);

    if (is_array($check_customer)
        && hash_equals($check_customer['customers_email_verify_key'], hash('sha256', $verify_key))
        && time() <= (int)strtotime((string)$check_customer['customers_email_verify_time']) + EMAIL_VERIFY_VALID_TIME
        )
    {
      // only one of parallel requests with the same link confirms
      xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                       SET customers_email_verified = now(),
                           customers_email_verify_key = '',
                           customers_email_verify_time = NULL,
                           customers_email_verify_count = 0
                     WHERE customers_id = '".$verify_customers_id."'
                       AND customers_email_verify_key = '".xtc_db_input($check_customer['customers_email_verify_key'])."'");
      $verify_ok = (xtc_db_affected_rows() == 1);
    }
  }

  if ($verify_ok === true) {
    if (isset($_SESSION['email_verify_pending']) && (int)$_SESSION['email_verify_pending'] == $verify_customers_id) {
      unset($_SESSION['email_verify_pending'], $_SESSION['email_verify_pending_time']);
    }
    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] == $verify_customers_id) {
      $messageStack->add_session('account', SUCCESS_EMAIL_VERIFIED, 'success');
      xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }
    $messageStack->add_session('login', SUCCESS_EMAIL_VERIFIED, 'success');
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  if (isset($_SESSION['customer_id'])) {
    $messageStack->add_session('account', ERROR_EMAIL_VERIFY_LINK_INVALID);
    xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  }
  $messageStack->add_session('login', ERROR_EMAIL_VERIFY_LINK_INVALID);
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// send the confirmation link again, the answer is always the same
if (isset($_POST['action']) && $_POST['action'] == 'verify_resend' && $_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once (DIR_FS_INC.'secure_form.inc.php');
  require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
  require_once (DIR_FS_INC.'send_email_verify_mail.inc.php');

  // the password version of the session or the blocked login must still be current
  $resend_customers_id = 0;
  $resend_time = -1;
  if (isset($_SESSION['customer_id'])) {
    $resend_customers_id = (int)$_SESSION['customer_id'];
    $resend_time = (int)$_SESSION['customer_time'];
  } elseif (isset($_SESSION['email_verify_pending'])) {
    $resend_customers_id = (int)$_SESSION['email_verify_pending'];
    $resend_time = isset($_SESSION['email_verify_pending_time']) ? (int)$_SESSION['email_verify_pending_time'] : -1;
  }

  if (check_secure_form($_POST) === true && $resend_customers_id > 0 && $email_verify != 'false') {
    $check_customer_query = xtc_db_query("SELECT customers_email_address
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".$resend_customers_id."'
                                             AND account_type = '0'
                                             AND customers_email_verified IS NULL
                                             AND customers_email_verify_key != ''
                                             AND customers_password_time = '".$resend_time."'");
    $check_customer = xtc_db_fetch_array($check_customer_query);

    if (is_array($check_customer)) {
      $email_token = xtc_random_charcode(32);

      // limits per account: 60 seconds between requests, 5 requests until 15 minutes pass without one
      $min_gap = date('Y-m-d H:i:s', time() - 60);
      $count_reset = date('Y-m-d H:i:s', time() - 15 * 60);
      $reset_expr = "(customers_email_verify_time IS NULL OR customers_email_verify_time <= '".$count_reset."')";

      // customers_email_verify_count comes first, MySQL evaluates the assignments left to right
      xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                       SET customers_email_verify_count = IF(".$reset_expr.", 1, customers_email_verify_count + 1),
                           customers_email_verify_key = '".xtc_db_input(hash('sha256', $email_token))."',
                           customers_email_verify_time = '".date('Y-m-d H:i:s')."'
                     WHERE customers_id = '".$resend_customers_id."'
                       AND customers_email_verified IS NULL
                       AND customers_email_verify_key != ''
                       AND customers_password_time = '".$resend_time."'
                       AND (customers_email_verify_time IS NULL OR customers_email_verify_time <= '".$min_gap."')
                       AND (".$reset_expr." OR customers_email_verify_count < 5)");

      if (xtc_db_affected_rows() === 1) {
        send_email_verify_mail($resend_customers_id, $check_customer['customers_email_address'], $email_token, EMAIL_VERIFY_VALID_TIME);
      }
    }
  }

  $messageStack->add_session((isset($_SESSION['customer_id']) ? 'account' : 'login'), sprintf(SUCCESS_EMAIL_VERIFY_RESENT, (EMAIL_VERIFY_VALID_TIME / 3600)), 'success');
  xtc_redirect(xtc_href_link((isset($_SESSION['customer_id']) ? FILENAME_ACCOUNT : FILENAME_LOGIN), '', 'SSL'));
}

// correct an unconfirmed address, the password was checked at the blocked login
if (isset($_POST['action']) && $_POST['action'] == 'verify_correct' && $_SERVER['REQUEST_METHOD'] == 'POST') {
  require_once (DIR_FS_INC.'secure_form.inc.php');
  require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
  require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
  require_once (DIR_FS_INC.'send_email_verify_mail.inc.php');

  $correct_customers_id = isset($_SESSION['email_verify_pending']) ? (int)$_SESSION['email_verify_pending'] : 0;
  $correct_time = isset($_SESSION['email_verify_pending_time']) ? (int)$_SESSION['email_verify_pending_time'] : -1;

  // a password change after the blocked login withdraws the permission
  $pending_valid = false;
  if ($correct_customers_id > 0) {
    $pending_query = xtc_db_query("SELECT customers_id
                                     FROM ".TABLE_CUSTOMERS."
                                    WHERE customers_id = '".$correct_customers_id."'
                                      AND account_type = '0'
                                      AND customers_email_verified IS NULL
                                      AND customers_email_verify_key != ''
                                      AND customers_password_time = '".$correct_time."'");
    $pending_valid = (xtc_db_num_rows($pending_query) == 1);
  }
  $email_address = isset($_POST['email_address']) ? trim(xtc_db_prepare_input($_POST['email_address'])) : '';
  $confirm_email_address = isset($_POST['confirm_email_address']) ? trim(xtc_db_prepare_input($_POST['confirm_email_address'])) : '';

  $correct_error = '';
  if (check_secure_form($_POST) === false || $correct_customers_id < 1 || $email_verify == 'false') {
    $correct_error = ENTRY_TOKEN_ERROR;
  } elseif ($pending_valid === false) {
    unset($_SESSION['email_verify_pending'], $_SESSION['email_verify_pending_time']);
    $correct_error = TEXT_RELOGIN_NEEDED;
  } elseif (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
    $correct_error = ENTRY_EMAIL_ADDRESS_ERROR;
  } elseif (xtc_validate_email($email_address) == false) {
    $correct_error = ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
  } elseif ($email_address != $confirm_email_address) {
    $correct_error = ENTRY_EMAIL_ERROR_NOT_MATCHING;
  } else {
    // no other request may take the same address between the check and the change
    $email_lock = 'customers_email_'.md5(mb_strtolower(trim($email_address)));
    $lock_query = xtc_db_query("SELECT GET_LOCK('".xtc_db_input($email_lock)."', 10) AS email_lock");
    $lock = xtc_db_fetch_array($lock_query);
    if (!isset($lock['email_lock']) || $lock['email_lock'] != '1') {
      $correct_error = ERROR_EMAIL_VERIFY_CORRECT_WAIT;
    } else {
      $check_email_query = xtc_db_query("SELECT count(*) as total
                                           FROM ".TABLE_CUSTOMERS."
                                          WHERE customers_email_address = '".xtc_db_input($email_address)."'
                                            AND account_type = '0'
                                            AND customers_id != '".$correct_customers_id."'");
      $check_email = xtc_db_fetch_array($check_email_query);
      if ($check_email['total'] > 0) {
        $correct_error = ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
      }
    }
  }

  if ($correct_error == '') {
    $email_token = xtc_random_charcode(32);

    // no 60 second gap, a typo is often noticed right after the registration; the count limit stays
    $count_reset = date('Y-m-d H:i:s', time() - 15 * 60);
    $reset_expr = "(customers_email_verify_time IS NULL OR customers_email_verify_time <= '".$count_reset."')";

    // customers_email_verify_count comes first, MySQL evaluates the assignments left to right
    // reset links sent to the old address must not confirm the new one
    xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                     SET customers_email_verify_count = IF(".$reset_expr.", 1, customers_email_verify_count + 1),
                         customers_email_address = '".xtc_db_input($email_address)."',
                         customers_email_verify_key = '".xtc_db_input(hash('sha256', $email_token))."',
                         customers_email_verify_time = '".date('Y-m-d H:i:s')."',
                         password_request_key = '',
                         password_request_time = NULL,
                         customers_last_modified = now()
                   WHERE customers_id = '".$correct_customers_id."'
                     AND account_type = '0'
                     AND customers_email_verified IS NULL
                     AND customers_email_verify_key != ''
                     AND customers_password_time = '".$correct_time."'
                     AND (".$reset_expr." OR customers_email_verify_count < 5)");

    if (xtc_db_affected_rows() === 1) {
      send_email_verify_mail($correct_customers_id, $email_address, $email_token, EMAIL_VERIFY_VALID_TIME);
      $messageStack->add_session('login', sprintf(SUCCESS_EMAIL_VERIFY_CORRECTED, (EMAIL_VERIFY_VALID_TIME / 3600)), 'success');
    } else {
      $correct_error = ERROR_EMAIL_VERIFY_CORRECT_WAIT;
    }
  }

  if (isset($email_lock)) {
    xtc_db_query("SELECT RELEASE_LOCK('".xtc_db_input($email_lock)."')");
  }

  if ($correct_error != '') {
    $messageStack->add_session('login', $correct_error);
  }
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if (isset($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

$account_options = ACCOUNT_OPTIONS;
$products = $_SESSION['cart']->get_products();
for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
  if (preg_match('/^GIFT/', addslashes($products[$i]['model']))) {
    $account_options = 'account';
    break;
  }
}
if ($account_options == 'guest') {
  xtc_redirect(xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL'));
}

// include needed functions
require_once (DIR_FS_INC.'xtc_get_country_list.inc.php');
require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
require_once (DIR_FS_INC.'xtc_get_geo_zone_code.inc.php');
require_once (DIR_FS_INC.'xtc_write_user_info.inc.php');
require_once (DIR_FS_INC.'get_customers_gender.inc.php');
require_once (DIR_FS_INC.'parse_multi_language_value.inc.php');
require_once (DIR_FS_INC.'generate_customers_cid.inc.php');
require_once (DIR_FS_INC.'check_country_required_zones.inc.php');
require_once (DIR_FS_INC.'secure_form.inc.php');
require_once (DIR_FS_INC.'write_customers_session.inc.php');
require_once (DIR_FS_INC.'xtc_email_address_lock.inc.php');
require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
require_once (DIR_FS_INC.'send_email_verify_mail.inc.php');

// include needed classes
require_once (DIR_FS_EXTERNAL.'password_policy/password_policy.php');
require_once (DIR_WS_CLASSES.'modified_captcha.php');

// create smarty elements
$smarty = new Smarty();

$mod_captcha = $_mod_captcha_class::getInstance();

if (!isset($_POST['country'])) {
  $country = isset($_SESSION['country']) ? (int)$_SESSION['country'] : STORE_COUNTRY; //is country_id (int)
} else {
  $country = (int)$_POST['country'];
}
$privacy = isset($_POST['privacy']) && $_POST['privacy'] == 'privacy' ? true : false;

$required_zones = check_country_required_zones($country);

$process = false;
if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
  $process = true;

  $valid_params = array(
    'gender',
    'firstname',
    'lastname',
    'street_address',
    'postcode',
    'city',
    'country',
    'state',
    'company',
    'suburb',
    'email_address',
    'confirm_email_address',
    'vat',
    'password',
    'confirmation',
    'telephone',
    'fax',
    'newsletter',
    'dob',
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
    $messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
  }

  if (mb_strlen($lastname, $_SESSION['language_charset']) < ENTRY_LAST_NAME_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_LAST_NAME_ERROR);
  }

  if (ACCOUNT_DOB == 'true') {
    $date = xtc_date_raw($dob);
    if (is_numeric($date) == false
        || strlen($date) != 8
        || checkdate(substr($date, 4, 2), substr($date, 6, 2), substr($date, 0, 4)) == false
        )
    {
      $error = true;
      $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
    }
  }

  // New VAT Check
  if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
    if (!isset($vat)) $vat = '';
    require_once(DIR_WS_CLASSES.'vat_validation.php');
    $vatID = new vat_validation($vat, '', '', (int)$country);
    $customers_status = $vatID->vat_info['status'];
    $customers_vat_id_status = isset($vatID->vat_info['vat_id_status']) ? $vatID->vat_info['vat_id_status'] : '';
    if (isset($vatID->vat_info['error']) && $vatID->vat_info['error']==1){
      $messageStack->add('create_account', ENTRY_VAT_ERROR);
      $error = true;
    }
  }

  // email check
  if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
  } elseif (xtc_validate_email($email_address) == false) {
    $error = true;
    $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
  } elseif ($email_address != $confirm_email_address) {
    $error = true;
    $messageStack->add('create_account', ENTRY_EMAIL_ERROR_NOT_MATCHING);
  } else {
    $check_email_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CUSTOMERS."
                                        WHERE customers_email_address = '".xtc_db_input($email_address)."'
                                          AND account_type = '0'");
    $check_email = xtc_db_fetch_array($check_email_query);
    if ($check_email['total'] > 0) {
      $error = true;
      $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }
  }

  if (mb_strlen($street_address, $_SESSION['language_charset']) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_STREET_ADDRESS_ERROR);
  }

  if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_POST_CODE_ERROR);
  }

  if (mb_strlen($city, $_SESSION['language_charset']) < ENTRY_CITY_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_CITY_ERROR);
  }

  if (is_numeric($country) == false) {
    $error = true;
    $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
  } else {
    $check_country_query = xtc_db_query("SELECT countries_id
                                           FROM ".TABLE_COUNTRIES."
                                          WHERE countries_id = '".(int)$country."'
                                            AND status = '1'");
    if (xtc_db_num_rows($check_country_query) < 1) {
      $error = true;
      $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
    }
  }

  $entry_state_has_zones = false;
  if (ACCOUNT_STATE == 'true') {
    $zone_id = 0;
    $check_query = xtc_db_query("SELECT count(*) AS total 
                                   FROM ".TABLE_ZONES." z
                                   JOIN ".TABLE_COUNTRIES." c 
                                        ON c.countries_id = z.zone_country_id 
                                          AND c.required_zones = '1' 
                                  WHERE z.zone_country_id = '".(int)$country."'");
    $check = xtc_db_fetch_array($check_query);
    $entry_state_has_zones = ($check['total'] > 0);
    if ($entry_state_has_zones == true) {
        $zone_query = xtc_db_query("SELECT DISTINCT zone_id
                                               FROM ".TABLE_ZONES."
                                              WHERE zone_country_id = '".(int)$country ."'
                                                AND (zone_id = '" . (int)$state . "'
                                                     OR zone_code = '" . xtc_db_input($state) . "'
                                                     OR zone_name LIKE '" . xtc_db_input($state) . "%'
                                                     )");
      if (xtc_db_num_rows($zone_query) == 1) {
        $zone = xtc_db_fetch_array($zone_query);
        $zone_id = $zone['zone_id'];
        $state = '';
      } else {
        $error = true;
        $messageStack->add('create_account', ENTRY_STATE_ERROR_SELECT);
      }
    } else {
      if (!$required_zones) {
        $state = '';
      } elseif (mb_strlen($state, $_SESSION['language_charset']) < ENTRY_STATE_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_STATE_ERROR);
      }
    }
  }

  if (ACCOUNT_TELEPHONE_OPTIONAL == 'false' && strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_TELEPHONE_NUMBER_ERROR);
  }

  $policy = new password_policy();
  if (!$policy->validate($password)) {
    $error = true;
    foreach ($policy->get_errors() as $k => $error) {
      $messageStack->add('create_account', $error);
    }
  }
  elseif ($password != $confirmation) {
    $error = true;
    $messageStack->add('create_account', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
  }

  if (DISPLAY_PRIVACY_CHECK == 'true' && empty($privacy)) {
    $error = true;
    $messageStack->add('create_account', ENTRY_PRIVACY_ERROR);
  }

  if (in_array('create_account', $use_captcha)) {
    if ($mod_captcha->validate((isset($_POST['vvcode'])) ? $_POST['vvcode'] : '') !== true) {
      $messageStack->add('create_account', strip_tags(ERROR_VVCODE, '<b><strong>'));
      $error = true;
    }
  }
  
  if (check_secure_form($_POST) === false) {
    $messageStack->add('create_account', ENTRY_TOKEN_ERROR);
    $error = true;
  }
  
  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/create_account_check_data/','php') as $file) require ($file);

  if ($error == false) {
    $customers_password_time = time();
    
    if (!isset($customers_status) || (int)$customers_status == 0) {
      if (DEFAULT_CUSTOMERS_STATUS_ID != 0) {
        $customers_status = DEFAULT_CUSTOMERS_STATUS_ID;
      } else {
        $customers_status = 2;
      }
    }

    $sql_data_array = array(
      'customers_cid' => generate_customers_cid(true),
      'customers_status' => (int)$customers_status,
      'customers_firstname' => $firstname,
      'customers_lastname' => $lastname,
      'customers_email_address' => $email_address,
      'customers_telephone' => ((isset($telephone)) ? $telephone : ''),
      'customers_fax' => ((isset($fax)) ? $fax : ''),
      'customers_newsletter' => ((isset($newsletter)) ? (int)$newsletter : 0),
      'customers_password' => xtc_encrypt_password($password),
      'customers_password_time' => $customers_password_time,
      'customers_date_added' => 'now()',
      'customers_last_modified' => 'now()',
    );

    if (ACCOUNT_GENDER == 'true') {
      $sql_data_array['customers_gender'] = $gender;
    }
    if (ACCOUNT_DOB == 'true') {
      $sql_data_array['customers_dob'] = xtc_date_raw($dob);
    }
    if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
      $sql_data_array['customers_vat_id'] = $vat;
      $sql_data_array['customers_vat_id_status'] = $customers_vat_id_status;
    }

    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/create_account_customer_data/','php') as $file) require ($file);

    // only the hash is stored, the token goes into the link
    $email_verify_token = '';
    if ($email_verify != 'false') {
      $email_verify_token = xtc_random_charcode(32);
      $sql_data_array['customers_email_verify_key'] = hash('sha256', $email_verify_token);
      $sql_data_array['customers_email_verify_time'] = date('Y-m-d H:i:s');
      $sql_data_array['customers_email_verify_count'] = 1;
    }
    
    // check email again, a parallel registration or address change must not take the same address
    $check_email = array('total' => 1);
    if (xtc_email_address_lock($email_address) === true) {
      $check_email_query = xtc_db_query("SELECT count(*) as total
                                           FROM ".TABLE_CUSTOMERS."
                                          WHERE customers_email_address = '".xtc_db_input($email_address)."'
                                            AND account_type = '0'");
      $check_email = xtc_db_fetch_array($check_email_query);
    }
    if ($check_email['total'] == 0) {
      xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);

      $new_customer_id = xtc_db_insert_id();
      xtc_email_address_lock('', true);

      // required: the login follows after the confirmation
      $auto_login = ($email_verify != 'required');
      if ($auto_login === true) {
        $_SESSION['customer_id'] = $new_customer_id;
        $_SESSION['customer_time'] = $customers_password_time;
      }
      
      $sql_data_array = array(
        'customers_id' => $new_customer_id,
        'entry_firstname' => $firstname,
        'entry_lastname' => $lastname,
        'entry_street_address' => $street_address,
        'entry_postcode' => $postcode,
        'entry_city' => $city,
        'entry_country_id' => (int)$country,
        'address_date_added' => 'now()',
        'address_last_modified' => 'now()'
      );

      if (ACCOUNT_GENDER == 'true') {
        $sql_data_array['entry_gender'] = $gender;
      }
      if (ACCOUNT_COMPANY == 'true') {
        $sql_data_array['entry_company'] = $company;
      }
      if (ACCOUNT_SUBURB == 'true') {
        $sql_data_array['entry_suburb'] = $suburb;
      }
      if (ACCOUNT_STATE == 'true') {
        $sql_data_array['entry_zone_id'] = (isset($zone_id) ? (int)$zone_id : 0);
        $sql_data_array['entry_state'] = ((isset($state) && !empty($state)) ? $state : '');
      }

      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/create_account_address_data/','php') as $file) require ($file);

      xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

      $address_id = xtc_db_insert_id();

      xtc_db_query("UPDATE ".TABLE_CUSTOMERS." 
                       SET customers_default_address_id = '".(int)$address_id."' 
                     WHERE customers_id = '".(int)$new_customer_id."'");
    
      $sql_data_array = array(
        'customers_info_id' => (int)$new_customer_id,
        'customers_info_number_of_logons' => (($auto_login === true) ? '1' : '0'),
        'customers_info_date_account_created' => 'now()',
      );
      if ($auto_login === true) {
        $sql_data_array['customers_info_date_of_last_logon'] = 'now()';
      }
      xtc_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);

      if ($auto_login === true) {
        if (SESSION_RECREATE == 'True') {
          xtc_session_recreate();
        }

        // write customers session
        write_customers_session((int)$new_customer_id);
      }

      // user info
			xtc_write_user_info((int)$new_customer_id);

      // restore cart contents
      if ($auto_login === true) {
        $_SESSION['cart']->restore_contents();
      }

      // build the message content
      $name = $firstname.' '.$lastname;

      // load data into array
      $module_content = array(
        'MAIL_NAME' => $name,
        'MAIL_REPLY_ADDRESS' => parse_multi_language_value(EMAIL_SUPPORT_REPLY_ADDRESS, $_SESSION['language_code']),
        'MAIL_GENDER' => ((ACCOUNT_GENDER == 'true') ? get_customers_gender($gender) : '')
      );

      // assign data to smarty
      $smarty->assign('language', $_SESSION['language']);
      $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
      $smarty->assign('content', $module_content);
      $smarty->assign('GENDER', ((ACCOUNT_GENDER == 'true') ? $gender : ''));
      $smarty->assign('FIRSTNAME', $firstname);
      $smarty->assign('LASTNAME', $lastname);
      $smarty->assign('NAME', $name);

      // campaign tracking
      if (isset($_SESSION['tracking']['refID'])) {
        $campaign_check = xtc_db_query("SELECT campaigns_id, 
                                               campaigns_leads
                                          FROM ".TABLE_CAMPAIGNS."
                                         WHERE campaigns_refID = '".xtc_db_input($_SESSION['tracking']['refID'])."'");
        if (xtc_db_num_rows($campaign_check) > 0) {
          $campaign = xtc_db_fetch_array($campaign_check);
        
          xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                           SET refferers_id = '".(int)$campaign['campaigns_id']."'
                         WHERE customers_id = '".(int)$new_customer_id."'");
        
          xtc_db_query("UPDATE ".TABLE_CAMPAIGNS."
                           SET campaigns_leads = campaigns_leads + 1
                         WHERE campaigns_id = '".(int)$campaign['campaigns_id']."'");
        }
      }
      
      $send_mail = ((SEND_MAIL_ACCOUNT_CREATED == 'true') ? true : false);
      
      // GV Code - CREDIT CLASS CODE BLOCK
      if (ACTIVATE_GIFT_SYSTEM == 'true' 
          && ((defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true') 
              || (defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && MODULE_ORDER_TOTAL_COUPON_STATUS == 'true')
              )
          )
      {
        $check_query = xtc_db_query("SELECT *
                                       FROM ".TABLE_COUPON_EMAIL_TRACK."
                                      WHERE emailed_to = '".xtc_db_input($email_address)."'
                                        AND sent_firstname = 'Registration'");
        if (xtc_db_num_rows($check_query) < 1) {
          if (NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
            $coupon_code = create_coupon_code();
            $sql_data_array = array(
              'coupon_code' => $coupon_code,
              'coupon_type' => 'G',
              'coupon_amount' => NEW_SIGNUP_GIFT_VOUCHER_AMOUNT,
              'date_created' => 'now()'
            );
            xtc_db_perform(TABLE_COUPONS, $sql_data_array);

            $coupon_id = xtc_db_insert_id();

            if (!isset($lng) || (isset($lng) && !is_object($lng))) {
              require_once(DIR_WS_CLASSES . 'language.php');
              $lng = new language;
            }
          
            foreach ($lng->catalog_languages as $languages) {
              $sql_data_array = array(
                'coupon_id' => $coupon_id,
                'language_id' => $languages['id'],
                'coupon_name' => 'Registration',
                'coupon_description' => '',
              );
              xtc_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_array);
            }
                    
            $sql_data_array = array(
              'coupon_id' => $coupon_id,
              'customer_id_sent' => '0',
              'sent_firstname' => 'Registration',
              'emailed_to' => $email_address,
              'date_sent' => 'now()'
            );
            xtc_db_perform(TABLE_COUPON_EMAIL_TRACK, $sql_data_array);

            $smarty->assign('SEND_GIFT', 'true');
            $smarty->assign('GIFT_AMMOUNT', $xtPrice->xtcFormat(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT, true));
            $smarty->assign('GIFT_CODE', $coupon_code);
            $smarty->assign('GIFT_LINK', xtc_href_link(FILENAME_GV_REDEEM, 'gv_no='.$coupon_code, 'NONSSL', false));
            $send_mail = true;
          }
          
          if (NEW_SIGNUP_DISCOUNT_COUPON != '') {
            $coupon_query = xtc_db_query("SELECT * 
                                            FROM ".TABLE_COUPONS." c
                                            JOIN ".TABLE_COUPONS_DESCRIPTION." cd
                                                 ON c.coupon_id = cd.coupon_id
                                                    AND cd.language_id = '".(int)$_SESSION['languages_id']."'
                                           WHERE c.coupon_code = '".xtc_db_input(trim(NEW_SIGNUP_DISCOUNT_COUPON))."'");
            if (xtc_db_num_rows($coupon_query) > 0) {
              $coupon = xtc_db_fetch_array($coupon_query);
        
              $sql_data_array = array(
                'coupon_id' => $coupon['coupon_id'],
                'customer_id_sent' => '0',
                'sent_firstname' => 'Registration',
                'emailed_to' => $email_address,
                'date_sent' => 'now()'
              );
              xtc_db_perform(TABLE_COUPON_EMAIL_TRACK, $sql_data_array);
        
              $smarty->assign('SEND_COUPON', 'true');
              $smarty->assign('COUPON_DESC', $coupon['coupon_description']);
              $smarty->assign('COUPON_CODE', $coupon['coupon_code']);
              $send_mail = true;
            }
          }
        }
      }

      // create templates
      $smarty->caching = 0;
      $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/create_account_mail.html');
      $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/create_account_mail.txt');
    
      if (SEND_EMAILS == 'true' && $send_mail == true) {
        xtc_php_mail(EMAIL_SUPPORT_ADDRESS, 
                     EMAIL_SUPPORT_NAME, 
                     $email_address, 
                     $name, 
                     EMAIL_SUPPORT_FORWARDING_STRING, 
                     EMAIL_SUPPORT_REPLY_ADDRESS, 
                     EMAIL_SUPPORT_REPLY_ADDRESS_NAME, 
                     '', 
                     '', 
                     EMAIL_SUPPORT_SUBJECT, 
                     $html_mail, 
                     $txt_mail);
      }
   
      if (isset($newsletter) && $newsletter == '1') {
        require_once (DIR_WS_CLASSES.'class.newsletter.php');
        $newsletter = new newsletter;
        $newsletter->AddUserAuto($email_address);
      }

      if ($email_verify_token != '') {
        send_email_verify_mail($new_customer_id, $email_address, $email_verify_token, EMAIL_VERIFY_VALID_TIME);
      }

      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/create_account_before_redirect/','php') as $file) require ($file);
      
      if ($auto_login === false) {
        $messageStack->add_session('login', sprintf(SUCCESS_EMAIL_VERIFY_REQUIRED, (EMAIL_VERIFY_VALID_TIME / 3600)), 'success');
        xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
      }

      if ($_SESSION['cart']->count_contents() > 0) {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
      }
      xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    } else {
      xtc_email_address_lock('', true);
      $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }
  }
}

// build breadcrumb
$breadcrumb->add(NAVBAR_TITLE_CREATE_ACCOUNT, xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));

// include header
require (DIR_WS_INCLUDES . 'header.php');

// include boxes
$display_mode = 'createaccount';
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

if ($messageStack->size('create_account') > 0) {
  $smarty->assign('error_message', $messageStack->output('create_account'));
}

$smarty->assign('FORM_ACTION', xtc_draw_form('create_account', xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post').xtc_draw_hidden_field('action', 'process').secure_form('create_account'));

if (ACCOUNT_GENDER == 'true') {
  $smarty->assign('gender', '1');
  $smarty->assign('INPUT_GENDER', xtc_draw_pull_down_menuNote(array('name' => 'gender', 'text' => (xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">'.ENTRY_GENDER_TEXT.'</span>' : '')), get_customers_gender(), ((isset($gender)) ? $gender : ''), 'autocomplete="sex"'));
} else {
  $smarty->assign('gender', '0');
}

$smarty->assign('INPUT_FIRSTNAME', xtc_draw_input_fieldNote(array('name' => 'firstname', 'text' => (xtc_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_FIRST_NAME_TEXT.'</span>' : '')), '', 'autocomplete="given-name"'));
$smarty->assign('INPUT_LASTNAME', xtc_draw_input_fieldNote(array('name' => 'lastname', 'text' => (xtc_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_LAST_NAME_TEXT.'</span>' : '')), '', 'autocomplete="family-name"'));

if (ACCOUNT_DOB == 'true') {
  $smarty->assign('birthdate', '1');
  $smarty->assign('INPUT_DOB', xtc_draw_input_fieldNote(array('name' => 'dob', 'text' => (xtc_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">'.ENTRY_DATE_OF_BIRTH_TEXT.'</span>' : '')), '', 'autocomplete="bday"'));
  $smarty->assign('TEXT_DOB_NOTE', ENTRY_DATE_OF_BIRTH_NOTE);
} else {
  $smarty->assign('birthdate', '0');
}

$smarty->assign('INPUT_EMAIL', xtc_draw_input_fieldNote(array('name' => 'email_address', 'text' => (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), '', 'autocomplete="email"'));
$smarty->assign('INPUT_CONFIRM_EMAIL', xtc_draw_input_fieldNote(array('name' => 'confirm_email_address', 'text' => (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), '', 'autocomplete="email"'));

if (ACCOUNT_COMPANY == 'true') {
  $smarty->assign('company', '1');
  $smarty->assign('INPUT_COMPANY', xtc_draw_input_fieldNote(array('name' => 'company', 'text' => (xtc_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>' : '')), '', 'autocomplete="organization"'));
} else {
  $smarty->assign('company', '0');
}

if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
  $smarty->assign('vat', '1');
  $smarty->assign('INPUT_VAT', xtc_draw_input_fieldNote(array('name' => 'vat', 'text' => (xtc_not_null(ENTRY_VAT_TEXT) ? '<span class="inputRequirement">'.ENTRY_VAT_TEXT.'</span>' : ''))));
  $smarty->assign('TEXT_VAT_NOTE', ENTRY_VAT_NOTE);
} else {
  $smarty->assign('vat', '0');
}

$smarty->assign('INPUT_STREET', xtc_draw_input_fieldNote(array('name' => 'street_address', 'text' => (xtc_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_STREET_ADDRESS_TEXT.'</span>' : '')), '', 'autocomplete="street-address"'));

if (ACCOUNT_SUBURB == 'true') {
  $smarty->assign('suburb', '1');
  $smarty->assign('INPUT_SUBURB', xtc_draw_input_fieldNote(array('name' => 'suburb', 'text' => (xtc_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">'.ENTRY_SUBURB_TEXT.'</span>' : '')), '', 'autocomplete="address-level4"'));
} else {
  $smarty->assign('suburb', '0');
}

$smarty->assign('INPUT_CODE', xtc_draw_input_fieldNote(array('name' => 'postcode', 'text' => (xtc_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">'.ENTRY_POST_CODE_TEXT.'</span>' : '')), '', 'autocomplete="postal-code"'));
$smarty->assign('INPUT_CITY', xtc_draw_input_fieldNote(array('name' => 'city', 'text' => (xtc_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">'.ENTRY_CITY_TEXT.'</span>' : '')), '', 'autocomplete="address-level2"'));

if (ACCOUNT_STATE == 'true') { //important no $required_zones because of ajax loader
  $smarty->assign('state', '1');
  $smarty->assign('display_state', '');
  if ($process == true) {
    if ($entry_state_has_zones == true) {
      $zones_array = array();
      $zones_query = xtDBquery("SELECT zone_id, 
                                       zone_name 
                                  FROM ".TABLE_ZONES." 
                                 WHERE zone_country_id = '".(int)$country."' 
                              ORDER BY zone_name");
      while ($zones_values = xtc_db_fetch_array($zones_query, true)) {
        $zones_array[] = array(
          'id' => $zones_values['zone_id'],
          'text' => $zones_values['zone_name']
        );
      }
      $state_input = xtc_draw_pull_down_menuNote(array('name' => 'state', 'text' => (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')), $zones_array, $zone_id, 'autocomplete="address-level1"');
    } else {
      $state_input = xtc_draw_input_fieldNote(array('name' => 'state', 'text' => (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')), '', 'autocomplete="address-level1"');
      if (!$required_zones) {
        $state_input = '<input type="hidden" value="0" name="state">';
        $smarty->assign('display_state', ' style="display:none"');        
      }
    }
  } else {
    $state_input = xtc_draw_input_fieldNote(array('name' => 'state', 'text' => (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')), '', 'autocomplete="address-level1"');
    if (!$required_zones) {
      $state_input = '<input type="hidden" value="0" name="state">';
      $smarty->assign('display_state', ' style="display:none"');     
    }
  }
  $smarty->assign('INPUT_STATE', $state_input);
} else {
  $smarty->assign('state', '0');
}

if (ACCOUNT_FAX == 'true') {
  $smarty->assign('fax', '1');
  $smarty->assign('INPUT_FAX', xtc_draw_input_fieldNote(array('name' => 'fax', 'text' => (xtc_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">'.ENTRY_FAX_NUMBER_TEXT.'</span>' : '')), '', 'autocomplete="fax"'));
} else {
  $smarty->assign('fax', '0');
}

if (in_array('create_account', $use_captcha)) {
  $smarty->assign('VVIMG', $mod_captcha->get_image_code());
  $smarty->assign('INPUT_VVCODE', $mod_captcha->get_input_code());
}
$smarty->assign('SELECT_COUNTRY', xtc_get_country_list(array('name' => 'country', 'text' => (xtc_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">'.ENTRY_COUNTRY_TEXT.'</span>' : '')), (int)$country, 'autocomplete="country"'));
$smarty->assign('INPUT_TEL', xtc_draw_input_fieldNote(array('name' => 'telephone', 'text' => ((ACCOUNT_TELEPHONE_OPTIONAL == 'false' && xtc_not_null(ENTRY_TELEPHONE_NUMBER_TEXT)) ? '<span class="inputRequirement">'.ENTRY_TELEPHONE_NUMBER_TEXT.'</span>' : '')), '', 'autocomplete="tel"'));
$smarty->assign('INPUT_PASSWORD', xtc_draw_password_fieldNote(array('name' => 'password', 'text' => (xtc_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_TEXT.'</span>' : '')), '', 'autocomplete="new-password"'));
$smarty->assign('INPUT_CONFIRMATION', xtc_draw_password_fieldNote(array('name' => 'confirmation', 'text' => (xtc_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_CONFIRMATION_TEXT.'</span>' : '')), '', 'autocomplete="new-password"'));
if (defined('MODULE_NEWSLETTER_STATUS') && MODULE_NEWSLETTER_STATUS == 'true') {
  $smarty->assign('CHECKBOX_NEWSLETTER', xtc_draw_checkbox_field('newsletter', '1', false, 'id="newsletter"').(xtc_not_null(ENTRY_NEWSLETTER_TEXT) ? '<span class="inputRequirement">'.ENTRY_NEWSLETTER_TEXT.'</span>' : ''));
}
if (DISPLAY_PRIVACY_CHECK == 'true') {
  $smarty->assign('PRIVACY_CHECKBOX', xtc_draw_checkbox_field('privacy', 'privacy', $privacy, 'id="privacy"'));
}
$smarty->assign('PRIVACY_LINK', $main->getContentLink(2, MORE_INFO, $request_type));
$smarty->assign('FORM_END', '</form>');
$smarty->assign('language', $_SESSION['language']);
$smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('BUTTON_SUBMIT_SAVE', xtc_image_submit('button_save.gif', IMAGE_BUTTON_SAVE));

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/account/create_account_smarty_data/','php') as $file) require ($file);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/create_account.html');
$smarty->assign('main_content', $main_content);
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
