<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// define some tables
define('TABLE_KLARNA_PAYMENTS', 'klarna_payments');


//include needed functions
require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');


// include needed classes
require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaPaymentBase.php');
require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');


// language
if (is_file(DIR_FS_EXTERNAL.'klarna/lang/'.$_SESSION['language'].'.php')) {
  require_once(DIR_FS_EXTERNAL.'klarna/lang/'.$_SESSION['language'].'.php');
} else {
  require_once(DIR_FS_EXTERNAL.'klarna/lang/english.php');
}


class KlarnaPayment extends KlarnaPaymentBase {

  function __construct($code) {
    $this->code = $code;

    // logger
    $this->logger = new LoggingManager(DIR_FS_LOG.'mod_%s_%s.log', 'info', 'error');

    KlarnaPaymentBase::init();

    $this->merchant_id = MODULE_PAYMENT_KLARNA_MERCHANT_ID;
    $this->shared_secret = MODULE_PAYMENT_KLARNA_SHARED_SECRET;
    
    if (MODULE_PAYMENT_KLARNA_MODE == 'LIVE') {
      $this->api_endpoint = Klarna\Rest\Transport\ConnectorInterface::EU_BASE_URL;
    } else {
      $this->api_endpoint = Klarna\Rest\Transport\ConnectorInterface::EU_TEST_BASE_URL;
    }
    
    $this->setConnector();
  }


  function setConnector() {
    require_once(DIR_FS_INC.'get_database_version.inc.php');
    $db_version = get_database_version();
    
    $user_agent = new Klarna\Rest\Transport\UserAgent();
    $user_agent->setField('modified-eCommerce-Shopsoftware', 'v', $db_version['plain']);
    $user_agent->setField('Klarna', 'v', $this->klarna_version);
    
    $this->connector = Klarna\Rest\Transport\GuzzleConnector::create(
      $this->merchant_id,
      $this->shared_secret,
      $this->api_endpoint,
      $user_agent
    );
  }


  function getKlarnaCheckout($shipping_content = array()) {
    global $xtPrice, $main;
    
    $order_array = $this->getOrderData();
    $order_array['merchant_urls'] = $this->getMerchantUrl();
        
    if (count($shipping_content) > 0) {
      $order_array['shipping_options'] = $this->getShippingData($shipping_content);
    }
        
    $options = new stdclass();
    $options->allow_separate_shipping_address = true;    
    $order_array['options'] = $options;
        
    $valid = false;
    if (isset($_SESSION['klarna'])
        && array_key_exists('order_id', $_SESSION['klarna'])
        && array_key_exists('customer_id', $_SESSION['klarna'])
        && $_SESSION['klarna']['customer_id'] == $_SESSION['customer_id']
        && array_key_exists('cart_id', $_SESSION['klarna'])
        && $_SESSION['klarna']['cart_id'] == $_SESSION['cart']->cartID
        )
    {
      $checkout = $this->fetchKlarnaCheckout($_SESSION['klarna']['order_id']);
      if (strtolower($checkout['status']) == 'checkout_incomplete') {
        $valid = true;
      }
    }
    
    if ($valid === false) {
      try {
        $checkout = new Klarna\Rest\Checkout\Order($this->connector);
        $resonse = $checkout->create($order_array);

        // set session
        $_SESSION['klarna'] = array(
          'order_id' => $resonse->getId(),
          'html_snippet' => $resonse['html_snippet'],
          'customer_id' => ((isset($_SESSION['customer_id'])) ? $_SESSION['customer_id'] : 0),
          'cart_id' => $_SESSION['cart']->cartID,
        );
      } catch (Exception $e) {
        $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      }
    }
  }


