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

class IdealoProductPrepareSaver {
	protected $resources = array();
	
	protected $mpId = 0;
	protected $marketplace = '';
	
	protected $isAjax = false;
	
	protected $prepareSettings = array();

	protected $generalKeyType;
	
	public function __construct(&$resources, $prepareSettings) {
		$this->resources = &$resources;
		$this->mpId = $this->resources['session']['mpID'];
		$this->marketplace = $this->resources['session']['currentPlatform'];
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		
		$this->prepareSettings = $prepareSettings;

		$this->generalKeyType = getDBConfigValue('general.keytype', '0');
	}
	
	public function loadDefaults() {
		return array (
			'Checkout' => getDBConfigValue($this->marketplace . '.checkout.status', $this->mpId),
			'PaymentMethod' => getDBConfigValue($this->marketplace . '.payment.methods', $this->mpId),
			'ShippingMethod' => getDBConfigValue($this->marketplace . '.shipping.methods', $this->mpId),
			'ShippingCountry' => getDBConfigValue($this->marketplace . '.shipping.country', $this->mpId),
			'ShippingCostMethod' => getDBConfigValue($this->marketplace . '.shipping.method', $this->mpId),
			'ShippingCost' => getDBConfigValue($this->marketplace . '.shipping.cost', $this->mpId),
			'DeliveryTime' => getDBConfigValue($this->marketplace . '.deliverytime', $this->mpId),
			'FulFillmentType' => getDBConfigValue($this->marketplace . '.shipping.methods', $this->mpId),
			'TwoManHandlingFee' => getDBConfigValue($this->marketplace . '.shipping.methods.twomanhandlingfee', $this->mpId),
			'DisposalFee' => getDBConfigValue($this->marketplace . '.shipping.methods.disposalfee', $this->mpId),
		);
	}
	
	public function loadProperties($pId) {
		$lang = getDBConfigValue($this->marketplace . '.lang', $this->mpId);

		$prod = MagnaDB::gi()->fetchArray('
			SELECT
				pd.products_name as Title,
				pd.products_description as Description
			FROM ' . TABLE_PRODUCTS . ' p
			LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = ' . $lang . '
			WHERE p.products_id = ' . $pId
		);

		$aItemDetails['Title'] = $prod[0]['Title'];
		$aItemDetails['Description'] = $prod[0]['Description'];

		return $aItemDetails;
	}
	
	public function loadSelection() {
		// load already prepared data
		$dbOldSelectionQuery = '
		    SELECT mp.*
		      FROM ' . TABLE_MAGNA_IDEALO_PROPERTIES . ' mp
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
		}

		return $data;
	}
	
	protected function loadProductsModel($pIds) {
		return MagnaDB::gi()->fetchArray('
			SELECT p.products_id, p.products_model
			FROM ' . TABLE_PRODUCTS . ' p
			WHERE p.products_id IN (' . implode(', ', $pIds) . ')
		');
	}
	
	public function saveProperties($pIds, $data) {
		$defaults = $this->loadDefaults();

		$pIds = $this->loadProductsModel($pIds);
		
		$data['PreparedTs'] = date('Y-m-d H:i:s');
		$data['Verified'] = 'OK';

		foreach ($pIds as $row) {
			$properties = $this->loadProperties($row['products_id']);

			$set = array_replace_recursive(
				array (
					'mpID' => $this->mpId
				),
				$row,
				$defaults,
				$properties,
				$data
			);

			if (isset($set['PictureUrl'])) {
				$aImages = (array)$set['PictureUrl'];
				if (in_array('false', $aImages) && count($aImages) > 1) {
					array_shift($aImages);
				}

				$aPictureURL = array();
				foreach ($aImages as $key => $value) {
					$aPictureURL[urldecode($key)] = $value;
				}

				$set['PictureUrl'] = json_encode($aPictureURL);
			} else {
				$aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->marketplace . '.lang', $this->mpId))->getProductById($row['products_id']);

				$images = array();

				foreach ($aProduct['Images'] as $image) {
					$images[$image] = 'true';
				}

				$set['PictureUrl'] = json_encode($images);
			}

			if (isset($data['Checkout']) && $data['Checkout'] === 'on') {
				$set['Checkout'] = json_encode(array('val' => true));
			} else {
				$set['Checkout'] = json_encode(array('val' => false));
			}
			$set['PaymentMethod'] = json_encode((array)$set['PaymentMethod']);
			MagnaDB::gi()->insert(TABLE_MAGNA_IDEALO_PROPERTIES, $set, true);
			// remove outdated entries
			if ('artNr' == $this->generalKeyType) {
				MagnaDB::gi()->query('DELETE FROM '.TABLE_MAGNA_IDEALO_PROPERTIES.'
					WHERE products_model = \''.$set['products_model'].'\'
					  AND products_id != '.$set['products_id']);
			}
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
			DELETE FROM '.TABLE_MAGNA_IDEALO_PROPERTIES.'
			WHERE mpID = "'.$this->mpId.'"
				AND '.$sType .' IN ("'.implode('", "', $aIds).'")
		');

		return true;
	}
	
	public function resetProperties($pId) {
		return true;
	}
}
