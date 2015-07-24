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

class MLProductListDependencyMarketplaceSync extends MLProductListDependency {
	
	protected $blCache = true; //default = true
	
	protected $filterValues = array (
		'' => ML_OPTION_FILTER_ARTICLES_ALL,
		'notactive' => ML_OPTION_FILTER_ARTICLES_NOTACTIVE,
		'active' => ML_OPTION_FILTER_ARTICLES_ACTIVE,
		'nottransferred' => ML_OPTION_FILTER_ARTICLES_NOTTRANSFERRED,
		'sync' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_SYNC,
//		'button' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_BUTTON,
		'expired' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_EXPIRED,
	);
	
	/**
	 * @NOTICE: Order is important here. The default filter has to be executed after all the other filters
	 * @var array $filterByType assoc
	 */
	protected $filterByType = array (
		'deleted' => array('sync', 'button', 'expired'),
		'default' => array( // default always includes all available options.
			'notactive', 'nottransferred', 'active', 
			'sync', 'button', 'expired' // delete actions need default
		),
	);
	
	public function getFilterRightTemplate() {
		return 'marketplacesync';
	}
	
	protected function getDefaultConfig() {
		return array(
			'propertiestablename' => null,//if value ends with dot (.) its a table-alias in query -> so properties-table will not joined, should be joined before
			'limit' => 500,
		);
	}
	
	public function getFilterValues() {
		return $this->filterValues;
	}
	
	public function getFiltersByType($type = '') {
		return empty($type)
			? $this->filterByType
			: (isset($this->filterByType[$type]) 
				? $this->filterByType[$type] 
				: array()
			);
	}
	
