<?php 
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2010 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

	include ('includes/application_top.php');

	require_once(DIR_WS_INCLUDES . 'modules/payment/billpaytransactioncredit.php');
	require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpaytransactioncredit.php');
	
	$billpay = new billpaytransactioncredit();
	
	require (DIR_WS_CLASSES . 'order.php');
	$order = new order();

	require (DIR_WS_CLASSES . 'order_total.php');
	$order_total_modules = new order_total();
	$order_total_modules->process();
	
	$billpayTotals = $billpay->_calculate_billpay_totals($order_total_modules, $order, true);

	$rr_data = array();
	$rr_data['country'] = $order->billing['country']['iso_code_3'];
	$rr_data['currency'] = $order->info['currency'];
	$rr_data['merchant'] = $billpay->bp_merchant;
	$rr_data['portal'] = $billpay->bp_portal;
	$rr_data['bp_secure'] =  $billpay->bp_secure;
	$rr_data['api_url'] = $billpay->api_url;
	$rr_data['base'] = $billpay->_currencyToSmallerUnit($billpayTotals['orderTotalGross'] - $billpayTotals['billpayShippingGross']);
	$rr_data['total'] =  $billpay->_currencyToSmallerUnit($billpayTotals['orderTotalGross']);
	$rr_data['termsUrl'] = $billpay->_buildTcTermsUrl();

	echo '<html><head>';
	echo '</head><body style="margin:0; padding:0">';
	
	echo '<form name="billpay_rates_form" method="POST">';
	$country = $rr_data['country'];
	$currency =  $rr_data['currency'];
	$billpayLanguage = $billpay->_getLanguage();

	$defaultRateNumber = 12;
	
	if (isset($_SESSION['billpay_module_config'][$country][$currency])) {
		$config = $_SESSION['billpay_module_config'][$country][$currency];
		if ($config == false) {
			$billpay->_logError('Fetching module config failed previously. Billpay payment not available.');
		}
		$terms = $config['terms'];
		$defaultRateNumber = in_array(12, $terms) ? 12 : $terms[0];
		
		echo '<div style="overflow:hidden; border:1px solid silver; padding: 10px">';
		echo '<div style="float:left; font: 12px Arial, Helvetica, sans-serif;">'
			. MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ENTER_NUMBER_RATES . ':</div>';
			
		echo '<select name="numberRates" onchange="document.billpay_rates_form.submit();" style="float:right;">';
		foreach ($terms as $term) {
			if (isset($_POST['numberRates'])) {
				if ($term == $_POST['numberRates']) {
					echo '<option selected="selected">' . $term . '</option>';
				} else {
					echo '<option>' . $term . '</option>';
				}
			} else if ($_SESSION['bp_rate_result']) {
				if ($term == $_SESSION['bp_rate_result']['numberRates']) {
					echo '<option selected="selected">' . $term . '</option>';
				} else {
					echo '<option>' . $term . '</option>';
				}
			} else if ($term == $defaultRateNumber) {
				echo '<option selected="selected">' . $term . '</option>';
			} else {
				echo '<option>' . $term . '</option>';
			}
		}
		echo '</select>';
		echo '</div>';
	} else {
		echo 'no module config';
	}
	
	$numberOfRates = $_POST['numberRates'];

	// check session
	if (!isset($numberOfRates)) {
		$numberOfRates = $_SESSION['bp_rate_result']['numberRates'];
	}
	
	// check preload status
	if (!isset($numberOfRates) && $_GET['preload'] == '1') {
		$numberOfRates = $defaultRateNumber;
	}
	
	// store in session
	if (isset($numberOfRates)) {
		$_SESSION['bp_rate_result']['numberRates'] = $numberOfRates;
	}
	
	
	if (isset($numberOfRates)) {
		$rateResult = $_SESSION['bp_rate_result'];
		
		if (!isset($rateResult) || $rateResult['base'] != $rr_data['base'] || $rateResult['total'] != $rr_data['total']) {
			require_once(DIR_WS_INCLUDES . 'external/billpay/api/ipl_xml_api.php');
			require_once(DIR_WS_INCLUDES . 'external/billpay/api/php4/ipl_calculate_rates_request.php');
			
			//$rr_data = $_SESSION['rr_data'];
			$req = new ipl_calculate_rates_request($rr_data['api_url']); 
			$req->set_default_params($rr_data['merchant'], $rr_data['portal'], $rr_data['bp_secure']);
			$req->set_locale($country, $currency, $billpayLanguage);
			$req->set_rate_request_params($rr_data['base'], $rr_data['total']);

			$internalError = $req->send();

			$xmlreq = (string)utf8_decode($req->get_request_xml());
			$xmlresp =	(string)utf8_decode($req->get_response_xml());

			$billpay->_logError($xmlreq, 'XML REQUEST CALCULATE_RATES');
			$billpay->_logError($xmlresp, 'XML RESPONSE CALCULATE_RATES');

			if ($req->has_error()) {
				$billpay->_logError('Error code (' . $req->get_error_code()
					. ') received (Calculate rates): ' . $req->get_merchant_error_message());
				return;
			}
			$rateResult = array();
			$rateResult['rateplan'] = $req->get_options();
			$rateResult['numberRates'] = $numberOfRates;
			$rateResult['base'] = $rr_data['base'];
			$rateResult['total'] = $rr_data['total'];
			$_SESSION['bp_rate_result'] = $rateResult;
		} //else {
		//	$_SESSION['bp_rate_result']['numberRates'] = $numberOfRates;
		//}

		displayRateplan($rateResult['rateplan'], $numberOfRates, $order->info['currency']);
	} else if (isset($_SESSION['bp_rate_result'])) {
		displayRateplan($_SESSION['bp_rate_result']['rateplan'], $_SESSION['bp_rate_result']['numberRates'], $order->info['currency']);
	}
	else {
		echo '<div style="overflow:hidden; border:1px solid silver; padding: 10px; margin-top:10px; height:35px; text-align:center">';
		echo '<input type="submit" value="' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CALCULATE_RATES . '" style="margin-left:2px; "/>';
		echo '</div>';
	}
	echo '</form>';	
	echo '</body></html>';

	function displayRateplan($ratePlanArray, $numberRates, $currency) {
		$billpay = new billpaytransactioncredit();
		
		$selectedRatePlan = $ratePlanArray[$numberRates];
		$cart = (float)$selectedRatePlan['calculation']['cart'] / 100;
		$base = (float)$selectedRatePlan['calculation']['base'] / 100;
		$additional = $cart - $base; 
		$interest = (float)$selectedRatePlan['calculation']['interest'] / 100;
		$surcharge = (float)$selectedRatePlan['calculation']['surcharge'] / 100;
		$fee = (float)$selectedRatePlan['calculation']['fee'] / 100;
		$total = (float)$selectedRatePlan['calculation']['total'] / 100;
		$annual = (float)$selectedRatePlan['calculation']['anual'] / 100;
		$first = (float)$selectedRatePlan['dues'][0][value] / 100;
		$following = (float)$selectedRatePlan['dues'][1][value] / 100;
		
		echo '<div style="overflow:hidden; border:1px solid silver; padding: 10px; margin-top:10px; height:35px">';
			echo '<table border="0" style="width:100%; font: 12px Arial, Helvetica, sans-serif; border-collapse:collapse;">';
			
			if ($first != $following) {
				echo '<tr>';
					echo '<td style="padding-bottom:6px">' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_FIRST_RATE . '</td>';
					echo '<td style="padding-bottom:6px">&nbsp;</td>';
					echo '<td style="padding-bottom:6px; font-weight: bold;text-align: right;">'
						. formatCurrency($first, $currency) . '</td>';
				echo '</tr><tr>';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_FOLLOWING_RATES . '</td>';
					echo '<td>&nbsp;</td>';
					echo '<td style="font-weight: bold;text-align: right;">'
					 	. formatCurrency($following, $currency) . '</td>';
				echo '</tr>';
				echo '<tr><td colspan="3">';
				
				echo '</td></tr>';
			}
			else {
				echo '<tr>';
					echo '<td style="padding-bottom:6px; padding-top:10px">' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_UNIQUE_RATE . '</td>';
					echo '<td style="padding-bottom:6px; padding-top:10px">&nbsp;</td>';
					echo '<td style="padding-bottom:6px; font-weight: bold;text-align: right; padding-top:10px">'
						. formatCurrency($first, $currency) . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		
		echo '</div>';
		
		echo '<div style="font: 12px Arial, Helvetica, sans-serif;">';
		echo '<a href="' . buildRateplanUrl($numberRates, $selectedRatePlan, $currency) . '" target="_blank">' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_FINANCE_DETAILS_LINK_TEXT . '</a>';
		echo '</div>';
	}

	function formatCurrency($value, $currency) {
		// return (float)value / 100;
		$xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
		return $xtPrice->xtcFormat($value, true);
	}
	
	function buildRateplanUrl($numberRates, $selectedRatePlan, $currency) {
		return 'https://www.billpay.de/api/ratenkauf/ratenplan?rateCount=' . $numberRates
			. '&interest=' . $selectedRatePlan['calculation']['interest']
			. '&firstRate=' . $selectedRatePlan['dues'][0][value]
			. '&followingRate=' . $selectedRatePlan['dues'][1][value]
			. '&currency=' . $currency
			. '&base=' . $selectedRatePlan['calculation']['base']
			. '&cart=' . $selectedRatePlan['calculation']['cart']
			. '&term=' . $numberRates
			. '&rateCount=' . $numberRates
			. '&prepayment=0' 
			. '&surcharge=' . $selectedRatePlan['calculation']['surcharge']
			. '&fee=' . $selectedRatePlan['calculation']['fee']
			. '&total=' . $selectedRatePlan['calculation']['total']
			. '&apr=' . $selectedRatePlan['calculation']['anual'];
	}
?>
