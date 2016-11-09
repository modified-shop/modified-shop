<?php
/* -----------------------------------------------------------------------------------------
   $Id: boxes.php 10016 2016-06-26 14:11:26Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// css buttons
if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/inc/css_button.inc.php')) {
  require_once ('templates/'.CURRENT_TEMPLATE.'/source/inc/css_button.inc.php');
}

// define full content sites
$fullcontent = array(FILENAME_CHECKOUT_SHIPPING,
                     FILENAME_CHECKOUT_PAYMENT,
                     FILENAME_CHECKOUT_CONFIRMATION,
                     FILENAME_CHECKOUT_SUCCESS,
                     FILENAME_CHECKOUT_SHIPPING_ADDRESS,
                     FILENAME_CHECKOUT_PAYMENT_ADDRESS,
                     FILENAME_ACCOUNT,
                     FILENAME_ACCOUNT_EDIT,
                     FILENAME_ACCOUNT_HISTORY,
                     FILENAME_ACCOUNT_HISTORY_INFO,
                     FILENAME_ACCOUNT_PASSWORD,
                     FILENAME_ACCOUNT_DELETE,
                     FILENAME_ACCOUNT_CHECKOUT_EXPRESS,
                     FILENAME_CREATE_ACCOUNT,
                     FILENAME_CREATE_GUEST_ACCOUNT,
                     FILENAME_ADDRESS_BOOK,
                     FILENAME_ADDRESS_BOOK_PROCESS,
                     FILENAME_PASSWORD_DOUBLE_OPT,
                     FILENAME_ADVANCED_SEARCH_RESULT,
                     FILENAME_ADVANCED_SEARCH,
                     FILENAME_SHOPPING_CART,
                     FILENAME_GV_SEND,
                     FILENAME_NEWSLETTER,
                     FILENAME_LOGIN,
                     FILENAME_CONTENT,
                     FILENAME_REVIEWS,
                     FILENAME_WISHLIST,
                     );

// -----------------------------------------------------------------------------------------
//	full content
// -----------------------------------------------------------------------------------------
  if (!in_array(basename($PHP_SELF), $fullcontent)) {
    require_once(DIR_FS_BOXES . 'categories.php');
    require_once(DIR_FS_BOXES . 'manufacturers.php');
    require_once(DIR_FS_BOXES . 'last_viewed.php');
  } else {
    // smarty full content
    $smarty->assign('fullcontent', true);  
  }

// -----------------------------------------------------------------------------------------
//	always visible
// -----------------------------------------------------------------------------------------
  require_once(DIR_FS_BOXES . 'search.php');
  require_once(DIR_FS_BOXES . 'content.php');
  require_once(DIR_FS_BOXES . 'information.php');
  require_once(DIR_FS_BOXES . 'miscellaneous.php');
  require_once(DIR_FS_BOXES . 'languages.php'); 
  require_once(DIR_FS_BOXES . 'infobox.php');
  require_once(DIR_FS_BOXES . 'loginbox.php');
  if (defined('MODULE_NEWSLETTER_STATUS') && MODULE_NEWSLETTER_STATUS == 'true') {
    require_once(DIR_FS_BOXES . 'newsletter.php');
  }
  if (defined('MODULE_TS_TRUSTEDSHOPS_ID') 
      && (MODULE_TS_WIDGET == '1'
          || (MODULE_TS_REVIEW_STICKER != '' && MODULE_TS_REVIEW_STICKER_STATUS == '1'))
      ) 
  {
    require_once(DIR_FS_BOXES . 'trustedshops.php');
  }
// -----------------------------------------------------------------------------------------
//	only if show price
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
    require_once(DIR_FS_BOXES . 'add_a_quickie.php');
    require_once(DIR_FS_BOXES . 'shopping_cart.php');
    if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
      require_once(DIR_FS_BOXES . 'wishlist.php');
    }
  }
// -----------------------------------------------------------------------------------------
//	hide in search
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0,8) != 'advanced' && WHATSNEW_CATEGORIES === false) {
    require_once(DIR_FS_BOXES . 'whats_new.php'); 
  }
// -----------------------------------------------------------------------------------------
//	admins only
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status'] == '0') {
    require_once(DIR_FS_BOXES . 'admin.php');
    $smarty->assign('is_admin', true);
  }
// -----------------------------------------------------------------------------------------
//	product details
// -----------------------------------------------------------------------------------------
  if ($product->isProduct() === true) {
    require_once(DIR_FS_BOXES . 'manufacturer_info.php');
  } else {
    require_once(DIR_FS_BOXES . 'best_sellers.php');
    if ($_SESSION['customers_status']['customers_status_specials'] == '1' && SPECIALS_CATEGORIES === false) {
      require_once(DIR_FS_BOXES . 'specials.php');
    }
  }
// -----------------------------------------------------------------------------------------
//	only logged id users
// -----------------------------------------------------------------------------------------
  if (isset($_SESSION['customer_id'])) {
    require_once(DIR_FS_BOXES . 'order_history.php');
  }
// -----------------------------------------------------------------------------------------
//	only if reviews allowed
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_read_reviews'] == '1') {
    require_once(DIR_FS_BOXES . 'reviews.php');
  }
// -----------------------------------------------------------------------------------------
//	hide during checkout
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0, 8) != 'checkout') {
    require_once(DIR_FS_BOXES . 'currencies.php');
  }
// -----------------------------------------------------------------------------------------

// -----------------------------------------------------------------------------------------
// Smarty home
// -----------------------------------------------------------------------------------------
$smarty->assign('home', ((basename($PHP_SELF) == FILENAME_DEFAULT && !isset($_GET['cPath']) && !isset($_GET['manufacturers_id'])) ? 1 : 0));

// -----------------------------------------------------------------------------------------
// Smarty bestseller
// -----------------------------------------------------------------------------------------
$smarty->assign('bestseller', strpos($PHP_SELF, FILENAME_LOGOFF) 
                           || strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) 
                           || strpos($PHP_SELF, FILENAME_SHOPPING_CART)
                           || strpos($PHP_SELF, FILENAME_NEWSLETTER));
// -----------------------------------------------------------------------------------------

$smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
?>