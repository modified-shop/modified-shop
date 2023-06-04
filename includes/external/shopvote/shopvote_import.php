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
  require_once (DIR_WS_CLASSES.'modified_api.php');
  require_once (DIR_WS_CLASSES.'language.php');


  class shopvote_import {

    function __construct() {
      modified_api::reset();
      modified_api::setEndpoint('https://api.shopvote.de/');
    }
    
    function auth() {    
      $response = false;
      if (is_file(SQL_CACHEDIR.'shopvote.cache')) {
        $response = unserialize(file_get_contents(SQL_CACHEDIR.'shopvote.cache'));
      }
    
      if ($response === false
          || $response['exp'] < time()
          )
      {
        $options = array(
          CURLOPT_HTTPHEADER => array(
            'Apikey: '.MODULE_SHOPVOTE_API_KEY,
            'Apisecret: '.MODULE_SHOPVOTE_API_SECRET,
            'Origin: '.HTTP_SERVER,
          ),
          CURLOPT_USERAGENT => 'App.RF5.'.MODULE_SHOPVOTE_SHOPID,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        );
        modified_api::setOptions($options);      
        $response = modified_api::request('auth');

    
        if (is_array($response)
            && isset($response['Token'])
            )
        {
          $token = explode('.', $response['Token']);
          $response = array_merge($response, json_decode(base64_decode($token[1]), true));
    
          file_put_contents(SQL_CACHEDIR.'shopvote.cache', serialize($response));
        }
      }
      
      return $response;
    }

    function import($days, $sku = '', $migrate = false) {
      $response = $this->auth();
      
      if (is_array($response)
          && isset($response['Code']) 
          && $response['Code'] == 200
          )
      {
        $options = array(
          CURLOPT_HTTPHEADER => array(
            'Token: Bearer '.$response['Token'],
          ),
          CURLOPT_USERAGENT => 'App.RF5.'.MODULE_SHOPVOTE_SHOPID,
        );
        modified_api::setOptions($options);      
            
        $response = modified_api::request('product-reviews/v2/reviews?days='.$days.'&sd=false'.(($sku != '') ? '&sku='.(int)$sku : ''));
      
        if (is_array($response)
            && isset($response['shopid']) 
            )
        {
          if (count($response['reviews']) > 0) {
            if (!isset($lng) || (isset($lng) && !is_object($lng))) {
              $lng = new language();
            }
                          
            foreach ($response['reviews'] as $reviews) {
              if ($sku != '') $reviews['sku'] = $sku;
              
              if ($migrate === true) {
                $check_query = xtc_db_query("SELECT *  
                                               FROM ".TABLE_REVIEWS."
                                              WHERE customers_name = '".xtc_db_input($reviews['author'])."'
                                                AND products_id = '".(int)$reviews['sku']."'
                                                AND date_added = '".xtc_db_input(date('Y-m-d H:i:s', strtotime($reviews['created'])))."'
                                                AND customers_id = '0'
                                                AND external_id = ''");
                if (xtc_db_num_rows($check_query) > 0) {
                  $check = xtc_db_fetch_array($check_query);
                
                  $sql_data_array = array(
                    'external_id' => xtc_db_prepare_input($reviews['reviewUID']),
                    'external_source' => 'shopvote',
                  );
                  xtc_db_perform(TABLE_REVIEWS, $sql_data_array, 'update', "reviews_id = '".(int)$check['reviews_id']."'");               
                }
              }
            
              $check_query = xtc_db_query("SELECT r.*
                                             FROM ".TABLE_REVIEWS." r
                                             JOIN ".TABLE_PRODUCTS." p
                                                  ON r.products_id = p.products_id
                                            WHERE r.products_id = '".(int)$reviews['sku']."'
                                              AND r.external_id = '".xtc_db_input($reviews['reviewUID'])."'
                                              AND r.external_source = 'shopvote'");
              if (xtc_db_num_rows($check_query) < 1) {
                if (isset($lng->catalog_languages[$reviews['lang']])) {
                  $language = $lng->catalog_languages[$reviews['lang']];
                } else {
                  $language = $lng->catalog_languages[MODULE_SHOPVOTE_DEFAULT_LANG];
                }

                $sql_data_array = array(
                  'products_id' => (int)$reviews['sku'],
                  'customers_id' => 0,
                  'customers_name' => xtc_db_prepare_input($reviews['author']),
                  'reviews_rating' => (int)$reviews['rating_value'],
                  'date_added' => date('Y-m-d H:i:s', strtotime($reviews['created'])),
                  'external_id' => xtc_db_prepare_input($reviews['reviewUID']),
                  'external_source' => 'shopvote',
                );
                xtc_db_perform(TABLE_REVIEWS, $sql_data_array);
                $insert_id = xtc_db_insert_id();

                $sql_data_array = array(
                  'reviews_id' => $insert_id,
                  'languages_id' => (int)$language['id'],
                  'reviews_text' => xtc_db_prepare_input($reviews['text'])
                );
                xtc_db_perform(TABLE_REVIEWS_DESCRIPTION, $sql_data_array);
              } 
            }
          }
          
          return true;
        }          
      }
      
      return false;        
    }
    
  }
