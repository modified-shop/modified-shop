<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// define some tables
defined('TABLE_KLARNA_PAYMENTS') or define('TABLE_KLARNA_PAYMENTS', 'klarna_payments');


//include needed functions
require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');

require_once(DIR_FS_INC.'xtc_get_countries.inc.php');
require_once(DIR_FS_INC.'xtc_get_products_image.inc.php');

// include needed classes
require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaPaymentBase.php');
require_once(DIR_FS_EXTERNAL.'klarna/functions/klarna_category_check.php');
require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');


// language
if (isset($_SESSION['language']) && is_file(DIR_FS_EXTERNAL.'klarna/lang/'.$_SESSION['language'].'.php')) {
  require_once(DIR_FS_EXTERNAL.'klarna/lang/'.$_SESSION['language'].'.php');
} else {
  require_once(DIR_FS_EXTERNAL.'klarna/lang/english.php');
}


class KlarnaPayment extends KlarnaPaymentBase {

  var $code;
  var $logger;
  var $merchant_id;
  var $shared_secret;
  var $api_endpoint;
  var $connector;
  // set after a failed session request, shared by all Klarna modules in this request
  public static $session_failed = false;

  function __construct($code) {
    $this->code = $code;

    // logger
    $this->logger = new LoggingManager(DIR_FS_LOG.'mod_%s_%s.log', 'info', 'error');

    KlarnaPaymentBase::init();

    $this->merchant_id = ((defined('MODULE_PAYMENT_KLARNA_MERCHANT_ID')) ? MODULE_PAYMENT_KLARNA_MERCHANT_ID : '');
    $this->shared_secret = ((defined('MODULE_PAYMENT_KLARNA_SHARED_SECRET')) ? MODULE_PAYMENT_KLARNA_SHARED_SECRET : '');
    
    if (defined('MODULE_PAYMENT_KLARNA_MODE') && MODULE_PAYMENT_KLARNA_MODE == 'LIVE') {
      $this->api_endpoint = Klarna\Rest\Transport\ConnectorInterface::EU_BASE_URL;
    } else {
      $this->api_endpoint = Klarna\Rest\Transport\ConnectorInterface::EU_TEST_BASE_URL;
    }
    
    $this->setConnector();
    
    if (defined('RUN_MODE_ADMIN') && defined('MODULE_PAYMENT_KLARNA_CHECK_BUTTON')) {
      $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULES, 'set=payment&module=' . $this->code . '&action=custom') . '">' . MODULE_PAYMENT_KLARNA_CHECK_BUTTON . '</a>';
    }
  }


  function setConnector() {
    require_once(DIR_FS_INC.'get_database_version.inc.php');
    $db_version = get_database_version();
    
    $user_agent = new Klarna\Rest\Transport\UserAgent();
    $user_agent->setField('modified-eCommerce-Shopsoftware', 'v', $db_version['plain']);
    $user_agent->setField('Klarna', 'v', $this->klarna_version);
    
    // build the client with timeouts, the SDK factory leaves them unlimited
    $client = new \GuzzleHttp\Client(array(
      'base_uri' => $this->api_endpoint,
      'connect_timeout' => 10,
      'timeout' => 30,
    ));
    $this->connector = new Klarna\Rest\Transport\GuzzleConnector(
      $client,
      $this->merchant_id,
      $this->shared_secret,
      $user_agent
    );
  }


  function getKlarnaSession() {
    // every module calls this from its constructor, a Klarna outage would cost one timeout each
    if (KlarnaPayment::$session_failed === true) {
      return;
    }
    
    $order_array = $this->getOrderData(true);
        
    try {
      $session = new Klarna\Rest\Payments\Sessions($this->connector);
      $resonse = $session->create($order_array);
        
      // set session
      $_SESSION['klarna'] = array(
        'session_id' => $resonse->getId(),
        'client_token' => $resonse['client_token'],
        'methods' => $resonse['payment_method_categories'],
        'sendto' => $_SESSION['sendto'],
        'sendto_id' => $this->get_country_id($_SESSION['sendto']),
        'billto' => $_SESSION['billto'],
        'billto_id' => $this->get_country_id($_SESSION['billto']),
        'cart_id' => $_SESSION['cart']->cartID,
        'time_created' => time(),
      );
    } catch (Exception $e) {
      KlarnaPayment::$session_failed = true;
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    } 
  }


  // admin action of the module page, shows the categories Klarna returns for the store country
  function custom() {
    global $messageStack;
    
    if (!defined('RUN_MODE_ADMIN')) {
      return;
    }
    
    $check = $this->check_payment_categories();
    $messageStack->add_session(klarna_category_check_html($check), (($check['error_type'] == '') ? 'info' : 'error'));
  }


  // creates a session with a test amount, no order and no write in the shop
  function check_payment_categories() {
    $check = array(
      'context' => array(
        'country' => '',
        'currency' => ((defined('DEFAULT_CURRENCY')) ? DEFAULT_CURRENCY : ''),
        'amount' => '100.00',
        'mode' => ((defined('MODULE_PAYMENT_KLARNA_MODE')) ? MODULE_PAYMENT_KLARNA_MODE : ''),
      ),
      'error_type' => '',
      'error_message' => '',
      'categories' => array(),
      'used_category' => '',
      'installed' => false,
      'active' => false,
      'active_old' => array(),
    );
    
    if ($this->merchant_id == '' || $this->shared_secret == '') {
      $check['error_type'] = 'credentials';
      $this->logger->log('klarna', 'category check: no credentials');
      
      return $check;
    }
    
    $country_query = xtc_db_query("SELECT countries_iso_code_2
                                     FROM ".TABLE_COUNTRIES."
                                    WHERE countries_id = '".(int)STORE_COUNTRY."'");
    $country = xtc_db_fetch_array($country_query);
    if (!is_array($country) || $country['countries_iso_code_2'] == '') {
      $check['error_type'] = 'country';
      $this->logger->log('klarna', 'category check: store country not found');
      
      return $check;
    }
    $check['context']['country'] = $country['countries_iso_code_2'];
    
    $order_array = array(
      'locale' => strtolower((isset($_SESSION['language_code'])) ? $_SESSION['language_code'] : 'en').'-'.strtoupper($country['countries_iso_code_2']),
      'purchase_country' => $country['countries_iso_code_2'],
      'purchase_currency' => $check['context']['currency'],
      'order_amount' => 10000,
      'order_tax_amount' => 0,
      'order_lines' => array(
        array(
          'type' => 'physical',
          'reference' => 'check',
          'name' => 'Klarna check',
          'quantity' => 1,
          'unit_price' => 10000,
          'tax_rate' => 0,
          'total_amount' => 10000,
          'total_tax_amount' => 0,
        ),
      ),
    );
    
    try {
      $session = new Klarna\Rest\Payments\Sessions($this->connector);
      $response = $session->create($order_array);
      $categories = ((isset($response['payment_method_categories'])) ? $response['payment_method_categories'] : array());
    } catch (Exception $e) {
      $check['error_type'] = 'api';
      $check['error_message'] = $e->getMessage();
      $this->logger->log('klarna', 'category check: '.$e->getMessage());
      
      return $check;
    }
    
    $installed_modules = ((defined('MODULE_PAYMENT_INSTALLED')) ? explode(';', MODULE_PAYMENT_INSTALLED) : array());
    $installed = in_array('klarna.php', $installed_modules, true);
    $active = ($installed && defined('MODULE_PAYMENT_KLARNA_STATUS') && MODULE_PAYMENT_KLARNA_STATUS == 'True');
    
    $active_old = array();
    foreach (klarna_legacy_modules() as $module) {
      if (in_array($module.'.php', $installed_modules, true)
          && defined('MODULE_PAYMENT_'.strtoupper($module).'_STATUS')
          && constant('MODULE_PAYMENT_'.strtoupper($module).'_STATUS') == 'True'
          )
      {
        $active_old[] = $module;
      }
    }
    
    return array_merge($check, klarna_category_check_analyze($categories, $installed, $active, $active_old));
  }


  function readKlarnaSession($session_id) {
    try {
      $session = new Klarna\Rest\Payments\Sessions($this->connector, $session_id);
      $session->fetch();
      
      return $session->getArrayCopy();
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function updateKlarnaSession($session_id) {
    $order_array = $this->getOrderData(true);
    
    try {
      $session = new Klarna\Rest\Payments\Sessions($this->connector, $session_id);
      $resonse = $session->update($order_array);
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function updateMerchantReference($order_id, $reference1, $reference2) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->updateMerchantReferences(array(
        'merchant_reference1' => $reference1,
        'merchant_reference2' => $reference2,
      ));
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function fetchOrder($order_id) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->fetch();
      
      return $management->getArrayCopy();
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }
  }


  function cancelOrder($order_id) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->cancel();
      
      return TEXT_KLARNA_TRANSACTION_CANCEL;
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      
      return $e->getMessage();
    }
  }


  function resolveFraudStatus($oID, $event_type = '') {
    $check_query = xtc_db_query("SELECT kp.klarna_order_id,
                                        kp.fraud_status
                                   FROM ".TABLE_KLARNA_PAYMENTS." kp
                                   JOIN ".TABLE_ORDERS." o
                                        ON o.orders_id = kp.orders_id
                                  WHERE kp.orders_id = '".(int)$oID."'");
    if (xtc_db_num_rows($check_query) < 1) {
      return false;
    }
    $check = xtc_db_fetch_array($check_query);
    
    $pending_status = $this->get_pending_status_id();
    $accepted_status = (((int)$this->order_status > 0) ? (int)$this->order_status : $pending_status);
    
    // the stop cannot be read back from the order, so the push decides
    if ($event_type == 'FRAUD_RISK_STOPPED') {
      return $this->stopFraudReview($oID, $pending_status, $accepted_status);
    }
    
    // the accept is still running or its request died, the row shows it
    if ($check['fraud_status'] == 'ACCEPTING') {
      $orders_status = $this->get_orders_status($oID);
      if ($accepted_status != $pending_status && $orders_status == $pending_status) {
        return $this->completeAccept($oID, $check['klarna_order_id'], $pending_status, $accepted_status);
      }
      
      if ($orders_status == $accepted_status) {
        return $this->startCapture($oID, $check['klarna_order_id'], $accepted_status);
      }
      
      // a manual status change wins
      if ($this->skip_capture($oID, 'Klarna fraud status: ACCEPTED, order status not changed, nothing captured') === false) {
        return $this->is_fraud_status_final($oID);
      }
      
      return 'ACCEPTED';
    }
    
    // accepted before, but the capture failed: only the capture is repeated
    if ($check['fraud_status'] == 'CAPTURE_PENDING') {
      return $this->captureIfDue($oID, $check['klarna_order_id'], $accepted_status);
    }
    
    if ($check['fraud_status'] != 'PENDING') {
      return true;
    }
    
    $data = $this->fetchOrder($check['klarna_order_id']);
    if (!is_array($data) || !isset($data['fraud_status'])) {
      return false;
    }
    
    $fraud_status = strtoupper($data['fraud_status']);
    if ($fraud_status != 'ACCEPTED' && $fraud_status != 'REJECTED') {
      return false;
    }
    
    // read after the API call, the merchant may have changed the status meanwhile
    $current_status = $this->get_orders_status($oID);
    $waiting = ($current_status == $pending_status);
    
    $capture = ($fraud_status == 'ACCEPTED' && $waiting === true && $this->capture_enabled());
    
    // a repeated notification or a parallel admin view must not resolve the order twice
    xtc_db_query("UPDATE ".TABLE_KLARNA_PAYMENTS."
                     SET fraud_status = '".xtc_db_input((($capture === true) ? 'ACCEPTING' : $fraud_status))."'
                   WHERE orders_id = '".(int)$oID."'
                     AND fraud_status = 'PENDING'");
    if (xtc_db_affected_rows() < 1) {
      // only a final result counts as done, anything else is still in progress
      return $this->is_fraud_status_final($oID);
    }
    
    if ($waiting === false) {
      $this->insert_status_history($oID, $current_status, 'Klarna fraud status: '.$fraud_status.', order status not changed'.(($fraud_status == 'ACCEPTED' && $this->capture_enabled()) ? ', nothing captured' : ''));
      
      return $fraud_status;
    }
    
    // the status change is conditional, a parallel stop or a manual change must not be overwritten
    if ($fraud_status == 'ACCEPTED') {
      if ($capture === true) {
        return $this->completeAccept($oID, $check['klarna_order_id'], $pending_status, $accepted_status, $data, true);
      }
      
      if ($this->change_orders_status($oID, $pending_status, $accepted_status, 'Klarna fraud status: ACCEPTED', 'ACCEPTED') === false) {
        // after a stop in between, the stop entry stands alone
        $state_query = xtc_db_query("SELECT fraud_status
                                       FROM ".TABLE_KLARNA_PAYMENTS."
                                      WHERE orders_id = '".(int)$oID."'");
        $state = xtc_db_fetch_array($state_query);
        if (is_array($state) && $state['fraud_status'] == 'ACCEPTED') {
          $this->insert_status_history($oID, $this->get_orders_status($oID), 'Klarna fraud status: ACCEPTED, order status not changed');
        }
      }
      
      return $fraud_status;
    }
    
    $rejected_status = ((defined('MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID') && (int)MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID > 0) ? (int)MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID : 0);
    if ($rejected_status == 0
        || $this->change_orders_status($oID, $pending_status, $rejected_status, 'Klarna fraud status: REJECTED', 'REJECTED') === false
        )
    {
      $this->insert_status_history($oID, $this->get_orders_status($oID), 'Klarna fraud status: REJECTED');
    }
    
    return $fraud_status;
  }


  function completeAccept($oID, $order_id, $pending_status, $accepted_status, $data = null, $first_attempt = false) {
    // a parallel call may have completed the accept, then the capture is still due
    if ($this->change_orders_status($oID, $pending_status, $accepted_status, 'Klarna fraud status: ACCEPTED', 'ACCEPTING') === false
        && $this->get_orders_status($oID) != $accepted_status
        )
    {
      if ($this->skip_capture($oID, 'Klarna fraud status: ACCEPTED, order status not changed, nothing captured') === false) {
        return $this->is_fraud_status_final($oID);
      }
      
      return true;
    }
    
    return $this->startCapture($oID, $order_id, $accepted_status, $data, $first_attempt);
  }


  function startCapture($oID, $order_id, $accepted_status, $data = null, $first_attempt = false) {
    // the status change is done, from here on only the capture is open
    xtc_db_query("UPDATE ".TABLE_KLARNA_PAYMENTS."
                     SET fraud_status = 'CAPTURE_PENDING'
                   WHERE orders_id = '".(int)$oID."'
                     AND fraud_status = 'ACCEPTING'");
    if (xtc_db_affected_rows() < 1) {
      // a stop or a parallel call took over
      return $this->is_fraud_status_final($oID);
    }
    
    return $this->captureIfDue($oID, $order_id, $accepted_status, $data, $first_attempt);
  }


  function stopFraudReview($oID, $pending_status, $accepted_status) {
    // moving the row first makes sure no capture follows
    xtc_db_query("UPDATE ".TABLE_KLARNA_PAYMENTS."
                     SET fraud_status = 'STOPPED'
                   WHERE orders_id = '".(int)$oID."'
                     AND fraud_status NOT IN ('STOPPED', 'REJECTED')");
    if (xtc_db_affected_rows() < 1) {
      return true;
    }
    
    // a stop during the capture request cannot be prevented, the history then shows both entries
    $comment = 'Klarna fraud status: STOPPED, check the order before shipping';
    $rejected_status = ((defined('MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID') && (int)MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID > 0) ? (int)MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID : 0);
    
    // the second round catches a parallel accept that moved the order meanwhile
    for ($i = 0; $i < 2; $i ++) {
      $orders_status = $this->get_orders_status($oID);
      if ($rejected_status > 0
          && ($orders_status == $pending_status || $orders_status == $accepted_status)
          && $this->change_orders_status($oID, $orders_status, $rejected_status, $comment, 'STOPPED') === true
          )
      {
        return 'STOPPED';
      }
      if ($rejected_status == 0 || ($orders_status != $pending_status && $orders_status != $accepted_status)) {
        break;
      }
    }
    
    $this->insert_status_history($oID, $this->get_orders_status($oID), $comment);
    
    return 'STOPPED';
  }


  function get_orders_status($oID) {
    $status_query = xtc_db_query("SELECT orders_status
                                    FROM ".TABLE_ORDERS."
                                   WHERE orders_id = '".(int)$oID."'");
    $status = xtc_db_fetch_array($status_query);
    
    return ((is_array($status)) ? (int)$status['orders_status'] : 0);
  }


  function change_orders_status($oID, $from_status, $to_status, $comment, $fraud_status) {
    // order status and row state are checked in one statement, a stop or a manual change in between wins
    if ((int)$to_status != (int)$from_status) {
      xtc_db_query("UPDATE ".TABLE_ORDERS." o
                      JOIN ".TABLE_KLARNA_PAYMENTS." kp
                           ON kp.orders_id = o.orders_id
                       SET o.orders_status = '".(int)$to_status."',
                           o.last_modified = now()
                     WHERE o.orders_id = '".(int)$oID."'
                       AND o.orders_status = '".(int)$from_status."'
                       AND kp.fraud_status = '".xtc_db_input($fraud_status)."'");
      if (xtc_db_affected_rows() < 1) {
        return false;
      }
    } else {
      $check_query = xtc_db_query("SELECT o.orders_id
                                     FROM ".TABLE_ORDERS." o
                                     JOIN ".TABLE_KLARNA_PAYMENTS." kp
                                          ON kp.orders_id = o.orders_id
                                    WHERE o.orders_id = '".(int)$oID."'
                                      AND o.orders_status = '".(int)$from_status."'
                                      AND kp.fraud_status = '".xtc_db_input($fraud_status)."'");
      if (xtc_db_num_rows($check_query) < 1) {
        return false;
      }
    }
    
    $this->insert_status_history($oID, $to_status, $comment);
    
    return true;
  }


  function change_new_orders_status($oID, $from_status, $to_status, $comment) {
    // for an order without a klarna_payments row, no resolver or push can act on it yet
    xtc_db_query("UPDATE ".TABLE_ORDERS."
                     SET orders_status = '".(int)$to_status."',
                         last_modified = now()
                   WHERE orders_id = '".(int)$oID."'
                     AND orders_status = '".(int)$from_status."'");
    if (xtc_db_affected_rows() < 1) {
      return false;
    }
    
    $this->insert_status_history($oID, $to_status, $comment);
    
    return true;
  }


  function insert_status_history($oID, $orders_status, $comment) {
    $order_history_data = array(
      'orders_id' => (int)$oID,
      'orders_status_id' => (int)$orders_status,
      'date_added' => 'now()',
      'customer_notified' => '0',
      'comments' => $comment,
    );
    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_history_data);
  }


  function is_fraud_status_final($oID) {
    $final_query = xtc_db_query("SELECT fraud_status
                                   FROM ".TABLE_KLARNA_PAYMENTS."
                                  WHERE orders_id = '".(int)$oID."'");
    $final = xtc_db_fetch_array($final_query);
    
    return (is_array($final) && in_array($final['fraud_status'], array('ACCEPTED', 'REJECTED', 'STOPPED')));
  }


  function skip_capture($oID, $comment) {
    xtc_db_query("UPDATE ".TABLE_KLARNA_PAYMENTS."
                     SET fraud_status = 'ACCEPTED'
                   WHERE orders_id = '".(int)$oID."'
                     AND fraud_status IN ('ACCEPTING', 'CAPTURE_PENDING')");
    if (xtc_db_affected_rows() < 1) {
      return false;
    }
    
    $this->insert_status_history($oID, $this->get_orders_status($oID), $comment);
    
    return true;
  }


  function captureIfDue($oID, $order_id, $accepted_status, $data = null, $first_attempt = false) {
    // fetched first, the API call takes seconds and a stop may arrive meanwhile
    if (!is_array($data)) {
      $data = $this->fetchOrder($order_id);
    }
    
    // read again right before the capture, a parallel stop or a finished capture may have changed the row
    $check_query = xtc_db_query("SELECT fraud_status
                                   FROM ".TABLE_KLARNA_PAYMENTS."
                                  WHERE orders_id = '".(int)$oID."'");
    $check = xtc_db_fetch_array($check_query);
    if (!is_array($check) || $check['fraud_status'] != 'CAPTURE_PENDING') {
      return $this->is_fraud_status_final($oID);
    }
    
    // without a module status the accepted status is the waiting status, a reset to it cannot be told apart
    if ($this->get_orders_status($oID) != $accepted_status) {
      // a manual status change wins, the capture is skipped for good
      if ($this->skip_capture($oID, 'Klarna capture skipped, the order status was changed in the meantime') === false) {
        return $this->is_fraud_status_final($oID);
      }
      
      return 'ACCEPTED';
    }
    
    if (!is_array($data) || !isset($data['remaining_authorized_amount'])) {
      return false;
    }
    
    return $this->captureAcceptedOrder($oID, $order_id, $data, $first_attempt);
  }


  function captureAcceptedOrder($oID, $order_id, $data, $first_attempt = false) {
    // capture what Klarna still holds, so a repeat never captures twice
    $captured = false;
    if ($data['remaining_authorized_amount'] > 0 && $this->capture_enabled()) {
      if ($this->captureOrder($data['remaining_authorized_amount'] / 100, $order_id) == '') {
        // one entry for the first failure, not one per retry
        if ($first_attempt === true) {
          $this->insert_status_history($oID, $this->get_orders_status($oID), 'Klarna capture failed, it is retried automatically');
        }
        
        return false;
      }
      $captured = true;
    }
    
    xtc_db_query("UPDATE ".TABLE_KLARNA_PAYMENTS."
                     SET fraud_status = 'ACCEPTED'
                   WHERE orders_id = '".(int)$oID."'
                     AND fraud_status = 'CAPTURE_PENDING'");
    
    if ($captured === true) {
      $this->insert_status_history($oID, $this->get_orders_status($oID), 'Klarna capture: '.number_format($data['remaining_authorized_amount'] / 100, 2, '.', '').((isset($data['purchase_currency'])) ? ' '.$data['purchase_currency'] : '').' captured');
    }
    
    return 'ACCEPTED';
  }


  function capture_enabled() {
    return (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_CAPTURE')
            && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_CAPTURE') == 'True');
  }


  function captureCompleteOrder($oID, $order_id) {
    global $xtPrice;
    
    $order = new order($oID);
    $this->captureOrder($order->info['pp_total'], $order_id);
  }


  function captureOrder($amount, $order_id) {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->createCapture(array(
        'captured_amount' => $this->format_amount($amount),
      ));

      return TEXT_KLARNA_TRANSACTION_CAPTURE;
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      $_SESSION['klarna_error'] = $e->getMessage();
    }
  }


  function refundOrder($amount, $order_id, $description = '') {
    try {
      $management = new Klarna\Rest\OrderManagement\Order($this->connector, $order_id);
      $management->refund(array(
        'refunded_amount' => $this->format_amount($amount),
        'description' => $description,
      ));
      
      return TEXT_KLARNA_TRANSACTION_REFUND;
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      $_SESSION['klarna_error'] = $e->getMessage();
    }
  }


  function get_locale($country_iso) {
    // locales per purchase country as documented by Klarna
    $locales = array(
      'AU' => array('en'),
      'AT' => array('de', 'en'),
      'BE' => array('nl', 'fr', 'en'),
      'CA' => array('en', 'fr'),
      'CZ' => array('cs', 'en'),
      'DK' => array('da', 'en'),
      'FI' => array('fi', 'sv', 'en'),
      'FR' => array('fr', 'en'),
      'DE' => array('de', 'en'),
      'GR' => array('el', 'en'),
      'HU' => array('hu', 'en'),
      'IE' => array('en'),
      'IT' => array('it', 'en'),
      'MX' => array('en', 'es'),
      'NL' => array('nl', 'en'),
      'NZ' => array('en'),
      'NO' => array('nb', 'en'),
      'PL' => array('pl', 'en'),
      'PT' => array('pt', 'en'),
      'RO' => array('ro', 'en'),
      'SK' => array('sk', 'en'),
      'ES' => array('es', 'en'),
      'SE' => array('sv', 'en'),
      'CH' => array('de', 'fr', 'it', 'en'),
      'GB' => array('en'),
      'US' => array('en', 'es'),
    );

    $country_iso = strtoupper($country_iso);
    $language = strtolower($_SESSION['language_code']);
    if ($language == 'no') {
      $language = 'nb';
    }

    // an unsupported language falls back to English, then to the first locale of the country
    if (!isset($locales[$country_iso]) || in_array($language, $locales[$country_iso])) {
      return ((isset($locales[$country_iso])) ? $language : 'en').'-'.$country_iso;
    }
    return ((in_array('en', $locales[$country_iso])) ? 'en' : $locales[$country_iso][0]).'-'.$country_iso;
  }


  function getOrderData($minimal = false) {
    global $xtPrice, $product;
    
    $order = $this->get_order();
    
    $add_tax = false;
    if ($_SESSION['customers_status']['customers_status_add_tax_ot'] == '1'
        || ($_SESSION['customers_status']['customers_status_add_tax_ot'] == '0'
            && $_SESSION['customers_status']['customers_status_show_price_tax'] == '0'
            && $order->delivery['country_id'] == STORE_COUNTRY
            )
        )
    {
      $add_tax = true;
    }
        
    $i = 0;
    $tax_total = 0;
    $products_total = 0;
    $products_array = array();
    foreach ($order->products as $products) {
      $amount = $products['price'];
      if ($add_tax === true) {
        $amount = $this->xtcAddTax($amount, $products['tax']);
      }
      $type = $xtPrice->get_content_type_product($products['id']);
      
      $products_array[$i] = array(
        'type' => (($type == 'virtual') ? 'digital' : 'physical'),
        'reference' =>  encode_utf8((($products['model'] != '' && mb_strlen($products['model'], $_SESSION['language_charset']) <= 64) ? $products['model'] : (int)$products['id']), $_SESSION['language_charset'], true),
        'name' => encode_utf8(strip_tags($products['name']), $_SESSION['language_charset'], true),
        'quantity' => $products['qty'],
        'unit_price' => $this->format_amount($amount),
        'tax_rate' => $this->format_amount($products['tax']),
        'total_amount' => $this->format_amount($amount * $products['qty']),
        'total_tax_amount' => $this->format_amount($xtPrice->xtcGetTax(($amount * $products['qty']), $products['tax'])),
      );
      
      $products['image'] = xtc_get_products_image($products['id']);
      if ($products['image'] != '') {
        $image_url = $product->productImage($products['image'],'thumbnail');
        if ($image_url != '') {
          $products_array[$i]['image_url'] = $image_url;
        }
      }
      
      $tax_total += $products_array[$i]['total_tax_amount'];
      $products_total += $products_array[$i]['total_amount'];
      $i ++;
    }
    $tax_total_products = $tax_total;
    
    if (isset($_SESSION['shipping']) && $_SESSION['shipping'] !== false) {
      $shipping_method = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
      if ($shipping_method == 'free') {
        $tax_class_id = MODULE_ORDER_TOTAL_SHIPPING_TAX_CLASS;
      } else {
        $tax_class_id = constant('MODULE_SHIPPING_'.strtoupper($shipping_method).'_TAX_CLASS');
      }
      $tax = isset($xtPrice->TAX[$tax_class_id]) ? $xtPrice->TAX[$tax_class_id] : 0;
      $shipping_cost = ((isset($order->info['pp_shipping'])) ? $order->info['pp_shipping'] : $order->info['shipping_cost']);
      if ($add_tax === true) {
        $shipping_cost = $this->xtcAddTax($shipping_cost, $tax);
      }
      
      $products_array[$i] = array(
        'type' => 'shipping_fee',
        'reference' => $_SESSION['shipping']['id'],
        'name' => encode_utf8(strip_tags($order->info['shipping_method']), $_SESSION['language_charset'], true),
        'quantity' => 1,
        'unit_price' => $this->format_amount($shipping_cost),
        'tax_rate' => $this->format_amount($tax),
        'total_amount' => $this->format_amount($shipping_cost),
        'total_tax_amount' => $this->format_amount($xtPrice->xtcGetTax($shipping_cost, $tax)),
      );
      
      $tax_total += $products_array[$i]['total_tax_amount'];
      
      if (defined('MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER')
          && (int)MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER > (int)MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER
          )
      {
        $products_total += $products_array[$i]['total_amount'];
      }
      $i ++;
    }
        
    $order_amount = $order_tax_amount = 0;
    foreach ($order->totals as $total) {
      switch ($total['code']) {
        case 'ot_subtotal':
        case 'ot_subtotal_no_tax':
        case 'ot_shipping':
          break;
        
        case 'ot_tax':
          $order_tax_amount += $total['value'];
          break;
          
        case 'ot_total':
          $order_amount += $total['value'];
          break;

        default:              
          $tax_class_id = defined('MODULE_ORDER_TOTAL_'.strtoupper(substr($total['code'], 3)).'_TAX_CLASS') ? constant('MODULE_ORDER_TOTAL_'.strtoupper(substr($total['code'], 3)).'_TAX_CLASS') : 0;
          $tax = isset($xtPrice->TAX[$tax_class_id]) ? $xtPrice->TAX[$tax_class_id] : 0;
          $amount = $total['value'];
          if ($add_tax === true) {
            $amount = $this->xtcAddTax($amount, $tax);
          }
                    
          if ($total['code'] == 'ot_discount' && $products_total > 0) {          
            $tax = round(($tax_total_products / ($products_total - $tax_total_products)), 2) * 100;
          }
          
          $products_array[$i] = array(
            'type' => (($total['value'] > 0) ? 'surcharge' : 'discount'),
            'reference' => $total['code'],
            'name' => encode_utf8(strip_tags($total['title']), $_SESSION['language_charset'], true),
            'quantity' => 1,
            'unit_price' => $this->format_amount($amount),
            'tax_rate' => $this->format_amount(($tax != '') ? $tax : 0),
            'total_amount' => $this->format_amount($amount),
            'total_tax_amount' => $this->format_amount($xtPrice->xtcGetTax($amount, $tax)),
          );
          
          $tax_total += $products_array[$i]['total_tax_amount'];
          $i ++;
          break;
      }
    }
    
    $country = xtc_get_countriesList(STORE_COUNTRY);    
    
    // outside $_SESSION['klarna'], which is reset, so session and order get the same URL
    if (!isset($_SESSION['klarna_notify_token']) || !is_string($_SESSION['klarna_notify_token']) || $_SESSION['klarna_notify_token'] === '') {
      $_SESSION['klarna_notify_token'] = bin2hex(random_bytes(16));
    }
    
    // the billing country decides the market at Klarna, the store country is the fallback
    $purchase_country = strtoupper($country['countries_iso_code_2']);
    if (isset($order->billing['country_iso_2']) && $order->billing['country_iso_2'] != '') {
      $purchase_country = strtoupper($order->billing['country_iso_2']);
    } elseif (isset($order->billing['country']) && is_array($order->billing['country']) && !empty($order->billing['country']['iso_code_2'])) {
      $purchase_country = strtoupper($order->billing['country']['iso_code_2']);
    }

    $order_array = array(
      'locale' => $this->get_locale($purchase_country),
      'purchase_country' => $purchase_country,
      'purchase_currency' => $order->info['currency'],
      'order_amount' => $this->format_amount($order_amount),
      'order_tax_amount' => $this->format_amount($order_tax_amount),
      'merchant_reference1' => ((isset($_SESSION['customer_id'])) ? $_SESSION['customer_id'] : 0),
      'order_lines' => $products_array,
      'merchant_urls' => array(
        'notification' => xtc_href_link('callback/klarna/fraud_notification.php', 'token='.$_SESSION['klarna_notify_token'], 'SSL', false),
      ),
    );
    
    if ($order_array['order_tax_amount'] != $tax_total) {
      foreach ($order_array['order_lines'] as $k => $order_lines) {
        if ($order_lines['reference'] == 'ot_coupon') {
          $order_array['order_lines'][$k]['total_tax_amount'] = abs($order_array['order_tax_amount'] - $tax_total) * (-1);
          if ($add_tax === true) {
            $order_array['order_lines'][$k]['unit_price'] += $order_array['order_lines'][$k]['total_tax_amount'];
            $order_array['order_lines'][$k]['total_amount'] += $order_array['order_lines'][$k]['total_tax_amount'];
          }
          $order_array['order_lines'][$k]['tax_rate'] = $this->format_amount((($order_array['order_lines'][$k]['total_amount'] / ($order_array['order_lines'][$k]['total_amount'] - $order_array['order_lines'][$k]['total_tax_amount']) - 1)) * 100);
        }
        if ($order_lines['reference'] == 'ot_discount') {
          $order_array['order_lines'][$k]['total_tax_amount'] += ($order_array['order_tax_amount'] - $tax_total);
          if ($add_tax === true) {
             $order_array['order_lines'][$k]['unit_price'] += $order_array['order_lines'][$k]['total_tax_amount'];
             $order_array['order_lines'][$k]['total_amount'] += $order_array['order_lines'][$k]['total_tax_amount'];
          }
          $order_array['order_lines'][$k]['tax_rate'] = $this->format_amount((($order_array['order_lines'][$k]['total_amount'] / ($order_array['order_lines'][$k]['total_amount'] - $order_array['order_lines'][$k]['total_tax_amount']) - 1)) * 100);
        }
      }
    }

    if ($minimal === true) {
      //return $order_array;
    }

    $country_zones = array();
    if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED')
        && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED') != ''
        )
    {
      $countries_table = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED');
      $countries_table  = preg_replace("'[\r\n\s]+'",'',$countries_table);
      $country_zones = explode(",", $countries_table);
    }
    
    if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')
        && (int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE') > 0
        ) 
    {
      $countries_query = xtc_db_query("SELECT c.countries_iso_code_2 
                                         FROM ".TABLE_ZONES_TO_GEO_ZONES." gz
                                         JOIN ".TABLE_COUNTRIES." c
                                              ON c.countries_id = gz.zone_country_id
                                        WHERE gz.geo_zone_id = '".(int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')."'");
      while ($countries = xtc_db_fetch_array($countries_query)) {
        $country_zones[] = $countries['countries_iso_code_2'];
      }
    }
    $country_zones = array_unique($country_zones);
    
    if (count($country_zones) < 1) {
      $countries = xtc_get_countriesList();
      foreach ($countries as $country) {
        $country_zones[] = $country['countries_iso_code_2'];
      }
    }
    $order_array['billing_countries'] = $country_zones;
    $order_array['shipping_countries'] = $country_zones;
    
    $shipping_address = new stdclass();    
    if ($minimal === false) {
      $shipping_address->title = $this->parse_gender($_SESSION['language_code'], $order->delivery['gender']);
      $shipping_address->given_name = encode_utf8($order->delivery['firstname'], $_SESSION['language_charset'], true);
      $shipping_address->family_name = encode_utf8($order->delivery['lastname'], $_SESSION['language_charset'], true);
      $shipping_address->organization_name = encode_utf8($order->delivery['company'], $_SESSION['language_charset'], true);
      $shipping_address->street_address = encode_utf8($order->delivery['street_address'], $_SESSION['language_charset'], true);
      $shipping_address->street_address2 = (($order->delivery['suburb'] != '') ? encode_utf8($order->delivery['suburb'], $_SESSION['language_charset'], true) : NULL);
      $shipping_address->postal_code = $order->delivery['postcode'];
      $shipping_address->city = encode_utf8($order->delivery['city'], $_SESSION['language_charset'], true);
      $shipping_address->region = ((isset($order->delivery['state']) && $order->delivery['state'] != '') ? encode_utf8($order->delivery['state'], $_SESSION['language_charset'], true) : NULL);
      $shipping_address->email = $order->customer['email_address'];
      $shipping_address->phone = $order->customer['telephone'];
    }
    $shipping_address->country = ((isset($order->delivery['country_iso_2'])) ? $order->delivery['country_iso_2'] : $order->delivery['country']['iso_code_2']);
    $order_array['shipping_address'] = $shipping_address;
    
    $billing_address = new stdclass();
    if ($minimal === false) {
      $billing_address->title = $this->parse_gender($_SESSION['language_code'], $order->billing['gender']);
      $billing_address->given_name = encode_utf8($order->billing['firstname'], $_SESSION['language_charset'], true);
      $billing_address->family_name = encode_utf8($order->billing['lastname'], $_SESSION['language_charset'], true);
      $billing_address->organization_name = encode_utf8($order->billing['company'], $_SESSION['language_charset'], true);
      $billing_address->street_address = encode_utf8($order->billing['street_address'], $_SESSION['language_charset'], true);
      $billing_address->street_address2 = (($order->billing['suburb'] != '') ? encode_utf8($order->billing['suburb'], $_SESSION['language_charset'], true) : NULL);
      $billing_address->postal_code = $order->billing['postcode'];
      $billing_address->city = encode_utf8($order->billing['city'], $_SESSION['language_charset'], true);
      $billing_address->region = ((isset($order->billing['state']) && $order->billing['state'] != '') ? encode_utf8($order->billing['state'], $_SESSION['language_charset'], true) : NULL);
      $billing_address->email = $order->customer['email_address'];
      $billing_address->phone = $order->customer['telephone'];
    }
    $billing_address->country = ((isset($order->billing['country_iso_2'])) ? $order->billing['country_iso_2'] : $order->billing['country']['iso_code_2']);
    $order_array['billing_address'] = $billing_address;
        
    return $order_array;
  }

}