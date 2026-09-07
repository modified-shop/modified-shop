<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MLProductListDependencyTemuApplyFormAction extends MLProductListDependency {

	public function getActionBottomLeftTemplate() {
		return 'temuapplyformleft';
	}

	public function getActionBottomRightTemplate() {
		return 'temuapplyformright';
	}

	public function getDefaultConfig() {
		return array(
			'selectionname' => 'general',
		);
	}

	public function executeAction() {
		$aRequest = $this->getActionRequest();
		if (isset($aRequest['removeapply'])) {
			$this->removeApply();
		} elseif (isset($aRequest['resetapply'])) {
			$this->resetApply();
		}
		return $this;
	}

	protected function removeApply() {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID
			FROM '.TABLE_MAGNA_SELECTION.'
			WHERE mpID = \''.$this->getMagnaSession('mpID').'\'
				AND selectionname = \''.$this->getConfig('selectionname').'\'
				AND session_id = \''.session_id().'\'
		', true);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				$sKeyType = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id';
				if ($sKeyType == 'products_model') {
					$sModel = MagnaDB::gi()->fetchOne("SELECT products_model FROM ".TABLE_PRODUCTS." WHERE products_id = '".(int)$pID."'");
					$sWhere = "products_model = '".MagnaDB::gi()->escape($sModel)."'";
				} else {
					$sWhere = "products_id = '".(int)$pID."'";
				}
				MagnaDB::gi()->query("
					DELETE FROM ".TABLE_MAGNA_TEMU_PREPARE."
					WHERE mpID = '".$this->getMagnaSession('mpID')."'
						AND ".$sWhere."
						AND PrepareType = 'Apply'
				");
				MagnaDB::gi()->query("
					DELETE FROM ".TABLE_MAGNA_TEMU_PREPARE_LONGTEXT."
					WHERE mpID = '".$this->getMagnaSession('mpID')."'
						AND products_id = '".(int)$pID."'
				");
			}
		}
	}

	protected function resetApply() {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID
			FROM '.TABLE_MAGNA_SELECTION.'
			WHERE mpID = \''.$this->getMagnaSession('mpID').'\'
				AND selectionname = \''.$this->getConfig('selectionname').'\'
				AND session_id = \''.session_id().'\'
		', true);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				MagnaDB::gi()->update(TABLE_MAGNA_TEMU_PREPARE, array(
					'Transferred' => 0,
				), array(
					'mpID' => $this->getMagnaSession('mpID'),
					'products_id' => (int)$pID,
					'PrepareType' => 'Apply',
				));
			}
		}
	}
}
