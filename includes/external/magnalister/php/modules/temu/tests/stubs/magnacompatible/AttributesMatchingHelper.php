<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/**
 * Test stub — minimal base class so TemuHelper can be loaded standalone (CLI) for the
 * variation-spec regression tests. The real shop-value resolution is injected per-test via a
 * mock object passed to TemuHelper::resolveVariationDimsShopValues(); this stub only needs to
 * exist so `class TemuHelper extends AttributesMatchingHelper` loads.
 */
class AttributesMatchingHelper {
	protected $mpID = 0;
	public function __construct($mpID = 0) { $this->mpID = (int)$mpID; }
	public static function gi() { return new static(); }
}
