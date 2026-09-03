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
  require_once(DIR_FS_INC.'get_external_content.inc.php');
  require_once(DIR_FS_INC.'xtc_get_countries.inc.php');

  // include needed classes
  require_once(DIR_WS_CLASSES.'order.php');
  require_once(DIR_WS_CLASSES.'order_total.php');
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');


  function get_shipping_methods() {
    global $order, $xtPrice;

    $request_json = get_external_content('php://input', 3, false);
    $request = json_decode($request_json, true);

    $paypal = new PayPalPaymentV2('paypalexpress');

    if (!isset($request['id'])
        || !isset($_SESSION['paypal']['OrderID'])
        || $_SESSION['paypal']['OrderID'] != $request['id']
        )
    {
      $paypal->LoggingManager->log('WARNING', 'Wallet get_shipping_methods aborted', array(
        'reason' => 'order id mismatch',
        'request_id' => (isset($request['id']) ? $request['id'] : null),
        'session_order_id' => (isset($_SESSION['paypal']['OrderID']) ? $_SESSION['paypal']['OrderID'] : null),
      ));
      return;
    }

    $session_backup = array();
    foreach (array('customer_id', 'sendto', 'billto') as $session_key) {
      if (array_key_exists($session_key, $_SESSION)) {
        $session_backup[$session_key] = $_SESSION[$session_key];
      }
    }

    try {
      unset($_SESSION['customer_id']);
      unset($_SESSION['sendto']);
      unset($_SESSION['billto']);

      if (isset($request['shipping_contact']) && is_array($request['shipping_contact'])) {
        // Apple Pay / Google Pay post the wallet contact shape
        $_SESSION['paypal']['contact']['shipping_quote'] = $request['shipping_contact'];
      } elseif (isset($request['shipping_address']) && is_array($request['shipping_address'])) {
        // PayPal's server-side SHIPPING_OPTIONS callback posts its own address shape
        $_SESSION['paypal']['contact']['shipping_quote'] = $paypal->callback_address_to_contact($request['shipping_address']);
      }

      $shipping_contact = isset($_SESSION['paypal']['contact']['shipping_quote'])
                          && is_array($_SESSION['paypal']['contact']['shipping_quote'])
                          ? $_SESSION['paypal']['contact']['shipping_quote']
                          : array();
      $country_code = isset($shipping_contact['countryCode']) ? trim($shipping_contact['countryCode']) : '';
      $postcode = isset($shipping_contact['postalCode']) ? trim($shipping_contact['postalCode']) : '';

      // only the country is load bearing here, countries without a postal code system stay quotable
      if ($country_code == '') {
        $paypal->LoggingManager->log('WARNING', 'Wallet get_shipping_methods aborted', array(
          'reason' => 'missing country code',
          'postcode' => $postcode,
        ));
        return;
      }

      $shipping_contact['countryCode'] = strtoupper($country_code);
      $shipping_contact['postalCode'] = $postcode;
      $_SESSION['paypal']['contact']['shipping_quote'] = $shipping_contact;

      $shipping_address = $paypal->parse_contact($shipping_contact);
      if (empty($shipping_address['country_id'])) {
        $paypal->LoggingManager->log('WARNING', 'Wallet get_shipping_methods aborted', array(
          'reason' => 'unknown country',
          'country_code' => $shipping_contact['countryCode'],
        ));
        return;
      }
      $_SESSION['country'] = $shipping_address['country_id'];

      if (isset($request['shipping_option'])
          && is_array($request['shipping_option'])
          && isset($request['shipping_option']['id'])
          && is_scalar($request['shipping_option']['id'])
          )
      {
        $shipping_option_id = (string)$request['shipping_option']['id'];
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

      // reinitialize the price class to get the correct prices
      $xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
      $_SESSION['cart']->calculate();

      $order = $paypal->apply_address_to_delivery($paypal->set_order_object(), $shipping_address);

      $quotes = $paypal->get_shipping_data(true);

      $shipping_option = array();
      $shipping_session = array();
      if (is_array($quotes) && count($quotes) > 0) {
        foreach ($quotes as $quote) {
          if (!isset($quote['error'])) {
            foreach ($quote['methods'] as $methods) {
              if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 || !isset($quote['tax'])) {
                $quote['tax'] = 0;
              }
              $methods['price'] = $xtPrice->xtcFormat($xtPrice->xtcAddTax($methods['cost'], $quote['tax'], false), false);

              $id = sprintf("%u", crc32($quote['id'].'_'.$methods['id']));
              $shipping_option[] = array(
                'id' => $id,
                'amount' => array(
                  'currency_code' => $_SESSION['currency'],
                  'value' => sprintf($paypal->numberFormat, $methods['price'])
                ),
                'type' => 'SHIPPING',
                'label' => decode_htmlentities($paypal->encode_utf8($quote['module'].(($methods['title'] != '') ? ' ('.$methods['title'].')' : ''))),
                'selected' => false
              );

              $shipping_session[$id] = array(
                'id' => $quote['id'].'_'.$methods['id'],
                'title' => $quote['module'],
                'cost' => $methods['cost']
              );
            }
          }
        }
      }

      if (empty($shipping_option)) {
        $paypal->LoggingManager->log('INFO', 'Wallet get_shipping_methods no options', array(
          'country' => (isset($_SESSION['country']) ? $_SESSION['country'] : null),
        ));
      } else {
        // a previously chosen method can be gone after an address change, fall back to the first one
        if (!isset($shipping_option_id) || !isset($shipping_session[$shipping_option_id])) {
          if (isset($shipping_option_id)) {
            $paypal->LoggingManager->log('INFO', 'Wallet get_shipping_methods option unavailable', array(
              'requested' => $shipping_option_id,
              'available' => implode(', ', array_keys($shipping_session)),
            ));
          }
          $shipping_option_id = $shipping_option[0]['id'];
        }

        foreach ($shipping_option as $key => $option) {
          $shipping_option[$key]['selected'] = ($option['id'] == $shipping_option_id);
        }

        $_SESSION['shipping'] = $shipping_session[$shipping_option_id];
        $order = $paypal->apply_address_to_delivery($paypal->set_order_object(), $shipping_address);
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

      return array(
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
    } finally {
      foreach (array('customer_id', 'sendto', 'billto') as $session_key) {
        unset($_SESSION[$session_key]);
        if (array_key_exists($session_key, $session_backup)) {
          $_SESSION[$session_key] = $session_backup[$session_key];
        }
      }
    }
  }
