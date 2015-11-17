<?php
/* -----------------------------------------------------------------------------------------
   $Id: write_customers_status.php 9050 2015-11-17 07:55:54Z GTB $

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

  // include needed function
  require_once(DIR_FS_INC.'set_customers_status_by_id.inc.php');
  
  // write customers status in session
  if (isset($_SESSION['customer_id'])) {
    $customer_status_query = xtc_db_query("SELECT customers_status
                                                FROM " . TABLE_CUSTOMERS . "
                                               WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "'");

    if (xtc_db_num_rows($customer_status_query) == 1) {
      $customer_status = xtc_db_fetch_array($customer_status_query);      

      if ($customer_status['customers_status'] == '0' && !defined('RUN_MODE_ADMIN')) {
        set_customers_status_by_id(DEFAULT_CUSTOMERS_STATUS_ID_ADMIN);
        
        // additional 
        $_SESSION['customers_status']['customers_status_id'] = DEFAULT_CUSTOMERS_STATUS_ID_ADMIN;
        $_SESSION['customers_status']['customers_status'] = $customer_status['customers_status'];
      } else {
        set_customers_status_by_id($customer_status['customers_status']);
        
        // additional 
        $_SESSION['customers_status']['customers_status_id'] = $customer_status['customers_status'];
        $_SESSION['customers_status']['customers_status'] = $customer_status['customers_status'];
      }
    } else {
      unset($_SESSION['customer_id']);
      xtc_redirect(xtc_href_link(FILENAME_LOGOFF, '', 'SSL'));
    }
  } else {
    set_customers_status_by_id(DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
    
    // additional 
    $_SESSION['customers_status']['customers_status_id'] = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
    $_SESSION['customers_status']['customers_status'] = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
  }
?>