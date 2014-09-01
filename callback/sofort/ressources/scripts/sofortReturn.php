<?php
/**

 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 13:49:09 +0200 (Thu, 06 Sep 2012) $

 * @author SOFORT AG (integration@sofort.com)

 * @link http://www.sofort.com/

 *

 * Copyright (c) 2012 SOFORT AG

 *

 * $Id: sofortReturn.php 3759 2012-10-10 12:13:39Z gtb-modified $

 */

chdir('../../../../');

require_once('includes/application_top.php');
require_once(DIR_FS_CATALOG.'callback/sofort/helperFunctions.php');

/*
$session = session_name() . '=' . session_id();

if (ENABLE_SSL == true)
	$server = HTTPS_SERVER;
else
	$server = HTTP_SERVER;
*/

switch ($_REQUEST['sofortaction']) {
	case 'success':
/*
		$url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
		
		//add sv-bankdata
		if ($_REQUEST['sofortcode'] == 'sofort_sofortvorkasse') {
			$url .= '&holder='.strip_tags($_GET['holder']);
			$url .= '&account_number='.strip_tags($_GET['account_number']);
			$url .= '&iban='.strip_tags($_GET['iban']);
			$url .= '&bank_code='.strip_tags($_GET['bank_code']);
			$url .= '&bic='.strip_tags($_GET['bic']);
			$url .= '&amount='.strip_tags($_GET['amount']);
			$url .= '&reason_1='.strip_tags($_GET['reason_1']);
			$url .= '&reason_2='.strip_tags($_GET['reason_2']);
		}
*/
		//add sv-bankdata
		$param = '';
		if ($_REQUEST['sofortcode'] == 'sofort_sofortvorkasse') {
			$param .= 'holder='.strip_tags($_GET['holder']);
			$param .= '&account_number='.strip_tags($_GET['account_number']);
			$param .= '&iban='.strip_tags($_GET['iban']);
			$param .= '&bank_code='.strip_tags($_GET['bank_code']);
			$param .= '&bic='.strip_tags($_GET['bic']);
			$param .= '&amount='.strip_tags($_GET['amount']);
			$param .= '&reason_1='.strip_tags($_GET['reason_1']);
			$param .= '&reason_2='.strip_tags($_GET['reason_2']);
		}
		$url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, $param, 'SSL');
		
		break; 
	case 'cancel':
	  //from this function we get a correct URL
		$url = HelperFunctions::getCancelUrl(strip_tags($_REQUEST['sofortcode']));
		break;
	default: 
		//$url = $server.DIR_WS_CATALOG;
		$url = xtc_href_link(FILENAME_DEFAULT);
		break;
}

$_SESSION['sofort']['checkout_process'] = false;

xtc_redirect($url);
?>