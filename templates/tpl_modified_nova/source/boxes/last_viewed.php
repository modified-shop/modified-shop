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

  if (isset($_SESSION['tracking']['products_history']) && count($_SESSION['tracking']['products_history']) > 0) {

    // set cache id
    $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|curr:'.$_SESSION['currency'].'|history:'.implode(',', $_SESSION['tracking']['products_history']));

    if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_last_viewed.html', $cache_id) || !$cache) {

      $last_viewed_query = xtDBquery("SELECT ".$product->default_select.",
                                             p2c.categories_id,
                                             cd.categories_name
                                        FROM " . TABLE_PRODUCTS . " p
                                        JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                             ON p.products_id = pd.products_id
                                                AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                                AND trim(pd.products_name) != ''
                                        JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                             ON p.products_id = p2c.products_id
                                        JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                             ON cd.categories_id = p2c.categories_id
                                                AND cd.language_id = '".(int)$_SESSION['languages_id']."'
                                       WHERE p.products_status = '1'
                                         AND p.products_id IN ('".implode("', '", $_SESSION['tracking']['products_history'])."')
                                             ".PRODUCTS_CONDITIONS_P."
                                    ORDER BY FIELD(p.products_id, ".implode(',',array_reverse($_SESSION['tracking']['products_history'])).")");
        
      $box_content = array();
      if (xtc_db_num_rows($last_viewed_query, true) > 0) {
        while ($last_viewed = xtc_db_fetch_array($last_viewed_query, true)) {
          $box_content[$last_viewed['products_id']] = $product->buildDataArray($last_viewed);
          $box_content[$last_viewed['products_id']]['CATEGORY_LINK'] = xtc_href_link(FILENAME_DEFAULT, xtc_category_link($last_viewed['categories_id'], $last_viewed['categories_name']));
          $box_content[$last_viewed['products_id']]['CATEGORY_NAME'] = $last_viewed['categories_name'];
        }
      }
    
      $box_smarty->assign('BOX_CONTENT', $box_content);
      $box_smarty->assign('MY_PERSONAL_PAGE', xtc_href_link(FILENAME_ACCOUNT));
    }

    $box_last_viewed = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_last_viewed.html', $cache_id);

    $smarty->assign('box_LAST_VIEWED', $box_last_viewed);
  }
