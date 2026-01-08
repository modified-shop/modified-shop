<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC.'get_country_id.inc.php');
  require_once(DIR_FS_INC.'get_external_content.inc.php');
  require_once(DIR_FS_INC.'xtc_get_countries.inc.php');

  // include needed classes
  require_once(DIR_WS_CLASSES.'order.php');
  require_once(DIR_WS_CLASSES.'order_total.php');
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');

  
  function get_shipping_methods() {
    global $xtPrice;
    
    $request_json = get_external_content('php://input', 3, false);
    $request = json_decode($request_json, true);
    
    if (!isset($request['id'])
        || !isset($_SESSION['paypal']['OrderID'])
        || $_SESSION['paypal']['OrderID'] != $request['id']
        )
    {
      return;
    }

    if (isset($_SESSION['customer_id'])) {
      $customer_id = $_SESSION['customer_id'];
    }
    unset($_SESSION['customer_id']);
    unset($_SESSION['sendto']);
    unset($_SESSION['billto']);
    
    $paypal = new PayPalPaymentV2('paypalexpress');
    
    if (isset($request['shipping_address'])) {
      $_SESSION['country'] = get_country_id($request['shipping_address']['country_code']);
    }
 
    if (isset($request['shipping_option'])) {
      $shipping_option_id = $request['shipping_option']['id'];
    }

    $countries_id = STORE_COUNTRY;
    if (isset($_SESSION['customer_country_id'])) {
      $countries = xtc_get_countriesList($_SESSION['customer_country_id']);
      if ($countries !== false) {
        $countries_id = $countries['countries_id'];
      }
    }
  
    if (isset($_SESSION['country'])) {
      $countries = xtc_get_countriesList($_SESSION['country']);
      $countries_id = (($countries !== false) ? $countries['countries_id'] : $countries_id);
      $_SESSION['country'] = $countries_id;
    }

    $order = $paypal->set_order_object();
        
    $quotes = $paypal->get_shipping_data(true);

    $shipping_option = array();
    if (is_array($quotes) && count($quotes) > 0) {
      foreach ($quotes as $quote) {
        if (!isset($quote['error'])) {
          foreach ($quote['methods'] as $methods) {
            if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 || !isset($quote['tax'])) {
              $quote['tax'] = 0;
            }
            $methods['price'] = $xtPrice->xtcFormat($xtPrice->xtcAddTax($methods['cost'], $quote['tax'], false), false);						
  
            $selected = false;
            $id = sprintf("%u", crc32($quote['id'].'_'.$methods['id']));
            if ((isset($shipping_option_id) && $shipping_option_id == $id)
                || !isset($shipping_option_id)
                )
            {
              $selected = true;
              $shipping_option_id = $id;
            }          
            $shipping_option[] = array(
              'id' => $id,
              'amount' => array(
                'currency_code' => $_SESSION['currency'],
                'value' => sprintf($paypal->numberFormat, $methods['price'])
              ),
              'type' => 'SHIPPING',
              'label' => decode_htmlentities($paypal->encode_utf8($quote['module'].(($methods['title'] != '') ? ' ('.$methods['title'].')' : ''))),
              'selected' => $selected
            );
            
            if ($selected === true) {
              $_SESSION['shipping'] = array (
                'id' => $quote['id'].'_'.$methods['id'], 
                'title' => $quote['module'], 
                'cost' => $methods['cost']
              );
              $order = $paypal->set_order_object();
            }
          }
        }
      }
    }
    
    $total = $order->info['total'];
    if (($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
         && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
         ) || ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
               && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0 
               && $order->delivery['country_id'] == STORE_COUNTRY
               )
        ) 
    {
      $total += $order->info['tax'];
    }
 
    if (isset($customer_id)) {
      $_SESSION['customer_id'] = $customer_id;
    }
     
    $response = array(
      'id' => $request['id'],
      'purchase_units' => array(
        array(
          'reference_id' => $request['purchase_units'][0]['reference_id'],
          'amount' => array(
            'currency_code' => $_SESSION['currency'],
            'value' => sprintf($paypal->numberFormat, $total),
          ),
          'shipping_options' => $shipping_option,
        )
      )
    );
        
    return $response;
  }
