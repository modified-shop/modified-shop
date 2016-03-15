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

    function __construct($module = '') {
      global $PHP_SELF,$order;

      $this->modules = array();
      
      if (defined('MODULE_PAYMENT_INSTALLED') && xtc_not_null(MODULE_PAYMENT_INSTALLED)) {

        ## Paypal
        if (isset($_SESSION['paypal'])
            && isset($_SESSION['paypal']['payment_modules'])
            && $_SESSION['paypal']['payment_modules'] != ''
           )
        {
          $modules = explode(';', $_SESSION['paypal']['payment_modules']);
        } else {
          $modules = explode(';', MODULE_PAYMENT_INSTALLED);
          $key = array_search('paypalcart.php', $modules);
          if ($key !== false) {
            unset($modules[$key]);
          }
        }
        
        $module_directory = DIR_WS_MODULES . 'payment/';
        foreach($modules as $file) {
          $class = substr($file, 0, strrpos($file, '.'));
          $module_status = (defined('MODULE_PAYMENT_'. strtoupper($class) .'_STATUS') && strtolower(constant('MODULE_PAYMENT_'. strtoupper($class) .'_STATUS')) == 'true') ? true : false;
          if (is_file($module_directory . $file) && $module_status) {
            $this->modules[] = $file;
          }
        }
        unset($modules);

        $include_modules = array();

        if (xtc_not_null($module) && in_array($module.'.php', $this->modules)) {
          $this->selected_module = $module;
          $include_modules[] = array(
            'class' => $module,
            'file' => $module.'.php'
          );
        } else {
          reset($this->modules);
          while (list(, $value) = each($this->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array(
              'class' => $class,
              'file' => $value
            );
          }
        }
        
        // unallowed modules
        $unallowed_modules_string = $_SESSION['customers_status']['customers_status_payment_unallowed'];

        // unallowed modules/Download
        if (isset($order) && is_object($order)) {        
          if (isset($order->customer['payment_unallowed']) && trim($order->customer['payment_unallowed']) != '') {
            $unallowed_modules_string .= (($unallowed_modules_string != '') ? ',' : '').$order->customer['payment_unallowed'];
          }
          if ($order->content_type == 'virtual' 
              || $order->content_type == 'virtual_weight' 
              || $order->content_type == 'mixed'
              )
          {
            $unallowed_modules_string .= (($unallowed_modules_string != '') ? ',' : '').DOWNLOAD_UNALLOWED_PAYMENT;
          }
        }

        // unallowed payment / shipping
        if (MODULE_EXCLUDE_PAYMENT_STATUS == 'True') {
          for ($i=1; $i<=MODULE_EXCLUDE_PAYMENT_NUMBER; $i++) {
            $shipping_exclude = explode(',', constant('MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$i));
            if (in_array(substr($_SESSION['shipping']['id'], 0, (strpos($_SESSION['shipping']['id'], '_'))), $shipping_exclude) !== false) {
              $unallowed_modules_string .= (($unallowed_modules_string != '') ? ',' : '').constant('MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$i);
            }
          }
        }

        // unallowed modules as array
        $unallowed_modules_string = preg_replace("'[\r\n\s]+'",'',$unallowed_modules_string);
        $unallowed_modules = explode(',', $unallowed_modules_string);

        for ($i = 0, $n = sizeof($include_modules); $i < $n; $i++) {
          if (!in_array($include_modules[$i]['class'], $unallowed_modules)) {
            // check if zone is alowed to see module
            $unallowed_zones = array();
            if (constant('MODULE_PAYMENT_' . strtoupper($include_modules[$i]['class']) . '_ALLOWED') != '') {
              $unallowed_zones = explode(',', constant('MODULE_PAYMENT_' . strtoupper($include_modules[$i]['class']) . '_ALLOWED'));
            }
            if ((isset($_SESSION['delivery_zone']) 
                 && in_array($_SESSION['delivery_zone'], $unallowed_zones) == true
                 ) || count($unallowed_zones) == 0
                )
            {
              if ($include_modules[$i]['file'] != 'no_payment') {
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
        if (xtc_count_payment_modules() == 1 
            && (!isset($_SESSION['payment']) 
                || !is_object($_SESSION['payment'])
                )
            )
        {
          $_SESSION['payment'] = $include_modules[0]['class'];
        }

        if (xtc_not_null($module) 
            && in_array($module, $this->modules) 
            && isset($GLOBALS[$module]->form_action_url)
            ) 
        {
          $this->form_action_url = $GLOBALS[$module]->form_action_url;
        }
      }
    }

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
        if (isset($GLOBALS[$this->selected_module]) 
            && is_object($GLOBALS[$this->selected_module])
            && method_exists($GLOBALS[$this->selected_module], 'update_status')
            ) 
        {
          $GLOBALS[$this->selected_module]->update_status();
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
              '  if (document.getElementById("checkout_payment").payment) {' . "\n" .
              '    if (document.getElementById("checkout_payment").payment.length) {' . "\n" .
              '      for (var i=0; i<document.getElementById("checkout_payment").payment.length; i++) {' . "\n" .
              '        if (document.getElementById("checkout_payment").payment[i].checked) {' . "\n" .
              '          payment_value = document.getElementById("checkout_payment").payment[i].value;' . "\n" .
              '        }' . "\n" .
              '      }' . "\n" .
              '    } else if (document.getElementById("checkout_payment").payment.checked) {' . "\n" .
              '      payment_value = document.getElementById("checkout_payment").payment.value;' . "\n" .
              '    } else if (document.getElementById("checkout_payment").payment.value) {' . "\n" .
              '      payment_value = document.getElementById("checkout_payment").payment.value;' . "\n" .
              '    }' . "\n" .
              '  }' . "\n\n";

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (isset($GLOBALS[$class]) 
              && is_object($GLOBALS[$class]) 
              && $GLOBALS[$class]->enabled
              && method_exists($GLOBALS[$class], 'javascript_validation')
              ) 
          {
            $js .= $GLOBALS[$class]->javascript_validation();
          }
        }

        $js .= "\n" . '  if (document.getElementById("rd-cot_gv")) {' . "\n" .
               '    var gv_value = parseFloat(document.getElementById("rd-cot_gv").value);' . "\n" .
               '    var cot_value = 0;' . "\n" .
               '    if (document.getElementById("cot-cot_gv")) {' . "\n" .
               '      cot_value = parseFloat(document.getElementById("cot-cot_gv").value);' . "\n" .
               '    }' . "\n" .
               '    if (document.getElementById("rd-cot_gv").checked) {' . "\n" .
               '      if (gv_value >= cot_value) {' . "\n" .
               '        payment_value = "use_gv";' . "\n" .
               '        error = 0;' . "\n" .
               '        error_message = unescape("' . xtc_js_lang(JS_ERROR) . '");' . "\n" .
               '      }' . "\n" .  
               '    }' . "\n" .       
               '  }' . "\n\n";

        if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
          $js .= "\n" . '  if (!document.getElementById("checkout_payment").conditions.checked) {' . "\n" .
                 '    error_message = error_message + unescape("' . xtc_js_lang(JS_ERROR_CONDITIONS_NOT_ACCEPTED) . '");' . "\n" .
                 '    error = 1;' . "\n" .
                 '  }' . "\n\n";
        }

        if (DISPLAY_REVOCATION_VIRTUAL_ON_CHECKOUT == 'true'
            && ($_SESSION['cart']->content_type == 'virtual'
                || $_SESSION['cart']->content_type == 'mixed')
            )
        {
          $js .= "\n" . '  if (!document.getElementById("checkout_payment").revocation.checked) {' . "\n" .
                 '    error_message = error_message + unescape("' . xtc_js_lang(JS_ERROR_REVOCATION_NOT_ACCEPTED) . '");' . "\n" .
                 '    error = 1;' . "\n" .
                 '  }' . "\n\n";
        }
                
        $js .= "\n" . '  if (document.getElementById("gccover")) {' . "\n" .
               '     payment_value = "gccover";' . "\n" .
               '  }' . "\n\n"; 
       
        $js .= "\n" . '  if (payment_value == null) {' . "\n" .
               '    error_message = error_message + unescape("' . xtc_js_lang(JS_ERROR_NO_PAYMENT_MODULE_SELECTED) . '");' . "\n" .
               '    error = 1;' . "\n" .
               '  }' . "\n\n" .
               '  if (error == 1 && submitter != 1) {' . "\n" . 
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
          if (isset($GLOBALS[$class]) 
              && is_object($GLOBALS[$class])
              && $GLOBALS[$class]->enabled
              && method_exists($GLOBALS[$class], 'selection')
              ) 
          {
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
    //check credit covers was setup to test whether credit covers is set in other parts of the code
    function check_credit_covers() {
      global $credit_covers;
      return $credit_covers;
    }

    function pre_confirmation_check() {
      global $credit_covers, $payment_modules;
      
      if (is_array($this->modules)) {
        if (isset($GLOBALS[$this->selected_module]) 
            && is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            ) 
        {
          if ($credit_covers) {
            $GLOBALS[$this->selected_module]->enabled = false;
            $GLOBALS[$this->selected_module] = NULL;
            $payment_modules = '';
          } else {
            if (method_exists($GLOBALS[$this->selected_module], 'pre_confirmation_check')) {
              $GLOBALS[$this->selected_module]->pre_confirmation_check();
            }
          }
        }
      }
    }

    function confirmation() {
      if (is_array($this->modules)) {
        if (isset($GLOBALS[$this->selected_module]) 
            && is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            && method_exists($GLOBALS[$this->selected_module], 'confirmation')
            ) 
        {
          return $GLOBALS[$this->selected_module]->confirmation();
        }
      }
    }

    function process_button() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            && method_exists($GLOBALS[$this->selected_module], 'process_button')
            )
        {
          return $GLOBALS[$this->selected_module]->process_button();
        }
      }
    }

    function before_process() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            && method_exists($GLOBALS[$this->selected_module], 'before_process')
            ) 
        {
          return $GLOBALS[$this->selected_module]->before_process();
        }
      }
    }

    function payment_action() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            && method_exists($GLOBALS[$this->selected_module], 'payment_action')
            ) 
        {
          return $GLOBALS[$this->selected_module]->payment_action();
        }
      }
    }

    function after_process() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            && method_exists($GLOBALS[$this->selected_module], 'after_process')
            ) 
        {
          return $GLOBALS[$this->selected_module]->after_process();
        }
      }
    }

    function success() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            ) 
        {
          if (method_exists($GLOBALS[$this->selected_module], 'success')) {
            return $GLOBALS[$this->selected_module]->success();
          } else {
            return array();          
          }
        }
      }
    }

    function get_error() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            && method_exists($GLOBALS[$this->selected_module], 'get_error')
            ) 
        {
          return $GLOBALS[$this->selected_module]->get_error();
        }
      }
    }

    function iframeAction() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            && method_exists($GLOBALS[$this->selected_module], 'iframeAction')
            ) 
        {
          return $GLOBALS[$this->selected_module]->iframeAction();
        }
      }
    }

    function create_paypal_link() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module])
            && $GLOBALS[$this->selected_module]->enabled
            && method_exists($GLOBALS[$this->selected_module], 'create_paypal_link')
            ) 
        {
          return $GLOBALS[$this->selected_module]->create_paypal_link();
        }
      }
    }

    function info() {
      if (is_array($this->modules)) {
        if (is_object($GLOBALS[$this->selected_module]) 
            && $GLOBALS[$this->selected_module]->enabled
            ) 
        {
          if (method_exists($GLOBALS[$this->selected_module], 'info')) {
            return $GLOBALS[$this->selected_module]->info();
          } else {
            return array();          
          }
        }
      }
    }
    
  }
?>
