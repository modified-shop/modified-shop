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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'hood/classes/HoodApiConfigValues.php');

class HoodHelper extends AttributesMatchingHelper {

    private static $instance;
    protected $marketplaceTitle = 'Hood';
    public static function gi() {
        if (self::$instance === null) {
            self::$instance = new HoodHelper();
        }

        return self::$instance;
    }
	public static function processCheckinErrors($result, $mpID) {
		// Empty is ok, the API has a method to fetch the error log later.
	}
	
	public static function hasStore() {
		$store = HoodApiConfigValues::gi()->getHasStore();
		return is_array($store) && isset($store['Info.ShopType']) && ($store['Info.ShopType'] != 'noShop');
	}
	
	public static function loadPriceSettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$config = array(
			'Auction' => array(
				'StartPrice' => array(
					'AddKind' => getDBConfigValue($mp.'.auction.startprice.addkind', $mpId, 'percent'),
					'Factor'  => (float)getDBConfigValue($mp.'.auction.startprice.factor', $mpId, 0),
					'Signal'  => getDBConfigValue($mp.'.auction.startprice.signal', $mpId, ''),
				),
				'BuyItNow' => array(
					'AddKind' => getDBConfigValue($mp.'.auction.buyitnowprice.addkind', $mpId, 'percent'),
					'Factor'  => (float)getDBConfigValue($mp.'.auction.buyitnowprice.factor', $mpId, 0),
					'Signal'  => getDBConfigValue($mp.'.auction.buyitnowprice.signal', $mpId, ''),
					'UseBuyItNow' => getDBConfigValue(array($mp.'.auction.buyitnowprice.active', 'val'), $mpId, false),
				),
			),
			'Fixed' => array(
				'AddKind' => getDBConfigValue($mp.'.fixed.price.addkind', $mpId, 'percent'),
				'Factor'  => (float)getDBConfigValue($mp.'.fixed.price.factor', $mpId, 0),
				'Signal'  => getDBConfigValue($mp.'.fixed.price.signal', $mpId, ''),
				'Group'   => getDBConfigValue($mp.'.fixed.price.group', $mpId, ''),
				'UseSpecialOffer' => getDBConfigValue(array($mp.'.fixed.price.usespecialoffer', 'val'), $mpId, false),
			),
		);
		if (getDBConfigValue('hood.strike.price.group', $mpId, -1) > -1) {
			$config['Strike'] = array(
				'AddKind' => getDBConfigValue($mp.'.strike.price.addkind', $mpId, 'percent'),
				'Factor'  => (float)getDBConfigValue($mp.'.strike.price.factor', $mpId, 0),
				'Signal'  => getDBConfigValue($mp.'.strike.price.signal', $mpId, ''),
				'Group'   => getDBConfigValue($mp.'.strike.price.group', $mpId, ''),
				'UseSpecialOffer' => false
			);
		}
		$config['Auction']['StartPrice']['Group'] = $config['Auction']['BuyItNow']['Group'] =
			getDBConfigValue($mp.'.auction.price.group', $mpId, '');
		
		$config['Auction']['StartPrice']['UseSpecialOffer'] = $config['Auction']['BuyItNow']['UseSpecialOffer'] =
			getDBConfigValue(array($mp.'.auction.price.usespecialoffer', 'val'), $mpId, false);
		
