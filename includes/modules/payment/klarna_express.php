<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed classes
require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaPayment.php');


class klarna_express extends KlarnaPayment {

  var $code;
  var $klarna_code;

  function __construct() {
    global $order;

    $this->code = 'klarna_express';
    $this->klarna_code = 'express';

    KlarnaPayment::__construct($this->code);

    if (!defined('RUN_MODE_ADMIN') && is_object($order)) {
      $this->update_status();
    }
  }


  function update_status() {
    // payment choice only for the Klarna session the customer authorized in the express popup
    $this->enabled = (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS')
                      && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True'
                      && self::express_session_valid()
                      );
  }


  function selection() {
    // Klarna shows the chosen method in the container, no category is loaded
    $_SESSION['klarna']['script'][$this->klarna_code] = '
          Klarna.Payments.load({
            container: "#klarna-payments-express"
          });';

    return array(
      'id' => $this->code,
      'module' => MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_SELECTION,
      'description' => $this->info.'<div id="klarna-payments-express"></div>',
    );
  }


  function is_express_payment() {
    return self::express_session_valid();
  }


  // the Klarna session of an express checkout for this cart and these addresses, not older than an hour
  public static function express_session_valid() {
    return (isset($_SESSION['klarna'])
            && is_array($_SESSION['klarna'])
            && isset($_SESSION['klarna']['express_flow'])
            && $_SESSION['klarna']['express_flow'] === true
            && isset($_SESSION['klarna']['client_token'])
            && is_string($_SESSION['klarna']['client_token'])
            && $_SESSION['klarna']['client_token'] !== ''
            && isset($_SESSION['cart'])
            && is_object($_SESSION['cart'])
            && isset($_SESSION['klarna']['cart_id'])
            && $_SESSION['klarna']['cart_id'] === $_SESSION['cart']->cartID
            && isset($_SESSION['sendto'])
            && isset($_SESSION['klarna']['sendto'])
            && $_SESSION['klarna']['sendto'] == $_SESSION['sendto']
            && isset($_SESSION['billto'])
            && isset($_SESSION['klarna']['billto'])
            && $_SESSION['klarna']['billto'] == $_SESSION['billto']
            && isset($_SESSION['klarna']['time_created'])
            && ($_SESSION['klarna']['time_created'] + 3600) >= time()
            );
  }


  // the button needs the module on, the client id and the module in the installed list
  public static function express_enabled() {
    return (defined('MODULE_PAYMENT_KLARNA_EXPRESS_STATUS')
            && MODULE_PAYMENT_KLARNA_EXPRESS_STATUS == 'True'
            && defined('MODULE_PAYMENT_INSTALLED')
            && in_array('klarna_express.php', explode(';', MODULE_PAYMENT_INSTALLED), true)
            );
  }


  // virtual-only carts have no shipping address to collect
  function cart_requires_shipping() {
    $content_type = $_SESSION['cart']->get_content_type();

    return !($content_type == 'virtual'
             || $content_type == 'virtual_weight'
             || $_SESSION['cart']->count_contents_virtual() == 0
             );
  }


  // order data for the button, a guest cart has no billing country
  function get_express_order_data() {
    $order_array = $this->getOrderData(true);

    if (is_object($order_array['billing_address'])
        && (!isset($order_array['billing_address']->country) || $order_array['billing_address']->country == '')
        )
    {
      $order_array['billing_address']->country = $order_array['purchase_country'];
    }
    if (is_object($order_array['shipping_address'])
        && (!isset($order_array['shipping_address']->country) || $order_array['shipping_address']->country == '')
        )
    {
      $order_array['shipping_address']->country = $order_array['purchase_country'];
    }

    $order_array['locale'] = strtolower($_SESSION['language_code']).'-'.strtoupper($order_array['purchase_country']);

    return $order_array;
  }


  // the token binds the callback request to the cart page that rendered the button
  function get_express_token() {
    if (!isset($_SESSION['klarna_express'])
        || !is_array($_SESSION['klarna_express'])
        || !isset($_SESSION['klarna_express']['token'])
        || !is_string($_SESSION['klarna_express']['token'])
        || $_SESSION['klarna_express']['token'] === ''
        || $_SESSION['klarna_express']['cart_id'] !== (string)$_SESSION['cart']->cartID
        || ($_SESSION['klarna_express']['time'] + 3600) < time()
        )
    {
      $_SESSION['klarna_express'] = array(
        'cart_id' => (string)$_SESSION['cart']->cartID,
        'token' => bin2hex(random_bytes(16)),
        'time' => time(),
      );
    }

    return $_SESSION['klarna_express']['token'];
  }


  // returns the failure reason or an array with the checked data
  function check_express_request($post) {
    if (self::express_enabled() !== true) {
      return 'disabled';
    }

    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST' || !is_array($post)) {
      return 'method';
    }

