<?php 
	include ('includes/application_top.php');


	require_once(DIR_WS_INCLUDES . 'modules/payment/billpaytransactioncredit.php');
	require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpaytransactioncredit.php');
	
	$billpay = new billpaytransactioncredit();
	
	require (DIR_WS_CLASSES . 'order.php');
	$order = new order();

	require (DIR_WS_CLASSES . 'order_total.php');
	$order_total_modules = new order_total();
	$order_total_modules->process();
	
	$billpayTotals = $billpay->_calculate_billpay_totals($order_total_modules, $order, false);

	$rr_data = array();
	$rr_data['country'] = $order->billing['country']['iso_code_3'];
	$rr_data['currency'] = $order->info['currency'];
	$rr_data['merchant'] = $billpay->bp_merchant;
	$rr_data['portal'] = $billpay->bp_portal;
	$rr_data['bp_secure'] =  $billpay->bp_secure;
	$rr_data['api_url'] = $billpay->api_url;
	$rr_data['base'] = $billpay->CurrencyToSmallerUnit($billpayTotals['orderTotalGross'] - $billpayTotals['billpayShippingGross']);
	$rr_data['total'] =  $billpay->CurrencyToSmallerUnit($billpayTotals['orderTotalGross']);
	$rr_data['termsUrl'] = $billpay->_buildTcTermsUrl();

	echo '<html><head>';
	echo '</head><body style="margin:0; padding:0">';
	
	$country = $rr_data['country'];
	$currency =  $rr_data['currency'];
	$billpayLanguage = $billpay->_getLanguage();

	$defaultRateNumber = 12;
	
	if (isset($_SESSION['billpay_module_config'][$country][$currency])) {
		$config = $_SESSION['billpay_module_config'][$country][$currency];
		if ($config == false) {
			$billpay->_logError('Fetching module config failed previously. BillPay payment not available.');
		}
		$terms = $config['terms'];
        if ($billpay->isBigCHFOrder($country, $currency, $billpayTotals['orderTotalGross'])) {
            foreach ($terms as $key => $val) {
                if (!in_array($val, array(6,9,12))) unset($terms[$key]);
            }
        }
		$defaultRateNumber = in_array(12, $terms) ? 12 : $terms[0];
	} else {
		echo 'no module config';
	}
	
    $duration = (!empty($_POST['duration']) ? $_POST['duration'] : $_GET['duration']);

	// check session
	if (!isset($duration)) {
        $duration = $_SESSION['bp_rate_result']['duration'];
	}
	
	// check preload status
	if (!isset($duration) && $_GET['preload'] == '1') {
        $duration = $defaultRateNumber;
	}
	
	// store in session
	if (isset($duration)) {
		$_SESSION['bp_rate_result']['duration'] = $duration;
	}
	
	
	if (isset($duration)) {
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
			$rateResult['duration'] = $duration;
			$rateResult['base'] = $rr_data['base'];
			$rateResult['total'] = $rr_data['total'];
			$_SESSION['bp_rate_result'] = $rateResult;
		} //else {
		//	$_SESSION['bp_rate_result']['numberRates'] = $numberOfRates;
		//}

		displayRateplan($billpay, $rateResult['rateplan'], $duration, $order->info['currency']);
	} else if (isset($_SESSION['bp_rate_result'])) {
		displayRateplan($billpay, $_SESSION['bp_rate_result']['rateplan'], $_SESSION['bp_rate_result']['duration'], $order->info['currency']);
	}
	else {
		echo '<div style="overflow:hidden; border:1px solid silver; padding: 10px; margin-top:10px; height:35px; text-align:center">';
		echo '<input type="submit" value="' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CALCULATE_RATES . '" style="margin-left:2px; "/>';
		echo '</div>';
	}
	echo '</body></html>';

    function displayRateplan($billpay, $ratePlanArray, $duration, $currency) {
        $selectedRatePlan = $ratePlanArray[$duration];
        $first = (float)$selectedRatePlan['dues'][0]['value'] / 100;
        $following = (float)$selectedRatePlan['dues'][1]['value'] / 100;

        $total = (float)$selectedRatePlan['calculation']['total'] / 100;
        $base = (float)$selectedRatePlan['calculation']['base'] / 100;
        $surcharge = (float)$selectedRatePlan['calculation']['surcharge'] / 100;
        $fee = (float)$selectedRatePlan['calculation']['fee'] / 100;
        $other = (float)(($selectedRatePlan['calculation']['cart'] - $selectedRatePlan['calculation']['base']) / 100);

        echo '<div class="bpy-rateplan">';
            echo '<div class="bpy-rate-details-block">';
                echo '<div class="bpy-row">';
                    echo sprintf($billpay->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_FIRST_RATE), formatCurrency($first, $currency)).' ';
                    echo sprintf($billpay->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_NEXT_RATE), formatCurrency($following, $currency));
                echo '</div>';
                echo '<div class="bpy-row">';
                    echo sprintf($billpay->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_TOTAL), formatCurrency($total, $currency)).' ';
                    echo sprintf($billpay->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_BASE), formatCurrency($base, $currency)).' ';
                    echo sprintf($billpay->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_RATES), formatCurrency($surcharge, $currency)).' ';
                    echo sprintf($billpay->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_PROCESSING), formatCurrency($fee, $currency)).' ';
                    echo sprintf($billpay->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_SHIPPING), formatCurrency($other, $currency));
                echo '</div>';
            echo '</div>';
            echo '<div class="bpy-financial-details-block">';
                echo '<a onclick="bpyExternalPopup(this); return false;" href="'.buildRateplanUrl($duration, $selectedRatePlan, $currency).'" target="_blank">'.$billpay->EnsureString(MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_FORM_DETAILS).'</a>';
            echo '</div>';
        echo '</div>';
    }

    function formatCurrency($value) {
        $xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
        return $xtPrice->xtcFormat($value, true);
    }

    function buildRateplanUrl($duration, $selectedRatePlan, $currency) {
        $domain = 'billpay.de';
        if ($currency == 'CHF') {
            $domain = 'billpay.ch';
        }
        return 'https://www.'.$domain.'/api/ratenplan?numInst=' . $selectedRatePlan['rateCount']
            . '&duration=' . $duration
            . '&interest=' . $selectedRatePlan['calculation']['interest']
            . '&firstRate=' . $selectedRatePlan['dues'][0]['value']
            . '&followingRate=' . $selectedRatePlan['dues'][1]['value']
            . '&currency=' . $currency
            . '&base=' . $selectedRatePlan['calculation']['base']
            . '&cart=' . $selectedRatePlan['calculation']['cart']
            . '&prepayment=0'
            . '&surcharge=' . $selectedRatePlan['calculation']['surcharge']
            . '&fee=' . $selectedRatePlan['calculation']['fee']
            . '&total=' . $selectedRatePlan['calculation']['total']
            . '&apr=' . $selectedRatePlan['calculation']['anual'];
    }

