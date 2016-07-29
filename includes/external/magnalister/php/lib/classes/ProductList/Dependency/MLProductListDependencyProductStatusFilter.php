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

class MLProductListDependencyProductStatusFilter extends MLProductListDependency {

	protected $aConditions = array();
	protected $sKeyType = '';
	protected $sTable = '';
	
	/**
	 * switch between manipulatequery and keytypefilter
	 * @var bool
	 */
	protected $blUseIdentFilter = false;
	
	/**
	 * makes array of unexecuted ML_Database_Model_Query_Select with querys over prepare table 
	 * the result will be excluded in MLProductListDependencyProductStatusFilter
	 * 
	 * @return array
	 */
	protected function getProductStatus() {
		return array(
			'active' =>  'p.`products_status` <> 0',
			'inactive' =>  'p.`products_status` = 0',
		);
	}

	public function manipulateQuery() {
		if (!$this->blUseIdentFilter && !in_array($this->getFilterRequest(), array(null, 'all', ''))) {
			$aStatusValues = $this->getProductStatus();
			$sFilter = $aStatusValues[$this->getFilterRequest()];
			$this->getQuery()->where($sFilter);
		}

		return $this;
	}

	protected function getDefaultConfig() {
		return array(
			'selectValues' => array(
				'all' => ML_OPTION_FILTER_PRODUCTSTATUS_ARTICLES_ALL,
				'active' => ML_OPTION_FILTER_PRODUCTSTATUS_ARTICLES_ACTIVE,
				'inactive' => ML_OPTION_FILTER_PRODUCTSTATUS_ARTICLES_INACTIVE,
			),
			'statusconditions' => array(
			// string => ML_Database_Model_Query_Select over keytype for filtering
			)
		);
	}

	public function getFilterRightTemplate() {
		return 'select';
	}
}
