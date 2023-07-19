<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  // include needed classes
  require_once (DIR_WS_CLASSES.'language.php');

  //include needed functions
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
  require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');
  require_once(DIR_FS_INC.'get_database_version.inc.php');

  // include needed defaults
  require_once(DIR_FS_EXTERNAL.'trustedshops/trustedshops.php');


  class eTrusted {

    const URL_AUTH = 'https://login.etrusted.com';
    const URL_API = 'https://api.etrusted.com';
  
    private $access_token;
    private $client_id;
    private $client_secret;
    private $version_module = '1.0';
    private $version_system;


    function __construct($language_id) {    
      $this->LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_trustedshops_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', 'zettle', 'debug');

      $this->language_id = $language_id;

      $this->client = new \GuzzleHttp\Client();

      $this->client_id = (defined('MODULE_TS_PRODUCT_STICKER_API_CLIENT_'.$this->language_id) ?  constant('MODULE_TS_PRODUCT_STICKER_API_CLIENT_'.$this->language_id) : '');
      $this->client_secret = (defined('MODULE_TS_PRODUCT_STICKER_API_SECRET_'.$this->language_id) ?  constant('MODULE_TS_PRODUCT_STICKER_API_SECRET_'.$this->language_id) : '');

      $db_version_check = get_database_version();
      $this->version_system = $db_version_check['plain'];
    
      $this->getAccessToken();
    }

    function getAccessToken() {
      $headers = array(
        'Content-Type' => 'application/x-www-form-urlencoded'
      );
      $body = array(
        'form_params' => array(
          'client_id'     => $this->client_id,
          'client_secret' => $this->client_secret,
          'grant_type'    => 'client_credentials',
          'audience'      => 'https://api-etrusted.com'
        )
      );

      if ($this->access_token == '') {
        $response = $this->call(self::URL_AUTH.'/oauth/token', 'POST', $body, $headers);
        $this->access_token = $response['access_token'];
      }

      return $this->access_token;
    }

    function getReviews($url = '', $migrate = false) {
      if ($url == '') {
        $url_array = array(
          'type' => 'PRODUCT_REVIEW',
          'Trustedshops-ClientSystem' => 'modifiedecommerce',
          'Trustedshops-ConnectorModule' => 'TrustedShopsPlugin',
          'Trustedshops-ConnectorModuleVersion' => $this->version_module,
          'Trustedshops-ClientSystemVersion' => $this->version_system,
        );
        
        if (constant('MODULE_TRUSTEDSHOPS_CRONJOB_'.$this->language_id) > 0) {
           $url_array['submittedAfter'] = date('Y-m-d\TH:i:s.000\Z', constant('MODULE_TRUSTEDSHOPS_CRONJOB_'.$this->language_id));
        }
        
        $url = self::URL_API.'/reviews?'.http_build_query($url_array, '', '&');
      }
      $headers = array(
        'Authorization' => 'Bearer '.$this->access_token,
      );

      $response = $this->call($url, 'GET', array(), $headers);

      if (isset($response['items'])
          && count($response['items']) > 0
          )
      {
        if (!isset($lng) || (isset($lng) && !is_object($lng))) {
          $lng = new language();
        }

        foreach ($response['items'] as $review) {
          $products_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PRODUCTS."
                                          WHERE products_model = '".xtc_db_input($review['product']['sku'])."'");
          if (xtc_db_num_rows($products_query) > 0) {
            $products = xtc_db_fetch_array($products_query);
            
            $language_id = $this->language_id;
            if (isset($review['questionnaire']) 
                && isset($review['questionnaire']['locale'])
                )
            {
              $language_code = substr($review['questionnaire']['locale'], 0, strpos($review['questionnaire']['locale'], '_'));
              if (isset($lng->catalog_languages[$language_code])) {
                $language = $lng->catalog_languages[$language_code];
                $language_id = $language['id'];
              }
            }
            
            $customers_id = 0;
            $author = constant('TEXT_GUEST_'.$language_id);

            // customer
            $customers_query = xtc_db_query("SELECT *
                                               FROM ".TABLE_CUSTOMERS."
                                              WHERE customers_email_address = '".xtc_db_input($review['customer']['email'])."'");
          
            // order
            if (xtc_db_num_rows($customers_query) < 1) {
              $customers_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_ORDERS."
                                                WHERE customers_email_address = '".xtc_db_input($review['customer']['email'])."'");
            }
          
            // order
            if (xtc_db_num_rows($customers_query) < 1
                && isset($review['transaction'])
                && isset($review['transaction']['reference'])
                )
            {
              $customers_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_ORDERS."
                                                WHERE orders_id = '".xtc_db_input($review['transaction']['reference'])."'");
            }

            if (xtc_db_num_rows($customers_query) > 0) {
              $customers = xtc_db_fetch_array($customers_query);
              
              $customers_id = $customers['customers_id'];
              $author = $customers['customers_firstname'].' '.$customers['customers_lastname'][0].'.';
            }

            if ($migrate === true) {
              $check_query = xtc_db_query("SELECT *  
                                             FROM ".TABLE_REVIEWS."
                                            WHERE customers_name = '".xtc_db_input($author)."'
                                              AND products_id = '".(int)$products['products_id']."'
                                              AND date_added = '".xtc_db_input(date('Y-m-d H:i:s', strtotime($review['createdAt'])))."'
                                              AND customers_id = '".(int)$customers_id."'
                                              AND (external_id = '' OR external_id IS NULL)");
              if (xtc_db_num_rows($check_query) > 0) {
                $check = xtc_db_fetch_array($check_query);
            
                $sql_data_array = array(
                  'external_id' => xtc_db_prepare_input($review['id']),
                  'external_source' => 'trustedshops',
                );
                xtc_db_perform(TABLE_REVIEWS, $sql_data_array, 'update', "reviews_id = '".(int)$check['reviews_id']."'");               
              }
            }

            $check_query = xtc_db_query("SELECT r.*
                                           FROM ".TABLE_REVIEWS." r
                                           JOIN ".TABLE_PRODUCTS." p
                                                ON r.products_id = p.products_id
                                          WHERE r.products_id = '".(int)$products['products_id']."'
                                            AND r.external_id = '".xtc_db_input($review['id'])."'
                                            AND r.external_source = 'trustedshops'");
            if (xtc_db_num_rows($check_query) < 1) {
              $sql_data_array = array(
                'products_id' => $products['products_id'],
                'customers_id' => $customers_id,
                'customers_name' => $author,
                'reviews_rating' => $review['rating'],
                'date_added' =>  date('Y-m-d H:i:s', strtotime($review['createdAt'])),
                'external_id' => xtc_db_prepare_input($review['id']),
                'external_source' => 'trustedshops',
              );        
              xtc_db_perform(TABLE_REVIEWS, $sql_data_array);
              $insert_id = xtc_db_insert_id();

              $sql_data_array = array(
                'reviews_id' => $insert_id,
                'languages_id' => (int)$language_id,
                'reviews_text' => decode_utf8($review['comment'])
              );
              xtc_db_perform(TABLE_REVIEWS_DESCRIPTION, $sql_data_array);
            }
          }
        }
        
        if (isset($response['paging'])
            && isset($response['paging']['links'])
            && isset($response['paging']['links']['next'])
            )
        {
          $this->getReviews($response['paging']['links']['next'], $migrate);
        }
      }
    }

    function call($url, $method, $body = array(), $headers = array()) {
      try {
        $response = $this->client->request($method, $url, array_merge(array('headers' => $headers), $body));
        if (is_object($response)) {
          $response = $response->getBody()->getContents();
          $response = json_decode($response, true);
        }
        return $response;
      } catch (Exception $ex) {
        $response = array(
          'headers' => $ex->getResponse()->getHeaders(),
          'status' => $ex->getResponse()->getStatusCode(),
          'reason' => $ex->getResponse()->getReasonPhrase(),
          'error' => json_decode($ex->getResponse()->getBody(), true),
        );
        $this->LoggingManager->log('DEBUG', 'call', $response);        
        return $response;
      }          
    }

  }
