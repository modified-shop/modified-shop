<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(filenames.php,v 1.3 2003/05/25); www.oscommerce.com 
   (c) 2003  nextcommerce (filenames.php,v 1.21 2003/08/25); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require_once(DIR_FS_INC.'auto_include.inc.php');
foreach(auto_include(DIR_FS_CATALOG.'includes/extra/filenames/','php') as $file) require ($file);

//compatibility for modified eCommerce Shopsoftware 1.06 files
if (!defined('DIR_ADMIN')) {
  define('DIR_ADMIN', 'admin/');
}

$filename_array = array(
  'FILENAME_ACCOUNT' => 'account.php',
  'FILENAME_ACCOUNT_EDIT' => 'account_edit.php',
  'FILENAME_ACCOUNT_HISTORY' => 'account_history.php',
  'FILENAME_ACCOUNT_HISTORY_INFO' => 'account_history_info.php',
  'FILENAME_ACCOUNT_PASSWORD' => 'account_password.php',
  'FILENAME_ACCOUNT_DELETE' => 'account_delete.php',
  'FILENAME_ADDRESS_BOOK' => 'address_book.php',
  'FILENAME_ADDRESS_BOOK_PROCESS' => 'address_book_process.php',
  'FILENAME_ADVANCED_SEARCH' => 'advanced_search.php',
  'FILENAME_ADVANCED_SEARCH_RESULT' => 'advanced_search_result.php',
  'FILENAME_ALSO_PURCHASED_PRODUCTS' => 'also_purchased_products.php',
  'FILENAME_CHECKOUT_CONFIRMATION' => 'checkout_confirmation.php',
  'FILENAME_CHECKOUT_PAYMENT' => 'checkout_payment.php',
  'FILENAME_CHECKOUT_PAYMENT_ADDRESS' => 'checkout_payment_address.php',
  'FILENAME_CHECKOUT_PROCESS' => 'checkout_process.php',
  'FILENAME_CHECKOUT_SHIPPING' => 'checkout_shipping.php',
  'FILENAME_CHECKOUT_SHIPPING_ADDRESS' => 'checkout_shipping_address.php',
  'FILENAME_CHECKOUT_SUCCESS' => 'checkout_success.php',
  'FILENAME_COOKIE_USAGE' => 'cookie_usage.php',
  'FILENAME_CUSTOMERS' => DIR_ADMIN.'customers.php',
  'FILENAME_CREATE_ACCOUNT' => 'create_account.php',
  'FILENAME_DEFAULT' => 'index.php',
  'FILENAME_DOWNLOAD' => 'download.php',
  'FILENAME_MODULES' => DIR_ADMIN.'modules.php',
  'FILENAME_NEW_PRODUCTS' => 'new_products.php',
  'FILENAME_LOGIN' => 'login.php',
  'FILENAME_LOGOFF' => 'logoff.php',
  'FILENAME_NEWSLETTER' => 'newsletter.php',
  'FILENAME_POPUP_SEARCH_HELP' => 'popup_search_help.php',
  'FILENAME_PRODUCT_INFO' => 'product_info.php',
  'FILENAME_PRODUCT_LISTING' => 'product_listing.php',
  'FILENAME_PRODUCT_REVIEWS' => 'product_reviews.php',
  'FILENAME_PRODUCT_REVIEWS_INFO' => 'product_reviews_info.php',
  'FILENAME_PRODUCT_REVIEWS_WRITE' => 'product_reviews_write.php',
  'FILENAME_PRODUCTS_NEW' => 'products_new.php',
  'FILENAME_REDIRECT' => 'redirect.php',
  'FILENAME_REVIEWS' => 'reviews.php',
  'FILENAME_SHIPPING' => 'shipping.php',
  'FILENAME_SHOPPING_CART' => 'shopping_cart.php',
  'FILENAME_START' => DIR_ADMIN.'start.php',
  'FILENAME_SPECIALS' => 'specials.php',
  'FILENAME_SSL_CHECK' => 'ssl_check.php',
  'FILENAME_ORDERS' => DIR_ADMIN.'orders.php',
  'FILENAME_METATAGS' => 'metatags.php',
  'FILENAME_MINIMUM_ORDER' => 'reviews.php',
  'FILENAME_PRODUCTS_MEDIA' => 'products_media.php',
  'FILENAME_PASSWORD_DOUBLE_OPT' => 'password_double_opt.php',
  'FILENAME_CREATE_GUEST_ACCOUNT' => 'create_guest_account.php',
  'FILENAME_DISPLAY_VVCODES' => 'display_vvcodes.php',
  'FILENAME_CART_ACTIONS' => 'cart_actions.php',
  'FILENAME_CROSS_SELLING' => 'cross_selling.php',
  'FILENAME_GV_FAQ' => 'gv_faq.php',
  'FILENAME_GV_REDEEM' => 'gv_redeem.php',
  'FILENAME_GV_REDEEM_PROCESS' => 'gv_redeem_process.php',
  'FILENAME_GV_SEND' => 'gv_send.php',
  'FILENAME_GV_SEND_PROCESS' => 'gv_send_process.php',
  'FILENAME_PRODUCT_LISTING_COL' => 'product_listing_col.php',
  'FILENAME_POPUP_COUPON_HELP' => 'popup_coupon_help.php',
  'FILENAME_POPUP_CONTENT' => 'popup_content.php',
  'FILENAME_EDIT_PRODUCTS' => DIR_ADMIN.'categories.php',
  'FILENAME_GRADUATED_PRICE' => 'graduated_prices.php',
  'FILENAME_PRINT_PRODUCT_INFO' => 'print_product_info.php',
  'FILENAME_PRINT_ORDER' => 'print_order.php',
  'FILENAME_ERROR_HANDLER' => 'error_handler.php',
  'FILENAME_CONTENT' => 'shop_content.php',
  'FILENAME_BANNER' => 'banners.php',
  'FILENAME_FINDOLOGIC' => 'findologic.php',
  'FILENAME_WISHLIST' => 'wishlist.php',
  'FILENAME_ACCOUNT_CHECKOUT_EXPRESS' => 'account_checkout_express.php',
  'FILENAME_CHECKOUT_PAYMENT_IFRAME' => 'checkout_payment_iframe.php',
  'FILENAME_MEDIA_CONTENT' => 'media_content.php',
);

// define 
foreach ($filename_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>