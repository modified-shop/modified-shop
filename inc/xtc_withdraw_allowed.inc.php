<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // An order keeps its customer group even when the customer changes groups later.
  function xtc_withdraw_allowed($customers_status) {
    if (!defined('MODULE_WITHDRAW_B2B_CUSTOMERS_STATUS')) {
      return true;
    }

    foreach (explode(',', MODULE_WITHDRAW_B2B_CUSTOMERS_STATUS) as $group) {
      $group = trim($group);
      if (ctype_digit($group) && (int)$group > 0 && (int)$group === (int)$customers_status) {
        return false;
      }
    }

    return true;
  }
