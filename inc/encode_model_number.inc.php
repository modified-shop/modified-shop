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
    // the characters to encode have the same bytes in every charset of the shop, so the bytes of the
    // value stay as stored, whatever the charset of the page or mail is; entities of HTML5 count as existing
    $flags = $flags | ENT_HTML5;
    $charset = 'ISO-8859-1';

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
      // only the configured delimiter or a line break stays markup
      $delimiters = array();
      if (defined('ATTRIBUTE_MODEL_DELIMITER') && ATTRIBUTE_MODEL_DELIMITER != '') {
        $delimiters[] = ATTRIBUTE_MODEL_DELIMITER;
      }
      if (preg_match('/^<br\s*\/?>/i', substr($model, strlen($parts[0])), $match)) {
        $delimiters[] = $match[0];
      }
      // a line break that was cut cannot be read from the value
      $delimiters = array_unique(array_merge($delimiters, array('<br />', '<br>', '<br/>')));

      foreach ($delimiters as $delimiter) {
        // orders_products.products_model holds 64 characters, a longer joined value was cut there:
        // at 64 characters of the stored text, or at 64 bytes where the column counts bytes
        $joined = implode($delimiter, $parts);
        $cuts = array(substr($joined, 0, 64));
        if (function_exists('mb_substr') && mb_check_encoding($joined, 'UTF-8')) {
          $cuts[] = mb_substr($joined, 0, 64, 'UTF-8');
        }
        if ($model === $joined || in_array($model, $cuts, true)) {
          $encoded = '';
          $rest = $model;
          foreach ($parts as $key => $part) {
            if ($key > 0) {
              // the delimiter stands between two numbers, a cut value may end with it or a piece of it
              $piece = (string)substr($rest, 0, strlen($delimiter));
              $rest = (string)substr($rest, strlen($delimiter));
              if ($piece !== $delimiter || $rest === '') {
                break;
              }
              $encoded .= $delimiter;
            }
            $encoded .= htmlspecialchars((string)substr($rest, 0, strlen($part)), $flags, $charset, false);
            $rest = (string)substr($rest, strlen($part));
          }

          return $encoded;
        }
      }
    }

    return htmlspecialchars($model, $flags, $charset, false);
  }
