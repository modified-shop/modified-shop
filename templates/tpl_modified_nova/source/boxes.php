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
  $smarty->assign('tpl_path', Template::url(''));
  $smarty->assign('theme_color', ((defined('THEME_COLOR')) ? 'theme_'.THEME_COLOR : 'theme_default'));
  $smarty->assign('display_mode', $display_mode);
  $smarty->assign('content_size', 'small');

  // -----------------------------------------------------------------------------------------
  //	always visible
  // -----------------------------------------------------------------------------------------
  require_once(Template::path('source/boxes/categories.php'));
  require_once(Template::path('source/boxes/search.php'));
  require_once(Template::path('source/boxes/content.php'));
  require_once(Template::path('source/boxes/information.php'));
  require_once(Template::path('source/boxes/miscellaneous.php'));
  require_once(Template::path('source/boxes/infobox.php'));
  require_once(Template::path('source/boxes/login.php'));
  
  if (defined('HEADER_SHOW_MANUFACTURERS')
      && HEADER_SHOW_MANUFACTURERS == true
      )
  {
    require_once(Template::path('source/boxes/manufacturers.php'));
  }
  
  if (!defined('MODULE_NEWSLETTER_STATUS') 
      || MODULE_NEWSLETTER_STATUS == 'true'
      )
  {
    require_once(Template::path('source/boxes/newsletter.php'));
  }

  // -----------------------------------------------------------------------------------------
  //	only if show price
  // -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
    require_once(Template::path('source/boxes/cart.php'));
    if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
      require_once(Template::path('source/boxes/wishlist.php'));
    }
  }

  // -----------------------------------------------------------------------------------------
  // additional boxes
  // -----------------------------------------------------------------------------------------
  if (in_array($display_mode, array('home', 'logoff', 'error', 'shoppingcart', 'newsletter')) 
      || basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS
      )
  {
    require_once(Template::path('source/boxes/last_viewed.php'));
    require_once(Template::path('source/boxes/whats_new.php'));
    require_once(Template::path('source/boxes/best_sellers.php'));
    require_once(Template::path('source/boxes/specials.php'));
    require_once(Template::path('source/boxes/xsell.php'));
  }

  // -----------------------------------------------------------------------------------------
  //	hide during checkout
  // -----------------------------------------------------------------------------------------
  if ($display_mode != 'checkout') {
    require_once(Template::path('source/boxes/currencies.php'));
    require_once(Template::path('source/boxes/shipping_country.php'));
    require_once(Template::path('source/boxes/languages.php')); 
  }

  // -----------------------------------------------------------------------------------------
  //	admins only
  // -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status'] == '0') {
    require_once(Template::path('source/boxes/admin.php'));
  }

  // -----------------------------------------------------------------------------------------
  //	display mode
  // -----------------------------------------------------------------------------------------
  switch ($display_mode) {
    case 'home':
      // greeting
      require_once(Template::path('source/boxes/greeting.php'));
      // specials
      if ($_SESSION['customers_status']['customers_status_specials'] == '1' 
          && SPECIALS_CATEGORIES === false
          )
      {
        require_once(Template::path('source/boxes/specials.php'));
      }
      // whats_new
      if (substr(basename($PHP_SELF), 0,8) != 'advanced' 
          && WHATSNEW_CATEGORIES === false
          )
      {
        require_once(Template::path('source/boxes/whats_new.php')); 
      }
      // reviews
      if ($_SESSION['customers_status']['customers_status_read_reviews'] == '1') {
        require_once(Template::path('source/boxes/reviews.php'));
      }
      // trustedshops
      if (defined('MODULE_TS_TRUSTEDSHOPS_ID') 
          && MODULE_TS_REVIEW_STICKER != '' 
          && MODULE_TS_REVIEW_STICKER_STATUS == '1'
          ) 
      {
        require_once(Template::path('source/boxes/trustedshops.php'));
      }
      break;
    
    case 'listing':
      // sub categories
      require_once(Template::path('source/boxes/sub_categories.php'));
      break;

    case 'productinfo':
      // sub categories
      require_once(Template::path('source/boxes/last_viewed.php'));
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

  // tax status
  if (MODULE_SMALL_BUSINESS != 'true') {
    $tax_status = 1;
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0) {
      $tax_status = 0;
    } elseif (isset($_SESSION['country'])) {
      if (!isset($_SESSION['tax_status']) || $_SESSION['tax_status']['country'] != $_SESSION['country']) {
        $_SESSION['tax_status'] = array(
          'country' =>  $_SESSION['country'],
          'status' => 1,
        );
        $country_query = xtDBquery("SELECT * 
                                      FROM ".TABLE_COUNTRIES."
                                     WHERE countries_id = '".(int)$_SESSION['country']."'");
        $country = xtc_db_fetch_array($country_query, true);
        if ($main->getDeliveryDutyInfo($country['countries_iso_code_2'])) {
          $_SESSION['tax_status']['status'] = 0;
        }
      }
      $tax_status = $_SESSION['tax_status']['status'];
    }
    $smarty->assign('tax_status', $tax_status);
  }
