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

    const TRANSACTION_TABLE = 'worldpay_junior_transactions';
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
    private $verified_transaction_id = 0;


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
      $this->tmpOrders = false;
      $this->tmpStatus = 0;
      $this->form_action_url = '';

      $this->configuration_valid = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID')
                                   && trim((string)MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID) != ''
                                   && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD')
                                   && (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD != ''
                                   && defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD')
                                   && (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD != '';

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
      global $checkout;

      if ($this->configuration_valid === false || $this->available === false) {
        if (is_object($checkout)) {
          $checkout->fail();
        }
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      return false;
    }


    function before_create_order() {
      global $checkout, $order;

      $checkout_token = isset($_SESSION['checkout_processing_phase_token'])
                        && is_string($_SESSION['checkout_processing_phase_token'])
        ? $_SESSION['checkout_processing_phase_token']
        : '';
      $checkout_key = is_object($checkout) ? $checkout->get_key() : '';
      if (!preg_match('/^[a-f0-9]{64}$/', $checkout_key)
          || !preg_match('/^[a-f0-9]{64}$/', $checkout_token)
          )
      {
        if (is_object($checkout)) {
          $checkout->fail();
        }
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      $amount = $this->format_raw($order->info['total']);
      $currency = (string)$_SESSION['currency'];
      $session_id = xtc_session_id();
      $customers_id = (int)$_SESSION['customer_id'];
      $language = (string)$_SESSION['language'];
      $request_token = self::get_request_checkout_token();
      $transaction = self::find_verified_transaction(
        $checkout_key,
        $customers_id,
        $session_id,
        $request_token,
        $amount,
        $currency
      );
      if ($transaction === false
          && isset($_SESSION['worldpay_junior_callback_transaction_id'])
          && is_numeric($_SESSION['worldpay_junior_callback_transaction_id'])
          )
      {
        $transaction = self::find_verified_transaction_by_id(
          (int)$_SESSION['worldpay_junior_callback_transaction_id'],
          $checkout_key,
          $customers_id,
          $session_id,
          $amount,
          $currency
        );
        if ($transaction === false) {
          unset($_SESSION['worldpay_junior_callback_transaction_id']);
        }
      }

      if (is_array($transaction)) {
        $this->verified_transaction_id = (int)$transaction['worldpay_id'];
        $_SESSION['worldpay_junior_transaction_id'] = $this->verified_transaction_id;
        unset($_SESSION['worldpay_junior_callback_transaction_id']);
        $order->info['order_status'] = $this->get_success_status();

        return false;
      }

      $transaction_id = self::create_transaction(
        $checkout_key,
        $customers_id,
        $session_id,
        $checkout_token,
        $amount,
        $currency
      );
      if ($transaction_id < 1) {
        $checkout->fail();
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      $fields = array(
        'instId' => MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID,
        'amount' => $amount,
        'currency' => $currency,
        'hideCurrency' => 'true',
        'cartId' => $transaction_id,
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
        'signature' => md5(MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD.':'.$amount.':'.$currency.':'.$transaction_id),
        'MC_callback' => $this->get_callback_url(),
        'M_sid' => $session_id,
        'M_cid' => $customers_id,
        'M_lang' => $language,
        'M_checkout_key' => $checkout_key,
        'M_checkout_token' => $checkout_token,
        'M_hash' => self::callback_hash($session_id, $customers_id, $transaction_id, $checkout_key, $language, $amount, $currency, $checkout_token),
      );

      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD == 'Pre-Authorization') {
        $fields['authMode'] = 'E';
      }
      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True') {
        $fields['testMode'] = '100';
      }

      $this->output_redirect_form($fields);
    }


    function payment_action() {
      return false;
    }


    function before_send_order() {
      global $insert_id;

      $transaction_id = $this->verified_transaction_id;
      if ($transaction_id < 1
          && isset($_SESSION['worldpay_junior_transaction_id'])
          && is_numeric($_SESSION['worldpay_junior_transaction_id'])
          )
      {
        $transaction_id = (int)$_SESSION['worldpay_junior_transaction_id'];
      }

      if ($transaction_id < 1 || !self::complete_transaction($transaction_id, (int)$insert_id)) {
        return false;
      }

      $sql_data_array = array(
        'orders_id' => (int)$insert_id,
        'orders_status_id' => $this->get_success_status(),
        'date_added' => 'now()',
        'customer_notified' => '0',
        'comments' => self::VERIFIED_COMMENT,
      );
      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True') {
        $sql_data_array['comments'] = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE;
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }

      return false;
    }


    function after_process() {
      unset($_SESSION['worldpay_junior_transaction_id']);
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
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED', '', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID', '', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD', '', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD', '', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD', 'Capture', '6', '0', 'xtc_cfg_select_option(array(\'Pre-Authorization\', \'Capture\'), ', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");

      self::ensure_transaction_table();
    }


    function remove() {
      $keys = $this->keys();
      $keys[] = 'MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID';
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION."
                          WHERE configuration_key IN ('".implode("', '", $keys)."')");
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


    static function ensure_transaction_table() {
      static $table_exists;

      if ($table_exists === true) {
        return true;
      }

      $table_query = xtc_db_query("SHOW TABLES LIKE '".str_replace('_', '\\_', self::TRANSACTION_TABLE)."'");
      if (xtc_db_num_rows($table_query) < 1) {
        $created = xtc_db_query("CREATE TABLE IF NOT EXISTS `".self::TRANSACTION_TABLE."` (
                                  `worldpay_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                  `checkout_key` CHAR(64) NOT NULL,
                                  `customers_id` INT UNSIGNED NOT NULL,
                                  `session_id` VARCHAR(64) NOT NULL,
                                  `checkout_token` CHAR(64) NOT NULL,
                                  `amount` DECIMAL(15,4) NOT NULL,
                                  `currency` CHAR(3) NOT NULL,
                                  `legacy_order_id` INT UNSIGNED DEFAULT NULL,
                                  `transaction_id` VARCHAR(128) NOT NULL DEFAULT '',
                                  `transaction_status` VARCHAR(16) NOT NULL DEFAULT 'pending',
                                  `orders_id` INT UNSIGNED NOT NULL DEFAULT 0,
                                  `date_added` DATETIME NOT NULL,
                                  `last_modified` DATETIME NOT NULL,
                                  PRIMARY KEY (`worldpay_id`),
                                  UNIQUE KEY `idx_checkout_token` (`checkout_token`),
                                  UNIQUE KEY `idx_legacy_order_id` (`legacy_order_id`),
                                  KEY `idx_checkout_key` (`checkout_key`, `customers_id`),
                                  KEY `idx_orders_id` (`orders_id`)
                                ) ENGINE=InnoDB");
        if ($created === false) {
          return false;
        }
      }

      $table_exists = true;
      return true;
    }


    static function create_transaction($checkout_key, $customers_id, $session_id, $checkout_token, $amount, $currency) {
      if (!self::ensure_transaction_table()) {
        return 0;
      }

      xtc_db_query("INSERT IGNORE INTO `".self::TRANSACTION_TABLE."`
                                (`checkout_key`, `customers_id`, `session_id`, `checkout_token`, `amount`, `currency`, `date_added`, `last_modified`)
                         VALUES ('".xtc_db_input($checkout_key)."',
                                 '".(int)$customers_id."',
                                 '".xtc_db_input($session_id)."',
                                 '".xtc_db_input($checkout_token)."',
                                 '".self::normalize_amount($amount)."',
                                 '".xtc_db_input($currency)."',
                                 NOW(),
                                 NOW())");

      $transaction_query = xtc_db_query("SELECT worldpay_id
                                            FROM `".self::TRANSACTION_TABLE."`
                                           WHERE checkout_key = '".xtc_db_input($checkout_key)."'
                                             AND customers_id = '".(int)$customers_id."'
                                             AND session_id = '".xtc_db_input($session_id)."'
                                             AND checkout_token = '".xtc_db_input($checkout_token)."'
                                             AND amount = '".self::normalize_amount($amount)."'
                                             AND currency = '".xtc_db_input($currency)."'
                                             AND transaction_status IN ('pending', 'cancelled')
                                           LIMIT 1");
      if (xtc_db_num_rows($transaction_query) !== 1) {
        return 0;
      }

      $transaction = xtc_db_fetch_array($transaction_query);
      return (int)$transaction['worldpay_id'];
    }


    static function find_verified_transaction($checkout_key, $customers_id, $session_id, $checkout_token, $amount, $currency) {
      if (!preg_match('/^[a-f0-9]{64}$/', (string)$checkout_token)
          || !self::ensure_transaction_table()
          )
      {
        return false;
      }

      $transaction_query = xtc_db_query("SELECT worldpay_id,
                                                transaction_status,
                                                orders_id
                                           FROM `".self::TRANSACTION_TABLE."`
                                          WHERE checkout_key = '".xtc_db_input($checkout_key)."'
                                            AND customers_id = '".(int)$customers_id."'
                                            AND session_id = '".xtc_db_input($session_id)."'
                                            AND checkout_token = '".xtc_db_input($checkout_token)."'
                                            AND amount = '".self::normalize_amount($amount)."'
                                            AND currency = '".xtc_db_input($currency)."'
                                            AND transaction_status IN ('verified', 'completed')
                                          LIMIT 1");

      return xtc_db_num_rows($transaction_query) === 1
        ? xtc_db_fetch_array($transaction_query)
        : false;
    }


    static function find_verified_transaction_by_id($transaction_id, $checkout_key, $customers_id, $session_id, $amount, $currency) {
      if ($transaction_id < 1 || !self::ensure_transaction_table()) {
        return false;
      }

      $transaction_query = xtc_db_query("SELECT worldpay_id,
                                                transaction_status,
                                                orders_id
                                           FROM `".self::TRANSACTION_TABLE."`
                                          WHERE worldpay_id = '".(int)$transaction_id."'
                                            AND checkout_key = '".xtc_db_input($checkout_key)."'
                                            AND customers_id = '".(int)$customers_id."'
                                            AND session_id = '".xtc_db_input($session_id)."'
                                            AND amount = '".self::normalize_amount($amount)."'
                                            AND currency = '".xtc_db_input($currency)."'
                                            AND transaction_status IN ('verified', 'completed')
                                          LIMIT 1");

      return xtc_db_num_rows($transaction_query) === 1
        ? xtc_db_fetch_array($transaction_query)
        : false;
    }


    static function process_callback($callback, $success, $transaction_id = '') {
      if (!is_array($callback)
          || !isset($callback['transaction_id'])
          || !self::ensure_transaction_table()
          )
      {
        return false;
      }

      $status_condition = $success ? "IN ('pending', 'cancelled')" : "= 'pending'";
      $new_status = $success ? 'verified' : 'cancelled';
      xtc_db_query("UPDATE `".self::TRANSACTION_TABLE."`
                       SET transaction_status = '".$new_status."',
                           transaction_id = '".xtc_db_input($transaction_id)."',
                           last_modified = NOW()
                     WHERE worldpay_id = '".(int)$callback['transaction_id']."'
                       AND checkout_key = '".xtc_db_input($callback['checkout_key'])."'
                       AND customers_id = '".(int)$callback['customers_id']."'
                       AND session_id = '".xtc_db_input($callback['session_id'])."'
                       AND checkout_token = '".xtc_db_input($callback['checkout_token'])."'
                       AND amount = '".self::normalize_amount($callback['amount'])."'
                       AND currency = '".xtc_db_input($callback['currency'])."'
                       AND transaction_status ".$status_condition);

      $transaction_query = xtc_db_query("SELECT worldpay_id,
                                                transaction_status,
                                                orders_id
                                           FROM `".self::TRANSACTION_TABLE."`
                                          WHERE worldpay_id = '".(int)$callback['transaction_id']."'
                                            AND checkout_key = '".xtc_db_input($callback['checkout_key'])."'
                                            AND customers_id = '".(int)$callback['customers_id']."'
                                            AND session_id = '".xtc_db_input($callback['session_id'])."'
                                            AND checkout_token = '".xtc_db_input($callback['checkout_token'])."'
                                            AND amount = '".self::normalize_amount($callback['amount'])."'
                                            AND currency = '".xtc_db_input($callback['currency'])."'
                                          LIMIT 1");
      if (xtc_db_num_rows($transaction_query) !== 1) {
        return false;
      }

      $transaction = xtc_db_fetch_array($transaction_query);
      if ($success) {
        return in_array($transaction['transaction_status'], array('verified', 'completed'))
          ? $transaction
          : false;
      }

      return in_array($transaction['transaction_status'], array('cancelled', 'verified', 'completed'))
        ? $transaction
        : false;
    }


    static function migrate_legacy_transaction($callback, $checkout_key, $checkout_token, $success) {
      if (!is_array($callback)
          || !isset($callback['legacy_order_id'])
          || !self::ensure_transaction_table()
          )
      {
        return false;
      }

      xtc_db_query("INSERT IGNORE INTO `".self::TRANSACTION_TABLE."`
                                (`checkout_key`, `customers_id`, `session_id`, `checkout_token`, `amount`, `currency`, `legacy_order_id`, `date_added`, `last_modified`)
                         VALUES ('".xtc_db_input($checkout_key)."',
                                 '".(int)$callback['customers_id']."',
                                 '".xtc_db_input($callback['session_id'])."',
                                 '".xtc_db_input($checkout_token)."',
                                 '".self::normalize_amount($callback['amount'])."',
                                 '".xtc_db_input($callback['currency'])."',
                                 '".(int)$callback['legacy_order_id']."',
                                 NOW(),
                                 NOW())");

      $new_status = $success ? 'verified' : 'cancelled';
      $status_condition = $success ? "IN ('pending', 'cancelled')" : "= 'pending'";
      xtc_db_query("UPDATE `".self::TRANSACTION_TABLE."`
                       SET transaction_status = '".$new_status."',
                           last_modified = NOW()
                     WHERE legacy_order_id = '".(int)$callback['legacy_order_id']."'
                       AND checkout_key = '".xtc_db_input($checkout_key)."'
                       AND customers_id = '".(int)$callback['customers_id']."'
                       AND session_id = '".xtc_db_input($callback['session_id'])."'
                       AND checkout_token = '".xtc_db_input($checkout_token)."'
                       AND amount = '".self::normalize_amount($callback['amount'])."'
                       AND currency = '".xtc_db_input($callback['currency'])."'
                       AND transaction_status ".$status_condition);

      $transaction_query = xtc_db_query("SELECT worldpay_id,
                                                transaction_status,
                                                orders_id
                                           FROM `".self::TRANSACTION_TABLE."`
                                          WHERE legacy_order_id = '".(int)$callback['legacy_order_id']."'
                                            AND checkout_key = '".xtc_db_input($checkout_key)."'
                                            AND customers_id = '".(int)$callback['customers_id']."'
                                            AND session_id = '".xtc_db_input($callback['session_id'])."'
                                            AND checkout_token = '".xtc_db_input($checkout_token)."'
                                            AND amount = '".self::normalize_amount($callback['amount'])."'
                                            AND currency = '".xtc_db_input($callback['currency'])."'
                                          LIMIT 1");

      return xtc_db_num_rows($transaction_query) === 1
        ? xtc_db_fetch_array($transaction_query)
        : false;
    }


    static function find_legacy_transaction($legacy_order_id, $customers_id, $session_id) {
      if ($legacy_order_id < 1 || !self::ensure_transaction_table()) {
        return false;
      }

      $transaction_query = xtc_db_query("SELECT worldpay_id,
                                                checkout_key,
                                                checkout_token,
                                                amount,
                                                currency,
                                                transaction_status,
                                                orders_id
                                           FROM `".self::TRANSACTION_TABLE."`
                                          WHERE legacy_order_id = '".(int)$legacy_order_id."'
                                            AND customers_id = '".(int)$customers_id."'
                                            AND session_id = '".xtc_db_input($session_id)."'
                                          LIMIT 1");

      return xtc_db_num_rows($transaction_query) === 1
        ? xtc_db_fetch_array($transaction_query)
        : false;
    }


    static function complete_transaction($transaction_id, $orders_id) {
      if ($transaction_id < 1 || $orders_id < 1 || !self::ensure_transaction_table()) {
        return false;
      }

      xtc_db_query("UPDATE `".self::TRANSACTION_TABLE."`
                       SET transaction_status = 'completed',
                           orders_id = '".(int)$orders_id."',
                           last_modified = NOW()
                     WHERE worldpay_id = '".(int)$transaction_id."'
                       AND transaction_status = 'verified'
                       AND orders_id = 0");

      return xtc_db_affected_rows() === 1;
    }


    static function callback_hash($session_id, $customers_id, $transaction_id, $checkout_key, $language, $amount, $currency, $checkout_token) {
      $amount = self::normalize_amount($amount);
      $payload = implode("\n", array(
        (string)$session_id,
        (int)$customers_id,
        (int)$transaction_id,
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
        'transaction_id' => (int)$post['cartId'],
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
          || $data['transaction_id'] < 1
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
        $data['transaction_id'],
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
        'legacy_order_id' => (int)$post['cartId'],
        'language' => basename(trim((string)$post['M_lang'])),
        'amount' => trim((string)$post['amount']),
        'currency' => strtoupper(trim((string)$post['currency'])),
        'hash' => strtolower(trim((string)$post['M_hash'])),
        'legacy' => true,
      );
      if (!preg_match('/^(?:[a-z0-9]{26}|[a-z0-9]{32}|[a-z0-9]{40}|[a-z0-9]{52})$/i', $data['session_id'])
          || $data['customers_id'] < 1
          || $data['legacy_order_id'] < 1
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
        .$data['legacy_order_id']
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


    private static function get_request_checkout_token() {
      if (isset($_GET['checkout_token']) && is_string($_GET['checkout_token'])) {
        return $_GET['checkout_token'];
      }
      if (isset($_POST['checkout_token']) && is_string($_POST['checkout_token'])) {
        return $_POST['checkout_token'];
      }

      return '';
    }


    private function get_success_status() {
      return $this->order_status > 0 ? $this->order_status : (int)DEFAULT_ORDERS_STATUS_ID;
    }
  }
