<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include smarty
  include(DIR_FS_BOXES_INC . 'smarty_default.php');

  // set cache id
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|cP:'.$cPath.(((defined('SPECIALS_CATEGORIES') && SPECIALS_CATEGORIES === true) || (defined('WHATSNEW_CATEGORIES') && WHATSNEW_CATEGORIES === true)) ? '|self:'.basename($PHP_SELF) : ''));

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_sub_categories.html', $cache_id) || !$cache) {

    // include needed functions
    require_once (DIR_FS_BOXES_INC.'xtc_show_category.inc.php');
    require_once (DIR_FS_INC.'xtc_count_products_in_category.inc.php');
    require_once (DIR_FS_INC.'xtc_has_category_subcategories.inc.php');
  
    $category_tree_array = array();

    if (isset($cPath_array)
        && is_array($cPath_array)
        )
    {
      $new_cPath_array = $cPath_array;
      array_unshift($new_cPath_array, 0);

      $path = '';
      foreach ($new_cPath_array as $categories_id) {
        $path = $path . (($path != '') ? '_' : '') . (($categories_id > 0) ? $categories_id : '');
        $data = array(
          'name' => '',
          'id' => 0,
          'link' => xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL'),
          'active' => 0,
        );
        if ($categories_id != 0) {
          $category_data = xtc_get_category_data($categories_id);
          $data = array(
            'name' => $category_data['categories_name'],
            'id' => $category_data['categories_id'],
            'link' => xtc_href_link(FILENAME_DEFAULT, 'cPath='.$path, 'NONSSL'),
            'active' => ((end($cPath_array) == $category_data['categories_id']) ? 1 : 0),
          );

          if (SHOW_COUNTS == 'true') {
            $products_in_category = xtc_count_products_in_category($category_data['categories_id']);
            if ($products_in_category > 0) {
              $data['count'] = $products_in_category;
            }
          }  
          
          if (defined('CATEGORIES_CHECK_SUBS') && (CATEGORIES_CHECK_SUBS == true)) {
            $data['hasSubs'] = 0;
            $tmpCheck = xtc_has_category_subcategories($category_data['categories_id']);
            if ($tmpCheck) {
              $data['hasSubs'] = 1;
            }
          }
        }
        
        $subs = xtc_get_categories_tree_data($categories_id, 1);
        if (count($subs) > 0) {                   
          foreach ($subs as $subs_id => $subs_data) {
            if (mod_count_products_in_category($subs_data['id']) > 0) {
              $link_path = $path . (($path != '') ? '_' : '') . $subs_data['id'];
              $subs[$subs_id]['level'] = 1;
              $subs[$subs_id]['link'] = xtc_href_link(FILENAME_DEFAULT, 'cPath='.$link_path, 'NONSSL');
              $subs[$subs_id]['active'] = ((end($cPath_array) == $subs_data['id']) ? 1 : 0);
    
              if (SHOW_COUNTS == 'true') {
                $products_in_category = xtc_count_products_in_category($subs_data['id']);
                if ($products_in_category > 0) {
                  $subs[$subs_id]['count'] = $products_in_category;
                }
              }  
              
              if (defined('CATEGORIES_CHECK_SUBS') && (CATEGORIES_CHECK_SUBS == true)) {
                $subs[$subs_id]['hasSubs'] = 0;
                $tmpCheck = xtc_has_category_subcategories($subs_data['id']);
                if ($tmpCheck) {
                  $subs[$subs_id]['hasSubs'] = 1;
                }
              }
            } else {
              unset($subs[$subs_data['id']]);
            }
          }
          
          $category_tree_array[$categories_id] = array(
            'DATA' => $data,
            'SUBS' => $subs,
          );
        }        
      }      
    }
        
    if (count($category_tree_array) > 0) {
      $box_smarty->assign('BOX_CONTENT', array(array_pop($category_tree_array)));
      $box_smarty->assign('BOX_CONTENT_HOVER', $category_tree_array);
    }
  }

  $box_sub_categories = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_sub_categories.html', $cache_id);

  $smarty->assign('box_SUB_CATEGORIES', $box_sub_categories);
