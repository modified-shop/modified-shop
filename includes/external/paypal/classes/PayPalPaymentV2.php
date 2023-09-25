<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 array(www.modified-shop.org)
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  // compatibillity
  defined('DIR_WS_BASE') OR define('DIR_WS_BASE', '');

  // database tables
  defined('TABLE_PAYPAL_PAYMENT') OR define('TABLE_PAYPAL_PAYMENT', 'paypal_payment');
  defined('TABLE_PAYPAL_CONFIG') OR define('TABLE_PAYPAL_CONFIG', 'paypal_config');
  defined('TABLE_PAYPAL_IPN') OR define('TABLE_PAYPAL_IPN', 'paypal_ipn');
  defined('TABLE_PAYPAL_INSTRUCTIONS') OR define('TABLE_PAYPAL_INSTRUCTIONS', 'paypal_instructions');
  defined('TABLE_PAYPAL_TRACKING') OR define('TABLE_PAYPAL_TRACKING', 'paypal_tracking');

  // include needed functions
  require_once(DIR_FS_EXTERNAL.'paypal/functions/PayPalFunctions.php');
  require_once(DIR_FS_INC.'xtc_random_charcode.inc.php');

  // include needed classes
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentBase.php');
  require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');

  use PayPalClient\PayPalClient;
  use PayPalCheckoutSdk\Core\PayPalHttpClient;
  use PayPalCheckoutSdk\Core\SandboxEnvironment;
  use PayPalCheckoutSdk\Core\ProductionEnvironment;
  use PayPalCheckoutSdk\Core\GenerateClientTokenRequest;
  use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
  use PayPalCheckoutSdk\Orders\OrdersGetRequest;
  use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
  use PayPalCheckoutSdk\Orders\OrdersPatchRequest;
  use PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest;
  use PayPalCheckoutSdk\Orders\OrdersConfirmRequest;
  use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
  use PayPalCheckoutSdk\Payments\AuthorizationsCaptureRequest;
  
  // language
  if (is_file(DIR_FS_EXTERNAL.'paypal/lang/'.$_SESSION['language'].'.php')) {
    require_once(DIR_FS_EXTERNAL.'paypal/lang/'.$_SESSION['language'].'.php');
  } else {
    require_once(DIR_FS_EXTERNAL.'paypal/lang/english.php');
  }


  class PayPalPaymentV2 extends PayPalPaymentBase {

    function __construct($class) {
      $this->loglevel = ((PayPalPaymentBase::check_install() === true) ? $this->get_config('PAYPAL_LOG_LEVEL') : 'INFO'); 
      $this->logmode = ((PayPalPaymentBase::check_install() === true) ? $this->get_config('PAYPAL_MODE') : 'paypal'); 
      $this->LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_paypal_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', $this->logmode, strtolower($this->loglevel));

      PayPalPaymentBase::init($class);
    }
    
    
    function GenerateClientToken() {
      // auth
      $client = $this->GetClient();
    
      $request = new GenerateClientTokenRequest();

      try {
        $response = $client->execute($request);
                
        return $response->result;
        
      } catch (PayPalHttp\HttpException $ex) {
        $this->LoggingManager->log('DEBUG', 'CreateOrder', array('exception' => $ex));
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'CreateOrder', array('exception' => $ex));
      }
    }
    
    
    function CreateOrder($payment_source = array(), $error = false) {
      global $order, $xtPrice;
      
      // auth
      $client = $this->GetClient();
            
      // shipping cost
      $order->info['shipping_cost'] = 0;
      if ($this->code == 'paypalexpress') {
        $shipping_data = $this->get_shipping_data();
        if (is_array($shipping_data)) {
          $order->info['shipping_cost'] = $shipping_data['total'];
          $order->info['tax'] += $shipping_data['tax'];
        }
      }
      
      $purchase_unit = array(
        'description' => $this->encode_utf8(mb_substr(MODULE_PAYMENT_PAYPAL_TEXT_ORDER, 0, 127)),
        'soft_descriptor' => $this->encode_utf8(mb_substr(STORE_NAME, 0, 22)),
        'amount' => array(
          'value' => sprintf("%01.2f", round(($order->info['total'] + $order->info['shipping_cost']), 2)),
          'currency_code' => $this->encode_utf8($order->info['currency'])
        )
      );

      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
          && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
          ) 
      {
        $purchase_unit['amount']['value'] = sprintf("%01.2f", round(($order->info['total'] + $order->info['shipping_cost'] + $order->info['tax']), 2));
      }
      
      if ($this->code == 'paypalpui') {
        $order_total = $this->calculate_total(2);
        foreach ($order_total as $total) {
          switch ($total['code']) {
            case 'ot_subtotal':
            case 'ot_subtotal_no_tax':
            case 'ot_tax':
              break;
            case 'ot_shipping':
              $purchase_unit['amount']['breakdown']['shipping'] = array(
                'value' => sprintf("%01.2f", round($total['value'], 2)),
                'currency_code' => $this->encode_utf8($order->info['currency'])
              );
              break;
            case 'ot_total':
              $purchase_unit['amount']['value'] = sprintf("%01.2f", round($total['value'], 2));
              break;
            default:
              if ($total['value'] > 0) {
                if (!isset($purchase_unit['amount']['breakdown']['handling'])) {
                  $purchase_unit['amount']['breakdown']['handling'] = array(
                    'value' => sprintf("%01.2f", round($total['value'], 2)),
                    'currency_code' => $this->encode_utf8($order->info['currency'])
                  );
                } else {
                  $purchase_unit['amount']['breakdown']['handling']['value'] += sprintf("%01.2f", round($total['value'], 2));
                }              
              } else {
                if (!isset($purchase_unit['amount']['breakdown']['discount'])) {
                  $purchase_unit['amount']['breakdown']['discount'] = array(
                    'value' => sprintf("%01.2f", round(abs($total['value']), 2)),
                    'currency_code' => $this->encode_utf8($order->info['currency'])
                  );
                } else {
                  $purchase_unit['amount']['breakdown']['discount']['value'] += sprintf("%01.2f", round(abs($total['value']), 2));
                }
              }
              break;
          }
        }
        
        $sum_net = $sum_tax = array();
        $purchase_unit['items'] = array();    
        foreach ($order->products as $product) {
          $product['price_net'] = $product['final_price'];
          $product['tax_value'] = 0;
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1 && $product['tax'] > 0) {
            $product['price_net'] = round($xtPrice->xtcRemoveTax($product['final_price'], $product['tax']), 2);
            $product['tax_value'] = round($xtPrice->xtcGetTax($product['final_price'], $product['tax']), 2);
          }
          
          $product['tax'] = (string)$product['tax'];
          if (!isset($sum_net[$product['tax']])) $sum_net[$product['tax']] = 0;
          if (!isset($sum_tax[$product['tax']])) $sum_tax[$product['tax']] = 0;
          
          $sum_net[$product['tax']] += $product['price_net'];
          $sum_tax[$product['tax']] += $product['tax_value'];
          
          $item = array(
            'name' => $this->encode_utf8($product['name']),
            'category' => 'PHYSICAL_GOODS',
            'unit_amount' => array(
              'value' => sprintf("%01.2f", $product['price_net']),
              'currency_code' => $this->encode_utf8($order->info['currency'])
            ),
            'tax' => array(
              'value' => sprintf("%01.2f", $product['tax_value']),
              'currency_code' => $this->encode_utf8($order->info['currency'])
            ),
            'tax_rate' => sprintf("%01.2f", round($product['tax'], 2)),
            'quantity' => $product['qty'],
          );
      
          $purchase_unit['items'][] = $item;
        }

        if ($this->get_config('PAYPAL_ADD_CART_DETAILS') == '0') { 
          $purchase_unit['items'] = array();
          
          foreach ($sum_net as $tax => $sum) {
            $item = array(
              'name' => $this->encode_utf8(MODULE_PAYMENT_PAYPAL_TEXT_ORDER),
              'category' => 'PHYSICAL_GOODS',
              'unit_amount' => array(
                'value' => sprintf("%01.2f", $sum_net[$tax]),
                'currency_code' => $this->encode_utf8($order->info['currency'])
              ),
              'tax' => array(
                'value' => sprintf("%01.2f", $sum_tax[$tax]),
                'currency_code' => $this->encode_utf8($order->info['currency'])
              ),
              'tax_rate' => sprintf("%01.2f", $tax),
              'quantity' => 1,
            );
          }
          
          $purchase_unit['items'][] = $item;
        }

        $purchase_unit['amount']['breakdown']['item_total'] = array(
          'value' => sprintf("%01.2f", array_sum($sum_net)),
          'currency_code' => $this->encode_utf8($order->info['currency'])
        );
        $purchase_unit['amount']['breakdown']['tax_total'] = array(
          'value' => sprintf("%01.2f", array_sum($sum_tax)),
          'currency_code' => $this->encode_utf8($order->info['currency'])
        );
        
        if (isset($_SESSION['tmp_oID'])) {
          $purchase_unit['invoice_id'] = $this->get_config('PAYPAL_CONFIG_INVOICE_PREFIX').$_SESSION['tmp_oID'];
        }
      }
      
      if (isset($_SESSION['customer_id'])) {
        $purchase_unit['shipping'] = array(
          'name' => array(
            'full_name' => $this->encode_utf8($order->delivery['firstname'].' '.$order->delivery['lastname']),
          ),
          'address' => array(
            'address_line_1' => $this->encode_utf8($order->delivery['street_address']),
            'address_line_2' => $this->encode_utf8($order->delivery['suburb']),
            'admin_area_1' => $this->encode_utf8((isset($order->delivery['state']) && $order->delivery['state'] != '') ? xtc_get_zone_code($order->delivery['country_id'], $order->delivery['zone_id'], $order->delivery['state']) : ''), // state
            'admin_area_2' => $this->encode_utf8($order->delivery['city']), // city
            'postal_code' => $this->encode_utf8($order->delivery['postcode']),
            'country_code' => $this->encode_utf8($order->delivery['country']['iso_code_2'])
          )
        );

        if ($order->delivery['company'] != '') {
          $purchase_unit['shipping']['address']['address_line_2'] = $this->encode_utf8($order->delivery['company']);
          if ($order->delivery['suburb'] != '') {
            $purchase_unit['shipping']['address']['address_line_1'] = $this->encode_utf8($order->delivery['street_address'].', '.$order->delivery['suburb']);
          }
        }
      }
      
      if (isset($_SESSION['customer_id'])) {
        $payer = array(
          'email_address' => $this->encode_utf8($order->customer['email_address']),
          'name' => array(
            'given_name' => $this->encode_utf8($order->customer['firstname']),
            'surname' => $this->encode_utf8($order->customer['lastname'])
          ),
          'address' => array(
            'address_line_1' => $this->encode_utf8($order->customer['street_address']),
            'address_line_2' => $this->encode_utf8($order->customer['suburb']),
            'admin_area_1' => $this->encode_utf8((isset($order->customer['state']) && $order->customer['state'] != '') ? xtc_get_zone_code($order->customer['country_id'], $order->customer['zone_id'], $order->customer['state']) : ''), // state
            'admin_area_2' => $this->encode_utf8($order->customer['city']), // city
            'postal_code' => $this->encode_utf8($order->customer['postcode']),
            'country_code' => $this->encode_utf8($order->customer['country']['iso_code_2'])
          )
        );

        if ($order->customer['company'] != '') {
          $payer['address']['address_line_2'] = $this->encode_utf8($order->customer['company']);
          if ($order->customer['suburb'] != '') {
            $payer['address']['address_line_1'] = $this->encode_utf8($order->customer['street_address'].', '.$order->customer['suburb']);
          }
        }
      }
      
      $request = new OrdersCreateRequest();
      $request->payPalRequestId(md5($this->code.$_SESSION['cart']->cartID));
      $request->prefer('return=representation');
      $request->body = array(
        'intent' => $this->intent,
        'purchase_units' => array($purchase_unit),
        'application_context' => array(
          'brand_name' => $this->encode_utf8(STORE_NAME),
          'locale' => $_SESSION['language_code'].'-'.strtoupper(($_SESSION['language_code'] == 'en') ? 'GB' : $_SESSION['language_code']),
          'landing_page' => 'BILLING',
          'user_action' => 'CONTINUE',
          'cancel_url' => $this->link_encoding(xtc_href_link('callback/paypal/error.php', 'payment_error='.$this->code.'&'.xtc_session_name().'='.xtc_session_id(), 'SSL', false)),
          'return_url' => $this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_PROCESS, xtc_session_name().'='.xtc_session_id(), 'SSL', false)),
        ) 
      );
      
      if (isset($_SESSION['customer_id'])) {
        $request->body['application_context']['shipping_preference'] = 'SET_PROVIDED_ADDRESS';
      }
      
      if (isset($payer)) {
        $request->body['payer'] = $payer;
      }
      
      if (count($payment_source) > 0) {
        $request->body = array_merge($request->body, $payment_source);
      }
      
      if ($this->code == 'paypalpui') {
        $request->payPalClientMetadataId($_SESSION['paypal']['FraudNetID']);
      }

      try {
        $response = $client->execute($request);
        return $response->result->id;
        
      } catch (PayPalHttp\HttpException $ex) {
        $this->LoggingManager->log('DEBUG', 'CreateOrder', array('exception' => $ex));
        if ($error === true) {
          return json_decode($ex->getMessage(), true);
        }      
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'CreateOrder', array('exception' => $ex));
        if ($error === true) {
          return json_decode($ex->getMessage(), true);
        }
      }
    }
    
    
    function CaptureOrder($OrderID, $error = false) {
      global $insert_id;
      
      // auth
      $client = $this->GetClient();

      $request = new OrdersCaptureRequest($OrderID);
      $request->prefer('return=representation');
      
      try {
        $response = $client->execute($request);
        return $response->result;
        
      } catch (PayPalHttp\HttpException $ex) {
        $this->LoggingManager->log('DEBUG', 'CaptureOrder', array('exception' => $ex));
        if ($error === true) {
          return json_decode($ex->getMessage(), true);
        }      
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'CaptureOrder', array('exception' => $ex));
        if ($error === true) {
          return json_decode($ex->getMessage(), true);
        }
      }
      
      $PayPalOrder = $this->GetOrder($OrderID);

      if ($PayPalOrder->status == 'PAYER_ACTION_REQUIRED') {
        foreach ($PayPalOrder->links as $links) {
          if ($links->rel == 'payer-action') {
            xtc_redirect($links->href);
            break;
          }
        }
      }
      
      if (isset($insert_id) && (int)$insert_id > 0) {
        $this->remove_order($insert_id);
      }

      if (isset($ex->details)
          && is_array($ex->details)
          && isset($ex->details[0])
          && isset($ex->details[0]->issue)
          )
      {
        $_SESSION['paypal_payment_error'] = strtoupper($ex->details[0]->issue);
      }
      
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL')); 
    }


    function AuthorizeOrder($OrderID, $error = false) {
      global $insert_id;
      
      // auth
      $client = $this->GetClient();

      $request = new OrdersAuthorizeRequest($OrderID);
      $request->body = '{}';
      $request->prefer('return=representation');
            
      try {
        $response = $client->execute($request);

        if ($this->get_config('PAYPAL_CAPTURE_MANUELL') == '0' && $error === false) {
          $order = new order($insert_id);
          $this->CaptureAuthorizedOrder($response->result->purchase_units[0]->payments->authorizations[0]->id, $order->info['pp_total'], $order->info['currency'], true);
          return $this->GetOrder($_SESSION['paypal']['OrderID']);
        }
        return $response->result;
        
      } catch (PayPalHttp\HttpException $ex) {
        $this->LoggingManager->log('DEBUG', 'AuthorizeOrder', array('exception' => $ex));
        if ($error === true) {
          return json_decode($ex->getMessage(), true);
        }      
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'AuthorizeOrder', array('exception' => $ex));
        if ($error === true) {
          return json_decode($ex->getMessage(), true);
        }      
      }

      $PayPalOrder = $this->GetOrder($OrderID);

      if ($PayPalOrder->status == 'PAYER_ACTION_REQUIRED') {
        foreach ($PayPalOrder->links as $links) {
          if ($links->rel == 'payer-action') {
            xtc_redirect($links->href);
            break;
          }
        }
      }

      if (isset($insert_id) && (int)$insert_id > 0) {
        $this->remove_order($insert_id);
      }

      if (isset($ex->details)
          && is_array($ex->details)
          && isset($ex->details[0])
          && isset($ex->details[0]->issue)
          )
      {
        $_SESSION['paypal_payment_error'] = strtoupper($ex->details[0]->issue);
      }
       
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL')); 
    }


    function CaptureAuthorizedOrder($authorize_id, $amunt, $currency, $final_capture = false) {
      
      // auth
      $client = $this->GetClient();
            
      $request = new AuthorizationsCaptureRequest($authorize_id);
      $request->body = array(
        'amount' => array(
          'value' => sprintf("%01.2f", $amunt),
          'currency_code' => $this->encode_utf8($currency)
        )
      );
      
      if ($final_capture == true) {
        $request->body['final_capture'] = true;
      }
      
      try {
        $response = $client->execute($request);
        return $response->result;
        
      } catch (PayPalHttp\HttpException $ex) {
        $this->LoggingManager->log('DEBUG', 'CaptureAuthorizedOrder', array('exception' => $ex));
        
        return $ex;
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'CaptureAuthorizedOrder', array('exception' => $ex));
      }
    }
    
    
    function PatchOrder($orderID) {
      global $insert_id;
      
      $order = new order($insert_id);
      
      // auth
      $client = $this->GetClient();
      
      $shipping_address = array(
        'address_line_1' => $this->encode_utf8($order->delivery['street_address']),
        'address_line_2' => $this->encode_utf8($order->delivery['suburb']),
        'admin_area_1' => $this->encode_utf8((isset($order->delivery['state']) && $order->delivery['state'] != '') ? xtc_get_zone_code($order->delivery['country_id'], $order->delivery['zone_id'], $order->delivery['state']) : ''), // state
        'admin_area_2' => $this->encode_utf8($order->delivery['city']), // city
        'postal_code' => $this->encode_utf8($order->delivery['postcode']),
        'country_code' => $this->encode_utf8($order->delivery['country_iso_2'])
      );
      
      if ($order->delivery['company'] != '') {
        $shipping_address['address_line_2'] = $this->encode_utf8($order->delivery['company']);
        if ($order->delivery['suburb'] != '') {
          $shipping_address['address_line_1'] = $this->encode_utf8($order->delivery['street_address'].', '.$order->delivery['suburb']);
        }
      }

      $request = new OrdersPatchRequest($orderID);
      $request->body = array(
        array(
          'op' => 'add',
          'path' => "/purchase_units/@reference_id=='default'/invoice_id",
          'value' => $this->get_config('PAYPAL_CONFIG_INVOICE_PREFIX').$insert_id
        ),
        array(
          'op' => 'replace',
          'path' => "/purchase_units/@reference_id=='default'/amount",
          'value' => array (
            'currency_code' => $this->encode_utf8($order->info['currency']),
            'value' => sprintf("%01.2f", round($order->info['pp_total'], 2))
          )
        ),
        array(
          'op' => 'replace',
          'path' => "/purchase_units/@reference_id=='default'/shipping/name",
          'value' => array(
            'full_name' => $this->encode_utf8($order->delivery['firstname'].' '.$order->delivery['lastname'])
          )
        ),
        array(
          'op' => 'replace',
          'path' => "/purchase_units/@reference_id=='default'/shipping/address",
          'value' => $shipping_address
        ),
      );
      
      try {
        $response = $client->execute($request);
        return $response->result;
        
      } catch (PayPalHttp\HttpException $ex) {
        $this->LoggingManager->log('DEBUG', 'PatchOrder', array('exception' => $ex));
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'PatchOrder', array('exception' => $ex));
      }
    }
    
    
    function refundOrder($captureId, $amount, $currency, $comment) {
      
      // auth
      $client = $this->GetClient();

      $request = new CapturesRefundRequest($captureId);
      $request->body = array(
        'amount' => array(
          'value' => $amount,
          'currency_code' => $this->encode_utf8($currency)
        ),
      );
      
      if ($comment != '') {
        $request->body['note_to_payer'] = $this->encode_utf8($comment);
      }
      
      try {
        $response = $client->execute($request);
        return $response->result;
        
      } catch (PayPalHttp\HttpException $ex) {
        $this->LoggingManager->log('DEBUG', 'refundOrder', array('exception' => $ex));
        
        return $ex;
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'refundOrder', array('exception' => $ex));
      }
    }


    function GetOrder($OrderID) {

      // auth
      $client = $this->GetClient();

      $request = new OrdersGetRequest($OrderID);
      
      try {
        $response = $client->execute($request);
        return $response->result;
          
      } catch (PayPalHttp\HttpException $ex) {
        $this->LoggingManager->log('DEBUG', 'GetOrder', array('exception' => $ex));
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'GetOrder', array('exception' => $ex));
      }
    }
    
    
    function GetOrderDetails($order_id) {
      $OrderID = $this->getOrderID($order_id);
      
      if ($OrderID != '') {
        $response = $this->GetOrder($OrderID);
        if (isset($response->purchase_units[0]->shipping)) {
          $response->purchase_units[0]->shipping->address_array = $this->parse_address($response->purchase_units[0]->shipping);
        }
        
        return $response;
      }
    }
        
    
    function FinishOrder($order_id) {
      $this->PatchOrder($_SESSION['paypal']['OrderID']);
      
      if ($this->intent == 'CAPTURE') {
        $result = $this->CaptureOrder($_SESSION['paypal']['OrderID']);
        $result->transaction_id = $result->purchase_units[0]->payments->captures[0]->id;
        $result->transaction_status = $result->purchase_units[0]->payments->captures[0]->status;
      } else {
        $result = $this->AuthorizeOrder($_SESSION['paypal']['OrderID']);
        if (isset($result->purchase_units[0]->payments->captures)) {
          $result->transaction_id = $result->purchase_units[0]->payments->captures[0]->id;
          $result->transaction_status = $result->purchase_units[0]->payments->captures[0]->status;
        } else {
          $result->transaction_id = $result->purchase_units[0]->payments->authorizations[0]->id;
          $result->transaction_status = $result->purchase_units[0]->payments->authorizations[0]->status;
        }
      }
      
      if (isset($result->payer->payer_id)) {
        $_SESSION['paypal']['PayerID'] = $result->payer->payer_id;
      }
      
      $sql_data_array = array(
        'orders_id' => $order_id,
        'payment_id' => $_SESSION['paypal']['OrderID'],
        'payer_id' => $_SESSION['paypal']['PayerID'],
        'transaction_id' => $result->transaction_id,
      );
      xtc_db_perform(TABLE_PAYPAL_PAYMENT, $sql_data_array);
      
      $status_id = $this->order_status_pending;
      if ($result->status == 'COMPLETED') {
        if ($result->transaction_status == 'COMPLETED') {
          $status_id = $this->order_status_success;
        }
      }
      $this->update_order('Order ID: '.$_SESSION['paypal']['OrderID'], $status_id, $order_id);
      unset($_SESSION['paypal']);
      
      return $result;
    }
    

    function FinishOrderPui($order_id, $PayPalOrder = '') {
      $check_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_PAYPAL_INSTRUCTIONS."
                                    WHERE orders_id = '".(int)$order_id."'");
      if (xtc_db_num_rows($check_query) < 1) {                       
        if (!is_object($PayPalOrder)) {
          $OrderID = $this->getOrderID($order_id);
          if ($OrderID != '') {        
            $PayPalOrder = $this->GetOrder($OrderID);
          }
        }
                
        if (is_object($PayPalOrder)) {
          if (isset($PayPalOrder->payment_source->pay_upon_invoice)
              && isset($PayPalOrder->payment_source->pay_upon_invoice->deposit_bank_details)
              )
          {
            $sql_data_array = array(
              'orders_id' => $order_id,
              'method' => $this->code,
              'amount' => $PayPalOrder->purchase_units[0]->amount->value,
              'currency' => $PayPalOrder->purchase_units[0]->amount->currency_code,
              'reference' => $PayPalOrder->payment_source->pay_upon_invoice->payment_reference,
              'date' => date('Y-m-d', strtotime('+30 days')),
              'name' => $PayPalOrder->payment_source->pay_upon_invoice->deposit_bank_details->bank_name,
              'holder' => $PayPalOrder->payment_source->pay_upon_invoice->deposit_bank_details->account_holder_name,
              'iban' => $PayPalOrder->payment_source->pay_upon_invoice->deposit_bank_details->iban,
              'bic' => $PayPalOrder->payment_source->pay_upon_invoice->deposit_bank_details->bic,
            );
            xtc_db_perform(TABLE_PAYPAL_INSTRUCTIONS, $sql_data_array);
          }
          
          if (isset($PayPalOrder->purchase_units[0]->payments)) {
            $sql_data_array = array(
              'transaction_id' => $PayPalOrder->purchase_units[0]->payments->captures[0]->id,
            );          
            xtc_db_perform(TABLE_PAYPAL_PAYMENT, $sql_data_array, 'update', "orders_id = '".(int)$order_id."'");
          }
          
          return $PayPalOrder;
        }
      }
    }

    
    function parse_address($address) {
      if (isset($address->name->full_name)) {
        $name = explode(' ', $address->name->full_name, 2);
      } else {
        $name = array(
          $address->name->given_name,
          $address->name->surname
        );
      }
      
      $data = array(
        'name' => implode(' ', $name),
        'company' => '',
        'firstname' => $name[0],
        'lastname' => $name[1],
        'street_address' => ((isset($address->address->address_line_1)) ? $address->address->address_line_1 : ''),
        'suburb' => ((isset($address->address->address_line_2)) ? $address->address->address_line_2 : ''),
        'state' => ((isset($address->address->admin_area_1)) ? $address->address->admin_area_1 : ''),
        'city' => ((isset($address->address->admin_area_2)) ? $address->address->admin_area_2 : ''),
        'postcode' => ((isset($address->address->postal_code)) ? $address->address->postal_code : ''),
        'country_iso_code_2' => ((isset($address->address->country_code)) ? $address->address->country_code : ''),
      );

      $country_iso_query = xtc_db_query("SELECT countries_id,
                                                countries_name,
                                                countries_iso_code_2,
                                                countries_iso_code_3
                                           FROM ".TABLE_COUNTRIES." 
                                          WHERE countries_iso_code_2 = '".xtc_db_input($data['country_iso_code_2'])."'");
      $country_iso = xtc_db_fetch_array($country_iso_query);
      $data['country_id'] = $country_iso['countries_id'];
      $data['country'] = array(
        'id' => $country_iso['countries_id'],
        'title' => $country_iso['countries_name'],
        'iso_code_2' => $country_iso['countries_iso_code_2'],
        'iso_code_3' => $country_iso['countries_iso_code_3'],
      );

      $data['zone_id'] = 0;
      $check_query = xtc_db_query("SELECT count(*) AS total 
                                     FROM ".TABLE_ZONES." 
                                    WHERE zone_country_id = '".(int)$data['country_id']."'");
      $check = xtc_db_fetch_array($check_query);
      $entry_state_has_zones = ($check['total'] > 0);
      if ($entry_state_has_zones == true) {
          $zone_query = xtc_db_query("SELECT DISTINCT zone_id
                                                 FROM ".TABLE_ZONES."
                                                WHERE zone_country_id = '".(int)$data['country_id'] ."'
                                                  AND (zone_id = '" . (int)$data['state'] . "'
                                                       OR zone_code = '" . xtc_db_input($data['state']) . "'
                                                       OR zone_name LIKE '" . xtc_db_input($data['state']) . "%'
                                                       )");
        if (xtc_db_num_rows($zone_query) == 1) {
          $zone = xtc_db_fetch_array($zone_query);
          $data['zone_id'] = $zone['zone_id'];
        } else {
          $data['state'] = '';
        }
      }
      
      return $data;
    }


    function getOrderID($order_id) {
      $orders_query = xtc_db_query("SELECT p.payment_id
                                      FROM ".TABLE_PAYPAL_PAYMENT." p
                                      JOIN ".TABLE_ORDERS." o
                                           ON p.orders_id = o.orders_id
                                     WHERE p.orders_id = '".(int)$order_id."'");
      if (xtc_db_num_rows($orders_query) > 0) {
        $orders = xtc_db_fetch_array($orders_query);
        return $orders['payment_id'];
      }    
    }
  }
