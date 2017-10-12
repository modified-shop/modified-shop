<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function parse_multi_language_value($text, $lang_code, $admin=false) {    
    
    if (xtc_not_null($text)) {
      $text_array = explode("||",$text);
      $lang_array = array();
      foreach ($text_array as $val) {
        $val_array = explode ("::", $val);
        if (count($val_array) == 2) {
          if (!empty($val_array[1])) {
            $lang_array[trim(strtolower($val_array[0]))] = trim($val_array[1]);
          }
        }
        unset ($val_array);
      }
      
      if (count($lang_array) == 0) {
        if ($admin === true && $lang_code == DEFAULT_LANGUAGE) {
          return decode_htmlentities($text);
        } elseif ($admin === false) {
          return decode_htmlentities($text);
        }
      }
      
      if (isset($lang_array[$lang_code])) {
        return decode_htmlentities($lang_array[$lang_code]);
      } elseif ($admin === false) {
        if (isset($lang_array['en'])) {
          return decode_htmlentities($lang_array['en']);
        } elseif (isset($lang_array[DEFAULT_LANGUAGE])) {
          return decode_htmlentities($lang_array[DEFAULT_LANGUAGE]);
        } else {
          return decode_htmlentities(array_shift($lang_array));
        }
      }
    }
    
    return '';
  }
?>