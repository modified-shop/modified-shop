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


    function __construct() {
      global $order;

      $this->signature = 'worldpay|worldpay_junior|1.0|2.2';
      $this->code = 'worldpay_junior';
      $this->title = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER') ? MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER : '';
      $this->enabled = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS') && MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS == 'True';

      $this->order_status = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID')
        ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID
        : 0;
      $this->tmpOrders = true;
      $this->tmpStatus = $this->order_status > 0 ? $this->order_status : (int)DEFAULT_ORDERS_STATUS_ID;
      $this->form_action_url = '';

      if ($this->enabled
          && (!defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID')
              || trim((string)MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID) == ''
              || !defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD')
              || (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD == ''
              || !defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD')
              || (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD == '')
          )
      {
        $this->enabled = false;
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
          $this->enabled = false;
        }
      }
    }


    function javascript_validation() {
      return false;
    }


    function selection() {
      if (isset($_SESSION['tmp_worldpay_oID']) && is_numeric($_SESSION['tmp_worldpay_oID'])) {
        $this->remove_temporary_order((int)$_SESSION['tmp_worldpay_oID']);
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

      // The first checkout_process request creates the temporary order. Payment
      // verification is only possible after WorldPay has returned to the shop.
      if (!isset($_SESSION['tmp_oID']) || !is_numeric($_SESSION['tmp_oID'])) {
        return false;
      }

      $order_id = (int)$_SESSION['tmp_oID'];
      $verified_query = xtc_db_query("SELECT o.orders_id
                                        FROM ".TABLE_ORDERS." o
                                       WHERE o.orders_id = '".$order_id."'
                                         AND o.customers_id = '".(int)$_SESSION['customer_id']."'
                                         AND o.payment_class = '".xtc_db_input($this->code)."'
                                         AND EXISTS (
                                               SELECT 1
                                                FROM ".TABLE_ORDERS_STATUS_HISTORY." osh
                                                WHERE osh.orders_id = o.orders_id
                                                  AND osh.comments = '".xtc_db_input(self::VERIFIED_COMMENT)."'
                                             )
                                       LIMIT 1");
      if (xtc_db_num_rows($verified_query) !== 1) {
        if (is_object($checkout)) {
          $checkout->fail();
        }
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      return false;
    }


    function payment_action() {
      global $checkout, $insert_id, $order;

      if (!isset($insert_id) || !is_numeric($insert_id)) {
        $insert_id = isset($_SESSION['tmp_oID']) && is_numeric($_SESSION['tmp_oID']) ? (int)$_SESSION['tmp_oID'] : 0;
      }

      $checkout_token = isset($_SESSION['checkout_processing_phase_token'])
                        && is_string($_SESSION['checkout_processing_phase_token'])
        ? $_SESSION['checkout_processing_phase_token']
        : '';
      if ((int)$insert_id < 1 || !preg_match('/^[a-f0-9]{64}$/', $checkout_token)) {
        if (is_object($checkout)) {
          $checkout->fail();
        }
        $this->remove_temporary_order((int)$insert_id);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }

      $order_id = (int)$insert_id;
      $amount = $this->format_raw($order->info['total']);
      $currency = (string)$_SESSION['currency'];
      $session_id = xtc_session_id();
      $customers_id = (int)$_SESSION['customer_id'];
      $language = (string)$_SESSION['language'];

      $fields = array(
        'instId' => MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID,
        'amount' => $amount,
        'currency' => $currency,
        'hideCurrency' => 'true',
        'cartId' => $order_id,
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
        'signature' => md5(MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD.':'.$amount.':'.$currency.':'.$order_id),
        'MC_callback' => $this->get_callback_url(),
        'M_sid' => $session_id,
        'M_cid' => $customers_id,
        'M_lang' => $language,
        'M_checkout_token' => $checkout_token,
        'M_hash' => self::callback_hash($session_id, $customers_id, $order_id, $language, $amount, $currency, $checkout_token),
      );

      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD == 'Pre-Authorization') {
        $fields['authMode'] = 'E';
      }
      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True') {
        $fields['testMode'] = '100';
      }

      $_SESSION['tmp_worldpay_oID'] = $order_id;
      $this->output_redirect_form($fields);
    }


    function after_process() {
      unset($_SESSION['tmp_worldpay_oID']);
      return false;
    }


    function get_error() {
      if (isset($_SESSION['tmp_worldpay_oID']) && is_numeric($_SESSION['tmp_worldpay_oID'])) {
        $this->remove_temporary_order((int)$_SESSION['tmp_worldpay_oID']);
      }

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
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          xtc_db_query("INSERT INTO ".TABLE_ORDERS_STATUS."
                                  (orders_status_id, language_id, orders_status_name)
                           VALUES ('".$status_id."', '".(int)$languages[$i]['id']."', 'Preparing [WorldPay]')");
        }

        $flags_query = xtc_db_query("DESCRIBE ".TABLE_ORDERS_STATUS." public_flag");
        if (xtc_db_num_rows($flags_query) == 1) {
          xtc_db_query("UPDATE ".TABLE_ORDERS_STATUS."
                           SET public_flag = 0,
                               downloads_flag = 0
                         WHERE orders_status_id = '".$status_id."'");
        }
      } else {
        $check = xtc_db_fetch_array($check_query);
        $status_id = (int)$check['orders_status_id'];
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
      global $currencies;

      if (empty($currency_code) || !isset($currencies->currencies[$currency_code])) {
        $currency_code = $_SESSION['currency'];
      }
      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(
        xtc_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']),
        $currencies->currencies[$currency_code]['decimal_places'],
        '.',
        ''
      );
    }


    static function callback_hash($session_id, $customers_id, $order_id, $language, $amount, $currency, $checkout_token) {
      $amount = number_format((float)$amount, 2, '.', '');
      $payload = implode("\n", array(
        (string)$session_id,
        (int)$customers_id,
        (int)$order_id,
        (string)$language,
        $amount,
        (string)$currency,
        (string)$checkout_token,
      ));

      return hash_hmac('sha256', $payload, (string)MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD);
    }


    static function validate_callback($post) {
      $required = array(
        'M_sid',
        'M_cid',
        'cartId',
        'M_lang',
        'amount',
        'currency',
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
        'order_id' => (int)$post['cartId'],
        'language' => basename(trim((string)$post['M_lang'])),
        'amount' => trim((string)$post['amount']),
        'currency' => strtoupper(trim((string)$post['currency'])),
        'checkout_token' => trim((string)$post['M_checkout_token']),
        'hash' => trim((string)$post['M_hash']),
      );

      if (!preg_match('/^(?:[a-z0-9]{26}|[a-z0-9]{32}|[a-z0-9]{40}|[a-z0-9]{52})$/i', $data['session_id'])
          || $data['customers_id'] < 1
          || $data['order_id'] < 1
          || !preg_match('/^[a-z0-9_-]{1,32}$/i', $data['language'])
          || !is_numeric($data['amount'])
          || (float)$data['amount'] <= 0
          || !preg_match('/^[A-Z]{3}$/', $data['currency'])
          || !preg_match('/^[a-f0-9]{64}$/', $data['checkout_token'])
          || !preg_match('/^[a-f0-9]{64}$/', $data['hash'])
          )
      {
        return false;
      }

      $data['amount'] = number_format((float)$data['amount'], 2, '.', '');
      $expected_hash = self::callback_hash(
        $data['session_id'],
        $data['customers_id'],
        $data['order_id'],
        $data['language'],
        $data['amount'],
        $data['currency'],
        $data['checkout_token']
      );
      if (!hash_equals($expected_hash, $data['hash'])) {
        return false;
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

      return $data;
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


    private function remove_temporary_order($order_id) {
      $order_id = (int)$order_id;
      if ($order_id > 0 && isset($_SESSION['customer_id'])) {
        $order_query = xtc_db_query("SELECT o.orders_id
                                       FROM ".TABLE_ORDERS." o
                                      WHERE o.orders_id = '".$order_id."'
                                        AND o.customers_id = '".(int)$_SESSION['customer_id']."'
                                        AND o.payment_class = '".xtc_db_input($this->code)."'
                                        AND o.orders_status = '".$this->tmpStatus."'
                                        AND NOT EXISTS (
                                              SELECT 1
                                                FROM ".TABLE_ORDERS_STATUS_HISTORY." osh
                                               WHERE osh.orders_id = o.orders_id
                                                 AND osh.comments = '".xtc_db_input(self::VERIFIED_COMMENT)."'
                                            )
                                      LIMIT 1");
        if (xtc_db_num_rows($order_query) === 1) {
          require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
          xtc_remove_order($order_id, STOCK_LIMITED == 'true' ? 'on' : false);
        }
      }

      if (isset($_SESSION['tmp_worldpay_oID']) && (int)$_SESSION['tmp_worldpay_oID'] === $order_id) {
        unset($_SESSION['tmp_worldpay_oID']);
      }
      if (isset($_SESSION['tmp_oID']) && (int)$_SESSION['tmp_oID'] === $order_id) {
        unset($_SESSION['tmp_oID']);
      }
    }
  }
