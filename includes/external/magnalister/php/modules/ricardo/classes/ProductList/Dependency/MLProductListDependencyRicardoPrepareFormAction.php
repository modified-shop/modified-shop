<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependency.php';

class MLProductListDependencyRicardoPrepareFormAction extends MLProductListDependency {
	public function getActionBottomLeftTemplate() {
		return 'ricardoprepareformleft';
	}
	
	public function getActionBottomRightTemplate() {
		return 'ricardoprepareformright';
	}
	
	public function getActionBottomCenterTemplate() {
		return '';
	}
	
	public function getDefaultConfig() {
		return array(
			'selectionname' => 'general'
		);
	}
	
	protected function unprepare() {
		$aPIDs = MagnaDB::gi()->fetchArray("
				SELECT pID
				  FROM ".TABLE_MAGNA_SELECTION."
				 WHERE mpID = '".$this->getMagnaSession('mpID')."'
					   AND selectionname = '".$this->getConfig('selectionname')."'
					   AND session_id = '".session_id()."'
		", true);
		if (!empty($aPIDs)) {
			foreach ($aPIDs as $sPID) {
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array ('products_model' => MagnaDB::gi()->fetchOne('
						SELECT products_model
						  FROM '.TABLE_PRODUCTS.'
						 WHERE products_id = '.$sPID
					))
					: array ('products_id' => $sPID)
				;
				$where['mpID'] = $this->getMagnaSession('mpID');
				MagnaDB::gi()->delete(TABLE_MAGNA_RICARDO_PROPERTIES, $where);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $sPID,
					'mpID' => $this->getMagnaSession('mpID'),
					'selectionname' => $this->getConfig('selectionname'),
					'session_id' => session_id()
				));
			}
		}
		return $this;
	}

	protected function resetDescription() {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			WHERE
				mpID=\''.$this->getMagnaSession('mpID').'\' AND
				selectionname=\''.$this->getConfig('selectionname').'\' AND
				session_id=\''.session_id().'\'
			',
			true
		);
		if (!empty($pIDs)) {
			require_once(DIR_MAGNALISTER_MODULES . 'ricardo/classes/RicardoProductSaver.php');
			$oRicardoSaver = new RicardoProductSaver($this->aMagnaSession);
			foreach ($pIDs as $pID) {
				$oRicardoSaver->resetProductProperties($pID);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $this->getMagnaSession('mpID'),
					'selectionname' => $this->getConfig('selectionname'),
					'session_id' => session_id()
				));
			}
		}
		return $this;
	}

	public function executeAction() {
		$aRequest = $this->getActionRequest();
		if (isset($aRequest['unprepare'])) {
			$this->unprepare();
		} elseif (isset($aRequest['resetDescription'])) {
			$this->resetDescription();
		}

		return $this;
	}
}
