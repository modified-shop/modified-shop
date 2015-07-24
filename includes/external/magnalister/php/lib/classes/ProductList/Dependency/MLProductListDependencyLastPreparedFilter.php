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
require_once DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependency.php';

class MLProductListDependencyLastPreparedFilter extends MLProductListDependency {
	
	protected function getDefaultConfig() {
		$mpId = $this->getMagnaSession('mpID');
		$aPrepareTsData = MagnaDB::gi()->fetchArray("
			  SELECT DISTINCT ".$this->getConfig('preparedtimestampfield')."
			    FROM `".$this->getConfig('propertiestablename')."`
			   WHERE ".$this->getConfig('preparedtimestampfield')." != '0000-00-00 00:00:00'
			         AND mpID = '".$mpId."'
			ORDER BY ".$this->getConfig('preparedtimestampfield')." DESC
			   LIMIT 100", true
		);
		$aSelectValues = array('0' => ML_OPTION_FILTER_LASTPREPARED_ARTICLES_ALL);
		if (!empty($aPrepareTsData)) {
			foreach ($aPrepareTsData as $sDateTime) {
				$aSelectValues[$sDateTime] = date (ML_OPTION_FILTER_LASTPREPARED_DATE_FORMAT, strtotime($sDateTime));
			}
		}
		return array(
			'selectValues' => $aSelectValues,
			// 'propertiestablename' => $this->getConfig('propertiestablename') // setted extern
			// 'propertiestablealias' => $this->getConfig('propertiestablealias') // setted extern
			// 'preparedtimestampfield' => $this->getConfig('preparedtimestampfield') // setted extern
		);
	}
	
	public function getFilterRightTemplate(){
		return 'select';
	}
	
	public function manipulateQuery() {
		if (!in_array($this->getFilterRequest(), array(null, '0'))) {
			$this->getQuery()->where($this->getConfig('propertiestablealias').'.'.$this->getConfig('preparedtimestampfield')."='".MagnaDB::gi()->escape($this->getFilterRequest())."'");
		}
		return $this;
		
	}
}
