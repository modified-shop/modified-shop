<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_listing.php,v 1.42 2003/05/27); www.oscommerce.com
   (c) 2003 nextcommerce (product_listing.php,v 1.19 2003/08/1); www.nextcommerce.org
   (c) 2006 xt:Commerce (product_listing.php 1286 2005-10-07); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new Smarty;
$module_smarty->caching = false; //DokuMan - 2012-10-30 - avoid Smarty caching in order to display the correct data, if caching is enabled in shop backend

//BOF - GTB - 2010-08-03 - Security Fix - Base
$module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
//$module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
//EOF - GTB - 2010-08-03 - Security Fix - Base

$result = true;
// include needed functions
require_once (DIR_FS_INC.'xtc_get_all_get_params.inc.php');
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');

$listing_split = new splitPageResults($listing_sql, (isset($_GET['page']) ? (int)$_GET['page'] : 1), MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');
$module_content = array ();
$category = array ();

if ($listing_split->number_of_rows > 0) {
  //BOF - web28 - 2011-03-27 - FIX page search results -> urlencode($_GET['keywords'])
  $navigation = '
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="smallText">'.$listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS).'</td>
        <td class="smallText" align="right">'.TEXT_RESULT_PAGE.' '.$listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y', 'keywords')).(isset($_GET['keywords'])?'&keywords='. urlencode($_GET['keywords']):'')).'</td>
      </tr>
    </table>';
  //EOF - web28 - 2011-03-27 - FIX page search results -> urlencode($_GET['keywords'])
  $group_check = '';
  if (GROUP_CHECK == 'true') {
    $group_check = "and c.group_permission_".$_SESSION['customers_status']['customers_status_id'].'= 1';
  }
  $category_query = xtDBquery("SELECT cd.categories_description,
                                      cd.categories_name,
                                      cd.categories_heading_title,
                                      c.listing_template,
                                      c.categories_image
                                 FROM ".TABLE_CATEGORIES." c,
                                      ".TABLE_CATEGORIES_DESCRIPTION." cd
                                WHERE c.categories_id = '".$current_category_id."'
                                  AND cd.categories_id = '".$current_category_id."'
                                      ".$group_check."
                                  AND cd.language_id = '".$_SESSION['languages_id']."'
                                LIMIT 1"); //DokuMan - 2011-05-13 - added LIMIT 1

  $category = xtc_db_fetch_array($category_query,true);
  $image = '';
  if ($category['categories_image'] != '') {
    $image = DIR_WS_IMAGES.'categories/'.$category['categories_image'];
    if(!file_exists($image)) $image = DIR_WS_IMAGES.'categories/noimage.gif'; //Tomcraft - 2009-10-30 - noimage.gif is displayed, when no image is defined

    //BOF - GTB - 2010-08-03 - Security Fix - Base
    $image = DIR_WS_BASE.$image;
    //EOF - GTB - 2010-08-03 - Security Fix - Base
  }

  //BOF -web28- 2010-08-06 - BUGFIX no manufacturers image displayed
  if (isset ($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
    // BOF - web28 - 2011-05-06 - FIX display manufacturers_name
    $manu_query = xtDBquery("SELECT manufacturers_image, manufacturers_name FROM ".TABLE_MANUFACTURERS." WHERE manufacturers_id = '".(int) $_GET['manufacturers_id']."'");
    $manu = xtc_db_fetch_array($manu_query,true);
    $category['categories_name'] = $manu['manufacturers_name'];
    // EOF - web28 - 2011-05-06 - FIX display manufacturers_name

    // BOF - web28 - 2011-01-28 - FIX display manufacturers_image
    if ($manu['manufacturers_image'] != '') {
      $image = DIR_WS_IMAGES.$manu['manufacturers_image'];
      if(!file_exists($image)){
        $image = '';
      }
      //BOF - GTB - 2010-08-03 - Security Fix - Base
      if ($image != ''){
        $image = DIR_WS_BASE.$image;
      }
      //EOF - GTB - 2010-08-03 - Security Fix - Base
    }
    // EOF - web28 - 2011-01-28 - FIX display manufacturers_image
  }
  //EOF -web28- 2010-08-06 - BUGFIX no manufacturers image displayed

  if (isset($category['categories_name'])) {
    $list_title = $category['categories_name'];
  } elseif (isset($category['categories_heading_title'])) {
    $list_title = $category['categories_heading_title'];
  } elseif (isset($_GET['keywords'])) {
    $list_title = TEXT_SEARCH_TERM . htmlentities(str_replace("+", " ", $_GET['keywords']));
  }
  $module_smarty->assign('LIST_TITLE',  isset($list_title) ? $list_title : '');
  $module_smarty->assign('CATEGORIES_NAME', isset($category['categories_name']) ? $category['categories_name'] : '');
  $module_smarty->assign('CATEGORIES_HEADING_TITLE', isset($category['categories_heading_title']) ? $category['categories_heading_title'] : '');
  $module_smarty->assign('CATEGORIES_DESCRIPTION', isset($category['categories_description']) ? $category['categories_description'] : '');
  $module_smarty->assign('CATEGORIES_IMAGE', $image);

  $rows = 0;
  $listing_query = xtDBquery($listing_split->sql_query);
  while ($listing = xtc_db_fetch_array($listing_query, true)) {
    $rows ++;
    $module_content[] = $product->buildDataArray($listing);
  }
  
  // Highlight Search Terms
  if (defined('SEARCH_HIGHLIGHT') && SEARCH_HIGHLIGHT == 'true') {
    if (isset($_GET['keywords'])) {
      $keywords = explode('+', htmlentities($_GET['keywords']));
      function highlight($word) { 
        $style = (SEARCH_HIGHLIGHT_STYLE != '') ? 'style="'.SEARCH_HIGHLIGHT_STYLE.'"' : 'class="highlight"';
        return '<span '.$style.'>'.$word.'</span>'; 
      }
      $keyword_highlight = array_map("highlight", $keywords);
      for ($i=0; $i<count($module_content); $i++) {
        $module_content[$i]['PRODUCTS_NAME'] = str_ireplace($keywords, $keyword_highlight, $module_content[$i]['PRODUCTS_NAME']);
        if (isset($module_content[$i]['PRODUCTS_SHORT_DESCRIPTION']) && !empty($module_content[$i]['PRODUCTS_SHORT_DESCRIPTION'])) {
          $module_content[$i]['PRODUCTS_SHORT_DESCRIPTION'] = str_ireplace($keywords, $keyword_highlight, $module_content[$i]['PRODUCTS_SHORT_DESCRIPTION']);
        }
      }
    }
  }

} else {
  // no product found
  $result = false;
}

//include Categorie Listing
include (DIR_WS_MODULES. 'categories_listing.php');

// get default template
if ($result !== false && (!isset($category) || empty($category['listing_template']) || $category['listing_template'] == 'default')) {
  $files = array ();
  if ($dir = opendir(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_listing/')) {
    while (($file = readdir($dir)) !== false) {
      if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_listing/'.$file) && (substr($file, -5) == ".html") && ($file != "index.html") && (substr($file, 0, 1) !=".")) { //Tomcraft - 2010-02-04 - Prevent fetching other files than *.html
        // BOF - web28 - 2010-07-12 - sort templates array
        //$files[] = array ('id' => $file, 'text' => $file);
        $files[] = $file;
      } //if
    } // while
    closedir($dir);
  }
  sort($files);
  //$category['listing_template'] = $files[0]['id'];
  $category['listing_template'] = $files[0];
  // EOF - web28 - 2010-07-12 - sort templates array
}

if ($result != false) {
  $module_smarty->assign('MANUFACTURER_DROPDOWN', (isset($manufacturer_dropdown) ? $manufacturer_dropdown : ''));
  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('module_content', $module_content);
  $module_smarty->assign('NAVIGATION', $navigation);

  // BOF - web28 - 2011-05-06 - support for own manufacturers template
  $template = CURRENT_TEMPLATE.'/module/product_listing/'.$category['listing_template'];
  if (isset ($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0 && strpos($PHP_SELF, FILENAME_ADVANCED_SEARCH_RESULT) === false) {
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/manufacturers_listing.html')) {
      $template = CURRENT_TEMPLATE.'/module/manufacturers_listing.html';
    }
  }
  // EOF - web28 - 2011-05-06 - support for own manufacturers template

  // set cache ID
  if (!CacheCheck()) {
    $module_smarty->caching = 0;
    $module = $module_smarty->fetch($template); //web28 - 2011-05-06 - support for own manufacturers template
  } else {
    $module_smarty->caching = 1;
    $module_smarty->cache_lifetime = CACHE_LIFETIME;
    $module_smarty->cache_modified_check = CACHE_CHECK;
    //BOF - web28 - 2011-03-27 - FIX page search results -> urlencode($_GET['keywords'])
    //$cache_id = $current_category_id.'_'.$_SESSION['language'].'_'.$_SESSION['customers_status']['customers_status_name'].'_'.$_SESSION['currency'].'_'.$_GET['manufacturers_id'].'_'.$_GET['filter_id'].'_'.$_GET['page'].'_'.$_GET['keywords'].'_'.$_GET['categories_id'].'_'.$_GET['pfrom'].'_'.$_GET['pto'].'_'.$_GET['x'].'_'.$_GET['y'];
    $cache_id = $current_category_id.'_'.$_SESSION['language'].'_'.$_SESSION['customers_status']['customers_status_name'].'_'.$_SESSION['currency'].'_'.$_GET['manufacturers_id'].'_'.$_GET['filter_id'].'_'.$_GET['page'].'_'.urlencode($_GET['keywords']).'_'.$_GET['categories_id'].'_'.$_GET['pfrom'].'_'.$_GET['pto'].'_'.$_GET['x'].'_'.$_GET['y'];
    //EOF - web28 - 2011-03-27 - FIX page search results -> urlencode($_GET['keywords'])
    $module = $module_smarty->fetch($template, $cache_id); //web28 - 2011-05-06 - support for own manufacturers template
  }
  $smarty->assign('main_content', $module);
} else {
  $error = TEXT_PRODUCT_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
}
?>