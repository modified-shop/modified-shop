<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'temu/TemuHelper.php');

class TemuProductSaver {

	protected $aMagnaSession = array();
	protected $aErrors = array();
	protected $aMissingFields = array();

	public function __construct(&$magnaSession) {
		$this->aMagnaSession = &$magnaSession;
	}

	public function getErrors() {
		return $this->aErrors;
	}

	public function getMissingFields() {
		return $this->aMissingFields;
	}

	public function insertPrepareData($aData) {
		$mpID = $this->aMagnaSession['mpID'];

		$aInsert = array(
			'mpID' => $mpID,
			'products_id' => $aData['products_id'],
			'products_model' => isset($aData['products_model']) ? $aData['products_model'] : '',
			'PrepareType' => isset($aData['PrepareType']) ? $aData['PrepareType'] : 'Apply',
			'Title' => isset($aData['Title']) ? $aData['Title'] : '',
			'Description' => isset($aData['Description']) ? $aData['Description'] : '',
			'BulletPoints' => isset($aData['BulletPoints']) ? $aData['BulletPoints'] : '',
			'PrimaryCategory' => isset($aData['PrimaryCategory']) ? $aData['PrimaryCategory'] : '',
			'TopPrimaryCategory' => isset($aData['TopPrimaryCategory']) ? $aData['TopPrimaryCategory'] : '',
			'Price' => isset($aData['Price']) ? $aData['Price'] : null,
			'MsrpPrice' => isset($aData['MsrpPrice']) ? $aData['MsrpPrice'] : null,
			'MainImage' => isset($aData['MainImage']) ? $aData['MainImage'] : '',
			'Images' => isset($aData['Images']) ? $aData['Images'] : '',
			'SKU' => isset($aData['SKU']) ? $aData['SKU'] : '',
			'EAN' => isset($aData['EAN']) ? $aData['EAN'] : '',
			'ProcessingTime' => isset($aData['ProcessingTime']) ? (int)$aData['ProcessingTime'] : null,
			'ShippingType' => isset($aData['ShippingType']) ? $aData['ShippingType'] : 'PARCEL',
			'CostTemplateId' => isset($aData['CostTemplateId']) ? $aData['CostTemplateId'] : '',
			'ShipmentLimitDay' => isset($aData['ShipmentLimitDay']) ? (int)$aData['ShipmentLimitDay'] : null,
			'variation_theme' => isset($aData['variation_theme']) ? $aData['variation_theme'] : '',
			'VariationThemeBlacklist' => isset($aData['VariationThemeBlacklist']) ? $aData['VariationThemeBlacklist'] : '',
			'Verified' => isset($aData['Verified']) ? $aData['Verified'] : '',
			'PrepareError' => isset($aData['PrepareError']) ? $aData['PrepareError'] : '',
			'Transferred' => 0,
			'PreparedTS' => date('Y-m-d H:i:s'),
		);

		MagnaDB::gi()->delete(TABLE_MAGNA_TEMU_PREPARE, array(
			'mpID' => $mpID,
			'products_id' => (int)$aData['products_id'],
			'PrepareType' => $aInsert['PrepareType'],
		));

		MagnaDB::gi()->insert(TABLE_MAGNA_TEMU_PREPARE, $aInsert);

		if (isset($aData['ShopVariationId']) || isset($aData['CategoryIndependentShopVariationId'])) {
			MagnaDB::gi()->delete(TABLE_MAGNA_TEMU_PREPARE_LONGTEXT, array(
				'mpID' => $mpID,
				'products_id' => (int)$aData['products_id'],
			));
			MagnaDB::gi()->insert(TABLE_MAGNA_TEMU_PREPARE_LONGTEXT, array(
				'mpID' => $mpID,
				'products_id' => (int)$aData['products_id'],
				'ShopVariationId' => isset($aData['ShopVariationId']) ? $aData['ShopVariationId'] : '',
				'CategoryIndependentShopVariationId' => isset($aData['CategoryIndependentShopVariationId']) ? $aData['CategoryIndependentShopVariationId'] : '',
			));
		}

		return true;
	}

