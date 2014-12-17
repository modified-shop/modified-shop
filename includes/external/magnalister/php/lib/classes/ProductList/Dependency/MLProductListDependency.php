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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
abstract class MLProductListDependency{
	/**
	 * @var ML_Database_Model_Query_Select $oQuery
	 */
	protected $oQuery = null;
	
	/**
	 * @var MLProductList $oProductList
	 */
	protected $oProductList = null;
	
	/**
	 * @var array $aConfig
	 */
	protected $aConfig = array();
	/**
	 * @var mixed $mRequest
	 */
	protected $mFilterRequest = null;
	
	protected $aMagnaSession = array();
	
	protected $aMagnaConfig = array();
	
	public function setConfig($aFilterConfig) {
		$this->aConfig = $aFilterConfig;// set twice, to have external config in getDefaultConfig method
		$this->aConfig = array_merge_recursive_simple($this->getDefaultConfig(), $aFilterConfig);
		return $this;
	}
	/**
	 * if any class needs config, this should be overwritten
	 * also to see, what config-vars are possible
	 * return array
	 */
	protected function getDefaultConfig(){
		return array();
	}
	
	public function getConfig($sKey = null){
		return $this->getData($this->aConfig, $sKey);
	}
	
	protected function getData($aData, $sKey){
		if($sKey === null){
			return $aData;
		}elseif(isset($aData[$sKey])){
			return $aData[$sKey];
		}else{
			return null;
		}
	}
	
	public function getMagnaSession($sKey){
		return $this->getData($this->aMagnaSession, $sKey);
	}
	
	public function getMagnaConfig($sKey){
		return $this->getData($this->aMagnaConfig, $sKey);
	}
	
	public function setMagnaSession(&$aMagnaSession) {
		$this->aMagnaSession = $aMagnaSession;
		return $this;
	}
	public function setMagnaConfig(&$aMagnaConfig) {
		$this->aMagnaConfig = $aMagnaConfig;
		return $this;
	}
	public function setFilterRequest($mRequest){
		$this->mFilterRequest = $mRequest;
		return $this;
	}
	public function getFilterRequest(){
		return $this->mFilterRequest;
	}
	
	public function setActionRequest($mRequest){
		$this->mActionRequest = $mRequest;
		return $this;
	}
	public function getActionRequest(){
		return $this->mActionRequest;
	}
	
	public function setProductList($oProductList){
		$this->oProductList = $oProductList;
		return $this;
	}
	protected function getProductList(){
		return $this->oProductList;
	}

	public function setQuery($oQuery){
		$this->oQuery = $oQuery;
		return $this;
	}
	public function getQuery(){
		return $this->oQuery;
	}
	
	public function manipulateQuery(){
		return $this;
	}
	
	public function executeAction(){
		return $this;
	}
	
	public function getFilterLeftTemplate(){
		return '';
	}
	
	public function getFilterRightTemplate(){
		return '';
	}
	
	public function getActionTopTemplate(){
		return '';
	}
	
	public function getActionBottomLeftTemplate(){
		return '';
	}
	
	public function getActionBottomRightTemplate(){
		return '';
	}
	
	public function getActionBottomCenterTemplate(){
		return '';
	}
	
	public function getHeaderTemplate() {
		return '';
	}
	/**
	 * return will be used in http-request-parameters....
	 * @return string 
	 */
	public function getIdent() {
		$sClass =  strtolower(get_class($this));
		if (substr($sClass, 0, 23) == 'mlproductlistdependency') {
			$sClass = substr($sClass, 23, strlen($sClass));
		}
		return $sClass;
	}
	
	/**
	 * returns array of global.config.keytypes which are in main-query in and not in
	 * @return array  array('in'=> array(), 'notIn'=>array()) // if value === null, dont will be used
	 */
	public function getKeyTypeFilter () { 
		return array(
			'in' => null,
			'notIn' => null,
		);
	}
	
}
