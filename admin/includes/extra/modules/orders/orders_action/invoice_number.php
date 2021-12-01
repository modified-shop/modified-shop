<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_INVOICE_NUMBER_STATUS') 
      && MODULE_INVOICE_NUMBER_STATUS == 'True'
      && $order_exists
      )
  {
    $order = new order($oID);
    if ($order->info['ibn_billnr'] == '') {
      $n = (int)MODULE_INVOICE_NUMBER_IBN_BILLNR;
      
      if ($n > 0) {
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = ".($n + 1)."
                       WHERE configuration_key = 'MODULE_INVOICE_NUMBER_IBN_BILLNR'");
    
        $ibn_billnr = MODULE_INVOICE_NUMBER_IBN_BILLNR_FORMAT;
        $ibn_billnr = str_replace('{n}', $n, $ibn_billnr);
        $ibn_billnr = str_replace('{d}', date('d'), $ibn_billnr);
        $ibn_billnr = str_replace('{m}', date('m'), $ibn_billnr);
        $ibn_billnr = str_replace('{y}', date('Y'), $ibn_billnr);
    
        $sql_data_array = array(
          'ibn_billnr' => xtc_db_prepare_input($ibn_billnr), 
          'ibn_billdate' => 'now()'
        );
        xtc_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '".(int)$oID."'"); 
      }
    }
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=edit'));
  }
