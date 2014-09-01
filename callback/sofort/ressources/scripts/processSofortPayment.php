<?php
/**

 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 13:49:09 +0200 (Thu, 06 Sep 2012) $

 * @author SOFORT AG (integration@sofort.com)

 * @link http://www.sofort.com/

 *

 * Copyright (c) 2012 SOFORT AG

 *

 * $Id: processSofortPayment.php 3755 2012-10-10 10:46:00Z gtb-modified $

 */

chdir('../../../../');

require_once('includes/application_top.php');
require_once(DIR_FS_CATALOG.'callback/sofort/helperFunctions.php');

$language = HelperFunctions::getSofortLanguage($_SESSION['language']);

include(DIR_WS_LANGUAGES.$language.'/modules/payment/sofort_general.php');

/*
$server = (ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER;

$errorUrl = $server.DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.session_name().'='.session_id().'&payment_error='.$_SESSION['sofort']['sofort_payment_method'].'&';
*/
$errorUrl = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$_SESSION['sofort']['sofort_payment_method'], 'SSL');

if(!$_SESSION['sofort']['sofort_payment_url']){
	$sofortPaymentUrl = $errorUrl;
} else {
	$sofortHost = (getenv('sofortApiUrl') != '') ? getenv('sofortApiUrl') : 'https://www.sofort.com';
	$hostToCheck = parse_url($sofortHost, PHP_URL_HOST);
	$paymentHost = parse_url($_SESSION['sofort']['sofort_payment_url'], PHP_URL_HOST);
	
	if (strpos($paymentHost, $hostToCheck) === false) {
		$sofortPaymentUrl = $errorUrl;
	} else {
		$sofortPaymentUrl = $_SESSION['sofort']['sofort_payment_url'];
	}
	
	unset($_SESSION['sofort']['sofort_payment_url']);
	unset($_SESSION['sofort']['sofort_payment_method']);
	$_SESSION['sofort']['checkout_process'] = true;
}

echo '
	<head>
		<meta http-equiv="refresh" content="0; URL='.$sofortPaymentUrl.'/">
		<meta content="text/html; charset='.HelperFunctions::getIniValue('shopEncoding').'" http-equiv="Content-Type">
	</head>
	<body>
		<div style="text-align:center;">
			<div style="height:50px;">&nbsp;</div>
			<div style="height:50px;">
				<img src="'.DIR_WS_CATALOG.'callback/sofort/ressources/images/loader.gif" alt="" />
			</div>
			<div style="height:50px;">
				'.MODULE_PAYMENT_SOFORT_MULTIPAY_FORWARDING.'
			</div>
		</div>
	</body>';
?>