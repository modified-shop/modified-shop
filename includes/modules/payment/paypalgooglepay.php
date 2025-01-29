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


class paypalgooglepay extends PayPalPaymentV2 {

  var $code;
  var $description;
  var $tmpOrders;

  function __construct() {
    global $order;
  
    PayPalPaymentV2::__construct('paypalgooglepay');
    $this->tmpOrders = false;
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
    $paypal_smarty->assign('paypalgooglepay', true);

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

    $error_url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL');
    
    $paypalscript = '
    if ($("#apms_button4").length) {
      if (google && paypal.Googlepay) {
        onGooglePayLoaded();
      } else {
        redirectGoogleError();
      }     
    } else {
      redirectGoogleError();
    }     
    ';

    $process_button .= sprintf($this->get_js_sdk(), $paypalscript, "$('#checkout_confirmation').replaceWith('".$info."');");

    $process_button .= '
    <script>
      function getGoogleOrderID() {
        return "'.$_SESSION['paypal']['OrderID'].'";
      }
      
      function getGoogleTransactionInfo() {
        return {
          countryCode: "'.strtoupper($order->delivery['country']['iso_code_2']).'",
          currencyCode: "'.$order->info['currency'].'",
          totalPrice: "'.round($order->info['total'], 2).'",
          totalPriceStatus: "FINAL",
        };
      }

      function addGooglePayButton() {
        const paymentsClient = getGooglePaymentsClient();
        const button = paymentsClient.createButton({
          buttonColor: "default",
          buttonType: "buy",
          buttonLocale: "'.$_SESSION['language_code'].'",
          onClick: onGooglePaymentButtonClicked
        });
        document.getElementById("apms_button4").appendChild(button);
        document.getElementsByClassName("apms_form_button_overlay")[0].style.display = "none";
      }
      
      function redirectGoogleSuccess() {
        $("#checkout_confirmation").submit();
        $(".apms_form_button").hide();
      }
      
      function redirectGoogleError() {
        window.location.href = "'.$error_url.'";
      }
      
      function getGoogleEnviroment() {
        return "'.(($this->get_config('PAYPAL_MODE') == 'live') ? 'PRODUCTION' : 'TEST').'"
      }
    </script>
    ';
    
    return $process_button;
  }


  function before_process() {	  
    $PayPalOrder = $this->GetOrder($_SESSION['paypal']['OrderID']);

    if ($PayPalOrder->status == 'PAYER_ACTION_REQUIRED') {
      $this->redirectOrder($PayPalOrder->links, 'payer-action');
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
      'MODULE_PAYMENT_PAYPALGOOGLEPAY_STATUS', 
      'MODULE_PAYMENT_PAYPALGOOGLEPAY_ALLOWED', 
      'MODULE_PAYMENT_PAYPALGOOGLEPAY_ZONE',
      'MODULE_PAYMENT_PAYPALGOOGLEPAY_SORT_ORDER'
    );
  }

}
