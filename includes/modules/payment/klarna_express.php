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
  var $shipping_modules;
  var $shipping_quotes;
  var $shipping_html;
  var $selected_shipping = '';

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

    if ($this->enabled === true && $this->zone_allowed() === false) {
      $this->enabled = false;
    }
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


  public static function short_checkout_enabled() {
    return (defined('MODULE_PAYMENT_KLARNA_EXPRESS_SHORT_CHECKOUT')
            && MODULE_PAYMENT_KLARNA_EXPRESS_SHORT_CHECKOUT == 'True'
            );
  }


  // the callback marks the session when it skipped the shipping and payment pages
  function use_short_checkout() {
    return (self::short_checkout_enabled()
            && self::express_session_valid()
            && isset($_SESSION['klarna']['short_checkout'])
            && $_SESSION['klarna']['short_checkout'] === true
            );
  }


  // the agreements of the short checkout, same switches as checkout_payment.php
  function get_agreements() {
    $_SESSION['cart']->get_content_type();

    $display_conditions = (defined('DISPLAY_CONDITIONS_ON_CHECKOUT') && DISPLAY_CONDITIONS_ON_CHECKOUT == 'true');
    $display_privacy = (defined('DISPLAY_PRIVACY_ON_CHECKOUT') && DISPLAY_PRIVACY_ON_CHECKOUT == 'true');

    return array(
      'display_conditions' => $display_conditions,
      'conditions' => ($display_conditions && (!defined('SIGN_CONDITIONS_ON_CHECKOUT') || SIGN_CONDITIONS_ON_CHECKOUT == 'true')),
      'display_privacy' => $display_privacy,
      'privacy' => ($display_privacy && (!defined('DISPLAY_PRIVACY_CHECK') || DISPLAY_PRIVACY_CHECK == 'true')),
      'revocation' => (defined('DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT')
                       && DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT == 'true'
                       && in_array($_SESSION['cart']->content_type, array('virtual', 'mixed'))
                       ),
    );
  }


  // the order has no shipping address to select a method for
  function is_virtual_order() {
    global $order;

    return ($order->content_type == 'virtual'
            || $order->content_type == 'virtual_weight'
            || $_SESSION['cart']->count_contents_virtual() == 0
            );
  }


  // the cart ships free of charge, decided on an order without a shipping method
  function calculate_free_shipping() {
    global $order, $free_shipping, $free_shipping_value_over;

    require_once(DIR_WS_CLASSES.'order.php');
    require_once(DIR_WS_CLASSES.'order_total.php');

    $order_backup = $order;
    $has_shipping = array_key_exists('shipping', $_SESSION);
    $shipping_backup = (($has_shipping) ? $_SESSION['shipping'] : null);
    unset($_SESSION['shipping']);

    $order = new order();

    $free_shipping = false;
    $free_shipping_value_over = 0;
    if (xtc_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
      $order_total_modules = new order_total();
      $order_total_modules->process();
    }
    $result = array($free_shipping === true, $free_shipping_value_over);

    $order = $order_backup;
    if ($has_shipping) {
      $_SESSION['shipping'] = $shipping_backup;
    }

    return $result;
  }


  // shipping methods that the shop excludes for this payment module
  function remove_excluded_shipping($shipping_modules) {
    if (!defined('MODULE_EXCLUDE_PAYMENT_STATUS') || MODULE_EXCLUDE_PAYMENT_STATUS != 'True') {
      return;
    }

    for ($i = 1; $i <= MODULE_EXCLUDE_PAYMENT_NUMBER; $i ++) {
      $payment_exclude = array_map('trim', explode(',', constant('MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$i)));
      if (in_array($this->code, $payment_exclude)) {
        $shipping_exclude = array_map('trim', explode(',', constant('MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$i)));
        foreach ($shipping_modules->modules as $key => $file) {
          if (in_array(substr($file, 0, -4), $shipping_exclude)) {
            unset($shipping_modules->modules[$key]);
          }
        }
      }
    }
  }


  // loads the shipping modules and their quotes for the order in the global $order
  function load_shipping() {
    global $order, $total_weight, $total_count, $free_shipping, $free_shipping_value_over;

    require_once(DIR_FS_INC.'xtc_count_shipping_modules.inc.php');
    require_once(DIR_WS_CLASSES.'shipping.php');

    if ($order->delivery['country']['iso_code_2'] != '') {
      $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
    }
    if (isset($order->delivery['delivery_zone']) && $order->delivery['delivery_zone'] != '') {
      $_SESSION['delivery_zone'] = $order->delivery['delivery_zone'];
    }
    if ($order->billing['country']['iso_code_2'] != '') {
      $_SESSION['billing_zone'] = $order->billing['country']['iso_code_2'];
    }

    // a method with its own address (pickup) is zoned by the billing country
    if (isset($_SESSION['shipping'])
        && is_array($_SESSION['shipping'])
        && isset($_SESSION['shipping']['id'])
        && strpos($_SESSION['shipping']['id'], '_') !== false
        )
    {
      $class = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
      if (isset($GLOBALS[$class])
          && is_object($GLOBALS[$class])
          && method_exists($GLOBALS[$class], 'address')
          && $order->billing['country']['iso_code_2'] != ''
          )
      {
        $_SESSION['delivery_zone'] = $order->billing['country']['iso_code_2'];
      }
    }

    $total_weight = $_SESSION['cart']->show_weight();
    $total_count = $_SESSION['cart']->count_contents();

    list($free_shipping, $free_shipping_value_over) = $this->calculate_free_shipping();

    $this->shipping_modules = new shipping;
    $this->remove_excluded_shipping($this->shipping_modules);

    $this->shipping_quotes = $this->shipping_modules->quote();

    return $this->shipping_quotes;
  }


  // the cheapest shipping method of the cart, false if the shop offers none
  function get_cheapest_shipping() {
    global $free_shipping;

    $this->load_shipping();

    if ($free_shipping === true) {
      return array(
        'id' => 'free_free',
        'title' => FREE_SHIPPING_TITLE,
        'cost' => 0,
      );
    }

    $cheapest = $this->shipping_modules->cheapest();
    if (!is_array($cheapest)) {
      return false;
    }

    return array(
      'id' => $cheapest['id'],
      'title' => $cheapest['title'],
      'cost' => $cheapest['cost'],
    );
  }


  // the callback skips the shipping and payment pages, false sends the customer to the shipping page
  function start_short_checkout() {
    global $order;

    if (self::short_checkout_enabled() !== true
        || self::express_session_valid() !== true
        || $this->cart_requires_shipping() !== true
        )
    {
      return false;
    }

    // outside the checkout pages the order takes its delivery country from the cart estimate
    $_SESSION['country'] = (int)$_SESSION['klarna']['sendto_id'];

    require_once(DIR_WS_CLASSES.'order.php');
    $order = new order();

    $shipping = $this->get_cheapest_shipping();
    if (!is_array($shipping)) {
      unset($_SESSION['shipping']);

      return false;
    }

    $_SESSION['shipping'] = $shipping;
    $_SESSION['klarna']['short_checkout'] = true;

    // the shipping and payment pages would end a former payment handover and rotate the key of the attempt
    unset($_SESSION['tmp_oID']);
    $_SESSION['payment_nonce'] = md5(uniqid((string)rand(), true));

    return true;
  }


  function pre_confirmation_check() {
    if ($this->use_short_checkout() !== true) {
      return parent::pre_confirmation_check();
    }

    global $smarty, $free_shipping;

    $this->shipping_html = null;
    $this->load_shipping();
    $shipping_modules = $this->shipping_modules;

    // process the selected shipping method, it redirects when the method is valid
    if (isset($_POST['action']) && $_POST['action'] == 'process') {
      if (isset($_POST['shipping'])
          && is_string($_POST['shipping'])
          && preg_match('/^[A-Za-z0-9]+_[^_]+/', $_POST['shipping'])
          )
      {
        list($module, $method) = explode('_', $_POST['shipping']);
        global ${$module};
      } else {
        unset($_POST['shipping']);
      }

      $redirect_link = xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(array('conditions_message')), 'SSL');
      require(DIR_WS_INCLUDES.'shipping_action.php');
    }

    $this->prepare_shipping_block();

    // the final cart and shipping go to the Klarna session
    parent::pre_confirmation_check();
  }


  // shipping selection of the confirmation page, built once per request
  function prepare_shipping_block() {
    global $order, $xtPrice, $free_shipping, $free_shipping_value_over;

    if ($this->shipping_html !== null) {
      return;
    }
    $this->shipping_html = '';
    $this->selected_shipping = '';

    $no_shipping = $this->is_virtual_order();

    if (!is_object($this->shipping_modules)) {
      $this->load_shipping();
    }
    $shipping_modules = $this->shipping_modules;
    $quotes = $this->shipping_quotes;
    $quotes_count = xtc_count_shipping_modules();
    $free_shipping_active = ($free_shipping === true);

    // select the cheapest method if none is selected, or if the one method of a former selection is gone
    if ($no_shipping === false
        && ((!isset($_SESSION['shipping']) && defined('CHECK_CHEAPEST_SHIPPING_MODUL') && CHECK_CHEAPEST_SHIPPING_MODUL == 'true')
            || (isset($_SESSION['shipping']) && $_SESSION['shipping'] == false && $quotes_count == 1)
            )
        )
    {
      if ($free_shipping === true) {
        $_SESSION['shipping'] = array(
          'id' => 'free_free',
          'title' => FREE_SHIPPING_TITLE,
          'cost' => 0,
        );
      } else {
        $_SESSION['shipping'] = $shipping_modules->cheapest();
      }
      $order = new order();
    }

    if ($no_shipping === true) {
      $_SESSION['shipping'] = false;

      return;
    }

    if (defined('SHOW_SELFPICKUP_FREE') && SHOW_SELFPICKUP_FREE == 'true') {
      if ($free_shipping == true) {
        $free_shipping = false;

        $ot_shipping = new ot_shipping();
        $quotes_array = $ot_shipping->quote();
        for ($i = 0, $n = sizeof($quotes); $i < $n; $i ++) {
          if (isset($GLOBALS[$quotes[$i]['id']])
              && is_object($GLOBALS[$quotes[$i]['id']])
              && method_exists($GLOBALS[$quotes[$i]['id']], 'display_free')
              )
          {
            if ($GLOBALS[$quotes[$i]['id']]->display_free() === true) {
              $quotes_array = array_merge($quotes_array, $shipping_modules->quote($quotes[$i]['id'], $quotes[$i]['methods'][0]['id']));
            }
          } elseif (strpos($quotes[$i]['id'], 'selfpickup') !== false) {
            $quotes_array = array_merge($quotes_array, $shipping_modules->quote($quotes[$i]['id'], $quotes[$i]['methods'][0]['id']));
          }
        }
        $quotes = $quotes_array;
      }
    }

    // the confirm button belongs to a choice between methods, not between modules
    $methods_count = 0;
    foreach ($quotes as $quote) {
      if (!isset($quote['error']) && isset($quote['methods']) && is_array($quote['methods'])) {
        $methods_count += count($quote['methods']);
      }
    }

    // build shipping block
    require(DIR_WS_INCLUDES.'shipping_block.php');

    $shipping_found = false;
    if (isset($_SESSION['shipping'])
        && is_array($_SESSION['shipping'])
        && array_key_exists('id', $_SESSION['shipping'])
        )
    {
      if ($free_shipping_active === true && $_SESSION['shipping']['id'] == 'free_free') {
        $shipping_found = true;
      }
      for ($i = 0, $n = sizeof($quotes); $i < $n; $i ++) {
        if (isset($quotes[$i]['methods']) && is_array($quotes[$i]['methods'])) {
          for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j ++) {
            if ($quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']) {
              $shipping_found = true;
            }
          }
        }
      }
    }
    if ($shipping_found === true) {
      $this->selected_shipping = $_SESSION['shipping']['id'];
    }

    $module_smarty->assign('FORM_SHIPPING_ACTION', xtc_draw_form('checkout_shipping', xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(), 'SSL')).xtc_draw_hidden_field('action', 'process'));
    $module_smarty->assign('shipping_message', '');
    $module_smarty->assign('BUTTON_CONTINUE', '');
    if ($shipping_found === false) {
      $module_smarty->assign('shipping_message', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
      $module_smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
    } elseif ($methods_count > 1 && $free_shipping != true) {
      $module_smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
    }
    $module_smarty->assign('FORM_END', '</form>');
    $module_smarty->assign('SHIPPING_BLOCK', $shipping_block);

    if ($quotes_count == 0) {
      $_SESSION['shipping'] = '';
    }

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->caching = 0;

    $this->shipping_html = $module_smarty->fetch($this->get_template('shipping_block.html'));
  }


  // a template of the current template set wins over the one of the Klarna module
  function get_template($file) {
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/klarna/'.$file)) {
      return DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/klarna/'.$file;
    }

    return DIR_FS_EXTERNAL.'klarna/templates/'.$file;
  }


  function confirmation() {
    if ($this->use_short_checkout() !== true) {
      return parent::confirmation();
    }

    global $smarty;

    $this->prepare_shipping_block();

    if ($this->shipping_html != '') {
      $smarty->assign('SHIPPING_METHOD', $this->shipping_html);
    }
    $smarty->assign('SHIPPING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, xtc_get_all_get_params(), 'SSL'));
    $smarty->assign('BILLING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, xtc_get_all_get_params(), 'SSL'));

    // the selection is part of the page now, the payment method has no choice
    $smarty->clear_assign('SHIPPING_EDIT');
    $smarty->clear_assign('PAYMENT_EDIT');

    return false;
  }


  function process_button() {
    if ($this->use_short_checkout() !== true) {
      return parent::process_button();
    }

    global $main;

    $agreements = $this->get_agreements();

    $module_smarty = new Smarty();

    $checked = ((isset($_GET['step']) && $_GET['step'] == 'step2') ? ' checked="checked"' : '');

    if ($agreements['display_conditions']) {
      $shop_content_data = $main->getContentData(3);
      $module_smarty->assign('AGB', '<div class="agbframe">'.$shop_content_data['content_text'].'</div>');
      $module_smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO, 'SSL'));
      if ($agreements['conditions']) {
        $module_smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" id="conditions"'.$checked.' />');
      }
    }

    if ($agreements['revocation']) {
      $module_smarty->assign('REVOCATION_LINK', $main->getContentLink(REVOCATION_ID, MORE_INFO, 'SSL'));
      $module_smarty->assign('REVOCATION_checkbox', '<input type="checkbox" value="revocation" name="revocation" id="revocation"'.$checked.' />');
    }

    if ($agreements['display_privacy']) {
      $module_smarty->assign('PRIVACY_LINK', $main->getContentLink(2, MORE_INFO, 'SSL'));
      if ($agreements['privacy']) {
        $module_smarty->assign('PRIVACY_checkbox', '<input type="checkbox" value="privacy" name="privacy" id="privacy"'.$checked.' />');
      }
    }

    $module_smarty->assign('COMMENTS', xtc_draw_textarea_field('comments', 'soft', '60', '5', ((isset($_SESSION['comments'])) ? $_SESSION['comments'] : '')).xtc_draw_hidden_field('comments_added', 'YES'));
    $module_smarty->assign('ADR_checkbox', '<input type="checkbox" value="address" name="check_address" id="address" />');

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->caching = 0;

    return $module_smarty->fetch($this->get_template('comments_block.html')).parent::process_button();
  }


  // a guard message without its trailing line breaks, the guard adds its own
  function get_guard_text($text) {
    return xtc_js_lang(preg_replace('/(\\\\n|\\s)+$/', '', $text));
  }


  // the shop texts of the payment page, checked before finalize() opens the Klarna popup
  function get_submit_guard_js() {
    if ($this->use_short_checkout() !== true) {
      return '';
    }

    $agreements = $this->get_agreements();

    $checks = array();
    $checks[] = array('#address', ERROR_ADDRESS_NOT_ACCEPTED);
    if ($agreements['conditions']) {
      $checks[] = array('#conditions', JS_ERROR_CONDITIONS_NOT_ACCEPTED);
    }
    if ($agreements['privacy']) {
      $checks[] = array('#privacy', JS_ERROR_PRIVACY_NOTICE_NOT_ACCEPTED);
    }
    if ($agreements['revocation']) {
      $checks[] = array('#revocation', JS_ERROR_REVOCATION_NOT_ACCEPTED);
    }

    // the shop texts end with line breaks in German only
    $js = '
                var klarna_error = "";
                var klarna_decode = function(str) {
                  return str.replace(/%([0-9A-Fa-f]{2})/g, function(m, hex) { return String.fromCharCode(parseInt(hex, 16)); });
                };';
    foreach ($checks as $check) {
      $js .= '
                if (!$("'.$check[0].'").is(":checked")) {
                  klarna_error += klarna_decode("'.$this->get_guard_text($check[1]).'") + "\\n\\n";
                }';
    }

    if ($this->is_virtual_order() === false) {
      // the radio buttons belong to the shipping form, the selection counts once the form is sent
      $js .= '
                var klarna_shipping = $("#checkout_shipping input[name=shipping]:checked").val();
                if (klarna_shipping === undefined) {
                  klarna_shipping = $("#checkout_shipping input[name=shipping][type=hidden]").val();
                }
                if (klarna_shipping === undefined) {
                  klarna_error += klarna_decode("'.$this->get_guard_text(JS_ERROR_NO_SHIPPING_MODULE_SELECTED).'") + "\\n\\n";
                } else if (klarna_shipping != '.json_encode($this->selected_shipping, JSON_HEX_TAG | JSON_HEX_AMP).') {
                  klarna_error += klarna_decode("'.$this->get_guard_text(MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_JS_ERROR_SHIPPING).'") + "\\n\\n";
                }';
    }

    $js .= '
                if (klarna_error != "") {
                  alert(klarna_decode("'.xtc_js_lang(JS_ERROR).'") + klarna_error);
                  return;
                }';

    return $js;
  }


  // agreements and shipping are checked before the Klarna order is created, a failed check returns to the page
  function check_short_checkout() {
    global $messageStack, $order;

    $error = false;
    $agreements = $this->get_agreements();

    if (isset($_POST['comments_added']) && $_POST['comments_added'] != '') {
      $_SESSION['comments'] = xtc_db_prepare_input((isset($_POST['comments'])) ? $_POST['comments'] : '');
      $order->info['comments'] = $_SESSION['comments'];
    }

    if ($agreements['conditions'] && (!isset($_POST['conditions']) || $_POST['conditions'] != 'conditions')) {
      $error = true;
      $messageStack->add_session('checkout_confirmation', str_replace('\n', '', ERROR_CONDITIONS_NOT_ACCEPTED));
    }
    if (!isset($_POST['check_address']) || $_POST['check_address'] != 'address') {
      $error = true;
      $messageStack->add_session('checkout_confirmation', str_replace('\n', '', ERROR_ADDRESS_NOT_ACCEPTED));
    }

    $no_shipping = $this->is_virtual_order();
    if (!isset($_SESSION['shipping'])
        || ($_SESSION['shipping'] !== false && (!is_array($_SESSION['shipping']) || !isset($_SESSION['shipping']['id'])))
        || ($no_shipping === false && $_SESSION['shipping'] === false)
        )
    {
      $error = true;
      $messageStack->add_session('checkout_confirmation', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
    }
    if ($agreements['revocation'] && (!isset($_POST['revocation']) || $_POST['revocation'] != 'revocation')) {
      $error = true;
      $messageStack->add_session('checkout_confirmation', str_replace('\n', '', ERROR_REVOCATION_NOT_ACCEPTED));
    }
    if ($agreements['privacy'] && (!isset($_POST['privacy']) || $_POST['privacy'] != 'privacy')) {
      $error = true;
      $messageStack->add_session('checkout_confirmation', str_replace('\n', '', ERROR_PRIVACY_NOTICE_NOT_ACCEPTED));
    }

    if ($error === true) {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true', 'SSL'));
    }
  }


  function before_process() {
    global $messageStack;

    // no order without a valid Klarna session
    if (self::express_session_valid() !== true) {
      self::discard_session();
      $messageStack->add_session('checkout_payment', MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_SESSION);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }

    // the confirmation page of the short checkout holds the agreements, check them first
    if ($this->use_short_checkout() === true) {
      $this->check_short_checkout();
    }

    return parent::before_process();
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
            && isset($_SESSION['klarna']['sendto_id'])
            && $_SESSION['klarna']['sendto_id'] == self::address_country_id($_SESSION['sendto'])
            && isset($_SESSION['billto'])
            && isset($_SESSION['klarna']['billto'])
            && $_SESSION['klarna']['billto'] == $_SESSION['billto']
            && isset($_SESSION['klarna']['billto_id'])
            && $_SESSION['klarna']['billto_id'] == self::address_country_id($_SESSION['billto'])
            && isset($_SESSION['klarna']['time_created'])
            && ($_SESSION['klarna']['time_created'] + 3600) >= time()
            );
  }


  // the country of an address book entry, read once per request
  public static function address_country_id($address_id) {
    static $cache = array();

    $address_id = (int)$address_id;
    if (!isset($cache[$address_id])) {
      $address_query = xtc_db_query("SELECT entry_country_id
                                       FROM ".TABLE_ADDRESS_BOOK."
                                      WHERE address_book_id = '".$address_id."'");
      $address = xtc_db_fetch_array($address_query);
      if (!is_array($address)) {
        return 0;
      }
      $cache[$address_id] = (int)$address['entry_country_id'];
    }

    return $cache[$address_id];
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

    $order_array['locale'] = $this->get_express_locale($order_array['purchase_country']);

    return $order_array;
  }


  // the button loads before an order exists, the purchase country is the shop country
  function get_express_locale($purchase_country = '') {
    if ($purchase_country == '') {
      require_once(DIR_FS_INC.'xtc_get_countries.inc.php');
      $country = xtc_get_countriesList(STORE_COUNTRY);
      $purchase_country = $country['countries_iso_code_2'];
    }

    return strtolower($_SESSION['language_code']).'-'.strtoupper($purchase_country);
  }


  // the fields of the order data that authorize() receives
  function get_express_payload($order_array) {
    return array(
      'purchase_country' => $order_array['purchase_country'],
      'purchase_currency' => $order_array['purchase_currency'],
      'locale' => $order_array['locale'],
      'order_amount' => $order_array['order_amount'],
      'order_tax_amount' => $order_array['order_tax_amount'],
      'order_lines' => $order_array['order_lines'],
    );
  }


  // payload and callback token of the current cart, the session keeps token and amount for the callback
  function prepare_express() {
    $order_array = $this->get_express_order_data();
    $token = $this->get_express_token();
    $this->set_express_amount($order_array['order_amount']);

    return array(
      'locale' => $order_array['locale'],
      'payload' => $this->get_express_payload($order_array),
      'token' => $token,
    );
  }


  // settings of the button on cart and product page, $express comes from prepare_express()
  function get_express_config($express = null, $error_url = '') {
    $config = array('client_id' => MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID);
    if (is_array($express)) {
      $config['locale'] = $express['locale'];
      $config['payload'] = $express['payload'];
      $config['token'] = $express['token'];
    } else {
      $config['locale'] = $this->get_express_locale();
    }
    $config['callback'] = str_replace('&amp;', '&', xtc_href_link('callback/klarna/express.php', '', 'SSL'));
    $config['error_url'] = $error_url;

    return $config;
  }


  // shop side conditions of the button for the current cart
  function express_available() {
    return ($_SESSION['cart']->show_total() > 0
            && self::express_enabled() === true
            && $this->cart_requires_shipping() === true
            && $this->payment_allowed() === true
            );
  }


  // module is installed, active and has a client id
  public static function express_configured() {
    return (self::express_enabled() === true
            && defined('MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID')
            && MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID != ''
            );
  }


  // button, loader and result handling of cart and product page, $on_click_js is the body of on_click(authorize)
  function get_express_button($config, $on_click_js) {
    $config_json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if ($config_json === false) {
      return '';
    }

    return '<link rel="stylesheet" property="stylesheet" href="'.DIR_WS_BASE.DIR_WS_EXTERNAL.'klarna/css/express.css?v=3" type="text/css" media="screen" />
          <div id="klarna-express-button"></div>
          <script>
            (function () {
              var cfg = '.$config_json.';
              var options = {auto_finalize: false, collect_shipping_address: true};
              var fail = function () {
                window.location.href = cfg.error_url;
              };
              var post = function (result) {
                var fields = {
                  token: cfg.token,
                  client_token: result.client_token,
                  session_id: result.session_id,
                  collected_shipping_address: JSON.stringify(result.collected_shipping_address || {}),
                  payment_method_categories: JSON.stringify(result.payment_method_categories || [])
                };
                var form = document.createElement("form");
                form.method = "post";
                form.action = cfg.callback;
                Object.keys(fields).forEach(function (name) {
                  var input = document.createElement("input");
                  input.type = "hidden";
                  input.name = name;
                  input.value = fields[name];
                  form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
              };
              var done = function (result) {
                if (result && result.approved === true) {
                  if (result.finalize_required === true && result.client_token && result.session_id) {
                    post(result);
                  } else {
                    fail();
                  }
                } else if (result && result.show_form === false) {
                  fail();
                }
              };
              window.klarnaAsyncCallback = function () {
                try {
                  window.Klarna.Payments.Buttons.init({client_id: cfg.client_id}).load({
                    container: "#klarna-express-button",
                    theme: "default",
                    shape: "rect",
                    locale: cfg.locale,
                    on_click: function (authorize) {
                      '.$on_click_js.'
                    }
                  });
                } catch (e) {}
              };
            })();
          </script>
          <script src="https://x.klarnacdn.net/kp/lib/v1/api.js" async></script>';
  }


  // the ajax token binds the product page request to the session
  function get_ajax_token() {
    if (!isset($_SESSION['klarna_express_ajax'])
        || !is_string($_SESSION['klarna_express_ajax'])
        || $_SESSION['klarna_express_ajax'] === ''
        )
    {
      $_SESSION['klarna_express_ajax'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['klarna_express_ajax'];
  }


  function is_valid_ajax_token() {
    return (isset($_SERVER['REQUEST_METHOD'])
            && $_SERVER['REQUEST_METHOD'] == 'POST'
            && isset($_SESSION['klarna_express_ajax'])
            && is_string($_SESSION['klarna_express_ajax'])
            && $_SESSION['klarna_express_ajax'] !== ''
            && isset($_POST['klarna_express_ajax_token'])
            && is_string($_POST['klarna_express_ajax_token'])
            && hash_equals($_SESSION['klarna_express_ajax'], $_POST['klarna_express_ajax_token'])
            );
  }


  // stock and order value limits that the cart page enforces, returns the failure reason or an empty string
  function check_cart_limits() {
    global $xtPrice;

    if (STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT != 'true') {
      require_once(DIR_FS_INC.'xtc_check_stock.inc.php');
      require_once(DIR_FS_INC.'check_stock_specials.inc.php');

      foreach ((array)$_SESSION['cart']->get_products(false) as $products) {
        if (xtc_check_stock($products['id'], $products['quantity'], $products['stock']) != '') {
          return 'stock';
        }
        if (STOCK_CHECK_SPECIALS == 'true'
            && $xtPrice->xtcCheckSpecial($products['id'])
            && check_stock_specials($products['id'], $products['quantity']) != ''
            )
        {
          return 'stock';
        }
      }
    }

    $total = $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total());
    if ($total < $_SESSION['customers_status']['customers_status_min_order']
        || ($_SESSION['customers_status']['customers_status_max_order'] != 0
            && $total > $_SESSION['customers_status']['customers_status_max_order']
            )
        )
    {
      return 'order_value';
    }

    return '';
  }


  // error answer of the product page request, the texts hold HTML entities for the page
  public static function get_ajax_error($reason) {
    $texts = array(
      'add' => MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_ADD,
      'stock' => MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_STOCK,
      'order_value' => MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_ORDER_VALUE,
      'token' => MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_TOKEN,
      'unavailable' => MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_UNAVAILABLE,
    );
    if (!isset($texts[$reason])) {
      $reason = 'unavailable';
    }

    return array(
      'ok' => false,
      'error' => $reason,
      'message' => html_entity_decode($texts[$reason], ENT_QUOTES, 'UTF-8'),
    );
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


  // the amount the button was rendered with, the callback compares it with the Klarna session
  function set_express_amount($amount) {
    if (isset($_SESSION['klarna_express']) && is_array($_SESSION['klarna_express'])) {
      $_SESSION['klarna_express']['order_amount'] = (int)round($amount);
    }
  }


  // shop restrictions of this module, the country is known in the callback only
  function payment_allowed($country_iso = '', $country_id = 0, $zone_id = 0) {
    $unallowed = ((isset($_SESSION['customers_status']['customers_status_payment_unallowed'])) ? $_SESSION['customers_status']['customers_status_payment_unallowed'] : '');

    $content_type = $_SESSION['cart']->get_content_type();
    if (in_array($content_type, array('virtual', 'virtual_weight', 'mixed')) && defined('DOWNLOAD_UNALLOWED_PAYMENT')) {
      $unallowed .= ','.DOWNLOAD_UNALLOWED_PAYMENT;
    }
    if ($_SESSION['cart']->count_contents_virtual() != $_SESSION['cart']->count_contents()
        && defined('MODULE_ORDER_TOTAL_GV_UNALLOWED_PAYMENT')
        )
    {
      $unallowed .= ','.MODULE_ORDER_TOTAL_GV_UNALLOWED_PAYMENT;
    }
    if (in_array($this->code, explode(',', preg_replace("'[\r\n\s]+'", '', $unallowed)))) {
      return false;
    }

    if ($country_iso != '') {
      if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED') && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED') != '') {
        $allowed_zones = array_filter(explode(',', strtoupper(preg_replace("'[\r\n\s]+'", '', constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED')))));
        if (count($allowed_zones) > 0 && !in_array(strtoupper($country_iso), $allowed_zones)) {
          return false;
        }
      }
      if ($this->zone_allowed((int)$country_id, (int)$zone_id) === false) {
        return false;
      }
    }

    return true;
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

    // the browser posts the token, a token of another session must not reach the checkout
    if ((isset($klarna_session['client_token']) && (!is_string($klarna_session['client_token']) || !hash_equals($klarna_session['client_token'], $post['client_token'])))
        || (isset($klarna_session['session_id']) && (!is_string($klarna_session['session_id']) || !hash_equals($klarna_session['session_id'], $post['session_id'])))
        )
    {
      $this->logger->log('klarna', 'express callback: session mismatch');

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

    // the amount of the button payload as the cart page rendered it
    if (isset($klarna_session['order_amount'])
        && is_numeric($klarna_session['order_amount'])
        && isset($_SESSION['klarna_express']['order_amount'])
        && (int)$klarna_session['order_amount'] !== (int)$_SESSION['klarna_express']['order_amount']
        )
    {
      $this->logger->log('klarna', 'express callback: amount mismatch', array(
        'klarna' => (int)$klarna_session['order_amount'],
        'shop' => (int)$_SESSION['klarna_express']['order_amount'],
      ));

      return 'amount';
    }

    // the status is only logged, its values are not documented for the button flow
    if (isset($klarna_session['status']) && is_string($klarna_session['status']) && $klarna_session['status'] !== 'incomplete') {
      $this->logger->log('klarna', 'express callback: session status '.substr(preg_replace('/[^A-Za-z_]/', '', $klarna_session['status']), 0, 32));
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

    // the payment page would drop the Klarna choice for this address anyway
    $zone_address = $this->to_shop_charset(array('state' => $address['state']));
    if ($this->payment_allowed($address['country'], $address['country_id'], $this->get_zone_id($address['country_id'], $zone_address['state'])) !== true) {
      return 'unavailable';
    }

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
      'email' => 255,
      'phone' => 32,
    );
    $data = array();
    $too_long = array();
    foreach ($fields as $key => $length) {
      $value = ((isset($collected[$key]) && is_string($collected[$key])) ? $collected[$key] : '');
      $data[$key] = trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', strip_tags($value)));
      // a cut address or email would reach the wrong place, so the customer enters it in the checkout
      if (mb_strlen($data[$key], 'UTF-8') > $length) {
        $too_long[] = $key;
      }
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
    if (count($missing) > 0 || count($too_long) > 0) {
      // field names only, the values are personal data
      $this->logger->log('klarna', 'express callback: address incomplete', array('fields' => array_values(array_unique($missing)), 'too_long' => $too_long));

      return false;
    }

    $suburb = trim($data['street_address2'].' '.$data['attention']);
    $street = $data['street_address'];
    if ($suburb != '' && (!defined('ACCOUNT_SUBURB') || ACCOUNT_SUBURB != 'true')) {
      // no suburb field in this shop, keep the second line in the street
      $street = $street.', '.$suburb;
      $suburb = '';
    }
    if (mb_strlen($street, 'UTF-8') > 64 || mb_strlen($suburb, 'UTF-8') > 32) {
      $this->logger->log('klarna', 'express callback: address incomplete', array('fields' => array(), 'too_long' => array('street_address')));

      return false;
    }

    return array(
      'firstname' => $data['given_name'],
      'lastname' => $data['family_name'],
      'company' => $data['organization_name'],
      'street_address' => $street,
      'suburb' => $suburb,
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

    // an aborted PayPal express would keep its payment list and win on the payment page
    unset($_SESSION['paypal']);

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
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_SHORT_CHECKOUT', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_BUTTON_CART', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_BUTTON_PRODUCT', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
  }


  function keys() {
    $keys = parent::keys();

    $keys[] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_CLIENT_ID';
    $keys[] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_SHORT_CHECKOUT';
    $keys[] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_BUTTON_CART';
    $keys[] = 'MODULE_PAYMENT_'.strtoupper($this->code).'_BUTTON_PRODUCT';

    return $keys;
  }

}
