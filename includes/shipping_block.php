<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  $module_smarty = new Smarty;
  $shipping_block = '';
  if (xtc_count_shipping_modules() > 0) {
    $showtax = $_SESSION['customers_status']['customers_status_show_price_tax'];
    $module_smarty->assign('FREE_SHIPPING', $free_shipping);
    # free shipping or not...
    if ($free_shipping == true) {
      $module_smarty->assign('FREE_SHIPPING_TITLE', FREE_SHIPPING_TITLE);
      $module_smarty->assign('FREE_SHIPPING_DESCRIPTION', sprintf(FREE_SHIPPING_DESCRIPTION, $xtPrice->xtcFormat($free_shipping_value_over, true, 0, true)).xtc_draw_hidden_field('shipping', 'free_free'));
      $module_smarty->assign('FREE_SHIPPING_ICON', $quotes[$i]['icon']);
    } else {
      $radio_buttons = 0;
      #loop through installed shipping methods...
      for ($i = 0, $n = sizeof($quotes); $i < $n; $i ++) {
        if (!isset($quotes[$i]['error'])) {
          for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j ++) {
            # set the radio button to be checked if it is the method chosen
            $quotes[$i]['methods'][$j]['radio_buttons'] = $radio_buttons;
            $checked = ((isset($_SESSION['shipping']) && isset($_SESSION['shipping']['id']) && $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']) ? true : false);
            if (($checked == true) || ($n == 1 && $n2 == 1)) {
              $quotes[$i]['methods'][$j]['checked'] = 1;
            }
            if (($n > 1) || ($n2 > 1)) {
              if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 || !isset($quotes[$i]['tax'])) {
                $quotes[$i]['tax'] = 0;
              }
              $quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax']), true, 0, true);						
              $quotes[$i]['methods'][$j]['radio_field'] = xtc_draw_radio_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'], $checked, 'id="rd-'.($i+1).'"');
            } else {
              if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0) {
                $quotes[$i]['tax'] = 0;
              }
              $quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0), true, 0, true).xtc_draw_hidden_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id']);
            }
            $radio_buttons ++;
          }
        }
      }
      $module_smarty->assign('module_content', $quotes);
    }
    $module_smarty->caching = 0;
    $shipping_block = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_shipping_block.html');
  }
?>