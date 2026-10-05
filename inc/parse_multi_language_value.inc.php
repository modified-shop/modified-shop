<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'split_multi_language_value.inc.php');

  function parse_multi_language_value($text, $lang_code, $admin=false) {    
    
    if (xtc_not_null($text)) {
      $lang_array = split_multi_language_value($text);
      
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