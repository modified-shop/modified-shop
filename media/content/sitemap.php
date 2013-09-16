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

$module_smarty = new Smarty;
//BOF - GTB - 2010-08-03 - Security Fix - Base
$module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
//$module_smarty->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/');
//EOF - GTB - 2010-08-03 - Security Fix - Base
require_once(DIR_FS_INC . 'xtc_count_products_in_category.inc.php');

//to get category trees
function get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $cPath = '') {
   if ($parent_id == 0){ $cPath = ''; } else { $cPath .= $parent_id . '_'; }
   if (!is_array($category_tree_array)) $category_tree_array = array();
   if ( (sizeof($category_tree_array) < 1) && ($exclude != '0') ) $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

   if ($include_itself) {
     //$category_query = xtDBquery($category_query); //DokuMan - 2010-11-10 - Moved query further down
     $category_query = "
     select cd.categories_name
           from " . TABLE_CATEGORIES_DESCRIPTION . " cd
           where cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
           and c.categories_status = '1'
           and cd.categories_id = '" . (int)$parent_id . "'
           order by cd.categories_name";
     $category_query = xtDBquery($category_query); //DokuMan - 2010-11-10 - Moved query further dow
     $category = xtc_db_fetch_array($category_query,true);
     $category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name']);
   }

   $categories_query = "
   select c.categories_id,
          cd.categories_name,
          c.parent_id
          from " . TABLE_CATEGORIES . " c,
          " . TABLE_CATEGORIES_DESCRIPTION . " cd
          where c.categories_id = cd.categories_id
          and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
          and c.parent_id = '" . (int)$parent_id . "'
          and c.categories_status = '1'
          order by c.sort_order, cd.categories_name";
   $categories_query = xtDBquery($categories_query);
   while ($categories = xtc_db_fetch_array($categories_query,true)) {
     $SEF_link = xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories['categories_id'],$categories['categories_name']));

     if ($exclude != $categories['categories_id'])
      $category_tree_array[] = array(
                                      'id' => $categories['categories_id'],
                                      'text' => $spacing . $categories['categories_name'],
                                      'link' => $SEF_link);
      $category_tree_array = get_category_tree($categories['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, false, $cPath);
   }

   return $category_tree_array;
 }

 if (GROUP_CHECK == 'true') {
  $group_check = "and c.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
 }

 $categories_query = "
 select c.categories_image,
        c.categories_id,
        cd.categories_name
        from " . TABLE_CATEGORIES . " c
        left join " . TABLE_CATEGORIES_DESCRIPTION ." cd on c.categories_id = cd.categories_id
        where c.categories_status = '1'
        and cd.language_id = ".(int)$_SESSION['languages_id'] ."
        and c.parent_id = '0'
        ".$group_check."
        order by c.sort_order, cd.categories_name";

 // db Cache
 $categories_query = xtDBquery($categories_query);
 $module_content = array();
 while ($categories = xtc_db_fetch_array($categories_query,true)) {
   $SEF_link = xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories['categories_id'],$categories['categories_name']));

   $module_content[]=array('ID'  => $categories['categories_id'],
                           'CAT_NAME'  => $categories['categories_name'],
                           'CAT_IMAGE' => DIR_WS_IMAGES . 'categories/' . $categories['categories_image'],
                           'CAT_LINK'  => $SEF_link,
                           'SCATS'  => get_category_tree($categories['categories_id'], '',0)
                           );
 }

 // if there's sth -> assign it
 if (sizeof($module_content)>=1) {
  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('module_content',$module_content);

   if($_SESSION['language'] == "german") {
     $fehler = array(404 => 'Fehler 404: Die gesuchte Seite wurde nicht gefunden!',
     401 => "Fehler 401: Authentifizierungsfehler.",
     400 => "Fehler 400: Die Anforderung war syntaktisch falsch.",
     403 => "Fehler 403: Der Server verweigert die Ausführung.",
     500 => "Fehler 500: Beim Server gab es einen internen Fehler.");
   }
   else {
     $fehler = array(404 => 'Error 404: Not Found!',
     401 => "Error 401: Unauthorized.",
     400 => "Error 400: Bad Request.",
     403 => "Error 403: Forbidden.",
     500 => "Error 500: Internal Server Error.");
   }
   if (isset($_REQUEST['error'])) {
    $module_smarty->assign('herror',$fehler[$_REQUEST['error']]);
   //BOF - DokuMan - 2011-03-18 - also set HTTP status code to 404 (in order to be not crawled by search engines)
     if ($_REQUEST['error'] == '404') {
       header('HTTP/1.1 404 Not Found');
     }
   }
   //BOF - DokuMan - 2011-03-18 - also set HTTP status code to 404 (in order to be not crawled by search engines)

   // set cache ID
   if (!CacheCheck()) {
     $module_smarty->caching = 0;
     echo $module_smarty->fetch(CURRENT_TEMPLATE.'/module/sitemap.html');
   } else {
     $module_smarty->caching = 1;
     $module_smarty->cache_lifetime=CACHE_LIFETIME;
     $module_smarty->cache_modified_check=CACHE_CHECK;
     $cache_id = $GET['cPath'].$_SESSION['language'].$_SESSION['customers_status']['customers_status_name'].$_SESSION['currency'];
     echo $module_smarty->fetch(CURRENT_TEMPLATE.'/module/sitemap.html',$cache_id);
   }
 }
?>