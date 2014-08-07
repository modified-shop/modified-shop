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
 * $Id: AmazonCheckinSubmit.php 4319 2014-08-01 13:49:42Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'amazon/amazonFunctions.php');

class AmazonCheckinSubmit extends CheckinSubmit {
	private $checkinDetails = array();

	public function __construct($settings = array()) {
		global $_MagnaSession;
		/* Setzen der Currency nicht noetig, da Preisberechnungen bereits in 
		   der AmazonSummaryView Klasse gemacht wurden.
		 */
		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'].'.lang', $_MagnaSession['mpID']),
			'itemsPerBatch' => 100,
			'skuAsMfrPartNo' => getDBConfigValue(array('amazon.checkin.SkuAsMfrPartNo', 'val'), $_MagnaSession['mpID'], false),
		), $settings);

		parent::__construct($settings);
	}
	
	public function makeSelectionFromErrorLog() {}
	
	protected function generateRequestHeader() {
		return array(
			'ACTION' => 'AddItems',
			'MODE' => $this->submitSession['mode']
		);
	}
	
	protected function markAsFailed($iPID) {
		MagnaDB::gi()->insert(
			TABLE_MAGNA_AMAZON_ERRORLOG,
			array (
				'mpID' => $this->_magnasession['mpID'],
				'batchid' => '-',
				'errormessage' => ML_GENERIC_ERROR_UNABLE_TO_LOAD_PREPARE_DATA,
				'dateadded' => gmdate('Y-m-d H:i:s'),
				'additionaldata' => serialize(array(
					'PID' => $iPID,
					'SKU' => magnaPID2SKU($iPID),
				))
			)
		);
		$this->badItems[] = $iPID;
		unset($this->selection[$iPID]);
	}

	protected function getVariations($pID, $product, &$data) {
		$variationTheme = array();
		if (defined('MAGNA_FIELD_ATTRIBUTES_EAN') 
			&& MagnaDB::gi()->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES)
		) {
			$variationTheme = MagnaDB::gi()->fetchArray(eecho('
			    SELECT po.products_options_name AS VariationTitle,
			           pov.products_options_values_name AS VariationValue,
			           pa.products_attributes_id AS aID,
			           pa.options_values_price AS aPrice,
			           pa.price_prefix AS aPricePrefix,
			           pa.attributes_stock AS Quantity,
			           '.MAGNA_FIELD_ATTRIBUTES_EAN.' AS EAN
			      FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa,
			           '.TABLE_PRODUCTS_OPTIONS.' po, 
			           '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov
			     WHERE pa.products_id = \''.$pID.'\'
			           AND po.language_id = \''.getDBConfigValue(
			                $this->_magnasession['currentPlatform'].'.lang',
			                $this->_magnasession['mpID'],
			                $_SESSION['languages_id']
			           ).'\'
			           AND po.products_options_id = pa.options_id
			           AND po.products_options_name<>\'\'
			           AND pov.language_id = po.language_id
			           AND pov.products_options_values_id = pa.options_values_id
			           AND pov.products_options_values_name<>\'\'
			           AND pa.attributes_stock IS NOT NULL
			           AND '.MAGNA_FIELD_ATTRIBUTES_EAN.' IS NOT NULL
			           AND '.MAGNA_FIELD_ATTRIBUTES_EAN.'<>\'\'
			', false));
			arrayEntitiesToUTF8($variationTheme);
			#print_r($variationTheme);
			$quantityType = getDBConfigValue(
				$this->_magnasession['currentPlatform'].'.quantity.type',
				$this->_magnasession['mpID']
			);
			$quantityValue = getDBConfigValue(
				$this->_magnasession['currentPlatform'].'.quantity.value',
				$this->_magnasession['mpID'],
				0
			);
		}

		if (empty($variationTheme)) {
			return;
		}

		$tax = SimplePrice::getTaxByPID($pID);

		foreach ($variationTheme as &$item) {
			$item['SKU'] = magnaAID2SKU($item['aID']);
			unset($item['aID']);
			switch ($quantityType) {
				case 'stock': {
					# Already set.
					break;
				}
				case 'stocksub': {
					$item['Quantity'] = (int)$item['Quantity'] - $quantityValue;
					break;
				}
				default: {
					$item['Quantity'] = $quantityValue;
				}
			}
			if ($item['Quantity'] < 0) {
				$item['Quantity'] = 0;
			}
			$item['Tax'] = $tax;
			$this->simpleprice->setPrice($data['price']);
			if (getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.addkind',
					$this->_magnasession['mpID']
				) == 'percent'
			) {
				$this->simpleprice->removeTax((float)getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.factor',
					$this->_magnasession['mpID']
				));
			} else if (getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.addkind',
					$this->_magnasession['mpID']
				) == 'addition'
			) {
				$this->simpleprice->subLump((float)getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.factor',
					$this->_magnasession['mpID']
				));
			}
			$this->simpleprice->removeTax($tax);

			$this->simpleprice->addLump($item['aPrice'] * (($item['aPricePrefix'] == '-') ? -1 : 1));

			$this->simpleprice->addTax($tax);
			if (getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.addkind', 
					$this->_magnasession['mpID']
				) == 'percent'
			) {
				$this->simpleprice->addTax((float)getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.factor',
					$this->_magnasession['mpID']
				));
			} else if (getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.addkind',
					$this->_magnasession['mpID']
				) == 'addition'
			) {
				$this->simpleprice->addLump((float)getDBConfigValue(
					$this->_magnasession['currentPlatform'].'.price.factor',
					$this->_magnasession['mpID']
				));
			}

			$item['Price'] = $this->simpleprice->roundPrice()->makeSignalPrice(
					getDBConfigValue($this->_magnasession['currentPlatform'].'.price.signal', $this->_magnasession['mpID'], '')
			    )->getPrice();
			unset($item['aPrice']);
			unset($item['aPricePrefix']);
			
			if ($this->settings['skuAsMfrPartNo']
				&& (!isset($item['ManufacturerPartNumber']) || empty($item['ManufacturerPartNumber']))
			) {
				$item['ManufacturerPartNumber'] = $item['SKU'];
			}
		}
	
		$data['submit']['Variations'] = $variationTheme;
		#echo print_m($variationTheme);
	}

	protected function appendAdditionalData($pID, $product, &$data) {

		$conditionType = getDBConfigValue('amazon.itemCondition', $this->_magnasession['mpID']);
		
		$productMatching = $productApply = false;
		
		if ($data['quantity'] < 0) {
			$data['quantity'] = 0;
		}

		if (($productMatching = MagnaDB::gi()->fetchRow('
			SELECT * FROM `'.TABLE_MAGNA_AMAZON_PROPERTIES.'`
			 WHERE asin<>\'\' AND 
			      '.((getDBConfigValue('general.keytype', '0') == 'artNr')
			            ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
			            : 'products_id=\''.$pID.'\''
			        ).' AND
			       mpID=\''.$this->_magnasession['mpID'].'\'
			 LIMIT 1
		')) !== false) {
			$data['submit']['SKU'] = magnaPID2SKU($pID);
			$data['submit']['ASIN'] = $productMatching['asin'];
			$data['submit']['ConditionType'] = empty($productMatching['item_condition']) ? $conditionType : $productMatching['item_condition'];
			$data['submit']['Price'] = $data['price'];
			$data['submit']['Quantity'] = $data['quantity'];
			$data['submit']['WillShipInternationally'] = $productMatching['will_ship_internationally'];
			$data['submit']['ConditionNote'] = sanitizeProductDescription($productMatching['item_note']);
			if ($productMatching['leadtimeToShip'] > 0) {
				$data['submit']['LeadtimeToShip'] = $productMatching['leadtimeToShip'];
			}

		} else if (($productApply = MagnaDB::gi()->fetchRow('
			SELECT category, data, leadtimeToShip
			  FROM `'.TABLE_MAGNA_AMAZON_APPLY.'`
			 WHERE data<>\'\'
			       AND '.((getDBConfigValue('general.keytype', '0') == 'artNr')
			            ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
			            : 'products_id=\''.$pID.'\''
			       ).'
			       AND is_incomplete=\'false\'
			       AND mpID=\''.$this->_magnasession['mpID'].'\'
			 LIMIT 1
		')) !== false) {
			$productApply['data'] = (array)@unserialize(@base64_decode($productApply['data']));
			$productApply['data'] = array_merge(
				(array)@unserialize(@base64_decode($productApply['category'])),
				$productApply['data']
			);
			unset($productApply['category']);
			if (!is_array($productApply['data']) || empty($productApply['data'])) {
				$this->markAsFailed($pID);
				return;
			} 
			$data['submit'] = array_merge(
				array(
					'SKU' => magnaPID2SKU($pID),
					'Price' => $data['price'],
					'Quantity' => $data['quantity'],
					'ConditionType' => $conditionType,
				),
				$productApply['data']
			); 
			if (!empty($data['submit']['BrowseNodes'])) {
				foreach ($data['submit']['BrowseNodes'] as $i => $bn) {
					if ($bn == 'null') {
						unset($data['submit']['BrowseNodes'][$i]);
					}
				}
			}
			$imagePath = getDBConfigValue('amazon.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
			$imagePath = trim($imagePath, '/ ').'/';
			$images = array();
			if (!empty($data['submit']['Images'])) {
				foreach ($data['submit']['Images'] as $image => $use) {
					if ($use == 'true') {
						$images[] = $imagePath.$image;
					}
				}
				$data['submit']['Images'] = $images;
			}
			
			if ($productApply['leadtimeToShip'] > 0) {
				$data['submit']['LeadtimeToShip'] = $productApply['leadtimeToShip'];
			}
			
			if ((float)$product['products_weight'] > 0) {
				$data['submit']['Weight'] = array (
					'Unit' => 'kg',
					'Value' => $product['products_weight'],
				);
			}
		} else {
			$this->markAsFailed($pID);
			return;
		}
		
		# BasePrice = Grundpreis
		if ((isset($product['products_vpe_name'])) && ($product['products_vpe_value'] > 0)) {
			$data['submit']['BasePrice'] = array (
				'Unit'  => htmlspecialchars(trim($product['products_vpe_name'])),
				'Value' => $product['products_vpe_value'],
			);
		}
		
		if ($productApply === false) {
			return;
		}
		
		if (
			(!isset($data['submit']['ManufacturerPartNumber']) || empty($data['submit']['ManufacturerPartNumber']))
			&& $this->settings['skuAsMfrPartNo']
		) {
			$data['submit']['ManufacturerPartNumber'] = $data['submit']['SKU'];
		}
		
		$this->getVariations($pID, $product, $data);
	}

	protected function processSubmitResult($result) { }

	protected function filterSelection() {
		/*
		foreach ($this->selection as $pID => &$data) {
			if ((int)$data['submit']['Quantity'] == 0) {
				unset($this->selection[$pID]);
				$this->disabledItems[] = $pID;
			}
		}
		*/
	}

	protected function postSubmit() {
		try {
			//*
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
			//*/
		} catch (MagnaException $e) {
			$this->submitSession['api']['exception'] = $e;
			$this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();
		}
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode'   => 'listings',
		), true);
	}

}