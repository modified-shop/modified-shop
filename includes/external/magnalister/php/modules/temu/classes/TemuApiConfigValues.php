<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/classes/MagnaCompatibleApiConfigValues.php');

class TemuApiConfigValues extends MagnaCompatibleApiConfigValues {

	protected static $instance = null;

	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function getShippingTypes() {
		return TemuHelper::GetShippingTypesConfig();
	}

	public function getCategoryAttributes($categoryId) {
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'GetCategoryDetails',
				'SUBSYSTEM' => 'Temu',
				'MARKETPLACEID' => $this->mpID,
				'DATA' => array('CategoryID' => $categoryId),
			), 30 * 60);
			return isset($result['DATA']) ? $result['DATA'] : array();
		} catch (MagnaException $e) {
			return array();
		}
	}
}
