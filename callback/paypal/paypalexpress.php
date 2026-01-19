<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
include('includes/application_top.php');


// include needed classes
require_once(DIR_WS_CLASSES.'order.php');
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');

if (isset($_SESSION['paypal'])
    && isset($_SESSION['paypal']['OrderID'])
    )
{
  $paypal = new PayPalPaymentV2('paypalexpress');
  $PayPalOrder = $paypal->GetOrder($_SESSION['paypal']['OrderID']);
  
  if (!in_array($PayPalOrder->status, array('COMPLETED', 'APPROVED'))) {
    unset($_SESSION['paypal']);
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$paypal->code, 'NONSSL'));
  } else {
    if (isset($PayPalOrder->purchase_units[0]->shipping)) {
      $PayPalOrder->purchase_units[0]->shipping->address_array = $paypal->parse_address($PayPalOrder->purchase_units[0]->shipping);
    }
  
    if (isset($PayPalOrder->payer)) {
      $PayPalOrder->payer->address_array = $paypal->parse_address($PayPalOrder->payer);
    }
  
    $customers_data = array();
    foreach ($PayPalOrder->purchase_units[0]->shipping->address_array as $key => $value) {
      $customers_data['customers']['customers_'.$key] = $value;
      $customers_data['delivery']['delivery_'.$key] = $value;
      $customers_data['payment']['payment_'.$key] = $value;
      $customers_data['plain'][$key] = $value;
    }
    $customers_data['info']['gender'] = '';
    $customers_data['info']['dob'] = ((isset($PayPalOrder->payer->birth_date)) ? $PayPalOrder->payer->birth_date : '');    
    $customers_data['info']['email_address'] = $PayPalOrder->payer->email_address;
    if (isset($PayPalOrder->purchase_units[0]->shipping->email_address)) {
      $customers_data['info']['email_address'] = $PayPalOrder->purchase_units[0]->shipping->email_address;
    }
    $customers_data['info']['telephone'] = ((isset($PayPalOrder->payer->phone->phone_number->national_number)) ? $PayPalOrder->payer->phone->phone_number->national_number : '');
    if (isset($PayPalOrder->purchase_units[0]->shipping->phone_number)) {
      $customers_data['info']['telephone'] = $PayPalOrder->purchase_units[0]->shipping->phone_number->national_number;
    }
    
    if (isset($PayPalOrder->payer->name)) {
      $customers_data['customers']['customers_name'] = $PayPalOrder->payer->address_array['name'];
      $customers_data['customers']['customers_firstname'] = $PayPalOrder->payer->name->given_name;
      $customers_data['customers']['customers_lastname'] = $PayPalOrder->payer->name->surname;
  
      $customers_data['payment']['payment_name'] = $PayPalOrder->payer->address_array['name'];
      $customers_data['payment']['payment_firstname'] = $PayPalOrder->payer->name->given_name;
      $customers_data['payment']['payment_lastname'] = $PayPalOrder->payer->name->surname;
  
      $customers_data['plain']['name'] = $PayPalOrder->payer->address_array['name'];
      $customers_data['plain']['firstname'] = $PayPalOrder->payer->name->given_name;
      $customers_data['plain']['lastname'] = $PayPalOrder->payer->name->surname;
    }
    $customers_data = $paypal->decode_utf8($customers_data);
    
    if (!isset($_SESSION['customer_id'])
        && isset($customers_data['info']['email_address']) 
        && $customers_data['info']['email_address'] != ''
        ) 
    {
      $paypal->login_customer($customers_data);
    }
  
    if (!isset($_SESSION['customer_id'])
        || !isset($_SESSION['paypal']['cartID'])
        || $_SESSION['paypal']['cartID'] != $_SESSION['cart']->cartID
        )
    {
      // redirect
      unset($_SESSION['paypal']);
      xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$paypal->code, 'NONSSL'));
    }
             
    // sendto
    $_SESSION['sendto'] = $paypal->get_shipping_address($_SESSION['customer_id'], $customers_data['delivery']);
    $_SESSION['delivery_zone'] = $customers_data['delivery']['delivery_country']['iso_code_2'];
  
    // shipping
    $_SESSION['shipping'] = '';
    if (isset($PayPalOrder->purchase_units[0]->shipping->options[0])) {
      $quotes = $paypal->get_shipping_data(true);
      foreach ($quotes as $quote) {
        if (!isset($quote['error'])) {
          foreach ($quote['methods'] as $methods) {
            $id = sprintf("%u", crc32($quote['id'].'_'.$methods['id']));
            if ($PayPalOrder->purchase_units[0]->shipping->options[0]->id == $id) {
              $title = $quote['module'];
              if (!defined('SHOW_SHIPPING_MODULE_TITLE') || SHOW_SHIPPING_MODULE_TITLE == 'shipping_default') {
                $title .= ((trim($methods['title']) != '') ? ' ('.$methods['title'].')' : '');
              } elseif (SHOW_SHIPPING_MODULE_TITLE == 'shipping_custom' && parse_multi_language_value(CUSTOM_SHIPPING_TITLE, $_SESSION['language_code']) != '') {
                $title = parse_multi_language_value(CUSTOM_SHIPPING_TITLE, $_SESSION['language_code']);
              }
  
              $_SESSION['shipping'] = array (
                'id' => $quote['id'].'_'.$methods['id'], 
                'title' => $title, 
                'cost' => $methods['cost']
              );
            }
          }
        }
      }
    }
  
    $order = new order();
  
    if ($order->content_type == 'virtual' 
        || ($order->content_type == 'virtual_weight') 
        || ($_SESSION['cart']->count_contents_virtual() == 0)
        )
    {
      $_SESSION['shipping'] = false;
      $_SESSION['sendto'] = false;
    } elseif ($order->delivery['country']['iso_code_2'] != '') {
      $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
      if (isset($order->delivery['delivery_zone']) && $order->delivery['delivery_zone'] != '') {
        $_SESSION['delivery_zone'] = $order->delivery['delivery_zone'];
      }
    }
  
    // register a random ID in the session to check throughout the checkout procedure
    // against alterations in the shopping cart contents
    $_SESSION['cartID'] = $_SESSION['cart']->cartID;

    // payment
    $_SESSION['payment'] = 'paypalexpress';
  
    // billto
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
  
    if ($order->billing['country']['iso_code_2'] != '') {
      $_SESSION['billing_zone'] = $order->billing['country']['iso_code_2'];
    }
  
    // paypal
    $_SESSION['paypal']['payment_modules'] = 'paypalexpress.php';
    if (isset($PayPalOrder->payer->payer_id)) {
      $_SESSION['paypal']['PayerID'] = $PayPalOrder->payer->payer_id;
    }
    
    if (MODULE_PAYMENT_PAYPALEXPRESS_SHORT_CHECKOUT == 'False') {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true', 'SSL'));
  }
}