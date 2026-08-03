<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2008 osCommerce(worldpay_junior.php 1807 2008-01-13); www.oscommerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  class worldpay_junior {

    const VERIFICATION_PENDING_COMMENT = 'WorldPay: Transaction Verification Pending';
    const VERIFIED_COMMENT = 'WorldPay: Transaction Verified';

    var $code;
    var $title;
    var $info;
    var $description;
    var $sort_order;
    var $enabled;
    var $order_status;
    var $form_action_url;
    var $tmpOrders;
    var $tmpStatus;
    var $_check;
    var $signature;

    private $gateway_url = 'https://secure.wp3.rbsworldpay.com/wcc/purchase';
    private $configuration_valid = false;
    private $available = true;


    function __construct() {
      global $order;

      $this->signature = 'worldpay|worldpay_junior|1.1|2.2';
      $this->code = 'worldpay_junior';
      $this->title = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER') ? MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER : '';
      $this->enabled = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS') && MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS == 'True';

      $this->order_status = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID')
        ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID
        : 0;
      $this->tmpOrders = true;
      $this->tmpStatus = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID')
        ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID
        : (int)DEFAULT_ORDERS_STATUS_ID;
      $this->form_action_url = '';

      $this->configuration_valid = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID')
                                   && trim((string)MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID) != ''
                                   && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD')
                                   && (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD != ''
                                   && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD')
                                   && (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD != '';

      if (defined('RUN_MODE_ADMIN')
          && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_ADMIN_CONFIGURATION')
          )
      {
        $this->description .= '<br><br>'.MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_ADMIN_CONFIGURATION;
      }

      if (defined('RUN_MODE_ADMIN')
          && $this->enabled
          && $this->configuration_valid === false
          && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_CONFIGURATION_WARNING')
          )
      {
        $this->description .= '<div class="error_message">'.MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_CONFIGURATION_WARNING.'</div>';
      }

      if (!defined('RUN_MODE_ADMIN') && is_object($order)) {
        $this->update_status();
      }
    }


    function update_status() {
      global $order;

      if ($this->enabled == true && (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE > 0) {
        $check_flag = false;
        $check_query = xtc_db_query("SELECT zone_id
                                       FROM ".TABLE_ZONES_TO_GEO_ZONES."
                                      WHERE geo_zone_id = '".(int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE."'
                                        AND zone_country_id = '".(int)$order->billing['country']['id']."'
                                   ORDER BY zone_id");
        while ($check = xtc_db_fetch_array($check_query)) {
          if ((int)$check['zone_id'] < 1 || (int)$check['zone_id'] == (int)$order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->available = false;
        }
      }
    }


    function javascript_validation() {
      return false;
    }


    function selection() {
      if ($this->configuration_valid === false || $this->available === false) {
        return false;
      }

      return array(
        'id' => $this->code,
        'module' => $this->title,
        'description' => $this->description,
      );
    }


    function pre_confirmation_check() {
      return false;
    }


    function confirmation() {
      return false;
    }


    function process_button() {
      return false;
    }


    function before_process() {
      global $checkout, $order;

      if ($this->configuration_valid === false || $this->available === false) {
        if (is_object($checkout)) {
          $checkout->fail();
        }
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      if (isset($_SESSION['tmp_oID']) && is_numeric($_SESSION['tmp_oID'])) {
        $orders_id = (int)$_SESSION['tmp_oID'];
        if (!self::is_verified_order($orders_id, (int)$_SESSION['customer_id'])) {
          if (is_object($checkout)) {
            $checkout->fail();
          }
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
        }

        $order->info['order_status'] = $this->get_success_status();
      }

      return false;
    }


    function before_checkout_claim($checkout) {
      $customers_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;
      if (!is_object($checkout) || $customers_id < 1) {
        return false;
      }

      $session_orders_id = isset($_SESSION['tmp_oID']) && is_numeric($_SESSION['tmp_oID'])
        ? (int)$_SESSION['tmp_oID']
        : 0;
      $processing = $checkout->find();
      $processing_is_authoritative = is_array($processing)
                                     && array_key_exists('orders_id', $processing)
                                     && isset($processing['processing_status'])
                                     && in_array(
                                          $processing['processing_status'],
                                          array('processing', 'waiting', 'ready', 'failed'),
                                          true
                                        );
      if ($processing_is_authoritative) {
        $orders_id = (int)$processing['orders_id'];
        if ($orders_id > 0) {
          $_SESSION['tmp_oID'] = $orders_id;
        } else {
          unset($_SESSION['tmp_oID']);
        }
      } else {
        $orders_id = $session_orders_id;
      }

      // There is no previous temporary order during the initial checkout.
      if ($orders_id < 1) {
        return false;
      }

      $resume_token = isset($_SESSION['checkout_processing_phase_token'])
                      && is_string($_SESSION['checkout_processing_phase_token'])
                      && preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_phase_token'])
        ? $_SESSION['checkout_processing_phase_token']
        : '';
      $language = isset($_SESSION['language']) ? (string)$_SESSION['language'] : 'english';

      if (self::is_verified_order($orders_id, $customers_id)) {
        // Close the small window between the committed payment history and the
        // callback publishing the waiting checkout as ready.
        $payment_ready = checkout::mark_payment_ready($checkout->get_key(), $customers_id, $orders_id);
        if (!$payment_ready
            && is_array($processing)
            && isset($processing['orders_id'])
            && (int)$processing['orders_id'] === $orders_id
            )
        {
          $processing_url = $checkout->get_processing_url($language, $resume_token);
          session_write_close();
          xtc_redirect($processing_url);
        }

        // A verified legacy order without a checkout-processing row may still
        // use the normal claim path.
        return false;
      }

      $verification_pending = self::order_history_exists(
        $orders_id,
        self::VERIFICATION_PENDING_COMMENT
      );
      if (($verification_pending === true || $verification_pending === null)
          && $processing_is_authoritative
          && (int)$processing['orders_id'] === $orders_id
          )
      {
        // Keep the checkout binding while a retry repairs a MyISAM partial
        // commit between the provider status update and Verified history.
        $processing_url = $checkout->get_processing_url($language, $resume_token);
        session_write_close();
        xtc_redirect($processing_url);
      }

      if (self::is_temporary_order($orders_id, $customers_id)) {
        $processing_url = $checkout->get_processing_url($language, $resume_token);
        session_write_close();
        xtc_redirect($processing_url);
      }

      unset($_SESSION['tmp_oID']);
      $checkout->fail();
      $checkout->create_key();
      $error_url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL');
      session_write_close();
      xtc_redirect($error_url);
    }


    function before_create_order() {
      return false;
    }


    function payment_action() {
      global $checkout, $insert_id, $order;

      $orders_id = (int)$insert_id;
      $checkout_key = is_object($checkout) ? $checkout->get_key() : '';
      if ($orders_id < 1
          || !preg_match('/^[a-f0-9]{64}$/', $checkout_key)
          || !is_object($checkout)
          )
      {
        if (is_object($checkout)) {
          $checkout->fail();
        }
        throw new RuntimeException('Unable to prepare WorldPay temporary order');
      }

      $checkout_token = isset($_SESSION['checkout_processing_phase_token'])
                        && is_string($_SESSION['checkout_processing_phase_token'])
        ? $_SESSION['checkout_processing_phase_token']
        : '';
      if (!preg_match('/^[a-f0-9]{64}$/', $checkout_token)) {
        $checkout->fail();
        throw new RuntimeException('Unable to prepare WorldPay checkout return');
      }

      $amount = $this->format_raw($order->info['total']);
      $currency = (string)$_SESSION['currency'];
      $session_id = xtc_session_id();
      $customers_id = (int)$_SESSION['customer_id'];
      $language = (string)$_SESSION['language'];
      $return_url = decode_htmlentities(xtc_href_link(
        FILENAME_CHECKOUT_PROCESS,
        xtc_session_name().'='.rawurlencode($session_id)
          .'&checkout_token='.rawurlencode($checkout_token),
        'SSL',
        false,
        false
      ));
      $fields = array(
        'instId' => MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID,
        'amount' => $amount,
        'currency' => $currency,
        'hideCurrency' => 'true',
        'cartId' => $orders_id,
        'desc' => STORE_NAME,
        'name' => $order->billing['firstname'].' '.$order->billing['lastname'],
        'address' => $order->billing['street_address'],
        'postcode' => $order->billing['postcode'],
        'country' => $order->billing['country']['iso_code_2'],
        'tel' => $order->customer['telephone'],
        'email' => $order->customer['email_address'],
        'fixContact' => 'Y',
        'lang' => $this->get_worldpay_language(),
        'signatureFields' => 'amount:currency:cartId',
        'signature' => md5(MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD.':'.$amount.':'.$currency.':'.$orders_id),
        'MC_callback' => $this->get_callback_url(),
        'MC_returnurl' => $return_url,
        'M_sid' => $session_id,
        'M_cid' => $customers_id,
        'M_lang' => $language,
        'M_checkout_key' => $checkout_key,
        'M_checkout_token' => $checkout_token,
        'M_hash' => self::callback_hash($session_id, $customers_id, $orders_id, $checkout_key, $language, $amount, $currency, $checkout_token),
      );

      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD == 'Pre-Authorization') {
        $fields['authMode'] = 'E';
      }
      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True') {
        $fields['testMode'] = '100';
      }

      if (!$checkout->wait_for_payment($orders_id)) {
        $checkout->fail();
        throw new RuntimeException('Unable to wait for the WorldPay payment result');
      }

      $this->output_redirect_form($fields);
    }


    function before_send_order() {
      global $insert_id;

      if (!self::is_verified_order((int)$insert_id, (int)$_SESSION['customer_id'])) {
        throw new RuntimeException('WorldPay temporary order is not verified');
      }

      return false;
    }


    function after_process() {
      return false;
    }


    function get_error() {
      return array('error' => MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_UNSUCCESSFUL_TRANSACTION);
    }


    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value
                                         FROM ".TABLE_CONFIGURATION."
                                        WHERE configuration_key = 'MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }

      return $this->_check;
    }


    function install() {
      $check_query = xtc_db_query("SELECT orders_status_id
                                     FROM ".TABLE_ORDERS_STATUS."
                                    WHERE orders_status_name = 'Preparing [WorldPay]'
                                    LIMIT 1");
      if (xtc_db_num_rows($check_query) < 1) {
        $status_query = xtc_db_query("SELECT MAX(orders_status_id) AS status_id
                                        FROM ".TABLE_ORDERS_STATUS);
        $status = xtc_db_fetch_array($status_query);
        $status_id = (int)$status['status_id'] + 1;

        $languages = xtc_get_languages();
        foreach ($languages as $language) {
          xtc_db_query("INSERT INTO ".TABLE_ORDERS_STATUS."
                                   (orders_status_id, language_id, orders_status_name)
                            VALUES ('".$status_id."', '".(int)$language['id']."', 'Preparing [WorldPay]')");
        }

        $flags_query = xtc_db_query("DESCRIBE ".TABLE_ORDERS_STATUS." public_flag");
        if (xtc_db_num_rows($flags_query) === 1) {
          xtc_db_query("UPDATE ".TABLE_ORDERS_STATUS."
                           SET public_flag = 0,
                               downloads_flag = 0
                         WHERE orders_status_id = '".$status_id."'");
        }
      } else {
        $status = xtc_db_fetch_array($check_query);
        $status_id = (int)$status['orders_status_id'];
      }

      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED', '', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID', '', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD', '', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD', '', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD', 'Capture', '6', '0', 'xtc_cfg_select_option(array(\'Pre-Authorization\', \'Capture\'), ', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID', '".$status_id."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
    }


    function remove() {
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION."
                          WHERE configuration_key IN ('".implode("', '", $this->keys())."')");
    }


    function keys() {
      return array(
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID',
        'MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER',
      );
    }


    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $xtPrice;

      if (empty($currency_code) || !isset($xtPrice->currencies[$currency_code])) {
        $currency_code = $_SESSION['currency'];
      }
      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $xtPrice->currencies[$currency_code]['value'];
      }

      return number_format(
        xtc_round($number * $currency_value, $xtPrice->currencies[$currency_code]['decimal_places']),
        $xtPrice->currencies[$currency_code]['decimal_places'],
        '.',
        ''
      );
    }


    static function process_callback($callback, $success, $provider_transaction_id = '') {
      if (!self::callback_can_be_processed($callback)) {
        return false;
      }

      $callback_lock = self::acquire_callback_lock((int)$callback['orders_id']);
      if ($callback_lock === false) {
        return false;
      }

      try {
        return $success === false
          ? self::process_cancellation($callback)
          : self::process_successful_callback($callback, $provider_transaction_id);
      } finally {
        self::release_callback_lock($callback_lock);
      }
    }


    private static function process_successful_callback($callback, $provider_transaction_id) {
      if (xtc_db_query('START TRANSACTION') === false) {
        return false;
      }

      $temporary_order = self::lock_callback_order($callback);
      if ($temporary_order === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      $result = self::complete_successful_callback(
        $callback,
        $temporary_order,
        $provider_transaction_id
      );
      if ($result === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      if (xtc_db_query('COMMIT') === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      return $result;
    }


    private static function complete_successful_callback($callback, $temporary_order, $provider_transaction_id) {
      $orders_id = (int)$callback['orders_id'];
      $verified = self::order_history_exists($orders_id, self::VERIFIED_COMMENT);
      $verification_pending = self::order_history_exists(
        $orders_id,
        self::VERIFICATION_PENDING_COMMENT
      );
      if ($verified === null || $verification_pending === null) {
        return false;
      }

      if ($verified === false && $verification_pending === false) {
        if ((int)$temporary_order['orders_status'] !== self::get_prepare_status()
            || self::add_order_history(
                 $orders_id,
                 (int)$temporary_order['orders_status'],
                 self::VERIFICATION_PENDING_COMMENT
               ) === false
            )
        {
          return false;
        }
      }

      $success_status = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID')
                        && (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID > 0
        ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID
        : (int)DEFAULT_ORDERS_STATUS_ID;
      if ($verified === false) {
        // The pending history entry is a durable retry journal for MyISAM. If
        // this update or the final history insert fails, the next Y or C
        // notification resumes here instead of rejecting the paid order.
        if ((int)$temporary_order['orders_status'] === self::get_prepare_status()) {
          $update_result = xtc_db_query("UPDATE ".TABLE_ORDERS."
                                           SET orders_status = '".$success_status."',
                                               last_modified = NOW()
                                         WHERE orders_id = '".$orders_id."'
                                           AND customers_id = '".(int)$callback['customers_id']."'");
          // MySQL may report zero changed rows when both configured statuses
          // are identical, so only a query error is a failure here.
          if ($update_result === false) {
            return false;
          }
        }
      }

      $provider_transaction_id = substr(trim((string)$provider_transaction_id), 0, 128);
      if ($provider_transaction_id !== '') {
        $transaction_comment = 'WorldPay transaction ID: '.$provider_transaction_id;
        $transaction_exists = self::order_history_exists($orders_id, $transaction_comment);
        if ($transaction_exists === null) {
          return false;
        }
        if ($transaction_exists === false) {
          if (self::add_order_history($orders_id, $success_status, $transaction_comment) === false) {
            return false;
          }
        }
      }

      $test_warning_exists = false;
      if (defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE')
          && MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True'
          && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE')
          )
      {
        $test_warning_exists = self::order_history_exists(
          $orders_id,
          MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE
        );
        if ($test_warning_exists === null) {
          return false;
        }
      }
      if (defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE')
          && MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True'
          && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE')
          && $test_warning_exists === false
          )
      {
        if (self::add_order_history(
              $orders_id,
              $success_status,
              MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE
            ) === false)
        {
          return false;
        }
      }

      // Publish the verified marker last. Browser checkout recovery only
      // treats the provider result as complete after the status and all audit
      // history available in this notification have been persisted.
      if ($verified === false
          && self::add_order_history($orders_id, $success_status, self::VERIFIED_COMMENT) === false
          )
      {
        return false;
      }

      return array(
        'orders_id' => $orders_id,
        'success' => true,
      );
    }


    static function cancel_temporary_order($callback) {
      if (!self::callback_can_be_processed($callback)) {
        return false;
      }

      $callback_lock = self::acquire_callback_lock((int)$callback['orders_id']);
      if ($callback_lock === false) {
        return false;
      }

      try {
        $result = self::process_cancellation($callback);
      } finally {
        self::release_callback_lock($callback_lock);
      }

      return is_array($result) && $result['success'] === false;
    }


    private static function process_cancellation($callback) {
      if (!self::callback_can_be_processed($callback)
          || xtc_db_query('START TRANSACTION') === false
          )
      {
        return false;
      }

      $orders_id = (int)$callback['orders_id'];
      $temporary_order = self::lock_callback_order($callback);
      if ($temporary_order === false) {
        $already_cancelled = self::checkout_cancellation_exists($callback)
                             || (!empty($callback['legacy'])
                                 && self::callback_order_is_missing($orders_id));
        if (xtc_db_query('COMMIT') === false) {
          xtc_db_query('ROLLBACK');
          return false;
        }

        return $already_cancelled
          ? array('orders_id' => $orders_id, 'success' => false)
          : false;
      }

      // A successful notification, including a retryable MyISAM partial
      // commit, always wins over a later cancellation.
      $verified = self::order_history_exists($orders_id, self::VERIFIED_COMMENT);
      $verification_pending = self::order_history_exists(
        $orders_id,
        self::VERIFICATION_PENDING_COMMENT
      );
      if ($verified === null || $verification_pending === null) {
        xtc_db_query('ROLLBACK');
        return false;
      }
      if ($verified === true || $verification_pending === true) {
        $result = self::complete_successful_callback($callback, $temporary_order, '');
        if ($result === false) {
          xtc_db_query('ROLLBACK');
          return false;
        }

        if (xtc_db_query('COMMIT') === false) {
          xtc_db_query('ROLLBACK');
          return false;
        }

        return $result;
      }

      if ((int)$temporary_order['orders_status'] !== self::get_prepare_status()) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      if (empty($callback['legacy'])) {
        if (!isset($callback['checkout_key'])
            || !preg_match('/^[a-f0-9]{64}$/', (string)$callback['checkout_key'])
            )
        {
          xtc_db_query('ROLLBACK');
          return false;
        }

        $update_result = xtc_db_query("UPDATE ".TABLE_CHECKOUT_PROCESSING."
                                         SET processing_status = 'failed',
                                             last_modified = NOW()
                                       WHERE checkout_key = '".xtc_db_input($callback['checkout_key'])."'
                                         AND customers_id = '".(int)$callback['customers_id']."'
                                         AND orders_id = '".$orders_id."'
                                         AND processing_status = 'waiting'");
        if ($update_result === false) {
          xtc_db_query('ROLLBACK');
          return false;
        }
        if (xtc_db_affected_rows() !== 1 && !self::checkout_cancellation_exists($callback)) {
          xtc_db_query('ROLLBACK');
          return false;
        }
      }

      require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
      xtc_remove_order(
        $orders_id,
        defined('STOCK_LIMITED') && STOCK_LIMITED == 'true' ? 'on' : false,
        defined('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS') && STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS == 'true'
      );
      if (!self::callback_order_is_missing($orders_id)) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      if (xtc_db_query('COMMIT') === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      return array(
        'orders_id' => $orders_id,
        'success' => false,
      );
    }


    private static function callback_can_be_processed($callback) {
      return is_array($callback)
             && isset($callback['orders_id'], $callback['customers_id'], $callback['currency'])
             && (int)$callback['orders_id'] > 0
             && (int)$callback['customers_id'] > 0;
    }


    private static function acquire_callback_lock($orders_id) {
      $database = defined('DB_DATABASE') ? (string)DB_DATABASE : '';
      $lock_scope = $database.':'.TABLE_ORDERS;
      $lock_name = 'mshop_wp_'.substr(hash('sha256', $lock_scope), 0, 16).'_'.(int)$orders_id;
      $lock_query = xtc_db_query("SELECT GET_LOCK('".xtc_db_input($lock_name)."', 10) AS callback_lock");
      if ($lock_query === false || xtc_db_num_rows($lock_query) !== 1) {
        return false;
      }

      $lock = xtc_db_fetch_array($lock_query);
      return isset($lock['callback_lock']) && (int)$lock['callback_lock'] === 1
        ? $lock_name
        : false;
    }


    private static function release_callback_lock($lock_name) {
      xtc_db_query("SELECT RELEASE_LOCK('".xtc_db_input($lock_name)."')");
    }


    private static function lock_callback_order($callback) {
      $payment_condition = !empty($callback['legacy'])
        ? "(payment_class = '' OR payment_class IS NULL OR payment_class = 'worldpay_junior')"
        : "payment_class = 'worldpay_junior'";
      $order_query = xtc_db_query("SELECT orders_id,
                                          orders_status
                                     FROM ".TABLE_ORDERS."
                                    WHERE orders_id = '".(int)$callback['orders_id']."'
                                      AND customers_id = '".(int)$callback['customers_id']."'
                                      AND currency = '".xtc_db_input($callback['currency'])."'
                                      AND ".$payment_condition."
                                    LIMIT 1
                                   FOR UPDATE");
      if ($order_query === false || xtc_db_num_rows($order_query) !== 1) {
        return false;
      }

      return xtc_db_fetch_array($order_query);
    }


    private static function callback_order_is_missing($orders_id) {
      $order_query = xtc_db_query("SELECT orders_id
                                     FROM ".TABLE_ORDERS."
                                    WHERE orders_id = '".(int)$orders_id."'
                                    LIMIT 1");

      return $order_query !== false && xtc_db_num_rows($order_query) === 0;
    }


    private static function checkout_cancellation_exists($callback) {
      if (!empty($callback['legacy'])
          || !isset($callback['checkout_key'])
          || !preg_match('/^[a-f0-9]{64}$/', (string)$callback['checkout_key'])
          )
      {
        return false;
      }

      $processing_query = xtc_db_query("SELECT checkout_key
                                          FROM ".TABLE_CHECKOUT_PROCESSING."
                                         WHERE checkout_key = '".xtc_db_input($callback['checkout_key'])."'
                                           AND customers_id = '".(int)$callback['customers_id']."'
                                           AND orders_id = '".(int)$callback['orders_id']."'
                                           AND processing_status = 'failed'
                                         LIMIT 1");

      return $processing_query !== false && xtc_db_num_rows($processing_query) === 1;
    }


    private static function is_temporary_order($orders_id, $customers_id) {
      if ($orders_id < 1 || $customers_id < 1) {
        return false;
      }

      $order_query = xtc_db_query("SELECT orders_id
                                     FROM ".TABLE_ORDERS."
                                    WHERE orders_id = '".(int)$orders_id."'
                                      AND customers_id = '".(int)$customers_id."'
                                      AND orders_status = '".self::get_prepare_status()."'
                                      AND (payment_class = ''
                                           OR payment_class IS NULL
                                           OR payment_class = 'worldpay_junior')
                                    LIMIT 1");

      return xtc_db_num_rows($order_query) === 1;
    }


    private static function is_verified_order($orders_id, $customers_id) {
      if ($orders_id < 1 || $customers_id < 1) {
        return false;
      }

      $order_query = xtc_db_query("SELECT o.orders_id
                                     FROM ".TABLE_ORDERS." o
                                    WHERE o.orders_id = '".(int)$orders_id."'
                                      AND o.customers_id = '".(int)$customers_id."'
                                      AND (o.payment_class = ''
                                           OR o.payment_class IS NULL
                                           OR o.payment_class = 'worldpay_junior')
                                      AND EXISTS (
                                            SELECT 1
                                              FROM ".TABLE_ORDERS_STATUS_HISTORY." osh
                                             WHERE osh.orders_id = o.orders_id
                                               AND osh.comments = '".xtc_db_input(self::VERIFIED_COMMENT)."'
                                          )
                                    LIMIT 1");

      return xtc_db_num_rows($order_query) === 1;
    }


    private static function add_order_history($orders_id, $orders_status_id, $comments) {
      $sql_data_array = array(
        'orders_id' => (int)$orders_id,
        'orders_status_id' => (int)$orders_status_id,
        'date_added' => 'now()',
        'customer_notified' => '0',
        'comments' => $comments,
      );
      return xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
    }


    private static function order_history_exists($orders_id, $comments) {
      $history_query = xtc_db_query("SELECT orders_status_history_id
                                       FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                      WHERE orders_id = '".(int)$orders_id."'
                                        AND comments = '".xtc_db_input($comments)."'
                                      LIMIT 1");

      if ($history_query === false) {
        return null;
      }

      return xtc_db_num_rows($history_query) === 1;
    }


    private static function get_prepare_status() {
      return defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID')
        ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID
        : 0;
    }


    static function callback_hash($session_id, $customers_id, $orders_id, $checkout_key, $language, $amount, $currency, $checkout_token) {
      $amount = self::normalize_amount($amount);
      $payload = implode("\n", array(
        (string)$session_id,
        (int)$customers_id,
        (int)$orders_id,
        (string)$checkout_key,
        (string)$language,
        $amount,
        (string)$currency,
        (string)$checkout_token,
      ));

      return hash_hmac('sha256', $payload, (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD);
    }


    static function validate_callback($post) {
      if (!defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD')
          || (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD == ''
          )
      {
        return false;
      }

      if (!isset($post['M_checkout_key'])) {
        return self::validate_legacy_callback($post);
      }

      if (!defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD')
          || (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD == ''
          || !isset($post['callbackPW'])
          || !is_scalar($post['callbackPW'])
          || !hash_equals((string)MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD, (string)$post['callbackPW'])
          )
      {
        return false;
      }

      $required = array(
        'M_sid',
        'M_cid',
        'cartId',
        'M_lang',
        'amount',
        'currency',
        'M_checkout_key',
        'M_checkout_token',
        'M_hash',
      );
      foreach ($required as $name) {
        if (!isset($post[$name]) || !is_scalar($post[$name])) {
          return false;
        }
      }

      $data = array(
        'session_id' => trim((string)$post['M_sid']),
        'customers_id' => (int)$post['M_cid'],
        'orders_id' => (int)$post['cartId'],
        'language' => basename(trim((string)$post['M_lang'])),
        'amount' => trim((string)$post['amount']),
        'currency' => strtoupper(trim((string)$post['currency'])),
        'checkout_key' => trim((string)$post['M_checkout_key']),
        'checkout_token' => trim((string)$post['M_checkout_token']),
        'hash' => trim((string)$post['M_hash']),
        'legacy' => false,
      );

      if (!preg_match('/^(?:[a-z0-9]{26}|[a-z0-9]{32}|[a-z0-9]{40}|[a-z0-9]{52})$/i', $data['session_id'])
          || $data['customers_id'] < 1
          || $data['orders_id'] < 1
          || !preg_match('/^[a-z0-9_-]{1,32}$/i', $data['language'])
          || !is_numeric($data['amount'])
          || (float)$data['amount'] <= 0
          || !preg_match('/^[A-Z]{3}$/', $data['currency'])
          || !preg_match('/^[a-f0-9]{64}$/', $data['checkout_key'])
          || !preg_match('/^[a-f0-9]{64}$/', $data['checkout_token'])
          || !preg_match('/^[a-f0-9]{64}$/', $data['hash'])
          )
      {
        return false;
      }

      $data['amount'] = self::normalize_amount($data['amount']);
      $expected_hash = self::callback_hash(
        $data['session_id'],
        $data['customers_id'],
        $data['orders_id'],
        $data['checkout_key'],
        $data['language'],
        $data['amount'],
        $data['currency'],
        $data['checkout_token']
      );
      if (!hash_equals($expected_hash, $data['hash'])) {
        return false;
      }

      return $data;
    }


    private static function validate_legacy_callback($post) {
      $required = array('M_sid', 'M_cid', 'cartId', 'M_lang', 'amount', 'currency', 'M_hash');
      foreach ($required as $name) {
        if (!isset($post[$name]) || !is_scalar($post[$name])) {
          return false;
        }
      }

      if (defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD')
          && (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD != ''
          && (!isset($post['callbackPW'])
              || !is_scalar($post['callbackPW'])
              || !hash_equals((string)MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD, (string)$post['callbackPW']))
          )
      {
        return false;
      }

      $data = array(
        'session_id' => trim((string)$post['M_sid']),
        'customers_id' => (int)$post['M_cid'],
        'orders_id' => (int)$post['cartId'],
        'language' => basename(trim((string)$post['M_lang'])),
        'amount' => trim((string)$post['amount']),
        'currency' => strtoupper(trim((string)$post['currency'])),
        'hash' => strtolower(trim((string)$post['M_hash'])),
        'legacy' => true,
      );
      if (!preg_match('/^(?:[a-z0-9]{26}|[a-z0-9]{32}|[a-z0-9]{40}|[a-z0-9]{52})$/i', $data['session_id'])
          || $data['customers_id'] < 1
          || $data['orders_id'] < 1
          || !preg_match('/^[a-z0-9_-]{1,32}$/i', $data['language'])
          || !is_numeric($data['amount'])
          || (float)$data['amount'] <= 0
          || !preg_match('/^[A-Z]{3}$/', $data['currency'])
          || !preg_match('/^[a-f0-9]{32}$/', $data['hash'])
          )
      {
        return false;
      }

      $expected_hash = md5(
        $data['session_id']
        .$data['customers_id']
        .$data['orders_id']
        .$data['language']
        .number_format((float)$data['amount'], 2)
        .MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD
      );
      if (!hash_equals($expected_hash, $data['hash'])) {
        return false;
      }

      $data['amount'] = self::normalize_amount($data['amount']);
      return $data;
    }


    private static function normalize_amount($amount) {
      return number_format((float)$amount, 4, '.', '');
    }


    private function get_worldpay_language() {
      $language_query = xtc_db_query("SELECT code
                                        FROM ".TABLE_LANGUAGES."
                                       WHERE languages_id = '".(int)$_SESSION['languages_id']."'");
      if (xtc_db_num_rows($language_query) > 0) {
        $language = xtc_db_fetch_array($language_query);
        if (isset($language['code']) && is_scalar($language['code'])) {
          return strtoupper((string)$language['code']);
        }
      }

      return 'EN';
    }


    private function get_callback_url() {
      $url = xtc_href_link('callback/worldpay/junior_callback.php', '', 'SSL', false, false);
      $url = decode_htmlentities($url);
      $scheme_position = strpos($url, '://');

      return $scheme_position === false ? $url : substr($url, $scheme_position + 3);
    }


    private function output_redirect_form($fields) {
      $charset = isset($_SESSION['language_charset']) ? $_SESSION['language_charset'] : 'UTF-8';

      session_write_close();

      echo '<!DOCTYPE html>';
      echo '<html><head><meta charset="'.htmlspecialchars($charset, ENT_QUOTES, $charset).'">';
      echo '<title>'.htmlspecialchars($this->title, ENT_QUOTES, $charset).'</title></head><body>';
      echo '<form id="worldpay_checkout" action="'.htmlspecialchars($this->gateway_url, ENT_QUOTES, $charset).'" method="post">';
      foreach ($fields as $name => $value) {
        echo '<input type="hidden" name="'.htmlspecialchars($name, ENT_QUOTES, $charset).'" value="'.htmlspecialchars((string)$value, ENT_QUOTES, $charset).'">';
      }
      echo '<noscript><button type="submit">'.htmlspecialchars(sprintf(MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_CONTINUE_BUTTON, STORE_NAME), ENT_QUOTES, $charset).'</button></noscript>';
      echo '</form><script>document.getElementById("worldpay_checkout").submit();</script></body></html>';
      exit;
    }


    private function get_success_status() {
      return $this->order_status > 0 ? $this->order_status : (int)DEFAULT_ORDERS_STATUS_ID;
    }
  }
