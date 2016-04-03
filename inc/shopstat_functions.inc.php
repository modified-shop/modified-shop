<?PHP
/*-----------------------------------------------------------------------
    $Id: shopstat_functions.inc.php 2522 2011-12-14 13:45:11Z dokuman $
    xtC-SEO-Module by www.ShopStat.com (Hartmut König)
    http://www.shopstat.com
    info@shopstat.com
    © 2004 ShopStat.com
    All Rights Reserved.

   Version 1.07 rev.07(c) by web28  - www.rpa-com.de
------------------------------------------------------------------------*/
//#################################

//-- Einstellungen für die Trennzeichen -   Doppelpunkt oder Minuszeichen
//-- Bei Minuszeichen wird eine spezielle htaccess Datei benötigt
defined('SEO_SEPARATOR') OR define('SEO_SEPARATOR',':');
//define('SEO_SEPARATOR','-'); //.htaccess Datei entsprechend anpassen

//Sonderzeichen
defined('SPECIAL_CHAR_FR') OR define('SPECIAL_CHAR_FR', true);  //Französische Sonderzeichen
defined('SPECIAL_CHAR_ES') OR define('SPECIAL_CHAR_ES', true);  //Spanische/Italienische/Portugisische Sonderzeichen (nur aktivieren wenn auch französiche Sonderzeichen aktiviert sind)
defined('SPECIAL_CHAR_PL') OR define('SPECIAL_CHAR_PL', true);  //Polnische Sonderzeichen (nur aktivieren wenn auch französiche Sonderzeichen aktiviert sind)
defined('SPECIAL_CHAR_CZ') OR define('SPECIAL_CHAR_CZ', true);  //Tschechische Sonderzeichen (nur aktivieren wenn auch französiche und polnische Sonderzeichen aktiviert sind)
defined('SPECIAL_CHAR_MORE') OR define('SPECIAL_CHAR_MORE', true);  //Weitere Sonderzeichen

//-- Kategorienamen in Artikellink hinzufügen - Standard true
//-- false verbessert die Performance bei Shops mit sehr vielen Kategorien
//-- false erzeugt eindeutige Artikellinks bei verlinkten Artikeln
defined('ADD_CAT_NAMES_TO_PRODUCT_LINK') OR define('ADD_CAT_NAMES_TO_PRODUCT_LINK', true); // true false

//#################################

//BOF - web28 - 2010-08-18 -- Definition für die Trennzeichen
define('CAT_DIVIDER',SEO_SEPARATOR.SEO_SEPARATOR.SEO_SEPARATOR); //Kategorie ':::'
define('ART_DIVIDER',SEO_SEPARATOR.SEO_SEPARATOR);               //Artikel '::'
define('CNT_DIVIDER',SEO_SEPARATOR.'_'.SEO_SEPARATOR);           //Content ':_:'
define('MAN_DIVIDER',SEO_SEPARATOR.'.'.SEO_SEPARATOR);           //Hersteller ':.:'
define('PAG_DIVIDER',SEO_SEPARATOR);                             //Seitennummer ':'
//EOF - web28 - 2010-08-18 -- Definition für die Trennzeichen

include_once (DIR_FS_INC . 'seo_url_href_mask.php');

if(!function_exists('language')) {
  include_once (DIR_WS_CLASSES.'language.php');
}

function shopstat_getSEO($page='', $parameters='', $connection='NONSSL', $add_session_id=true, $search_engine_safe=true, $mode='user') {
  global $languages_id;
  
  $link = $maname = '';
  
  if ($mode == 'admin') {
    require_once(DIR_FS_INC . 'xtc_parse_category_path.inc.php');
    require_once(DIR_FS_INC . 'xtc_get_product_path.inc.php');
    require_once(DIR_FS_INC . 'xtc_get_parent_categories.inc.php');
    require_once(DIR_FS_INC . 'xtc_check_agent.inc.php');
  } else {
    require_once(DIR_FS_INC . 'xtc_get_products_name.inc.php');
  }

  require_once(DIR_FS_INC . 'xtc_get_manufacturers.inc.php');

  //-- XTC
  (!isset($languages_id)) ? $languages_id = $_SESSION['languages_id'] : false;

  //Die Parameter aufspalten
  parse_str($parameters, $pararray);
  
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
  $show       = (isset($pararray['show']))?$pararray['show']:'';

  $go = true;
  //-- Nur bei der index.php und product_info.php
  if ($page != 'index.php' && $page != 'product_info.php' && $page != 'shop_content.php') {
    $go = false;
  } elseif ($sort != '') {
    //-- Unter diesen Bedingungen werden die URLs nicht umgewandelt
    //-- Sortieren
    $go = false;
  } elseif ($filter_id != '') {
    //-- Sortieren der Herstellerprodukte
    //$go = false;
  } elseif ($action != '') {
    //-- Andere Aktion
    $go = false;
  } elseif (strpos($prodid,'{') !== false) {
    //-- Produkt mit Attributen
    $go = false;
  } elseif ($show != '') {
    //$go = false;
  }

  //BOF web28 - 2010-08-18 -- Falls eine Sprache übergeben wurde, wird diese als 'Linksprache' definiert
  if (strlen($lang) > 0) {
    $seolng  = new language;
    $lang_id = $seolng->catalog_languages[$lang]['id'];
  } else {
    $lang_id = $languages_id;
  }
  //EOF- web28 - 2010-08-18 -- Falls eine Sprache übergeben wurde, wird diese als 'Linksprache' definiert

  if ($go === true && (xtc_not_null($maid) || xtc_not_null($cPath) || xtc_not_null($prodid) || xtc_not_null($coid))) {
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

      $category = array('categories_name' => '');
      if ($prodid === false) {
        $category['categories_name'] = shopstat_getRealPath($cPath, '/', $lang_id);
        $link .= shopstat_hrefCatlink($category['categories_name'], $cPath, $pager);
      } else {
        if (ADD_CAT_NAMES_TO_PRODUCT_LINK) {
          $category['categories_name'] = shopstat_getRealPath(xtc_get_product_path($prodid), '/', $lang_id);
        }
        $link .= shopstat_hrefLink($category['categories_name'], xtc_get_products_name($prodid, $lang_id), $prodid);
      }
    } elseif (xtc_not_null($coid)) {
      $content = shopstat_getContentName($coid, $lang_id);
      $link .= shopstat_hrefContlink($content, $coid);
    } elseif (xtc_not_null($maid)) {
      $manufacturers = xtc_get_manufacturers();      
      $maname = $manufacturers[$maid]['text'];        
      $link .= shopstat_hrefManulink($maname, $maid, $pager);
    }
    $separator  = '?';
    //-- Concat the lang-var
    //-- Check parameters and given language, just concat
    //-- if the language is different
    //web28 - 2010-08-18 -- Parameter für die Sprachumschaltung und hreflang
    //if (strlen($lang)>0 && $lang_id != $languages_id) {
    if (strlen($lang) > 0) {
      $link .= $separator.'language='. $lang;
    }

    // unset not needed params
    unset($pararray['language']);
    unset($pararray['cPath']);
    unset($pararray['manufacturers_id']);
    unset($pararray['products_id']);
    unset($pararray['coID']);
    unset($pararray['page']);
    unset($pararray['content']);
    unset($pararray['product']);
    
    if (count($pararray) > 0) {
      $link .= $separator.http_build_query($pararray, '', '&');
      $separator  = '&';
    }
  }
  
  return $link;
}

