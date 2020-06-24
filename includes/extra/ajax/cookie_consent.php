<?php
  /* --------------------------------------------------------------
   $Id: cookie_consent.js.php $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function cookie_consent() {
    $response = array();
    if (defined('MODULE_COOKIE_CONSENT_STATUS') && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true') {
      $response['vendorListVersion'] = MODULE_COOKIE_CONSENT_VERSION;
      $response['lastUpdated'] = date('c',strtotime(MODULE_COOKIE_CONSENT_LAST_UPDATE));
      $response['categories'] = array();
      $response['purposes'] = array();
      $response['features'] = array();
      $response['vendors'] = array();

      $cookies_query = xtDBquery("SELECT *
                                    FROM " . TABLE_COOKIE_CONSENT_COOKIES . " 
                                   WHERE languages_id = '".(int)$_SESSION['languages_id']."' AND `status`=1
                                ORDER BY sort_order, cookies_name");
      $cookies_cat = array();
      while ($row = xtc_db_fetch_array($cookies_query, true)) {
        if (!array_key_exists($row['categories_id'], $cookies_cat)) {
          $cookies_cat[$row['categories_id']] = array();
        }
        $cookies_cat[$row['categories_id']][] = $row;
      }
    
      $options_query = xtDBquery("SELECT *
                                    FROM " . TABLE_COOKIE_CONSENT_CATEGORIES . " 
                                   WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                ORDER BY sort_order, categories_name");
      while ($options = xtc_db_fetch_array($options_query, true)) {
        if (!empty($cookies_cat[$options['categories_id']])) {
          $response['categories'][] = array(
            'id' => (int)$options['categories_id'],
            'name' => encode_htmlentities($options['categories_name']),
            'description' => encode_htmlentities($options['categories_description']),
            'value' => $options['categories_id'] == 1 ? true : false,
            'locked' => $options['categories_id'] == 1 ? true : false
          );
        }
      }
    
      $i = 0;
      foreach ($cookies_cat as $cat => $cookies) {
        foreach ($cookies as $value) {
          $response['purposes'][] = array(
            'id' => (int)$value['cookies_id'],
            'name' => encode_htmlentities($value['cookies_name']),
            'description' => encode_htmlentities($value['cookies_description']),
            'category' => (int)$cat,
            'value' => $cat == 1 ? true : false
          );
          if (!empty($value['cookies_list'])) {
            $response['purposes'][$i]['cookies'] = explode(',', encode_htmlentities($value['cookies_list']));
          }
          $i++;
        }
      }
    }
  
    return $response;
  }
