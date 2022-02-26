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
class MLProductListDependencyEbayPrepareFormAction extends MLProductListDependency {
	public function getActionBottomLeftTemplate(){
		return 'ebayprepareformleft';
	}
	
	public function getActionBottomRightTemplate(){
		return 'ebayprepareformright';
	}
	
	public function getActionBottomCenterTemplate(){
		return '';
	}
	
	public function getDefaultConfig() {
		return array(
			'selectionname' => 'general'
		);
	}
	
	protected function unprepare(){
		$pIDs = MagnaDB::gi()->fetchArray(
			"
				SELECT pID FROM ".TABLE_MAGNA_SELECTION."
				WHERE 
					mpID='".$this->getMagnaSession('mpID')."' AND
					selectionname='".$this->getConfig('selectionname')."' AND
					session_id='".session_id()."'
			"
			, true
		);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array ('products_model' => MagnaDB::gi()->fetchOne('
						SELECT products_model
						FROM '.TABLE_PRODUCTS.'
						WHERE products_id='.$pID
					))
					: array ('products_id'    => $pID)
				;
				$where['mpID'] = $this->getMagnaSession('mpID');
				MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_PROPERTIES, $where);

				if (isset($where['products_model'])) {
					$where['products_sku'] = $where['products_model'];
					unset($where['products_model']);
				}
				MagnaDB::gi()->delete('magnalister_ebay_variations_epids', $where);
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

//	protected function resetDescription() { 
//		return $this->resetPartly(array('Description', 'PictureURL'));
//	}

	protected function resetPartly($aParts) {
		if (empty($aParts)) {
			return $this;
		} else {
			$aUpdateCols = array();
			foreach ($aParts as $sPart) {
				$aUpdateCols[$sPart] = '';
				if ('PictureURL' == $sPart) {
					$aUpdateCols[$sPart] = NULL;
					$aUpdateCols['eBayPicturePackPurge'] = '1';
				} else if ('StrikePriceConf' == $sPart) {
					$aUpdateCols[$sPart] = '{"ebay.strike.price.kind":"DontUse","ebay.strike.price.addkind":"percent","ebay.strike.price.factor":"0","ebay.strike.price.group":-1,"ebay.strike.price.kind.isUVP":"{\"val\":false}","ebay.strike.price.signal":""}';
				}
			}
		}
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
			foreach ($pIDs as $pID) {
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array ('products_model' => MagnaDB::gi()->fetchOne('
						SELECT products_model
						FROM '.TABLE_PRODUCTS.'
						WHERE products_id='.$pID
					))
					: array ('products_id' => $pID)
				;
				$where['mpID'] = $this->getMagnaSession('mpID');
				MagnaDB::gi()->update(TABLE_MAGNA_EBAY_PROPERTIES, $aUpdateCols, $where);
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
		$aPartsToReset = array();
		if (isset($aRequest['unprepare'])) {
			$this->unprepare();
		} else {
			if (isset($aRequest['resetTitle'])) {
				$aPartsToReset[] = 'Title';
			}
			if (isset($aRequest['resetSubtitle'])) {
				$aPartsToReset[] = 'Subtitle';
			}
			if (isset($aRequest['resetDescription'])) {
				$aPartsToReset[] = 'Description';
			}
			if (isset($aRequest['resetPictures'])) {
				$aPartsToReset[] = 'PictureURL';
			}
			if (isset($aRequest['resetStrikePrices'])) {
				$aPartsToReset[] = 'StrikePriceConf';
			}
			if (!empty($aPartsToReset)) {
				$this->resetPartly($aPartsToReset);
			}
		}
		return $this;
	}
}