/******************************************************
/*
 * FUNCTION shopstat_getRealPath
 * Get the 'breadcrumb'-path
 */
function shopstat_getRealPath($cPath, $delimiter = '/', $language = '') {
  static $realpath_cache;

  if (!is_array($realpath_cache)) {
    $realpath_cache = array();
  }
  
  if (empty($cPath)) {
    return;
  }
  
  if (empty($language)){
    $language = $_SESSION['languages_id'];
  }

  if (!isset($realpath_cache[$cPath][$language])) {  
    $path       = explode("_",$cPath);
    $categories = array();

    foreach($path as $key => $value) {
      $categories[$key] = shopstat_getCategoriesName($value, $language);
    }

    $realpath = implode($delimiter, $categories);
    $realpath_cache[$cPath][$language] = $realpath;
  }
  
  return $realpath_cache[$cPath][$language];
}

function shopstat_getContentName($coid, $language = '') {
  static $content_title_cache;
  
  if (!is_array($content_title_cache)) {
    $content_title_cache = array();
  }
  
  if (empty($coid)) {
    return;
  }
  
  if (empty($language)) {
    $language = $_SESSION['languages_id'];
  }
  
  if (!isset($content_title_cache[$coid][$language])) {  
    $content_query = xtDBquery("SELECT content_title 
                                  FROM ".TABLE_CONTENT_MANAGER." 
                                 WHERE languages_id='".(int)$language."' 
                                   AND content_group = ".(int)$coid);
    $content_data = xtc_db_fetch_array($content_query, true);
    $content_title_cache[$coid][$language] = $content_data['content_title'];
  }
  
  return $content_title_cache[$coid][$language];
}

/*
 * FUNCTION shopstat_getCategoriesName
 * Get the Category-Name from a give CID
 */
function shopstat_getCategoriesName($categories_id, $language = '') {
  static $categories_name_cache;

  if (!is_array($categories_name_cache)) {
    $categories_name_cache = array();
  }

  if (empty($categories_id)) {
    return;
  }
  
  if (empty($language)) {
    $language = $_SESSION['languages_id'];
  }
  
  if (!isset($categories_name_cache[$categories_id][$language])) { 
    $categories_query = xtDBquery("SELECT categories_name 
                                     FROM " . TABLE_CATEGORIES_DESCRIPTION . " 
                                    WHERE categories_id = '" . (int)$categories_id . "' 
                                      AND language_id = '" . (int)$language . "'");
    $categories = xtc_db_fetch_array($categories_query, true);
    $categories_name_cache[$categories_id][$language] = $categories['categories_name'];
  }
  
  return $categories_name_cache[$categories_id][$language];
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
  
  return $link;
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

  return $link;
}

/*
 * FUNCTION shopstat_hrefContlink
 */
function shopstat_hrefContlink($content_name, $content_id) {
  $link = shopstat_hrefMask($content_name). CNT_DIVIDER.$content_id.".html";

  return $link;
}

/*
 * FUNCTION shopstat_hrefManulink
 */
function shopstat_hrefManulink($content_name, $content_id, $pager=false) {
  $link = shopstat_hrefMask($content_name).MAN_DIVIDER.$content_id;
  if ($pager && $pager != 1) {
    $link .= PAG_DIVIDER.$pager.".html";
  } else {
    $link .= ".html";
  }

  return $link;
}

/*
 * FUNCTION shopstat_hrefSmallmask
 */
function shopstat_hrefSmallmask($string, $urlencode = false) {
  return seo_url_href_mask($string, $urlencode);
}

/*
 * FUNCTION shopstat_hrefMask
 */
function shopstat_hrefMask($string) {
  return shopstat_hrefSmallmask($string, true);  
}
?>