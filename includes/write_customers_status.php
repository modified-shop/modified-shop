<?php
/* -----------------------------------------------------------------------------------------
   $Id: shopping_cart.php 3725 2012-09-30 12:53:03Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (write_customers_status.php,v 1.8 2003/08/1); www.nextcommerce.org
   (c) 2006 xtCommerce (write_customers_status.php)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------

   based on Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // write customers status in session
  if (isset($_SESSION['customer_id'])) {
    $customers_status_query_1 = xtc_db_query("SELECT customers_status
                                                FROM " . TABLE_CUSTOMERS . "
                                               WHERE customers_id = '" . $_SESSION['customer_id'] . "'");

    if (xtc_db_num_rows($customers_status_query_1) == 1) {
      $customers_status_value_1 = xtc_db_fetch_array($customers_status_query_1);

      $customers_status_query = xtc_db_query("SELECT *
                                                FROM " . TABLE_CUSTOMERS_STATUS . "
                                               WHERE customers_status_id = '" . $customers_status_value_1['customers_status'] . "'
                                                 AND language_id = '" . $_SESSION['languages_id'] . "'");

      $_SESSION['customers_status'] = xtc_db_fetch_array($customers_status_query);

      if ($customers_status_value_1['customers_status'] == '0' && !defined('RUN_MODE_ADMIN')) {
        $_SESSION['customers_status']['customers_status_id'] = DEFAULT_CUSTOMERS_STATUS_ID_ADMIN;
        $_SESSION['customers_status']['customers_status'] = $customers_status_value_1['customers_status'];
      } else {
        $_SESSION['customers_status']['customers_status_id'] = $customers_status_value_1['customers_status'];
        $_SESSION['customers_status']['customers_status'] = $customers_status_value_1['customers_status'];
      }
    } else {
      unset($_SESSION['customer_id']);
      xtc_redirect(xtc_href_link(FILENAME_LOGOFF, '', 'SSL'));
    }
  } else {
    $customers_status_query = xtc_db_query("SELECT *
                                              FROM " . TABLE_CUSTOMERS_STATUS . "
                                             WHERE customers_status_id = '" . DEFAULT_CUSTOMERS_STATUS_ID_GUEST . "'
                                               AND language_id = '" . $_SESSION['languages_id'] . "'");

    $_SESSION['customers_status'] = xtc_db_fetch_array($customers_status_query);
    $_SESSION['customers_status']['customers_status_id'] = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
    $_SESSION['customers_status']['customers_status'] = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
  }
?>