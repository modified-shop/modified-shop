<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  chdir('../../');
  include('includes/application_top.php');
  
  // include needed functions
  require_once(DIR_FS_INC.'get_external_content.inc.php');

  // include needed classes
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalZettle.php');

  $request_json = get_external_content('php://input', 3, false);
  $request = json_decode($request_json, true);
  
  if (isset($request['payload'])) {
    $data = json_decode($request['payload'], true);
    
    switch ($request['eventName']) {
      case 'InventoryBalanceChanged':
        if (isset($data['balanceBefore'])
            && is_array($data['balanceBefore'])
            && isset($data['balanceAfter'])
            && is_array($data['balanceAfter'])
            )
        {
          foreach ($data['balanceBefore'] as $k => $inventory) {
            $check_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." pz2p
                                           JOIN ".TABLE_PRODUCTS." p
                                                ON p.products_id = pz2p.products_id
                                          WHERE pz2p.stock = 1
                                            AND pz2p.products_uuid = '".xtc_db_input($inventory['productUuid'])."'
                                            AND p.products_quantity != '".(float)$data['balanceAfter'][$k]['balance']."'");
            if (xtc_db_num_rows($check_query) > 0) {
              $check = xtc_db_fetch_array($check_query);
          
              $quantity = $data['balanceAfter'][$k]['balance'] - $inventory['balance'];
          
              xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                               SET products_quantity = products_quantity ".(($quantity > 0) ? '+ ' : '- ').abs($quantity)."
                             WHERE products_id = '".$check['products_id']."'");
            }
          }
        }
        break;
      
      case 'InventoryTrackingStarted':
        xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                         SET stock = 1
                       WHERE products_uuid = '".$data['productUuid']."'");
        break;

      case 'InventoryTrackingStopped':
        xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                         SET stock = 0
                       WHERE products_uuid = '".$data['productUuid']."'");
        break;
      
      case 'ProductDeleted':
        xtc_db_query("DELETE FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                            WHERE products_uuid = '".$data['uuid']."'");
        break;
    }  
  }