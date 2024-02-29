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


class paypalapplepay extends PayPalPaymentV2 {

  var $code;
  var $description;
  var $enabled;
  var $tmpOrders;
  var $paypal_code;

  function __construct() {
		global $order;
		
		$this->paypal_code = 'applepay';
    PayPalPaymentV2::__construct('paypalapplepay');
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
    global $smarty, $order;
    
    $smarty->clear_assign('CHECKOUT_BUTTON');
    
    if (!isset($_SESSION['paypal'])
        || $_SESSION['paypal']['cartID'] != $_SESSION['cart']->cartID
        || $_SESSION['paypal']['OrderID'] == ''
        )
    {
      $_SESSION['paypal'] = array(
        'cartID' => $_SESSION['cart']->cartID,
        'OrderID' => $this->CreateOrder()
      );
    }
    
    $error_url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL');
    if ($_SESSION['paypal']['OrderID'] == '') {
	    xtc_redirect($error_url);
    }

    $paypal_smarty = new Smarty();
    $paypal_smarty->assign('language', $_SESSION['language']);
    $paypal_smarty->assign('checkout', true);
    $paypal_smarty->assign('paypalapplepay', true);

    $paypal_smarty->caching = 0;

    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/apms.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html';
    }
    $process_button = $paypal_smarty->fetch($tpl_file);
    
    $paypalscript .= '
    if ($("#apms_button3").length) {    
      // eslint-disable-next-line no-undef
      if (typeof ApplePaySession != "undefined" && ApplePaySession?.supportsVersion(4) && ApplePaySession?.canMakePayments()) {
        setupApplepay().catch(console.error);
      } else {
        redirectAppleError();
      }     
    } else {
      redirectAppleError();
    }     
    ';

    $process_button .= sprintf($this->get_js_sdk(), $paypalscript);

    $process_button .= '
    <script>
      function getAppleOrderID() {
        return "'.$_SESSION['paypal']['OrderID'].'";
      }
      
      function getAppleTransactionInfo() {
        return {
          countryIsoCode: "'.strtoupper($order->delivery['country']['iso_code_2']).'",
          currencyIsoCode: "'.$order->info['currency'].'",
          totalPrice: "'.round($order->info['total'], 2).'",
          totalPriceStatus: "final",
          totalLabel: "'.$this->encode_utf8(mb_substr(STORE_NAME, 0, 22)).'",
        };
      }
      
      function redirectAppleSuccess() {
        $("#checkout_confirmation").submit();
        $(".apms_form_button").hide();
      }
      
      function redirectAppleError() {
        window.location.href = "'.xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL').'";
      }
    </script>
    ';
    
    return $process_button;
  }


  function before_process() {	  
    $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);

    if ($PayPalOrder->status == 'PAYER_ACTION_REQUIRED') {
      foreach ($PayPalOrder->links as $links) {
        if ($links->rel == 'payer-action') {
          xtc_redirect($links->href);
          break;
        }
      }
    }
  
    if (isset($PayPalOrder->payer->payer_id)) {
      $_SESSION['paypal']['PayerID'] = $PayPalOrder->payer->payer_id;
    }
  
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
      'MODULE_PAYMENT_PAYPALAPPLEPAY_STATUS', 
      'MODULE_PAYMENT_PAYPALAPPLEPAY_ALLOWED', 
      'MODULE_PAYMENT_PAYPALAPPLEPAY_ZONE',
      'MODULE_PAYMENT_PAYPALAPPLEPAY_SORT_ORDER'
    );
  }

}
