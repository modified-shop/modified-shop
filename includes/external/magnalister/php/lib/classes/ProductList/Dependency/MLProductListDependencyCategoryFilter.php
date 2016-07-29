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
//require_once DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependencyFilter.php';
class MLProductListDependencyCategoryFilter extends MLProductListDependency {
	/**
	 * if count of categories more then $iTreeMaxCount, display just part of cat-tree
	 * @var int $iTreeMaxCount
	 */
	protected $iTreeMaxCount = 200;
	
	/**
	 * shows count of ALL products in select>option
	 * its only a information and dont have relation to already setted filter like active products
	 * @var bool
	 */
	protected $blCountProducts = false;
	
	/**
	 * @var null not initalised
	 * @var array array(catId=>count childs) displays only cats, count childs is needed to display arrow in select>option
	 */
	protected $aCatsFilter = null;
	
	/**
	 * @var array $aCatCache for less sql-querys
	 */
	protected $aCatCache = array();
	
	public function executeAction() {
		return $this;
	}
	
	public function getFilterRightTemplate() {
		return 'select';
	}
	
	/**
	 * get all category-child-ids of given cat-id
	 * @param int $iParentId
	 * @return array
	 */
	protected function getSubCatsIds($iParentId) {
		$aIds = MagnaDB::gi()->fetchArray("SELECT categories_id FROM ".TABLE_CATEGORIES." WHERE parent_id='".$iParentId."'", true);
		foreach ($aIds as $iId) {
			foreach ($this->getSubCatsIds($iId) as $iChild) {
				$aIds[] = $iChild;
			}
		}
		return $aIds;
	}

	public function getKeyTypeFilter() {
		if ((int)$this->getFilterRequest() != 0) {
			return parent::getKeyTypeFilter();
		} else {
			$sKeyType = ((getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id');
			$aResult = MagnaDB::gi()->fetchArray("
				   SELECT p.".$sKeyType."
				     FROM ".TABLE_PRODUCTS." p
				LEFT JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON p.products_id = p2c.products_id
				    WHERE p2c.products_id IS NULL
			", true);
			return array(
				'in' => null,
				'notIn' => $aResult,
			);
		}
	}

	public function manipulateQuery() {
		$iSearch = $this->getFilterRequest();
		if ($iSearch != 'null' && $iSearch !== null) {
			if ($iSearch == 'top') {
				$aSubCats[] = '0';
			} else {
				$aSubCats = $this->getSubCatsIds($iSearch);
				$aSubCats[] = $iSearch;
			}
			$this->getQuery()
				->join(
					array(
						TABLE_PRODUCTS_TO_CATEGORIES,
						'p2c',
						"p.`products_id` = p2c.`products_id` AND p2c.`categories_id` IN('".implode("', '", $aSubCats)."')"
					),
					ML_Database_Model_Query_Select::JOIN_TYPE_INNER
				)
			;
		}
		return $this;
	}

	protected function getDefaultConfig() {
		return array('selectValues' => array('null' => ML_OPTION_FILTER_CATEGORY_ARTICLES_ALL) + $this->getCategoryTree());
	}

	protected function getCategoryTree($iParentId = 0) {
		if (array_key_exists($iParentId, $this->aCatCache)) {
			return $this->aCatCache[$iParentId];
		}
		$this->aCatCache[$iParentId] = array();
		$this->getFilterCategories();
		$sQuery = "
			    SELECT c.categories_id, cd.categories_name".($this->blCountProducts ? ", count(p2c.products_id) as productcount" : '')."
			      FROM ".TABLE_CATEGORIES." c 
			INNER JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON c.categories_id = cd.categories_id AND cd.language_id = '".(int)$_SESSION['languages_id']."'
			           ".($this->blCountProducts ? "LEFT JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON c.categories_id = p2c.categories_id" : '')."
			     WHERE c.parent_id = '".$iParentId."' ".$this->getFilterCategories()."
			           ".($this->blCountProducts ? "GROUP BY c.categories_id" : '')."
			  ORDER BY TRIM(cd.categories_name) ASC
		"; 
		
		// top cat
		if ($iParentId == 0) {
			$this->aCatCache[$iParentId]['top'] = ML_LABEL_CATEGORY_TOP.'&nbsp;&#8628;';
		}
		
		$sPad = str_repeat('&nbsp;', 3);
		foreach (MagnaDB::gi()->fetchArray($sQuery) as $aRow){
			$aChilds = $this->getCategoryTree($aRow['categories_id']);
			$this->aCatCache[$iParentId][$aRow['categories_id']] =
				$sPad
				.fixHTMLUTF8Entities(trim($aRow['categories_name']))
				.($this->blCountProducts ? '&nbsp;&nbsp;('.$aRow['productcount'].')' : '')
				.((count($aChilds) == 0) && $this->haveSubCats($aRow['categories_id']) ? '&nbsp;&#8628;' : '' )
			;
			foreach ($aChilds as $iChild => $sChild) {
				$this->aCatCache[$iParentId][$iChild] = $sPad.$sChild;
			}
		}
		return $this->aCatCache[$iParentId];
	}
	
	protected function haveSubCats($iId){
		return array_key_exists($iId, $this->aCatsFilter) && $this->aCatsFilter[$iId] != null;
	}
	
	protected function getFilterCategories(){
		if ($this->aCatsFilter === null) {
			$this->aCatsFilter = array();
			if (MagnaDB::gi()->fetchOne("SELECT COUNT(categories_id) FROM ".TABLE_CATEGORIES."") > $this->iTreeMaxCount) {
				$iRequestId = (int)$this->getFilterRequest();
				
				//rootcat
				$this->aCatsFilter[] = null;
				
				//current cat
				$this->aCatsFilter[$iRequestId] = null;
				
				//parents till 0
				$iParentId = $iRequestId;
				$sParentQuery = "SELECT parent_id FROM ".TABLE_CATEGORIES." WHERE categories_id = '%s'";
				while (($iParentId = MagnaDB::gi()->fetchOne(sprintf($sParentQuery, $iParentId))) != 0) {
					$this->aCatsFilter[$iParentId] = null;
				}
				
				//siblings of all parents and current
				foreach(MagnaDB::gi()->fetchArray("SELECT categories_id FROM ".TABLE_CATEGORIES." WHERE parent_id IN('".implode("', '", array_keys($this->aCatsFilter))."')") as $aRow){
					$this->aCatsFilter[$aRow['categories_id']] = null;
				}
				
				//childs of request
				foreach(MagnaDB::gi()->fetchArray("SELECT categories_id FROM ".TABLE_CATEGORIES." WHERE parent_id = '".$iRequestId."'") as $aRow){
					$this->aCatsFilter[$aRow['categories_id']] = null;
				}
				
				//count subsubcats
				foreach(MagnaDB::gi()->fetchArray("SELECT parent_id, count(categories_id) AS count FROM ".TABLE_CATEGORIES." WHERE parent_id IN('".implode("', '", array_keys($this->aCatsFilter))."') GROUP BY categories_id") as $aRow){
					$this->aCatsFilter[$aRow['parent_id']] = $aRow['count'];
				}
			}
		}
		return $this->aCatsFilter === array() ? '' : " AND c.categories_id IN('".implode("', '", array_keys($this->aCatsFilter))."')";
	}

}
