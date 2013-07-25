<?php
	/*  File:       findologic.php
	 *  Version:    4.1 (120)
	 *  Date:       08.Sep.2009
	 *
	 *  FINDOLOGIC GmbH
	 */

	require_once('./findologic_config.inc.php');

	function curl_http_request($link, $timeout) {
		$http_response = '';
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $link);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
		$http_response = curl_exec($c);
		return $http_response;
	}

	function http_request($link) {
		$http_response = '';
		$handle = fopen($link, 'r');
		if (!handle) {
			return '';
		} else {
			while (!feof($handle)) {
				$http_response .= fread($handle, 512);
			}
		}
		return $http_response;
	}

	function direct_http_request($link, $timeout) {
		$http_response = '';
		$url = parse_url($link);
		$handle = fsockopen($url['host'], 80, $err_num, $err_msg, $timeout);
		if (!$handle) {
			return "error: $err_msg($err_num)";
		} else {
			fputs($handle, 'GET ' . $url['path'] . '?' . $url['query']  . " HTTP/1.0\n");
			fputs($handle, 'Host: ' . $url['host'] . "\n");
			fputs($handle, "Connection: close\n\n");
			while (!feof($handle)) {
				$http_response .= fgets($handle, 512);
			}
			fclose($handle);
		}
		return $http_response;
	}

	function async_request($link) {
		$timeout = 10;
		$url = parse_url($link);
		$handle = fsockopen($url['host'], 80, $err_num, $err_msg, $timeout);
		if (!$handle) {
			return "error: $err_msg($err_num)";
		} else {
			stream_set_timeout($handle, $timeout);
			fputs($handle, 'GET ' . $url['path'] . '?' . $url['query']  . " HTTP/1.0\n");
			fputs($handle, 'Host: ' . $url['host'] . "\n");
			fputs($handle, "Connection: close\n\n");
			fclose($handle);
		}
	}

	function is_alive($url) {
		if (function_exists('curl_init')) {
			$status = curl_http_request($url.'alivetest.php?shopkey=' . FL_SHOP_ID, FL_ALIVE_TEST_TIMEOUT);
			return trim($status) == "alive";
		} else {
			$status = direct_http_request($url.'alivetest.php?shopkey=' . FL_SHOP_ID, FL_ALIVE_TEST_TIMEOUT);
			return substr(trim($status), -5) == 'alive';
		}
	}

	if ($_GET['target'] != '') {
		// user clicked on product:
		// notify service
		$url = FL_SERVICE_URL.'index.php?'.
			'shop='.FL_SHOP_ID.
			'&userip='.$_SERVER['REMOTE_ADDR'].
			'&'.$_SERVER['QUERY_STRING'];
		async_request($url);

		// redirect to product-page
		$productpage = urldecode($_GET['target']);
		if ($_GET['MODsid'] != '')
			$productpage .= '&MODsid='.$_GET['MODsid'];
		header("Location: $productpage");
	} else {
		// user is searching:
		// load SESSION from XT-Commerce
		include('includes/application_top.php');

		$do_findologic_search = ($_SESSION['language'] == 'german') && is_alive(FL_SERVICE_URL);

		if ($do_findologic_search) {
			// do http-request
			$url = FL_SERVICE_URL.'index.php?'.
				'shop='.FL_SHOP_ID.
				'&shopurl='.urlencode(FL_SHOP_URL).
				'&userip='.$_SERVER['REMOTE_ADDR'].
				'&referer='.urlencode($_SERVER['HTTP_REFERER']).
				'&revision='.FL_REVISION.
				'&'.$_SERVER['QUERY_STRING'];
			if (function_exists('curl_init')) {
				$content = curl_http_request($url, FL_REQUEST_TIMEOUT);
			} else {
				$content = http_request($url);
			}
			if (strlen($content) == 0) $do_findologic_search = false;
		}

		if (!$do_findologic_search) {
			// findologic search not appropriate or failed: use standard search
			$standard_search = FL_SHOP_URL.'advanced_search_result.php?'.$_SERVER['QUERY_STRING'];

			// rediret to standard search
			header("Location: $standard_search");
		} else {
			// create smarty element
			$smarty = new Smarty;

			// include boxes & header
			require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
			require (DIR_WS_INCLUDES.'header.php');

			$smarty->assign('main_content', $content);
			$smarty->assign('language', $_SESSION['language']);
			$smarty->caching = 0;
			if (!defined(RM))
				$smarty->load_filter('output', 'note');

			$smarty->display(CURRENT_TEMPLATE.'/index.html');
			include('includes/application_bottom.php');
		}
	}
?>
