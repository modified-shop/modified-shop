<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
include('includes/application_top_callback.php');

require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneModified.php');
$payone = new PayoneModified();

function payone_callback_response($response) {
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	header('Content-Type: text/plain');
	header('Content-Length: '.strlen($response));
	header('Connection: close');
	echo $response;
	ignore_user_abort(true);
	if (function_exists('fastcgi_finish_request')) {
		fastcgi_finish_request();
	} else {
		flush();
	}
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	$payone->log("not a POST request!");
	payone_callback_response('NACK');
	exit;
}

$payone->log("received status from ".((isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : 'unknown'));

// include language
require_once (DIR_WS_CLASSES.'language.php');
$lng = new language(xtc_input_validation(DEFAULT_LANGUAGE, 'lang'));
require_once (DIR_FS_EXTERNAL.'payone/lang/'.$lng->language['directory'].'.php');

$support_ready = $payone->prepareTransactionStatusSupport();
if ($support_ready === false) {
	$payone->log('could not prepare PAYONE transaction status support');
	payone_callback_response('NACK');
	exit;
}
if ($support_ready === null) {
	$payone->log('ignored TxStatus because no PAYONE payment module is installed');
	payone_callback_response('TSOK');
	exit;
}

// authenticate and persist the callback before acknowledging it
$status_saved = $payone->saveTransactionStatus($_POST, false);

if ($status_saved !== true
    && $payone->getTransactionStatusError() === PayoneModified::TRANSACTION_STATUS_ERROR_PERSISTENCE
    )
{
	payone_callback_response('NACK');
	exit;
}

// PAYONE expects the exact response before any potentially slow processing starts
payone_callback_response('TSOK');

if ($status_saved === true) {
	$payone->processTransactionStatus((int)$_POST['reference'], (string)$_POST['txid']);
}
?>
