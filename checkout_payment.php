<?php
/* -----------------------------------------------------------------------------------------
   $Id: checkout_payment.php 3434 2012-08-20 11:25:35Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_payment.php,v 1.110 2003/03/14); www.oscommerce.com
   (c) 2003 nextcommerce (checkout_payment.php,v 1.20 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (checkout_payment.php 1325 2005-10-30)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   agree_conditions_1.01          Autor:  Thomas Plänkers (webmaster@oscommerce.at)

   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

//web28 - 2012-04-27 - pre-selection the first payment option
if (!defined('CHECK_FIRST_PAYMENT_MODUL')) {
  define ('CHECK_FIRST_PAYMENT_MODUL', false); //true, false - default false
}
// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC . 'xtc_address_label.inc.php');
require_once (DIR_FS_INC . 'xtc_get_address_format_id.inc.php');
require_once (DIR_FS_INC . 'xtc_check_stock.inc.php');
unset ($_SESSION['tmp_oID']);
unset ($_SESSION['transaction_id']); //Dokuman - 2009-10-02 - added moneybookers payment module version 2.4

// if the customer is not logged on, redirect them to the login page
if (!isset ($_SESSION['customer_id'])) {
  if (ACCOUNT_OPTIONS == 'guest') {
    xtc_redirect(xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL'));
  } else {
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
}

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() < 1)
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset ($_SESSION['shipping']))
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset ($_SESSION['cart']->cartID) && isset ($_SESSION['cartID'])) {
  if ($_SESSION['cart']->cartID != $_SESSION['cartID'])
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

if (isset ($_SESSION['credit_covers']))
  unset ($_SESSION['credit_covers']); //ICW ADDED FOR CREDIT CLASS SYSTEM
// Stock Check
if ((STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true')) {
  $products = $_SESSION['cart']->get_products();
  $any_out_of_stock = 0;
  for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
    if (xtc_check_stock($products[$i]['id'], $products[$i]['quantity']))
      $any_out_of_stock = 1;
  }
  if ($any_out_of_stock == 1)
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// if no billing destination address was selected, use the customers own address as default
if (!isset ($_SESSION['billto'])) {
  $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
} else {
  // verify the selected billing address
  $check_address_query = xtc_db_query("select count(*) as total
                                            from " . TABLE_ADDRESS_BOOK . "
                                            where customers_id = '" . (int) $_SESSION['customer_id'] . "'
                                            and address_book_id = '" . (int) $_SESSION['billto'] . "'");
  $check_address = xtc_db_fetch_array($check_address_query);

  if ($check_address['total'] != '1') {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
    if (isset ($_SESSION['payment'])) {
      unset ($_SESSION['payment']);
    }
  }
}

if (!isset ($_SESSION['sendto']) || $_SESSION['sendto'] == "") {
  $_SESSION['sendto'] = $_SESSION['billto'];
}
require (DIR_WS_CLASSES . 'order.php');
$order = new order();

require (DIR_WS_CLASSES . 'order_total.php'); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM
$order_total_modules = new order_total(); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM

$total_weight = $_SESSION['cart']->show_weight();

$total_count = $_SESSION['cart']->count_contents_virtual(); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM

if ($order->billing['country']['iso_code_2'] != '' && $order->delivery['country']['iso_code_2'] == '') {
$_SESSION['delivery_zone'] = $order->billing['country']['iso_code_2'];
} else {
$_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
}

// load all enabled payment modules
require_once (DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment;

$order_total_modules->process();
// redirect if Coupon matches ammount

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_PAYMENT, xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_PAYMENT, xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

$smarty->assign('FORM_ACTION', xtc_draw_form('checkout_payment', xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'onSubmit="return check_form();"'));
$smarty->assign('ADDRESS_LABEL', xtc_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'));
$smarty->assign('BUTTON_ADDRESS', '<a href="' . xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . xtc_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>');
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('FORM_END', '</form>');

require (DIR_WS_INCLUDES . 'header.php');
$module_smarty = new Smarty;
$order_total = $xtPrice->xtcFormat($order->info['total'],false); //web28 2012-04-27 - rounded $order_total
if ($order_total > 0) {
  if (isset ($_GET['payment_error']) && is_object(${ $_GET['payment_error'] }) && ($error = ${$_GET['payment_error']}->get_error())) {
    $smarty->assign('error', encode_htmlspecialchars($error['error']));
  }
  // BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
  if(isset($_SESSION['reshash']['FORMATED_ERRORS'])) {
    $smarty->assign('error', $_SESSION['reshash']['FORMATED_ERRORS']);
  }
  // EOF - Tomcraft - 2009-10-03 - Paypal Express Modul
  $selection = $payment_modules->selection();

  $radio_buttons = 0;
  for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
    //ot_payment Anzeige Zahlungsrabatt bei Zahlungsauswahl
    if (isset($GLOBALS['ot_payment']) && !isset($selection[$i]['module_cost'])) {
      $selection[$i]['module_cost'] = $GLOBALS['ot_payment']->get_module_cost($selection[$i]);
    }
    $selection[$i]['radio_buttons'] = $radio_buttons;
    if ((isset($_SESSION['payment']) && $selection[$i]['id'] == $_SESSION['payment']) || (!isset($_SESSION['payment']) && $i == 0 && CHECK_FIRST_PAYMENT_MODUL)) { //web28 - 2012-04-27 - FIX pre-selection the first payment option
      $selection[$i]['checked'] = 1;
    } else {
      $selection[$i]['checked'] = 0;
    }

    if (sizeof($selection) > 1) {
      $selection[$i]['selection'] = xtc_draw_radio_field('payment', $selection[$i]['id'], ($selection[$i]['checked']), 'id="'.($i+1).'"'); //web28 - 2010-11-23 - FIX pre-selection the first payment option
    } else {
      $selection[$i]['selection'] = xtc_draw_hidden_field('payment', $selection[$i]['id']);
    }

    if (!isset ($selection[$i]['error'])) {
      $radio_buttons++;
    }
  }

  $module_smarty->assign('module_content', $selection);
} else {
  $smarty->assign('GV_COVER', 'true');
  if (isset ($_SESSION['payment'])){
    unset ($_SESSION['payment']); //web28 - 2012-04-27 -  Fix for order_total <= 0
  }
}

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
unset($_SESSION['reshash']);
unset($_SESSION['nvpReqArray']);
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul
if (ACTIVATE_GIFT_SYSTEM == 'true') {
  $smarty->assign('module_gift', $order_total_modules->credit_selection());
}

$module_smarty->caching = 0;
$payment_block = $module_smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_payment_block.html');

$smarty->assign('COMMENTS', xtc_draw_textarea_field('comments', 'soft', '60', '5', isset($_SESSION['comments']) ? $_SESSION['comments'] : '') . xtc_draw_hidden_field('comments_added', 'YES'));

//check if display conditions on checkout page is true
if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
  $shop_content_data = $main->getContentData(3);

  $smarty->assign('AGB', '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>');
  $smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));

  if (isset($_GET['step']) && $_GET['step'] == 'step2') {
    $smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" id="conditions" checked="checked" />');
  } else {
    $smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" id="conditions" />');
  }
}

//BOF - Dokuman - 2012-06-19 - BILLSAFE payment module
if ($_GET['billsafe_close'] == 'true' || $_GET['payment_error'] == 'billsafe_2' || $_GET['payment_error'] == 'billsafe_2hp') echo '<script type="text/javascript"> if (top.lpg) top.lpg.close("'.xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.stripslashes(urlencode(html_entity_decode($_GET['error_message']))), 'SSL').'"); </script>';
//EOF - Dokuman - 2012-06-19 - BILLSAFE payment module

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('PAYMENT_BLOCK', $payment_block);
$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_payment.html');
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE . '/index.html');
include ('includes/application_bottom.php');
?>
