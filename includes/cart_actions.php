<?php
/* -----------------------------------------------------------------------------------------
   $Id: cart_actions.php 3255 2012-07-18 18:27:41Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_top.php,v 1.273 2003/05/19); www.oscommerce.com
   (c) 2003 nextcommerce (application_top.php,v 1.54 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce (cart_actions.php 168 2007-02-06)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Add A Quickie v1.0 Autor  Harald Ponce de Leon

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$action = (isset($_GET['action']) ? $_GET['action'] : '');

// Shopping cart actions
if (xtc_not_null($action)) {
  // redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled
  if ($session_started == false) {
    xtc_redirect(xtc_href_link(FILENAME_COOKIE_USAGE));
  }
    
  $wishlist = false;
  if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
    if ((isset($_POST['wishlist_x']) && isset($_POST['wishlist_y'])) || isset($_GET['wishlist']) || isset($_POST['wishlist'])) {
      $wishlist = true;
    }
  }

  $co_express = false;
  if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
    if ((isset($_POST['express_x']) && isset($_POST['express_y'])) || isset($_GET['express'])) {
      $co_express = true;
    }
  }
  
  $parameters = array ('action', 'pid', 'info_message_3', 'wishlist', 'prd_id', 'info_message');
  if (DISPLAY_CART == 'true') {
    $goto = FILENAME_SHOPPING_CART;
    array_push($parameters, 'products_id', 'cPath');
  } else {
    $goto = basename($PHP_SELF);
    if ($_GET['action'] == 'buy_now') {
      if ($goto == FILENAME_PRODUCT_INFO) {
        $_GET['products_id'] = $_GET['BUYproducts_id'];
      }
      $parameters[] = 'BUYproducts_id';
    } else {
      if ($goto != FILENAME_ACCOUNT_HISTORY_INFO) {
        $parameters[] = 'order_id';
      }
      array_push($parameters, 'products_id', 'BUYproducts_id', 'info');
    }
  }

  // do not redirect to shopping cart if delete from box cart
  if (isset($_GET['box']) && $_GET['box'] == 'cart' && isset($_GET['prd_id'])) {
    $goto = basename($PHP_SELF);
    for ($i=0, $n=count($parameters); $i<$n; $i++) {
      if (in_array($parameters[$i], array('products_id', 'cPath'))) unset($parameters[$i]);
    }
  }

  if (!is_object($_SESSION['cart'])) {
    $_SESSION['cart'] = new shoppingCart();
  }
  
  if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
    if (!is_object($_SESSION['wishlist'])) {
      $_SESSION['wishlist'] = new shoppingCart('wishlist');
    }
  }
  
  $cart_object = $_SESSION['cart'];
  if ($wishlist === true) {
    $cart_object = $_SESSION['wishlist'];
  }

  switch ($action) {

    case 'remove_product':
      $prd_id = xtc_input_validation($_GET['prd_id'], 'products_id', '');
      $cart_object->remove($prd_id);
      xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters), 'NONSSL'));
      break;

    // customer wants to update the product quantity in their shopping cart
    case 'update_product':
      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/cart_actions/update_product_prepare_post/','php') as $file) require ($file);
      // VERSANDKOSTEN IM WARENKORB
      if (isset($_POST['country'])) {
        $_SESSION['country'] = xtc_remove_non_numeric($_POST['country']);
        unset($_SESSION['sendto']);
      }
      
      for ($i = 0, $n = sizeof($_POST['products_id']); $i < $n; $i++) {

        $cart_quantity = $_POST['cart_quantity'][$i] = xtc_remove_non_numeric($_POST['cart_quantity'][$i]);
        $_POST['old_qty'][$i] = xtc_remove_non_numeric($_POST['old_qty'][$i]);
        $_POST['products_id'][$i] = xtc_input_validation($_POST['products_id'][$i], 'products_id', '');
          
        if ($cart_quantity == 0) $cart_object->remove($_POST['products_id'][$i]);
      
        if (in_array($_POST['products_id'][$i], (isset($_POST['cart_delete']) && is_array($_POST['cart_delete']) ? $_POST['cart_delete'] : array ()))) {
          $cart_object->remove($_POST['products_id'][$i]);
        } else {
          if ((int)$_POST['cart_quantity'][$i] > MAX_PRODUCTS_QTY) {
            $cart_quantity = MAX_PRODUCTS_QTY;
            $_SESSION['err_max_prod'][$i] = true;  // error message for exceeded product quantity, noRiddle
          }
          $attributes = isset($_POST['id'][$_POST['products_id'][$i]]) ? $_POST['id'][$_POST['products_id'][$i]] : '';

          $cart_object->add_cart($_POST['products_id'][$i], $cart_quantity, $attributes, false);
          unset($cart_quantity);
        }
      }
      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/cart_actions/update_product_before_redirect/','php') as $file) require ($file);
      xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters) . $info_message, 'NONSSL'));
      break;

    // customer adds a product from the products page
    case 'add_product':
      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/cart_actions/add_product_prepare_post/','php') as $file) require ($file);
      if (isset ($_POST['products_id']) && is_numeric($_POST['products_id'])) {
        $cart_quantity = (xtc_remove_non_numeric($_POST['products_qty']) + $cart_object->get_quantity(xtc_get_uprid($_POST['products_id'], isset($_POST['id'])?$_POST['id']:'')));
        if ($cart_quantity > MAX_PRODUCTS_QTY) {            
          $cart_quantity = MAX_PRODUCTS_QTY;
          $_SESSION['err_max_prod'] = true;   // error message for exceeded product quantity, noRiddle
          $_GET['max_prod_id'] = (int)$_POST['products_id'];
        }
        $cart_object->add_cart((int)$_POST['products_id'], $cart_quantity, isset($_POST['id'])?$_POST['id']:'');
      }
      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/cart_actions/add_product_before_redirect/','php') as $file) require ($file);
      if ($co_express === true) {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, 'express=on', 'SSL'));
      } else {
        xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters) . 'products_id=' . (int)$_POST['products_id'] . $info_message));
      }
      break;

    case 'check_gift':
      require_once (DIR_FS_INC . 'xtc_collect_posts.inc.php');
      xtc_collect_posts();
      break;

    // customer wants to add a quickie to the cart (called from a box)
    case 'add_a_quickie' :
      if (isset($_POST['quickie']) && $_POST['quickie'] != '') {
        $quicky = addslashes($_POST['quickie']);
        $quickie_query = xtc_db_query("SELECT products_fsk18,
                                              products_id
                                        FROM " . TABLE_PRODUCTS . "
                                       WHERE products_model = '" . $quicky . "'
                                         AND products_status = '1' " . 
                                         PRODUCTS_CONDITIONS
                                      );

        if (!xtc_db_num_rows($quickie_query)) {
          $quickie_query = xtc_db_query("SELECT products_fsk18,
                                                products_id
                                           FROM " . TABLE_PRODUCTS . "
                                          WHERE products_model LIKE '%" . $quicky . "%'
                                            AND products_status = '1' " .
                                            PRODUCTS_CONDITIONS
                                        );
        }
        if (xtc_db_num_rows($quickie_query) != 1) {
          xtc_redirect(xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords=' . $quicky, 'NONSSL'));
        }
        $quickie = xtc_db_fetch_array($quickie_query);
        if (xtc_has_product_attributes($quickie['products_id'])) {
          xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $quickie['products_id'], 'NONSSL'));
        } else {
          // check for FSK18
          if ($quickie['products_fsk18'] == '1' && $_SESSION['customers_status']['customers_fsk18'] == '1') {
            xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $quickie['products_id'], 'NONSSL'));
          }
          if ($_SESSION['customers_status']['customers_fsk18_display'] == '0' && $quickie['products_fsk18'] == '1') {
            xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $quickie['products_id'], 'NONSSL'));
          }
          if ($_POST['quickie'] != '') {
            $cart_quantity = ($cart_object->get_quantity(xtc_get_uprid($quickie['products_id'],''))+1);
            if ($cart_quantity > MAX_PRODUCTS_QTY) {
              $cart_quantity = MAX_PRODUCTS_QTY;
              $_SESSION['err_max_prod'] = true;   // error message for exceeded product quantity, noRiddle
              $_GET['max_prod_id'] = $quickie['products_id'];
            }
            $cart_object->add_cart($quickie['products_id'], $cart_quantity);
            xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters) . $info_message, 'NONSSL'));
          } else {
            xtc_redirect(xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords=' . $quicky, 'NONSSL'));
          }
        }
      } else {
        xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action'))));
      }
      break;

    // performed by the 'buy now' button in product listings and review page
    case 'buy_now':
      if (isset($_GET['BUYproducts_id'])) {
        $_GET['BUYproducts_id'] = (int)$_GET['BUYproducts_id'];
        // check permission to view product
        $permission_query = xtc_db_query("SELECT group_permission_" . $_SESSION['customers_status']['customers_status_id'] . " as customer_group,
                                                 products_fsk18
                                           from " . TABLE_PRODUCTS . "
                                          where products_id='" . $_GET['BUYproducts_id'] . "'");
        $permission = xtc_db_fetch_array($permission_query);

        // check for FSK18
        if ($permission['products_fsk18'] == '1' && $_SESSION['customers_status']['customers_fsk18'] == '1') {
          xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' .$_GET['BUYproducts_id'], 'NONSSL'));
        }
        if ($_SESSION['customers_status']['customers_fsk18_display'] == '0' && $permission['products_fsk18'] == '1') {
          xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' .$_GET['BUYproducts_id'], 'NONSSL'));
        }
        // check for customer group
        if (GROUP_CHECK == 'true') {
          if ($permission['customer_group'] != '1') {
            xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' .$_GET['BUYproducts_id']));
          }
        }
        if (xtc_has_product_attributes($_GET['BUYproducts_id'])) {
          xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' .$_GET['BUYproducts_id']));
        } else {
          if (isset ($cart_object)) {
            $cart_quantity = ($cart_object->get_quantity(xtc_get_uprid($_GET['BUYproducts_id'],''))+1);
            if ($cart_quantity > MAX_PRODUCTS_QTY) {
              $cart_quantity = MAX_PRODUCTS_QTY;
              $_SESSION['err_max_prod'] = true;   // error message for exceeded product quantity, noRiddle
              $_GET['max_prod_id'] = $_GET['BUYproducts_id'];
            }
            $cart_object->add_cart($_GET['BUYproducts_id'], $cart_quantity);
          } else {
            xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
          }
        }
      }
      xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters) . $info_message));
      break;

    case 'cust_order':
      if (isset ($_SESSION['customer_id']) && isset ($_GET['pid'])) {
        $_GET['pid'] = (int)$_GET['pid'];
        if (xtc_has_product_attributes($_GET['pid'])) {
          xtc_redirect(xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['pid']));
        } else {
          $cart_object->add_cart($_GET['pid'], $cart_object->get_quantity($_GET['pid']) + 1);
        }
      }
      xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters), 'NONSSL'));
      break;

    ## Paypal
    case 'paypal_cart_checkout':
      if (defined('MODULE_PAYMENT_PAYPALCART_STATUS')
          && MODULE_PAYMENT_PAYPALCART_STATUS == 'True')
      {
        require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
        $paypal_cart = new PayPalPayment('paypalcart');
        $paypal_cart->payment_redirect(true);
      }
      break;

    case 'wishlist_cart':
      if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
        if ($_SESSION['wishlist']->in_cart($_GET['BUYproducts_id'])) {
          $wishlist_content = $_SESSION['wishlist']->contents[$_GET['BUYproducts_id']];
          $attributes_array = ((isset($wishlist_content['attributes'])) ? $wishlist_content['attributes'] : '');
          $cart_quantity = (xtc_remove_non_numeric($wishlist_content['qty']) + $_SESSION['cart']->get_quantity(xtc_get_uprid($_GET['BUYproducts_id'], $attributes_array)));
          $products_id = xtc_get_prid($_GET['BUYproducts_id']);
          if ($cart_quantity > MAX_PRODUCTS_QTY) {            
            $cart_quantity = MAX_PRODUCTS_QTY;
            $_SESSION['err_max_prod'] = true;
            $_GET['max_prod_id'] = (int)$products_id;
          }
          $_SESSION['cart']->add_cart($products_id, $cart_quantity, $attributes_array);

          $prd_id = xtc_input_validation($_GET['BUYproducts_id'], 'products_id', '');
          $_SESSION['wishlist']->remove($prd_id);
        }
        xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters), 'NONSSL'));
      }
      break;

    case 'cart_wishlist':
      if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
        if ($_SESSION['cart']->in_cart($_GET['BUYproducts_id'])) {
          $wishlist_content = $_SESSION['cart']->contents[$_GET['BUYproducts_id']];
          $attributes_array = ((isset($wishlist_content['attributes'])) ? $wishlist_content['attributes'] : '');
          $cart_quantity = (xtc_remove_non_numeric($wishlist_content['qty']) + $_SESSION['wishlist']->get_quantity(xtc_get_uprid($_GET['BUYproducts_id'], $attributes_array)));
          $products_id = xtc_get_prid($_GET['BUYproducts_id']);
          if ($cart_quantity > MAX_PRODUCTS_QTY) {            
            $cart_quantity = MAX_PRODUCTS_QTY;
            $_SESSION['err_max_prod'] = true;
            $_GET['max_prod_id'] = (int)$products_id;
          }
          $_SESSION['wishlist']->add_cart($products_id, $cart_quantity, $attributes_array);

          $prd_id = xtc_input_validation($_GET['BUYproducts_id'], 'products_id', '');
          $_SESSION['cart']->remove($prd_id);
        }
        xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters), 'NONSSL'));
      }
      break;
 
    case 'add_order':
      if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])
          && isset($_SESSION['customer_id'])) 
      {
        $orders_info_query = xtc_db_query("SELECT customers_id 
                                             FROM ".TABLE_ORDERS." 
                                            WHERE orders_id = '".(int)$_GET['order_id']."'
                                              AND customers_id = '".(int)$_SESSION['customer_id']."'");
        if (xtc_db_num_rows($orders_info_query) > 0) {
          require_once (DIR_WS_CLASSES.'order.php');
          $order = new order((int)$_GET['order_id']);        
          $order_data_array = $order->getOrderData((int)$_GET['order_id']);
          
          if (is_array($order_data_array) && count($order_data_array) > 0) {
            foreach ($order_data_array as $order_data) {
              $attributes_array = array();
              if (is_array($order_data['PRODUCTS_ATTRIBUTES_ARRAY'])) {
                foreach ($order_data['PRODUCTS_ATTRIBUTES_ARRAY'] as $attributes_data) {
                  $attributes_array[$attributes_data['option_id']] = $attributes_data['value_id'];
                }
              }

              $products_id = $order_data['PRODUCTS_ID'];
              $cart_quantity = (xtc_remove_non_numeric($order_data['PRODUCTS_QTY']) + $cart_object->get_quantity(xtc_get_uprid($products_id, ((count($attributes_array) > 0) ? $attributes_array : ''))));
              if ($cart_quantity > MAX_PRODUCTS_QTY) {            
                $cart_quantity = MAX_PRODUCTS_QTY;
                $_SESSION['err_max_prod'] = true;
                $_GET['max_prod_id'] = (int)$products_id;
              }
              $cart_object->add_cart((int)$products_id, $cart_quantity, ((count($attributes_array) > 0) ? $attributes_array : ''));

            }
 
            if ($co_express === true) {
              xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, 'express=on', 'SSL'));
            } else {
              xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params($parameters) . $info_message));
            }
          }
        }
      }    
      xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params($parameters), $request_type));
      break;
         
    case 'add_order_product':
      if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])
          && isset($_GET['id']) && is_numeric($_GET['id'])
          && isset($_SESSION['customer_id'])) 
      {
        $orders_info_query = xtc_db_query("SELECT o.customers_id,
                                                  op.products_id,
                                                  op.products_quantity,
                                                  opa.orders_products_options_id,
                                                  opa.orders_products_options_values_id
                                             FROM ".TABLE_ORDERS." o
                                             JOIN ".TABLE_ORDERS_PRODUCTS." op
                                                  ON o.orders_id = op.orders_id
                                                     AND op.orders_products_id = '".(int)$_GET['id']."'
                                        LEFT JOIN ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." opa
                                                  ON op.orders_products_id = opa.orders_products_id
                                            WHERE o.orders_id = '".(int)$_GET['order_id']."'
                                              AND o.customers_id = '".(int)$_SESSION['customer_id']."'");
        if (xtc_db_num_rows($orders_info_query) > 0) {
          $attributes_array = array();
          while ($orders_info = xtc_db_fetch_array($orders_info_query)) {       
            if ($orders_info['orders_products_options_id'] != '') {
              $attributes_array[$orders_info['orders_products_options_id']] = $orders_info['orders_products_options_values_id'];
            }
            $products_id = $orders_info['products_id'];
            $products_quantity = $orders_info['products_quantity'];
          }      

          if (isset($products_id)) {
            $cart_quantity = (xtc_remove_non_numeric($products_quantity) + $cart_object->get_quantity(xtc_get_uprid($products_id, ((count($attributes_array) > 0) ? $attributes_array : ''))));
            if ($cart_quantity > MAX_PRODUCTS_QTY) {            
              $cart_quantity = MAX_PRODUCTS_QTY;
              $_SESSION['err_max_prod'] = true;
              $_GET['max_prod_id'] = (int)$products_id;
            }
            $cart_object->add_cart((int)$products_id, $cart_quantity, ((count($attributes_array) > 0) ? $attributes_array : ''));

            if ($co_express === true) {
              xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, 'express=on', 'SSL'));
            } else {
              xtc_redirect(xtc_href_link($goto, xtc_get_all_get_params(array_merge(array('id'), $parameters)) . $info_message));
            }
          }
        }
      }
      xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array_merge(array('id'), $parameters)), $request_type));
      break;

  }
}
?>