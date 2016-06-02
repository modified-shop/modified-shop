<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
  
  
  if (defined('MODULE_TRUSTEDSHOPS_STATUS') && MODULE_TRUSTEDSHOPS_STATUS == 'true') {
    // load configuration
    $trustedshops_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_TRUSTEDSHOPS."
                                         WHERE status = '1'
                                           AND languages_id = '".(int)$_SESSION['languages_id']."'");
    if (xtc_db_num_rows($trustedshops_query) > 0) {
      $trustedshops = xtc_db_fetch_array($trustedshops_query);
      foreach ($trustedshops as $key => $value) {
        defined('MODULE_TS_'.strtoupper($key)) OR define('MODULE_TS_'.strtoupper($key), $value);
      }
    }

    if (defined('MODULE_TS_TRUSTEDSHOPS_ID') && MODULE_TS_PRODUCT_STICKER_API == '1') {
      if (is_object($product) 
          && $product->isProduct() === true
          && (time() - strtotime($product->data['products_last_modified']) > 3600)
          ) 
      {
        // include needed functions
        require_once (DIR_FS_INC.'get_external_content.inc.php');
      
        $url = 'https://api.trustedshops.com/rest/public/v2/products.xml?tsId='.MODULE_TS_TRUSTEDSHOPS_ID.'&sku='.$product->data['products_model'];
        $reviews_api = get_external_content($url, 3, false);
        $reviews_xml = simplexml_load_string($reviews_api);
        
        if (is_object($reviews_xml->data->products)) {
          foreach ($reviews_xml->data->products->product->reviews->review as $reviews) {
            $check_query = xtc_db_query("SELECT customers_id  
                                           FROM ".TABLE_REVIEWS."
                                          WHERE customers_name = '".xtc_db_input($reviews->reviewer->firstname . ' ' . $reviews->reviewer->lastname)."'
                                            AND date_added = '".xtc_db_input(date('Y-m-d H:i:s', strtotime($reviews->creationDate)))."'
                                            AND customers_id = '0'");
            if (xtc_db_num_rows($check_query) < 1) {
              $sql_data_array = array('products_id' => $product->data['products_id'],
                                      'customers_id' => 0,
                                      'customers_name' => xtc_db_prepare_input($reviews->reviewer->firstname . ' ' . $reviews->reviewer->lastname),
                                      'reviews_rating' => (int)$reviews->mark,
                                      'date_added' => date('Y-m-d H:i:s', strtotime($reviews->creationDate)),
                                      //'ts_uid' => $reviews->UID
                                      );
      
              xtc_db_perform(TABLE_REVIEWS, $sql_data_array);
              $insert_id = xtc_db_insert_id();

              $sql_data_array = array('reviews_id' => $insert_id,
                                      'languages_id' => (int)$_SESSION['languages_id'],
                                      'reviews_text' => xtc_db_prepare_input($reviews->comment)
                                      );
              xtc_db_perform(TABLE_REVIEWS_DESCRIPTION, $sql_data_array);
            } 
          }
        }
        
        // update product for caching
        xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                         SET products_last_modified = now()
                       WHERE products_id = '".$product->data['products_id']."'");
      }   
    }
  }
?>