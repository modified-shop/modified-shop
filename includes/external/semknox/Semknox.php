<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  //include needed functions
  require_once(DIR_FS_INC.'xtc_get_vpe_name.inc.php');
  require_once(DIR_FS_INC.'xtc_get_category_path.inc.php');
  require_once(DIR_FS_INC.'get_database_version.inc.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');

  // include needed classes
  require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');
  require_once(DIR_FS_CATALOG.'includes/classes/main.php');
  require_once(DIR_FS_CATALOG.'includes/classes/product.php');
  require_once(DIR_FS_CATALOG.'includes/classes/xtcPrice.php');
  
  defined('FILENAME_PRODUCT_INFO') OR define('FILENAME_PRODUCT_INFO', 'product_info.php');
  defined('FILENAME_POPUP_CONTENT') OR define('FILENAME_POPUP_CONTENT', '../popup_content.php');
  defined('TEXT_LINK_TITLE_INFORMATION') OR define('TEXT_LINK_TITLE_INFORMATION', 'Information');
  
  use GuzzleHttp\Client;
  
  class Semknox {
  
    protected $project_id;
    protected $api_key;
    protected $version = '1.0';
  
    function __construct($language_id) {
      // logger
      $this->logger = new LoggingManager(DIR_FS_LOG.'mod_%s_%s.log', 'info', 'error');
      
      $this->language_id = $language_id;
      $this->api_key = constant('MODULE_SEMKNOX_SYSTEM_API_'.$this->language_id);
      
      $this->main = new main($this->language_id);
      
      $this->customers_status_array = array();
      $this->customers_status_all_array = array();
      $customers_status_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_CUSTOMERS_STATUS."
                                               WHERE language_id = ".$this->language_id);
      while ($customers_status = xtc_db_fetch_array($customers_status_query)) {
        $this->customers_status_all_array[] = $customers_status['customers_status_id'];
        
        $this->customers_status_array[$customers_status['customers_status_id']] = array(
          'id' => $customers_status['customers_status_id'],
          'xtPrice' => new xtcPrice(DEFAULT_CURRENCY, $customers_status['customers_status_id']),
        );
      }
      
      if (defined('RUN_MODE_ADMIN')) {
        $this->dir_images_popup = ltrim(DIR_WS_CATALOG_POPUP_IMAGES, '/');
        $this->dir_images_info = ltrim(DIR_WS_CATALOG_INFO_IMAGES, '/');
        $this->dir_images_thumbnail = ltrim(DIR_WS_CATALOG_THUMBNAIL_IMAGES, '/');        
      } else {
        $this->dir_images_popup = DIR_WS_POPUP_IMAGES;
        $this->dir_images_info = DIR_WS_INFO_IMAGES;
        $this->dir_images_thumbnail = DIR_WS_THUMBNAIL_IMAGES;        
      }

      $db_version = get_database_version();
      
      // client
      $this->client = new Client(
        array(
          'base_uri' => 'https://api-v3.semknox.com/',
          'timeout' => 3,
            'headers' => array(
              'SHOPSYSVER' => 'modified-eCommerce-Shopsoftware-v'.$db_version['plain'],
              'EXTVER' => $this->version,
            ),
        ),
      );  
    }
  

    private function getText($string) {
      $string = strip_tags($string);
      $string = preg_replace ("/\s++/u", ' ', $string);
      $string = trim($string);

      return $string;
    }


    private function convertToString($string) {
      if (is_array($string)) {
        foreach ($string as $key => $value) {
          $string[$key] = $this->convertToString($value);
        }
      } else {
        $string = strval($string);
        return $string;
      }
    
      return $string;  
    }


    private function getVpeData($products, $price) {
      static $vpe_name_array;
      
      if (!isset($vpe_name_array)) {
        $vpe_name_array = array();
      }
      
      if (isset($products['products_vpe_status']) 
          && $products['products_vpe_status'] == 1 
          && $products['products_vpe_value'] != 0.0 
          && $price > 0
          )
      {        
        if (!isset($vpe_name_array[$this->language_id][$products['products_vpe']])) {
          $vpe_name_query = xtc_db_query("SELECT products_vpe_name 
                                            FROM ".TABLE_PRODUCTS_VPE." 
                                           WHERE language_id = '".(int)$this->language_id."' 
                                             AND products_vpe_id = '".(int)$products['products_vpe']."'");
          $vpe_name = xtc_db_fetch_array($vpe_name_query);
          $vpe_name_array[$this->language_id][$products['products_vpe']] = $vpe_name['products_vpe_name'];
        }

        return array(
          'vpe_name' => $vpe_name_array[$this->language_id][$products['products_vpe']],
          'vpe_price' => $price * (1 / $products['products_vpe_value']),
        );
      }
      
      return false;
    }

    
    public function initBatch() {
      try {
        $response = $this->client->post(
          'products/batch/initiate?apiKey='.$this->api_key
        );
        $json = $response->getBody();
        $response = json_decode($json);
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }

    
    public function uploadBatch($products_id_array) {
      $products_array = array();
      foreach ($products_id_array as $products_id) {
        $products_array['products'][] = $this->getProduct($products_id);
      }
      
      try {
        $response = $this->client->post(
          'products/batch/upload?apiKey='.$this->api_key, 
          array(GuzzleHttp\RequestOptions::JSON => $this->convertToString($products_array))
        );
        $json = $response->getBody();
        $response = json_decode($json, true);
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }


    public function startBatch() {
      try {
        $response = $this->client->post(
          'products/batch/start?apiKey='.$this->api_key
        );
        $json = $response->getBody();
        $response = json_decode($json);
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }


    public function sendProduct($products_id) {
      $products_array = array();
      $products_array['products'][] = $this->getProduct($products_id);
      
      try {
        $response = $this->client->post(
          'products?apiKey='.$this->api_key, 
          array(GuzzleHttp\RequestOptions::JSON => $this->convertToString($products_array))
        );
        $json = $response->getBody();
        $response = json_decode($json, true);
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }
  
    
    public function deleteProduct($products_id) {
      $product_data = array(
        'identifiers' => array(
          $products_id
        )
      );
      
      try {
        $response = $this->client->delete(
          'products?apiKey='.$this->api_key, 
          array(GuzzleHttp\RequestOptions::JSON => $this->convertToString($product_data))
        );
        $json = $response->getBody();
        $response = json_decode($json);
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }
  

    public function getTask($taskId) {
      try {
        $response = $this->client->get(
          'tasks/'.$taskId.'?apiKey='.$this->api_key
        );
        $json = $response->getBody();
        $response = json_decode($json);
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }
    
    
    private function getProduct($products_id) {
      global $product, $xtPrice;
      
      $product = new product();
      $xtPrice = new xtcPrice(DEFAULT_CURRENCY, DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
      
      $xtc_href_link = 'xtc_href_link';
      if (defined('RUN_MODE_ADMIN')) {
        $xtc_href_link = 'xtc_catalog_href_link';
      }
                
      $products_query = xtc_db_query("SELECT p.*,
                                             m.*,
                                             pd.*
                                        FROM ".TABLE_PRODUCTS." p
                                        JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                             ON p.products_id = pd.products_id
                                                AND pd.language_id = ".$this->language_id."
                                   LEFT JOIN ".TABLE_MANUFACTURERS." m
                                             ON p.manufacturers_id = m.manufacturers_id
                                       WHERE p.products_id = ".$products_id."
                                         AND p.products_status = 1");
      $products = xtc_db_fetch_array($products_query);
                                   
      $products_array = array(
        'identifier' => $products['products_id'],
        'groupIdentifier' => $products['products_id'],
        'name' => $products['products_name'],
        'productUrl' => $xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$products['products_id'], 'NONSSL', false),
        'categories' => $this->getCategories($products['products_id']),
        'images' => array(),
        'attributes' => $this->getTags($products['products_id']),
      );
    
      if ($products['products_image'] != '') {
        $products_array['images'][] = array(
          'url' => $xtc_href_link($this->dir_images_popup.$products['products_image'], '', 'NONSSL', false),
          'type' => 'LARGE',
        );
        $products_array['images'][] = array(
          'url' => $xtc_href_link($this->dir_images_info.$products['products_image'], '', 'NONSSL', false),
          'type' => 'SMALL',
        );
        $products_array['images'][] = array(
          'url' => $xtc_href_link($this->dir_images_thumbnail.$products['products_image'], '', 'NONSSL', false),
          'type' => 'THUMB',
        );
      }
    
      $products_array['attributes'][] = array(
        'key' => 'quantity',
        'value' => $products['products_quantity'],
          'userGroups' => $this->customers_status_all_array,
      );

      if ($products['products_description'] != '') {
        $products_array['attributes'][] = array(
          'key' => 'description',
          'value' => $this->getText($products['products_description']),
          'userGroups' => $this->customers_status_all_array,
        );
      }

      if ($products['products_short_description'] != '') {
        $products_array['attributes'][] = array(
          'key' => 'description_short',
          'value' => $this->getText($products['products_short_description']),
          'userGroups' => $this->customers_status_all_array,
        );
      }

      if ($products['products_ean'] != '') {
        $products_array['attributes'][] = array(
          'key' => 'ean',
          'value' => $this->getText($products['products_ean']),
          'userGroups' => $this->customers_status_all_array,
        );
      }

      if ($products['products_model'] != '') {
        $products_array['attributes'][] = array(
          'key' => 'model',
          'value' => $this->getText($products['products_model']),
          'userGroups' => $this->customers_status_all_array,
        );
      }

      if ($products['products_manufacturers_model'] != '') {
        $products_array['attributes'][] = array(
          'key' => 'mpn',
          'value' => $this->getText($products['products_manufacturers_model']),
          'userGroups' => $this->customers_status_all_array,
        );
      }

      if ($products['manufacturers_name'] != '') {
        $products_array['attributes'][] = array(
          'key' => 'brand',
          'value' => $this->getText($products['manufacturers_name']),
          'userGroups' => $this->customers_status_all_array,
        );
      }

      if ($products['products_keywords'] != '') {
        $products_array['attributes'][] = array(
          'key' => 'keywords',
          'value' => $this->getText($products['products_keywords']),
          'userGroups' => $this->customers_status_all_array,
        );
      }      
      
      if ((int)$products['products_shippingtime'] > 0 && ACTIVATE_SHIPPING_STATUS == 'true') {
        $shipping_status_array = array(
          'shipping_name' => $this->main->getShippingStatusName($products['products_shippingtime']),
          'shipping_image' => $this->main->getShippingStatusImage($products['products_shippingtime']),
          'shipping_link' => $this->main->getShippingStatusName($products['products_shippingtime'], true),
        );
        foreach ($shipping_status_array as $key => $value) {
          if ($value != '') {
            $products_array['attributes'][] = array(
              'key' => $key,
              'value' => $value,
              'userGroups' => $this->customers_status_all_array,
            );
          }
        }
      }
      
      $vpe_array = $this->getVpeData($products, 1);
      if (is_array($vpe_array)) {
        $products_array['attributes'][] = array(
          'key' => 'vpe_name',
          'value' => $vpe_array['vpe_name'],
          'userGroups' => $this->customers_status_all_array,
        );          
      }
      
      $price_array = array();
      $price_formatted_array = array();
      $vpe_price_array = array();
      $tax_info_array = array();

      foreach ($this->customers_status_array as $customers_status) {
        $products_price = $customers_status['xtPrice']->xtcGetPrice($products['products_id'], true, 1, $products['products_tax_class_id'], $products['products_price'], 1);
        $tax_rate = isset($customers_status['xtPrice']->TAX[$products['products_tax_class_id']]) ? $customers_status['xtPrice']->TAX[$products['products_tax_class_id']] : 0;
        $_SESSION['customers_status'] = $customers_status['xtPrice']->cStatus;
        
        if (!isset($price_array[$products_price['plain']])) {
          $price_array[$products_price['plain']] = array(
            'key' => 'price',
            'value' => $products_price['plain'],
            'userGroups' => array(),
          );

          $price_formatted_array[$products_price['plain']] = array(
            'key' => 'price_formatted',
            'value' => $customers_status['xtPrice']->xtcFormatCurrency($products_price['plain']),
            'userGroups' => array(),
          );
          
          $vpe_array = $this->getVpeData($products, $products_price['plain']);
          if (is_array($vpe_array)) {
            $vpe_price_array[$products_price['plain']] = array(
              'key' => 'vpe_price',
              'value' => $customers_status['xtPrice']->xtcFormatCurrency($vpe_array['vpe_price']),
              'userGroups' => array(),
            );
          }
        }
        $price_array[$products_price['plain']]['userGroups'][] = $customers_status['id'];
        $price_formatted_array[$products_price['plain']]['userGroups'][] = $customers_status['id'];
        if (count($vpe_price_array) > 0) {
          $vpe_price_array[$products_price['plain']]['userGroups'][] = $customers_status['id'];
        }
        
        if (!isset($tax_info_array[$tax_rate])) {
          $tax_info_array[$tax_rate] = array(
            'key' => 'tax_rate_'.$tax_rate,
            'value' => $tax_rate,
            'userGroups' => array(),
          );
        }
        $tax_info_array[$tax_rate]['userGroups'][] = $customers_status['id'];

        if (GROUP_CHECK == 'true') {
          if ($products['group_permission_'.$customers_status['id']] == 1) {
            $products_array['settings']['includeUserGroups'][] = $customers_status['id'];
          } else {
            $products_array['settings']['excludeUserGroups'][] = $customers_status['id'];
          }
        }
      }
      $products_array['attributes'] = array_merge($products_array['attributes'], array_values($price_array));
      $products_array['attributes'] = array_merge($products_array['attributes'], array_values($price_formatted_array));
      $products_array['attributes'] = array_merge($products_array['attributes'], array_values($vpe_price_array));
      $products_array['attributes'] = array_merge($products_array['attributes'], array_values($tax_info_array));
      
      return $products_array;
    }
  
  
    private function getCategories($products_id) {
      $categories_array = array();
                                           
      $categories_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                         WHERE products_id = ".$products_id);
      while ($categories = xtc_db_fetch_array($categories_query)) {
        $cPath = xtc_get_category_path($categories['categories_id']);
        $categories_array[]['path'] = array_map(array($this, 'getCategoriesNames'), explode('_', $cPath));
      }
    
      return  $categories_array;
    }
  
  
    private function getCategoriesNames($categories_id) {
      static $categories_array;
    
      if (!isset($categories_array)) {
        $categories_array = array();
      }
    
      if (!isset($categories_array[$categories_id])) {
        $categories_query = xtc_db_query("SELECT categories_name
                                            FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                           WHERE categories_id = ".$categories_id."
                                             AND language_id = ".$this->language_id);
        $categories = xtc_db_fetch_array($categories_query);
        $categories_array[$categories_id] = $categories['categories_name'];
      }
    
      return $categories_array[$categories_id];
    }
  
  
    private function getTags($products_id) {
      $tags_array = array();                     
      $tags_query = xtDBquery("SELECT pto.options_name,
                                      pto.options_description,
                                      ptv.values_name,
                                      ptv.values_description
                                 FROM ".TABLE_PRODUCTS_TAGS." pt
                                 JOIN ".TABLE_PRODUCTS_TAGS_OPTIONS." pto
                                      ON pt.options_id = pto.options_id
                                         AND pto.status = '1'
                                         AND pto.languages_id = ".$this->language_id."
                                 JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv
                                      ON ptv.values_id = pt.values_id
                                         AND ptv.status = '1'
                                         AND ptv.languages_id = ".$this->language_id."
                                WHERE pt.products_id = ".$products_id."
                             ORDER BY pt.sort_order, pto.sort_order, pto.options_name, ptv.sort_order, ptv.values_name");

      if (xtc_db_num_rows($tags_query, true) > 0) {
        while ($tags = xtc_db_fetch_array($tags_query, true)) {
          $tags_array[] = array(
            'key' => 'tags',
            'value' => $tags['options_name'],
            'userGroups' => $this->customers_status_all_array,
          );
          $tags_array[] = array(
            'key' => 'tags',
            'value' => $tags['values_name'],
            'userGroups' => $this->customers_status_all_array,
          );
        }
      }
    
      return $tags_array;
    }
  
  }