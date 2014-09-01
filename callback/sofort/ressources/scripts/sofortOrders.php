<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 13:49:09 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofortOrders.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * 
 * Should be included in admin/orders.php, line 35 (ca.; after all other requires but before '// initiate template engine for mail')
 */

require_once(DIR_FS_CATALOG.'callback/sofort/ressources/scripts/sofortOrderSynchronisation.php');
$sofort = false;

if ((($_GET['action'] == 'edit') || ($_GET['action'] == 'update_order')) && ($_GET['oID'])) {
	$oID = shopDbPrepareInput($_GET['oID']);
	$orders_query = shopDbQuery('SELECT payment_method FROM '.TABLE_ORDERS.' WHERE orders_id = \''.shopDbInput($oID).'\'');
	$order_exists = true;
	
	if (!shopDbNumRows($orders_query)) {
		$order_exists = false;
		$messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
	} else {
		$result = shopDbQuery('SHOW TABLES LIKE \'sofort_orders\'');
		
		if($result){
			$orders_query = shopDbQuery('SELECT payment_method, transaction_id FROM sofort_orders WHERE orders_id = \''.shopDbInput($oID).'\'');
			$result = shopDbFetchArray($orders_query);
			
			if($result) {
				$sofort = true;
				$tId = $result['transaction_id'];
				
				switch ($result['payment_method']){
					case 'rechnung_by_sofort'	:	$paymentMethodShort = 'SR'; break;
					case 'sofortueberweisung'	:	$paymentMethodShort = 'SU'; break;
					case 'vorkasse_by_sofort'	:	$paymentMethodShort = 'SV'; break;
					case 'sofortlastschrift'	:	$paymentMethodShort = 'SL'; break;
					case 'lastschrift_by_sofort':	$paymentMethodShort = 'LS'; break;
					default						:	$sofort = false; break;
				}
			}
		}
	}
}

