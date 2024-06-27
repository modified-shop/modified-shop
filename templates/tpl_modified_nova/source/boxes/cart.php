<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include smarty
  include (DIR_FS_BOXES_INC . 'smarty_default.php');

  // include needed functions
  require_once (DIR_FS_INC.'xtc_get_products_stock.inc.php');
  require_once (DIR_FS_INC.'check_stock_specials.inc.php');

  // define defaults
  $any_out_of_stock = false;
  $products_cart = array ();
  $qty = 0;

  if ($_SESSION['cart']->count_contents() > 0) {
    $total = $_SESSION['cart']->show_total();

    // build array with cart content and count quantity  
    if (strpos($PHP_SELF, FILENAME_LOGOFF) === false) {
      $products = $_SESSION['cart']->get_products();
      for ($i = 0, $n = count($products); $i < $n; $i++) {
        $del_button = '<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'box', 'prd_id')).'action=remove_product&box=cart&prd_id=' . $products[$i]['id'], 'NONSSL') . '">' . xtc_image_button('cart_del.gif', IMAGE_BUTTON_DELETE) . '</a>';
        $del_link = '<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'box', 'prd_id')).'action=remove_product&box=cart&prd_id=' . $products[$i]['id'], 'NONSSL') . '">' . IMAGE_BUTTON_DELETE . '</a>';
      
        $image = '';
        if ($products[$i]['image'] != '') {
          $image = $product->productImage($products[$i]['image'],'thumbnail');
        }
        
        $mark_stock = '';
        if (STOCK_CHECK == 'true') {
          $mark_stock = xtc_check_stock($products[$i]['id'], $products[$i]['quantity'], $products[$i]['stock']);
          if ($mark_stock) $any_out_of_stock = true;
        }
  
        if (STOCK_CHECK_SPECIALS == 'true' && $xtPrice->xtcCheckSpecial($products[$i]['id'])) {
          $mark_stock = check_stock_specials($products[$i]['id'], $products[$i]['quantity']);
          if ($mark_stock) $any_out_of_stock = true;
        }

        $qty += $products[$i]['quantity'];
        
        $products_cart[] = array (
          'QTY' => $products[$i]['quantity'],
          'LINK' => xtc_href_link(FILENAME_PRODUCT_INFO, xtc_get_all_get_params_include(array('language')).'products_id='.$products[$i]['id']),
          'IMAGE' => $image,
          'NAME' => $products[$i]['name'],
          'BUTTON_DELETE' => $del_button,
          'LINK_DELETE' => $del_link,
          'MARK_STOCK' => $mark_stock,
        );
      }
    }
  }

  if (STOCK_CHECK == 'true' && $any_out_of_stock === true) {
    if (STOCK_ALLOW_CHECKOUT == 'true') {
      $messageStack->add('box_cart', OUT_OF_STOCK_CAN_CHECKOUT);
    } else {
      $messageStack->add('box_cart', OUT_OF_STOCK_CANT_CHECKOUT);
    }
  }
  
  if ($messageStack->size('box_cart') > 0) {
    $box_smarty->assign('error_message', $messageStack->output('box_cart'));
  }

  $box_smarty->assign('deny_cart', strpos($PHP_SELF, 'checkout') !== false ? 'true' : 'false'); // no cart at the checkout
  $box_smarty->assign('products', $products_cart);
  $box_smarty->assign('PRODUCTS', $qty);
  $box_smarty->assign('empty', $qty > 0 ? 'false' : 'true');
  $box_smarty->assign('ACTIVATE_GIFT', ((ACTIVATE_GIFT_SYSTEM == 'true' && ((defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true') || (defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && MODULE_ORDER_TOTAL_COUPON_STATUS == 'true'))) ? 'true' : false));

  // GV Code
  if (isset($_SESSION['customer_id'])) {
    $gv_query = xtc_db_query("SELECT amount
                                FROM ".TABLE_COUPON_GV_CUSTOMER."
                               WHERE customer_id = '".(int)$_SESSION['customer_id']."'");
    if (xtc_db_num_rows($gv_query) > 0) {
      $gv_result = xtc_db_fetch_array($gv_query);
      if ($gv_result['amount'] > 0) {
        $box_smarty->assign('GV_AMOUNT', $xtPrice->xtcFormat($gv_result['amount'], true, 0, true));
      }
    }
  }

  $box_smarty->assign('LINK_CART', xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
  $box_smarty->assign('LINK_CHECKOUT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  
  if (defined('SHOW_ADD_QUICKIE') && SHOW_ADD_QUICKIE === true) {
    $box_smarty->assign('FORM_ACTION', xtc_draw_form('quick_add', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array ('action')) . 'action=add_a_quickie', $request_type)));
    $box_smarty->assign('INPUT_FIELD', xtc_draw_input_field('quickie', '', 'class="addquickie_input" id="input_quick_add"'));
    $box_smarty->assign('SUBMIT_BUTTON', xtc_image_submit('button_add_quick.gif', BOX_HEADING_ADD_PRODUCT_ID));
    $box_smarty->assign('FORM_END', '</form>');
  }
  
  $box_smarty->caching = 0;
  $box_cart = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_cart.html');

  $smarty->assign('box_CART', $box_cart);
