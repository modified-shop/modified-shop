<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'get_joined_model_parts.inc.php');

  // the joined attribute numbers in the model number of an order position, see get_joined_model_parts();
  // an own number of the product is its one part without numbers, false says that the stored data does not tell
  function get_order_model_parts($model, $orders_products_id) {
    $model = (string)$model;
    $numbers = array();
    $numbers_query = xtc_db_query("SELECT attributes_model
                                     FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                    WHERE orders_products_id = '".(int)$orders_products_id."'
                                 ORDER BY orders_products_attributes_id");
    while ($numbers_array = xtc_db_fetch_array($numbers_query)) {
      $numbers[] = $numbers_array['attributes_model'];
    }
    $joined = get_joined_model_parts($model, $numbers);

    // the checkout takes the own number of a product as it is; a cut value is looked for behind it
    if ($joined === false) {
      $model_query = xtc_db_query("SELECT p.products_model
                                     FROM ".TABLE_ORDERS_PRODUCTS." op
                                     JOIN ".TABLE_PRODUCTS." p
                                          ON p.products_id = op.products_id
                                    WHERE op.orders_products_id = '".(int)$orders_products_id."'");
      $model_array = xtc_db_fetch_array($model_query);
      if (is_array($model_array) && $model != '' && (string)$model_array['products_model'] === $model) {
        return array('delimiter' => '', 'parts' => array($model), 'numbers' => array());
      }
      $joined = get_joined_model_parts($model, $numbers, true);
    }

    // a position from before the numbers were stored, or one with attributes changed later, has those of the catalog
    if ($joined === false) {
      $numbers_query = xtc_db_query("SELECT pa.attributes_model
                                       FROM ".TABLE_ORDERS_PRODUCTS." op
                                       JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                            ON pa.products_id = op.products_id
                                      WHERE op.orders_products_id = '".(int)$orders_products_id."'
                                        AND pa.attributes_model != ''");
      if (xtc_db_num_rows($numbers_query) > 0) {
        while ($numbers_array = xtc_db_fetch_array($numbers_query)) {
          $numbers[] = $numbers_array['attributes_model'];
        }
        $joined = get_joined_model_parts($model, $numbers, true);
      }
    }

    return $joined;
  }
