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
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/RicardoHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoProductSaver.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

class RicardoCheckinSubmit extends MagnaCompatibleCheckinSubmit {
	private $bVerify = false;
	private $oLastException = null;

	public function __construct($settings = array()) {
		global $_MagnaSession;

		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], ''),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
			'keytype' => getDBConfigValue('general.keytype', '0'),
			'itemsPerBatch' => 10,
			'mlProductsUseLegacy' => false,
		), $settings);

		#$this->summaryAddText = ML_RICARDO_TEXT_AFTER_UPLOAD;

		parent::__construct($settings);
	}
	
	protected function generateRequestHeader() {
		if (isset($_GET['where']) && $_GET['where'] === 'getItemsFee') {
			return array(
				'ACTION' => 'GetArticlesFee'
			);
		}
		
		return array_merge(parent::generateRequestHeader(), array('UPLOAD' => true));
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
		MLProduct::gi()->setPriceConfig(RicardoHelper::loadPriceSettings($this->mpID));
		MLProduct::gi()->setQuantityConfig(RicardoHelper::loadQuantitySettings($this->mpID));
		MLProduct::gi()->useMultiDimensionalVariations(true);
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'purgeVariations' => true,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
		));
	}

	protected function appendAdditionalData($iPID, $aProduct, &$aData) {
		if (isset($_GET['where']) && $_GET['where'] === 'getItemsFee') {
			$this->appendAdditionalDataForItemsFee($iPID, $aProduct, $aData);
			return;
		}

		$aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM ' . TABLE_MAGNA_RICARDO_PROPERTIES . '
			 WHERE ' . ($this->settings['keytype'] == 'artNr'
				? 'products_model = "' . MagnaDB::gi()->escape($aProduct['ProductsModel']) . '"'
				: 'products_id = "' . $iPID . '"'
			) . '
				   AND mpID = ' . $this->_magnasession['mpID']
		);

		// Will not happen in submit cycle but can happen in loadProductByPId
		if (empty($aPropertiesRow)) {
			$aData['submit'] = array();
			return;
		}

		$aData['submit']['SKU'] = $aData['submit']['ParentSKU'] = ($this->settings['keytype'] == 'artNr') ? $aProduct['MarketplaceSku'] : $aProduct['MarketplaceId'];
		$aData['submit']['Descriptions'] = array();

		if ($aPropertiesRow['LangDe'] === 'true') {
			$sTitle = html_entity_decode(fixHTMLUTF8Entities($aPropertiesRow['TitleDe']), ENT_COMPAT, 'UTF-8');
			$sSubtitle = html_entity_decode(fixHTMLUTF8Entities($aPropertiesRow['SubtitleDe']), ENT_COMPAT, 'UTF-8');
			$sDescription = html_entity_decode(fixHTMLUTF8Entities($aPropertiesRow['DescriptionDe']), ENT_COMPAT, 'UTF-8');

			$aData['submit']['Descriptions']['DE'] = array(
				'Title' => $sTitle,
				'Subtitle' => $sSubtitle,
				'Description' => $sDescription
			);
		}

		if ($aPropertiesRow['LangFr'] === 'true') {
			$sTitle = html_entity_decode(fixHTMLUTF8Entities($aPropertiesRow['TitleFr']), ENT_COMPAT, 'UTF-8');
			$sSubtitle = html_entity_decode(fixHTMLUTF8Entities($aPropertiesRow['SubtitleFr']), ENT_COMPAT, 'UTF-8');
			$sDescription = html_entity_decode(fixHTMLUTF8Entities($aPropertiesRow['DescriptionFr']), ENT_COMPAT, 'UTF-8');

			$aData['submit']['Descriptions']['FR'] = array(
				'Title' => $sTitle,
				'Subtitle' => $sSubtitle,
				'Description' => $sDescription
			);
		}

		if (isset($aPropertiesRow['DescriptionTemplate']) && $aPropertiesRow['DescriptionTemplate'] !== '-1') {
			$aData['submit']['DescriptionTemplate'] = $aPropertiesRow['DescriptionTemplate'];
		}

		if ($aPropertiesRow['Warranty'] == 0) {
			$aData['submit']['WarrantyDescription'] = array();

			if ($aPropertiesRow['LangDe'] === 'true') {
				$aData['submit']['WarrantyDescription']['DE'] = $aPropertiesRow['WarrantyDescriptionDe'];
			}

			if ($aPropertiesRow['LangFr'] === 'true') {
				$aData['submit']['WarrantyDescription']['FR'] = $aPropertiesRow['WarrantyDescriptionFr'];
			}
		}

		$aData['submit']['Quantity'] = $aProduct['Quantity'];

		$imagePath = getDBConfigValue($this->marketplace . '.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
		$imagePath = trim($imagePath, '/ ').'/';

		if (empty($aPropertiesRow['PictureUrl']) === false) {
			$pictureUrls = json_decode($aPropertiesRow['PictureUrl']);

			foreach ($pictureUrls as $image => $use) {
				if ($use == 'true') {
					$aData['submit']['Images'][] = array(
						'URL' => $imagePath . $image
					);
				}
			}
		}

		$aData['submit']['MarketplaceCategories'] = array(
			$aPropertiesRow['MarketplaceCategories']
		);

		$aData['submit']['Price'] = null;

		$simplePrice = new SimplePrice(null, getCurrencyFromMarketplace($this->_magnasession['mpID']));
		$productTax = SimplePrice::getTaxByPID($aProduct['ProductId']);
		$taxFromConfig = getDBConfigValue($this->marketplace . '.checkin.mwst', $this->_magnasession['mpID']);
		$priceSignal = getDBConfigValue($this->marketplace . '.price.signal', $this->mpID);

		$simplePrice->setFinalPriceFromDB($aProduct['ProductId'], $this->_magnasession['mpID']);
		if (isset($taxFromConfig) && $taxFromConfig !== '') {
			$simplePrice
				->removeTax($productTax)
				->addTax($taxFromConfig)
				->makeSignalPrice($priceSignal);
		}

		$ricardoPrice = $simplePrice
				->roundPrice()
				->getPrice();

		if ($aPropertiesRow['BuyingMode'] === 'buy_it_now' || ($aPropertiesRow['BuyingMode'] === 'auction' && $aPropertiesRow['EnableBuyNowPrice'] === 'on')) {
			$aData['submit']['Price'] =
				isset($aPropertiesRow['BuyNowPrice'])
				? $aPropertiesRow['BuyNowPrice']
				: $ricardoPrice;
		} else if ($aPropertiesRow['BuyingMode'] === 'auction' && $aPropertiesRow['BuyNowPrice'] !== null && $aPropertiesRow['BuyNowPrice'] !== 0) {
			$aData['submit']['Price'] = $aPropertiesRow['BuyNowPrice'];
		}

		// Needed for variation calculation
		$aData['price'] = (isset($aData['submit']['Price']) && !empty($aData['submit']['Price']))
			? $aData['submit']['Price']
			: $aData['price'];

		$aData['submit']['ListingType'] = $aPropertiesRow['BuyingMode'];

		if ($aPropertiesRow['BuyingMode'] === 'auction') {
			$aData['submit']['Auction'] = array(
				'StartPrice' => $aPropertiesRow['StartPrice'],
				'Increment' => $aPropertiesRow['Increment'],
			);
		}

		$aData['submit']['ConditionType'] = $aPropertiesRow['ArticleCondition'];

		$shippingService = array(
			'Service' => $aPropertiesRow['ShippingDetails'],
			'Cost' => $aPropertiesRow['ShippingCost'],
			'Cumulative' => $aPropertiesRow['ShippingCumulative'] === 'true' ? 1 : 0,
		);

		if ($aPropertiesRow['PackageSize'] !== null) {
			$shippingService['PackageSize'] = $aPropertiesRow['PackageSize'];
		}

		$aData['submit']['ShippingServices'] = array(
			$aData['submit']['ShippingServices'] = $shippingService
		);

		$aData['submit']['DeliveryCondition'] = $aPropertiesRow['ShippingDetails'];
		if ($aPropertiesRow['ShippingDetails'] === '0') {
			$aData['submit']['DeliveryDescription'] = array();

			if ($aPropertiesRow['LangDe'] === 'true') {
				$aData['submit']['DeliveryDescription']['DE'] = $aPropertiesRow['ShippingDescriptionDe'];
			}

			if ($aPropertiesRow['LangFr'] === 'true') {
				$aData['submit']['DeliveryDescription']['FR'] = $aPropertiesRow['ShippingDescriptionFr'];
			}
		}

		$aData['submit']['MaxRelistCount'] = $aPropertiesRow['MaxRelistCount'];
		$aData['submit']['StartTime'] = $aPropertiesRow['StartDate'];
		$aData['submit']['EndTime'] = $aPropertiesRow['EndTime'];
		$aData['submit']['ListingDuration'] = $aPropertiesRow['Duration'];
		$aData['submit']['PaymentMethods'] = json_decode($aPropertiesRow['PaymentDetails']);

		if (in_array(0, $aData['submit']['PaymentMethods'])) {
			$aData['submit']['PaymentDescription'] = array();

			if ($aPropertiesRow['LangDe'] === 'true') {
				$aData['submit']['PaymentDescription']['DE'] = $aPropertiesRow['PaymentdetailsDescriptionDe'];
			}

			if ($aPropertiesRow['LangFr'] === 'true') {
				$aData['submit']['PaymentDescription']['FR'] = $aPropertiesRow['PaymentdetailsDescriptionFr'];
			}
		}

		if ((empty($aPropertiesRow['FirstPromotion']) === false && $aPropertiesRow['FirstPromotion'] !== '-1') || (empty($aPropertiesRow['SecondPromotion']) === false && $aPropertiesRow['SecondPromotion'] !== '-1')) {
			$aData['submit']['Promotions'] = array();

			if (empty($aPropertiesRow['FirstPromotion']) === false && $aPropertiesRow['FirstPromotion'] !== '-1') {
				$aData['submit']['Promotions'][] = $aPropertiesRow['FirstPromotion'];
			}

			if (empty($aPropertiesRow['SecondPromotion']) === false && $aPropertiesRow['SecondPromotion'] !== '-1') {
				$aData['submit']['Promotions'][] = $aPropertiesRow['SecondPromotion'];
			}
		}

		if (getDBConfigValue(array($this->marketplace.'.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false)) {
			$aData['submit']['ShippingTime'] = getDBConfigValue(
				array($this->marketplace.'.leadtimetoshipmatching.values', $aProduct['ShippingTimeId']),
				$this->mpID,
				$aPropertiesRow['Availability']
			);
		} else {
			$aData['submit']['ShippingTime'] = $aPropertiesRow['Availability'];
		}

		$aData['submit']['ItemTax'] = $aProduct['TaxPercent'];

		$variationSupport = true;

		/* {Hook} "RicardoCheckinSubmit_VariationSupport": Enables you to turn off variation support.<br>
			Variables that can be used:
			<ul>
				<li>$marketplace: The name of the marketplace.</li>
				<li>$variationSupport: Use to enable or disable variations.</li>
			</ul>
		*/
		if (($hp = magnaContribVerify('RicardoCheckinSubmit_VariationSupport', 1)) !== false) {
			$marketplace = $this->marketplace;

			require($hp);
		}

		if ($variationSupport === true) {
			$this->getVariations($iPID, $aProduct, $aData);
			if (array_key_exists('Variations', $aData['submit']) && count($aData['submit']['Variations']) > 0) {
				$aData['submit']['Quantity'] = 0; // is sum of variation qty
				foreach ($aData['submit']['Variations'] as $aVariation) {
					$aData['submit']['Quantity'] += max($aVariation['Quantity'], 0); // if qty is negativ
				}
			}
		}
		if ($aData['submit']['Quantity'] < 1) {
			$aData['submit'] = array();
			$this->badItems[] = $iPID;
			unset($this->selection[$iPID]);
		}
	}
	
	protected function appendAdditionalDataForItemsFee($iPID, $aProduct, &$aData) {
		$aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM ' . TABLE_MAGNA_RICARDO_PROPERTIES . '
			 WHERE ' . ($this->settings['keytype'] == 'artNr'
				? 'products_model = "' . MagnaDB::gi()->escape($aProduct['ProductsModel']) . '"'
				: 'products_id = "' . $iPID . '"'
			) . '
			       AND mpID = ' . $this->_magnasession['mpID']
		);

		// Will not happen in submit cycle but can happen in loadProductByPId
		if (empty($aPropertiesRow)) {
			$aData['submit'] = array();
			return;
		}

		$aData['submit']['Quantity'] = $aProduct['Quantity'];


		$imagePath = getDBConfigValue($this->marketplace . '.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
		$imagePath = trim($imagePath, '/ ').'/';

		if (empty($aPropertiesRow['PictureUrl']) === false) {
			$pictureUrls = json_decode($aPropertiesRow['PictureUrl']);
			$aData['submit']['ImageCount'][] = count($pictureUrls);
		}

		$aData['submit']['Category'] = $aPropertiesRow['MarketplaceCategories'];

		$aData['submit']['Price'] = null;
		
		$simplePrice = new SimplePrice(null, getCurrencyFromMarketplace($this->_magnasession['mpID']));
		$productTax = SimplePrice::getTaxByPID($aProduct['ProductId']);
		$taxFromConfig = getDBConfigValue($this->marketplace . '.checkin.mwst', $this->_magnasession['mpID']);

		$simplePrice->setFinalPriceFromDB($aProduct['ProductId'], $this->_magnasession['mpID']);
		if (isset($taxFromConfig) && $taxFromConfig !== '') {
			$simplePrice
				->removeTax($productTax)
				->addTax($taxFromConfig);
		}

		$ricardoPrice = $simplePrice
				->roundPrice()
				->getPrice();
		
		if ($aPropertiesRow['BuyingMode'] === 'buy_it_now' || ($aPropertiesRow['BuyingMode'] === 'auction' && $aPropertiesRow['EnableBuyNowPrice'] === 'on')) {
			$aData['submit']['Price'] =
				isset($aPropertiesRow['BuyNowPrice'])
				? $aPropertiesRow['BuyNowPrice']
				: $ricardoPrice;
		} else if ($aPropertiesRow['BuyingMode'] === 'auction' && $aPropertiesRow['BuyNowPrice'] !== null && $aPropertiesRow['BuyNowPrice'] !== 0) {
			$aData['submit']['Price'] = $aPropertiesRow['BuyNowPrice'];
		}

		$aData['submit']['ListingType'] = $aPropertiesRow['BuyingMode'];
		$aData['submit']['StartPrice'] = $aPropertiesRow['StartPrice'];
		$aData['submit']['ConditionType'] = $aPropertiesRow['ArticleCondition'];
		$aData['submit']['StartDate'] = $aPropertiesRow['StartDate'];
		$aData['submit']['Promotions'] = array();

		if (empty($aPropertiesRow['FirstPromotion']) === false && $aPropertiesRow['FirstPromotion'] !== '-1') {
			$aData['submit']['Promotions'][] = $aPropertiesRow['FirstPromotion'];
		}

		if (empty($aPropertiesRow['SecondPromotion']) === false && $aPropertiesRow['SecondPromotion'] !== '-1') {
			$aData['submit']['Promotions'][] = $aPropertiesRow['SecondPromotion'];
		}

		$variationSupport = true;

		// also use hook for get item fee
		if (($hp = magnaContribVerify('RicardoCheckinSubmit_VariationSupport', 1)) !== false) {
			$marketplace = $this->marketplace;

			require($hp);
		}

		if ($variationSupport === true) {
			$this->getVariations($iPID, $aProduct, $aData);
		}
	}

	protected function preSubmit(&$request) {
		if (isset($_GET['where']) && $_GET['where'] === 'getItemsFee') {
			$this->preSubmitItemsFee($request);
			return;
		}

		$request['DATA'] = array();
		foreach ($this->selection as $iProductId => &$aProduct) {
			// Prepare product
			$deDescription = '';
			$frDescription = '';
			if (isset($aProduct['submit']['Descriptions']['DE']['Title'])) {
				$deDescription = html_entity_decode(fixHTMLUTF8Entities($aProduct['submit']['Descriptions']['DE']['Description']));
				MLProduct::gi()->setLanguage($this->settings['language']['DE']);
				MLProduct::gi()->setPriceConfig(RicardoHelper::loadPriceSettings($this->mpID));
				MLProduct::gi()->setQuantityConfig(RicardoHelper::loadQuantitySettings($this->mpID));

				$productDe = MLProduct::gi()->getProductById($iProductId);
			}

			if (isset($aProduct['submit']['Descriptions']['FR']['Title'])) {
				$frDescription = html_entity_decode(fixHTMLUTF8Entities($aProduct['submit']['Descriptions']['FR']['Description']));
				MLProduct::gi()->setLanguage($this->settings['language']['FR']);
				MLProduct::gi()->setPriceConfig(RicardoHelper::loadPriceSettings($this->mpID));
				MLProduct::gi()->setQuantityConfig(RicardoHelper::loadQuantitySettings($this->mpID));

				$productFr = MLProduct::gi()->getProductById($iProductId);
			}
			
			//add variations
			$product = MLProduct::gi()->getProductById($iProductId);
			if (array_key_exists('Variations', $product)) {
				unset($aProduct['submit']['Variations']);// avoid double entries
				foreach ($product['Variations'] as $variation) {
					$variation['Price'] = $variation['Price']['Price'];
					$variation['SKU'] = ($this->settings['keytype'] == 'artNr') ? $variation['MarketplaceSku'] : $variation['MarketplaceId'];
					unset($variation['Variation']); // is in $productDe or $productFr
					$aProduct['submit']['Variations'][] = $variation;
				}
			}
			
			if (empty($aProduct['submit']['Variations'])) {
				if (strpos($deDescription, '#VARIATIONDETAILS#') !== false) {
					$aProduct['submit']['Descriptions']['DE']['Description'] = str_replace('#VARIATIONDETAILS#', '', $deDescription);
				}

				if (strpos($frDescription, '#VARIATIONDETAILS#') !== false) {
					$aProduct['submit']['Descriptions']['FR']['Description'] = str_replace('#VARIATIONDETAILS#', '', $frDescription);
				}
				
				$request['DATA'][] = $aProduct['submit'];
				continue;
			}


			foreach ($aProduct['submit']['Variations'] as $aVariation) {
				$aVariationData = $aProduct;
				unset($aVariationData['submit']['Variations']);
				unset($aVariation['ShippingTime']);// get matched shipping time from article
				foreach ($aVariation as $sParameter => $mParameterValue) {
					if (array_key_exists($sParameter, $aVariationData['submit'])) {
						$aVariationData['submit'][$sParameter] = $mParameterValue;
					}
				}

				if (isset($productDe)) {
					foreach ($productDe['Variations'] as $v) {
						if (    (    ($this->settings['keytype'] == 'artNr')
						          && ($v['MarketplaceSku'] === $aVariation['SKU'])) 
						     || (    ($this->settings['keytype'] != 'artNr')
						          && ($v['MarketplaceId'] === $aVariation['MarketplaceId'])) 
						) {
							$attributes = array();
							foreach ($v['Variation'] as $var) {
								$attributes[] = "{$var['Name']} - {$var['Value']}";
							}

							if (strpos($deDescription, '#VARIATIONDETAILS#') !== false)  {
								$aVariationData['submit']['Descriptions']['DE']['Description'] =
									str_replace('#VARIATIONDETAILS#', html_entity_decode(fixHTMLUTF8Entities(implode(' ', $attributes))), $deDescription);
							}
						}
					}
				}

				if (isset($productFr)) {
					foreach ($productFr['Variations'] as $v) {
						if (    (    ($this->settings['keytype'] == 'artNr')
						          && ($v['MarketplaceSku'] === $aVariation['SKU'])) 
						     || (    ($this->settings['keytype'] != 'artNr')
						          && ($v['MarketplaceId'] === $aVariation['MarketplaceId'])) 
						) {
							$attributes = array();
							foreach ($v['Variation'] as $var) {
								$attributes[] = "{$var['Name']} - {$var['Value']}";
							}

							if (strpos($frDescription, '#VARIATIONDETAILS#') !== false) {
								$aVariationData['submit']['Descriptions']['FR']['Description'] =
									str_replace('#VARIATIONDETAILS#', html_entity_decode(fixHTMLUTF8Entities(implode(' ', $attributes))), $frDescription);
							}
						}
					}
				}

				$request['DATA'][] = $aVariationData['submit'];
			}
		}
		arrayEntitiesToUTF8($request['DATA']);
	}

	protected function preSubmitItemsFee(&$request) {
		$request['DATA'] = array();
		foreach ($this->selection as $iProductId => &$aProduct) {
			if (empty($aProduct['submit']['Variations'])) {
				$request['DATA'][] = $aProduct['submit'];
				continue;
			}

			foreach ($aProduct['submit']['Variations'] as $aVariation) {
				$aVariationData = $aProduct;
				unset($aVariationData['submit']['Variations']);
				$aVariationData['submit']['Quantity'] = $aVariation['Quantity'];

				$request['DATA'][] = $aVariationData['submit'];
			}
		}

		arrayEntitiesToUTF8($request['DATA']);
	}

	protected function markAsFailed($sku) {
		$iPID = magnaSKU2pID($sku);
		$this->badItems[] = $iPID;
		unset($this->selection[$iPID]);
	}

	protected function postSubmit() {
        $this->ajaxReply['uploadNotSync'] = true;
        parent::postSubmit();
	}
	
	protected function afterSendRequest() {
		if ($this->lastRequest['ACTION'] === 'GetArticlesFee') {
			$this->deleteSelection = false;
			if (isset($this->lastResponse['DATA']['TotalFee'])) {
				$this->ajaxReply['status'] = 'ok';
				$this->ajaxReply['totalfee'] = $this->lastResponse['DATA']['TotalFee'];
			} else {
				$this->ajaxReply['status'] = 'error';
				$this->ajaxReply['error'] = $this->lastResponse['ERRORS'][0]['ERRORMESSAGE'];
			}
		}
	}
	
	protected function initSelection($offset, $limit) {
		if (isset($_GET['where']) && $_GET['where'] === 'getItemsFee') {
			// join used to support sorting by products_name (same sort order as summary view)
			$newSelectionResult = MagnaDB::gi()->query('
			    SELECT ms.pID, ms.data
			      FROM '.TABLE_MAGNA_SELECTION.' ms
			 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pd.products_id = ms.pID AND pd.language_id = "'.reset($this->settings['language']).'"
			     WHERE     ms.mpID = \''.$this->mpID.'\'
			           AND ms.selectionname = \''.$this->settings['selectionName'].'\'
			           AND ms.session_id = \''.session_id().'\'
			           AND ms.data NOT LIKE \'%s:8:"selected";b:0;%\'
			  ORDER BY pd.products_name ASC
			');
			$this->selection = array();
			while ($row = MagnaDB::gi()->fetchNext($newSelectionResult)) {
				$this->selection[$row['pID']] = unserialize($row['data']);
			}
		} else {
			parent::initSelection($offset, $limit);
		}
	}
	
	protected function processSubmitResult($result) {
		if (array_key_exists('ERRORS', $result)
			&& is_array($result['ERRORS'])
			&& !empty($result['ERRORS'])
		) {
			foreach ($result['ERRORS'] as $err) {
				$ad = array ();
				if (isset($err['DETAILS']['SKU'])) {
					$ad['SKU'] = $err['DETAILS']['SKU'];
				}
				$err = array (
					'mpID' => $this->mpID,
					'errormessage' => $err['ERRORMESSAGE'],
					'dateadded' => gmdate('Y-m-d H:i:s'),
					'additionaldata' => serialize($ad),
				);
				MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
			}
		}
	}

	protected function deleteSelection() {
		// for ricardo key is not product_id (check afterPopulateSelectionWithData())
		foreach ($this->selection as $key => &$data) {
			$sParentSKU = $data['submit']['ParentSKU'];
			$this->badItems[] = magnaSKU2pID($sParentSKU, true);
		}
		$this->badItems = array_merge(
			$this->badItems,
			$this->disabledItems
		);
		if (!empty($this->badItems)) {
			MagnaDB::gi()->delete(
				TABLE_MAGNA_SELECTION,
				array(
					'mpID' => $this->mpID,
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id()
				),
				'AND pID IN ('.implode(', ', $this->badItems).')'
			);
		}
	}

	public function renderBasicHTMLStructure() {
		//$this->initSelection(0, $this->settings['itemsPerBatch']);
		//$this->populateSelectionWithData();

		//$html = print_m($this->selection, '$this->selection').'
		$html = '
			<div id="checkinSubmit">
				<h1 id="threeDots">
					<span id="headline">'.ML_HEADLINE_SUBMIT_PRODUCTS.'</span><span class="alldots"
						><span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>&nbsp;
					</span>
				</h1>
				<hr/>
				<p>'.ML_NOTICE_SUBMIT_PRODUCTS.'</p>
				<div id="apiException" style="display:none;"><p class="errorBox">'.ML_ERROR_SUBMIT_PRODUCTS.'</p></div>
				<div id="uploadprogress" class="progressBarContainer">
					<div class="progressBar"></div>
					<div class="progressPercent"></div>
				</div>
				<br>
				<div id="checkinSubmitStatus" class="paddingBottom"></div>
				<div style="display: none; text-align: left; background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.2); border-radius: 3px 3px 3px 3px; margin-bottom: 1em; padding: 0 7px 7px;" id="checkinSubmitDebug">'.print_m($this->submitSession, 'submitSession').'</div>
			</div>
		';

		ob_start();?>
<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/classes/CheckinSubmit.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	var csaj = new GenericCheckinSubmitAjaxController();
	csaj.setTriggerURL('<?php echo toURL($this->realUrl, array('kind' => 'ajax'), true); ?>');
	csaj.addLocalizedMessages({
		'TitleInformation' : <?php echo json_encode(ML_LABEL_INFORMATION); ?>,
		'TitleAjaxError': 'Ajax '+<?php echo json_encode(ML_ERROR_LABEL); ?>,
		'LabelStatus': <?php echo json_encode(ML_GENERIC_STATUS); ?>,
		'LabelError': <?php echo json_encode(ML_ERROR_LABEL); ?>,
		'MessageUploadFinal': <?php echo json_encode(ML_STATUS_SUBMIT_PRODUCTS_SUMMARY.$this->summaryAddText); ?>,
		'MessageUploadStatus': <?php echo json_encode(ML_STATUS_SUBMIT_PRODUCTS); ?>,
		'MessageUploadFatalError': <?php echo json_encode(ML_STATUS_SUBMIT_PHP_ERROR); ?>,
		'MessageUploadNotSync': <?php echo json_encode(ML_RICARDO_PRODUCTS_NOT_SYNCRONIZED); ?>
	});
	csaj.setInitialUploadStatus('<?php echo $this->submitSession['state']['total']; ?>');
	csaj.doAbort(<?php echo isset($_GET['abort']) ? 'true' : 'false'; ?>);
	csaj.runSubmitBatch();
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;
	}
}
