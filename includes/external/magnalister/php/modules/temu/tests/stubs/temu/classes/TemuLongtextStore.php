<?php
/**
 * Test stub for TemuLongtextStore.
 *
 * TemuHelper requires the real class, which talks to the database. The tests in this
 * directory exercise the pure value logic — resolveVariationDimsShopValues(),
 * buildSpecDetails() and the per-variant resolver — and never read or write a product, so
 * the store only has to exist and be loadable. Every method here answers "nothing stored".
 *
 * If a test ever needs real store behaviour, replace this with a fixture that keeps the
 * values in a static array rather than pointing it at a database.
 */
class TemuLongtextStore {

	const FIELD_SHOP_VARIATION = 'ShopVariation';
	const FIELD_CATEGORY_INDEPENDENT = 'CategoryIndependentShopVariation';
	const PREPARE_TYPE = 'Apply';

	/** @var array field => stored value, set by a test that needs one (empty = nothing stored) */
	public static $aStored = array();

	public static function read($mpID, $pID, $sField) {
		return isset(self::$aStored[$sField]) ? self::$aStored[$sField] : '';
	}

	public static function readRow($mpID, $pID) {
		return array(
			'ShopVariationId'                    => '',
			'CategoryIndependentShopVariationId' => '',
		);
	}

	public static function write($mpID, $pID, $sField, $sValue) {
	}

	public static function clear($mpID, $pID, $sField) {
	}

	public static function remove($mpID, $pID) {
	}

	public static function carryReferences($mpID, $pID, $sPrepareType, $aRow) {
		return $aRow;
	}
}
