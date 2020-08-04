<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
    (c) 2003 nextcommerce (metatags.php, v1.7 2003/08/14); www.nextcommerce.org
    (c) 2006 xt:Commerce (metatags.php, v.1140 2005/08/10); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------
   Modified by Gunnar Tillmann (August 2006)
   http://www.gunnart.de
   ---------------------------------------------------------------------------------------
    AUTOMATISCHE METATAGS MULTILANGUAGE für xt:Commerce 3.04
   ---------------------------------------------------------------------------------------
      Version 0.96n / 13. Dezember 2010 / DokuMan / modified eCommerce Shopsoftware

    -  Unterstützung für Pagination
   ---------------------------------------------------------------------------------------
      Version 0.96m / 26. August 2010 / DokuMan / modified eCommerce Shopsoftware

    -  Unterstützung für "canonical"-Tag
   ---------------------------------------------------------------------------------------
      Version 0.96 / 21. Juni 2009

    -  Umwandlung von Umlauten in Keywords statt in ae und oe JETZT in &auml; &ouml;
    -  "Bindestrich-Wörter" (z.B. T-Shirt oder DVD-Player) werden in den Keywords nicht
       mehr getrennt
    -  Metatags auch für ContentManager-Seiten (Achtung! Dazu Erweiterung erforderlich!)
    -  Im ContentManager können auch automatische Metatags aus eingebundenen HTML- oder
       Text-Dateien erzeugt werden
    -  Standard-Meta-Angaben durch Content-Metas auch mehrsprachig möglich. Dazu eine
       Seite namens "STANDARD_META" anlegen
    -  Bei automatisch erzeugen Keywords oder Descriptions werden Wörter nach Zeilen-
       umbrüchen nicht mehr "zusammengezogen"
    -  Eigene (mehrsprachige) Metas für die Shop-Startseite möglich - Dazu werden die
       Metas aus der "index"-Seite im ContentManager geholt
    -  Seiten-Nummer im Title bei Artikel-Listen (also Kategorien, Sonderangebote etc.)
    -  Eigener Title bei Suchergebnissen (Mit Seiten-Nummer, Suchbegriff, ggf. Hersteller
       und Kategorienname)
    -  Bei allen Seiten, die nicht "Kategorie", "Startseite", "Content", "Produkt" o.ä.
       sind, wird der Title aus den Einträgen im $breadcrumb-Objekt zusammengesetzt
    -  BugFix: BreadCrumb wird nicht mehr verkürzt
   ---------------------------------------------------------------------------------------
    Inspired by "Dynamic Meta" - Ein WordPress-PlugIn von Michael Schwarz
    http://www.php-vision.de/plugins-scripte/dynamicmeta-wpplugin.php
   ---------------------------------------------------------------------------------------*/


  // config
  $metaStopWords        = META_STOP_WORDS;
  $metaGoWords          = META_GO_WORDS;
  $metaMinLength        = META_MIN_KEYWORD_LENGTH; // min length keywords
  $metaMaxLength        = META_MAX_KEYWORD_LENGTH; // max length keywords
  $metaMaxKeywords      = META_KEYWORDS_NUMBER;    // may keywords
  $metaDesLength        = META_DESCRIPTION_LENGTH; // max length "description" 
  $addPagination        = true;
  $addCatShopTitle      = ((META_CAT_SHOP_TITLE == 'true') ? true : false);      // add title to categories
  $addProdShopTitle     = ((META_PROD_SHOP_TITLE == 'true') ? true : false);     // add title to products
  $addContentShopTitle  = ((META_CONTENT_SHOP_TITLE == 'true') ? true : false);  // add title to content
  $addSpecialsShopTitle = ((META_SPECIALS_SHOP_TITLE == 'true') ? true : false); // add title to specials
  $addNewsShopTitle     = ((META_NEWS_SHOP_TITLE == 'true') ? true : false);     // add title to new products
  $addSearchShopTitle   = ((META_SEARCH_SHOP_TITLE == 'true') ? true : false);   // add title to search
  $addOthersShopTitle   = ((META_OTHER_SHOP_TITLE == 'true') ? true : false);    // add title to other
  $noIndexUnimportant   = true;
  $set_hreflang         = true;
  
  // pages to index
  $pagesToShow = array(
    FILENAME_DEFAULT,
    FILENAME_PRODUCT_INFO,
    FILENAME_CONTENT,
    FILENAME_SPECIALS,
    FILENAME_PRODUCTS_NEW
  );
  
  // needed functions
  function metaNoEntities($Text){ 
    return decode_htmlentities($Text);  
  }  

  function prepareWordArray($Text) {
    $Text = str_replace(array('&nbsp;', '\t', '\r', '\n', '\b'), ' ', preg_replace("/<[^>]*>/", ' ', $Text));
    $Text = encode_htmlentities(metaNoEntities($Text), ENT_QUOTES, $_SESSION['language_charset']);    
    $Text = preg_replace("/\s\-|\-\s/", ' ', $Text);
    $Text = strtolower($Text);
    $Text = preg_replace("/[^0-9a-z|\-|&|;]/", ' ', $Text);
    $Text = str_replace('& ', '&', $Text);    
    $Text = trim(preg_replace("/\s\s+/", ' ', $Text));
  
    return $Text;
  }

  function makeWordArray($Text) {
    $Text = func_get_args();
    $Words = array();
    foreach($Text as $Word) {
      if (!empty($Word) && is_string($Word)) {
        $Words = array_merge($Words, explode(' ', $Word));
      }
    }
    return array_unique($Words);
  }

  function WordArray($Text) {
    return makeWordArray(prepareWordArray($Text));
  }

  function cleanKeyWords($KeyWords) {
    global $metaStopWords;
  
    $KeyWords = WordArray($KeyWords);
    $StopWords = WordArray($metaStopWords);
    $KeyWords = array_diff($KeyWords, $StopWords);
    $KeyWords = array_filter($KeyWords, "filterKeyWordArray");
    return $KeyWords;
  }

  function filterKeyWordArray($KeyWord) {
    global $metaMinLength, $metaMaxLength, $metaGoWords;
  
    $GoWords = WordArray($metaGoWords);
    if (!in_array($KeyWord,$GoWords)) {
      $Length = strlen(preg_replace("/(&[^;]*;)/", '#', $KeyWord));
      if ($Length < $metaMinLength) {
        return false;
      } elseif ($Length > $metaMaxLength) {
        return false;
      }
    }
    return true;
  }

  function getGoWords(){
    global $metaGoWords, $categories_meta, $product;
  
    $GoWords = $metaGoWords.' '.ML_META_KEYWORDS.' '.ML_META_TITLE;
    return $GoWords;
  }

  function metaClean($Text, $Length = false, $Abk = ' ...') {
    $Text = preg_replace("/<[^>]*>/", ' ', $Text);
    $Text = metaNoEntities($Text);
    $Text = str_replace(array('&nbsp;', '\t', '\r', '\n', '\b'), ' ', $Text);
    $Text = trim(preg_replace("/\s\s+/", ' ', $Text));
    if ($Length > 0) {
      if (strlen($Text) > $Length) {
        $Length -= strlen($Abk);
        $Text = preg_replace('/\s+?(\S+)?$/', '', substr($Text, 0, $Length + 1));
        $Text = substr($Text, 0, $Length).$Abk;
      }
    }
    $Text = encode_htmlspecialchars($Text, ENT_QUOTES, $_SESSION['language_charset']);
    return str_replace('&amp;', '&', $Text); 
  }

  function metaTitle($Title = array()) {
    $Title = func_get_args();
    $Title = array_filter($Title, "metaClean");
    return implode(' - ', $Title);
  }

  function metaKeyWords($Text) {
    global $metaMaxKeywords;
  
    $KeyWords = cleanKeyWords($Text);
    if (count($KeyWords)  > $metaMaxKeywords) {
      $KeyWords = array_slice($KeyWords, 0, $metaMaxKeywords);
    }
    return implode(', ', $KeyWords);
  }

  // default title
  $breadcrumbTitle = end($breadcrumb->_trail);
  $breadcrumbTitle = $breadcrumbTitle['title'];

  // meta robots
  $meta_robots = META_ROBOTS;
  if ($noIndexUnimportant && !in_array(basename($PHP_SELF), $pagesToShow)) {
    $meta_robots = 'noindex, nofollow, noodp';
  }

  // set standard metas
  if (basename($PHP_SELF) == FILENAME_DEFAULT 
      && (!isset($_GET['cat']) || $_GET['cat'] == '')
      && (!isset($_GET['cPath']) || $_GET['cPath'] == '')
      && (!isset($_GET['manufacturers_id']) || $_GET['manufacturers_id'] == '')
      )
  {
    define('META_TITLE', TITLE);
    $ml_meta_query = xtDBquery("SELECT content_meta_title as ml_meta_title,
                                       content_meta_description as ml_meta_description,
                                       content_meta_keywords as ml_meta_keywords
                                  FROM ".TABLE_CONTENT_MANAGER."
                                 WHERE content_group = '5'
                                   AND languages_id = '".(int)$_SESSION['languages_id']."'");
    $ml_meta = xtc_db_fetch_array($ml_meta_query, true);
    foreach ($ml_meta as $k => $v) {
      define(strtoupper($k), (($v != '') ? $v : constant(strtoupper(substr($k, 3)))));
    }
  } else {
    define('ML_META_KEYWORDS', META_KEYWORDS);
    define('ML_META_DESCRIPTION', META_DESCRIPTION);
    define('ML_META_TITLE', TITLE);
  }
  
  // add multilang
  $metaGoWords = getGoWords();

  // set page params
  $Page = $page_param = '';
  if (isset($_GET['page']) && $_GET['page'] < 0) $_GET['page'] = 1;
  if (isset($_GET['page']) && $_GET['page'] > 1 && $addPagination) {
    $Page = trim(str_replace('%d','',PREVNEXT_TITLE_PAGE_NO)).' '.(int)$_GET['page'];
    $page_param = '&page='. (int)$_GET['page'];
  }

  // create meta array
  $metadata_array = array();
  switch(basename($PHP_SELF)) {

    case FILENAME_PRODUCT_INFO :
      if ($product->isProduct() === true) {
        $canonical_flag = true;
        $metadata_array = array(
          'title' => (($product->data['products_meta_title'] != '') ? $product->data['products_meta_title'] : metaTitle($product->data['products_name'], ((isset($product->data['manufacturers_name'])) ? $product->data['manufacturers_name'] : ''))),
          'description' => (($product->data['products_meta_description'] != '') ? $product->data['products_meta_description'] : $product->data['products_name'].': '.$product->data['products_description']),
          'keywords' => (($product->data['products_meta_keywords'] != '') ? $product->data['products_meta_keywords'] : metaKeyWords($product->data['products_name'].' '.$product->data['products_description'])),
          'link' => xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product->data['products_id'], 'NONSSL', false),
        );
        $canonical_flag = false;
      
        if ($addProdShopTitle === true) $metadata_array['title'] .= ' - ' . ML_META_TITLE;
      
        // no hreflang if not canonical URL
        $product_link = str_replace(array(HTTP_SERVER,HTTPS_SERVER), '', preg_replace("/([^\?]*)(\?.*)/", "$1", $metadata_array['link']));
        $current_link = preg_replace("/([^\?]*)(\?.*)/", "$1", $_SERVER['REQUEST_URI']);
        if ($product_link != $current_link) {
          $set_hreflang = false;
        }
      }
      break;

    case FILENAME_DEFAULT :
      if (isset($current_category_id) && (int)$current_category_id > 0) {
        $categories_meta_query = xtDBquery("SELECT *
                                              FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                             WHERE categories_id='".(int)$current_category_id."'
                                               AND language_id='".(int)$_SESSION['languages_id']."'");
        if (xtc_db_num_rows($categories_meta_query, true) > 0) {
          $categories_meta = xtc_db_fetch_array($categories_meta_query, true);
      
          $metadata_array = array(
            'title' => (($categories_meta['categories_meta_title'] != '') ? $categories_meta['categories_meta_title'] : $categories_meta['categories_name']),
            'description' => (($categories_meta['categories_meta_description'] != '') ? $categories_meta['categories_meta_description'] : $categories_meta['categories_name'].(($categories_meta['categories_description'] != '') ? ': '.$categories_meta['categories_description'] : '')),
            'keywords' => (($categories_meta['categories_meta_keywords'] != '') ? $categories_meta['categories_meta_keywords'] : metaKeyWords($categories_meta['categories_name'].' '.$categories_meta['categories_description'])),
            'link' => xtc_href_link(FILENAME_DEFAULT, 'cPath='.$cPath.$page_param, 'NONSSL', false),
          );
		  
		      if ($Page != '') $metadata_array['title'] .= ' - ' . $Page;
          if ($addCatShopTitle === true) $metadata_array['title'] .= ' - ' . ML_META_TITLE;
        
          $metaGoWords .= ','.$categories_meta['categories_name'];
        }
      } elseif ((isset($_GET['manufacturers_id']) && (int)$_GET['manufacturers_id'] > 0)
                || (isset($_GET['filter_id']) && !is_array($_GET['filter_id']) && (int)$_GET['filter_id'] > 0)
                )
      {
        $manu_id = (int)((isset($_GET['manufacturers_id']) && (int)$_GET['manufacturers_id'] > 0) ? $_GET['manufacturers_id'] : $_GET['filter_id']);
      
        $manufacturers_array = xtc_get_manufacturers();
        if (isset($manufacturers_array[$manu_id])) {
          $manufacturer = $manufacturers_array[$manu_id];
        
          $metadata_array = array(
            'title' => (($manufacturer['manufacturers_meta_title'] != '') ? $manufacturer['manufacturers_meta_title'] : $manufacturer['manufacturers_name']),
            'description' => (($manufacturer['manufacturers_meta_description'] != '') ? $manufacturer['manufacturers_meta_description'] : $manufacturer['manufacturers_name'].(($manufacturer['manufacturers_description'] != '') ? ': '.$manufacturer['manufacturers_description'] : '')),
            'keywords' => (($manufacturer['manufacturers_meta_keywords'] != '') ? $manufacturer['manufacturers_meta_keywords'] : metaKeyWords($manufacturer['manufacturers_name'].' '.$manufacturer['manufacturers_description'])),
            'link' => xtc_href_link(FILENAME_DEFAULT, 'manufacturers_id='.(int)$manu_id.$page_param, 'NONSSL', false),
          );
          
		      if ($Page != '') $metadata_array['title'] .= ' - ' . $Page;
          if ($addCatShopTitle === true) $metadata_array['title'] .= ' - ' . ML_META_TITLE;

          $metaGoWords .= ','.$manufacturer['manufacturers_name'];
        }
      } else {
        $metadata_array = array(
          'title' => ML_META_TITLE,
          'description' => ML_META_DESCRIPTION,
          'keywords' => ML_META_KEYWORDS,
          'link' => xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL', false),
        );
      }
      break;

    case FILENAME_CONTENT :
      $contents_meta_query = xtDBquery("SELECT *
                                          FROM ".TABLE_CONTENT_MANAGER."
                                         WHERE content_group = '".(int)$_GET['coID']."'
                                           AND languages_id = '".(int)$_SESSION['languages_id']."'");

      if (xtc_db_num_rows($contents_meta_query, true) > 0) {
        $contents_meta = xtc_db_fetch_array($contents_meta_query,true);

        if ($contents_meta['content_file']) {
          if (preg_match("/\.(txt|htm|html)$/i", $contents_meta['content_file'])) {
            $contents_meta['content_text'] .= ' '.implode(' ', @file(DIR_FS_CATALOG.'media/content/'.$contents_meta['content_file']));
          }
        }

        // meta robots
        if ($contents_meta['content_meta_robots']!='') {
          $meta_robots = $contents_meta['content_meta_robots'];
        }

        $metadata_array = array(
          'title' => (($contents_meta['content_meta_title'] != '') ? $contents_meta['content_meta_title'] : metaTitle($contents_meta['content_title'], $contents_meta['content_heading'])),
          'description' => (($contents_meta['content_meta_description'] != '') ? $contents_meta['content_meta_description'] : (($contents_meta['content_heading'] != '') ? $contents_meta['content_heading'].': ' : '').$contents_meta['content_text']),
          'keywords' => (($contents_meta['content_meta_keywords'] != '') ? $contents_meta['content_meta_keywords'] : metaKeyWords($contents_meta['content_title'].' '.$contents_meta['content_heading'].' '.$contents_meta['content_text'])),
          'link' => xtc_href_link(FILENAME_CONTENT, 'coID='.(int)$_GET['coID'], 'NONSSL', false),
        );

        if ($addContentShopTitle === true) $metadata_array['title'] .= ' - ' . ML_META_TITLE;

        $metaGoWords .= ','.$contents_meta['content_title'];
      }
      break;

    case FILENAME_ADVANCED_SEARCH_RESULT :
      $metadata_array = array(
        'title' => metaTitle($breadcrumbTitle, $Page),
        'description' => '',
        'keywords' => '',
        'link' => '',
        'robot' => '',
      );

      if (isset($_GET['manufacturers_id']) && (int)$_GET['manufacturers_id'] > 0) {
        $manufacturers_array = xtc_get_manufacturers();
        if (isset($manufacturers_array[(int)$_GET['manufacturers_id']])) {
          $manufacturers = $manufacturers_array[(int)$_GET['manufacturers_id']];
        
          $metadata_array['title'] = metaTitle($metadata_array['title'], $manufacturers['manufacturers_name']);
          $metaGoWords .= ','.$manufacturers['manufacturers_name'];
        }
      }

      if (isset($_GET['categories_id']) && (int)$_GET['categories_id'] > 0) {
        $cat_name_query = xtDBquery("SELECT categories_name
                                       FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                      WHERE categories_id = '".(int)$_GET['categories_id']."'
                                        AND language_id = '".(int)$_SESSION['languages_id']."'");
        if (xtc_db_num_rows($cat_name_query, true) > 0) {
          $cat_name = xtc_db_fetch_array($cat_name_query, true);
      
          $metadata_array['title'] = metaTitle($metadata_array['title'], $cat_name['categories_name']);
          $metaGoWords .= ','.$cat_name['categories_name'];
        } 
      }

      if ($addSearchShopTitle === true) $metadata_array['title'] .= ' - ' . ML_META_TITLE;
      break;

    case FILENAME_SPECIALS :
      $metadata_array = array(
        'title' => metaTitle($breadcrumbTitle, $Page),
        'description' => '',
        'keywords' => '',
        'link' => xtc_href_link(FILENAME_SPECIALS, '', 'NONSSL', false),
        'robot' => '',
      );

      if ($addSpecialsShopTitle === true) $metadata_array['title'] .= ' - ' . ML_META_TITLE;
      break;

    case FILENAME_PRODUCTS_NEW :
      $metadata_array = array(
        'title' => metaTitle($breadcrumbTitle, $Page),
        'description' => '',
        'keywords' => '',
        'link' => xtc_href_link(FILENAME_PRODUCTS_NEW, '', 'NONSSL', false),
        'robot' => '',
      );

      if ($addNewsShopTitle === true) $metadata_array['title'] .= ' - ' . ML_META_TITLE;
      break;

    default:
      $metadata_array = array(
        'title' => metaTitle($breadcrumbTitle, $Page),
        'description' => '',
        'keywords' => '',
        'link' => '',
        'robot' => '',
      );

      if ($addOthersShopTitle == true) $metadata_array['title'] .= ' - ' . ML_META_TITLE;
      break;
  }

  // use standard for empty
  foreach ($metadata_array as $k => $v) {
    if ($v == '' && defined('ML_META_'.strtoupper($k))) {
      $metadata_array[$k] = constant('ML_META_'.strtoupper($k));
    }
  }

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/metatags_data/','php') as $file) require ($file); 

  // metaClean
  $metadata_array = array_map('metaClean', $metadata_array);
  $metadata_array['description'] =  ((isset($metadata_array['description'])) ? metaClean($metadata_array['description'], $metaDesLength) : '');

  // output metas
  if (TEMPLATE_HTML_ENGINE == 'xhtml') {
    echo '<meta http-equiv="Content-Type" content="text/html; charset='.$_SESSION['language_charset'].'" />'."\n";
    echo '<meta http-equiv="Content-Style-Type" content="text/css" />'."\n";
    echo '<meta http-equiv="cache-control" content="no-cache" />'."\n";
  } else {
    echo '<meta charset="'.$_SESSION['language_charset'].'" />'."\n";
  }
  if (TEMPLATE_RESPONSIVE == 'true') {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />'."\n";
  }
  if (isset($metadata_array['title']) && $metadata_array['title'] != '') {
    echo '<title>'. $metadata_array['title'] .'</title>'."\n";
  }
  if ($_SESSION['language_code'] != '' && TEMPLATE_HTML_ENGINE == 'xhtml') {
    echo '<meta http-equiv="content-language" content="'. $_SESSION['language_code'] .'" />'."\n";
  }
  if (isset($metadata_array['keywords']) && $metadata_array['keywords'] != '') {
    echo '<meta name="keywords" content="'. $metadata_array['keywords'] .'" />'."\n";
  }
  if ($metadata_array['description'] != '') {
    echo '<meta name="description" content="'. $metadata_array['description'] .'" />'."\n";
  }
  if ($_SESSION['language_code'] != '' && TEMPLATE_HTML_ENGINE == 'xhtml') {
    echo '<meta name="language" content="'. $_SESSION['language_code'] .'" />'."\n";
  }
  if ($meta_robots != '') {
    echo '<meta name="robots" content="'. $meta_robots .'" />'."\n";
  }
  if (metaClean(META_AUTHOR) != '') {
    echo '<meta name="author" content="'.metaClean(META_AUTHOR) .'" />'."\n";
  }
  if (metaClean(META_PUBLISHER) != '' && TEMPLATE_HTML_ENGINE == 'xhtml') {
    echo '<meta name="publisher" content="'. metaClean(META_PUBLISHER) .'" />'."\n";
  }
  if (metaClean(META_COMPANY) != '' && TEMPLATE_HTML_ENGINE == 'xhtml') {
    echo '<meta name="company" content="'. metaClean(META_COMPANY) .'" />'."\n";
  }
  if (metaClean(META_TOPIC) != '' && TEMPLATE_HTML_ENGINE == 'xhtml') {
    echo '<meta name="page-topic" content="'. metaClean(META_TOPIC) .'" />'."\n";
  }
  if (META_REPLY_TO != 'xx@xx.com' && TEMPLATE_HTML_ENGINE == 'xhtml') {
    echo '<meta name="reply-to" content="'. META_REPLY_TO .'" />'."\n";
  }
  if (META_REVISIT_AFTER != '0') {
    echo '<meta name="revisit-after" content="'. META_REVISIT_AFTER .' days" />'."\n";
  }
  if (META_GOOGLE_VERIFICATION_KEY != '') {
    echo '<meta name="google-site-verification" content="'. META_GOOGLE_VERIFICATION_KEY .'" />'."\n";
  }
  if (META_BING_VERIFICATION_KEY != '') {
    echo '<meta name="msvalidate.01" content="'. META_BING_VERIFICATION_KEY .'" />'."\n";
  }

  if (strpos($meta_robots,'noindex') !== false) {
    $set_hreflang = false;
    if (isset($metadata_array['link'])) {
      unset($metadata_array['link']);
    }
  } else {
    $meta_url = parse_url($_SERVER['REQUEST_URI']);
    if (isset($meta_url['query'])) {
      parse_str($meta_url['query'], $meta_params_array);
      if (isset($meta_params_array[xtc_session_name()])) {
        unset($meta_params_array[xtc_session_name()]);
      }
      if (isset($meta_params_array['language'])) {
        unset($meta_params_array['language']);
      }
      if (count($meta_params_array) && (!isset($_GET['page']) || $_GET['page'] > 1)) {
        $set_hreflang = false;
      }
    } elseif (basename($meta_url['path']) == FILENAME_DEFAULT) {
      $set_hreflang = false;
    }
  }

  if ($addPagination) {
    $number_of_pages = 0;
    $split_obj = array('listing_split', 'specials_split', 'products_new_split');
    foreach ($split_obj as $object) {
      if (isset(${$object}) && is_object(${$object})) {
        $number_of_pages = ${$object}->number_of_pages;
        break;
      }
    }
    if ($number_of_pages > 1) {
      $page = ((isset($_GET['page']) && $_GET['page'] > 0) ? (int)$_GET['page'] : 1);
      if ($page > 1 && $number_of_pages >= $page) {
        echo '<link rel="prev" href="'.xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params_include(array('products_id', 'cPath', 'manufacturers_id', 'coID')).(($page > 2) ? 'page='.($page - 1) : ''), 'NONSSL', false).'" />'."\n";
      }
      if ($page >= 1 && $number_of_pages > 1 && $number_of_pages > $page) {
        echo '<link rel="next" href="'.xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params_include(array('products_id', 'cPath', 'manufacturers_id', 'coID')).'page='.($page + 1), 'NONSSL', false).'" />'."\n";
      }
      /*
      // dont show canonical with pagination
      if (isset($metadata_array['link']) && $page > 1) {
        unset($metadata_array['link']);
      }
      */
    }
  }

  $meta_alternate = array();
  if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    require_once(DIR_WS_CLASSES . 'language.php');
    $lng = new language;
  }
  if (SEARCH_ENGINE_FRIENDLY_URLS == 'true' 
      && defined('MODULE_MULTILANG_STATUS')
      && MODULE_MULTILANG_STATUS == 'true'
      && $set_hreflang 
      && count($lng->catalog_languages) > 1 
      //&& (!isset($_GET['page']) || $_GET['page'] == 1)
      ) 
  {
    $canonical_flag = true;
    $x_default_flag = true;
    $x_default_lng = ((defined('MODULE_MULTILANG_X_DEFAULT')) ? MODULE_MULTILANG_X_DEFAULT : 'en');
    $x_default_link = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params_include(array('products_id', 'cPath', 'manufacturers_id', 'coID')).'language='.$x_default_lng.$page_param, 'NONSSL', false);
    if ($x_default_link != '#') {
      $meta_alternate['x-default'] = '<link rel="alternate" href="'.$x_default_link.'" hreflang="x-default" />';
    } else {
      $x_default_flag = false;
    }
    reset($lng->catalog_languages);
    foreach ($lng->catalog_languages as $key => $value) {
      $alternate_link = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params_include(array('products_id', 'cPath', 'manufacturers_id', 'coID')).'language='.$key.$page_param, 'NONSSL', false);
      if ($alternate_link != '#') {
        if ($x_default_flag === false) {
          $meta_alternate['x-default'] = '<link rel="alternate" href="'.$alternate_link.'" hreflang="x-default" />';
          $x_default_flag = true;
        } else {
          $meta_alternate[$value['code']] = '<link rel="alternate" href="'. $alternate_link .'" hreflang="'.$value['code'].'" />';
        }
      }
    }
    $canonical_flag = false;
    
    // canonical for alternate
    if (isset($metadata_array['link']) && $metadata_array['link'] != '') {
      $meta_alternate['canonical'] = '<link rel="canonical" href="'.$metadata_array['link'].'" />';
    }
  }
  if (count($meta_alternate) > 2) {
    echo implode("\n",$meta_alternate)."\n";
  } elseif (isset($metadata_array['link']) && $metadata_array['link'] != '') {
    echo '<link rel="canonical" href="'.$metadata_array['link'].'" />'."\n";
  }

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/metatags_end/','php') as $file) require ($file); 
?>