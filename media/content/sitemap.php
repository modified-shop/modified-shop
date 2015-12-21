<?php
/* -----------------------------------------------------------------------------------------
   $Id: sitemap.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce; www.oscommerce.com
   (c) 2003 nextcommerce; www.nextcommerce.org
   (c) 2005 xtCommerce (sitemap.php 1278 2005-10-02); www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new smarty;
$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

// set cache ID
if (!CacheCheck()) {
  $cache=false;
  $module_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache=true;
  $module_smarty->caching = 1;
  $module_smarty->cache_lifetime = CACHE_LIFETIME;
  $module_smarty->cache_modified_check = CACHE_CHECK;
}

// include needed function
require_once(DIR_FS_INC . 'xtc_count_products_in_category.inc.php');

$cache_id = md5($_SESSION['language'].$_SESSION['customers_status']['customers_status'].((isset($_REQUEST['error'])) ? $_REQUEST['error'] : ''));

if (!$module_smarty->is_cached(CURRENT_TEMPLATE.'/module/sitemap.html', $cache_id) || !$cache) {

  $categories_query = xtDBquery("SELECT c.categories_image, 
                                        c.categories_id, 
                                        cd.categories_name
                                   FROM " . TABLE_CATEGORIES . " c
                                   JOIN " . TABLE_CATEGORIES_DESCRIPTION ." cd 
                                        ON c.categories_id = cd.categories_id
                                           AND cd.language_id = ".(int)$_SESSION['languages_id']."
                                           AND trim(cd.categories_name) != ''
                                  WHERE c.categories_status = 1
                                    AND c.parent_id = '0'
                                        ".CATEGORIES_CONDITIONS_C."
                               ORDER BY c.sort_order, cd.categories_name");

  $module_content = array();
  while ($categories = xtc_db_fetch_array($categories_query,true)) {
    $module_content[]=array('ID'  => $categories['categories_id'],
                            'CAT_NAME'  => $categories['categories_name'],
                            'CAT_IMAGE' => DIR_WS_IMAGES . 'categories/' . $categories['categories_image'],
                            'CAT_LINK'  => xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories['categories_id'],$categories['categories_name'])),
                            'SCATS'  => get_category_tree($categories['categories_id'], '',0)
                            );
  }

  // if there's sth -> assign it
  if (sizeof($module_content) >= 1) {
    $module_smarty->assign('module_content',$module_content);

    if ($_SESSION['language'] == 'german') {
       $fehler = array(404 => 'Fehler 404: Die gesuchte Seite wurde nicht gefunden!',
       401 => "Fehler 401: Authentifizierungsfehler.",
       400 => "Fehler 400: Die Anforderung war syntaktisch falsch.",
       403 => "Fehler 403: Der Server verweigert die Ausf&uuml;hrung.",
       500 => "Fehler 500: Beim Server gab es einen internen Fehler.");
    } else {
       $fehler = array(404 => 'Error 404: Not Found!',
       401 => "Error 401: Unauthorized.",
       400 => "Error 400: Bad Request.",
       403 => "Error 403: Forbidden.",
       500 => "Error 500: Internal Server Error.");
    }
    if (isset($_REQUEST['error'])) {
      $module_smarty->assign('herror',$fehler[$_REQUEST['error']]);
       // also set HTTP status code to 404 (in order to be not crawled by search engines)
       if ($_REQUEST['error'] == '404') {
         header('HTTP/1.1 404 Not Found');
       }
    }
  }
}

if (!$cache) {
  echo $module_smarty->fetch(CURRENT_TEMPLATE.'/module/sitemap.html');
} else {
  echo $module_smarty->fetch(CURRENT_TEMPLATE.'/module/sitemap.html',$cache_id);
}


// function to get category trees
function get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $cPath = '') {
  if ($parent_id == 0){ 
    $cPath = ''; 
  } else { 
    $cPath .= $parent_id . '_';
  }
  if (!is_array($category_tree_array)) { 
    $category_tree_array = array(); 
  }
  if ((sizeof($category_tree_array) < 1) && ($exclude != '0') ) {
    $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);
  }
  $parent_id = (int)$parent_id;
  if ($include_itself) {
    $category_query = xtDBquery("SELECT cd.categories_name
                                   FROM " . TABLE_CATEGORIES . " c
                                   JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                        ON c.categories_id = cd.categories_id
                                           AND cd.language_id = ".(int)$_SESSION['languages_id']."
                                           AND trim(cd.categories_name) != ''
                                  WHERE c.categories_status = 1
                                        ".CATEGORIES_CONDITIONS_C."
                                    AND c.categories_id = '".$parent_id."'
                                  LIMIT 1");
    $category = xtc_db_fetch_array($category_query, true);
    $category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name']);
  }

  $categories_query = "SELECT c.categories_id, 
                              cd.categories_name, 
                              c.parent_id
                         FROM " . TABLE_CATEGORIES . " c
                         JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                              ON c.categories_id = cd.categories_id
                                 AND cd.language_id = ".(int)$_SESSION['languages_id']."
                                 AND trim(cd.categories_name) != ''
                        WHERE c.parent_id = '".$parent_id."'
                          AND c.categories_status = '1'
                              ".CATEGORIES_CONDITIONS_C."
                     ORDER BY c.sort_order, cd.categories_name";
  $categories_query = xtDBquery($categories_query);
  while ($categories = xtc_db_fetch_array($categories_query,true)) {
    if ($exclude != $categories['categories_id']) {
      $category_tree_array[] = array('id' => $categories['categories_id'],
                                     'text' => $spacing . $categories['categories_name'],
                                     'link' => xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories['categories_id'],$categories['categories_name']))
                                     );
    }
    $category_tree_array = get_category_tree($categories['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, false, $cPath);
  }

  return $category_tree_array;
}
?>