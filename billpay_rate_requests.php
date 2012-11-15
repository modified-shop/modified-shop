<?php 
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2011 xtcModified
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

	echo '<form method="POST">';
	$country = $rr_data['country'];
	$currency =  $rr_data['currency'];
	$billpayLanguage = $billpay->_getLanguage();

	$defaultRateNumber = 12;
	
	echo '<p style="font: 12px Arial, Helvetica, sans-serif; font-weight: bold;">'
		. MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ENTER_NUMBER_RATES . ':</p>';
	if (isset($_SESSION['billpay_module_config'][$country][$currency])) {
		$config = $_SESSION['billpay_module_config'][$country][$currency];
		if ($config == false) {
			$billpay->_logError('Fetching module config failed previously. Billpay payment not available.');
		}
		$terms = $config['terms'];
		$defaultRateNumber = in_array(12, $terms) ? 12 : $terms[0];
		echo '<select name="numberRates">';
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
	} else {
		echo 'no module config';
	}
	echo '<input type="submit" value="' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CALCULATE_RATES . '" style="margin-left:2px"/>';
	
	$numberOfRates = $_POST['numberRates'];
	if (!isset($numberOfRates) && $_GET['preload'] == '1') {
		$numberOfRates = $defaultRateNumber;
	}
	
	if (isset($numberOfRates)) {
		$rateResult = $_SESSION['bp_rate_result'];
		if (!isset($rateResult) || $rateResult['base'] != $rr_data['base'] || $rateResult['total'] != $rr_data['total']) {
        require_once(DIR_FS_EXTERNAL . 'billpay/api/ipl_xml_api.php'); // DokuMan -2011-09-08 - BILLPAY payment module (in external directory)
        require_once(DIR_FS_EXTERNAL . 'billpay/api/php4/ipl_calculate_rates_request.php'); // DokuMan -2011-09-08 - BILLPAY payment module (in external directory)
			
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
		} else {
			$_SESSION['bp_rate_result']['numberRates'] = $numberOfRates;
		}

		displayRateplan($rateResult['rateplan'], $numberOfRates);
	} else if (isset($_SESSION['bp_rate_result'])) {
		displayRateplan($_SESSION['bp_rate_result']['rateplan'], $_SESSION['bp_rate_result']['numberRates']);
	}
	echo '</form>';	

	function displayRateplan($ratePlanArray, $numberRates) {
		$billpay = new billpaytransactioncredit();
		
		$selectedRatePlan = $ratePlanArray[$numberRates];
		$caption = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CAPTION_TEXT1 . ' ' . $numberRates . ' '
				 . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CAPTION_TEXT2;
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
		
		echo '<h2 style="font: 12px Arial, Helvetica, sans-serif; font-weight: bold;">' . $caption . '</h2>';

		echo '<table border="0" style="table-layout: fixed">';
		echo '<tr><td style="width: 75%">';
			echo '<table border="0" style="font: 12px Arial, Helvetica, sans-serif; border-collapse:collapse">';
				echo '<tr>';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CART_AMOUNT_TEXT . '</td>';
					echo '<td>&nbsp;&nbsp;=&nbsp;</td>';
					echo '<td style="text-align: right;">' . formatCurrency($base, $currency) . $currency . '</td>';
				echo '</tr><tr>';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTC_SURCHARGE_TEXT . '</td>';
					echo '<td>&nbsp;&nbsp;+&nbsp;</td>';
					echo '<td style="text-align: right;"></td>';
				echo '</tr><tr>';
					echo '<td>(' . formatCurrency($base, $currency) . '&nbsp;' . $currency . ' x ' . $interest . ' x '
						. $numberRates . ') / 100</td>';
					echo '<td>&nbsp;&nbsp;=&nbsp;</td>';
					echo '<td style="text-align: right;">' . formatCurrency($surcharge, $currency) . $currency . '</td>';
				echo '</tr><tr>';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TEXT . '</td>';
					echo '<td>&nbsp;&nbsp;+&nbsp;</td>';
					echo '<td style="text-align: right;">' . formatCurrency($fee, $currency) . $currency . '</td>';
				echo '</tr><tr style="border-bottom-style: solid; border-bottom-width: 1px">';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_OTHER_COSTS_TEXT . '</td>';
					echo '<td>&nbsp;&nbsp;+&nbsp;</td>';
					echo '<td style="text-align: right;">' . formatCurrency($additional, $currency) . $currency . '</td>';
				echo '</tr><tr style="font-weight: bold">';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_AMOUNT_TEXT . '</td>';
					echo '<td>&nbsp;&nbsp;=&nbsp;</td>';
					echo '<td style="text-align: right;">' . formatCurrency($total, $currency) . $currency . '</td>';
				echo '</tr><tr>';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_DIVIDED_BY_RATES . '</td>';
					echo '<td>&nbsp;</td>';
					echo '<td style="text-align: right;">' . $numberRates . ' '
						. MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_RATES . '</td>';
				echo '</tr><tr>';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_FIRST_RATE . '</td>';
					echo '<td>&nbsp;</td>';
					echo '<td style="font-weight: bold;text-align: right;">'
						. formatCurrency($first, $currency) . $currency . '</td>';
				echo '</tr><tr style="border-bottom-style: solid; border-bottom-width: 1px">';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_FOLLOWING_RATES . '</td>';
					echo '<td>&nbsp;</td>';
					echo '<td style="font-weight: bold;text-align: right;">'
					 	. formatCurrency($following, $currency) . $currency . '</td>';
				echo '</tr><tr style="font-weight:bold">';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ANUAL_RATE_TEXT . '</td>';
					echo '<td>&nbsp;&nbsp;=&nbsp;</td>';
					echo '<td style="text-align: right;">' . $annual . '%</td>';
				echo '</tr>';
			echo '</table>';
		echo '</td><td valign="top">';
			echo '<table style="font: 12px Arial, Helvetica, sans-serif;">';
				echo '<tr>';
					echo '<td><a href="' . $billpay->_buildTcTermsUrl() . '" target="_BLANK">' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_LINK1 . '</a></td>';
					//echo '<td><a href="' . $_SESSION['rr_data']['termsUrl'] . '" target="_BLANK">' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_LINK1 . '</a></td>';
				echo '</tr><tr>';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_LINK2 . '</td>';
				echo '</tr><tr>';
					echo '<td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_LINK3 . '</td>';
				echo '</tr>';
			echo '</table>';
		echo '</td></tr></table>';
	}

	function formatCurrency($value, $currency) {
		// return (float)value / 100;
		$xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
		return $xtPrice->xtcFormat($value, true);
	}
?>
