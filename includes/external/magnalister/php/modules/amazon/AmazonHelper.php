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

    protected function getPreparedData($category, $prepare = false)
    {
        if (!$prepare) {
            return false;
        }

        $availableCustomConfigs = false;
        $productIdCondition = is_int($prepare) ? ' OR products_id = '.$prepare : '';
        $dataFromDB = MagnaDB::gi()->fetchRow(eecho('
            SELECT `data`
            FROM '.TABLE_MAGNA_AMAZON_APPLY.'
            WHERE mpID = '.$this->mpId.'
                AND topMainCategory = "'.$category.'"
                AND (products_model = "'.$prepare.'"'.$productIdCondition.')
        ', false));

        if (!$dataFromDB) {
            return false;
        }

        $dataDB = unserialize(base64_decode($dataFromDB['data']));

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

        return !$availableCustomConfigs ? null : $availableCustomConfigs;
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @return array|null
     */
    protected function getPreparedProductsData($category)
    {
        $dataFromDB = MagnaDB::gi()->fetchArray(eecho('
				SELECT `data`
				FROM ' . TABLE_MAGNA_AMAZON_APPLY . '
				WHERE mpID = ' . $this->mpId . '
					AND topMainCategory = "' . $category . '"
			', false), true);

        if ($dataFromDB) {
            $result = array();
            foreach ($dataFromDB as $preparedData) {
                $data = unserialize(base64_decode($preparedData));
                if ($data['ShopVariation']) {
                    $result[] = json_decode($data['ShopVariation'], true);
                }
            }

            return $result;
        }

        return null;
    }

    protected function getAttributesFromMP($category)
    {
        $data = false;
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetCategoryDetails',
                'MARKETPLACEID' => $this->mpId,
                'CATEGORY' => $category,
            ));
            if (!empty($result['DATA'])) {
                $data = $result['DATA'];
                if (getDBConfigValue('amazon.site', $this->mpId) === 'US') {
                    $data['attributes']['UPC'] = array(
                        'title' => 'UPC',
                        'mandatory' => true,
                    );
                }
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

    public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true)
    {
        // amazon does not have category pick button
        return parent::renderMatchingTable($url, $categoryOptions, false);
    }

    public function saveMatching($category, &$matching, $savePrepare, $fromPrepare = false)
    {
        $errors = parent::saveMatching($category, $matching, $savePrepare, $fromPrepare);

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
}