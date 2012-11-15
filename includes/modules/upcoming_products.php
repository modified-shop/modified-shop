<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(upcoming_products.php,v 1.23 2003/02/12); www.oscommerce.com
   (c) 2003	nextcommerce (upcoming_products.php,v 1.7 2003/08/22); www.nextcommerce.org
   (c) 2006 XT-Commerce (upcoming_products.php)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
//BOF - Dokuman - 2009-09-02: show upcoming products only when greater zero
if (MAX_DISPLAY_UPCOMING_PRODUCTS != '0') {
//EOF - Dokuman - 2009-09-02: show upcoming products only when greater zero
  $module_smarty = new Smarty;
  $smarty->caching = false; //DokuMan - 2012-10-30 - avoid Smarty caching in order to display the correct data, if caching is enabled in shop backend

  //BOF - GTB - 2010-08-03 - Security Fix - Base
  $module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  //$module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
  //EOF - GTB - 2010-08-03 - Security Fix - Base
  // include needed functions
  require_once (DIR_FS_INC.'xtc_date_short.inc.php');
  $module_content = array ();

  //fsk18 lock
  $fsk_lock = '';
  if ($_SESSION['customers_status']['customers_fsk18_display'] == '0')
    $fsk_lock = ' and p.products_fsk18!=1';

  $group_check = '';
  if (GROUP_CHECK == 'true')
    $group_check = "and p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";

//BOF - GTB/vr - 2011-01-24 - Bugfix: only show active products
/* $expected_query = xtDBquery("select p.products_id,
                                    pd.products_name,
                                    products_date_available as date_expected
                                    from ".TABLE_PRODUCTS." p,
                                    ".TABLE_PRODUCTS_DESCRIPTION." pd
                                    where to_days(products_date_available) >= to_days(now())
                                    and p.products_id = pd.products_id
                                    ".$group_check."
                                    ".$fsk_lock."
                                    and pd.language_id = '".(int) $_SESSION['languages_id']."'
                                    order by ".EXPECTED_PRODUCTS_FIELD." ".EXPECTED_PRODUCTS_SORT."
                                    limit ".MAX_DISPLAY_UPCOMING_PRODUCTS); */
$expected_query = xtDBquery("select p.products_id,
                                    pd.products_name,
                                    p.products_date_available as date_expected
                                    from ".TABLE_PRODUCTS." p
                                    join ".TABLE_PRODUCTS_DESCRIPTION." pd on pd.products_id = p.products_id
                                    where to_days(p.products_date_available) >= to_days(now())
                                    ".$group_check."
                                    ".$fsk_lock."
                                    and pd.language_id = '".(int) $_SESSION['languages_id']."'
                                    and p.products_status = '1'
                                    order by ".EXPECTED_PRODUCTS_FIELD." ".EXPECTED_PRODUCTS_SORT."
                                    limit ".MAX_DISPLAY_UPCOMING_PRODUCTS);
//BOF - GTB/vr - 2011-01-24 - Bugfix: only show active products
                                    
  if (xtc_db_num_rows($expected_query,true) > 0) {

    $row = 0;
    while ($expected = xtc_db_fetch_array($expected_query,true)) {
      $row ++;
      $module_content[] = array (
      'PRODUCTS_LINK' => xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($expected['products_id'], $expected['products_name'])),
      'PRODUCTS_NAME' => $expected['products_name'],
      'PRODUCTS_DATE' => xtc_date_short($expected['date_expected'])
      );
    }

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->assign('module_content', $module_content);
    // set cache ID
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/upcoming_products.html');
    $default_smarty->assign('MODULE_upcoming_products', $module);
  }
//BOF - Dokuman - 2009-09-02: show upcoming products only when greater zero
}
//BOF - Dokuman - 2009-09-02: show upcoming products only when greater zero
?>