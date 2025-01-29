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
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');


class paypal extends PayPalPaymentV2 {

  var $code;
  var $description;
  var $tmpOrders;
  var $paypal_code;

  function __construct() {
    global $order;
    
    $this->paypal_code = 'paypal';
    PayPalPaymentV2::__construct('paypal');
    $this->tmpOrders = false;
  }


  function confirmation() {
    return array ('title' => $this->description);
  }

  
  function process_button() {
    global $smarty;
    
    $smarty->clear_assign('CHECKOUT_BUTTON');
    
    $paypal_smarty = new Smarty();
    $paypal_smarty->assign('language', $_SESSION['language']);
    $paypal_smarty->assign('checkout', true);
    $paypal_smarty->assign('paypalexpress', true);
    if ($this->get_config('MODULE_PAYMENT_'.strtoupper($this->code).'_SHOW_CHECKOUT_BNPL') == '1') {
      $paypal_smarty->assign('paypalbnpl', true);
    }
    if ($this->get_config('MODULE_PAYMENT_'.strtoupper($this->code).'_SAVE_PAYMENT') == '1') {
      $paypal_smarty->assign('SAVE_PAYMENT_CHECKBOX', xtc_draw_checkbox_field('save_payment', 'save_payment', false, 'id="save_payment"'));
    }
    
    $paypal_smarty->caching = 0;

    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/apms.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html';
    }
    $process_button = $paypal_smarty->fetch($tpl_file);

    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/pui_error.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/pui_error.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/pui_error.html';
    }
    $paypal_smarty->assign('error_message', TEXT_PAYPAL_ERROR_NOT_AVAILABLE);
    $info = $paypal_smarty->fetch($tpl_file);
    $info = trim(str_replace(array("\r", "\n"), '', $info));

    $order_url = DIR_WS_BASE.'ajax.php?ext=create_paypal_order';
    $error_url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL');
    
    $paypalscript = '
      paypal.Buttons({
        fundingSource: paypal.FUNDING.PAYPAL,
        style: {
          layout: "'.$this->get_config('PAYPAL_BUTTON_LAYOUT').'",
          shape: "'.$this->get_config('PAYPAL_BUTTON_SHAPE').'",
          color: "'.$this->get_config('PAYPAL_BUTTON_PRIMARY_COLOR').'",
          height: '.$this->get_config('PAYPAL_BUTTON_HEIGHT').',
          label: "buynow"
        },
        createOrder: function(data, actions) {
          var formdata = $("#checkout_confirmation").serializeArray(); 
          return $.ajax({
            type: "POST",
            url: "'.$order_url.'",
            data: formdata,
            dataType: "json"
          });
        },
        onApprove: function(data, actions) {
          $("#checkout_confirmation").submit();
          $(".apms_form_button").hide();
        },
        onError: function (err) {
          console.error("failed to load PayPal buttons", err);
          window.location.href = "'.$error_url.'";
        },
        onRender: function() { 
          $(".apms_form_button_overlay").hide();
        }
      }).render("#apms_button1");
    ';
    
    if ($this->get_config('MODULE_PAYMENT_'.strtoupper($this->code).'_SHOW_CHECKOUT_BNPL') == '1') {
      $paypalscript .= '
        paypal.Buttons({
          fundingSource: paypal.FUNDING.PAYLATER,
          style: {
            layout: "'.$this->get_config('PAYPAL_BUTTON_LAYOUT').'",
            shape: "'.$this->get_config('PAYPAL_BUTTON_SHAPE').'",
            color: "'.$this->get_config('PAYPAL_BUTTON_SECONDARY_COLOR').'",
            height: '.$this->get_config('PAYPAL_BUTTON_HEIGHT').'
          },
          createOrder: function(data, actions) {
            var formdata = $("#checkout_confirmation").serializeArray(); 
            return $.ajax({
              type: "POST",
              url: "'.$order_url.'",
              data: formdata,
              dataType: "json"
            });
          },
          onApprove: function(data, actions) {
            $("#checkout_confirmation").submit();
            $(".apms_form_button").hide();
          },
          onError: function (err) {
            console.error("failed to load PayPal BNPL buttons", err);
            $("#apms_bnpl").hide();
          },
          onRender: function() { 
            $("#apms_bnpl").show();
          }
        }).render("#apms_button2");
      ';
    }
    
    $process_button .= sprintf($this->get_js_sdk('true', false, $this->GenerateUserToken()->tokenId), $paypalscript, "$('#checkout_confirmation').replaceWith('".$info."');");
    
    return $process_button;
  }


  function before_process() {	  
    $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);
        
    if (!in_array($PayPalOrder->status, array('COMPLETED', 'APPROVED'))) {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
  }


  function before_send_order() {
    global $insert_id;
  
    $this->FinishOrder($insert_id);    
  }


  function after_process() {
    unset($_SESSION['paypal']);
  }
  
  
  function success() {    
    return false;
  }


  function install() {	
    parent::install();	  
  }


  function keys() {
    return array(
      'MODULE_PAYMENT_PAYPAL_STATUS', 
      'MODULE_PAYMENT_PAYPAL_ALLOWED', 
      'MODULE_PAYMENT_PAYPAL_ZONE',
      'MODULE_PAYMENT_PAYPAL_SORT_ORDER'
    );
  }

}
