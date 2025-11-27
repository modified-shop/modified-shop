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
 * $Id: apply.php 6005 2015-09-17 08:12:42Z masoud.khodaparast $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES . 'amazon/amazonFunctions.php');
require_once(DIR_MAGNALISTER_MODULES . 'amazon/AmazonHelper.php');

function amazonSanitizeDesc($str) {
	$str = !magnalisterIsUTF8($str) ? utf8_encode($str) : $str;
	# preg_replace could return NULL at 5.2.0 to 5.3.6 - "/(\s*<br[^>]*>\s*)*$/"
	# tested at: http://3v4l.org/WGcod
	if (version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.3.6', '<=')) {
		@ini_set('pcre.backtrack_limit', '10000000');
		@ini_set('pcre.recursion_limit', '10000000');
	}
	$str = str_replace(array('&nbsp;', html_entity_decode('&nbsp;', ENT_COMPAT, 'UTF-8')), ' ', $str);
	$str = sanitizeProductDescription(
		$str,
		'<p><br><ul><ol><li><strong><b><em><i>',
		'_keep_all_'
	);
	$str = str_replace(array('<br />', '<br/>'), '<br>', $str);
	$str = str_replace(array("\n"), ' ', $str);
	// $str = preg_replace('/(\s*<br[^>]*>\s*)*$/', ' ', $str);
	$str = preg_replace('/\s\s+/', ' ', $str);
	return AmazonHelper::gi()->truncateString($str, 2000);
}

function getMultiPrepareData($genericDataStructure = array()) {
    global $_MagnaSession;
    $mpID = $_MagnaSession['mpID'];

    $query = '
        SELECT ap.products_id, ap.category, ap.data
        FROM '.TABLE_MAGNA_AMAZON_APPLY.' ap
        INNER JOIN '.TABLE_MAGNA_SELECTION.' ms ON ap.products_id = ms.pID AND ap.mpID = ms.mpID
        WHERE selectionname="apply" 
              AND ms.mpID = "'.$mpID.'" 
              AND session_id="'.session_id().'" 
              AND ap.products_id IS NOT NULL 
              AND TRIM(ap.products_id) <> ""
    ';

    $prepData = MagnaDB::gi()->fetchArray($query);
    $dbData = false;
    if (!empty($prepData)) {
        $prepData = reset($prepData);
        $dbData = $prepData['data'];
    }

    $prepData['category'] = base64_decode($prepData['category']);
    $prepData['category'] = unserialize($prepData['category']);
    $dbData = base64_decode($dbData);
    $dbData = unserialize($dbData);

    if(isset($dbData['Keywords']) && is_array($dbData['Keywords'])){
        $dbData['Keywords'] = implode(' ', $dbData['Keywords']);
    }
    if (is_array($prepData['category']) && !empty($prepData['category'])
        && is_array($dbData) && !empty($dbData)
    ) {
        $genericDataStructure = array_merge(
            $genericDataStructure,
            $prepData['category'],
            $dbData
        );
    }
    $genericDataStructure['LeadtimeToShip'] = $prepData['leadtimeToShip'];
    $genericDataStructure['ConditionType'] = $prepData['ConditionType'];
    $genericDataStructure['ConditionNote'] = $prepData['ConditionNote'];

    if($prepData['ShippingTemplate'] !== null){
        $genericDataStructure['ShippingTemplate'] = $prepData['ShippingTemplate'];
    }

    if (($hp = magnaContribVerify('AmazonApply_populateGenericData', 1)) !== false) {
        require($hp);
    }


    return $genericDataStructure;
}