    if (!isset($_SESSION['klarna_express'])
        || !is_array($_SESSION['klarna_express'])
        || !isset($_SESSION['klarna_express']['token'])
        || !is_string($_SESSION['klarna_express']['token'])
        || $_SESSION['klarna_express']['token'] === ''
        || !isset($post['token'])
        || !is_string($post['token'])
        || !hash_equals($_SESSION['klarna_express']['token'], $post['token'])
        || ($_SESSION['klarna_express']['time'] + 3600) < time()
        )
    {
      return 'token';
    }

    if (!isset($_SESSION['cart'])
        || !is_object($_SESSION['cart'])
        || $_SESSION['cart']->count_contents() < 1
        || $_SESSION['klarna_express']['cart_id'] !== (string)$_SESSION['cart']->cartID
        || $this->cart_requires_shipping() !== true
        )
    {
      return 'cart';
    }

    if (!isset($post['session_id'])
        || !is_string($post['session_id'])
        || !preg_match('/^[A-Za-z0-9_-]{8,64}$/', $post['session_id'])
        || !isset($post['client_token'])
        || !is_string($post['client_token'])
        || !preg_match('/^[A-Za-z0-9_.=+\/-]{16,4096}$/', $post['client_token'])
        )
    {
      return 'session';
    }

    // Klarna knows the session only if it belongs to this merchant account
    $klarna_session = $this->readKlarnaSession($post['session_id']);
    if (!is_array($klarna_session)) {
      return 'session';
    }

    if (!isset($klarna_session['purchase_currency'])
        || !is_string($klarna_session['purchase_currency'])
        || strtoupper($klarna_session['purchase_currency']) !== strtoupper($_SESSION['currency'])
        )
    {
      $this->logger->log('klarna', 'express callback: currency mismatch', array(
        'klarna' => ((isset($klarna_session['purchase_currency']) && is_string($klarna_session['purchase_currency'])) ? $klarna_session['purchase_currency'] : null),
        'shop' => $_SESSION['currency'],
      ));

      return 'currency';
    }

    $collected = ((isset($post['collected_shipping_address']) && is_string($post['collected_shipping_address']) && strlen($post['collected_shipping_address']) <= 20000) ? json_decode($post['collected_shipping_address'], true) : null);
    $address = $this->map_collected_address($collected);
    if (!is_array($address)) {
      return 'address';
    }

    $country = $this->get_active_country($address['country']);
    if (!is_array($country)) {
      return 'country';
    }
    $address['country_id'] = $country['countries_id'];

    // Klarna may return the categories on read, the posted ones only come from the browser
    $methods = $this->clean_categories((isset($klarna_session['payment_method_categories'])) ? $klarna_session['payment_method_categories'] : null);
    if (count($methods) < 1) {
      $posted = ((isset($post['payment_method_categories']) && is_string($post['payment_method_categories']) && strlen($post['payment_method_categories']) <= 20000) ? json_decode($post['payment_method_categories'], true) : null);
      $methods = $this->clean_categories($posted);
    }
    if (count($methods) < 1) {
      return 'methods';
    }

