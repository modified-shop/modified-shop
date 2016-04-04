<?php
  /* --------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

$current_page = basename($PHP_SELF);
$checkout_position = array(
  'paypalplus_redirect.php'     => 0, 
  'checkout_shipping.php'       => 1,
  'checkout_payment.php'        => 2,
  'checkout_confirmation.php'   => 3,
  'checkout_process.php'        => 4,
  'checkout_payment_iframe.php' => 4,
);

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() < 1) {
	xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// check if checkout is allowed
if (isset($_SESSION['allow_checkout']) && $_SESSION['allow_checkout'] == 'false') {
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// if the customer is not logged on, redirect them to the login page
if (!isset($_SESSION['customer_id'])) {
  if (ACCOUNT_OPTIONS == 'guest') {
    xtc_redirect(xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL'));
  } else {
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
}

// no checkout if it is not allowed to see prices
if ($_SESSION['customers_status']['customers_status_show_price'] != '1') {
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT,'','NONSSL'));
}

if ($_SESSION['customers_status']['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
  $products = $_SESSION['cart']->get_products();
  for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
    if (preg_match('/^GIFT/', addslashes($products[$i]['model']))) {
      $messageStack->add_session('shopping_cart', GUEST_VOUCHER_NOT_ALLOWED);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
    }
  }
}

// Stock Check
// muss auf jeder Checkout-Seite geladen werden, damit gleichzeitige Bestellungen
// nicht zu minus Bestaenden fuehren !!!
if (STOCK_CHECK == 'true' 
    && STOCK_ALLOW_CHECKOUT != 'true' 
    && (!isset($_SESSION['tmp_oID']) 
        || (isset($_SESSION['tmp_oID']) && !is_numeric($_SESSION['tmp_oID']))
        )
    )
{
  $products = $_SESSION['cart']->get_products();
  for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
    if (xtc_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
      $_SESSION['any_out_of_stock'] = 1;
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
    }
    //products attributes
    if (ATTRIBUTE_STOCK_CHECK == 'true' && isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
      reset($products[$i]['attributes']);
      while (list ($option, $value) = each($products[$i]['attributes'])) {
        $attributes = $main->getAttributes($products[$i]['id'],$option,$value);
        if ($attributes['attributes_stock'] - $products[$i]['quantity'] < 0) {
          $_SESSION['any_out_of_stock'] = 1;
          xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
        }
      }
    }
  }
}

// Stock Check Specials
// muss auf jeder Checkout-Seite geladen werden, damit gleichzeitige Bestellungen
// nicht zu einem Ueberkaufen der Sonderangebote fuehrt !!!
if (STOCK_CHECK_SPECIALS == 'true' 
    && STOCK_ALLOW_CHECKOUT != 'true'
    && (!isset($_SESSION['tmp_oID']) 
        || (isset($_SESSION['tmp_oID']) && !is_numeric($_SESSION['tmp_oID']))
        )
    )
{
  require_once (DIR_FS_INC.'check_stock_specials.inc.php');
  $products = $_SESSION['cart']->get_products();
  for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
    if ($xtPrice->xtcCheckSpecial($products[$i]['id']) && check_stock_specials($products[$i]['id'], $products[$i]['quantity'])) {
      $_SESSION['any_out_of_stock'] = 1;
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
    }
  }  
}

// checkout only if minimum order value is reached
if ($_SESSION['customers_status']['customers_status_min_order'] != 0 && $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) < $_SESSION['customers_status']['customers_status_min_order'] ) {
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// Checkout only when maximum order value is not reached
if ($_SESSION['customers_status']['customers_status_max_order'] != 0 && $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) > $_SESSION['customers_status']['customers_status_max_order'] ) {
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// from checkout_payment
if ($checkout_position[$current_page] >= 2) {
  // avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cartID']) && $_SESSION['cart']->cartID != $_SESSION['cartID']) {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }
  // if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!isset($_SESSION['shipping'])) {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }
}

// from checkout_confirmation
if ($checkout_position[$current_page] >= 3) {
  if (!isset ($_SESSION['sendto'])) {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }
}

// from checkout_process / checkout_payment_iframe
if ($checkout_position[$current_page] >= 4) {
  if (xtc_not_null(MODULE_PAYMENT_INSTALLED) && !isset($_SESSION['payment'])) {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/checkout/checkout_requirements/','php') as $file) require_once ($file);
?>