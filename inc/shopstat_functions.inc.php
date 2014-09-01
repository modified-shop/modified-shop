<?PHP
/*-----------------------------------------------------------------------
    $Id: shopstat_functions.inc.php 2522 2011-12-14 13:45:11Z dokuman $
    xtC-SEO-Module by www.ShopStat.com (Hartmut König)
    http://www.shopstat.com
    info@shopstat.com
    © 2004 ShopStat.com
    All Rights Reserved.

   Version 1.07 rev.06(c) by web28  - www.rpa-com.de
------------------------------------------------------------------------*/
//#################################

//-- Einstellungen für die Trennzeichen -   Doppelpunkt oder Minuszeichen
//-- Bei Minuszeichen wird eine spezielle htaccess Datei benötigt
define('SEO_SEPARATOR',':');
//define('SEO_SEPARATOR','-'); //.htaccess Datei entsprechend anpassen

//Sonderzeichen
define('SPECIAL_CHAR_FR', true);  //Französische Sonderzeichen
define('SPECIAL_CHAR_ES', true);  //Spanische/Italienische/Portugisische Sonderzeichen (nur aktivieren wenn auch französiche Sonderzeichen aktiviert sind)
define('SPECIAL_CHAR_MORE', true);  //Weitere Sonderzeichen

//-- Kategorienamen in Artikellink hinzufügen - Standard true
//-- false verbessert die Performance bei Shops mit sehr vielen Kategorien
//-- false erzeugt eindeutige Artikellinks bei verlinkten Artikeln
define('ADD_CAT_NAMES_TO_PRODUCT_LINK', true); // true false

//#################################

//BOF - web28 - 2010-08-18 -- Definition für die Trennzeichen
define('CAT_DIVIDER',SEO_SEPARATOR.SEO_SEPARATOR.SEO_SEPARATOR); //Kategorie ':::'
define('ART_DIVIDER',SEO_SEPARATOR.SEO_SEPARATOR);               //Artikel '::'
define('CNT_DIVIDER',SEO_SEPARATOR.'_'.SEO_SEPARATOR);           //Content ':_:'
define('MAN_DIVIDER',SEO_SEPARATOR.'.'.SEO_SEPARATOR);           //Hersteller ':.:'
define('PAG_DIVIDER',SEO_SEPARATOR);                             //Seitennummer ':'
//EOF - web28 - 2010-08-18 -- Definition für die Trennzeichen

if (file_exists(DIR_FS_INC . 'search_replace_'.strtolower($_SESSION['language_charset']) .'.php')) {
  include (DIR_FS_INC . 'search_replace_'.strtolower($_SESSION['language_charset']) .'.php');
} else {
  include (DIR_FS_INC . 'search_replace_default.php');
}

if(!function_exists('language')) {
  include_once (DIR_WS_CLASSES.'language.php');
}

