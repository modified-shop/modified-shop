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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/MagnaCompatibleHelper.php');

abstract class MagnaCompatibleCronBase {
	const DBGLV_NONE = 0;
	const DBGLV_LOW  = 1;
	const DBGLV_MED  = 2;
	const DBGLV_HIGH = 3;

	protected $mpID = 0;
	protected $marketplace = '';
	protected $marketplaceTitle = '';
	protected $language = '';

	protected $specificResource = false;

	protected $resources = array();

	protected $config = array();

	protected $echoMarker = true;

	protected $_debug = false;
	protected $_debugLevel = 0;
	protected $_debugDryRun = false;

	public function __construct($mpID, $marketplace) {
		global $_MagnaSession, $_magnaLanguage, $_modules;

		$this->mpID = $mpID;
		$this->marketplace = $marketplace;
		$this->marketplaceTitle = $_modules[$marketplace]['title'];

		if (!is_array($_MagnaSession)) {
			$_MagnaSession = array (
				'mpID' => $this->mpID,
				'currentPlatform' => $this->marketplace,
			);
		} else {
			if (!isset($_MagnaSession['mpID'])) {
				$_MagnaSession['mpID'] = $this->mpID;
			}
			if (!isset($_MagnaSession['currentPlatform'])) {
				$_MagnaSession['currentPlatform'] = $this->marketplace;
			}
		}

		// $this->specificResource can be set by the inheriting class!
		if ($this->specificResource === false) {
			$this->specificResource = strtolower($this->marketplace);
		}

		$this->resources = array (
			'session' => &$_MagnaSession,
		);

		$this->language = $_magnaLanguage;

		$this->determineDebugOptions();

		$this->initConfig();

		$this->loadDependencies();
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

	protected function storeLogging($sType, $mData) {
		$sLogPath = DIR_MAGNALISTER_LOGS.get_class($this).'_'.$this->mpID.'.log';
		if (!file_exists(dirname($sLogPath))) {
			mkdir(dirname($sLogPath), 0777, true);
		}
		if (file_exists($sLogPath) && filesize($sLogPath) > (50 * 1024 * 1024)) {
			$sDir = dirname($sLogPath);
			if (!file_exists($sDir . '/old')) {
				mkdir($sDir . '/old', 0777, true);
			}
			$sBackupPath = DIR_MAGNALISTER_LOGS.get_class($this).$this->mpID.'_%s.log.gz';
			if (function_exists('gzopen')) {
				$mFiles = glob(sprintf($sBackupPath, '*'));
				if (is_array($mFiles)) {
					foreach ($mFiles as $iFile => $sFile) {
						if (in_array(basename($sFile), array('.', '..'))) {
							unset($mFiles[$iFile]);
						}
					}
				} else {
					$mFiles = array();
				}
				foreach ($mFiles as $sBackupFile) {
					if (time() - filemtime($sBackupFile) > 120) { //9 days, modifiedtime for remote filesystems (see touch)
						unlink($sBackupFile);
					}
				}
				$rDate = fopen($sLogPath, 'r');
				$sStartDate = fgets($rDate, 20);
				fclose($rDate);
				$sBackupFile = sprintf($sBackupPath, str_replace(array(' ', ':'),array('.', '.'),$sStartDate).'_'.date('Y-m-d.H.i.s'));
				$rBackup = gzopen($sBackupFile, 'wb9');
				gzwrite($rBackup, file_get_contents($sLogPath));
				gzclose($rBackup);
				@chmod($sBackupFile, 0666);
				touch($sBackupFile, time());
				unlink($sLogPath);
			} else {
				rename($sLogPath, $sBackupPath);
			}
		}
		$r = fopen($sLogPath, 'a+');
		fwrite($r, date('Y-m-d H:i:s ').' '.$sType.':: '.MagnaCompatibleHelper::encodeData($mData)."\n");
		fclose($r);
		@chmod($sLogPath, 0666);
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

	protected function loadFile($file) {
		if (file_exists($file)) {
			require_once($file);
			return true;
		}
		return false;
	}
	
	protected function initApiConfigValuesClass() {
		$class = ucfirst($this->marketplace).'ApiConfigValues';
		if ($this->loadFile(DIR_MAGNALISTER_MODULES.$this->specificResource.'/classes/'.$class.'.php')) {
			# http://3v4l.org/am7HB
			if (version_compare(PHP_VERSION, '5.2.2', '>=')) {
				$instance = call_user_func_array($class.'::gi', array());
			} else {
				$instance = call_user_func_array(array($class, 'gi'), array());
			}
			$r = $instance->init($this->resources['session']);
		}
	}
	
	protected function loadDependencies() {
		$this->loadFile(DIR_MAGNALISTER_MODULES.$this->specificResource.'/'.ucfirst($this->marketplace).'Helper.php');
		$this->initApiConfigValuesClass();
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
