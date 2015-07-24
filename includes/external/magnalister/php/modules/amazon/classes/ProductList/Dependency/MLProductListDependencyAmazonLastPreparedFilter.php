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
require_once DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependencyLastPreparedFilter.php';

class MLProductListDependencyAmazonLastPreparedFilter extends MLProductListDependency {
	protected function getDefaultConfig() {
		$mpId = $this->getMagnaSession('mpID');
		$aSelectValues = array('0' => ML_OPTION_FILTER_LASTPREPARED_ARTICLES_ALL);
		$aPrepareTsData = MagnaDB::gi()->fetchArray(eecho("
			(
				SELECT DISTINCT PreparedTs
				  FROM `".TABLE_MAGNA_AMAZON_APPLY."`
				 WHERE PreparedTs != '0000-00-00 00:00:00'
				       AND mpID = '".$mpId."'
			) UNION (
				SELECT DISTINCT PreparedTs
				  FROM `".TABLE_MAGNA_AMAZON_PROPERTIES."`
				 WHERE PreparedTs != '0000-00-00 00:00:00'
				       AND mpID = '".$mpId."'
			)
			ORDER BY PreparedTS DESC
			   LIMIT 100
		", false), true);
		if (!empty($aPrepareTsData)) {
			foreach ($aPrepareTsData as $sDateTime) {
				$aSelectValues[$sDateTime] = date (ML_OPTION_FILTER_LASTPREPARED_DATE_FORMAT, strtotime($sDateTime));
			}
		}
		return array(
			'selectValues' => $aSelectValues,
			// 'preparedtimestampfield' => $this->getConfig('preparedtimestampfield') // setted extern
		);
	}
	
	public function getFilterRightTemplate(){
		return 'select';
	}
	
	/**
	 * overwritten, because we use keytypefilter
	 * @return \MLProductListDependencyAmazonLastPreparedFilter
	 */
	public function manipulateQuery() {
		return $this;
	}
	
	public function getKeyTypeFilter() {
		if (!in_array($this->getFilterRequest(), array(null, '0'))) {
			$sKeyType = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id';
			return array(
				'in' => MagnaDB::gi()->fetchArray("
					(
						SELECT DISTINCT ".$sKeyType."
						  FROM `".TABLE_MAGNA_AMAZON_APPLY."`
						 WHERE PreparedTs = '".MagnaDb::gi()->escape($this->getFilterRequest())."'
						       AND mpID = '".$this->getMagnaSession('mpID')."'
					) UNION (
						SELECT DISTINCT ".$sKeyType."
						  FROM `".TABLE_MAGNA_AMAZON_PROPERTIES."`
						 WHERE PreparedTs = '".MagnaDb::gi()->escape($this->getFilterRequest())."'
						       AND mpID = '".$this->getMagnaSession('mpID')."'
					)
				", true),
				'notIn' => null,
			);
		} else {
			return parent::getKeyTypeFilter();
		}
	}
}
