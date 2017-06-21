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
require_once DIR_MAGNALISTER_MODULES_AMAZON_ORDERLIST . '/Dependency/MLOrderlistDependency.php';
class MLOrderlistDependencySelectionAction extends MLOrderlistDependency {

	public function getActionTopTemplate() {
		return 'selection';
	}

	public function executeAction() {
		if ($this->getProductList()->isAjax()) {
			$aRequest = explode('_', $this->getActionRequest());
			if (
				count($aRequest) == 2
				&& $aRequest[0] != ''
				&& in_array($aRequest[1], array('true', 'false'))
			) {
				if ($aRequest[1] == 'true') {
					MagnaDB::gi()->insert(
						$this->getConfig('selectiontablename'),
						array(
							'element_id' => $aRequest[0],
							'session_id' => $this->getConfig('session_id'),
							'mpID' => $this->getConfig('mpID'),
							'selectionname' => $this->getConfig('selectionname')
						),
						true
					);
				} else {
					MagnaDB::gi()->delete(
						$this->getConfig('selectiontablename'),
						array(
							'element_id' => $aRequest[0],
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
							$this->getConfig('selectiontablename'),
							array(
								'element_id' => $aRow['AmazonOrderID'],
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
							$this->getConfig('selectiontablename'),
							array(
								'element_id' => $aRow['AmazonOrderID'],
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
							$this->getConfig('selectiontablename'),
							array(
								'element_id' => $aRow['AmazonOrderID'],
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
							$this->getConfig('selectiontablename'),
							array(
								'session_id' => $this->getConfig('session_id'),
								'mpID' => $this->getConfig('mpID'),
								'selectionname' => $this->getConfig('selectionname')
							)
						);
					break;
				}
			}
		}
		return $this;
	}

	public function getSelectedCount() {
		$sSql = "
			SELECT count(*) from ".$this->getConfig('selectiontablename')."
			WHERE `session_id` = '".$this->getConfig('session_id')."'
			  AND `mpID` = '".$this->getConfig('mpID')."'
			  AND `selectionname` = '".$this->getConfig('selectionname')."'
		";
		return MagnaDB::gi()->fetchOne($sSql);
	}

	protected function getDefaultConfig() {
		return array(
			'selectionname' => 'general',
			'mpID' => $this->getMagnaSession('mpID'),
			'session_id' => session_id(),
		);
	}

	public function getHeaderTemplate() {
		return 'selection';
	}

}
