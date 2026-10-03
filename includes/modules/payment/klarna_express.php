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
    // no Klarna session and no category check, the module is not a payment choice
    $this->enabled = (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS')
                      && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True');
  }


  function selection() {
    // hidden in the normal checkout
    return false;
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


  // own session key, $_SESSION['klarna'] stays untouched
  function get_express_session() {
    if (KlarnaPayment::$session_failed === true) {
      return false;
    }

    $order_array = $this->get_express_order_data();

    try {
      $session = new Klarna\Rest\Payments\Sessions($this->connector);
      $response = $session->create($order_array);

      $_SESSION['klarna_express'] = array(
        'session_id' => $response->getId(),
        'client_token' => $response['client_token'],
        'cart_id' => $_SESSION['cart']->cartID,
        'time_created' => time(),
      );

      return $_SESSION['klarna_express'];
    } catch (Exception $e) {
      KlarnaPayment::$session_failed = true;
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
    }

    return false;
  }


  function install() {
    parent::install();

    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_CLIENT_ID', '', '6', '0', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_SHORT_CHECKOUT', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
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
