<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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
  class DHLBusinessShipment {

    const DHL_API_AUTH = 'https://api-%s.dhl.com/parcel/de/account/auth/ropc/v1';
    const DHL_API_URL = 'https://api-%s.dhl.com/parcel/de/shipping/v2';

    private $data;
    private $info;
    private $client;
    private $order;
    private $insurance_array;
    private $loglevel;
    private $LoggingManager;
    private $message;

    protected $sandbox;


    function __construct($data, $init = true) {
      $this->sandbox = (MODULE_DHL_BUSINESS_MODE === 'sandbox');    
      $this->loglevel = defined('MODULE_DHL_BUSINESS_LOGLEVEL') ? MODULE_DHL_BUSINESS_LOGLEVEL : 'ERROR';
      $this->LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_dhl_%s_%s.log', 'dhl', strtolower($this->loglevel));
      
      $this->data = array(
        'user'          => MODULE_DHL_BUSINESS_USER,
        'signature'     => MODULE_DHL_BUSINESS_SIGNATURE,
        'ekp'           => MODULE_DHL_BUSINESS_EKP,
        'api_user'      => 'lWh2GbG4I1VyVRvADeWNiWGNKVPI4Wfk',
        'api_password'  => 'PUrMdYgQnh9ko8ii',
      );
      
      $account_data = preg_split("/[:,]/", MODULE_DHL_BUSINESS_ACCOUNT); 
      for ($i=0, $n=count($account_data); $i<$n; $i+=2) {
        if (!isset($this->data['account'][$account_data[$i]])) {
          $this->data['account'][$account_data[$i]] = array();
        }
        if (strpos($account_data[$i+1], 'PK') !== false) {
          $this->data['account'][$account_data[$i]]['PK'] = preg_replace('/[^\d]/', '', $account_data[$i+1]);
        } elseif (strpos($account_data[$i+1], 'KP') !== false) {
          $this->data['account'][$account_data[$i]]['KP'] = preg_replace('/[^\d]/', '', $account_data[$i+1]);
        } elseif (strpos($account_data[$i+1], 'WP') !== false) {
          $this->data['account'][$account_data[$i]]['WP'] = preg_replace('/[^\d]/', '', $account_data[$i+1]);
        } elseif (strpos($account_data[$i+1], 'RT') !== false) {
          $this->data['account'][$account_data[$i]]['RT'] = preg_replace('/[^\d]/', '', $account_data[$i+1]);
        } else {
          $this->data['account'][$account_data[$i]]['PK'] = preg_replace('/[^\d]/', '', $account_data[$i+1]);
          $this->data['account'][$account_data[$i]]['KP'] = preg_replace('/[^\d]/', '', $account_data[$i+1]);
          $this->data['account'][$account_data[$i]]['WP'] = preg_replace('/[^\d]/', '', $account_data[$i+1]);
          $this->data['account'][$account_data[$i]]['RT'] = preg_replace('/[^\d]/', '', $account_data[$i+1]);
        }
      }
      
      $country = xtc_get_countries_with_iso_codes(STORE_COUNTRY);
     
      $this->info = array(
        'name'            => MODULE_DHL_BUSINESS_FIRSTNAME . ' ' . MODULE_DHL_BUSINESS_LASTNAME,
        'firstname'       => MODULE_DHL_BUSINESS_FIRSTNAME,
        'lastname'        => MODULE_DHL_BUSINESS_LASTNAME,
        'company'         => MODULE_DHL_BUSINESS_COMPANY,
        'street_address'  => MODULE_DHL_BUSINESS_ADDRESS,
        'postcode'        => MODULE_DHL_BUSINESS_POSTCODE,
        'city'            => MODULE_DHL_BUSINESS_CITY,
        'country'         => $country['countries_name'],
        'country_iso_2'   => $country['countries_iso_code_2'],
        'country_iso_3'   => $country['countries_iso_code_3'],
        'email_address'   => STORE_OWNER_EMAIL_ADDRESS,
        'telephone'       => preg_replace('/[^\+\d]/', '', MODULE_DHL_BUSINESS_TELEPHONE),
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
      
      if ($init === true) {
        $this->client = new \GuzzleHttp\Client();
        $this->getAccessToken();
      }
    }


    public function CreateLabel($order_id) {
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

      $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_URL, '/orders?includeDocs=URL'.(($this->codeable == true) ? '&mustEncode=true' : '')), $headers, $body);
     
      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);
        
        foreach ($response['items'] as $items) {
          $tracking_id = $this->SaveLabel(
            $items['shipmentNo'], 
            $items['label']['url'], 
            ((isset($items['customsDoc'])) ? $items['customsDoc']['url'] : ''), 
          );

          $result['label'][] = array(
            'tracking_id' => $tracking_id,
            'parcel_id' => $items['shipmentNo'],
          ); 
          
          if (isset($items['validationMessages']) && $this->loglevel == 'INFO') {
            foreach ($items['validationMessages'] as $messages) {
              $this->message['warning'][] = sprintf('Property %s: %s', ((isset($messages['property'])) ? $messages['property'] : ''), $messages['validationMessage']);
            }
          }
        }
      } catch (Exception $ex) {
        if (is_object($ex)
            && method_exists($ex, 'getResponse')
            )
        {
          $error = json_decode($ex->getResponse()->getBody(), true);      
  
          if (isset($error['items'])) {
            foreach ($error['items'] as $items) {
              if (isset($items['validationMessages'])) {
                foreach ($items['validationMessages'] as $messages) {
                  $this->message['error'][] = sprintf('Property %s: %s', ((isset($messages['property'])) ? $messages['property'] : ''), $messages['validationMessage']);
                }
              }
            }
          } elseif (isset($error['detail'])) {
            $this->message['error'][] = $error['detail'];
          }
  
          $this->LoggingManager->log('ERROR', 'CreateLabel', array('exception' => $error));
        } else {
          $this->LoggingManager->log('ERROR', 'CreateLabel', array('exception' => $ex));
        }
      }
      
      $result['message'] = $this->message;
      
      return $result;
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
      
      return xtc_db_insert_id();
    }


    public function DeleteLabel($shipmentNumber) {
      $headers = [
        'Authorization' => 'Bearer '.$this->data['access_token'],
      ];

      $result = array(
        'message' => array(),
      );

      $request = new \GuzzleHttp\Psr7\Request('DELETE', $this->getUrl(self::DHL_API_URL, '/orders?shipment='.$shipmentNumber), $headers);

      try {
        $response = $this->client->send($request);
        $response = json_decode($response->getBody()->getContents(), true);        
      } catch (Exception $ex) {
        if (is_object($ex)
            && method_exists($ex, 'getResponse')
            )
        {
          $error = json_decode($ex->getResponse()->getBody(), true);      
  
          foreach ($error['items'] as $items) {
            if (isset($items['sstatus'])) {
              $this->message['error'][] = sprintf('Status %s: %s', $items['sstatus']['status'], $items['sstatus']['detail']);
            }
          }
  
          $this->LoggingManager->log('ERROR', 'DeleteLabel', array('exception' => $error));
        } else {
          $this->LoggingManager->log('ERROR', 'DeleteLabel', array('exception' => $ex));
        }
      }

      $result['message'] = $this->message;
      
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
          'grant_type' => 'password',
        )
      );
      
      if (!isset($this->data['access_token'])) {
        $request = new \GuzzleHttp\Psr7\Request('POST', $this->getUrl(self::DHL_API_AUTH, '/token'), $headers);
        
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
  
            $this->message['error'][] = sprintf('Status %s: %s', $error['status'], $error['detail']);
  
            $this->LoggingManager->log('ERROR', 'getAccessToken', array('exception' => $error));
          } else {
            $this->LoggingManager->log('ERROR', 'getAccessToken', array('exception' => $ex));
          }        
        }
      }
    }


    private function getUrl($url, $path) {
      if ($this->sandbox === true) {
        $url = sprintf($url, 'sandbox');
      } else {
        $url = sprintf($url, 'eu');
      }
      return $url.$path;
    }


    private function buildLabelData() {
      // customers_data
      $customers_data = $this->buildCustomersData();
            
      // Service
      $Service = new stdClass();

      // international
      if (in_array($this->data['product_code'], array('53', '66'))) {
        $this->notification = 1;
        if ($this->premium > 0) {
          $Service->premium = true;
        }
        if ($this->dutypaid > 0) {
          $Service->postalDeliveryDutyPaid = true;
        }
        if ($this->droppoint > 0) {
          $Service->closestDropPoint = true;
        }
      }

      // Shipper
      $Shipper = $this->buildShippingDetails($this->info, 'sender');
            
      // ReturnReceiver
      $ReturnReceiver = $this->buildShippingDetails($this->info, 'sender');

      // Receiver
      $Receiver = $this->buildShippingDetails($customers_data, 'receiver');
      
      // cod
      if ($this->data['payment_class'] == 'cod') {        
        // bankdata
        $BankData = new stdClass();
        $BankData->accountHolder = MODULE_DHL_BUSINESS_ACCOUNT_OWNER;
        $BankData->bankName = MODULE_DHL_BUSINESS_BANK_NAME;
        $BankData->iban = MODULE_DHL_BUSINESS_IBAN;
        $BankData->bic = MODULE_DHL_BUSINESS_BIC;
        
        $Service->cashOnDelivery = array(
          'amount' => array(
            'currency' => $this->data['currency'],
            'value' => $this->data['amount'],
          ),
          'bankAccount' => $BankData,
          'transferNote1' => $this->data['reference'],
        );
      }
      
      // insurance
      if ($this->insurance > 0) {
        $Service->additionalInsurance = array(
          'currency' => $this->data['currency'],
          'value' => $this->insurance_array[$this->insurance]
        );
      }
      
      // avs
      if ($this->avs > 0) {
        $Service->visualCheckOfAge = 'A'.$this->avs;
      }
      
      // personal
      if ($this->personal > 0) {
        $Service->namedPersonOnly = true;
      }
      
      // no neighbour
      if ($this->no_neighbour > 0) {
        $Service->noNeighbourDelivery = true;
      }

      // parcel outlet
      if ($this->parcel_outlet > 0) {
        $Service->parcelOutletRouting = $customers_data['email_address'];
        if ($this->notification != 1) {
          $Service->parcelOutletRouting = $this->info['email_address'];
        }
      }

      // signed
      if ($this->data['product'] == 'V01PAK' 
          && $this->signed > 0
          )
      {
        $Service->signedForByRecipient = true;
      }

      // bulky
      if ($this->bulky > 0) {
        $Service->bulkyGoods = true;
      }
      
      // ident
      if ($this->ident > 0) {        
        $Ident = new stdClass();
        $Ident->lastName = $this->order->delivery['lastname'];
        $Ident->firstName = $this->order->delivery['firstname'];
        $Ident->dateOfBirth = date('Y-m-d', strtotime($this->dob));
        $Ident->minimumAge = 'A'.$this->ident;
        
        $Service->identCheck = $Ident;
      }
      
      // endorsement
      if (in_array($this->data['product_code'], array('53', '66'))) {
        $Service->endorsement = $this->endorsement;
      }
      
      // retoure      
      if ($this->retoure > 0) {
        $Retoure = new stdClass();
        $Retoure->billingNumber = $this->data['ekp'].'07'.((isset($this->data['account'][$customers_data['country_iso_2']])) ? $this->data['account'][$customers_data['country_iso_2']]['RT'] : $this->data['account']['WORLD']['RT']);
        $Retoure->refNo = $this->data['reference'];
        $Retoure->returnAddress = $ReturnReceiver;
        
        $Service->dhlRetoure = $Retoure;
      }
            
      // Details
      $Details = new stdClass();
      $Details->weight = array(
        'uom' => 'kg',
        'value' => (double)sprintf("%01.2f", $this->data['weight']),
      );
      
      // Shipment
      $Shipment = new stdClass();
      $Shipment->product = $this->data['product'];
      $Shipment->billingNumber = $this->data['ekp'].$this->data['product_code'].((isset($this->data['account'][$customers_data['country_iso_2']])) ? $this->data['account'][$customers_data['country_iso_2']][$this->data['product_type']] : $this->data['account']['WORLD'][$this->data['product_type']]);
      $Shipment->shipDate = date('Y-m-d');
      $Shipment->refNo = $this->data['reference'];
      $Shipment->shipper = $Shipper;    
      $Shipment->consignee = $Receiver;
      $Shipment->details = $Details;
      $Shipment->services = $Service;
            
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
        $Shipment->customs = $this->buildExportDocument();
        $Shipment->services->endorsement = $this->endorsement;
      }
          
      // request
      $request = new stdClass();
      $request->shipments = array($Shipment);

      return $request;
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
        'email_address' => $this->order->customer['email_address'],
        'packstation' => ((stripos($this->order->delivery['street_address'], 'packstation') !== false) ? true : false),
        'postfiliale' => ((stripos($this->order->delivery['street_address'], 'postfiliale') !== false) ? true : false),
        'postnumber' => '',
        'telephone' => preg_replace('/[^\+\d]/', '', $this->order->customer['telephone']),
      );
      
      if ($customers_data['packstation'] === true || $customers_data['postfiliale'] === true) {        
        if (preg_replace('/[^0-9]/', '', $customers_data['company']) != '') {
          $customers_data['postnumber'] = preg_replace('/[^0-9]/', '', $customers_data['company']);
          $customers_data['company'] = '';
          $customers_data['suburb'] = '';
        }
        if (preg_replace('/[^0-9]/', '', $customers_data['suburb']) != '') {
          $customers_data['postnumber'] = preg_replace('/[^0-9]/', '', $customers_data['suburb']);
          $customers_data['company'] = '';
          $customers_data['suburb'] = '';
        }
      }

      $customers_data = $this->encode_request($customers_data);
      
      // global data
      $this->data['orders_id'] = $this->order->info['order_id'];
      $this->data['reference'] = $this->getReference();
      $this->data['orders_status'] = $this->order->info['orders_status_id'];
      $this->data['payment_class'] = $this->order->info['payment_class'];
      $this->data['amount'] = number_format(($this->order->info['pp_total']), 2, '.', '');
      $this->data['currency'] = $this->order->info['currency'];
      $this->data['name'] = $this->order->delivery['name'];
      $this->data['email_address'] = $this->order->customer['email_address'];
      $this->data['weight'] = ($this->weight > 0) ? $this->weight : $this->calculate_weight($this->order->info['order_id']);
      $this->data['product_type'] = 'PK';
      
      // create product code
      switch ($this->order->delivery['country_iso_2']) {
        case 'DE':
          $this->data['product'] = 'V01PAK';
          $this->data['product_code'] = '01';
          if ($this->type == 1) {
            $this->data['product'] = 'V62KP';
            $this->data['product_code'] = '62';
            $this->data['product_type'] = 'KP';
          }
          break;
        default:
          $this->data['product'] = 'V53WPAK';
          $this->data['product_code'] = '53';
          if ($this->type == 1) {
            $this->data['product'] = 'V66WPI';
            $this->data['product_code'] = '66';
            $this->data['product_type'] = 'WP';
          }
          break;
      }
      $this->data = $this->encode_request($this->data);
  
      return $customers_data;
    }


    private function buildShippingDetails($data, $type = 'sender') {
      $Address = new stdClass();
      $Address->name1 = (($data['company'] != '') ? substr($data['company'], 0, 35) : substr(($data['firstname'] . ' ' . $data['lastname']), 0, 35));
      if ($data['company'] != ''
          && ($data['firstname'] != '' || $data['lastname'] != '')
          )
      {
         $Address->name2 = substr(($data['firstname'] . ' ' . $data['lastname']), 0, 35);
      }
      
      if (isset($data['suburb'])
          && $data['suburb'] != '' 
          ) 
      {
        if (!isset($Address->name2)) {
          $Address->name2 = $data['suburb'];
        } else {
          $Address->name3 = $data['suburb'];
        }
      }
      $Address->addressStreet = $this->format_street_address($data['street_address']);
      $Address->postalCode = $data['postcode'];
      $Address->city = $data['city'];
      $Address->country = $data['country_iso_3'];
      if ($this->notification == 1 || $type == 'sender') {
        if ($data['telephone'] != '') {
          $Address->phone = $data['telephone'];
        }
        $Address->email = $data['email_address'];
      }
  
      if (isset($data['packstation']) && $data['packstation'] === true) {
        $Packstation = new stdClass();
        $Packstation->name = (($Address->name2 != '') ? $Address->name2 : $Address->name1);
        $Packstation->lockerID = preg_replace('/[^0-9]/', '', $data['street_address']);
        $Packstation->postNumber = $data['postnumber'];
        $Packstation->postalCode = $data['postcode'];
        $Packstation->city = $data['city'];
        $Packstation->country = $data['country_iso_3'];
        if ($this->notification == 1) {
          $Packstation->email = $data['email_address'];
        }
      }

      if (isset($data['postfiliale']) && $data['postfiliale'] === true) {
        $Postfiliale = new stdClass();
        $Postfiliale->name = (($Address->name2 != '') ? $Address->name2 : $Address->name1);
        $Postfiliale->retailID = preg_replace('/[^0-9]/', '', $data['street_address']);
        $Postfiliale->postNumber = $data['postnumber'];
        $Postfiliale->postalCode = $data['postcode'];
        $Postfiliale->city = $data['city'];
        $Postfiliale->country = $data['country_iso_3'];
        if ($this->notification == 1) {
          $Postfiliale->email = $data['email_address'];
        }
      }
      
      switch ($type) {
        case 'sender':
          $shipping_details = $Address;
          break;
    
        case 'receiver':         
          if (isset($Packstation) && is_object($Packstation)) {
            $shipping_details = $Packstation;
          } elseif (isset($Postfiliale) && is_object($Postfiliale)) {
            $shipping_details = $Postfiliale;
          } else {
            $shipping_details = $Address;
          }
          break;
      }
  
      return $shipping_details;
    }


    private function getReference() {
      $reference = MODULE_DHL_BUSINESS_PREFIX.$this->data['orders_id'];
      $length = mb_strlen($reference);
      if ($length < 8) {
        $reference = MODULE_DHL_BUSINESS_PREFIX.str_pad($this->data['orders_id'], (8 - mb_strlen(MODULE_DHL_BUSINESS_PREFIX)), '0', STR_PAD_LEFT);
      }
      
      return $reference;
    }
    
    
    private function buildExportDocument() {
      $ExportDocument = new stdClass();
      $ExportDocument->exportType = 'COMMERCIAL_GOODS';
//       $ExportDocument->shipperCustomsRef = '';
//       $ExportDocument->consigneeCustomsRef = '';
//       $ExportDocument->invoiceNo = '';
//       $ExportDocument->permitNo = '';
//       $ExportDocument->attestationNo = '';
      $ExportDocument->postalCharges = array(
        'currency' => $this->data['currency'],
        'value' => (double)sprintf("%01.2f", $this->order->info['pp_shipping'] + $this->order->info['pp_fee']),
      );
//       if ($this->mrn != '') {
//         $ExportDocument->MRN = $this->mrn;
//       }
      
      $ExportDocument->items = array();
      $this->order->products = $this->encode_request($this->order->products);
      for ($i=0, $n=count($this->order->products); $i<$n; $i++) {
        if (isset($this->order->products[$i]['origin']) && $this->order->products[$i]['origin'] != '') {
          $this->order->products[$i]['origin'] = $this->get_country_iso_3($this->order->products[$i]['origin']);
        }
        $ExportDocument->items[$i] = new stdClass();
        $ExportDocument->items[$i]->itemDescription = ((isset($this->order->products[$i]['tariff_title']) && $this->order->products[$i]['tariff_title'] != '') ? $this->order->products[$i]['tariff_title'] : $this->order->products[$i]['name']);
        $ExportDocument->items[$i]->countryOfOrigin = ((isset($this->order->products[$i]['origin']) && $this->order->products[$i]['origin'] != '') ? $this->order->products[$i]['origin'] : $this->info['country_iso_3']);
        $ExportDocument->items[$i]->hsCode = ((isset($this->order->products[$i]['tariff']) && $this->order->products[$i]['tariff'] != '') ? $this->order->products[$i]['tariff'] : '');
        $ExportDocument->items[$i]->packagedQuantity = (double)$this->order->products[$i]['quantity'];
        $ExportDocument->items[$i]->itemWeight = array(
          'uom' => 'kg',
          'value' => (double)sprintf("%01.3f", $this->order->products[$i]['weight'] + (($this->order->products[$i]['weight'] == 0) ? (double)MODULE_DHL_BUSINESS_WEIGHT_CN23 : 0)),
        );
        $ExportDocument->items[$i]->itemValue = array(
          'currency' => $this->data['currency'],
          'value' => (double)sprintf("%01.2f", $this->order->products[$i]['price']),
        );
      }
      
      return $ExportDocument;
    }


    public function calculate_weight($order_id) {
      if (!isset($this->order)) {
        $this->order = new order($order_id);
      }
            
      $weight = 0;
      for ($i = 0, $n = count($this->order->products); $i < $n; $i++) {
        $weight += ($this->order->products[$i]['qty'] * $this->order->products[$i]['weight']);
      }
      
      if ($weight > 0) {
        if ((double)SHIPPING_BOX_WEIGHT >= ($weight * (double)SHIPPING_BOX_PADDING / 100)) {
          $weight = $weight + (double)SHIPPING_BOX_WEIGHT;
        } else {
          $weight = $weight + ($weight * (double)SHIPPING_BOX_PADDING / 100);
        }
      } else {
        $weight = (double)SHIPPING_BOX_WEIGHT;
      }
      
      if ($weight == 0) {
        $weight = 1;
      }
    
      return $weight;
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