if ($sofort) {
	require_once(DIR_FS_CATALOG.'/callback/sofort/helperFunctions.php');
	require_once(DIR_FS_CATALOG.'/callback/sofort/library/sofortLib.php');
	
	require(DIR_WS_CLASSES.'currencies.php');
	$currencies = new currencies();
	
	require(DIR_WS_CLASSES.'order.php');
	$order = new order($oID);
	
	$successCodes = false;
	$errorCodes = false;
	
	$lang = $_SESSION['languages_id'];
	
	$ordersStatuses = array();
	$ordersStatusArray = array();
	$ordersStatusQuery = shopDbQuery('SELECT orders_status_id, orders_status_name FROM '.TABLE_ORDERS_STATUS.' WHERE language_id = \''.(int)$lang.'\'');
	
	$action = (isset($_GET['action']) ? $_GET['action'] : '');
	
	while ($ordersStatus = shopDbFetchArray($ordersStatusQuery)) {
		$ordersStatuses[] = array('id' => $ordersStatus['orders_status_id'], 'text' => $ordersStatus['orders_status_name']);
		$ordersStatusArray[$ordersStatus['orders_status_id']] = $ordersStatus['orders_status_name'];
	}
	
	$getLng = $_SESSION['language'];
	require(shopGetLanguageFile($getLng,$paymentMethodShort));
	require(shopGetLanguageFile($getLng,'general'));
	
	$logo = shopGetLogo($paymentMethodShort);
	
	$PnagInvoice = new PnagInvoice(shopGetConfigKey(),$tId);
	
	if ($_POST['sofort_action'] == 'sofort_comment') {
		shopSofortComment($_GET['oID'], $order, $_POST['status'], $_POST['comments'], $_POST['notify'], $_POST['notify_comments']);
	}
	
	if ($_POST['sofort_action'] == 'sofort_save' && $_POST['sofort_delete_all'] == '1') {
		$_POST['sofort_action'] = 'sofort_buttons';
		$_POST['sofort_button'] = 'cancel';
	}
	
	if ($_POST['sofort_action'] == 'sofort_buttons') {
		switch($_POST['sofort_button']){
			case 'invoice':
			case 'preview':
			case 'credit':	$PnagInvoice->getInvoice();
							break;
			case 'confirm':	$errors = $PnagInvoice->confirmInvoice();
							$warnings = $PnagInvoice->getWarnings();
							break;
			case 'cancel':	$errors = $PnagInvoice->cancelInvoice();
							$warnings = $PnagInvoice->getWarnings();
							break;
			default:		break;
		}
		
		$errorCodes = "";
		$successCodes = "";
		
		if ($errors) {
			$errorCodes .= "<div class='sofort_error'>";
			
			foreach ($errors as $oneError) {
				if (defined('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$oneError['code'])) {
					$errorCodes .= constant('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$oneError['code'])."<br/>";
				} else {
					$errorCodes .= MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_0."<br/>";
				}
			}
			
			$errorCodes .= "</div>";
		}
		
		if ($warnings) {
			$errorCodes .= "<div class='sofort_error'>";
			
			foreach ($warnings as $oneWarning) {
				if (defined('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$oneWarning['code'])) {
					$errorCodes .= constant('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$oneWarning['code'])."<br/>";
				}else{
					$errorCodes .= MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_0."<br/>";
				}
			}
			
			$errorCodes .= "</div>";
		} else {
			if(!$errors){
				switch($_POST['sofort_button']){
					case 'confirm':	$successCodes .= "<div class='sofort_success'>";
									$successCodes .= MODULE_PAYMENT_SOFORT_SR_INVOICE_CONFIRMED."<br/>";
									$successCodes .= "</div>";
									break;
					case 'cancel':	$successCodes .= "<div class='sofort_success'>";
									$successCodes .= ($PnagInvoice->getStatusReason() == 'confirm_invoice') ? MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED : MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED_REFUNDED."<br/>";
									$successCodes .= "</div>";
									break;
				}
			}
		}
	}
	
	
	if ($_POST['sofort_action'] == 'sofort_save'){
		$articles = array();
		reset($_POST['opid_product']);
		
		for ($i = 0, $n = count($_POST['opid_product']); $i < $n; ++$i) {
			$query_product = shopDbQuery('SELECT products_quantity, products_price, products_model, products_tax, products_name FROM '.TABLE_ORDERS_PRODUCTS.' WHERE orders_products_id = "'.$_POST['opid_product'][$i].'"');
			$result_product = shopDbFetchArray($query_product);
			
			$query_attributes = shopDbQuery("SELECT products_options, products_options_values, options_values_price, price_prefix FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." WHERE orders_id = '".shopDbInput($_GET['oID'])."' AND orders_products_id = '".$_POST['opid_product'][$i]."'");
			$description = '';
			
			while ($attributes = shopDbFetchArray($query_attributes)) {
				$description .= $attributes['products_options'].": ".nl2br($attributes['products_options_values'])."\n";
				
				if ($attributes['options_values_price'] != '0'){
					$description .= " (".$attributes['price_prefix']." ".number_format($attributes['options_values_price'], 2).")";
				}
			}
			
			$description = substr($description, 0 , strlen($description)-1);
			
			$query = shopDbQuery('SELECT item_id FROM sofort_products WHERE orders_products_id = "'.$_POST['opid_product'][$i].'"');
			$result = shopDbFetchArray($query);
			
			if ($_POST['delete_product'][$i] == 'delete'){
				$_POST['qty_product'][$i] = 'delete';
			}
			
			array_push($articles, array(
					'articleId'				  => $result['item_id'],
					'articleNumber'			  => $result_product['products_model'],
					'articleTitle'			  => $result_product['products_name'],
					'articleDescription'	  => $description,
					'articleQuantity'		  => $_POST['qty_product'][$i],
					'articlePrice'			  => $_POST['price_product'][$i],
					'articleTax'			  => $result_product['products_tax'],
					'articleOrdersProductsId' => $_POST['opid_product'][$i],
					'articleOrdersId'		  => $_GET['oID'],
					'articleType'			  => 'product'
				)
			);
		}
		
		$totalsQuery = shopDbQuery('SELECT class, value, title FROM '.TABLE_ORDERS_TOTAL.' WHERE orders_id = "'.$_GET['oID'].'"');
		
		while ($totalsResult = shopDbFetchArray($totalsQuery)){
			$totals[$totalsResult['class']] = array(
					'value' => $totalsResult['value'],
					'title' => $totalsResult['title']
			);
		}
		
		$shippingQuery = shopDbQuery('SELECT shipping_class FROM '.TABLE_ORDERS.' WHERE orders_id = "'.$_GET['oID'].'"');
		$shippingResult = shopDbFetchArray($shippingQuery);
		
		if ($shippingResult['shipping_class'] != ''){
			$shippingClass = explode('_', $shippingResult['shipping_class']);
			$itemId = 'shipping|'.$shippingClass[0].'|'.$shippingClass[1];
			
			$tax = shopGetTaxRate(constant(MODULE_SHIPPING_.strtoupper($shippingClass[0])._TAX_CLASS));
			
			array_push($articles, array(
					'articleId'				  => $itemId,
					'articleNumber'			  => '',
					'articleTitle'			  => $totals['ot_shipping']['title'],
					'articleDescription'	  => '',
					'articleQuantity'		  => 1,
					'articlePrice'			  => $_POST['value_shipping'],
					'articleTax'			  => $tax,
					'articleOrdersProductsId' => '',
					'articleOrdersId'		  => $_GET['oID'],
					'articleType'			  => 'shipping'
					)
			);
		}
		
		if (isset($_POST['value_ot_sofort'])){
			$itemId = 'discount|ot_sofort';
			$tax = shopGetTaxRate(MODULE_ORDER_TOTAL_SOFORT_TAX_CLASS);
			
			array_push($articles, array(
					'articleId'				  => $itemId,
					'articleNumber'			  => '',
					'articleTitle'			  => $totals['ot_sofort']['title'],
					'articleDescription'	  => '',
					'articleQuantity'		  => 1,
					'articlePrice'			  => $_POST['value_ot_sofort'],
					'articleTax'			  => $tax,
					'articleOrdersProductsId' => '',
					'articleOrdersId'		  => $_GET['oID'],
					'articleType'			  => 'discount'
					)
			);
		}
		
		if (isset($_POST['value_ot_discount'])){
			$itemId = 'discount|ot_discount';
			$tax = 19;
			
			array_push($articles, array(
					'articleId'				  => $itemId,
					'articleNumber'			  => '',
					'articleTitle'			  => $totals['ot_discount']['title'],
					'articleDescription'	  => '',
					'articleQuantity'		  => 1,
					'articlePrice'			  => $_POST['value_ot_discount'],
					'articleTax'			  => $tax,
					'articleOrdersProductsId' => '',
					'articleOrdersId'		  => $_GET['oID'],
					'articleType'			  => 'discount'
					)
			);
		}
		
		if (isset($_POST['value_ot_gv'])){
			$itemId = 'discount|ot_gv';
			$tax = shopGetTaxRate(MODULE_ORDER_TOTAL_GV_TAX_CLASS);
			
			array_push($articles, array(
					'articleId'				  => $itemId,
					'articleNumber'			  => '',
					'articleTitle'			  => $totals['ot_gv']['title'],
					'articleDescription'	  => '',
					'articleQuantity'		  => 1,
					'articlePrice'			  => $_POST['value_ot_gv'],
					'articleTax'			  => $tax,
					'articleOrdersProductsId' => '',
					'articleOrdersId'		  => $_GET['oID'],
					'articleType'			  => 'discount'
					)
			);
		}
		
		if (isset($_POST['value_ot_coupon'])){
			$itemId = 'discount|ot_coupon';
			$tax = shopGetTaxRate(MODULE_ORDER_TOTAL_COUPON_TAX_CLASS);
			
			array_push($articles, array(
					'articleId'				  => $itemId,
					'articleNumber'			  => '',
					'articleTitle'			  => $totals['ot_coupon']['title'],
					'articleDescription'	  => '',
					'articleQuantity'		  => 1,
					'articlePrice'			  => $_POST['value_ot_coupon'],
					'articleTax'			  => $tax,
					'articleOrdersProductsId' => '',
					'articleOrdersId'		  => $_GET['oID'],
					'articleType'			  => 'discount'
					)
			);
		}
		
		if (isset($_POST['value_ot_loworderfee'])){
			$itemId = 'agio|ot_loworderfee';
			$tax = shopGetTaxRate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS);
			
			array_push($articles, array(
					'articleId'				  => $itemId,
					'articleNumber'			  => '',
					'articleTitle'			  => $totals['ot_loworderfee']['title'],
					'articleDescription'	  => '',
					'articleQuantity'		  => 1,
					'articlePrice'			  => $_POST['value_ot_loworderfee'],
					'articleTax'			  => $tax,
					'articleOrdersProductsId' => '',
					'articleOrdersId'		  => $_GET['oID'],
					'articleType'			  => 'agio'
					)
			);
		}
		
		$query = shopDbQuery('SELECT transaction_id FROM sofort_orders WHERE orders_id = "'.$_GET['oID'].'"');
		$result = shopDbFetchArray($query);
		
		$sofortOrderSynchronisation = new sofortOrderSynchronisation();
		$errors = $sofortOrderSynchronisation->editArticlesSofort($result['transaction_id'], $articles, $_POST['sofort_update_comment']);
		
		if($errors){
			if ($errors['errors']) {
				$errorCodes .= "<div class='sofort_error'>";
				
				foreach ($errors['errors'] as $oneError) {
					if (defined('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$oneError['code'])) {
						$errorCodes .= constant('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$oneError['code'])."<br/>";
					} else {
						$errorCodes .= MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_0."<br/>";
					}
				}
				$errorCodes .= "</div>";
			}
			
			if ($errors['warnings']) {
				$errorCodes .= "<div class='sofort_error'>";
				
				foreach ($errors['warnings'] as $oneWarning) {
					if (defined('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$oneWarning['code'])) {
						$errorCodes .= constant('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_'.$oneWarning['code'])."<br/>";
					} else {
						$errorCodes .= MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_0."<br/>";
					}
				}
				$errorCodes .= "</div>";
			}
		} else {
			$successCodes .= "<div class='sofort_success'>".MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_EDITED."</div>";
		}
	}
	
	if ($errorCodes || $successCodes) {
		header ("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."&errorText=".str_replace('%27','%2527',urlencode($errorCodes))."&successText=".str_replace('%27','%2527',urlencode($successCodes)));
	}
	
	require_once(shopGetTop());
	$order = new order($_GET['oID']); //reinitialized on purpose
	
	echo "\n\n";
	echo "<div id='sofort'>\n";
	
	$hint = explode('.', MODULE_PAYMENT_SOFORT_SR_UPDATE_DISCOUNTS_HINT);
	
	echo '	<script type="text/javascript">
		function sofortProductCheck(inputId, originalValue, fieldNumber, inputClass, compareInputId, compareOriginalValue) {
			var toCheck = document.getElementById(inputId);
			
			if(isNaN(toCheck.value)){
				alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_AND_QUANTITY_NAN.'");
				toCheck.value = parseFloat(originalValue);
				toCheck.select();
				return;
			}
			
			if (!(parseFloat(toCheck.value) >= parseFloat(1)) && inputClass =="qty"){
				alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_ZERO_HINT.'");
				toCheck.value = parseFloat(originalValue);
				toCheck.select();
				return;
			}
			
			if (parseFloat(toCheck.value) < parseFloat(0)) {
				alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_VALUE_LTZERO_HINT.'");
				toCheck.value = parseFloat(originalValue);
				toCheck.select();
				return;
			}
			
			if (parseFloat(toCheck.value) > parseFloat(originalValue) && parseFloat(toCheck.value) >= 0){
				if (inputClass =="qty"){
					alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_HINT.'");
				} else if (inputClass =="price"){
					alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_HINT.'");
				} else if (inputClass =="shipping"){
					alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_HINT.'");
				}
				
				toCheck.value = parseFloat(originalValue);
				toCheck.select();
				return;
			}
			
			if (inputClass !="shipping"){
				var compare = document.getElementById(compareInputId);
				
				if (parseFloat(toCheck.value) != parseFloat(originalValue) && parseFloat(compare.value) != parseFloat(compareOriginalValue)){
					alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_AND_QUANTITY_HINT.'");
					toCheck.value = parseFloat(originalValue);
					toCheck.select();
					return;
				}
			}
			
			var newSum = 0;
			
			for (var i = 0; i >= 0; i++){
				nameQty = "qty_" + i;
				namePrice = "price_" + i;
				
				if (document.getElementById(nameQty) == undefined){
					break;
				}
				
				if (i != fieldNumber){
					newSum = newSum + (parseFloat(document.getElementById(nameQty).value) * parseFloat(document.getElementById(namePrice).value));
				}
			}
			
			for (var j = 0; j <= 5; j++){
				nameTotal = "total_" + j;
				if(document.getElementById(nameTotal) != undefined){
					newSum = newSum + parseFloat(document.getElementById(nameTotal).value);
				}
			}
			
			if (newSum < 0){
				if (inputClass =="qty"){
					alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_TOTAL_GTZERO.'");
				} else if (inputClass =="price"){
					alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_TOTAL_GTZERO.'");
				}  else if (inputClass =="shipping"){
					alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_TOTAL_GTZERO.'");
				}
				
				toCheck.value = parseFloat(originalValue);
				toCheck.select();
				return;
			}
		}
		
		function sofortRabattCheck(countItems, oldSum, fieldId, fieldValue, disAgio){
			var toCheck = document.getElementById(fieldId);
			var nameQty = "";
			var namePrice = "";
			var nameTotal = "";
			var newSum = 0;
			
			if(isNaN(toCheck.value)){
				alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_AND_QUANTITY_NAN.'");
				toCheck.value = parseFloat(fieldValue);
				toCheck.select();
				return;
			}
			
			if (disAgio == "discount" && parseFloat(toCheck.value) > parseFloat(0)){
				alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_DISCOUNTS_GTZERO_HINT.'");
				toCheck.value = parseFloat(fieldValue);
				toCheck.select();
				return;
			}
			
			for (var i = 0; i < countItems; i++){
				nameQty = "qty_" + i;
				namePrice = "price_" + i;
				newSum = newSum + (parseFloat(document.getElementById(nameQty).value) * parseFloat(document.getElementById(namePrice).value));
			}
			
			for (var j = 0; j <= 5; j++){
				nameTotal = "total_" + j;
				if(document.getElementById(nameTotal) != undefined){
					newSum = newSum + parseFloat(document.getElementById(nameTotal).value);
				}
			}
			
			if ( newSum > oldSum ) {
				alert("'.$hint[2].'");
				toCheck.value = parseFloat(fieldValue);
				toCheck.select();
				return;
			}
		}
		
		function sofortDelete(fieldId, fieldNumber, productName) {
			if (document.getElementById(fieldId).checked == false) {
				document.getElementById("sofort_delete_all").value = "0";
				return;
			}
			
			var newSum = 0;
			
			for (var i = 0; i >= 0; i++){
				nameQty = "qty_" + i;
				namePrice = "price_" + i;
				
				if (document.getElementById(nameQty) == undefined){
					break;
				}
				
				if (i != fieldNumber){
					newSum = newSum + (parseFloat(document.getElementById(nameQty).value) * parseFloat(document.getElementById(namePrice).value));
				}
			}
			
			for (var j = 0; j <= 5; j++){
				nameTotal = "total_" + j;
				if(document.getElementById(nameTotal) != undefined){
					newSum = newSum + parseFloat(document.getElementById(nameTotal).value);
				}
			}
			
			if (newSum < 0){
				alert("'.MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_TOTAL_GTZERO.'");
				document.getElementById("sofort_delete_all").value = "0";
				document.getElementById(fieldId).checked = false;
				return;
			}
			
			var deleteQuestion = "'.MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_QUESTION.'";
			var deleteLastItemQuestion = "'.MODULE_PAYMENT_SOFORT_SR_REMOVE_LAST_ARTICLE_HINT.'";
			deleteQuestion = deleteQuestion.replace("%s",productName);
			
			if(confirm(deleteQuestion) == true){
				var nameDelete = "";
				
				for (var i = 0; i >= 0; i++){
					nameDelete = "delete_product_" + i;
					if(document.getElementById(nameDelete) != undefined){
						if(document.getElementById(nameDelete).checked == true){
							continue;
						} else {
							return;
						}
					} else {
						if(confirm(deleteLastItemQuestion)){
							document.getElementById("sofort_delete_all").value = "1";
							return;
						} else {
							document.getElementById("sofort_delete_all").value = "0";
							document.getElementById(fieldId).checked = false;
							return;
						}
					}
				}
				
				return;
			} else {
				document.getElementById("sofort_delete_all").value = "0";
				document.getElementById(fieldId).checked = false;
			}
		}
		
		function sofortSubmit() {'."\n";
	
	switch($PnagInvoice->getStatusReason()){
			case 'not_credited_yet':
			case 'credited':	echo '			var commentfield = document.getElementById("sofort_update_comment");
			
			if (!commentfield.value || commentfield.value.length === 0) {
				alert("'.MODULE_PAYMENT_SOFORT_SR_UPDATE_CONFIRMED_INVOICE_HINT.'");
				return false;
			}';
						break;
			default:	echo '			return true;';
						break;
	}
	echo "\n		}
		
		function sofortRefresh() {
			 window.location.reload();
		}
	</script>\n";
	
	echo urldecode($_GET['errorText']);
	echo urldecode($_GET['successText']);
	
	echo "	<div id='sofort_head'>\n";
	echo "		<div class='sofort_hfl'>".$logo."</div>\n";
	echo "		<div class='sofort_hfl' style='padding-top:5px'>".HEADING_TITLE." Nr : ".$oID." - ".ENTRY_DATE_PURCHASED." ".shopDatetimeShort($order->info['date_purchased'])."</div>\n";
	echo "		<div class='sofort_hfr'><a target='_self' class='sofort_info_icon' href='http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."' onclick='sofortRefresh();'><img src='".shopGetIconPath()."refresh.gif' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_REFRESH_PAGE."</span></span></a></div>\n";
	echo "	</div>\n";
	
	echo "	<br clear='all'/>\n";
	
	echo "	<div id='sofort_infos'>\n";
	echo "		<div id='sofort_customer_infos'>\n";
	echo "			<div id='sofort_customer'>\n";
	echo "				<div class='sofort_flb'>".ENTRY_CUSTOMER."</div>\n";
	echo "				<div class='sofort_fl'><br/>".shopAdressFormat($order->customer['format_id'], $order->customer, 1, '', '<br/>')."</div>\n";
	echo "			</div>\n";
	echo "			<div class='vr'></div>\n";
	echo "			<div id='sofort_delivery'>\n";
	echo "				<div class='sofort_flb'>".ENTRY_SHIPPING_ADDRESS."</div>\n";
	echo "				<div class='sofort_fl'><br/>".shopAdressFormat($order->delivery['format_id'], $order->delivery, 1, '', '<br/>')."</div>\n";
	echo "			</div>\n";
	echo "			<div class='vr'></div>\n";
	echo "			<div id='sofort_billing'>\n";
	echo "				<div class='sofort_flb'>".ENTRY_BILLING_ADDRESS."</div>\n";
	echo "				<div class='sofort_fl'><br/>".shopAdressFormat($order->billing['format_id'], $order->billing, 1, '', '<br/>')."</div>\n";
	echo "			</div>\n";
	echo "		</div>\n";
	
	echo "		<br clear='all'/>\n";
	
	echo "		<div id='sofort_additional_infos'>\n";
	echo "			<div class='sofort_hflb'>\n";
	echo "				".ENTRY_TELEPHONE_NUMBER."<br/>\n";
	echo "				".ENTRY_EMAIL_ADDRESS."<br/>\n";
	echo "				".ENTRY_CUSTOMERS_VAT_ID."<br/>\n";	
	echo "			</div>\n";
	echo "			<div class='sofort_hfl' style='width: 250px;'>\n";
	echo "				".$order->customer['telephone']."<br/>\n";
	echo "				<a href='mailto:".$order->customer['email_address']."' style='color:'".$paymentMethodColor."'><u>".$order->customer['email_address']."</u></a><br/>\n";
	echo "				".$order->customer['vat_id']."\n";
	echo "			</div>\n";
	echo "			<div class='sofort_hflb'>\n";
	echo "				".CUSTOMERS_MEMO."<br/>\n";
	echo "				".IP."<br/>\n";
	echo "				".ENTRY_PAYMENT_METHOD."<br/>\n";
	echo "			</div>\n";
	echo "			<div class='sofort_hfl' style='width: 250px;'>\n";
	
	$memo_count = shopDbFetchArray(shopDbQuery("SELECT count(*) as count FROM ".TABLE_CUSTOMERS_MEMO." where customers_id='".$order->customer['ID']."'"));
	
	echo "				".$memo_count['count']."<a style='cursor:pointer' onclick='javascript:window.open(\"".shopHrefLink(FILENAME_POPUP_MEMO,'ID='.$order->customer['ID'])."\", \"popup\", \"scrollbars=yes, width=500, height=500\")'>(".DISPLAY_MEMOS.")</a><br/>\n";
	echo "				".$order->customer['cIP']."<br/>\n";
	echo "				".$order->info['payment_method']."<br/>\n";
	echo "			</div>\n";
	echo "		</div>\n";
	echo "	</div>\n";
	
	echo "	<br clear='all'/>\n";
	
	echo "	<div id='sofort_orders'>\n";
	echo "		<table class='sofort_table'>\n";
	echo "			<tr class='sofort_htr'>\n";
	echo "				<td class='sofort_htd' align='left' colspan='2'>".TABLE_HEADING_PRODUCTS."</td>\n";
	echo "				<td class='sofort_htd' align='left' width='10%'>".TABLE_HEADING_PRODUCTS_MODEL."</td>\n";
	echo "				<td class='sofort_htd' align='right' width='5%'>".TABLE_HEADING_TAX."</td>\n";
	echo "				<td class='sofort_htd' align='right' width='12%'>".TABLE_HEADING_PRICE_EXCLUDING_TAX."</td>\n";
	echo "				<td class='sofort_htd' align='right' width='12%'>".TABLE_HEADING_PRICE_INCLUDING_TAX."</td>\n";
	echo "				<td class='sofort_htd' align='right' width='12%'>".TABLE_HEADING_TOTAL_EXCLUDING_TAX."</td>\n";
	echo "				<td class='sofort_htd' align='right' width='12%'>".TABLE_HEADING_TOTAL_INCLUDING_TAX."</td>\n";
	
	if ($_POST['sofort_action'] == 'sofort_edit') {
		echo "				<td class='sofort_htd' align='center' width='8%'>".MODULE_PAYMENT_SOFORT_SR_REMOVE_ARTICLE_FROM_INVOICE."</td>";
		echo "			</tr>\n";
		echo "			<form name='edit_products' action='' method='post' onSubmit='return sofortSubmit();'>\n";
		echo "			<input type='hidden' name='sofort_action' value='sofort_save'/>\n";
	} else {
		echo "			</tr>\n";
	}
	
	
	for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
		$bgColor = ($i%2) ? '#E0E0E0': '#FAFAFA';
		echo "			<tr class='sofort_tr' style='background-color:".$bgColor."'>\n";
		
		if ($_POST['sofort_action'] == 'sofort_edit') {
			$singlePrice = $order->products[$i]['final_price'] / $order->products[$i]['qty'];
			
			echo "				<input type='hidden' name='opid_product[]' value='".$order->products[$i]['opid']."'/>\n";
			echo "				<td class='sofort_td' align='right'><a target='_blank' class='sofort_info_icon' href='#' onclick='return false;'><img src='".shopGetTooltipImage()."' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_HINT."</span></span></a>\n";
			echo "				<input type='text' size ='3' id='qty_".$i."' name='qty_product[]' value='".$order->products[$i]['qty']."' onBlur='sofortProductCheck(\"qty_".$i."\",\"".$order->products[$i]['qty']."\",\"".$i."\",\"qty\",\"price_".$i."\",\"".$singlePrice."\");'/> x</td>\n";
		} else {
			echo "				<td class='sofort_td' align='right'>".$order->products[$i]['qty']." x</td>\n";
		}
		echo "				<td class='sofort_td' align='left'>".$order->products[$i]['name']."";
		
		$productName = $order->products[$i]['name'];
		
		if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
			for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
				echo "<br/><nobr><small><i> - ".$order->products[$i]['attributes'][$j]['option'].": ".nl2br($order->products[$i]['attributes'][$j]['value']);
				
				$productName .= " | ".$order->products[$i]['attributes'][$j]['option'].": ".$order->products[$i]['attributes'][$j]['value'];
				
				if ($order->products[$i]['attributes'][$j]['price'] != '0'){
					echo " (".$order->products[$i]['attributes'][$j]['prefix'].$currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']).")";
				}
				
				echo "</i></small></nobr>"; 
			}
		}
		
		echo "</td>\n";
		echo "				<td class='sofort_td' align='left'>";
		echo ($order->products[$i]['model'] != '') ? $order->products[$i]['model'] : "<br/>";
		
		if (sizeof($order->products[$i]['attributes']) > 0) {
			for ($j=0, $k=sizeof($order->products[$i]['attributes']); $j<$k; $j++) {
				$model = xtc_get_attributes_model($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['value'],$order->products[$i]['attributes'][$j]['option']);
				echo ($model != '') ? "<br/><small><i>".$model."</i></small>": "<br/>";
			}
		}
			
		echo "</td>\n";
		echo "				<td class='sofort_td' align='right'>".shopDisplayTaxValue($order->products[$i]['tax'])." %</td>\n";
		echo "				<td class='sofort_td' align='right'>".format_price($order->products[$i]['final_price'] / $order->products[$i]['qty'], 1, $order->info['currency'], $order->products[$i]['allow_tax'], $order->products[$i]['tax'])."</td>\n";
			
		if($_POST['sofort_action'] == 'sofort_edit'){
			$singlePrice = $order->products[$i]['final_price'] / $order->products[$i]['qty'];
			echo "				<td class='sofort_td' align='right'>\n";
			echo "					<input type='text' size ='5' id='price_".$i."' name='price_product[]' value='".$singlePrice."' onBlur='sofortProductCheck(\"price_".$i."\",\"".$singlePrice."\",\"".$i."\",\"price\",\"qty_".$i."\",\"".$order->products[$i]['qty']."\");'/><a target='_blank' class='sofort_info_icon' href='#' onclick='return false;'><img src='".shopGetTooltipImage()."' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_HINT."</span></span></a>";
			echo "				</td>\n";
		} else {
			echo "				<td class='sofort_td' align='right'>".format_price($order->products[$i]['final_price'] / $order->products[$i]['qty'], 1, $order->info['currency'], 0, 0)."</td>\n";
		}
		
		echo "				<td class='sofort_td' align='right'>".format_price($order->products[$i]['final_price'], 1, $order->info['currency'], $order->products[$i]['allow_tax'], $order->products[$i]['tax'])."</td>\n";
		echo "				<td class='sofort_td' align='right'><strong>".format_price(($order->products[$i]['final_price']), 1, $order->info['currency'], 0, 0)."</strong></td>\n";
		
		if($_POST['sofort_action'] == 'sofort_edit'){
			echo "				<td class='sofort_td' align='center'><input type='checkbox' id='delete_product_".$i."' name='delete_product[".$i."]' value='delete' onChange='sofortDelete(\"delete_product_".$i."\",\"".$i."\",\"".$productName."\")'/><a target='_blank' class='sofort_info_icon' href='#' onclick='return false;'><img src='".shopGetTooltipImage()."' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_HINT."</span></span></a><td>\n";
		}
			
		echo "			</tr>\n";
	}
	
	$itemsCnt = $i;
	
	echo "			<tr class='sofort_tr'>\n";
	
	if ($_POST['sofort_action'] == 'sofort_edit') {
		echo "				<td colspan='9' align='right'>\n";
	} else {
		echo "				<td colspan='8' align='right'>\n";
	}
	
	echo "					<table class='sofort_total'>\n";
	
	if ($_POST['sofort_action'] == 'sofort_edit') {
		$query = "SELECT title, value, class FROM ".TABLE_ORDERS_TOTAL." WHERE class != 'ot_subtotal' AND class !='ot_tax' AND class != 'ot_total' AND orders_id = '".$_GET['oID']."'";
		$result = shopDbQuery($query);
		
		while($row = shopDbFetchArray($result)){
			$totalsArray[] = $row;
		}
		
		$oldTotalQry = shopDbQuery("SELECT value as old_total FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id= '".$_GET['oID']."' AND class = 'ot_total'");
		$oldTotal = shopDbFetchArray($oldTotalQry);
		
		foreach ($order->totals as $singleTotal) {
			echo "						<tr>\n";
			echo "							<td class='sofort_ttd' align='right' class='smallText'>".$singleTotal['title']."</td>\n";
			echo "							<td class='sofort_ttd' align='center'>\n";
			
			$done = false;
			
			foreach ($totalsArray as $coreTotal) {
				if ($singleTotal['title'] == $coreTotal['title']){
					switch ($coreTotal['class']){
						case 'ot_shipping':		$id = 0; $name = 'value_shipping';		 break;
						case 'ot_sofort':		$id = 1; $name = 'value_ot_sofort';		 break;
						case 'ot_discount':		$id = 2; $name = 'value_ot_discount';	 if($coreTotal['value']>0) $coreTotal['value'] = (-1)*$coreTotal['value']; break;
						case 'ot_gv':			$id = 3; $name = 'value_ot_gv';			 if($coreTotal['value']>0) $coreTotal['value'] = (-1)*$coreTotal['value']; break;
						case 'ot_coupon':		$id = 4; $name = 'value_ot_coupon';		 if($coreTotal['value']>0) $coreTotal['value'] = (-1)*$coreTotal['value']; break;
						case 'ot_loworderfee':	$id = 5; $name = 'value_ot_loworderfee'; break;
					}
					
					$j = $i+1;
					
					if($id === 0){
						echo "								<input type='text' id='total_".$id."' name='".$name."' size='6' onBlur='sofortProductCheck(\"total_".$id."\",\"".number_format($coreTotal['value'],2)."\",\"".$j."\",\"shipping\",\"\",\"\");' value='".number_format($coreTotal['value'],2)."'/><a target='_blank' class='sofort_info_icon' href='#' onclick='return false;'><img src='".shopGetTooltipImage()."' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_HINT."</span></span></a>\n";
						$done = true;
					} elseif ($id != 5 && isset($id)) {
						echo "								<input type='text' id='total_".$id."' name='".$name."' size='6' onBlur ='sofortRabattCheck(".$itemsCnt.", ".$oldTotal['old_total'].", \"total_".$id."\", ".number_format($coreTotal['value'],2).", \"discount\")' value='".number_format($coreTotal['value'],2)."'/><a target='_blank' class='sofort_info_icon' href='#' onclick='return false;'><img src='".shopGetTooltipImage()."' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_SR_UPDATE_DISCOUNTS_HINT."</span></span></a>\n";
						$done = true;
					} elseif (isset($id)) {
						echo "								<input type='text' id='total_".$id."' name='".$name."' size='6' onBlur ='sofortRabattCheck(".$itemsCnt.", ".$oldTotal['old_total'].", \"total_".$id."\", ".number_format($coreTotal['value'],2).", \"agio\")' value='".number_format($coreTotal['value'],2)."'/><a target='_blank' class='sofort_info_icon' href='#' onclick='return false;'><img src='".shopGetTooltipImage()."' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_SR_UPDATE_DISCOUNTS_HINT."</span></span></a>\n";
						$done = true;
					}
					
					unset($id);
					continue;
				}
			}
			
			if(!$done){
				echo "								".MODULE_PAYMENT_SOFORT_SR_RECALCULATION."\n";
			}
			
			echo "							</td>\n";
			echo "						</tr>\n";
		}
	} else {
		for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
			echo "						<tr>\n";
			echo "							<td class='sofort_ttd' align='right'>".$order->totals[$i]['title']."</td>\n";
			echo "							<td class='sofort_ttd' align='right'>".$order->totals[$i]['text']."</td>\n";
			echo "						</tr>\n";
		}
	}
	echo "					</table>\n";
	echo "				</td>\n";
	echo "			</tr>\n";
	echo "		</table>\n";
	echo "		<div style='width:860px;text-align:right'>\n";
	
	if ($_POST['sofort_action'] != 'sofort_edit' && $paymentMethodShort == 'SR' && $PnagInvoice->getStatusReason() != 'canceled' && $PnagInvoice->getStatusReason() != 'confirmation_period_expired' && $PnagInvoice->getStatusReason() != 'refunded'){
		echo "			<form name='edit' action='' method='post'>\n";
		echo "				<input type='hidden' name='sofort_action' value='sofort_edit'/>\n";
		echo "				<input type='submit' value='".MODULE_PAYMENT_SOFORT_SR_EDIT_CART."'/>\n";
	} elseif ($_POST['sofort_action'] == 'sofort_edit') {
		echo "			<input type='hidden' name='sofort_action' value='sofort_save'/>\n";
		echo "			<div class='sofort_save'>\n";
		echo "				<input type='hidden' id='sofort_delete_all' name='sofort_delete_all' value='0'/>\n";
		echo "				<input type='submit' value='".MODULE_PAYMENT_SOFORT_SR_UPDATE_CART."'/> \n";
		echo "				<a target='_blank' class='sofort_info_icon' href='#' onclick='return false;'><img src='".shopGetTooltipImage()."' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_SR_UPDATE_CART_HINT."</span></span></a>\n";
		echo "			</div>\n";
		echo "			<div class='sofort_etarea' style='float: right;'><textarea id='sofort_update_comment' name='sofort_update_comment' wrap='soft' cols='60' rows='6'></textarea></div>\n";
		echo "			<div class='sofort_etitle' style='float: right;'>";
		echo 				MODULE_PAYMENT_SOFORT_SR_UPDATE_CONFIRMED_INVOICE." <a target='_blank' class='sofort_info_icon' href='#' onclick='return false;'><img src='".shopGetTooltipImage()."' class='sofort_label_icon' alt=''><span class='sofort_on_over'><span class='sofort_on_over_head'></span><span class='sofort_on_over_inner'>".MODULE_PAYMENT_SOFORT_SR_UPDATE_CONFIRMED_INVOICE_HINT."</span></span></a>\n";
		echo "			</div>\n";
	}
	
	echo "			<input type='hidden' id='item_count' value='".$itemsCnt."'/>";
	echo "		</form>\n";
	echo "		</div>\n";
	echo "	</div>\n";
	
	echo "	<br clear='all'/><br/><br/>\n";
	
	echo "	<table class='sofort_table'>\n";
	echo "		<tr class='sofort_htr'>\n";
	echo "			<td class='sofort_htd' align='center' width='16%'>".TABLE_HEADING_DATE_ADDED."</td>\n";
	echo "			<td class='sofort_htd' align='center' width='16%'>".TABLE_HEADING_CUSTOMER_NOTIFIED."</td>\n";
	echo "			<td class='sofort_htd' align='center' width='16%'>".TABLE_HEADING_STATUS."</td>\n";
	echo "			<td class='sofort_htd' align='center' width='52%'>".TABLE_HEADING_COMMENTS."</td>\n";
	echo "		</tr>\n";
	
	$ordersHistoryQuery = shopDbQuery('SELECT orders_status_id, date_added, customer_notified, comments FROM '.TABLE_ORDERS_STATUS_HISTORY.' WHERE orders_id = \''.$oID.'\' ORDER BY date_added');
	
	if (shopDbNumRows($ordersHistoryQuery)){
		$i=0;
		
		while ($ordersHistory = shopDbFetchArray($ordersHistoryQuery)){
			$bgColor = ($i%2) ? '#E0E0E0': '#FAFAFA';
			$i++;
			
			echo "		<tr class='sofort_tr' style='background-color:".$bgColor."'>\n";
			echo "			<td class='sofort_td' align='center'>".shopDatetimeShort($ordersHistory['date_added'])."</td>\n";
			echo "			<td class='sofort_td' align='center'>";
			
			if ($ordersHistory['customer_notified'] == '1') {
				echo "<img src='".shopGetIconPath()."ok.gif' />";
			} elseif ($ordersHistory['customer_notified'] == '-1') {
				echo "<img src='".shopGetIconPath()."lock.gif' />";
			} else {
				echo "<img src='".shopGetIconPath()."error.gif' />";
			}
			
			echo "</td>\n";
			echo "			<td class='sofort_td' align='left'>".$ordersStatusArray[$ordersHistory['orders_status_id']]."</td>\n";
			echo "			<td class='sofort_td' align='left'>".nl2br(shopDbOutput($ordersHistory['comments']))."</td>\n";
			echo "		</tr>\n";
		}
	} else {
		echo "		<tr>";
		echo "			<td class='smallText' colspan='5'>".TEXT_NO_ORDER_HISTORY."</td>";
		echo "		</tr>";
	}
	
	echo "	</table>\n";
	
	if ($paymentMethodShort == 'SR'){
		echo " <div class='sofort_hint'> * ".str_replace('{{refresh}}', '<a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"].'" target="_self" onClick="sofortRefresh()">'.strtolower(BUTTON_UPDATE).'</a>', MODULE_PAYMENT_SOFORT_REFRESH_INFO)."</div>";
	}
	
	echo "	<br clear='all'/>\n";
	
	echo "	<div id='sofort_buttons'>\n";
	
	if ($paymentMethodShort == 'SR'){
		echo "		<form name='sofort_buttons' action='' method='post'>\n";
		echo "			<input type='hidden' name='sofort_action' value='sofort_buttons'>\n";
		
		switch($PnagInvoice->getStatusReason()){
			case 'confirm_invoice'			  :	echo "<button type='submit' name='sofort_button' value='confirm' title='".MODULE_PAYMENT_SOFORT_SR_CONFIRM_INVOICE."'>".MODULE_PAYMENT_SOFORT_SR_CONFIRM_INVOICE."</button> ";
												echo "<button type='submit' name='sofort_button' value='preview' title='".MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE_HINT."'>".MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE_PREVIEW."</button> ";
												echo "<button type='submit' name='sofort_button' value='cancel' onclick='return confirm(\"".MODULE_PAYMENT_SOFORT_SR_CANCEL_INVOICE_QUESTION."\");' title='".MODULE_PAYMENT_SOFORT_SR_CANCEL_INVOICE."'>".MODULE_PAYMENT_SOFORT_SR_CANCEL_INVOICE."</button> ";
												break;
			case 'not_credited_yet'			  :
			case 'credited'					  :	echo "<button type='submit' name='sofort_button' value='invoice' title='".MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE_HINT."'>".MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE."</button> ";
												echo "<button type='submit' name='sofort_button' value='cancel' onclick='return confirm(\"".MODULE_PAYMENT_SOFORT_SR_CANCEL_CONFIRMED_INVOICE_QUESTION."\");' title='".MODULE_PAYMENT_SOFORT_SR_CANCEL_CONFIRMED_INVOICE."'>".MODULE_PAYMENT_SOFORT_SR_CANCEL_CONFIRMED_INVOICE."</button> ";
												break;
			case 'refunded'					  :	echo "<button type='submit' name='sofort_button' value='credit' title='".MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE_HINT."'>".MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_CREDIT_MEMO."</button> ";
												break;
			case 'canceled'					  :	break;
			case 'confirmation_period_expired':	break;
			default							  :	break;
		}
	} else {
		echo "			<a class='sofort_button' href='Javascript:void()' onclick='window.open(\"".shopHrefLink(shopGetInvoiceLink(),'oID='.$_GET['oID'])."\", \"popup\", \"toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=800, height=600\")'>".shopGetInvoiceText()."</a>\n";
	}
	
	echo "			<a class='sofort_button'  href='Javascript:void()' onclick='window.open(\"".shopHrefLink(shopGetPackingslipLink(),'oID='.$_GET['oID'])."\", \"popup\", \"toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=800, height=600\")'>".shopGetPackingslipText()."</a>\n";
	echo "			<a class='sofort_button'  href='".shopHrefLink(FILENAME_ORDERS,'oID='.$_GET['oID'])."'>".shopGetBackText()."</a>\n";
	
	if($paymentMethodShort == 'SR'){
		echo "		</form>\n";
	}
	
	echo "	</div>\n";
	
	echo "	<br clear='all'/><br/>\n";
	
	echo "	<div id='sofort_comments'>\n";
	echo "		<div class='sofort_ctitle' style='float: left;'>".TABLE_HEADING_COMMENTS."</div>\n";
	echo "			".shopDrawForm('status', FILENAME_ORDERS, shopGetAllGetParams(array('sofort_action')))."\n";
	echo "				<input type='hidden' name='sofort_action' value='sofort_comment'/>\n";
	echo "				<div class='sofort_ctarea' style='float: left;'>\n";
	echo "					".shopDrawTextareaField('comments', 'soft', '60', '6', $order->info['comments'])."\n";
	echo "				</div>\n";
	echo "				<div class='sofort_copt'>\n";
	echo "					<table>\n";
	echo "						<tr>\n";
	echo "							<td class='sofort_chtd'>".ENTRY_STATUS."</td>\n";
	echo "							<td class='sofort_ctd' style='text-align:center'>".shopDrawPullDownMenu('status', $ordersStatuses, $order->info['orders_status'])."</td>\n";
	echo "						</tr>\n";
	echo "						<tr>\n";
	echo "							<td class='sofort_chtd'>".ENTRY_NOTIFY_CUSTOMER."</td>\n";
	echo "							<td class='sofort_ctd' style='text-align:center'>\n";
	echo "								".shopDrawCheckboxField('notify', '', true)."\n";
	echo "							</td>\n";
	echo "						</tr>\n";
	echo "						<tr>\n";
	echo "							<td class='sofort_chtd'>".ENTRY_NOTIFY_COMMENTS."</td>\n";
	echo "							<td class='sofort_ctd' style='text-align:center'>".shopDrawCheckboxField('notify_comments', '', true)."</td>\n";
	echo "						</tr>\n";
	echo "						<tr>\n";
	echo "							<td></td>\n";
	echo "							<td class='sofort_ctd' style='text-align:center'><input type='submit' value='(".substr(ENTRY_STATUS,0,-1).") - ".BUTTON_UPDATE."' title='".BUTTON_UPDATE."'/></td>\n";
	echo "						</tr>\n";
	echo "					</table>\n";
	echo "				</div>\n";
	echo "			</form>\n";
	echo "		</div>\n";
	echo "	</div>\n";
	echo "</div>\n";
	
	require_once(shopGetBottom());
	exit;
}
?>