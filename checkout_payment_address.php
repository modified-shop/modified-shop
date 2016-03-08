<?php
/* -----------------------------------------------------------------------------------------
   $Id: checkout_payment_address.php 3784 2012-10-17 11:32:47Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_payment_address.php,v 1.13 2003/05/27); www.oscommerce.com
   (c) 2003 nextcommerce (checkout_payment_address.php,v 1.14 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (checkout_payment_address.php 993 2005-07-06)

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

$params = '';
$link_checkout_payment = FILENAME_CHECKOUT_PAYMENT;
if (isset($_SESSION['paypal']['PayerID'])) {
  $params = xtc_get_all_get_params();
  $link_checkout_payment = FILENAME_CHECKOUT_CONFIRMATION;
}

// if the customer is not logged on, redirect them to the login page
if (!isset ($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() < 1) {
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

$error = false;
$process = false;
if (isset ($_POST['action']) && ($_POST['action'] == 'submit')) {
  // process a new billing address
  if (xtc_not_null($_POST['firstname']) 
      && xtc_not_null($_POST['lastname']) 
      && xtc_not_null($_POST['street_address'])
      ) 
  {
    $checkout_page = 'payment';
    include(DIR_WS_MODULES.'checkout_address_store.php');
  // process the selected billing destination
  } elseif (isset ($_POST['address'])) {
    $reset_payment = false;
    if (isset ($_SESSION['billto'])) {
      if ($billto != $_POST['address']) {
        if (isset ($_SESSION['payment'])) {
          $reset_payment = true;
        }
      }
    }

    $_SESSION['billto'] = (int)$_POST['address'];
    
    if ($_SESSION['shipping'] === false) {
      $_SESSION['sendto'] = $_SESSION['billto'];
    }
    
    $check_address_query = xtc_db_query("SELECT count(*) AS total 
                                           FROM ".TABLE_ADDRESS_BOOK." 
                                          WHERE customers_id = '".(int)$_SESSION['customer_id']."' 
                                            AND address_book_id = '".(int)$_SESSION['billto']."'");
    $check_address = xtc_db_fetch_array($check_address_query);

    if ($check_address['total'] == '1') {
      if ($reset_payment == true && !isset($_SESSION['paypal']['PayerID'])) {
        unset ($_SESSION['payment']);
      }
      xtc_redirect(xtc_href_link($link_checkout_payment, $params, 'SSL'));
    } else {
      unset ($_SESSION['billto']);
    }
  } else {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];

    if ($_SESSION['shipping'] === false) {
      $_SESSION['sendto'] = $_SESSION['billto'];
    }
    xtc_redirect(xtc_href_link($link_checkout_payment, $params, 'SSL'));
  }
}

if (!isset ($_SESSION['billto'])) {
  $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
}

$breadcrumb->add(NAVBAR_TITLE_1_PAYMENT_ADDRESS, xtc_href_link($link_checkout_payment, $params, 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_PAYMENT_ADDRESS, xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, $params, 'SSL'));

$addresses_count = xtc_count_customer_address_book_entries();
require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('FORM_ACTION', xtc_draw_form('checkout_address', xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, $params, 'SSL'), 'post', 'onsubmit="return check_form_optional(checkout_address);"'));

if ($messageStack->size('checkout_address') > 0) {
  $smarty->assign('error', $messageStack->output('checkout_address'));
}

if ($process == false) {
  $smarty->assign('ADDRESS_LABEL', xtc_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'));
  $billto = $_SESSION['billto'];
  include(DIR_WS_MODULES.'checkout_address_layout.php');
}

if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
  require (DIR_WS_MODULES.'checkout_new_address.php');
}
$smarty->assign('BUTTON_CONTINUE', xtc_draw_hidden_field('action', 'submit').xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));

if ($process == true) {
  $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, $params, 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
}
$smarty->assign('FORM_END', '</form>');
$smarty->assign('language', $_SESSION['language']);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_payment_address.html');
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>