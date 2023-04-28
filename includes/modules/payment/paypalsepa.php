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


class paypalsepa extends PayPalPaymentV2 {
	var $code, $title, $description, $extended_description, $enabled;


	function __construct() {
		global $order;
		
		$this->paypal_code = 'sepa';
    PayPalPaymentV2::__construct('paypalsepa');
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
    $paypal_smarty->caching = 0;

    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/apms.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/apms.html';
    }
    $process_button = $paypal_smarty->fetch($tpl_file);

    $process_button .= sprintf($this->get_js_sdk(), '
      paypal.Buttons({
        fundingSource: paypal.FUNDING.SEPA,
        style: {
          layout: "vertical",
          shape: "rect",
          label: "buynow",
        },
        createOrder: function(data, actions) {
          return "'.$_SESSION['paypal']['OrderID'].'";
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
    ');
    
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
		  'MODULE_PAYMENT_PAYPALSEPA_STATUS', 
      'MODULE_PAYMENT_PAYPALSEPA_ALLOWED', 
      'MODULE_PAYMENT_PAYPALSEPA_ZONE',
      'MODULE_PAYMENT_PAYPALSEPA_SORT_ORDER'
    );
	}

}
?>