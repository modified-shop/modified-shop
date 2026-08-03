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

    const TRANSACTION_STATUS_PENDING = 'verification_pending';
    const TRANSACTION_STATUS_VERIFIED = 'verified';

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
    private $transaction_table_ready = false;
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
      $this->transaction_table_ready = $this->update() !== false;

      $credentials_valid = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID')
                           && trim((string)MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID) != ''
                           && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD')
                           && (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD != ''
                           && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD')
                           && (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD != '';
      $this->configuration_valid = $credentials_valid;

      if (defined('RUN_MODE_ADMIN')
          && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_ADMIN_CONFIGURATION')
          )
      {
        $this->description .= '<br><br>'.MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_ADMIN_CONFIGURATION;
      }

      if (defined('RUN_MODE_ADMIN')
          && $this->enabled
          && $credentials_valid === false
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
      if ($this->transaction_table_ready === false
          || $this->configuration_valid === false
          || $this->available === false
          )
      {
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

      $payment_state = self::get_order_payment_state($orders_id, $customers_id);
      if (is_array($payment_state)
          && $payment_state['transaction_status'] === self::TRANSACTION_STATUS_VERIFIED
          )
      {
        // Close the small window between the committed payment result and the
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

      if (is_array($payment_state)
          && $processing_is_authoritative
          && (int)$processing['orders_id'] === $orders_id
          && ($payment_state['transaction_status'] === self::TRANSACTION_STATUS_PENDING
              || ($payment_state['transaction_status'] === ''
                  && (int)$payment_state['orders_status'] === self::get_prepare_status()
                  && $processing['processing_status'] !== 'failed'))
          )
      {
        // Keep the checkout binding while the provider result is still
        // pending or a retry repairs a partial database update.
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
      $auth_mode = MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD == 'Pre-Authorization' ? 'E' : 'A';
      $test_mode = MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True' ? '100' : '0';
      $callback_url = $this->get_callback_url();
      $signature_fields = 'instId:amount:currency:cartId:authMode:testMode:MC_callback:M_auth_mode:M_test_mode';
      $signature = md5(
        MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD
        .':'.MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID
        .':'.$amount
        .':'.$currency
        .':'.$orders_id
        .':'.$auth_mode
        .':'.$test_mode
        .':'.$callback_url
        .':'.$auth_mode
        .':'.$test_mode
      );
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
        'authMode' => $auth_mode,
        'testMode' => $test_mode,
        'signatureFields' => $signature_fields,
        'signature' => $signature,
        'MC_callback' => $callback_url,
        'MC_returnurl' => $return_url,
        'M_sid' => $session_id,
        'M_cid' => $customers_id,
        'M_lang' => $language,
        'M_checkout_key' => $checkout_key,
        'M_checkout_token' => $checkout_token,
        'M_auth_mode' => $auth_mode,
        'M_test_mode' => $test_mode,
        'M_hash' => self::callback_hash($session_id, $customers_id, $orders_id, $checkout_key, $language, $amount, $currency, $checkout_token, $auth_mode, $test_mode),
      );

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
      global $messageStack;

      if (!self::ensure_transaction_table()) {
        if (isset($messageStack) && is_object($messageStack)) {
          $message = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_INSTALLATION_ERROR')
            ? MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_INSTALLATION_ERROR
            : 'The WorldPay transaction table could not be created.';
          $messageStack->add_session($message, 'error');
        }

        return false;
      }

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


    function update() {
      if ($this->check() < 1) {
        return false;
      }

      return self::ensure_transaction_table() ? '' : false;
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
      $provider_transaction_id = self::normalize_transaction_id($provider_transaction_id);
      if ($provider_transaction_id === false) {
        return false;
      }

      // Persist the authenticated provider result before changing the order.
      // This row is the durable retry journal even when shop tables use a
      // non-transactional storage engine.
      if (xtc_db_query('START TRANSACTION') === false) {
        return false;
      }

      $temporary_order = self::lock_callback_order($callback);
      if ($temporary_order === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      $payment_transaction = self::ensure_pending_transaction(
        $callback,
        $temporary_order,
        $provider_transaction_id
      );
      if ($payment_transaction === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      if (xtc_db_query('COMMIT') === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      if ($payment_transaction['transaction_status'] === self::TRANSACTION_STATUS_VERIFIED) {
        return array(
          'orders_id' => (int)$callback['orders_id'],
          'success' => true,
        );
      }

      if (xtc_db_query('START TRANSACTION') === false) {
        return false;
      }

      $temporary_order = self::lock_callback_order($callback);
      if ($temporary_order === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      $payment_transaction = self::get_payment_transaction((int)$callback['orders_id']);
      if (!is_array($payment_transaction)
          || !self::payment_transaction_matches(
                $payment_transaction,
                $callback,
                $provider_transaction_id
              )
          )
      {
        xtc_db_query('ROLLBACK');
        return false;
      }

      if ($payment_transaction['transaction_status'] === self::TRANSACTION_STATUS_VERIFIED) {
        if (xtc_db_query('COMMIT') === false) {
          xtc_db_query('ROLLBACK');
          return false;
        }

        return array(
          'orders_id' => (int)$callback['orders_id'],
          'success' => true,
        );
      }

      if ($payment_transaction['transaction_status'] !== self::TRANSACTION_STATUS_PENDING
          || self::complete_successful_callback(
               $callback,
               $temporary_order
             ) === false
          )
      {
        xtc_db_query('ROLLBACK');
        return false;
      }

      if (xtc_db_query('COMMIT') === false) {
        xtc_db_query('ROLLBACK');
        return false;
      }

      // Publish paid only after the order status and audit history are durable.
      if (!self::mark_transaction_verified($callback, $provider_transaction_id)) {
        return false;
      }

      return array(
        'orders_id' => (int)$callback['orders_id'],
        'success' => true,
      );
    }


    private static function complete_successful_callback($callback, $temporary_order) {
      $orders_id = (int)$callback['orders_id'];
      $success_status = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID')
                        && (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID > 0
        ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID
        : (int)DEFAULT_ORDERS_STATUS_ID;
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

      $test_mode = isset($callback['test_mode']) && $callback['test_mode'] === '100';
      $test_warning_exists = false;
      if ($test_mode
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
      if ($test_mode
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

      // A successful notification, including a retryable partial database
      // update, always wins over a later cancellation. Order history is not a
      // trust source; only the authenticated transaction row is authoritative.
      $payment_transaction = self::get_payment_transaction($orders_id);
      if ($payment_transaction === null) {
        xtc_db_query('ROLLBACK');
        return false;
      }
      if (is_array($payment_transaction)) {
        if (!self::payment_transaction_matches(
              $payment_transaction,
              $callback,
              $payment_transaction['transaction_id']
            )
            || ($payment_transaction['transaction_status'] !== self::TRANSACTION_STATUS_PENDING
                && $payment_transaction['transaction_status'] !== self::TRANSACTION_STATUS_VERIFIED)
            )
        {
          xtc_db_query('ROLLBACK');
          return false;
        }

        if ($payment_transaction['transaction_status'] === self::TRANSACTION_STATUS_PENDING
            && self::complete_successful_callback(
                 $callback,
                 $temporary_order
               ) === false
            )
        {
          xtc_db_query('ROLLBACK');
          return false;
        }

        if (xtc_db_query('COMMIT') === false) {
          xtc_db_query('ROLLBACK');
          return false;
        }

        if (!self::mark_transaction_verified(
              $callback,
              $payment_transaction['transaction_id']
            ))
        {
          return false;
        }

        return array(
          'orders_id' => $orders_id,
          'success' => true,
        );
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
             && isset(
                  $callback['orders_id'],
                  $callback['customers_id'],
                  $callback['amount'],
                  $callback['currency']
                )
             && is_scalar($callback['orders_id'])
             && is_scalar($callback['customers_id'])
             && is_scalar($callback['amount'])
             && is_scalar($callback['currency'])
             && (int)$callback['orders_id'] > 0
             && (int)$callback['customers_id'] > 0
             && is_numeric($callback['amount'])
             && (float)$callback['amount'] > 0
             && is_finite((float)$callback['amount'])
             && (float)$callback['amount'] <= 99999999999.9999
             && preg_match('/^[A-Z]{3}$/', (string)$callback['currency']);
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


    public static function ensure_transaction_table() {
      if (!defined('TABLE_WORLDPAY_JUNIOR_TRANSACTIONS')) {
        return false;
      }

      $table_pattern = addcslashes(TABLE_WORLDPAY_JUNIOR_TRANSACTIONS, '\\_%');
      $table_query = xtc_db_query("SHOW TABLES LIKE '".xtc_db_input($table_pattern)."'");
      if ($table_query === false) {
        return false;
      }

      return xtc_db_num_rows($table_query) === 1 || self::create_transaction_table();
    }


    private static function create_transaction_table() {
      return xtc_db_query("CREATE TABLE IF NOT EXISTS `".TABLE_WORLDPAY_JUNIOR_TRANSACTIONS."` (
                             `orders_id` INT(11) NOT NULL,
                             `transaction_id` VARBINARY(128) NOT NULL,
                             `transaction_status` VARCHAR(24) NOT NULL DEFAULT '".self::TRANSACTION_STATUS_PENDING."',
                             `amount` DECIMAL(15,4) NOT NULL,
                             `currency` CHAR(3) NOT NULL,
                             `date_added` DATETIME NOT NULL,
                             `last_modified` DATETIME NOT NULL,
                             PRIMARY KEY (`orders_id`),
                             UNIQUE KEY `idx_transaction_id` (`transaction_id`),
                             KEY `idx_transaction_status` (`transaction_status`, `last_modified`)
                           )") !== false;
    }


    private static function get_order_payment_state($orders_id, $customers_id) {
      if ($orders_id < 1 || $customers_id < 1) {
        return false;
      }

      $order_query = xtc_db_query("SELECT o.orders_id,
                                          o.orders_status,
                                          wjt.transaction_status
                                     FROM ".TABLE_ORDERS." o
                                LEFT JOIN ".TABLE_WORLDPAY_JUNIOR_TRANSACTIONS." wjt
                                       ON wjt.orders_id = o.orders_id
                                    WHERE o.orders_id = '".(int)$orders_id."'
                                      AND o.customers_id = '".(int)$customers_id."'
                                      AND (o.payment_class = ''
                                           OR o.payment_class IS NULL
                                           OR o.payment_class = 'worldpay_junior')
                                    LIMIT 1");
      if ($order_query === false) {
        return null;
      }
      if (xtc_db_num_rows($order_query) !== 1) {
        return false;
      }

      $payment_state = xtc_db_fetch_array($order_query);
      $payment_state['transaction_status'] = isset($payment_state['transaction_status'])
        ? (string)$payment_state['transaction_status']
        : '';

      return $payment_state;
    }


    private static function is_verified_order($orders_id, $customers_id) {
      $payment_state = self::get_order_payment_state($orders_id, $customers_id);

      return is_array($payment_state)
             && $payment_state['transaction_status'] === self::TRANSACTION_STATUS_VERIFIED;
    }


    private static function get_payment_transaction($orders_id) {
      $transaction_query = xtc_db_query("SELECT orders_id,
                                                transaction_id,
                                                transaction_status,
                                                amount,
                                                currency
                                           FROM ".TABLE_WORLDPAY_JUNIOR_TRANSACTIONS."
                                          WHERE orders_id = '".(int)$orders_id."'
                                          LIMIT 1");
      if ($transaction_query === false) {
        return null;
      }
      if (xtc_db_num_rows($transaction_query) !== 1) {
        return false;
      }

      return xtc_db_fetch_array($transaction_query);
    }


    private static function ensure_pending_transaction($callback, $temporary_order, $transaction_id) {
      $payment_transaction = self::get_payment_transaction((int)$callback['orders_id']);
      if ($payment_transaction === null) {
        return false;
      }
      if (is_array($payment_transaction)) {
        return self::payment_transaction_matches(
                 $payment_transaction,
                 $callback,
                 $transaction_id
               )
               && ($payment_transaction['transaction_status'] === self::TRANSACTION_STATUS_PENDING
                   || $payment_transaction['transaction_status'] === self::TRANSACTION_STATUS_VERIFIED)
          ? $payment_transaction
          : false;
      }

      if ((int)$temporary_order['orders_status'] !== self::get_prepare_status()) {
        return false;
      }

      $amount = self::normalize_amount($callback['amount']);
      $insert_result = xtc_db_query("INSERT INTO ".TABLE_WORLDPAY_JUNIOR_TRANSACTIONS."
                                                (orders_id,
                                                 transaction_id,
                                                 transaction_status,
                                                 amount,
                                                 currency,
                                                 date_added,
                                                 last_modified)
                                         VALUES ('".(int)$callback['orders_id']."',
                                                 '".xtc_db_input($transaction_id)."',
                                                 '".self::TRANSACTION_STATUS_PENDING."',
                                                 '".xtc_db_input($amount)."',
                                                 '".xtc_db_input($callback['currency'])."',
                                                 NOW(),
                                                 NOW())");
      if ($insert_result === false) {
        return false;
      }

      return array(
        'orders_id' => (int)$callback['orders_id'],
        'transaction_id' => $transaction_id,
        'transaction_status' => self::TRANSACTION_STATUS_PENDING,
        'amount' => $amount,
        'currency' => (string)$callback['currency'],
      );
    }


    private static function payment_transaction_matches($payment_transaction, $callback, $transaction_id) {
      return is_array($payment_transaction)
             && isset(
                  $payment_transaction['transaction_id'],
                  $payment_transaction['transaction_status'],
                  $payment_transaction['amount'],
                  $payment_transaction['currency']
                )
             && hash_equals((string)$payment_transaction['transaction_id'], (string)$transaction_id)
             && self::normalize_amount($payment_transaction['amount']) === self::normalize_amount($callback['amount'])
             && (string)$payment_transaction['currency'] === (string)$callback['currency'];
    }


    private static function mark_transaction_verified($callback, $transaction_id) {
      $amount = self::normalize_amount($callback['amount']);
      $update_result = xtc_db_query("UPDATE ".TABLE_WORLDPAY_JUNIOR_TRANSACTIONS."
                                       SET transaction_status = '".self::TRANSACTION_STATUS_VERIFIED."',
                                           last_modified = NOW()
                                     WHERE orders_id = '".(int)$callback['orders_id']."'
                                       AND transaction_id = '".xtc_db_input($transaction_id)."'
                                       AND transaction_status = '".self::TRANSACTION_STATUS_PENDING."'
                                       AND amount = '".xtc_db_input($amount)."'
                                       AND currency = '".xtc_db_input($callback['currency'])."'");
      if ($update_result === false) {
        return false;
      }
      if (xtc_db_affected_rows() === 1) {
        return true;
      }

      $payment_transaction = self::get_payment_transaction((int)$callback['orders_id']);

      return is_array($payment_transaction)
             && $payment_transaction['transaction_status'] === self::TRANSACTION_STATUS_VERIFIED
             && self::payment_transaction_matches(
                  $payment_transaction,
                  $callback,
                  $transaction_id
                );
    }


    private static function normalize_transaction_id($transaction_id) {
      if (!is_scalar($transaction_id)) {
        return false;
      }

      $transaction_id = trim((string)$transaction_id, ' ');

      return preg_match('/^[\x21-\x7e]{1,128}$/D', $transaction_id)
        ? $transaction_id
        : false;
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


    static function callback_hash($session_id, $customers_id, $orders_id, $checkout_key, $language, $amount, $currency, $checkout_token, $auth_mode = '', $test_mode = '') {
      $amount = self::normalize_amount($amount);
      $payload_fields = array(
        (string)$session_id,
        (int)$customers_id,
        (int)$orders_id,
        (string)$checkout_key,
        (string)$language,
        $amount,
        (string)$currency,
        (string)$checkout_token,
      );
      if ($auth_mode !== '' || $test_mode !== '') {
        $payload_fields[] = (string)$auth_mode;
        $payload_fields[] = (string)$test_mode;
      }
      $payload = implode("\n", $payload_fields);

      return hash_hmac('sha256', $payload, (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD);
    }


    static function validate_callback($post) {
      if (!defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD')
          || (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD == ''
          )
      {
        return false;
      }

      $callback_modes = self::get_callback_modes($post);
      if ($callback_modes === false) {
        return false;
      }

      if (!isset($post['M_checkout_key'])) {
        $data = self::validate_legacy_callback($post);
        return is_array($data) ? array_merge($data, $callback_modes) : false;
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
        'auth_mode' => $callback_modes['auth_mode'],
        'test_mode' => $callback_modes['test_mode'],
        'modes_bound' => $callback_modes['modes_bound'],
        'legacy' => false,
      );

      if (!preg_match('/^(?:[a-z0-9]{26}|[a-z0-9]{32}|[a-z0-9]{40}|[a-z0-9]{52})$/i', $data['session_id'])
          || $data['customers_id'] < 1
          || $data['orders_id'] < 1
          || !preg_match('/^[a-z0-9_-]{1,32}$/i', $data['language'])
          || !is_numeric($data['amount'])
          || (float)$data['amount'] <= 0
          || !is_finite((float)$data['amount'])
          || (float)$data['amount'] > 99999999999.9999
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
        $data['checkout_token'],
        $data['modes_bound'] ? $data['auth_mode'] : '',
        $data['modes_bound'] ? $data['test_mode'] : ''
      );
      if (!hash_equals($expected_hash, $data['hash'])) {
        return false;
      }

      return $data;
    }


    private static function get_callback_modes($post) {
      $has_transaction_auth_mode = array_key_exists('M_auth_mode', $post);
      $has_transaction_test_mode = array_key_exists('M_test_mode', $post);
      if ($has_transaction_auth_mode !== $has_transaction_test_mode) {
        return false;
      }

      if ($has_transaction_auth_mode) {
        if (!is_scalar($post['M_auth_mode']) || !is_scalar($post['M_test_mode'])) {
          return false;
        }
        $auth_mode = trim((string)$post['M_auth_mode']);
        $test_mode = trim((string)$post['M_test_mode']);
        if (!in_array($auth_mode, array('A', 'E'), true)
            || !in_array($test_mode, array('0', '100'), true)
            )
        {
          return false;
        }
      } else {
        $auth_mode = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD')
                     && MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD == 'Pre-Authorization'
          ? 'E'
          : 'A';
        $test_mode = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE')
                     && MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True'
          ? '100'
          : '0';
      }

      $allowed_auth_modes = $auth_mode === 'E' ? array('E', 'O') : array('A');
      if (array_key_exists('authMode', $post)
          && (!is_scalar($post['authMode'])
              || !in_array(trim((string)$post['authMode']), $allowed_auth_modes, true))
          )
      {
        return false;
      }

      if (array_key_exists('testMode', $post)
          && (!is_scalar($post['testMode']) || trim((string)$post['testMode']) !== $test_mode)
          )
      {
        return false;
      }

      return array(
        'auth_mode' => $auth_mode,
        'test_mode' => $test_mode,
        'modes_bound' => $has_transaction_auth_mode,
      );
    }


    private static function validate_legacy_callback($post) {
      $required = array('M_sid', 'M_cid', 'cartId', 'M_lang', 'amount', 'currency', 'M_hash');
      foreach ($required as $name) {
        if (!isset($post[$name]) || !is_scalar($post[$name])) {
          return false;
        }
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
          || !is_finite((float)$data['amount'])
          || (float)$data['amount'] > 99999999999.9999
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
