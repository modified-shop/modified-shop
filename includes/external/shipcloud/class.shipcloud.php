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

class shipcloud {
  
  const SC_URL_LABEL = 'https://api.shipcloud.io/v1/shipments/';
  const SC_URL_QUOTES = 'https://api.shipcloud.io/v1/shipment_quotes/';
  const SC_URL_PICKUP = 'https://api.shipcloud.io/v1/pickup_requests/';
  const SC_URL_CARRIERS = 'api.shipcloud.io/v1/carriers/';
  
  
  function __construct($oID = '') {
    if ($oID != '') {
      $this->order = new order($oID);
    }
    $this->log = ((defined('MODULE_SHIPCLOUD_LOG') && MODULE_SHIPCLOUD_LOG == 'True') ? true : true);
    $this->debug = false;
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
    $this->carrier_id = (int)$params['carrier_id'];
    $dimension_array = explode(',', $params['parcel']);
    list($this->length, $this->width, $this->height) = $dimension_array;
    $this->description = $params['description'];
    
    $this->carrier = $this->check_carrier();
    
    if ($this->carrier !== false) {
      $request_array = array(
        'carrier'               => $this->carrier,
        'to'                    => $this->receiver_data(),
        'package'               => $this->package_data(),
        'reference_number'      => $this->order->info['orders_id'],
        'create_shipping_label' => 'true',
        'service'               => $params['service'],
      );
      
      if (in_array($params['service'], array('books', 'letter', 'parcel_letter'))) {
        $request_array['carrier'] = 'dpag';
        $request_array['service'] = 'standard';
        $request_array['package']['type'] = $params['service'];
      }
      
      $sender_data = $this->sender_data();
      if ($sender_data != '') {
        $request_array['from'] = $sender_data;
      }
      
      if ($this->order->info['payment_method'] == 'cod') {
        $bank_data = $this->bank_data();
        if ($bank_data != '') {
          $request_array['additional_services'] = array(
            array(
              'name' => 'cash_on_delivery',
              'properties' => $bank_data,
            ),
          );
        }
      }
      
      if ($this->carrier == 'dpd') {
        if (!isset($request_array['additional_services']) 
            || !is_array($request_array['additional_services'])
            ) 
        {
          $request_array['additional_services'] = array();
        }
        $request_array['additional_services'][] = array(
          'name' => 'advance_notice',
          'properties' => array(
            'email' => $this->order->customer['email_address'],
            'language' => (($this->order->info['language'] == 'german') ? 'de' : 'en'),
          ),
        );
      }
      
      if (MODULE_SHIPCLOUD_EMAIL == 'True' && MODULE_SHIPCLOUD_EMAIL_TYPE == 'shipcloud') {
        $request_array['notification_email'] = $this->order->customer['email_address'];
      }
      
      $request_array = $this->encode_request($request_array);
      $this->logger($request_array);
            
      if (!isset($params['quote'])) {
        $request = $this->do_request(json_encode($request_array));
        if (is_array($request) && count($request) > 0) {
          $messageStack->add_session(TEXT_LABEL_CREATED, 'success');
          $this->logger($request);
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

        unset($request_array['package']['description']);
        
        $request = $this->do_request(json_encode($request_array), self::SC_URL_QUOTES);
        if (is_array($request) && isset($request['shipment_quote'])) {
          $messageStack->add_session(CFG_TXT_PRICE.': &euro; '.number_format($request['shipment_quote']['price'], 2, ',', '.'), 'success');
          $this->logger($request);
        }
      }
    } else {
      $messageStack->add_session(TEXT_CARRIER_ERROR, 'warning');
      $this->logger(TEXT_CARRIER_ERROR);
    }
  }


  private function save_label($request) {    
		$sql_data_array = array('orders_id' => $this->order->info['order_id'],
		                        'carrier_id' => $this->carrier_id,
		                        'parcel_id' => $request['carrier_tracking_no'],
		                        'external' => '1',
		                        'sc_label_url' => $request['label_url'],
		                        'sc_id' => $request['id'],
		                        'sc_date_added' => 'now()',
		                        );
		xtc_db_perform(TABLE_ORDERS_TRACKING,$sql_data_array);
  }
  
  
  public function check_carrier($check = true) { 
    $check_carrier_query = xtc_db_query("SELECT LOWER(carrier_name) as name 
                                            FROM ".TABLE_CARRIERS." 
                                           WHERE carrier_id = '".$this->carrier_id."'");
    $check_carrier = xtc_db_fetch_array($check_carrier_query);

    $request = get_external_content('https://'.MODULE_SHIPCLOUD_API.'@'.self::SC_URL_CARRIERS, 3, false);
    $request = json_decode($request, true);
    
    if (is_array($request) && count($request) > 0) {
      if ($check === true) {
        foreach($request as $carrier) {
          if ($carrier['name'] == $check_carrier['name']) {
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
        'amount' => $this->order->info['pp_total'],
        'currency' => $this->order->info['currency'],
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

  
  private function package_data() {
    $package_data = array(
      'width'          => (($this->width != '') ? $this->width : '20'),
      'length'         => (($this->length != '') ? $this->length : '20'),
      'height'         => (($this->height != '') ? $this->height : '20'),
      'weight'         => $this->calculate_weight(),
      'description'    => $this->description,
      /*
      'declared_value' => array(
        'amount'   => $this->order->info['pp_total'],
        'currency' => $this->order->info['currency']
      )
      */
    );
    
    return $package_data;
  }
  
  
  private function calculate_weight() {    
    $weight = (double) SHIPPING_BOX_WEIGHT;
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
  
    if ($weight == '0') {
      $weight = '1';
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
    $this->logger($request_array);
        
    $request = $this->do_request(json_encode($request_array), self::SC_URL_PICKUP);
        
    if (is_array($request)) {
    	for ($i = 0, $n=count($request['shipments']); $i<$n; $i++) {
    		xtc_db_query("UPDATE ".TABLE_ORDERS_TRACKING." SET sc_date_pickup = NOW() WHERE sc_id = '".$request['shipments'][$i]['id']."'");
    	}
    }
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
      $this->logger(curl_errno($ch), curl_error($ch));
    } elseif (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '204' && $request == 'DELETE') {
      $messageStack->add_session(TEXT_DELETE_SHIPMENT_SUCCESS, 'success');
    } elseif (curl_getinfo($ch, CURLINFO_HTTP_CODE) != '200') {
      $response = json_decode($response, true);
      if (isset($response['errors']) && is_array($response['errors'])) {
        foreach($response['errors'] as $error) {
          $messageStack->add_session($error, 'warning');
        }
      }
      $this->logger($response);
    } else {
      return json_decode($response, true);
    }
  }
  
  
	private function logger($message) {
		if ($this->log === true) {
		  if ($this->debug === true) {
        $this->output($message);
      }
      error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' ' . print_r($message, true) . "\n", 3, DIR_FS_LOG.'mod_shipcloud_' .date('Y-m-d') .'.log');
		}
	}


  public function output($array, $exit = false) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
    
    if ($exit === true) {
      echo 'exit';
      exit();
    }
  }

}
?>