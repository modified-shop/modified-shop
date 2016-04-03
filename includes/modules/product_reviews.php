<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_reviews.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_reviews.php,v 1.47 2003/02/13); www.oscommerce.com
   (c) 2003 nextcommerce (product_reviews.php,v 1.12 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // create smarty elements
  $module_smarty = new Smarty;
  $module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->caching = 0;

  // include needed functions
  require_once (DIR_FS_INC.'xtc_row_number_format.inc.php');
  require_once (DIR_FS_INC.'xtc_date_short.inc.php');
    
  if (defined('MODULE_TS_TRUSTEDSHOPS_ID') && MODULE_TS_PRODUCT_STICKER_STATUS == '1') {
    $module_smarty->assign('MODULE_TS_PRODUCT_STICKER', sprintf(MODULE_TS_PRODUCT_STICKER, MODULE_TS_TRUSTEDSHOPS_ID, $product->data['products_model']));
  } else {
  
    $button_preview = '';
    if ($_SESSION['customers_status']['customers_status_write_reviews'] == 1) {
      $button_preview = '<a href="'.xtc_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, 'products_id='.$product->data['products_id']).'">'.xtc_image_button('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW).'</a>';
    }
  
    $module_smarty->assign('BUTTON_WRITE', $button_preview);
  
    $reviews_count = $product->getReviewsCount();
  }
  
  $module = '';
  if (($_SESSION['customers_status']['customers_status_read_reviews'] == '1' && $reviews_count > 0) || $_SESSION['customers_status']['customers_status_write_reviews'] == 1) {    
    $module_smarty->assign('reviews_count', $reviews_count);
    $module_smarty->assign('module_content', $product->getReviews());
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/products_reviews.html');
  }
  
  $info_smarty->assign('MODULE_products_reviews', $module);
?>