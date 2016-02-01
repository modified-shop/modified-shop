<?php
/* -----------------------------------------------------------------------------------------
   $Id: address_book_process.php 4221 2013-01-11 10:18:52Z gtb-modified $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(address_book_process.php,v 1.77 2003/05/27); www.oscommerce.com
   (c) 2003	 nextcommerce (address_book_process.php,v 1.13 2003/08/17); www.nextcommerce.org 
   (c) 2006 XT-Commerce - www.xt-commerce.com
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_count_customer_address_book_entries.inc.php');
require_once (DIR_FS_INC.'xtc_address_label.inc.php');
require_once (DIR_FS_INC.'xtc_get_country_name.inc.php');

if (!isset ($_SESSION['customer_id']))
	xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));

if (isset ($_GET['action']) && ($_GET['action'] == 'deleteconfirm') && isset ($_GET['delete']) && is_numeric($_GET['delete'])) {
	xtc_db_query("delete from ".TABLE_ADDRESS_BOOK." where address_book_id = '".(int) $_GET['delete']."' and customers_id = '".(int) $_SESSION['customer_id']."'");

	$messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'success');

	xtc_redirect(xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
}

// error checking when updating or adding an entry
$process = false;
if (isset ($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update'))) {
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
    'zone_id',
    'country_id',
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
		$messageStack->add('addressbook', ENTRY_GENDER_ERROR);
  }

	if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
		$error = true;
		$messageStack->add('addressbook', ENTRY_FIRST_NAME_ERROR);
	}

	if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
		$error = true;
		$messageStack->add('addressbook', ENTRY_LAST_NAME_ERROR);
	}

	if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
		$error = true;
		$messageStack->add('addressbook', ENTRY_STREET_ADDRESS_ERROR);
	}

	if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
		$error = true;
		$messageStack->add('addressbook', ENTRY_POST_CODE_ERROR);
	}

	if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
		$error = true;
		$messageStack->add('addressbook', ENTRY_CITY_ERROR);
	}

	if (is_numeric($country) == false) {
		$error = true;
		$messageStack->add('addressbook', ENTRY_COUNTRY_ERROR);
	}

  if (ACCOUNT_STATE == 'true') {
    $zone_id = 0;
    $check_query = xtc_db_query("SELECT count(*) AS total FROM " . TABLE_ZONES . " WHERE zone_country_id = '" . (int)$country . "'");
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
        $messageStack->add('addressbook', ENTRY_STATE_ERROR_SELECT);
      }
    } else {
      if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
        $error = true;
        $messageStack->add('addressbook', ENTRY_STATE_ERROR);
      }
    }
  }

	if ($error == false) {
		$sql_data_array = array('entry_firstname' => $firstname, 
		                        'entry_lastname' => $lastname, 
		                        'entry_street_address' => $street_address, 
		                        'entry_postcode' => $postcode, 
		                        'entry_city' => $city, 
		                        'entry_country_id' => (int) $country,
		                        'address_last_modified' => 'now()');

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

		if ($_POST['action'] == 'update') {
			xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '".(int) $_GET['edit']."' and customers_id ='".(int) $_SESSION['customer_id']."'");

			// reregister session variables
			if ((isset ($_POST['primary']) && ($_POST['primary'] == 'on')) || ($_GET['edit'] == $_SESSION['customer_default_address_id'])) {
				$_SESSION['customer_first_name'] = $firstname;
				$_SESSION['customer_country_id'] = $country_id;
				$_SESSION['customer_zone_id'] = (($zone_id > 0) ? (int) $zone_id : '0');
				$_SESSION['customer_default_address_id'] = (int) $_GET['edit'];

				$sql_data_array = array('customers_firstname' => $firstname, 
				                        'customers_lastname' => $lastname, 
				                        'customers_default_address_id' => (int) $_GET['edit'],
				                        'customers_last_modified' => 'now()');

				if (ACCOUNT_GENDER == 'true') {
					$sql_data_array['customers_gender'] = $gender;
        }
        
				xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".(int) $_SESSION['customer_id']."'");
			}
		} else {
			$sql_data_array['customers_id'] = (int) $_SESSION['customer_id'];
			$sql_data_array['address_date_added'] = 'now()';
			xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

			$new_address_book_id = xtc_db_insert_id();

			// reregister session variables
			if (isset ($_POST['primary']) && ($_POST['primary'] == 'on')) {
				$_SESSION['customer_first_name'] = $firstname;
				$_SESSION['customer_country_id'] = $country_id;
				$_SESSION['customer_zone_id'] = (($zone_id > 0) ? (int) $zone_id : '0');
				if (isset ($_POST['primary']) && ($_POST['primary'] == 'on')) {
					$_SESSION['customer_default_address_id'] = $new_address_book_id;
        }
        
				$sql_data_array = array('customers_firstname' => $firstname, 
				                        'customers_lastname' => $lastname,
				                        'customers_last_modified' => 'now()',
				                        'customers_date_added' => 'now()');

				if (ACCOUNT_GENDER == 'true') {
					$sql_data_array['customers_gender'] = $gender;
				}
				if (isset ($_POST['primary']) && ($_POST['primary'] == 'on')) {
					$sql_data_array['customers_default_address_id'] = $new_address_book_id;
        }
        
				xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".(int) $_SESSION['customer_id']."'");
			}
		}
		$messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'success');

		xtc_redirect(xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
	}
}

if (isset ($_GET['edit']) && is_numeric($_GET['edit'])) {
	$entry_query = xtc_db_query("SELECT *
                                 FROM ".TABLE_ADDRESS_BOOK."
                                WHERE customers_id = '".(int) $_SESSION['customer_id']."'
                                  AND address_book_id = '".(int) $_GET['edit']."'");

	if (xtc_db_num_rows($entry_query) == false) {
		$messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

		xtc_redirect(xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
	}

	$entry = xtc_db_fetch_array($entry_query);

} elseif (isset ($_GET['delete']) && is_numeric($_GET['delete'])) {
	if ($_GET['delete'] == $_SESSION['customer_default_address_id']) {
		$messageStack->add_session('addressbook', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');

		xtc_redirect(xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
	} else {
		$check_query = xtc_db_query("SELECT count(*) as total 
		                               FROM ".TABLE_ADDRESS_BOOK." 
		                              WHERE address_book_id = '".(int) $_GET['delete']."' 
		                                AND customers_id = '".(int) $_SESSION['customer_id']."'");
		$check = xtc_db_fetch_array($check_query);

		if ($check['total'] < 1) {
			$messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

			xtc_redirect(xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
		}
	}
} else {
	$entry = array ();
}

if (!isset ($_GET['delete']) && !isset ($_GET['edit'])) {
	if (xtc_count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
		$messageStack->add_session('addressbook', ERROR_ADDRESS_BOOK_FULL);

		xtc_redirect(xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
	}
}

$breadcrumb->add(NAVBAR_TITLE_1_ADDRESS_BOOK_PROCESS, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_ADDRESS_BOOK_PROCESS, xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

if (isset ($_GET['edit']) && is_numeric($_GET['edit'])) {
	$breadcrumb->add(NAVBAR_TITLE_MODIFY_ENTRY_ADDRESS_BOOK_PROCESS, xtc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit='.(int)$_GET['edit'], 'SSL'));
}
elseif (isset ($_GET['delete']) && is_numeric($_GET['delete'])) {
	$breadcrumb->add(NAVBAR_TITLE_DELETE_ENTRY_ADDRESS_BOOK_PROCESS, xtc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete='.(int)$_GET['delete'], 'SSL'));
} else {
	$breadcrumb->add(NAVBAR_TITLE_ADD_ENTRY_ADDRESS_BOOK_PROCESS, xtc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, '', 'SSL'));
}

require (DIR_WS_INCLUDES.'header.php');

if (!isset($_GET['delete'])) {
	$action = xtc_draw_form('addressbook', xtc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, (isset ($_GET['edit']) ? 'edit='.$_GET['edit'] : ''), 'SSL'), 'post', 'onsubmit="return check_form(addressbook);"');
}

$smarty->assign('FORM_ACTION', $action);
if ($messageStack->size('addressbook') > 0) {
	$smarty->assign('error', $messageStack->output('addressbook'));
}

if (isset ($_GET['delete'])) {
	$smarty->assign('delete', '1');
	$smarty->assign('ADDRESS', xtc_address_label($_SESSION['customer_id'], (int)$_GET['delete'], true, ' ', '<br />'));

	$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
	$smarty->assign('BUTTON_DELETE', '<a href="'.xtc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete='.(int)$_GET['delete'].'&action=deleteconfirm', 'SSL').'">'.xtc_image_button('button_delete.gif', IMAGE_BUTTON_DELETE).'</a>');
} else {

	include (DIR_WS_MODULES.'address_book_details.php');

	if (isset ($_GET['edit']) && is_numeric($_GET['edit'])) {
		$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
		$smarty->assign('BUTTON_UPDATE', xtc_draw_hidden_field('action', 'update').xtc_draw_hidden_field('edit', $_GET['edit']).xtc_image_submit('button_update.gif', IMAGE_BUTTON_UPDATE));
	} else {
		if (sizeof($_SESSION['navigation']->snapshot) > 0) {
			$back_link = xtc_href_link($_SESSION['navigation']->snapshot['page'], xtc_array_to_string($_SESSION['navigation']->snapshot['get'], array (xtc_session_name())), $_SESSION['navigation']->snapshot['mode']);
		} else {
			$back_link = xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL');
		}
		$smarty->assign('BUTTON_BACK', '<a href="'.$back_link.'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
		$smarty->assign('BUTTON_UPDATE', xtc_draw_hidden_field('action', 'process').xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
	}
	$smarty->assign('FORM_END', '</form>');
}

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/address_book_process.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>