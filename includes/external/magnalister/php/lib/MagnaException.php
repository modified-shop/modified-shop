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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MagnaException extends Exception {
	const NO_RESPONSE      = 0x1;
	const NO_SUCCESS       = 0x2;
	const INVALID_RESPONSE = 0x4;
	const UNKNOWN_ERROR    = 0x8;
	const TIMEOUT          = 0x10;

	protected $response = array();
	protected $request = array();
	protected $time = 0;
	private $isCritical = true;

	private $action = '';
	private $subsystem = '';
	private $apierrors = array();

	private $backtrace = array();

	public function __construct($message, $code = 0, $request = array(), $response = array(), $time = 0) {
		parent::__construct($message, $code);
		$this->response = $response;
		$this->request = $request;
		$this->time = $time;

		if (is_array($this->response) && isset($this->response['ERRORS'])) {
			$this->apierrors = $this->response['ERRORS'];
		}
		$error = array();
		if (count($this->apierrors) == 1 && isset($this->apierrors[0])) {
			$error = $this->apierrors[0];
		}

		$this->action = isset($error['ACTION']) 
			? $error['ACTION']
			: (isset($this->request['ACTION']) ? $this->request['ACTION'] : 'UNKOWN');

		$this->subsystem = isset($error['SUBSYSTEM']) 
			? $error['SUBSYSTEM']
			: (isset($this->request['SUBSYSTEM']) ? $this->request['SUBSYSTEM'] : 'UNKOWN');

		if (function_exists('prepareErrorBacktrace')) {
			$this->backtrace = prepareErrorBacktrace(2);
		} else {
			$this->backtrace = array();
		}
	}
	
	public function getResponse() {
		return $this->response;
	}
	
	public function getFirstAPIErrorCode() {
		if (!is_array($this->response) || !isset($this->response['ERRORS'][0]['ERRORCODE'])) {
			return false;
		}
		return $this->response['ERRORS'][0]['ERRORCODE'];
	}
	
	public function getErrorArray() {
		return $this->response;
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getTime() {
		return $this->time;
	}
	
	public function setCriticalStatus($b) {
		$this->isCritical = $b;
	}
	
	public function isCritical() {
		return $this->isCritical;
	}	
	
	public function saveRequest() {
		MagnaDB::gi()->insert(TABLE_MAGNA_API_REQUESTS, array(
			'data' => serialize($this->request),
			'date' => date('Y-m-d H:i:s')
		));
	}
	
	public function getDebugBacktrace() {
		return $this->backtrace;
	}

	public function getAction() {
		return $this->action;
	}

	public function getSubsystem() {
		return $this->subsystem;
	}

	public function toJson() {
		return array (
			'Message' => $this->getMessage(),
			'Action' => $this->action,
			'Subsystem' => $this->subsystem,
			'Time' => $this->time,
			'IsCritical' => $this->isCritical,
			'Request' => $this->request,
			'Response' => $this->response,
			'Backtrace' => $this->backtrace,
		);
	}
	
}
