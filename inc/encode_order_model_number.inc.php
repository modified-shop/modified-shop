<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'encode_model_number.inc.php');
  require_once(DIR_FS_INC.'get_order_model_parts.inc.php');

  // the model number of an order position, see encode_model_number()
  function encode_order_model_number($model, $orders_products_id, $flags = ENT_QUOTES) {
    $model = (string)$model;
    $encoded = encode_model_number($model, array(), $flags);

    // without a character to encode or the beginning of a delimiter there is no delimiter to keep
    $delimiter = ((defined('ATTRIBUTE_MODEL_DELIMITER') && ATTRIBUTE_MODEL_DELIMITER != '') ? substr(ATTRIBUTE_MODEL_DELIMITER, 0, 1) : '<');
    if ($encoded !== $model && (strpos($model, '<') !== false || strpos($model, $delimiter) !== false)) {
      $joined = get_order_model_parts($model, $orders_products_id);
      if ($joined !== false) {
        $encoded = encode_model_number($model, $joined['numbers'], $flags);
      }
    }

    return $encoded;
  }
