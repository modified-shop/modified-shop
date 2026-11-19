<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // a model number is text, only the delimiter that joins the attribute numbers of an order position is markup
  function encode_model_number($model) {
    $encoding = get_default_encoding('');
    $model = htmlspecialchars((string)$model, ENT_QUOTES | ENT_SUBSTITUTE, $encoding, false);

    // older orders may still carry the default delimiter
    $delimiters = array('<br />');
    if (defined('ATTRIBUTE_MODEL_DELIMITER')) {
      $delimiters[] = ATTRIBUTE_MODEL_DELIMITER;
    }
    foreach (array_unique($delimiters) as $delimiter) {
      $encoded_delimiter = htmlspecialchars($delimiter, ENT_QUOTES | ENT_SUBSTITUTE, $encoding, false);
      if ($encoded_delimiter != '' && $encoded_delimiter != $delimiter) {
        $model = str_replace($encoded_delimiter, $delimiter, $model);
      }
    }

    return $model;
  }
