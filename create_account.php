<?php
/* -----------------------------------------------------------------------------------------
   $Id: create_account.php 2810 2012-04-30 16:16:59Z hhacker $

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

if (isset($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_get_country_list.inc.php');
require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
require_once (DIR_FS_INC.'xtc_get_geo_zone_code.inc.php');
require_once (DIR_FS_INC.'xtc_write_user_info.inc.php');
require_once (DIR_FS_INC.'get_customers_gender.inc.php');
require_once (DIR_FS_INC.'parse_multi_language_value.inc.php');
require_once (DIR_FS_INC.'generate_customers_cid.inc.php');

require_once (DIR_FS_EXTERNAL.'password_policy/password_policy.php');

$country = isset($_POST['country']) ? (int)$_POST['country'] : STORE_COUNTRY;
$privacy = isset($_POST['privacy']) && $_POST['privacy'] == 'privacy' ? 'privacy' : '';

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
    'privacy',
    'dob',
  );

  // prepare variables
  foreach ($_POST as $key => $value) {
    if (!is_object(${$key}) && in_array($key , $valid_params)) {
      ${$key} = xtc_db_prepare_input($value);
    }
  }
  
  $error = false;

  if (ACCOUNT_GENDER == 'true' && $gender == '') {
    $error = true;
    $messageStack->add('create_account', ENTRY_GENDER_ERROR);
  }

  if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
  }

  if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_LAST_NAME_ERROR);
  }

  if (ACCOUNT_DOB == 'true' && ( is_numeric(xtc_date_raw($dob)) == false ||
      (@checkdate(substr(xtc_date_raw($dob), 4, 2), substr(xtc_date_raw($dob), 6, 2), substr(xtc_date_raw($dob), 0, 4)) == false))) {
    $error = true;
    $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
  }

  // New VAT Check
  if (ACCOUNT_COMPANY_VAT_CHECK == 'true'){
    require_once(DIR_WS_CLASSES.'vat_validation.php');
    $vatID = new vat_validation($vat, '', '', $country);
    $customers_status = $vatID->vat_info['status'];
    $customers_vat_id_status = isset($vatID->vat_info['vat_id_status']) ? $vatID->vat_info['vat_id_status'] : '';
    if (isset($vatID->vat_info['error']) && $vatID->vat_info['error']==1){
      $messageStack->add('create_account', ENTRY_VAT_ERROR);
      $error = true;
    }
  }

  // xs:booster prefill (customer group)
  if(isset($_SESSION['xtb0']['DEFAULT_CUSTOMER_GROUP']) && $_SESSION['xtb0']['DEFAULT_CUSTOMER_GROUP'] != '') {
    $customers_status = $_SESSION['xtb0']['DEFAULT_CUSTOMER_GROUP'];
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
      $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
    }
  }

  if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_STREET_ADDRESS_ERROR);
  }

  if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_POST_CODE_ERROR);
  }

  if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
    $error = true;
    $messageStack->add('create_account', ENTRY_CITY_ERROR);
  }

  if (is_numeric($country) == false) {
    $error = true;
    $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
  }

  if (ACCOUNT_STATE == 'true') {
    $zone_id = 0;
    $check_query = xtc_db_query("SELECT count(*) AS total FROM ".TABLE_ZONES." WHERE zone_country_id = '".(int)$country."'");
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
      } else {
        $error = true;
        $messageStack->add('create_account', ENTRY_STATE_ERROR_SELECT);
      }
    } else {
      if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
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

  if(isset($customers_status)) {
    $customers_status = (int)$customers_status;
  }

  if (!isset($customers_status) || $customers_status == 0) {
    if (DEFAULT_CUSTOMERS_STATUS_ID != 0) {
        $customers_status = DEFAULT_CUSTOMERS_STATUS_ID;
    } else {
        $customers_status = 2;
    }
  }

  if ($error == false) {
    $sql_data_array = array('customers_cid' => generate_customers_cid(true),
                            'customers_vat_id' => $vat,
                            'customers_vat_id_status' => $customers_vat_id_status,
                            'customers_status' => $customers_status,
                            'customers_firstname' => $firstname,
                            'customers_lastname' => $lastname,
                            'customers_email_address' => $email_address,
                            'customers_telephone' => $telephone,
                            'customers_fax' => $fax,
                            'customers_newsletter' => (int)$newsletter,
                            'customers_password' => xtc_encrypt_password($password),
                            'customers_date_added' => 'now()',
                            'customers_last_modified' => 'now()',
                            );

    if (ACCOUNT_GENDER == 'true') {
      $sql_data_array['customers_gender'] = $gender;
    }
    if (ACCOUNT_DOB == 'true') {
      $sql_data_array['customers_dob'] = xtc_date_raw($dob);
    }
    xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);

    $_SESSION['customer_id'] = xtc_db_insert_id();
    xtc_write_user_info($_SESSION['customer_id']);
    
    $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
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
      $sql_data_array['entry_zone_id'] = (int)$zone_id;
      $sql_data_array['entry_state'] = $state;
    }

    xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

    $address_id = xtc_db_insert_id();

    xtc_db_query("UPDATE ".TABLE_CUSTOMERS." 
                     SET customers_default_address_id = '".(int)$address_id."' 
                   WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
    
    $sql_data_array = array('customers_info_id' => (int)$_SESSION['customer_id'],
                            'customers_info_number_of_logons' => '1',
                            'customers_info_date_account_created' => 'now()'
                            );
    xtc_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);                       

    if (SESSION_RECREATE == 'True') {
      xtc_session_recreate();
    }

    $_SESSION['customer_first_name'] = $firstname;
    $_SESSION['customer_last_name'] = $lastname;
    $_SESSION['customer_default_address_id'] = $address_id;
    $_SESSION['customer_country_id'] = $country;
    $_SESSION['customer_zone_id'] = $zone_id;
    $_SESSION['customer_vat_id'] = $vat;

    // session gender
    if (ACCOUNT_GENDER == 'true') {
      $_SESSION['customer_gender'] = $gender;
    }
    
    // restore cart contents
    $_SESSION['cart']->restore_contents();

    // build the message content
    $name = $firstname.' '.$lastname;

    // load data into array
    $module_content = array('MAIL_NAME' => $name,
                            'MAIL_REPLY_ADDRESS' => parse_multi_language_value(EMAIL_SUPPORT_REPLY_ADDRESS, $_SESSION['language_code']),
                            'MAIL_GENDER' => get_customers_gender($gender));

    // assign data to smarty
    $smarty->assign('language', $_SESSION['language']);
    $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
    $smarty->assign('content', $module_content);
    $smarty->assign('GENDER', get_customers_gender($gender));
    $smarty->assign('LASTNAME',$lastname);

	  // campaign tracking
    if (isset($_SESSION['tracking']['refID'])) {
      $refID = $leads = 0;
      $campaign_check = xtc_db_query("SELECT campaigns_id, 
                                             campaigns_leads
                                        FROM ".TABLE_CAMPAIGNS."
                                       WHERE campaigns_refID = '".$_SESSION['tracking']['refID']."'");
      if (xtc_db_num_rows($campaign_check) > 0) {
        $campaign = xtc_db_fetch_array($campaign_check);
        $refID = $campaign['campaigns_id'];
		    $leads = $campaign['campaigns_leads'];
      }
      $leads++;
      xtc_db_query("UPDATE " . TABLE_CUSTOMERS . "
	                     SET refferers_id = '".$refID."'
                     WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
      xtc_db_query("UPDATE " . TABLE_CAMPAIGNS . "
                       SET campaigns_leads = '".$leads."'
                     WHERE campaigns_id = '".$refID."'");
    }

    // GV Code - CREDIT CLASS CODE BLOCK
    if (ACTIVATE_GIFT_SYSTEM == 'true') {
      if (NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
        $coupon_code = create_coupon_code();
        $sql_data_array = array('coupon_code' => $coupon_code,
                                'coupon_type' => 'G',
                                'coupon_amount' => NEW_SIGNUP_GIFT_VOUCHER_AMOUNT,
                                'date_created' => $email_address
                                );
        xtc_db_perform(TABLE_COUPONS, $sql_data_array);

        $insert_id = xtc_db_insert_id($insert_query);
        $sql_data_array = array('coupon_id' => $insert_id,
                                'customer_id_sent' => '0',
                                'sent_firstname' => 'Admin',
                                'emailed_to' => $email_address,
                                'date_sent' => 'now()'
                                );
        xtc_db_perform(TABLE_COUPON_EMAIL_TRACK, $sql_data_array);

        $smarty->assign('SEND_GIFT', 'true');
        $smarty->assign('GIFT_AMMOUNT', $xtPrice->xtcFormat(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT, true));
        $smarty->assign('GIFT_CODE', $coupon_code);
        $smarty->assign('GIFT_LINK', xtc_href_link(FILENAME_GV_REDEEM, 'gv_no='.$coupon_code, 'NONSSL', false));
      }
      if (NEW_SIGNUP_DISCOUNT_COUPON != '') {
        $coupon_code = NEW_SIGNUP_DISCOUNT_COUPON;
        $coupon_query = xtc_db_query("SELECT * 
                                        FROM ".TABLE_COUPONS." 
                                       WHERE coupon_code = '".xtc_db_input($coupon_code)."'");
        $coupon = xtc_db_fetch_array($coupon_query);
        $coupon_id = $coupon['coupon_id'];
        $coupon_desc_query = xtc_db_query("SELECT * 
                                             FROM ".TABLE_COUPONS_DESCRIPTION." 
                                            WHERE coupon_id = '".$coupon_id."' 
                                              AND language_id = '".(int)$_SESSION['languages_id']."'");
        $coupon_desc = xtc_db_fetch_array($coupon_desc_query);
        
        $sql_data_array = array('coupon_id' => $coupon_id,
                                'customer_id_sent' => '0',
                                'sent_firstname' => 'Admin',
                                'emailed_to' => $email_address,
                                'date_sent' => 'now()'
                                );
        xtc_db_perform(TABLE_COUPON_EMAIL_TRACK, $sql_data_array);
        
        $smarty->assign('SEND_COUPON', 'true');
        $smarty->assign('COUPON_DESC', $coupon_desc['coupon_description']);
        $smarty->assign('COUPON_CODE', $coupon['coupon_code']);
      }
    }

    // create templates
    $smarty->caching = 0;
    $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/create_account_mail.html');
    $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/create_account_mail.txt');
    
    if (SEND_EMAILS == 'true' && SEND_MAIL_ACCOUNT_CREATED == 'true') {
      xtc_php_mail(EMAIL_SUPPORT_ADDRESS, 
                   EMAIL_SUPPORT_NAME, 
                   $email_address, 
                   $name, 
                   '', 
                   EMAIL_SUPPORT_REPLY_ADDRESS, 
                   EMAIL_SUPPORT_REPLY_ADDRESS_NAME, 
                   '', 
                   '', 
                   EMAIL_SUPPORT_SUBJECT, 
                   $html_mail, 
                   $txt_mail);
    }
    
    // send mail to admin
    if (EMAIL_SUPPORT_FORWARDING_STRING != '') {
      xtc_php_mail(EMAIL_SUPPORT_ADDRESS, 
                   EMAIL_SUPPORT_NAME, 
                   EMAIL_SUPPORT_FORWARDING_STRING, 
                   EMAIL_SUPPORT_NAME, 
                   '',
                   $email_address, 
                   $name, 
                   '', 
                   '', 
                   EMAIL_SUPPORT_SUBJECT, 
                   $html_mail, 
                   $txt_mail);
    }
   
    if ($newsletter == '1') {
      require_once (DIR_WS_CLASSES.'class.newsletter.php');
      $newsletter = new newsletter;
      $newsletter->AddUserAuto($email_address);
    }

    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
  }
}

$breadcrumb->add(NAVBAR_TITLE_CREATE_ACCOUNT, xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
require (DIR_WS_INCLUDES.'header.php');

// xs:booster (v1.041)
if(@isset($_SESSION['xtb0']['tx'][0])) {
  $GLOBALS['gender'] = 'm';
  $GLOBALS['firstname'] = substr($_SESSION['xtb0']['tx'][0]['XTB_EBAY_NAME'],0,strpos($_SESSION['xtb0']['tx'][0]['XTB_EBAY_NAME']," "));
  $GLOBALS['lastname'] = substr($_SESSION['xtb0']['tx'][0]['XTB_EBAY_NAME'],strpos($_SESSION['xtb0']['tx'][0]['XTB_EBAY_NAME']," ")+1,strlen($_SESSION['xtb0']['tx'][0]['XTB_EBAY_NAME']));
  $GLOBALS['street_address'] =  $_SESSION['xtb0']['tx'][0]['XTB_EBAY_STREET'];
  $GLOBALS['postcode'] = $_SESSION['xtb0']['tx'][0]['XTB_EBAY_POSTALCODE'];
  $GLOBALS['city'] = $_SESSION['xtb0']['tx'][0]['XTB_EBAY_CITY'];
  $GLOBALS['country'] = $_SESSION['xtb0']['tx'][0]['XTB_EBAY_COUNTRYNAME'];
  $GLOBALS['email_address'] = $_SESSION['xtb0']['tx'][0]['XTB_EBAY_EMAIL'];
  $GLOBALS['telephone'] = $_SESSION['xtb0']['tx'][0]['XTB_EBAY_PHONE'];
}


if ($messageStack->size('create_account') > 0) {
  $smarty->assign('error', $messageStack->output('create_account'));
}

$smarty->assign('FORM_ACTION', xtc_draw_form('create_account', xtc_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post').xtc_draw_hidden_field('action', 'process'));

if (ACCOUNT_GENDER == 'true') {
  $smarty->assign('gender', '1');
  $smarty->assign('INPUT_MALE', xtc_draw_radio_field(array ('name' => 'gender', 'suffix' => MALE), 'm'));
  $smarty->assign('INPUT_FEMALE', xtc_draw_radio_field(array ('name' => 'gender', 'suffix' => FEMALE, 'text' => (xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">'.ENTRY_GENDER_TEXT.'</span>' : '')), 'f'));
  // Gender Dropdown
  $smarty->assign('INPUT_GENDER', xtc_draw_pull_down_menuNote(array ('name' => 'gender', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">'.ENTRY_GENDER_TEXT.'</span>' : '')), get_customers_gender(), $gender));
} else {
  $smarty->assign('gender', '0');
}

$smarty->assign('INPUT_FIRSTNAME', xtc_draw_input_fieldNote(array ('name' => 'firstname', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_FIRST_NAME_TEXT.'</span>' : ''))));
$smarty->assign('INPUT_LASTNAME', xtc_draw_input_fieldNote(array ('name' => 'lastname', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_LAST_NAME_TEXT.'</span>' : ''))));

if (ACCOUNT_DOB == 'true') {
  $smarty->assign('birthdate', '1');
  $smarty->assign('INPUT_DOB', xtc_draw_input_fieldNote(array ('name' => 'dob', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">'.ENTRY_DATE_OF_BIRTH_TEXT.'</span>' : ''))));
} else {
  $smarty->assign('birthdate', '0');
}

$smarty->assign('INPUT_EMAIL', xtc_draw_input_fieldNote(array ('name' => 'email_address', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), '',''));
$smarty->assign('INPUT_CONFIRM_EMAIL', xtc_draw_input_fieldNote(array ('name' => 'confirm_email_address', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), '',''));

if (ACCOUNT_COMPANY == 'true') {
  $smarty->assign('company', '1');
  $smarty->assign('INPUT_COMPANY', xtc_draw_input_fieldNote(array ('name' => 'company', 'text' => '&nbsp;' . (xtc_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>' : ''))));
} else {
  $smarty->assign('company', '0');
}

if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
  $smarty->assign('vat', '1');
  $smarty->assign('INPUT_VAT', xtc_draw_input_fieldNote(array ('name' => 'vat', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_VAT_TEXT) ? '<span class="inputRequirement">'.ENTRY_VAT_TEXT.'</span>' : ''))));
} else {
  $smarty->assign('vat', '0');
}

$smarty->assign('INPUT_STREET', xtc_draw_input_fieldNote(array ('name' => 'street_address', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_STREET_ADDRESS_TEXT.'</span>' : ''))));

if (ACCOUNT_SUBURB == 'true') {
  $smarty->assign('suburb', '1');
  $smarty->assign('INPUT_SUBURB', xtc_draw_input_fieldNote(array ('name' => 'suburb', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">'.ENTRY_SUBURB_TEXT.'</span>' : ''))));
} else {
  $smarty->assign('suburb', '0');
}

$smarty->assign('INPUT_CODE', xtc_draw_input_fieldNote(array ('name' => 'postcode', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">'.ENTRY_POST_CODE_TEXT.'</span>' : ''))));
$smarty->assign('INPUT_CITY', xtc_draw_input_fieldNote(array ('name' => 'city', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">'.ENTRY_CITY_TEXT.'</span>' : ''))));

if (ACCOUNT_STATE == 'true') {
  $smarty->assign('state', '1');
  if ($process == true) {
    if ($entry_state_has_zones == true) {
      $zones_array = array ();
      $zones_query = xtc_db_query("SELECT zone_id, 
                                          zone_name 
                                     FROM ".TABLE_ZONES." 
                                    WHERE zone_country_id = '".(int)$country."' 
                                 ORDER BY zone_name");
      while ($zones_values = xtc_db_fetch_array($zones_query)) {
        $zones_array[] = array ('id' => $zones_values['zone_id'],
                                'text' => $zones_values['zone_name']
                                );
      }
      $state_input = xtc_draw_pull_down_menuNote(array ('name' => 'state', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')), $zones_array, $zone_id);
    } else {
      $state_input = xtc_draw_input_fieldNote(array ('name' => 'state', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')));
    }
  } else {
    $state_input = xtc_draw_input_fieldNote(array ('name' => 'state', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')));
  }
  $smarty->assign('INPUT_STATE', $state_input);
} else {
  $smarty->assign('state', '0');
}

$smarty->assign('SELECT_COUNTRY', xtc_get_country_list(array ('name' => 'country', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">'.ENTRY_COUNTRY_TEXT.'</span>' : '')), $country));
$smarty->assign('INPUT_TEL', xtc_draw_input_fieldNote(array ('name' => 'telephone', 'text' => '&nbsp;'. ((ACCOUNT_TELEPHONE_OPTIONAL == 'false' && xtc_not_null(ENTRY_TELEPHONE_NUMBER_TEXT)) ? '<span class="inputRequirement">'.ENTRY_TELEPHONE_NUMBER_TEXT.'</span>' : ''))));
$smarty->assign('INPUT_FAX', xtc_draw_input_fieldNote(array ('name' => 'fax', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">'.ENTRY_FAX_NUMBER_TEXT.'</span>' : ''))));
$smarty->assign('INPUT_PASSWORD', xtc_draw_password_fieldNote(array ('name' => 'password', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_TEXT.'</span>' : ''))));
$smarty->assign('CHECKBOX_NEWSLETTER', xtc_draw_checkbox_field('newsletter', '1').'&nbsp;'. (xtc_not_null(ENTRY_NEWSLETTER_TEXT) ? '<span class="inputRequirement">'.ENTRY_NEWSLETTER_TEXT.'</span>' : ''));
$smarty->assign('INPUT_CONFIRMATION', xtc_draw_password_fieldNote(array ('name' => 'confirmation', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">'.ENTRY_PASSWORD_CONFIRMATION_TEXT.'</span>' : ''))));
if (DISPLAY_PRIVACY_CHECK == 'true') {
  $smarty->assign('PRIVACY_CHECKBOX', xtc_draw_checkbox_field('privacy', 'privacy', $privacy));
  $smarty->assign('PRIVACY_LINK', $main->getContentLink(2, MORE_INFO, $request_type));
}
$smarty->assign('FORM_END', '</form>');
$smarty->assign('language', $_SESSION['language']);
$smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));

$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/create_account.html');
$smarty->assign('main_content', $main_content);
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');

include ('includes/application_bottom.php');
?>