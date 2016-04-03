<?php
##
## xt:booster v1.0425 für xt:Commerce und Gambio
## Copyright (c) 2008-2009 xt:booster Limited
##
## Licensed under GNU/GPL
##

define("XTBOOSTER_VERSION", "1.0425");
define('USE_SANDBOX', false);

class xtbooster_base
{
	function __construct()
	{
		global $xtb_config;
		if(!isset($xtb_config['MODULE_XTBOOSTER_SHOPKEY'])) $this->config();
	}

	function config()
	{
		global $xtb_config;
		$xtb_config = array();
		$configuration_query = xtc_db_query("select configuration_key,configuration_id, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_XTB%'");
		while($c = xtc_db_fetch_array($configuration_query))
			$xtb_config[$c['configuration_key']]=$c['configuration_value'];
	}

	function get($function,$request)
	{
		global $xtb_config;
		$connection = curl_init();
		
		$v = curl_version();
		$have_ssl = @in_array("https", $v['protocols']);

		curl_setopt($connection, CURLOPT_URL, ($have_ssl?'https':'http').'://api.'.(USE_SANDBOX?'test.':'').'xsbooster.com'.$function.'?'.$request);
		if($have_ssl) {
			curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($connection, CURLOPT_TIMEOUT, 300);
		curl_setopt($connection, CURLOPT_POST, 0);
		curl_setopt($connection, CURLOPT_POSTFIELDS, $request);
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($connection);
		curl_close($connection);
		return $response;	
	}

	function exec($request)
	{
		global $xtb_config;
		$connection = curl_init();

		$v = curl_version();
		$have_ssl = @in_array("https", $v['protocols']);

		$request = "SHOPKEY:".$xtb_config['MODULE_XTBOOSTER_SHOPKEY']."\n".trim($request);
		curl_setopt($connection, CURLOPT_URL, ($have_ssl?'https':'http').'://api.'.(USE_SANDBOX?'test.':'').'xsbooster.com');
		if($have_ssl) {
			curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		}
		curl_setopt($connection, CURLOPT_TIMEOUT, 300);
		curl_setopt($connection, CURLOPT_POST, 1);

		$request = trim($request)."\nPLATFORM: xtCommerce|NA|NA\n";

		curl_setopt($connection, CURLOPT_POSTFIELDS, $request);
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($connection);
		curl_close($connection);
		return $response;
	}

	function parse($res)
	{
		$r = array();
		$x = explode("\n", trim($res));
		foreach($x as $line)
		{
			$line = trim($line);
			if($line==''||$line[0]=='#') continue;
			$key = trim(substr($line, 0, strpos($line,":")));
			$val = trim(substr($line, strpos($line,":")+1, strlen($line)-strlen($key)));
			if($val[0]=='-'&&$val[1]=='=') $val = trim(base64_decode(substr($val,2,strlen($val)-2)));
			$r[strtoupper($key)]=$val;
		}
		return $r;
	}
}

?>
