<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');

class TemuCheckinSubmit extends MagnaCompatibleCheckinSubmit {

	protected $ignoreErrors = true;
	protected $aRegionalUnits = array();

	public function __construct($settings = array()) {
		parent::__construct($settings);
		global $_MagnaSession;
		$this->aRegionalUnits = TemuMarketplace::getRegionalUnits($_MagnaSession['mpID']);
	}

	public function init($mode, $items = -1) {
		parent::init($mode, $items);
	}

	protected function setUpMLProduct() {
		parent::setUpMLProduct();
		MLProduct::gi()->setPriceConfig(TemuHelper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(TemuHelper::loadQuantitySettings($this->mpID));
		// FEAT-014: load variants so the checkin can emit one payload item per variant.
		// Simple products return no Variations (unaffected); products without variation-dim
		// matching fall back to a single item inside TemuHelper::buildSubmitItems().
		MLProduct::gi()->useMultiDimensionalVariations(true);
		MLProduct::gi()->setOptions(array(
			'includeVariations'          => true,
			'sameVariationsToAttributes' => false,
			'purgeVariations'            => true,
			// Gambio properties store variations in products_properties_combis. Without this flag
			// MLProduct takes the multi-dim path (fetchMultiVariations), finds nothing for a
			// properties-based product, and the upload collapses to a single master item (all
			// variants missing). Mirror OttoCheckinSubmit::setUpMLProduct().
			'useGambioProperties'        => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties'),
		));
	}

	protected function appendAdditionalData($pID, $product, &$data) {
		global $_MagnaSession;
		$mpID = $_MagnaSession['mpID'];

		$aPrepare = MagnaDB::gi()->fetchRow("
			SELECT * FROM ".TABLE_MAGNA_TEMU_PREPARE."
			 WHERE mpID = '".$mpID."' AND products_id = '".(int)$pID."' AND PrepareType = 'Apply'
		");
		if (empty($aPrepare)) {
			return;
		}
		$aBase = array(
			'price'    => isset($data['price']) ? $data['price'] : null,
			'quantity' => isset($data['quantity']) ? $data['quantity'] : null,
			'currency' => isset($data['currency']) ? $data['currency'] : null,
		);

		// Load the MLProduct variants (config already set in setUpMLProduct); build one payload
		// item per variant (or a single item for simple / non-variation-prepared products).
		$aMLProduct = MLProduct::gi()->getProductById((int)$pID);
		$aVariants  = (is_array($aMLProduct) && !empty($aMLProduct['Variations'])) ? $aMLProduct['Variations'] : null;

		$aItems = TemuHelper::buildSubmitItems($mpID, $pID, $aPrepare, $aBase, $aVariants);

		// One item → assoc payload (as before). Many → numeric list; preSubmit() flattens the
		// per-product nesting that CheckinSubmit::sendRequest introduces (DATA[] = $data['submit']).
		$data['submit'] = (count($aItems) === 1) ? $aItems[0] : $aItems;
	}

	/**
	 * FEAT-014: CheckinSubmit::sendRequest() pushes each product's $data['submit'] into
	 * DATA[]. For variant products $data['submit'] is a LIST of items, so DATA ends up
	 * with a nested level. Flatten so DATA is a flat array of item payloads (works for
	 * any mix of simple items and per-variant lists across selected products).
	 */
	protected function preSubmit(&$request) {
		parent::preSubmit($request);
		if (empty($request['DATA']) || !is_array($request['DATA'])) {
			return;
		}
		$aFlat = array();
		foreach ($request['DATA'] as $mEntry) {
			if (!is_array($mEntry)) {
				continue;
			}
			if (isset($mEntry['SKU'])) {
				$aFlat[] = $mEntry;            // single item
			} else {
				foreach ($mEntry as $mItem) { // nested list of variant items
					if (is_array($mItem)) {
						$aFlat[] = $mItem;
					}
				}
			}
		}
		$request['DATA'] = $aFlat;
	}

	// NOTE: no afterSendRequest() override is needed for the "X of Y items submitted" counter.
	// CheckinSubmit::sendRequest() raises it by count($this->selection) — i.e. per PRODUCT, not
	// per payload item — so emitting N variant items for one product cannot inflate it.

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp'   => $this->realUrl['mp'],
			'mode' => ($state == 'fail') ? 'errorlog' : 'listings',
		), true);
	}

	protected function postSubmit() {
		parent::postSubmit();
		foreach ($this->selection as $pID => $data) {
			MagnaDB::gi()->update(TABLE_MAGNA_TEMU_PREPARE, array(
				'Transferred' => 1,
			), array(
				'mpID' => $this->mpID,
				'products_id' => (int)$pID,
				'PrepareType' => 'Apply',
			));
		}
	}
}
