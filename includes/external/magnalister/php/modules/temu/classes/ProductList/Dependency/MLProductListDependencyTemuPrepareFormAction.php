<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MLProductListDependencyTemuPrepareFormAction extends MLProductListDependency {

	public function getActionBottomLeftTemplate() {
		return 'temuprepareformleft';
	}

	public function getActionBottomRightTemplate() {
		return 'temuprepareformright';
	}

	public function getDefaultConfig() {
		return array(
			'selectionname' => 'general',
		);
	}

	public function executeAction() {
		$aRequest = $this->getActionRequest();
		if (isset($aRequest['unprepare'])) {
			$this->unprepare();
		}
		return $this;
	}

	protected function unprepare() {
		$mpID = (int)$this->getMagnaSession('mpID');
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID
			FROM '.TABLE_MAGNA_SELECTION.'
			WHERE mpID = \''.$mpID.'\'
				AND selectionname = \''.MagnaDB::gi()->escape($this->getConfig('selectionname')).'\'
				AND session_id = \''.MagnaDB::gi()->escape(session_id()).'\'
		', true);
		if (empty($pIDs)) {
			return $this;
		}
		foreach ($pIDs as $pID) {
			$pID = (int)$pID;
			$sWhere = "products_id = '".$pID."'";
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$sModel = MagnaDB::gi()->fetchOne("SELECT products_model FROM ".TABLE_PRODUCTS." WHERE products_id = '".$pID."'");
				/* Never fall through to products_model = '': that would match every
				 * prepared product without an article number, not just this one. */
				if (!empty($sModel)) {
					$sWhere = "products_model = '".MagnaDB::gi()->escape($sModel)."'";
				}
			}
			MagnaDB::gi()->query("
				DELETE FROM ".TABLE_MAGNA_TEMU_PREPARE."
				WHERE mpID = '".$mpID."'
					AND ".$sWhere."
					AND PrepareType = 'Apply'
			");
			/* The longtext table is always keyed by products_id, never by products_model. */
			MagnaDB::gi()->query("
				DELETE FROM ".TABLE_MAGNA_TEMU_PREPARE_LONGTEXT."
				WHERE mpID = '".$mpID."'
					AND products_id = '".$pID."'
			");
			MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
				'pID'           => $pID,
				'mpID'          => $mpID,
				'selectionname' => $this->getConfig('selectionname'),
				'session_id'    => session_id(),
			));
		}
		return $this;
	}
}
