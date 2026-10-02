<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // only "XX::" at the start or after "||" marks a language, "::" and "||" in free text stay intact
  function split_multi_language_value($text) {
    $code = '[a-z]{2}(?:[-_][a-z]{2})?';

    $lang_array = array();
    if (!preg_match('/(?:^|\|\|)\s*'.$code.'\s*::/i', (string)$text)) {
      return $lang_array;
    }

    foreach (preg_split('/\|\|(?=\s*'.$code.'\s*::)/i', (string)$text) as $val) {
      if (preg_match('/^\s*('.$code.')\s*::(.*)$/is', $val, $match)
          && !empty($match[2])
          )
      {
        $lang_array[strtolower($match[1])] = trim($match[2]);
      }
    }

    return $lang_array;
  }
?>