    return array(
      'session_id' => $post['session_id'],
      'client_token' => $post['client_token'],
      'methods' => $methods,
      'address' => $address,
    );
  }


  // maps the address collected by Klarna to the shop fields, false if a required field is missing
  function map_collected_address($collected) {
    if (!is_array($collected)) {
      return false;
    }

    $fields = array(
      'given_name' => 64,
      'family_name' => 64,
      'organization_name' => 64,
      'street_address' => 64,
      'street_address2' => 64,
      'attention' => 64,
      'postal_code' => 10,
      'city' => 64,
      'region' => 64,
      'country' => 2,
      'email' => 96,
      'phone' => 32,
    );
    $data = array();
    foreach ($fields as $key => $length) {
      $value = ((isset($collected[$key]) && is_string($collected[$key])) ? $collected[$key] : '');
      $value = trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', strip_tags($value)));
      $data[$key] = mb_substr($value, 0, $length, 'UTF-8');
    }

    $missing = array();
    foreach (array('given_name', 'family_name', 'street_address', 'postal_code', 'city', 'country', 'email') as $key) {
      if ($data[$key] === '') {
        $missing[] = $key;
      }
    }
    if (!preg_match('/^[A-Za-z]{2}$/', $data['country'])) {
      $missing[] = 'country';
    }
    if ($data['email'] !== '' && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
      $missing[] = 'email';
    }
    if (count($missing) > 0) {
      // field names only, the values are personal data
      $this->logger->log('klarna', 'express callback: address incomplete', array('fields' => array_values(array_unique($missing))));

      return false;
    }

    $suburb = trim($data['street_address2'].' '.$data['attention']);
    $street = $data['street_address'];
    if ($suburb != '' && (!defined('ACCOUNT_SUBURB') || ACCOUNT_SUBURB != 'true')) {
      // no suburb field in this shop, keep the second line in the street
      $street = $street.', '.$suburb;
      $suburb = '';
    }

    return array(
      'firstname' => $data['given_name'],
      'lastname' => $data['family_name'],
      'company' => $data['organization_name'],
      'street_address' => mb_substr($street, 0, 64, 'UTF-8'),
      'suburb' => mb_substr($suburb, 0, 32, 'UTF-8'),
      'postcode' => $data['postal_code'],
      'city' => $data['city'],
      'state' => $data['region'],
      'country' => strtoupper($data['country']),
      'email_address' => $data['email'],
      'telephone' => $data['phone'],
    );
  }


  function get_active_country($iso_code_2) {
    $country_query = xtc_db_query("SELECT countries_id,
                                          countries_iso_code_2
                                     FROM ".TABLE_COUNTRIES."
                                    WHERE countries_iso_code_2 = '".xtc_db_input($iso_code_2)."'
                                      AND status = '1'");
    if (xtc_db_num_rows($country_query) < 1) {
      return false;
    }

    return xtc_db_fetch_array($country_query);
  }


  // keeps the category fields the payment modules read
  function clean_categories($categories) {
    $clean = array();
    if (!is_array($categories)) {
      return $clean;
    }

    foreach ($categories as $category) {
      if (!is_array($category)
          || !isset($category['identifier'])
          || !is_string($category['identifier'])
          || !preg_match('/^[a-z_]{1,32}$/', $category['identifier'])
          )
      {
        continue;
      }

      $entry = array(
        'identifier' => $category['identifier'],
        'name' => ((isset($category['name']) && is_string($category['name'])) ? mb_substr(strip_tags($category['name']), 0, 100, 'UTF-8') : ''),
      );
      if (isset($category['asset_urls']) && is_array($category['asset_urls'])) {
        foreach (array('descriptive', 'standard') as $key) {
          if (isset($category['asset_urls'][$key])
              && is_string($category['asset_urls'][$key])
              && strpos($category['asset_urls'][$key], 'https://') === 0
              && filter_var($category['asset_urls'][$key], FILTER_VALIDATE_URL) !== false
              )
          {
            $entry['asset_urls'][$key] = $category['asset_urls'][$key];
          }
        }
      }
      $clean[] = $entry;
    }

    return $clean;
  }


  // the address in the character set of the shop
  function to_shop_charset($address) {
    foreach ($address as $key => $value) {
      if (is_string($value)) {
        $address[$key] = decode_utf8($value);
      }
    }

    return $address;
  }


  function get_zone_id($country_id, $region) {
    if ($region == '' || !defined('ACCOUNT_STATE') || ACCOUNT_STATE != 'true') {
      return 0;
    }

    $zone_query = xtc_db_query("SELECT DISTINCT zone_id
                                  FROM ".TABLE_ZONES."
                                 WHERE zone_country_id = '".(int)$country_id."'
                                   AND (zone_code = '".xtc_db_input($region)."'
                                        OR zone_name = '".xtc_db_input($region)."'
                                        )");
    if (xtc_db_num_rows($zone_query) == 1) {
      $zone = xtc_db_fetch_array($zone_query);

      return (int)$zone['zone_id'];
    }

    return 0;
  }


  function create_address_book($customer_id, $address) {
    $sql_data_array = array(
      'customers_id' => (int)$customer_id,
      'entry_gender' => '',
      'entry_company' => $address['company'],
      'entry_firstname' => $address['firstname'],
      'entry_lastname' => $address['lastname'],
      'entry_street_address' => $address['street_address'],
      'entry_suburb' => $address['suburb'],
      'entry_postcode' => $address['postcode'],
      'entry_city' => $address['city'],
      'entry_country_id' => (int)$address['country_id'],
      'entry_zone_id' => (int)$address['zone_id'],
      'entry_state' => ((defined('ACCOUNT_STATE') && ACCOUNT_STATE == 'true') ? $address['state'] : ''),
      'address_date_added' => 'now()',
      'address_last_modified' => 'now()',
    );
    xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

    return xtc_db_insert_id();
  }


  // an address book entry of the customer with the same data, else a new one
  function get_address_id($customer_id, $address) {
    $check_query = xtc_db_query("SELECT address_book_id
                                   FROM ".TABLE_ADDRESS_BOOK."
                                  WHERE customers_id = '".(int)$customer_id."'
                                    AND entry_firstname = '".xtc_db_input($address['firstname'])."'
                                    AND entry_lastname = '".xtc_db_input($address['lastname'])."'
                                    AND entry_company = '".xtc_db_input($address['company'])."'
                                    AND entry_street_address = '".xtc_db_input($address['street_address'])."'
                                    AND entry_suburb = '".xtc_db_input($address['suburb'])."'
                                    AND entry_postcode = '".xtc_db_input($address['postcode'])."'
                                    AND entry_city = '".xtc_db_input($address['city'])."'
                                    AND entry_country_id = '".(int)$address['country_id']."'
                                    AND entry_zone_id = '".(int)$address['zone_id']."'
                                  LIMIT 1");
    if (xtc_db_num_rows($check_query) > 0) {
      $check = xtc_db_fetch_array($check_query);

      return (int)$check['address_book_id'];
    }

    return $this->create_address_book($customer_id, $address);
  }


  // always a guest account, an existing account is never logged in by Klarna data
  function create_guest_account($address) {
    require_once(DIR_FS_INC.'xtc_create_password.inc.php');
    require_once(DIR_FS_INC.'write_customers_session.inc.php');
    require_once(DIR_FS_INC.'xtc_write_user_info.inc.php');

    $customers_password_time = time();

    $sql_data_array = array(
      'customers_status' => ((DEFAULT_CUSTOMERS_STATUS_ID_GUEST != 0) ? (int)DEFAULT_CUSTOMERS_STATUS_ID_GUEST : 1),
      'customers_gender' => '',
      'customers_firstname' => $address['firstname'],
      'customers_lastname' => $address['lastname'],
      'customers_email_address' => $address['email_address'],
      'customers_telephone' => $address['telephone'],
      'customers_fax' => '',
      'customers_newsletter' => 0,
      'account_type' => '1',
      'customers_password' => xtc_create_password(8),
      'customers_password_time' => $customers_password_time,
      'customers_date_added' => 'now()',
      'customers_last_modified' => 'now()',
    );
    xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);

    $customer_id = xtc_db_insert_id();
    if ((int)$customer_id < 1) {
      return false;
    }

    $address_id = $this->create_address_book($customer_id, $address);

    xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                     SET customers_default_address_id = '".(int)$address_id."'
                   WHERE customers_id = '".(int)$customer_id."'");

    $sql_data_array = array(
      'customers_info_id' => (int)$customer_id,
      'customers_info_number_of_logons' => '1',
      'customers_info_date_account_created' => 'now()',
      'customers_info_date_of_last_logon' => 'now()',
    );
    xtc_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);

    if (SESSION_RECREATE == 'True') {
      xtc_session_recreate();
    }

    $_SESSION['customer_id'] = $customer_id;
    $_SESSION['customer_time'] = $customers_password_time;

    write_customers_session((int)$customer_id);
    xtc_write_user_info((int)$customer_id);

    // the guest keeps the cart
    $_SESSION['cart']->restore_contents();

    return $address_id;
  }


  // account, address and the shop and Klarna sessions for the checkout
  function start_express($data) {
    $address = $this->to_shop_charset($data['address']);
    $address['country_id'] = $data['address']['country_id'];
    $address['zone_id'] = $this->get_zone_id($address['country_id'], $address['state']);

    if (isset($_SESSION['customer_id'])) {
      $address_id = $this->get_address_id($_SESSION['customer_id'], $address);
    } else {
      $address_id = $this->create_guest_account($address);
    }
    if ((int)$address_id < 1) {
      return false;
    }

    $_SESSION['sendto'] = (int)$address_id;
    $_SESSION['billto'] = (int)$address_id;
    $_SESSION['delivery_zone'] = $data['address']['country'];
    $_SESSION['billing_zone'] = $data['address']['country'];

    unset($_SESSION['shipping']);
    $_SESSION['payment'] = $this->code;

    // checks throughout the checkout against changes of the cart
    $_SESSION['cartID'] = $_SESSION['cart']->cartID;

    // same shape as getKlarnaSession(), klarna_express is the payment module of the order
    $_SESSION['klarna'] = array(
      'session_id' => $data['session_id'],
      'client_token' => $data['client_token'],
      'methods' => $data['methods'],
      'sendto' => $_SESSION['sendto'],
      'sendto_id' => $this->get_country_id($_SESSION['sendto']),
      'billto' => $_SESSION['billto'],
      'billto_id' => $this->get_country_id($_SESSION['billto']),
      'cart_id' => $_SESSION['cart']->cartID,
      'time_created' => time(),
      'express_flow' => true,
    );

    unset($_SESSION['klarna_express']);

    return true;
  }


  function install() {
    parent::install();

    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_CLIENT_ID', '', '6', '0', now())");
    // True is not implemented yet and behaves like False
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_SHORT_CHECKOUT', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_BUTTON_CART', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
  }


  function keys() {
    $keys = parent::keys();

    $keys[] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_CLIENT_ID';
    $keys[] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_SHORT_CHECKOUT';
    $keys[] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_BUTTON_CART';

    return $keys;
  }

}
