<?php
/* -----------------------------------------------------------------------------------------
   $Id: boxes.php 3409 2012-08-10 12:47:17Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// some defines
define('DIR_FS_BOXES', DIR_FS_CATALOG .'templates/'.CURRENT_TEMPLATE. '/source/boxes/');
define('DIR_FS_BOXES_INC', DIR_FS_CATALOG .'templates/'.CURRENT_TEMPLATE. '/source/inc/');

// some confiurations
define('SPECIALS_CATEGORIES', true);
define('WHATSNEW_CATEGORIES', true);

// redirect
require_once(DIR_FS_BOXES_INC . 'gunnart_productRedirect.inc.php');

//BOC require boxes
// -----------------------------------------------------------------------------------------
//	Immer sichtbar
// -----------------------------------------------------------------------------------------
  require_once(DIR_FS_BOXES . 'categories.php');
  require_once(DIR_FS_BOXES . 'manufacturers.php');
  require_once(DIR_FS_BOXES . 'last_viewed.php');
  require_once(DIR_FS_BOXES . 'search.php');
  require_once(DIR_FS_BOXES . 'content.php');
  require_once(DIR_FS_BOXES . 'information.php');
  require_once(DIR_FS_BOXES . 'languages.php'); 
  require_once(DIR_FS_BOXES . 'infobox.php');
  require_once(DIR_FS_BOXES . 'loginbox.php');
  require_once(DIR_FS_BOXES . 'newsletter.php');
// -----------------------------------------------------------------------------------------
//	Nur, wenn Preise sichtbar
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
    require_once(DIR_FS_BOXES . 'add_a_quickie.php');
    require_once(DIR_FS_BOXES . 'shopping_cart.php');
  }
// -----------------------------------------------------------------------------------------
//	In der Suche verborgen
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0,8) != 'advanced' && WHATSNEW_CATEGORIES === false) {
    require_once(DIR_FS_BOXES . 'whats_new.php'); 
  }
// -----------------------------------------------------------------------------------------
//	Nur fuer Admins
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status'] == '0') {
    require_once(DIR_FS_BOXES . 'admin.php');
    $smarty->assign('is_admin', true);
  }
// -----------------------------------------------------------------------------------------
//	Produkt-Detailseiten
// -----------------------------------------------------------------------------------------
  if ($product->isProduct()) {
    //Aktuelle Seite ist Produkt-Detailseite
    require_once(DIR_FS_BOXES . 'manufacturer_info.php');
  } else {
    //Aktuelle Seite ist keine  Produkt-Detailseite
    require_once(DIR_FS_BOXES . 'best_sellers.php');
    if (SPECIALS_CATEGORIES === false) {
      require_once(DIR_FS_BOXES . 'specials.php');
    }
  }
// -----------------------------------------------------------------------------------------
//	Nur fuer eingeloggte Besucher
// -----------------------------------------------------------------------------------------
  if (isset($_SESSION['customer_id'])) {
    require_once(DIR_FS_BOXES . 'order_history.php');
  }
// -----------------------------------------------------------------------------------------
//	Nur, wenn Bewertungen erlaubt
// -----------------------------------------------------------------------------------------
  if ($_SESSION['customers_status']['customers_status_read_reviews'] == '1') {
    require_once(DIR_FS_BOXES . 'reviews.php');
  }
// -----------------------------------------------------------------------------------------
//	Waehrend des Kauf-Abschlusses verborgen 
// -----------------------------------------------------------------------------------------
  if (substr(basename($PHP_SELF), 0, 8) != 'checkout') {
    require_once(DIR_FS_BOXES . 'currencies.php');
  }
// -----------------------------------------------------------------------------------------
//EOC require boxes

// -----------------------------------------------------------------------------------------
// Smarty Zuweisung Startseite
// -----------------------------------------------------------------------------------------
$smarty->assign('home', ((basename($PHP_SELF) == FILENAME_DEFAULT && !isset($_GET['cPath']) && !isset($_GET['manufacturers_id'])) ? 1 : 0));

// -----------------------------------------------------------------------------------------
// Smarty Zuweisung Full content
// -----------------------------------------------------------------------------------------
$smarty->assign('fullcontent', strpos($PHP_SELF, 'checkout') 
                            || strpos($PHP_SELF, 'account') 
                            || strpos($PHP_SELF, 'address') 
                            || strpos($PHP_SELF, 'password') 
                            || strpos($PHP_SELF, FILENAME_ADVANCED_SEARCH_RESULT) 
                            || strpos($PHP_SELF, FILENAME_SHOPPING_CART) 
                            || strpos($PHP_SELF, FILENAME_NEWSLETTER) 
                            || strpos($PHP_SELF, FILENAME_LOGIN) 
                            || strpos($PHP_SELF, FILENAME_CONTENT) 
                            || strpos($PHP_SELF, FILENAME_REVIEWS)); 

// -----------------------------------------------------------------------------------------
// Smarty Zuweisung bestseller
// -----------------------------------------------------------------------------------------
$smarty->assign('bestseller', strpos($PHP_SELF, FILENAME_LOGOFF) 
                           || strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) 
                           || strpos($PHP_SELF, FILENAME_SHOPPING_CART)
                           || strpos($PHP_SELF, FILENAME_NEWSLETTER));
// -----------------------------------------------------------------------------------------

$smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
?>