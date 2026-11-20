<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  // database tables
  defined('TABLE_PAYPAL_ZETTLE_TO_PRODUCTS') OR define('TABLE_PAYPAL_ZETTLE_TO_PRODUCTS', 'paypal_zettle_to_products');
  defined('TABLE_PAYPAL_ZETTLE_IMPORT') OR define('TABLE_PAYPAL_ZETTLE_IMPORT', 'paypal_zettle_import');
  defined('TABLE_PAYPAL_ZETTLE_EVENTS') OR define('TABLE_PAYPAL_ZETTLE_EVENTS', 'paypal_zettle_events');


  // include needed classes
  require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');


  //include needed functions
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');

  require_once(DIR_FS_INC.'xtc_rand.inc.php');
  require_once(DIR_FS_INC.'xtc_href_link_from_admin.inc.php');


  class PayPalZettle {

    const URL_AUTH = 'https://oauth.izettle.com';
    const URL_SECURE = 'https://secure.izettle.com';
    const URL_PRODUCTS = 'https://products.izettle.com';
    const URL_IMAGE = 'https://image.izettle.com';
    const URL_INVENTORY = 'https://inventory.izettle.com';
    const URL_SUBSCRIPTIONS = 'https://pusher.izettle.com';
    const CLIENT_ID = '91e9a115-8c82-49de-9ce6-8c04605e8c62';
  
    private $access_token;
    private $organizationUuid;
    private $locations = array();

    public $account = array();
    public $error = array();
    public $api_key;
    public $client;
    public $client_id;
    public $LoggingManager;
    public $zettle_version = '1.11';
    // true when the last answer was a JSON list
    public $response_list = false;
    
    function __construct($timeout = 20) {
      $this->LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_zettle_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', 'zettle', 'debug');

      $this->client = new \GuzzleHttp\Client(array('connect_timeout' => min(5, (int)$timeout), 'timeout' => (int)$timeout));

      $this->client_id = self::CLIENT_ID;
      $this->api_key = defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY') ? MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY : '';
      $this->organizationUuid = defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_ORGANIZATION') ? MODULE_CATEGORIES_ZETTLE_CATEGORIES_ORGANIZATION : '';
    
      if ($this->api_key != '') {
        $this->getAccessToken();
      }
    }


    function getAccessToken() {
      $headers = array(
        'Content-Type' => 'application/x-www-form-urlencoded'
      );
      $body = array(
        'form_params' => array(
          'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
          'assertion' => $this->api_key,
          'client_id' => $this->client_id,
        )
      );
    
      if ($this->access_token == '') {
        $response = $this->call(self::URL_AUTH.'/token', 'POST', $body, $headers);
        if (isset($response['error']) && defined('RUN_MODE_ADMIN')) {
          
        }
        $this->access_token = (isset($response['access_token'])) ? $response['access_token'] : '';
      }
    
      return $this->access_token;
    }


    function getAccountInfo() {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
    
      $response = $this->call(self::URL_SECURE.'/api/resources/organizations/self', 'GET', array(), $headers);
      if (isset($response['error'])) {
        return $response;
      }
      
      $this->account = array(
        'name' => $response['name'],
        'receiptName' => $response['receiptName'],
        'contactEmail' => $response['contactEmail'],
        'webSite' => $response['webSite'],
        'profileImageUrl' => $response['profileImageUrl'],
      );
      
      $this->organizationUuid = $response['uuid'];
    
      return $response;
    }


    function getLocations() {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );
    
      if (count($this->locations) < 1) {
        $response = $this->call(self::URL_INVENTORY.sprintf('/organizations/%s/locations', $this->organizationUuid), 'GET', array(), $headers);
    
        foreach ($response as $inventory) {
          if (isset($inventory['uuid'])) {
            $this->locations[$inventory['type']] = $inventory['uuid'];
          }
        }
      }
    
      return $this->locations;
    }


    function getInventoryLocations($locationUuid, $productUuid) {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );
        
      $response = $this->call(self::URL_INVENTORY.sprintf('/organizations/%s/inventory/locations/%s/products/%s', $this->organizationUuid, $locationUuid, $productUuid), 'GET', array(), $headers);
      return $response;
    }
  

    function getInventory($inventory) {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );
      $body = array(
        'query' => array(
          'type' => strtoupper($inventory)
        )
      );
        
      $response = $this->call(self::URL_INVENTORY.sprintf('/organizations/%s/inventory/locations', $this->organizationUuid), 'GET', $body, $headers);
      return $response;
    }

    function setInventory($uuid) {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
      $body = array(
        'json' => array(
          'productUuid' => $uuid
        )
      );
   
      $response = $this->call(self::URL_INVENTORY.sprintf('/organizations/%s/inventory', $this->organizationUuid), 'POST', $body, $headers);
      return $response;
    }


    function deleteInventory($uuid) {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
   
      $response = $this->call(self::URL_INVENTORY.sprintf('/organizations/%s/inventory/products/%s', $this->organizationUuid, $uuid), 'DELETE', array(), $headers);
      return $response;
    }


    function setInventoryProducts($uuid) {
      $this->getLocations();
      
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
    
      foreach ($this->locations as $type => $location) {
        $body = array(
          'json' => array(
            'locationType' => $type,
            'productUuids' => array($uuid)
          )
        );
      
        $response = $this->call(self::URL_INVENTORY.sprintf('/organizations/%s/inventory/products', $this->organizationUuid), 'POST', $body, $headers);
      }
    }


    function updateInventory($uuid, $products) {
      $this->getLocations();
      if (!isset($this->locations['STORE'])) {
        return array(
          'status' => 0,
          'error' => array(
            'error' => 'locations',
            'error_description' => 'Zettle inventory locations could not be read',
          ),
        );
      }
      
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );
        
      $inventory = $this->getInventoryLocations($this->locations['STORE'], $uuid);

      if ($inventory['status'] == '404') {
        $this->setInventory($uuid);
        $this->setInventoryProducts($uuid);
        $inventory = $this->getInventoryLocations($this->locations['STORE'], $uuid);
      }
    
      // a failed read must not count as balance zero, 404 means no balance exists yet
      if (isset($inventory['error']) && $inventory['status'] != '404') {
        return $inventory;
      }

      if (isset($inventory['variants']) && count($inventory['variants']) > 0) {
        $quantity = $products['products_quantity'] - $inventory['variants'][0]['balance'];
      } else {
        $product = $this->getProduct($uuid);
        if (isset($product['error'])) {
          return $product;
        }
      
        $quantity = $products['products_quantity'];
        $inventory['variants'][0]['productUuid'] = $product['uuid'];
        $inventory['variants'][0]['variantUuid'] = $product['variants'][0]['uuid'];
      }

      if ($quantity != 0) {
        $body = array(
          'json' => array(
            'changes' => array(
              array(
                'productUuid' => $inventory['variants'][0]['productUuid'],
                'variantUuid' => $inventory['variants'][0]['variantUuid'],
                'fromLocationUuid' => (($quantity > 0) ? $this->locations['SUPPLIER'] : $this->locations['STORE']),
                'toLocationUuid' => (($quantity > 0) ? $this->locations['STORE'] : $this->locations['BIN']),
                'change' => abs($quantity),
              )
            )
          )
        );
  
        $response = $this->call(self::URL_INVENTORY.sprintf('/organizations/%s/inventory', $this->organizationUuid), 'PUT', $body, $headers);
        return $response;
      }
    }


    function getSubscriptions() {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );

      $response = $this->call(self::URL_SUBSCRIPTIONS.sprintf('/organizations/%s/subscriptions', $this->organizationUuid), 'GET', array(), $headers);
      return $response;
    }
  
  
    function setSubscriptions() {
      $data = $this->get_subscription_scheme();
      $data['uuid'] = $this->generate_uuid();
      
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
      $body = array(
        'json' => $data
      );
    
      $response = $this->call(self::URL_SUBSCRIPTIONS.sprintf('/organizations/%s/subscriptions', $this->organizationUuid), 'POST', $body, $headers);
      return $response;
    }


    function updateSubscriptions($uuid) {
      $data = $this->get_subscription_scheme();
      
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
      $body = array(
        'json' => $data
      );
    
      $response = $this->call(self::URL_SUBSCRIPTIONS.sprintf('/organizations/%s/subscriptions/%s', $this->organizationUuid, $uuid), 'PUT', $body, $headers);
      return $response;
    }
  

    // the subscription list returns the keys Zettle signs its webhooks with: the keys of this shop,
    // an empty text when Zettle has no subscription of this shop, false when the list did not arrive
    function saveSigningKey() {
      $subscriptions = $this->getSubscriptions();
      $scheme = $this->get_subscription_scheme();

      // without the list the stored keys stay as they are
      if (!is_array($subscriptions)
          || isset($subscriptions['error'])
          || !isset($subscriptions['status'])
          || (int)$subscriptions['status'] != 200
          || $this->response_list !== true
          )
      {
        return false;
      }

      $shop_keys = array();
      // the answer also carries headers, status and reason
      foreach ($subscriptions as $subscription) {
        if (!is_array($subscription)
            || !isset($subscription['signingKey'])
            || !is_string($subscription['signingKey'])
            || $subscription['signingKey'] == ''
            || strpos($subscription['signingKey'], ',') !== false
            || !isset($subscription['destination'])
            || $subscription['destination'] != $scheme['destination']
            || !isset($subscription['eventNames'])
            || !is_array($subscription['eventNames'])
            || count($subscription['eventNames']) < 1
            )
        {
          continue;
        }
        // the name of an event is not signed: a subscription that also delivers other events could have one of
        // them sent under a name the webhook handles. A list without names stands for all events
        $shop_events = true;
        foreach ($subscription['eventNames'] as $event_name) {
          if (!is_string($event_name) || !in_array($event_name, $scheme['eventNames'], true)) {
            $shop_events = false;
            break;
          }
        }
        // every subscription that delivers to this shop signs with a key of its own
        if ($shop_events === true) {
          $shop_keys[$subscription['signingKey']] = $subscription['signingKey'];
        }
      }
      // a subscription that is gone or changed loses its key here
      $signing_key = implode(',', $shop_keys);

      $check_query = xtc_db_query("SELECT configuration_key
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key = 'MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY'");
      if (xtc_db_num_rows($check_query) > 0) {
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = '".xtc_db_input($signing_key)."',
                             sort_order = '-1'
                       WHERE configuration_key = 'MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY'");
      } elseif ($signing_key != '') {
        xtc_db_query("INSERT IGNORE INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY', '".xtc_db_input($signing_key)."', '6', '-1', NOW())");
      }

      // the time the keys were read: a valid event has them read again after a day
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".time()."'
                     WHERE configuration_key = 'MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY_TIME'");
      if (xtc_db_affected_rows() < 1) {
        xtc_db_query("INSERT IGNORE INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY_TIME', '".time()."', '6', '-1', NOW())");
      }

      return $signing_key;
    }


    // the lookup is a request to Zettle. Calls to the webhook start one in 15 minutes: at once when no stored key
    // fits, with a fitting key only when the keys were read more than a day ago. An attempt without an answer
    // leaves that time as it is, so the next one follows after 15 minutes
    static function signingKeyLookupAllowed($signed = false) {
      $now = time();
      $check_time = null;
      $key_time = 0;
      $check_query = xtc_db_query("SELECT configuration_key,
                                          configuration_value
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key IN ('MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY_CHECK', 'MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY_TIME')");
      while ($check = xtc_db_fetch_array($check_query)) {
        if ($check['configuration_key'] == 'MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY_CHECK') {
          $check_time = $check['configuration_value'];
        } else {
          $key_time = (int)$check['configuration_value'];
        }
      }
      if ($signed === true && $key_time > ($now - 86400)) {
        return false;
      }

      if ($check_time === null) {
        // with the unique key of the table a second call at the same time adds nothing
        xtc_db_query("INSERT IGNORE INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY_CHECK', '".$now."', '6', '-1', NOW())");
        return (xtc_db_affected_rows() > 0);
      }
      if ((int)$check_time > ($now - 900)) {
        return false;
      }

      // of several calls at the same time only one changes the value
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".$now."'
                     WHERE configuration_key = 'MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY_CHECK'
                       AND configuration_value = '".xtc_db_input($check_time)."'");

      return (xtc_db_affected_rows() > 0);
    }


    static function isValidSignature($request, $signature, $signing_key) {
      if (!isset($request['timestamp'])
          || !is_string($request['timestamp'])
          || !isset($request['payload'])
          || !is_string($request['payload'])
          || !is_string($signature)
          )
      {
        return false;
      }

      // the text exactly as it arrived, a changed timestamp or payload must not pass
      foreach (explode(',', (string)$signing_key) as $key) {
        if ($key != '' && hash_equals(hash_hmac('sha256', $request['timestamp'].'.'.$request['payload'], $key), strtolower($signature))) {
          return true;
        }
      }

      return false;
    }


    // the event name is not part of the signed text: the payload has to have the form Zettle documents for this event
    static function isEventPayload($event_name, $data) {
      if (!is_string($event_name) || !is_array($data)) {
        return false;
      }

      switch ($event_name) {
        case 'InventoryBalanceChanged':
          return (isset($data['balanceBefore'], $data['balanceAfter'])
                  && is_array($data['balanceBefore'])
                  && is_array($data['balanceAfter'])
                  && !isset($data['productUuid'])
                  );

        case 'InventoryTrackingStarted':
          return (isset($data['productUuid'], $data['created'])
                  && is_string($data['productUuid'])
                  && !isset($data['changeInformation'])
                  && !isset($data['balanceBefore'])
                  );

        case 'InventoryTrackingStopped':
          return (isset($data['productUuid'], $data['changeInformation'])
                  && is_string($data['productUuid'])
                  && !isset($data['created'])
                  && !isset($data['balanceBefore'])
                  );

        case 'ProductDeleted':
          return (isset($data['uuid'], $data['variants'])
                  && is_string($data['uuid'])
                  && is_array($data['variants'])
                  && !isset($data['productUuid'])
                  && !isset($data['balanceBefore'])
                  );
      }

      return false;
    }


    function deleteSubscriptions($uuid) {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );

      $response = $this->call(self::URL_SUBSCRIPTIONS.sprintf('/organizations/%s/subscriptions/%s', $this->organizationUuid, $uuid), 'DELETE', array(), $headers);
      return $response;
    }


    function getAllProducts() {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
    
      $response = $this->call(self::URL_PRODUCTS.sprintf('/organizations/%s/products', $this->organizationUuid), 'GET', array(), $headers);
      return $response;
    }


    function getProduct($uuid) {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
    
      $response = $this->call(self::URL_PRODUCTS.sprintf('/organizations/%s/products/%s', $this->organizationUuid, $uuid), 'GET', array(), $headers);
      return $response;
    }


    function insertProduct($products) {
      $data = $this->get_product_scheme($products, true);      
      
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );
      $body = array(
        'json' => $data
      );
        
      $response = $this->call(self::URL_PRODUCTS.sprintf('/organizations/%s/products?returnEntity=true', $this->organizationUuid), 'POST', $body, $headers);
      return $response;
    }


    function updateProduct($uuid, $products) {
      $product = $this->getProduct($uuid);
      if (isset($product['error'])) {
        return $product;
      }
 
      $data = $this->get_product_scheme($products);
      $data['uuid'] = $product['uuid'];
      $data['variants'][0]['uuid'] = $product['variants'][0]['uuid'];
   
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
        'If-Match' => sprintf('"%s"', $product['etag'])
      );
      $body = array(
        'json' => $data
      );
        
      $response = $this->call(self::URL_PRODUCTS.sprintf('/organizations/%s/products/v2/%s', $this->organizationUuid, $uuid), 'PUT', $body, $headers);    
      return $response;
    }


    function deleteProduct($uuid) {  
      $this->setInventory($uuid);
      $this->setInventoryProducts($uuid);
      
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );
      
      $response = $this->call(self::URL_PRODUCTS.sprintf('/organizations/%s/products/%s', $this->organizationUuid, $uuid), 'DELETE', array(), $headers);
      return $response;
    }
  

    function getImport($uuid) {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
    
      $response = $this->call(self::URL_PRODUCTS.sprintf('/organizations/%s/import/status/%s', $this->organizationUuid, $uuid), 'GET', array(), $headers);
      return $response;
    }

    function setImport($data) {      
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );
      $body = array(
        'json' => $data
      );
        
      $response = $this->call(self::URL_PRODUCTS.sprintf('/organizations/%s/import/v2', $this->organizationUuid), 'POST', $body, $headers);
      return $response;
    }


    function getImages() {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
    
      $response = $this->call(self::URL_PRODUCTS.sprintf('/organizations/%s/images', $this->organizationUuid), 'GET', array(), $headers);
      return $response;
    }


    function uploadImage($products_image) {
      $image_path_absolute = defined('DIR_FS_CATALOG_THUMBNAIL_IMAGES') ? DIR_FS_CATALOG_THUMBNAIL_IMAGES : DIR_FS_CATALOG.DIR_WS_THUMBNAIL_IMAGES;
      $image_path_relative = defined('DIR_WS_CATALOG_THUMBNAIL_IMAGES') ? DIR_WS_CATALOG_THUMBNAIL_IMAGES : DIR_WS_THUMBNAIL_IMAGES;
      $xtc_href_link = (defined('RUN_MODE_ADMIN') && function_exists('xtc_href_link_from_admin')) ? 'xtc_href_link_from_admin' : 'xtc_href_link';
      
      if ($products_image != ''
          && is_file($image_path_absolute.$products_image)
          )
      {
        $image = pathinfo($products_image);

        $headers = array(
          'Authorization' => 'Bearer '.$this->access_token,
        );
        $body = array(
          'json' =>  array(
            'imageFormat' => str_replace('JPG', 'JPEG', strtoupper($image['extension'])),
            'imageUrl' => $xtc_href_link(ltrim($image_path_relative, '/').$products_image, '', 'SSL', false),
          )
        );
      
        $response = $this->call(self::URL_IMAGE.sprintf('/v2/images/organizations/%s/products', $this->organizationUuid), 'POST', $body, $headers);
        return $response;
      }
    }
  

    function get_product_scheme($products, $insert = false) {
      $scheme = array(
        'uuid' => (($insert === true) ? $this->generate_uuid() : ''),
        'name' => $products['products_name'],
        'variants' => array(
          array(
            'uuid' => (($insert === true) ? $this->generate_uuid() : ''),
            'sku' => $products['products_model'],
            'barcode' => $products['products_ean'],
            'price' => array(
              'amount' => (int)round($products['products_price']),
              'currencyId' => $products['products_currency'],
            ),
          ),
        ),
        'externalReference' => $products['products_id'],
        'vatPercentage' => round($products['products_tax'], 2),
        'metadata' => array(
          'inPos' => true,
          'source' => array(
            'name' => 'modified eCommerce Shopsystem',
            'external' => true,
          )
        ),
      );
    
      $image = $this->uploadImage($products['products_image']);
      if (is_array($image) && isset($image['imageLookupKey'])) {
        $scheme['imageLookupKeys'][] = $image['imageLookupKey'];
        $scheme['presentation']['imageUrl'] = array_shift($image['imageUrls']);
      }
        
      return $scheme;
    }


    // the API key is a JWT, its client_id can differ from the client id of the module
    static function isOwnClient($client_uuid) {
      if ($client_uuid == '') {
        return false;
      }

      if ($client_uuid == self::CLIENT_ID) {
        return true;
      }

      $api_key = explode('.', defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY') ? MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY : '');
      if (count($api_key) != 3) {
        return false;
      }
      $claims = json_decode(base64_decode(strtr($api_key[1], '-_', '+/')), true);

      return (is_array($claims) && isset($claims['client_id']) && $claims['client_id'] == $client_uuid);
    }


    function get_subscription_scheme() {
      $scheme = array(
        'transportName' => 'WEBHOOK',
        'eventNames' => array(
          'InventoryBalanceChanged',
          'InventoryTrackingStarted',
          'InventoryTrackingStopped',
          'ProductDeleted',
        ),
        'destination' => xtc_href_link_from_admin('api/zettle/webhook.php', '', 'SSL', false),
        'contactEmail' => STORE_OWNER_EMAIL_ADDRESS,
      );
      
      return $scheme;
    }


    function generate_uuid() {
      $time = microtime(false);
      $time = substr($time, 11) . substr($time, 2, 7);
      $time = str_pad(dechex($time + 0x01b21dd213814000), 16, '0', STR_PAD_LEFT);
      $clockSeq = xtc_rand(0, 0x3fff);
      $node = sprintf('%06x%06x',
        xtc_rand(0, 0xffffff) | 0x010000,
        xtc_rand(0, 0xffffff)
      );
    
      return sprintf('%08s-%04s-1%03s-%04x-%012s',
        substr($time, -8),
        substr($time, -12, 4),
        substr($time, -15, 3),
        $clockSeq | 0x8000,
        $node
      );
    }
  

    // no answer, no valid token, changed etag, rate limit or server error
    function isTemporaryError($response) {
      if (!is_array($response) || !isset($response['error'])) {
        return false;
      }

      return (in_array((int)$response['status'], array(0, 401, 403, 408, 412, 429)) || $response['status'] >= 500);
    }


    function call($url, $method, $body = array(), $headers = array()) {
      $headers['X-iZettle-Application-Id'] = $this->client_id;
    
      try {
        $response = $this->client->request($method, $url, array_merge(array('headers' => $headers), $body));
        if (is_object($response)) {
          $headers = $response->getHeaders();
          $status = $response->getStatusCode();
          $reason = $response->getReasonPhrase();

          $response_text = $response->getBody()->getContents();
          $response = json_decode($response_text, true);
          // an answer that is no JSON must not read as an empty list
          $this->response_list = (is_array($response) && substr(ltrim($response_text), 0, 1) == '[');
          $response['headers'] = $headers;
          $response['status'] = $status;
          $response['reason'] = $reason;
        }
        return $response;
      } catch (Exception $ex) {
        $this->response_list = false;
        // connection failures come without a response
        $ex_response = (method_exists($ex, 'getResponse')) ? $ex->getResponse() : null;

        $response = array(
          'headers' => (is_object($ex_response)) ? $ex_response->getHeaders() : array(),
          'status' => (is_object($ex_response)) ? $ex_response->getStatusCode() : 0,
          'reason' => (is_object($ex_response)) ? $ex_response->getReasonPhrase() : $ex->getMessage(),
          'error' => (is_object($ex_response)) ? json_decode($ex_response->getBody(), true) : null,
        );
        if (!is_array($response['error']) || isset($response['error']['developerMessage'])) {
          $response['error'] = array(
            'error' => $response['reason'],
            'error_description' => ((isset($response['error']['developerMessage'])) ? $response['error']['developerMessage'] : $response['reason']),
          );
        }
        $this->error[] = $response['error'];
        $this->LoggingManager->log('DEBUG', 'call', $response['error']);        
        
        return $response;
      }          
    }

  }
