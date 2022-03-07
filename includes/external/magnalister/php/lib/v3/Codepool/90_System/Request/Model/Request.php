<?php

/**
 * SQL query builder
 */
class ML_Request_Model_Request {

	protected $aRequest = array();
	protected $aResult = null;
	protected $aResultAll = null;
	protected $iResult = null;
	protected $iResultAll = null;

	public function __construct() {

	}

	public function set($aData){
		foreach($aData as $sKey => $mValue){
			$this->aRequest[$sKey] = $mValue;
		}
		return $this;
	}

	public function init() {
		$oRef = new ReflectionClass($this);
		$aStaticfieldKeys = array_keys($oRef->getStaticProperties());
		foreach ($oRef->getDefaultProperties() as $sKey => $mValue) {
			if (!in_array($sKey, $aStaticfieldKeys)) {
				$this->$sKey = $mValue;
			}
		}
		return $this;
	}

	public function reset() {
		$this->aRequest = array();
		$this->iResult = null;
		$this->iResultAll = null;
		$this->aResult = null;
		$this->aResultAll = null;
	}



	/**
	 * Limit results in query
	 * @param type $iLimit
	 * @param type $iFrom
	 * @return ML_Database_Model_Query_Select
	 * @todo refactor to natural speach
	 * - limit from, count
	 * - limit count
	 */
	public function limit($iFrom, $iLimit = null) {
		$this->aResult = null;
		$this->iResult = null;
		$this->aLimit = array(
			'from' => $iLimit === null ? '0' : $iFrom,
			'limit' => $iLimit === null ? $iFrom : $iLimit,
		);
		return $this;
	}

	/**
	 * return array of rows
	 * @todo long query log
	 * @return array
	 */
	public function getResult() {
		if ($this->aResult === null) {
			$this->aResult =array_slice($this->getAll(), $this->aLimit['from'], $this->aLimit['limit']) ;
		}
		return $this->aResult;
	}

	/**
	 * return array of rows
	 * @return array
	 */
	public function getAll() {
		if ($this->aResultAll === null) {
			try{
				$aResponse = MagnaConnector::gi()->submitRequest($this->aRequest);
				if(isset($aResponse['DATA'])){
					$this->aResultAll = $aResponse['DATA'];
				}
			}  catch (MagnaException $e){
				$e->setCriticalStatus(false);
			}
		}
		return $this->aResultAll;
	}

	/**
	 * return count of selected row ocording to with limit included or excluded
	 * @param type $blTotal , if true exclude limit from select and otherwise it will be included
	 * @return int
	 */
	public function getCount() {
		return count($this->getAll());
	}


}
