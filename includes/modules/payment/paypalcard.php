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


class paypalcard extends PayPalPaymentV2 {

  var $code;
  var $description;
  var $enabled;
  var $tmpOrders;
  var $paypal_code;

	function __construct() {
		global $order;
		
		$this->paypal_code = 'card';
    PayPalPaymentV2::__construct('paypalcard');
		$this->tmpOrders = false;
	}


  function update_status() {
    global $order;
    
    $this->enabled = false;
    if (isset($_SESSION['paypal_instruments'])
        && is_array($_SESSION['paypal_instruments'])
        && in_array($this->paypal_code, $_SESSION['paypal_instruments'])
        )
    {
      $this->enabled = true;
    }
    
	  parent::update_status();	  
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

    $process_button .= sprintf($this->get_js_sdk(), '
      paypal.Buttons({
        fundingSource: paypal.FUNDING.CARD,
        style: {
          layout: "'.$this->get_config('PAYPAL_BUTTON_LAYOUT').'",
          shape: "'.$this->get_config('PAYPAL_BUTTON_SHAPE').'",
          height: '.$this->get_config('PAYPAL_BUTTON_HEIGHT').',
          label: "buynow"
        },
        createOrder: function(data, actions) {
          return $.ajax({
            type: "POST",
            url: "'.$order_url.'",
            dataType: "json"
          });
        },
        onApprove: function(data, actions) {
          $("#checkout_confirmation").submit();
          $(".apms_form_button").hide();
        },
        onError: function (err) {
          window.location.href = "'.$error_url.'";
        }
      }).render("#apms_button").then(() => {
        $(".apms_form_button_overlay").hide();
      });
    ', "$('#checkout_confirmation').replaceWith('".$info."');");

    return $process_button;
  }
  
  
	function before_process() {	  
	  $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);
	  	  
	  if (!in_array($PayPalOrder->status, array('COMPLETED', 'APPROVED'))) {
	    $key = array_search($this->paypal_code, $_SESSION['paypal_instruments']);
	    unset($_SESSION['paypal_instruments'][$key]);

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
		  'MODULE_PAYMENT_PAYPALCARD_STATUS', 
      'MODULE_PAYMENT_PAYPALCARD_ALLOWED', 
      'MODULE_PAYMENT_PAYPALCARD_ZONE',
      'MODULE_PAYMENT_PAYPALCARD_SORT_ORDER'
    );
	}

}
