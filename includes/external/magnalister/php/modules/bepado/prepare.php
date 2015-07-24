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
 * $Id: prepare.php 3830 2014-05-06 13:00:00Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


class BepadoPrepare extends MagnaCompatibleBase {

	protected $prepareSettings = array();

	public function __construct(&$params) {
		parent::__construct($params);

		$this->prepareSettings['selectionName'] = 'prepare';
		$this->resources['url']['mode'] = $this->prepareSettings['selectionName'];
	}

	protected function saveMatching() {
		if (!array_key_exists('savePrepareData', $_POST)) {
			return;
		}

		require_once(DIR_MAGNALISTER_MODULES . 'bepado/classes/BepadoProductSaver.php');
		require_once(DIR_MAGNALISTER_MODULES . 'bepado/checkin/BepadoCheckinSubmit.php');

		$itemDetails = $_POST;
		unset($itemDetails['savePrepareData']);

		$oProductSaver = new BepadoProductSaver($this->resources['session']);

		$aProductIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID="' . $this->mpID . '" AND
				   selectionname="' . $this->prepareSettings['selectionName'] . '" AND
				   session_id="' . session_id() . '"
		', true);

		if (1 == count($aProductIDs)) {
			$oProductSaver->saveSingleProductProperties($aProductIDs[0], $itemDetails);
		} else if (!empty($aProductIDs)) {
			$oProductSaver->saveMultipleProductProperties($aProductIDs, $itemDetails);
		}

		$oDCS = new BepadoCheckinSubmit(array(
			'selectionName' => $this->prepareSettings['selectionName'],
			'marketplace' => 'bepado',
		));

		$aVerified = $oDCS->verifyOneItem(false);
		#echo print_m($aVerified);
		if ('SUCCESS' == $aVerified['STATUS']) {
			//*
			MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
				'mpID' => $this->mpID,
				'selectionname' => $this->prepareSettings['selectionName'],
				'session_id' => session_id()
			));
			//*/
			//$_POST['prepare'] = 'prepare';
		} else if ('ERROR' == $aVerified['STATUS']) {
			# noch mal in der Maske bleiben
			$_POST['prepare'] = 'prepare';

			/* Letzte Exception holen */
			$oException = $oDCS->getLastException();
			if (is_object($oException) && ($aErrors = $oException->getErrorArray())) {
				$oException->setCriticalStatus(false);
				foreach ($aErrors['ERRORS'] as $aError) {
					echo '
						<div class="dawanda errorBox">
							<span class="error">' . sprintf(ML_MAGNACOMPAT_LABEL_ERROR, ML_BEPADO_LABEL, $aError['ERRORCODE']) . '</span>:
							'.fixHTMLUTF8Entities($aError['ERRORMESSAGE']).'
						</div>';
				}
			}
		}
		//echo print_m(json_indent($ecs->getLastRequest()));
	}

	protected function deleteMatching() {
		if (!(array_key_exists('unprepare', $_POST)) || empty($_POST['unprepare'])) {
			return;
		}
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = "'.$this->mpID.'" AND
				   selectionname = "'.$this->prepareSettings['selectionName'].'" AND
				   session_id = "'.session_id().'"
		', true);
		if (empty($pIDs)) {
			return;
		}

		foreach ($pIDs as $pID) {
			$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
				? array ('products_model' => MagnaDB::gi()->fetchOne('
							SELECT products_model
							  FROM '.TABLE_PRODUCTS.'
							 WHERE products_id='.$pID
					))
				: array ('products_id' => $pID);
			$where['mpID'] = $this->mpID;

			MagnaDB::gi()->delete(TABLE_MAGNA_BEPADO_PROPERTIES, $where);
			MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
				'pID' => $pID,
				'mpID' => $this->mpID,
				'selectionname' => $this->prepareSettings['selectionName'],
				'session_id' => session_id()
			));
		}
		unset($_POST['unprepare']);
	}

	protected function processMatching() {
		if (($class = $this->loadResource('prepare', 'PrepareView')) === false) {
			if ($this->isAjax) {
				echo '{"error": "'.__METHOD__.' This is not supported"}';
			} else {
				echo __METHOD__.' This is not supported';
			}
			return;
		}

		$params = array();
		foreach (array('mpID', 'marketplace', 'marketplaceName', 'resources') as $attr) {
			if (isset($this->$attr)) {
				$params[$attr] = &$this->$attr;
			}
		}

		$cMDiag = new $class($params);

		if ($this->isAjax) {
			echo $cMDiag->renderAjax();
		} else {
			$html = $cMDiag->process();
			echo $html;
		}
	}

	protected function processSelection() {
		if (($class = $this->loadResource('prepare', 'PrepareCategoryView')) === false) {
			if ($this->isAjax) {
				echo '{"error": "'.__METHOD__.' This is not supported"}';
			} else {
				echo __METHOD__.' This is not supported';
			}
			return;
		}
		$pV = new $class(
			null,
			$this->prepareSettings,
			isset($_GET['sorting'])   ? $_GET['sorting']   : false,
			isset($_POST['tfSearch']) ? $_POST['tfSearch'] : ''
		);
		if ($this->isAjax) {
			echo $pV->renderAjaxReply();
		} else {
			echo $pV->printForm();
		}
	}

	protected function getSelectedProductsCount() {
		return (int)MagnaDB::gi()->fetchOne('
			SELECT COUNT(*)
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = '.$this->mpID.'
			       AND selectionname = "'.$this->prepareSettings['selectionName'].'"
			       AND session_id = "'.session_id().'"
		');
	}

	protected function processProductList() {
		if (($sClass = $this->loadResource('prepare', 'PrepareProductList')) === false) {
			if ($this->isAjax) {
				echo '{"error": "This is not supported"}';
			} else {
				echo 'This is not supported';
			}
			return;
		}
		$o = new $sClass();
		echo $o;
	}

	public function process() {
		$this->saveMatching();
		
		if ((
				isset($_POST['prepare'])
				|| (
					isset($_GET['where'])
					&& in_array($_GET['where'], array('prepareView', 'catMatchView'))
				)
			)
			&& ($this->getSelectedProductsCount() > 0)
		) {
			$this->processMatching();
		} else {
			$this->processProductList();
		}
	}

}
