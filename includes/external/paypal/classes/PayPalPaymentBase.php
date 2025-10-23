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
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalCommon.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_count_shipping_modules.inc.php');


class PayPalPaymentBase extends PayPalCommon {

  var $code;
  var $title;
  var $info;
  var $description;
  var $extended_description;
  var $sort_order;
  var $enabled;
  var $order_status_success;
  var $order_status_rejected;
  var $order_status_pending;
  var $order_status_capture;
  var $order_status_tmp;
  var $tmpOrders;
  var $tmpStatus;
  var $loglevel;
  var $LoggingManager;
  
  var $_check;
  var $_check_install;
  var $paypal_version;
  var $admin_access_array;
  var $intent;
  var $transaction_type;
  var $free_shipping;
  var $free_shipping_value_over;
  var $paypal_config;
  var $numberFormat;

  function __construct() {

  }


  function init($class) {
    global $order;

    $this->code = $class;
    $this->paypal_version = '1.105';

    $this->admin_access_array = array(
      'paypal_info',
      'paypal_config',
      'paypal_module',
      'paypal_profile',
      'paypal_webhook',
    );

    $this->title = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE') : '');
    if ((defined('DIR_WS_INSTALLER') || defined('RUN_MODE_ADMIN')) && defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ADMIN_TITLE')) {
      $this->title = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ADMIN_TITLE');
    }
    $this->info = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO') : '');
    $this->description = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION').((defined('RUN_MODE_ADMIN') && defined('MODULE_PAYMENT_'.strtoupper($this->code).'_LP')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_LP') : '') : '');
    $this->extended_description = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_EXTENDED_DESCRIPTION')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_EXTENDED_DESCRIPTION') : '');
  
    $this->sort_order = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER') : '');
    $this->enabled = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True') ? true : false);
  
    if ($this->check_install() === true) {
      $this->order_status_success = (($this->get_config('PAYPAL_ORDER_STATUS_SUCCESS_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_SUCCESS_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->order_status_rejected = (($this->get_config('PAYPAL_ORDER_STATUS_REJECTED_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_REJECTED_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->order_status_pending = (($this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->order_status_capture = (($this->get_config('PAYPAL_ORDER_STATUS_CAPTURED_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_CAPTURED_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->order_status_tmp = (($this->get_config('PAYPAL_ORDER_STATUS_TMP_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_TMP_ID') : DEFAULT_ORDERS_STATUS_ID);

      $this->tmpOrders = true;
      $this->tmpStatus = $this->order_status_tmp;

      $this->numberFormat = "%01.2f";
      $this->loglevel = $this->get_config('PAYPAL_LOG_LEVEL');
  
      $payment_sale = array(
        'paypalacdc',
        'paypalpui',
        'paypalsofort',
        'paypaltrustly',
        'paypalprzelewy',
        'paypalmybank',
        'paypalideal',
        'paypaleps',
        'paypalblik',
        'paypalbancontact',
        'paypalplus',
        'paypalpluslink',
      );
      $this->transaction_type = $this->get_config('PAYPAL_TRANSACTION_TYPE');
      if (in_array($this->code, $payment_sale)) {
        $this->transaction_type = 'sale';
      }

      $this->intent = 'CAPTURE';
      if ($this->transaction_type == 'authorize') {
        $this->intent = 'AUTHORIZE';
      }
    }
  
    if (is_object($order) && !defined('RUN_MODE_ADMIN')) {
      $this->update_status();
    }
    
    if ($this->check_install() && version_compare($this->paypal_version, $this->get_config('PAYPAL_VERSION', false), '>')) {
      $this->paypal_update();
    }
  }


  function update_status() {
    global $order;

    if (!isset($_SESSION['paypal_payment_forbidden'])) {
      $_SESSION['paypal_payment_forbidden'] = array();
    }
    
    if (in_array($this->code, $_SESSION['paypal_payment_forbidden'])) {
      $this->enabled = false;
    }
    
    if ($this->enabled == true
        && defined('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')
        && (int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE') > 0
        ) 
    {
      $check_flag = false;
      $check_query = xtc_db_query("SELECT zone_id 
                                     FROM ".TABLE_ZONES_TO_GEO_ZONES." 
                                    WHERE geo_zone_id = '".(int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')."' 
                                      AND zone_country_id = '".$order->billing['country']['id']."' 
                                 ORDER BY zone_id");
      while($check = xtc_db_fetch_array($check_query)) {
        if ($check['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
      }
      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
  }

  
  function is_enabled() {
    if ($this->enabled === true) {
      $unallowed_modules_string = $_SESSION['customers_status']['customers_status_payment_unallowed'];
      $unallowed_modules_string = preg_replace("'[\r\n\s]+'", '', $unallowed_modules_string);
      $unallowed_modules = explode(',', strtoupper($unallowed_modules_string));
      
      if (!in_array(strtoupper($this->code), $unallowed_modules)) {
        return true;
      }
    }
    
    return false;
  }
  
  
  function javascript_validation() {
    return false;
  }


  function selection() {    
    return array(
      'id' => $this->code, 
      'module' => $this->title, 
      'description' => $this->info,
    );
  }


  function payment_action() {
    return;
  }


  function pre_confirmation_check() {
    global $order, $smarty, $total_weight, $total_count, $free_shipping;
    
    if (!in_array($this->code, array('paypalcart', 'paypalexpress')) || MODULE_PAYMENT_PAYPALEXPRESS_SHORT_CHECKOUT == 'False') {
      return false;
    }

    if (isset($_SESSION['shipping'])) {
      $shipping = $_SESSION['shipping'];
      unset($_SESSION['shipping']);
    }
    
    $this->free_shipping = $this->calculate_total(3);
    
    if (isset($shipping)) {
      $_SESSION['shipping'] = $shipping;
    }
    
    // process the selected shipping method
    if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
      if ((isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_'))) {
        list ($module, $method) = explode('_', $_POST['shipping']);
        global ${$module};
      }

      $total_weight = $_SESSION['cart']->show_weight();
      $total_count = $_SESSION['cart']->count_contents();

      if ($order->delivery['country']['iso_code_2'] != '') {
        $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
      }

      if (isset($order->delivery['delivery_zone']) && $order->delivery['delivery_zone'] != '') {
        $_SESSION['delivery_zone'] = $order->delivery['delivery_zone'];
      }

      if ($order->billing['country']['iso_code_2'] != '') {
        $_SESSION['billing_zone'] = $order->billing['country']['iso_code_2'];
      }

      // load all enabled shipping modules
      require_once (DIR_WS_CLASSES.'shipping.php');
      $shipping_modules = new shipping;
            
      $redirect_link = xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(array('conditions_message')), 'SSL');
      require(DIR_WS_INCLUDES.'shipping_action.php');
    }
    
    $this->confirmation();
  }


  function confirmation() {
    global $order, $smarty, $xtPrice, $main, $messageStack, $total_weight, $total_count, $free_shipping;
    
    if (!in_array($this->code, array('paypalcart', 'paypalexpress')) || MODULE_PAYMENT_PAYPALEXPRESS_SHORT_CHECKOUT == 'False') {
      return false;
    }

    if ($order->delivery['country']['iso_code_2'] != '') {
      $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
    }

    if (isset($order->delivery['delivery_zone']) && $order->delivery['delivery_zone'] != '') {
      $_SESSION['delivery_zone'] = $order->delivery['delivery_zone'];
    }

    if ($order->billing['country']['iso_code_2'] != '') {
      $_SESSION['billing_zone'] = $order->billing['country']['iso_code_2'];
    }

    if (isset($_SESSION['shipping']) 
        && is_array($_SESSION['shipping']) 
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

    $allowed_zones = explode(',', strtoupper(preg_replace("'[\r\n\s]+'",'',constant('MODULE_PAYMENT_' . strtoupper($this->code) . '_ALLOWED'))));
    $allowed_zones = array_filter($allowed_zones);

    if (isset($_SESSION['billing_zone']) 
        && count($allowed_zones) > 0
        && in_array($_SESSION['billing_zone'], $allowed_zones) != true
        )
    {
      $messageStack->add_session('global', ERROR_NO_PAYMENT_MODULE_SELECTED);
      xtc_redirect(xtc_href_link((($this->code == 'paypalcart') ? FILENAME_CHECKOUT_SHIPPING : FILENAME_CHECKOUT_PAYMENT), '', 'SSL'));
    }
    
    $no_shipping = false;
    if ($order->content_type == 'virtual' || ($order->content_type == 'virtual_weight') || ($_SESSION['cart']->count_contents_virtual() == 0)) {
      $no_shipping = true;
    }

    $total_weight = $_SESSION['cart']->show_weight();
    $total_count = $_SESSION['cart']->count_contents();

    // load all enabled shipping modules
    require_once (DIR_WS_CLASSES . 'shipping.php');
    $shipping_modules = new shipping;

    // add unallowed payment / shipping
    if (defined('MODULE_EXCLUDE_PAYMENT_STATUS') && MODULE_EXCLUDE_PAYMENT_STATUS == 'True') {
      for ($i=1; $i<=MODULE_EXCLUDE_PAYMENT_NUMBER; $i++) {
        $payment_exclude = explode(',', constant('MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$i));
        
        if (in_array($this->code, $payment_exclude)) {
          $shipping_exclude = explode(',', constant('MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$i));
        
          for ($i=0, $n=count($shipping_modules->modules); $i<$n; $i++) {
            if (in_array(substr($shipping_modules->modules[$i], 0, -4), $shipping_exclude)) {
              unset($shipping_modules->modules[$i]);
            }
          }
        
        }
      }
    }
    
    $free_shipping = $this->free_shipping;
    $free_shipping_value_over = $this->free_shipping_value_over;
    
    // get all available shipping quotes
    $quotes = $shipping_modules->quote();
    //$quotes_count = $shipping_modules->count_modules();
    $quotes_count = xtc_count_shipping_modules();
    
    // if no shipping method has been selected, automatically select the cheapest method.
    // if the modules status was changed when none were available, to save on implementing
    // a javascript force-selection method, also automatically select the cheapest shipping
    // method if more than one module is now enabled
    if ($no_shipping == false
        && ((!isset($_SESSION['shipping']) && CHECK_CHEAPEST_SHIPPING_MODUL == 'true') 
            || (isset($_SESSION['shipping']) && ($_SESSION['shipping'] == false) && ($quotes_count == 1))
            )
        )
    {
      if ($free_shipping == true) {
        $_SESSION['shipping'] = array(
          'id' => 'free_free',
          'title' => FREE_SHIPPING_TITLE,
          'cost' => 0
        );
      } else {
        $_SESSION['shipping'] = $shipping_modules->cheapest();
      }
      $order = new order();
    }

    if ($no_shipping === true) $_SESSION['shipping'] = false;

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
          } elseif ($quotes[$i]['id'] == 'selfpickup') {
            $quotes_array = array_merge($quotes_array, $shipping_modules->quote($quotes[$i]['id'], $quotes[$i]['methods'][0]['id']));
          }
        }
        $quotes = $quotes_array;
      }
    }

    // build shipping block
    require(DIR_WS_INCLUDES.'shipping_block.php');
    
    if ($no_shipping === false) {
      $module_smarty->assign('FORM_SHIPPING_ACTION', xtc_draw_form('checkout_shipping', xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(), 'SSL')).xtc_draw_hidden_field('action', 'process'));
    
      $shipping_found = false;
      for ($i = 0, $n = sizeof($quotes); $i < $n; $i ++) {
        if (isset($quotes[$i]['methods'])
            && is_array($quotes[$i]['methods'])
            )
        {
          for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j ++) {
            if (isset($_SESSION['shipping']) 
                && is_array($_SESSION['shipping']) 
                && array_key_exists('id', $_SESSION['shipping'])
                && $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']
                )
            {
              $shipping_found = true;
              break;
            }
          }
        }
      }
      if ($shipping_found === false) {
        $module_smarty->assign('shipping_message', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
        $module_smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
      } elseif ($quotes_count > 1) {
        $module_smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
      }
      $module_smarty->assign('FORM_END', '</form>');
    
      if ($no_shipping === false) {
        $module_smarty->assign('SHIPPING_BLOCK', $shipping_block);
      }
      
      if ($quotes_count == 0) {
        $_SESSION['shipping'] = '';
      }
      
      $module_smarty->assign('language', $_SESSION['language']);
      $module_smarty->caching = 0;

      $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/shipping_block.html';
      if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/shipping_block.html')) {
        $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/shipping_block.html';
      }
      $shipping_method = $module_smarty->fetch($tpl_file);
    
      $smarty->assign('SHIPPING_METHOD', $shipping_method);
    }
    $smarty->assign('SHIPPING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, xtc_get_all_get_params(), 'SSL'));
    $smarty->assign('BILLING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, xtc_get_all_get_params(), 'SSL'));

    $smarty->clear_assign('SHIPPING_EDIT');
    $smarty->clear_assign('PAYMENT_EDIT');
    //$smarty->clear_assign('PRODUCTS_EDIT');
  }


  function process_button() {
    global $smarty, $main, $messageStack;
    
    if (!in_array($this->code, array('paypalcart', 'paypalexpress')) || MODULE_PAYMENT_PAYPALEXPRESS_SHORT_CHECKOUT == 'False') {
      return false;
    }

    $module_smarty = new Smarty();
    
    //check if display conditions on checkout page is true
    if (DISPLAY_REVOCATION_ON_CHECKOUT == 'true') {
      //revocation  
      $shop_content_data = $main->getContentData(REVOCATION_ID);
      $module_smarty->assign('REVOCATION', $shop_content_data['content_text']);
      $module_smarty->assign('REVOCATION_TITLE', $shop_content_data['content_heading']);
      $module_smarty->assign('REVOCATION_LINK', $main->getContentLink(REVOCATION_ID, MORE_INFO, 'SSL'));
      //agb
      $shop_content_data = $main->getContentData(3);
      $module_smarty->assign('AGB_TITLE', $shop_content_data['content_heading']);
      $module_smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));
      $module_smarty->assign('TEXT_AGB_CHECKOUT', sprintf(TEXT_AGB_CHECKOUT, $main->getContentLink(3, MORE_INFO,'SSL'), $main->getContentLink(REVOCATION_ID, MORE_INFO,'SSL'), $main->getContentLink(2, MORE_INFO,'SSL')));
      //privacy
      $shop_content_data = $main->getContentData(2);
      $module_smarty->assign('PRIVACY', $shop_content_data['content_heading']);
      $module_smarty->assign('PRIVACY_TITLE', $shop_content_data['content_heading']);
      $module_smarty->assign('PRIVACY_LINK', $main->getContentLink(2, MORE_INFO,'SSL'));
    }

    //check if display conditions on checkout page is true
    if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
      $shop_content_data = $main->getContentData(3);
      $module_smarty->assign('AGB', '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>');
      $module_smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));
      if ((defined('SIGN_CONDITIONS_ON_CHECKOUT') && SIGN_CONDITIONS_ON_CHECKOUT == 'true') || (!defined('SIGN_CONDITIONS_ON_CHECKOUT') && DISPLAY_CONDITIONS_ON_CHECKOUT == 'true')) {
        $module_smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" id="conditions"'.(isset($_GET['step']) && $_GET['step'] == 'step2' ? ' checked="checked"' : '').' />');
      }
    }

    if (defined('DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT')
        && DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT == 'true'
        && ($_SESSION['cart']->content_type == 'virtual'
            || $_SESSION['cart']->content_type == 'mixed')
        )
    {
      $shop_content_data = $main->getContentData(REVOCATION_ID);
      $module_smarty->assign('REVOCATION', '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>');
      $module_smarty->assign('REVOCATION_LINK', $main->getContentLink(REVOCATION_ID, MORE_INFO,'SSL'));
      $module_smarty->assign('REVOCATION_checkbox', '<input type="checkbox" value="revocation" name="revocation" id="revocation"'.(isset($_GET['step']) && $_GET['step'] == 'step2' ? ' checked="checked"' : '').' />');
    }

    if (defined('DISPLAY_PRIVACY_ON_CHECKOUT') && DISPLAY_PRIVACY_ON_CHECKOUT == 'true') {
      $shop_content_data = $main->getContentData(2);
      $module_smarty->assign('PRIVACY', '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>');
      $module_smarty->assign('PRIVACY_LINK', $main->getContentLink(2, MORE_INFO,'SSL'));
      if (!defined('DISPLAY_PRIVACY_CHECK') || DISPLAY_PRIVACY_CHECK == 'true') {
        $module_smarty->assign('PRIVACY_checkbox', '<input type="checkbox" value="privacy" name="privacy" id="privacy"'.(isset($_GET['step']) && $_GET['step'] == 'step2' ? ' checked="checked"' : '').' />');
      }
    }

    $module_smarty->assign('COMMENTS', xtc_draw_textarea_field('comments', 'soft', '60', '5', isset($_SESSION['comments'])?$_SESSION['comments']:'') . xtc_draw_hidden_field('comments_added', 'YES'));
    $module_smarty->assign('ADR_checkbox', '<input type="checkbox" value="address" name="check_address" id="address" />');

    if ($messageStack->size('checkout_confirmation') > 0) {
      $smarty->assign('error_message', $messageStack->output('checkout_confirmation'));
    } elseif (isset($_SESSION['paypal_express_new_customer'])
              && !isset($_SESSION['paypal_express_new_customer_note'])
              )
    {
      $smarty->assign('error_message', TEXT_PAYPAL_CART_ACCOUNT_CREATED);
      $_SESSION['paypal_express_new_customer_note'] = 'true';
    }

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->caching = 0;
    
    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/comments_block.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/comments_block.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/comments_block.html';
    }
    $process_button = $module_smarty->fetch($tpl_file);

    return $process_button;
  }


  function before_process() {
    global $messageStack, $order;

    if (!in_array($this->code, array('paypalcart', 'paypalexpress')) || MODULE_PAYMENT_PAYPALEXPRESS_SHORT_CHECKOUT == 'False' || isset($_SESSION['tmp_oID'])) {
      return false;
    }
        
    if (isset($_SESSION['payment']) 
        && $_SESSION['payment'] == $this->code
        && !isset($_SESSION['paypal']['process'])
        )
    {
      if (isset($_SESSION['paypal']['paymentId'])
          || isset($_SESSION['paypal']['OrderID'])
          )
      {
        $error = false;
        if ($_POST['comments_added'] != '') {
          $_SESSION['comments'] = xtc_db_prepare_input($_POST['comments']);
        }
        if (((defined('SIGN_CONDITIONS_ON_CHECKOUT') && SIGN_CONDITIONS_ON_CHECKOUT == 'true')
             || (!defined('SIGN_CONDITIONS_ON_CHECKOUT') && DISPLAY_CONDITIONS_ON_CHECKOUT == 'true')
             ) && (!isset($_POST['conditions']) || $_POST['conditions'] != 'conditions')
            )
        {
          $error = true;
          $messageStack->add_session('checkout_confirmation', str_replace('\n', '', ERROR_CONDITIONS_NOT_ACCEPTED));
        }
        if (!isset($_POST['check_address']) || $_POST['check_address'] != 'address') {
          $error = true;
          $messageStack->add_session('checkout_confirmation', str_replace('\n', '', ERROR_ADDRESS_NOT_ACCEPTED));
        }

        $no_shipping = false;
        if ($order->content_type == 'virtual' || ($order->content_type == 'virtual_weight') || ($_SESSION['cart']->count_contents_virtual() == 0)) {
          $no_shipping = true;
        }
        if (!isset($_SESSION['shipping']) 
            || ($_SESSION['shipping'] !== false && !is_array($_SESSION['shipping']))
            || ($no_shipping === false && $_SESSION['shipping'] === false)
            ) 
        {
          $error = true;
          $messageStack->add_session('checkout_confirmation', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
        }
        if (defined('DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT')
            && DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT == 'true'
            && ($_SESSION['cart']->content_type == 'virtual'
                || $_SESSION['cart']->content_type == 'mixed'
                )
            && (!isset($_POST['revocation']) || $_POST['revocation'] != 'revocation')
            )
        {
          $error = true;
          $messageStack->add_session('checkout_confirmation', str_replace('\n', '', ERROR_REVOCATION_NOT_ACCEPTED));
        }
        if (defined('DISPLAY_PRIVACY_ON_CHECKOUT') 
            && DISPLAY_PRIVACY_ON_CHECKOUT == 'true' 
            && (!defined('DISPLAY_PRIVACY_CHECK') || DISPLAY_PRIVACY_CHECK == 'true')
            && (!isset($_POST['privacy']) || $_POST['privacy'] != 'privacy')
            )
        {
          $error = true;
          $messageStack->add_session('checkout_confirmation', str_replace('\n', '', ERROR_PRIVACY_NOTICE_NOT_ACCEPTED));
        }
        
        if ($error === true) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(array('conditions_message')).'conditions=true', 'SSL', true, false));
        }
      }

      if ($this->code == 'paypalexpress') {
        $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);
        
        if (isset($PayPalOrder->status) && !in_array($PayPalOrder->status, array('COMPLETED', 'APPROVED'))) {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
        }
      }
    } elseif (isset($_SESSION['paypal']['process'])) {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }
    
    $_SESSION['paypal']['process'] = true;
  }


  function before_send_order() {
    return false;
  }


  function after_process() {
    global $insert_id;

    $check_query = xtc_db_query("SELECT orders_status
                                   FROM ".TABLE_ORDERS." 
                                  WHERE orders_id = '".(int)$insert_id."'");
    $check = xtc_db_fetch_array($check_query);
  
    if ($check['orders_status'] != $this->order_status_pending) {
      $this->update_order('', $this->order_status_pending, $insert_id);    
    }
    unset($_SESSION['paypal']);
  }


  function success() {
    global $last_order;
  
    if (!isset($last_order) || $last_order == '') {
      return;
    }
    
    return $this->get_payment_instructions($last_order);
  }


  function set_number_format($currency) {
    $check_query = xtDBquery("SELECT *
                                FROM ".TABLE_CURRENCIES."
                               WHERE UPPER(code) = '".xtc_db_input(strtoupper($currency))."'");
    if (xtc_db_num_rows($check_query, true) > 0) {
      $check = xtc_db_fetch_array($check_query, true);
      $this->numberFormat = sprintf("%%01.%df", (int)$check['decimal_places']);
    }                          
  }
  
  
  function get_payment_instructions($orders_id) {
    // include needed functions
    if (!function_exists('xtc_date_short')) {
      require_once(DIR_FS_INC.'xtc_date_short.inc.php');
    }

    $payment_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_PAYPAL_INSTRUCTIONS."
                                    WHERE orders_id = '".(int)$orders_id."'");
    if (xtc_db_num_rows($payment_query) > 0) {
      $payment = xtc_db_fetch_array($payment_query);
      
      $this->set_number_format($payment['currency']);
      
      $payment['amount'] = sprintf($this->numberFormat, round($payment['amount'], 2));
      $payment['date'] = xtc_date_short($payment['date']);

      $fields = array(
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_AMOUNT,
          'field' => $payment['amount'].' '.$payment['currency'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_REFERENCE,
          'field' => $payment['reference'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_PAYDATE,
          'field' => $payment['date'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_ACCOUNT,
          'field' => $payment['name'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_HOLDER,
          'field' => $payment['holder'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_IBAN,
          'field' => $payment['iban'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_BIC,
          'field' => $payment['bic'],
        ),
      );

      $title = sprintf(TEXT_PAYPAL_INSTRUCTIONS_CHECKOUT, $payment['amount'].' '.$payment['currency'], $payment['date']);
      if ($payment['date'] == '') {
        unset($fields[2]);
        $fields = array_values($fields);
        $title = sprintf(TEXT_PAYPAL_INSTRUCTIONS_CHECKOUT_SHORT, $payment['amount'].' '.$payment['currency']);
      }

      $success = array(
        array (
          'title' => $title,
          'class' => $this->code,
          'fields' => $fields
        ),
      );

      return $success;
    }

    return false;
  }


  function save_payment_instructions($orders_id) {
    $payment = $this->get_order_details($orders_id);
  
    if (isset($payment['instruction'])) {
      
      $sql_data_array = array(
        'orders_id' => $orders_id,
        'method' => $payment['instruction']['type'],
        'amount' => $payment['instruction']['amount']['total'],
        'currency' => $payment['instruction']['amount']['currency'],
        'reference' => $payment['instruction']['reference'],
        'date' => date('Y-m-d', strtotime($payment['instruction']['date'])),
        'name' => $payment['instruction']['bank']['name'],
        'holder' => $payment['instruction']['bank']['holder'],
        'iban' => $payment['instruction']['bank']['iban'],
        'bic' => $payment['instruction']['bank']['bic'],
      );
    
      xtc_db_perform(TABLE_PAYPAL_INSTRUCTIONS, $sql_data_array);
    }
  }
  
  
  function admin_order($oID) {
    return false;
  }


  function get_error() {
    $error = false;
    if (isset($_GET['payment_error']) && $_GET['payment_error'] == $this->code) {
      $message = decode_htmlentities(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_MESSAGE'));
      if (isset($_SESSION['paypal_payment_error'])) {
        if (defined('TEXT_PAYPAL_'.$_SESSION['paypal_payment_error'].'_ERROR')) {
          $message = decode_htmlentities(constant('TEXT_PAYPAL_'.$_SESSION['paypal_payment_error'].'_ERROR'));
        }
        unset($_SESSION['paypal_payment_error']);
      }
      $error = array(
        'title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_HEADING'),
        'error' => $message
      );
    }
    
    return $error;
  }


  function output_error() {
    return false;
  }


  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value 
                                       FROM ".TABLE_CONFIGURATION." 
                                      WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($this->code)."_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }


  function check_install() {
    if (!isset($this->_check_install)) {
      if (defined('MODULE_PAYMENT_PAYPAL_SECRET')) {
        $this->_check_install = true;
      } else {
        $this->_check_install = false;
        $check_query = xtc_db_query("SHOW TABLES LIKE '".TABLE_PAYPAL_CONFIG."'");
        if (xtc_db_num_rows($check_query) > 0) {
          $this->_check_install = true;
        }
      }
    }
    return $this->_check_install;
  }
    
  
  function checkout_button() {
    global $PHP_SELF;
  
    if ($this->enabled === true
        && $_SESSION['cart']->show_total() > 0
        && (!isset($_SESSION['allow_checkout']) || $_SESSION['allow_checkout'] == 'true')
        ) 
    {
      $unallowed_modules = explode(',', $_SESSION['customers_status']['customers_status_payment_unallowed']);
      if (!in_array($this->code, $unallowed_modules)) {
        $image = ((is_file(DIR_FS_CATALOG.DIR_WS_ICONS.'epaypal_'.strtolower($_SESSION['language_code']).'.gif')) ? 'epaypal_'.strtolower($_SESSION['language_code']).'.gif' : 'epaypal_en.gif');
        $image = xtc_image_button(DIR_WS_ICONS.$image, '', 'id="paypalcartbutton"');
        $checkout_button = '<a href="'.xtc_href_link(basename($PHP_SELF), 'action=paypal_cart_checkout').'">'.$image.'</a>';

        return $checkout_button;
      }
    }
  }


  function product_checkout_button() {    
    if ($this->enabled === true) {
      $unallowed_modules = explode(',', $_SESSION['customers_status']['customers_status_payment_unallowed']);
      if (!in_array($this->code, $unallowed_modules)) {
        $image = ((is_file(DIR_FS_CATALOG.DIR_WS_ICONS.'epaypal_'.strtolower($_SESSION['language_code']).'.gif')) ? 'epaypal_'.strtolower($_SESSION['language_code']).'.gif' : 'epaypal_en.gif');
        $checkout_button = xtc_image_submit(DIR_WS_BASE.DIR_WS_ICONS.$image, IMAGE_BUTTON_IN_CART, 'id="paypalcartexpress" name="paypalcartexpress"');

        return $checkout_button;
      }
    }
  }


  function create_paypal_link($orders_id = '', $cleanlink = false) {
    global $last_order, $PHP_SELF;
  
    if ($orders_id == '') {
      $orders_id = $last_order;
    }
      
    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_PAYPAL_PAYMENT."
                                  WHERE orders_id = '".(int)$orders_id."'");
  
    if (xtc_db_num_rows($check_query) < 1) {
      require_once (DIR_WS_CLASSES . 'order.php');
      $order = new order($orders_id);
      $hash = md5($order->customer['email_address']);
      if (defined('RUN_MODE_ADMIN')) {
        $link = xtc_catalog_href_link('callback/paypal/'.$this->code.'.php', 'oID='.$orders_id.'&key='.$hash, 'SSL');
      } else {
        $link = xtc_href_link('callback/paypal/'.$this->code.'.php', 'oID='.$orders_id.'&key='.$hash, 'SSL');
      }
    
      if ($cleanlink === true) {
        return $link;
      }
      
      $image = ((is_file(DIR_FS_CATALOG.DIR_WS_ICONS.'epaypal_'.strtolower($_SESSION['language_code']).'.gif')) ? 'epaypal_'.strtolower($_SESSION['language_code']).'.gif' : 'epaypal_en.gif');
      if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS) {
        $image = xtc_image_button(DIR_WS_ICONS.$image, '', 'id="paypalcartbutton"');
      } else {
        $image = '<img src="'.((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.DIR_WS_ICONS.$image.'" id="paypalcartbutton" />';
      }
      $checkout_button = '<a href="'.$link.'">'.$image.'</a>';

      return $checkout_button;
    }
  }

  
  function get_js_sdk($commit = 'true', $client_token = false, $user_token = false, $custom = false) {
    return get_paypal_js_sdk($this->get_config('PAYPAL_CLIENT_ID_'.strtoupper($this->get_config('PAYPAL_MODE'))), $_SESSION['currency'], $this->intent, $commit, $client_token, $user_token, $custom);
  }
  
  
  function update_order($comment, $orders_status, $orders_id, $notified = 0) {
    $order_history_data = array(
      'orders_id' => (int)$orders_id,
      'orders_status_id' => (int)$orders_status,
      'date_added' => 'now()',
      'customer_notified' => $notified,
      'comments' => $comment,
    );
    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_history_data);
  
    xtc_db_query("UPDATE ".TABLE_ORDERS."
                     SET orders_status = '".(int)$orders_status."', 
                         last_modified = now() 
                   WHERE orders_id = '".(int)$orders_id."'");   
  }


  function remove_order($orders_id) {
    $check_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ORDERS." 
                                  WHERE orders_id = '".(int)$orders_id."'");
    if (xtc_db_num_rows($check_query) > 0) {
      $check = xtc_db_fetch_array($check_query);
      if ($_SESSION['customer_id'] == $check['customers_id']) {
        if ($this->get_config('PAYPAL_REMOVE_ORDER_TMP') == '1') {
          require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
          xtc_remove_order((int)$orders_id, ((STOCK_LIMITED == 'true') ? 'on' : false));
          $this->LoggingManager->log('INFO', 'Remove Order ID: '.$orders_id);
        } else {
          require_once(DIR_FS_INC.'xtc_restock_order.inc.php');
          xtc_restock_order((int)$orders_id, true);
          $this->LoggingManager->log('INFO', 'Restock Order ID: '.$orders_id);
        }
      }
    }
  }


  function get_shipping_data() {
    global $order, $xtPrice, $free_shipping, $total_weight, $total_count;
    
    if ($_SESSION['cart']->count_contents() > 0
        && $_SESSION['cart']->get_content_type() != 'virtual'
        )
    {
      require_once(DIR_WS_CLASSES.'shipping.php');
      require_once(DIR_WS_CLASSES.'product.php');
      require_once(DIR_WS_CLASSES.'order.php');
      require_once(DIR_FS_INC.'xtc_get_countries.inc.php');
    
      $order = new order();
    
      $countries_id = isset($_SESSION['customer_country_id']) ? $_SESSION['customer_country_id'] : STORE_COUNTRY;
      if (isset($_SESSION['country'])) {
        $countries_id = $_SESSION['country'];
      }
    
      $country = xtc_get_countriesList($countries_id, true);
    
      $order->delivery['country']['iso_code_2'] = $country['countries_iso_code_2'];
      $order->delivery['country']['title'] = $country['countries_name'];
      $order->delivery['country']['id'] = $country['countries_id'];
      $order->delivery['country_id'] = $country['countries_id'];
      $order->delivery['zone_id'] = 0;
 
      $order->delivery['shipping'] = $order->delivery['country'];
      $order->delivery['shipping']['zone_id'] = $order->delivery['zone_id'];
   
      $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
      if (isset($order->delivery['delivery_zone']) && $order->delivery['delivery_zone'] != '') {
        $_SESSION['delivery_zone'] = $order->delivery['delivery_zone'];
      }
    
      $total_weight = $_SESSION['cart']->show_weight();
      $total_count = $_SESSION['cart']->count_contents();

      // load all enabled shipping modules
      $shipping_modules = new shipping();

      $free_shipping = false;
      if (xtc_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
        require_once (DIR_WS_CLASSES . 'order_total.php');
        $order_total_modules = new order_total();
        $order_total_modules->process();
      }

      $shipping_modules->quote();
      $shipping_data = $shipping_modules->cheapest();
      unset($_SESSION['delivery_zone']);
    
      if ($free_shipping === true) {
        $shipping_data = array(
          'cost' => 0,
          'total' => 0,
          'tax' => 0,
        );
      } elseif (is_array($shipping_data)) {
        $shipping_data['tax'] = 0;
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
            && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
            ) 
        {
          $module = substr($shipping_data['id'], 0, strpos($shipping_data['id'], '_'));
          if (is_object($GLOBALS[$module]) && property_exists($GLOBALS[$module], 'tax_class')) {
            $shipping_tax = xtc_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
            $shipping_data['tax'] = $xtPrice->xtcAddTax($shipping_data['cost'], $shipping_tax) - $shipping_data['cost'];
          }
        }      
      }
    
      return $shipping_data;
    }
  }


  function install() {
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_STATUS', 'True', '6', '1', NULL, now(), '', 'xtc_cfg_select_option(array(\'True\', \'False\'),' )");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_SORT_ORDER', '0', '6', '2', NULL, now(), '', '')");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ALLOWED', '', '6', '3', NULL, now(), '', '')");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ZONE', '0', '6', '4', NULL, now(), 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(')");
    if ($this->code == 'paypalexpress') {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_SHORT_CHECKOUT', 'True', '6', '1', NULL, now(), '', 'xtc_cfg_select_option(array(\'True\', \'False\'),' )");
    }
    
    // scheduled task
    if (defined('TABLE_SCHEDULED_TASKS')) {
      $scheduled_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_SCHEDULED_TASKS."
                                        WHERE tasks = 'paypal_tracking'");
      if (xtc_db_num_rows($scheduled_query) < 1) {
        xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '1', 'paypal_tracking')");
      }
    }
    
    if (!defined('MODULE_PAYMENT_PAYPAL_SECRET')) {
      $check_query = xtc_db_query("SELECT * 
                                     FROM ".TABLE_CONFIGURATION." 
                                    WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_SECRET'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_PAYPAL_SECRET', '".md5(uniqid())."', '6', '3', NULL, now(), '', '')");
      }
    }
    
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_PAYMENT." ( 
                    paypal_id int(11) NOT NULL auto_increment, 
                    orders_id int(11) NOT NULL default '0', 
                    payment_id varchar(64) NOT NULL default '', 
                    payer_id varchar(64) NOT NULL default '', 
                    transaction_id varchar(64) NOT NULL default '', 
                    send_order int(1) NOT NULL default '0', 
                    PRIMARY KEY (paypal_id), 
                    KEY idx_orders_id (orders_id),
                    KEY idx_payment_id (payment_id)
                  );");
  
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_CONFIG." (
                    config_id int(11) NOT NULL auto_increment, 
                    config_key varchar(128) NOT NULL,
                    config_value text NOT NULL,
                    PRIMARY KEY (config_id), 
                    KEY idx_config_key (config_key)
                  );");

    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_INSTRUCTIONS." (
                    paypal_instructions_id int(11) NOT NULL auto_increment, 
                    orders_id int(11) NOT NULL DEFAULT '0',
                    method varchar(64) NOT NULL,
                    amount decimal(15,4) DEFAULT NULL,
                    currency varchar(8) DEFAULT NULL,
                    reference varchar(128) DEFAULT NULL,
                    date date DEFAULT NULL,
                    name varchar(128) DEFAULT NULL,
                    holder varchar(128) DEFAULT NULL,
                    iban varchar(34) DEFAULT NULL,
                    bic varchar(11) DEFAULT NULL,
                    PRIMARY KEY (paypal_instructions_id),
                    KEY idx_orders_id (orders_id)
                  );");

    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_TRACKING." (
                   tracking_id int(11) NOT NULL AUTO_INCREMENT,
                   orders_id int(11) NOT NULL,
                   transaction_id varchar(64) NOT NULL,
                   tracking_number varchar(64) NOT NULL,
                   carrier varchar(16) NOT NULL,
                   trackers_id varchar(128) NOT NULL,
                   date_added datetime NOT NULL,
                   PRIMARY KEY (tracking_id),
                   KEY idx_orders_id (orders_id)
                 );");
  
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_VAULT." (
                   paypal_id int(11) NOT NULL AUTO_INCREMENT,
                   customers_id int(11) NOT NULL,
                   paypal_customers_id varchar(64) NOT NULL,
                   vault_id varchar(64) NOT NULL,
                   payment_source varchar(8) NOT NULL,
                   PRIMARY KEY (paypal_id),
                   KEY idx_customers_id (customers_id),
                   KEY idx_paypal_customers_id (paypal_customers_id)
                 );");

    $admin_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ADMIN_ACCESS."
                                  LIMIT 1");
    $admin = xtc_db_fetch_array($admin_query);
    foreach ($this->admin_access_array as $admin_access) {
      if (!isset($admin[$admin_access])) {
        xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD `".$admin_access."` INT(1) DEFAULT '0' NOT NULL");
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '9' WHERE customers_id = 'groups' LIMIT 1");        
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '1' LIMIT 1");        
        
        if (defined('RUN_MODE_ADMIN') && $_SESSION['customer_id'] > 1) {
          xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '".$_SESSION['customer_id']."' LIMIT 1") ;
        }
      }
    }
  
    $status_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_ORDERS_STATUS."
                                   LIMIT 1");
    $status = xtc_db_fetch_array($status_query);
    if (!isset($status['sort_order'])) {
      xtc_db_query("ALTER TABLE ".TABLE_ORDERS_STATUS." ADD `sort_order` int(11) NOT NULL DEFAULT '0'");
    }
  
    // check tabs
    if ($this->code == 'paypalplus') {
      if ($this->get_config('MODULE_PAYMENT_PAYPALPLUS_USE_TABS', false) == '') {
        $sql_data_array = array(
          'config_key' => 'MODULE_PAYMENT_PAYPALPLUS_USE_TABS',
          'config_value' => '1'
        );
        xtc_db_perform(TABLE_PAYPAL_CONFIG, $sql_data_array);
      }
    }

    // check express button
    if ($this->code == 'paypalcart') {
      if ($this->get_config('MODULE_PAYMENT_PAYPALCART_SHOW_PRODUCT', false) == '') {
        $sql_data_array = array(
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALCART_SHOW_PRODUCT',
            'config_value' => '1'
          ),
        );
        $this->save_config($sql_data_array);
      }
    }

    if ($this->code == 'paypalexpress') {
      if ($this->get_config('MODULE_PAYMENT_PAYPALEXPRESS_SAVE_PAYMENT', false) == '') {
        $sql_data_array = array(
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALEXPRESS_SAVE_PAYMENT',
            'config_value' => '1',
          ),
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALEXPRESS_SHOW_CART_BNPL',
            'config_value' => '1',
          ),
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALEXPRESS_SHOW_PRODUCT',
            'config_value' => '1',
          ),
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALEXPRESS_SHOW_PRODUCT_BNPL',
            'config_value' => '1',
          ),
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALEXPRESS_SHOW_BOX_CART',
            'config_value' => '1',
          ),
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALEXPRESS_SHOW_BOX_CART_BNPL',
            'config_value' => '1',
          ),
        );
        $this->save_config($sql_data_array);
      }
    }

    // check 3D secure
    if ($this->code == 'paypalacdc') {
      if ($this->get_config('MODULE_PAYMENT_PAYPALACDC_EXTEND_CARDS', false) == '') {
        $sql_data_array = array(
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALACDC_EXTEND_CARDS',
            'config_value' => '0'
          ),
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALACDC_SAVE_PAYMENT',
            'config_value' => '1',
          ),
        );
        $this->save_config($sql_data_array);
      }
    }

    // set buttons
    if ($this->code == 'paypal') {
      if ($this->get_config('PAYPAL_BUTTON_LAYOUT', false) == '') {
        $sql_data_array = array(
          array(
            'config_key' => 'PAYPAL_BUTTON_LAYOUT',
            'config_value' => 'horizontal',
          ),
          array(
            'config_key' => 'PAYPAL_BUTTON_SHAPE',
            'config_value' => 'rect',
          ),
          array(
            'config_key' => 'PAYPAL_BUTTON_PRIMARY_COLOR',
            'config_value' => 'gold',
          ),
          array(
            'config_key' => 'PAYPAL_BUTTON_SECONDARY_COLOR',
            'config_value' => 'blue',
          ),
          array(
            'config_key' => 'PAYPAL_BUTTON_HEIGHT',
            'config_value' => '35',
          ),
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPAL_SAVE_PAYMENT',
            'config_value' => '1',
          ),
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPAL_SHOW_CHECKOUT_BNPL',
            'config_value' => '1',
          ),
        );
        $this->save_config($sql_data_array);
      }
    }
  }


  function remove() {
    $check_query = xtc_db_query("SELECT configuration_key 
                                   FROM ".TABLE_CONFIGURATION." 
                                  WHERE configuration_key LIKE 'MODULE_PAYMENT_PAYPAL%_STATUS'");
    if (xtc_db_num_rows($check_query) == 1) {
      //xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_PAYMENT);
      //xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_CONFIG);
      //xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_INSTRUCTIONS);
      //xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_TRACKING);

      $admin_query = xtc_db_query("SELECT * 
                                     FROM ".TABLE_ADMIN_ACCESS."
                                    LIMIT 1");
      $admin = xtc_db_fetch_array($admin_query);
      foreach ($this->admin_access_array as $admin_access) {
        if ($admin_access != 'paypal_info' 
            && $admin_access != 'paypal_module' 
            && isset($admin[$admin_access])
            )
        {
          xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP COLUMN `".$admin_access."`");
        }
      }
      
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE_PAYMENT_PAYPAL_SECRET'");

      // scheduled task
      if (defined('TABLE_SCHEDULED_TASKS')) {
        xtc_db_query("DELETE FROM " . TABLE_SCHEDULED_TASKS . " WHERE tasks = 'paypal_tracking'");
      }
    }

    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE_PAYMENT_".strtoupper($this->code)."\_%' AND configuration_key != 'MODULE_PAYMENT_PAYPAL_SECRET'");
  }


  function status_install($stati = '') {

    // install order status
    if (!is_array($stati) 
        || (is_array($stati) && count($stati) < 1)
        )
    {
      $stati = array(
        'PAYPAL_INST_ORDER_STATUS_TMP_NAME' => 'PAYPAL_ORDER_STATUS_TMP_ID',
        'PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME' => 'PAYPAL_ORDER_STATUS_SUCCESS_ID',
        'PAYPAL_INST_ORDER_STATUS_PENDING_NAME' => 'PAYPAL_ORDER_STATUS_PENDING_ID',
        'PAYPAL_INST_ORDER_STATUS_CAPTURED_NAME' => 'PAYPAL_ORDER_STATUS_CAPTURED_ID',
        'PAYPAL_INST_ORDER_STATUS_REFUNDED_NAME' => 'PAYPAL_ORDER_STATUS_REFUNDED_ID',
        'PAYPAL_INST_ORDER_STATUS_REJECTED_NAME' => 'PAYPAL_ORDER_STATUS_REJECTED_ID',
      );
    }
    
    foreach($stati as $statusname => $statusid) {
      $languages_query = xtc_db_query("SELECT * 
                                         FROM " . TABLE_LANGUAGES . " 
                                     ORDER BY sort_order");
      while($languages = xtc_db_fetch_array($languages_query)) {
        if (file_exists(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal_config.php')) {
          include(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal_config.php');
        }
        if (isset(${$statusname}) && ${$statusname} != '') {
          $check_query = xtc_db_query("SELECT orders_status_id 
                                         FROM " . TABLE_ORDERS_STATUS . " 
                                        WHERE orders_status_name = '" .xtc_db_input(${$statusname}). "' 
                                          AND language_id = '".(int)$languages['languages_id']."' 
                                        LIMIT 1");
          $status = xtc_db_fetch_array($check_query);
          if (xtc_db_num_rows($check_query) < 1 || (isset(${$statusid}) && $status['orders_status_id'] != ${$statusid}) ) {
            if (!isset(${$statusid})) {
              $status_query = xtc_db_query("SELECT max(orders_status_id) as status_id FROM " . TABLE_ORDERS_STATUS);
              $status = xtc_db_fetch_array($status_query);
              ${$statusid} = $status['status_id'] + 1;
            }
            $check_query = xtc_db_query("SELECT orders_status_id 
                                           FROM " . TABLE_ORDERS_STATUS . " 
                                          WHERE orders_status_id = '".(int)${$statusid} ."' 
                                            AND language_id='".(int)$languages['languages_id']."'");
            if (xtc_db_num_rows($check_query) < 1) {
              $sql_data_array = array(
                'orders_status_id' => (int)${$statusid},
                'language_id' => (int)$languages['languages_id'],
                'orders_status_name' => ${$statusname},
              );
              xtc_db_perform(TABLE_ORDERS_STATUS, $sql_data_array);
              $sql_data_array = array(
                array(
                  'config_key' => $statusid,
                  'config_value' => (int)${$statusid},
                )
              );
              $this->save_config($sql_data_array);
            }
          } else {
            ${$statusid} = $status['orders_status_id'];
          }
        }
      }
    }
  }
    
  
  function paypal_update() {
    $table_array = array(
      array('column' => 'transaction_id', 'default' => "varchar(64) NOT NULL DEFAULT ''"),
      array('column' => 'send_order', 'default' => "int(1) NOT NULL default '0'"),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_PAYMENT." LIKE '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_PAYMENT." ADD ".$table['column']." ".$table['default']."");
      }
    }
    
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_INSTRUCTIONS." (
                    paypal_instructions_id int(11) NOT NULL auto_increment, 
                    orders_id int(11) NOT NULL DEFAULT '0',
                    method varchar(64) NOT NULL,
                    amount decimal(15,4) DEFAULT NULL,
                    currency varchar(8) DEFAULT NULL,
                    reference varchar(128) DEFAULT NULL,
                    date date DEFAULT NULL,
                    name varchar(128) DEFAULT NULL,
                    holder varchar(128) DEFAULT NULL,
                    iban varchar(34) DEFAULT NULL,
                    bic varchar(11) DEFAULT NULL,
                    PRIMARY KEY (paypal_instructions_id),
                    KEY idx_orders_id (orders_id)
                  );");
    
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_TRACKING." (
                   tracking_id int(11) NOT NULL AUTO_INCREMENT,
                   orders_id int(11) NOT NULL,
                   transaction_id varchar(64) NOT NULL,
                   tracking_number varchar(64) NOT NULL,
                   carrier varchar(16) NOT NULL,
                   trackers_id varchar(128) NOT NULL,
                   date_added datetime NOT NULL,
                   PRIMARY KEY (tracking_id),
                   KEY idx_orders_id (orders_id)
                 );");

    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_VAULT." (
                   paypal_id int(11) NOT NULL AUTO_INCREMENT,
                   customers_id int(11) NOT NULL,
                   paypal_customers_id varchar(64) NOT NULL,
                   vault_id varchar(64) NOT NULL,
                   payment_source varchar(8) NOT NULL,
                   PRIMARY KEY (paypal_id),
                   KEY idx_customers_id (customers_id),
                   KEY idx_paypal_customers_id (paypal_customers_id)
                 );");

    $table_array = array(
      array('column' => 'method', 'default' => "varchar(64) NOT NULL AFTER orders_id"),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_INSTRUCTIONS." LIKE '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_INSTRUCTIONS." ADD ".$table['column']." ".$table['default']."");
      }
    }

    $table_array = array(
      array('column' => 'trackers_id', 'default' => "varchar(128) NOT NULL AFTER carrier"),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_TRACKING." LIKE '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_TRACKING." ADD ".$table['column']." ".$table['default']."");
      }
    }

    // add new column
    $admin_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ADMIN_ACCESS."
                                  LIMIT 1");
    $admin = xtc_db_fetch_array($admin_query);
    foreach ($this->admin_access_array as $admin_access) {
      if (!isset($admin[$admin_access])) {
        xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD `".$admin_access."` INT(1) DEFAULT '0' NOT NULL");
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '9' WHERE customers_id = 'groups' LIMIT 1");        
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '1' LIMIT 1");        
        if (defined('RUN_MODE_ADMIN') && $_SESSION['customer_id'] > 1) {
          xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '".$_SESSION['customer_id']."' LIMIT 1") ;
        }
      }
    }

    // drop old column
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ADMIN_ACCESS." LIKE 'paypal_payment'");
    if (xtc_db_num_rows($check_query) == 1) {
      xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP `paypal_payment`");
    }
    
    $sql_data_array = array(
      array(
        'config_key' => 'PAYPAL_VERSION',
        'config_value' => $this->paypal_version,
      )
    );
    $this->save_config($sql_data_array);
    
    if ($this->get_config('PAYPAL_INSTALLMENT_BANNER_DISPLAY', false) == '') {
      $sql_data_array = array(
        array(
          'config_key' => 'PAYPAL_INSTALLMENT_BANNER_DISPLAY',
          'config_value' => '1',
        )
      );
      $this->save_config($sql_data_array);
    }

    if ($this->get_config('PAYPAL_INSTALLMENT_BANNER_COLOR', false) == '') {
      $sql_data_array = array(
        array(
          'config_key' => 'PAYPAL_INSTALLMENT_BANNER_COLOR',
          'config_value' => 'white',
        )
      );
      $this->save_config($sql_data_array);
    }

    if ($this->get_config('PAYPAL_REMOVE_ORDER_TMP', false) == '') {
      $sql_data_array = array(
        array(
          'config_key' => 'PAYPAL_REMOVE_ORDER_TMP',
          'config_value' => '1',
        )
      );
      $this->save_config($sql_data_array);
    }
    
    if (!defined('MODULE_PAYMENT_PAYPAL_SECRET')) {
      $check_query = xtc_db_query("SELECT * 
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key LIKE 'MODULE_PAYMENT_PAYPAL%_STATUS'"); 
      if (xtc_db_num_rows($check_query) > 0) {
        $check_query = xtc_db_query("SELECT * 
                                       FROM ".TABLE_CONFIGURATION." 
                                      WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_SECRET'");
        if (xtc_db_num_rows($check_query) < 1) {
          xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_PAYPAL_SECRET', '".md5(uniqid())."', '6', '3', NULL, now(), '', '')");
        }      
      }
    }
 
    if ((defined('MODULE_PAYMENT_PAYPALEXPRESS_STATUS') 
         || defined('MODULE_PAYMENT_PAYPALCART_STATUS')
         ) && !defined('MODULE_PAYMENT_PAYPALEXPRESS_SHORT_CHECKOUT')
        )
    {
      $check_query = xtc_db_query("SELECT * 
                                     FROM ".TABLE_CONFIGURATION." 
                                    WHERE configuration_key = 'MODULE_PAYMENT_PAYPALEXPRESS_SHORT_CHECKOUT'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_PAYPALEXPRESS_SHORT_CHECKOUT', 'True', '6', '1', NULL, now(), '', 'xtc_cfg_select_option(array(\'True\', \'False\'),' )");
      }
    }
   
    //check tables
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_CONFIG." LIKE 'config_id'");
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_CONFIG." ADD `config_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    }
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_INSTRUCTIONS." LIKE 'paypal_inctructions_id'");
    if (xtc_db_num_rows($check_query) == 1) {
      xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_INSTRUCTIONS." CHANGE `paypal_inctructions_id` `paypal_instructions_id` INT(11) NOT NULL AUTO_INCREMENT");
    }
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_INSTRUCTIONS." LIKE 'paypal_instructions_id'");
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_INSTRUCTIONS." ADD `paypal_instructions_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    }

    // remove ipn
    xtc_db_query("DROP TABLE IF EXISTS `paypal_ipn`");
    if (is_file(DIR_FS_CATALOG.'callback/paypal/paypalipn.php')) {
      unlink(DIR_FS_CATALOG.'callback/paypal/paypalipn.php');
    }
    
    // check scheduled tasks
    if (defined('TABLE_SCHEDULED_TASKS')) {
      $check_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_SCHEDULED_TASKS."
                                    WHERE tasks = 'paypal_tracking'");
      if (xtc_db_num_rows($check_query) < 1) {                      
        xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'h',  '1', 'paypal_tracking')");
      }
    }
    
    // reset zones
    $fixed_zones_modules_array = array(
      'paypalacdc',
      'paypalpui',
      'paypalexpress',
      'paypalsofort',
      'paypaltrustly',
      'paypalprzelewy',
      'paypalmybank',
      'paypalideal',
      'paypaleps',
      'paypalblik',
      'paypalbancontact',
    );
    foreach ($fixed_zones_modules_array as $zones_modules) {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = ''
                     WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($zones_modules)."_ZONE'");
    }
    
    // set all files to be deleted                     
    $unlink_file = array(
      'callback/paypal/paypalinstallment.php',
      'includes/external/paypal/modules/installment.php',
      'includes/external/paypal/templates/presentment.html',
      'includes/external/paypal/templates/presentment_info.html',
      'includes/modules/order_total/ot_paypalinstallment_fee.php',
      'includes/modules/payment/paypalgiropay.php',
      'includes/modules/payment/paypalinstallment.php',
      'lang/english/modules/order_total/ot_paypalinstallment_fee.php',
      'lang/english/modules/payment/paypalgiropay.php',
      'lang/english/modules/payment/paypalinstallment.php',
      'lang/german/modules/order_total/ot_paypalinstallment_fee.php',
      'lang/german/modules/payment/paypalgiropay.php',
      'lang/german/modules/payment/paypalinstallment.php',
    );
  
    foreach ($unlink_file as $unlink) {
      if (trim($unlink) != '' && is_file(DIR_FS_CATALOG.$unlink)) {  
        unlink(DIR_FS_CATALOG.$unlink);
      }
    }
    
    // remove giropay
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_PAYMENT_PAYPALGIROPAY_%'");
    if (xtc_db_affected_rows() > 0) {
      require_once(DIR_FS_INC.'update_module_configuration.inc.php');
      update_module_configuration('payment');
    }
    
    if (defined('MODULE_PAYMENT_PAYPALACDC_STATUS')) {
      if ($this->get_config('MODULE_PAYMENT_PAYPALACDC_EXTEND_CARDS', false) == '') {
        $sql_data_array = array(
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALACDC_EXTEND_CARDS',
            'config_value' => '0',
          )
        );
        $this->save_config($sql_data_array);
      }
      if ($this->get_config('MODULE_PAYMENT_PAYPALACDC_SAVE_PAYMENT', false) == '') {
        $sql_data_array = array(
          array(
            'config_key' => 'MODULE_PAYMENT_PAYPALACDC_SAVE_PAYMENT',
            'config_value' => '1',
          )
        );
        $this->save_config($sql_data_array);
      }
    }
    
    // set buttons
    if ($this->get_config('PAYPAL_BUTTON_LAYOUT', false) == '') {
      $sql_data_array = array(
        array(
          'config_key' => 'PAYPAL_BUTTON_LAYOUT',
          'config_value' => 'horizontal',
        ),
        array(
          'config_key' => 'PAYPAL_BUTTON_SHAPE',
          'config_value' => 'rect',
        ),
        array(
          'config_key' => 'PAYPAL_BUTTON_PRIMARY_COLOR',
          'config_value' => 'gold',
        ),
        array(
          'config_key' => 'PAYPAL_BUTTON_SECONDARY_COLOR',
          'config_value' => 'blue',
        ),
        array(
          'config_key' => 'PAYPAL_BUTTON_HEIGHT',
          'config_value' => '35',
        )
      );
      $this->save_config($sql_data_array);
    }

    if (defined('MODULE_PAYMENT_PAYPALEXPRESS_STATUS')
        && $this->get_config('MODULE_PAYMENT_PAYPALEXPRESS_SHOW_BOX_CART', false) == ''
        )
    {
      $sql_data_array = array(
        array(
          'config_key' => 'MODULE_PAYMENT_PAYPALEXPRESS_SHOW_BOX_CART',
          'config_value' => '1',
        ),
        array(
          'config_key' => 'MODULE_PAYMENT_PAYPALEXPRESS_SHOW_BOX_CART_BNPL',
          'config_value' => '1',
        ),
      );
      $this->save_config($sql_data_array);
    }
    
    // unlink association files
    if (is_file(DIR_FS_EXTERNAL.'paypal/templates/apple-developer-merchantid-domain-association.live')) {
      unlink(DIR_FS_EXTERNAL.'paypal/templates/apple-developer-merchantid-domain-association.live');
    }
    if (is_file(DIR_FS_EXTERNAL.'paypal/templates/apple-developer-merchantid-domain-association.sandbox')) {
      unlink(DIR_FS_EXTERNAL.'paypal/templates/apple-developer-merchantid-domain-association.sandbox');
    }
    
  }

}
