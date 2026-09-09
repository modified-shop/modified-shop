<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuVariationMatching.php');

/**
 * Product Preparation screen: the attribute-matching form PLUS per-product product-data
 * fieldsets on top, and a "Save and Close" that creates+verifies prepare rows.
 * Reached via the "Prepare Selected" flow (prepare.php routes $_POST['prepare'] here).
 */
class TemuPrepareForm extends TemuVariationMatching {

	protected function showProductFieldsets() {
		return true;
	}

	protected function triggersPrepare() {
		return true;
	}
}
