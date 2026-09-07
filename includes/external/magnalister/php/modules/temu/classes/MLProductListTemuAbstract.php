<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

abstract class MLProductListTemuAbstract extends MLProductList {

	protected $aPrepareData = array();

	protected function getPreparedStatusIndicator($aRow) {
		$sVerified = $this->getPrepareData($aRow, 'Verified');
		if (empty($sVerified)) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/grey_dot.png', ML_TEMU_PRODUCT_MATCHED_NO, 9, 9);
		} elseif ('OK' == $sVerified) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/green_dot.png', ML_TEMU_PRODUCT_PREPARED_OK, 9, 9);
		} elseif ('EMPTY' == $sVerified) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/white_dot.png', ML_TEMU_PRODUCT_PREPARED_OK, 9, 9);
		} else {
			return html_image(DIR_MAGNALISTER_WS_IMAGES.'status/red_dot.png', ML_TEMU_PRODUCT_PREPARED_FAULTY, 9, 9);
		}
	}

	protected function getPrepareData($aRow, $sFieldName = null) {
		if (!isset($this->aPrepareData[$aRow['products_id']])) {
			$sWhere = (getDBConfigValue('general.keytype', '0') == 'artNr')
				? "products_model='".MagnaDB::gi()->escape($aRow['products_model'])."'"
				: "products_id='".(int)$aRow['products_id']."'";
			$aApplyData = MagnaDB::gi()->fetchRow("
				SELECT *
				FROM ".TABLE_MAGNA_TEMU_PREPARE."
				WHERE ".$sWhere."
					AND mpID = '".$this->aMagnaSession['mpID']."'
					AND PrepareType='Apply'
			");
			$this->aPrepareData[$aRow['products_id']] = $aApplyData ? $aApplyData : array();
		}
		if ($sFieldName === null) {
			return $this->aPrepareData[$aRow['products_id']];
		}
		return isset($this->aPrepareData[$aRow['products_id']][$sFieldName])
			? $this->aPrepareData[$aRow['products_id']][$sFieldName]
			: null;
	}

	protected function getMarketPlaceCategory($aRow) {
		$aData = $this->getPrepareData($aRow);
		if (!empty($aData) && !empty($aData['PrimaryCategory'])) {
			$sCatName = MagnaDB::gi()->fetchOne("
				SELECT CategoryName
				FROM ".TABLE_MAGNA_TEMU_CATEGORIES."
				WHERE CategoryId = '".(int)$aData['PrimaryCategory']."'
					AND mpID = '".$this->aMagnaSession['mpID']."'
			");
			return $sCatName ? $sCatName : $aData['PrimaryCategory'];
		}
		return '&mdash;';
	}

	protected function isPreparedDifferently($aRow) {
		return false;
	}

	protected function isDeletedAttributeFromShop($aRow, &$message) {
		return false;
	}

	protected function getSelectionName() {
		return 'prepare';
	}
}
