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
require_once(DIR_FS_INC.'send_order_mail.inc.php');

// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');
require_once(DIR_WS_CLASSES.'order.php');

defined('TABLE_PAYPAL_WEBHOOK_EVENT') OR define('TABLE_PAYPAL_WEBHOOK_EVENT', 'paypal_webhook_event');

$request_json = get_external_content('php://input', 3, false);

$webhook_headers = array(
  'PAYPAL-AUTH-ALGO' => (isset($_SERVER['HTTP_PAYPAL_AUTH_ALGO']) ? $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] : ''),
  'PAYPAL-CERT-URL' => (isset($_SERVER['HTTP_PAYPAL_CERT_URL']) ? $_SERVER['HTTP_PAYPAL_CERT_URL'] : ''),
  'PAYPAL-TRANSMISSION-ID' => (isset($_SERVER['HTTP_PAYPAL_TRANSMISSION_ID']) ? $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] : ''),
  'PAYPAL-TRANSMISSION-SIG' => (isset($_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG']) ? $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] : ''),
  'PAYPAL-TRANSMISSION-TIME' => (isset($_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME']) ? $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] : ''),
);

$paypal_webhook_auth = new PayPalPayment('paypal');
if (!$paypal_webhook_auth->verify_webhook_signature($webhook_headers, $request_json)) {
  header("HTTP/1.0 403 Forbidden");
  header("Status: 403 Forbidden");
  exit();
}

$request = json_decode($request_json, true);

if (is_array($request)
    && isset($request['resource'])
    && is_array($request['resource'])
    )
{
  if (array_key_exists('parent_payment', $request['resource']) || array_key_exists('supplementary_data', $request['resource'])) {
    if (array_key_exists('parent_payment', $request['resource'])) {
      $payment_id = $request['resource']['parent_payment'];
      $version = 1;
    } else {
      $payment_id = $request['resource']['supplementary_data']['related_ids']['order_id'];
      $version = 2;
    }
  
    $check_query = xtc_db_query("SELECT p.*,
                                        o.orders_status,
                                        o.payment_class,
                                        o.customers_id,
                                        o.customers_country_iso_code_2
                                   FROM ".TABLE_PAYPAL_PAYMENT." p
                                   JOIN ".TABLE_ORDERS." o
                                        ON o.orders_id = p.orders_id
                                  WHERE p.payment_id = '".xtc_db_input($payment_id)."'");
  
    if (xtc_db_num_rows($check_query) > 0) {
      $check = xtc_db_fetch_array($check_query);
      
      $notified = 0;
      $event_id = ((isset($request['id'])) ? (string)$request['id'] : '');

      if ($version == 1) {
        $paypal = new PayPalPayment($check['payment_class']);
      } else {
        $paypal = new PayPalPaymentV2($check['payment_class']);
        $PayPalOrder = $paypal->GetOrder($check['payment_id']);
        
        if ($check['payment_class'] == 'paypalpui') {
          $paypal->FinishOrderPui($check['orders_id'], $PayPalOrder);
        } elseif ($check['transaction_id'] == '' && isset($PayPalOrder->purchase_units[0]->payments)) {
          // a payment left unconfirmed during checkout learns its id here
          foreach (array('captures', 'authorizations') as $payment_type) {
            if (isset($PayPalOrder->purchase_units[0]->payments->{$payment_type}[0]->id)) {
              xtc_db_query("UPDATE ".TABLE_PAYPAL_PAYMENT."
                               SET transaction_id = '".xtc_db_input($PayPalOrder->purchase_units[0]->payments->{$payment_type}[0]->id)."'
                             WHERE paypal_id = '".(int)$check['paypal_id']."'");
              break;
            }
          }
        }

        if (is_object($PayPalOrder)
            && isset($PayPalOrder->status)
            && $PayPalOrder->status == 'COMPLETED'
            && $check['send_order'] >= 1
            )
        {
          // send_order 2: the merchant copy went out already, retry the customer mail only
          $customer_mail_sent = false;
          $mail_output_level = ob_get_level();
          ob_start();
          try {
            $mail_processed = send_order_mail($check['orders_id'], false, ($check['send_order'] == 2), $customer_mail_sent);
          } finally {
            // buffered so a mail warning cannot send the headers before the 503
            $mail_output = '';
            while (ob_get_level() > $mail_output_level) {
              $mail_output = ob_get_clean().$mail_output;
            }
          }

          if ($customer_mail_sent === true) {
            $notified = 1;
          }

          if ($mail_processed === true) {
            xtc_db_query("UPDATE ".TABLE_PAYPAL_PAYMENT."
                             SET send_order = 0
                           WHERE paypal_id = '".(int)$check['paypal_id']."'");
          } else {
            xtc_db_query("UPDATE ".TABLE_PAYPAL_PAYMENT."
                             SET send_order = 2
                           WHERE paypal_id = '".(int)$check['paypal_id']."'");

            header("HTTP/1.0 503 Service Unavailable");
            header("Status: 503 Service Unavailable");
          }
          echo $mail_output;
        } elseif ($check['send_order'] >= 1 && !isset($PayPalOrder->status)) {
          // without the order the pending mail stays undecided, PayPal has to deliver the event again
          header("HTTP/1.0 503 Service Unavailable");
          header("Status: 503 Service Unavailable");
        }
      }

      $orders_status_id = $paypal->get_config($request['event_type']);
      if ($orders_status_id < 0) {
        $orders_status_id = $check['orders_status'];
      }

      // PayPal repeats an event for up to three days, its status is written only once
      $recorded_event = 0;
      if ($event_id != '') {
        // created here so the check does not wait for a module update
        xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_WEBHOOK_EVENT." (
                        event_id varchar(128) NOT NULL,
                        orders_id int(11) NOT NULL,
                        orders_status_history_id int(11) NOT NULL,
                        date_added datetime NOT NULL,
                        PRIMARY KEY (event_id),
                        KEY idx_date_added (date_added)
                      );");

        $event_query = xtc_db_query("SELECT orders_status_history_id
                                       FROM ".TABLE_PAYPAL_WEBHOOK_EVENT."
                                      WHERE event_id = '".xtc_db_input($event_id)."'");
        if (xtc_db_num_rows($event_query) > 0) {
          $event = xtc_db_fetch_array($event_query);
          $recorded_event = (int)$event['orders_status_history_id'];
        }
      }

      if ($recorded_event == 0) {
        $paypal->update_order($request['summary'], $orders_status_id, $check['orders_id'], $notified);

        if ($event_id != '') {
          xtc_db_query("INSERT IGNORE INTO ".TABLE_PAYPAL_WEBHOOK_EVENT."
                                    (event_id, orders_id, orders_status_history_id, date_added)
                             SELECT '".xtc_db_input($event_id)."', '".(int)$check['orders_id']."', MAX(orders_status_history_id), now()
                               FROM ".TABLE_ORDERS_STATUS_HISTORY."
                              WHERE orders_id = '".(int)$check['orders_id']."'");

          xtc_db_query("DELETE FROM ".TABLE_PAYPAL_WEBHOOK_EVENT."
                              WHERE date_added < DATE_SUB(now(), INTERVAL 30 DAY)");
        }
      } elseif ($notified == 1) {
        // the order may have moved on since the event, only the notification is new
        xtc_db_query("UPDATE ".TABLE_ORDERS_STATUS_HISTORY."
                         SET customer_notified = 1
                       WHERE orders_status_history_id = '".$recorded_event."'");
      }
    } else {
      // order is missing
      header("HTTP/1.0 404 Not Found");
      header("Status: 404 Not Found");
    }
  } else {
    // webhook action
    switch ($request['event_type']) {
      case 'VAULT.PAYMENT-TOKEN.DELETED':
      case 'VAULT.PAYMENT-TOKEN.DELETION-INITIATED':
        xtc_db_query("DELETE FROM ".TABLE_PAYPAL_VAULT."
                            WHERE vault_id = '".xtc_db_input($request['resource']['id'])."'");
        break;
        
      case 'VAULT.PAYMENT-TOKEN.CREATED':
        if (isset($request['resource']['metadata']['order_id'])) {
          $orders_query = xtc_db_query("SELECT o.customers_id,
                                               o.payment_class
                                          FROM ".TABLE_PAYPAL_PAYMENT." p
                                          JOIN ".TABLE_ORDERS." o
                                               ON p.orders_id = o.orders_id
                                         WHERE p.payment_id = '".xtc_db_input($request['resource']['metadata']['order_id'])."'");
          if (xtc_db_num_rows($orders_query) > 0) {
            $orders = xtc_db_fetch_array($orders_query);
          
            $sql_data_array = array(
              'customers_id' => (int)$orders['customers_id'],
              'paypal_customers_id' => $request['resource']['customer']['id'],
              'vault_id' => $request['resource']['id'],
              'payment_source' => (($orders['payment_class'] == 'paypalacdc') ? 'card' : 'paypal')
            );
            xtc_db_perform(TABLE_PAYPAL_VAULT, $sql_data_array);
          }
        }
        break;
      
      default:
        $check_query = xtc_db_query("SELECT p.*,
                                            o.orders_status,
                                            o.payment_class
                                       FROM ".TABLE_PAYPAL_PAYMENT." p
                                       JOIN ".TABLE_ORDERS." o
                                            ON o.orders_id = p.orders_id
                                      WHERE p.payment_id = '".xtc_db_input($request['resource']['id'])."'");
        if (xtc_db_num_rows($check_query) > 0) {
          $check = xtc_db_fetch_array($check_query);
          
          $paypal = new PayPalPaymentV2($check['payment_class']);
                
          $orders_status_id = $paypal->get_config($request['event_type']);
          if ($orders_status_id < 0) {
            $orders_status_id = $check['orders_status'];
          }
          
          $paypal->update_order($request['summary'], $orders_status_id, $check['orders_id']);
        }
        break;
    }
  }
} else {
  // order is missing
  header("HTTP/1.0 404 Not Found");
  header("Status: 404 Not Found");
}
