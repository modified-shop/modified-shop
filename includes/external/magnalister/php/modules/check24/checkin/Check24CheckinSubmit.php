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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'check24/Check24Helper.php');
require_once(DIR_MAGNALISTER_MODULES.'check24/classes/Check24ProductSaver.php');

class Check24CheckinSubmit extends MagnaCompatibleCheckinSubmit {
	private $oLastException = null;

	public function __construct($settings = array()) {
		global $_MagnaSession;

		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], ''),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'itemsPerBatch' => 100,
			'mlProductsUseLegacy' => false,
		), $settings);

		$this->summaryAddText = ML_CHECK24_TEXT_AFTER_UPLOAD;
		
		parent::__construct($settings);
		
		$this->settings['SyncInventory'] = array (
			'Price' => getDBConfigValue($settings['marketplace'].'.inventorysync.price', $this->mpID, '') == 'auto',
			'Quantity' => getDBConfigValue($settings['marketplace'].'.stocksync.tomarketplace', $this->mpID, '') == 'auto',
		);
	}

	protected function processException($e) {
		$this->oLastException = $e;
	}

	public function getLastException() {
		return $this->oLastException;
	}

	protected function setUpMLProduct() {
		parent::setUpMLProduct();

		// Set Price and Quantity settings
		MLProduct::gi()->setPriceConfig(Check24Helper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(Check24Helper::loadQuantitySettings($this->mpID));
        MLProduct::gi()->useMultiDimensionalVariations(true);
        MLProduct::gi()->setOptions(array(
            'sameVariationsToAttributes' => false,
            'purgeVariations' => true,
            'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
        ));
	}

	protected function appendAdditionalData($iPID, $aProduct, &$aData) {
        $this->UpdateShopVariationProductPrepareByOldData($aProduct, $iPID);
		$aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_CHECK24_PROPERTIES.'
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr')
				? 'products_model = "'.MagnaDB::gi()->escape($aProduct['ProductsModel']).'"'
				: 'products_id = "'.$iPID.'"'
			) . '
			       AND mpID = '.$this->_magnasession['mpID']
		);
		
		// Will not happen in sumbit cycle but can happen in loadProductByPId.
		if (empty($aPropertiesRow)) {
			$aData['submit'] = array();
			return;
		}

		$aData['submit']['CategoryPath'] = renderCategoryPath($iPID, 'product', ' > ');

		#echo print_m($aProduct);

		$aData['submit']['SKU'] = $aData['submit']['MasterSKU'] = ($this->settings['keytype'] == 'artNr') ? $aProduct['MarketplaceSku'] : $aProduct['MarketplaceId'];
		$aData['submit']['Title'] = $aProduct['Title'];

		if (!empty($aProduct['Description'])) {
			$aData['submit']['Description'] = sanitizeProductDescription($aProduct['Description']);
		}

		if (empty($aProduct['Manufacturer']) === false) {
			$aData['submit']['Manufacturer'] = $aProduct['Manufacturer'];
		} else {
			$manufacturerName = getDBConfigValue($this->marketplace.'.checkin.manufacturerfallback', $this->mpID, '');
			if (empty($manufacturerName) === false) {
				$aData['submit']['Manufacturer'] = $manufacturerName;
			}
		}

		if (empty($aProduct['ManufacturerPartNumber']) === false) {
			$aData['submit']['ManufacturerPartNumber'] = $aProduct['ManufacturerPartNumber'];
		}

		if (empty($aProduct['EAN']) === false) {
			$aData['submit']['EAN'] = $aProduct['EAN'];
		}

		$sImagePath = getDBConfigValue($this->marketplace . '.imagepath', $this->mpID, SHOP_URL_POPUP_IMAGES);
		if (empty($aProduct['Images']) === false) {
			foreach($aProduct['Images'] as $sImg) {
				$aData['submit']['Images'][] = array('URL' => $sImagePath.$sImg);
			}
		}

		if (isset($aProduct['Weight']) && !empty($aProduct['Weight'])) {
			$aData['submit']['Weight'] = $aProduct['Weight'];
		}

		$aData['submit']['ProductUrl'] = $aProduct['ProductUrl'];
		$aData['submit']['Quantity'] = $aData['quantity'];
		$aData['submit']['Price'] = $aData['price'];
		$aData['submit']['BasePrice'] = $aProduct['BasePrice'];
		$aData['submit']['ShippingTime'] = $aPropertiesRow['ShippingTime'];
		$aData['submit']['ShippingCost'] = $aPropertiesRow['ShippingCost'];
		if (!empty($aPropertiesRow['ItemHandlingData'])) {
			$aItemHandlingData = json_decode($aPropertiesRow['ItemHandlingData'], true);
			foreach ($aItemHandlingData as $sIHKey => $sIHValue) {
				$aData['submit'][$sIHKey] = $sIHValue;
			}
			if (    array_key_exists('DeliveryMode', $aData['submit'])
			     && ($aData['submit']['DeliveryMode'] == 'EigeneAngaben')) {
				if (array_key_exists('DeliveryModeText', $aData['submit'])
			             && !empty($aData['submit']['DeliveryModeText'])) {
					$aData['submit']['DeliveryMode'] = $aData['submit']['DeliveryModeText'];
					unset($aData['submit']['DeliveryModeText']);
				}
			}
			if (!array_key_exists('CustomTariffsNumber', $aData['submit'])) {
			// use config value
				$aCustomTariffsNumberDBMatching = getDBConfigValue($this->marketplace.'.custom_tariffs_number.dbmatching.table', $this->mpID, '');
				if (    !empty($aCustomTariffsNumberDBMatching)
				     && isset($aCustomTariffsNumberDBMatching['column'])
				     && isset($aCustomTariffsNumberDBMatching['table'])) {
					$aData['submit']['CustomTariffsNumber'] = (string)MagnaDB::gi()->fetchOne('SELECT '.$aCustomTariffsNumberDBMatching['column'].' FROM '.$aCustomTariffsNumberDBMatching['table'].' WHERE products_id = '.$iPID.' LIMIT 1');
					if (empty($aData['submit']['CustomTariffsNumber'])) {
						unset($aData['submit']['CustomTariffsNumber']);
					}
				}
			}
		}

