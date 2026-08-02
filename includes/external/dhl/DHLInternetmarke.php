<?php
/* -----------------------------------------------------------------------------------------
   $Id: DHLBusinessShipment.php 16757 2026-01-09 07:43:36Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed function
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');

  require_once(DIR_FS_INC.'xtc_get_countries_with_iso_codes.inc.php');
  require_once(DIR_FS_INC.'xtc_get_countries.inc.php');

  // include nneded classes
  require_once(DIR_WS_CLASSES.'order.php');


  class DHLInternetmarke {

    const DHL_API_URL = 'https://api-eu.dhl.com/post/de/shipping/im/v1';

    private $data;
    private $info;
    private $client;
    private $order;
    private $loglevel;
    private $LoggingManager;
    private $message = array();
    private $format = 0;
    private $row = 0;
    private $column = 0;
    private $product = 0;
    private $price = 0;


    function __construct($data, $init = true) {
      $this->loglevel = defined('MODULE_INTERNETMARKE_LOGLEVEL') ? MODULE_INTERNETMARKE_LOGLEVEL : 'ERROR';
      $this->LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_dhl_internetmarke_%s_%s.log', 'internetmarke', strtolower($this->loglevel));
      
      $this->data = array(
        'user'          => MODULE_INTERNETMARKE_PORTO_USER,
        'signature'     => MODULE_INTERNETMARKE_PORTO_PASS,
        'api_user'      => 'lWh2GbG4I1VyVRvADeWNiWGNKVPI4Wfk',
        'api_password'  => 'PUrMdYgQnh9ko8ii',
      );
      
      $country = xtc_get_countries_with_iso_codes(STORE_COUNTRY);
     
      $this->info = array(
        'name'            => MODULE_INTERNETMARKE_FIRSTNAME . ' ' . MODULE_INTERNETMARKE_LASTNAME,
        'firstname'       => MODULE_INTERNETMARKE_FIRSTNAME,
        'lastname'        => MODULE_INTERNETMARKE_LASTNAME,
        'company'         => MODULE_INTERNETMARKE_COMPANY,
        'suburb'           => MODULE_INTERNETMARKE_SUBURB,
        'street_address'  => MODULE_INTERNETMARKE_STREET,
        'postcode'        => MODULE_INTERNETMARKE_PLZ,
        'city'            => MODULE_INTERNETMARKE_CITY,
        'country'         => $country['countries_name'],
        'country_iso_2'   => $country['countries_iso_code_2'],
        'country_iso_3'   => $country['countries_iso_code_3'],
      );
      $this->info = $this->encode_request($this->info);
      
      $this->format = isset($data['format']) && is_scalar($data['format']) ? (int)$data['format'] : 0;
      $this->row = isset($data['row']) && is_scalar($data['row']) ? (int)$data['row'] : 0;
      $this->column = isset($data['column']) && is_scalar($data['column']) ? (int)$data['column'] : 0;
      $this->product = isset($data['product']) && is_scalar($data['product']) ? (int)$data['product'] : 0;
               
      if ($init === true
          && $this->data['user'] != ''
          && $this->data['signature'] != ''
          )
      {
        $this->client = new \GuzzleHttp\Client();
        $this->getAccessToken();
      }
    }


    public function CreateLabel($order_id) {
      $result = array(
        'label' => array(),
        'message' => array(),
      );

      if ($this->loadOrder($order_id) !== true) {
        $result['message'] = $this->message;
        return $result;
      }

      if ($this->validateLabelData() !== true) {
        $result['message'] = $this->message;
        return $result;
      }

      if ($this->initShoppingCart() !== true) {
        $result['message'] = $this->message;
        return $result;
      }
          
      $headers = array(
        'Authorization' => 'Bearer '.$this->data['access_token'],
        'Content-Type' => 'application/json'
      );

      $body = json_encode($this->buildLabelData());
      if ($body === false) {
        $this->message['error'][] = 'The Internetmarke label data could not be encoded.';
        $result['message'] = $this->message;
        return $result;
      }

      $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_URL, '/app/shoppingcart/pdf'), $headers, $body);
     
      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);

        $shopping_cart_id = isset($response['shoppingCart']['shopOrderId'])
                            && is_scalar($response['shoppingCart']['shopOrderId'])
          ? trim((string)$response['shoppingCart']['shopOrderId'])
          : '';
        $expected_shopping_cart_id = isset($this->data['shopOrderId']) ? (string)$this->data['shopOrderId'] : '';
        $label_url = isset($response['link']) && is_scalar($response['link'])
          ? trim((string)$response['link'])
          : '';
        $label_url_scheme = ($label_url != '') ? parse_url($label_url, PHP_URL_SCHEME) : false;

        if (!is_array($response)
            || !isset($response['shoppingCart'])
            || !is_array($response['shoppingCart'])
            || !isset($response['shoppingCart']['voucherList'])
            || !is_array($response['shoppingCart']['voucherList'])
            || count($response['shoppingCart']['voucherList']) < 1
            || $shopping_cart_id == ''
            || $shopping_cart_id !== $expected_shopping_cart_id
            || strlen($shopping_cart_id) > 80
            || strlen($label_url) > 512
            || filter_var($label_url, FILTER_VALIDATE_URL) === false
            || !is_string($label_url_scheme)
            || strtolower($label_url_scheme) != 'https'
            )
        {
          $this->handleUnexpectedPurchaseResponse($response, $expected_shopping_cart_id, $label_url);
        } else {
          $voucher_array = array();
          foreach ($response['shoppingCart']['voucherList'] as $items) {
            if (!is_array($items)
                || !isset($items['voucherId'])
                || !is_scalar($items['voucherId'])
                || trim((string)$items['voucherId']) == ''
                || (isset($items['trackId']) && !is_scalar($items['trackId']))
                )
            {
              $voucher_array = array();
              break;
            }

            $voucher_id = trim((string)$items['voucherId']);
            $parcel_id = isset($items['trackId']) && trim((string)$items['trackId']) != ''
              ? trim((string)$items['trackId'])
              : $voucher_id;
            if (strlen($voucher_id) > 80 || strlen($parcel_id) > 80) {
              $voucher_array = array();
              break;
            }
            $voucher_array[] = array(
              'voucher_id' => $voucher_id,
              'parcel_id' => $parcel_id,
            );
          }

          if (count($voucher_array) != count($response['shoppingCart']['voucherList'])) {
            $this->handleUnexpectedPurchaseResponse($response, $shopping_cart_id, $label_url);
          } else {
            foreach ($voucher_array as $voucher) {
              $tracking_id = $this->SaveLabel(
                $voucher['parcel_id'],
                $voucher['voucher_id'],
                $label_url,
                $shopping_cart_id
              );

              if ($tracking_id === false) {
                $recovery_data = array(
                  'orders_id' => $this->order->info['order_id'],
                  'voucher_id' => $voucher['voucher_id'],
                  'parcel_id' => $voucher['parcel_id'],
                  'shopping_cart_id' => $shopping_cart_id,
                  'label_url' => $label_url,
                );
                $this->cancelUnstoredLabel($voucher['voucher_id'], 'voucherId', $recovery_data);
                continue;
              }

              $result['label'][] = array(
                'tracking_id' => $tracking_id,
                'parcel_id' => $voucher['parcel_id'],
              );
            }
          }
        }
      } catch (Exception $ex) {
        $this->handleException($ex, 'CreateLabel');
        if ((!method_exists($ex, 'getResponse') || !is_object($ex->getResponse()))
            && isset($this->data['shopOrderId'])
            && $this->data['shopOrderId'] != ''
            )
        {
          $this->cancelUnstoredLabel(
            $this->data['shopOrderId'],
            'shopOrderId',
            array(
              'orders_id' => $this->order->info['order_id'],
              'shopping_cart_id' => $this->data['shopOrderId'],
              'checkout_status' => 'unknown',
              'exception' => $ex->getMessage(),
            )
          );
        }
      }
      
      $result['message'] = $this->message;
      
      return $result;
    }


    private function loadOrder($order_id) {
      $order_id = (int)$order_id;
      if ($order_id < 1) {
        $this->message['error'][] = 'The Internetmarke order is invalid.';
        return false;
      }

      $order_query = xtc_db_query("SELECT orders_id
                                      FROM ".TABLE_ORDERS."
                                     WHERE orders_id = '".$order_id."'");
      if (xtc_db_num_rows($order_query) < 1) {
        $this->message['error'][] = 'The Internetmarke order does not exist.';
        return false;
      }

      $this->order = new order($order_id);
      return true;
    }


    private function SaveLabel($shipment_number, $voucher_id, $label_url, $cart_id) {
      $sql_data_array = array(
        'orders_id' => $this->order->info['order_id'],
        'carrier_id' => (int)MODULE_INTERNETMARKE_CARRIER,
        'external' => '3',
        'date_added' => 'now()',
        'parcel_id' => $shipment_number,
        'im_orders_id' => $cart_id,
        'im_url' => $label_url,
        'im_voucher_id' => $voucher_id,
      );
      if (xtc_db_perform(TABLE_ORDERS_TRACKING, $sql_data_array) === false) {
        return false;
      }

      $tracking_id = (int)xtc_db_insert_id();
      if ($tracking_id > 0) {
        return $tracking_id;
      }

      $tracking_query = xtc_db_query("SELECT tracking_id
                                        FROM ".TABLE_ORDERS_TRACKING."
                                       WHERE orders_id = '".(int)$this->order->info['order_id']."'
                                         AND carrier_id = '".(int)MODULE_INTERNETMARKE_CARRIER."'
                                         AND im_voucher_id = '".xtc_db_input($voucher_id)."'
                                         AND im_orders_id = '".xtc_db_input($cart_id)."'
                                    ORDER BY tracking_id DESC
                                       LIMIT 1");
      if (xtc_db_num_rows($tracking_query) > 0) {
        $tracking = xtc_db_fetch_array($tracking_query);
        return (int)$tracking['tracking_id'];
      }

      return false;
    }


    private function handleUnexpectedPurchaseResponse($response, $shopping_cart_id, $label_url) {
      $recovery_data = array(
        'orders_id' => $this->order->info['order_id'],
        'shopping_cart_id' => $shopping_cart_id,
        'label_url' => $label_url,
        'api_response' => $response,
      );

      if ($shopping_cart_id != '') {
        $this->cancelUnstoredLabel($shopping_cart_id, 'shopOrderId', $recovery_data);
      } else {
        $recovery_data['refund_status'] = 'not_possible';
        $this->logPersistenceFailure($recovery_data);
        $this->message['error'][] = 'The Internetmarke API returned an unexpected response. Check the Internetmarke error log for recovery data.';
      }
    }


    private function cancelUnstoredLabel($shipment_number, $identifier, $recovery_data) {
      $recovery_data['refund_status'] = 'pending';
      $this->logPersistenceFailure($recovery_data);

      $refund = $this->DeleteLabel($shipment_number, $identifier);
      if (isset($refund['label'])
          && is_array($refund['label'])
          && count($refund['label']) > 0
          )
      {
        $recovery_data['refund_status'] = 'successful';
        $this->message['error'][] = 'The Internetmarke purchase could not be saved locally and was cancelled automatically.';
      } else {
        $recovery_data['refund_status'] = 'failed';
        $this->message['error'][] = 'The Internetmarke purchase could not be saved locally or cancelled automatically. Check the Internetmarke error log for recovery data.';
      }
      $this->logPersistenceFailure($recovery_data);
    }


    private function logPersistenceFailure($recovery_data) {
      try {
        $LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_dhl_internetmarke_%s_%s.log', 'internetmarke', 'error');
        $LoggingManager->log('ERROR', 'SaveLabel', $recovery_data);
      } catch (Throwable $ex) {
        error_log('Internetmarke SaveLabel recovery data: '.json_encode($recovery_data));
      }
    }


    public function DeleteLabel($shipmentNumber, $identifier = 'voucherId') {
      $result = array(
        'label' => array(),
        'message' => array(),
      );

      if (!isset($this->data['access_token']) || $this->data['access_token'] == '') {
        if (count($this->message) == 0) {
          $this->message['error'][] = 'Authentication with the Internetmarke API failed.';
        }
        $result['message'] = $this->message;
        return $result;
      }

      if (!in_array($identifier, array('voucherId', 'trackId', 'shopOrderId', 'auto'), true)) {
        $result['message']['error'][] = 'The Internetmarke label identifier is invalid.';
        return $result;
      }

      if (!is_scalar($shipmentNumber)
          || trim((string)$shipmentNumber) == ''
          || strlen(trim((string)$shipmentNumber)) > 80
          )
      {
        $result['message']['error'][] = 'The Internetmarke label identifier is invalid.';
        return $result;
      }
      $shipmentNumber = trim((string)$shipmentNumber);

      $headers = [
        'Authorization' => 'Bearer '.$this->data['access_token'],
        'Content-Type' => 'application/json'
      ];

      $identifiers = $identifier == 'auto' ? array('voucherId', 'trackId') : array($identifier);
      $last_exception = null;
      foreach ($identifiers as $identifier_type) {
        $shoppingCart = new stdClass();
        if ($identifier_type == 'shopOrderId') {
          $shoppingCart->shopOrderId = $shipmentNumber;
        } else {
          $voucherList = new stdClass();
          $voucherList->{$identifier_type} = $shipmentNumber;
          $shoppingCart->voucherList = array($voucherList);
        }

        $data = new stdClass();
        $data->shoppingCart = $shoppingCart;

        $body = json_encode($data);
        if ($body === false) {
          $this->message['error'][] = 'The Internetmarke cancellation data could not be encoded.';
          $result['message'] = $this->message;
          return $result;
        }
        $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_URL, '/app/retoure'), $headers, $body);

        try {
          $response = $this->client->send($request);
          $response = json_decode($response->getBody()->getContents(), true);

          if (isset($response['retoureTransactionId'])
              && is_scalar($response['retoureTransactionId'])
              && trim((string)$response['retoureTransactionId']) != ''
              && isset($response['shopRetoureId'])
              && is_scalar($response['shopRetoureId'])
              && trim((string)$response['shopRetoureId']) != ''
              )
          {
            $result['label'] = $response;
            return $result;
          }
        } catch (Exception $ex) {
          $last_exception = $ex;
        }
      }

      if ($last_exception !== null) {
        $this->handleException($last_exception, 'DeleteLabel');
      } else {
        $this->message['error'][] = 'The Internetmarke API returned an unexpected response.';
      }
      $result['message'] = $this->message;

      return $result;
    }


    public function getPageFormats($id = '', $single = false) {      
      $formats_array = array();

      $result = array(
        'formats' => array(),
        'message' => array(),
      );

      if (!isset($this->data['access_token']) || $this->data['access_token'] == '') {
        if (count($this->message) == 0) {
          $this->message['error'][] = 'Authentication with the Internetmarke API failed.';
        }
        $result['message'] = $this->message;
        return $result;
      }

      $headers = [
        'Authorization' => 'Bearer '.$this->data['access_token'],
      ];

      $request = new \GuzzleHttp\Psr7\Request('GET', $this->getUrl(self::DHL_API_URL, '/app/catalog?types=PAGE_FORMATS'), $headers);

      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);

        if (isset($response['pageFormats']) && is_array($response['pageFormats'])) {
          foreach ($response['pageFormats'] as $PageFormat) {
            if (!is_array($PageFormat)
                || !isset($PageFormat['id'], $PageFormat['name'], $PageFormat['pageLayout']['labelCount']['labelX'], $PageFormat['pageLayout']['labelCount']['labelY'])
                || !is_scalar($PageFormat['id'])
                || !is_scalar($PageFormat['name'])
                || !is_scalar($PageFormat['pageLayout']['labelCount']['labelX'])
                || !is_scalar($PageFormat['pageLayout']['labelCount']['labelY'])
                || !$this->isPositiveIntegerValue($PageFormat['id'])
                || !$this->isPositiveIntegerValue($PageFormat['pageLayout']['labelCount']['labelX'])
                || !$this->isPositiveIntegerValue($PageFormat['pageLayout']['labelCount']['labelY'])
                )
            {
              continue;
            }

            $format_id = (int)$PageFormat['id'];
            $formats_array[$format_id] = array(
              'id' => $format_id,
              'text' => (string)$PageFormat['name'],
              'labelX' => (int)$PageFormat['pageLayout']['labelCount']['labelX'],
              'labelY' => (int)$PageFormat['pageLayout']['labelCount']['labelY'],
            );
          }
          if (count($formats_array) > 0) {
            ksort($formats_array);
            $result['formats'] = $formats_array;
          } else {
            $this->message['error'][] = 'The Internetmarke API returned no valid page formats.';
          }
        } else {
          $this->message['error'][] = 'The Internetmarke API returned an unexpected response.';
        }
        
      } catch (Exception $ex) {
        $this->handleException($ex, 'getPageFormats');
      }
      
      $result['message'] = $this->message;
      
      if ($id != '') {
        $id_array = explode(',', $id);
                
        if ($single === false) {
          $selected_formats_array = array();
          foreach ($id_array as $id) {
            if (isset($formats_array[$id])) {
              $selected_formats_array[$id] = $formats_array[$id];
            }
          }
          $result['formats'] = $selected_formats_array;
        } else {
          $result['formats'] = isset($formats_array[$id_array[0]]) ? $formats_array[$id_array[0]] : array();
        }
      }
      
      return $result;
    }


    private function getAccessToken() {
      $headers = array(
        'Content-Type' => 'application/x-www-form-urlencoded'
      );
      
      $options = array(
        'form_params' => array(
          'client_id' => $this->data['api_user'],
          'client_secret' => $this->data['api_password'],
          'username' => $this->data['user'],
          'password' => $this->data['signature'],
          'grant_type' => 'client_credentials',
        )
      );
      
      if (!isset($this->data['access_token'])) {
        $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_URL, '/user'), $headers);      

        try {
          $response = $this->client->send($request, $options);
          $response = json_decode($response->getBody()->getContents(), true);
          if (isset($response['access_token'])
              && is_scalar($response['access_token'])
              && trim((string)$response['access_token']) != ''
              )
          {
            $this->data['access_token'] = trim((string)$response['access_token']);
          } else {
            $this->message['error'][] = 'The Internetmarke API did not return an access token.';
          }
        } catch (Exception $ex) {
          $this->handleException($ex, 'getAccessToken');
        }
      }
    }


    private function initShoppingCart() {
      if (!isset($this->data['access_token']) || $this->data['access_token'] == '') {
        if (count($this->message) == 0) {
          $this->message['error'][] = 'Authentication with the Internetmarke API failed.';
        }
        return false;
      }

      $headers = [
        'Authorization' => 'Bearer '.$this->data['access_token'],
      ];

      $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_URL, '/app/shoppingcart'), $headers);

      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);
        if (isset($response['shopOrderId'])
            && is_scalar($response['shopOrderId'])
            && trim((string)$response['shopOrderId']) != ''
            && strlen(trim((string)$response['shopOrderId'])) <= 80
            )
        {
          $this->data['shopOrderId'] = trim((string)$response['shopOrderId']);
          return true;
        }
        $this->message['error'][] = 'The Internetmarke API did not return a shopping cart ID.';
      } catch (Exception $ex) {
        $this->handleException($ex, 'initShoppingCart');
      }

      return false;
    }


    private function handleException($ex, $method) {
      $error = array();

      if (is_object($ex)
          && method_exists($ex, 'getResponse')
          && is_object($ex->getResponse())
          )
      {
        $error = json_decode($ex->getResponse()->getBody()->getContents(), true);
        if (!is_array($error)) {
          $error = array();
        }
      }

      if (isset($error['status']) && is_scalar($error['status'])
          && isset($error['detail']) && is_scalar($error['detail'])
          )
      {
        $this->message['error'][] = sprintf('Status %s: %s', $error['status'], $error['detail']);
      } elseif (isset($error['statusCode']) && is_scalar($error['statusCode'])
                && isset($error['description']) && is_scalar($error['description'])
                )
      {
        $this->message['error'][] = sprintf('Status %s: %s', $error['statusCode'], $error['description']);
      } elseif (isset($error['description']) && is_scalar($error['description'])) {
        $this->message['error'][] = (string)$error['description'];
      } elseif (isset($error['detail']) && is_scalar($error['detail'])) {
        $this->message['error'][] = (string)$error['detail'];
      } elseif (isset($error['title']) && is_scalar($error['title'])) {
        $this->message['error'][] = (string)$error['title'];
      } else {
        $this->message['error'][] = $ex->getMessage();
      }

      try {
        $this->LoggingManager->log('ERROR', $method, array('exception' => count($error) > 0 ? $error : $ex));
      } catch (Throwable $logging_exception) {
        error_log('Internetmarke '.$method.' error: '.$ex->getMessage());
      }
    }


    private function getUrl($url, $path) {
      return $url.$path;
    }


    private function buildLabelData() {
      // customers_data
      $customers_data = $this->buildCustomersData();
            
      $Shipment = new stdClass();
      $Shipment->type = 'AppShoppingCartPDFRequest';
      $Shipment->shopOrderId = $this->data['shopOrderId'];
      $Shipment->pageFormatId = $this->format;
      $Shipment->positions = array();
      $Shipment->total = (int)round($this->price * 100);
      $Shipment->createManifest = true;
      $Shipment->createShippingList = '2';
      $Shipment->dpi = 'DPI300';

      $Address = new stdClass();
      $Address->sender = $this->buildShippingDetails($this->info);
      $Address->receiver = $this->buildShippingDetails($customers_data);
      
      $Position = new stdClass();
      $Position->labelX = $this->column;
      $Position->labelY = $this->row;
      $Position->page = 1;
      
      $Positions = new stdClass();
      $Positions->productCode = $this->product;
      $Positions->address = $Address;
      $Positions->voucherLayout = 'ADDRESS_ZONE';
      $Positions->position = $Position;
      $Positions->positionType = 'AppShoppingCartPDFPosition';
      
      $Shipment->positions[] = $Positions;
      
      return $Shipment;
    }


    private function validateLabelData() {
      if ($this->format < 1
          || $this->row < 1
          || $this->column < 1
          || $this->product < 1
          )
      {
        $this->message['error'][] = 'The Internetmarke label data is invalid.';
        return false;
      }

      if ($this->validatePersistenceConfiguration() !== true) {
        return false;
      }

      $allowed_formats = array_filter(array_map('intval', explode(',', MODULE_INTERNETMARKE_PAGEFORMATS)));
      if (!in_array($this->format, $allowed_formats, true)) {
        $this->message['error'][] = 'The selected Internetmarke page format is not configured.';
        return false;
      }

      $price_query = xtc_db_query("SELECT PROID, PROPR
                                     FROM `internetmarke`
                                    WHERE PROID = '".(int)$this->product."'
                                      AND SEL != 0");
      if (xtc_db_num_rows($price_query) < 1) {
        $this->message['error'][] = 'The selected Internetmarke product is not configured.';
        return false;
      }
      $price = xtc_db_fetch_array($price_query);
      if (!isset($price['PROPR']) || !is_numeric($price['PROPR']) || (float)$price['PROPR'] <= 0) {
        $this->message['error'][] = 'The selected Internetmarke product price is invalid.';
        return false;
      }
      $this->price = (float)$price['PROPR'];

      $result = $this->getPageFormats((string)$this->format, true);
      if (!isset($result['formats']['labelX'])
          || !isset($result['formats']['labelY'])
          || $this->column > (int)$result['formats']['labelX']
          || $this->row > (int)$result['formats']['labelY']
          )
      {
        if (count($this->message) == 0) {
          $this->message['error'][] = 'The selected Internetmarke label position is invalid.';
        }
        return false;
      }

      return true;
    }


    private function isPositiveIntegerValue($value) {
      return (is_int($value) && $value > 0)
             || (is_string($value) && ctype_digit($value) && (int)$value > 0);
    }


    private function validatePersistenceConfiguration() {
      $column_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ORDERS_TRACKING." LIKE 'im_voucher_id'");
      if (xtc_db_num_rows($column_query) < 1) {
        $this->message['error'][] = 'The Internetmarke module must be updated before labels can be created.';
        return false;
      }

      $cart_column_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ORDERS_TRACKING." LIKE 'im_orders_id'");
      if (xtc_db_num_rows($cart_column_query) < 1) {
        $this->message['error'][] = 'The Internetmarke module must be updated before labels can be created.';
        return false;
      }
      $cart_column = xtc_db_fetch_array($cart_column_query);
      if (!isset($cart_column['Type']) || strtolower($cart_column['Type']) != 'varchar(80)') {
        $this->message['error'][] = 'The Internetmarke module must be updated before labels can be created.';
        return false;
      }

      $carrier_id = defined('MODULE_INTERNETMARKE_CARRIER') ? (int)MODULE_INTERNETMARKE_CARRIER : 0;
      if ($carrier_id < 1) {
        $this->message['error'][] = 'The Deutsche Post carrier must be installed before Internetmarke labels can be created.';
        return false;
      }

      $carrier_query = xtc_db_query("SELECT carrier_id
                                       FROM ".TABLE_CARRIERS."
                                      WHERE carrier_id = '".$carrier_id."'");
      if (xtc_db_num_rows($carrier_query) < 1) {
        $this->message['error'][] = 'The configured Deutsche Post carrier is invalid.';
        return false;
      }

      return true;
    }


    private function buildCustomersData() { 
      $customers_data = array(
        'name' => $this->order->delivery['name'],
        'firstname' => $this->order->delivery['firstname'],
        'lastname' => $this->order->delivery['lastname'],
        'company' => $this->order->delivery['company'],
        'suburb' => $this->order->delivery['suburb'],
        'street_address' => $this->order->delivery['street_address'],
        'postcode' => $this->order->delivery['postcode'],
        'city' => $this->order->delivery['city'],
        'country' => $this->order->delivery['country'],
        'country_iso_2' => $this->order->delivery['country_iso_2'],
        'country_iso_3' => $this->get_country_iso_3($this->order->delivery['country_iso_2']),
      );

      $customers_data = $this->encode_request($customers_data);
      
      // global data
      $this->data['orders_id'] = $this->order->info['order_id'];
  
      return $customers_data;
    }


    private function buildShippingDetails($data) {
      $Address = new stdClass();
      $Address->name = (($data['company'] != '') ? $this->truncateText($data['company'], 35) : $this->truncateText(($data['firstname'] . ' ' . $data['lastname']), 35));
      if ($data['company'] != ''
          && ($data['firstname'] != '' || $data['lastname'] != '')
          )
      {
         $Address->additionalName = $this->truncateText(($data['firstname'] . ' ' . $data['lastname']), 35);
      }
      
      if (isset($data['suburb'])
          && $data['suburb'] != '' 
          ) 
      {
        $Address->addressLine2 = $data['suburb'];
      }
      $Address->addressLine1 = $this->format_street_address($data['street_address']);
      $Address->postalCode = $data['postcode'];
      $Address->city = $data['city'];
      $Address->country = $data['country_iso_3'];
  
      return $Address;
    }


    private function truncateText($text, $length) {
      return function_exists('mb_substr') ? mb_substr($text, 0, $length, 'UTF-8') : substr($text, 0, $length);
    }


    private function format_street_address($street_address) {
      preg_match_all("! [0-9]{1,5}[/ \- 0-9 a-z A-Z]*!m", $street_address, $matches, PREG_SET_ORDER);
      if (count($matches) < 1) {
        preg_match_all("/^([\d][a-z-\/\d]*)|[\s]+([\d][a-z-\/][\d]*)/i", $street_address, $matches, PREG_SET_ORDER);
      }
      if (count($matches) < 1) {
        preg_match_all("![0-9]{1,5}[/ \- 0-9 a-z A-Z]*!m", $street_address, $matches, PREG_SET_ORDER);
      }
      $addr = end($matches);
      
      $address = array(
        'street_name' => ((isset($addr[0])) ? trim(str_replace(trim($addr[0]), '', $street_address), ', ') : $street_address),
        'street_number' => ((isset($addr[0])) ? trim($addr[0]) : ''),
      );
      
      $street_address = implode(' ', $address);
      $street_address = preg_replace('/\s+/', ' ', $street_address);
      
      return $street_address;
    }


    private function encode_request($array) {
      foreach ($array as $key => $value) {
        if (is_array($value)) {
          $array[$key] = $this->encode_request($value);
        } else {
          $array[$key] = ((!is_bool($value)) ? encode_utf8(decode_htmlentities($value), $_SESSION['language_charset'], true) : $value);
        }
      }
    
      return $array;
    }

    
    private function get_country_iso_3($iso_code_2) {
      $country_query = xtc_db_query("SELECT countries_iso_code_3 
                                       FROM ".TABLE_COUNTRIES."
                                      WHERE countries_iso_code_2 = '".xtc_db_input($iso_code_2)."'");
      if (xtc_db_num_rows($country_query) > 0) {
        $country = xtc_db_fetch_array($country_query);
        return $country['countries_iso_code_3'];
      }
      
      return $iso_code_2;
    }
  }
