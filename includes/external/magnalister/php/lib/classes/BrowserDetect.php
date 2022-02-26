<?php
/**
 * Simple browser detection class
 *
 * Released under the MIT license
 *
 * @author Alexander Papst (http://derpapst.eu/)
 */
class MLBrowserDetect {
	private static $instance = null;
	
	private $browsers = array (
		'seamonkey', 'chrome', 'chromium', 'maxthon', 'safari', 'msie', 'konqueror', 'opera',
		'itunes', 'dillo', 'galeon', 'iceape', 'iceweasel', 'shadowfox', 'namoroka',
		'fennec', 'firefox', 'midori', 'netsurf', 'netpositive', 'minefield', 'mozilla'
	);
	private $mobile = array (
		'ipad', 'iphone', 'ipod', 'android', 'blackberry', 'maemo', 'fennec', 'kindle',
		'webos', 'symbian', 'iemobile', 'htc'
	);
	private $engines = array (
		'trident', 'webkit', 'presto', 'khtml', 'gecko'
	);
	private $oses = array (
		'win', 'linux', 'mac', 'freebsd', 'cros', 'netbsd', 'sunos', 'webos',
		'playstation', 'beos'
	);
	
	private $ua = '';
	
	private $browser = array (
		'Browser'   => false,
		'BVersion'  => false,
		'Engine'    => false,
		'EVersion'  => false,
		'Mobile'    => false,
		'MVersion'  => false,
		'MBVersion' => false,
		'Platform'  => false,
	);
	
	private function __construct() {
		$this->ua = $_SERVER['HTTP_USER_AGENT'];
		
		foreach ($this->oses as $os) {
			if (preg_match('/'.$os.'/i', $this->ua)) {
				$this->browser['Platform'] = $os;
				break;
			}
		}
		
		foreach ($this->browsers as $b) {
			$m = array();
			if (preg_match('/'.$b.'(\/([^\s;\)]*))?/i', $this->ua, $m)) {
				if (isset($m[2])) {
					$this->browser['BVersion'] = $m[2];
				}
				$this->browser['Browser'] = $b;
				break;
			}
		}
		
		foreach ($this->engines as $e) {
			$m = array();
			if (preg_match('/'.$e.'(\/([^\s;\)]*))?/i', $this->ua, $m)) {
				if (isset($m[2])) {
					$this->browser['EVersion'] = $m[2];
				}
				$this->browser['Engine'] = $e;
				break;
			}
		}
		
		foreach ($this->mobile as $mo) {
			$m = array();
			if (preg_match('/'.$mo.'/i', $this->ua, $m)) {
				$this->browser['Mobile'] = $mo;
				$this->getMobileVersion();
				break;
			}
		}
		
		$this->bVersionFixes();
		$this->operaFixes();
		$this->msieFixes();
		$this->mozillaFixes();
	}
	
	final public static function gi() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function bVersionFixes() {
		if ($this->browser['BVersion'] !== false) {
			return;
		}
		$m = array();
		if (preg_match('/'.$this->browser['Browser'].' ([^;\)]*)/i', $this->ua, $m)) {
			$this->browser['BVersion'] = $m[1];
		}
	}
	
	private function operaFixes() {
		if ($this->browser['Browser'] === 'opera') {
			$m = array();
			if (preg_match('/version\/([^\s;]*)/i', $this->ua, $m)) {
				$this->browser['BVersion'] = $m[1];
			}
			if ($this->browser['Engine'] === false) {
				$this->browser['Engine'] = 'presto';
			}
		}
	}
	
	private function msieFixes() {
		if ($this->browser['Browser'] !== 'msie') {
			return;
		}
		if ($this->browser['Engine'] === false) {
			$this->browser['Engine'] = 'trident';
		}
	}
	
	private function mozillaFixes() {
		if ($this->browser['Browser'] !== 'mozilla') {
			return;
		}
		$m = array();
		if (preg_match('/rv:([^;\)]*)/i', $this->ua, $m)) {
			$this->browser['BVersion'] = $m[1];
		}
	}
	
	private function getMobileVersion() {
		$m = array();
		if (preg_match('/version\/([^\s;]*)/i', $this->ua, $m)) {
			$this->browser['MBVersion'] = $m[1];
		}
		
		if ($this->browser['Mobile'] === 'android') {
			$m = array();
			if (preg_match('/'.$this->browser['Mobile'].' ([^;\)]*)/i', $this->ua, $m)) {
				$this->browser['MVersion'] = $m[1];
			}
			return;
		}
		
		if (preg_match('/(ipad|iphone|ipod)/', $this->browser['Mobile'])) {
			$this->browser['Platform'] = 'ios';
		}
		
		$m = array();
		if (preg_match('/OS ([0-9]*_[0-9_]*)/', $this->ua, $m)) {
			$this->browser['MVersion'] = str_replace('_', '.', $m[1]);
		}
		
		$m = array();
		if (   ($this->browser['MVersion'] === false)
			&& (preg_match('/'.$this->browser['Mobile'].'\/([^\s;\)]*)/i', $this->ua, $m))
		) {
			$this->browser['MVersion'] = $m[1];
		}
		
		$m = array();
		if (   ($this->browser['MVersion'] === false)
			&& (preg_match('/'.$this->browser['Mobile'].' ([^;\)]*)/i', $this->ua, $m))
		) {
			$this->browser['MVersion'] = $m[1];
		}
	}
	
	public function getBrowser() {
		return $this->browser;
	}
	
	public function get($key) {
		return isset($this->browser[$key]) ? $this->browser[$key] : null;
	}
	
	public function compare($key, $value, $op) {
		$bool = isset($this->browser[$key])
			? (($op === '==')
				? ($this->browser[$key] == $value)
				: version_compare($this->browser[$key], $value, $op)
			)
			: false;
		#echo print_m(func_get_args(), trim(var_dump_pre($bool)));
		return $bool;
	}
	
	public function is($condition) {
		if (!is_array($condition) || empty($condition)) {
			return false;
		}
		foreach ($condition as $key => $value) {
			$op = '==';
			
			$m = array();
			if (preg_match('/^([<>]=?|\!=)\s+(.*)/', $value, $m)) {
				$op = $m[1];
				$value = $m[2];
			}
			if ($this->compare($key, $value, $op) === false) {
				return false;
			}
		}
		return true;
	}
	
	public function getUserAgentString() {
		return $this->ua;
	}
}
