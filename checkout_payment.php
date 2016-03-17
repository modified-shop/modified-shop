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

// pre-selection the first payment option
defined('CHECK_FIRST_PAYMENT_MODUL') or define('CHECK_FIRST_PAYMENT_MODUL', 'false'); // default: 'false'

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC . 'xtc_address_label.inc.php');
require_once (DIR_FS_INC . 'xtc_get_address_format_id.inc.php');

require (DIR_WS_INCLUDES.'checkout_requirements.php');

unset ($_SESSION['tmp_oID']);
unset ($_SESSION['transaction_id']); ### moneybookers payment module version 2.4
unset ($_SESSION['paypal']);

//if (isset($_SESSION['credit_covers'])) unset($_SESSION['credit_covers']);
if (isset($_SESSION['cot_gv']) /*&& isset($_SESSION['payment'])*/) {
  //unset($_SESSION['payment']); 
  unset($_SESSION['cot_gv']); //ADDED FOR CREDIT CLASS SYSTEM 
}

//express checkout
if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
  if (isset($_GET['express']) && $_GET['express'] == 'on') {
    $express_query = xtc_db_query("SELECT checkout_payment,
                                          checkout_payment_address
                                     FROM ".TABLE_CUSTOMERS_CHECKOUT." 
                                    WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
    $express = xtc_db_fetch_array($express_query);
    if ($express['checkout_payment_address'] != '') {
      $_SESSION['billto'] = $express['checkout_payment_address'];
    }
  }
}

// if no billing destination address was selected, use the customers own address as default
if (!isset($_SESSION['billto'])) {
  $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
} else {
  // verify the selected billing address
  $check_address_query = xtc_db_query("SELECT count(*) AS total
                                         FROM " . TABLE_ADDRESS_BOOK . "
                                        WHERE customers_id = '" . (int) $_SESSION['customer_id'] . "'
                                          AND address_book_id = '" . (int) $_SESSION['billto'] . "'");
  $check_address = xtc_db_fetch_array($check_address_query);
  if ($check_address['total'] != '1') {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
    if (isset($_SESSION['payment'])) {
      unset ($_SESSION['payment']);
    }
  }
}

if (!isset($_SESSION['sendto']) || $_SESSION['sendto'] == "") {
  $_SESSION['sendto'] = $_SESSION['billto'];
}
require_once (DIR_WS_CLASSES . 'order.php');
$order = new order();

require_once (DIR_WS_CLASSES . 'order_total.php'); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM
$order_total_modules = new order_total(); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM

$content_type = $_SESSION['cart']->get_content_type();
$total_weight = $_SESSION['cart']->show_weight();
$total_count = $_SESSION['cart']->count_contents_virtual(); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM

if ($order->billing['country']['iso_code_2'] != '' && $order->delivery['country']['iso_code_2'] == '') {
  $_SESSION['delivery_zone'] = $order->billing['country']['iso_code_2'];
} else {
  $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
}

// load all enabled payment modules
require_once (DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment();

$order_total = $order_total_modules->process();
// redirect if Coupon matches ammount

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_PAYMENT, xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_PAYMENT, xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

$smarty->assign('FORM_ACTION', xtc_draw_form('checkout_payment', xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'onSubmit="return check_form();"'));
$smarty->assign('ADDRESS_LABEL', xtc_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'));
$smarty->assign('BUTTON_ADDRESS', '<a href="' . xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . xtc_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>');
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('BUTTON_CHECKOUT_STEP3', xtc_image_submit('button_checkout_step3.gif', IMAGE_BUTTON_CHECKOUT_STEP3));
$smarty->assign('FORM_END', '</form>');

$module_smarty = new Smarty;

$credit_amount = 0;
if (ACTIVATE_GIFT_SYSTEM == 'true') {
  $credit_selection = $order_total_modules->credit_selection();
  for ($i = 0, $n = sizeof($credit_selection); $i < $n; $i++) {
    if ((isset($_SESSION['c'.$credit_selection[$i]['id']]) && $credit_selection[$i]['id'] == $_SESSION['c'.$credit_selection[$i]['id']])) {
      $credit_selection[$i]['checked'] = 1;
    } else {
      $credit_selection[$i]['checked'] = 0;
    }
    $credit_amount =  $credit_selection[$i]['credit_amount'];
    $credit_order_total = $credit_selection[$i]['credit_order_total'];
    $credit_selection[$i]['selection'] = xtc_draw_checkbox_field('c'.$credit_selection[$i]['id'], $credit_amount, $credit_selection[$i]['checked'], 'id="rd-'.'c'.$credit_selection[$i]['id'].'"');
    $credit_selection[$i]['selection'] .= '<input type="hidden" name="credit_order_total"  id="cot-'.'c'.$credit_selection[$i]['id'].'" value="'.$credit_order_total.'">';
    $credit_selection[$i]['credit_amount'] = $xtPrice->xtcFormat($credit_amount, true);
    $module_smarty->assign('credit_amount_payment_info', $credit_amount >= $credit_order_total ? GV_NO_PAYMENT_INFO : GV_ADD_PAYMENT_INFO);
  }
  $module_smarty->assign('module_gift', $credit_selection);  
}

$total = $xtPrice->xtcFormat($order->info['total'], false);
if ($total > 0 || ($credit_amount && $total > 0) || (isset($_SESSION['credit_covers']) && $_SESSION['credit_covers'] == 1 && $total > 0)) {
  
  $error = false;
  if (isset($_GET['payment_error']) 
      && is_object(${$_GET['payment_error']}) 
      && method_exists(${$_GET['payment_error']}, 'get_error')
      && ($error = ${$_GET['payment_error']}->get_error())
      ) 
  {
    $smarty->assign('error',  encode_htmlspecialchars($error['error']));
    $_SESSION['payment'] = $_GET['payment_error'];
    $error = true;
  }
  
  //get payment modules
  $selection = $payment_modules->selection();

  ## PayPal
  if (defined('MODULE_PAYMENT_PAYPAL_PLUS_THIRDPARTY_PAYMENT')
      && defined('MODULE_PAYMENT_PAYPALPLUS_STATUS')
      &&  MODULE_PAYMENT_PAYPALPLUS_STATUS == 'True'
      && isset($GLOBALS['paypalplus'])
      && is_object($GLOBALS['paypalplus'])
      && $GLOBALS['paypalplus']->enabled === true
      && (!isset($credit_selection) || count($credit_selection) < 1)
      )
  {
    $hide_payment_ppp = explode(';', MODULE_PAYMENT_PAYPAL_PLUS_THIRDPARTY_PAYMENT);
    for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
      if (in_array($selection[$i]['id'], $hide_payment_ppp)) {
        if (isset($_SESSION['payment']) && $selection[$i]['id'] == $_SESSION['payment']) {
          $_SESSION['payment'] = 'paypalplus';
        }
        unset($selection[$i]);
        continue;
      }
    }
    $selection = array_values($selection);
  }
  
  $radio_buttons = 0;
  for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
    
    //express checkout
    if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
      if ($credit_amount == 0 && isset($_GET['express']) && $_GET['express'] == 'on' && $error === false) {
        if ($express['checkout_payment'] != '' && $selection[$i]['id'] == $express['checkout_payment']) {
          $_SESSION['payment'] = $express['checkout_payment'];
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(array('conditions')).'conditions=on', 'SSL'));
        }
      }
    }
    
    //ot_payment 
    if (isset($GLOBALS['ot_payment']) && !isset($selection[$i]['module_cost'])) {
      $selection[$i]['module_cost'] = $GLOBALS['ot_payment']->get_module_cost($selection[$i]);
    }
    $selection[$i]['radio_buttons'] = $radio_buttons;
    if ((isset($_SESSION['payment']) && $selection[$i]['id'] == $_SESSION['payment']) || (!isset($_SESSION['payment']) && $i == 0 && CHECK_FIRST_PAYMENT_MODUL == 'true')) { // pre-selection the first payment option
      $selection[$i]['checked'] = 1;
    } else {
      $selection[$i]['checked'] = 0;
    }

    if (sizeof($selection) > 1) {
      $selection[$i]['selection'] = xtc_draw_radio_field('payment', $selection[$i]['id'], ($selection[$i]['checked']), 'id="rd-'.($i+1).'"'); // pre-selection the first payment option
    } else {
      //$selection[$i]['selection'] = xtc_draw_hidden_field('payment', $selection[$i]['id']);
      $selection[$i]['selection'] = xtc_draw_radio_field('payment', $selection[$i]['id'], 1, 'id="rd-'.($i+1).'"');
    }

    if (!isset($selection[$i]['error'])) {
      $radio_buttons++;
    }
  }
  $module_smarty->assign('module_content', $selection);
} 
//Coupon 100%
elseif (isset($_SESSION['cc_id']) && $total <= 0) {
  $order_total_modules->pre_confirmation_check();
  $smarty->assign('GV_COVER', 'true');
} 
//Guthaben
elseif (!isset($_SESSION['cot_gv'])) {
  $order_total_modules->pre_confirmation_check();
  //$smarty->assign('GV_COVER', 'true');
}

