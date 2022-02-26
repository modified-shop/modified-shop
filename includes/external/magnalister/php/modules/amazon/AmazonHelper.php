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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');

class AmazonHelper extends AttributesMatchingHelper {

    private static $instance;

    public static function gi()
    {
        if (self::$instance === null) {
            self::$instance = new AmazonHelper();
        }

        return self::$instance;
    }

	public static function processCheckinErrors($result, $mpID) {
		// Empty is ok, the API has a method to fetch the error log later.
	}

	public static function loadPriceSettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);

		$config = array(
			'AddKind' => getDBConfigValue($mp.'.price.addkind', $mpId, 'percent'),
			'Factor'  => (float)getDBConfigValue($mp.'.price.factor', $mpId, 0),
			'Signal'  => getDBConfigValue($mp.'.price.signal', $mpId, ''),
			'Group'   => getDBConfigValue($mp.'.price.group', $mpId, ''),
			'UseSpecialOffer' => getDBConfigValue(array($mp.'.price.usespecialoffer', 'val'), $mpId, false),
			'Currency' => getCurrencyFromMarketplace($mpId),
			'ConvertCurrency' => getDBConfigValue(array($mp.'.exchangerate', 'update'), $mpId, false),
		);

		return $config;
	}

	public static function loadQuantitySettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);

		$config = array(
			'Type'  => getDBConfigValue($mp.'.quantity.type', $mpId, 'lump'),
			'Value' => (int)getDBConfigValue($mp.'.quantity.value', $mpId, 0),
			'MaxQuantity' => (int)getDBConfigValue($mp.'.quantity.maxquantity', $mpId, 0),
		);

		return $config;
	}

    protected function isProductPrepared($category, $prepare = false)
    {
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'. $prepare . '"';
        }

        if ($prepare) {
            $dataFromDB = MagnaDB::gi()->fetchRow(eecho('
					SELECT `products_id`
					FROM '.TABLE_MAGNA_AMAZON_APPLY.'
					WHERE mpID = '.$this->mpId.'
						AND topMainCategory = "'.$category.'"
						' . $sSQLAnd . '
					LIMIT 1
				', false)
            );

            return !empty($dataFromDB['products_id']);
        }

        return false;
    }

    protected function getPreparedData($category, $prepare = false, $customIdentifier = '')
    {
        if (!$prepare) {
            return false;
        }

        $availableCustomConfigs = false;

	    if (getDBConfigValue('general.keytype', '0') == 'artNr') {
		    $sSQLAnd = ' AND products_model = "'.$prepare.'"';
	    } else {
		    $sSQLAnd = ' AND products_id = "'. $prepare . '"';
	    }

        if ($prepare) {
	        $dataFromDB = MagnaDB::gi()->fetchRow(eecho('
				SELECT `data`, `topProductType`
				FROM ' . TABLE_MAGNA_AMAZON_APPLY . '
				WHERE mpID = ' . $this->mpId . '
					AND topMainCategory = "' . $category . '"
					'.$sSQLAnd.'
			', false));

	        if (!$dataFromDB) {
		        return false;
	        }

	        $dataDB = unserialize(base64_decode($dataFromDB['data']));

            // fix for prepare because it was set as an attribute (but we have separate column in db)
            if (isset($dataDB['Attributes']) && (count($dataDB['Attributes']) == 1) && isset($dataDB['Attributes']['MerchantShippingGroupName'])) {
                unset($dataDB['Attributes']['MerchantShippingGroupName']);
            }

	        if (!empty($dataDB['ShopVariation'])) {
		        if (is_array($dataDB['ShopVariation'])) {
			        $availableCustomConfigs = $dataDB['ShopVariation'];
		        } else {
			        $availableCustomConfigs = json_decode($dataDB['ShopVariation'], true);
		        }
	        } elseif (!empty($dataDB['Attributes'])) {
		        foreach ($dataDB['Attributes'] as $attributeKey => $attributeValue) {
			        $availableCustomConfigs[$attributeKey] = array(
				        'Kind' => 'Matching',
				        'Values' => $attributeValue,
				        'Error' => false
			        );
		        }
	        }
        }

        return !$availableCustomConfigs ? null : $availableCustomConfigs;
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @param string $customIdentifier
     * @return array|null
     */
    protected function getPreparedProductsData($category, $customIdentifier = '') {
	// LIMIT 4096 to prevent unprocessable long data
        $dataFromDB = MagnaDB::gi()->fetchArray(eecho('
				SELECT `data`, `topProductType`
				 FROM ' . TABLE_MAGNA_AMAZON_APPLY . '
				 WHERE mpID = ' . $this->mpId . '
				 AND topMainCategory = "' . $category . '"
				 ORDER BY products_id DESC LIMIT 4096
			', false), true);

        if ($dataFromDB) {
            $result = array();
            foreach ($dataFromDB as $preparedData) {
                $data = unserialize(base64_decode($preparedData['data']));
                if ($data['ShopVariation'] && isset($dataFromDB['topProductType']) && ($customIdentifier == $dataFromDB['topProductType'])) {
                    $result[] = json_decode($data['ShopVariation'], true);
                }
            }

            return $result;
        }

        return null;
    }

    public function getCustomIdentifiers($category, $prepare = false, $getDate = false)
    {
	    return $this->getProductTypes($category);
    }

    protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '')
    {
        $data = false;
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetCategoryDetails',
                'MARKETPLACEID' => $this->mpId,
                'CATEGORY' => $category,
                'PRODUCTTYPE' => $customIdentifier,
            ));
            if (!empty($result['DATA'])) {
                $data = $result['DATA'];
                if (getDBConfigValue('amazon.site', $this->mpId) === 'US') {
                    $data['attributes']['UPC'] = array(
                        'title' => 'UPC',
                        'mandatory' => true,
                    );
                }
                // add variation theme to skip all variations
                $data['variation_details']['skip_variations'] = array(
                    'name' => ML_GENERAL_VARIATION_THEME_SKIP_VARIATIONS,
                    'attributes' => array(),
                );
            }
        } catch (MagnaException $e) {
            $e->setCriticalStatus(false);
        }

        if (!is_array($data) || !isset($data['attributes'])) {
            $data = array();
        }

        if (!empty($data['attributes'])) {
            foreach ($data['attributes'] as &$value) {
                if (!isset($value['mandatory'] )) {
                    $value['mandatory'] = true;
                }
            }
        } else {
            $data['attributes'] = array();
        }

        return $data;
    }

    public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true, $displayCategory = true, $customIdentifierHtml = '')
    {
        $customIdentifierHtml = '
            <tr id="mpCustomIdentifierSelector">
                <th>'.ML_LABEL_SUBCATEGORY.'</th>
                <td class="input">
                    <table class="inner middle fullwidth customIdentifierSelect">
                        <tbody>
                        <tr>
                            <td>
                                <div class="hoodCatVisual" id="CustomIdentifierVisual">
                                    <select id="CustomIdentifier" name="CustomIdentifier" style="width:100%">
                                        '. $this->renderCustomIdentifierOptions() .'
                                    </select>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td class="info"></td>
            </tr>
        ';
        // amazon does not have category pick button
        return parent::renderMatchingTable($url, $categoryOptions, false, $displayCategory, $customIdentifierHtml);
    }

	private function renderCustomIdentifierOptions()
	{
		$noProductTypeOption = '<option value="">'.ML_AMAZON_LABEL_APPLY_PLEASE_SELECT.'</option>' . "\n";

        $category = isset($_POST['PrimaryCategory']) ? $_POST['PrimaryCategory'] : null;
        $customIdentifier = isset($_POST['CustomIdentifier']) ? $_POST['CustomIdentifier'] : null;
		if (empty($category)) {
			return $noProductTypeOption;
		}

		$productTypes = $this->getProductTypes($category);

		$out = '';
		foreach ($productTypes as $productTypeKey => $productType) {
			$selected = ($productTypeKey == $customIdentifier) ? 'selected="selected"' : '';
			$out .= '<option value="'.fixHTMLUTF8Entities($productTypeKey).'" '.$selected.'>'.fixHTMLUTF8Entities($productType).'</option>' . "\n";
		}

		return !empty($out) ? $out : $noProductTypeOption;
	}

    public function saveMatching($category, &$matching, $savePrepare, $fromPrepare,
         $validateCustomAttributesNumber, $variationThemeKeyAttributes = null, $sCustomIdentifier = ''
    ) {
        $errors = parent::saveMatching($category, $matching, $savePrepare, $fromPrepare,
            $validateCustomAttributesNumber, $variationThemeKeyAttributes, $sCustomIdentifier);

        if (!$fromPrepare) {
            return $errors;
        }

        $result = '';
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $errorCssClass = 'errorBox';
                $errorMessage = $error;
                if (is_array($error)) {
                    $errorCssClass = "{$error['type']}Box {$error['additionalCssClass']}";
                    $errorMessage = $error['message'];
                }

                $result .= '<p class="'.$errorCssClass.'">' . $errorMessage . '</p>';
            }
        } else if (!$fromPrepare) {
            $result = '<p class="successBox">' . ML_LABEL_SAVED_SUCCESSFULLY . '</p>';
        }

        if ($result) {
            // on apply page we need errors in POST to display them properly
            $_POST['Errors'] = $result;
        }

        return json_encode($matching['ShopVariation']);
    }

    private function getProductTypes($category)
    {
        $productTypes = array();

        if (empty($category)) {
            return $productTypes;
        }

        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetCategoryDetails',
                'MARKETPLACEID' => $this->mpId,
                'CATEGORY' => $category,
            ));

            if (!empty($result['DATA']['productTypes'])) {
                $productTypes = $result['DATA']['productTypes'];
            }

        } catch (MagnaException $e) {
            // No product types in this case
        }

        return $productTypes;
    }

    protected function getSavedVariationThemeCode($category, $prepare = false)
    {
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'. $prepare . '"';
        }

        $variationTheme = null;
        if ($prepare) {
            $variationTheme = MagnaDB::gi()->fetchOne(eecho('
				SELECT variation_theme
				FROM ' . TABLE_MAGNA_AMAZON_APPLY . '
				WHERE MpId = ' . $this->mpId . '
					  AND topMainCategory = "' . $category . '"
					  ' . $sSQLAnd
                )
            );
        }
        $variationTheme = json_decode($variationTheme, true);

        return is_array($variationTheme) ? key($variationTheme) : '';
    }
}
