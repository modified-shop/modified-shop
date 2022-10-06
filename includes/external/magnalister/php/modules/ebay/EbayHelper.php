<?php
/*
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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

class EbayHelper extends AttributesMatchingHelper
{
	protected static $priceConfigs = array();
	protected static $marketplaces = array();
	protected $marketplaceTitle = 'eBay';

	protected $numberOfMaxAdditionalAttributes = self::UNLIMITED_ADDITIONAL_ATTRIBUTES;

	private static $instance;
/**
 * 
 * @return EbayHelper
 */
	public static function gi()
	{
		if (self::$instance === null){
			self::$instance = new EbayHelper();
		}

		return self::$instance;
	}

	protected static function getMarketplaceById($mpId) {
		if (!array_key_exists($mpId, self::$marketplaces)) {
			self::$marketplaces[$mpId] = magnaGetMarketplaceByID($mpId);
		}
		return self::$marketplaces[$mpId];
	}

	public static function getPriceSettingsByListingType($mpId, $listingType){
		if ($listingType == 'Chinese') {
			$priceTypes = array('chinese.buyitnow', 'chinese');
		} else { //StoresFixedPrice, FixedPriceItem
			$priceTypes = array('fixed');
			if ($sStrikePriceKind = getDBConfigValue('ebay.strike.price.kind', $mpId, 'DontUse') != 'DontUse') {
				$priceTypes[] = 'strike';
			}

		}
		$priceConfigs = array();
		foreach ($priceTypes as $priceType) {
			$priceConfig = EbayHelper::getPriceSettingsByPriceType($mpId, $priceType);
			if ($priceConfig['active']) {
				unset($priceConfig['active']);
				$priceConfigs[$priceType] = $priceConfig;
			}
		}
		return $priceConfigs;
	}
	public static function getQuantitySettingsByListingType($mpId, $listingType){
		$currency = getDBConfigValue('ebay.currency', $mpId);
		if ($listingType == 'Chinese') {
			return array (
				'Type' => 'stocksub',
				'Value' => 0,
				'MaxQuantity' => 1,
			);
		}else{
			$maxQuantity = (int)getDBConfigValue('ebay.maxquantity', $mpId, 0);
			$maxQuantity = (0 == $maxQuantity) ? PHP_INT_MAX : $maxQuantity;
			return array (
				'Type' => getDBConfigValue('ebay.fixed.quantity.type', $mpId),
				'Value' => (int)getDBConfigValue('ebay.fixed.quantity.value', $mpId),
				'MaxQuantity' => $maxQuantity,
				'Currency' => $currency
			);
		}
	}

	public static function getPriceSettingsByPriceType($mpId, $priceType) {
		$marketplace = self::getMarketplaceById($mpId);
		if (
			!array_key_exists($mpId, self::$priceConfigs)
			|| !array_key_exists($priceType, self::$priceConfigs[$mpId])
		) {
			foreach (array(
				array('key' => array('active', 'val'),			'default' => true),
				array('key' => 'AddKind',						'default' => 'percent'),
				array('key' => 'Factor',						'default' => 0),
				array('key' => 'Signal',						'default' => ''),
				array('key' => 'Group',							'default' => ''),
				array('key' => array('UseSpecialOffer', 'val'), 'default' => false),
				array('key' => 'Currency',						'default' => null),
				array('key' => 'ConvertCurrency',				'default' => null)
			) as $config) {
				if (is_array($config['key'])) {
					$configKey = array(
						$marketplace.'.'.$priceType.'.price.'.strtolower($config['key'][0]),
						strtolower($config['key'][1])
					);
					$priceKey = $config['key'][0];
				} else {
					$configKey = strtolower($marketplace.'.'.$priceType.'.price.'.$config['key']);
					// currency: same for all price types
					if (('Currency' == $config['key']) || ('ConvertCurrency' == $config['key'])) {
						$configKey = strtolower($marketplace.'.'.$config['key']);
					}
					$priceKey = $config['key'];
				}
					self::$priceConfigs[$mpId][$priceType][$priceKey] = getDBConfigValue(
						$configKey,
						$mpId,
						$config['default']
					);
			}
			// for strike prices, the rules are slightly different:
			// 'SpecialPrice' means, configure like main price, except special price
			if ('strike' == $priceType) {
				if ('SpecialPrice' == getDBConfigValue('ebay.strike.price.kind', $mpId, 'DontUse')) {
					self::$priceConfigs[$mpId]['strike']['AddKind'] = getDBConfigValue('ebay.fixed.price.addkind', $mpId, '0');
					self::$priceConfigs[$mpId]['strike']['Factor'] = getDBConfigValue('ebay.fixed.price.factor', $mpId, '0');
					self::$priceConfigs[$mpId]['strike']['Signal'] = getDBConfigValue('ebay.fixed.price.signal', $mpId, '0');
					self::$priceConfigs[$mpId]['strike']['Group'] = getDBConfigValue('ebay.fixed.price.group', $mpId, '0');
					if (is_array(self::$priceConfigs[$mpId]['fixed'])) {
						self::$priceConfigs[$mpId]['fixed']['UseSpecialOffer'] = 1;
					}
				}
			}
		}
		return self::$priceConfigs[$mpId][$priceType]['active'] ? self::$priceConfigs[$mpId][$priceType] : array();
	}

	/*
	 * return array - matched details (brand, mpn, ean)
	 */
	public static function getProductListingDetailsFromProduct($iProductId, $iLang) {
		global $_MagnaSession;

		MLProduct::gi()->setLanguage($iLang);

		// match manufacturer part number
		$aManufacturerPartNumber = getDBConfigValue('ebay.listingdetails.mpn.dbmatching.table', $_MagnaSession['mpID'], false);
		if (is_array($aManufacturerPartNumber) && !empty($aManufacturerPartNumber['column']) && !empty($aManufacturerPartNumber['table'])) {
			$sPidAlias = getDBConfigValue('ebay.listingdetails.mpn.dbmatching.alias', $_MagnaSession['mpID']);
			if (empty($sPidAlias)) {
				$sPidAlias = 'products_id';
			}
			MLProduct::gi()->setDbMatching('ManufacturerPartNumber', array (
				'Table'  => $aManufacturerPartNumber['table'],
				'Column' => $aManufacturerPartNumber['column'],
				'Alias'  => $sPidAlias,
			));
		}

		// match ean
		$aEAN = getDBConfigValue('ebay.listingdetails.ean.dbmatching.table', $_MagnaSession['mpID'], false);
		if (is_array($aEAN) && !empty($aEAN['column']) && !empty($aEAN['table'])) {
			$sPidAlias = getDBConfigValue('ebay.listingdetails.ean.dbmatching.alias', $_MagnaSession['mpID']);
			if (empty($sPidAlias)) {
				$sPidAlias = 'products_id';
			}
			MLProduct::gi()->setDbMatching('EAN', array (
				'Table'  => $aEAN['table'],
				'Column' => $aEAN['column'],
				'Alias'  => $sPidAlias,
			));
		}

		// get product
		$aProduct = MLProduct::gi()->getProductById($iProductId);

		// set listing details
		$aListingDetails = array(
			'Brand' => $aProduct['Manufacturer'],
			'MPN' => $aProduct['ManufacturerPartNumber'],
			'EAN' => $aProduct['EAN'],
		);

		// if brand is empty try to get it from config
		$sAlternativeBrand = getDBConfigValue('ebay.listingdetails.manufacturerfallback', $_MagnaSession['mpID'], false);
		if (   empty($aListingDetails['Brand'])
			&& $sAlternativeBrand !== false
		) {
			$aListingDetails['Brand'] = $sAlternativeBrand;
		}

		/* {Hook} "EbayHelper_getProductListingDetailsFromProduct": Is called before the data of the product in <code>$aListingDetails</code> will return.
			Useful to manipulate some of the data.
			Variables that can be used:
			<ul>
				<li>$aListingDetails: The data of a product for the preparation</li>
				<li>$_MagnaSession: magna session data (marketplace, mpID etc.)</li>
			</ul>
		*/
		if (($hp = magnaContribVerify('EbayHelper_getProductListingDetailsFromProduct', 1)) !== false) {
			require($hp);
		}

		return $aListingDetails;
	}

	// add mobile description with the required tags within the main description
	// (when uploading product)
	public static function appendMobileDescription(&$mainDesc, $mobileDesc) {
		if (strpos($mainDesc, '#MOBILEDESCRIPTION#') === false) return;
		// if placeholder is used, but no content for it, remove placeholder
		if (empty($mobileDesc)) {
			$mainDesc = str_replace('#MOBILEDESCRIPTION#', '', $mainDesc);
			return;
		}
		$mobileDesc = trim(strip_tags($mobileDesc, '<ol></ol><ul></ul><li></li><br><br/><br />'));
		if (empty($mobileDesc)) {
			$mainDesc = str_replace('#MOBILEDESCRIPTION#', '', $mainDesc);
			return;
		}
		$mainDesc = str_replace('#MOBILEDESCRIPTION#', '<div vocab="http://schema.org/" typeof="Product"><span property="description">'
			.$mobileDesc
			.'</span></div>', $mainDesc);
	}

	// if mobile template is in use, and a placeholder is used in mobile template
	// check if the same is also used in main template, and remove it from there
	// Additionally, remove PICTURE placeholders from the mobile template (not allowed)
	public static function filterDoubleContentFromDescTemplate(&$mainDesc, &$mobileDesc) {
		if (strpos($mainDesc, '#MOBILEDESCRIPTION#') === false) return;
		if (empty($mobileDesc)) return;
		$aPlaceholders = array (
			'#TITLE#',
			'#ARTNR#',
			'#PID#',
			'#PRICE#',
			'#VPE#',
			'#BASEPRICE#',
			'#SHORTDESCRIPTION#',
			'#DESCRIPTION#',
			'#WEIGHT#');
		foreach ($aPlaceholders as $sPlaceholder) {
			if (   (strpos($mainDesc,   $sPlaceholder) !== false)
			    && (strpos($mobileDesc, $sPlaceholder) !== false) ) {
				$mainDesc = str_replace($sPlaceholder, '', $mainDesc);
			}
		}
		if (strpos($mobileDesc, '#PICTURE') !== false) {
			$mobileDesc = preg_replace('/#PICTURE(\d+)#/', '', $mobileDesc);
		}
	}

	public function convertOldAttributes($oldAttributes, $category)
	{
		$newAttributes = array();
		foreach ($oldAttributes as $key => $oldAttribute) {
			if (is_array($oldAttribute) && !empty($oldAttribute['select']) && ($oldAttribute['select'] == -1)) {
				continue;
			}

			$utf8Key = fixHTMLUTF8Entities($key);
			if (is_array($oldAttribute) && !empty($oldAttribute) && empty($oldAttribute['select']) && empty($oldAttribute['text'])) {
				$values = array_map(function ($oldValue) {
					return html_entity_decode($oldValue, ENT_NOQUOTES, 'UTF-8');
				}, $oldAttribute);

				$newAttributes[$utf8Key] = array(
					"Code" => "attribute_value",
					"Kind" => "Matching",
					"Required" => false,
					'DataType' => 'multiSelectAndText',
					"AttributeName" => $utf8Key,
					'CategoryId' => $category,
					"Values" => $values,
					"Error" => false,
				);
			} else if (is_array($oldAttribute) && !empty($oldAttribute['select'])) {
				$newAttributes[$utf8Key] = array(
					"Code" => ($oldAttribute['select'] == -6) ? "freetext" : "attribute_value",
					"Kind" => ($oldAttribute['select'] == -6) ? "FreeText" : "Matching",
					"Required" => false,
					'DataType' => 'selectAndText',
					"AttributeName" => $utf8Key,
					'CategoryId' => $category,
					"Values" => html_entity_decode(($oldAttribute['select'] == -6) ? $oldAttribute['text'] : $oldAttribute['select']),
					"Error" => false,
				);
			} else if (is_string($oldAttribute) && !empty($oldAttribute)) {
				$newAttributes[$utf8Key] = array(
					"Code" => "freetext",
					"Kind" => "FreeText",
					"Required" => false,
					'DataType' => 'text',
					"AttributeName" => $utf8Key,
					'CategoryId' => $category,
					"Values" => html_entity_decode($oldAttribute),
					"Error" => false,
				);
			}
		}

		return $newAttributes;
	}

	public function uniformShopVariation($availableCustomConfigsDB, $primaryCategory, $secondaryCategory = null)
	{
		$availableCustomConfigs = array();
		if (!empty($availableCustomConfigsDB['ShopVariation'])) {
			$availableCustomConfigs = $availableCustomConfigsDB['ShopVariation'];
		} else if (!empty($availableCustomConfigsDB['1'])) {
			$availableCustomConfigs = $this->convertOldAttributes($availableCustomConfigsDB['1'], $primaryCategory);
			if (!empty($availableCustomConfigsDB['2']) && !empty($secondaryCategory)) {
				$availableSecondaryCategoryCustomConfigs = $this->convertOldAttributes($availableCustomConfigsDB['2'], $secondaryCategory);
				foreach ($availableSecondaryCategoryCustomConfigs as $attributeKey => $attribute) {
					if (!empty($availableCustomConfigs[$attributeKey])) {
						continue;
					}

					$availableCustomConfigs[$attributeKey] = $attribute;
				}
			}
		}

		$selectedCategories = array($primaryCategory, $secondaryCategory);
		$aOut = array();
		foreach ($availableCustomConfigs as $attributeCode => $attribute) {
			$isAdditionalAttribute = strpos($attributeCode, 'additional_attribute_') === 0;
			if ($isAdditionalAttribute || in_array($attribute['CategoryId'], $selectedCategories)) {
				$aOut[$attributeCode] = $attribute;
			}
		}
		return $aOut;
	}

	protected function isProductPrepared($category, $prepare = false)
	{
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sKeyType = 'products_model';
		} else {
			$sKeyType = 'products_id';
		}

		return MagnaDB::gi()->recordExists(TABLE_MAGNA_EBAY_PROPERTIES, array(
			'MpId' => $this->mpId,
			$sKeyType => $prepare,
			'PrimaryCategory' => $category,
		));
	}

	public function getPreparedData($category, $prepare = false, $customIdentifier = '')
	{
		if (!$prepare) {
			return false;
		}

		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sSQLAnd = ' AND products_model = "'.MagnaDB::gi()->escape($prepare).'"';
		} else {
			$sSQLAnd = ' AND products_id = "' . $prepare . '"';
		}

		$availableCustomConfigs = false;
		$secondaryCategory = !empty($_POST['SecondarySelectValue']) ? $_POST['SecondarySelectValue'] : null;
		if (empty($secondaryCategory)) {
			$secondaryCategory = !empty($_POST['SecondaryCategory']) ? $_POST['SecondaryCategory'] : null;
		}

		if ($prepare) {
			$availableCustomConfigsDB = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT ItemSpecifics
				FROM ' . TABLE_MAGNA_EBAY_PROPERTIES . '
				WHERE MpId = ' . $this->mpId . '
					AND PrimaryCategory = "' . $category . '"
					' . $sSQLAnd . '
			', false), true), true);

			$availableCustomConfigs = $this->uniformShopVariation($availableCustomConfigsDB, $category, $secondaryCategory);
		}


		return $availableCustomConfigs;
	}

	protected function getPreparedProductsData($category)
	{
		$dataFromDB = MagnaDB::gi()->fetchArray(eecho('
				SELECT DISTINCT `ItemSpecifics`, PrimaryCategory, SecondaryCategory
				FROM ' . TABLE_MAGNA_EBAY_PROPERTIES . '
				WHERE mpID = ' . $this->mpId . '
					AND (
						PrimaryCategory = "' . $category . '" OR
						SecondaryCategory = "' . $category . '"
					)
			', false), true);

		if ($dataFromDB) {
			$result = array();
			foreach ($dataFromDB as $preparedData) {
				if ($preparedData) {
					$decodedItemSpecifics = json_decode($preparedData['ItemSpecifics'], true);
					$itemSpecifics = $this->uniformShopVariation($decodedItemSpecifics, $preparedData['PrimaryCategory'], $preparedData['SecondaryCategory']);
					if (!empty($itemSpecifics)) {
						$result[] = $itemSpecifics;
					}
                    unset($decodedItemSpecifics);
					unset($itemSpecifics);
				}
			}

			return $result;
		}

		return null;
	}

	public function getCategoryMatching($category, $customIdentifier = '')
	{
		$tableName = $this->getVariationMatchingTableName();
		$secondaryCategory = !empty($_POST['SecondarySelectValue']) ? $_POST['SecondarySelectValue'] : null;
		if (empty($secondaryCategory)) {
			$secondaryCategory = !empty($_POST['SecondaryCategory']) ? $_POST['SecondaryCategory'] : null;
		}

		$matching = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT ShopVariation
				FROM ' . $tableName . '
				WHERE MpId = ' . $this->mpId . '
					AND MpIdentifier = "' . $category . '"
			', false)), true);

		if (!empty($secondaryCategory)) {
			$secondaryCategoryMatching = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT ShopVariation
				FROM ' . $tableName . '
				WHERE MpId = ' . $this->mpId . '
					AND MpIdentifier = "' . $secondaryCategory . '"
			', false)), true);
			$secondaryCategoryMatching = is_array($secondaryCategoryMatching) ? $secondaryCategoryMatching : array();
			foreach ($secondaryCategoryMatching as $attributeCode => $attribute) {
				if (!empty($matching[$attributeCode])) {
					continue;
				}

				$matching[$attributeCode] = $attribute;
			}
		}

		return $matching ? $matching : array();
	}

    public function getConfigProductListingDetails($aMarketplaceAttributeCodes, $iCategoryId) {
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetConfigItemSpecProductListingDetailsFieldNames',
                'MARKETPLACEID' => $this->mpId,
            ));

            if (!empty($result['DATA'])) {
                $aPossibleFieldNames = $result['DATA'];
            }

        } catch (MagnaException $e) {
            return array();
        }

        $aFieldNames = array();

        foreach ($aPossibleFieldNames as $sKey => $aPossibleName) {
            foreach ($aPossibleName as $sName) {
                $bResult = array_search($sName, $aMarketplaceAttributeCodes);
                if ($bResult !== false) {
                    $aFieldNames[$sKey] = $aMarketplaceAttributeCodes[$bResult];
                }
            }
        }

        //List of all manufacturers
        $aManufacturers = MagnaDB::gi()->fetchArray('
            SELECT manufacturers_id AS ID, manufacturers_name AS Name
              FROM '.TABLE_MANUFACTURERS.'
             WHERE manufacturers_id <> 0
          ORDER BY manufacturers_name ASC
        ');
        $aManufacturerMatchValues = array();
        foreach ($aManufacturers as $aManufacturer) {
            $aManufacturerMatchValues[] = array(
                'Shop' => array(
                    'Key' => $aManufacturer['ID'],
                    'Value' => $aManufacturer['Name'],
                ),
                'Marketplace' => array(
                    'Key' => 'manual',
                    'Value' => $aManufacturer['Name'],
                    'Info' => $aManufacturer['Name'],
                ),
            );
        }

        $aMatchEan = array();
        $aMatchMpn = array();
        $aMatchBrand = array(
            $aFieldNames['Brand'] => array(
                'Code' => 'manufacturer',
                'Kind' => 'Matching',
                'Required' => true,
                'AttributeName' => $aFieldNames['Brand'],
                'CategoryId' => $iCategoryId,
                'Values' => $aManufacturerMatchValues,
                'Error' => false,
            )
        );

        $aEAN = getDBConfigValue('ebay.listingdetails.ean.dbmatching.table', $this->mpId, false);
        if (is_array($aEAN) && !empty($aEAN['column']) && !empty($aEAN['table'])) {
            $sPidAlias = getDBConfigValue('ebay.listingdetails.ean.dbmatching.alias', $this->mpId, false);
            if (empty($sPidAlias)) {
                $sPidAlias = 'products_id';
            }
            $aMatchEan = array(
                $aFieldNames['EAN'] => array(
                    'Code' => 'database_value',
                    'Kind' => 'Matching',
                    'Required' => true,
                    'AttributeName' => 'EAN',
                    'CategoryId' => $iCategoryId,
                    'Values' => array(
                        'Table' => $aEAN['table'],
                        'Column' => $aEAN['column'],
                        'Alias' => $sPidAlias,
                    ),
                    'Error' => false,
                )
            );
        }

        $aManufacturerPartNumber = getDBConfigValue('ebay.listingdetails.mpn.dbmatching.table', $this->mpId, false);
        if (is_array($aManufacturerPartNumber) && !empty($aManufacturerPartNumber['column']) && !empty($aManufacturerPartNumber['table'])) {
            $sPidAlias = getDBConfigValue('ebay.listingdetails.mpn.dbmatching.alias', $this->mpId, false);
            if (empty($sPidAlias)) {
                $sPidAlias = 'products_id';
            }
            $aMatchMpn = array(
                $aFieldNames['MPN'] => array(
                    'Code' => 'database_value',
                    'Kind' => 'Matching',
                    'Required' => true,
                    'AttributeName' => $aFieldNames['MPN'],
                    'CategoryId' => $iCategoryId,
                    'Values' => array(
                        'Table' => $aManufacturerPartNumber['table'],
                        'Column' => $aManufacturerPartNumber['column'],
                        'Alias' => $sPidAlias,
                    ),
                    'Error' => false,
                )
            );
        }

        return array_merge($aMatchEan, $aMatchBrand, $aMatchMpn);
    }

	public function getMPVariations($category, $prepare = false, $getDate = false, $additionalData = null, $customIdentifier = '')
	{
		$mpData = $this->getAttributesFromMP($category, $additionalData, $customIdentifier);
		$dbData = $this->getPreparedData($category, $prepare, $customIdentifier);
		$tableName = $this->getVariationMatchingTableName();
		$shopAttributes = $this->flatShopVariations();

		// load default values from Attributes Matching tab (global matching)
		$usedGlobal = false;
		$globalMatching = $this->getCategoryMatching($category, $customIdentifier);

		if (!$this->isProductPrepared($category, $prepare)) {
			$dbData = $globalMatching;
			$usedGlobal = true;
		}

		arrayEntitiesToUTF8($mpData);
		$attributes = array();

        //get matched values for EAN, MPN, Brand (Config)
        if (empty($dbData)) {
            $dbData = self::getConfigProductListingDetails(array_keys($mpData['attributes']), $category);
        }
        arrayEntitiesToUTF8($dbData);

		foreach ($mpData['attributes'] as $code => $value) {
			$utf8Code = $this->fixHTMLUTF8Entities($code);
			$attributes[$utf8Code] = array(
				'AttributeCode' => $utf8Code,
				'AttributeName' => $value['title'],
				'AllowedValues' => isset($value['values']) ? $value['values'] : array(),
				'CategoryId' => $value['categoryId'],
				'AttributeDescription' => isset($value['desc']) ? htmlspecialchars_decode($value['desc'], ENT_NOQUOTES) : '',
				'CurrentValues' => isset($dbData[$utf8Code]) ? $dbData[$utf8Code] : array('Values' => array()),
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

		$secondaryCategory = !empty($_POST['SecondarySelectValue']) ? $_POST['SecondarySelectValue'] : null;
		if (empty($secondaryCategory)) {
			$secondaryCategory = !empty($_POST['SecondaryCategory']) ? $_POST['SecondaryCategory'] : null;
		}
		$selectedCategories = array($category, $secondaryCategory);

		// If there are saved values but they were removed either from Marketplace or Shop, display warning to user.
		if (is_array($dbData)) {
			foreach ($dbData as $utf8Code => $value) {
				$isAdditionalAttribute = strpos($utf8Code, 'additional_attribute_') !== false;
				if (!isset($attributes[$utf8Code]) && !$isAdditionalAttribute &&
					in_array($value['CategoryId'], $selectedCategories)
				) {
					$attributes[$utf8Code] = array(
						'Deleted' => true,
						'AttributeCode' => $utf8Code,
						'AttributeName' => !empty($value['AttributeName']) ? $value['AttributeName'] : $utf8Code,
						'AllowedValues' => array(),
						'CategoryId' => $value['CategoryId'],
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
					$attributes[$utf8Code]['IsDeletedOnShop'] = $this->detectIfAttributeIsDeletedOnShop($shopAttributes,
						$value, $attributes[$utf8Code]['WarningMessage']);
				}
			}
		}

		$attributes = array_reduce($attributes, function ($carry, $attribute) {
			if (0 !== strpos($attribute['AttributeCode'], 'additional_attribute_')) {
				$attribute['AttributeCode'] = unpack('H*', $attribute['AttributeCode']);
				$attribute['AttributeCode'] = $attribute['AttributeCode'][1];
			}

			$carry[$attribute['AttributeCode']] = $attribute;

			return $carry;
		}, array());

		if ($getDate) {
			$modificationDate = MagnaDB::gi()->fetchOne(eecho('
					SELECT ModificationDate
					FROM ' . $tableName . '
					WHERE MpId = ' . $this->mpId . '
						AND MpIdentifier = "' . $category . '"
						AND CustomIdentifier = "' . $customIdentifier . '"
				', false));

			$variationThemeData = array();
			if (!empty($mpData['variation_details'])) {
				$variationThemeData['variation_details'] = $mpData['variation_details'];
				$variationThemeData['variation_theme_code'] = $this->getSavedVariationThemeCode($category, $prepare);
			}

			$variationThemeBlacklistData = array();
			if (!empty($mpData['variation_details_blacklist'])) {
				$variationThemeData['variation_details_blacklist'] = $mpData['variation_details_blacklist'];
			}

			return array_merge(
				array(
					'Attributes' => $attributes,
					'ModificationDate' => $modificationDate,
					'DifferentProducts' => $hasDifferentlyPreparedProducts,
				),
				$variationThemeData,
				$variationThemeBlacklistData
			);
		}

		return $attributes;
	}

	public function saveMatching($category, &$matching, $savePrepare, $fromPrepare,
		$validateCustomAttributesNumber, $variationThemeAttributes = null, $customIdentifier = '')
	{
		$packedShopVariations = array();
		foreach ($matching['ShopVariation'] as $key => $value) {
			$packedKey = $key;
			if (0 !== strpos($key, 'additional_attribute_')) {
				$packedKey = pack('H*', $key);
			}

			$packedShopVariations[$packedKey] = $value;
		}
		$matching['ShopVariation'] = $packedShopVariations;

		return parent::saveMatching($category, $matching, $savePrepare, $fromPrepare,
			$validateCustomAttributesNumber,$variationThemeAttributes, $customIdentifier);
	}

	protected function autoMatch($categoryId, $sMPAttributeCode, &$aAttributes, $customIdentifier = '')
	{
		$sMPAttributeCode = unpack('H*', $sMPAttributeCode);
		$sMPAttributeCode = $sMPAttributeCode[1];

		return parent::autoMatch($categoryId, $sMPAttributeCode, $aAttributes, $customIdentifier);
	}

	protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '')
	{
		$secondaryCategory = !empty($_POST['SecondarySelectValue']) ? $_POST['SecondarySelectValue'] : null;
		if (empty($secondaryCategory)) {
			$secondaryCategory = !empty($_POST['SecondaryCategory']) ? $_POST['SecondaryCategory'] : null;
		}

		$data = EbayApiConfigValues::gi()->getVariantConfigurationDefinition($category, $secondaryCategory);

		if (!is_array($data) || !isset($data['attributes'])) {
			$data = array();
		}

		if (empty($data['attributes'])) {
			$data['attributes'] = array();
		}

		return $data;
	}

	protected function setMatchingTableTranslations() {
		return array(
			'mpTitle' => str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_TITLE),
			'mpAttributeTitle' => str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_MP_ATTRIBUTE),
			'mpOptionalAttributeTitle' => str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE),
			'mpCustomAttributeTitle' => str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE),
		);
	}

	public function getVarMatchTranslations() {
		$translations = parent::getVarMatchTranslations();
		$translations['mpValue'] = str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_MP_VALUE);
		$translations['attributeChangedOnMp'] = str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_ATTRIBUTE_CHANGED_ON_MP);
		$translations['attributeDeletedOnMp'] = str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_ATTRIBUTE_DELETED_ON_MP);
		$translations['attributeValueDeletedOnMp'] = str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_ATTRIBUTE_VALUE_DELETED_ON_MP);;
		$translations['categoryWithoutAttributesInfo'] = str_replace('%marketplace%', $this->marketplaceTitle, ML_GENERAL_VARMATCH_CATEGORY_WITHOUT_ATTRIBUTES_INFO);

		return $translations;
	}
}