function populateGenericData($pID, $edit = false) {
	global $_MagnaSession;

	$mpId = $_MagnaSession['mpID'];
	$genericDataStructure = array(
		'MainCategory' => '',
		'ProductType' => '',
		'BrowseNodes' => array(),
		'variationTheme' => array(),
		'ItemTitle' => '',
		'Manufacturer' => '',
		'Brand' => '',
		'ManufacturerPartNumber' => '',
		'EAN' => '',
		'Images' => array(),
		'BulletPoints' => array('', '', '', '', ''),
		'Description' => '',
		'Keywords' => '',
		'Attributes' => array(),
		'LeadtimeToShip' => getDBConfigValue('amazon.leadtimetoship', $mpId, '-'),
		'ConditionType' => getDBConfigValue('amazon.itemCondition', $mpId, '0'),
		'ConditionNote' => '',
		'B2BActive' => getDBConfigValue('amazon.b2b.active', $mpId, 'false'),
		'B2BSellTo' => getDBConfigValue('amazon.b2b.sell_to', $mpId, 'b2b_b2c'),
		'QuantityPriceType' => getDBConfigValue('amazon.b2b.discount_type', $mpId, ''),
		'QuantityLowerBound1' => getDBConfigValue('amazon.b2b.discount_tier1.quantity', $mpId, '0'),
		'QuantityPrice1' => getDBConfigValue('amazon.b2b.discount_tier1.discount', $mpId, '0'),
		'QuantityLowerBound2' => getDBConfigValue('amazon.b2b.discount_tier2.quantity', $mpId, '0'),
		'QuantityPrice2' => getDBConfigValue('amazon.b2b.discount_tier2.discount', $mpId, '0'),
		'QuantityLowerBound3' => getDBConfigValue('amazon.b2b.discount_tier3.quantity', $mpId, '0'),
		'QuantityPrice3' => getDBConfigValue('amazon.b2b.discount_tier3.discount', $mpId, '0'),
		'QuantityLowerBound4' => getDBConfigValue('amazon.b2b.discount_tier4.quantity', $mpId, '0'),
		'QuantityPrice4' => getDBConfigValue('amazon.b2b.discount_tier4.discount', $mpId, '0'),
		'QuantityLowerBound5' => getDBConfigValue('amazon.b2b.discount_tier5.quantity', $mpId, '0'),
		'QuantityPrice5' => getDBConfigValue('amazon.b2b.discount_tier5.discount', $mpId, '0'),
		'ShopVariation' => array(),
	);

    $aDefaultTemplate = getDBConfigValue(array($_MagnaSession['currentPlatform'] . '.shipping.template', 'defaults'), $mpId);
    if(is_array($aDefaultTemplate)) {
        $genericDataStructure['ShippingTemplate'] = array_search('1', $aDefaultTemplate);
    }

	if ($pID === 0) {
		if ($edit) {
			$genericDataStructure['LeadtimeToShip'] = 'X';
		}
        $result = getMultiPrepareData($genericDataStructure);
        return $result;
	}
	$product = MLProduct::gi()->getProductByIdOld(
		$pID, getDBConfigValue('amazon.lang', $mpId, $_SESSION['languages_id'])
	);
	if ($product === false) {
		return $genericDataStructure;
	}
	if ($product['manufacturers_id'] > 0) {
		$genericDataStructure['Manufacturer'] = $genericDataStructure['Brand'] = MagnaDB::gi()->fetchOne('
			SELECT manufacturers_name 
			  FROM ' . TABLE_MANUFACTURERS . '
			 WHERE manufacturers_id=\'' . $product['manufacturers_id'] . '\'
		');
	}
	if (empty($genericDataStructure['Manufacturer'])) {
		$genericDataStructure['Manufacturer'] = $genericDataStructure['Brand'] = getDBConfigValue(
			'amazon.prepare.manufacturerfallback', $mpId, ''
		);
	}
    if (isset($product['brand_name']) && !empty($product['brand_name'])) {
        $genericDataStructure['Brand'] = $product['brand_name'];
    }
	$mfrmd = getDBConfigValue('amazon.prepare.manufacturerpartnumber.table', $mpId, false);
	if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
		$pIDAlias = getDBConfigValue('amazon.prepare.manufacturerpartnumber.alias', $mpId);
		if (empty($pIDAlias)) {
			$pIDAlias = 'products_id';
		}
		$genericDataStructure['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne('
			SELECT `' . $mfrmd['column'] . '`
			  FROM `' . $mfrmd['table'] . '`
			 WHERE `' . $pIDAlias . '`=\'' . MagnaDB::gi()->escape($pID) . '\'
			 LIMIT 1
		');
	}
	if (!empty($product['products_allimages'])) {
		foreach ($product['products_allimages'] as $img) {
			$genericDataStructure['Images'][$img] = 'true';
		}
	}
	$genericDataStructure['ItemTitle'] = $product['products_name'];
	$genericDataStructure['EAN'] = $product[MAGNA_FIELD_PRODUCTS_EAN];
	$genericDataStructure['Description'] = amazonSanitizeDesc($product['products_description']);

    $trimFunc = function(&$v) {
        $v = trim($v);
    };

	$product['products_meta_description'] = explode(',', $product['products_meta_description']);
	array_walk($product['products_meta_description'], $trimFunc);
	$genericDataStructure['BulletPoints'] = array_slice($product['products_meta_description'], 0, 5);
	# Laenge auf 500 Zeichen beschraenken
	if (!empty($genericDataStructure['BulletPoints'])) {
		foreach ($genericDataStructure['BulletPoints'] as &$bullet) {
			$bullet = trim($bullet);
            $bullet = !magnalisterIsUTF8($bullet) ? utf8_encode($bullet) : $bullet;
			if (empty($bullet)) continue;
			$bullet = substr($bullet, 0, strpos(wordwrap($bullet, 500, "\n", true) . "\n", "\n"));
		}
	}
	$genericDataStructure['BulletPoints'] = array_pad($genericDataStructure['BulletPoints'], 5, '');

	$genericDataStructure['Keywords'] =  trim($product['products_meta_keywords']);
    $genericDataStructure['Keywords'] = substr($genericDataStructure['Keywords'], 0, strpos(wordwrap($genericDataStructure['Keywords'], 1000, "\n", true) . "\n", "\n"));

	$prepData = MagnaDB::gi()->fetchRow('
		SELECT category, data, DataId, leadtimeToShip, ConditionType, ConditionNote, ShippingTemplate
		  FROM ' . TABLE_MAGNA_AMAZON_APPLY . '
		 WHERE mpID=\'' . $mpId . '\' AND
		       ' . ((getDBConfigValue('general.keytype', '0') == 'artNr')
			? 'products_model=\'' . MagnaDB::gi()->escape($product['products_model']) . '\''
			: 'products_id = \'' . $pID . '\''
		) . '
		 LIMIT 1
	');
	$dbData = false;
	if (!empty($prepData)) {
		$dbData = $prepData['data'];
	}

	# Folgende 3 Zeilen auskommentieren, falls die bereits gespeicherten Produktdaten zur Beantragung
	# nicht ueberschrieben werden sollen.
	if (!$edit) {
		# Attributes matching shouldn't be reset
		$dataForShopVariation = unserialize(base64_decode($dbData));
        $shopVariationData = null;

        // V3 approach: PRIMARY load from DataId (new format), FALLBACK to data column (old format)
        // PRIMARY: Try to load from longtext table (new format)
        if (!empty($prepData['DataId'])) {
            $longtextRow = MagnaDB::gi()->fetchRow("
				SELECT Value
				FROM magnalister_amazon_prepare_longtext
				WHERE TextId = '" . MagnaDB::gi()->escape($prepData['DataId']) . "'
				  AND ReferenceFieldName = 'data'
			");
            if (!empty($longtextRow['Value'])) {
                $shopVariationData = $longtextRow['Value'];
            }
        }

        // FALLBACK: If not found in longtext, try old format from data column
        if (empty($shopVariationData) && isset($dataForShopVariation['ShopVariation'])) {
            $shopVariationData = $dataForShopVariation['ShopVariation'];
        }

        $genericDataStructure['ShopVariation'] = $shopVariationData;
		$dbData = false;
	}

	if ($dbData !== false) {
		$prepData['category'] = base64_decode($prepData['category']);
		$prepData['category'] = unserialize($prepData['category']);
		$dbData = base64_decode($dbData);
		$dbData = unserialize($dbData);

        if(isset($dbData['Keywords']) && is_array($dbData['Keywords'])){
            $dbData['Keywords'] = implode(' ', $dbData['Keywords']);
        }
		if (is_array($prepData['category']) && !empty($prepData['category'])
			&& is_array($dbData) && !empty($dbData)
		) {
			$existingImages = $genericDataStructure['Images'];
			$genericDataStructure = array_merge(
				$genericDataStructure,
				$prepData['category'],
				$dbData
			);
			$savedImages = $genericDataStructure['Images'];
			if (!empty($existingImages)) {
				foreach ($existingImages as $img => $checked) {
					$genericDataStructure['Images'][$img] = (
					(is_array($savedImages) && array_key_exists($img, $savedImages)) ? $savedImages[$img] : 'false'
					);
				}
			}
		}
		$genericDataStructure['LeadtimeToShip'] = $prepData['leadtimeToShip'];
		$genericDataStructure['ConditionType'] = $prepData['ConditionType'];
		$genericDataStructure['ConditionNote'] = $prepData['ConditionNote'];

		if($prepData['ShippingTemplate'] !== null){
			$genericDataStructure['ShippingTemplate'] = $prepData['ShippingTemplate'];
		}
	}

	/* {Hook} "AmazonApply_populateGenericData": Enables you to extend or modifiy the product data.<br>
	   Variables that can be used: 
	   <ul><li>$pID: The ID of the product (Table <code>products.products_id</code>).</li>
		   <li>$product: The data of the product (Tables <code>products</code>, <code>products_description</code>,
			           <code>products_images</code> and <code>products_vpe</code>).</li>
		   <li>$genericDataStructure: The additional recommenced data of the product for Amazon (MainCategory, ProductType, BrowseNodes, ItemTitle, Manufacture, Brand, ManufacturerPartNumber, EAN, Images, BulletPoints, Description, Keywords, Attributes, LeadtimeToShip)</li>
	   </ul>
	 */
	if (($hp = magnaContribVerify('AmazonApply_populateGenericData', 1)) !== false) {
		require($hp);
	}

	//echo print_m($genericDataStructure);
	return $genericDataStructure;
}

function validateB2BTierPrices(&$data) {
	$quantityDiscountType = $data['QuantityPriceType'];
	if ($data['B2BActive'] === 'true' && $quantityDiscountType !== '') {
		$errors = array();
		$previousQuantity = -1;
		$previousPrice = -1;
		$isPercent = $quantityDiscountType === 'percent';
		for ($i = 1; $i < 6; $i++) {
			$q = 'QuantityLowerBound'.$i;
			$p = 'QuantityPrice'.$i;
			$quantity = priceToFloat($data[$q]);
			$price = priceToFloat($data[$p]);

			if (($quantity > 0 && $price <= 0) || ($quantity <= 0 && $price > 0) || $quantity < 0 || $price < 0) {
				$errors[] = $i;
			} else if ($quantity > 0 && $price > 0) {
				if ($i !== 1) {
					if ($previousQuantity >= $quantity
						|| ($isPercent && $previousPrice >= $price) || (!$isPercent && $previousPrice <= $price)
					) {
						$errors[] = $i;
					}
				}

				$previousPrice = $price;
				$previousQuantity = $quantity;
			}
		}

		if (!empty($errors)) {
			$result = '<p class="errorBox"><span class="error bold larger">' . ML_ERROR_LABEL . ':</span>';
			foreach ($errors as $tier) {
				$result .= '<br>' . sprintf(ML_AMAZON_CONF_QUANTITY_TIER_ERROR, $tier);
			}

			$result .= '</p>';
			return $result;
		}
	}

	return '';
}

$_url['view'] = 'apply';
$applySetting = array(
	'selectionName' => 'apply'
);

$applyAction = 'categoryview';

setDBConfigValue(
	array(
		$_MagnaSession['currentPlatform'] . '.' . $applySetting['selectionName'] . '.status', 'val'
	),
	$_MagnaSession['mpID'],
	getDBConfigValue(array($_MagnaSession['currentPlatform'] . '.matching.status', 'val'), $_MagnaSession['mpID'], false)
);

if (!empty($_POST) && (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax'))) {
//	echo print_m($_POST);
//	echo var_export_pre($_POST, '$_POST');
}

if (!empty($_POST) && isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
	if (isset($_GET['applyAction'])) {
		$applyAction = $_GET['applyAction'];
	}
}
//$line = __FILE__.__LINE__;die($line);
/**
 * Daten speichern
 */
if (array_key_exists('saveApplyData', $_POST) || (array_key_exists('Action', $_POST) && $_POST['Action'] === 'SaveMatching')) {
	$postAction = isset($_POST['Action']);
	$invalidAttributes = false;
	if (isset($_GET['where']) && $_GET['where'] === 'varmatchView') {
		if (isset($_POST['Variations'])) {
			parse_str_unlimited($_POST['Variations'], $postVariables);
			$_POST = $postVariables;
		}
	}
	$requiredData = array(
		'MainCategory' => ML_LABEL_MAINCATEGORY,
		'BrowseNodes' => ML_AMAZON_LABEL_APPLY_BROWSENODES,
		'ItemTitle' => ML_LABEL_PRODUCT_NAME,
		'Manufacturer' => ML_GENERIC_MANUFACTURER_NAME,
	);

	$sMainCategory = $_POST['MainCategory'];

	$pIDs = MagnaDB::gi()->fetchArray('
		SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
		 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
		       selectionname=\'' . $applySetting['selectionName'] . '\' AND
		       session_id=\'' . session_id() . '\'
	', true);

	$variationThemeAttributes = array();

	if (isset($_POST['variationTheme'])) {
		if ($_POST['variationTheme'] !== 'null') {
			$variationThemes = json_decode($_POST['variationThemes'], true);
			$variationThemeAttributes = $variationThemes[$_POST['variationTheme']]['attributes'];
		} else {
			$variationThemeAttributes = 'null';
		}
	}

	$_POST['ShopVariation'] = AmazonHelper::gi()->saveMatching(
		$sMainCategory,
		$_POST['ml']['match'],
		!$postAction,
		true,
		count($pIDs) == 1,
		$variationThemeAttributes,
        $sMainCategory
	);

	unset($_POST['ml']);

	$itemDetails = $_POST;
	unset($itemDetails['saveApplyData']);

	$errors = (!$postAction ? (isset($errors) ? $errors: '') . validateB2BTierPrices($itemDetails) : '');
	if (isset($itemDetails['Errors'])) {
		if (!$postAction) {
			$errors = $itemDetails['Errors'];
			$_POST['apply'] = true;
		}

		$invalidAttributes = true;
		unset($itemDetails['Errors']);
	}

	$leadtimePost = $itemDetails['LeadtimeToShip'];
	unset($itemDetails['LeadtimeToShip']);

	if (isset($itemDetails['variationTheme'])) {
		$itemDetails['variationTheme'] = json_encode(array($itemDetails['variationTheme'] => $variationThemeAttributes));
	} else {
		$itemDetails['variationTheme'] = null;
	}
	foreach($itemDetails as $sKey => $sValue) {
	    if(strpos($sKey, '__FromWebShop')){
	        $sMainKey = str_replace('__FromWebShop', '', $sKey);
            $itemDetails[$sMainKey] = null;
            unset($itemDetails[$sKey]);
        }
    }

	if (!empty($pIDs)) {
		$preparedTs = date('Y-m-d H:i:s');
        $verificationErrors = array(); // Collect verification errors
        $allVerifiedSuccessfully = true;
        $batchData = array(); // Collect all product data for batch insert

        // First pass: Prepare all product data and verify with Amazon API
		foreach ($pIDs as $pID) {
			$data = array_merge(
				populateGenericData($pID),
				$itemDetails
			);
			arrayEntitiesToUTF8($data);

			$leadtimeToShip = $leadtimePost;
			if ($leadtimeToShip == 'X') {
				$leadtimeToShip = $data['LeadtimeToShip'];
			}

            // Client-side validation removed - now using Amazon API verification via is_incomplete field

			$productModel = MagnaDB::gi()->fetchOne('
				SELECT products_model
				  FROM ' . TABLE_PRODUCTS . '
				 WHERE products_id=' . $pID
			);

			$c = array(
				'MainCategory' => $data['MainCategory'],
				'ProductType' => $data['ProductType'],
				'BrowseNodes' => $data['BrowseNodes'],
				'ConditionType' => $data['ConditionType'],
				'ConditionNote' => $data['ConditionNote']
			);
			unset($data['MainCategory']);
			unset($data['ProductType']);
			unset($data['BrowseNodes']);
			unset($data['ConditionType']);
			unset($data['ConditionNote']);
			$shippingTemplate = null;
			if(isset($data['ShippingTemplate'])){
				if(!isset($data['Attributes'])){
					$data['Attributes'] = array();
				}
				$shippingTemplate = $data['ShippingTemplate'];
				unset($data['ShippingTemplate']);
			}

			// recreate indexes from 0 (because if u select a top ten category you have index keys like 6 or 9)
            $c['BrowseNodes'] = array_values($c['BrowseNodes']);

            $isIncomplete = 'true'; // Default to incomplete until verification succeeds

            // Verify product with Amazon API (only if category is selected)
            if (!empty($c['MainCategory']) && $c['MainCategory'] !== 'none') {
                require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/AmazonCheckinSubmit.php');

                try {
                    $checkinSubmit = new AmazonCheckinSubmit(array(
                        'itemsPerBatch' => 1,
                        'selectionName' => 'prepare',
                        'marketplace'   => 'amazon',
                    ));

                    $verificationResult = $checkinSubmit->verifyOneItem($pID);
                    // Check verification status
                    if ($verificationResult['status'] === 'OK') {
                        // Only set to false if verification succeeded
                        $isIncomplete = 'false';
                    } else {
                        $isIncomplete = 'true';
                        $allVerifiedSuccessfully = false;

                        // Extract error messages from API response
                        $errorMessages = array();

                        // Helper function to encode UTF-8 to HTML entities (works with or without mbstring)
                        $encodeToHtmlEntities = function($str) {
                            if (function_exists('mb_convert_encoding')) {
                                // Use mbstring if available (best option - handles all Unicode)
                                return mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');
                            } else {
                                // Fallback: Use htmlentities (works without mbstring)
                                return htmlentities($str, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
                            }
                        };

                        // Check for top-level error message
                        if (isset($verificationResult['result']['ERRORMESSAGE'])) {
                            $errorMessages[] = $encodeToHtmlEntities($verificationResult['result']['ERRORMESSAGE']);
                        }

                        // Check for ERRORS array at top level (Amazon API standard format)
                        if (isset($verificationResult['result']['ERRORS']) && is_array($verificationResult['result']['ERRORS'])) {
                            foreach ($verificationResult['result']['ERRORS'] as $error) {
                                if (isset($error['ERRORMESSAGE'])) {
                                    $errorMessages[] = $encodeToHtmlEntities($error['ERRORMESSAGE']);
                                }
                            }
                        }
                        // Check for errors in RESPONSEDATA (fallback for other formats)
                        if (isset($verificationResult['result']['RESPONSEDATA']) && is_array($verificationResult['result']['RESPONSEDATA'])) {
                            foreach ($verificationResult['result']['RESPONSEDATA'] as $responseData) {
                                if (isset($responseData['ERRORS']) && is_array($responseData['ERRORS'])) {
                                    foreach ($responseData['ERRORS'] as $error) {
                                        if (isset($error['ERRORMESSAGE'])) {
                                            $errorMessages[] = $encodeToHtmlEntities($error['ERRORMESSAGE']);
                                        }
                                    }
                                }
                            }
                        }

                        // Store errors for this product
                        $product = MLProduct::gi()->getProductByIdOld($pID);
                        $verificationErrors[] = array(
                            'productId'   => $pID,
                            'productName' => $product['products_name'],
                            'errors'      => $errorMessages
                        );
                    }

                    // Always log verification result (visible in HTML source)
                    echo '<!-- Amazon Verification: Product ' . $pID . ' => ' . $verificationResult['status'];
                    if ($verificationResult['status'] === 'ERROR' && isset($verificationResult['result']['ERRORMESSAGE'])) {
                        echo ' - Error: ' . htmlspecialchars($verificationResult['result']['ERRORMESSAGE']);
                    }
                    echo ' -->';
                } catch (Exception $e) {
                    $isIncomplete = 'true';
                    $allVerifiedSuccessfully = false;
                    // Always log exception (visible in HTML source)
                    echo '<!-- Amazon Verification Exception: Product ' . $pID . ' => ' . htmlspecialchars($e->getMessage()) . ' -->';
                }
            }

            $rowData = array(
                'mpID'            => $_MagnaSession['mpID'],
                'products_id'     => $pID,
                'products_model'  => $productModel,
                'category'        => base64_encode(serialize($c)),
                'data'            => base64_encode(serialize($data)),
                'is_incomplete'   => $isIncomplete,
                // Legacy field, now using iscomplete from API verification
                'leadtimeToShip'  => $leadtimeToShip,
                'topMainCategory' => $c['MainCategory'] == null ? '' : $c['MainCategory'],
                'topProductType'  => $c['ProductType'] == null ? '' : $c['ProductType'],
                'topBrowseNode1'  => $c['BrowseNodes'] == null ? '' : json_encode([$c['MainCategory'] => $c['BrowseNodes']]),
                'ConditionType'   => $c['ConditionType'],
                'ConditionNote'   => $c['ConditionNote'],
                'PreparedTs'      => $preparedTs,
                'variation_theme' => $data['variationTheme']
            );

            if ($shippingTemplate !== null) {
                $rowData['ShippingTemplate'] = $shippingTemplate;
            }

            $batchData[] = $rowData;
        }

        // Second pass: Batch insert/update all products at once using ON DUPLICATE KEY UPDATE
        if (!empty($batchData)) {
            // Define fields to update on duplicate (all fields except primary key: mpID, products_id, products_model)
            $fieldsToUpdate = array(
                'category',
                'data',
                'is_incomplete',
                'leadtimeToShip',
                'topMainCategory',
                'topProductType',
                'topBrowseNode1',
                'ConditionType',
                'ConditionNote',
                'PreparedTs',
                'variation_theme'
            );

            // Add ShippingTemplate if it exists in any row
            $hasShippingTemplate = false;
            foreach ($batchData as $row) {
                if (isset($row['ShippingTemplate'])) {
                    $hasShippingTemplate = true;
                    break;
                }
            }
            if ($hasShippingTemplate) {
                $fieldsToUpdate[] = 'ShippingTemplate';
            }

            // Use batchinsert with ON DUPLICATE KEY UPDATE
            MagnaDB::gi()->batchinsert(TABLE_MAGNA_AMAZON_APPLY, $batchData, false, // Don't use REPLACE
                $fieldsToUpdate // Update these fields on duplicate key
            );
        }

        // Display verification errors if any
        if (!empty($verificationErrors) && !$postAction) {
            foreach ($verificationErrors as $errorInfo) {
                echo '<p class="errorBox">';
                echo '<span class="error bold larger">' . ML_ERROR_LABEL . ':</span><br>';
                if (!empty($errorInfo['errors'])) {
                    foreach ($errorInfo['errors'] as $errorMsg) {
                        // $errorMsg is already converted to HTML entities by mb_convert_encoding()
                        // Just output it directly (already safe for any charset)
                        echo '&bull; Amazon: ' . $errorMsg . '<br>';
                    }
                } else {
                    echo '&bull; Amazon verification failed<br>';
                }
                echo '</p>';
            }
            // Force stay in preparation form
            $_POST['apply'] = 'prepare';
        }

        // Delete selection ONLY if all products verified successfully
        if ($allVerifiedSuccessfully && !$postAction) {
            foreach ($pIDs as $pID) {
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $applySetting['selectionName'],
					'session_id' => session_id()
				));
			}

            // Show success message
            if (count($pIDs) == 1) {
                echo '<div class="successBox">' . ML_AMAZON_LABEL_PREPARED_SUCCESS . '</div>';
            } else {
                echo '<div class="successBox">' . ML_AMAZON_LABEL_PREPARED_SUCCESS_MULTI . '</div>';
            }
		}

		if (!$postAction && isset($errors)) {
			echo $errors;
		}
	}
}

if (!defined('MAGNA_DEV_PRODUCTLIST') || MAGNA_DEV_PRODUCTLIST !== true) { // will be done in MLProductListDependencyAmazonApplyFormAction
	/**
	 * Daten loeschen
	 */
	if (array_key_exists('removeapply', $_POST)) {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
				   selectionname=\'' . $applySetting['selectionName'] . '\' AND
				   session_id=\'' . session_id() . '\'
		', true);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array('products_model' => MagnaDB::gi()->fetchOne('
								SELECT products_model
								  FROM ' . TABLE_PRODUCTS . '
								 WHERE products_id=' . $pID
						))
					: array('products_id' => $pID);
				$where['mpID'] = $_MagnaSession['mpID'];

				MagnaDB::gi()->delete(TABLE_MAGNA_AMAZON_APPLY, $where);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $applySetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
	}

	/**
	 * Daten zuruecksetzen
	 */
	if (array_key_exists('resetapply', $_POST)) {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
				   selectionname=\'' . $applySetting['selectionName'] . '\' AND
				   session_id=\'' . session_id() . '\'
		', true);
		if (!empty($pIDs)) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$aProducts = MagnaDB::gi()->fetchArray('
					SELECT aa.products_id AS PID, aa.products_model AS PModel, aa.* 
					  FROM ' . TABLE_MAGNA_AMAZON_APPLY . ' aa
					 WHERE aa.products_id IN (\'' . implode('\', \'', $pIDs) . '\')
				');
			} else {
				$aProducts = MagnaDB::gi()->fetchArray('
					SELECT p.products_id AS PID, p.products_model AS PModel, aa.*
					  FROM ' . TABLE_MAGNA_AMAZON_APPLY . ' aa
				INNER JOIN ' . TABLE_PRODUCTS . ' p ON p.products_model=aa.products_model
					 WHERE p.products_id IN (\'' . implode('\', \'', $pIDs) . '\')
				');
			}
			foreach ($aProducts as $aRow) {
				$aRow['category'] = unserialize(base64_decode($aRow['category']));
				$aRow['data'] = unserialize(base64_decode($aRow['data']));
				if (!is_array($aRow['data']) || empty($aRow['data'])) {
					continue;
				}
				#echo print_m($aRow);

				$aNewRow = populateGenericData($aRow['PID']);
				#echo print_m($aNewRow);

				unset($aNewRow['MainCategory']);
				unset($aNewRow['ProductType']);
				unset($aNewRow['BrowseNodes']);

				$aNewRow['Attributes'] = $aRow['data']['Attributes'];
				if ($aRow['leadtimeToShipFrozen'] >= 0) {
					$aNewRow['LeadtimeToShip'] = $aRow['leadtimeToShip'];
				}

				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array('products_model' => $aRow['PModel'])
					: array('products_id' => $aRow['PID']);
				$where['mpID'] = $_MagnaSession['mpID'];

				MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_APPLY, array(
					'products_id' => $aRow['PID'],
					'products_model' => $aRow['PModel'],
					'data' => base64_encode(serialize($aNewRow)),
					'leadtimeToShip' => $aNewRow['LeadtimeToShip']
				), $where);

				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $aRow['PID'],
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $applySetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
	}
}

if (isset($_GET['edit']) && MagnaDB::gi()->recordExists(TABLE_MAGNA_AMAZON_APPLY, array(
		'mpID' => $_MagnaSession['mpID'],
		'products_id' => (int)$_GET['edit']
	))
) {
	MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
		'mpID' => $_MagnaSession['mpID'],
		'selectionname' => $applySetting['selectionName'],
		'session_id' => session_id()
	));
	MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, array(
		'pID' => (int)$_GET['edit'],
		'data' => serialize(array()),
		'mpID' => $_MagnaSession['mpID'],
		'selectionname' => $applySetting['selectionName'],
		'session_id' => session_id(),
		'expires' => gmdate('Y-m-d H:i:s')
	));
	$_POST['apply'] = 'EDITMODE';
}

/**
 * Beantragen Vorbereitung
 */
if (array_key_exists('apply', $_POST) && (!empty($_POST['apply']))) {
	$itemCount = (int)MagnaDB::gi()->fetchOne('
		SELECT count(*) FROM ' . TABLE_MAGNA_SELECTION . '
		 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
		       selectionname=\'' . $applySetting['selectionName'] . '\' AND
		       session_id=\'' . session_id() . '\'
	  GROUP BY selectionname
	');

	if ($itemCount == 1) {
		$applyAction = 'singleapplication';
	} else if ($itemCount > 1) {
		$applyAction = 'multiapplication';
	}
}

if (($applyAction === 'singleapplication') || ($applyAction === 'multiapplication') || ($applyAction === 'react')) {
//	 die('stop');
	include_once(DIR_MAGNALISTER_MODULES.'amazon/application/applicationviews.php');
} else if (isset($_GET['where']) && $_GET['where'] === 'varmatchView') {
	if (isset($_POST['Action']) && $_POST['Action'] === 'DBMatchingColumns') {
		$columns = MagnaDB::gi()->getTableCols($_POST['Table']);
		$editedColumns = array();
		foreach ($columns as $column) {
			$editedColumns[$column] = $column;
		}

		echo json_encode($editedColumns, JSON_FORCE_OBJECT);
	} else {
		if (isset($pIDs[0])) {
			$pID = $pIDs[0];
		} else {
			$pIDs = MagnaDB::gi()->fetchArray('
				SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
				 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
					   selectionname=\''.$applySetting['selectionName'].'\' AND
					   session_id=\''.session_id().'\'
			', true);
		}
        if (!empty($pIDs)) {
            $pID = reset($pIDs);
        } else {
            $pID = false;
        }
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$productModel = MagnaDB::gi()->fetchOne('
                SELECT products_model
                  FROM '.TABLE_PRODUCTS.'
                 WHERE products_id=\''.$pID.'\' LIMIT 1
            ');

			if (!$productModel) {
				$productModel = false;
			}
		} else {
			$productModel = (int)$pID;
		}

		if (isset($_POST['SelectValue'])) {
			$category = $_POST['SelectValue'];
		} else {
			$category = $_POST['MainCategory'];
		}

		$customIdentifier = !empty($_POST['CustomIdentifierValue']) ? $_POST['CustomIdentifierValue'] : '';
		if (empty($customIdentifier)) {
			$customIdentifier = !empty($_POST['ProductType']) ? $_POST['ProductType'] : '';
		}

		echo json_encode(AmazonHelper::gi()->getMPVariations($category, $productModel, true, null,$customIdentifier));
	}
} else if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {
	require_once(DIR_MAGNALISTER_MODULES.'amazon/prepare/AmazonApplyProductList.php');
	$o = new AmazonApplyProductList();
	echo $o;
} else {
	require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ApplyCategoryView.php');

	$aCV = new ApplyCategoryView(
		$current_category_id, $applySetting,  /* $current_category_id is a global variable from xt:Commerce */
		(isset($_GET['sorting']) ? $_GET['sorting'] : ''),
		(isset($_POST['tfSearch']) ? $_POST['tfSearch'] : '')
	);
	if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
		echo $aCV->renderAjaxReply();
	} else {
		echo $aCV->printForm();
	}
}
