<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// define full content sites
$fullcontent = array(FILENAME_CHECKOUT_SHIPPING,
                     FILENAME_CHECKOUT_PAYMENT,
                     FILENAME_CHECKOUT_CONFIRMATION,
                     FILENAME_CHECKOUT_SUCCESS,
                     FILENAME_CHECKOUT_SHIPPING_ADDRESS,
                     FILENAME_CHECKOUT_PAYMENT_ADDRESS,
                     FILENAME_COOKIE_USAGE,
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
                     FILENAME_CHECKOUT_PAYMENT_IFRAME,
                     );

// -----------------------------------------------------------------------------------------
//	full content
// -----------------------------------------------------------------------------------------
  if (!in_array(basename($PHP_SELF), $fullcontent) || (isset($display_mode) && $display_mode == 'error')) {
    require_once(Template::path('source/boxes/categories.php'));
    require_once(Template::path('source/boxes/manufacturers.php'));
    require_once(Template::path('source/boxes/last_viewed.php'));
  } else {
    // smarty full content
    $smarty->assign('fullcontent', true);  
  }

// -----------------------------------------------------------------------------------------
//	always visible
// -----------------------------------------------------------------------------------------
  require_once(Template::path('source/boxes/search.php'));
  require_once(Template::path('source/boxes/content.php'));
  require_once(Template::path('source/boxes/information.php'));
  require_once(Template::path('source/boxes/miscellaneous.php'));
  require_once(Template::path('source/boxes/languages.php')); 
  require_once(Template::path('source/boxes/infobox.php'));
  require_once(Template::path('source/boxes/loginbox.php'));
  if (!defined('MODULE_NEWSLETTER_STATUS') || MODULE_NEWSLETTER_STATUS == 'true') {
    require_once(Template::path('source/boxes/newsletter.php'));
  }
  if (defined('MODULE_TS_TRUSTEDSHOPS_ID') 
      && MODULE_TS_REVIEW_STICKER != '' 
      && MODULE_TS_REVIEW_STICKER_STATUS == '1'
      ) 
  {
    require_once(Template::path('source/boxes/trustedshops.php'));
  }
// -----------------------------------------------------------------------------------------
//	only if show price
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
    require_once(Template::path('source/boxes/add_a_quickie.php'));
    require_once(Template::path('source/boxes/shopping_cart.php'));
    if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
      require_once(Template::path('source/boxes/wishlist.php'));
    }
  }
// -----------------------------------------------------------------------------------------
//	hide in search
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0,8) != 'advanced' && WHATSNEW_CATEGORIES === false) {
    require_once(Template::path('source/boxes/whats_new.php')); 
  }
// -----------------------------------------------------------------------------------------
//	admins only
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status'] == '0') {
    require_once(Template::path('source/boxes/admin.php'));
    $smarty->assign('is_admin', true);
  }
// -----------------------------------------------------------------------------------------
//	product details
// -----------------------------------------------------------------------------------------
  if ($product->isProduct() === true) {
    require_once(Template::path('source/boxes/manufacturer_info.php'));
  } else {
    if ($_SESSION['customers_status']['customers_status_specials'] == '1' && SPECIALS_CATEGORIES === false) {
      require_once(Template::path('source/boxes/specials.php'));
    }
  }
// -----------------------------------------------------------------------------------------
//	only logged id users
// -----------------------------------------------------------------------------------------
  if (isset($_SESSION['customer_id'])) {
    require_once(Template::path('source/boxes/order_history.php'));
  }
// -----------------------------------------------------------------------------------------
//	only if reviews allowed
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_read_reviews'] == '1') {
    require_once(Template::path('source/boxes/reviews.php'));
  }
// -----------------------------------------------------------------------------------------
//	hide during checkout
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0, 8) != 'checkout') {
    require_once(Template::path('source/boxes/currencies.php'));
    require_once(Template::path('source/boxes/shipping_country.php'));
  }
// -----------------------------------------------------------------------------------------

// -----------------------------------------------------------------------------------------
// Smarty home
// -----------------------------------------------------------------------------------------
$smarty->assign('home', ((basename($PHP_SELF) == FILENAME_DEFAULT && !isset($_GET['cPath']) && !isset($_GET['manufacturers_id'])) ? 1 : 0));

// -----------------------------------------------------------------------------------------
// Smarty bestseller
// -----------------------------------------------------------------------------------------
$smarty->assign('bestseller', false);
$bestsellers = array(FILENAME_DEFAULT,
                     FILENAME_LOGOFF, 
                     FILENAME_CHECKOUT_SUCCESS, 
                     FILENAME_SHOPPING_CART, 
                     FILENAME_NEWSLETTER
                     );
if ((isset($display_mode) && $display_mode == 'error') || (in_array(basename($PHP_SELF), $bestsellers) && !isset($_GET['cPath']) && !isset($_GET['manufacturers_id']))) {
  require_once(Template::path('source/boxes/best_sellers.php'));
  $smarty->assign('bestseller', true);
}
// -----------------------------------------------------------------------------------------

// Legacy compatibility for custom templates.
// New Smarty templates must use {template_asset ...} for concrete template assets. Not tpl_path or logo_path.
$smarty->assign('tpl_path', Template::url(''));

$content_data_query = xtDBquery("SELECT *
                                   FROM ".TABLE_CONTENT_MANAGER."
                                  WHERE content_group IN (4,7)
                                    AND content_active = '1'
                                    AND trim(content_title) != ''
                                    AND languages_id = '".(int)$_SESSION['languages_id']."'
                                        ".CONTENT_CONDITIONS);
if (xtc_db_num_rows($content_data_query, true) > 0) {
  while ($content_data = xtc_db_fetch_array($content_data_query, true)) {
    if ($content_data['content_group'] == '7') $smarty->assign('contact', xtc_href_link(FILENAME_CONTENT, 'coID=7', 'SSL'));
    if ($content_data['content_group'] == '4') $smarty->assign('imprint', xtc_href_link(FILENAME_CONTENT, 'coID=4', 'SSL'));
  }
}
