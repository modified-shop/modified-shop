<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 13:49:09 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: getContent.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * 

 * Should be required once to output javascript

 */
require_once(dirname(__FILE__).'/../../helperFunctions.php');
$url = isset($_POST['url'])? $_POST['url'] : '';

if(!santiyCheck($url)) {
	exit;
}

switch (getDownloadMethod()) {
	case 'file_get_contents':
		$agb = file_get_contents($url);
		break;
	case 'curl':
		$agb = handleCurlDownload($url);
		break;
	default:
		$agb = handleSocketDownload($url);
		break;
}

$matches = array();
preg_match("/<\!-- content -->.*<\!-- \/content -->/s", $agb, $matches);
echo HelperFunctions::convertEncoding($matches[0], 1, 'ISO-8859-15');


function santiyCheck($url) {
	if(strpos('aaaa'.$url, 'https://documents.sofort.com') == 4) return true;
	return false;
}


function getDownloadMethod() {
	if (ini_get('allow_url_fopen')) {
		$method = 'file_get_contents';
	} elseif (function_exists('curl_init')) {
		$method = 'curl';
	} else {
		$method = 'socket';
	}
	
	return $method;
}


function handleCurlDownload($url) {
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $url);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);
	
	return $buffer;
}


function handleSocketDownload($url) {
	$uri = parse_url($url);
	
	$handle = fsockopen('ssl://'.$uri['host'], 443);
	
	$header  = "GET ".$uri['path']." HTTP/1.1\r\n";
	$header .= "Host: ".$uri['host']."\r\n";
	$header .= "Connection: Close\r\n\r\n";
	
	
	fwrite($handle, $header);
	$buffer = null;
	
	while (!feof($handle)) {
		$buffer .= fgets($handle, 8192);
	}
	
	fclose($handle);
	return $buffer;
}
?>