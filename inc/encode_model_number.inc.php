<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // a model number is text; $composed is for an order position, which may hold attribute numbers joined by a delimiter
  function encode_model_number($model, $composed = false, $flags = ENT_QUOTES) {
    $model = (string)$model;
    $encoding = get_default_encoding('');
    $flags = $flags | ENT_SUBSTITUTE;

    if ($composed !== true) {
      return htmlspecialchars($model, $flags, $encoding, false);
    }

    // older orders may carry the default delimiter
    $delimiters = array('<br />');
    if (defined('ATTRIBUTE_MODEL_DELIMITER') && ATTRIBUTE_MODEL_DELIMITER != '') {
      $delimiters[] = ATTRIBUTE_MODEL_DELIMITER;
    }
    $delimiters = array_unique($delimiters);
    rsort($delimiters);
    foreach ($delimiters as $key => $delimiter) {
      $delimiters[$key] = preg_quote($delimiter, '/');
    }

    // split first, so that only a stored delimiter stays markup
    $parts = preg_split('/('.implode('|', $delimiters).')/', $model, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts)) {
      return htmlspecialchars($model, $flags, $encoding, false);
    }
    foreach ($parts as $key => $part) {
      if ($key % 2 == 0) {
        $parts[$key] = htmlspecialchars($part, $flags, $encoding, false);
      }
    }

    return implode('', $parts);
  }
