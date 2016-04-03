<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2016 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(upcoming_products.php,v 1.23 2003/02/12); www.oscommerce.com 
   (c) 2003	 nextcommerce (upcoming_products.php,v 1.7 2003/08/22); www.nextcommerce.org
   (c) 2003 XT-Commerce (upcoming_products.php r 1243 2005-09-25 ) www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

if (MAX_DISPLAY_UPCOMING_PRODUCTS != '0') {

  $module_smarty = new Smarty;
  $module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

  // include needed functions
  require_once (DIR_FS_INC.'xtc_date_short.inc.php');

  $expected_query = xtDBquery("SELECT p.products_id, 
                                      pd.products_name, 
                                      products_date_available as date_expected
                                 FROM ".TABLE_PRODUCTS." p
                                 JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                      ON p.products_id = pd.products_id
                                         AND pd.language_id = ".(int)$_SESSION['languages_id']."
                                         AND pd.products_name <> ''
                                WHERE to_days(products_date_available) >= to_days(now())
                                  AND p.products_status = 1
                                      ".PRODUCTS_CONDITIONS_P."
                             ORDER BY ".EXPECTED_PRODUCTS_FIELD." ".EXPECTED_PRODUCTS_SORT."
                                LIMIT ".MAX_DISPLAY_UPCOMING_PRODUCTS);

  if (xtc_db_num_rows($expected_query,true) > 0) {
    $module_content = array ();
    while ($expected = xtc_db_fetch_array($expected_query,true)) {
      $module_content[] = array (
          'PRODUCTS_LINK' => xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($expected['products_id'], $expected['products_name'])),
          'PRODUCTS_NAME' => $expected['products_name'],
          'PRODUCTS_DATE' => xtc_date_short($expected['date_expected'])
        );
    }

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->assign('module_content', $module_content);
    $module_smarty->caching = 0;
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/upcoming_products.html');

    $default_smarty->assign('MODULE_upcoming_products', $module);
  }
}
?>