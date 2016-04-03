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
require_once DIR_MAGNALISTER_INCLUDES . 'lib/classes/ProductList/Dependency/MLProductListDependency.php';

abstract class MLProductListDependencyPrepareStatusFilter extends MLProductListDependency {

	abstract protected function getPrepareTable();

	abstract protected function getPrepareCondition();

	protected $aConditions = array();
	protected $sKeyType = '';
	protected $sTable = '';
	
	/**
	 * switch between manipulatequery and keytypefilter
	 * @var bool
	 */
	protected $blUseIdentFilter = true;
	
	/**
	 * makes array of unexecuted ML_Database_Model_Query_Select with querys over prepare table 
	 * the result will be excluded in MLProductListDependencyPrepareStatusFilter
	 * 
	 * @return array
	 */
	protected function getPreparedStatus() {
		$this->sKeyType = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id';
		$this->sTable = $this->getPrepareTable();
		$this->aConditions = $this->getPrepareCondition();
		$aStatusCOndition = array();
		foreach($this->aConditions as $sStatus => $aValue){
			$aStatusCOndition[$sStatus] = $this->{'get'.ucfirst($sStatus).'Condition'}();
		}
		return $aStatusCOndition;
	}

	public function manipulateQuery() {
		if (!$this->blUseIdentFilter && !in_array($this->getFilterRequest(), array(null, 'all', ''))) {
			$aStatusValues = $this->getPreparedStatus();
			$aFilter = $aStatusValues[$this->getFilterRequest()];
			$this->getQuery()->where("p." . $this->sKeyType ." ".($aFilter['filter'] == 'in' ? "IN" : "NOT IN")."(".$aFilter['query']->getQuery(false).")");
		}
		return $this;
	}
	
	public function getKeyTypeFilter () { 
		if ($this->blUseIdentFilter && !in_array($this->getFilterRequest(), array(null, 'all', ''))) {
			$aStatusValues = $this->getPreparedStatus();
			$aFilter = $aStatusValues[$this->getFilterRequest()];
			$aFilterValues = array();
			foreach ($aFilter['query']->getResult() as $aRow) {
				$aFilterValues[] = $aRow[$this->sKeyType];
			}
			return array(
				'in' => $aFilter['filter'] == 'in' ? $aFilterValues : null,
				'notIn' => $aFilter['filter'] == 'notIn' ? $aFilterValues : null,
			);
		} else {
			return parent::getKeyTypeFilter();
		}
	}

	protected function getDefaultConfig() {
		return array(
			'selectValues' => array(
				'all' => ML_OPTION_FILTER_PREPARESTATUS_ARTICLES_ALL,
				'notprepared' => ML_OPTION_FILTER_PREPARESTATUS_ARTICLES_NOTPREPARED,
				'failed' => ML_OPTION_FILTER_PREPARESTATUS_ARTICLES_FAILED,
				'prepared' => ML_OPTION_FILTER_PREPARESTATUS_ARTICLES_PREPARED
			),
			'statusconditions' => array(
			// string => ML_Database_Model_Query_Select over keytype for filtering
			)
		);
	}

	public function getFilterRightTemplate() {
		return 'select';
	}
	
	protected function getFailedCondition(){
		return array(
			'filter' => 'in',
			'query' => MLDatabase::factorySelectClass()
					->select('DISTINCT '.$this->sKeyType)
					->from($this->sTable)
					->where("
						mpID = '".$this->aMagnaSession['mpID']."'
						{$this->aConditions['failed']}
					")
		);
	}
	protected function getPreparedCondition(){
		return array(
			'filter' => 'in',
			'query' => MLDatabase::factorySelectClass()
				->select('DISTINCT '.$this->sKeyType)
				->from($this->sTable)
				->where("
						mpID = '".$this->aMagnaSession['mpID']."'
						{$this->aConditions['prepared']}
				")
		);
	}
	protected function getNotpreparedCondition(){
		return array(
			'filter' => 'notIn',
			'query' => MLDatabase::factorySelectClass()
				->select('DISTINCT '.$this->sKeyType)
				->from($this->sTable)
				->where("
					mpID = '".$this->aMagnaSession['mpID']."'
					{$this->aConditions['notprepared']}
				")
		);
	}

}
