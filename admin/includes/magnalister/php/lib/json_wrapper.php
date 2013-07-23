<?php
# In PHP 5.2 or higher we don't need to bring this in
if (!function_exists('json_encode')) {
	require_once 'json/json.php';
	
	function json_encode($arg) {
		global $services_json;
		if (!isset($services_json)) {
			$services_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		}
		return $services_json->encodeUnsafe($arg);
	}
	
	function json_decode($arg) {
		global $services_json;
		if (!isset($services_json)) {
			$services_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		}
		return $services_json->decode($arg);
	}
}
?>
