<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-14 14:26:12 +0200 (Fri, 14 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: callback.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

chdir('../../');
require_once('includes/application_top.php');
require_once(DIR_FS_INC.'xtc_calculate_tax.inc.php');
require_once(DIR_FS_INC.'xtc_address_label.inc.php');
require_once(DIR_FS_INC.'changedatain.inc.php');
require_once(DIR_WS_CLASSES.'payment.php');
require_once(DIR_WS_CLASSES.'shipping.php');
require_once(DIR_WS_CLASSES.'order.php');
require_once(DIR_WS_CLASSES.'order_total.php');

require_once(DIR_FS_CATALOG.'callback/sofort/helperFunctions.php');
require_once(DIR_FS_CATALOG.'callback/sofort/ressources/scripts/sofortOrderSynchronisation.php');
require_once(DIR_FS_CATALOG.'callback/sofort/library/sofortLib.php');
require_once(DIR_FS_CATALOG.'callback/sofort/library/sofortLib_classic_notification.inc.php');
require_once(DIR_FS_CATALOG.'callback/sofort/library/helper/class.invoice.inc.php');

require_once(DIR_FS_CATALOG.'includes/modules/payment/sofort_lastschrift.php');
require_once(DIR_FS_CATALOG.'includes/modules/payment/sofort_sofortlastschrift.php');
require_once(DIR_FS_CATALOG.'includes/modules/payment/sofort_sofortrechnung.php');
require_once(DIR_FS_CATALOG.'includes/modules/payment/sofort_sofortueberweisung.php');
require_once(DIR_FS_CATALOG.'includes/modules/payment/sofort_sofortvorkasse.php');

$otTotalDir = DIR_FS_CATALOG.'includes/modules/order_total';

if ($handle = opendir($otTotalDir)) {
	while (false !== ($file = readdir($handle))) {
		if ('.' === $file) continue;
		if ('..' === $file) continue;
		require_once($otTotalDir.'/'.$file);
	}
	
	closedir($handle);
}

$language = HelperFunctions::getSofortLanguage($_SESSION['language']);

include(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/sofort_sofortvorkasse.php');
include(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/sofort_sofortueberweisung.php');
include(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/sofort_sofortrechnung.php');
include(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/sofort_sofortlastschrift.php');
include(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/sofort_lastschrift.php');
include(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/sofort_ideal.php');

if ($_GET['action'] == 'ideal'){ // iDeal
	list ($userid, $projectid) = split(':', MODULE_PAYMENT_SOFORT_IDEAL_CLASSIC_CONFIGURATION_KEY);
	$SofortLib_ClassicNotification = new SofortLib_ClassicNotification($userid, $projectid, MODULE_PAYMENT_SOFORT_IDEAL_CLASSIC_NOTIFICATION_PASSWORD);
	$SofortLib_ClassicNotification->getNotification();
	
	if ($SofortLib_ClassicNotification->isError()) {
		exit($SofortLib_ClassicNotification->getError());
	}
	
	$transactionId = $SofortLib_ClassicNotification->getTransaction();
	
	if (empty($transactionId)) exit('TransID empty!');
	
	$time = $SofortLib_ClassicNotification->getTime();
	$time = strftime('%Y-%m-%d %H:%M:%S', strtotime($time));
	$statusReason = $SofortLib_ClassicNotification->getStatusReason();
	$status = $SofortLib_ClassicNotification->getStatus();
	$xOrderId = $SofortLib_ClassicNotification->getUserVariable(0);
	$xCustomerId = $SofortLib_ClassicNotification->getUserVariable(1);
	$paymentMethod = 'ideal';
} elseif ($_GET['action'] == 'su'){ // SU-classic
	
} elseif ($_GET['action'] == 'multipay' || !$_GET['action']) { // Multipay
	$SofortLib_Notification = new SofortLib_Notification();
	$transactionId = $SofortLib_Notification->getNotification();
	
	if (empty($transactionId)) exit('TransID empty!');
	
	$time = $SofortLib_Notification->getTime();
	$time = strftime('%Y-%m-%d %H:%M:%S', strtotime($time));
	$SofortLib_TransactionData = new SofortLib_TransactionData(MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY);
	if (!is_object($SofortLib_TransactionData) || !($SofortLib_TransactionData instanceof SofortLib_TransactionData)) {
		exit('Error: TransactionData-Object corrupt.');
	}
	
	$SofortLib_TransactionData->setTransaction($transactionId);
	$SofortLib_TransactionData->sendRequest();
	
	if ($SofortLib_TransactionData->isError()) {
		exit($SofortLib_TransactionData->getError());
	}
	
	$paymentMethod = $SofortLib_TransactionData->getPaymentMethod();
	$paymentSecret = $_REQUEST['paymentSecret'];
	
	$statusReason = $SofortLib_TransactionData->getStatusReason();
	$status = $SofortLib_TransactionData->getStatus();
	$xCustomerId = $SofortLib_TransactionData->getUserVariable(1);
	
	//insert the serialized order into shop-db if it doesnt exist
	$xOrderId = handleOrderInsertion($transactionId, $paymentSecret, $paymentMethod, $xCustomerId);
	
	if (!$xOrderId) {
		exit('Error: No orderId found.');
	}
}

$configuration = getPaymentModuleConfiguration();

if ($paymentMethod == 'sr') { // Rechnung by Sofort
	
	$srOrderStatusArr = array(
		'unconfirmed' => getStatusId($configuration['MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID']),
		'confirmed'	  => getStatusId($configuration['MODULE_PAYMENT_SOFORT_SR_ORDER_STATUS_ID']),
		'cancelled'	  => getStatusId($configuration['MODULE_PAYMENT_SOFORT_SR_CANCEL_STATUS_ID']),
		'check'		  => getStatusId($configuration['MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID']),
	);
	
	$PnagInvoice = new PnagInvoice(MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY, $transactionId);
	
	if ($PnagInvoice->isError()) {
		$errors = $PnagInvoice->getErrors();
		echo MODULE_PAYMENT_SOFORT_ERROR_TERMINATED.' - '.print_r($errors, true);
		exit;
	}
	
	$orderId = getOrderId($transactionId);
	$lastOrderStatus = getLastOrderStatus($orderId);
	$completeInvoiceStatus = $PnagInvoice->getState();
	$newTotal = checkIfNewTotal($PnagInvoice, $orderId);
	$newComments = '';
	
	// Update the order-status
	switch($completeInvoiceStatus) {
		case PnagInvoice::PENDING_CONFIRM_INVOICE:
			updateShopAdresses($SofortLib_TransactionData->getInvoiceAddress(), $SofortLib_TransactionData->getShippingAddress(), $orderId);
			$historyComments = getHistoryComments($completeInvoiceStatus, $transactionId);
			$newComments = updateOrderStatus($PnagInvoice, $orderId, $srOrderStatusArr['unconfirmed'], $historyComments['customer'], $time, $newTotal);
			break;
			
		case PnagInvoice::PENDING_NOT_CREDITED_YET_PENDING:
			//refunded invoice was just reanimated
			if (refundedInvoiceWasJustReanimated($orderId, $PnagInvoice->getStatus())) {
				$newComments = updateOrderStatus($PnagInvoice, $orderId, $srOrderStatusArr['confirmed'], MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REANIMATED, $time, false, true);
			} else {
				$historyComments = getHistoryComments($completeInvoiceStatus);
				$newComments = updateOrderStatus($PnagInvoice, $orderId, $srOrderStatusArr['confirmed'], $historyComments['customer'], $time, $newTotal);
			}
			break;
			
		case PnagInvoice::REFUNDED_REFUNDED_REFUNDED:
		case PnagInvoice::LOSS_CANCELED:
		case PnagInvoice::LOSS_CONFIRMATION_PERIOD_EXPIRED:
			$historyComments = getHistoryComments($completeInvoiceStatus);
			$newComments = updateOrderStatus($PnagInvoice, $orderId, $srOrderStatusArr['cancelled'], $historyComments['customer'], $time, false);
			break;
			
		default:
			if ($newTotal) {
				insertNewTotalCommentToHistory($orderId, $lastOrderStatus, $time, $newTotal);
			}
			$historyComments = array();
			if (refundedInvoiceWasJustReanimated($orderId, $PnagInvoice->getStatus())) {
				$newComments = updateOrderStatus($PnagInvoice, $orderId, $srOrderStatusArr['confirmed'], MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REANIMATED, $time, false, true);
				$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REANIMATED;
				$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REANIMATED;
			}else{
				
				$SofortOrderSynchronisation = new sofortOrderSynchronisation();
				$SofortOrderSynchronisation->editArticlesShop($PnagInvoice, $orderId);
				
				echo MODULE_PAYMENT_SOFORT_SR_STATUSUPDATE_UNNECESSARY.' (Status: '.$completeInvoiceStatus.")\n";
				$historyComments['customer'] = "";
				$historyComments['seller'] = "";
			}
			break;
	}
	
	if ($newComments != ''){
		$historyComments['customer'] = $newComments;
		$historyComments['seller'] = $newComments;
	}
	
	saveNotificationInSofortTables($PnagInvoice, $orderId, $historyComments['customer'], $historyComments['seller']);
	exit();
} else { // all payment-methods except Rechnung by Sofort
	$consumerProtection = $SofortLib_TransactionData->getConsumerProtection();
	
	if ($paymentMethod == 'su') {
		if($consumerProtection != false){
			$paymentMethodStr = MODULE_PAYMENT_SOFORT_SU_KS_TEXT_TITLE;
		} else {
			$paymentMethodStr = MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_TEXT_TITLE;
		}
		
		$tmpOrderStatus = DEFAULT_ORDERS_STATUS_ID;
		$confirmedOrderStatus = getStatusId(MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID);
	} elseif ($paymentMethod == 'sl') {
		$paymentMethodStr = MODULE_PAYMENT_SOFORT_SOFORTLASTSCHRIFT_TEXT_TITLE;
		$tmpOrderStatus = DEFAULT_ORDERS_STATUS_ID;
		$confirmedOrderStatus = getStatusId(MODULE_PAYMENT_SOFORT_SL_ORDER_STATUS_ID);
	} elseif ($paymentMethod == 'sv') {
		if($consumerProtection != false){
			$paymentMethodStr = MODULE_PAYMENT_SOFORT_SV_KS_TEXT_TITLE;
		} else {
			$paymentMethodStr = MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_TEXT_TITLE;
		}
		
		$tmpOrderStatus = getStatusId(MODULE_PAYMENT_SOFORT_SV_TMP_STATUS_ID);
		$confirmedOrderStatus = getStatusId(MODULE_PAYMENT_SOFORT_SV_ORDER_STATUS_ID);
	} elseif ($paymentMethod == 'ls') {
		$paymentMethodStr = MODULE_PAYMENT_SOFORT_LASTSCHRIFT_TEXT_TITLE;
		$tmpOrderStatus = DEFAULT_ORDERS_STATUS_ID;
		$confirmedOrderStatus = getStatusId(MODULE_PAYMENT_SOFORT_LS_ORDER_STATUS_ID);
	} elseif ($paymentMethod == 'ideal') {
		$paymentMethodStr = MODULE_PAYMENT_SOFORT_IDEAL_TEXT_TITLE;
		$tmpOrderStatus = getStatusId(MODULE_PAYMENT_SOFORT_IDEAL_CLASSIC_TMP_STATUS_ID);
		$confirmedOrderStatus = getStatusId(MODULE_PAYMENT_SOFORT_IDEAL_CLASSIC_ORDER_STATUS_ID);
	} else {
		exit('Error! Unknown payment method! ('.$paymentMethod.').');
	}
	
	$checkOrderStatus = getStatusId($configuration['MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID']);
	
	if ($statusReason == 'not_credited_yet') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_NOT_CREDITED_YET;
	} elseif ($statusReason == 'wait_for_money' && $paymentMethod == 'sv') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_WAIT_FOR_MONEY;
	} elseif ($statusReason == 'partially_credited' && $paymentMethod == 'sv') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_PARTIALLY_CREDITED;
	} elseif ($statusReason == 'overpayment' && $paymentMethod == 'sv') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_OVERPAYMENT;
	} elseif ($statusReason == 'compensation' && $paymentMethod == 'sv') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_SV_COMPENSATION;
		$refunded_amount = $SofortLib_TransactionData->getAmountRefunded();	// Used in regex below, dont change!
	} elseif ($statusReason == 'credited') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_RECEIVED;
	} elseif ($status == 'loss' && $paymentMethod == 'su') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_SU_LOSS;
	} elseif ($status == 'loss' && $paymentMethod == 'sv') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_SV_LOSS;
	} elseif ($status == 'loss' && $paymentMethod == 'sl') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_DEBIT_RETURNED;
	} elseif ($status == 'loss' && $paymentMethod == 'ls') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_DEBIT_RETURNED;
	} elseif ($statusReason == 'refunded') {
		$statusReasonStr = MODULE_PAYMENT_SOFORT_STATUS_REFUNDED;
	} else {
		exit(MODULE_PAYMENT_SOFORT_ERROR_UNEXPECTED_STATUS." (".$status.") (".$statusReason.")");
	}
	
	$orderQuery = xtc_db_query("SELECT orders_status, currency_value, orders_id FROM ".HelperFunctions::escapeSql(TABLE_ORDERS)." WHERE orders_ident_key = '".HelperFunctions::escapeSql($transactionId)."'");
	
	if (xtc_db_num_rows($orderQuery) != 1 || empty($transactionId)) {
		$orderQuery = xtc_db_query("SELECT orders_status, currency_value, orders_id FROM ".HelperFunctions::escapeSql(TABLE_ORDERS)." WHERE orders_id = '".(int)$xOrderId."' AND customers_id = '".(int)$xCustomerId."'");
	}
	
	if (xtc_db_num_rows($orderQuery) != 1) {
		echo MODULE_PAYMENT_SOFORT_ERROR_ORDER_NOT_FOUND.' '.$xOrderId.' '.$transactionId;
	} else {
		$order = xtc_db_fetch_array($orderQuery);
		
		if ((int)$xOrderId != (int)$order['orders_id']) {
			echo MODULE_PAYMENT_SOFORT_ERROR_ORDER_NOT_FOUND.' '.$xOrderId.' : '.$order['orders_id'].' '.$transactionId;
			exit();
		}
		
		$totalQuery = xtc_db_query("SELECT value FROM ".HelperFunctions::escapeSql(TABLE_ORDERS_TOTAL)." WHERE orders_id = '".(int)$xOrderId."' AND class = 'ot_total' LIMIT 1");
		$total = xtc_db_fetch_array($totalQuery);
		$orderTotal = number_format($total['value'], 2, '.', '');
		
		//get the "normal" Shopstate for the given $status, $statusReason and $paymentMethod
		$orderStatus = getShopOrderStatus($status, $paymentMethod, $statusReason, $xOrderId, $confirmedOrderStatus, $tmpOrderStatus, $checkOrderStatus);
		if ($orderStatus === false) {
			$orderStatus = $tmpOrderStatus;
			$comment = MODULE_PAYMENT_SOFORT_ERROR_UNEXPECTED_STATUS." ".$status." ".$statusReasonStr;
			$errUnexpected = true;
			echo MODULE_PAYMENT_SOFORT_ERROR_UNEXPECTED_STATUS;
		}
		
		if ((!isset($errUnexpected)) || (isset($errUnexpected) && !$errUnexpected)) {
			$comment = $statusReasonStr;
			echo MODULE_PAYMENT_SOFORT_SUCCESS_CALLBACK;
		}
		
		$tId = $transactionId; // Mandatory for replacement in following line
		$comment = preg_replace('#\{\{([a-zA-Z0-9_]+)\}\}#e', '$$1', $comment); // Set paymentMethod, tId and time
		
		$sofortNotificationStatus = $orderStatus;
		
		if ($orderStatus != $checkOrderStatus) {
			$allCurrentOrderStatus = HelperFunctions::getAllCurrentOrderStatus($xOrderId);
			if (!empty($allCurrentOrderStatus['sofortOrdersStatus']) && $allCurrentOrderStatus['sofortOrdersStatus'] != $allCurrentOrderStatus['coreStatus']) {
				$orderStatus = $allCurrentOrderStatus['coreStatus'];
			}
		}

		if ($_GET['action'] == 'ideal') { // iDeal: set transactionId to history
			$sqlDataArray = array(
				'orders_id'			=> $xOrderId,
				'orders_status_id'	=> $tmpOrderStatus,
				'date_added'		=> 'sqlcommand:now()',
				'customer_notified' => 0,
				'comments'			=> MODULE_PAYMENT_SOFORT_MULTIPAY_TRANSACTION_ID.': '.$transactionId,
			);
			xtc_db_query(HelperFunctions::getEscapedInsertInto(TABLE_ORDERS_STATUS_HISTORY, $sqlDataArray));
			$sqlDataArray = array(
				'orders_id'			=> $xOrderId,
				'orders_status_id'	=> $orderStatus,
				'date_added'		=> 'sqlcommand:now()',
				'customer_notified' => 0,
				'comments'			=> addslashes($comment),
			);
			xtc_db_query(HelperFunctions::getEscapedInsertInto(TABLE_ORDERS_STATUS_HISTORY, $sqlDataArray));
			xtc_db_query("UPDATE ".HelperFunctions::escapeSql(TABLE_ORDERS)." SET orders_status = '".HelperFunctions::escapeSql($orderStatus)."', last_modified = NOW() WHERE orders_id = '".(int)$xOrderId."'");
		} else {
			$sqlDataArray = array(
				'orders_id'			=> $xOrderId,
				'orders_status_id'	=> $orderStatus,
				'date_added'		=> 'sqlcommand:now()',
				'customer_notified' => 0,
				'comments'			=> addslashes($comment),
			);
			xtc_db_query(HelperFunctions::getEscapedInsertInto(TABLE_ORDERS_STATUS_HISTORY, $sqlDataArray));
			xtc_db_query("UPDATE ".HelperFunctions::escapeSql(TABLE_ORDERS)." SET orders_status = '".HelperFunctions::escapeSql($orderStatus)."', last_modified = NOW() WHERE orders_id = '".(int)$xOrderId."'");
		}
		
		$query = xtc_db_query( 'SELECT id FROM sofort_orders WHERE orders_id = '.HelperFunctions::escapeSql($xOrderId));
		$result = xtc_db_fetch_array($query);
		$sofortOrdersId = $result['id'];
		HelperFunctions::updateTimeline($sofortOrdersId,$sofortNotificationStatus,addslashes($comment));
	}
}


