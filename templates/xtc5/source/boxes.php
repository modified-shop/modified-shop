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

//BOC require boxes
// -----------------------------------------------------------------------------------------
//	Immer sichtbar
// -----------------------------------------------------------------------------------------
  require_once(Template::path('source/boxes/categories.php'));
  require_once(Template::path('source/boxes/manufacturers.php'));
  require_once(Template::path('source/boxes/last_viewed.php'));
  require_once(Template::path('source/boxes/search.php'));
  require_once(Template::path('source/boxes/content.php'));
  require_once(Template::path('source/boxes/information.php'));
  require_once(Template::path('source/boxes/languages.php')); 
  require_once(Template::path('source/boxes/infobox.php'));
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
//	Nur sichtbar, wenn nicht auf der login.php Seite
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0,5) != 'login') {
    require_once(Template::path('source/boxes/loginbox.php'));
  }
// -----------------------------------------------------------------------------------------
//	Nur, wenn Preise sichtbar
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
    require_once(Template::path('source/boxes/add_a_quickie.php'));
    require_once(Template::path('source/boxes/shopping_cart.php'));
    if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
      require_once(Template::path('source/boxes/wishlist.php'));
    }
  }
// -----------------------------------------------------------------------------------------
//	In der Suche verborgen
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0,8) != 'advanced') {
    require_once(Template::path('source/boxes/whats_new.php')); 
  }
// -----------------------------------------------------------------------------------------
//	Nur fuer Admins
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status'] == '0') {
    require_once(Template::path('source/boxes/admin.php'));
    $smarty->assign('is_admin', true);
  }
// -----------------------------------------------------------------------------------------
//	Produkt-Detailseiten
// -----------------------------------------------------------------------------------------
  if ($product->isProduct() === true) {
    //Aktuelle Seite ist Produkt-Detailseite
    require_once(Template::path('source/boxes/manufacturer_info.php'));
  } else {
    //Aktuelle Seite ist keine  Produkt-Detailseite
    require_once(Template::path('source/boxes/best_sellers.php'));
    if ($_SESSION['customers_status']['customers_status_specials'] == '1') {
      require_once(Template::path('source/boxes/specials.php'));
    }
  }
// -----------------------------------------------------------------------------------------
//	Nur fuer eingeloggte Besucher
// -----------------------------------------------------------------------------------------
  if (isset($_SESSION['customer_id'])) {
    require_once(Template::path('source/boxes/order_history.php'));
  }
// -----------------------------------------------------------------------------------------
//	Nur, wenn Rezensionen erlaubt
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_read_reviews'] == '1') {
    require_once(Template::path('source/boxes/reviews.php'));
  }
// -----------------------------------------------------------------------------------------
//	Waehrend des Kauf-Abschlusses verborgen 
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0, 8) != 'checkout') {
    require_once(Template::path('source/boxes/currencies.php'));
    require_once(Template::path('source/boxes/shipping_country.php'));
  }
// -----------------------------------------------------------------------------------------
//EOC require boxes

// -----------------------------------------------------------------------------------------
// Smarty Zuweisung Startseite
// -----------------------------------------------------------------------------------------
$smarty->assign('home', strpos($PHP_SELF, 'index')!==false && !isset($_GET['cPath']) && !isset($_GET['manufacturers_id']) ? 1 : 0);
// -----------------------------------------------------------------------------------------

$smarty->assign('tpl_path',Template::url(''));