//		if (!empty($aPropertiesRow['GPSRData'])) {
//			$aGPSRData = json_decode($aPropertiesRow['GPSRData'], true);
//			foreach ($aGPSRData as $sGPSRKey => $sGPSRValue) {
//				// check24 uses umlauts (we don't internally)
//				if ($sGPSRKey == 'Hersteller_Strasse_Hausnummer') {
//					$sGPSRKey = 'Hersteller_Straße_Hausnummer';
//				} else if ($sGPSRKey == 'Verantwortliche_Person_fuer_EU_Strasse_Hausnummer') {
//					$sGPSRKey = 'Verantwortliche_Person_für_EU_Straße';
//				} else if (strpos($sGPSRKey, 'Verantwortliche_Person_fuer_EU_') === 0) {
//					$sGPSRKey = str_replace('Verantwortliche_Person_fuer', 'Verantwortliche_Person_für', $sGPSRKey);
//				}
//				$aData['submit'][$sGPSRKey] = $sGPSRValue;
//			}
//		}

        //sigle product
        if (empty($aProduct['Variations']) ==true) {
            $AttributeMatchingDataResult = Check24Helper::gi()->convertMatchingToNameValue(
                json_decode($aPropertiesRow['CategoryIndependentShopVariation'], true),
                $aProduct
            );
            foreach ($AttributeMatchingDataResult as $key => $value) {
                $aData['submit']['MarketplaceAttributes'][str_replace(' ', '_', $key)]= $value;
            }


        }else{//Variation products
            $aData['submit']['Variations'] = $aProduct['Variations'];
        }
    }

    protected function preSubmit(&$request) {

        $request['DATA'] = array();
        foreach ($this->selection as $iProductId => &$aProduct) {
            if (empty($aProduct['submit']['Variations'])) {
                $request['DATA'][] = $aProduct['submit'];
                continue;
            }

            foreach ($aProduct['submit']['Variations'] as $aVariation) {
                $aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_CHECK24_PROPERTIES.'
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr')
                        ? 'products_model = "'.MagnaDB::gi()->escape($aProduct['submit']['MasterSKU']).'"'
                        : 'products_id = "'.$iProductId.'"'
                    ) . '
			       AND mpID = '.$this->_magnasession['mpID']
                );

                $aVariationData = $aProduct;
                unset($aVariationData['submit']['Variations']);
                $aVariationData['submit']['SKU'] = $aVariation['MarketplaceSku'];
                $aVariationData['submit']['Quantity'] = $aVariation['Quantity'];
                $aVariationData['submit']['Price'] = $aVariation['Price']['Price'];
                $aVariationData['submit']['EAN'] = $aVariation['EAN'];
                if (isset($aVariation['Weight'])) {
                    $aVariationData['submit']['Weight'] = $aVariation['Weight'];
                }
                $masterData =$aVariationData['submit'];

                if (getDBConfigValue('general.keytype', '0') == 'artNr') {
                    $sSkuKey = 'MarketplaceSku';
                } else {
                    $sSkuKey = 'MarketplaceId';
                }

                $CategoryIndependentAttributesBySKU = $this->translateCategoryAttributesForVariations(
                    $aPropertiesRow['CategoryIndependentShopVariation'],
                    $aProduct['submit']['Variations'],
                    $sSkuKey
                );
                //replacing space with _ for example: convert `manufacturer name` to `manufacturer_name`
                $AttributeMatchingDataResult = array_merge(Check24Helper::gi()->convertMatchingToNameValue(
                    json_decode($masterData['CategoryIndependentShopVariation'], true),
                    $aProduct
                ), $CategoryIndependentAttributesBySKU[$aVariation[$sSkuKey]]);
                //ading new key and value to the `MarketplaceAttributes`
                foreach ($AttributeMatchingDataResult as $key => $value) {
                    $aVariationData['submit']['MarketplaceAttributes'][str_replace(' ', '_', $key)]= $value;
                }

                $attributes = array();
                foreach ($aVariation['Variation'] as $var) {
                    $attributes[] = $var['Name'].' - '.$var['Value'];
                }

				$aVariationData['submit']['Title'] .= ': ' . implode(', ', $attributes);
				$request['DATA'][] = $aVariationData['submit'];
			}
		}

        arrayEntitiesToUTF8($request['DATA']);
    }

    /**
     * @param $productsModel
     * @param $iPID
     * @return void
     */
    public function UpdateShopVariationProductPrepareByOldData($productsModel, $iPID)
    {
        $aPropertiesData = MagnaDB::gi()->fetchRow('
			SELECT * FROM ' . TABLE_MAGNA_CHECK24_PROPERTIES . '
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr')
                ? 'products_model = "' . MagnaDB::gi()->escape($productsModel['ProductsModel']) . '"'
                : 'products_id = "' . $iPID . '"'
            ) . '
			       AND mpID = ' . $this->_magnasession['mpID']
        );


        if (isset($aPropertiesData)) {
            if (isset($aPropertiesData['GPSRData']) && !empty($aPropertiesData['GPSRData'])) {
                if (isset($aPropertiesData['CategoryIndependentShopVariation']) && empty($aPropertiesData['CategoryIndependentShopVariation'])) {
                    $GPSRDATA = json_decode($aPropertiesData['GPSRData'], true);
                    $variationShopGenerator = array();
                    foreach ($GPSRDATA as $key => $value) {

                        if (!in_array($key, array('Verantwortliche_Person_fuer_EU_Name', 'Verantwortliche_Person_fuer_EU_Strasse_Hausnummer', 'Verantwortliche_Person_fuer_EU_PLZ', 'Verantwortliche_Person_fuer_EU_Stadt', 'Verantwortliche_Person_fuer_EU_Land', 'Verantwortliche_Person_fuer_EU_Email', 'Verantwortliche_Person_fuer_EU_Telefonnummer'))) {
                            if($key == 'Hersteller_Strasse_Hausnummer') {
                                $variationShopGenerator['manufacturer street'] = array(
                                    'Code' => "freetext",
                                    'Kind' => "FreeText",
                                    'Required' => true,
                                    'AttributeName' => "GPSR - Hersteller Straße und Hausnummer",
                                    'Values' => $value,
                                    "Error" => false
                                );
                            }else{
                                if($key == 'Marke'){
                                    $variationShopGenerator['brand'] = array(
                                        'Code' => "freetext",
                                        'Kind' => "FreeText",
                                        'Required' => true,
                                        'AttributeName' => "GPSR - Brand",
                                        'Values' => $value,
                                        "Error" => false
                                    );
                                }elseif ($key == 'Hersteller_Name') {
                                    $variationShopGenerator['manufacturer name'] = array(
                                        'Code' => "freetext",
                                        'Kind' => "FreeText",
                                        'Required' => true,
                                        'AttributeName' => "GPSR - Hersteller Name",
                                        'Values' => $value,
                                        "Error" => false
                                    );
                                }elseif ($key == 'Hersteller_PLZ') {
                                    $variationShopGenerator['manufacturer postcode'] = array(
                                        'Code' => "freetext",
                                        'Kind' => "FreeText",
                                        'Required' => true,
                                        'AttributeName' => "GPSR - Hersteller PLZ",
                                        'Values' => $value,
                                        "Error" => false
                                    );
                                }elseif ($key == 'Hersteller_Stadt') {
                                    $variationShopGenerator['manufacturer city'] = array(
                                        'Code' => "freetext",
                                        'Kind' => "FreeText",
                                        'Required' => true,
                                        'AttributeName' => "GPSR - Hersteller Stadt",
                                        'Values' => $value,
                                        "Error" => false
                                    );
                                }elseif ($key == 'Hersteller_Land') {
                                    $variationShopGenerator['manufacturer country'] = array(
                                        'Code' => "freetext",
                                        'Kind' => "FreeText",
                                        'Required' => true,
                                        'AttributeName' => "GPSR - Hersteller Land",
                                        'Values' => $value,
                                        "Error" => false
                                    );
                                }elseif ($key == 'Hersteller_Email') {
                                    $variationShopGenerator['manufacturer email'] = array(
                                        'Code' => "freetext",
                                        'Kind' => "FreeText",
                                        'Required' => true,
                                        'AttributeName' => "GPSR - Hersteller Email",
                                        'Values' => $value,
                                        "Error" => false
                                    );
                                }elseif ($key == 'Hersteller_Telefonnummer') {
                                    $variationShopGenerator['manufacturer phone number'] = array(
                                        'Code' => "freetext",
                                        'Kind' => "FreeText",
                                        'Required' => false,
                                        'AttributeName' => "Hersteller Telefonnummer",
                                        'Values' => $value,
                                        "Error" => false
                                    );
                                }
                            }

                        }
                    }
                    MagnaDB::gi()->update(TABLE_MAGNA_CHECK24_PROPERTIES, array(
                        'CategoryIndependentShopVariation' => json_encode($variationShopGenerator),
                    ), array(
                        'mpID' => $this->_magnasession['mpID'],
                        'products_id' => $iPID,
                    ));
                }
            }
        }
    }
    private function translateCategoryAttributesForVariations($jCategoryAttributes, $aVariations, $sSkuKey) {

        $aCategoryAttributes = json_decode($jCategoryAttributes, true);

        $aShopCodesForCategoryAttributes = array_map(function ($attr) {
            return $attr['Code'];
        }, $aCategoryAttributes);

        $res = $freetext = array();
        foreach ($aCategoryAttributes as $key => $matched) {
            if ($matched['Code'] === 'freetext') {
                $freetext[str_replace(' ', '_', $key)] = $matched['Values'];
                unset($aCategoryAttributes[$key]);
            }
        }
        foreach ($aVariations as $aVariation) {
            $ean = array();

            //get the ean from the variations
            if (in_array('ean', $aShopCodesForCategoryAttributes) && $aVariation['EAN'] != '') {
                foreach ($aCategoryAttributes as $value) {
                    if ($value['Code'] == 'ean') {
                        $ean[$value['AttributeName']] = $aVariation['EAN'];
                    }
                }
            }


            $res[$aVariation[$sSkuKey]] = array();
            foreach ($aVariation['Variation'] as $variant) {
                foreach ($aCategoryAttributes as $attr => $matchedAttributes) {
                    if (// We need to either match the attribute name or the code
                        (  $variant['Name'] !== $matchedAttributes['AttributeName']
                            && $variant['NameId'] !== $matchedAttributes['Code']
                        )
                        // The matched attribute values needs to be an array
                        || !is_array($matchedAttributes['Values'])
                    ) {
                        continue;
                    }

                    foreach ($matchedAttributes['Values'] as $matched) {
                        if (// only if the value from the shop is matched by the attribute value
                            !is_array($matched)
                            || !array_key_exists('Shop', $matched)
                            || !is_array($matched['Shop'])
                            || !array_key_exists('Value', $matched['Shop'])
                            || $matched['Shop']['Value'] !== $variant['Value']
                        ) {
                            continue;
                        }

                        $res[$aVariation[$sSkuKey]][$attr] = $matched['Marketplace']['Value'];
                    }
                }
            }
            $res[$aVariation[$sSkuKey]] = array_merge($res[$aVariation[$sSkuKey]], $freetext, $ean);
        }

        return $res;
    }

	protected function markAsFailed($sku) {
		$iPID = magnaSKU2pID($sku);
		$this->badItems[] = $iPID;
		unset($this->selection[$iPID]);
	}

	/*protected function postSubmit() {
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
		} catch (MagnaException $e) {
			$this->submitSession['api']['exception'] = $e;
			$this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();
		}
	}*/

}
