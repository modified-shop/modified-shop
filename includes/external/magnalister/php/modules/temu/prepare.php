<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/MagnaCompatibleBase.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MLProductList.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependency.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependencyPrepareStatusFilter.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/classes/TemuProductSaver.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/classes/MLProductListTemuAbstract.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/classes/ProductList/Dependency/MLProductListDependencyTemuApplyFormAction.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/classes/ProductList/Dependency/MLProductListDependencyTemuPrepareFormAction.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/classes/ProductList/Dependency/MLProductListDependencyTemuPrepareStatusFilter.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuApplyProductList.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuPrepareProductList.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuVariationMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuPrepareForm.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'temu/classes/TemuTopTenCategories.php');

class TemuPrepare extends MagnaCompatibleBase {

	public function __construct(&$params) {
		if (isset($_POST['FullSerializedForm'])) {
			$aPostData = array();
			parse_str($_POST['FullSerializedForm'], $aPostData);
			$_POST = array_merge($_POST, $aPostData);
		}
		parent::__construct($params);

        $this->prepareSettings['selectionName'] = isset($_GET['view']) ? $_GET['view'] : 'prepare';
        $this->resources['url']['mode'] = 'prepare';
        $this->resources['url']['view'] = $this->prepareSettings['selectionName'];
        if ('apply' == $this->prepareSettings['selectionName']) $this->prepareSettings['selectionName'] = 'prepare';
	}