		return $config;
	}
	
	public static function loadQuantitySettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$config = array(
			'Auction' => array(
				'Type'  => getDBConfigValue($mp.'.auction.quantity.type', $mpId, 'lump'),
				'Value' => (int)getDBConfigValue($mp.'.auction.quantity.value', $mpId, 0),
				'MaxQuantity' => (int)getDBConfigValue($mp.'.auction.quantity.maxquantity', $mpId, 999999),
			),
			'Fixed' => array(
				'Type'  => getDBConfigValue($mp.'.fixed.quantity.type', $mpId, 'lump'),
				'Value' => (int)getDBConfigValue($mp.'.fixed.quantity.value', $mpId, 0),
				'MaxQuantity' => (int)getDBConfigValue($mp.'.fixed.quantity.maxquantity', $mpId, 999999),
			),
		);
		return $config;
	}
	
	public static function calcQuantity($dbQuantity, $config) {
		if (!is_array($config) || !isset($config['Type']) || !isset($config['Value'])) {
			return $dbQuantity;
		}
		if (!isset($config['MaxQuantity'])) {
			$config['MaxQuantity'] = 999999;
		}
		switch ($config['Type']) {
			case 'stocksub': {
				$dbQuantity -= $config['Value'];
				break;
			}
			case 'lump': {
				$dbQuantity = $config['Value'];
				break;
			}
		}
		if (($config['MaxQuantity'] > 0) && ($config['Type'] != 'lump')) {
			$dbQuantity = min($dbQuantity, $config['MaxQuantity']);
		}
		$dbQuantity = max($dbQuantity, 0); // make sure it is always >= 0
		return $dbQuantity;
	}
	
	public static function substituteTemplate($mpId, $pID, $template, $substitution) {
        /* {Hook} "HoodSubstituteTemplate": Enables you to extend the Hood Template substitution (e.g. use your own placeholders).<br>
           Variables that can be used:
           <ul><li><code>$mpID</code>: The ID of the marketplace.</li>
               <li><code>$pID</code>: The ID of the product (Table <code>products.products_id</code>).</li>
               <li><code>$template</code>: The Hood product template.</li>
               <li><code>$substitution</code>: Associative array. Keys are placeholders, Values are their content.</li>
           </ul>
         */
		if (($hp = magnaContribVerify('HoodSubstituteTemplate', 1)) !== false) {
			require($hp);
		}
		
		return substituteTemplate($template, $substitution);
	}
	
	public static function getSubstitutePictures($tmplStr, $pID, $imagePath) {
		$undo = ml_extractBase64($tmplStr);
		
		$pics = MLProduct::gi()->getAllImagesByProductsId($pID);
		$i = 1;
		# Ersetze alle Bilder
		foreach($pics as $pic) {
			$tmplStr = str_replace(
				'#PICTURE' . $i . '#',
				"<img src=\"" . $imagePath . $pic . "\" style=\"border:0;\" alt=\"\" title=\"\" />",
				preg_replace(
					'/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(#PICTURE' . $i . '#)/',
					'\1\2\3' . $imagePath . $pic,
					$tmplStr
				)
			);
			++$i;
		}
		# Uebriggebliebene #PICTUREx# loeschen
		$tmplStr = preg_replace('/<[^<]*(src|SRC|href|HREF|rev|REV)\s*=\s*(\'|")#PICTURE\d+#(\'|")[^>]*\/*>/', '', $tmplStr);
		$tmplStr = preg_replace('/#PICTURE\d+#/','', $tmplStr);
		$str = ml_restoreBase64($tmplStr, $undo);
		
		# ggf. leere image tags loeschen
		$str = preg_replace('/<img[^>]*src=(""|\'\')[^>]*>/i', '', $str);
		return $str;
	}

    public function getVarMatchTranslations() {
        $translations = parent::getVarMatchTranslations();
        $translations['mpValue'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_MP_VALUE);
        $translations['attributeChangedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_CHANGED_ON_MP);
        $translations['attributeDeletedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_DELETED_ON_MP);
        $translations['attributeValueDeletedOnMp'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_ATTRIBUTE_VALUE_DELETED_ON_MP);;
        $translations['categoryWithoutAttributesInfo'] = str_replace('%marketplace%', ucfirst($this->marketplaceTitle), ML_GENERAL_VARMATCH_CATEGORY_WITHOUT_ATTRIBUTES_INFO);

        return $translations;
    }

    public function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '') {
#echo print_m(func_get_args(), __METHOD__);

        $data = HoodApiConfigValues::gi()->getVariantConfigurationDefinition($category, null);
#echo print_m($data, __LINE__.' '.__METHOD__);

        if (!is_array($data) || !isset($data['attributes'])) {
            $data = array();
        }

        if (empty($data['attributes'])) {
            $data['attributes'] = array();
        }

        return $data;
    }

    /**
     * @param string $category
     * @param bool $prepare
     * @param bool $getDate Set to <b>TRUE</b> if modification date should be returned
     * @param mixed $additionalData Use this parameter for additional handling if needed.
     * @return array
     *
     * copied from parent class, the only difference is we don't use fixHTMLUTF8Entities for utf8Code
     * (it broke the javascript functionality)
     * and removed the logic that is fatching data from attribute matching tab because we do not have it in the hood
     *
     */
    public function getMPVariations($category, $prepare = false, $getDate = false, $additionalData = null, $customIdentifier = '') {
        $mpData = $this->getAttributesFromMP($category, $additionalData, $customIdentifier);
        $dbData = $this->getPreparedData($category, $prepare, $customIdentifier);
        $tableName = $this->getVariationMatchingTableName();
        $shopAttributes = $this->flatShopVariations();

        // no attribute matching tab for hood
        // load default values from Attributes Matching tab (global matching)
//        $usedGlobal = false;
//        $globalMatching = $this->getCategoryMatching($category, $customIdentifier);
//
//        if (!$this->isProductPrepared($category, $prepare)) {
//            $dbData = $globalMatching;
//            $usedGlobal = true;
//        }

        arrayEntitiesToUTF8($mpData);
        $attributes = array();
        foreach ($mpData['attributes'] as $code => $value) {
            $utf8Code = $this->fixHTMLUTF8Entities($code);
            #$utf8Code = $code;
            $attributes[$utf8Code] = array(
                'AttributeCode' => $utf8Code,
                'AttributeName' => $value['title'],
                'AllowedValues' => isset($value['values']) ? $value['values'] : array(),
                'AttributeDescription' => isset($value['desc']) ? $value['desc'] : '',
                'CurrentValues' => isset($dbData[$utf8Code]) ? $dbData[$utf8Code] : array(),
                'ChangeDate' => isset($value['changed']) ? $value['changed'] : false,
                'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                'DataType' => isset($value['type']) ? $value['type'] : 'text',
            );

            if (isset($value['limit'])) {
                $attributes[$utf8Code]['Limit'] = $value['limit'];
            }

            if (isset($dbData[$utf8Code])) {
                if (!isset($dbData[$utf8Code]['Required'])) {
                    $dbData[$utf8Code]['Required'] = isset($value['mandatory']) ? $value['mandatory'] : true;
                    $dbData[$utf8Code]['Code'] = !empty($value['values']) ? 'attribute_value' : 'freetext';
                    $dbData[$utf8Code]['AttributeName'] = $value['title'];
                }

                $attributes[$utf8Code]['CurrentValues'] = $dbData[$utf8Code];
            }
        }

        if ($this->getNumberOfMaxAdditionalAttributes() > 0) {
            $this->addAdditionalAttributesMP($attributes, $dbData);
        }

        $hasDifferentlyPreparedProducts = false;
        if (!$usedGlobal && !empty($globalMatching)) {
            $this->detectChanges($globalMatching, $attributes);
        } else if (!$prepare && !empty($globalMatching)) {
            // on variation matching tab. Check whether some products are prepared differently
            $hasDifferentlyPreparedProducts = $this->areProductsDifferentlyPrepared($category, $globalMatching, $customIdentifier);
        }

        arrayEntitiesToUTF8($dbData);

        // If there are saved values but they were removed either from Marketplace or Shop, display warning to user.
        if (is_array($dbData)) {
            foreach ($dbData as $utf8Code => $value) {
                $utf8Code = $this->fixHTMLUTF8Entities($utf8Code);
                $isAdditionalAttribute = strpos($utf8Code, 'additional_attribute_') !== false;
                if (!isset($attributes[$utf8Code]) && !$isAdditionalAttribute) {
                    $attributes[$utf8Code] = array(
                        'Deleted' => true,
                        'AttributeCode' => $utf8Code,
                        'AttributeName' => !empty($value['AttributeName']) ? $value['AttributeName'] : $utf8Code,
                        'AllowedValues' => array(),
                        'AttributeDescription' => '',
                        'CurrentValues' => array('Values' => array()),
                        'ChangeDate' => '',
                        'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                        'DataType' => 'text',
                    );
                } else {
                    if ($isAdditionalAttribute && $this->getNumberOfMaxAdditionalAttributes() <= 0) {
                        continue;
                    }

                    $attributes[$utf8Code]['WarningMessage'] = '';
                    $attributes[$utf8Code]['IsDeletedOnShop'] = $this->detectIfAttributeIsDeletedOnShop($shopAttributes, $value, $attributes[$utf8Code]['WarningMessage']);
                }
            }
        }

        if ($getDate) {
            $modificationDate = MagnaDB::gi()->fetchOne(eecho('
					SELECT ModificationDate
					FROM '.$tableName.'
					WHERE MpId = '.$this->mpId.'
						AND MpIdentifier = "'.$category.'"
						AND CustomIdentifier = "'.$customIdentifier.'"
				', false));

            $variationThemeData = array();
            if (!empty($mpData['variation_details'])) {
                $variationThemeData['variation_details'] = $mpData['variation_details'];
                $variationThemeData['variation_theme_code'] = $this->getSavedVariationThemeCode($category, $prepare);
            }

            if (!empty($mpData['variation_details_blacklist'])) {
                $variationThemeData['variation_details_blacklist'] = $mpData['variation_details_blacklist'];
            }

            return array_merge(
                array(
                    'Attributes' => $attributes,
                    'ModificationDate' => $modificationDate,
                    'DifferentProducts' => $hasDifferentlyPreparedProducts,
                ), $variationThemeData
            );
        }

        return $attributes;
    }

    protected function getPreparedData($category, $prepare = false, $customIdentifier = '') {
        $availableCustomConfigs = array();

        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'.$prepare.'"';
        }

        if ($prepare) {

            $availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
                SELECT ShopVariation
                  FROM '.TABLE_MAGNA_HOOD_PROPERTIES.'
                 WHERE     mpID = '.$this->mpId.'
                       AND PrimaryCategory = "'.$category.'"
                      '.$sSQLAnd.'
            ', false), true), true);
        }

        return !$availableCustomConfigs ? array() : $availableCustomConfigs;
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @return array|null
     */
    protected function getPreparedProductsData($category) {
        $dataFromDB = MagnaDB::gi()->fetchArray(eecho('
            SELECT `ShopVariation`
              FROM '.TABLE_MAGNA_HOOD_PROPERTIES.'
             WHERE     mpID = '.$this->mpId.'
                   AND PrimaryCategory = "'.$category.'"
        ', false), true);

        if ($dataFromDB) {
            $result = array();
            foreach ($dataFromDB as $preparedData) {
                if ($preparedData) {
                    $result[] = json_decode($preparedData, true);
                }
            }

            return $result;
        }

        return null;
    }
	
}