function updateShopAdresses ($invoiceAddress, $shippingAddress, $orderId) {
	
	if (!$orderId) exit("No order_id given to function updateShopAdresses(). Exit!");

	xtc_db_query("	UPDATE	".HelperFunctions::escapeSql(TABLE_ORDERS)." 
					SET		billing_name = '".HelperFunctions::escapeConvert($invoiceAddress['firstname'],2)." ".HelperFunctions::escapeConvert($invoiceAddress['lastname'],2)."',
							billing_firstname = '".HelperFunctions::escapeConvert($invoiceAddress['firstname'],2)."',
							billing_lastname = '".HelperFunctions::escapeConvert($invoiceAddress['lastname'],2)."', 
							billing_company = '', 
							billing_street_address = '".HelperFunctions::escapeConvert($invoiceAddress['street'],2)." ".HelperFunctions::escapeConvert($invoiceAddress['street_number'],2)."', 
							billing_suburb = '".HelperFunctions::escapeConvert($invoiceAddress['street_additive'],2)."', 
							billing_city = '".HelperFunctions::escapeConvert($invoiceAddress['city'],2)."', 
							billing_postcode = '".HelperFunctions::escapeConvert($invoiceAddress['zipcode'],2)."', 
							billing_state = '', 
							billing_country = 'Germany',
							billing_country_iso_code_2 = '" .HelperFunctions::escapeConvert($invoiceAddress['country_code'],2). "',
							last_modified = now() 
					WHERE	orders_id = '".(int)$orderId."'");
	xtc_db_query("	UPDATE	".HelperFunctions::escapeSql(TABLE_ORDERS)."
					SET		delivery_name = '".HelperFunctions::escapeConvert($shippingAddress['firstname'],2)." ".HelperFunctions::escapeConvert($shippingAddress['lastname'],2)."',
							delivery_firstname = '".HelperFunctions::escapeConvert($shippingAddress['firstname'],2)."', 
							delivery_lastname = '".HelperFunctions::escapeConvert($shippingAddress['lastname'],2)."', 
							delivery_company = '', 
							delivery_street_address = '".HelperFunctions::escapeConvert($shippingAddress['street'],2)." ".HelperFunctions::escapeConvert($shippingAddress['street_number'],2)."', 
							delivery_suburb = '".HelperFunctions::escapeConvert($shippingAddress['street_additive'],2)."', 
							delivery_city = '".HelperFunctions::escapeConvert($shippingAddress['city'],2)."', 
							delivery_postcode = '".HelperFunctions::escapeConvert($shippingAddress['zipcode'],2)."', 
							delivery_state = '', 
							delivery_country = 'Germany',
							delivery_country_iso_code_2 = '" .HelperFunctions::escapeConvert($shippingAddress['country_code'],2). "',
							last_modified = now() 
					WHERE	orders_id = '".(int)$orderId."'");
	echo MODULE_PAYMENT_SOFORT_SR_SUCCESS_ADDRESS_UPDATED."\n";
}


function getStatusId ($status) {
	return ($status > 0) ? $status : DEFAULT_ORDERS_STATUS_ID;
}


/**
 * Update the order status and the order status history in database
 * @param int $orderId
 * @param array $status
 * @param string $comment
 */
function updateOrderStatus($PnagInvoice, $orderId, $status, $comment, $time, $newTotal, $isReanimatedInvoice = false) {
	$comments = '';
	
	if (!$orderId) exit("No order_id given to function updateOrderStatus(). Exit!");
	if ($newTotal) $comments = insertNewTotalCommentToHistory($orderId, $status, $time, $newTotal);
	
	$SofortOrderSynchronisation = new sofortOrderSynchronisation();
	$SofortOrderSynchronisation->editArticlesShop($PnagInvoice, $orderId);
	
	$completeInvoiceStatus = $PnagInvoice->getState();
	
	if (!commentIsValid($orderId, $completeInvoiceStatus) && !$isReanimatedInvoice) {
		echo MODULE_PAYMENT_SOFORT_SR_STATUSUPDATE_UNNECESSARY;
		return $comments;
	}
	
	xtc_db_query('UPDATE '.HelperFunctions::escapeSql(TABLE_ORDERS).' SET orders_status = "'.HelperFunctions::escapeSql($status).'", last_modified = now() WHERE orders_id = '.(int)$orderId.';');
	
	$sqlDataArray = array(
			'orders_id'			=> (int)$orderId,
			'orders_status_id'	=> $status,
			'date_added'		=> 'sqlcommand:now()',
			'customer_notified' => 0,
			'comments'			=> $comment.' '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TIME.': '.$time,
	);
	xtc_db_query(HelperFunctions::getEscapedInsertInto(TABLE_ORDERS_STATUS_HISTORY, $sqlDataArray));
	echo MODULE_PAYMENT_SOFORT_SUCCESS_CALLBACK;
	
	return $comments;
}


/**
 *
 * Check, if it makes sense, to show the comment in CUSTOMER-history (in comparison with the previous comment/invoiceStatus)
 * @param string $orderId
 * @param string $completeInvoiceStatus
 * @return false - dont show the comment in customer-history ELSE true
 */
function commentIsValid($orderId, $completeInvoiceStatus){
	$lastShopStatusId = HelperFunctions::getLastFieldValueFromSofortTable($orderId,'status_id');
	
	if ($lastShopStatusId == $completeInvoiceStatus) return false;
	
	switch($completeInvoiceStatus) {
		//dont set comments with this states
		case PnagInvoice::PENDING_NOT_CREDITED_YET_RECEIVED: 
		case PnagInvoice::PENDING_NOT_CREDITED_YET_REMINDER_1:
		case PnagInvoice::PENDING_NOT_CREDITED_YET_REMINDER_2:
		case PnagInvoice::PENDING_NOT_CREDITED_YET_REMINDER_3:
		case PnagInvoice::PENDING_NOT_CREDITED_YET_DELCREDERE:
		case PnagInvoice::RECEIVED_CREDITED_REMINDER_1:
		case PnagInvoice::RECEIVED_CREDITED_REMINDER_2:
		case PnagInvoice::RECEIVED_CREDITED_REMINDER_3:
		case PnagInvoice::RECEIVED_CREDITED_DELCREDERE:
		case PnagInvoice::RECEIVED_CREDITED_PENDING:	
		case PnagInvoice::RECEIVED_CREDITED_RECEIVED:
			return false;
		default:
			return true;
	}
}


/**
 * Fetch order-ID
 * @param $transactionId
 */
function getOrderId($transactionId) {
	if (!$transactionId) exit("No Transaction-ID given to function getOrderId(). Exit!");
	
	$sql = 'SELECT orders_id FROM '.TABLE_ORDERS.' WHERE orders_ident_key = "'.$transactionId.'"';
	$orderId = xtc_db_query($sql);
	$orderId = xtc_db_fetch_array($orderId);
	return $orderId['orders_id'];
}


/**
 * Fetch the module's configuration
 * @return Array $configurationArray
 */
function getPaymentModuleConfiguration() {
	$sql = 'SELECT configuration_key, configuration_value FROM configuration WHERE configuration_key LIKE "%MODULE_PAYMENT_SOFORT_%"';
	$configurationQuery = xtc_db_query($sql);
	$configurationArray = array();
	
	while ($configurationData = xtc_db_fetch_array($configurationQuery)) {
		$configurationArray[$configurationData['configuration_key']] = $configurationData['configuration_value'];
	}
	
	return $configurationArray;
}


function insertNewTotalCommentToHistory($orderId, $status, $time, $newTotal){
	if($newTotal['newTotal'] > $newTotal['lastShopTotal']){
		$comments = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_RESET.' '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CURRENT_TOTAL.' '.$newTotal['newTotal'].' Euro '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TIME.': '.$time;
	} else {
		$comments = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_EDITED.' '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CURRENT_TOTAL.' '.$newTotal['newTotal'].' Euro '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TIME.': '.$time;
	}
	
	$sqlDataArray = array(
			'orders_id'			=> (int)$orderId,
			'orders_status_id'	=> $status,
			'date_added'		=> 'sqlcommand:now()',
			'customer_notified' => 0,
			'comments'			=> $comments,
	);
	xtc_db_query(HelperFunctions::getEscapedInsertInto(TABLE_ORDERS_STATUS_HISTORY, $sqlDataArray));
	echo MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_EDITED."\n";
	
	return $comments;
}


function checkIfNewTotal($PnagInvoice, $orderId){
	$lastShopTotal = HelperFunctions::getLastFieldValueFromSofortTable($orderId,'amount');
	
	//1st notification? -> amount cannot be changed
	if (!$lastShopTotal) {
		return false;
	}
	
	$sofortTotal = $PnagInvoice->getAmount();
	
	//warning, if no notification exists in sofort-table
	if($lastShopTotal === '') {
		echo 'Warning: Last total (amount) could not be found in shop-DB!\n';
		return false;
	}
	
	if ($lastShopTotal != $sofortTotal){
		return $newTotal = array(
				'newTotal' => $sofortTotal,
				'lastShopTotal' => $lastShopTotal
		);
	}
	
	return false;
}


/**
 * save SR-orders in our sofort-tables
 * Enter description here ...
 * @param PnagInvoice $PnagInvoice
 * @param Int $xOrderId
 */
function saveNotificationInSofortTables($PnagInvoice, $orderId, $customerComment, $sellerComment){
	$query = xtc_db_query('SELECT id FROM sofort_orders WHERE orders_id = '.$orderId);
	$result = xtc_db_fetch_array($query);
	$sofortOrdersId = $result['id'];
	HelperFunctions::insertSofortOrdersNotification($sofortOrdersId, $PnagInvoice, $customerComment, $sellerComment);
}


/**
 * return the last orderState for the given orderId from table orders (NOT from sofort-tables!)
 */
function getLastOrderStatus($orderId){
	if (!$orderId) exit("No Order-ID given to function getLastOrderStatus(). Exit!");
	
	$sql = 'SELECT orders_status FROM '.TABLE_ORDERS.' WHERE orders_id = "'.$orderId.'"';
	$orderStatus = xtc_db_query($sql);
	$orderStatus = xtc_db_fetch_array($orderStatus);
	return $orderStatus['orders_status'];
}

/**
 * Build for the given $invoiceStatus the history-comments for the customer and seller
 * @return array Comments for the customer and seller
 */
function getHistoryComments($invoiceStatus, $transactionId = ''){	
	$historyComments = array();
	
	switch($invoiceStatus){
		case PnagInvoice::PENDING_CONFIRM_INVOICE:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_PENDINIG_NOT_CONFIRMED_COMMENT.$transactionId;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_PENDINIG_NOT_CONFIRMED_COMMENT.$transactionId;
			break;
		case PnagInvoice::PENDING_NOT_CREDITED_YET_PENDING:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CONFIRMED;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CONFIRMED_SELLER;
			break;
		case PnagInvoice::REFUNDED_REFUNDED_REFUNDED:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED_REFUNDED;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED_REFUNDED_SELLER;
			break;
		case PnagInvoice::LOSS_CANCELED:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED;
			break;
		case PnagInvoice::LOSS_CONFIRMATION_PERIOD_EXPIRED:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCEL_30_DAYS;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCEL_30_DAYS;
			break;
		case PnagInvoice::PENDING_NOT_CREDITED_YET_RECEIVED:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_RECEIVED;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_RECEIVED_SELLER;
			break;
		case PnagInvoice::RECEIVED_CREDITED_RECEIVED:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_RECEIVED;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_RECEIVED_SELLER;
			break;
		case PnagInvoice::PENDING_NOT_CREDITED_YET_REMINDER_1:
			$historyComments['customer'] = str_replace('{{d}}', '1', MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REMINDER);
			$historyComments['seller'] = str_replace('{{d}}', '1', MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_REMINDER_SELLER);
			break;
		case PnagInvoice::PENDING_NOT_CREDITED_YET_REMINDER_2:
			$historyComments['customer'] = str_replace('{{d}}', '2', MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REMINDER);
			$historyComments['seller'] = str_replace('{{d}}', '2', MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_REMINDER_SELLER);
			break;
		case PnagInvoice::PENDING_NOT_CREDITED_YET_REMINDER_3:
			$historyComments['customer'] = str_replace('{{d}}', '3', MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REMINDER);
			$historyComments['seller'] = str_replace('{{d}}', '3', MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_REMINDER_SELLER);
			break;
		case PnagInvoice::PENDING_NOT_CREDITED_YET_DELCREDERE:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_DELCREDERE;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_DELCREDERE_SELLER;
			break;
		case PnagInvoice::RECEIVED_CREDITED_REMINDER_1:
			$historyComments['customer'] = str_replace('{{d}}', '1', MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REMINDER);
			$historyComments['seller'] = str_replace('{{d}}', '1', MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_REMINDER_SELLER);
			break;
		case PnagInvoice::RECEIVED_CREDITED_REMINDER_2:
			$historyComments['customer'] = str_replace('{{d}}', '2', MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REMINDER);
			$historyComments['seller'] = str_replace('{{d}}', '2', MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_REMINDER_SELLER);
			break;
		case PnagInvoice::RECEIVED_CREDITED_REMINDER_3:
			$historyComments['customer'] = str_replace('{{d}}', '3', MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REMINDER);
			$historyComments['seller'] = str_replace('{{d}}', '3', MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_REMINDER_SELLER);
			break;
		case PnagInvoice::RECEIVED_CREDITED_DELCREDERE:
			$historyComments['customer'] = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_DELCREDERE;
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_DELCREDERE_SELLER;
			break;
		case PnagInvoice::RECEIVED_CREDITED_PENDING:
			$historyComments['customer'] = '';
			$historyComments['seller'] = MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_PENDING_SELLER;
			break;
		default:
			$historyComments['customer'] = '';
			$historyComments['seller'] = '';
			break;
	}
	
	return $historyComments;
}


/**
 * Check if an already refunded invoice was enabled again
 */
function refundedInvoiceWasJustReanimated($orderId, $currentStatus) {
	$lastStatus = HelperFunctions::getLastFieldValueFromSofortTable($orderId,'status');
	if ($lastStatus == 'refunded' && $currentStatus != 'refunded') {
		return true;
	} else {
		return false;
	}
}


/**
 * Insert serialized orderdata into shop-db, if it was not inserted by successUrl-call before
 * @return int orderId or die() in case of failures
 */
function handleOrderInsertion($transactionId, $paymentSecret, $paymentMethod, $customerId) {
	
	require_once(HelperFunctions::getSofortOrderhandlingLink());
	$SofortOrderhandling = new SofortOrderhandling();
	
	//get serialized sessiondata
	$savedSession = $SofortOrderhandling->getSavedSessionData($transactionId, $paymentSecret);
	
	//Order was already saved
	if (!$savedSession){
		usleep(10000); //avoid race-conditions between success-url and notification and needless error-mails
		$orderId = $SofortOrderhandling->getOrderId($transactionId, $paymentSecret);
		
		if(!$orderId) {
			//saved sessiondata was not found and no order-id exists
			xtc_db_query('UPDATE sofort_orders SET data_acquired = "0" WHERE payment_secret = "'.HelperFunctions::escapeSql($paymentSecret).'" AND transaction_id = "'.HelperFunctions::escapeSql($transactionId).'"');
			$errors = array(
					'description' => 'Order could not be saved in shop-DB and orderdata could not be found.',
					'transactionId' => $transactionId,
					'customerId' => $customerId,
					'paymentmethod' => $paymentMethod
			);
			HelperFunctions::sendAdminErrorMail($errors);
			exit('Error: Order could not be saved.');
		} else {
			return $orderId;
		}
	} else {
		//restore $_SESSION and $GLOBALS from saved serialized data
		$SofortOrderhandling->restoreGivenSessionDataToSession($savedSession);
		
		$insertData = $SofortOrderhandling->insertOrderIntoShop();
		$orderId = $insertData['orderId'];
		$sofortData = $insertData['sofortData'];
		
		if (!$orderId){
			xtc_db_query('UPDATE sofort_orders SET data_acquired = "0" WHERE payment_secret = "'.HelperFunctions::escapeSql($paymentSecret).'" AND transaction_id = "'.HelperFunctions::escapeSql($transactionId).'"');
			$errors = array(
					'description' => 'Order was MAYBE not successfully saved in shop-DB or Order-ID is unknown. Please check the order for completeness!',
					'transactionId' => $transactionId,
					'paymentmethod' => $paymentMethod,
					'customerId' => $customerId,
					'orderdata' => $savedSession
			);
			HelperFunctions::sendAdminErrorMail($errors);
			exit('Error: MAYBE order could not be saved. Please check!');
		} else {
		//	order was successfully saved, delete serialized session from db and send email to seller/customer
			$SofortOrderhandling->insertOrderIdInSofortTables($transactionId, $paymentSecret, $orderId);
			
			//save articleattributes (required for order-sync with SR)
			if ($paymentMethod == 'sr') {
				$SofortOrderhandling->insertOrderAttributesInSofortTables($orderId, $sofortData);
			}
			
			$SofortOrderhandling->deleteSavedSessionFromDb($transactionId, $paymentSecret);
			
			$SofortOrderhandling->insertTransIdInTableOrders($transactionId, $orderId);
			
			if ($paymentMethod == 'sr') {
				HelperFunctions::sendOrderIdToSofort(MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY, $transactionId, $orderId);
			}
			
			$SofortOrderhandling->sendOrderEmails($orderId);
			$SofortOrderhandling->doSpecialThingsAfterSuccessfulInsertion();
			
			return $orderId;
		}
	}
}


/**
 * get the "normal" ShopOrderState for the given params
 */
function getShopOrderStatus($status, $paymentMethod, $statusReason, $xOrderId, $confirmedOrderStatus, $tmpOrderStatus, $checkOrderStatus) {
	$orderStatus = false;
	
	if ($status == 'pending') {
		switch ($paymentMethod) {
			case 'sl': 
			case 'ls': 
			case 'su':
				$orderStatus = $confirmedOrderStatus; 
				break;
			case 'sv':
				$orderStatus = $tmpOrderStatus;
				break;
		}
	} elseif ($status == 'received') {
		switch ($paymentMethod) {
			case 'sl':
			case 'ls':
			case 'su':
				$orderStatus = $confirmedOrderStatus;
				break;
			case 'sv':
				if ($statusReason == 'partially_credited' || $statusReason == 'overpayment') {
					$orderStatus = $checkOrderStatus;
				}else{
					$orderStatus = $confirmedOrderStatus;
				}
				break;
		}
	} elseif ($status == 'loss') {
		switch ($paymentMethod) {
			case 'sl':
			case 'ls':
				if ($statusReason == 'rejected') {
					$orderStatus = $checkOrderStatus;
				}
				break;
			case 'sv':
			case 'su':
				if ($statusReason == 'not_credited') {
					$orderStatus = $checkOrderStatus;
				}
				break;
		}
	} elseif ($status == 'refunded') {
		switch ($paymentMethod) {
			case 'sl':
			case 'ls':
			case 'sv':
			case 'su':
				if ($statusReason == 'compensation' || $statusReason == 'refunded') {
					$orderStatus = getLastOrderStatus($xOrderId);
				}
				break;
		}
	}
	
	return $orderStatus;
} 