function shopstat_getSEO($page='', $parameters='', $connection='NONSSL', $add_session_id=true, $search_engine_safe=true, $mode='user') {
  global $languages_id;
  $link = "";
  $maname = "";
  if($mode == 'admin') {
    require_once(DIR_FS_INC . 'xtc_parse_category_path.inc.php');
    require_once(DIR_FS_INC . 'xtc_get_product_path.inc.php');
    require_once(DIR_FS_INC . 'xtc_get_parent_categories.inc.php');
    require_once(DIR_FS_INC . 'xtc_check_agent.inc.php');
  } else {
    require_once(DIR_FS_INC . 'xtc_get_products_name.inc.php');
    require_once(DIR_FS_INC . 'xtc_get_manufacturers.inc.php');
  }

  //-- XTC
  (!isset($languages_id)) ? $languages_id = $_SESSION['languages_id'] : false;

  //BOF - web28 - 2010-08-18 -- Die Parameter aufspalten
  $pararray = array();
  foreach(explode("&",$parameters) as $pair) {
    $values = explode("=",$pair);
    if(!empty($values[0])) {
      $pararray[$values[0]] = $values[1];
    }
  }
  $cPath      = (isset($pararray['cPath']))?$pararray['cPath']:false;
  $prodid     = (isset($pararray['products_id']))?$pararray['products_id']:false;
  $content    = (isset($pararray['content']))?$pararray['content']:false;
  $coid       = (isset($pararray['coID']))?$pararray['coID']:false;
  $maid       = (isset($pararray['manufacturers_id']))?$pararray['manufacturers_id']:false;
  $pager      = (isset($pararray['page']))?$pararray['page']:false;
  $lang       = (isset($pararray['language']))?$pararray['language']:'';
  $sort       = (isset($pararray['sort']))?$pararray['sort']:'';
  $filter_id  = (isset($pararray['filter_id']))?$pararray['filter_id']:'';
  $action     = (isset($pararray['action']))?$pararray['action']:'';

  //EOF - web28 - 2010-08-18 -- Die Parameter aufspalten
  $go     = true;
  //-- Nur bei der index.php und product_info.php
  if ($page != "index.php" && $page != "product_info.php" && $page != "shop_content.php") {
    $go = false;
  } elseif (strlen($sort)>0) {
    //-- Unter diesen Bedingungen werden die URLs nicht umgewandelt
    //-- Sortieren
    $go = false;
  } elseif (strlen($filter_id)>0) {
    //-- Sortieren der Herstellerprodukte
    $go = false;
  } elseif (strlen($action)>0) {
    //-- Andere Aktion
    $go = false;
  }

  //BOF web28 - 2010-08-18 -- Falls eine Sprache übergeben wurde, wird diese als 'Linksprache' definiert
  if (strlen($lang)>0) {
    $seolng  = new language;
    $lang_id = $seolng->catalog_languages[$lang]['id'];
  } else {
    $lang_id    = $languages_id;
  }
  //EOF- web28 - 2010-08-18 -- Falls eine Sprache übergeben wurde, wird diese als 'Linksprache' definiert

  if ($go && (xtc_not_null($maid) || xtc_not_null($cPath) || xtc_not_null($prodid) || xtc_not_null($coid))) {
    if ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_CATALOG;
      }
    } else {
      $link = HTTP_SERVER . DIR_WS_CATALOG;
    }

    if ((xtc_not_null($cPath) || xtc_not_null($prodid))) {
      $cPath_array         = xtc_parse_category_path($cPath);
      $cPath               = implode('_', $cPath_array);
      $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];

      if (!$current_category_id && $prodid) {
        $current_category_id = xtc_get_product_path($prodid);
      }

      // -------------------------------------------------
      if (!$prodid) {
        $category['categories_name'] = shopstat_getRealPath($cPath,'/',$lang_id);
        $link .= shopstat_hrefCatlink($category['categories_name'], $cPath, $pager);
      } else {
        $category['categories_name'] = '';
        if (ADD_CAT_NAMES_TO_PRODUCT_LINK) {
          $category['categories_name'] = shopstat_getRealPath(xtc_get_product_path($prodid),'/',$lang_id);
        }
        $link .= shopstat_hrefLink($category['categories_name'], xtc_get_products_name($prodid,$lang_id), $prodid);
      }
    } elseif(xtc_not_null($coid)) {
      $content = shopstat_getContentName($coid, $lang_id);
      $link .= shopstat_hrefContlink($content, $coid);
    } elseif(xtc_not_null($maid)) {
      $manufacturers = xtc_get_manufacturers();
      foreach($manufacturers as $manufacturer) {
        if($manufacturer['id'] == $maid) {
          $maname = $manufacturer['text'];
          break;
        }
      }
      $link .= shopstat_hrefManulink($maname, $maid, $pager);
    }
    $separator  = '?';
    //-- Concat the lang-var
    //-- Check parameters and given language, just concat
    //-- if the language is different
    //web28 - 2010-08-18 -- Parameter für die Sprachumschaltung
    if (strlen($lang)>0 && $lang_id != $languages_id) {
      $link .= $separator.'language='. $lang;
    }
  }
  return($link);
}

/******************************************************
/*
 * FUNCTION shopstat_getRealPath
 * Get the 'breadcrumb'-path
 */
function shopstat_getRealPath($cPath, $delimiter = '/', $language = '') {
  if(empty($cPath)) {
    return;
  }
  if(empty($language)){
    $language = $_SESSION['languages_id'];
  }

  $path       = explode("_",$cPath);
  $categories = array();

  foreach($path as $key => $value) {
    $categories[$key] = shopstat_getCategoriesName($value, $language);
  }

  $realpath = implode($delimiter,$categories);
  return($realpath);
}

function shopstat_getContentName($coid, $language = '') {
  if(empty($coid)) {
    return;
  }
  if(empty($language)) {
    $language = $_SESSION['languages_id'];
  }
  $content_query  = "SELECT content_title FROM ".TABLE_CONTENT_MANAGER." WHERE languages_id='".(int)$language."' AND content_group = ".(int)$coid;
  $content_query  = xtDBquery($content_query);
  $content_data   = xtc_db_fetch_array($content_query, true);
  return($content_data['content_title']);
}

/*
 * FUNCTION shopstat_getCategoriesName
 * Get the Category-Name from a give CID
 */
function shopstat_getCategoriesName($categories_id, $language = '') {
  if(empty($categories_id)) {
    return;
  }
  if(empty($language)) {
    $language = $_SESSION['languages_id'];
  }
  $categories_query = "SELECT categories_name FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$language . "'";
  $categories_query   = xtDBquery($categories_query);
  $categories         = xtc_db_fetch_array($categories_query,true);
  return $categories['categories_name'];
}

/*
 * FUNCTION shopstat_hrefLink
 */
function shopstat_hrefLink($cat_desc, $product_name, $product_id) {
  $link = "";
  if (shopstat_hrefSmallmask($cat_desc)) {
    $link .= shopstat_hrefSmallmask($cat_desc)."/";
  }
  $link .= shopstat_hrefMask($product_name).ART_DIVIDER.$product_id.".html";
  return($link);
}

