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
 * (c) 2011 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once(DIR_MAGNALISTER_MODULES.'meinpaket/prepare/MeinpaketProductPrepareSaver.php');
require_once(DIR_MAGNALISTER_MODULES.'meinpaket/MeinpaketHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/MagnaCompatibleBase.php');

class MeinpaketProductPrepare extends MagnaCompatibleBase
{
	protected $resources = array();
	
	protected $mpID= 0;
	protected $marketplace = '';

	protected $isAjax = false;
	
	protected $prepareSettings = array();
	
	protected $saver = null;
	
	public function __construct(&$resources) {
		if (!empty($_POST['FullSerializedForm'])) {
			$newPost = array();
			parse_str_unlimited($_POST['FullSerializedForm'], $newPost);
			$_POST = array_merge($_POST, $newPost);
		}

		parent::__construct($resources);

		$this->resources = &$resources;
		$this->specificResource = ucfirst($this->resources['session']['currentPlatform']);
		$this->mpID = $this->resources['session']['mpID'];
		$this->marketplace = $this->resources['session']['currentPlatform'];
		$this->marketplaceName = $this->specificResource;
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		$this->prepareSettings['selectionName'] = isset($_GET['view']) ? $_GET['view'] : 'apply';
		$this->resources['url']['mode'] = 'prepare';
		$this->resources['url']['view'] = $this->prepareSettings['selectionName'];

		$this->saver = new MeinpaketProductPrepareSaver($this->resources, $this->prepareSettings);
	}
	
	protected function savePreparation() {
		if (!array_key_exists('saveMatching', $_POST)) {
			if (!isset($_POST['Action']) || $_POST['Action'] !== 'SaveMatching' || $_GET['where'] === 'varmatchView') {
				return;
			}
		}

		$pIds = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID="' . $this->mpID . '" AND
				   selectionname="' . $this->prepareSettings['selectionName'] . '" AND
				   session_id="' . session_id() . '"
		', true);

		$isSinglePrepare = 1 == count($pIds);

		$variationThemeAttributes = array();
		$shopVariations = $this->saveMatchingAttributes($this->saver, $isSinglePrepare, $variationThemeAttributes);
		$itemDetails = $_POST;
		unset($itemDetails['savePrepareData']);

		if (isset($itemDetails['VariationConfiguration'])) {
			$itemDetails['prepare']['VariationConfiguration'] = $itemDetails['VariationConfiguration'];
			$itemDetails['prepare']['variation_theme'] = json_encode(array($itemDetails['prepare']['VariationConfiguration'] => $variationThemeAttributes));
		}
		
		$itemDetails['prepare']['CategoryAttributes'] = $shopVariations;

		if (isset($itemDetails['prepare']['ShippingDetails']['ShippingCost'])) {
			$itemDetails['prepare']['ShippingDetails']['ShippingCost'] = mlFloatalize($itemDetails['prepare']['ShippingDetails']['ShippingCost']);
		}

		$this->saver->saveProperties($pIds, $itemDetails['prepare']);
		$saveMatching = array_key_exists('saveMatching', $_POST);

		if (count($this->saver->aErrors) === 0 || !$saveMatching) {
			if (!$saveMatching) {
				# stay on prepare product form
				$_POST['prepare'] = 'prepare';
			} else {
				unset($_POST['prepare']);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'mpID' => $this->mpID,
					'selectionname' => $this->prepareSettings['selectionName'],
					'session_id' => session_id()
				));

				echo '<p class="successBox">'.ML_LABEL_SAVED_SUCCESSFULLY.'</p>';
			}
		} else {
			# stay on prepare product form
			$_POST['prepare'] = 'prepare';

			if ($saveMatching) {
				foreach ($this->saver->aErrors as $sError) {
					echo '<div class="errorBox">' . $sError . '</div>';
				}
			}
		}
	}

	protected function deletePreparation() {
		if (!array_key_exists('unprepare', $_POST)) {
			return;
		}
		$pIds = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID="' . $this->mpID . '" AND
				   selectionname="' . $this->prepareSettings['selectionName'] . '" AND
				   session_id="' . session_id() . '"
		', true);
		$this->saver->deleteProperties($pIds);
		//*
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->mpID,
			'selectionname' => $this->prepareSettings['selectionName'],
			'session_id' => session_id()
		));
		//*/
	}
	
	protected function resetPreparation() {
		
	}
	
	protected function execPreparationView() {
		if ($this->prepareSettings['selectionName'] === 'varmatch') {
			$className = 'VariationMatching';
		} else {
			$className = 'ProductPrepareView';
		}
		
		if (($class = $this->loadResource('prepare', $className)) === false) {
			if ($this->isAjax) {
				echo '{"error": "'.__METHOD__.'This is not supported"}';
			} else {
				echo __METHOD__.'This is not supported';
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
			if ($className === 'VariationMatching') {
				$html = $cMDiag->process();
			} else {
				$html = $cMDiag->process($this->saver->loadSelection());
			}

			echo $html;
		}
	}
	
	protected function execSelectionView() {
        require_once(DIR_MAGNALISTER_MODULES.'meinpaket/prepare/MeinpaketPrepareCategoryView.php');
        $pV = new MeinpaketPrepareCategoryView(
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
	        	
    protected function processProductList()
    {
		if ($this->prepareSettings['selectionName'] === 'varmatch') {
			$this->execPreparationView();
			return;
		}

        require_once(DIR_MAGNALISTER_MODULES.'meinpaket/prepare/MeinpaketPrepareProductList.php');
		$o = new MeinpaketPrepareProductList();
        echo  $o;
	}

	public function process() {
		$this->savePreparation();
		$this->deletePreparation();
		$this->resetPreparation();
		
		#echo print_m($_GET, 'GET');
		#echo print_m($_POST, 'POST');
		
		if ((
				isset($_POST['prepare'])
				|| (
					isset($_GET['where'])
					&& in_array($_GET['where'], array('prepareView', 'catMatchView', 'varmatchView'))
				)
			)
			&& ($this->getSelectedProductsCount() > 0)
		) {
			$this->execPreparationView();
		} else {
			if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {
	            $this->processProductList();
			} else {
	            $this->execSelectionView();
			}
		}
	}
	
	protected function saveMatchingAttributes($oProductSaver, $isSinglePrepare, &$variationThemeAttributes = null)
	{
		if (isset($_POST['Variations'])) {
			parse_str_unlimited($_POST['Variations'], $params);
			$_POST = $params;
			if (isset($_POST['saveMatching'])) {
				unset($_POST['saveMatching']);
			}
		}

		$sIdentifier = $_POST['VariationConfiguration'];
		$matching = $_POST['ml']['match'];
		$savePrepare = isset($_POST['saveMatching']) ? $_POST['saveMatching'] : false;

		if ($variationThemeAttributes !== null && isset($_POST['VariationConfiguration'])) {
			if ($_POST['VariationConfiguration'] !== 'null') {
				$variationThemes = json_decode($_POST['variationThemes'], true);
				$variationThemeAttributes = $variationThemes[$_POST['VariationConfiguration']]['attributes'];
			} else {
				$variationThemeAttributes = 'null';
			}
		}

		$oProductSaver->aErrors = array_merge(
			$oProductSaver->aErrors,
			MeinpaketHelper::gi()->saveMatching(
				$sIdentifier,
				$matching,
				$savePrepare,
				true,
				$isSinglePrepare,
				$variationThemeAttributes
			)
		);

		return json_encode($matching['ShopVariation']);
	}
}
