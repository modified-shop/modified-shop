<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_info.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_info.php,v 1.94 2003/05/04); www.oscommerce.com
   (c) 2003 nextcommerce (product_info.php,v 1.46 2003/08/25); www.nextcommerce.org
   (c) 2006 xt:Commerce (product_info.php 1317 2005-10-21); www.xt-commerce.de

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
   New Attribute Manager v4b - Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   Cross-Sell (X-Sell) Admin 1 - Autor: Joshua Dechant (dreamscape)
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/******* SHOPGATE **********/
if(strpos(MODULE_PAYMENT_INSTALLED, 'shopgate.php') !== false && strpos($_SESSION['customers_status']['customers_status_payment_unallowed'], 'shopgate') === false){
  include_once DIR_FS_CATALOG.'includes/external/shopgate/base/includes/modules/product_info.php';
}
/******* SHOPGATE **********/

//include needed functions
require_once (DIR_FS_INC.'xtc_check_categories_status.inc.php');
require_once (DIR_FS_INC.'xtc_get_products_mo_images.inc.php');
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');
require_once (DIR_FS_INC.'get_cross_sell_name.inc.php');
require_once (DIR_FS_INC.'xtc_date_short.inc.php');  // for specials

if (!is_object($product) || !$product->isProduct()) {

  // product not found in database
  $error = TEXT_PRODUCT_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);

} else {

  $info_smarty = new Smarty;
  $info_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
  include_once(DIR_WS_INCLUDES.'modules/payment/klarna/display_klarna_price.php'); // Klarna payment module integration

  // defaults
  $hide_qty = 0;

  // xs:booster start (v1.041)
  if (isset($_SESSION['xtb0']['tx']) && is_array($_SESSION['xtb0']['tx'])) {
    $xsb_tx = array();
    foreach($_SESSION['xtb0']['tx'] as $tx) {
      if($tx['products_id'] == $product->data['products_id']) {
        $xsb_tx = $tx;
        break;
      }
    }
	if (isset($xsb_tx['products_id'])) {           // replace || with && ?
      $hide_qty = (@$xsb_tx['XTB_ALLOW_USER_CHQTY'] != 'true' || $xsb_tx['products_id'] == $product->data['products_id']) ? 1 : 0;
      if(isset($xsb_tx['XTB_REDIRECT_USER_TO']) && $xsb_tx['products_id'] == $product->data['products_id']) {
        $info_smarty->assign('XTB_REDIRECT_USER_TO', $xsb_tx['XTB_REDIRECT_USER_TO']);
      }
	}
  }

  if (ACTIVATE_NAVIGATOR == 'true') {
    include (DIR_WS_MODULES.'product_navigator.php');
  }

  // Update products_viewed
  if ($_SESSION['customers_status']['customers_status_id'] != 0) {
    xtc_db_query("-- product_info.php
        UPDATE ".TABLE_PRODUCTS_DESCRIPTION."
           SET products_viewed = products_viewed+1
         WHERE products_id = '".$product->data['products_id']."'
           AND language_id = ".$_SESSION['languages_id']);
  }

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

  // check if customer is allowed to add to cart
  if ($_SESSION['customers_status']['customers_status_show_price'] != '0'
      && (($_SESSION['customers_status']['customers_fsk18'] == '1' && $product->data['products_fsk18'] == '0')
      || $_SESSION['customers_status']['customers_fsk18'] != '1')) {
    $add_pid_to_qty = xtc_draw_hidden_field('products_id', $product->data['products_id']);
    $info_smarty->assign('ADD_QTY', xtc_draw_input_field('products_qty', '1', ($hide_qty ? '' : 'size="3"'), ($hide_qty ? 'hidden' : 'text')).' '.$add_pid_to_qty);
    $info_smarty->assign('ADD_CART_BUTTON', xtc_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART));
  }

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
    // BOF - Tutorial: Umsetzung der EU-Verbraucherrichtlinie vom 13.06.2014
    $info_smarty->assign('SHIPPING_NAME_LINK', $main->getShippingStatusName($product->data['products_shippingtime'], true));
    // EOF - Tutorial: Umsetzung der EU-Verbraucherrichtlinie vom 13.06.2014
  }

  // form tags
  $info_smarty->assign('FORM_ACTION', xtc_draw_form('cart_quantity', xtc_href_link(FILENAME_PRODUCT_INFO, xtc_get_all_get_params(array ('action')).'action=add_product')));
  $info_smarty->assign('FORM_END', '</form>');

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
    $info_smarty->assign('PRODUCTS_SHIPPING_LINK',$main->getShippingLink());
  }

  $info_smarty->assign('PRODUCTS_MODEL', $product->data['products_model']);
  $info_smarty->assign('PRODUCTS_EAN', $product->data['products_ean']);
  $info_smarty->assign('PRODUCTS_MANUFACTURERS_MODEL', $product->data['products_manufacturers_model']);
  $info_smarty->assign('PRODUCTS_QUANTITY', $product->data['products_quantity']);
  $info_smarty->assign('PRODUCTS_WEIGHT', $product->data['products_weight']);
  $info_smarty->assign('PRODUCTS_STATUS', $product->data['products_status']);
  $info_smarty->assign('PRODUCTS_ORDERED', $product->data['products_ordered']);
  $info_smarty->assign('PRODUCTS_PRINT', xtc_image_button('print.gif', $product->data['products_name'], 'onclick="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_PRODUCT_INFO, 'products_id='.$product->data['products_id']).'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, '.POPUP_PRODUCT_PRINT_SIZE.'\')"'));
  $info_smarty->assign('PRODUCTS_DESCRIPTION', stripslashes($product->data['products_description']));
  $info_smarty->assign('PRODUCTS_SHORT_DESCRIPTION', stripslashes($product->data['products_short_description']));
  $info_smarty->assign('PRODUCTS_IMAGE', $product->productImage($product->data['products_image'], 'info'));
  $info_smarty->assign('PRODUCTS_POPUP_LINK', 'javascript:popupWindow(\''.xtc_href_link(FILENAME_POPUP_IMAGE, 'pID='.$product->data['products_id'].'&imgID=0').'\')');
  $info_smarty->assign('PRODUCTS_URL', !empty($product->data['products_url']) ? sprintf(TEXT_MORE_INFORMATION, xtc_href_link(FILENAME_REDIRECT, 'action=product&id='.$product->data['products_id'], 'NONSSL', true, false)) : '');

  // more images
  $mo_images = xtc_get_products_mo_images($product->data['products_id']);
  if ($mo_images != false) {
    $more_images_data = array();
    foreach ($mo_images as $img) {
      $mo_img = $product->productImage($img['image_name'], 'info');
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

  //include modules
  if ($_SESSION['customers_status']['customers_status_graduated_prices'] == 1) {
    include (DIR_WS_MODULES.FILENAME_GRADUATED_PRICE);
  }
  include (DIR_WS_MODULES.'product_attributes.php');
  include (DIR_WS_MODULES.'product_reviews.php');
  include (DIR_WS_MODULES.FILENAME_PRODUCTS_MEDIA);
  include (DIR_WS_MODULES.FILENAME_ALSO_PURCHASED_PRODUCTS);
  include (DIR_WS_MODULES.FILENAME_CROSS_SELLING);

  // date available/added
  if ($product->data['products_date_available'] > date('Y-m-d H:i:s')) {
    $info_smarty->assign('PRODUCTS_DATE_AVIABLE', sprintf(TEXT_DATE_AVAILABLE, xtc_date_long($product->data['products_date_available'])));
    $info_smarty->assign('PRODUCTS_DATE_AVAILABLE', sprintf(TEXT_DATE_AVAILABLE, xtc_date_long($product->data['products_date_available']))); 
  } elseif ($product->data['products_date_added'] != '0000-00-00 00:00:00') {
    $info_smarty->assign('PRODUCTS_ADDED', sprintf(TEXT_DATE_ADDED, xtc_date_long($product->data['products_date_added'])));
  }

  // get default product_info template
  if ($product->data['product_template'] == '' || $product->data['product_template'] == 'default') {
    $files = array ();
    if ($dir = opendir(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_info/')) {
      while ($file = readdir($dir)) {
        if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_info/'.$file) && (substr($file, -5) == ".html") && ($file != "index.html") && (substr($file, 0, 1) !=".")) { 
          $files[] = $file;
        }
      }
      closedir($dir);
    }
    sort($files); 
    $product->data['product_template'] = $files[0];
  }

  // session products history
  $i = isset($_SESSION['tracking']['products_history']) ? count($_SESSION['tracking']['products_history']) : 0;
  if ($i > 6) { $i = 6; array_shift($_SESSION['tracking']['products_history']); }
  $_SESSION['tracking']['products_history'][$i] = $product->data['products_id'];
  $_SESSION['tracking']['products_history'] = array_unique($_SESSION['tracking']['products_history']);

  $info_smarty->assign('language', $_SESSION['language']);

  // set cache ID
  if (!CacheCheck()) {
    $info_smarty->caching = 0;
    $product_info = $info_smarty->fetch(CURRENT_TEMPLATE.'/module/product_info/'.$product->data['product_template']);
  } else {
    $info_smarty->caching = 1;
    $info_smarty->cache_lifetime = CACHE_LIFETIME;
    $info_smarty->cache_modified_check = CACHE_CHECK;
    $cache_id = $product->data['products_id'].$_SESSION['language'].$_SESSION['customers_status']['customers_status_name'].$_SESSION['currency'];
    $product_info = $info_smarty->fetch(CURRENT_TEMPLATE.'/module/product_info/'.$product->data['product_template'], $cache_id);
  }
  $smarty->assign('main_content', $product_info);
}
?>