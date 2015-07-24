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

class MeinpaketProductPrepare {
	protected $resources = array();
	
	protected $mpId = 0;
	
	protected $isAjax = false;
	
	protected $prepareSettings = array();
	
	protected $saver = null;
	
	public function __construct(&$resources) {
		$this->resources = &$resources;
		
		$this->mpId = $this->resources['session']['mpID'];
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		
		$this->prepareSettings['selectionName'] = 'prepare';
		
		$this->saver = new MeinpaketProductPrepareSaver($this->resources, $this->prepareSettings);
	}
	
	protected function savePreparation() {
		if (!array_key_exists('savePrepareData', $_POST)) {
			return;
		}
		$pIds = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID="' . $this->mpId . '" AND
				   selectionname="' . $this->prepareSettings['selectionName'] . '" AND
				   session_id="' . session_id() . '"
		', true);
		if (isset($_POST['prepare']['ShippingDetails']['ShippingCost'])) {
			$_POST['prepare']['ShippingDetails']['ShippingCost'] = mlFloatalize($_POST['prepare']['ShippingDetails']['ShippingCost']);
		}
		$this->saver->saveProperties($pIds, $_POST['prepare']);
		
		//*
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->mpId,
			'selectionname' => $this->prepareSettings['selectionName'],
			'session_id' => session_id()
		));
		//*/
		
		echo '<p class="successBox">'.ML_LABEL_SAVED_SUCCESSFULLY.'</p>';
	}
	
	protected function deletePreparation() {
		if (!array_key_exists('unprepare', $_POST)) {
			return;
		}
		$pIds = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID="' . $this->mpId . '" AND
				   selectionname="' . $this->prepareSettings['selectionName'] . '" AND
				   session_id="' . session_id() . '"
		', true);
		$this->saver->deleteProperties($pIds);
		//*
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->mpId,
			'selectionname' => $this->prepareSettings['selectionName'],
			'session_id' => session_id()
		));
		//*/
	}
	
	protected function resetPreparation() {
		
	}
	
	protected function execPreparationView() {
		require_once(DIR_MAGNALISTER_MODULES.'meinpaket/prepare/MeinpaketProductPrepareView.php');
		
		$cMDiag = new MeinpaketProductPrepareView($this->resources);
		if ($this->isAjax) {
			echo $cMDiag->renderAjax();
		} else {
			$html = $cMDiag->process($this->saver->loadSelection());
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
			 WHERE mpID = '.$this->mpId.'
			       AND selectionname = "'.$this->prepareSettings['selectionName'].'"
			       AND session_id = "'.session_id().'"
		');
	}
	        	
            protected function processProductList() {
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
					&& (
						($_GET['where'] == 'prepareView')
						|| ($_GET['where'] == 'catMatchView')
					)
				)
			)
			&& ($this->getSelectedProductsCount() > 0)
		) {
			$this->execPreparationView();
		} else {
                                  if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {  
                                                $this->processProductList();
                                  }else{
                                                $this->execSelectionView();
                                  }
		}
	}
	
}
