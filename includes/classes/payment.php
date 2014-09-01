<?php
/* -----------------------------------------------------------------------------------------
   $Id: payment.php 2594 2012-01-04 10:53:58Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(payment.php,v 1.36 2003/02/11); www.oscommerce.com
   (c) 2003 nextcommerce (payment.php,v 1.11 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (payment.php 41 2009-01-22)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC . 'xtc_count_payment_modules.inc.php');

  class payment {
    var $modules, $selected_module;

    // class constructor
    function payment($module = '') {
      global $PHP_SELF,$order;

      if (defined('MODULE_PAYMENT_INSTALLED') && xtc_not_null(MODULE_PAYMENT_INSTALLED)) {

        // BOF - Tomcraft - 2011-02-01 - Paypal Express Modul
        if(isset($_SESSION['paypal_express_checkout']) && $_SESSION['paypal_express_checkout'] == true){
          $this->modules = explode(';', $_SESSION['paypal_express_payment_modules'] );
        } else {
          $this->modules = explode(';', MODULE_PAYMENT_INSTALLED);
          $this->modules = str_replace('paypalexpress.php', '', $this->modules);
        }
        // EOF - Tomcraft - 2011-02-01 - Paypal Express Modul

        $include_modules = array();

        if ( (xtc_not_null($module)) && (in_array($module . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $this->selected_module = $module;
          $include_modules[] = array('class' => $module,
                                     'file' => $module . '.php');
        } else {
          reset($this->modules);
          while (list(, $value) = each($this->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array('class' => $class,
                                       'file' => $value);
          }
        }
        // load unallowed modules into array - remove spaces and line breaks by web28
        $unallowed_modules_string = $_SESSION['customers_status']['customers_status_payment_unallowed'];
        if (isset($order->customer['payment_unallowed']) && trim($order->customer['payment_unallowed']) != '') {
          $unallowed_modules_string .= ','.$order->customer['payment_unallowed'];
        }
        $unallowed_modules_string = preg_replace("'[\r\n\s]+'",'',$unallowed_modules_string);
        $unallowed_modules = explode(',',$unallowed_modules_string);
        // add unallowed modules/Download
        if (isset($order) && is_object($order) && ($order->content_type == 'virtual' || ($order->content_type == 'virtual_weight'))) {
         $download_unallowed_payment = preg_replace("'[\r\n\s]+'",'',DOWNLOAD_UNALLOWED_PAYMENT);
         $unallowed_modules = array_merge($unallowed_modules,explode(',',$download_unallowed_payment));
        }

        //print_r($include_modules);
        for ($i = 0, $n = sizeof($include_modules); $i < $n; $i++) {
          if (!in_array($include_modules[$i]['class'], $unallowed_modules)) {
            // check if zone is alowed to see module
            if (constant('MODULE_PAYMENT_' . strtoupper(str_replace('.php', '', $include_modules[$i]['file'])) . '_ALLOWED') != '') {
              $unallowed_zones = explode(',', constant('MODULE_PAYMENT_' . strtoupper(str_replace('.php', '', $include_modules[$i]['file'])) . '_ALLOWED'));
            } else {
              $unallowed_zones = array();
            }
            if ((isset($_SESSION['delivery_zone']) && in_array($_SESSION['delivery_zone'], $unallowed_zones) == true) || count($unallowed_zones) == 0) {
              if ($include_modules[$i]['file']!='' && $include_modules[$i]['file']!='no_payment') {

              include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $include_modules[$i]['file']);
              include_once(DIR_WS_MODULES . 'payment/' . $include_modules[$i]['file']);

              }
              if (class_exists($include_modules[$i]['class'])) {
                $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
              }
            }
          }
        }
        // if there is only one payment method, select it as default because in
        // checkout_confirmation.php the $payment variable is being assigned the
        // $HTTP_POST_VARS['payment'] value which will be empty (no radio button selection possible)
        // Do not preselect a payment method -> user interaction shall be required!
        if ( (xtc_count_payment_modules() == 1) && (!isset($_SESSION['payment']) || !is_object($_SESSION['payment'])) ) {
          $_SESSION['payment'] = $include_modules[0]['class'];
        }

        if ( (xtc_not_null($module)) && (in_array($module, $this->modules)) && (isset($GLOBALS[$module]->form_action_url)) ) {
          $this->form_action_url = $GLOBALS[$module]->form_action_url;
        }
      }
    }

    // class methods
    /* The following method is needed in the checkout_confirmation.php page
       due to a chicken and egg problem with the payment class and order class.
       The payment modules needs the order destination data for the dynamic status
       feature, and the order class needs the payment module title.
       The following method is a work-around to implementing the method in all
       payment modules available which would break the modules in the contributions
       section. This should be looked into again post 2.2.
     */
    function update_status() {
      if (is_array($this->modules)) {
        if (isset($GLOBALS[$this->selected_module]) && is_object($GLOBALS[$this->selected_module])) {
          if (function_exists('method_exists')) {
            if (method_exists($GLOBALS[$this->selected_module], 'update_status')) {
              $GLOBALS[$this->selected_module]->update_status();
            }
          }
        }
      }
    }

    function javascript_validation() {
      $js = '';
      if (is_array($this->modules)) {
        $js = '<script type="text/javascript"><!-- ' . "\n" .
              'function check_form() {' . "\n" .
              '  var error = 0;' . "\n" .
              '  var error_message = unescape("' . xtc_js_lang(JS_ERROR) . '");' . "\n" .
              '  var payment_value = null;' . "\n" .
              '  if (document.getElementById("checkout_payment").payment.length) {' . "\n" .
              '    for (var i=0; i<document.getElementById("checkout_payment").payment.length; i++) {' . "\n" .
              '      if (document.getElementById("checkout_payment").payment[i].checked) {' . "\n" .
              '        payment_value = document.getElementById("checkout_payment").payment[i].value;' . "\n" .
              '      }' . "\n" .
              '    }' . "\n" .
              '  } else if (document.getElementById("checkout_payment").payment.checked) {' . "\n" .
              '    payment_value = document.getElementById("checkout_payment").payment.value;' . "\n" .
              '  } else if (document.getElementById("checkout_payment").payment.value) {' . "\n" .
              '    payment_value = document.getElementById("checkout_payment").payment.value;' . "\n" .
              '  }' . "\n\n";

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
            $js .= $GLOBALS[$class]->javascript_validation();
          }
        }
        if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
        $js .= "\n" . '  if (!document.getElementById("checkout_payment").conditions.checked) {' . "\n" .
               '    error_message = error_message + unescape("' . xtc_js_lang(ERROR_CONDITIONS_NOT_ACCEPTED) . '");' . "\n" .
               '    error = 1;' . "\n" .
               '  }' . "\n\n";
        }
        $js .= "\n" . '  if (payment_value == null) {' . "\n" .
               '    error_message = error_message + unescape("' . xtc_js_lang(JS_ERROR_NO_PAYMENT_MODULE_SELECTED) . '");' . "\n" .
               '    error = 1;' . "\n" .
               '  }' . "\n\n" .
               '  if (error == 1 && submitter != 1) {' . "\n" . // GV Code Start/End
               '    alert(error_message);' . "\n" .
               '    return false;' . "\n" .
               '  } else {' . "\n" .
               '    return true;' . "\n" .
               '  }' . "\n" .
               '}' . "\n" .
               '//--></script>' . "\n";
      }
      return $js;
    }

    function selection() {
      $selection_array = array();
      if (is_array($this->modules)) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
            $selection = $GLOBALS[$class]->selection();
            if (is_array($selection)) {
              $selection_array[] = $selection;
            }
          }
        }
      }
      return $selection_array;
    }

    //GV Code Start
    //ICW CREDIT CLASS Gift Voucher System
    // check credit covers was setup to test whether credit covers is set in other parts of the code
    function check_credit_covers() {
      global $credit_covers;
      return $credit_covers;
    }
    // GV Code End

    function pre_confirmation_check() {
    global $credit_covers, $payment_modules;
      if (is_array($this->modules)) {
        if (isset($GLOBALS[$this->selected_module]) && is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          if ($credit_covers) {
            $GLOBALS[$this->selected_module]->enabled = false;
            $GLOBALS[$this->selected_module] = NULL;
            $payment_modules = '';
          } else {
            $GLOBALS[$this->selected_module]->pre_confirmation_check();
          }
        }
      }
    }

    function confirmation() {
      if (is_array($this->modules)) {
        if (isset($GLOBALS[$this->selected_module]) && is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->confirmation();
        }
      }
    }

    function process_button() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->process_button();
        }
      }
    }

    function before_process() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->before_process();
        }
      }
    }

    function payment_action() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->payment_action();
        }
      }
    }

    function after_process() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->after_process();
        }
      }
    }
// BOF - web28 - 2010-05-07 - PayPal API Modul
		// PayPal Express Giropay
    function giropay_process() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->giropay_process();
        }
      }
    }
// EOF - web28 - 2010-05-07 - PayPal API Modul
    function get_error() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->get_error();
        }
      }
    }

//BOF - Dokuman - 2009-10-02 - added entries for new moneybookers payment module version 2.4
  function iframeAction() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->iframeAction();
        }
      }
    }
//EOF - Dokuman - 2009-10-02 - added entries for new moneybookers payment module version 2.4

//BOF  - web28 - 2010-03-27 PayPal IPN Bezahl-Link
  function create_paypal_link() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
          return $GLOBALS[$this->selected_module]->create_paypal_link();
        }
      }
    }
//EOF  - web28 - 2010-03-27 PayPal IPN Bezahl-Link

  function info() {
    if (is_array($this->modules)) {
      if (is_object($GLOBALS[$this->selected_module]) && ($GLOBALS[$this->selected_module]->enabled) ) {
        if (method_exists($GLOBALS[$this->selected_module], 'info'))
          return $GLOBALS[$this->selected_module]->info();
        else
          return array();          
      }
    }
  }
}
?>