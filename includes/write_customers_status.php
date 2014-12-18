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

   based on Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // write customers status in session
  if (isset($_SESSION['customer_id'])) {
    $customers_status_query = xtc_db_query("
      SELECT c.customers_status, cs.*
        FROM " . TABLE_CUSTOMERS . " c
        JOIN " . TABLE_CUSTOMERS_STATUS . " cs
          ON cs.customers_status_id = c.customers_status 
       WHERE customers_id = " . $_SESSION['customer_id'] . "
         AND cs.language_id = " . $_SESSION['languages_id']);
    if (xtc_db_num_rows($customers_status_query)) {
      $_SESSION['customers_status'] = xtc_db_fetch_array($customers_status_query);
      if ($customers_status_query['customers_status'] == '0' && !defined('RUN_MODE_ADMIN')) {
        $_SESSION['customers_status']['customers_status_id'] = DEFAULT_CUSTOMERS_STATUS_ID_ADMIN;
      }
    } else {
      unset($_SESSION['customer_id']);
      xtc_redirect(xtc_href_link(FILENAME_LOGOFF, '', 'SSL'));
    }
  } else {
    $customers_status_query = xtc_db_query("
      SELECT *, customers_status_id as customers_status
        FROM " . TABLE_CUSTOMERS_STATUS . "
       WHERE customers_status_id = " . DEFAULT_CUSTOMERS_STATUS_ID_GUEST . "
         AND language_id = " . $_SESSION['languages_id']);
    $_SESSION['customers_status'] = xtc_db_fetch_array($customers_status_query);
  }
?>