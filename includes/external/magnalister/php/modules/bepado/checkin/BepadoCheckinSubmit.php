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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'bepado/BepadoHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'bepado/classes/BepadoProductSaver.php');

class BepadoCheckinSubmit extends MagnaCompatibleCheckinSubmit {
	private $bVerify = false;
	private $oLastException = null;

	public function __construct($settings = array()) {
		global $_MagnaSession;

		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], ''),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'itemsPerBatch' => 100,
			'mlProductsUseLegacy' => false,
		), $settings);
		
		parent::__construct($settings);
		
		$this->settings['SyncInventory'] = array (
			'Price' => getDBConfigValue($settings['marketplace'].'.inventorysync.price', $this->mpID, '') == 'auto',
			'Quantity' => getDBConfigValue($settings['marketplace'].'.stocksync.tomarketplace', $this->mpID, '') == 'auto',
		);
	}

	protected function generateRequestHeader() {
		# das Request braucht nur action, subsystem und data
		return array(
			'ACTION' => ($this->bVerify ? 'VerifyAddItems' : 'AddItems'),
			'SUBSYSTEM' => 'bepado',
			'MODE' => isset($this->submitSession['mode']) ? $this->submitSession['mode'] : 'ADD',
		);
	}

	protected function processException($e) {
		$this->oLastException = $e;
	}

	public function getLastException() {
		return $this->oLastException;
	}
	
	protected function setUpMLProduct() {
		parent::setUpMLProduct();
		
		// Set a db matching (e.g. 'ManufacturerPartNumber')
		$mfrmd = getDBConfigValue($this->settings['marketplace'].'.checkin.manufacturerpartnumber.table', $this->mpID, false);
		if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
			$pIDAlias = getDBConfigValue($this->settings['marketplace'].'.checkin.manufacturerpartnumber.alias', $this->mpID);
			if (empty($pIDAlias)) {
				$pIDAlias = 'products_id';
			}
			MLProduct::gi()->setDbMatching('ManufacturerPartNumber', array (
				'Table'  => $mfrmd['table'],
				'Column' => $mfrmd['column'],
				'Alias'  => $pIDAlias,
			));
		}
		
		// Set Price and Quantity settings
		MLProduct::gi()->setPriceConfig(BepadoHelper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(BepadoHelper::loadQuantitySettings($this->mpID));
	}

	protected function appendAdditionalData($iPID, $aProduct, &$aData) {
		#echo print_m(func_get_args(), __METHOD__.'{L'.__LINE__.'}');
		
		$aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_BEPADO_PROPERTIES.'
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr')
				? 'products_model = "'.MagnaDB::gi()->escape($aProduct['ProductsModel']).'"'
				: 'products_id = "'.$iPID.'"'
			) . '
			       AND mpID = '.$this->_magnasession['mpID']
		);
		// Will not happen in sumbit cycle but can happen in loadProductByPId.
		if (empty($aPropertiesRow)) {
			$aData['submit'] = array();
			return;
		}

		foreach (array('MarketplaceCategories', 'ShippingServiceOptions') as $jsonKey) {
			$aPropertiesRow[$jsonKey] = json_decode($aPropertiesRow[$jsonKey], true);
			if (!is_array($aPropertiesRow[$jsonKey])) {
				$aPropertiesRow[$jsonKey] = array();
			}
		}
		
		#echo print_m($aPropertiesRow, '$aPropertiesRow');
			
		$aData['submit'] = array_replace($aProduct, $aPropertiesRow);
		$unset = array(
			// Data from $aPropertiesRow
			'SubmitPurchasePrice', 'Verified', 'PreparedTS', 'TopMarketplaceCategory', 'mpID', 'products_id', 'products_model',
			// Data from $aProduct
			'TaxClass', 'ShippingTimeId', 'Variations'
		);
		foreach ($unset as $unsetField) {
			unset($aData['submit'][$unsetField]);
		}
		
		$aData['submit']['SKU'] = ($this->settings['keytype'] == 'artNr')
			? $aProduct['MarketplaceSku']
			: $aProduct['MarketplaceId'];
			
		#echo print_m($aData['submit'], 'submit{1}');
			
		//Images
		$sImagePath = getDBConfigValue($this->marketplace.'.imagepath', $this->mpID, '');
		if (empty($sImagePath)) {
			$sImagePath = SHOP_URL_POPUP_IMAGES;
		}
		$aImages = array();
		if (!empty($aProduct['Images'])) {
			foreach($aProduct['Images'] as $sImg) {
				$aImages[] = array('URL' => $sImagePath.$sImg);
			}
		}
		$aData['submit']['Images'] = $aImages;
		
		if ($aPropertiesRow['SubmitPurchasePrice'] == 'true') {
			$aData['submit']['PurchasePrice'] = $aData['submit']['Price']['PurchasePrice'];
		}
		$aData['submit']['Price'] = $aData['submit']['Price']['Price'];
		$aData['submit']['Currency'] = $aData['submit']['Currency']['Price'];
		
		if (!$this->settings['SyncInventory']['Price']) {
			$data['submit']['Price'] = $data['price'];
		}
		if (!$this->settings['SyncInventory']['Quantity']) {
			$data['submit']['Quantity'] = (int)$data['quantity'];
		}
		
		$aData['submit']['Tax'] = $aData['submit']['TaxPercent'];
		unset($aData['submit']['TaxPercent']);
		
		$aData['submit']['ItemUrl'] = $aData['submit']['ProductUrl'];
		unset($aData['submit']['ProductUrl']);
		
		// MarketplaceCategories
		if (is_array($aPropertiesRow['MarketplaceCategories'])) {
			$aData['submit']['MarketplaceCategories'] = array_values($aPropertiesRow['MarketplaceCategories']);
			if (isset($aData['submit']['MarketplaceCategories'][0])) {
				$aData['submit']['MarketplaceCategories'] = array($aData['submit']['MarketplaceCategories'][0]);
			}
		}

		// ShippingTime
		if (getDBConfigValue(array($this->marketplace.'.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false)) {
			$aData['submit']['ShippingTime'] = getDBConfigValue(
				array($this->marketplace.'.leadtimetoshipmatching.values', $aProduct['ShippingTimeId']),
				$this->mpID,
				getDBConfigValue($this->marketplace.'.checkin.leadtimetoship', $this->mpID, 0)
			);
		}
		if (!isset($aData['submit']['ShippingTime']) || empty($aData['submit']['ShippingTime'])) {
			$aData['submit']['ShippingTime'] = $aPropertiesRow['ShippingTime'];
		}
		if (empty($aData['submit']['ShippingTime'])) {
			$aData['submit']['ShippingTime'] = getDBConfigValue($this->marketplace.'.checkin.leadtimetoship', $this->mpID, 0);
		}
		
		#echo print_m($aData['submit'], 'submit{complete}');
	}

	protected function markAsFailed($sku) {
		$iPID = magnaSKU2pID($sku);
		$this->badItems[] = $iPID;
		unset($this->selection[$iPID]);
	}

	public function verifyOneItem($bEchoRequest = false) {
		$this->bVerify = true;
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'].'Verify',
			'session_id' => session_id()
		));
		$item = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID="'.$this->_magnasession['mpID'].'" AND
			       selectionname="'.$this->settings['selectionName'].'" AND
			       session_id="'.session_id().'"
			 LIMIT 1
		');
		if (empty($item)) {
			return false;
		}

		$oldSelectionName = $this->settings['selectionName'];
		$this->settings['selectionName'] = $this->settings['selectionName'].'Verify';
		$item['selectionname'] = $this->settings['selectionName'];
		MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, $item);

		//echo print_m($this->settings, '$this->settings');
		$this->init('VERIFY');
		
		$this->initSelection(0, 1);
		//echo print_m($this->selection, '$this->selection[1]');
		foreach ($this->selection as $pID => &$data) {
			if (!isset($data['quantity']) || ($data['quantity'] == 0)) {
				$data['quantity'] = 1; // hack to get verification of zero quantity items working
			}
		}

		$this->populateSelectionWithData();
		//echo print_m($this->selection, '$this->selection[2]');

		//Debug no sendRequest Fake result
		#$aResult = array('STATUS' => 'SUCCESS');

		#$this->sendRequest(true, true);
		$aResult = $this->sendRequest(false, $bEchoRequest);

		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id()
		));

		// restore selection name
		$this->settings['selectionName'] = $oldSelectionName;

		# Liste der pIDs um die ebay_properties upzudaten
		$aSelectedPIDs = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT pID
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = "'.$this->_magnasession['mpID'].'"
			       AND selectionname = "'.$this->settings['selectionName'].'"
			       AND session_id = "'.session_id().'"
		', true);
		MagnaDB::gi()->query(eecho('
			UPDATE '.TABLE_MAGNA_BEPADO_PROPERTIES. '
			   SET Verified = "'.(('SUCCESS' == $aResult['STATUS']) ? 'OK' : 'ERROR').'"
			 WHERE mpID = '.$this->_magnasession['mpID'].'
			       AND products_id IN ('.implode(', ', $aSelectedPIDs).')
		', false));
		
		return $aResult;
	}

	protected function postSubmit() {
		try {
			//*
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
			//*/
		} catch (MagnaException $e) {
			$this->submitSession['api']['exception'] = $e;
			$this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();
		}
	}

}
