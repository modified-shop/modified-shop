<?php
  /* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  $box_smarty = new smarty;
  //BOF - GTB - 2010-08-03 - Security Fix - Base
  $box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  //$box_smarty->assign('tpl_path', 'templates/' . CURRENT_TEMPLATE . '/');
  //EOF - GTB - 2010-08-03 - Security Fix - Base
  $box_content = '';

  if (isset ($_SESSION['tracking']['products_history'][0])) {
    // include needed functions
    require_once (DIR_FS_INC . 'xtc_get_path.inc.php');
    require_once (DIR_FS_INC . 'xtc_get_products_name.inc.php');
    $max = count($_SESSION['tracking']['products_history']);
    $max--;
    $random_last_viewed = mt_rand(0, $max);

    // query restrictions
    if ($_SESSION['customers_status']['customers_fsk18_display'] == '0') { 
      $fsk_lock = ' and p.products_fsk18!=1'; 
    } else {
      $fsk_lock = ""; 
    }
    $group_check = '';  //DokuMan - 2010-03-12 - set undefined variable 
    if (GROUP_CHECK == 'true') { 
      $group_check = " and p.group_permission_" . $_SESSION['customers_status']['customers_status_id'] . "=1 "; 
    } else {
      $group_check = ""; 
    }

    //BOF - Dokuman - 2010-01-29 - category name selection included in one select statement
    /*
    $random_query = "select p.products_id,
                             pd.products_name,
                             p.products_price,
                             p.products_tax_class_id,
                             p.products_image,
                             p.products_vpe,
                             p.products_vpe_status,
                             p.products_vpe_value
                             FROM
                             " . TABLE_PRODUCTS . " p,
                             " . TABLE_PRODUCTS_DESCRIPTION . " pd
                             where p.products_status = '1'
                             and p.products_id = '" . (int) $_SESSION['tracking']['products_history'][$random_last_viewed] . "'
                             and pd.products_id = '" . (int) $_SESSION['tracking']['products_history'][$random_last_viewed] . "'
                             and pd.language_id = '" . $_SESSION['languages_id'] . "'
                             " . $group_check . "
                             " . $fsk_lock;
    */
    //EOF - Dokuman - 2010-01-29 - category name selection included in one select statement

    $random_query = "select p.products_id,
                            pd.products_name,
                            p.products_price,
                            p.products_tax_class_id,
                            p.products_image,
                            p2c.categories_id,
                            p.products_vpe,
                            p.products_vpe_status,
                            p.products_vpe_value,
                            cd.categories_name
                       FROM " . TABLE_PRODUCTS . " p,
                            " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                            " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c,
                            " . TABLE_CATEGORIES_DESCRIPTION . " cd
                      WHERE p.products_status = '1'
                        AND p.products_id = '".(int)$_SESSION['tracking']['products_history'][$random_last_viewed]."'
                        AND pd.products_id = '".(int)$_SESSION['tracking']['products_history'][$random_last_viewed]."'
                        AND p2c.products_id = '".(int)$_SESSION['tracking']['products_history'][$random_last_viewed]."'
                        AND pd.language_id = '" . $_SESSION['languages_id'] . "'
                        AND cd.categories_id = p2c.categories_id
                            ".$group_check."
                            ".$fsk_lock."
                        AND cd.language_id = '" . $_SESSION['languages_id'] . "'";

    $random_query = xtDBquery($random_query);
    $random_product = xtc_db_fetch_array($random_query, true);

    //BOF - Dokuman - 2010-01-29 - category name selection included in one select statement
    /*
    // get category name
    $categories_query = xtDBquery("select
                                  cd.categories_name
                                  from ".TABLE_CATEGORIES_DESCRIPTION." cd,
                                  ".TABLE_CATEGORIES." c,
                                  " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                  WHERE c.categories_id=cd.categories_id
                                  and p2c.products_id = '".(int) $_SESSION['tracking']['products_history'][$random_last_viewed]."'
                                  and cd.language_id='".(int) $_SESSION['languages_id']."'");
    $categories_data = xtc_db_fetch_array($categories_query,true);
    */
    //EOF - Dokuman - 2010-01-29 - category name selection included in one select statement
    $random_products_price = $xtPrice->xtcGetPrice($random_product['products_id'], $format = true, 1, $random_product['products_tax_class_id'], $random_product['products_price']);
    $category_path = xtc_get_path($random_product['categories_id']);
    if ($random_product['products_name'] != '') {
      $box_smarty->assign('box_content', $product->buildDataArray($random_product));
      $box_smarty->assign('MY_PAGE', 'TEXT_MY_PAGE');
      $box_smarty->assign('WATCH_CATGORY', 'TEXT_WATCH_CATEGORY');
      $box_smarty->assign('MY_PERSONAL_PAGE', xtc_href_link(FILENAME_ACCOUNT));
      //BOF - Dokuman - 2010-01-29 - category name selection included in one select statement
      //$box_smarty->assign('CATEGORY_LINK', xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories_data['categories_id'], $categories_data['categories_name'])));
      //$box_smarty->assign('CATEGORY_NAME', $categories_data['categories_name']);
      $box_smarty->assign('CATEGORY_LINK',xtc_href_link(FILENAME_DEFAULT, xtc_category_link($random_product['categories_id'],$random_product['categories_name'])));
      $box_smarty->assign('CATEGORY_NAME',$random_product['categories_name']);
      //BOF - Dokuman - 2010-01-29 - category name selection included in one select statement
      $box_smarty->assign('language', $_SESSION['language']);
      // set cache ID
      if (!CacheCheck()) {
        $box_smarty->caching = 0;
        $box_last_viewed = $box_smarty->fetch(CURRENT_TEMPLATE . '/boxes/box_last_viewed.html');
      } else {
        $box_smarty->caching = 1;
        $box_smarty->cache_lifetime=CACHE_LIFETIME;
        $box_smarty->cache_modified_check=CACHE_CHECK;
        $cache_id = $_SESSION['language'].$random_product['products_id'].$_SESSION['customers_status']['customers_status_name'];
        $box_last_viewed = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_last_viewed.html',$cache_id);
      }
      $smarty->assign('box_LAST_VIEWED', $box_last_viewed);
    }
  }
?>