<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS')
      && MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS == 'true'
      && isset($specials)
      && isset($specials_quantity)
      && $specials['products_id'] == xtc_get_prid($order->products[$i]['id'])
      && $specials['specials_quantity'] != 0
      && $specials_quantity < 1
      )
  {
    // include needed classes
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalZettle.php');

    // the special just sold out, the cron sends the regular price
    xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                     SET bulk = 1
                   WHERE products_id = '".xtc_get_prid($order->products[$i]['id'])."'
                     AND status = 1
                     AND products_uuid != ''");
  }
