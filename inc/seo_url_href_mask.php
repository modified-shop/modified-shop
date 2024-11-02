<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  // include needed function
  include_once (DIR_FS_INC . 'search_replace_utf-8.php');

  // check iconv
  $check_iconv = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "test");


  function seo_url_href_mask($string, $urlencode = false, $charset = '') {
    global $check_iconv;

    static $char_search;
    static $char_replace;
    static $lang_charset;
    
    if (!is_array($char_search) || !is_array($char_replace)) {
      list($char_search, $char_replace) = shopstat_getRegExps();
    }
        
    $newstring = $string;
  
    if ($charset == '') {
      if (isset($_SESSION['language_charset'])) {
        $charset = strtoupper($_SESSION['language_charset']);
      } else {
        if (!isset($lang_charset)) {
          $language_query = xtDBquery("SELECT * 
                                         FROM ".TABLE_LANGUAGES." 
                                        WHERE code = '".xtc_db_input(DEFAULT_LANGUAGE)."'");
          $language = xtc_db_fetch_array($language_query, true);
          $lang_charset = $language['language_charset'];
        }
        $charset = $lang_charset;
      }
    }
  
    //-- convert to utf-8
    if ($charset != "UTF-8") {
      if (!$check_iconv) {
        $newstring = mb_convert_encoding($string, 'UTF-8', $charset);
      } else {
        $newstring = iconv($charset, "UTF-8", $newstring);
      }
    }

    //-- <br> remove
    $newstring  = preg_replace("/<br(\s+)?\/?>/i", "-", $newstring);

    //-- HTML remove
    $newstring  = strip_tags($newstring);
  
    //-- / remove
    if ($urlencode) {
      $newstring  = preg_replace("/\//", "-", $newstring);
    } else {
      $newstring  = preg_replace("/\s\/\s/", "-", $newstring);
    }

    //-- signs remove
    $newstring  = preg_replace($char_search, $char_replace, $newstring);
  
    //-- HTML remove
    $newstring  = html_entity_decode($newstring, ENT_NOQUOTES , "UTF-8");
  
    //-- & remove
    $newstring  = preg_replace("'&'", "-", $newstring);

    //-- invalid signs remove
    $newstring = preg_replace("/[^a-zA-Z0-9\/_-]/", '-', $newstring);

    //-- URL encode
    if ($urlencode) { 
      $newstring  = urlencode($newstring);
    }

    //-- double - remove
    $newstring  = preg_replace("/(-){2,}/", "-", $newstring);

    //-- remove last -
    $newstring = rtrim($newstring, "-");
    
    //-- convert to $charset
    if ($charset != "UTF-8") {
      if (!$check_iconv) {
        $newstring = mb_convert_encoding($newstring, $charset, 'UTF-8');
      } else {
        $newstring = iconv("UTF-8", $charset.'//TRANSLIT', $newstring);
      }  
    }

    return($newstring);
  }
?>