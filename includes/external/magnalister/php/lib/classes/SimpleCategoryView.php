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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

class SimpleCategoryView {
	const QUERY_DEBUG = false;
	
	/**
	 * Bei mehr als dieser Anzahl an Produkten werden Komfort-Funktionen abgeschaltet um eine bessere Perfomance
	 * zu gewaehrleisten
	 */
	const DISABLE_COMFORT_BORDER = 10000;
	
	/**
	 * Wenn bei Filtern der Artikel weniger als diese Menge an Produkt-Ids übrig geblieben ist, werden wieder die
	 * Komfort-Funktionen verwendet.
	 */
	const ENABLE_COMFORT_FILTERED_BORDER = 1000;
	
	/**
	 * array of AbstractProductIdFilter
	 * @var array $aIdFilters
	 */
	protected $aProductIdFilters = array();
	
	protected $cPath = 0;
	protected $cPathArray = array();
	protected $search = '';
	protected $productsQuery = '';

	protected $sorting = false;
	protected $allowedProductIDs = array();

	protected $list = array('categories' => array(), 'products' => array());
	protected $settings;
	protected $showOnlyActiveProducts = false;
	protected $_magnasession;
	protected $url = array();
	protected $marketplace = '';
	protected $mpID = 0;

	protected $action = array();

	protected $selection = array();
	protected $newSelection = array();
	
	protected $isAjax = false;
	protected $ajaxReply = array();
	
	protected $productsCount = 0;
	protected $productsFilteredCount = 0;
	
	protected $categoryCheckboxStateCache = array();

	protected $simplePrice = null;
	
	protected $topHTML = '';

	/* caches */
	private $cat2ProdCacheFilter = array();
	private $cat2ProdCacheQuery = '';
	private $__cat2prodCache = array();
	private $__categoryCacheTD = array(); /* Top -> Down */
	private $__categoryCacheBU = array(); /* Bottom -> Up */

