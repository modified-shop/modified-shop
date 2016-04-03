<?php
/* -----------------------------------------------------------------------------------------
   $Id: account_edit.php 4221 2013-01-11 10:18:52Z gtb-modified $   
   
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(account_edit.php,v 1.63 2003/05/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (account_edit.php,v 1.14 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce - www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_date_short.inc.php');
require_once (DIR_FS_INC.'xtc_image_button.inc.php');
require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
require_once (DIR_FS_INC.'xtc_get_geo_zone_code.inc.php');
require_once (DIR_FS_INC.'xtc_get_customers_country.inc.php');
require_once (DIR_FS_INC.'get_customers_gender.inc.php');

if (!isset($_SESSION['customer_id'])) { 
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if (isset($_SESSION['customer_id']) 
    && $_SESSION['customers_status']['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST
    && GUEST_ACCOUNT_EDIT != 'true'
    )
{ 
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', 'SSL'));
}

// clear session
unset($_SESSION['sendto']);
unset($_SESSION['billto']);
unset($_SESSION['shipping']);
unset($_SESSION['payment']);
unset($_SESSION['delivery_zone']);

if ($_SESSION['customers_status']['customers_status'] == '0') {
	xtc_redirect(xtc_href_link_admin(FILENAME_CUSTOMERS, 'cID='.$_SESSION['customer_id'].'&action=edit', 'SSL'));
}

if (isset ($_POST['action']) && ($_POST['action'] == 'process')) {

  $valid_params = array(
    'gender',
    'firstname',
    'lastname',
    'dob',
    'vat',
    'email_address',
    'confirm_email_address',
    'telephone',
    'fax',
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
    $messageStack->add('account_edit', ENTRY_GENDER_ERROR);
	}

	if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
		$error = true;
		$messageStack->add('account_edit', ENTRY_FIRST_NAME_ERROR);
	}

	if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
		$error = true;
		$messageStack->add('account_edit', ENTRY_LAST_NAME_ERROR);
	}

	if (ACCOUNT_DOB == 'true') {
		if (checkdate(substr(xtc_date_raw($dob), 4, 2), substr(xtc_date_raw($dob), 6, 2), substr(xtc_date_raw($dob), 0, 4)) == false) {
			$error = true;
			$messageStack->add('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
		}
	}

	// New VAT Check
	if (ACCOUNT_COMPANY_VAT_CHECK == 'true'){
    $country = xtc_get_customers_country($_SESSION['customer_id']);
    require_once(DIR_WS_CLASSES.'vat_validation.php');
    $vatID = new vat_validation($vat, $_SESSION['customer_id'], '', $country, ($_SESSION['account_type'] != '0'));
    if (ACCOUNT_COMPANY_VAT_GROUP == 'true' && $vat != '') {
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
   		$messageStack->add('create_account', ENTRY_EMAIL_ERROR_NOT_MATCHING);
	}
  
  if (ACCOUNT_TELEPHONE_OPTIONAL == 'false' && strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
		$error = true;
		$messageStack->add('account_edit', ENTRY_TELEPHONE_NUMBER_ERROR);
	}

	if ($error == false) {
		$sql_data_array = array('customers_vat_id' => $vat, 
		                        'customers_vat_id_status' => $customers_vat_id_status, 
		                        'customers_firstname' => $firstname, 
		                        'customers_lastname' => $lastname, 
		                        'customers_email_address' => $email_address, 
		                        'customers_telephone' => $telephone, 
		                        'customers_fax' => $fax,
		                        'customers_last_modified' => 'now()');

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
		
		xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".(int) $_SESSION['customer_id']."'");
		xtc_db_query("UPDATE ".TABLE_CUSTOMERS_INFO." 
		                 SET customers_info_date_account_last_modified = now() 
		               WHERE customers_info_id = '".(int) $_SESSION['customer_id']."'");

		// reset the session variables
		$customer_first_name = $firstname;
		$messageStack->add_session('account', SUCCESS_ACCOUNT_UPDATED, 'success');
		xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
	}
} else {
	$account_query = xtc_db_query("SELECT *
	                                 FROM ".TABLE_CUSTOMERS." 
	                                WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
	$account = xtc_db_fetch_array($account_query);
}

$breadcrumb->add(NAVBAR_TITLE_1_ACCOUNT_EDIT, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_ACCOUNT_EDIT, xtc_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('FORM_ACTION', xtc_draw_form('account_edit', xtc_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'), 'post', 'onsubmit="return check_form(account_edit);"').xtc_draw_hidden_field('action', 'process'));

if ($messageStack->size('account_edit') > 0)
	$smarty->assign('error', $messageStack->output('account_edit'));

if (ACCOUNT_GENDER == 'true') {
	$smarty->assign('gender', '1');
	$male = ($account['customers_gender'] == 'm') ? true : false;
	$female = !$male;
	$smarty->assign('INPUT_MALE', xtc_draw_radio_field(array ('name' => 'gender', 'suffix' => MALE.'&nbsp;'), 'm', $male));
	$smarty->assign('INPUT_FEMALE', xtc_draw_radio_field(array ('name' => 'gender', 'suffix' => FEMALE.'&nbsp;', 'text' => (xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">'.ENTRY_GENDER_TEXT.'</span>' : '')), 'f', $female));
  // Gender Dropdown
  $gender = ((isset($gender)) ? $gender : $account['customers_gender']);
  $smarty->assign('INPUT_GENDER', xtc_draw_pull_down_menuNote(array ('name' => 'gender', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">'.ENTRY_GENDER_TEXT.'</span>' : '')), get_customers_gender(), $gender));
}

if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
	$smarty->assign('vat', '1');
	$smarty->assign('INPUT_VAT', xtc_draw_input_fieldNote(array ('name' => 'vat', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_VAT_TEXT) ? '<span class="inputRequirement">'.ENTRY_VAT_TEXT.'</span>' : '')), $account['customers_vat_id']));
} else {
	$smarty->assign('vat', '0');
}

$smarty->assign('INPUT_FIRSTNAME', xtc_draw_input_fieldNote(array ('name' => 'firstname', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_FIRST_NAME_TEXT.'</span>' : '')), $account['customers_firstname']));
$smarty->assign('INPUT_LASTNAME', xtc_draw_input_fieldNote(array ('name' => 'lastname', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_LAST_NAME_TEXT.'</span>' : '')), $account['customers_lastname']));
$smarty->assign('csID', $account['customers_cid']);

if (ACCOUNT_DOB == 'true') {
	$smarty->assign('birthdate', '1');
	$smarty->assign('INPUT_DOB', xtc_draw_input_fieldNote(array ('name' => 'dob', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">'.ENTRY_DATE_OF_BIRTH_TEXT.'</span>' : '')), xtc_date_short($account['customers_dob'])));
}

$smarty->assign('INPUT_EMAIL', xtc_draw_input_fieldNote(array ('name' => 'email_address', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), $account['customers_email_address']));
$smarty->assign('INPUT_CONFIRM_EMAIL', xtc_draw_input_fieldNote(array ('name' => 'confirm_email_address', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), $account['customers_email_address']));
$smarty->assign('INPUT_TEL', xtc_draw_input_fieldNote(array ('name' => 'telephone', 'text' => '&nbsp;'. ((ACCOUNT_TELEPHONE_OPTIONAL == 'false' && xtc_not_null(ENTRY_TELEPHONE_NUMBER_TEXT)) ? '<span class="inputRequirement">'.ENTRY_TELEPHONE_NUMBER_TEXT.'</span>' : '')), $account['customers_telephone']));
$smarty->assign('INPUT_FAX', xtc_draw_input_fieldNote(array ('name' => 'fax', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">'.ENTRY_FAX_NUMBER_TEXT.'</span>' : '')), $account['customers_fax']));
$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('FORM_END', '</form>');
$smarty->assign('language', $_SESSION['language']);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_edit.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>