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


class DawandaPrepare extends MagnaCompatibleBase {

	protected $prepareSettings = array();

	public function __construct(&$params) {
		if (!empty($_POST['FullSerializedForm'])) {
			$newPost = array();
			parse_str_unlimited($_POST['FullSerializedForm'], $newPost);

			$_POST = array_merge($_POST, $newPost);
		}

		parent::__construct($params);

		$this->prepareSettings['selectionName'] = isset($_GET['view']) ? $_GET['view'] : 'apply';
		$this->resources['url']['mode'] = 'prepare';
		$this->resources['url']['view'] = $this->prepareSettings['selectionName'];
	}

	protected function saveMatching() {
		if (!array_key_exists('saveMatching', $_POST)) {
			if (!isset($_POST['Action']) || $_POST['Action'] !== 'SaveMatching' || $_GET['where'] === 'varmatchView') {
				return;
			}
		}

		require_once(DIR_MAGNALISTER_MODULES . 'dawanda/classes/DawandaProductSaver.php');

		$oDaWandaProductSaver = new DawandaProductSaver($this->resources['session']);

		$aProductIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID="' . $this->mpID . '" AND
				   selectionname="' . $this->prepareSettings['selectionName'] . '" AND
				   session_id="' . session_id() . '"
		', true);

		$isSinglePrepare = 1 == count($aProductIDs);
		$shopVariations = $this->saveMatchingAttributes($oDaWandaProductSaver, $isSinglePrepare);
		$itemDetails = $_POST;
		unset($itemDetails['savePrepareData']);
		$itemDetails['CategoryAttributes'] = $shopVariations;

		if (1 == count($aProductIDs)) {
			$oDaWandaProductSaver->saveSingleProductProperties($aProductIDs[0], $itemDetails);
		} else if (!empty($aProductIDs)) {
			$oDaWandaProductSaver->saveMultipleProductProperties($aProductIDs, $itemDetails);
		}

		$saveMatching = array_key_exists('saveMatching', $_POST);

		if (count($oDaWandaProductSaver->aErrors) === 0 || !$saveMatching) {
			$isAjax = false;
			if (!$saveMatching) {
				# stay on prepare product form
				$_POST['prepare'] = 'prepare';
				$isAjax = true;
			}

			$matchingNotFinished = isset($_POST['matching_nextpage']) && ctype_digit($_POST['matching_nextpage']) || $isAjax;
			if ($matchingNotFinished === false) {
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'mpID' => $this->mpID,
					'selectionname' => $this->prepareSettings['selectionName'],
					'session_id' => session_id()
				));
			}
		} else {
			# stay on prepare product form
			$_POST['prepare'] = 'prepare';

			if ($saveMatching) {
				foreach ($oDaWandaProductSaver->aErrors as $sError) {
					echo '<div class="errorBox">' . $sError . '</div>';
				}
			}
		}
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

			MagnaDB::gi()->delete(TABLE_MAGNA_DAWANDA_PROPERTIES, $where);
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
		if ($this->prepareSettings['selectionName'] === 'varmatch') {
			$className = 'VariationMatching';
		} else {
			$className = 'PrepareView';
		}
		
		if (($class = $this->loadResource('prepare', $className)) === false) {
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
		if ($this->prepareSettings['selectionName'] === 'varmatch') {
			$this->processMatching();
			return;
		}
		
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
					&& in_array($_GET['where'], array('prepareView', 'catMatchView', 'varmatchView'))
				)
			)
			&& ($this->getSelectedProductsCount() > 0)
		) {
			$this->processMatching();
		} else {
			if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {
				$this->processProductList();
			} else {
				$this->deleteMatching();
				$this->processSelection();
			}
		}
	}

	protected function saveMatchingAttributes($oProductSaver, $isSinglePrepare) {
		if (isset($_POST['Variations'])) {
			parse_str_unlimited($_POST['Variations'], $params);
			$_POST = $params;
			if (isset($_POST['saveMatching'])) {
				unset($_POST['saveMatching']);
			}
		}

		$sIdentifier = $_POST['PrimaryCategory'];
		$matching = isset($_POST['ml']['match']) ? $_POST['ml']['match'] : false;
		$savePrepare = isset($_POST['saveMatching']) ? $_POST['saveMatching'] : false;

		if (!is_array($matching)) {
            $match = 'false';
        } else {
		    $match = reset($matching);
        }
		if ($match != 'false') {
			$oProductSaver->aErrors = array_merge($oProductSaver->aErrors,
				DawandaHelper::gi()->saveMatching($sIdentifier, $matching, $savePrepare, true, $isSinglePrepare));
		}

		return $matching ? json_encode($matching['ShopVariation']) : false;
	}

}