	public function manipulateQuery() {
		if (substr($this->getConfig('propertiestablename'), -1) === '.') {
			$sAlias = $this->getConfig('propertiestablename');
		} else {
			$sAlias = 'ep.';
		}
		
		switch ($this->getFilterRequest()) {
			case 'notactive' : {
				$this->getQuery()
					->where($sAlias."Verified in('OK', 'EMPTY') OR ".$sAlias."Verified IS NULL")
					->where($sAlias."transferred='0' OR ".$sAlias."itemid IS NULL OR ".$sAlias."itemid='' OR ".$sAlias."deletedBy!=''")
					->where($sAlias."mpid='".$this->getMagnaSession('mpID')."' OR ".$sAlias."mpid is null")
				;
				$iJoinType = ML_Database_Model_Query_Select::JOIN_TYPE_LEFT;
				break;
			}
			case 'nottransferred' : {
				$this->getQuery()
					->where($sAlias."Verified IN('OK', 'EMPTY') OR ".$sAlias."Verified IS NULL")
					->where($sAlias."transferred='0' OR ".$sAlias."itemid IS NULL OR ".$sAlias."itemid =''")
					->where($sAlias."deletedBy='' OR ".$sAlias."deletedBy is null")
					->where($sAlias."mpid='".$this->getMagnaSession('mpID')."' OR ".$sAlias."mpid is null")
				;
				$iJoinType = ML_Database_Model_Query_Select::JOIN_TYPE_LEFT;
				break;
			}
			case 'active': {
				$this->getQuery()
					->where($sAlias."Verified in('OK', 'EMPTY')")
					->where($sAlias."transferred='1'")
					->where($sAlias."itemid is not null AND ".$sAlias."itemid !=''")
					->where($sAlias."deletedBy=''")
					->where($sAlias."mpid='".$this->getMagnaSession('mpID')."'")
				;
				$iJoinType = ML_Database_Model_Query_Select::JOIN_TYPE_INNER;
				break;
			}
			case 'sync':
			case 'button':
			case 'expired': {
				$this->getQuery()
					->where($sAlias."Verified in('OK', 'EMPTY')")
					->where($sAlias."deletedBy='" . $this->getFilterRequest() . "'")
					->where($sAlias."mpid='".$this->getMagnaSession('mpID')."'")
				;
				$iJoinType = ML_Database_Model_Query_Select::JOIN_TYPE_INNER;
				break;
			}
		}
		#echo var_dump_pre($_SESSION['magna_deletedFilter'][$this->getMagnaSession('mpID')], 'magna_deletedFilter');
		#echo var_dump_pre($sSql, $this->getFilterRequest());
		
		if (isset($iJoinType) && $this->getConfig('propertiestablename') != $sAlias) {//tablename is equal to table alias - so table is already joined
			$this->getQuery()->join(
				array(
					$this->getConfig('propertiestablename'),
					substr($sAlias, 0, -1),//no dot
					(
						(getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'p.products_model = '.$sAlias.'products_model'
						: 'p.products_id = '.$sAlias.'products_id'
					)
				),
				$iJoinType
			);
		}
		return $this;
	}
	
	public function getHeaderTemplate() {
		return 'marketplacesync';
	}
	
	public function executeAction() {
		$aRequest = $this->getActionRequest();
		if ($this->getProductList()->isAjax() && ($aRequest !== null)) {
			try {
				$this->apiRequest(
					$aRequest['step'] == 'default'
						? null 
						: strtoupper($aRequest['step']),
					(int) $aRequest['offset'],
					(int) $aRequest['limit']
				);
				echo json_encode(array('success' => true));
			} catch (Exception $oEx) {
				echo $oEx->getMessage();
			}
			exit;
		}
	}
	
	public function filterNeedsSync($sFilter) {
		#echo $sFilter;
		$mpID = $this->getMagnaSession('mpID');
		return !isset($_SESSION['magna_deletedFilter'][$mpID][$sFilter])
			|| (($_SESSION['magna_deletedFilter'][$mpID][$sFilter] + 1800) < time())
			|| !$this->blCache
		;
	}
	
	protected function apiRequestProcessDeleted($mpID, $offset, $limit) {
		$_SESSION['magna_deletedFilter'][$mpID]['deleted'] = time();
		try {
			$request = array(
				'ACTION' => 'GetInventory',
				'SUBSYSTEM' => $this->getMagnaSession('currentPlatform'),
				'MARKETPLACEID' => $mpID,
				'LIMIT' => $limit,
				'OFFSET' => $offset,
				'ORDERBY' => 'DateAdded',
				'SORTORDER' => 'DESC',
				'FILTER' => 'DELETED',
			);
			$result = MagnaConnector::gi()->submitRequest($request);
			if (!empty($result['DATA'])) {
				if((int)$offset == 0) {
					MagnaDb::gi()->query("OPTIMIZE TABLE ".$this->getConfig('propertiestablename'));
				}
				foreach ($result['DATA'] as $item) {
					if (!empty($item['MasterSKU'])) {
						$pID = magnaSKU2pID($item['MasterSKU']);
					} else {
						$pID = magnaSKU2pID($item['SKU']);
					}
					$this->updatePropertiesTable($pID, array(
						'deletedBy' => $item['deletedBy'],
					));
				}
			}
			$numberofitems = (int) $result['NUMBEROFLISTINGS'];
			if (($numberofitems - $offset - $limit) > 0) { //recursion
				$offset += $limit;
				$limit = (($offset + $limit) >= $numberofitems) ? $numberofitems - $offset : $limit;
				throw new Exception(json_encode(array(
					'params' => array(
						'offset' => $offset,
						'limit' => $limit,
					),
					'info' => array(
						'current' => $offset,
						'total' => $numberofitems,
					)
				)));
			}
		} catch (MagnaException $e) {
			//echo $e->getMessage();
		}
		
	}
	
	protected function apiRequestProcessDefault($mpID, $offset, $limit) {
		$_SESSION['magna_deletedFilter'][$mpID]['default'] = time();
		try {
			if ((int)$offset == 0) {
				MagnaDb::gi()->query("OPTIMIZE TABLE ".$this->getConfig('propertiestablename'));
				// set all articles as deleted, after api-request they should be correct not-deleted-value
				MagnaDB::gi()->query("
					UPDATE ".$this->getConfig('propertiestablename')."
					   SET deletedBy = 'notML' 
					 WHERE deletedBy = '' 
					       AND mpID = '".$mpID."'
				");
			}
			$request = array(
				'ACTION' => 'GetInventoryOnlySKUs',
				'SUBSYSTEM' => $this->getMagnaSession('currentPlatform'),
				'MARKETPLACEID' => $mpID,
			);
			$result = MagnaConnector::gi()->submitRequest($request);
			if (!empty($result['DATA'])) {
				foreach ($result['DATA'] as $iCount => $item) {
					if ($iCount < $offset ) {
						continue;
					}
					if ($iCount > ($offset + $limit)) {
						break;
					}
					$pID = magnaSKU2pID($item);
					$this->updatePropertiesTable($pID, array(
						'deletedBy' => '',
					));
				}
				$numberofitems = count($result['DATA']);
				if ($numberofitems - $offset - $limit > 0) { //recursion
					$offset += $limit;
					$limit = (($offset + $limit) >= $numberofitems) ? $numberofitems - $offset : $limit;
					throw new Exception(json_encode(array(
						'params' => array(
							'offset' => $offset,
							'limit' => $limit,
						),
						'info' => array(
							'current' => $offset,
							'total' => $numberofitems,
						)
					)));
				}
			}
		} catch (MagnaException $e) {
			//echo $e->getMessage();
		}
	}
	
	protected function apiRequest($sFilter = null, $offset = 0, $limit = 100) {
		$mpID = $this->getMagnaSession('mpID');
		
		if (strtolower($sFilter) == 'deleted') {
			if ($this->filterNeedsSync('deleted') || ($offset != 0)) {
				$this->apiRequestProcessDeleted($mpID, $offset, $limit);
			}
		} else {
			if ($this->filterNeedsSync('default') || ($offset != 0)) {
				$this->apiRequestProcessDefault($mpID, $offset, $limit);
			}
		}
	}
	
	protected function updatePropertiesTable($pID, $data) {
		if ($pID == 0) { // product does not exist
			return;
		}
		$mpID = $this->getMagnaSession('mpID');
		// get sku for current product-id - id can be changed
		$products_model = MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id = '.$pID.'');
		$aIdentify = array('mpID' => $mpID);
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$aIdentify['products_model'] = $products_model;
		} else {
			$aIdentify['products_id'] = $pID;
		}
		//setItemd=='true' if not setted
		$data['transferred'] = 1; //todo check if depends on entry exists
		if (MagnaDB::gi()->recordExists($this->getConfig('propertiestablename'), $aIdentify)) {
			$sSet = '';
			foreach ($data as $sKey => $sValue) {
				$sSet .= $sKey."='".MagnaDB::gi()->escape($sValue)."', ";
			}
			if (!isset($data['ItemID']) || empty($data['ItemID'])) {
				// set itemid to not null, if response comes from GetInventoryOnlySkus itemid is not setted
				$sSet .= "ItemID = if (ItemID is null, '__true__', ItemID), ";
			}
			$sSet = substr($sSet, 0, -2);
			$sWhere = '';
			foreach ($aIdentify as $sKey => $sValue) {
				$sWhere .= " ".$sKey."='".$sValue."' AND";
			}
			$sWhere =  substr($sWhere, 0, -4);
			/**
			 * limit is deactive, because one customer had changed the productmodel
			 */
			MagnaDB::gi()->query("
				UPDATE ".$this->getConfig('propertiestablename')."
				SET ".$sSet."
				WHERE ".$sWhere."
				-- LIMIT 1
			");
		} else {
			$data['ItemID'] = (isset($data['ItemID']) && !empty($data['ItemID'])) ? $data['ItemID'] : '__true__';
			$data['products_id'] = $pID;
			$data['products_model'] = $products_model;
			$data['Verified'] = 'EMPTY';
			$data['mpID'] = $mpID;
			MagnaDB::gi()->insert($this->getConfig('propertiestablename'), $data);
		}
	}
	
}