	/**
	 * @param $cPath	Selected Category. 0 == top category
	 * @param $sorting	How should the list be sorted? false == default sorting
	 * @param $search   Searchstring for Product
	 * @param $allowedProductIDs	Limit Products to a list of specified IDs, if empty show all Products
	 */
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $allowedProductIDs = array()) {
		global $_MagnaSession, $_url, $magnaConfig;
		
		$this->_magnasession = &$_MagnaSession;
		$this->magnaConfig = &$magnaConfig;

		$this->settings = array_merge(array(
			'ajaxSelector'    => true,
			'showCheckboxes'  => true,
			'selectionName'   => 'general',
			'selectionValues' => array(),
		), $settings);
	
		$this->marketplace = $this->_magnasession['currentPlatform'];
		$this->mpID = $this->_magnasession['mpID'];
	
		$this->showOnlyActiveProducts = getDBConfigValue(
			array($this->_magnasession['currentPlatform'].'.'.$this->settings['selectionName'].'.status', 'val'),
			$this->_magnasession['mpID'],
			false
		);	
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		
		$this->cPathArray = isset($_GET['cPath']) ? $_GET['cPath'] : '0';
		$this->cPathArray = explode('_', $this->cPathArray);
		$this->cPath = is_array($this->cPathArray) && !empty($this->cPathArray)
			? $this->cPathArray[count($this->cPathArray) - 1]
			: '0';
		
		if (!ctype_digit($this->cPath)) {
			$this->cPath = '0';
		}
		
		#echo var_dump_pre($this->cPathArray, '$this->cPathArray');
		#echo var_dump_pre($this->cPath, '$this->cPath');

		if (!$this->isAjax) {
			$this->sorting = $sorting;
			$this->search = $search;
			$this->simplePrice = new SimplePrice();
			$this->simplePrice->setCurrency(DEFAULT_CURRENCY);
			$this->url = &$_url;
			if (empty($this->url['cPath'])) {
				unset($this->url['cPath']);
			}

			if (($this->search == '') && isset($_POST['tfSearch']) && !empty($_POST['tfSearch'])) {
				$this->search = $_POST['tfSearch'];
			}
			if (empty($this->sorting) && isset($_GET['sorting']) && !empty($_GET['sorting'])) {
				$this->sorting = $_GET['sorting'];
			}
		} else {
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Thu, 01 Jan 1970 00:00:00 GMT"); // Datum in der Vergangenheit
			header('Content-Type: text/plain');
		}
		
		$this->productsCount = (int)MagnaDB::gi()->fetchOne('
			SELECT COUNT(*) FROM '.TABLE_PRODUCTS.' '.($this->showOnlyActiveProducts ? 'WHERE products_status <> 0' : '').'
		');
		
		
		/*if (!$this->isAjax) {
			echo print_m($this->_magnasession['currentPlatform'].'.'.$this->settings['selectionName'].'.status');
			echo var_dump_pre($this->showOnlyActiveProducts, '$this->showOnlyActiveProducts');
		}//*/

//		initArrayIfNecessary($_MagnaSession, $_MagnaSession['currentPlatform'].'|selection|'.$this->settings['selectionName']);
//		$this->selection = &$_MagnaSession[$_MagnaSession['currentPlatform']]['selection'][$this->settings['selectionName']];
//		$_MagnaSession[$_MagnaSession['currentPlatform']]['selection'][$this->settings['selectionName']] = array();

		/*MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array (
			'selected' => false,
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id(),
		));*/
		
		$this->init();
		$this->initSelection();
		$this->executeProductIdFilters($allowedProductIDs);
		
		$this->productsFilteredCount = count($this->allowedProductIDs);
		
		if ($this->isComfortDisabled()) {
			// Filter the product_ids further. Only display the products in the currently selected category.
			// Do this without a filter because we have to make sure the categories won't be filtered.
			$pIds = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT products_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' WHERE categories_id = "'.$this->cPath.'"
			', true);
			
			$this->allowedProductIDs = array_intersect($this->allowedProductIDs, is_array($pIds) ? $pIds : array());
			
			if (!$this->isAjax && (MAGNA_DEBUG === true)) {
				echo 'ComfortFilter => '.count($this->allowedProductIDs)."<br>\n";
			}
		} else if (!$this->isAjax && (MAGNA_DEBUG === true)) {
			echo "ComfortFilter =>  <span style=\"color:gray\">inactive</span><br>\n";
		}
		
		#var_dump($this->isComfortDisabled());
		
		$_timer = microtime(true);
		
		$this->selectProducts();
		
		if (!empty($this->ajaxReply)) {
			$this->ajaxReply['timer'] = microtime2human(microtime(true) -  $_timer);
		}
		

	}
	
	public function __destruct() {
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id()
		));
		if (!empty($this->selection)) {
			$batch = array();
			foreach ($this->selection as $pID => $data) {
				$batch[] = array(
					'pID' => $pID,
					'data' => serialize($data),
					'mpID' => $this->_magnasession['mpID'],
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id(),
					'expires' => gmdate('Y-m-d H:i:s')
				);
			}
			MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION, $batch, true);
			unset($batch);
		}
	}
	
	protected function init() {
		
	}
	
	protected function initSelection() {
		$newSelectionResult = MagnaDB::gi()->query('
			SELECT pID, data
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID="'.$this->_magnasession['mpID'].'" AND
			       selectionname="'.$this->settings['selectionName'].'" AND
			       session_id="'.session_id().'"
		');
		$this->selection = array();
		while ($row = MagnaDB::gi()->fetchNext($newSelectionResult)) {
			$this->selection[$row['pID']] = unserialize($row['data']);
		}
	}
	
	protected function isComfortDisabled() {
		// No comfort functions for very large inventories
		
		return ($this->productsCount > self::DISABLE_COMFORT_BORDER) 
			// Enable comfort behavior if the filtered list is very small
			&& ($this->productsFilteredCount > self::ENABLE_COMFORT_FILTERED_BORDER)
			// Confort functions always have to be enabled for ajax calls.
			&& !$this->isAjax
			// And searches.
			&& empty($this->search);
	}
	
	protected function executeProductIdFilters($allowedProductIDs) {
		// echo print_m($allowedProductIDs, '$allowedProductIDs');
		
		$this->allowedProductIDs = $this->productIdFilterGetIds(
			empty($allowedProductIDs)
				? $this->getProductIDsByCategoryID($this->cPath)
				: $allowedProductIDs
		);
		
		/* {Hook} "SimpleCategoryView_PostAllowedProductIDs": Enables you to filter the IDs of the products
		   that are going to be displayed by the view.<br>
		   Variables that can be used: <ul>
		       <li>$this->allowedProductIDs (array): The list of all allowed product ids that will be shown by the view.</li>
		       <li>$this->mpID: The current marketplace id.</li>
		       <li>$this->marketplace: The name of the marketplace.</li>
		   </ul>
		 */
		if (($hp = magnaContribVerify('SimpleCategoryView_PostAllowedProductIDs', 1)) !== false) {
			require($hp);
		}
		
		// echo print_m($this->allowedProductIDs, '$this->allowedProductIDs');
	}
	
	protected function productIdFilterGetIds($aProductIds) {
		if ($this->showOnlyActiveProducts) {
			foreach ($this->productIdFilterGetRegistered() as $oFilter) {
				/* @var $oFilter AbstractProductIdFilter */
				if ($oFilter->isActive()) {
					// add ProductStatusFilter, actual its only necessary if any filter is active. otherwise old behaviour
					$this->productIdFilterRegister('ProductStatusFilter', array());
					break;
				}
			}
		}
		foreach ($this->productIdFilterGetRegistered() as $oFilter) {
			/* @var $oFilter AbstractProductIdFilter */
			if ($oFilter->isActive()) {
				if (count($aProductIds) != 0) { // it is already 0, nothing to filter
					$oFilter->setCurrentIds($aProductIds);
					$aFilterIds = $oFilter->getProductIds();
					$aProductIds = array_intersect($aProductIds, (empty($aFilterIds) ? array() : $aFilterIds));
				}
				foreach ($oFilter->getUrlParams() as $sKey => $sValue) {
					$this->url[$sKey] = $sValue;
				}
				if (!$this->isAjax && (MAGNA_DEBUG === true)) {
					echo get_class($oFilter).' => '.count($aProductIds).'<br />';
				}
			} elseif (!$this->isAjax && (MAGNA_DEBUG === true)) {
				echo get_class($oFilter).' => <span style="color:gray">inactive</span><br />';
			}
		}
		return $aProductIds;
	}
	
	protected function productIdFilterRegister($sFilterName, $aInit = array()) {
		$sSearchFile = DIR_MAGNALISTER_INCLUDES.'%s/classes/productIdFilter/'.$sFilterName.'.php';
		if (file_exists(sprintf($sSearchFile, 'modules/'.$this->marketplace))) {
			$sFile = sprintf($sSearchFile, 'modules/'.$this->marketplace);
		} elseif (file_exists(sprintf($sSearchFile, 'lib'))) {
			$sFile = sprintf($sSearchFile, 'lib');
		}
		if (isset($sFile)) {
			require_once($sFile);
			$oFilter = new $sFilterName();
			/* @var $oFilter AbstractProductIdFilter */
			$this->aProductIdFilters[] = $oFilter;
			$oFilter->init(array_merge($aInit, array (
				'MpId' => $this->mpID,
				'Marketplace' => $this->marketplace,
			)));
		}
		return $oFilter;
	}
	
	protected function productIdFilterGetRegistered() {
		return $this->aProductIdFilters;
	}
	
	protected function productIdFilterRender() {
		$sHtml = '';
		foreach ($this->productIdFilterGetRegistered() as $oFilter) {
			/* @var $oFilter AbstractProductIdFilter */
			$sCurrentHtml = $oFilter->getHtml();
			if ($sCurrentHtml != '') {
				$sHtml .= '<div class="right" style="margin-top:.6em;margin-right:.6em">'.$sCurrentHtml.'</div>';
			}
		}
		return $sHtml;
	}

	private function selectProducts() {
		#echo print_m($_POST, true);
		
		if ($this->isAjax) {
			if (preg_match('/^(.*)\[(.*)\]$/', $_POST['action'], $match)) {
				$_POST[$match[1]][$match[2]] = 0;
			}
		}
		
		$sPIDs = array();
		if (array_key_exists('selectableProducts', $_POST) &&
			($_POST['selectableProducts'] = trim($_POST['selectableProducts'])) &&
			!empty($_POST['selectableProducts'])
		) {
			$sPIDs = explode(':', $_POST['selectableProducts']);
		}
		$sCIDs = array();
		if (array_key_exists('selectableCategories', $_POST) && 
			($_POST['selectableCategories'] = trim($_POST['selectableCategories'])) &&
			!empty($_POST['selectableCategories'])
		) {
			$sCIDs = explode(':', $_POST['selectableCategories']);
		}

		if (array_key_exists('pAdd', $_POST)) {
			$pID = array_keys($_POST['pAdd']);
			$this->addProductsToSelection($pID);
			$this->ajaxReply = array (
				'type' => 'p',
				'checked' => true,
				'newname' => 'pRemove['.$pID[0].']'
			);
		}
		if (array_key_exists('pRemove', $_POST)) {
			$pID = array_keys($_POST['pRemove']);
			$this->removeProductsFromSelection($pID);
			$this->ajaxReply = array (
				'type' => 'p',
				'checked' => false,
				'newname' => 'pAdd['.$pID[0].']'
			);
		}
		if (array_key_exists('cAdd', $_POST)) {
			$cID = array_keys($_POST['cAdd']);
			if ($cID[0] == '0') {
				//echo print_m($sPIDs, '$sPIDs[add]');
				//echo print_m($sCIDs, '$sCIDs[add]');
				if (!empty($sPIDs) || !empty($sCIDs)) {
					if (!empty($sCIDs)) {
						foreach ($sCIDs as $sCID) {
							$this->addProductsToSelection($this->getProductIDsByCategoryIDFiltered($sCID));
						}
					}
					if (!empty($sPIDs)) {
						$this->addProductsToSelection($sPIDs);
					}
				} else {
					$this->addProductsToSelection($this->allowedProductIDs);
				}
				$this->ajaxReply = array (
					'type' => 'a',
					'checked' => true,
					'newname' => 'cRemove[0]'
				);
			} else {
				#echo print_m($this->getProductIDsByCategoryID($cID[0]), true);
				$this->addProductsToSelection($this->getProductIDsByCategoryIDFiltered($cID[0]));
				$this->ajaxReply = array (
					'type' => 'c',
					'checked' => true,
					'newname' => 'cRemove['.$cID[0].']'
				);
			}
		}
		if (array_key_exists('cRemove', $_POST)) {
			$cID = array_keys($_POST['cRemove']);
			if ($cID[0] == '0') {
				//echo print_m($sPIDs, '$sPIDs[add]');
				//echo print_m($sCIDs, '$sCIDs[add]');
				if (!empty($sPIDs) || !empty($sCIDs)) {
					if (!empty($sCIDs)) {
						foreach ($sCIDs as $sCID) {
							$this->removeProductsFromSelection($this->getProductIDsByCategoryIDFiltered($sCID));
						}
					}
					if (!empty($sPIDs)) {
						$this->removeProductsFromSelection($sPIDs);
					}
				} else {
					$this->selection = array();
				}
				$this->ajaxReply = array (
					'type' => 'a',
					'checked' => false,
					'newname' => 'cAdd[0]'
				);
			} else {
				$this->removeProductsFromSelection($this->getProductIDsByCategoryIDFiltered($cID[0]));
				$this->ajaxReply = array (
					'type' => 'c',
					'checked' => false,
					'newname' => 'cAdd['.$cID[0].']'
				);
			}
		}
	}

	public function getCategoryCache() {
		if (empty($this->__categoryCacheTD)) {
			$where = ((SHOPSYSTEM != 'oscommerce') && $this->showOnlyActiveProducts) ? 'WHERE categories_status<>0' : '';
			$catQuery = MagnaDB::gi()->query('
			    SELECT categories_id, parent_id 
			      FROM '.TABLE_CATEGORIES.' 
			     '.$where.'
			');
			$this->__categoryCacheTD = array();
			while ($tmp = MagnaDB::gi()->fetchNext($catQuery)) {
				$this->__categoryCacheTD[(int)$tmp['parent_id']][] = (int)$tmp['categories_id'];
				$this->__categoryCacheBU[(int)$tmp['categories_id']][] = (int)$tmp['parent_id'];
			}
			unset($catQuery);
			unset($tmp);
		}
	}

	public function getAllSubCategoriesOfCategory($cID = 0) {
		$this->getCategoryCache();

		$subCategories = isset($this->__categoryCacheTD[$cID]) ? $this->__categoryCacheTD[$cID] : array();
	
		if (!empty($subCategories)) {
			foreach ($subCategories as $c) {
				$b = $this->getAllSubCategoriesOfCategory($c);
				$this->mergeArrays($subCategories, $b);
			}
		}
	
		return $subCategories;
	}

	protected function setCat2ProdCacheQueryFilter($ex = array()) {
		$this->cat2ProdCacheFilter = $ex;
	}

	protected function setupCat2ProdCacheQuery($ex = array()) {
		if (!empty($ex)) {
			$this->cat2ProdCacheFilter = $ex;
		}
		$this->cat2ProdCacheQuery = '
		    SELECT DISTINCT p2c.products_id, p2c.categories_id 
		      FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c
		      JOIN '.TABLE_PRODUCTS.' p ON p.products_id = p2c.products_id
		';
		$where = '';
		$join = '';
		if (!empty($this->cat2ProdCacheFilter)) {
			foreach ($this->cat2ProdCacheFilter as $item) {
				$this->cat2ProdCacheQuery .= (empty($item['join'])) ? '' : '
		           '.$item['join'];
				$where .= (empty($item['where'])) ? '' : ' AND
		           '.$item['where'];
			}
		}
		$where .= ($this->showOnlyActiveProducts) ? ' AND p.products_status<>0' : '';
		$where .= (getDBConfigValue('general.keytype', '0') == 'artNr') ? ' AND p.products_model<>""' : '';
		$this->cat2ProdCacheQuery .= $join.(!empty($where) ? '
		     WHERE '.substr($where, strlen(' AND')) : '');
		#echo var_dump_pre($this->showOnlyActiveProducts, '$this->showOnlyActiveProducts', true);
		
		if (self::QUERY_DEBUG) echo print_m($this->cat2ProdCacheQuery, '$this->cat2ProdCacheQuery['.get_class($this).']');
		//die();
	}

	public function getCat2ProdCache() {
		if (!empty($this->__cat2prodCache)) {
			return;
		}
		if (empty($this->cat2ProdCacheQuery)) {
			$this->setupCat2ProdCacheQuery();
		}
		$prod2catQuery = MagnaDB::gi()->query($this->cat2ProdCacheQuery);
		$this->__cat2prodCache = array();
		while ($tmp = MagnaDB::gi()->fetchNext($prod2catQuery)) {
			if ($tmp['products_id'] == '0') continue;
			$this->__cat2prodCache[(int)$tmp['categories_id']][] = (int)$tmp['products_id'];
		}
		#echo print_m($this->__cat2prodCache, '__cat2prodCache', true);
		unset($prod2catQuery);
		unset($tmp);
	}

	public function getProductIDsByCategoryID($cID) {
		#echo print_m($cID, __METHOD__);
		$this->getCat2ProdCache();

		$subCategories = array($cID);
		$c = $this->getAllSubCategoriesOfCategory($cID);
		$this->mergeArrays($subCategories, $c);
		unset($c);
			
		$productIDs = array();
		if (!empty($subCategories)) {
			foreach ($subCategories as $cC) {
				$copyArray = isset($this->__cat2prodCache[$cC]) ? $this->__cat2prodCache[$cC] : array();
				$this->mergeArrays(
					$productIDs,
					$copyArray
				);
			}
		}
		return array_unique($productIDs);
	}
	
	public function getProductIDsByCategoryIDFiltered($cID) {
		#echo print_m($cID, __METHOD__);
		return array_intersect($this->getProductIDsByCategoryID($cID), $this->allowedProductIDs);
	}
	
	protected function getChildProductsOfThisLevel($cID) {
		if (empty($this->cat2ProdCacheQuery)) {
			$this->setupCat2ProdCacheQuery();
		}
		$extQuery = $this->cat2ProdCacheQuery.' '.(
			(strpos($this->cat2ProdCacheQuery, 'WHERE') !== false)
				? 'AND'
				: 'WHERE' 
			).' p2c.categories_id="'.$cID.'"';
		$pIDs = array();
		$r = MagnaDB::gi()->query($extQuery);
		while($row = MagnaDB::gi()->fetchNext($r)) {
			$pIDs[] = $row['products_id'];
		}
		return $pIDs;
	}

	private function mergeArrays(&$sourceArray, &$copyArray){
		//merge copy array into source array
		$i = 0;
		while (isset($copyArray[$i])){
			$sourceArray[] = $copyArray[$i];
			unset($copyArray[$i]);
			++$i;
		}
	}

	private function addProductsToSelection($productsToAdd) {
		if (!empty($productsToAdd)) {
			foreach ($productsToAdd as $p) {
				if (!array_key_exists($p, $this->selection)) {
					$this->selection[(string)$p] = $this->settings['selectionValues'];
				}
			}
		}
	}

	private function removeProductsFromSelection($productsToRemove) {
		if (!empty($productsToRemove)) {
			foreach ($productsToRemove as $p) {
				if (array_key_exists($p, $this->selection)) {
					unset($this->selection[(string)$p]);
				}
			}
		}
	}

	protected function getSorting() {
		if (!$this->sorting) {
			$this->sorting = 'name';
		}
		switch ($this->sorting) {
			case 'price': {
				$sort['cat']  = 'TRIM(cd.categories_name) ASC';
				$sort['prod'] = 'p.products_price ASC';
				break;
			}
			case 'price-desc': {
				$sort['cat']  = 'TRIM(cd.categories_name) DESC';
				$sort['prod'] = 'p.products_price DESC';
				break;
			}
			case 'model': {
				$sort['cat']  = 'TRIM(cd.categories_name) ASC';
				$sort['prod'] = 'p.products_model ASC';
				break;
			}
			case 'model-desc': {
				$sort['cat']  = 'TRIM(cd.categories_name) DESC';
				$sort['prod'] = 'p.products_model DESC';
				break;
			}
			case 'name-desc': {
				$sort['cat']  = 'TRIM(cd.categories_name) DESC';
				$sort['prod'] = 'TRIM(pd.products_name) DESC';
				break;
			}
			case 'name':
			default: {
				$sort['cat']  = 'TRIM(cd.categories_name) ASC';
				$sort['prod'] = 'TRIM(pd.products_name) ASC';
				break;
			}
		}
		return $sort;
	}
	
	private function getParentCategories($cID, &$categories) {
		$topCID = ($this->search == '') ? $this->cPath : 0;
		
		$copyArray = isset($this->__categoryCacheBU[(int)$cID]) ? $this->__categoryCacheBU[(int)$cID] : array();
		if (empty($copyArray)) {
			return;
		}
		foreach($copyArray as $addCID) {
			if (!array_key_exists($addCID, $categories)) {
				if (($addCID == $topCID) || ($addCID == 0)) {
					return;
				}
				$categories[$addCID] = 0;
				$this->getParentCategories($addCID, $categories);
			}
		}
	}
	
	/**
	 * Sucht von Unterkategorie richtung Oberkategorie. Daher kann 
	 * $this->__categoryCacheTD nicht verwendet werden. Dieser ist fuer die andere
	 * Suchrichtung optimiert.
	 */
	private function getAllParentCategories(&$categories) {
		$this->getCategoryCache();
		$categories = array_flip($categories);

		foreach ($categories as $cID => $null) {
			$categories[(int)$cID] = 0;
			$this->getParentCategories($cID, $categories);
		}
		return array_keys($categories);
	}

	protected function filterCategoriesList() {
		// echo print_m($this->allowedProductIDs);
		
		if ($this->isComfortDisabled()) {
			// Don't filter the categories and show them all if the inventory is too large.
			return '';
		}
		
		if (!empty($this->allowedProductIDs)) {
			if (empty($this->cat2ProdCacheQuery)) {
				$this->setupCat2ProdCacheQuery();
			}
			
			$allowedCategoriesQuery = str_replace('DISTINCT p2c.products_id, ', 'DISTINCT ', $this->cat2ProdCacheQuery);
			$allowedCategoriesQuery .= ' AND p2c.products_id IN ('.implode(', ', $this->allowedProductIDs).')';
			/*
			$allowedCategoriesQuery = '
				SELECT DISTINCT p2c.categories_id 
				  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c
				 WHERE p2c.products_id IN ('.implode(', ', $this->allowedProductIDs).')
			';
			//*/
			
			if (self::QUERY_DEBUG) echo print_m($allowedCategoriesQuery, '$allowedCategoriesQuery');
			$allowedCategories = MagnaDB::gi()->fetchArray($allowedCategoriesQuery, true);
			
			/* Get all involved parent categories */
			if (!empty($allowedCategories)) {
				// echo print_m($allowedCategories, '$allowedCategories');
				//$_t = microtime(true);
				$allowedCategories = $this->getAllParentCategories($allowedCategories);
				//echo microtime2human(microtime(true) - $_t);
			}
			//echo print_m($allowedCategories, '$allowedCategories');
			
			$allowedCategoriesWhere = 'c.categories_id IN ('.implode(', ', $allowedCategories).') AND ';
		} else {
			$allowedCategoriesWhere = '(0 = 1) AND '; // false... obviously
		}
		return $allowedCategoriesWhere;
	}
	
	protected function retriveCategoriesListAddCategory($category) {
		$category['allproductsids'] = $this->getProductIDsByCategoryIDFiltered($category['categories_id']);
		$this->list['categories'][$category['categories_id']] = $category;
	}
	
	protected function retriveCategoriesList() {
		if (self::QUERY_DEBUG) echo print_m(__LINE__, __METHOD__);
		
		$allowedCategoriesWhere = $this->filterCategoriesList();
		
		$queryStr = '
			  SELECT c.categories_id, cd.categories_name, c.categories_image, c.parent_id
			    FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
			   WHERE c.categories_id = cd.categories_id
			         AND cd.language_id = "'.(int)$_SESSION['languages_id'].'"
			         AND '.(((SHOPSYSTEM != 'oscommerce') && $this->showOnlyActiveProducts)
			         	? 'categories_status<>0 AND' 
			         	: ''
			         ).' '.$allowedCategoriesWhere;
		
		if ($this->search != '') {
			$queryStr .= "cd.categories_name like '%" . MagnaDB::gi()->escape($this->search) . "%' ";
		} else {
			$queryStr .= "c.parent_id = '" . $this->cPath . "' ";
		}
		$sort = $this->getSorting();
		
		$queryStr .= "ORDER BY " . $sort['cat'];
		if (self::QUERY_DEBUG) echo print_m($queryStr, 'CategoryQuery');
		
		$categories = MagnaDB::gi()->fetchArray($queryStr);
		//echo var_dump_pre($categories, '$categories');
		$this->list['categories'] = array();
		if (!empty($categories)) {
			foreach ($categories as $category) {
				$this->retriveCategoriesListAddCategory($category);
			}
		}
		unset($categories);
	}
	
	protected function setupProductsQuery($fields = '', $from = '', $where = '') {
		$sort = $this->getSorting();
		
		if (!empty($this->allowedProductIDs)) {
			$whereProducs = 'p.products_id IN ('.implode(', ', $this->allowedProductIDs).')';
		} else {
			$whereProducs = '(0 = 1)'; // false again... ZOMG
		}

		$this->productsQuery = '
			SELECT p.products_tax_class_id, p.products_id, pd.products_name, p.products_model,
			       p.products_quantity, p.products_image, p.products_price, 
			       p2c.categories_id'.(($fields != '') ? (', '.$fields) : '').'
			  FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd, '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c 
			       '.(($from != '') ? (', '.$from) : '').'
			 WHERE p.products_id = pd.products_id
			       AND pd.language_id = "'.(int)$_SESSION['languages_id'].'"
			       AND p.products_id = p2c.products_id
			       '.(($this->showOnlyActiveProducts) ? 'AND p.products_status<>0' : '').'
			       AND '.$whereProducs.'
			       '.(($where != '') ? ('AND '.$where) : '').' ';

		$this->addFilterToProductsQuery();
		
		if ($this->search != '') {
			$addQuery = '';
			$search = MagnaDB::gi()->escape($this->search);
			/* {Hook} "SimpleCategoryView_ProductSearch": Enables you to extend the product search with additional features.<br>
			   Variables that can be used: <ul>
			       <li>$addQuery: extend the product serarch query with your own condition</li>
			       <li>$search: the string the user has searched for (already escaped, to unescape use MagnaDB::unescape())</li>
			   </ul>
			 */
			if (($hp = magnaContribVerify('SimpleCategoryView_ProductSearch', 1)) !== false) {
				require($hp);
			}
			if (empty($addQuery)) {
				$addQuery = '
				       AND (
				            pd.products_name LIKE "%'.$this->search.'%" OR p.products_model LIKE "%'.$this->search.'%"
				            OR p.products_id="'.$this->search.'"
				       )';
			}
			$this->productsQuery .= $addQuery.'
		  GROUP BY p.products_id ';
			$this->productsQuery = str_replace('SELECT', 'SELECT DISTINCT', $this->productsQuery);
			
		} else {
			$this->productsQuery .= 'AND p2c.categories_id = "'.$this->cPath.'" ';
		}
		$this->productsQuery .= 'ORDER BY '.$sort['prod'];
		
		if (self::QUERY_DEBUG) echo print_m($this->productsQuery, 'ProductsQuery');
	}
	
	protected function retriveProductsList() {
		if ($this->productsQuery == '') {
			$this->setupProductsQuery();
		}

		//echo print_m($this->productsQuery, '$this->productsQuery');
		$this->list['products'] = array();
		
		$products = MagnaDB::gi()->fetchArray($this->productsQuery);
		if (!empty($products)) {
			foreach ($products as $product) {
				$this->list['products'][$product['products_id']] = $product;
			}
		}
		
		unset($products);
	}
	
	protected function retriveList() {
		$this->retriveCategoriesList();
		$this->retriveProductsList();
	}

	private function buildCPath($newCID) {
		if ($this->cPath != 0) {
			return implode('_', array_merge($this->cPathArray, array($newCID)));
		}
		return $newCID;
	}

	private function getCategoryCheckboxState($id) {
		if (!array_key_exists($id, $this->categoryCheckboxStateCache)) {
			if (count($this->selection) == 0) {
				$iTotal = 1; //something
				$iSelected = 0;
			}else{
				$aTotal = $this->getProductIDsByCategoryID($id, true);
				$iTotal = count($aTotal);
				$iSelected = found_in_array($aTotal, $this->selection);
			}
			if ($iSelected == 0) {
				$this->categoryCheckboxStateCache[$id] = array(
					'state' => 'unchecked',
					'add' => true
				);
			} else if ($iSelected < $iTotal) {
				$this->categoryCheckboxStateCache[$id] = array(
					'state' => 'semichecked',
					'add' => true
				);
			} else if ($iSelected == $iTotal) {
				$this->categoryCheckboxStateCache[$id] = array(
					'state' => 'checked',
					'add' => false
				);
			}
		}
		return $this->categoryCheckboxStateCache[$id];
	}

	private function categorySelector($cID) {
		$cB = $this->getCategoryCheckboxState($cID, true);
		return '<input type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" '.
		               'class="checkbox '.$cB['state'].'" value="" '.
		               'name="c'.($cB['add'] ? 'Add' : 'Remove').'['.$cID.']" '.
		               'id="c_'.$cID.'" '.
		               'title="'.($cB['add'] ? ML_LABEL_SELECT_ALL_PRODUCTS_OF_CATEGORY : ML_LABEL_DESELECT_ALL_PRODUCTS_OF_CATEGORY).'" />';
	}

	private function productSelector($pID) {
		if (array_key_exists($pID, $this->selection)) {
			return '<input type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" '.
			               'class="checkbox checked" value="" name="pRemove['.$pID.']" '.
			               'id="p_'.$pID.'" title="'.ML_LABEL_DESELECT_PRODUCT.'"/>';
		}
		return '<input type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" '.
		               'class="checkbox unchecked" value="" name="pAdd['.$pID.']" '.
		               'id="p_'.$pID.'" title="'.ML_LABEL_SELECT_PRODUCT.'"/>';
	}

	private function topSelectionButtons() {
		$label = '<label for="selectAll">'.ML_LABEL_CHOICE.'</label>';
		if (empty($this->list['categories']) && empty($this->list['products'])) {
			return '<input type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" 
			               class="checkbox" value="" title="'.ML_LABEL_NO_PRODUCTS_SELECTABLE.'" disable="disable"/>';
		}

		$toAdd = 0;
		$toRemove = 0;
		if (!empty($this->list['categories'])) {
			foreach ($this->list['categories'] as $cID => $value) {
				$cB = $this->getCategoryCheckboxState($cID);
				$cB['add'] ? ++$toAdd : ++$toRemove;
			}
		}
		//echo '<pre>$toAdd = '.$toAdd.'; $toRemove = '.$toRemove.';</pre>';
		if (!empty($this->list['products'])) {
			foreach ($this->list['products'] as $pID => $value) {
				array_key_exists($pID, $this->selection) ? ++$toRemove : ++$toAdd;
			}
		}
		//echo '<pre>$toAdd = '.$toAdd.'; $toRemove = '.$toRemove.';</pre>';
		if (($toAdd == 0) && ($toRemove > 0)) {
			$name = 'cRemove';
			$state = 'checked';
			$add = false;
		} else if (($toAdd > 0) && ($toRemove == 0)) {
			$name = 'cAdd';
			$state = 'unchecked';
			$add = true;
		} else {
			$name = 'cAdd';
			$state = 'semichecked';
			$add = true;
		}		
		
		$addFields = '';
		if ( !empty($this->search) ) {
			if (!empty($this->list['categories'])) {
				$addFields .= '
					<input type="hidden" id="selectableCategories" name="selectableCategories" value="'.implode(':', array_keys($this->list['categories'])).'"/>';
			}
			if (!empty($this->list['products'])) {
				$addFields .= '
					<input type="hidden" id="selectableProducts" name="selectableProducts" value="'.implode(':', array_keys($this->list['products'])).'"/>';
			}
		}
		return $addFields.'
			<input id="selectAll" type="'.($this->settings['ajaxSelector'] ? 'button' : 'submit').'" '.
			       'class="checkbox '.$state.'" value="" name="'.$name.'['.(!empty($this->search) ? 0 : $this->cPath).']" title="'.
			            (($this->cPath == 0) 
			            	? 
			            		($add ? ML_LABEL_SELECT_ALL_PRODUCTS : ML_LABEL_DESELECT_ALL_PRODUCTS) 
			            	:
			            		($add ? ML_LABEL_SELECT_ALL_PRODUCTS_OF_CATEGORY : ML_LABEL_DESELECT_ALL_PRODUCTS_OF_CATEGORY)
			            ).
			       '" />'.$label;
		
	}

	protected function sortByType($type) {
		return '
			<span class="nowrap">
				<a href="'.toURL($this->url, array('sorting' => $type.'')).'" title="'.ML_LABEL_SORT_ASCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_ASCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_up.png" />
				</a>
				<a href="'.toURL($this->url, array('sorting' => $type.'-desc')).'" title="'.ML_LABEL_SORT_DESCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_DESCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_down.png" />
				</a>
			</span>';
	}
	
	protected function getEmptyInfoText() {
		return ML_LABEL_EMPTY;
	}
	
	public function appendTopHTML($html) {
		$this->topHTML .= $html;
	}
	
	public function prependTopHTML($html) {
		$this->topHTML = $html.$this->topHTML;
	}
	
	public function printForm() {
		$this->appendTopHTML($this->productIdFilterRender());
		if (array_key_exists('cPath', $_GET) && ($_GET['cPath'] != '')) {
			$this->url['cPath'] = $_GET['cPath'];
		}
		if ($this->sorting) {
			$this->url['sorting'] = $this->sorting;
		}

		if (empty($this->list['categories']) && empty($this->list['products'])) {
			$this->retriveList();
		}
		//echo print_m($this->list, '$this->list');
		
		$extendedHeadline = '';
		/* {Hook} "SimpleCategoryView_ExtendProdCatHeadline": Enables you to extend the category and product headline with html
		   intoduce additional sort parameters or anything else.<br>
		   Variables that can be used: <ul>
		       <li>$this->mpID: The current marketplace id.</li>
		       <li>$this->marketplace: The name of the marketplace.</li>
		       <li>$extendedHeadline: Assign your html content that will be added to the category and product headline to this variable.
		           <b>Don't use echo.</b>
		   </ul>
		   Methods that can be used: <ul>
		       <li>$this->sortByType($sortfield): Generates sort arrows (for the supported sort-fields check $this->getSorting()).</li>
		   </ul>
		 */
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('SimpleCategoryView_ExtendProdCatHeadline', 1)) !== false)) {
			require($hp);
		}
		
		$html = '
		<div id="managerUIElements">'.$this->topHTML.'<div class="visualClear"></div></div>
		<form class="categoryView" action="'.toURL($this->url).'" method="post">
			<input name="tfSearch" type="hidden" value="'.$this->search.'"/>
			<table class="list"><thead>
				<tr>
					<td class="nowrap edit"'.($this->settings['showCheckboxes'] ? ' colspan="2"' : '').'>
						'.($this->settings['showCheckboxes'] ? $this->topSelectionButtons() : '').'
					</td>
					<td class="katProd">'.
						ML_LABEL_CATEGORIES_PRODUCTS.' '.$this->sortByType('name').
						'<div class="nowrap" style="float:right;">'.$extendedHeadline.'</div>'.
					'</td>
					<td class="price">'.ML_LABEL_SHOP_PRICE.' '.$this->sortByType('price').'</td>
					'.$this->getAdditionalHeadlines().'
				</tr>
			</thead><tbody>
		';
		$odd = true;
		
		if (!empty($this->list['categories'])) {
			foreach ($this->list['categories'] as $category) {
				$html .= '
					<tr class="'.(($odd = !$odd) ? 'odd' : 'even').'">
						'.($this->settings['showCheckboxes'] ? '<td class="edit">'.$this->categorySelector($category['categories_id']).'</td>' : '').'
						<td class="image">'.generateProductCategoryThumb($category['categories_image'], 20, 20).'</td>
						<td><a href="'.toURL($this->url, array('cPath' => $this->buildCPath($category['categories_id']))).'">
							'.$this->imageHTML(DIR_MAGNALISTER_WS_IMAGES.'folder.png', ML_LABEL_CATEGORY).' '.fixHTMLUTF8Entities($category['categories_name']).'
						</a></td>
						<td>&mdash;</td>
						'.$this->getAdditionalCategoryInfo($category['categories_id'], $category).'
					</tr>
				';
			}
		}
		if (!empty($this->list['products'])) {
			foreach ($this->list['products'] as $product) {
				if(isset($product['products_price'])){
					$this->simplePrice->setPrice($product['products_price']);
					$netto = $this->simplePrice->format(true);
					$html .= '
						<tr class="'.(($odd = !$odd) ? 'odd' : 'even').'">
							'.($this->settings['showCheckboxes'] ? '<td class="edit">'.$this->productSelector($product['products_id']).'</td>' : '').'
							<td class="image">'.generateProductCategoryThumb($product['products_image'], ML_THUMBS_MINI, ML_THUMBS_MINI).'</td>
							<td><table class="nostyle"><tbody>
									<tr><td class="icoWidth">'.$this->imageHTML(DIR_MAGNALISTER_WS_IMAGES.'shape_square.png', ML_LABEL_PRODUCT).'</td>
										<td>'.fixHTMLUTF8Entities($product['products_name']).'</td></tr>
									<tr><td class="icoWidth">&nbsp;</td>
										<td class="artNr">'.ML_LABEL_ART_NR_SHORT.': '.(!empty($product['products_model']) ? $product['products_model'] : '&mdash;').'</td></tr>
								</tbody></table>
							<td><table class="nostyle"><tbody>
									<tr><td>'.ML_LABEL_BRUTTO.':&nbsp;</td><td class="textright">'
										.$this->simplePrice->addTaxByTaxID($product['products_tax_class_id'])->format(true)
									.'</td></tr>
									<tr><td>'.ML_LABEL_NETTO.':&nbsp;</td><td class="textright">'.$netto.'</td></tr>
								</tbody></table>
							</td>
							'.$this->getAdditionalProductInfo($product['products_id'], $product).'
						</tr>
					';
				}
			}
		}
		if (empty($this->list['categories']) && empty($this->list['products'])) {
			$cols = substr_count($html, '</td>');
			$html .= '
				<tr class="even">
					<td class="center bold" colspan="'.($cols+1).'">'.$this->getEmptyInfoText().'</td>
				</tr>
			';
		}

		$html .= '
			</tbody></table>
		</form>';
		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
