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
  
  if (defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS')
      && MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS == 'true'
      )
  {
    $request_json = get_external_content('php://input', 3, false);
    $request = json_decode($request_json, true);
  
    if (isset($request['payload'])) {
      $data = json_decode($request['payload'], true);
      
      switch ($request['eventName']) {
        case 'InventoryBalanceChanged':
          // changes made by this shop come back as events and are already booked
          if (isset($data['updated']['clientUuid']) && PayPalZettle::isOwnClient($data['updated']['clientUuid'])) {
            break;
          }

          // an event can be delivered more than once, retries end after about 73 hours
          xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_ZETTLE_EVENTS." (
                          `event_hash` char(40) NOT NULL,
                          `booked` int(1) NOT NULL DEFAULT 0,
                          `date_added` datetime NOT NULL,
                          PRIMARY KEY (`event_hash`),
                          KEY `idx_date_added` (`date_added`)
                        )");
          xtc_db_query("DELETE FROM ".TABLE_PAYPAL_ZETTLE_EVENTS." WHERE date_added < (NOW() - INTERVAL 7 DAY)");

          if (isset($data['balanceBefore'])
              && is_array($data['balanceBefore'])
              && isset($data['balanceAfter'])
              && is_array($data['balanceAfter'])
              )
          {
            $event_failed = false;
            // marks this request's own bookings, the error reset must not reopen an earlier or parallel one
            $attempt = mt_rand(2, 2147483647);
            foreach ($data['balanceBefore'] as $k => $inventory) {
              $event_hash = sha1($request['payload'].'-'.$k);
              $quantity = (float)($data['balanceAfter'][$k]['balance'] - $inventory['balance']);

              // one statement books the line and marks it, only MyISAM can split them on a write error
              if (xtc_db_query("INSERT IGNORE INTO ".TABLE_PAYPAL_ZETTLE_EVENTS." (event_hash, date_added) VALUES ('".$event_hash."', NOW())") === false) {
                $event_failed = true;
              } elseif (xtc_db_query("UPDATE ".TABLE_PRODUCTS." p
                                     JOIN ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." pz2p
                                          ON pz2p.products_id = p.products_id
                                     JOIN ".TABLE_PAYPAL_ZETTLE_EVENTS." pze
                                          ON pze.event_hash = '".$event_hash."'
                                      SET p.products_quantity = p.products_quantity ".(($quantity > 0) ? '+ ' : '- ').abs($quantity).",
                                          pze.booked = ".$attempt."
                                    WHERE pze.booked = 0
                                      AND pz2p.stock = 1
                                      AND pz2p.products_uuid = '".xtc_db_input($inventory['productUuid'])."'
                                      AND p.products_quantity != '".(float)$data['balanceAfter'][$k]['balance']."'") === false) {
                // MyISAM can keep the mark of a failed stock write, the retry has to find the line open
                xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_EVENTS." SET booked = 0 WHERE event_hash = '".$event_hash."' AND booked = ".$attempt);
                $event_failed = true;
              } elseif (xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_EVENTS." SET booked = 1 WHERE event_hash = '".$event_hash."' AND booked IN (0, ".$attempt.")") === false) {
                $event_failed = true;
              }
            }

            // Zettle delivers the event again after an error status
            if ($event_failed === true) {
              header('HTTP/1.1 500 Internal Server Error');
            }
          }
          break;
        
        case 'InventoryTrackingStarted':
          xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                           SET stock = 1
                         WHERE products_uuid = '".xtc_db_input($data['productUuid'])."'");
          break;
  
        case 'InventoryTrackingStopped':
          xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                           SET stock = 0
                         WHERE products_uuid = '".xtc_db_input($data['productUuid'])."'");
          break;
        
        case 'ProductDeleted':
          xtc_db_query("DELETE FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                              WHERE products_uuid = '".xtc_db_input($data['uuid'])."'");
          break;
      }  
    }
  }