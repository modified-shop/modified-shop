<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // logs in the customer in $check_customer and redirects
  // include it in the global scope, the hooks in includes/extra/login/ may render a page

  // include needed functions
  require_once (DIR_FS_INC.'xtc_write_user_info.inc.php');
  require_once (DIR_FS_INC.'write_customers_session.inc.php');

  if (SESSION_RECREATE == 'True') {
    xtc_session_recreate();
  }

  // reset Login tries
  unset($_SESSION['customers_login_tries']);
  xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_LOGIN."
                      WHERE (customers_email_address = '".xtc_db_input($check_customer['customers_email_address'])."'
                             OR customers_ip = '".xtc_db_input($_SESSION['tracking']['ip'])."')");

  // default session
  $_SESSION['customer_id'] = $check_customer['customers_id'];
  $_SESSION['customer_time'] = $check_customer['customers_password_time'];

  if ($_SESSION['customer_time'] == 0) {
    $_SESSION['customer_time'] = time();
    xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                     SET customers_password_time = '".(int)$_SESSION['customer_time']."'
                   WHERE customers_id = '".(int)$_SESSION['customer_id']."' ");
  }

  xtc_db_query("UPDATE ".TABLE_CUSTOMERS_INFO."
                   SET customers_info_date_of_last_logon = now(),
                       customers_info_number_of_logons = customers_info_number_of_logons+1
                 WHERE customers_info_id = '".(int)$_SESSION['customer_id']."'");

  // write customers status session
  require(DIR_WS_INCLUDES.'write_customers_status.php');

  // write customers session
  write_customers_session((int)$_SESSION['customer_id']);

  // user info
  xtc_write_user_info((int)$_SESSION['customer_id']);

  // who's online
  xtc_update_whos_online();

  // restore cart contents
  $_SESSION['cart']->restore_contents();

  // restore wishlist contents
  if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
    $_SESSION['wishlist']->restore_contents();
  }

  if (isset($econda) && is_object($econda)) {
    $econda->_loginUser();
  }

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/login/','php') as $file) require_once ($file);

  // redirect to last viewed page
  $cnt_pageview_history = count($_SESSION['tracking']['pageview_history']);
  if ($cnt_pageview_history > 1) {
    $redirect = $_SESSION['tracking']['pageview_history'][$cnt_pageview_history - 2];
    if (substr($redirect, 0, strlen(DIR_WS_CATALOG)) == DIR_WS_CATALOG) {
      $redirect = substr($redirect, strlen(DIR_WS_CATALOG));
    }
    if ($_SESSION['old_customers_basket_cart'] === true) {
      unset($_SESSION['old_customers_basket_cart']);
      $messageStack->add_session('global', TEXT_SAVED_BASKET);
    }
    xtc_redirect(xtc_href_link(ltrim($redirect, '/')));
  }

  // redirect fallback
  if ($_SESSION['cart']->count_contents() > 0) {
    if ($_SESSION['old_customers_basket_cart'] === true) {
      unset($_SESSION['old_customers_basket_cart']);
      $messageStack->add_session('info_message_3', TEXT_SAVED_BASKET);
    }
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART),'NONSSL');
  } else {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT),'NONSSL');
  }
