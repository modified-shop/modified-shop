<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
  
  
  if (defined('MODULE_SHOPVOTE_STATUS')
      && MODULE_SHOPVOTE_STATUS == 'true'
      )
  {
    if (MODULE_SHOPVOTE_API_KEY != ''
        && is_object($product) 
        && $product->isProduct() === true
        && (time() - strtotime($product->data['shopvote_last_imported']) >= 86400)
        ) 
    {
    
      // include needed classes
      require_once (DIR_WS_CLASSES.'modified_api.php');
      require_once (DIR_WS_CLASSES.'language.php');
    
      modified_api::reset();
      modified_api::setEndpoint('https://api.shopvote.de/');
          
      $response = unserialize(file_get_contents(SQL_CACHEDIR.'shopvote.cache'));
    
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

        $days = 365;
        $timestamp = strtotime($product->data['shopvote_last_imported']);
        if ($timestamp !== false) {
          $days = ceil((time() - $timestamp) / 86400);
          if ($days > 365) {
            $days = 365;
          }
        }
              
        $response = modified_api::request('product-reviews/v2/reviews?days='.$days.'&sd=false&sku='.$product->data['products_id']);
          
        if (is_array($response)
            && isset($response['shopid']) 
            )
        {
          if (count($response['reviews']) > 0) {
            if (!isset($lng) || (isset($lng) && !is_object($lng))) {
              $lng = new language;
            }
                      
            foreach ($response['reviews'] as $reviews) {
              $check_query = xtc_db_query("SELECT customers_id  
                                             FROM ".TABLE_REVIEWS."
                                            WHERE customers_name = '".xtc_db_input($reviews['author'])."'
                                              AND products_id = '".(int)$product->data['products_id']."'
                                              AND date_added = '".xtc_db_input(date('Y-m-d H:i:s', strtotime($reviews['created'])))."'
                                              AND customers_id = '0'");
              if (xtc_db_num_rows($check_query) < 1) {
                if (isset($lng->catalog_languages[$reviews['lang']])) {
                  $language = $lng->catalog_languages[$reviews['lang']];
                } else {
                  $language = $lng->catalog_languages[MODULE_SHOPVOTE_DEFAULT_LANG];
                }

                $sql_data_array = array(
                  'products_id' => $product->data['products_id'],
                  'customers_id' => 0,
                  'customers_name' => xtc_db_prepare_input($reviews['author']),
                  'reviews_rating' => (int)$reviews['rating_value'],
                  'date_added' => date('Y-m-d H:i:s', strtotime($reviews['created'])),
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
          xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                           SET shopvote_last_imported = now()
                         WHERE products_id = '".(int)$product->data['products_id']."'");
        }          
      }
    }   
  }
?>