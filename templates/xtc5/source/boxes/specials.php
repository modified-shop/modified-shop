<?php
/* -----------------------------------------------------------------------------------------
   $Id:$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.30 2003/02/10); www.oscommerce.com 
   (c) 2003	nextcommerce (specials.php,v 1.10 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce (specials.php 1292 2005-10-07 mz); www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once (DIR_FS_INC.'xtc_random_select.inc.php');

  if ($random_product = xtc_random_select("SELECT p.products_id,
                                                  pd.products_name,
                                                  p.products_price,
                                                  p.products_tax_class_id,
                                                  p.products_image,
                                                  s.expires_date,
                                                  p.products_vpe,
                                                  p.products_vpe_status,
                                                  p.products_vpe_value,
                                                  s.specials_new_products_price
                                             FROM ".TABLE_PRODUCTS." p
                                             JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                                  ON pd.products_id = p.products_id
                                                     AND trim(pd.products_name) != ''
                                                     AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                             JOIN ".TABLE_SPECIALS." s 
                                                  ON p.products_id = s.products_id
                                                     AND s.status = '1'
                                            WHERE p.products_status = '1'
                                                  ".PRODUCTS_CONDITIONS_P."                                             
                                         ORDER BY s.specials_date_added DESC
                                            LIMIT ".MAX_RANDOM_SELECT_SPECIALS)) {

  $box_smarty = new smarty;
  $box_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

  $box_smarty->assign('box_content', $product->buildDataArray($random_product));
  $box_smarty->assign('SPECIALS_LINK', xtc_href_link(FILENAME_SPECIALS));

  $box_smarty->assign('language', $_SESSION['language']);
  if ($random_product["products_id"] != '') {
    // set cache ID
     if (!CacheCheck()) {
      $box_smarty->caching = 0;
      $box_specials = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_specials.html');
    } else {
      $box_smarty->caching = 1;
      $box_smarty->cache_lifetime = CACHE_LIFETIME;
      $box_smarty->cache_modified_check = CACHE_CHECK;
      $cache_id = $_SESSION['language'].$random_product["products_id"].$_SESSION['customers_status']['customers_status_name'];
      $box_specials = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_specials.html', $cache_id);
    }
    $smarty->assign('box_SPECIALS', $box_specials);
  }
}
?>