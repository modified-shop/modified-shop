<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // accounts with admin status are managed with the right for the admin rights (accounting)
  function customers_manage_admins() {
    $admin_access = get_admin_access($_SESSION['customer_id']);

    return (isset($admin_access['accounting']) && $admin_access['accounting'] == 1);
  }

  // true when this admin must not change the account; with $remove also the main account, it is never deleted and keeps its status
  function customers_account_locked($customers_id, $remove = false) {
    $customers_id = (int)$customers_id;
    if ($remove === true && $customers_id == 1) {
      return true;
    }
    // the own account can be edited, deleting it or changing its status is no exception
    if (($customers_id == (int)$_SESSION['customer_id'] && $remove !== true) || customers_manage_admins()) {
      return false;
    }
    $check_query = xtc_db_query("SELECT customers_status
                                   FROM ".TABLE_CUSTOMERS."
                                  WHERE customers_id = '".$customers_id."'");
    $check = xtc_db_fetch_array($check_query);

    return (is_array($check) && (int)$check['customers_status'] == 0);
  }