	public function saveSingleProductProperties() {
		if (!isset($_POST['temu_prepare']) || !is_array($_POST['temu_prepare'])) {
			return false;
		}
		$aData = $_POST['temu_prepare'];
		if (empty($aData['products_id'])) {
			return false;
		}

		if (empty($aData['Title'])) {
			$this->aMissingFields[] = 'Title';
			$this->aErrors[] = ML_TEMU_ERROR_NO_TITLE;
		}
		if (empty($aData['PrimaryCategory'])) {
			$this->aMissingFields[] = 'PrimaryCategory';
			$this->aErrors[] = ML_TEMU_ERROR_NO_CATEGORY;
		}

		if (!empty($this->aErrors)) {
			return false;
		}

		$aData['Verified'] = 'OK';
		$aData['PrepareType'] = 'Apply';
		return $this->insertPrepareData($aData);
	}

	public function saveMultipleProductProperties() {
		if (!isset($_POST['temu_prepare_batch']) || !is_array($_POST['temu_prepare_batch'])) {
			return false;
		}
		$bSuccess = true;
		foreach ($_POST['temu_prepare_batch'] as $pID => $aData) {
			$aData['products_id'] = $pID;
			$aData['PrepareType'] = 'Apply';
			$aData['Verified'] = 'OK';
			if (!$this->insertPrepareData($aData)) {
				$bSuccess = false;
			}
		}
		return $bSuccess;
	}

	/**
	 * Builds a prepare-row array auto-filled from shop product data and marketplace config.
	 * Verified is intentionally set to '' (unverified) — the caller is responsible for
	 * setting it to 'OK' when the row passes user review.
	 *
	 * API note: MLProduct::gi()->getProductById() returns capitalised keys (Title,
	 * Description, ProductsModel, EAN, Price, Images) — NOT the old lowercase DB column
	 * names. setLanguage() must be called before getProductById().
	 *
	 * @param int    $pID              Shop products_id
	 * @param string $sPrimaryCategory Temu category identifier
	 * @param array  $aOverrides       Per-product overrides for Images, MainImage, ShippingType, ProcessingTime
	 * @return array Row suitable for MagnaDB::gi()->insert(TABLE_MAGNA_TEMU_PREPARE, ...)
	 */
	protected function preparePropertiesRow($pID, $sPrimaryCategory, $aOverrides = array()) {
		$mpID = $this->aMagnaSession['mpID'];
		$iLang = getDBConfigValue(
			'temu.lang',
			$mpID,
			isset($_SESSION['magna']['selected_language']) ? $_SESSION['magna']['selected_language'] : 2
		);
		// Temu requires the NET price — apply the Temu price config (IncludeTax=false) so the
		// stored/prepared price is NET, consistent with the VerifyAddItems/AddItems payload.
		$aProduct = MLProduct::gi()
			->setLanguage($iLang)
			->setPriceConfig(TemuHelper::loadPriceSettings($mpID))
			->getProductById((int)$pID);
		if (!is_array($aProduct)) {
			$aProduct = array();
		}
		$aRow = array(
			'mpID'            => $mpID,
			'products_id'     => (int)$pID,
			'products_model'  => isset($aProduct['ProductsModel']) ? $aProduct['ProductsModel'] : '',
			'PrepareType'     => 'Apply',
			'Title'           => isset($aProduct['Title']) ? $aProduct['Title'] : '',
			'Description'     => isset($aProduct['Description']) ? $aProduct['Description'] : '',
			'SKU'             => isset($aProduct['ProductsModel']) ? $aProduct['ProductsModel'] : '',
			'EAN'             => isset($aProduct['EAN']) ? $aProduct['EAN'] : '',
			'Price'           => isset($aProduct['Price']) ? $aProduct['Price'] : null,
			'PrimaryCategory' => $sPrimaryCategory,
			'ShippingType'    => getDBConfigValue('temu.shippingtype', $mpID, 'PARCEL'),
			'ProcessingTime'  => (int)getDBConfigValue('temu.processingtime', $mpID, 0),
			'CostTemplateId'  => getDBConfigValue('temu.costtemplateid', $mpID, ''),
			'Verified'        => '',
			'Transferred'     => 0,
			'PreparedTS'      => date('Y-m-d H:i:s'),
		);

		// Per-product overrides from the prepare form (fall back to auto-fill/config above).
		if (isset($aOverrides['ShippingType']) && $aOverrides['ShippingType'] !== '') {
			$aRow['ShippingType'] = $aOverrides['ShippingType'];
		}
		if (isset($aOverrides['ProcessingTime']) && $aOverrides['ProcessingTime'] !== '') {
			$aRow['ProcessingTime'] = (int)$aOverrides['ProcessingTime'];
		}
		if (isset($aOverrides['Images']) && is_array($aOverrides['Images']) && !empty($aOverrides['Images'])) {
			$aRow['Images'] = json_encode(array_values($aOverrides['Images']));
		}
		if (isset($aOverrides['Title']) && $aOverrides['Title'] !== '') {
			$aRow['Title'] = $aOverrides['Title'];
		}
		if (isset($aOverrides['Description'])) {
			require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuProductDataView.php');
			$aRow['Description'] = TemuProductDataView::sanitizeDescription($aOverrides['Description']);
		}

		return $aRow;
	}

