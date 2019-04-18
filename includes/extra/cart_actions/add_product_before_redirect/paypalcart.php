<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (((isset($_POST['paypalcartexpress_x']) 
        && isset($_POST['paypalcartexpress_y'])
        ) || isset($_POST['paypalcartexpress'])
      )
      && $_SESSION['cart']->show_total() > 0
      ) 
  {
    // include needed functions
    require_once (DIR_FS_INC.'xtc_get_products_stock.inc.php');
    require_once (DIR_FS_INC.'check_stock_specials.inc.php');
    
    $products = $_SESSION['cart']->get_products();
    for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
      if (STOCK_CHECK == 'true') {
        $mark_stock = xtc_check_stock($products[$i]['id'], $products[$i]['quantity']);
        if ($mark_stock) {
          $_SESSION['any_out_of_stock'] = 1;
        }
      }
      if (STOCK_CHECK_SPECIALS == 'true' && $xtPrice->xtcCheckSpecial($products[$i]['id'])) {
        $mark_stock = check_stock_specials($products[$i]['id'], $products[$i]['quantity']);
        if ($mark_stock) {
          $_SESSION['any_out_of_stock'] = 1;
        }  
      }
    }
    
    $_SESSION['allow_checkout'] = 'true';
    if (STOCK_CHECK == 'true') {
      if (isset($_SESSION['any_out_of_stock']) && $_SESSION['any_out_of_stock'] == 1) {
        if (STOCK_ALLOW_CHECKOUT == 'true') {
          $_SESSION['allow_checkout'] = 'true';
        } else {
          $_SESSION['allow_checkout'] = 'false';
        }
      } else {
        $_SESSION['allow_checkout'] = 'true';
      }
    }
  
    if ($_SESSION['allow_checkout'] == 'true') {
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'action=paypal_cart_checkout', 'SSL'));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
    }
  } 
?>