  function fetchKlarnaCheckout($order_id) {
    try {
      $checkout = new Klarna\Rest\Checkout\Order($this->connector, $order_id);
      $resonse = $checkout->fetch();
      
      return $resonse;
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function updateKlarnaCheckout($order_array) {
    try {
      $checkout = new Klarna\Rest\Checkout\Order($this->connector, $_SESSION['klarna']['order_id']);
      $response = $checkout->update($order_array);
            
      return $response;
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function getKlarnaSession() {
    $order_array = $this->getOrderData(true);
        
    try {
      $session = new Klarna\Rest\Payments\Sessions($this->connector);
      $resonse = $session->create($order_array);
        
      // set session
      $_SESSION['klarna'] = array(
        'session_id' => $resonse->getId(),
        'client_token' => $resonse['client_token'],
        'methods' => $resonse['payment_method_categories'],
        'sendto' => $_SESSION['sendto'],
        'sendto_id' => $this->get_country_id($_SESSION['sendto']),
        'billto' => $_SESSION['billto'],
        'billto_id' => $this->get_country_id($_SESSION['billto']),
        'cart_id' => $_SESSION['cart']->cartID,
        'time_created' => time(),
      );
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    } 
  }


  function readKlarnaSession($session_id) {
    try {
      $session = new Klarna\Rest\Payments\Sessions($this->connector, $session_id);
      $session->fetch();
      
      return $session->getArrayCopy();
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function updateMerchantReference($order_id, $reference1, $reference2) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->updateMerchantReferences(array(
        'merchant_reference1' => $reference1,
        'merchant_reference2' => $reference2,
      ));
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function fetchOrder($order_id) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->fetch();
      
      return $management->getArrayCopy();
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function acknowledgeOrder($order_id) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->acknowledge();
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      
      return $e->getMessage();
    }
  }


  function cancelOrder($order_id) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->cancel();
      
      return TEXT_KLARNA_TRANSACTION_CANCEL;
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      
      return $e->getMessage();
    }
  }


  function captureCompleteOrder($oID, $order_id) {
    global $xtPrice;
    
    $order = new order($oID);
    $this->captureOrder($order->info['pp_total'], $order_id);
  }


  function captureOrder($amount, $order_id) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->createCapture(array(
        'captured_amount' => $this->format_amount($amount),
      ));

      return TEXT_KLARNA_TRANSACTION_CAPTURE;
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      $_SESSION['klarna_error'] = $e->getMessage();
    }
  }


  function refundOrder($amount, $order_id) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->refund(array(
        'refunded_amount' => $this->format_amount($amount),
      ));
      
