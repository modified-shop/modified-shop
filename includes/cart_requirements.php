<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  

  // minimum/maximum order value
  if ($_SESSION['cart']->show_total() >= 0) {

    //check customers min-order by currency
    if ($xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) < $_SESSION['customers_status']['customers_status_min_order'] ) {
      $_SESSION['allow_checkout'] = 'false';
      $more_to_buy = $_SESSION['customers_status']['customers_status_min_order'] - $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total());
      $more_to_buy *= $xtPrice->currencies[$xtPrice->actualCurr]['value']; 
      $order_amount = $xtPrice->xtcFormat($more_to_buy, true);
      $min_order = $_SESSION['customers_status']['customers_status_min_order'];
      $min_order *= $xtPrice->currencies[$xtPrice->actualCurr]['value']; 
      $min_order = $xtPrice->xtcFormat($min_order, true);
      $smarty->assign('info_message_1', MINIMUM_ORDER_VALUE_NOT_REACHED_1);
      $smarty->assign('info_message_2', MINIMUM_ORDER_VALUE_NOT_REACHED_2);
      $smarty->assign('order_amount', $order_amount);
      $smarty->assign('min_order', $min_order);
    } 

    //check customers max-order by currency
    if ($_SESSION['customers_status']['customers_status_max_order'] != 0 && $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) > $_SESSION['customers_status']['customers_status_max_order']) {
      $_SESSION['allow_checkout'] = 'false';
      $less_to_buy = $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) - $_SESSION['customers_status']['customers_status_max_order'];
      $less_to_buy *= $xtPrice->currencies[$xtPrice->actualCurr]['value'];
      $order_amount = $xtPrice->xtcFormat($less_to_buy, true);
      $max_order = $_SESSION['customers_status']['customers_status_max_order'];
      $max_order *= $xtPrice->currencies[$xtPrice->actualCurr]['value']; 
      $max_order = $xtPrice->xtcFormat($max_order, true);  
      $smarty->assign('info_message_1', MAXIMUM_ORDER_VALUE_REACHED_1);
      $smarty->assign('info_message_2', MAXIMUM_ORDER_VALUE_REACHED_2);
      $smarty->assign('order_amount', $order_amount);
      $smarty->assign('min_order', $max_order);
    }
  }
  
  // error message for exceeded product quantity, noRiddle
  if (isset($_SESSION['err_max_prod'])) {
    $error_msg = array();
    for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
      if (isset($_SESSION['err_max_prod'][$i]) && !isset($_GET['products_id'])) {
        $emsg = sprintf(MAX_PROD_QTY_EXCEEDED, $products[$i]['name']);
        $error_msg[] = array(E_MSG => $emsg);
      } else if (isset($_SESSION['err_max_prod']) && (int)$_GET['max_prod_id'] == $products[$i]['id']) {
        $emsg = sprintf(MAX_PROD_QTY_EXCEEDED, $products[$i]['name']);
        $error_msg[] = array(E_MSG => $emsg);
      }
    }
    $smarty->assign('error_max_prod', $error_msg);
    unset($_SESSION['err_max_prod']);
  }
?>