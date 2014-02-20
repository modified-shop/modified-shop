<?php
/**
 * $Id: $
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 *
 * #todo: move econda.php code to a method of this class
 *
 */

  class econda{

    function __construct() {

      if (isset($_GET['action']) && !empty($action)) {
        $this->cart_actions($action);
      }

    }

    // cart_actions.php code extraction
    function cart_actions($action) {
        switch ($action) {
          case 'update_product':
            for ($i = 0, $n = sizeof($_POST['products_id']); $i < $n; $i++) {
              $cart_quantity = $_POST['cart_quantity'][$i] = xtc_remove_non_numeric($_POST['cart_quantity'][$i]);
              $_POST['products_id'][$i] = xtc_input_validation($_POST['products_id'][$i], 'products_id', '');
              if (in_array($_POST['products_id'][$i], (isset($_POST['cart_delete']) && is_array($_POST['cart_delete']) ? $_POST['cart_delete'] : array ()))) {
   	           	$_SESSION['econda_cart'][] = array(
                    'todo'     => 'del',
                    'id'       => $_POST['products_id'][$i],
                    'cart_qty' => $cart_quantity,
                    'old_qty'  => $_POST['old_qty'][$i]
                  );
              } else {
                if ($cart_quantity > MAX_PRODUCTS_QTY) $cart_quantity = MAX_PRODUCTS_QTY;
                $old_quantity = $_SESSION['cart']->get_quantity(xtc_get_uprid($_POST['products_id'][$i], $_POST['id'][$i]));
            		$_SESSION['econda_cart'][] = array(
                    'todo'     => 'update',
                    'id'       => $_POST['products_id'][$i],
                    'cart_qty' => $cart_quantity,
                    'old_qty'  => $old_quantity
                  );
              }
            }
          break;
          case 'add_product':
            if (isset ($_POST['products_id']) && is_numeric($_POST['products_id'])) {
              $cart_quantity = (xtc_remove_non_numeric($_POST['products_qty']) + $_SESSION['cart']->get_quantity(xtc_get_uprid($_POST['products_id'], isset($_POST['id'])?$_POST['id']:'')));
              if ($cart_quantity > MAX_PRODUCTS_QTY) $cart_quantity = MAX_PRODUCTS_QTY;
              $old_quantity = $_SESSION['cart']->get_quantity(xtc_get_uprid($_POST['products_id'], isset($_POST['id'])?$_POST['id']:''));
              $_SESSION['econda_cart'][] = array(
                  'todo'     => 'add',
                  'id'       => $_POST['products_id'],
                  'cart_qty' => $cart_quantity,
                  'old_qty'  => $old_quantity
                );
            }
          break;
          case 'buy_now':
            if (isset($_GET['BUYproducts_id'])) {
              $cart_quantity = ($_SESSION['cart']->get_quantity(xtc_get_uprid($_GET['BUYproducts_id'],''))+1);
              if ($cart_quantity > MAX_PRODUCTS_QTY) $cart_quantity = MAX_PRODUCTS_QTY;
              $old_quantity = $_SESSION['cart']->get_quantity($_GET['BUYproducts_id']);
              $_SESSION['econda_cart'][] = array(
                  'todo'     => 'add',
                  'id'       => $_POST['BUYproducts_id'],
                  'cart_qty' => $cart_quantity,
                  'old_qty'  => $old_quantity
                );
            }
          break;
        }
    }

   	function _loginUser() {
   		$_SESSION['login_success'] = 1;
   	}

  }

?>
