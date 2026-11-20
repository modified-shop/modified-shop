<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License 
  --------------------------------------------------------------*/

chdir('../../');
require_once('includes/application_top_callback.php');

// include needed classes
require_once(DIR_WS_CLASSES.'order.php');
require_once(DIR_WS_CLASSES.'xtcPrice.php');
require_once(DIR_FS_EXTERNAL.'shipcloud/class.shipcloud.php');

// include needed functions
require_once(DIR_FS_INC.'get_customers_status_by_id.inc.php');
require_once(DIR_FS_INC.'get_tracking_link.inc.php');
// tpl_modified_nova registers xtc_date_short as a Smarty modifier
require_once(DIR_FS_INC.'xtc_date_short.inc.php');

// orders_update.php is an admin module, the admin paths are not set up here
defined('DIR_FS_ADMIN') OR define('DIR_FS_ADMIN', DIR_FS_CATALOG.DIR_ADMIN);

// parse callback
$request = json_decode(file_get_contents("php://input"), true);

// a blank shipment id would match the tracking entries of other carriers, MySQL ignores trailing spaces
if (is_array($request)
    && isset($request['data']['id'])
    && is_string($request['data']['id'])
    && trim($request['data']['id']) != ''
    )
{
  $shipcloud = new shipcloud();
  $shipcloud->LoggingManager->log('INFO', 'callback', array('exception' => $request));

  $orders_query = xtc_db_query("SELECT *
                                  FROM ".TABLE_ORDERS_TRACKING." ortr
                                  JOIN ".TABLE_CARRIERS." ca
                                       ON ortr.carrier_id = ca.carrier_id
                                 WHERE ortr.sc_id = '".xtc_db_input($request['data']['id'])."'");
  if (xtc_db_num_rows($orders_query) > 0) {
    $orders = xtc_db_fetch_array($orders_query);
    
    // init order
    $order = new order($orders['orders_id']);

    $xtPrice = new xtcPrice($order->info['currency'], $order->info['status']);

    // get order language
    $lang_query = xtc_db_query("SELECT *
                                  FROM " . TABLE_LANGUAGES . "
                                 WHERE directory = '" . xtc_db_input($order->info['language']) . "'");
    $lang_array = xtc_db_fetch_array($lang_query);
    $lang = $lang_array['languages_id'];
    $lang_code = $lang_array['code'];
    $lang_charset = $lang_array['language_charset'];

    // orders status
    $orders_status_lang_array = array();
    $orders_status_query = xtc_db_query("SELECT orders_status_id,
                                                orders_status_name,
                                                language_id
                                           FROM ".TABLE_ORDERS_STATUS."
                                       ORDER BY sort_order");
    while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
      $orders_status_lang_array[$orders_status['language_id']][$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
    }

    // language translations
    require (DIR_WS_LANGUAGES.$order->info['language'].'/'. $order->info['language'] .'.php');
    require (DIR_WS_LANGUAGES.$order->info['language'].'/modules/system/shipcloud.php');

    // shipcloud sends the event type as a plain string
    $type = ((isset($request['type']) && is_string($request['type'])) ? strtoupper($request['type']) : '');

    // the callback has no sender check, shipcloud has to confirm the event before the order changes
    $event = false;
    if (strpos($type, 'SHIPMENT.') === 0 && defined($type)) {
      $event = $shipcloud->claim_event($orders['tracking_id'], $orders['sc_id'], $type);
    }
    if ($event !== true) {
      // without an answer shipcloud sends the event again
      if ($event === null) {
        header('HTTP/1.1 503 Service Unavailable');
      }
      exit();
    }

    // create smarty elements
    $smarty = new Smarty();

    // update order
    $oID = $order->info['orders_id'];
    // the confirmation took time, the order keeps the status it has now
    $status_query = xtc_db_query("SELECT orders_status
                                    FROM ".TABLE_ORDERS."
                                   WHERE orders_id = '".(int)$oID."'");
    $status_array = (($status_query !== false) ? xtc_db_fetch_array($status_query) : false);
    if (is_array($status_array)) {
      $order->info['orders_status_id'] = $status_array['orders_status'];
    }
    $status = $order->info['orders_status_id'];
    $comments = decode_htmlentities(constant($type));
    $order_updated = false;
    $email_preview = false;
    $_POST['notify'] = ((MODULE_SHIPCLOUD_EMAIL == 'True' && MODULE_SHIPCLOUD_EMAIL_TYPE == 'Shop') ? 'on' : 'off');
    $_POST['notify_comments'] = 'off';
    // only the parcel this callback belongs to
    $_POST['tracking_id'] = array($orders['tracking_id']);
    
    // the event is counted with its history entry: without the entry nothing is counted and shipcloud delivers it again
    $history_stored = false;
    try {
      define('_VALID_XTC', true);
      include (DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/orders_update.php');
    } catch (Throwable $ex) {
      $shipcloud->LoggingManager->log('ERROR', 'callback', array('exception' => $ex->getMessage()));
    }
    // an error after the entry, in the mail for instance, leaves the entry stored: it counts
    $history_stored = ($order_updated === true);
    if ($history_stored !== true) {
      $shipcloud->release_event();
      header('HTTP/1.1 503 Service Unavailable');
      exit();
    }
    // the entry is written, an error status would only have it written a second time
    if ($shipcloud->count_event($orders['tracking_id'], $type) !== true) {
      $shipcloud->LoggingManager->log('ERROR', 'callback', array('exception' => 'history entry written but not counted: '.$type.' for shipment '.$orders['sc_id']));
    }
  }
}