/*
 * FUNCTION shopstat_hrefCatlink
 */
function shopstat_hrefCatlink($category_name, $category_id, $pager=false) {
  $link = shopstat_hrefSmallmask($category_name).CAT_DIVIDER.$category_id;
  if ($pager && $pager != 1) {
    $link .= PAG_DIVIDER.$pager.".html";
  } else {
    $link .= ".html";
  }
  return($link);
}

/*
 * FUNCTION shopstat_hrefContlink
 */
function shopstat_hrefContlink($content_name, $content_id) {
  $link = shopstat_hrefMask($content_name). CNT_DIVIDER.$content_id.".html";
  return($link);
}

/*
 * FUNCTION shopstat_hrefManulink
 */
function shopstat_hrefManulink($content_name, $content_id, $pager=false) {
  $link = shopstat_hrefMask($content_name).MAN_DIVIDER.$content_id;
  if($pager && $pager != 1) {
    $link .= PAG_DIVIDER.$pager.".html";
  } else {
    $link .= ".html";
  }
  return($link);
}

/*
 * FUNCTION shopstat_hrefSmallmask
 */
function shopstat_hrefSmallmask($string) {
  shopstat_getRegExps($search, $replace);
  $newstring = $string;

  //web28 - 2010-08-17 - Eurozeichen ersetzen
  $newstring  = str_replace("&euro;", "-EUR-",$newstring);

  //web28 -2011-0812 - Geschütztes Leerzeichen entfernen - VOR html_entity_decode
  $newstring  = str_replace("&nbsp;", "-",$newstring);

  //web28 - 2010-08-18 -HTML-Codierung entfernen (&uuml; etc.)
  $newstring  = html_entity_decode($newstring, ENT_NOQUOTES , strtoupper($_SESSION['language_charset']));

  //-- <br> neutralisieren -  DokuMan - 2010-08-13 - optimize shopstat_getRegExps
  $newstring  = preg_replace("/<br(\s+)?\/?>/i","-",$newstring);

  //-- HTML entfernen
  $newstring  = strip_tags($newstring);

  //-- Schrägstriche entfernen
  $newstring  = preg_replace("/\s\/\s/","+",$newstring);

  //-- Definierte Zeichen entfernen
  $newstring  = preg_replace($search,$replace,$newstring);

  //--Anführungszeichen weg.
  $newstring  = preg_replace("/'|\"|´|`/","",$newstring);

  //-- Die nun noch (komisch aussehenden) doppelten Bindestriche entfernen
  $newstring  = preg_replace("/(-){2,}/","-",$newstring);

  //web28 - 2010-08-18 - Mögliches rechtstehendes Minuszeichen entfernen - wichtig für Minus Trennzeichen
  $newstring = rtrim($newstring,"-");

  //if($_REQUEST['test']){print $newstring."<hr>";}
  return($newstring);
}

/*
 * FUNCTION shopstat_hrefMask
 */
function shopstat_hrefMask($string) {
  shopstat_getRegExps($search, $replace);

  //BOF - DokuMan - 2010-08-13 - optimize shopstat_getRegExps
  $newstring = $string;

  //web28 - 2010-08-17 - Eurozeichen ersetzen
  $newstring  = str_replace("&euro;","-EUR-",$newstring);

  //web28 -2011-0812 - Geschütztes Leerzeichen entfernen  - VOR html_entity_decode
  $newstring  = str_replace("&nbsp;", "-",$newstring);

  //web28 - 2010-08-18 -HTML-Codierung entfernen (&uuml; etc.)
  $newstring  = html_entity_decode($newstring, ENT_NOQUOTES , strtoupper($_SESSION['language_charset']));

  //-- <br> neutralisieren - DokuMan - 2010-08-13 - optimize shopstat_getRegExps
  $newstring  = preg_replace("/<br(\s+)?\/?>/i","-",$newstring);

  //-- HTML entfernen
  $newstring  = strip_tags($newstring);

  //-- Schrägstriche entfernen
  $newstring  = preg_replace("/\//","-",$newstring);

  //-- Definierte Zeichen entfernen
  $newstring  = preg_replace($search,$replace,$newstring);

  //--Anführungszeichen weg.
  $newstring  = preg_replace("/'|\"|´|`/","",$newstring);

  //-- String URL-codieren
  $newstring  = urlencode($newstring);

  //-- Die nun noch (komisch aussehenden) doppelten Bindestriche entfernen
  $newstring  = preg_replace("/(-){2,}/","-",$newstring);

  //web28 - 2010-08-13 - Mögliches rechtstehendes Minuszeichen entfernen - wichtig für Minus Trennzeichen
  $newstring = rtrim($newstring,"-");

 //if($_REQUEST['test']){print $newstring."<hr>";}
  return($newstring);
}
?>