<?php
/* -----------------------------------------------------------------------------------------
   $Id: shipping_estimate.php 6035 2013-11-08 10:46:36Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (write_customers_status.php,v 1.8 2003/08/1); www.nextcommerce.org
   (c) 2006 xtCommerce (write_customers_status.php)
   
   Released under the GNU General Public License
   --------------------------------------------------------------------------------------- 
   
   based on Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
// 2013-12-31
// http://rpa-com.de -web28- add support for //MAXIMALE VERSANDKOSTEN
// http://neunzehn83.de/blog/2011/03/09/xtc-land-dropdown-im-warenkorb/
// http://www.xtc-modified.org/forum/topic.php?id=9883

if (!defined('SHOW_ALWAYS_LANG_DROPDOWN')) {
  define('SHOW_ALWAYS_LANG_DROPDOWN', true); // true: Zeigt immer das Länderauswahlfeld an - false: Zeigt Länderauswahlfeld nur bei nicht eingeloggten Kunden
}
require_once (DIR_WS_CLASSES.'order.php');
require_once (DIR_FS_INC.'xtc_get_country_list.inc.php');

$order = new order();
$total_weight = $_SESSION['cart']->show_weight();
$total_count = $_SESSION['cart']->count_contents();
  
$selected = isset($_SESSION['customer_country_id']) ? $_SESSION['customer_country_id'] : STORE_COUNTRY;
if (!isset($_SESSION['customer_id']) || SHOW_ALWAYS_LANG_DROPDOWN) {
  if (isset($_SESSION['country'])) {
    $selected = $_SESSION['country'];
  } 
  $module_smarty->assign('SELECT_COUNTRY', _SHIPPING_TO. xtc_get_country_list(array ('name' => 'country'), (int)$selected, 'onchange="this.form.submit()"'));
}

if (!isset($order->delivery['country']['iso_code_2']) || $order->delivery['country']['iso_code_2'] == ''  || SHOW_ALWAYS_LANG_DROPDOWN) {
  unset($_SESSION['shipping']);
  $delivery_zone_query = xtc_db_query("SELECT countries_id, 
                                              countries_iso_code_2, 
                                              countries_name 
                                         FROM ".TABLE_COUNTRIES." 
                                        WHERE countries_id = '". (int)$selected."'
                                     ");
  $delivery_zone = xtc_db_fetch_array($delivery_zone_query);
  
  $order->delivery['country']['iso_code_2'] = $delivery_zone['countries_iso_code_2'];
  $order->delivery['country']['title'] = $delivery_zone['countries_name'];
  $order->delivery['country']['id'] = $delivery_zone['countries_id'];
  $order->delivery['country_id'] = $delivery_zone['countries_id'];
  $order->delivery['zone_id'] = 0;
}

if (!isset($order->info['total'])) {
  $order->info['total'] = $_SESSION['cart']->show_total();
}

$_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];

//suppot downloads and gifts
if ($order->content_type == 'virtual' || ($order->content_type == 'virtual_weight') || ($_SESSION['cart']->count_contents_virtual() == 0)) {
    $shipping_content = array();
    $shipping_content[] = array('NAME' => _SHIPPING_FREE);
} else {
    require (DIR_WS_CLASSES.'shipping.php');
    $shipping = new shipping;
    
    $free_shipping = $free_shipping_freeamount = $has_freeamount = false;
    require (DIR_WS_MODULES.'order_total/ot_shipping.php');
    include (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_shipping.php');
    $ot_shipping = new ot_shipping;
    $ot_shipping->process();

    // load all enabled shipping modules
    $quotes = $shipping->quote();

    foreach ($quotes as $quote) {
      if ($quote['id'] == 'freeamount') {
        $has_freeamount = true;
        if (isset($quote['methods'])) {
          $free_shipping_freeamount = true;
          break;
        }
      }
    }

    $shipping_content = array ();
    if ($free_shipping == true) {
        $shipping_content[] = array(
            'NAME' => FREE_SHIPPING_TITLE,
            'VALUE' => $xtPrice->xtcFormat(0, true, 0, true)
        );
    } else if ($free_shipping_freeamount) {
        $shipping_content[] = array(
            'NAME' => $quote['module'] . ' - ' . $quote['methods'][0]['title'],
            'VALUE' => $xtPrice->xtcFormat(0, true, 0, true)
        );
    } else {
        if ($has_freeamount) {
          $module_smarty->assign('FREE_SHIPPING_INFO', sprintf(FREE_SHIPPING_DESCRIPTION, $xtPrice->xtcFormat(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER, true, 0, true)));
        }
        $i = 0;
        foreach ($quotes AS $quote) {
          if ($quote['id'] != 'freeamount') { 
            //BOC web28 Error Fix
            if (!isset($quote['error']) || (isset($quote['error']) && trim($quote['error']) == '')) {      
              $quote['methods'][0]['cost'] = $xtPrice->xtcCalculateCurr($quote['methods'][0]['cost']);
              $total += ((isset($quote['tax']) && $quote['tax'] > 0) ? $xtPrice->xtcAddTax($quote['methods'][0]['cost'],$quote['tax']) : (!empty($quote['methods'][0]['cost']) ? $quote['methods'][0]['cost'] : '0'));
              $shipping_content[$i] = array(
                'NAME' => $quote['module'] . ' - ' . $quote['methods'][0]['title'],
                'VALUE' => $xtPrice->xtcFormat(((isset($quote['tax']) && $quote['tax'] > 0) ? $xtPrice->xtcAddTax($quote['methods'][0]['cost'],$quote['tax']) : (!empty($quote['methods'][0]['cost']) ? $quote['methods'][0]['cost'] : '0')), true)
                );
            } else {
              $shipping_content[$i] = array(
                'NAME' => $quote['error'] . ' - ' . $quote['methods'][0]['title'],
                'VALUE' => ''
                );
            }
            //EOC web28 Error Fix
            $i++;
          }
        }
    }

    if (sizeof($quotes) < 1) {
      $shipping_content[] = array('NAME' => _MODULE_INVALID_SHIPPING_ZONE);
    }
    if (sizeof($shipping_content) < 1) {  
      $shipping_content[] = array('NAME' => _MODULE_UNDEFINED_SHIPPING_RATE); 
    }
}

#unset($_SESSION['billto']);
unset($_SESSION['delivery_zone']);
$module_smarty->assign('shipping_content', $shipping_content);
$module_smarty->assign('COUNTRY', $order->delivery['country']['title']);

if (count($shipping_content) <= 1) {
  $module_smarty->assign('total', $xtPrice->xtcFormat($total, true));
}
?>