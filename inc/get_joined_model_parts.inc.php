<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // an order position without an own number holds its attribute numbers, joined by the delimiter of that time:
  // returns this delimiter and the numbers in $model, or false when $model is not made of the $attribute_models;
  // $stored: $model was read from an order position
  function get_joined_model_parts($model, $attribute_models, $stored = false) {
    $model = (string)$model;
    $length = strlen($model);

    $known = array();
    if (is_array($attribute_models)) {
      foreach ($attribute_models as $attribute_model) {
        if (is_scalar($attribute_model) && (string)$attribute_model != '') {
          $known[(string)$attribute_model] = strlen((string)$attribute_model);
        }
      }
    }
    if (count($known) < 1) {
      return false;
    }
    // the longest first, a number that holds a delimiter itself stays text
    arsort($known);

    // only the configured delimiter or a line break
    $delimiters = array();
    if (defined('ATTRIBUTE_MODEL_DELIMITER') && ATTRIBUTE_MODEL_DELIMITER != '') {
      $delimiters[] = ATTRIBUTE_MODEL_DELIMITER;
    }
    if (preg_match_all('/<br[\t\n\f\r ]*\/?>/i', $model, $matches)) {
      $delimiters = array_merge($delimiters, $matches[0]);
    }
    // a line break that was cut cannot be read from the value
    $delimiters = array_unique(array_merge($delimiters, array('<br />', '<br>', '<br/>')));

    // orders_products.products_model holds 64 characters, a longer joined value was cut when it was stored;
    // a character is a byte only where the database has no UTF-8 charset
    $cut = false;
    if ($stored === true) {
      if (defined('DB_SERVER_CHARSET') && strpos(DB_SERVER_CHARSET, 'utf8') === false) {
        $cut = ($length == 64);
      } else {
        $cut = (preg_match_all('/./su', $model) === 64);
      }
    }

    foreach ($delimiters as $delimiter) {
      $delimiter = (string)$delimiter;
      $delimiter_length = strlen($delimiter);

      // from the end: what stands at each place where a number begins
      $found = array();
      for ($pos = $length - 1; $pos >= 0; $pos--) {
        if ($pos > 0 && ($pos < $delimiter_length || substr($model, $pos - $delimiter_length, $delimiter_length) !== $delimiter)) {
          continue;
        }
        foreach ($known as $number => $number_length) {
          $number = (string)$number;
          if (substr($model, $pos, $number_length) !== $number) {
            continue;
          }
          $end = $pos + $number_length;
          $rest = (string)substr($model, $end);
          if ($rest === '') {
            $found[$pos] = array('part' => $number, 'number' => $number, 'next' => false);
            break;
          }
          if (isset($found[$end + $delimiter_length]) && strncmp($rest, $delimiter, $delimiter_length) === 0) {
            $found[$pos] = array('part' => $number, 'number' => $number, 'next' => $end + $delimiter_length);
            break;
          }
          // a cut value may end with the delimiter or a piece of it
          if ($cut === true && strncmp($delimiter, $rest, strlen($rest)) === 0) {
            $found[$pos] = array('part' => $number, 'number' => $number, 'next' => false, 'cut' => true);
            break;
          }
        }
        // or with the beginning of a number
        if (!isset($found[$pos]) && $cut === true && $pos > 0) {
          $rest = (string)substr($model, $pos);
          foreach ($known as $number => $number_length) {
            $number = (string)$number;
            if (strlen($rest) < $number_length && strncmp($number, $rest, strlen($rest)) === 0) {
              $found[$pos] = array('part' => $rest, 'number' => $number, 'next' => false, 'cut' => true);
              break;
            }
          }
        }
      }

      if (isset($found[0])) {
        $parts = array();
        $numbers = array();
        $pos = 0;
        $is_cut = false;
        while ($pos !== false) {
          $parts[] = $found[$pos]['part'];
          $numbers[] = $found[$pos]['number'];
          $is_cut = isset($found[$pos]['cut']);
          $pos = $found[$pos]['next'];
        }
        // a single number is no joined one
        if (count($parts) > 1 || $is_cut === true) {
          return array('delimiter' => $delimiter, 'parts' => $parts, 'numbers' => $numbers);
        }
      }
    }

    return false;
  }
