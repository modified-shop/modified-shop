<?php
require_once DIR_MAGNALISTER . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'TopTen.php';

class AmazonTopTen extends TopTen {

	/**
	 *
	 * @param string $sType  topMainCategory || topProductType || topBrowseNode 
	 * @return array (key=>value)
	 * @throws Exception 
	 */
	public function getTopTenCategories($sField, $aConfig=array()) {
		$sParent = $aConfig[0];
		$sParentParent = $aConfig[1];
		switch ($sField) {
			case 'topMainCategory':{
				$sWhere = "1 = 1";
				$sUnion = null;
				break;
			}
			case 'topProductType':{
				$sWhere = "topMainCategory = '".$sParent."'";
				$sUnion = null;
				break;
			}
			case 'topBrowseNode':{
				$sField = 'topBrowseNode1';
				$sWhere = "topProductType = '".$sParent."'";
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
		}else{
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
				case 'topProductType':{
					$aCategories = MagnaConnector::gi()->submitRequest(array(
						'ACTION' => 'GetProductTypesAndAttributes',
						'CATEGORY' => $sParent
					));
					$aCategories = $aCategories['DATA']['ProductTypes'];
					break;
				}
				case 'topBrowseNode1':{
					$aCategories = MagnaConnector::gi()->submitRequest(array(
						'ACTION' => 'GetBrowseNodes',
						'CATEGORY' => $sParentParent,
						'SUBCATEGORY' => $sParent
					));
					$aCategories = $aCategories['DATA'];
					break;
				}
			}
			foreach($aTopTen as $sCurrent){
				if(array_key_exists($sCurrent, $aCategories)) {
					$aOut[$sCurrent] = $aCategories[$sCurrent];
				}else{
					MagnaDB::gi()->query("UPDATE ".TABLE_MAGNA_AMAZON_APPLY." set ".$sField." = 0 where ".$sField." = '".$sCurrent."'");//no mpid
					if($sUnion !== null){
						MagnaDB::gi()->query("UPDATE ".TABLE_MAGNA_AMAZON_APPLY." set ".$sUnion." = 0 where ".$sUnion." = '".$sCurrent."'");//no mpid
					}
				}
			}
		} catch (MagnaException $e) {
			echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
		}
		return $aOut;
	}

	public function renderConfigDelete($aDelete = array()) {
		global $_url;
		ob_start();
		if (count($aDelete)>0 ) {
			$this->configDelete($aDelete);
			?><p class="successBox"><?php echo ML_TOPTEN_DELETE_INFO ?></p><?php
		}
		?>
			<form method="post" action="<?php echo toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax'), true)?>&tab=delete">
				<select name="delete[]" style="width:100%" multiple="multiple" size="15">
					<?php foreach($this->getTopTenCategories('topMainCategory') as $sMainKey=>$sMainValue){ ?>
						<option title="<?php echo ML_AMAZON_CATEGORY ?>" value="main:<?php echo $sMainKey ?>"><?php echo $sMainValue ?></option>
						<?php foreach($this->getTopTenCategories('topProductType', array($sMainKey)) as $sTypeKey => $sTypeValue){ ?>
							<option title="<?php echo ML_AMAZON_PRODUCTGROUP ?>" value="type:<?php echo $sTypeKey ?>">&nbsp;&nbsp;<?php echo $sTypeValue ?></option>
							<?php foreach($this->getTopTenCategories('topBrowseNode', array($sTypeKey, $sMainKey)) as $sBrowseKey => $sBrowseValue){ ?>
								<option title="<?php echo ML_AMAZON_LABEL_APPLY_BROWSENODES ?>" value="browse:<?php echo $sBrowseKey ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $sBrowseValue ?></option>
							<?php } ?>
						<?php } ?>
					<?php } ?>
				</select>
				<button type="submit"><?php echo ML_TOPTEN_DELETE_HEAD ?></button>
			</form>
		<?php
		$sOut = ob_get_contents();
		ob_end_clean();
		return $sOut;
	}
	public function configCopy() {
		$sSelect = "select products_id, products_model, category from ".TABLE_MAGNA_AMAZON_APPLY." where mpID = '".$this->iMarketPlaceId."'";
		foreach (MagnaDb::gi()->fetchArray($sSelect) as $aRow) {
			$aCategory = unserialize(base64_decode($aRow['category']));
			$sCopySql = "
				update ".TABLE_MAGNA_AMAZON_APPLY."
				set 
					topMainCategory = '".$aCategory['MainCategory']."',
					topProductType = '".$aCategory['ProductType']."',
					topBrowseNode1 = '".$aCategory['BrowseNodes'][0]."',
					topBrowseNode2 = '".$aCategory['BrowseNodes'][1]."'
				where 
					mpID = '".$this->iMarketPlaceId."'
					and products_id = '".$aRow['products_id']."'
					and products_model = '".MagnaDB::gi()->escape($aRow['products_model'])."'
			";
			MagnaDb::gi()->query($sCopySql);
		}
	}

	public function configDelete($aDelete) {
		foreach($aDelete as $sValue){
			$aCurrent = explode(':', $sValue);
			switch ($aCurrent[0]) {
				case 'main':{
					MagnaDb::gi()->query("update ".TABLE_MAGNA_AMAZON_APPLY." set topMainCategory = '' where topMainCategory = '".$aCurrent[1]."'");
				}
				case 'type':{
					MagnaDb::gi()->query("update ".TABLE_MAGNA_AMAZON_APPLY." set topProductType = '' where topProductType = '".$aCurrent[1]."'");
				}
				case 'browse':{
					MagnaDb::gi()->query("update ".TABLE_MAGNA_AMAZON_APPLY." set topBrowseNode1 = '' where topBrowseNode1 = '".$aCurrent[1]."'");
					MagnaDb::gi()->query("update ".TABLE_MAGNA_AMAZON_APPLY." set topBrowseNode2 = '' where topBrowseNode2 = '".$aCurrent[1]."'");
				}
			}
		}
	}
}
