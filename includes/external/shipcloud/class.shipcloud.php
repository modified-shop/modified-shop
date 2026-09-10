<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License 
  --------------------------------------------------------------*/

// include needed function
require_once(DIR_FS_INC.'xtc_get_countries_with_iso_codes.inc.php');
require_once(DIR_FS_INC.'xtc_get_countries.inc.php');
require_once(DIR_FS_INC.'get_external_content.inc.php');

// include needed classes
require_once(DIR_WS_CLASSES.'order.php');
require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');

class shipcloud {
  
  const SC_URL_LABEL = 'https://api.shipcloud.io/v1/shipments/';
  const SC_URL_QUOTES = 'https://api.shipcloud.io/v1/shipment_quotes/';
  const SC_URL_PICKUP = 'https://api.shipcloud.io/v1/pickup_requests/';
  const SC_URL_CARRIERS = 'api.shipcloud.io/v1/carriers/';
  
  
  function __construct($oID = '') {
    if ($oID != '') {
      $this->order = new order($oID);
    }
    $this->LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_shipcloud_%s_%s.log', 'shipcloud', ((defined('MODULE_SHIPCLOUD_LOG') && MODULE_SHIPCLOUD_LOG == 'True') ? 'debug' : 'info'));
  }
  
  
  public function delete_label($tracking_id) {
    $sc_id_query = xtc_db_query("SELECT sc_id
                                   FROM ".TABLE_ORDERS_TRACKING."
                                  WHERE tracking_id = '".(int)$tracking_id."'");
    if (xtc_db_num_rows($sc_id_query) == 1) {
      $sc_id = xtc_db_fetch_array($sc_id_query);      
      $this->do_request('', self::SC_URL_LABEL.$sc_id['sc_id'], 'DELETE');
    }  
  }
  
  
  public function create_label($params) {
    global $messageStack;
    
    // parse params
    $this->carrier_name = $params['carrier_id'];
    $dimension_array = explode(',', $params['parcel']);
    list($this->length, $this->width, $this->height) = $dimension_array;
    $this->description_1 = $params['description_1'];
    $this->description_2 = $params['description_2'];
    $this->weight = $params['weight'];
    $this->service = $params['service'];
    $this->type = $params['type'];
    $this->insurance = ((isset($params['insurance']) && $params['insurance'] == '1' && $this->order->info['pp_total'] > '500') ? true : false);
    
    $this->carrier = $this->check_carrier();
    
    if ($this->carrier !== false) {
      $request_array = array(
        'carrier'               => $this->carrier,
        'to'                    => $this->receiver_data(),
        'package'               => $this->package_data(),
        'reference_number'      => $this->order->info['orders_id'],
        'create_shipping_label' => 'true',
        'service'               => $this->service,
        'description'           => $this->description_2,
        'additional_services'   => array(),
      );
            
      if ($request_array['description'] == '') {
        unset($request_array['description']);
      }
      
      $sender_data = $this->sender_data();
      if ($sender_data != '') {
        $request_array['from'] = $sender_data;
      }
      
      $customs_data = $this->customs_data();
      if ($customs_data !== false) {
        $request_array['customs_declaration'] = $customs_data;
      }

      if (strpos($this->carrier, 'dhl') !== false
          || $this->carrier == 'dpd'
          )
      {
        $request_array['additional_services'][] = array(
          'name' => 'advance_notice',
          'properties' => array(
            'email' => $this->order->customer['email_address'],
            'language' => (($this->order->info['language'] == 'german') ? 'de' : 'en'),
          ),
        );
      }
      
      if ($this->order->info['payment_method'] == 'cod') {
        $bank_data = $this->bank_data();
        if ($bank_data != '') {
          $request_array['additional_services'][] = array(
            'name' => 'cash_on_delivery',
            'properties' => $bank_data,
          );
        }
      }
      
      if (MODULE_SHIPCLOUD_EMAIL == 'True' && MODULE_SHIPCLOUD_EMAIL_TYPE == 'shipcloud') {
        $request_array['notification_email'] = $this->order->customer['email_address'];
      }
      
      if ($request_array['service'] == 'returns') {
        $from = $request_array['to'];
        $to = $request_array['from'];
        $request_array['to'] = $to;
        $request_array['from'] = $from;
        
        if (MODULE_SHIPCLOUD_EMAIL == 'True' && MODULE_SHIPCLOUD_EMAIL_TYPE == 'shipcloud') {
          $request_array['notification_email'] = STORE_OWNER_EMAIL_ADDRESS;
        }
        
        for ($i=0, $n=count($request_array['additional_services']); $i<$n; $i++) {
          if ($request_array['additional_services'][$i]['name'] == 'advance_notice') {
            $request_array['additional_services'][$i]['properties']['email'] = STORE_OWNER_EMAIL_ADDRESS;
          }
          if ($request_array['additional_services'][$i]['name'] == 'cash_on_delivery') {
            unset($request_array['additional_services'][$i]);
          }          
        }
      }
      
      $request_array = $this->encode_request($request_array);
      $this->LoggingManager->log('DEBUG', 'create_label', array('exception' => $request_array));
            
      if (!isset($params['quote'])) {
        $request = $this->do_request(json_encode($request_array));
        if (is_array($request) && count($request) > 0) {
          $messageStack->add_session(TEXT_LABEL_CREATED, 'success');
          $this->save_label($request);
        }
      } else {
        unset($request_array['reference_number']);
        unset($request_array['create_shipping_label']);
        
        unset($request_array['to']['first_name']);
        unset($request_array['to']['last_name']);
        unset($request_array['to']['company']);
        unset($request_array['to']['phone']);
        unset($request_array['to']['state']);

        unset($request_array['from']['first_name']);
        unset($request_array['from']['last_name']);
        unset($request_array['from']['company']);
        unset($request_array['from']['phone']);
        unset($request_array['from']['state']);

        unset($request_array['description']);
        unset($request_array['package']['description']);
        unset($request_array['customs_declaration']);
        
        $request = $this->do_request(json_encode($request_array), self::SC_URL_QUOTES);
        if (is_array($request) && isset($request['shipment_quote'])) {
          $messageStack->add_session(CFG_TXT_PRICE.': &euro; '.number_format($request['shipment_quote']['price'], 2, ',', '.'), 'success');
        }
      }
    } else {
      $messageStack->add_session(TEXT_CARRIER_ERROR, 'warning');
    }
  }


  private function save_label($request) {    
		$sql_data_array = array('orders_id' => $this->order->info['order_id'],
		                        'carrier_id' => $this->carrier_id,
		                        'parcel_id' => $request['carrier_tracking_no'],
		                        'date_added' => 'now()',
		                        'external' => '1',
		                        'sc_label_url' => $request['label_url'],
		                        'sc_id' => $request['id'],
		                        'sc_date_added' => 'now()',
		                        );
				
		xtc_db_perform(TABLE_ORDERS_TRACKING,$sql_data_array);
  }
  
  
  public function check_carrier($check = true) { 
    $request = $this->get_carriers(true);

    if (is_array($request) && count($request) > 0) {
      if ($check === true) {
        foreach($request as $carrier) {
          if ($carrier['name'] == $this->carrier_name) {
            $check_carrier_query = xtc_db_query("SELECT *
                                                   FROM ".TABLE_CARRIERS." 
                                                  WHERE (LOWER(carrier_name) = '".xtc_db_input($carrier['display_name'])."'
                                                         OR LOWER(carrier_name) = '".xtc_db_input($carrier['name'])."')");
            $check_carrier = xtc_db_fetch_array($check_carrier_query);
            $this->carrier_id = $check_carrier['carrier_id'];
            
            return $carrier['name'];
          }
        }
      } else {
        return $request;
      }
    }
    
    return false;
  }
  
  
  private function receiver_data() {
    $street_address = $this->parse_street_address($this->order->delivery['street_address']);
    
    $receiver_data = array(
      'first_name'  => $this->order->delivery['firstname'],
      'last_name'   => $this->order->delivery['lastname'],
      'company'     => ((strtolower($this->carrier) == 'dhl') ? substr($this->order->delivery['company'], 0, 30) : $this->order->delivery['company']),
      'street'      => $street_address['street_name'],
      'street_no'   => $street_address['street_number'],
      'care_of'     => $this->order->delivery['suburb'],
      'zip_code'    => $this->order->delivery['postcode'],
      'city'        => $this->order->delivery['city'],
      'state'       => $this->order->delivery['state'],
      'country'     => $this->order->delivery['country_iso_2'],
      'phone'       => $this->order->customer['telephone'],
    );
    
    return $receiver_data;
  }


  private function sender_data() {
    if (MODULE_SHIPCLOUD_FIRSTNAME != ''
        && MODULE_SHIPCLOUD_LASTNAME != ''
        && MODULE_SHIPCLOUD_COMPANY != ''
        && MODULE_SHIPCLOUD_ADDRESS != ''
        && MODULE_SHIPCLOUD_POSTCODE != ''
        && MODULE_SHIPCLOUD_CITY != ''
        && MODULE_SHIPCLOUD_TELEPHONE != ''
        ) 
    {
      $country = xtc_get_countries_with_iso_codes(STORE_COUNTRY);
      $street_address = $this->parse_street_address(MODULE_SHIPCLOUD_ADDRESS);
        
      $sender_data = array(
        'first_name'  => MODULE_SHIPCLOUD_FIRSTNAME,
        'last_name'   => MODULE_SHIPCLOUD_LASTNAME,
        'company'     => ((strtolower($this->carrier) == 'dhl') ? substr(MODULE_SHIPCLOUD_COMPANY, 0, 30) : MODULE_SHIPCLOUD_COMPANY),
        'street'      => $street_address['street_name'],
        'street_no'   => $street_address['street_number'],
        'zip_code'    => MODULE_SHIPCLOUD_POSTCODE,
        'city'        => MODULE_SHIPCLOUD_CITY,
        'country'     => $country['countries_iso_code_2'],
        'phone'       => MODULE_SHIPCLOUD_TELEPHONE,
      );
    
      return $sender_data;
    }
  }


  private function bank_data() {
    if (MODULE_SHIPCLOUD_BANK_HOLDER != ''
        && MODULE_SHIPCLOUD_BANK_NAME != ''
        && MODULE_SHIPCLOUD_ACCOUNT_IBAN != ''
        && MODULE_SHIPCLOUD_ACCOUNT_BIC != ''
        ) 
    {        
      $bank_data = array(
        'amount'              => $this->order->info['pp_total'],
        'currency'            => $this->order->info['currency'],
        'bank_account_holder' => MODULE_SHIPCLOUD_BANK_HOLDER,
        'bank_name'           => MODULE_SHIPCLOUD_BANK_NAME,
        'bank_account_number' => MODULE_SHIPCLOUD_ACCOUNT_IBAN,
        'bank_code'           => MODULE_SHIPCLOUD_ACCOUNT_BIC,
      );
    
      return $bank_data;
    }
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

    return array('street_name' => trim(str_replace(trim($addr[0]), '', $street_address), ', '),
                 'street_number' => trim($addr[0]),
                 );
  }

  
  private function customs_data() {
    if (isset($this->customs_declaration)) {
      return $this->customs_declaration;
    }

    $this->customs_declaration = false;
    if ($this->customs_required() === false) {
      return $this->customs_declaration;
    }

    $country = xtc_get_countries_with_iso_codes(STORE_COUNTRY);
    // the request carries three decimals, so a lighter fallback would be declared as no weight at all
    $default_weight = (float)sprintf("%01.3f", ((defined('MODULE_SHIPCLOUD_WEIGHT_CN23')) ? (float)MODULE_SHIPCLOUD_WEIGHT_CN23 : 0.1));
    if ($default_weight <= 0) {
      $default_weight = 0.001;
    }
    $currency = $this->customs_currency();

    // shipcloud declares this as the carriage charge, DHL demands it as soon as the customer pays it
    $additional_fees = 0;
    if ($this->service != 'returns') {
      $additional_fees = (float)sprintf("%01.2f", ((float)$this->order->info['pp_shipping'] + (float)$this->order->info['pp_fee']) * $currency['rate']);
    }

    $items = array();
    $contents = array();
    $total_value_amount = 0;
    for ($i = 0, $n = count($this->order->products); $i < $n; $i++) {
      $quantity = (int)$this->order->products[$i]['qty'];
      if ($quantity < 1) {
        continue;
      }

      $description = ((isset($this->order->products[$i]['tariff_title']) && $this->order->products[$i]['tariff_title'] != '') ? $this->order->products[$i]['tariff_title'] : $this->order->products[$i]['name']);
      $value_amount = (float)sprintf("%01.2f", $this->order->products[$i]['price'] * $currency['rate']);
      $net_weight = (float)sprintf("%01.3f", $this->order->products[$i]['weight']);
      if ($net_weight <= 0) {
        $net_weight = $default_weight;
      }

      $item = array(
        'origin_country' => ((isset($this->order->products[$i]['origin']) && $this->order->products[$i]['origin'] != '') ? $this->order->products[$i]['origin'] : $country['countries_iso_code_2']),
        'description'    => $description,
        'quantity'       => $quantity,
        'value_amount'   => $value_amount,
        'net_weight'     => $net_weight,
      );

      if (isset($this->order->products[$i]['tariff']) && $this->order->products[$i]['tariff'] != '') {
        $item['hs_tariff_number'] = $this->order->products[$i]['tariff'];
      }

      $items[] = $item;
      $contents[] = $description;
      $total_value_amount += $value_amount * $quantity;
    }

    if (count($items) < 1) {
      return $this->customs_declaration;
    }

    $this->customs_declaration = array(
      // a return carries the goods back, DHL rejects commercial_goods on returns from Great Britain
      'contents_type'        => (($this->service == 'returns') ? 'returned_goods' : 'commercial_goods'),
      'contents_explanation' => mb_substr(implode(', ', $contents), 0, 256),
      'currency'             => $currency['code'],
      'additional_fees'      => $additional_fees,
      'total_value_amount'   => (float)sprintf("%01.2f", $total_value_amount),
      'posting_date'         => date('Y-m-d'),
      'items'                => $items,
    );

    return $this->customs_declaration;
  }


  private function customs_currency() {
    $currency = array('code' => $this->order->info['currency'], 'rate' => 1);

    // DHL accepts the export declaration in EUR only
    if (strpos($this->carrier, 'dhl') === false || $currency['code'] == 'EUR') {
      return $currency;
    }

    $rate = 0;
    $currency_query = xtc_db_query("SELECT value
                                      FROM ".TABLE_CURRENCIES."
                                     WHERE code = 'EUR'");
    if (xtc_db_num_rows($currency_query) == 1) {
      $currency_data = xtc_db_fetch_array($currency_query);
      $rate = (float)$currency_data['value'];
    }

    $currency_value = (float)$this->order->info['currency_value'];
    if ($rate <= 0 || $currency_value <= 0) {
      $this->LoggingManager->log('WARNING', 'customs_currency', array('exception' => 'no EUR rate to convert '.$currency['code']));
      return $currency;
    }

    $currency['code'] = 'EUR';
    $currency['rate'] = $rate / $currency_value;

    return $currency;
  }


  private function customs_required() {
    $country = xtc_get_countries_with_iso_codes(STORE_COUNTRY);
    if ($this->order->delivery['country_iso_2'] == $country['countries_iso_code_2']) {
      return false;
    }

    // a destination outside every tax zone of the shop is treated as a third country
    $tax_rate = 0;
    $tax_rates_query = xtc_db_query("SELECT tr.tax_rate
                                       FROM ".TABLE_COUNTRIES." c
                                       JOIN ".TABLE_ZONES_TO_GEO_ZONES." ztgz
                                            ON c.countries_id = ztgz.zone_country_id
                                       JOIN ".TABLE_TAX_RATES." tr
                                            ON tr.tax_zone_id = ztgz.geo_zone_id
                                      WHERE c.countries_iso_code_2 = '".xtc_db_input($this->order->delivery['country_iso_2'])."'");
    while ($tax_rates = xtc_db_fetch_array($tax_rates_query)) {
      $tax_rate += $tax_rates['tax_rate'];
    }

    return (($tax_rate == 0) ? true : false);
  }


  private function package_data() {
    $package_data = array(
      'width'          => (($this->width != '') ? $this->width : '20'),
      'length'         => (($this->length != '') ? $this->length : '20'),
      'height'         => (($this->height != '') ? $this->height : '20'),
      'weight'         => (($this->weight != '') ? (float)str_replace(',', '.', $this->weight) : $this->calculate_weight()),
      'description'    => $this->description_1,
      'type'           => $this->type,
    );

    if ($package_data['description'] == '') {
      unset($package_data['description']);
    }
    
    if ($this->insurance === true) {
      $package_data['declared_value'] = array(
        'amount'   => $this->order->info['pp_total'],
        'currency' => $this->order->info['currency'],
      );
    }

    // a parcel cannot weigh less than the goods declared to customs
    $customs_data = $this->customs_data();
    if ($customs_data !== false) {
      $customs_weight = 0;
      for ($i = 0, $n = count($customs_data['items']); $i < $n; $i++) {
        $customs_weight += $customs_data['items'][$i]['net_weight'] * $customs_data['items'][$i]['quantity'];
      }
      if ($customs_weight > $package_data['weight']) {
        $package_data['weight'] = (float)sprintf("%01.3f", $customs_weight);
      }
    }
    
    return $package_data;
  }
  
  
  private function calculate_weight() {    
    $weight = (float) SHIPPING_BOX_WEIGHT;
    for ($i = 0, $n = count($this->order->products); $i < $n; $i++) {
      $product_query = xtc_db_query("SELECT products_weight 
                                       FROM ".TABLE_PRODUCTS." 
                                      WHERE products_id = '".$this->order->products[$i]['id']."'");
      if (xtc_db_num_rows($product_query) > 0) {
        $product = xtc_db_fetch_array($product_query);
        $weight += ($this->order->products[$i]['qty'] * $product['products_weight']);
      }
      if (isset($this->order->products[$i]['attributes']) && sizeof($this->order->products[$i]['attributes']) > 0) {
        for ($j = 0, $k = sizeof($this->order->products[$i]['attributes']); $j < $k; $j ++) {
          $attributes_query = xtc_db_query("SELECT options_values_weight,
                                                   weight_prefix
                                              FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                             WHERE options_id = '".$this->order->products[$i]['attributes'][$j]['orders_products_options_id']."'
                                               AND options_values_id = '".$this->order->products[$i]['attributes'][$j]['orders_products_options_values_id']."'
                                               AND products_id = '".$this->order->products[$i]['attributes'][$j]['orders_products_id']."'");
          if (xtc_db_num_rows($attributes_query) > 0) {
            $attributes = xtc_db_fetch_array($attributes_query);
            switch($attributes['weight_prefix']){
              case '+':
                $weight += ($this->order->products[$i]['qty'] * $attributes['options_values_weight']);
                break;
              case '-':
                $weight -= ($this->order->products[$i]['qty'] * $attributes['options_values_weight']);
                break;
            }
          }
        }
      }
    }
  
    if ($weight <= 0) {
      $weight = 1;
    }
  
    return $weight;
  }


  public function pickup($params) { 
    global $messageStack;
    
    $request_array = array(
      'carrier' => $params['carrier'],
      'pickup_time' => array(
        'earliest' => date('c', strtotime($params['earliest'])),
        'latest' => date('c', strtotime($params['latest'])),
      ),
    );
    
    if (isset($params['sc_'.$params['carrier']])) {
    	$request_array['shipments'] = array();
    	foreach ($params['sc_'.$params['carrier']] as $sc_id) {
    		$request_array['shipments'][] = array('id' =>$sc_id);
    	}
    }
      
    $request_array = $this->encode_request($request_array);
    $this->LoggingManager->log('DEBUG', 'pickup', array('exception' => $request_array));
        
    $request = $this->do_request(json_encode($request_array), self::SC_URL_PICKUP);
        
    if (is_array($request)) {
    	for ($i = 0, $n=count($request['shipments']); $i<$n; $i++) {
    		xtc_db_query("UPDATE ".TABLE_ORDERS_TRACKING." SET sc_date_pickup = NOW() WHERE sc_id = '".$request['shipments'][$i]['id']."'");
    	}
    }
  }

  
  public function get_carriers($database = false) {
    if (!is_file(SQL_CACHEDIR.'shipcloud.txt') 
        || time() - filemtime(SQL_CACHEDIR.'shipcloud.txt') > 86400
        )
    {
      $request = get_external_content('https://'.MODULE_SHIPCLOUD_API.'@'.self::SC_URL_CARRIERS, 3, false);
      file_put_contents(SQL_CACHEDIR.'shipcloud.txt', $request, LOCK_EX);
    } else {
      $request = file_get_contents(SQL_CACHEDIR.'shipcloud.txt');
    }
    $request = json_decode($request, true);
    
    if ($database === true) {
      foreach ($request as $data) {
        $check_carrier_query = xtc_db_query("SELECT *
                                               FROM ".TABLE_CARRIERS." 
                                              WHERE (LOWER(carrier_name) = '".xtc_db_input($data['display_name'])."'
                                                     OR LOWER(carrier_name) = '".xtc_db_input($data['name'])."')");
        if (xtc_db_num_rows($check_carrier_query) < 1) {
          $sql_data_array = array(
            'carrier_name' => $data['display_name'],
            'carrier_date_added' => 'now()',
          );
          xtc_db_perform(TABLE_CARRIERS, $sql_data_array);
        }
      }
    }
    return $request;
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


  private function do_request($data, $url = '', $request = 'POST') {
    global $messageStack;
    
    if ($url == '') {
      $url = self::SC_URL_LABEL;
    }
    
    $headers = array('Accept: application/json',
                     'Content-Type: application/json',
                     'Affiliate-ID: plugin.modified.zW7tF8ek'
                     );

    $ch = curl_init();
  
    // set options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERPWD, MODULE_SHIPCLOUD_API);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);
    if ($data != '') {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    $response = curl_exec($ch);
        
    if (curl_errno($ch)) {
      $this->LoggingManager->log('INFO', curl_errno($ch), array('exception' => curl_error($ch)));
    } elseif (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '204' && $request == 'DELETE') {
      $messageStack->add_session(TEXT_DELETE_SHIPMENT_SUCCESS, 'success');
    } elseif (curl_getinfo($ch, CURLINFO_HTTP_CODE) != '200') {
      $response = json_decode($response, true);
      if (isset($response['errors']) && is_array($response['errors'])) {
        foreach($response['errors'] as $error) {
          $messageStack->add_session($error, 'warning');
        }
      }
      $this->LoggingManager->log('INFO', 'do_request', array('exception' => $response));
    } else {
      return json_decode($response, true);
    }
  }

}
?>