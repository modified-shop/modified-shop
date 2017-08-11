<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_SHOPVOTE_STATUS')
      && MODULE_SHOPVOTE_STATUS == 'true'
      )
  {
    if (MODULE_SHIPPING_EASYREVIEWS != ''
        && basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS
        && isset($last_order)
        )
    {
      $orders_query = xtc_db_query("SELECT customers_email_address
                                      FROM ".TABLE_ORDERS."
                                     WHERE orders_id = '".(int)$last_order."'");
      if (xtc_db_num_rows($orders_query) > 0) {
        $orders = xtc_db_fetch_array($orders_query);

        $output = MODULE_SHIPPING_EASYREVIEWS;
        $output = str_replace(array('ORDERNUMBER', 'CUSTOMERMAIL'), array($last_order, $orders['customers_email_address']), $output);
        echo decode_htmlentities($output);
      }
    }
    
    if (MODULE_SHIPPING_RATINGSTARS != '') {
      echo decode_htmlentities(MODULE_SHIPPING_RATINGSTARS);
    }
  }
?>