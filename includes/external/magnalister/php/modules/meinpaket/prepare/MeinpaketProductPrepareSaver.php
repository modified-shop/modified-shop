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
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
// äöüß

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// REPLACE INTO `magnalister_meinpaket_properties`
//	(`mpID`,`products_id`,`products_model`,`mp_category_id`,`store_category_id`)
//	(SELECT * FROM magnalister_meinpaket_categorymatching)

class MeinpaketProductPrepareSaver {

	public $aErrors = array();

	protected $resources = array();
	
	protected $mpId = 0;
	protected $marketplace = '';
	
	protected $isAjax = false;
	
	protected $prepareSettings = array();
	
	public function __construct(&$resources, $prepareSettings) {
		$this->resources = &$resources;
		#echo print_m($this->resources, '$this->resources');
		
		$this->mpId = $this->resources['session']['mpID'];
		$this->marketplace = $this->resources['session']['currentPlatform'];
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		
		$this->prepareSettings = $prepareSettings;
	}
	
	public function loadDefaults() {
		return array(
			'MarketplaceCategory' => '',
			'StoreCategory' => '',
			'VariationConfiguration' => '',
			'ShippingDetails' => array (
				'ShippingCost' => getDBConfigValue($this->marketplace.'.prepare.shippingdetails.shippingcost', $this->mpId, ''),
				'ShippingCostFixed' => getDBConfigValue(array($this->marketplace.'.prepare.shippingdetails.shippingcostfixed', 'val'), $this->mpId, false),
				'ShippingType' => getDBConfigValue($this->marketplace.'.prepare.shippingdetails.shippingtype', $this->mpId, ''),
			),
		);
	}
	
	public function loadProperties($pId) {
		return array();
	}
	
	public function loadSelection() {
		// load already prepared data
		$dbOldSelectionQuery = '
		    SELECT mp.*
		      FROM ' . TABLE_MAGNA_MEINPAKET_PROPERTIES . ' mp
		';
		if ('artNr' == getDBConfigValue('general.keytype', '0')) {
			$dbOldSelectionQuery .= '
		INNER JOIN ' . TABLE_PRODUCTS . ' p ON mp.products_model = p.products_model
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON  p.products_id = ms.pID AND mp.mpID = ms.mpID
			';
		} else {
			$dbOldSelectionQuery .= '
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON mp.products_id = ms.pID AND mp.mpID = ms.mpID
			';
		}
		$dbOldSelectionQuery .='
		     WHERE ms.selectionname="'.$this->prepareSettings['selectionName'].'"
		           AND ms.mpID = "' . $this->mpId . '"
		           AND ms.session_id="' . session_id() . '"
		           AND mp.products_id IS NOT NULL
		           AND TRIM(mp.products_id) <> ""
		     LIMIT 1
		';
		
		#echo print_m($dbOldSelectionQuery, '$dbOldSelectionQuery');
		$data = MagnaDB::gi()->fetchRow($dbOldSelectionQuery);
		
		#echo print_m($data, '$data');
		$defaults = $this->loadDefaults();
		
		if (empty($data)) {
			$data = $defaults;
		} else {
			try {
				$data['ShippingDetails'] = @json_decode($data['ShippingDetails'], true);
			} catch (Exception $e) {}
			if (empty($data['ShippingDetails'])) {
				$data['ShippingDetails'] = $defaults['ShippingDetails'];
			}
		}
		return $data;
	}
	
	protected function loadProductsModel($pIds) {
		return MagnaDB::gi()->fetchArray('
			SELECT p.products_id, p.products_model
			  FROM ' . TABLE_PRODUCTS . ' p
			 WHERE p.products_id IN (' . implode($pIds, ', ') . ')
		');
	}
	
	public function saveProperties($pIds, $data) {
		#echo print_m(func_get_args(), __METHOD__);
		$defaults = $this->loadDefaults();
		
		$pIds = $this->loadProductsModel($pIds);
		
		$data['PreparedTs'] = date('Y-m-d H:i:s');
		$data['Verified'] = 'OK';

		if (empty($data['MarketplaceCategory'])) {
			$this->aErrors['ML_MEINPAKET_ERROR_CATEGORY'] = ML_MEINPAKET_ERROR_CATEGORY;
		}

		if (!empty($this->aErrors)) {
			$data['Verified'] = 'ERROR';
		}

		foreach ($pIds as $row) {
			$set = array_replace_recursive(
				array (
					'mpID' => $this->mpId
				),
				$row,
				$defaults,
				$data
			);
			$set['ShippingDetails'] = json_encode($set['ShippingDetails']);
			
			/*
			 * omit double data sets (the table primary unique key contains both products_id and _model,
			 * so it doesn't prevent it)
			 */
			switch (getDBConfigValue('general.keytype', '0')) {
				case ('pID'):
					MagnaDB::gi()->delete(TABLE_MAGNA_MEINPAKET_PROPERTIES, array (
						'mpID' => $this->mpId,
						'products_id' => $set['products_id']
					));
					break;
				case ('artNr'):
					MagnaDB::gi()->delete(TABLE_MAGNA_MEINPAKET_PROPERTIES, array (
						'mpID' => $this->mpId,
						'products_model' => $set['products_model']
					));
					break;
				default: break;
			}
			MagnaDB::gi()->insert(TABLE_MAGNA_MEINPAKET_PROPERTIES, $set, true);
		}
		
		return true;
	}
	
	public function deleteProperties($pIds) {
		if ('artNr' == getDBConfigValue('general.keytype', '0')) {
			$sType = 'products_model';
			$aIds = array();
			foreach ($this->loadProductsModel($pIds) as $aId) {
				$aIds[] = $aId['products_model'];
			}
		} else {
			$sType = 'products_id';
			$aIds = $pIds;
		}
		MagnaDB::gi()->query('
			DELETE FROM '.TABLE_MAGNA_MEINPAKET_PROPERTIES.'
			 WHERE mpID = "'.$this->mpId.'"
			       AND '.$sType .' IN ("'.implode('", "', $aIds).'")
		');
		return true;
	}
	
	public function resetProperties($pId) {
		return true;
	}
	
}
