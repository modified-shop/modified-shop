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

class MLProductListDependencyTemplateSelectionAction extends MLProductListDependency {
	
	public function getActionTopTemplate() {
		return 'templateselection';
	}
	
	public function executeAction() {
		if ($this->getProductList()->isAjax()) {
			$aRequest = explode('-', $this->getActionRequest());
			if (
				count($aRequest) == 2
				&& (int)$aRequest[0] != 0
				&& in_array($aRequest[1], array('true', 'false'))
			) {
				if ($aRequest[1] == 'true') {
					MagnaDB::gi()->insert(
						TABLE_MAGNA_SELECTION, 
						array(
							'pID' => $aRequest[0],
							'session_id' => $this->getConfig('session_id'),
							'mpID' => $this->getConfig('mpID'),
							'selectionname' => $this->getConfig('selectionname')
						),
						true
					);
				} else {
					MagnaDB::gi()->delete(
						TABLE_MAGNA_SELECTION, 
						array(
							'pID' => $aRequest[0],
							'session_id' => $this->getConfig('session_id'),
							'mpID' => $this->getConfig('mpID'),
							'selectionname' => $this->getConfig('selectionname')
						)
					);
				}
				echo sprintf(ML_LABEL_TO_SELECTION_SELECT, $this->getSelectedCount());
				exit();
			}
		} else {
			switch($this->getActionRequest()) {
				case 'add-page' : {
					foreach ($this->getQuery()->getResult() as $aRow) {
						MagnaDB::gi()->insert(
							TABLE_MAGNA_SELECTION, 
							array(
								'pID' => $aRow['products_id'],
								'session_id' => $this->getConfig('session_id'),
								'mpID' => $this->getConfig('mpID'),
								'selectionname' => $this->getConfig('selectionname')
							),
							true
						);
					}
					break;
				}
				case 'add-filtered' : {
					foreach ($this->getQuery()->getAll() as $aRow) {
						MagnaDB::gi()->insert(
							TABLE_MAGNA_SELECTION, 
							array(
								'pID' => $aRow['products_id'],
								'session_id' => $this->getConfig('session_id'),
								'mpID' => $this->getConfig('mpID'),
								'selectionname' => $this->getConfig('selectionname')
							),
							true
						);
					}
					break;
				}
				case 'sub-page' : {
					foreach ($this->getQuery()->getResult() as $aRow) {
						MagnaDB::gi()->delete(
							TABLE_MAGNA_SELECTION, 
							array(
								'pID' => $aRow['products_id'],
								'session_id' => $this->getConfig('session_id'),
								'mpID' => $this->getConfig('mpID'),
								'selectionname' => $this->getConfig('selectionname')
							)
						);
					}
					break;
				}
				case 'sub-all' : {
						MagnaDB::gi()->delete(
							TABLE_MAGNA_SELECTION, 
							array(
								'session_id' => $this->getConfig('session_id'),
								'mpID' => $this->getConfig('mpID'),
								'selectionname' => $this->getConfig('selectionname')
							)
						);
					break;
				}	
			}
			$this->getQuery()->reset();
		}
		return $this;
	}
	
	public function getSelectedCount() {
		$sSql = "
			SELECT count(*) from ".TABLE_MAGNA_SELECTION."
			WHERE `session_id` = '".$this->getConfig('session_id')."' 
			  AND `mpID` = '".$this->getConfig('mpID')."'
			  AND `selectionname` = '".$this->getConfig('selectionname')."'
		";
		return MagnaDB::gi()->fetchOne($sSql);
	}
		
	public function getTemplates() {
		$aTemplates = MagnaDB::gi()->fetchArray(
			"SELECT *
			   FROM ".TABLE_MAGNA_SELECTION_TEMPLATES."
			  WHERE mpID = '".$this->getMagnaSession('mpID')."'"
		);
		$aSelected = $this->getMagnaSession($this->getMagnaSession('mpID'));
		return array(
			'list' => $aTemplates,
			'selected' => (isset($aSelected['checkinTemplate']) ? $aSelected['checkinTemplate'] : '')
		);
	}
	
}
