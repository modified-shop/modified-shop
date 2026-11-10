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
  require_once(DIR_FS_INC.'get_customers_status_by_id.inc.php');
  require_once(DIR_FS_INC.'get_external_content.inc.php');

  // include needed classes
  require_once(DIR_WS_CLASSES.'language.php');
  require_once(DIR_WS_CLASSES.'xtcPrice.php');
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalZettle.php');

  if (defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS')
      && MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS == 'true'
      )
  {
    $lng = new language(MODULE_CATEGORIES_ZETTLE_CATEGORIES_LANGUAGE);
    $language_id = $lng->language['languages_id'];
    
    $PayPalZettle = new PayPalZettle();


    // specials start and end by date without any write, the price still has to follow
    $specials_now_query = xtc_db_query("SELECT NOW() AS specials_now");
    $specials_now = xtc_db_fetch_array($specials_now_query);
    $specials_now = $specials_now['specials_now'];
    if (defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_SPECIALS_CHECK')) {
      $specials_check = xtc_db_input(MODULE_CATEGORIES_ZETTLE_CATEGORIES_SPECIALS_CHECK);
      // a negative bulk waits for its import, a special still counts as valid in its expires_date second
      xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." pz2p
                      JOIN ".TABLE_SPECIALS." s
                           ON s.products_id = pz2p.products_id
                       SET pz2p.bulk = 1
                     WHERE pz2p.status = 1
                       AND pz2p.products_uuid != ''
                       AND pz2p.bulk >= 0
                       AND ((s.start_date > '".$specials_check."' AND s.start_date <= '".$specials_now."')
                            OR (s.expires_date >= '".$specials_check."' AND s.expires_date < '".$specials_now."'))");
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".$specials_now."'
                     WHERE configuration_key = 'MODULE_CATEGORIES_ZETTLE_CATEGORIES_SPECIALS_CHECK'");
    } else {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_CATEGORIES_ZETTLE_CATEGORIES_SPECIALS_CHECK', '".$specials_now."', '6', '3', NOW())");
    }
    
    
    // after an unclear attempt the first import can still be running, a rejection only counts at the end
    $import_settled = "(bulk = -1 OR bulk <= -5)";

    // import, bulk < 0 is a reserved uuid and counts its attempts
    // only one import at a time, its result decides about all reserved rows
    $products_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." pz2p
                                      JOIN ".TABLE_PRODUCTS." p
                                           ON p.products_id = pz2p.products_id
                                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                           ON p.products_id = pd.products_id
                                              AND pd.language_id = '".(int)$language_id."'
                                     WHERE (pz2p.bulk < 0 OR (pz2p.bulk > 0 AND pz2p.products_uuid = ''))
                                       AND NOT EXISTS (SELECT 1 FROM ".TABLE_PAYPAL_ZETTLE_IMPORT.")
                                  ORDER BY pz2p.bulk");
    if (xtc_db_num_rows($products_query) > 0) {
      $xtPrice = new xtcPrice(DEFAULT_CURRENCY, MODULE_CATEGORIES_ZETTLE_CATEGORIES_CUSTOMERS_STATUS);
      
      $products_array = array(
        'products' => array()
      );
      $import_products = array();
      $existing_uuids = null;
      
      while ($products = xtc_db_fetch_array($products_query)) {
        // reserved rows sort first and run alone, so a rejection cannot affect new products
        if ($products['products_uuid'] == '' && $existing_uuids !== null) {
          break;
        }

        // an earlier attempt got no answer, an import must not contain uuids Zettle already knows
        if ($products['products_uuid'] != '') {
          if ($existing_uuids === null) {
            $existing_products = $PayPalZettle->getAllProducts();
            if (isset($existing_products['error'])) {
              die();
            }
            $existing_uuids = array_column($existing_products, 'uuid');
          }
          if (in_array($products['products_uuid'], $existing_uuids)) {
            xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                             SET bulk = 1
                           WHERE zettle_id = '".(int)$products['zettle_id']."'");
            continue;
          }
        }

        $products['products_currency'] = DEFAULT_CURRENCY;
        $products['products_tax'] = xtc_get_tax_rate($products['products_tax_class_id']);
        $products['products_price'] = $xtPrice->xtcGetPrice($products['products_id'], false, 1, $products['products_tax_class_id'], $products['products_price']);
        $products['products_price'] *= 100;
        
        $data = $PayPalZettle->get_product_scheme($products, true);
        
        if ($products['products_uuid'] != '') {
          $data['uuid'] = $products['products_uuid'];
        }
        $import_products[$products['zettle_id']] = $data['uuid'];
  
        $products_array['products'][] = $data;
      }
      
      if (count($import_products) < 1) {
        die();
      }

      // reserved before the request, a lost answer must not create the products twice
      foreach ($import_products as $zettle_id => $products_uuid) {
        xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                         SET products_uuid = '".xtc_db_input($products_uuid)."',
                             bulk = IF(bulk < 0, bulk - 1, -1)
                       WHERE zettle_id = '".(int)$zettle_id."'");
      }

      $response = $PayPalZettle->setImport($products_array);
      
      if (isset($response['uuid'])) {
        $sql_data_array = array(
          'import_uuid' => $response['uuid']
        );
        xtc_db_perform(TABLE_PAYPAL_ZETTLE_IMPORT, $sql_data_array);
      } elseif (!$PayPalZettle->isTemporaryError($response)) {
        // Zettle rejected the batch, a retry would block all later steps
        xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                         SET products_uuid = '',
                             bulk = 0
                       WHERE zettle_id IN ('".implode("', '", array_map('intval', array_keys($import_products)))."')
                         AND ".$import_settled);
      }
      die();
    }
  
    
    // check
    $import_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_PAYPAL_ZETTLE_IMPORT);
    if (xtc_db_num_rows($import_query)) {
      while ($import = xtc_db_fetch_array($import_query)) {
        $response = $PayPalZettle->getImport($import['import_uuid']);
        
        // only a running import or a temporary failure is worth waiting for
        if ($PayPalZettle->isTemporaryError($response)
            || (!isset($response['error']) && (!isset($response['state']) || strpos($response['state'], 'FINISHED') !== 0))
            )
        {
          die();
        } else {
          // an unknown import has no state, its rows stay reserved for the retry
          if (isset($response['state'])) {
            $where = '';
            if ($response['state'] != 'FINISHED_SUCCESS') {
              // a failed import can still have created some of the products
              $existing_products = $PayPalZettle->getAllProducts();
              if (isset($existing_products['error'])) {
                die();
              }
              $where = " AND products_uuid IN ('".implode("', '", array_map('xtc_db_input', array_column($existing_products, 'uuid')))."')";
            }
            // queued whatever the stock setting, the product can have changed while it was reserved
            xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                             SET bulk = 1
                           WHERE bulk < 0".$where);
            xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                             SET products_uuid = '',
                                 bulk = 0
                           WHERE ".$import_settled);
          }
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
            || $response['status'] == 404
            )
        {
          xtc_db_query("DELETE FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." WHERE products_id = '".(int)$products['products_id']."'");
        } elseif (!$PayPalZettle->isTemporaryError($response)) {
          // Zettle refuses the delete, a retry would block all later steps
          xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                           SET bulk = 0
                         WHERE zettle_id = '".(int)$products['zettle_id']."'");
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
        
        $product_response = $PayPalZettle->updateProduct($products_uuid, $products);
        
        if ($products['stock'] == 1) {
          $inventory_response = $PayPalZettle->updateInventory($products_uuid, $products);
        } else {
          $inventory_response = $PayPalZettle->deleteInventory($products_uuid);
        }
  
        // retried on the next run, each operation works from the current state at Zettle
        if ($PayPalZettle->isTemporaryError($product_response)
            || $PayPalZettle->isTemporaryError($inventory_response)
            )
        {
          continue;
        }

        xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                         SET bulk = 0
                       WHERE zettle_id = '".(int)$products['zettle_id']."'");
      }    
    }
  }
