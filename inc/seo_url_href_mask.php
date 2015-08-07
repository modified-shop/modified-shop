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
    
    if (!is_array($char_search) || !is_array($char_replace)) {
      list($char_search, $char_replace) = shopstat_getRegExps();
    }
    
    if ($check_iconv = '') {
      $check_iconv = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "test");
    }
    
    $newstring = $string;
  
    if ($charset == '') {
      $charset = strtoupper($_SESSION['language_charset']);
    }
  
    //$newstring grundsätzlich VOR html_entity_decode und preg_replace nach utf-8 konvertieren
    if ($charset != "UTF-8") {
      if (!$check_iconv) {
        $newstring = mb_convert_encoding($string, 'UTF-8', $charset);
      } else {
        $newstring = iconv($charset, "UTF-8", $newstring);
      }
    }

    //-- <br> neutralisieren -  DokuMan - 2010-08-13 - optimize shopstat_getRegExps
    $newstring  = preg_replace("/<br(\s+)?\/?>/i","-",$newstring);

    //-- HTML entfernen
    $newstring  = strip_tags($newstring);
  
    //-- Schrägstriche entfernen
    if ($urlencode) {
      $newstring  = preg_replace("/\//","-",$newstring);
    } else {
      $newstring  = preg_replace("/\s\/\s/","-",$newstring);
    }

    //-- Definierte Zeichen entfernen
    $newstring  = preg_replace($char_search, $char_replace, $newstring);
  
    //--Restliche HTML-Codierungen entfernen
    $newstring  = html_entity_decode($newstring, ENT_NOQUOTES , "UTF-8");
  
    //--Restliche Kaufmännische Und entfernen
    $newstring  = preg_replace("'&'","-",$newstring);

    //Alles entfernen ausser Buchstaben, Zahlen, Slash, Unterstrich, Minus
    $newstring = preg_replace("/[^a-zA-Z0-9\/_-]/", '-', $newstring);

    //-- String URL-codieren
    if ($urlencode) { 
      $newstring  = urlencode($newstring);
    }

    //-- Doppelte Bindestriche entfernen
    $newstring  = preg_replace("/(-){2,}/","-",$newstring);

    //-- Mögliches rechtstehendes Minuszeichen entfernen - wichtig f¸r Minus Trennzeichen
    $newstring = rtrim($newstring,"-");
    
    //string wieder auf $charset zurückkonvertieren, es sollten sich aber keine Sonderzeichen mehr im String befinden
    if ($charset != "UTF-8") {
      if (!$check_iconv) {
        $newstring = mb_convert_encoding($newstring, $charset, 'UTF-8');
      } else {
        $newstring = iconv("UTF-8", $charset.'//TRANSLIT', $newstring);
      }  
    }
    //if($_REQUEST['test']){print $newstring."<hr>";}
    return($newstring);
  }

  ?>