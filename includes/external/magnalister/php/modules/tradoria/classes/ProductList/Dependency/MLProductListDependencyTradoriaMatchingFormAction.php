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
class MLProductListDependencyTradoriaMatchingFormAction extends MLProductListDependency {
	public function getActionBottomLeftTemplate(){
		return 'tradoriamatchingformleft';
	}
	
	public function getActionBottomRightTemplate(){
		return 'tradoriamatchingformright';
	}
	
	public function getHeaderTemplate() {
		return 'tradoriamatchingform';
	}


	public function getDefaultConfig() {
		return array(
			'selectionname' => 'general',
		);
	}
	
	public function executeAction() {
		$aRequest = $this->getActionRequest();
		if (isset($aRequest['unmatching'])) {
			$this->unmatching();
		}
		return $this;
	}
	
	protected function unmatching(){
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->getMagnaSession('mpID').'\' AND
				   selectionname=\''.$this->getConfig('selectionname').'\' AND
				   session_id=\''.session_id().'\'
		', true);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array ('products_model' => MagnaDB::gi()->fetchOne('
								SELECT products_model
								  FROM '.TABLE_PRODUCTS.'
								 WHERE products_id='.$pID
							))
					: array ('products_id'    => $pID);
				$where['mpID'] = $this->getMagnaSession('mpID');
				$where['PrepareType'] = 'Match';

				MagnaDB::gi()->delete(TABLE_MAGNA_TRADORIA_PREPARE, $where);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $this->getMagnaSession('mpID'),
					'selectionname' => $this->getConfig('selectionname'),
					'session_id' => session_id()
				));
			}
		}
	}
	
}
