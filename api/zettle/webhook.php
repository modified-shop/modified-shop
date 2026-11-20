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
      // a new subscription sends a test message, it changes nothing
      if (isset($request['eventName']) && $request['eventName'] == 'TestMessage') {
        die();
      }

      // an incomplete request never reaches the key lookup, the timestamp has the ISO 8601 form of Zettle
      $signature = (isset($_SERVER['HTTP_X_IZETTLE_SIGNATURE']) ? $_SERVER['HTTP_X_IZETTLE_SIGNATURE'] : '');
      if (!is_string($signature)
          || !preg_match('/^[0-9a-fA-F]{64}\z/', $signature)
          || !is_string($request['payload'])
          || !isset($request['timestamp'])
          || !is_string($request['timestamp'])
          || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:?\d{2})\z/', $request['timestamp'])
          )
      {
        header('HTTP/1.1 401 Unauthorized');
        die();
      }

      $signing_key = defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY') ? MODULE_CATEGORIES_ZETTLE_CATEGORIES_SIGNING_KEY : '';
      $signed = ($signing_key != '' && PayPalZettle::isValidSignature($request, $signature, $signing_key));

      // no key yet, or the subscription was created again and signs with a new one: one lookup in 15 minutes.
      // Stored keys are read again after a day, a subscription that is gone or changed loses its key
      if (PayPalZettle::signingKeyLookupAllowed($signed === true)) {
        // Zettle waits 10 seconds for the answer to a delivery, the token and the list get 3 seconds each
        $PayPalZettle = new PayPalZettle(3);
        $lookup_key = $PayPalZettle->saveSigningKey();
        // without an answer from Zettle the stored keys decide
        if ($lookup_key !== false) {
          $signing_key = $lookup_key;
          $signed = ($signing_key != '' && PayPalZettle::isValidSignature($request, $signature, $signing_key));
        }
      }

      // Zettle repeats the event, so a missing key does not lose it
      if ($signed !== true) {
        header(($signing_key == '') ? 'HTTP/1.1 503 Service Unavailable' : 'HTTP/1.1 401 Unauthorized');
        die();
      }

      // deliveries end after about 73 hours, the booked events are kept for 7 days
      $event_time = strtotime($request['timestamp']);
      if ($event_time === false || $event_time < (time() - 4 * 86400)) {
        die();
      }

      $data = json_decode($request['payload'], true);

      // only the timestamp and the payload are signed, an event sent under another name is ignored
      if (!isset($request['eventName']) || !PayPalZettle::isEventPayload($request['eventName'], $data)) {
        die();
      }
      
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