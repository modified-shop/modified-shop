<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // a model number is text; $attribute_models are the attribute numbers stored with an order position
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

    // a position without an own number holds its attribute numbers, joined by the delimiter of that time
    if (count($parts) > 1
        && strlen($model) > strlen($parts[0])
        && strncmp($model, $parts[0], strlen($parts[0])) === 0
        )
    {
      // only a line break or the configured delimiter stays markup
      $delimiter = '';
      $rest = substr($model, strlen($parts[0]));
      if (preg_match('/^<br\s*\/?>/i', $rest, $match)) {
        $delimiter = $match[0];
      } elseif (defined('ATTRIBUTE_MODEL_DELIMITER')
                && ATTRIBUTE_MODEL_DELIMITER != ''
                && strncmp($rest, ATTRIBUTE_MODEL_DELIMITER, strlen(ATTRIBUTE_MODEL_DELIMITER)) === 0
                )
      {
        $delimiter = ATTRIBUTE_MODEL_DELIMITER;
      }

      // the column is shorter than two long numbers, so the stored value may be the start of the joined one
      if ($delimiter != '' && strncmp(implode($delimiter, $parts), $model, strlen($model)) === 0) {
        $encoded = '';
        $rest = $model;
        foreach ($parts as $key => $part) {
          foreach ((($key > 0) ? array($delimiter, $part) : array($part)) as $index => $token) {
            $piece = (string)substr($rest, 0, strlen($token));
            $rest = (string)substr($rest, strlen($token));
            // a delimiter that was cut is text like the numbers
            $encoded .= (($key > 0 && $index == 0 && $piece === $token) ? $piece : htmlspecialchars($piece, $flags, $encoding, false));
          }
        }

        return $encoded;
      }
    }

    return htmlspecialchars($model, $flags, $encoding, false);
  }
