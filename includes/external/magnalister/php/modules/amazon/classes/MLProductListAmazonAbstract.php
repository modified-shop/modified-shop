<?php
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MLProductList.php');

abstract class MLProductListAmazonAbstract extends MLProductList {
	
	protected $aPrepareData = array();
	
	protected function getPrepareData($aRow, $sFieldName = null) {
		if (!isset($this->aPrepareData[$aRow['products_id']])) {
			$this->aPrepareData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
				SELECT * 
				FROM ".TABLE_MAGNA_AMAZON_APPLY." 
				WHERE 
					".(
						(getDBConfigValue('general.keytype', '0') == 'artNr')
							? 'products_model=\''.MagnaDB::gi()->escape($aRow['products_model']).'\''
							: 'products_id=\''.$aRow['products_id'].'\''
					)."
					AND mpID = '".$this->aMagnaSession['mpID']."'
			");
			if(empty($this->aPrepareData[$aRow['products_id']])){//not in apply maybe in properties?
				$this->aPrepareData[$aRow['products_id']] = MagnaDB::gi()->fetchRow("
					SELECT * 
					FROM ".TABLE_MAGNA_AMAZON_PROPERTIES." 
					WHERE 
						".(
							(getDBConfigValue('general.keytype', '0') == 'artNr')
								? 'products_model=\''.MagnaDB::gi()->escape($aRow['products_model']).'\''
								: 'products_id=\''.$aRow['products_id'].'\''
						)."
						AND mpID = '".$this->aMagnaSession['mpID']."'
				");
                                                if(!empty($this->aPrepareData[$aRow['products_id']])){
                                                            $this->aPrepareData[$aRow['products_id']]['preparetype'] = 'matched';
                                                }				
			} else {
				$this->aPrepareData[$aRow['products_id']]['preparetype'] = 'applied';
			}
		}
		if($sFieldName === null){
			return $this->aPrepareData[$aRow['products_id']];
		}else{
			return isset($this->aPrepareData[$aRow['products_id']][$sFieldName]) ? $this->aPrepareData[$aRow['products_id']][$sFieldName] : null;
		}
	}
	
	protected function getPreparedStatusIndicator($aRow){
		$aData = $this->getPrepareData($aRow);
		if (empty($aData)) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_AMAZON_LABEL_APPLY_NOT_PREPARED, 9, 9);
		}elseif (
                                            (isset($aData['is_incomplete']) && 'true' == $aData['is_incomplete'])//apply
                                            ||
                                            (isset($aData['asin']) && empty($aData['asin']))//matching
                                    ) {
                                            return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_INCOMPLETE, 9, 9);
                                    }else{
                                            return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_COMPLETE, 9, 9);
                                    }
                        }
	
	protected function getLowestPrice($aRow){
		$fLowestPrice = $this->getPrepareData($aRow, 'lowestprice');
		return $fLowestPrice > 0 ? $this->getPrice()->setPrice($fLowestPrice)->format() : '&mdash;';
	}
}