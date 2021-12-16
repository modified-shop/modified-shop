<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  include (dirname(__FILE__).'/../../includes/application_top_callback.php');
  
  // content, product, category - sql group_check/fsk_lock
  require_once (DIR_WS_INCLUDES.'define_conditions.php');

  // add_select
  require_once (DIR_WS_INCLUDES.'define_add_select.php');

  // include needed functions
  require_once(DIR_FS_INC.'get_external_content.inc.php');

  // include needed classes
  require_once(DIR_WS_CLASSES.'language.php');
  require_once(DIR_WS_CLASSES.'xtcPrice.php');
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalZettle.php');

  $lng = new language(MODULE_CATEGORIES_ZETTLE_CATEGORIES_LANGUAGE);
  $language_id = $lng->language['languages_id'];
  
  $PayPalZettle = new PayPalZettle();
  
  
  // import
  $products_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." pz2p
                                    JOIN ".TABLE_PRODUCTS." p
                                         ON p.products_id = pz2p.products_id
                                    JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                         ON p.products_id = pd.products_id
                                            AND pd.language_id = '".(int)$language_id."'
                                   WHERE pz2p.bulk > 0
                                     AND pz2p.products_uuid = ''");
  if (xtc_db_num_rows($products_query) > 0) {
    $xtPrice = new xtcPrice(DEFAULT_CURRENCY, MODULE_CATEGORIES_ZETTLE_CATEGORIES_CUSTOMERS_STATUS);
    
    $products_array = array(
      'products' => array()
    );
    
    while ($products = xtc_db_fetch_array($products_query)) {
      $products['products_currency'] = DEFAULT_CURRENCY;
      $products['products_tax'] = xtc_get_tax_rate($products['products_tax_class_id']);
      $products['products_price'] = $xtPrice->xtcGetPrice($products['products_id'], false, 1, $products['products_tax_class_id'], $products['products_price']);
      $products['products_price'] *= 100;
      
      $data = $PayPalZettle->get_product_scheme($products, true);
      
      $where = '';
      if ($products['stock'] != 1) {
        $where = ",bulk = 0";
      }
      xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                       SET products_uuid = '".xtc_db_input($data['uuid'])."'
                           ".$where ."
                     WHERE zettle_id = '".(int)$products['zettle_id']."'");

      $products_array['products'][] = $data;
    }
    
    $response = $PayPalZettle->setImport($products_array);
    
    $sql_data_array = array(
      'import_uuid' => $response['uuid']
    );
    xtc_db_perform(TABLE_PAYPAL_ZETTLE_IMPORT, $sql_data_array);
    die();
  }

  
  // check
  $import_query = xtc_db_query("SELECT *
                                  FROM ".TABLE_PAYPAL_ZETTLE_IMPORT);
  if (xtc_db_num_rows($import_query)) {
    while ($import = xtc_db_fetch_array($import_query)) {
      $response = $PayPalZettle->getImport($import['import_uuid']);
      
      if ($response['state'] != 'FINISHED_SUCCESS') {
        die();
      } else {
        xtc_db_query("DELETE FROM ".TABLE_PAYPAL_ZETTLE_IMPORT." WHERE zettle_id = '".(int)$import['zettle_id']."'");
      }
    }
  }


  // delete
  $products_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." pz2p
                                    JOIN ".TABLE_PRODUCTS." p
                                         ON p.products_id = pz2p.products_id
                                    JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                         ON p.products_id = pd.products_id
                                            AND pd.language_id = '".(int)$language_id."'
                                   WHERE pz2p.bulk > 0
                                     AND pz2p.status = 0
                                   LIMIT 50");
  if (xtc_db_num_rows($products_query) > 0) {
    while ($products = xtc_db_fetch_array($products_query)) {
      $response = $PayPalZettle->deleteProduct($products['products_uuid']);
      if ($products['products_uuid'] == '' 
          || ($response['status'] > 200 && $response['status'] < 300)
          )
      {
        xtc_db_query("DELETE FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." WHERE products_id = '".(int)$products['products_id']."'");
      }
    }
    die();
  }


  // update
  $products_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." pz2p
                                    JOIN ".TABLE_PRODUCTS." p
                                         ON p.products_id = pz2p.products_id
                                    JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                         ON p.products_id = pd.products_id
                                            AND pd.language_id = '".(int)$language_id."'
                                   WHERE pz2p.bulk > 0
                                     AND pz2p.products_uuid != ''
                                   LIMIT 50");
  if (xtc_db_num_rows($products_query) > 0) {
    $xtPrice = new xtcPrice(DEFAULT_CURRENCY, MODULE_CATEGORIES_ZETTLE_CATEGORIES_CUSTOMERS_STATUS);
    
    while ($products = xtc_db_fetch_array($products_query)) {
      $products_uuid = $products['products_uuid'];
      
      $products['products_currency'] = DEFAULT_CURRENCY;
      $products['products_tax'] = xtc_get_tax_rate($products['products_tax_class_id']);
      $products['products_price'] = $xtPrice->xtcGetPrice($products['products_id'], false, 1, $products['products_tax_class_id'], $products['products_price']);
      $products['products_price'] *= 100;
      
      $PayPalZettle->updateProduct($products_uuid, $products);
      
      if ($products['stock'] == 1) {
        $PayPalZettle->updateInventory($products_uuid, $products);
      } else {
        $PayPalZettle->deleteInventory($products_uuid);
      }

      xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                       SET bulk = 0
                     WHERE zettle_id = '".(int)$products['zettle_id']."'");
    }    
  }
