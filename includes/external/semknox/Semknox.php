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
    protected $language_directory;
    public $version = '1.0';
  
    function __construct($language_id, $timeout = 3) {
      $context = $this->capturePriceContext();
      try {
      // logger
      $this->logger = new LoggingManager(DIR_FS_LOG.'mod_%s_%s.log', 'info', 'error');
      
      $languages_query = xtc_db_query("SELECT * 
                                         FROM ".TABLE_LANGUAGES." 
                                         WHERE languages_id = '".(int)$language_id."'");
      $languages = xtc_db_fetch_array($languages_query);
      $this->language_id = $languages['languages_id'];
      $this->language_charset = $languages['language_charset'];
      $this->language_directory = $languages['directory'];
      $_SESSION['languages_id'] = $this->language_id;
      $_SESSION['language'] = $this->language_directory;

      $this->api_key = defined('MODULE_SEMKNOX_SYSTEM_API_'.$this->language_id) ? constant('MODULE_SEMKNOX_SYSTEM_API_'.$this->language_id) : '';
      $this->project_id = defined('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$this->language_id) ? constant('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$this->language_id) : '';
      
      $this->main = new main($this->language_id);
      
      $this->customers_status_array = array();
      $this->customers_status_all_array = array();
      $customers_status_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_CUSTOMERS_STATUS."
                                               WHERE language_id = ".$this->language_id);
      while ($customers_status = xtc_db_fetch_array($customers_status_query)) {
        $_SESSION['customers_status'] = $customers_status;
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
          'base_uri' => 'https://api-modified.sitesearch360.com/',
          'timeout' => $timeout,
          'headers' => array(
            'SHOPSYS' => 'MODIFIED',
            'SHOPSYSVER' => $db_version['plain'],
            'EXTVER' => $this->version,
          )
        )
      );
      } finally {
        $this->restorePriceContext($context);
      }
    }

    
    public function initBatch() {
      try {
        $response = $this->client->post(
          sprintf('products/batch/initiate?apiKey=%s&projectId=%s', $this->api_key, $this->project_id)
        );
        $json = $response->getBody();
        $response = json_decode($json);
        $this->logger->log('semknox', __FUNCTION__.': '.date('Y-m-d H:i:s'));
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }

    
    public function uploadBatch($products_id_array) {
      $products_array = array();
      foreach ($products_id_array as $nr => $products_id) {
        $product = $this->getProduct($products_id);
        if (xtc_not_null($product)) {
          $product = array_filter($product);
          if (is_array($product) && count($product) > 0) {
            $products_array['products'][] = $this->getProduct($products_id);
          }
        }
      }
      
      $response = new stdClass();
      $response->status = 'success';
      
      if (isset($products_array['products'])) {
        try {
          $response = $this->client->post(
            sprintf('products/batch/upload?apiKey=%s&projectId=%s', $this->api_key, $this->project_id),
            array(GuzzleHttp\RequestOptions::JSON => $this->convertToString($products_array))
          );
          $json = $response->getBody();
          $response = json_decode($json);
          $this->logger->log('semknox', __FUNCTION__.': '.date('Y-m-d H:i:s'));
        } catch (Exception $e) {
          $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
        }
      }
    
      return $response;
    }


    public function startBatch() {
      try {
        $response = $this->client->post(
          sprintf('products/batch/start?apiKey=%s&projectId=%s', $this->api_key, $this->project_id)
        );
        $json = $response->getBody();
        $response = json_decode($json);
        $this->logger->log('semknox', __FUNCTION__.': '.date('Y-m-d H:i:s'));
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
          sprintf('products?apiKey=%s&projectId=%s', $this->api_key, $this->project_id),
          array(GuzzleHttp\RequestOptions::JSON => $this->convertToString($products_array))
        );
        $json = $response->getBody();
        $response = json_decode($json, true);
        $this->logger->log('semknox', __FUNCTION__.': '.date('Y-m-d H:i:s'));
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
          sprintf('products?apiKey=%s&projectId=%s', $this->api_key, $this->project_id),
          array(GuzzleHttp\RequestOptions::JSON => $this->convertToString($product_data))
        );
        $json = $response->getBody();
        $response = json_decode($json);
        $this->logger->log('semknox', __FUNCTION__.': '.date('Y-m-d H:i:s'));
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }
  

    public function getTask($taskId) {
      try {
        $response = $this->client->get(
          sprintf('tasks/%s?apiKey=%s&projectId=%s', $taskId, $this->api_key, $this->project_id)
        );
        $json = $response->getBody();
        $response = json_decode($json);
        $this->logger->log('semknox', __FUNCTION__.': '.date('Y-m-d H:i:s'));
      } catch (Exception $e) {
        $this->logger->log('semknox', __FUNCTION__.': '.$e->getMessage());
      }
    
      return $response;
    }
    
    
    private function capturePriceContext() {
      $context = array('session' => array(), 'globals' => array());
      foreach (array('languages_id', 'language', 'customers_status') as $key) {
        $context['session'][$key] = array(array_key_exists($key, $_SESSION), isset($_SESSION[$key]) ? $_SESSION[$key] : null);
      }
      foreach (array('product', 'xtPrice') as $key) {
        $context['globals'][$key] = array(array_key_exists($key, $GLOBALS), isset($GLOBALS[$key]) ? $GLOBALS[$key] : null);
      }
      return $context;
    }

    private function restorePriceContext($context) {
      foreach ($context['session'] as $key => $value) {
        if ($value[0]) {
          $_SESSION[$key] = $value[1];
        } else {
          unset($_SESSION[$key]);
        }
      }
      foreach ($context['globals'] as $key => $value) {
        if ($value[0]) {
          $GLOBALS[$key] = $value[1];
        } else {
          unset($GLOBALS[$key]);
        }
      }
    }

    private function getProduct($products_id) {
      $context = $this->capturePriceContext();
      try {
        $_SESSION['languages_id'] = $this->language_id;
        $_SESSION['language'] = $this->language_directory;
        $_SESSION['customers_status'] = get_customers_status_by_id(DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
        return $this->buildProduct($products_id);
      } finally {
        $this->restorePriceContext($context);
      }
    }

    private function buildProduct($products_id) {
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
                                                AND trim(pd.products_name) != ''
                                   LEFT JOIN ".TABLE_MANUFACTURERS." m
                                             ON p.manufacturers_id = m.manufacturers_id
                                       WHERE p.products_id = ".$products_id."
                                         AND p.products_status = 1");
      if (xtc_db_num_rows($products_query) > 0) {
        $products = xtc_db_fetch_array($products_query);

        $products_array = array(
          'identifier' => $products['products_id'],
          'groupIdentifier' => $products['products_id'],
          'name' => $this->getText($products['products_name']),
          'productUrl' => $xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($products['products_id'], $products['products_name']), 'NONSSL', false),
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
          'key' => 'viewed',
          'value' => $products['products_viewed'],
          'userGroups' => $this->customers_status_all_array,
        );

        $products_array['attributes'][] = array(
          'key' => 'ordered',
          'value' => $products['products_ordered'],
          'userGroups' => $this->customers_status_all_array,
        );

        $products_array['attributes'][] = array(
          'key' => 'date_added',
          'value' => $this->getText($products['products_date_added']),
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

        if ($products['products_order_description'] != '') {
          $products_array['attributes'][] = array(
            'key' => 'description_order',
            'value' => $this->getText($products['products_order_description']),
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
      
        $reviews_avg = $product->getReviewsAverage($products['products_id'], 1);
        $products_array['attributes'][] = array(
          'key' => 'reviews_avg',
          'value' => number_format($reviews_avg, 1, ',', ''),
          'userGroups' => $this->customers_status_all_array,
        );

        $reviews_count = $product->getReviewsCount($products['products_id']);
        if ($reviews_count > 0) {
          $products_array['attributes'][] = array(
            'key' => 'reviews_count',
            'value' => $reviews_count,
            'userGroups' => $this->customers_status_all_array,
          );
          
          $reviews_key = $this->buildReviewsArray($reviews_avg);
          foreach ($reviews_key as $k => $v) {
            $products_array['attributes'][] = array(
              'key' => 'reviews_avg_'.$k,
              'value' => $v,
              'userGroups' => $this->customers_status_all_array,
            );
          }
        } else {
          $products_array['attributes'][] = array(
            'key' => 'reviews_zero',
            'value' => 1,
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
      
        $price_attributes = array();
        foreach ($this->customers_status_array as $customers_status) {
          $xtPrice = $customers_status['xtPrice'];
          $_SESSION['customers_status'] = $xtPrice->cStatus;
          $products_price = $xtPrice->xtcGetPrice($products['products_id'], true, 1, $products['products_tax_class_id'], $products['products_price'], 1, 0, true);
          $group = $customers_status['id'];
          $decimals = $xtPrice->get_decimal_places();
          $this->addPriceAttribute($price_attributes, 'price', number_format($products_price['plain'], 4, ',', ''), $group);
          $this->addPriceAttribute($price_attributes, 'price_formatted', $xtPrice->xtcFormatCurrency($products_price['plain']), $group);

          $tax_class = $xtPrice->tax_class;
          $tax_rate = 0;
          if (isset($xtPrice->TAX[$tax_class])
              && $xtPrice->cStatus['customers_status_show_price'] != 0
              && $xtPrice->cStatus['customers_status_show_price_tax'] != 0)
          {
            $tax_rate = $xtPrice->TAX[$tax_class];
          }
          $this->addPriceAttribute($price_attributes, 'tax_rate_'.$tax_rate, $tax_rate, $group);

          $minimum = isset($products_price['attribute_minimum']) ? $products_price['attribute_minimum'] : null;
          $quantity = ($minimum !== null && isset($minimum['available']) && !$minimum['available']) ? 0 : $products['products_quantity'];
          $this->addPriceAttribute($price_attributes, 'quantity', $quantity, $group);
          $vpe_products = $this->minimumVpeProducts($products, $minimum);
          $vpe_array = $products_price['plain'] > 0 ? $this->getVpeData($vpe_products, $products_price['plain'], $decimals) : false;
          if (is_array($vpe_array)) {
            $this->addPriceAttribute($price_attributes, 'vpe_name', $vpe_array['vpe_name'], $group);
            $this->addPriceAttribute($price_attributes, 'vpe_value', $vpe_array['vpe_value'], $group);
            $this->addPriceAttribute($price_attributes, 'vpe_price', $xtPrice->xtcFormatCurrency($vpe_array['vpe_price']), $group);
          }

          // Use the same complete configuration for the regular comparison price.
          if (isset($products_price['old_price_plain'])) {
            $old_price = $products_price['old_price_plain'];
          } else {
            $old_price = $xtPrice->xtcAddTax($products['products_price'], $tax_rate);
            if ($minimum !== null) {
              $old_price = $xtPrice->xtcAddAttributeMinimum($old_price, $minimum['old_price']);
            }
          }
          if ($xtPrice->cStatus['customers_status_show_price'] != 0 && $old_price > 0
              && round($products_price['plain'], $decimals) < round($old_price, $decimals))
          {
            $this->addPriceAttribute($price_attributes, 'price_old', $old_price, $group);
            $this->addPriceAttribute($price_attributes, 'price_old_save', round(($old_price - $products_price['plain']) / $old_price * 100), $group);
            $this->addPriceAttribute($price_attributes, 'price_old_formatted', $xtPrice->xtcFormatCurrency($old_price), $group);
          }

          if (GROUP_CHECK == 'true') {
            if ($products['group_permission_'.$group] == 1) {
              $products_array['settings']['includeUserGroups'][] = $group;
            } else {
              $products_array['settings']['excludeUserGroups'][] = $group;
            }
          }
        }
        $products_array['attributes'] = array_merge($products_array['attributes'], array_values($price_attributes));
        $products_array['attributes'] = array_values($products_array['attributes']);
                   
        if (count($products_array['categories']) > 0) {
          return $this->encode_utf8($products_array);
        }
      }
    }
  
  
    private function getCategories($products_id) {
      $categories_array = array();
                                           
      $categories_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                         WHERE products_id = ".$products_id);
      while ($categories = xtc_db_fetch_array($categories_query)) {
        $cPath = xtc_get_category_path($categories['categories_id']);

        $path = array_map(array($this, 'getCategoriesNames'), explode('_', $cPath));
        $path = array_filter($path);
        
        if (is_array($path) && count($path) > 0) {
          $categories_array[]['path'] = array_values($path);
        }
      }
    
      return $categories_array;
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
                                      ptv.values_name
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
            'key' => $tags['options_name'],
            'value' => $tags['values_name'],
            'userGroups' => $this->customers_status_all_array,
          );
        }
      }
    
      return $tags_array;
    }
  
  
    private function getText($string) {
      $string = strip_tags($string);
      $string = preg_replace("/\s++/", ' ', $string);
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


    private function addPriceAttribute(&$attributes, $key, $value, $group) {
      // Each field has its own grouping: equal prices need not imply equal VPE or savings.
      $hash = $key.':'.hash('sha256', serialize($value));
      if (!isset($attributes[$hash])) {
        $attributes[$hash] = array('key' => $key, 'value' => $value, 'userGroups' => array());
      }
      $attributes[$hash]['userGroups'][] = $group;
    }

    private function minimumVpeProducts($products, $minimum) {
      $vpe_products = $products;
      if ($minimum !== null) {
        if (!isset($minimum['attributes']) || !is_array($minimum['attributes'])) {
          $vpe_products['products_vpe_status'] = 0;
          $vpe_products['products_vpe_value'] = 0;
        } else {
          foreach ($minimum['attributes'] as $attribute) {
            $value = isset($attribute['attributes_vpe_value']) ? (float)$attribute['attributes_vpe_value'] : 0;
            switch ($attribute['weight_prefix']) {
              case '-':
                $vpe_products['products_vpe_value'] -= $value;
                break;
              case '=':
                $vpe_products['products_vpe_value'] = $value;
                break;
              default:
                $vpe_products['products_vpe_value'] += $value;
            }
            $vpe_products['products_vpe'] = (isset($attribute['attributes_vpe_id']) && $attribute['attributes_vpe_id'] > 0 ? $attribute['attributes_vpe_id'] : $products['products_vpe']);
          }
        }
      }
      return $vpe_products;
    }

    private function getVpeData($products, $price, $decimal_places = 2) {
      static $vpe_name_array;
      
      if (!isset($vpe_name_array)) {
        $vpe_name_array = array();
      }
      
      if (isset($products['products_vpe_status']) 
          && $products['products_vpe_status'] == 1 
          && $products['products_vpe_value'] != 0.0 
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
          'vpe_value' => $products['products_vpe_value'],
          'vpe_name' => $vpe_name_array[$this->language_id][$products['products_vpe']],
          'vpe_price' => round($price, $decimal_places) * (1 / $products['products_vpe_value']),
        );
      }
      
      return false;
    }


    function encode_utf8($string) {
      if (is_array($string)) {
        foreach ($string as $key => $value) {
          $string[$key] = $this->encode_utf8($value);
        }
      } else {
        $string = decode_htmlentities($string);
        $cur_encoding = detect_encoding($string);
        if ($cur_encoding == "UTF-8" && mb_check_encoding($string, "UTF-8")) {
          return $string;
        } else {
          return mb_convert_encoding($string, "UTF-8", $this->language_charset);
        }
      }
    
      return $string;  
    }


    function buildReviewsArray(float $value) {
      $intPart = (int) floor($value);
      $existing = [];
    
      for ($i = 1; $i <= $intPart; $i++) {
        $existing[$i * 10] = true;
      }
    
      $frac = round($value - $intPart, 5);
      if ($frac > 0) {
        $extra = (int) round($frac * 10 / 5) * 5;
        if ($extra > 0) {
          $existing[$intPart * 10 + $extra] = true;
        }
      }
    
      $result = [];
      for ($k = 5; $k <= 50; $k += 5) {
        $result[$k] = isset($existing[$k]) ? 1 : 0;
      }
    
      return $result;
    }

  }