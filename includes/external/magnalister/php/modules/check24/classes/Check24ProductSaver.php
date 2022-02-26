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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class Check24ProductSaver {
	const DEBUG = false;

	protected $aMagnaSession = array();
	protected $sMarketplace = '';
	protected $sMpId = 0;

	protected $aConfig = array();

	public function __construct($magnaSession) {
		$this->aMagnaSession = &$magnaSession;
		$this->sMarketplace = $this->aMagnaSession['currentPlatform'];
		$this->mpId = $this->aMagnaSession['mpID'];

		$this->aConfig['keytype'] = getDBConfigValue('general.keytype', '0');
	}

	protected function insertPrepareData($aData) {
		if (($hp = magnaContribVerify('Check24InsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($aData, __METHOD__);
			die();
		}
		#echo print_m($aData, __METHOD__);
		MagnaDB::gi()->insert(TABLE_MAGNA_CHECK24_PROPERTIES, $aData, true);
	}

	/**
	 * Hilfsfunktion fuer SaveHoodSingleProductProperties und SaveHoodMultipleProductProperties
	 * bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
	 */
	protected function preparePropertiesRow($iProductId, $aItemDetails) {
		$aRow = array();
		$aRow['mpID'] = $this->mpId;
		$aRow['products_id'] = $iProductId;
		$aRow['products_model'] = MagnaDB::gi()->fetchOne('
			SELECT products_model
			  FROM '.TABLE_PRODUCTS.'
			 WHERE products_id =' . $iProductId
		);
		
		$aRow['Verified'] = 'OK';
		$aRow['ShippingTime'] = $aItemDetails['ShippingTime'];
		$aItemHandlingDataKeys = array (
			'DeliveryMode',
			'DeliveryModeText',
			'2MenHandling',
			'InstallationService',
			'RemovalOldItem',
			'RemovalPackaging',
			'AvailableServiceProductIds',
			'LogisticsProvider',
			'CustomTariffsNumber',
			'ReturnShippingCosts'
		);
		$aItemHandlingData = array();
		foreach ($aItemHandlingDataKeys as $sItemHandlingDataKey) {
		# TODO: CustomTariffsNumber anders, ggf. auch DeliveryModeText
			if (!empty($aItemDetails[$sItemHandlingDataKey])) {
				$aItemHandlingData[$sItemHandlingDataKey] = $aItemDetails[$sItemHandlingDataKey];
				unset($aItemDetails[$sItemHandlingDataKey]);
			}
		}
		$aRow['ItemHandlingData'] = json_encode($aItemHandlingData);

		if ($aItemDetails['ShippingCost'] === '') {
			$aRow['Verified'] = 'ERROR';
			$this->addToErrorLog(ML_CHECK24_ERROR_SHIPPING_COST, $aRow['products_model']);
		}
		
		$aRow['ShippingCost'] = $aItemDetails['ShippingCost'];
		
		return $aRow;
	}

	public function saveSingleProductProperties($iProductId, $aItemDetails) {
		//No SingleProductSave at this Time so use Multi
		$this->saveMultipleProductProperties(array($iProductId), $aItemDetails);
	}

	public function saveMultipleProductProperties($iProductIds, $aItemDetails) {
		$preparedTs = date('Y-m-d H:i:s');
		foreach ($iProductIds as $iProductId) {
			$aRow = $this->preparePropertiesRow($iProductId, $aItemDetails);
			$aRow['PreparedTs'] = $preparedTs;
			$this->insertPrepareData($aRow);
		}
	}
	
	private function addToErrorLog($errorMessage, $sku) {
		$errorData = array('SKU' => $sku);
		$error = array (
				'mpID' => $this->mpId,
				'origin' => 'plugin',
				'errormessage' => $errorMessage,
				'dateadded' =>date("Y-m-d H:i:s"),
				'additionaldata' => serialize($errorData),
			);
		MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $error);
	}
}
