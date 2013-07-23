<?php
require_once DIR_MAGNALISTER.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'TopTen.php';
class EbayTopTen extends TopTen{
	/**
	 *
	 * @param string $sType  topPrimaryCategory || topSecondaryCategory || topStoreCategory || topStoreCategory2
	 * @return array (key=>value)
	 * @throws Exception 
	 */
	public function getTopTenCategories($sType,$aConfig=array()){
		$blStoreCat = substr($sType,0,16) == 'topStoreCategory';
		if ($blStoreCat) {
			try {
				$aStoreData = MagnaConnector::gi()->submitRequest(array('ACTION' => 'HasStore'));
			} catch (MagnaException $e) {
				echo print_m($e->getErrorArray(), 'Error');
			}
			if(!$aStoreData['DATA']['Answer']=='True'){
				throw new Exception('noStore');
			}
		}
		$sTopTenCatSql = "
            SELECT DISTINCT ".$sType."
            FROM ".TABLE_MAGNA_EBAY_PROPERTIES." 
            WHERE ".$sType." <> 0 and mpID = '".$this->iMarketPlaceId."'
            GROUP BY ".$sType." 
            ORDER BY count( `".$sType."` ) DESC
            ".(
					(int)getDBConfigValue('ebay.topten', $this->iMarketPlaceId) != 0
					? "LIMIT ".(int)getDBConfigValue('ebay.topten', $this->iMarketPlaceId)
					:""
			)
        ;
        $aTopTenCatSql = MagnaDB::gi()->fetchArray($sTopTenCatSql, true);
		$aTopTenCatIds = array();
		foreach ($aTopTenCatSql as $iCatId) {
			$aTopTenCatIds[$iCatId] = geteBayCategoryPath($iCatId,$blStoreCat);
			if ('<span class="invalid">'.ML_LABEL_INVALID.'</span>' == $aTopTenCatIds[$iCatId]) {
				unset($aTopTenCatIds[$iCatId]);
				MagnaDB::gi()->query("UPDATE ".TABLE_MAGNA_EBAY_PROPERTIES." set ".$sType."=0 where ".$sType."='".$iCatId."'");//no mpid
			}
		}
		return $aTopTenCatIds;
	}
	public function configCopy(){
		$sCopySql = "
			update ".TABLE_MAGNA_EBAY_PROPERTIES."
			set 
				topPrimaryCategory = primaryCategory,
				topSecondaryCategory = secondaryCategory,
				topStoreCategory1 = storeCategory,
				topStoreCategory2 = storeCategory2
			where 
				mpID = '".$this->iMarketPlaceId."'
		";
		MagnaDb::gi()->query($sCopySql);
	}
	public function configDelete($aDelete){
		foreach ($aDelete as $sKey => $aValue) {
			if(in_array($sKey, array('topPrimaryCategory', 'topSecondaryCategory', 'topStoreCategory1', 'topStoreCategory2'))){
				$sIn = '(';
				foreach($aValue as $iValue){
					$sIn .= ((int)$iValue).', ';
				}
				$sIn = substr($sIn, 0, -2).')';
				$sQuery = "update ".TABLE_MAGNA_EBAY_PROPERTIES." set ".$sKey." = '' where ".$sKey." in ".$sIn;
				MagnaDb::gi()->query($sQuery);
			}
		}
	}


	public function renderConfigDelete($aDelete = array()) {
		global $_url;
		ob_start();
		if(count($aDelete)>0){
			$this->configDelete($aDelete);
			?><p class="successBox"><?php echo ML_TOPTEN_DELETE_INFO ?></p><?php
		}
		$aCats = array();
		foreach(array(
			'topPrimaryCategory'	=> ML_EBAY_PRIMARY_CATEGORY, 
			'topSecondaryCategory'	=> ML_EBAY_SECONDARY_CATEGORY, 
			'topStoreCategory1'		=> ML_EBAY_STORE_CATEGORY, 
			'topStoreCategory2'		=> ML_EBAY_SECONDARY_STORE_CATEGORY
		) as $sType => $sName){
			try{
				$aCats[$sName] = array(
					'type' => $sType,
					'data' => $this->getTopTenCategories($sType)
				);
			}catch(Exception $oEx){
				//do nothing
			}
		}
		?>
			<form method="post" action="<?php echo toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax'), true)?>&tab=delete">
				<p><?php echo ML_TOPTEN_DELETE_DESC ?></p>
				<dl>
					<?php
						foreach($aCats as $sName => $aTopTenCatIds){
							?>
								<dt><?php echo $sName ?></dt>
								<dd>
									<select name="delete[<?php echo $aTopTenCatIds['type'] ?>][]" style="width:100%" multiple="multiple" size="5">
										<?php
											foreach ($aTopTenCatIds['data'] as $sKey => $sValue) {
												?><option value="<?php echo $sKey ?>"><?php echo $sValue ?></option>;<?php
											}
										?>
									</select>
								</td>
							<?php
						}
					?>
				</dl>
				<button type="submit"><?php echo ML_TOPTEN_DELETE_HEAD ?></button>
			</form>
		<?php
		$sOut = ob_get_contents();
		ob_end_clean();
		return $sOut;
	}
}