	/**
	 * Seeds the per-product longtext row for (mpID, products_id) from the category +
	 * category-independent templates in variantmatching, but ONLY when no row exists yet —
	 * an existing per-product row (e.g. saved from the prepare UI) is left untouched.
	 *
	 * @param int    $pID              Shop products_id
	 * @param string $sPrimaryCategory Temu category identifier (empty string = skip category lookup)
	 * @return void
	 */
	protected function resolveAttributesToLongtext($pID, $sPrimaryCategory) {
		$mpID = $this->aMagnaSession['mpID'];

		// If a per-product longtext row already exists (e.g. saved from the prepare
		// UI via the React AJAX save), keep it: do NOT overwrite per-product edits
		// with the category template. Only seed on first prepare / bulk prepare.
		$iExisting = (int)MagnaDB::gi()->fetchOne("
			SELECT COUNT(*) FROM ".TABLE_MAGNA_TEMU_PREPARE_LONGTEXT."
			 WHERE mpID = ".(int)$mpID." AND products_id = ".(int)$pID."
		");
		if ($iExisting > 0) {
			return;
		}

		$sShopVariation = ($sPrimaryCategory === '') ? '' : (string)MagnaDB::gi()->fetchOne("
			SELECT ShopVariation FROM ".TABLE_MAGNA_TEMU_VARIANTMATCHING."
			 WHERE mpID = ".(int)$mpID."
			       AND MpIdentifier = '".MagnaDB::gi()->escape($sPrimaryCategory)."'
			       AND CustomIdentifier = ''
		");
		$sCatIndep = (string)MagnaDB::gi()->fetchOne("
			SELECT ShopVariation FROM ".TABLE_MAGNA_TEMU_VARIANTMATCHING."
			 WHERE mpID = ".(int)$mpID."
			       AND MpIdentifier = 'category_independent_attributes'
			       AND CustomIdentifier = ''
		");
		if ($sShopVariation !== '' || $sCatIndep !== '') {
			MagnaDB::gi()->insert(TABLE_MAGNA_TEMU_PREPARE_LONGTEXT, array(
				'mpID'                               => $mpID,
				'products_id'                        => (int)$pID,
				'ShopVariationId'                    => $sShopVariation,
				'CategoryIndependentShopVariationId' => $sCatIndep,
			));
		}
	}

	/**
	 * Creates or replaces the prepare row for $pID with Verified='' and resolves
	 * attribute matching JSON into the longtext table.
	 * Called by the bulk-prepare action (Task 4) when a product has no existing row.
	 *
	 * @param int    $pID              Shop products_id
	 * @param string $sPrimaryCategory Temu category identifier
	 * @param array  $aOverrides       Per-product overrides for Images, MainImage, ShippingType, ProcessingTime
	 * @return bool Always true (DB errors surface via MagnaDB error state)
	 */
	public function preparePID($pID, $sPrimaryCategory, $aOverrides = array()) {
		$aRow = $this->preparePropertiesRow($pID, $sPrimaryCategory, $aOverrides);
		MagnaDB::gi()->delete(TABLE_MAGNA_TEMU_PREPARE, array(
			'mpID' => $aRow['mpID'], 'products_id' => (int)$pID, 'PrepareType' => 'Apply',
		));
		MagnaDB::gi()->insert(TABLE_MAGNA_TEMU_PREPARE, $aRow);
		$this->resolveAttributesToLongtext($pID, $sPrimaryCategory);
		return true;
	}
}
