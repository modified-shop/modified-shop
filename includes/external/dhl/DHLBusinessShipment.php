<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('API_AUTH', 'http://dhl.de/webservice/cisbase');
  define('API_URL', 'https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/geschaeftskundenversand-api/3.1/geschaeftskundenversand-api-3.1.wsdl');

  define('DHL_SANDBOX_URL', 'https://cig.dhl.de/services/sandbox/soap');
  define('DHL_PRODUCTION_URL', 'https://cig.dhl.de/services/production/soap');

  // include needed function
  require_once(DIR_FS_INC.'xtc_get_countries_with_iso_codes.inc.php');
  require_once(DIR_FS_INC.'xtc_get_countries.inc.php');

  // include nneded classes
  require_once(DIR_WS_CLASSES.'order.php');


  class DHLBusinessShipment {

    private $data;
    private $info;
    private $client;
    private $order;

    protected $sandbox;


    function __construct($data) {
      $this->sandbox = false;    
      $this->notification = MODULE_DHL_NOTIFICATION == 'True';
      $this->LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_dhl_%s_%s.log', 'dhl', 'debug');
      
      $this->data = array(
        'user'          => MODULE_DHL_USER,
        'signature'     => MODULE_DHL_SIGNATURE,
        'ekp'           => MODULE_DHL_EKP,
        'api_user'      => 'ModifiedShopV1_1',
        'api_password'  => 'tHv3UHNBc9FE6VXZz2mgWXK9oEFO5i',
      );
      
      $account = preg_split("/[:,]/", MODULE_DHL_ACCOUNT); 
      for ($i=0, $n=count($account); $i<$n; $i+=2) {
        $this->data['account'][$account[$i]] = $account[$i+1];
      }
      
      $country = xtc_get_countries_with_iso_codes(STORE_COUNTRY);
      $street_address = $this->parse_street_address(MODULE_DHL_ADDRESS);
      $this->info = array(
        'name'            => MODULE_DHL_FIRSTNAME . ' ' . MODULE_DHL_LASTNAME,
        'firstname'       => MODULE_DHL_FIRSTNAME,
        'lastname'        => MODULE_DHL_LASTNAME,
        'company'         => MODULE_DHL_COMPANY,
        'street_name'     => $street_address['street_name'],
        'street_number'   => $street_address['street_number'],
        'street_address'  => MODULE_DHL_ADDRESS,
        'postcode'        => MODULE_DHL_POSTCODE,
        'city'            => MODULE_DHL_CITY,
        'country'         => $country['countries_name'],
        'country_iso_2'   => $country['countries_iso_code_2'],
        'email_address'   => STORE_OWNER_EMAIL_ADDRESS,
        'telephone'       => MODULE_DHL_TELEPHONE,
      );
      $this->info = $this->encode_request($this->info);
      
      foreach ($data as $k => $v) {
        $this->$k = $v;
      }
      
      if (isset($this->weight)) {
        $this->weight = str_replace(',', '.', $this->weight);
      }
      
      $this->insurance_array = array(
        0 => '500',
        1 => '2500',
        2 => '25000',
      );      
    }


    public function CreateLabel($order_id) {
      $this->order = new order($order_id);
      
      $this->buildClient();
      $request = $this->buildLabelData();
      
      try {
        $response = $this->client->createShipmentOrder($request);
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'CreateLabel', array('exception' => $ex));
        return array('message' => 'ERROR - <b>Code:</b> '.$ex->faultcode.' <b>Message:</b> '.utf8_decode($ex->faultstring));
      }
      
      if ($response->Status->statusCode == '0') {
        $this->SaveLabel(
          $response->CreationState->shipmentNumber, 
          $response->CreationState->LabelData->labelUrl, 
          ((isset($response->CreationState->LabelData->exportLabelUrl)) ? $response->CreationState->LabelData->exportLabelUrl : '')
        );
      
        return $response->CreationState->shipmentNumber;
      } else {
        $message = array('ERROR - <b>Code:</b> '.$response->Status->statusCode.' <b>Message:</b> '.utf8_encode($response->Status->statusText));
        if (isset($response->CreationState->LabelData->Status->statusMessage)) {
          foreach ($response->CreationState->LabelData->Status->statusMessage as $status_message) {
            if (!isset($message[md5($status_message)])) {
              $message[md5($status_message)] = utf8_encode($status_message);
            }
          }
        }
        
        $this->LoggingManager->log('DEBUG', 'CreateLabel', array('exception' => $response->CreationState->LabelData->Status->statusMessage));
        return array('message' => implode('<br>- ',$message));
      }
    }


    private function SaveLabel($shipment_number, $dhl_label_url, $dhl_export_url = '') {
      $sql_data_array = array(
        'orders_id' => $this->order->info['order_id'],
        'carrier_id' => '1',
        'external' => '2',
        'date_added' => 'now()',
        'parcel_id' => $shipment_number,
        'dhl_label_url' => $dhl_label_url,
        'dhl_export_url' => $dhl_export_url,
      );
      xtc_db_perform(TABLE_ORDERS_TRACKING, $sql_data_array);
    }


    public function DeleteLabel($shipmentNumber) {
      $this->buildClient();

      // request
      $request = new stdClass();
      $request->Version = $this->buildVersion();
      $request->shipmentNumber = $shipmentNumber;
      
      try {
        $response = $this->client->deleteShipmentOrder($request);
      
        return $response->Status->statusCode;
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'buildClient', array('exception' => $ex));
        return array('message' => 'ERROR - <b>Code:</b> '.$ex->faultcode.' <b>Message:</b> '.utf8_encode($ex->faultstring));
      }
    }


    private function buildVersion() {
      $Version = new stdClass();
      $Version->majorRelease = 3;
      $Version->minorRelease = 1;
      $Version->build = 0;
      
      return $Version;
    }


    private function buildClient() {
      $header = $this->buildAuthHeader();
      
      if ($this->sandbox === true) {
        $location = DHL_SANDBOX_URL;
      } else {
        $location = DHL_PRODUCTION_URL;
      }
      
      $ssl_opts = array(
        'ssl' => array(
          'verify_peer' => false, 
          'verify_peer_name' => false
        )
      );
      
      $auth_params = array(
        'login' => $this->data['api_user'],
        'password' => $this->data['api_password'],
        'location' => $location,
        'trace' => 1,
        'authentication' => SOAP_AUTHENTICATION_BASIC,
        'connection_timeout' => 60,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'stream_context' => stream_context_create($ssl_opts)
      );
      
      try {
        $this->client = new SoapClient(API_URL, $auth_params);
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'buildClient', array('exception' => $ex));
        return array('message' => 'ERROR - <b>Code:</b> '.$ex->faultcode.' <b>Message:</b> '.$ex->faultstring);
      }
      
      try {
        $this->client->__setSoapHeaders($header);
      } catch (Exception $ex) {
        $this->LoggingManager->log('DEBUG', 'buildClient', array('exception' => $ex));
        return array('message' => 'ERROR - <b>Code:</b> '.$ex->faultcode.' <b>Message:</b> '.$ex->faultstring);
      }
    }


    private function buildAuthHeader() {
      $auth_params = array(
        'user' => $this->data['user'],
        'signature' => $this->data['signature'],
        'type' => 0
      );
    
      return new SoapHeader(API_AUTH, 'Authentification', $auth_params);
    }


    private function buildLabelData() {
      // customers_data
      $customers_data = $this->buildCustomersData();
      
      // Shipper
      $Shipper = $this->buildShippingDetails($this->info, 'sender');
      
      // Receiver
      $Receiver = $this->buildShippingDetails($customers_data, 'receiver');
      
      // ReturnReceiver
      $ReturnReceiver = $this->buildShippingDetails($this->info, 'sender');
      
      // Service
      $Service = new stdClass();
      
      // cod
      if ($this->data['payment_class'] == 'cod') {
        $Service->CashOnDelivery = array(
          'active' => 1,
          'codAmount' => $this->data['amount']
        );
        
        // bankdata
        $BankData = new stdClass();
        $BankData->accountOwner = MODULE_DHL_ACCOUNT_OWNER;
        $BankData->bankName = MODULE_DHL_BANK_NAME;
        $BankData->iban = MODULE_DHL_IBAN;
        $BankData->bic = MODULE_DHL_BIC;
        $BankData->note1 = $this->data['reference'];
      }
      
      // insurance
      if ($this->insurance > 0) {
        $Service->AdditionalInsurance = array(
          'active' => 1,
          'insuranceAmount' => $this->insurance_array[$this->insurance]
        );
      }
      
      // notification
      if ($this->notification === true) {
        $Notification = new stdClass();
        $Notification->recipientEmailAddress = $this->data['email_address'];
        $Service->Notification = $Notification;
      }
      
      // international
      if ($this->data['product_code'] == '53') {
        $Service->Premium = array(
          'active' => 1,
        );
      }
      
      // avs
      if ($this->avs > 0) {
        $Service->VisualCheckOfAge['active'] = '1';
        $Service->VisualCheckOfAge['type'] = 'A'.$this->avs;
      }
      
      // personal
      if ($this->personal > 0) {
        $Service->NamedPersonOnly['active'] = '1';
      }
      
      // bulky
      if ($this->bulky > 0) {
        $Service->BulkyGoods['active'] = '1';
      }
      
      // ShipmentDetails
      $ShipmentDetails = new stdClass();
      $ShipmentDetails->product = $this->data['product'];
      $ShipmentDetails->accountNumber = $this->data['ekp'].$this->data['product_code'].((isset($this->data['account'][$customers_data['country_iso_2']])) ? $this->data['account'][$customers_data['country_iso_2']] : $this->data['account']['WORLD']);
      $ShipmentDetails->shipmentDate = date('Y-m-d');
      $ShipmentDetails->customerReference = $this->data['reference'];
      
      if ($this->retoure > 0) {
        $ShipmentDetails->returnShipmentAccountNumber = $this->data['ekp'].'07'.((isset($this->data['account'][$customers_data['country_iso_2']])) ? $this->data['account'][$customers_data['country_iso_2']] : $this->data['account']['WORLD']);
        $ShipmentDetails->returnShipmentReference = $this->data['reference'];
      }
      
      $ShipmentDetails->Service = $Service;
      if (isset($BankData) && is_object($BankData)) {
        $ShipmentDetails->BankData = $BankData;
      }
    
      // ShipmentItem
      $ShipmentItem = new stdClass();
      $ShipmentItem->weightInKG = $this->data['weight'];
      $ShipmentDetails->ShipmentItem = $ShipmentItem;
      
      // Shipment
      $Shipment = new stdClass();
      $Shipment->ShipmentDetails = $ShipmentDetails;
      $Shipment->Shipper = $Shipper;    
      $Shipment->Receiver = $Receiver;
      if ($this->retoure > 0) {
        $Shipment->ReturnReceiver = $ReturnReceiver;
      }
      
      $tax_rate = 0;                        
      $tax_rates_query = xtc_db_query("SELECT tr.* 
                                         FROM " . TABLE_COUNTRIES . " c
                                         JOIN " . TABLE_ZONES_TO_GEO_ZONES . " ztgz 
                                              ON c.countries_id = ztgz.zone_country_id
                                         JOIN " . TABLE_TAX_RATES . " tr 
                                              ON tr.tax_zone_id = ztgz.geo_zone_id
                                        WHERE c.countries_iso_code_2 = '".xtc_db_input($customers_data['country_iso_2'])."'
                                     GROUP BY ztgz.zone_country_id");
      while ($tax_rates = xtc_db_fetch_array($tax_rates_query, true)) {
        $tax_rate += $tax_rates['tax_rate'];
      }
      
      if ($tax_rate == 0) {
        $Shipment->ExportDocument = $this->buildExportDocument();
        $Shipment->ShipmentDetails->Service->Endorsement['active'] = '1';
        $Shipment->ShipmentDetails->Service->Endorsement['type'] = 'IMMEDIATE';
      }
      
      // ShipmentOrder
      $ShipmentOrder = new stdClass();
      $ShipmentOrder->labelResponseType = 'URL';
      $ShipmentOrder->PrintOnlyIfCodeable['active'] = $this->codeable;
      $ShipmentOrder->sequenceNumber = $this->data['orders_id'];
      $ShipmentOrder->Shipment = $Shipment;
    
      // request
      $request = new stdClass();
      $request->Version = $this->buildVersion();
      $request->ShipmentOrder = $ShipmentOrder;
        
      return $request;
    }


    private function buildCustomersData() {
      $street_address = $this->parse_street_address($this->order->delivery['street_address']);
      
      $customers_data = array(
        'name' => $this->order->delivery['name'],
        'firstname' => $this->order->delivery['firstname'],
        'lastname' => $this->order->delivery['lastname'],
        'company' => $this->order->delivery['company'],
        'suburb' => $this->order->delivery['suburb'],
        'street_name' => $street_address['street_name'],
        'street_number' => $street_address['street_number'],
        'street_address' => $this->order->delivery['street_address'],
        'postcode' => $this->order->delivery['postcode'],
        'city' => $this->order->delivery['city'],
        'country' => $this->order->delivery['country'],
        'country_iso_2' => $this->order->delivery['country_iso_2'],
        'email_address' => $this->order->customer['email_address'],
        'packstation' => ((stripos($street_address['street_name'], 'packstation') !== false) ? true : false),
        'telephone' => $this->order->customer['telephone'],
      );
      $customers_data = $this->encode_request($customers_data);
      
      // global data
      $this->data['reference'] = $this->order->info['order_id'];
      $this->data['orders_id'] = $this->order->info['order_id'];
      $this->data['orders_status'] = $this->order->info['orders_status_id'];
      $this->data['payment_class'] = $this->order->info['payment_class'];
      $this->data['amount'] = number_format(($this->order->info['pp_total']), 2, '.', '');
      $this->data['currency'] = $this->order->info['currency'];
      $this->data['name'] = $this->order->delivery['name'];
      $this->data['email_address'] = $this->order->customer['email_address'];
      $this->data['weight'] = ($this->weight > 0) ? $this->weight : $this->calculate_weight($this->order->info['order_id']);
      
      // create product code
      switch ($this->order->delivery['country_iso_2']) {
        case 'DE':
          $this->data['product'] = 'V01PAK';
          $this->data['product_code'] = '01';
          if ($this->type == 1 && $this->data['weight'] <= 1) {
            $this->data['product'] = 'V62WP';
            $this->data['product_code'] = '62';
          }
          break;
        default:
          $this->data['product'] = 'V53WPAK';
          $this->data['product_code'] = '53';
          break;
      }
      $this->data = $this->encode_request($this->data);
  
      return $customers_data;
    }


    private function buildShippingDetails($data, $type = 'sender') {
      $Name = new stdClass();
      $Name->name1 = (($data['company'] != '') ? substr($data['company'], 0, 35) : substr(($data['firstname'] . ' ' . $data['lastname']), 0, 35));
      $Name->name2 = (($data['company'] != '') ? substr(($data['firstname'] . ' ' . $data['lastname']), 0, 35) : '');
      
      $Origin = new stdClass();
      $Origin->countryISOCode = $data['country_iso_2'];
      
      $Communication = new stdClass();
      $Communication->phone = $data['telephone'];
      //$Communication->email = $data['email_address'];
      
      $Address = new stdClass();
      $Address->streetName = $data['street_name'];
      $Address->streetNumber = $data['street_number'];
      $Address->addressAddition = $data['suburb'];
      $Address->zip = $data['postcode'];
      $Address->city = $data['city'];
      $Address->Origin = $Origin;
  
      if ($data['suburb'] != '' 
          && $data['country_iso_2'] == 'DE'
          && $Name->name2 == ''
          ) 
      {
        $Name->name2 = $data['suburb'];
      }
      
      if (isset($data['packstation']) && $data['packstation'] === true) {
        $Packstation = new stdClass();
        $Packstation->packstationNumber = preg_replace('/[^0-9]/', '', (($data['street_number'] != '') ? $data['street_number'] : $data['street_name']));
        $Packstation->postNumber = preg_replace('/[^0-9]/', '', $data['company']);
        $Packstation->zip = $data['postcode'];
        $Packstation->city = $data['city'];
        $Packstation->Origin = $Origin;
      }
      
      switch ($type) {
        case 'sender':
          $shipping_details = new stdClass();
          $shipping_details->Name = $Name;
          $shipping_details->Address = $Address;
          $shipping_details->Communication = $Communication;
          break;
    
        case 'receiver':
          $shipping_details = new stdClass();
          if (isset($Packstation) && is_object($Packstation)) {
            $shipping_details->name1 = (($Name->name2 != '') ? $Name->name2 : $Name->name1);
            $shipping_details->Packstation = $Packstation;
          } else {
            $shipping_details->name1 = $Name->name1;
            $shipping_details->Address = $Address;
            $shipping_details->Address->name2 = $Name->name2;
          }
          //$shipping_details->Communication = $Communication;
          break;
      }
  
      return $shipping_details;
    }


    private function buildExportDocument() {
      $ExportDocument = new stdClass();
      $ExportDocument->exportType = 'COMMERCIAL_GOODS';
      $ExportDocument->placeOfCommital = MODULE_DHL_CITY;
      $ExportDocument->additionalFee = $this->order->info['pp_shipping'] + $this->order->info['pp_fee'];
      $ExportDocument->customsCurrency = $this->order->info['currency'];
      
      $ExportDocument->ExportDocPosition = array();
      for ($i=0, $n=count($this->order->products); $i<$n; $i++) {
        $weight = ($this->order->products[$i]['qty'] * $this->order->products[$i]['weight']);
        if (isset($this->order->products[$i]['attributes']) && count($this->order->products[$i]['attributes']) > 0) {
          for ($j = 0, $k = count($this->order->products[$i]['attributes']); $j < $k; $j ++) {
            if ($this->order->products[$i]['attributes'][$j]['weight_prefix'] == '-' && $this->order->products[$i]['attributes'][$j]['options_values_weight'] > 0) {
              $this->order->products[$i]['attributes'][$j]['options_values_weight'] = $this->order->products[$i]['attributes'][$j]['options_values_weight'] * (-1);
            }
            $weight += ($this->order->products[$i]['qty'] * $this->order->products[$i]['attributes'][$j]['options_values_weight']);
          }
        }
        $ExportDocument->ExportDocPosition[$i]->description = ((isset($this->order->products[$i]['tariff_title']) && $this->order->products[$i]['tariff_title'] != '') ? $this->order->products[$i]['tariff_title'] : $this->order->products[$i]['name']);
        $ExportDocument->ExportDocPosition[$i]->countryCodeOrigin = ((isset($this->order->products[$i]['origin']) && $this->order->products[$i]['origin'] != '') ? $this->order->products[$i]['origin'] : $this->info['country_iso_2']);
        $ExportDocument->ExportDocPosition[$i]->customsTariffNumber = ((isset($this->order->products[$i]['tariff']) && $this->order->products[$i]['tariff'] != '') ? $this->order->products[$i]['tariff'] : '');
        $ExportDocument->ExportDocPosition[$i]->amount = $this->order->products[$i]['quantity'];
        $ExportDocument->ExportDocPosition[$i]->netWeightInKG = $weight + (($weight == 0) ? 0.1 : 0);
        $ExportDocument->ExportDocPosition[$i]->customsValue = $this->order->products[$i]['price'];
      }
      
      return $ExportDocument;
    }


    private function parse_street_address($street_address) {
      preg_match_all("! [0-9]{1,5}[/ \- 0-9 a-z A-Z]*!m", $street_address, $matches, PREG_SET_ORDER);
      if (count($matches) < 1) {
        preg_match_all("/^([\d][a-z-\/\d]*)|[\s]+([\d][a-z-\/][\d]*)/i", $street_address, $matches, PREG_SET_ORDER);
      }
      if (count($matches) < 1) {
        preg_match_all("![0-9]{1,5}[/ \- 0-9 a-z A-Z]*!m", $street_address, $matches, PREG_SET_ORDER);
      }
      $addr = end($matches);
    
      return array(
        'street_name' => trim(str_replace(trim($addr[0]), '', $street_address), ', '),
        'street_number' => trim($addr[0]),
      );
    }


    public function calculate_weight($order_id) {
      if (!isset($this->order)) {
        $this->order = new order($order_id);
      }
            
      $weight = (double)SHIPPING_BOX_WEIGHT;
      for ($i = 0, $n = count($this->order->products); $i < $n; $i++) {
        $weight += ($this->order->products[$i]['qty'] * $this->order->products[$i]['weight']);
        if (isset($this->order->products[$i]['attributes']) && count($this->order->products[$i]['attributes']) > 0) {
          for ($j = 0, $k = count($this->order->products[$i]['attributes']); $j < $k; $j ++) {
            if ($this->order->products[$i]['attributes'][$j]['weight_prefix'] == '-' && $this->order->products[$i]['attributes'][$j]['options_values_weight'] > 0) {
              $this->order->products[$i]['attributes'][$j]['options_values_weight'] = $this->order->products[$i]['attributes'][$j]['options_values_weight'] * (-1);
            }
            $weight += ($this->order->products[$i]['qty'] * $this->order->products[$i]['attributes'][$j]['options_values_weight']);
          }
        }
      }
    
      if ($weight == '0') {
        $weight = '1';
      }
    
      return $weight;
    }


    private function encode_request($array) {
      foreach ($array as $key => $value) {
        if (is_array($value)) {
          $array[$key] = $this->encode_request($value);
        } else {
          $array[$key] = encode_utf8(decode_htmlentities($value), $_SESSION['language_charset'], true);
        }
      }
    
      return $array;
    }

  }
