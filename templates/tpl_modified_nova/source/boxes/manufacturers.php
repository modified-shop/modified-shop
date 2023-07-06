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
  $cache_id = md5('lID:'.$_SESSION['language'].'|mID:'.(isset($_GET['manufacturers_id']) ? (int)$_GET['manufacturers_id'] : '0'));

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_manufacturers.html', $cache_id) || !$cache) {  
    $manufacturers_query = xtDBquery("SELECT m.*
                                        FROM ".TABLE_MANUFACTURERS." as m
                                        JOIN ".TABLE_PRODUCTS." as p 
                                             ON m.manufacturers_id = p.manufacturers_id
                                                AND p.products_status = '1'
                                                    ".PRODUCTS_CONDITIONS_P."
                                        JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                             ON p.products_id = pd.products_id
                                                AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                                AND trim(pd.products_name) != ''
                                        JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                                             ON p2c.products_id = pd.products_id
                                        JOIN ".TABLE_CATEGORIES." c
                                             ON c.categories_id = p2c.categories_id
                                                AND c.categories_status = 1
                                                    ".CATEGORIES_CONDITIONS_C."
                                       WHERE m.manufacturers_status = 1
                                    GROUP BY m.manufacturers_id 
                                    ORDER BY m.sort_order, m.manufacturers_name ASC");
  
    if (xtc_db_num_rows($manufacturers_query, true) > 0) {
      $box_content = array();
      while ($manufacturers = xtc_db_fetch_array($manufacturers_query, true)) {
        $image = $main->getImage($manufacturers['manufacturers_image'], 'manufacturers/', MANUFACTURER_IMAGE_SHOW_NO_IMAGE, 'manufacturers/noimage.gif');
      
        $box_content[] = array(
          'NAME' => $manufacturers['manufacturers_name'],
          'LINK' => xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link($manufacturers['manufacturers_id'],$manufacturers['manufacturers_name'])),
          'IMAGE' => (($image != '') ? DIR_WS_BASE . $image : ''),
          'SELECTED' => ((isset($_GET['manufacturers_id']) && (int)$_GET['manufacturers_id'] == $manufacturers['manufacturers_id']) ? 1 : 0),
        );
      }

      $box_smarty->assign('box_content', $box_content);
    }  
  }

  $box_manufacturers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_manufacturers.html', $cache_id);

  $smarty->assign('box_MANUFACTURERS', $box_manufacturers);