function toggleCheckboxClasses(elem, state) {
	if (typeof(elem) == 'string') {
		elem = '#'+elem;
	}
	if (state) {
		$(elem).addClass('checked').removeClass('semichecked').removeClass('unchecked');
	} else {
		$(elem).removeClass('checked').removeClass('semichecked').addClass('unchecked');
	}
}
function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
}

$(document).ready(function() {
	$('form.categoryView input[type="button"]').each(function() {
		$(this).click(function () {
			elem = $(this);
			elemID = $(this).attr('id')
			sCIDs = '';
			sPIDs = '';
			if (elemID == 'selectAll') {
				sC = $('#selectableCategories');
				sP = $('#selectableProducts');
				if (sC.length > 0) {
					sCIDs = sC.val();
				}
				if (sP.length > 0) {
					sPIDs = sP.val();
				}
			}
			jQuery.blockUI(blockUILoading);
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL($this->url, array('kind' => 'ajax', 'ts' => time()), true);?>',
				dataType: 'json',
				//contentType: 'application/json',
				data: {
					'action': $(this).attr('name'),
					'selectableCategories': sCIDs,
					'selectableProducts': sPIDs
				},
				success: function(data) {
					if (data == undefined || data == null) { /* Ein seltsamer Bug. Fast nicht reproduzierbar. */
						if (debugging == true) {
							myConsole.log($(elem).attr('name'), data);
						} else {
							window.location.href = '<?php echo toURL($this->url, true); ?>';
						}
					}
					toggleCheckboxClasses(elemID, data.checked);
					$(elem).attr('name', data.newname);
					if ($(elem).attr('id') == 'selectAll') {
						$('form.categoryView input[type="button"]:not(#selectAll)').each(function () {
							if (data.checked) {
								$(this).attr('name', str_replace('Add', 'Remove', $(this).attr('name')));
							} else {
								$(this).attr('name', str_replace('Remove', 'Add', $(this).attr('name')));
							}
							toggleCheckboxClasses(this, data.checked);
						});
					} else {
						checkedX = 0;
						itemCount = $('form.categoryView input[type="button"]:not(#selectAll)').each(function () {
							if ($(this).hasClass('checked')) {
								++checkedX;
							}
						}).length;
						if (checkedX == 0) {
							toggleCheckboxClasses($('#selectAll').attr('name', str_replace('Remove', 'Add', $('#selectAll').attr('name'))), false);
						} else if (checkedX == itemCount) {
							toggleCheckboxClasses($('#selectAll').attr('name', str_replace('Add', 'Remove', $('#selectAll').attr('name'))), true);
						} else {
							$('#selectAll').attr(
								'name', 
								str_replace('Remove', 'Add', $('#selectAll').attr('name'))
							).removeClass('checked').removeClass('unchecked').addClass('semichecked');
						}
					}
					myConsole.log('It took '+data.timer+' to perform this action.');
					jQuery.unblockUI();
				},
				error: function(xhr, status, error) {
					if (debugging == true) {
						myConsole.log(xhr);
						jQuery.unblockUI();
					} else {
						window.location.href = '<?php echo toURL($this->url, true); ?>';
					}
				}
			});
		});
	});
	
	$('form.categoryView').submit(function() {
		jQuery.blockUI(blockUILoading); 
	});
});
		/*]]>*/</script><?php
		$html .= ob_get_contents();	
		ob_end_clean();

		$leftButtons = $this->getLeftButtons();
		if (empty($leftButtons)) {
			if (count($this->cPathArray) > 1) {
				$leftButtons = $this->cPathArray;
				array_pop($leftButtons);
				$leftButtons = '<a class="ml-button" href="'.toURL($this->url, array('cPath' => implode('_', $leftButtons))).'">'.
					$this->imageHTML(DIR_MAGNALISTER_WS_IMAGES.'folder_back.png', ML_BUTTON_LABEL_BACK).' '. ML_BUTTON_LABEL_BACK . 
				'</a>';
			} else if (((count($this->cPathArray) == 1) && ($this->cPathArray[0] != '0')) || !empty($this->search)) {
				unset($this->url['cPath']);
				$leftButtons = '<a class="ml-button" href="'.toURL($this->url).'">'.
					$this->imageHTML(DIR_MAGNALISTER_WS_IMAGES.'folder_back.png', ML_BUTTON_LABEL_BACK).' '. ML_BUTTON_LABEL_BACK . 
				'</a>';
			}
		}/* else {
			$cPathBack = '&nbsp;';
		}*/
		
		$functionButtons = $this->getFunctionButtons();
		$infoText = $this->getInfoText();
		$html .= '
			<form id="actionForm" name="actionForm" action="'.toURL($this->url, $this->action).'" method="post">
				<input type="hidden" name="timestamp" value="'.time().'"/>
				<table class="actions">
					<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
					<tbody>
						<tr class="firstChild"><td>
							<table><tbody><tr>
								<td class="firstChild">'.$leftButtons.'</td>
								<td><label for="tfSearch">'.ML_LABEL_SEARCH.':</label>
									<input id="tfSearch" name="tfSearch" type="text" value="'.fixHTMLUTF8Entities($this->search, ENT_COMPAT).'"/>
									
									<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_GO.'" name="search_go" id="search_go" /></td>
								<td class="lastChild">'.$functionButtons.'</td>
							</tr></tbody></table>
						</td></tr>
						'.(($infoText != '') ? ('<tr><td colspan="2"><div class="h4">'.ML_LABEL_INFO.'</div>'.$infoText.'</td></tr>') : '').'
					</tbody>
				</table>
				<script type="text/javascript">/*<![CDATA[*/
					$(document).ready(function() {
						$(\'#tfSearch\').keypress(function(event) {
							if (event.keyCode == \'13\') {
								event.preventDefault();
								$(\'#search_go\').click();
								return false;
							}else{
								return true;
							}
						});
						$(\'form#actionForm\').submit(function() {
							jQuery.blockUI(blockUILoading); 
						});
					});
				/*]]>*/</script>
			</form>
		';
		return $html;
	}
	
	public function renderAjaxReply() {
		return json_encode($this->ajaxReply);
	}

	private function imageHTML($fName, $alt = '') {
		$alt = ($alt != '') ? $alt : basename($fName);
		return '<img src="'.$fName.'" alt="'.$alt.'" />';
	}

	/* Wird von erbender Klasse ueberschrieben */
	public function getAdditionalHeadlines() { return ''; }

	/* Wird von erbender Klasse ueberschrieben */
	public function getAdditionalCategoryInfo($cID, $data = false) { return ''; }

	/* Wird von erbender Klasse ueberschrieben */
	public function getAdditionalProductInfo($pID, $data = false) { return ''; }

	/* Wird von erbender Klasse ueberschrieben */
	public function getFunctionButtons() { return ''; }
	
	/* Wird von erbender Klasse ueberschrieben */
	public function getLeftButtons() { return ''; }
	
	/* Wird von erbender Klasse ueberschrieben */
	public function getInfoText() { return ''; }

	/* Wird von erbender Klasse ueberschrieben */
	protected function addFilterToProductsQuery(){}

}
