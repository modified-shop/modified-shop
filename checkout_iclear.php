<?php
/*
  $Id: checkout_iclear.php,v 1.1 2007/03/18 20:04:47 dis Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

************************************************************************
  Copyright (C) 2005 - 2007 BSE, David Brandt

                    All rights reserved.

  This program is free software licensed under the GNU General Public License (GPL).

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  USA

*************************************************************************/

  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
if (!isset ($_SESSION['customer_id'])) {
	xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// assign SESSION vars 2 locals (register_globals off)
	$cart = $_SESSION['cart'];
	$cartID = $_SESSION['cartID'];
 // if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
  }

 // avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && isset($_SESSION['cartID'])) { // Hetfield - 2009-08-19 - removed deprecated function session_is_registered to be ready for PHP >= 5.3
    if ($cart->cartID != $cartID) {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

	// if no shipping method has been selected, redirect the customer to the shipping method selection page
	if (!isset ($_SESSION['shipping']))
		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

// load the selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($payment);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  $payment_modules->update_status();

  if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }

// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($shipping);

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;
// check if this is a call from checkout confirmation and redirect 2 iclear login page if
  if(isset($HTTP_POST_VARS['targetURI'])) {
    xtc_redirect($HTTP_POST_VARS['targetURI']);
  } elseif (isset($HTTP_POST_VARS['error_message'])) {
    print '<div style="width: 100%; text-align: center; color: red; font-size:14px; font-weight: 900; font-family:sans-serif">Bei der Kommunikation mit dem iclear Paymentsystem trat folgender Fehler auf: <br />' . nl2br($HTTP_POST_VARS['error_message']) . '</div>';

  }
?>
