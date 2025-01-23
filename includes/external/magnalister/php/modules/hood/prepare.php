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
 * $Id: checkin.php 1174 2011-07-30 17:49:04Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class HoodPrepare extends MagnaCompatibleBase {

	protected $prepareSettings = array();

	public function __construct(&$params) {
        if (!empty($_POST['FullSerializedForm'])) {
            $newPost = array();
            parse_str_unlimited($_POST['FullSerializedForm'], $newPost);
            $_POST = array_merge($_POST, $newPost);
        }
		parent::__construct($params);

		$this->prepareSettings['selectionName'] = 'prepare';
		$this->resources['url']['mode'] = $this->prepareSettings['selectionName'];
		$this->resources['url']['view'] = $this->prepareSettings['selectionName'];
	}
	
	protected function saveMatching() {
		if (!array_key_exists('savePrepareData', $_POST)) {
            if (!isset($_POST['Action']) || $_POST['Action'] !== 'SaveMatching' || $_GET['where'] === 'varmatchView') {
                return;
            }
		}

		//echo print_m($_POST, '$_POST');

		require_once(DIR_MAGNALISTER_MODULES . 'hood/classes/HoodProductSaver.php');
		require_once(DIR_MAGNALISTER_MODULES . 'hood/checkin/HoodCheckinSubmit.php');
        $oProductSaver = new HoodProductSaver($this->resources['session']);
        $pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID="' . $this->mpID . '" AND
				   selectionname="' . $this->prepareSettings['selectionName'] . '" AND
				   session_id="' . session_id() . '"
		', true);
        $isSinglePrepare = 1 == count($pIDs);
        $shopVariations = $this->saveMatchingAttributes($oProductSaver, $isSinglePrepare);

        $itemDetails = $_POST;
        $itemDetails['ShopVariation'] = $shopVariations;
		unset($itemDetails['savePrepareData']);
		#echo print_m($itemDetails, '$itemDetails');

		$hoodSaver = new HoodProductSaver($this->resources['session']);
		
		if (1 == count($pIDs)) {
			$hoodSaver->saveSingleProductProperties($pIDs[0], $itemDetails);
		} else if (!empty($pIDs)) {
			$hoodSaver->saveMultipleProductProperties($pIDs, $itemDetails);
		}
		
		$ecs = new HoodCheckinSubmit(array(
			'selectionName' => $this->prepareSettings['selectionName'],
			'marketplace' => 'hood',
		));
		
		#echo print_m($ecs->verifyOneItem(), '$ecs->verifyOneItem()');
		$verified = $ecs->verifyOneItem(false);
        $savePrepareData = array_key_exists('savePrepareData', $_POST);
		if ('SUCCESS' == $verified['STATUS']) {
            $isAjax = false;
            if (!$savePrepareData) {
                # stay on prepare product form
                $_POST['prepare'] = 'prepare';
                $isAjax = true;
            }
            if (!$isAjax) {
                MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                    'mpID' => $this->mpID,
                    'selectionname' => $this->prepareSettings['selectionName'],
                    'session_id' => session_id()
                ));
                if (isset($verified['CONFIRMATIONS']) && is_array($verified['CONFIRMATIONS'])) {
                    foreach ($verified['CONFIRMATIONS'] as $value) {
                        echo '
						<div class="hood successBox">' . sprintf(ML_HOOD_LABEL_ADDITEM_COSTS, $value['Costs']) . '</div>';
                        break;
                    }
                }
            }
			
		} else if ('ERROR' == $verified['STATUS']) {
			# noch mal in der Maske bleiben
			$_POST['prepare'] = 'prepare';
	
			/* Letzte Exception holen */
        if ($savePrepareData) {
            $ex = $ecs->getLastException();
            if (is_object($ex) && ($errors = $ex->getErrorArray())) {
                $ex->setCriticalStatus(false);
                foreach ($errors['ERRORS'] as $error) {
                    echo '
						<div class="hood errorBox">
							<span class="error">' . sprintf(ML_HOOD_LABEL_HOODERROR, $error['ERRORCODE']) . '</span>:
							' . fixHTMLUTF8Entities($error['ERRORMESSAGE']) . '
						</div>';
                }
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
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID="'.$this->mpID.'" AND
			       selectionname="'.$this->prepareSettings['selectionName'].'" AND
			       session_id="'.session_id().'"
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
			
			MagnaDB::gi()->delete(TABLE_MAGNA_HOOD_PROPERTIES, $where);
			MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
				'pID' => $pID,
				'mpID' => $this->mpID,
				'selectionname' => $this->prepareSettings['selectionName'],
				'session_id' => session_id()
			));
		}
		unset($_POST['unprepare']);
	}
	
	protected function resetShopData() {
		if (!(array_key_exists('reset_description', $_POST)) || empty($_POST['reset_description'])) {
			return;
		}
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID="'.$this->mpID.'" AND
			       selectionname="'.$this->prepareSettings['selectionName'].'" AND
			       session_id="'.session_id().'"
		', true);
		if (empty($pIDs)) {
			return;
		}
		
		require_once(DIR_MAGNALISTER_MODULES . 'hood/classes/HoodProductSaver.php');
		$hoodSaver = new HoodProductSaver($this->resources['session']);
		
		foreach ($pIDs as $pID) {
			$hoodSaver->resetProductProperties($pID);
			
			MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
				'pID' => $pID,
				'mpID' => $this->mpID,
				'selectionname' => $this->prepareSettings['selectionName'],
				'session_id' => session_id()
			));
		}
		
		unset($_POST['reset_description']);
	}
	
	protected function processMatching() {
		if (($class = $this->loadResource('prepare', 'PrepareView')) === false) {
			if ($this->isAjax) {
				echo '{"error": "This is not supported"}';
			} else {
				echo 'This is not supported';
			}
			return;
		}
	
		$params = array();
		foreach (array('mpID', 'marketplace', 'marketplaceName', 'resources') as $attr) {
			if (isset($this->$attr)) {
				$params[$attr] = &$this->$attr;
			}
		}

        /** @var $cMDiag HoodPrepareView  */
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
				echo '{"error": "This is not supported"}';
			} else {
				echo 'This is not supported';
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
        // ajax / LoadMPVariations in prepare item form
        if ((isset($_GET['mode']) && $_GET['mode'] == 'prepare')
            && (isset($_GET['view']) && ($_GET['view'] == 'apply' || $_GET['view'] == 'prepare'))
            && (isset($_GET['kind']) && $_GET['kind'] == 'ajax')
            && (isset($_GET['where']) && $_GET['where'] == 'HoodPrepareView')
            && (isset($_POST['Action']) && $_POST['Action'] == 'LoadMPVariations')
            && (isset($_POST['SelectValue']))) {
            $productModel = HoodHelper::gi()->getProductModel('prepare');
            die(json_encode(HoodHelper::gi()->getMPVariations($_POST['SelectValue'], $productModel, true)));
        }
		$this->saveMatching();
		if (isset($_POST['prepare']) || (isset($_GET['where']) && ($_GET['where'] == 'prepareView'))) {
			$this->processMatching();
		} else if (    isset($_GET['where'])
            && ($_GET['where'] == 'HoodPrepareView')
            && isset($_GET['kind'])
            && ($_GET['kind'] == 'ajax')
            && isset($_POST['Action'])
            && ($_POST['Action'] == 'DBMatchingColumns')
            && isset($_POST['Table'])) {
            // processMatching instantiates MetroPrepareView and calls MetroPrepareView->renderAjax() if needed
            $this->processMatching();
        } else {
			if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {
				$this->processProductList();
			} else {
				$this->deleteMatching();
				$this->resetShopData();
				$this->processSelection();
			}
		}
	}

    protected function saveMatchingAttributes($oProductSaver, $isSinglePrepare) {

        if (isset($_POST['Variations'])) {
            parse_str_unlimited($_POST['Variations'], $params);
            $_POST = $params;
        }

        $sIdentifier = $_POST['PrimaryCategory'];
        $matching = isset($_POST['ml']['match']) ? $_POST['ml']['match'] : false;
        $variationThemeAttributes = null;

        if (isset($_POST['variationTheme']) && $_POST['variationTheme'] !== 'null') {
            $variationThemes = json_decode($_POST['variationThemes'], true);
            $variationThemeAttributes = $variationThemes[$_POST['variationTheme']]['attributes'];
        }

        $savePrepare = true;

        $oProductSaver->aErrors = array_merge($oProductSaver->aErrors,
            HoodHelper::gi()->saveMatching($sIdentifier, $matching, $savePrepare, true, $isSinglePrepare, $variationThemeAttributes));

        return json_encode($matching['ShopVariation']);
    }

}
