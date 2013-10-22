<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function parse_email_language_value($text, $lang_code, $default=false) {    
    
    if (xtc_not_null($text)) {
      $text_array = explode("||",$text);
      $lang_text = '';
      foreach ($text_array as $val) {
        $val_array = explode ("::", $val);
        if (count($val_array) > 0) {
          if (trim(strtolower($val_array[0])) == $lang_code ) {
            $lang_text = trim($val_array[1]);
          } elseif ($default === true) {
            $lang_text = trim($val_array[1]);
          }
        }
        unset ($val_array);
      }
    
      if ($lang_text == '') {
        $lang_text = $text;    
        if (strpos($lang_text, '::') !== false) {
          $lang_text = xtc_email_lang($lang_text, $lang_code, true);
        }
      }
    } else {
      $lang_text = $text;
    }
    
    return $lang_text;
  }
?>