// move header for Javascript form check
require (DIR_WS_INCLUDES . 'header.php');

$module_smarty->caching = 0;
$payment_block = $module_smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_payment_block.html');

$smarty->assign('COMMENTS', xtc_draw_textarea_field('comments', 'soft', '60', '5', isset($_SESSION['comments']) ? $_SESSION['comments'] : '') . xtc_draw_hidden_field('comments_added', 'YES'));

//check if display conditions on checkout page is true
if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
  $shop_content_data = $main->getContentData(3);
  $smarty->assign('AGB', '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>');
  $smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));
  $smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" id="conditions"'.(isset($_GET['step']) && $_GET['step'] == 'step2' ? ' checked="checked"' : '').' />');
}

if (DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT == 'true'
    && ($_SESSION['cart']->content_type == 'virtual'
        || $_SESSION['cart']->content_type == 'mixed')
    )
{
  $shop_content_data = $main->getContentData(REVOCATION_ID);
  $smarty->assign('REVOCATION', '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>');
  $smarty->assign('REVOCATION_LINK', $main->getContentLink(REVOCATION_ID, MORE_INFO,'SSL'));
  $smarty->assign('REVOCATION_checkbox', '<input type="checkbox" value="revocation" name="revocation" id="revocation"'.(isset($_GET['step']) && $_GET['step'] == 'step2' ? ' checked="checked"' : '').' />');
}

### BILLSAFE payment module
if ((isset($_GET['billsafe_close']) && $_GET['billsafe_close'] == 'true') 
|| (isset($_GET['payment_error']) && $_GET['payment_error'] == 'billsafe_2')) {
  echo '<script type="text/javascript"> if (top.lpg) top.lpg.close("'.xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.stripslashes(urlencode(decode_htmlentities($_GET['error_message']))), 'SSL').'"); </script>';
}
### BILLSAFE payment module

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('PAYMENT_BLOCK', $payment_block);
$main_content = $smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_payment.html');
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM')) $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE . '/index.html');

include ('includes/application_bottom.php');
?>