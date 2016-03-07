<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
include('includes/application_top.php');

if (!isset($_SESSION['customer_id'])) {
  die('Currently not available');
}

if (isset($_GET['checkout']) && $_SESSION['payment'] == 'paypalplus') {
  echo '<script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js" type="text/javascript"></script>'."\n";
  echo '<script type="application/javascript">PAYPAL.apps.PPP.doCheckout();</script>'."\n";
} elseif (isset($_SESSION['paypal']['approval'])) {
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');                                        

  $selection = get_third_party_payments();
  $paypal = new PayPalPayment('paypalplus');
  
  $module = array();
  if (ACTIVATE_GIFT_SYSTEM == 'true') {
    require_once (DIR_WS_CLASSES . 'order_total.php');
    $order_total_modules = new order_total();
    $credit_selection = $order_total_modules->credit_selection();
  }
  if (!isset($credit_selection) || !is_array($credit_selection) || count($credit_selection) < 1) {
    for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
      $description = $paypal->get_config(strtoupper($selection[$i]['id'].'_'.$_SESSION['language_code']));
      $module[] = array(
        'redirectUrl' => $paypal->encode_utf8($paypal->link_encoding(xtc_href_link('callback/paypal/paypalplus_redirect.php', 'payment='.$selection[$i]['id'], 'SSL'))),
        'methodName' => $paypal->encode_utf8(strip_tags($selection[$i]['module'])),
        'description' => $paypal->encode_utf8(($description != '') ? $description : strip_tags($selection[$i]['description'])),
      );
    }
  }

  $country_query = xtc_db_query("SELECT c.countries_iso_code_2
                                   FROM ".TABLE_COUNTRIES." c
                                   JOIN ".TABLE_ADDRESS_BOOK." ab
                                        ON c.countries_id = ab.entry_country_id
                                           AND address_book_id = '".$_SESSION['customer_default_address_id']."'");
  $country = xtc_db_fetch_array($country_query);

  echo '<div id="ppplus"></div>';
  echo '<script type="application/javascript">
  var ppp = PAYPAL.apps.PPP({	
  "approvalUrl": "'.$_SESSION['paypal']['approval'].'",
  "placeholder": "ppplus",
  "mode": "'.$paypal->get_config('PAYPAL_MODE').'",
  "language": "'.$_SESSION['language_code'].'_'.$country['countries_iso_code_2'].'",
  "country": "'.$country['countries_iso_code_2'].'",
  "buttonLocation": "outside",
  "preselection": "paypal",
  "useraction": "continue",
  "showLoadingIndicator": "true",
  "showPuiOnSandbox": "true"';
	
	if (count($module) > 0) {
	  echo ','."\n";
	  echo '"onContinue": function() { 
            var check = check_form();
            if (check == true) {
              var payment = ppp.getPaymentMethod();
              if (payment.substring(0, 2) != "pp") {
                ppp.doCheckout();
              } else {
                setTimeout("document.checkout_payment.submit()", 10);
              }
            }
	        }, ';
	  echo '  "thirdPartyPaymentMethods": '.json_encode($module)."\n";
	}

  echo '});
  </script>'."\n";
} else {
  die('Currently not available');
}