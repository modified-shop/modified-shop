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
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');


class paypalplus extends PayPalPayment {
	var $code, $title, $description, $extended_description, $enabled;


	function __construct() {
		global $order;
    
    PayPalPayment::__construct('paypalplus');
    
		$this->tmpOrders = false;
	}


  function selection() {
    global $smarty;
        
    $payments = get_third_party_payments();
    
    if (isset($_SESSION['payment'])) {
      for ($i=0, $n=count($payments); $i<$n; $i++) {
        if ($payments[$i]['id'] == $_SESSION['payment']) {
          $_SESSION['payment'] = $this->code;
        }
      }
    }
      
    $_SESSION['paypal']['approval'] = $this->payment_redirect(false, true);

    if ($_SESSION['paypal']['approval'] == '') {
      $GLOBALS['paypalplus']->enabled = false;
    } else {
      $description = '<div id="ppp_result"></div>
      <script type="text/javascript">
        (function() {
          var pp = document . createElement(\'script\');
          pp.type = \'text/javascript\';
          pp.async = true;
          pp.src = \'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js\';
          var s = document.getElementsByTagName(\'script\')[0];
          s . parentNode . insertBefore(pp, s);
        })();
        $(window).load(function() {
          '.((count($payments) > 0) ? '
          if ($(\'input[name="payment"]:checked\', \'#checkout_payment\').val() == "'.$this->code.'") {
            $("#continueButton").attr("onclick", "ppp.doContinue(); return false;");
          }
          ' : '').'
          $("#checkout_payment").attr("name", "checkout_payment");        
          $.get("'.xtc_href_link('callback/paypal/paypalplus.php', '', 'SSL').'", function(data) {
            $("#ppp_result").html(data);
          })
          $("[id*=\"rd\"]").click(function(e) {
            if ($(\'input[name="payment"]:checked\', \'#checkout_payment\').val() == "'.$this->code.'") {
              '.(($this->get_config('MODULE_PAYMENT_'.strtoupper($this->code).'_USE_TABS') == '1') ? '
              $.get("'.xtc_href_link('callback/paypal/paypalplus.php', '', 'SSL').'", function(data) {
                $("#ppp_result").html(data);
              });
              ' : '').'
              '.((count($payments) > 0) ? '
              $("#continueButton").removeAttr("onclick");
              $("#continueButton").attr("onclick", "ppp.doContinue(); return false;");
              ' : '').'
            } else {
              '.((count($payments) > 0) ? '$("#continueButton").removeAttr("onclick");' : '').'
            }
          });
        });
      </script>';
    
      $smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'id="continueButton"'));
    
      return array(
        'id' => $this->code, 
        'module' => $this->title, 
        'description' => $this->info . $description,
      );
    }
  }


	function javascript_validation() {
    $js = 'if (payment_value == "' . $this->code . '") {' . "\n" .
          '  var pp_payment = ppp.getPaymentMethod();' . "\n" .
          '  if (!pp_payment || pp_payment.length === 0) {' . "\n" .
          '    error_message = error_message + unescape("' . xtc_js_lang(JS_ERROR_NO_PAYMENT_MODULE_SELECTED) . '");' . "\n" .
          '    error = 1;' . "\n" .
          '  }' . "\n" .
          '}' . "\n";
    
    return $js;
	}


  function process_button() {
		// confirmed
		if (isset($_SESSION['paypal']['paymentId']) 
		    && $_SESSION['paypal']['paymentId'] != ''
		    ) 
		{
 		  $this->patch_payment_paypalplus();
		}
		
    return $description;
  }


	function before_process() {
		// confirmed
		if (isset($_GET['PayerID']) && $_GET['PayerID'] != '' 
		    && isset($_GET['token']) && $_GET['token'] != '' 
		    && isset($_GET['paymentId']) && $_GET['paymentId'] != '' 
		    && $_GET['paymentId'] == $_SESSION['paypal']['paymentId']		
		    ) 
		{
   		return;
		} elseif (isset($_SESSION['paypal']['paymentId']) 
		    && $_SESSION['paypal']['paymentId'] != ''
		    ) 
		{
 		  xtc_redirect(xtc_href_link('callback/paypal/paypalplus.php', 'checkout=true'));
		}
	}


	function after_process() {
		$this->validate_payment_paypal();
		unset($_SESSION['paypal']);
	}


	function keys() {
		return array('MODULE_PAYMENT_PAYPALPLUS_STATUS', 
		             'MODULE_PAYMENT_PAYPALPLUS_ALLOWED', 
		             'MODULE_PAYMENT_PAYPALPLUS_ZONE',
		             'MODULE_PAYMENT_PAYPALPLUS_SORT_ORDER'
		             );
	}

}
?>