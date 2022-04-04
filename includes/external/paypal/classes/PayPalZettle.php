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
  
    private $access_token;
    private $organizationUuid;
    private $locations = array();

    public $account = array();
    public $error = array();
    public $client_id;

    function __construct() {    
      $this->LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_zettle_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', 'zettle', 'debug');

      $this->client = new \GuzzleHttp\Client();

      $this->client_id = '91e9a115-8c82-49de-9ce6-8c04605e8c62';
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
        $this->access_token = $response['access_token'];
      }
    
      return $this->access_token;
    }


    function getAccountInfo() {
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token
      );
    
      $response = $this->call(self::URL_SECURE.'/api/resources/organizations/self', 'GET', array(), $headers);
      
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
      
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );
        
      $inventory = $this->getInventoryLocations($this->locations['STORE'], $uuid);

      if ($inventory['status'] == '404') {
        $this->setInventory($uuid);
        $this->setInventoryProducts($uuid);
        $inventory = $this->getInventoryLocations($this->locations['STORE'], $uuid);
      }
    
      if (isset($inventory['variants']) && count($inventory['variants']) > 0) {
        $quantity = $products['products_quantity'] - $inventory['variants'][0]['balance'];
      } else {
        $product = $this->getProduct($uuid);
      
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
      
      //$data['uuid'] = '5aaf99a4-47b4-11ec-bfc2-dfdd56f76d84';
      //$data['variants'][0]['uuid'] = '5aaf9a4e-47b4-11ec-a7af-898d3ba95def';
      
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
      if ($products_image != ''
          && is_file(DIR_FS_CATALOG_THUMBNAIL_IMAGES.$products_image)
          )
      {
        $image = pathinfo($products_image);

        $headers = array(
          'Authorization' => 'Bearer '.$this->access_token,
        );
        $body = array(
          'json' =>  array(
            'imageFormat' => str_replace('JPG', 'JPEG', strtoupper($image['extension'])),
            'imageUrl' => xtc_href_link_from_admin(ltrim(DIR_WS_CATALOG_THUMBNAIL_IMAGES, '/').$products_image, '', 'SSL', false),
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
              'amount' => $products['products_price'],
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
  

    function call($url, $method, $body = array(), $headers = array()) {
      $headers['X-iZettle-Application-Id'] = $this->client_id;
    
      try {
        $response = $this->client->request($method, $url, array_merge(array('headers' => $headers), $body));
        if (is_object($response)) {
          $headers = $response->getHeaders();
          $status = $response->getStatusCode();
          $reason = $response->getReasonPhrase();

          $response = $response->getBody()->getContents();
          $response = json_decode($response, true);
          $response['headers'] = $headers;
          $response['status'] = $status;
          $response['reason'] = $reason;
        }
        return $response;
      } catch (Exception $ex) {
        $response = array(
          'headers' => $ex->getResponse()->getHeaders(),
          'status' => $ex->getResponse()->getStatusCode(),
          'reason' => $ex->getResponse()->getReasonPhrase(),
          'error' => json_decode($ex->getResponse()->getBody(), true),
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
