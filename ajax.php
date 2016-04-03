<?php
/*
 * $Id:$
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2013 [www.hackersolutions.com]
 *
 * Released under the GNU General Public License
 */

include_once('includes/' . (isset($_REQUEST['speed']) ? (file_exists('includes/local/configure.php') ? 'local/configure.php' : 'configure.php') : 'application_top.php'));

// extension
$ajax_ext = preg_replace("/[^a-z0-9\\.\\_]/i", "", $_REQUEST['ext']);
$ajax_ext_file = DIR_WS_INCLUDES . 'extra/ajax/' . $ajax_ext . '.php';

// response type (e.g. json, xml or html): default is json
$ajax_rt = (isset($_REQUEST['type']) ?  preg_replace("/[^h-x]/i", "", $_REQUEST['type']) : 'json');

// return error if file not exist or include it
(!file_exists($ajax_ext_file) ? die('extension does not exist!') : include_once($ajax_ext_file));

// execute extension in ajax module dir
$response = (function_exists($ajax_ext) ? $ajax_ext() : (is_object($ajax_ext) ? new $ajax_ext : die("function/object does not exist")));

// return response data
header("Content-Type: text/$ajax_rt");
header("Expires: Sun, 19 Nov 1978 05:00:00 GMT");
header("Last-Modified: " . gmdate('D, d M Y H:i:s') . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
echo $ajax_rt == 'json' ? json_encode($response) : $response;

function_exists('xtc_db_close') ? xtc_db_close() : '';
?>