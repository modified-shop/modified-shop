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
$info_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
$info_smarty->assign('charset', $_SESSION['language_charset'] ); 

if (isset($_GET['pID']) && $_GET['pID']!='') {
  $_GET['products_id'] = xtc_get_prid($_GET['pID']);
  $info_smarty->assign('noprint',true); 
}
if (isset($_GET['products_id']) && $_GET['products_id']!='') {
  $product = new product((int)$_GET['products_id']);
}
if (!is_object($product) || !$product->isProduct()) {
  // product not found in database
  $error = TEXT_PRODUCT_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
} else {
  // defaults
  $hide_qty = 0;  
  $module_content = '';
  
  // Get manufacturer name etc. for the product page
  $manufacturer_query = xtc_db_query("SELECT m.manufacturers_id,
                                             m.manufacturers_name,
                                             m.manufacturers_image,
                                             mi.manufacturers_url
                                        FROM " . TABLE_MANUFACTURERS . " m
                                   LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                                          ON (m.manufacturers_id = mi.manufacturers_id
                                         AND mi.languages_id = '" . (int)$_SESSION['languages_id'] . "'),
                                             " . TABLE_PRODUCTS . " p
                                       WHERE p.products_id = '" . $product->data['products_id'] . "'
                                         AND p.manufacturers_id = m.manufacturers_id");
  if (xtc_db_num_rows($manufacturer_query)) {
    $manufacturer = xtc_db_fetch_array($manufacturer_query);
    $info_smarty->assign('MANUFACTURER_IMAGE', (!empty($manufacturer['manufacturers_image']) ? DIR_WS_IMAGES.$manufacturer['manufacturers_image'] : ''));
    $info_smarty->assign('MANUFACTURER', $manufacturer['manufacturers_name']);
    $info_smarty->assign('MANUFACTURER_LINK', xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link($manufacturer['manufacturers_id'], $manufacturer['manufacturers_name'])));
  }

  // build products price
  $products_price = $xtPrice->xtcGetPrice(
                                $product->data['products_id'],
                                $format = true,
                                1,
                                $product->data['products_tax_class_id'],
                                $product->data['products_price'],
                                1
                              );
                              
  $products_attributes_query = xtc_db_query("select count(*) as total 
                                               from ".TABLE_PRODUCTS_OPTIONS." popt, 
                                                    ".TABLE_PRODUCTS_ATTRIBUTES." patrib 
                                              where patrib.products_id='".$product->data['products_id']."' 
                                                and patrib.options_id = popt.products_options_id 
                                                and popt.language_id = '".(int) $_SESSION['languages_id']."'
                                            ");
  $products_attributes = xtc_db_fetch_array($products_attributes_query);
  if ($products_attributes['total'] > 0) {
    $products_options_name_query = xtc_db_query("select distinct popt.products_options_id, 
                                                                 popt.products_options_name 
                                                            from ".TABLE_PRODUCTS_OPTIONS." popt, 
                                                                 ".TABLE_PRODUCTS_ATTRIBUTES." patrib 
                                                           where patrib.products_id='".$product->data['products_id']."' 
                                                             and patrib.options_id = popt.products_options_id 
                                                             and popt.language_id = '".(int) $_SESSION['languages_id']."' 
                                                        order by popt.products_options_name
                                                ");
    while ($products_options_name = xtc_db_fetch_array($products_options_name_query)) {
      $products_options_query = xtc_db_query(" select pov.products_options_values_id,
                                                      pov.products_options_values_name,
                                                      pa.options_values_price,
                                                      pa.price_prefix,pa.attributes_stock,
                                                      pa.attributes_model
                                                 from ".TABLE_PRODUCTS_ATTRIBUTES." pa,
                                                      ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
                                                where pa.products_id = '".$product->data['products_id']."'
                                                  and pa.options_id = '".$products_options_name['products_options_id']."'
                                                  and pa.options_values_id = pov.products_options_values_id
                                                  and pov.language_id = '".(int) $_SESSION['languages_id']."'
                                             order by pa.sortorder
                                            ");
      while ($products_options = xtc_db_fetch_array($products_options_query)) {
        $module_content[] = array ('GROUP' => $products_options_name['products_options_name'], 
                                    'NAME' => $products_options['products_options_values_name']
                                  );
        if ($products_options['options_values_price'] != '0') {
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) {
            $tax_rate = $xtPrice->TAX[$product->data['products_tax_class_id']];
            $products_options['options_values_price'] = xtc_add_tax($products_options['options_values_price'], $xtPrice->TAX[$product->data['products_tax_class_id']]);
          }
          if ($_SESSION['customers_status']['customers_status_show_price'] == 1) {
            $module_content[sizeof($module_content) - 1]['NAME'] .= ' ('.$products_options['price_prefix'].$xtPrice->xtcFormat($products_options['options_values_price'], true,0,true).')';
          }
        }
      }
    }
  }
  $info_smarty->assign('module_content', $module_content);
  
  // show expiry date of active special products
  $special_expires_date_query = "SELECT expires_date
                                   FROM ".TABLE_SPECIALS."
                                  WHERE products_id = '".$product->data['products_id']."'
                                    AND status = '1'";
  $special_expires_date_query = xtDBquery($special_expires_date_query);
  $sDate = xtc_db_fetch_array($special_expires_date_query, true);
  $info_smarty->assign('PRODUCTS_EXPIRES', $sDate['expires_date'] != '0000-00-00 00:00:00' ? xtc_date_short($sDate['expires_date']) : '');

  // FSK18
  $info_smarty->assign('PRODUCTS_FSK18', $product->data['products_fsk18'] == '1' ? 'true' : '');

  //get shippingstatus image and name
  if (ACTIVATE_SHIPPING_STATUS == 'true') {
    $info_smarty->assign('SHIPPING_NAME', $main->getShippingStatusName($product->data['products_shippingtime']));
    $info_smarty->assign('SHIPPING_IMAGE', $main->getShippingStatusImage($product->data['products_shippingtime']));
  }

  //products formated price
  $info_smarty->assign('PRODUCTS_PRICE', $products_price['formated']);

  //get products vpe
  $info_smarty->assign('PRODUCTS_VPE',$main->getVPEtext($product->data, $products_price['plain'])); //web28 - 2012-04-17 - use classes function getVPEtext() 
  
  // products id
  $info_smarty->assign('PRODUCTS_ID', $product->data['products_id']);
  
  // products name
  $info_smarty->assign('PRODUCTS_NAME', $product->data['products_name']);

  // price incl tax and shipping link
  if ($_SESSION['customers_status']['customers_status_show_price'] != '0') {
    if (isset($xtPrice->TAX[$product->data['products_tax_class_id']])) {
      $tax_info = $main->getTaxInfo($xtPrice->TAX[$product->data['products_tax_class_id']]);
      $info_smarty->assign('PRODUCTS_TAX_INFO', $tax_info);
    }
    $info_smarty->assign('PRODUCTS_SHIPPING_LINK', strip_tags($main->getShippingLink()));
  }

  $info_smarty->assign('PRODUCTS_MODEL', $product->data['products_model']);
  $info_smarty->assign('PRODUCTS_EAN', $product->data['products_ean']);
  $info_smarty->assign('PRODUCTS_QUANTITY', $product->data['products_quantity']);
  $info_smarty->assign('PRODUCTS_WEIGHT', $product->data['products_weight']);
  $info_smarty->assign('PRODUCTS_STATUS', $product->data['products_status']);
  $info_smarty->assign('PRODUCTS_ORDERED', $product->data['products_ordered']);
  $info_smarty->assign('PRODUCTS_DESCRIPTION', stripslashes($product->data['products_description']));
  $info_smarty->assign('PRODUCTS_SHORT_DESCRIPTION', stripslashes($product->data['products_short_description']));
  $info_smarty->assign('PRODUCTS_IMAGE', $product->productImage($product->data['products_image'], 'thumbnail'));
  $info_smarty->assign('PRODUCTS_URL', !empty($product->data['products_url']) ? sprintf(TEXT_MORE_INFORMATION, xtc_href_link(FILENAME_REDIRECT, 'action=product&id='.$product->data['products_id'], 'NONSSL', true, false)) : '');

  // more images
  $mo_images = xtc_get_products_mo_images($product->data['products_id']);
  if ($mo_images != false) {
    $more_images_data = array();
    foreach ($mo_images as $img) {
      $mo_img = $product->productImage($img['image_name'], 'thumbnail');
      $more_images_data[] = array ('PRODUCTS_IMAGE' => $mo_img, 
                                   'PRODUCTS_POPUP_LINK' => 'javascript:popupWindow(\''.xtc_href_link(FILENAME_POPUP_IMAGE, 
                                   'pID='.$product->data['products_id'].'&imgID='.$img['image_nr']).'\')'
                                   );
      //next 2 lines only needed for non modified templates
      $info_smarty->assign('PRODUCTS_IMAGE_'.$img['image_nr'], $mo_img);
      $info_smarty->assign('PRODUCTS_POPUP_LINK_'.$img['image_nr'], 'javascript:popupWindow(\''.xtc_href_link(FILENAME_POPUP_IMAGE, 'pID='.$product->data['products_id'].'&imgID='.$img['image_nr']).'\')');
    }
    $info_smarty->assign('more_images', $more_images_data);
  }

  // product discount
  if ($_SESSION['customers_status']['customers_status_public'] == 1 && $_SESSION['customers_status']['customers_status_discount'] != '0.00') {
    $discount = 0.00;
    $discount = $_SESSION['customers_status']['customers_status_discount'];
    if ($product->data['products_discount_allowed'] < $_SESSION['customers_status']['customers_status_discount'])
      $discount = $product->data['products_discount_allowed'];
    if ($discount != '0.00')
      $info_smarty->assign('PRODUCTS_DISCOUNT', $discount.'%');
  }

  //canonical_link -> set canonical tag in /template/.../module/print_product_info.html
  $canonical_link = xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product->data['products_id'],$request_type,false);
  $info_smarty->assign('CanonicalLink', $canonical_link);
 
  //include modules
  if ($_SESSION['customers_status']['customers_status_graduated_prices'] == 1) {
    include (DIR_WS_MODULES.FILENAME_GRADUATED_PRICE);
  }
  
  include (DIR_WS_MODULES.FILENAME_PRODUCTS_MEDIA);  

  // date available/added
  if ($product->data['products_date_available'] > date('Y-m-d H:i:s')) {
    $info_smarty->assign('PRODUCTS_DATE_AVIABLE', sprintf(TEXT_DATE_AVAILABLE, xtc_date_long($product->data['products_date_available'])));
    $info_smarty->assign('PRODUCTS_DATE_AVAILABLE', sprintf(TEXT_DATE_AVAILABLE, xtc_date_long($product->data['products_date_available']))); 
  } elseif ($product->data['products_date_added'] != '0000-00-00 00:00:00') {
    $info_smarty->assign('PRODUCTS_ADDED', sprintf(TEXT_DATE_ADDED, xtc_date_long($product->data['products_date_added'])));
  }
 
  $info_smarty->assign('language', $_SESSION['language']);

  // set cache ID
   if (!CacheCheck()) {
    $info_smarty->caching = 0;
  } else {
    $info_smarty->caching = 1;
    $info_smarty->cache_lifetime = CACHE_LIFETIME;
    $info_smarty->cache_modified_check = CACHE_CHECK;
  }
  $cache_id = $_SESSION['language'].'_'.$product->data['products_id'];

  $info_smarty->display(CURRENT_TEMPLATE.'/module/print_product_info.html', $cache_id);
}
?>