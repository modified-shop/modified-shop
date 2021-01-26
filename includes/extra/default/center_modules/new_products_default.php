<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2016 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_products.php,v 1.33 2003/02/12); www.oscommerce.com
   (c) 2003 nextcommerce (new_products.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2006 xt:Commerce (new_products.php 1292 2005-10-07); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once (DIR_FS_INC.'get_pictureset_data.inc.php');

if (MAX_DISPLAY_NEW_PRODUCTS != '0') {
  //count products on startpage
  $count_query = xtc_db_query("SELECT count(*) as total
                                 FROM ".TABLE_PRODUCTS." p
                                 JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                      ON p.products_id = pd.products_id
                                         AND trim(pd.products_name) != ''
                                         AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                                WHERE p.products_startpage = 1
                                  AND p.products_status = 1
                                      ".PRODUCTS_CONDITIONS_P);
  $count = xtc_db_fetch_array($count_query);
  $startpage_total = $count['total'];

  $order_by = "p.products_startpage_sort ASC";
  if ($startpage_total > MAX_DISPLAY_NEW_PRODUCTS) {
    $order_by .= ",MD5(CONCAT(p.products_id, CURRENT_TIMESTAMP))";
  }

  if ($startpage_total > 0) {
    $new_products_query = "SELECT ".$product->default_select.",
                                  m.manufacturers_name
                             FROM ".TABLE_PRODUCTS." p
                        LEFT JOIN ".TABLE_MANUFACTURERS." m
                                  ON p.manufacturers_id = m.manufacturers_id
                             JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                  ON p.products_id = pd.products_id
                                     AND pd.products_name <> ''
                                     AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                            WHERE p.products_startpage = 1
                              AND p.products_status = 1
                                  ".PRODUCTS_CONDITIONS_P."
                         GROUP BY p.products_id
                         ORDER BY ".$order_by."
                            LIMIT ".MAX_DISPLAY_NEW_PRODUCTS;

    $check_new_products_query = xtDBquery($new_products_query);
    $startpage_total = xtc_db_num_rows($check_new_products_query, true);
  }

  if ($startpage_total < 1) {
      $days = '';
      if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
          $date_new_products = date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
          $days = " AND p.products_date_added > '".$date_new_products."' ";
      }
      $new_products_query = "SELECT ".$product->default_select.",
                                    m.manufacturers_name
                               FROM ".TABLE_PRODUCTS." p
                          LEFT JOIN ".TABLE_MANUFACTURERS." m
                                    ON p.manufacturers_id = m.manufacturers_id
                               JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                    ON p.products_id = pd.products_id
                                       AND pd.products_name <> ''
                                       AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                               JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                    ON p.products_id = p2c.products_id
                               JOIN ".TABLE_CATEGORIES." c
                                    ON c.categories_id = p2c.categories_id
                                       AND c.categories_status = 1
                              WHERE p.products_status = 1
                                    ".PRODUCTS_CONDITIONS_P."
                                    ".$days."
                           GROUP BY p.products_id
                           ORDER BY MD5(CONCAT(p.products_id, CURRENT_TIMESTAMP))
                              LIMIT ".MAX_DISPLAY_NEW_PRODUCTS;
  }

  $module_content = array();
  $new_products_query = xtDBquery($new_products_query);
  while ($new_products = xtc_db_fetch_array($new_products_query, true)) {
      $module_content[] = $product->buildDataArray($new_products);
  }

  if (sizeof($module_content) >= 1) {

      $module_smarty = new Smarty;
      $module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

      $module_smarty->assign('STARTPAGE', 'true');

      $module_smarty->assign('language', $_SESSION['language']);
      $module_smarty->assign('module_content', $module_content);

      if (defined('PICTURESET_BOX')) {
        $module_smarty->assign('pictureset_box', get_pictureset_data(PICTURESET_BOX));
      }
      if (defined('PICTURESET_ROW')) {
        $module_smarty->assign('pictureset_row', get_pictureset_data(PICTURESET_ROW));
      }

      // set cache ID
      if (!CacheCheck()) {
          $module_smarty->caching = 0;
          $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/new_products_default.html');
      } else {
          $module_smarty->caching = 1;
          $module_smarty->cache_lifetime = CACHE_LIFETIME;
          $module_smarty->cache_modified_check = CACHE_CHECK;
          $cache_id = md5('0'.$_SESSION['language'].$_SESSION['customers_status']['customers_status_name'].$_SESSION['currency']);
          $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/new_products_default.html', $cache_id);
      }
      $default_smarty->assign('MODULE_new_products', $module);
  }
}
?>