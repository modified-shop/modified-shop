<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // a model number is text; $attribute_models are the attribute numbers of an order position
  function encode_model_number($model, $attribute_models = array(), $flags = ENT_QUOTES) {
    $model = (string)$model;
    $encoding = get_default_encoding('');
    $flags = $flags | ENT_SUBSTITUTE;

    $parts = array();
    if (is_array($attribute_models)) {
      foreach ($attribute_models as $attribute_model) {
        if (is_scalar($attribute_model) && (string)$attribute_model != '') {
          $parts[] = (string)$attribute_model;
        }
      }
    }

    // a position without an own number holds exactly its attribute numbers, joined by the delimiter of that time
    $length = strlen($model) - strlen(implode('', $parts));
    if (count($parts) > 1 && $length > 0 && $length % (count($parts) - 1) == 0) {
      $delimiter = substr($model, strlen($parts[0]), (int)($length / (count($parts) - 1)));
      // only a line break or the configured delimiter stays markup
      if (implode($delimiter, $parts) === $model
          && (preg_match('/^<br\s*\/?>$/iD', $delimiter)
              || (defined('ATTRIBUTE_MODEL_DELIMITER') && $delimiter === ATTRIBUTE_MODEL_DELIMITER)
              )
          )
      {
        foreach ($parts as $key => $part) {
          $parts[$key] = htmlspecialchars($part, $flags, $encoding, false);
        }

        return implode($delimiter, $parts);
      }
    }

    return htmlspecialchars($model, $flags, $encoding, false);
  }
