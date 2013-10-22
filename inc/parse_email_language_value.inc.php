<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function parse_email_language_value($text, $lang_code, $admin=false) {    
    
    if (xtc_not_null($text)) {
      $text_array = explode("||",$text);
      $lang_text = '';
      $default = '';
      foreach ($text_array as $val) {
        $val_array = explode ("::", $val);
        if (count($val_array) > 0) {
          if (trim(strtolower($val_array[0])) == $lang_code && !empty(trim($val_array[1]))) {
            $lang_text = trim($val_array[1]);
            break;
          } elseif (!empty(trim($val_array[1])) && empty($default)) {
            $default = trim($val_array[1]);
          }
        }
        unset ($val_array);
      }
    
      if ($lang_text == '' && $admin === false && !empty($default)) {
        $lang_text = $default;    
      } elseif ($lang_text == '' && $admin === false) {
        $lang_text = $text;
      }
    } else {
      $lang_text = $text;
    }
    
    return $lang_text;
  }
?>