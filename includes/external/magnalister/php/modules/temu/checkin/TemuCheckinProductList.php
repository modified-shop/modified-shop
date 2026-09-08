<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/classes/MLProductListMagnaCompatibleAbstract.php');

class TemuCheckinProductList extends MLProductListMagnaCompatibleAbstract {

	public function __construct() {
		// Shop Stock | Stock for Temu | Shop Price | Price for Temu
		$aShopPriceColumn = array_pop($this->aListConfig);
		$this->aListConfig[] = array(
			'head'  => array('attributes' => 'class="lowestprice"', 'content' => 'ML_LABEL_SHOP_QUANTITY'),
			'field' => array('shopquantity'),
		);
		$this->aListConfig[] = array(
			'head'  => array('attributes' => 'class="lowestprice"', 'content' => 'ML_TEMU_STOCK_FOR_TEMU'),
			'field' => array('quantityfortemu'),
		);
		$this->aListConfig[] = $aShopPriceColumn;
		$this->aListConfig[] = array(
			'head'  => array('attributes' => 'class="lowestprice"', 'content' => 'ML_TEMU_PRICE_FOR_TEMU'),
			'field' => array('pricefortemu'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyCheckinToSummaryAction')
			->addDependency('MLProductListDependencyTemplateSelectionAction')
			->addDependency('MLProductListDependencyLastPreparedFilter', array(
				'propertiestablename'    => TABLE_MAGNA_TEMU_PREPARE,
				'propertiestablealias'   => 'tp',
				'preparedtimestampfield' => 'PreparedTS',
			))
		;
	}

	protected function getSelectionName() {
		return 'checkin';
	}

	protected function buildQuery() {
		parent::buildQuery()->oQuery->join(
			array(
				TABLE_MAGNA_TEMU_PREPARE,
				'tp',
				((getDBConfigValue('general.keytype', '0') == 'artNr')
					? 'p.products_model=tp.products_model'
					: 'p.products_id=tp.products_id')
				." AND tp.mpID = '".$this->aMagnaSession['mpID']."'"
				." AND tp.PrepareType = 'Apply'"
				." AND tp.Verified = 'OK'"
			),
			ML_Database_Model_Query_Select::JOIN_TYPE_INNER
		);
		return $this;
	}

	protected function getPrepareData($aRow, $sFieldName = null) {
		static $cache = array();
		if (!isset($cache[$aRow['products_id']])) {
			$cache[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT * FROM ".TABLE_MAGNA_TEMU_PREPARE."
				WHERE mpID = '".$this->aMagnaSession['mpID']."'
					AND products_id = '".(int)$aRow['products_id']."'
					AND PrepareType = 'Apply'
			");
		}
		if ($sFieldName === null) {
			return $cache[$aRow['products_id']];
		}
		return isset($cache[$aRow['products_id']][$sFieldName])
			? $cache[$aRow['products_id']][$sFieldName]
			: null;
	}

	protected function getMarketPlaceCategory($aRow) {
		$aCat = $this->getPrepareData($aRow);
		if (!empty($aCat['PrimaryCategory'])) {
			$sCatName = MagnaDB::gi()->fetchOne("
				SELECT CategoryName FROM ".TABLE_MAGNA_TEMU_CATEGORIES."
				WHERE CategoryId = '".(int)$aCat['PrimaryCategory']."'
					AND mpID = '".$this->aMagnaSession['mpID']."'
			");
			return $sCatName ? $sCatName : $aCat['PrimaryCategory'];
		}
		return '&mdash;';
	}

	protected function isPreparedDifferently($aRow) {
		return false;
	}

	protected function isDeletedAttributeFromShop($aRow, &$message) {
		return false;
	}

	protected function getQuantity($aRow) {
		return (int)$aRow['products_quantity'];
	}

	protected function getQuantityForTemu($aRow) {
		return TemuHelper::getQuantityForTemu($aRow['products_quantity'], $this->aMagnaSession['mpID']);
	}

	protected function getTemuPrice() {
		return $this->getPrice()->setPrice($this->getPrice()->getPrice())->format();
	}
}
