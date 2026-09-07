<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleSummaryView.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/TemuHelper.php');

class TemuSummaryView extends MagnaCompatibleSummaryView {

	public function __construct($settings = array()) {
		parent::__construct($settings);
	}

	/**
	 * Override to skip GetInventoryBySKUs API call.
	 * Temu does not support this API action (same as V3 which disables MarketplaceSyncFilter).
	 * All product data comes from local database tables.
	 */
	protected function additionalInitialisation() {
		// Intentionally empty — do not call parent::additionalInitialisation()
		// which calls GetInventoryBySKUs and causes a fatal MagnaException for Temu.
	}

	protected function getAdditionalHeadlines() {
		return str_replace(
			array(ML_MAGNACOMPAT_LABEL_MP_PRICE_SHORT, ML_LABEL_BRUTTO),
			array(ML_TEMU_PRICE_FOR_TEMU, ML_LABEL_NETTO),
			parent::getAdditionalHeadlines());
	}

	protected function extendProductAttributes($pID, &$data) {
		global $_MagnaSession;
		// Temu requires the NET price. The base computes $data['price'] GROSS
		// (addTaxByPID) and reads the markup from temu.price.factor instead of
		// temu.price.addvalue. This price is serialized into the selection and later
		// wins over the prepare row in TemuHelper::buildSubmitData() ($aBase['price']),
		// so it must be NET here — the submit-side price config never touches it.
		if (!isset($data['price']) || ($data['price'] === null)) {
			$data['price'] = $this->simplePrice->setFinalPriceFromDB(
				$pID, $_MagnaSession['mpID'], TemuHelper::loadPriceSettings($_MagnaSession['mpID'])
			)->getPrice();
		}
		parent::extendProductAttributes($pID, $data);
		// maxquantity cap ONLY — mirror OttoSummaryView. The base already applies
		// temu.quantity.type/value + price config; do NOT re-apply via getQuantityForTemu.
		$iMaxQuantity = getDBConfigValue('temu.maxquantity', $_MagnaSession['mpID'], 0);
		if (($iMaxQuantity > 0) && ($iMaxQuantity < $data['quantity'])) {
			$data['quantity'] = $iMaxQuantity;
		}
	}
}
