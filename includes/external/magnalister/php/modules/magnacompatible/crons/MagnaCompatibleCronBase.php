<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

abstract class MagnaCompatibleCronBase {
	const DBGLV_NONE = 0;
	const DBGLV_LOW  = 1;
	const DBGLV_MED  = 2;
	const DBGLV_HIGH = 3;
	
	protected $mpID = 0;
	protected $marketplace = '';
	protected $marketplaceTitle = '';
	protected $language = '';
	
	protected $config = array(); 
	
	protected $echoMarker = true;
	
	protected $_debug = false;
	protected $_debugLevel = 0;
	protected $_debugDryRun = false;
	
	public function __construct($mpID, $marketplace) {
		global $_magnaLanguage, $_modules;

		$this->mpID = $mpID;
		$this->marketplace = $marketplace;
		$this->marketplaceTitle = $_modules[$marketplace]['title'];
		
		$this->language = $_magnaLanguage;
		
		$this->determineDebugOptions();
		
		$this->initConfig();
	}
	
	protected function out($str) {
		echo $str;
		flush();
		#ob_flush();
	}
	
	protected function log($str) {
		if (!$this->_debug) return;
		$this->out($str);
	}
	
	protected function dataOut($aData) {
		if (!$this->echoMarker) {
			return;
		}
		$this->out("\n{#".base64_encode(json_encode($aData))."#}\n");
	}
	
	protected function logAPIRequest($request) {
		$this->log("\n\nAPI-Request: ".print_m(json_indent(json_encode($request))));
	}
	
	protected function logAPIResponse($response) {
		$this->log("\n\nAPI-Response: ".print_m(json_indent(json_encode($response))));
	}
	
	protected function logAPIErrors($errors) {
		$this->log("\n\nAPI-Errors: ".print_m(json_indent(json_encode($errors))));
	}

	protected function logException($e, $details = true) {
		$dbg = $e->getErrorArray();
		$msg = "\nEXCEPTION: ".$e->getMessage().' in '.microtime2human($e->getTime());
		if (!$details || empty($dbg)) {
			$this->log($msg);
		} else {
			$this->log(print_m($dbg, $msg));
		}
	}

	protected function determineDebugOptions() {
		$this->_debug = isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] === 'true');
		
		if (!$this->_debug) return;
		
		$ref = new ReflectionClass($this);
		$dbgLevels = $ref->getConstants();
		$lvl = 'DBGLV_'.(isset($_GET['LEVEL']) ? strtoupper($_GET['LEVEL']) : 'NONE');
		if (!array_key_exists($lvl, $dbgLevels)) {
			$this->_debugLevel = $this->_debug ? self::DBGLV_LOW : self::DBGLV_NONE;
		} else {
			$this->_debugLevel = $dbgLevels[$lvl];
			$this->log('   DebugLevel: '.(isset($_GET['LEVEL']) ? $_GET['LEVEL'] : 'low').' ('.$this->_debugLevel.")\n");
		}
		
		$this->_debugDryRun = isset($_GET['DRYRUN']) && ($_GET['DRYRUN'] === 'true');
	}
	
	public function disableMarker($bl) {
		$this->echoMarker = !$bl;
	}
	
	abstract protected function getConfigKeys();
	
	protected function initConfig() {
		$ckeys = $this->getConfigKeys();
		foreach ($ckeys as $k => $o) {
			$mKey = $o['key'];
			if (is_array($mKey)) {
				$mKey[0] = $this->marketplace.'.'.$mKey[0];
			} else {
				$mKey = $this->marketplace.'.'.$mKey;
			}
			$this->config[$k] = getDBConfigValue($mKey, $this->mpID);
			/* Not found, try global config. */
			if ($this->config[$k] === null) {
				$this->config[$k] = getDBConfigValue($o['key'], 0);
			}
			/* Still not found. Use default. */
			if ($this->config[$k] === null) {
				$this->config[$k] = isset($o['default']) ? $o['default'] : null;
			}
		}
	}

	protected function getBaseRequest() {
		return array (
			'SUBSYSTEM' => $this->marketplace,
			'MARKETPLACEID' => $this->mpID,
		);
	}

	abstract public function process();
	
	public static function isAssociativeArray($var) {
		return is_array($var) && array_keys($var) !== range(0, sizeof($var) - 1);
	}
}
