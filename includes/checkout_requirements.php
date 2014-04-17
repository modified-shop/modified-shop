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
  'paypal_checkout.php'       => 0,
  'checkout_shipping.php'     => 1,
  'checkout_payment.php'      => 2,
  'checkout_confirmation.php' => 3,
  'checkout_process.php'      => 4
);

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

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() < 1) {
	xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// check if checkout is allowed
if (isset($_SESSION['allow_checkout']) && $_SESSION['allow_checkout'] == 'false') {
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// Stock Check
// muss auf jeder Checkout-Seite geladen werden, damit gleichzeitige Bestellungen
// nicht zu minus Beständen fuehren !!!
if ((STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true')) {
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

if ($current_page == 'checkout_process.php') {
  if (xtc_not_null(MODULE_PAYMENT_INSTALLED) && !isset($_SESSION['payment'])) {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }
}

/* original:

// if the customer is not logged on, redirect them to the login page
if (!isset ($_SESSION['customer_id'])) {
  if (ACCOUNT_OPTIONS == 'guest') {
    xtc_redirect(xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL'));
  } else {
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
}

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() < 1) {
	xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

##################################################### checkout_shipping.php
// check if checkout is allowed
if (isset($_SESSION['allow_checkout']) && $_SESSION['allow_checkout'] == 'false') {
	xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

//  checkout only if minimum order value is reached
if ($_SESSION['cart']->show_total() > 0 ) {
  if ($_SESSION['cart']->show_total() < $_SESSION['customers_status']['customers_status_min_order'] ) {
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
  }
}
##################################################### checkout_payment.php

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset($_SESSION['shipping']))
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cartID']) && $_SESSION['cart']->cartID != $_SESSION['cartID']) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// Stock Check
if ((STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true')) {
  $products = $_SESSION['cart']->get_products();
  for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
    if (xtc_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
    }
  }
}
##################################################### checkout_confirmation.php


// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cartID']) && $_SESSION['cart']->cartID != $_SESSION['cartID']) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset($_SESSION['shipping']))
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

// Stock Check
$any_out_of_stock = false;
if (STOCK_CHECK == 'true') {
  for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
    if (xtc_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
      $any_out_of_stock = true;
   }
  }
  // Out of Stock
  if ((STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true)) {
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
  }
}
##################################################### checkout_process.php
// if the customer is not logged on, redirect them to the login page
if (!isset ($_SESSION['customer_id'])) {
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if ($_SESSION['customers_status']['customers_status_show_price'] != '1') {
  //BOF - web28.de - FIX redirect to NONSSL
  //xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', ''));
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT,'','NONSSL'));
  //EOF - web28.de - FIX redirect to NONSSL
}

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
/ *
if (!isset ($_SESSION['sendto'])) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

if ((xtc_not_null(MODULE_PAYMENT_INSTALLED)) && (!isset ($_SESSION['payment']))) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset ($_SESSION['cart']->cartID) && isset ($_SESSION['cartID'])) {
  if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }
}
* /
if (!isset ($_SESSION['sendto'])) {
  if($_SESSION['payment']=='paypalexpress') {
    xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
  } else {
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }
}

if ((xtc_not_null(MODULE_PAYMENT_INSTALLED)) && (!isset ($_SESSION['payment']))) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset ($_SESSION['cart']->cartID) && isset ($_SESSION['cartID'])) {
  if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
    if($_SESSION['payment']=='paypalexpress') {
      xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }
}
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset ($_SESSION['shipping'])) {
  xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}
*/
?>