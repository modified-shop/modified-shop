<?php
require_once DIR_MAGNALISTER_FS . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'TopTen.php';

class AmazonTopTen extends TopTen {

	/**
	 *
	 * @param string $sType  topMainCategory || topProductType || topBrowseNode 
	 * @return array (key=>value)
	 * @throws Exception 
	 */
	public function getTopTenCategories($sField, $aConfig=array()) {
		$sParent = $aConfig[0];
		
		$sUnion = null;
		
		switch ($sField) {
			case 'topMainCategory':{
				$sWhere = "1 = 1";
				$sUnion = null;
				break;
			}
			
			case 'topBrowseNode':{
				$sField = 'topBrowseNode1';
				$sWhere = "1 = 1";
				$sUnion = 'topBrowseNode2';
				break;
			}
			
		}
		if ($sUnion === null) {
			$sSql = "
				select ".$sField." 
				from ".TABLE_MAGNA_AMAZON_APPLY." 
				where ".$sWhere."
				and  mpID = '".$this->iMarketPlaceId."'
				and ".$sField." <> '0'
				group by ".$sField." 
				order by count(*) desc
			";
		} else {
			// if performance problems in this query, get all data and prepare with php
			$sSql="
				select m.".$sField." from
				(
					(
						select f.".$sField."
						from ".TABLE_MAGNA_AMAZON_APPLY." f 
						where ".$sWhere." and mpID = '".$this->iMarketPlaceId."' and ".$sField." <> '0' 
					)
					UNION ALL
					(
						select u.".$sUnion."
						from ".TABLE_MAGNA_AMAZON_APPLY." u 
						where ".$sWhere." and mpID = '".$this->iMarketPlaceId."' and ".$sUnion." <> '0'
					)
				) m
				group by m.".$sField."
				order by count(m.".$sField.") desc
			";
		}
		$aTopTen = MagnaDB::gi()->fetchArray($sSql, true);
		$aOut = array();
		try {
			switch ($sField) {
				case 'topMainCategory':{
					$aCategories = MagnaConnector::gi()->submitRequest(array(
						'ACTION' => 'GetMainCategories',
					));
					$aCategories=$aCategories['DATA'];
					break;
				}
				case 'topBrowseNode1':{
					$aCategories = MagnaConnector::gi()->submitRequest(array(
						'ACTION' => 'GetBrowseNodes',
						'CATEGORY' => $sParent
					));
					$aCategories = $aCategories['DATA'];
					break;
				}
			}
			foreach ($aTopTen as $sCurrent) {
				if (array_key_exists($sCurrent, $aCategories)) {
					$aOut[$sCurrent] = $aCategories[$sCurrent];
				} else {
					MagnaDB::gi()->query("UPDATE ".TABLE_MAGNA_AMAZON_APPLY." set ".$sField." = 0 where ".$sField." = '".$sCurrent."'");//no mpid
					if($sUnion !== null){
						MagnaDB::gi()->query("UPDATE ".TABLE_MAGNA_AMAZON_APPLY." set ".$sUnion." = 0 where ".$sUnion." = '".$sCurrent."'");//no mpid
					}
				}
			}
		} catch (MagnaException $e) {
			echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
		}
        asort($aOut);
		return $aOut;
	}

	public function renderConfigDelete($aDelete = array()) {
		global $_url;

		$html = '';
		if (count($aDelete) > 0) {
			$this->configDelete($aDelete);
			$html .= '<p class="successBox">'.ML_TOPTEN_DELETE_INFO.'</p>';
		}
		$html .= '
			<form method="post" action="'.toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax')).'&amp;tab=delete">
				<select name="delete[]" style="width:100%" multiple="multiple" size="15">';
		foreach ($this->getTopTenCategories('topMainCategory') as $sMainKey => $sMainValue) {
			$browseNodes = $this->getTopTenCategories('topBrowseNode', array($sMainKey));
			
			$html .= '
					<option title="'.ML_AMAZON_CATEGORY.'" value="main:'.$sMainKey.'">'.fixHTMLUTF8Entities($sMainValue).'</option>';
			foreach ($browseNodes as $sBrowseKey => $sBrowseValue) {
				$html .= '
					<option title="'.ML_AMAZON_LABEL_APPLY_BROWSENODES.'" value="browse:'.$sBrowseKey.'">&nbsp;&nbsp;&nbsp;'.fixHTMLUTF8Entities($sBrowseValue).'</option>';
			}
		}
		$html .= '
				</select>
				<button type="submit">'.ML_TOPTEN_DELETE_HEAD.'</button>
			</form>';
		return $html;
	}
	
	public function configCopy() {
		$sSelect = "select products_id, products_model, category from ".TABLE_MAGNA_AMAZON_APPLY." where mpID = '".$this->iMarketPlaceId."'";
		foreach (MagnaDb::gi()->fetchArray($sSelect) as $aRow) {
			$aCategory = unserialize(base64_decode($aRow['category']));
			$sCopySql = "
				UPDATE ".TABLE_MAGNA_AMAZON_APPLY."
				   SET topMainCategory = '".$aCategory['MainCategory']."',
				       topBrowseNode1 = '".$aCategory['BrowseNodes'][0]."',
				       topBrowseNode2 = '".$aCategory['BrowseNodes'][1]."'
				 WHERE mpID = '".$this->iMarketPlaceId."'
				       AND products_id = '".$aRow['products_id']."'
				       AND products_model = '".MagnaDB::gi()->escape($aRow['products_model'])."'
			";
			MagnaDb::gi()->query($sCopySql);
		}
	}

	public function configDelete($aDelete) {
		foreach ($aDelete as $sValue) {
			$aCurrent = explode(':', $sValue);
			switch ($aCurrent[0]) {
				case 'main': {
					MagnaDb::gi()->query("update ".TABLE_MAGNA_AMAZON_APPLY." set topMainCategory = '' where topMainCategory = '".$aCurrent[1]."'");
					break;
				}
				case 'browse': {
					MagnaDb::gi()->query("update ".TABLE_MAGNA_AMAZON_APPLY." set topBrowseNode1 = '' where topBrowseNode1 = '".$aCurrent[1]."'");
					MagnaDb::gi()->query("update ".TABLE_MAGNA_AMAZON_APPLY." set topBrowseNode2 = '' where topBrowseNode2 = '".$aCurrent[1]."'");
					break;
				}
			}
		}
	}
	
	/** new **/
	protected function getTableName() {
		return TABLE_MAGNA_AMAZON_APPLY;
	}
	protected function getResettableCategoryDefinition() {
		return array();
	}
	protected function getResettableCategoryDescription() {
		return array();
	}
	
	public static function renderConfigForm($args, &$value = '') {
		return self::runRenderConfigForm(new self(), __METHOD__, $args, $value);
	}
}
