<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$categorie_smarty = new Smarty();
$categorie_smarty->assign('language', $_SESSION['language']);
$categorie_smarty->assign('tpl_path', DIR_WS_BASE . 'templates/'.CURRENT_TEMPLATE.'/');

// set cache ID
if (!CacheCheck()) {
  $cache = false;
  $categorie_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache = true;
  $categorie_smarty->caching = 1;
  $categorie_smarty->cache_lifetime = CACHE_LIFETIME;
  $categorie_smarty->cache_modified_check = CACHE_CHECK == 'true';
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].((isset($cPath) && preg_match('/_/', $cPath)) ? '|cP:'.$cPath : '|cID:'.$current_category_id));
}

$categorie_template = 'sub_categories_listing.html';
foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/categories_listing/categories_smarty/','php') as $file) require_once ($file);

if (!$categorie_smarty->is_cached(CURRENT_TEMPLATE.'/module/'.$categorie_template, $cache_id) || !$cache) {
  if (MAX_DISPLAY_CATEGORIES_PER_ROW > 0) {
    if (isset ($cPath) && preg_match('/_/', $cPath)) { 
      $category_links = array_reverse($cPath_array);
      $categories_query = "SELECT ".ADD_SELECT_CATEGORIES."
                                  c.categories_id,
                                  c.categories_image,
                                  c.categories_image_list,
                                  c.categories_image_mobile,
                                  c.parent_id,
                                  cd.categories_name,
                                  cd.categories_description,
                                  cd.categories_short_description,
                                  cd.categories_heading_title
                             FROM ".TABLE_CATEGORIES." c
                             JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd 
                                  ON c.categories_id = cd.categories_id
                                     AND trim(cd.categories_name) != ''
                            WHERE c.categories_status = '1'
                              AND c.parent_id = '".(int)$category_links[0]."'
                              AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                              " . CATEGORIES_CONDITIONS_C . "
                         ORDER BY sort_order, cd.categories_name";
    } else {
      $categories_query = "SELECT ".ADD_SELECT_CATEGORIES."
                                  c.categories_id,
                                  c.categories_image,
                                  c.categories_image_list,
                                  c.categories_image_mobile,
                                  c.parent_id,
                                  cd.categories_name,
                                  cd.categories_description,
                                  cd.categories_short_description,
                                  cd.categories_heading_title
                             FROM ".TABLE_CATEGORIES." c
                             JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd 
                                  ON c.categories_id = cd.categories_id
                                     AND trim(cd.categories_name) != ''
                            WHERE c.categories_status = '1'
                              AND c.parent_id = '".(int)$current_category_id."'
                              AND c.parent_id <> '0'
                              AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                              " . CATEGORIES_CONDITIONS_C . "
                           ORDER BY sort_order, cd.categories_name";
    }
    $categories_query = xtDBquery($categories_query); 
  
    if ( xtc_db_num_rows($categories_query, true) >= 1 ) {
      $categories_content = array();
      $rows = 0;
      while ($categories = xtc_db_fetch_array($categories_query, true)) {
        $cPath_new = xtc_category_link($categories['categories_id'],$categories['categories_name']);
     
        $image = $main->getImage($categories['categories_image']);
        $image_list = $main->getImage($categories['categories_image_list'] != '' ? $categories['categories_image_list'] : $categories['categories_image']);
        $image_mobile = $main->getImage($categories['categories_image_mobile']);
      
        $categories_content[$rows] = array (
          'CATEGORIES_NAME' => $categories['categories_name'], 
          'CATEGORIES_HEADING_TITLE' => $categories['categories_heading_title'],
          'CATEGORIES_IMAGE' => (($image != '') ? DIR_WS_BASE . $image : ''),
          'CATEGORIES_IMAGE_LIST' => (($image_list != '') ? DIR_WS_BASE . $image_list : ''),
          'CATEGORIES_IMAGE_MOBILE' => (($image_mobile != '') ? DIR_WS_BASE . $image_mobile : ''),
          'CATEGORIES_LINK' => xtc_href_link(FILENAME_DEFAULT, $cPath_new), 
          'CATEGORIES_DESCRIPTION' => $categories['categories_description'],
          'CATEGORIES_SHORT_DESCRIPTION' => $categories['categories_short_description'],
        );

        foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/categories_listing/categories_content/','php') as $file) require ($file);
                                     
        $rows ++;
      }  
      $categorie_smarty->assign('categories_content', $categories_content);
    }

    $max_per_row = MAX_DISPLAY_CATEGORIES_PER_ROW;
    if ($max_per_row > 0){
      $width = (int) (100 / $max_per_row).'%';
    }
    $categorie_smarty->assign('TR_COLS', $max_per_row);
    $categorie_smarty->assign('TD_WIDTH', $width);
  }
}

$module = $categorie_smarty->fetch(CURRENT_TEMPLATE.'/module/'.$categorie_template, $cache_id);

if (isset($module_smarty) && is_object($module_smarty)) {
  $module_smarty->assign('CATEGORIES_LISTING', !empty($module) ? trim($module) : $module);
}

if (isset($default_smarty) && is_object($default_smarty)) {
  $default_smarty->assign('CATEGORIES_LISTING', !empty($module) ? trim($module) : $module);
}
