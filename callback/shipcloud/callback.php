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

// parse callback
$request = json_decode(file_get_contents("php://input"), true);

if (is_array($request) && count($request) > 0) {
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

    // update order
    $oID = $order->info['orders_id'];
    $status = $order->info['orders_status_id'];
    $comments = decode_htmlentities(constant(strtoupper($request['type']['value'])));
    $order_updated = false;
    $_POST['notify'] = ((MODULE_SHIPCLOUD_EMAIL == 'True' && MODULE_SHIPCLOUD_EMAIL_TYPE == 'Shop') ? 'on' : 'off');
    $_POST['notify_comments'] = 'off';
    
    define('_VALID_XTC', true);
    include (DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/orders_update.php');
  }
}
