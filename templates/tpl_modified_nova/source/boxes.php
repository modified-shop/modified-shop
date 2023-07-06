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
   -----------------------------------------------------------------------------------------
   valid display modes: 

   account, category, checkout, contactus, content, cookieusage, createaccount, download 
   error, gv, home, listing, login, logoff, manufacturer, newsletter, password, productinfo 
   productsnew, reviews, shoppingcart, search, sitemap, specials, sslcheck, wishlist
   ---------------------------------------------------------------------------------------*/


  // -----------------------------------------------------------------------------------------
  //	default smarty
  // -----------------------------------------------------------------------------------------
  $smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  $smarty->assign('display_mode', $display_mode);
  $smarty->assign('content_size', 'small');

  // -----------------------------------------------------------------------------------------
  //	always visible
  // -----------------------------------------------------------------------------------------
  require_once(DIR_FS_BOXES . 'categories.php');
  require_once(DIR_FS_BOXES . 'manufacturers.php');
  require_once(DIR_FS_BOXES . 'search.php');
  require_once(DIR_FS_BOXES . 'content.php');
  require_once(DIR_FS_BOXES . 'information.php');
  require_once(DIR_FS_BOXES . 'miscellaneous.php');
  require_once(DIR_FS_BOXES . 'infobox.php');
  require_once(DIR_FS_BOXES . 'login.php');
  if (!defined('MODULE_NEWSLETTER_STATUS') 
      || MODULE_NEWSLETTER_STATUS == 'true'
      )
  {
    require_once(DIR_FS_BOXES . 'newsletter.php');
  }

  // -----------------------------------------------------------------------------------------
  //	only if show price
  // -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
    require_once(DIR_FS_BOXES . 'cart.php');
    if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
      require_once(DIR_FS_BOXES . 'wishlist.php');
    }
  }

  // -----------------------------------------------------------------------------------------
  // additional boxes
  // -----------------------------------------------------------------------------------------
  if (in_array($display_mode, array('home', 'logoff', 'error', 'shopping_cart', 'newsletter')) 
      || basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS
      )
  {
    require_once(DIR_FS_BOXES . 'last_viewed.php');
    require_once(DIR_FS_BOXES . 'whats_new.php');
    require_once(DIR_FS_BOXES . 'best_sellers.php');
    require_once(DIR_FS_BOXES . 'specials.php');
    require_once(DIR_FS_BOXES . 'xsell.php');
  }

  // -----------------------------------------------------------------------------------------
  //	hide during checkout
  // -----------------------------------------------------------------------------------------
  if ($display_mode != 'checkout') {
    require_once(DIR_FS_BOXES . 'currencies.php');
    require_once(DIR_FS_BOXES . 'shipping_country.php');
    require_once(DIR_FS_BOXES . 'languages.php'); 
  }

  // -----------------------------------------------------------------------------------------
  //	admins only
  // -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status'] == '0') {
    require_once(DIR_FS_BOXES . 'admin.php');
  }

  // -----------------------------------------------------------------------------------------
  //	display mode
  // -----------------------------------------------------------------------------------------
  switch ($display_mode) {
    case 'home':
      // greeting
      require_once(DIR_FS_BOXES . 'greeting.php');
      // specials
      if ($_SESSION['customers_status']['customers_status_specials'] == '1' 
          && SPECIALS_CATEGORIES === false
          )
      {
        require_once(DIR_FS_BOXES . 'specials.php');
      }
      // whats_new
      if (substr(basename($PHP_SELF), 0,8) != 'advanced' 
          && WHATSNEW_CATEGORIES === false
          )
      {
        require_once(DIR_FS_BOXES . 'whats_new.php'); 
      }
      // reviews
      if ($_SESSION['customers_status']['customers_status_read_reviews'] == '1') {
        require_once(DIR_FS_BOXES . 'reviews.php');
      }
      // trustedshops
      if (defined('MODULE_TS_TRUSTEDSHOPS_ID') 
          && MODULE_TS_REVIEW_STICKER != '' 
          && MODULE_TS_REVIEW_STICKER_STATUS == '1'
          ) 
      {
        require_once(DIR_FS_BOXES . 'trustedshops.php');
      }
      break;
    
    case 'listing':
      // sub categories
      require_once(DIR_FS_BOXES . 'sub_categories.php');
      break;

    case 'category':
    case 'manufacturer':
    case 'search':
    case 'productsnew':
    case 'specials':
      // set big content for non cases in index.html
      $smarty->assign('content_size', 'big');
      Break;
  }
