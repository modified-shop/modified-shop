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


  #[AllowDynamicProperties]
  class DHLInternetmarke {

    const DHL_API_URL = 'https://api-eu.dhl.com/post/de/shipping/im/v1';

    private $data;
    private $info;
    private $client;
    private $order;
    private $loglevel;
    private $LoggingManager;
    private $message;


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
        'street_address'  => MODULE_INTERNETMARKE_STREET,
        'postcode'        => MODULE_INTERNETMARKE_PLZ,
        'city'            => MODULE_INTERNETMARKE_CITY,
        'country'         => $country['countries_name'],
        'country_iso_2'   => $country['countries_iso_code_2'],
        'country_iso_3'   => $country['countries_iso_code_3'],
      );
      $this->info = $this->encode_request($this->info);
      
      foreach ($data as $k => $v) {
        $this->$k = $v;
      }
               
      if ($init === true) {
        $this->client = new \GuzzleHttp\Client();
        $this->getAccessToken();
      } 
    }


    public function CreateLabel($order_id) {
      $this->initShoppingCart();

      $this->order = new order($order_id);
          
      $headers = array(
        'Authorization' => 'Bearer '.$this->data['access_token'],
        'Content-Type' => 'application/json'
      );

      $body = json_encode($this->buildLabelData());

      $result = array(
        'label' => array(),
        'message' => array(),
      );

      $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_URL, '/app/shoppingcart/pdf'), $headers, $body);
     
      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);

        foreach ($response['shoppingCart']['voucherList'] as $items) {
          $tracking_id = $this->SaveLabel(
            $items['voucherId'], 
            $response['link'], 
            $response['shoppingCart']['shopOrderId'], 
          );

          $result['label'][] = array(
            'tracking_id' => $tracking_id,
            'parcel_id' => $items['voucherId'],
          ); 
        }
      } catch (Exception $ex) {
        if (is_object($ex)
            && method_exists($ex, 'getResponse')
            )
        {
          $error = json_decode($ex->getResponse()->getBody(), true);      
  
          $this->message['error'][] = $error['description'];
  
          $this->LoggingManager->log('ERROR', 'CreateLabel', array('exception' => $error));
        } else {
          $this->LoggingManager->log('ERROR', 'CreateLabel', array('exception' => $ex));
        }
      }
      
      $result['message'] = $this->message;
      
      return $result;
    }


    private function SaveLabel($shipment_number, $label_url, $cart_id) {
      $sql_data_array = array(
        'orders_id' => $this->order->info['order_id'],
        'carrier_id' => (int)MODULE_INTERNETMARKE_CARRIER,
        'external' => '3',
        'date_added' => 'now()',
        'parcel_id' => $shipment_number,
        'im_orders_id' => $cart_id,
        'im_url' => $label_url,
      );
      xtc_db_perform(TABLE_ORDERS_TRACKING, $sql_data_array);
      
      return xtc_db_insert_id();
    }


    public function DeleteLabel($shipmentNumber) {
      $headers = [
        'Authorization' => 'Bearer '.$this->data['access_token'],
        'Content-Type' => 'application/json'
      ];

      $result = array(
        'label' => array(),
        'message' => array(),
      );

      $voucherList = new stdClass();
      $voucherList->voucherId = $shipmentNumber;
      
      $shoppingCart = new stdClass();
      $shoppingCart->voucherList = array();
      $shoppingCart->voucherList[] = $voucherList;
      
      $data = new stdClass();
      $data->shoppingCart = $shoppingCart;

      $body = json_encode($data);

      $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_URL, '/app/retoure'), $headers, $body);

      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);        

        $result['label'] = $response;
        
      } catch (Exception $ex) {
        if (is_object($ex)
            && method_exists($ex, 'getResponse')
            )
        {
          $error = json_decode($ex->getResponse()->getBody(), true);      

          if (isset($error['status'])) {
            $this->message['error'][] = sprintf('Status %s: %s', $error['status'], $error['detail']);
          }
          if (isset($error['statusCode'])) {
            $this->message['error'][] = sprintf('Status %s: %s', $error['statusCode'], $error['description']);
          }
          
          $this->LoggingManager->log('ERROR', 'DeleteLabel', array('exception' => $error));
        } else {
          $this->LoggingManager->log('ERROR', 'DeleteLabel', array('exception' => $ex));
        }
      }

      $result['message'] = $this->message;
      
      return $result;
    }


    public function getPageFormats($id = '', $single = false) {      
      $headers = [
        'Authorization' => 'Bearer '.$this->data['access_token'],
      ];

      $result = array(
        'formats' => array(),
        'message' => array(),
      );

      $request = new \GuzzleHttp\Psr7\Request('GET', $this->getUrl(self::DHL_API_URL, '/app/catalog?types=PAGE_FORMATS'), $headers);

      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);
        
        $formats_array = array();
        foreach ($response['pageFormats'] as $PageFormat) {
          $formats_array[$PageFormat['id']] = array(
            'id' => $PageFormat['id'],
            'text' => $PageFormat['name'],
            'labelX' => $PageFormat['pageLayout']['labelCount']['labelX'],
            'labelY' => $PageFormat['pageLayout']['labelCount']['labelY'],
          );
        }
        ksort($formats_array);
        $result['formats'] = $formats_array;
        
      } catch (Exception $ex) {
        if (is_object($ex)
            && method_exists($ex, 'getResponse')
            )
        {
          $error = json_decode($ex->getResponse()->getBody(), true);      
                    
          if (isset($error['status'])) {
            $this->message['error'][] = sprintf('Status %s: %s', $error['status'], $error['detail']);
          }
          if (isset($error['statusCode'])) {
            $this->message['error'][] = sprintf('Status %s: %s', $error['statusCode'], $error['description']);
          }

          $this->LoggingManager->log('ERROR', 'getPageFormats', array('exception' => $error));
        } else {
          $this->LoggingManager->log('ERROR', 'getPageFormats', array('exception' => $ex));
        }        
      }
      
      $result['message'] = $this->message;
      
      if ($id != '') {
        $id_array = explode(',', $id);
                
        if ($single === false) {
          $selected_formats_array = array();
          foreach ($id_array as $id) {
            $selected_formats_array[$id] = $formats_array[$id];
          }
          $result['formats'] = $selected_formats_array;
        } else {
          $result['formats'] = $formats_array[$id_array[0]];
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
          $this->data['access_token'] = $response['access_token'];   
        } catch (Exception $ex) {
          if (is_object($ex)
              && method_exists($ex, 'getResponse')
              )
          {
            $error = json_decode($ex->getResponse()->getBody(), true);      
  
            if (isset($error['status'])) {
              $this->message['error'][] = sprintf('Status %s: %s', $error['status'], $error['detail']);
            }
            if (isset($error['statusCode'])) {
              $this->message['error'][] = sprintf('Status %s: %s', $error['statusCode'], $error['description']);
            }
  
            $this->LoggingManager->log('ERROR', 'getAccessToken', array('exception' => $error));
          } else {
            $this->LoggingManager->log('ERROR', 'getAccessToken', array('exception' => $ex));
          }        
        }
      }
    }


    private function initShoppingCart() {
      $headers = [
        'Authorization' => 'Bearer '.$this->data['access_token'],
      ];

      $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_URL, '/app/shoppingcart'), $headers);

      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);
        $this->data['shopOrderId'] = $response['shopOrderId'];   
      } catch (Exception $ex) {
        if (is_object($ex)
            && method_exists($ex, 'getResponse')
            )
        {
          $error = json_decode($ex->getResponse()->getBody(), true);      

          if (isset($error['status'])) {
            $this->message['error'][] = sprintf('Status %s: %s', $error['status'], $error['detail']);
          }
          if (isset($error['statusCode'])) {
            $this->message['error'][] = sprintf('Status %s: %s', $error['statusCode'], $error['description']);
          }

          $this->LoggingManager->log('ERROR', 'initShoppingCart', array('exception' => $error));
        } else {
          $this->LoggingManager->log('ERROR', 'initShoppingCart', array('exception' => $ex));
        }        
      }
    }


    private function getUrl($url, $path) {
      return $url.$path;
    }


    private function buildLabelData() {
      $price_query = xtc_db_query("SELECT PROPR
                                     FROM `internetmarke`
                                    WHERE PROID = '".(int)$this->product."'");
      $price = xtc_db_fetch_array($price_query);

      // customers_data
      $customers_data = $this->buildCustomersData();
            
      $Shipment = new stdClass();
      $Shipment->type = 'AppShoppingCartPDFRequest';
      $Shipment->shopOrderId = $this->data['shopOrderId'];
      $Shipment->pageFormatId = $this->format;
      $Shipment->positions = array();
      $Shipment->total = ($price['PROPR'] * 100);
      $Shipment->createManifest = true;
      $Shipment->createShippingList = '1';
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
      $Address->name = (($data['company'] != '') ? substr($data['company'], 0, 35) : substr(($data['firstname'] . ' ' . $data['lastname']), 0, 35));
      if ($data['company'] != ''
          && ($data['firstname'] != '' || $data['lastname'] != '')
          )
      {
         $Address->additionalName = substr(($data['firstname'] . ' ' . $data['lastname']), 0, 35);
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
