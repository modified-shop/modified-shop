<?php
/* -----------------------------------------------------------------------------------------
   $Id: print_product_info.php 3429 2012-08-17 10:09:04Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_info.php,v 1.94 2003/05/04); www.oscommerce.com
   (c) 2003   nextcommerce (print_product_info.php,v 1.16 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_date_long.inc.php');
require_once (DIR_FS_INC.'xtc_date_short.inc.php'); 
require_once (DIR_FS_INC.'xtc_get_products_mo_images.inc.php');

// create smarty elements
$info_smarty = new Smarty;
$info_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
$info_smarty->assign('charset', $_SESSION['language_charset'] ); 
if (DIR_WS_BASE == '') {
  $info_smarty->assign('base_href', (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG);
}
if (isset($_GET['pID']) && $_GET['pID']!='') {
  $_GET['products_id'] = xtc_get_prid($_GET['pID']);
  $info_smarty->assign('noprint',true); 
}
if (isset($_GET['products_id']) && $_GET['products_id']!='') {
  $product = new product((int)$_GET['products_id']);
}
if (!is_object($product) || $product->isProduct() === false || $language_not_found === true) {
  // create smarty elements
  $smarty = new Smarty;

  // include boxes
  require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

  // product not found in database
  $site_error = TEXT_PRODUCT_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);

  require (DIR_WS_INCLUDES.'header.php');

  $smarty->assign('language', $_SESSION['language']);
  $smarty->caching = 0;
  if (!defined('RM'))
    $smarty->load_filter('output', 'note');
  $smarty->display(CURRENT_TEMPLATE.'/index.html');

  include ('includes/application_bottom.php');
} else {
  
  // Get manufacturer name etc. for the product page
  $manufacturer_query = xtc_db_query("SELECT m.manufacturers_id,
                                             m.manufacturers_name,
                                             m.manufacturers_image,
                                             mi.manufacturers_url
                                        FROM " . TABLE_MANUFACTURERS . " m
                                   LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                                          ON (m.manufacturers_id = mi.manufacturers_id
                                         AND mi.languages_id = '" . (int)$_SESSION['languages_id'] . "')
                                        JOIN " . TABLE_PRODUCTS . " p
                                             ON p.manufacturers_id = m.manufacturers_id
                                       WHERE p.products_id = '" . $product->data['products_id'] . "'");
  if (xtc_db_num_rows($manufacturer_query)) {
    $manufacturer = xtc_db_fetch_array($manufacturer_query);
    if ($manufacturer['manufacturers_image'] != '') {
      $image = DIR_WS_IMAGES.$manufacturer['manufacturers_image'];
      if (!file_exists(DIR_FS_CATALOG.$image)) {
        if (MANUFACTURER_IMAGE_SHOW_NO_IMAGE == 'true') {
          $image = DIR_WS_IMAGES.'manufacturers/noimage.gif';
        } else {
          $image = '';
        }
      }
    }
    $info_smarty->assign('MANUFACTURER_IMAGE', (($image != '') ? DIR_WS_BASE . $image : ''));
    $info_smarty->assign('MANUFACTURER', $manufacturer['manufacturers_name']);
    $info_smarty->assign('MANUFACTURER_LINK', xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link($manufacturer['manufacturers_id'], $manufacturer['manufacturers_name'])));
  }

  // load all definitions from product class
  foreach ($product->buildDataArray($product->data, 'info') as $key => $value) {
    $info_smarty->assign($key, $value);
  }

  /*
   * assign smarty additional variables or overwrite them
   * START
   */
  
  // show expiry date of active special products
  $special_expires_date_query = "SELECT expires_date
                                   FROM ".TABLE_SPECIALS."
                                  WHERE products_id = '".$product->data['products_id']."'
                                    AND status = '1'
                                    AND (start_date IS NULL 
                                         OR start_date <= NOW())";
  $special_expires_date_query = xtDBquery($special_expires_date_query);
  if (xtc_db_num_rows($special_expires_date_query, true) > 0) {
    $sDate = xtc_db_fetch_array($special_expires_date_query, true);
    $info_smarty->assign('PRODUCTS_EXPIRES', $sDate['expires_date'] != '0000-00-00 00:00:00' ? xtc_date_short($sDate['expires_date']) : '');
  }

  // FSK18
  $info_smarty->assign('PRODUCTS_FSK18', $product->data['products_fsk18'] == '1' ? 'true' : '');

  //get shippingstatus image and name
  if (ACTIVATE_SHIPPING_STATUS == 'true') {
    $info_smarty->assign('SHIPPING_NAME', $main->getShippingStatusName($product->data['products_shippingtime']));
    $info_smarty->assign('SHIPPING_NAME_LINK', $main->getShippingStatusName($product->data['products_shippingtime'], true));
    $info_smarty->assign('SHIPPING_IMAGE', $main->getShippingStatusImage($product->data['products_shippingtime']));
  }

  // price incl tax and shipping link
  if ($_SESSION['customers_status']['customers_status_show_price'] != '0') {
    if (isset($xtPrice->TAX[$product->data['products_tax_class_id']])) {
      $tax_info = $main->getTaxInfo($xtPrice->TAX[$product->data['products_tax_class_id']]);
      $info_smarty->assign('PRODUCTS_TAX_INFO', $tax_info);
    }
    $info_smarty->assign('PRODUCTS_SHIPPING_LINK', SHIPPING_EXCL.' '.SHIPPING_COSTS);
  }

  $info_smarty->assign('PRODUCTS_DESCRIPTION', stripslashes($product->data['products_description']));
  $info_smarty->assign('PRODUCTS_SHORT_DESCRIPTION', stripslashes($product->data['products_short_description']));
  $info_smarty->assign('PRODUCTS_URL', !empty($product->data['products_url']) ? sprintf(TEXT_MORE_INFORMATION, xtc_href_link(FILENAME_REDIRECT, 'action=product&id='.$product->data['products_id'], 'NONSSL', true, false)) : '');

  // more images
  $mo_images = xtc_get_products_mo_images($product->data['products_id']);
  if ($mo_images != false) {
    $more_images_data = array();
    foreach ($mo_images as $img) {
      $mo_img = $product->productImage($img['image_name'], 'thumbnail');
      if ($mo_img != '') {
        $more_images_data[] = array ('PRODUCTS_IMAGE' => $mo_img);
      }
    }
    $info_smarty->assign('more_images', $more_images_data);
  }

  // product discount
  $discount = 0.00;
  if ($_SESSION['customers_status']['customers_status_public'] == '1' && $_SESSION['customers_status']['customers_status_discount'] != '0.00') {
    $discount = $_SESSION['customers_status']['customers_status_discount'];
    if ($product->data['products_discount_allowed'] < $_SESSION['customers_status']['customers_status_discount'])
      $discount = $product->data['products_discount_allowed'];
    if ($discount != '0.00')
      $info_smarty->assign('PRODUCTS_DISCOUNT', $discount.'%');
  }

  // date available/added
  if ($product->data['products_date_available'] > date('Y-m-d H:i:s')) {
    $info_smarty->assign('PRODUCTS_DATE_AVIABLE', sprintf(TEXT_DATE_AVAILABLE, xtc_date_long($product->data['products_date_available'])));
    $info_smarty->assign('PRODUCTS_DATE_AVAILABLE', sprintf(TEXT_DATE_AVAILABLE, xtc_date_long($product->data['products_date_available']))); 
  } elseif ($product->data['products_date_added'] != '0000-00-00 00:00:00') {
    $info_smarty->assign('PRODUCTS_ADDED', sprintf(TEXT_DATE_ADDED, xtc_date_long($product->data['products_date_added'])));
  }

  /*
   * assign smarty additional variables or overwrite them
   * END
   */
 
  //include modules
  if ($_SESSION['customers_status']['customers_status_graduated_prices'] == 1) {
    include (DIR_WS_MODULES.FILENAME_GRADUATED_PRICE);
  }
  include (DIR_WS_MODULES.FILENAME_PRODUCTS_MEDIA);

  include (DIR_WS_MODULES.'product_attributes.php');
  $module_content = array();
  if (isset($products_options_data) && is_array($products_options_data)) {
    foreach ($products_options_data as $attributes) {
      foreach ($attributes['DATA'] as $key => $value) {
        $module_content[] = array('GROUP' => $attributes['NAME'],
                                  'NAME' => $value['TEXT'] . ((isset($value['PREFIX'])) ? ' ('.$value['PREFIX'].$value['PRICE'].')' : '')
                                  );
      }
    }
    $info_smarty->assign('module_content', $module_content);
  }
  
  //canonical_link -> set canonical tag in /template/.../module/print_product_info.html
  $canonical_link = xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product->data['products_id'],$request_type,false);
  $info_smarty->assign('CanonicalLink', $canonical_link);

  $info_smarty->assign('language', $_SESSION['language']);

  // set cache ID
   if (!CacheCheck()) {
    $info_smarty->caching = 0;
  } else {
    $info_smarty->caching = 1;
    $info_smarty->cache_lifetime = CACHE_LIFETIME;
    $info_smarty->cache_modified_check = CACHE_CHECK;
  }
  $cache_id = md5($_SESSION['language'].'_'.$product->data['products_id']);

  $info_smarty->display(CURRENT_TEMPLATE.'/module/print_product_info.html', $cache_id);
}
?>