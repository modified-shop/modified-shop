<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// include needed functions
if (!function_exists('xtc_date_short')) {
  require_once(DIR_FS_INC.'xtc_date_short.inc.php');
}

// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');


class paypalpui extends PayPalPaymentV2 {

  var $code;
  var $title;
  var $description;
  var $enabled;
  var $tmpOrders;
  var $tmpStatus;
  var $form_action_url;
  var $allowed_zones;
  var $order_status_pending;
  var $order_status_success;

  var $min_order = 5;
  var $max_order = 2500;

  function __construct() {
    global $order;

    $this->allowed_zones = array('DE');

    PayPalPaymentV2::__construct('paypalpui');

    if (PayPalPaymentBase::check_install() === true) {
      $this->tmpOrders = true;
      $this->tmpStatus = (($this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->form_action_url = '';
    }
  }


  function update_status() {
    global $order, $PHP_SELF;
    
    if (strpos(basename($PHP_SELF), 'checkout') !== false) {
      $this->enabled = false;
      if (isset($order->billing['country']['iso_code_2'])
          && in_array($order->billing['country']['iso_code_2'], $this->allowed_zones)
          && in_array($order->info['currency'], array('EUR'))
          && $_SESSION['customers_status']['customers_status_show_price_tax'] == 1
          && $order->content_type == 'physical'
          && $order->info['total'] >= $this->min_order
          && $order->info['total'] <= $this->max_order
          )
      {
        $this->enabled = true;
      }
    }

    parent::update_status();
  }


  function selection() {
    global $order, $messageStack;

    if (!isset($_SESSION['customer_dob'])) {
      $dob_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_CUSTOMERS."
                                  WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
      $dob = xtc_db_fetch_array($dob_query);
      $_SESSION['customer_dob'] = ((strtotime($dob['customers_dob']) !== false && strtotime($dob['customers_dob']) > 0) ? date('Y-m-d', strtotime($dob['customers_dob'])) : '');
    }

    $info = '';
    if ($messageStack->size('paypalpui') > 0) {
      $paypal_smarty = new Smarty();
      $paypal_smarty->assign('language', $_SESSION['language']);
      $paypal_smarty->caching = 0;

      $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/pui_error.html';
      if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/pui_error.html')) {
        $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/pui_error.html';
      }
      $paypal_smarty->assign('error_message', $messageStack->output('paypalpui'));
      $info = $paypal_smarty->fetch($tpl_file);
    }

    $selection = array(
      'id' => $this->code,
      'module' => $this->title,
      'description' => $info,
      'fields' => array()
    );

    $selection['fields'][] = array(
      'title' => MODULE_PAYMENT_PAYPALPUI_TEXT_DOB,
      'field' => xtc_draw_input_field('dob', (($_SESSION['customer_dob'] != '') ? xtc_date_short($_SESSION['customer_dob']) : ''))
    );

    $selection['fields'][] = array(
      'title' => MODULE_PAYMENT_PAYPALPUI_TEXT_TELEPHONE,
      'field' => xtc_draw_input_field('telephone', ((isset($order->customer['telephone'])) ? $order->customer['telephone'] : ''))
    );

    return $selection;
  }


  function javascript_validation() {
    $js = 'if (payment_value == "' . $this->code . '") {' . "\n" .
          '  var dob = document.getElementById("checkout_payment").dob.value;' . "\n" .
          '  var telephone = document.getElementById("checkout_payment").telephone.value;' . "\n" .
          '  if (dob == "") {' . "\n" .
          '    error_message = error_message + "' . JS_DOB_ERROR . '";' . "\n" .
          '    error = 1;' . "\n" .
          '  }' . "\n" .
          '  if (telephone == "") {' . "\n" .
          '    error_message = error_message + "' . JS_TELEPHONE_ERROR . '";' . "\n" .
          '    error = 1;' . "\n" .
          '  }' . "\n" .
          '}' . "\n";

    return $js;
  }


  function pre_confirmation_check() {
    global $messageStack;

    $dob = xtc_db_prepare_input($_POST['dob']);
    $telephone = xtc_db_prepare_input($_POST['telephone']);

    $error = false;
    $date = xtc_date_raw($dob);
    if (is_numeric($date) == false
        || strlen($date) != 8
        || checkdate(substr($date, 4, 2), substr($date, 6, 2), substr($date, 0, 4)) == false
        )
    {
      $error = true;
      $messageStack->add_session('paypalpui', ENTRY_DATE_OF_BIRTH_ERROR);
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;
      $messageStack->add_session('paypalpui', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    if ($error === false) {
      $sql_data_array = array(
        'customers_telephone' => $telephone,
        'customers_dob' => xtc_date_raw($dob),
      );
      $_SESSION['customer_dob'] = date('Y-m-d', strtotime($dob));
      xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".(int)$_SESSION['customer_id']."'");
    } else {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
  }


  function confirmation() {
    return array ('title' => $this->description);
  }


  function process_button() {
    global $order, $messageStack;

    if ($order->info['total'] < $this->min_order
        || $order->info['total'] > $this->max_order
        )
    {
      $messageStack->add_session('paypalpui', MODULE_PAYMENT_PAYPALPUI_MIN_MAX_AMOUNT);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }

    $_SESSION['paypal'] = array(
      'cartID' => $_SESSION['cart']->cartID,
      'OrderID' => '',
      'FraudNetID' => xtc_random_charcode(32),
      'PayerID' => ''
    );

    $paypal_smarty = new Smarty();
    $paypal_smarty->assign('language', $_SESSION['language']);
    $paypal_smarty->caching = 0;

    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/pui.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/pui.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/pui.html';
    }
    $process_button = $paypal_smarty->fetch($tpl_file);

    $error_url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL');

    $process_button .= '
      <script type="application/json" fncls="fnparams-dede7cc5-15fd-4c75-a9f4-36c430ee3a99">
        {
          "f": "'.$_SESSION['paypal']['FraudNetID'].'",
          "s": "modified2_checkout-page",
          "sandbox": '.(($this->get_config('PAYPAL_MODE') == 'sandbox') ? 'true' : 'false').'
        }
      </script>
    ';

    $process_button .= sprintf($this->get_js_sdk('true', false, false, true), '
      try {
        loadCustomScript({
          url: "https://c.paypal.com/da/r/fb.js"
        });
      } catch (error) {
        window.location.href = "'.$error_url.'";
      }
    ');

    return $process_button;
  }


  function payment_action() {
    global $order, $messageStack;

    $payment_source = array(
      'processing_instruction' => 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL',
      'payment_source' => array(
        'pay_upon_invoice' => array(
          'name' => array(
            'given_name' => $this->encode_utf8($order->billing['firstname']),
            'surname' => $this->encode_utf8($order->billing['lastname']),
          ),
          'email' => $this->encode_utf8($order->customer['email_address']),
          'birth_date' => $_SESSION['customer_dob'],
          'phone' => array(
            'national_number' => $this->encode_utf8(substr(preg_replace('/[^0-9]/', '', $order->customer['telephone']), 0, 14)),
            'country_code' => '49',
          ),
          'billing_address' => array(
            'address_line_1' => $this->encode_utf8($order->billing['street_address']),
            'address_line_2' => $this->encode_utf8($order->billing['suburb']),
            'admin_area_1' => $this->encode_utf8((isset($order->billing['state']) && $order->billing['state'] != '') ? xtc_get_zone_code($order->billing['country_id'], $order->billing['zone_id'], $order->billing['state']) : ''),
            'admin_area_2' => $this->encode_utf8($order->billing['city']),
            'postal_code' => $this->encode_utf8($order->billing['postcode']),
            'country_code' => $this->encode_utf8($order->billing['country']['iso_code_2'])
          ),
          'experience_context' => array(
            'customer_service_instructions' => array(
              sprintf(MODULE_PAYMENT_PAYPALPUI_TEXT_SERVICE, STORE_OWNER_EMAIL_ADDRESS)
            )
          )
        )
      )
    );

    if ($order->customer['company'] != '') {
      $payment_source['payment_source']['pay_upon_invoice']['billing_address']['address_line_2'] = $this->encode_utf8($order->customer['company']);
      if ($order->customer['suburb'] != '') {
        $payment_source['payment_source']['pay_upon_invoice']['billing_address']['address_line_1'] = $this->encode_utf8($order->customer['street_address'].', '.$order->customer['suburb']);
      }
    }

    $result = $this->CreateOrder($payment_source, true);

    if (is_array($result)) {
      $disable = false;
      if (isset($result['details'])
          && is_array($result['details'])
          && isset($result['details'][0])
          )
      {
        if (!in_array($result['details'][0]['issue'], array('PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED', 'BILLING_ADDRESS_INVALID', 'SHIPPING_ADDRESS_INVALID'))) {
          $disable = true;
        }
        $messageStack->add_session('paypalerror', (defined('MODULE_PAYMENT_PAYPALPUI_'.$result['details'][0]['issue']) ? constant('MODULE_PAYMENT_PAYPALPUI_'.$result['details'][0]['issue']) : MODULE_PAYMENT_PAYPALPUI_TEXT_ERROR_MESSAGE));
      }
    } else {
      $_SESSION['paypal']['OrderID'] = $result;
    }

    if ($_SESSION['paypal']['OrderID'] == '') {
      if ($disable === true) {
        $_SESSION['paypal_payment_forbidden'][] = $this->code;
      }
      $this->remove_order($_SESSION['tmp_oID']);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }

    $wait = 0;
    for ($i = 0; $i <= 10; $i ++) {
      $wait += $i * 0.5;
      sleep($wait);

      $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);
      if ($PayPalOrder->status == 'COMPLETED' || $PayPalOrder->status == 'PENDING_APPROVAL') {

        if ($PayPalOrder->status == 'COMPLETED') {
          $this->FinishOrderPui($_SESSION['tmp_oID'], $PayPalOrder);
        }

        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
      } elseif (in_array($PayPalOrder->status, array('DENIED', 'REVERSED'))) {
        break;
      }
    }

    // cancel pp order
    $_SESSION['paypal_payment_forbidden'][] = $this->code;
    $this->remove_order($_SESSION['tmp_oID']);
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
  }


  function before_send_order() {
    global $insert_id;
    
    $_SESSION['paypal']['send'] = 0;
    $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);
    if (is_object($PayPalOrder)
        && $PayPalOrder->status == 'COMPLETED'
        )
    {
      return false;
    }
    
    $_SESSION['paypal']['send'] = 1;
    return true;
  }


  function after_process() {
    global $insert_id;

    $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);

    $transaction_id = '';
    $status_id = $this->order_status_pending;
    
    if (is_object($PayPalOrder)) {
      if ($PayPalOrder->status == 'COMPLETED') {
        $status_id = $this->order_status_success;
      }

      if (isset($PayPalOrder->purchase_units[0]->payments)) {
        $transaction_id = $PayPalOrder->purchase_units[0]->payments->captures[0]->id;
      }

      if (isset($PayPalOrder->payer->payer_id)) {
        $_SESSION['paypal']['PayerID'] = $PayPalOrder->payer->payer_id;
      }
    }
    
    $sql_data_array = array(
      'orders_id' => $insert_id,
      'payment_id' => $_SESSION['paypal']['OrderID'],
      'payer_id' => $_SESSION['paypal']['PayerID'],
      'transaction_id' => $transaction_id,
      'send_order' => $_SESSION['paypal']['send'],
    );
    xtc_db_perform(TABLE_PAYPAL_PAYMENT, $sql_data_array);

    $this->update_order('Order ID: '.$_SESSION['paypal']['OrderID'], $status_id, $insert_id);
    unset($_SESSION['paypal']);
    unset($_SESSION['paypal_payment_forbidden']);
  }


  function success() {
    global $last_order;
    
    return $this->get_payment_instructions($last_order);
  }


  function get_error() {
    global $messageStack;
    
    $error = false;
    if ($messageStack->size('paypalerror') > 0) {
      $error = array(
        'error_message' => $messageStack->output('paypalerror')
      );
    }
    
    return $error;
  }


  function install() {
    parent::install();
  }


  function keys() {
    return array(
      'MODULE_PAYMENT_PAYPALPUI_STATUS',
      'MODULE_PAYMENT_PAYPALPUI_ALLOWED',
      'MODULE_PAYMENT_PAYPALPUI_ZONE',
      'MODULE_PAYMENT_PAYPALPUI_SORT_ORDER'
    );
  }

}
