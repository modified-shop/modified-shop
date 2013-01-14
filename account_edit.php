<?php

/* -----------------------------------------------------------------------------------------
   $Id: account_edit.php 4221 2013-01-11 10:18:52Z gtb-modified $   
   
   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(account_edit.php,v 1.63 2003/05/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (account_edit.php,v 1.14 2003/08/17); www.nextcommerce.org

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

if (!isset ($_SESSION['customer_id']))
	xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));

if ($_SESSION['customers_status']['customers_status_id']==0)
	xtc_redirect(xtc_href_link_admin(FILENAME_CUSTOMERS, 'cID='.$_SESSION['customer_id'].'&action=edit', 'SSL'));

if (isset ($_POST['action']) && ($_POST['action'] == 'process')) {
	if (ACCOUNT_GENDER == 'true')
		$gender = xtc_db_prepare_input($_POST['gender']);
	$firstname = xtc_db_prepare_input($_POST['firstname']);
	$lastname = xtc_db_prepare_input($_POST['lastname']);
	if (ACCOUNT_DOB == 'true')
		$dob = xtc_db_prepare_input($_POST['dob']);
	if (ACCOUNT_COMPANY_VAT_CHECK == 'true')
		$vat = xtc_db_prepare_input($_POST['vat']);
	$email_address = xtc_db_prepare_input($_POST['email_address']);
	$confirm_email_address = xtc_db_prepare_input($_POST['confirm_email_address']); // Hetfield - 2009-08-15 - confirm e-mail at registration
	$telephone = xtc_db_prepare_input($_POST['telephone']);
	$fax = xtc_db_prepare_input($_POST['fax']);

	$error = false;

	if (ACCOUNT_GENDER == 'true') {
		if (($gender != 'm') && ($gender != 'f')) {
			$error = true;
			$messageStack->add('account_edit', ENTRY_GENDER_ERROR);
		}
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
	$country = xtc_get_customers_country($_SESSION['customer_id']);
	require_once(DIR_WS_CLASSES.'vat_validation.php');
	$vatID = new vat_validation($vat, $_SESSION['customer_id'], '', $country);

	$customers_status = $vatID->vat_info['status'];
	$customers_vat_id_status = $vatID->vat_info['vat_id_status'];
	
	// BOF - DokuMan - 2009-05-26 - Code optimization
	//$error = $vatID->vat_info['error'];
	//if($error==1){
	if($vatID->vat_info['error']==1){
	// EOF - DokuMan - 2009-05-26 - Code optimization
	
	$messageStack->add('account_edit', ENTRY_VAT_ERROR);
	$error = true;
  }

// New VAT CHECK END


	if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
		$error = true;
		$messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR);
	}

	if (xtc_validate_email($email_address) == false) {
		$error = true;
		$messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
  //BOF - Dokuman - 2009-08-19 - BUGFIX: #0000233 added check for emails in account_edit
	} else { 
        $check_email_query = xtc_db_query("select count(*) as total from ".TABLE_CUSTOMERS." where customers_email_address = '".xtc_db_input($email_address)."' and account_type = '0' and customers_id != '".$_SESSION['customer_id']."'"); 
        $check_email = xtc_db_fetch_array($check_email_query); 
        if ($check_email['total'] > 0) { 
            $error = true; 
            $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS); 
        }
  //EOF - Dokuman - 2009-08-19 - BUGFIX: #0000233 added check for emails in account_edit        
    }
	//BOF - Hetfield - 2009-08-15 - confirm e-mail at registration
	if ($email_address != $confirm_email_address) {
   		$error = true;    
   		$messageStack->add('create_account', ENTRY_EMAIL_ERROR_NOT_MATCHING);
	}
	//EOF - Hetfield - 2009-08-15 - confirm e-mail at registration
	if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
		$error = true;
		$messageStack->add('account_edit', ENTRY_TELEPHONE_NUMBER_ERROR);
	}


	if ($error == false) {
		$sql_data_array = array ('customers_vat_id' => $vat, 'customers_vat_id_status' => $customers_vat_id_status, 'customers_firstname' => $firstname, 'customers_lastname' => $lastname, 'customers_email_address' => $email_address, 'customers_telephone' => $telephone, 'customers_fax' => $fax,'customers_last_modified' => 'now()');

		if (ACCOUNT_GENDER == 'true')
			$sql_data_array['customers_gender'] = $gender;
		if (ACCOUNT_DOB == 'true')
			$sql_data_array['customers_dob'] = xtc_date_raw($dob);
		
		xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".(int) $_SESSION['customer_id']."'");

		xtc_db_query("update ".TABLE_CUSTOMERS_INFO." set customers_info_date_account_last_modified = now() where customers_info_id = '".(int) $_SESSION['customer_id']."'");

		// reset the session variables
		$customer_first_name = $firstname;
		$messageStack->add_session('account', SUCCESS_ACCOUNT_UPDATED, 'success');
		xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
	}
} else {
	$account_query = xtc_db_query("select customers_gender, customers_cid, customers_vat_id, customers_vat_id_status, customers_firstname, customers_lastname, customers_dob, customers_email_address, customers_telephone, customers_fax from ".TABLE_CUSTOMERS." where customers_id = '".(int) $_SESSION['customer_id']."'");
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
//BOF - Hetfield - 2009-08-15 - confirm e-mail at registration
$smarty->assign('INPUT_CONFIRM_EMAIL', xtc_draw_input_fieldNote(array ('name' => 'confirm_email_address', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), $account['customers_email_address']));
//EOF - Hetfield - 2009-08-15 - confirm e-mail at registration
$smarty->assign('INPUT_TEL', xtc_draw_input_fieldNote(array ('name' => 'telephone', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">'.ENTRY_TELEPHONE_NUMBER_TEXT.'</span>' : '')), $account['customers_telephone']));
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