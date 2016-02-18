<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_address_format.inc.php');
require_once (DIR_FS_INC.'xtc_get_address_format_id.inc.php');
require_once (DIR_FS_INC.'xtc_image_button.inc.php');

function get_address_iso_code($address_id) {
  $address_query = xtc_db_query("SELECT co.countries_iso_code_2
                                   FROM ".TABLE_COUNTRIES." co 
                                   JOIN ".TABLE_ADDRESS_BOOK." ab
                                        ON ab.entry_country_id = co.countries_id
                                           AND ab.address_book_id = '".(int)$address_id."'
                                           AND ab.customers_id = '".(int)$_SESSION['customer_id']."'");
  $address = xtc_db_fetch_array($address_query);
  
  return $address['countries_iso_code_2'];
}

if (!isset($_SESSION['customer_id']) 
    || $_SESSION['customers_status']['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST
    ) 
{
	xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if (!defined('MODULE_CHECKOUT_EXPRESS_STATUS') || MODULE_CHECKOUT_EXPRESS_STATUS == 'false') {
	xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

if (isset ($_POST['action']) && ($_POST['action'] == 'process')) {

  $valid_params = array(
    'payment',
    'payment_address',
    'shipping',
    'shipping_address',
  );

  // prepare variables
  foreach ($_POST as $key => $value) {
    if (!is_object(${$key}) && in_array($key , $valid_params)) {
      ${$key} = xtc_db_prepare_input($value);
    }
  }

  $check_query = xtc_db_query("SELECT *
                                 FROM ".TABLE_CUSTOMERS_CHECKOUT." 
                                WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
  
  $sql_data_array = array('customers_id' => (int)$_SESSION['customer_id'],
                          'checkout_payment' => $payment,
                          'checkout_payment_address' => $payment_address,
                          'checkout_shipping' => $shipping,
                          'checkout_shipping_address' => $shipping_address);
  if (xtc_db_num_rows($check_query) < 1) {
    xtc_db_perform(TABLE_CUSTOMERS_CHECKOUT, $sql_data_array);  
  } else {
    unset($sql_data_array['customers_id']);
    xtc_db_perform(TABLE_CUSTOMERS_CHECKOUT, $sql_data_array, 'update', "customers_id = '".(int)$_SESSION['customer_id']."'");
  }                        
}

// reset error
$error = false;

// clear session
unset($_SESSION['sendto']);
unset($_SESSION['billto']);
unset($_SESSION['shipping']);
unset($_SESSION['payment']);
unset($_SESSION['delivery_zone']);

$account_query = xtc_db_query("SELECT *
                                 FROM ".TABLE_CUSTOMERS_CHECKOUT." 
                                WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
$account = xtc_db_fetch_array($account_query);

require_once (DIR_WS_CLASSES.'order.php');
$order = new order();

// shipping
$total_weight = 0;
$total_count = 0;
$order->info['total'] = 0;

$_SESSION['sendto'] = $account['checkout_shipping_address'];
$_SESSION['delivery_zone'] = get_address_iso_code($account['checkout_shipping_address']);

require_once (DIR_WS_CLASSES.'shipping.php');
$shipping_modules = new shipping;

$quotes = $shipping_modules->quote();

$check_shipping = false;
if ($account['checkout_shipping'] == 'cheapest_cheapest') {
  $check_shipping = true;
}
$module_name = 'cheapest_cheapest';
$module_shipping = array(array('FIELD' => xtc_draw_radio_field('shipping', $module_name, (($account['checkout_shipping'] == 'cheapest_cheapest') ? true : false), 'id="shipping_'.strtok($module_name,'_').'"'),
                               'NAME' => TEXT_CHECKOUT_EXPRESS_CHECK_CHEAPEST,
                               'ID' => strtok($module_name,'_')
                               )
                         );

foreach ($quotes as $shipping) {
  $module_name = $shipping['id'].'_'.$shipping['methods'][0]['id'];
  if ($account['checkout_shipping'] == $module_name) {
    $check_shipping = true;
  }
  $module_shipping[] = array('FIELD' => xtc_draw_radio_field('shipping', $module_name, (($account['checkout_shipping'] == $module_name) ? true : false), 'id="shipping_'.strtok($module_name,'_').'"'),
                             'NAME' => strip_tags($shipping['module']),
                             'ID' => strtok($module_name,'_')
                             );
}
if ($check_shipping === false) {
  $error = true;
  $smarty->assign('module_shipping_error', TEXT_ERROR_CHECKOUT_EXPRESS_SHIPPING_MODULE);
}
$smarty->assign('module_shipping', $module_shipping);

$check_shipping_address = false;
$address_content = array();
$addresses_query = xtc_db_query("SELECT address_book_id,
                                        entry_firstname as firstname,
                                        entry_lastname as lastname,
                                        entry_company as company,
                                        entry_street_address as street_address,
                                        entry_suburb as suburb,
                                        entry_city as city,
                                        entry_postcode as postcode,
                                        entry_state as state,
                                        entry_zone_id as zone_id,
                                        entry_country_id as country_id
                                   FROM ".TABLE_ADDRESS_BOOK."
                                  WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
while ($addresses = xtc_db_fetch_array($addresses_query)) {
  $format_id = xtc_get_address_format_id($addresses['country_id']);
  if ($addresses['address_book_id'] == $account['checkout_shipping_address']) {
    $check_shipping_address = true;
  }
  $address_content[] = array('NAME' => $addresses['firstname'] . ' ' . $addresses['lastname'],
                             'FIELD' => xtc_draw_radio_field('shipping_address', $addresses['address_book_id'], ($addresses['address_book_id'] == $account['checkout_shipping_address']), 'id="shipping_address_'.$addresses['address_book_id'].'"'),
                             'ADDRESS' => xtc_address_format($format_id, $addresses, true, ' ', ', '),
                             'ID' => $addresses['address_book_id']
                             );
}
if ($check_shipping_address === false) {
  $error = true;
  $smarty->assign('module_shipping_address_error', TEXT_ERROR_CHECKOUT_EXPRESS_SHIPPING_ADDRESS);
}
$smarty->assign('module_shipping_address', $address_content);


// payment
if ($account['checkout_shipping'] != 'cheapest_cheapest') {
  $_SESSION['shipping'] = array('id' => $account['checkout_shipping']);
}
$_SESSION['billto'] = $account['checkout_payment_address'];
if ((int)$account['checkout_payment_address'] != '0' && (int)$account['checkout_shipping_address'] == '0') {
  $_SESSION['delivery_zone'] = get_address_iso_code($account['checkout_payment_address']);
}

require_once (DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment;
$selection = $payment_modules->selection();

// disable some modules, because needed action on checkout_payment
$disallowed_payment = array(
  'banktransfer',
  'billsafe_2',  
  'paypalplus',
  'payone_installment',
  'payone_otrans',
);
for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
  if (in_array($selection[$i]['id'], $disallowed_payment)
      || strpos($selection[$i]['id'], 'billpay') !== false
      || strpos($selection[$i]['id'], 'klarna') !== false
      || strpos($selection[$i]['id'], 'masterpayment') !== false
      ) 
  {
    unset($selection[$i]);
  }
}
$selection = array_values($selection);

$check_payment = false;
$module_payment = array();
foreach ($selection as $payment) {
  if ($account['checkout_payment'] == $payment['id']) {
    $check_payment = true;
  }
  $module_payment[] = array('FIELD' => xtc_draw_radio_field('payment', $payment['id'], (($account['checkout_payment'] == $payment['id']) ? true : false), 'id="payment_'.$payment['id'].'"'),
                            'NAME' => strip_tags($payment['module']),
                            'ID' => $payment['id']
                            );

}
if ($check_payment === false) {
  $error = true;
  $smarty->assign('module_payment_error', TEXT_ERROR_CHECKOUT_EXPRESS_PAYMENT_MODULE);
}
$smarty->assign('module_payment', $module_payment);

$check_payment_address = false;
$address_content = array();
$addresses_query = xtc_db_query("SELECT address_book_id,
                                        entry_firstname as firstname,
                                        entry_lastname as lastname,
                                        entry_company as company,
                                        entry_street_address as street_address,
                                        entry_suburb as suburb,
                                        entry_city as city,
                                        entry_postcode as postcode,
                                        entry_state as state,
                                        entry_zone_id as zone_id,
                                        entry_country_id as country_id
                                   FROM ".TABLE_ADDRESS_BOOK."
                                  WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
while ($addresses = xtc_db_fetch_array($addresses_query)) {
  $format_id = xtc_get_address_format_id($addresses['country_id']);
  if ($addresses['address_book_id'] == $account['checkout_payment_address']) {
    $check_payment_address = true;
  }
  $address_content[] = array('NAME' => $addresses['firstname'] . ' ' . $addresses['lastname'],
                             'FIELD' => xtc_draw_radio_field('payment_address', $addresses['address_book_id'], ($addresses['address_book_id'] == $account['checkout_payment_address']), 'id="payment_address_'.$addresses['address_book_id'].'"'),
                             'ADDRESS' => xtc_address_format($format_id, $addresses, true, ' ', ', '),
                             'ID' => $addresses['address_book_id']
                             );
}
if ($check_payment_address === false) {
  $error = true;
  $smarty->assign('module_payment_address_error', TEXT_ERROR_CHECKOUT_EXPRESS_PAYMENT_ADDRESS);
}
$smarty->assign('module_payment_address', $address_content);


// clear session
unset($_SESSION['sendto']);
unset($_SESSION['billto']);
unset($_SESSION['shipping']);
unset($_SESSION['payment']);
unset($_SESSION['delivery_zone']);

if (isset($_POST['action']) && $_POST['action'] == 'process' && $error === false) {
  if (isset($_GET['products_id']) && (int)$_GET['products_id'] > '0') {
    xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, xtc_get_all_get_params(), 'SSL'));
  } elseif (isset($_GET['cart']) && $_GET['cart'] == 'true') {
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));  
  }
  $messageStack->add_session('account', SUCCESS_CHECKOUT_EXPRESS_UPDATED, 'success');
  xtc_redirect(xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}


$breadcrumb->add(NAVBAR_TITLE_1_ACCOUNT_EDIT, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_ACCOUNT_CHECKOUT_EXPRESS_EDIT, xtc_href_link(FILENAME_ACCOUNT_CHECKOUT_EXPRESS, '', 'SSL'));

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('FORM_ACTION', xtc_draw_form('account_edit', xtc_href_link(FILENAME_ACCOUNT_CHECKOUT_EXPRESS, xtc_get_all_get_params(), 'SSL')).xtc_draw_hidden_field('action', 'process'));
$smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('FORM_END', '</form>');

if (isset($_GET['products_id']) && (int)$_GET['products_id'] > '0') {
  $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_PRODUCT_INFO, xtc_get_all_get_params(), 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
} elseif (isset($_GET['cart']) && $_GET['cart'] == 'true') {
  $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_SHOPPING_CART, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
} else {
  $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
}

$smarty->assign('language', $_SESSION['language']);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_checkout_express.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>