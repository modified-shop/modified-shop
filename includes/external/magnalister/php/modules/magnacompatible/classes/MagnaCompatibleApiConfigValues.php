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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class MagnaCompatibleApiConfigValues {
	protected static $instance = null;
	
	protected $mpId = 0;
	protected $marketplace = '';
	protected $data = array();
	
	protected $exceptions = array();
	
	protected function __construct() {
		
	}
	
	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function init(&$magnaSession) {
		$this->mpId = $magnaSession['mpID'];
		$this->marketplace = $magnaSession['currentPlatform'];
		if (!isset($magnaSession[$this->mpId])) {
			$magnaSession[$this->mpId] = array();
		}
		$class = get_class($this);
		if (!isset($magnaSession[$this->mpId][$class])) {
			$magnaSession[$this->mpId][$class] = array();
		}
		$this->data = &$magnaSession[$this->mpId][$class];
		
		return $this;
	}

    public function fetchDataFromApi($action, $extend = array(), $encode = true) {
		$key = $action.json_encode($extend);
		if (isset($this->data[$key]) && ($this->data[$key] !== false)) {
			return $this->data[$key];
		}
		try {
			$aRequest = array(
				'ACTION' => $action,
				'SUBSYSTEM' => $this->marketplace,
				'MARKETPLACEID' => $this->mpId
			);
			$data = MagnaConnector::gi()->submitRequest(array_replace($aRequest, $extend));
			$this->data[$key] = $data['DATA'];
			if ($encode) {
				arrayEntitiesFixHTMLUTF8($this->data[$key]);
			}
		} catch (MagnaException $e) {
			$this->exceptions[] = $e;
			$this->data[$key] = false;
		}
		return $this->data[$key];
	}
	
	public function getMagnaExceptions() {
		return $this->exceptions;
	}
	
	public function cleanMagnaExceptions() {
		$this->exceptions = array();
		return $this;
	}
	
	public function getCountries() {
		return $this->fetchDataFromApi('GetCountries', array (
			'SUBSYSTEM' => 'Core',
		));
	}
	
}