	/**
	 * Handle the "Save and Close" submit from the Product Preparation screen.
	 *
	 * @return null|bool null if this is not a prepare submit, true if at least one
	 *                   product failed verification (stay on the prepare form so the
	 *                   user can fix it), false if everything was prepared successfully
	 *                   (close back to the product list).
	 */
	protected function savePrepare() {
		if (!isset($_POST['temu_apply_prepare'])) {
			return null;
		}
		require_once(DIR_MAGNALISTER_MODULES.'temu/classes/TemuVerifier.php');
		global $_MagnaSession;
		$mpID = $_MagnaSession['mpID'];
		$sPrimaryCategory = isset($_POST['PrimaryCategory']) ? $_POST['PrimaryCategory'] : '';
		if ($sPrimaryCategory === '' || $sPrimaryCategory === 'none') {
			echo '<p class="errorBox">'.ML_TEMU_ERROR_NO_CATEGORY.'</p>';
			return true;
		}
		$aPIDs = MagnaDB::gi()->fetchArray("
			SELECT pID FROM ".TABLE_MAGNA_SELECTION."
			 WHERE mpID = ".(int)$mpID." AND selectionname = 'prepare' AND session_id = '".session_id()."'
		", true);
		if (empty($aPIDs)) {
			return false;
		}
		$oSaver = new TemuProductSaver($_MagnaSession);
		$aPreparePost = (isset($_POST['temu_prepare']) && is_array($_POST['temu_prepare'])) ? $_POST['temu_prepare'] : array();
		$bHasErrors = false;
		foreach ($aPIDs as $pID) {
			$aOverrides = (isset($aPreparePost[$pID]) && is_array($aPreparePost[$pID])) ? $aPreparePost[$pID] : array();
			$oSaver->preparePID($pID, $sPrimaryCategory, $aOverrides);
			$aRes = TemuVerifier::verifyPID($mpID, $pID);
			if ($aRes['ok']) {
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => (int)$pID, 'mpID' => $mpID,
					'selectionname' => 'prepare', 'session_id' => session_id(),
				));
			} else {
				$bHasErrors = true;
				foreach ($aRes['errors'] as $sErr) {
					echo '<p class="errorBox">'.htmlspecialchars($sErr).'</p>';
				}
			}
		}
		return $bHasErrors;
	}

	protected function saveMatching() {
		$oProductSaver = new TemuProductSaver($this->aMagnaSession);
		$oProductSaver->saveSingleProductProperties();
		$aErrors = $oProductSaver->getErrors();
		if (!empty($aErrors)) {
			foreach ($aErrors as $sError) {
				$this->boxes .= '<p class="errorBox">'.$sError.'</p>';
			}
		}
	}

	protected function deleteMatching() {
		if (isset($_POST['temu_delete_products_id'])) {
			$pID = (int)$_POST['temu_delete_products_id'];
			MagnaDB::gi()->delete(TABLE_MAGNA_TEMU_PREPARE, array(
				'mpID' => $this->aMagnaSession['mpID'],
				'products_id' => $pID,
			));
			MagnaDB::gi()->delete(TABLE_MAGNA_TEMU_PREPARE_LONGTEXT, array(
				'mpID' => $this->aMagnaSession['mpID'],
				'products_id' => $pID,
			));
		}
	}

	protected function processMatching() {
		if (isset($_POST['temu_prepare_action'])) {
			switch ($_POST['temu_prepare_action']) {
				case 'save':
					$this->saveMatching();
					break;
				case 'delete':
					$this->deleteMatching();
					break;
			}
		}
	}

	public function process() {
		// React attribute-matching AJAX is dispatched by request marker (applyAction=react),
		// independent of the current view — mirrors Amazon's apply.php dispatch. Without this,
		// a category-change reload or attribute save posted from view=apply/prepare falls into
		// the product-list branch below and returns HTML, breaking the JSON-expecting React
		// frontend ("Error loading attributes.").
		if ($this->isAjax && isset($_GET['applyAction']) && $_GET['applyAction'] === 'react') {
			$oVarMatch = new TemuVariationMatching(array('resources' => $this->resources));
			$oVarMatch->renderAjax();
			return;
		}

		$mPrepareResult = $this->savePrepare();
		$this->processMatching();

		global $_MagnaSession, $_url;

		// AJAX requests for variation matching come without view param
		if ($this->isAjax && isset($_GET['where']) && in_array($_GET['where'], array('catMatchView', 'prepareView'))) {
			$sView = 'varmatch';
		} else {
			$sView = isset($_GET['view']) ? $_GET['view'] : 'apply';
		}

		// "Prepare Selected" ($_POST['prepare']) opens the Product Preparation screen
		// (product data + matching). The standalone Attributes Matching tab stays 'varmatch'.
		if (isset($_POST['prepare'])) {
			$sView = 'prepareform';
		}

		// "Save and Close" on the Product Preparation screen posts to the matching form's
		// action (view=varmatch). Without this, a failed save would drop the user onto the
		// Attributes Matching tab. Keep them on the prepare form when verification fails so
		// they can fix the product; on full success, close back to the product list.
		if ($mPrepareResult !== null) {
			$sView = $mPrepareResult ? 'prepareform' : 'apply';
		}

		switch ($sView) {
			case 'apply': {
				// Load the product-list class defensively (mirrors Otto): fall back to
				// a "not supported" notice instead of rendering blank if it is missing.
				if (($sClass = $this->loadResource('prepare', 'PrepareProductList')) === false) {
					if ($this->isAjax) {
						echo '{"error": "This is not supported"}';
					} else {
						echo 'This is not supported';
					}
					break;
				}
				$oProductList = new $sClass();
				// echo the list object directly so __toString() renders it (init +
				// renderTemplate('skeleton')). Calling renderTemplate() without a
				// template name throws a ValueError on PHP 8 (func_get_arg(0) with no
				// args), which silently aborted the product list and left the
				// "Create New Products" screen blank.
				echo $oProductList;
				break;
			}
			case 'prepareform': {
				$aParams = array(
					'resources' => $this->resources,
				);
				$oPrepareForm = new TemuPrepareForm($aParams);
				if ($this->isAjax) {
					$oPrepareForm->renderAjax();
				} else {
					$oPrepareForm->process();
				}
				break;
			}
			case 'varmatch': {
				$aParams = array(
					'resources' => $this->resources,
				);
				$oVarMatch = new TemuVariationMatching($aParams);
				if ($this->isAjax) {
					$oVarMatch->renderAjax();
				} else {
					$oVarMatch->process();
				}
				break;
			}
			default: {
				// Load the product-list class defensively (mirrors Otto): fall back to
				// a "not supported" notice instead of rendering blank if it is missing.
				if (($sClass = $this->loadResource('prepare', 'PrepareProductList')) === false) {
					if ($this->isAjax) {
						echo '{"error": "This is not supported"}';
					} else {
						echo 'This is not supported';
					}
					break;
				}
				$oProductList = new $sClass();
				// echo the list object directly so __toString() renders it (init +
				// renderTemplate('skeleton')). Calling renderTemplate() without a
				// template name throws a ValueError on PHP 8 (func_get_arg(0) with no
				// args), which silently aborted the product list and left the
				// "Create New Products" screen blank.
				echo $oProductList;
				break;
			}
		}
	}
}