      return TEXT_KLARNA_TRANSACTION_REFUND;
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      $_SESSION['klarna_error'] = $e->getMessage();
    }
  }


  function getMerchantUrl() {
    $merchant_url = array(
      'terms'                  => xtc_href_link(FILENAME_CONTENT, 'coID=3', 'SSL'),
      'cancellation_terms'     => xtc_href_link(FILENAME_CONTENT, 'coID='.REVOCATION_ID, 'SSL'),

      'checkout'               => xtc_href_link(FILENAME_CHECKOUT_PAYMENT, xtc_session_name().'='.xtc_session_id(), 'SSL', false),
      'confirmation'           => xtc_href_link('callback/klarna/confirmation.php', xtc_session_name().'='.xtc_session_id(), 'SSL', false),
      
      'address_update'         => xtc_href_link('callback/klarna/callback.php', xtc_session_name().'='.xtc_session_id(), 'SSL', false),
      'shipping_option_update' => xtc_href_link('callback/klarna/callback.php', xtc_session_name().'='.xtc_session_id(), 'SSL', false),
      'country_change'         => xtc_href_link('callback/klarna/callback.php', xtc_session_name().'='.xtc_session_id(), 'SSL', false),

      'validation'             => xtc_href_link('callback/klarna/validation.php', xtc_session_name().'='.xtc_session_id(), 'SSL', false),
      
      'push'                   => xtc_href_link('callback/klarna/notification.php', '', 'SSL', false).'?type=push&orders_id={checkout.order.id}',
      'notification'           => xtc_href_link('callback/klarna/notification.php', '', 'SSL', false).'?type=notify&orders_id={checkout.order.id}',
    );
    
    return $merchant_url;
  }


  function getShippingData($shipping_content = array()) {
    global $xtPrice;
    
    if (count($shipping_content) > 0) {
      $shipping_options = array();
      foreach ($shipping_content as $shipping) {
        if (isset($shipping['QUOTE'])) {
          $price = $xtPrice->xtcAddTax($shipping['QUOTE']['methods'][0]['cost'], $shipping['QUOTE']['tax']);
        
          $shipping_options[] = array(
            'id' => $shipping['QUOTE']['id'].'_'.$shipping['QUOTE']['methods'][0]['id'],
            'name' => decode_htmlentities(strip_tags($shipping['QUOTE']['module'])),
            'description' => decode_htmlentities(strip_tags($shipping['QUOTE']['methods'][0]['title'])),
            'price' => $this->format_amount($price),
            'tax_amount' => $this->format_amount($price - $xtPrice->xtcCalculateCurr($shipping['QUOTE']['methods'][0]['cost'])),
            'tax_rate' => $this->format_amount($shipping['QUOTE']['tax']),
            'preselected' => ((isset($_SESSION['shipping']) && is_array($_SESSION['shipping']) && array_key_exists('id', $_SESSION['shipping']) && $shipping['QUOTE']['id'].'_'.$shipping['QUOTE']['methods'][0]['id'] == $_SESSION['shipping']['id']) ? true : false),
          );
        }
      }
      
      return $shipping_options;
    }
  }


  function getOrderData($minimal = false) {
    global $xtPrice, $product;
    
    $order = $this->get_order();
    
    $add_tax = false;
    if ($_SESSION['customers_status']['customers_status_add_tax_ot'] == '1'
        || ($_SESSION['customers_status']['customers_status_add_tax_ot'] == '0'
            && $_SESSION['customers_status']['customers_status_show_price_tax'] == '0'
            && $order->delivery['country_id'] == STORE_COUNTRY
            )
        )
    {
      $add_tax = true;
    }
        
    $i = 0;
    $tax_total = 0;
    $products_array = array();
    foreach ($order->products as $products) {
      $amount = $products['price'];
      if ($add_tax === true) {
        $amount = $this->xtcAddTax($amount, $products['tax']);
      }
      $type = $xtPrice->get_content_type_product($products['id']);
      
      $products_array[$i] = array(
        'type' => (($type == 'virtual') ? 'digital' : 'physical'),
        'reference' => $products['id'],
        'name' => $products['name'],
        'quantity' => $products['qty'],
        'unit_price' => $this->format_amount($amount),
        'tax_rate' => $this->format_amount($products['tax']),
        'total_amount' => $this->format_amount($amount * $products['qty']),
        'total_tax_amount' => $this->format_amount($xtPrice->xtcGetTax(($amount * $products['qty']), $products['tax'])),
      );
      
      $products['image'] = xtc_get_products_image($products['id']);
      if ($products['image'] != '') {
        $image_url = $product->productImage($products['image'],'thumbnail');
        if ($image_url != '') {
          $products_array[$i]['image_url'] = $image_url;
        }
      }
      
      $tax_total += $products_array[$i]['total_tax_amount'];
      $i ++;
    }
        
    if (isset($_SESSION['shipping']) && $_SESSION['shipping'] !== false) {
      $shipping_method = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
      $tax_class_id = constant('MODULE_SHIPPING_'.strtoupper($shipping_method).'_TAX_CLASS');
      $tax = $xtPrice->TAX[$tax_class_id];
      $shipping_cost = ((isset($order->info['pp_shipping'])) ? $order->info['pp_shipping'] : $order->info['shipping_cost']);
      if ($add_tax === true) {
        $shipping_cost = $this->xtcAddTax($shipping_cost, $tax);
      }
      
      $products_array[$i] = array(
        'type' => 'shipping_fee',
        'reference' => $_SESSION['shipping']['id'],
        'name' => $order->info['shipping_method'],
        'quantity' => 1,
        'unit_price' => $this->format_amount($shipping_cost),
        'tax_rate' => $this->format_amount($tax),
        'total_amount' => $this->format_amount($shipping_cost),
        'total_tax_amount' => $this->format_amount($xtPrice->xtcGetTax($shipping_cost, $tax)),
      );
      
      $tax_total += $products_array[$i]['total_tax_amount'];
      $i ++;
    }
        
    $order_amount = $order_tax_amount = 0;
    foreach ($order->totals as $total) {
      switch ($total['code']) {
        case 'ot_subtotal':
        case 'ot_subtotal_no_tax':
        case 'ot_shipping':
          break;
        
        case 'ot_tax':
          $order_tax_amount += $total['value'];
          break;
          
        case 'ot_total':
          $order_amount += $total['value'];
          break;

        default:              
          $tax_class_id = constant('MODULE_ORDER_TOTAL_'.strtoupper(substr($total['code'], 3)).'_TAX_CLASS');
          $tax = $xtPrice->TAX[$tax_class_id];
          $amount = $total['value'];
          if ($add_tax === true) {
            $amount = $this->xtcAddTax($amount, $tax);
          }
          
          $products_array[$i] = array(
            'type' => (($total['value'] > 0) ? 'surcharge' : 'discount'),
            'reference' => $total['code'],
            'name' => $total['title'],
            'quantity' => 1,
            'unit_price' => $this->format_amount($amount),
            'tax_rate' => $this->format_amount(($tax != '') ? $tax : 0),
            'total_amount' => $this->format_amount($amount),
            'total_tax_amount' => $this->format_amount($xtPrice->xtcGetTax($amount, $tax)),
          );
          
          $tax_total += $products_array[$i]['total_tax_amount'];
          $i ++;
          break;
      }
    }
    
    $country = xtc_get_countriesList(STORE_COUNTRY);    
    
    $order_array = array(
      'locale' => $_SESSION['language_code'].'-'.$_SESSION['language_code'],
      'purchase_country' => $country['countries_iso_code_2'],
      'purchase_currency' => $order->info['currency'],
      'order_amount' => $this->format_amount($order_amount),
      'order_tax_amount' => $this->format_amount($order_tax_amount),
      'merchant_reference1' => ((isset($_SESSION['customer_id'])) ? $_SESSION['customer_id'] : 0),
      'order_lines' => $products_array,
    );
    
    if ($order_array['order_tax_amount'] != $tax_total) {
      foreach ($order_array['order_lines'] as $k => $order_lines) {
        if ($order_lines['reference'] == 'ot_coupon') {
          $order_array['order_lines'][$k]['total_tax_amount'] = abs($order_array['order_tax_amount'] - $tax_total) * (-1);
          if ($add_tax === true) {
            $order_array['order_lines'][$k]['unit_price'] += $order_array['order_lines'][$k]['total_tax_amount'];
            $order_array['order_lines'][$k]['total_amount'] += $order_array['order_lines'][$k]['total_tax_amount'];
          }
          $order_array['order_lines'][$k]['tax_rate'] = $this->format_amount((($order_array['order_lines'][$k]['total_amount'] / ($order_array['order_lines'][$k]['total_amount'] - $order_array['order_lines'][$k]['total_tax_amount']) - 1)) * 100);
        }
      }
    }

    if ($minimal === true) {
      //return $order_array;
    }

    $country_zones = array();
    if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED')
        && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED') != ''
        )
    {
      $countries_table = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED');
      $countries_table  = preg_replace("'[\r\n\s]+'",'',$countries_table);
      $country_zones = explode(",", $countries_table);
    }
    
    if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')
        && (int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE') > 0
        ) 
    {
      $countries_query = xtc_db_query("SELECT c.countries_iso_code_2 
                                         FROM ".TABLE_ZONES_TO_GEO_ZONES." gz
                                         JOIN ".TABLE_COUNTRIES." c
                                              ON c.countries_id = gz.zone_country_id
                                        WHERE gz.geo_zone_id = '".(int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')."'");
      while ($countries = xtc_db_fetch_array($countries_query)) {
        $country_zones[] = $countries['countries_iso_code_2'];
      }
    }
    $country_zones = array_unique($country_zones);
    
    if (count($country_zones) < 1) {
      $countries = xtc_get_countriesList();
      foreach ($countries as $country) {
        $country_zones[] = $country['countries_iso_code_2'];
      }
    }
    $order_array['billing_countries'] = $country_zones;
    $order_array['shipping_countries'] = $country_zones;
    
    $shipping_address = new stdclass();    
    if ($minimal === false) {
      $shipping_address->title = $this->parse_gender($_SESSION['language_code'], $order->delivery['gender']);
      $shipping_address->given_name = $order->delivery['firstname'];
      $shipping_address->family_name = $order->delivery['lastname'];
      $shipping_address->organization_name = $order->delivery['company'];
      $shipping_address->street_address = $order->delivery['street_address'];
      $shipping_address->street_address2 = (($order->delivery['suburb'] != '') ? $order->delivery['suburb'] : NULL);
      $shipping_address->postal_code = $order->delivery['postcode'];
      $shipping_address->city = $order->delivery['city'];
      $shipping_address->region = (($order->delivery['state'] != '') ? $order->delivery['state'] : NULL);
      $shipping_address->email = $order->customer['email_address'];
      $shipping_address->phone = $order->customer['telephone'];
    }
    $shipping_address->country = ((isset($order->delivery['country_iso_2'])) ? $order->delivery['country_iso_2'] : $order->delivery['country']['iso_code_2']);
    $order_array['shipping_address'] = $shipping_address;
    
    $billing_address = new stdclass();
    if ($minimal === false) {
      $billing_address->title = $this->parse_gender($_SESSION['language_code'], $order->billing['gender']);
      $billing_address->given_name = $order->billing['firstname'];
      $billing_address->family_name = $order->billing['lastname'];
      $billing_address->organization_name = $order->billing['company'];
      $billing_address->street_address = $order->billing['street_address'];
      $billing_address->street_address2 = (($order->billing['suburb'] != '') ? $order->billing['suburb'] : NULL);
      $billing_address->postal_code = $order->billing['postcode'];
      $billing_address->city = $order->billing['city'];
      $billing_address->region = (($order->billing['state'] != '') ? $order->billing['state'] : NULL);
      $billing_address->email = $order->customer['email_address'];
      $billing_address->phone = $order->customer['telephone'];
    }
    $billing_address->country = ((isset($order->billing['country_iso_2'])) ? $order->billing['country_iso_2'] : $order->billing['country']['iso_code_2']);
    $order_array['billing_address'] = $billing_address;
        
    return $order_array;
  }

}