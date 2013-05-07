<?php
/* -----------------------------------------------------------------------------------------
   $Id: categories_listing.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$categorie_smarty = new Smarty;
$categorie_smarty->assign('tpl_path', DIR_WS_BASE . 'templates/'.CURRENT_TEMPLATE.'/');
  
  $group_check='';
  if (GROUP_CHECK == 'true') {
    $group_check = "and c.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
  }
  if (isset ($cPath) && preg_match('/_/', $cPath)) { 
    $category_links = array_reverse($cPath_array);
    $categories_query = "SELECT cd.categories_description,
                                c.categories_id,
                                cd.categories_name,
                                cd.categories_heading_title,
                                c.categories_image,
                                c.parent_id 
                           FROM ".TABLE_CATEGORIES." c
                           JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON c.categories_id = cd.categories_id
                          WHERE c.categories_status = '1'
                            AND c.parent_id = '".$category_links[0]."'
                            AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                                ".$group_check."
                       ORDER BY sort_order, cd.categories_name";
    $categories_query = xtDBquery($categories_query); 
  } else {
    $categories_query = "select cd.categories_description,
                                c.categories_id,
                                cd.categories_name,
                                cd.categories_heading_title,
                                c.categories_image,
                                c.parent_id
                           FROM ".TABLE_CATEGORIES." c
                           JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON c.categories_id = cd.categories_id
                          WHERE c.categories_status = '1'
                            AND c.parent_id = '".$current_category_id."'
                            AND c.parent_id <> '0'
                            AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                                ".$group_check."
                         ORDER BY sort_order, cd.categories_name";
    $categories_query = xtDBquery($categories_query);
  }
  
  $categories_listing = array();
  if ( xtc_db_num_rows($categories_query, true) >= 1 ) {
    $rows = 0;
    while ($categories = xtc_db_fetch_array($categories_query, true)) {
      $rows ++;
     
      $cPath_new = xtc_category_link($categories['categories_id'],$categories['categories_name']);

      $image = '';
      if ($categories['categories_image'] != '') {
        $image = DIR_WS_IMAGES.'categories/'.$categories['categories_image'];
      }
      if(!file_exists($image)) $image = DIR_WS_IMAGES.'categories/noimage.gif';
      
      $categories_content[] = array ('CATEGORIES_NAME' => $categories['categories_name'], 
                                     'CATEGORIES_HEADING_TITLE' => $categories['categories_heading_title'],
                                     'CATEGORIES_IMAGE' => DIR_WS_BASE . $image,
                                     'CATEGORIES_LINK' => xtc_href_link(FILENAME_DEFAULT, $cPath_new), 
                                     'CATEGORIES_DESCRIPTION' => $categories['categories_description']);
    }  
    $categorie_smarty->assign('categories_content', $categories_content);
  }

  $max_per_row = MAX_DISPLAY_CATEGORIES_PER_ROW;
  if ($max_per_row > 0){
    $width = (int) (100 / $max_per_row).'%';
  }

  $categorie_smarty->assign('TR_COLS', $max_per_row);
  $categorie_smarty->assign('TD_WIDTH', $width);

  $categorie_smarty->assign('language', $_SESSION['language']);
  $categorie_smarty->caching = 0;
  $categories_listing = $categorie_smarty->fetch(CURRENT_TEMPLATE.'/module/sub_categories_listing.html');

$module_smarty->assign('CATEGORIES_LISTING', $categories_listing);
?>