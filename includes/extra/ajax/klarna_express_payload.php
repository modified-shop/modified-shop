<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // include needed classes
  include_once(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/klarna_express.php');
  require_once(DIR_FS_CATALOG.'includes/modules/payment/klarna_express.php');


  // called as ajax.php?action=add_product&ext=klarna_express_payload, cart_actions.php has added the product before
  function klarna_express_payload() {
    global $klarna_express_cart_before;

    if (!klarna_express::express_configured()
        || !isset($_SESSION['cart'])
        || !is_object($_SESSION['cart'])
        )
    {
      return klarna_express::get_ajax_error('unavailable');
    }

    $klarna_express = new klarna_express();

    if (!$klarna_express->is_valid_ajax_token()) {
      return $klarna_express->get_ajax_error('token');
    }

    // the add must have raised the quantity of this very product, an invalid attribute choice adds nothing
    $_SESSION['cart']->get_products(false);
    if (!is_array($klarna_express_cart_before)) {
      return $klarna_express->get_ajax_error('add');
    }
    $cart_quantity = $_SESSION['cart']->get_quantity($klarna_express_cart_before['id']);
    if ($cart_quantity <= 0
        || ($cart_quantity <= $klarna_express_cart_before['quantity'] && $cart_quantity < MAX_PRODUCTS_QTY)
        )
    {
      return $klarna_express->get_ajax_error('add');
    }

    if ($klarna_express->express_available() !== true) {
      return $klarna_express->get_ajax_error('unavailable');
    }

    $limit_error = $klarna_express->checkout_allowed();
    if ($limit_error != '') {
      return $klarna_express->get_ajax_error($limit_error);
    }

    $express = $klarna_express->prepare_express();

    return array(
      'ok' => true,
      'payload' => $express['payload'],
      'token' => $express['token'],
      'locale' => $express['locale'],
    );
  }
