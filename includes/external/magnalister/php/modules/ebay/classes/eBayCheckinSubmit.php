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
 * $Id: CheckinCategoryView.php 167 2013-02-08 12:00:00Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/EbayHelper.php');

class eBayCheckinSubmit extends CheckinSubmit {
	private $verify = false;
	private $lastException = null;

	protected $ignoreErrors = true;

	protected $properties = array();

	private $errorVariationMatchingFailed = false; // for the case all data are dropped in preSubmit

	public function __construct($settings = array()) {
		global $_MagnaSession;
		$settings = array_merge(array(
			'mlProductsUseLegacy' => false,
			'itemsPerBatch'   => 1,
			'language' => getDBConfigValue($_MagnaSession['currentPlatform'].'.lang', $_MagnaSession['mpID']),
			'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
		), $settings);


		parent::__construct($settings);
		if (getDBConfigValue('ebay.zerostockontrol', $this->mpID, false) !== 'true') {
			$this->summaryAddText = "<br />\n".ML_EBAY_SUBMIT_ADD_TEXT_ZERO_STOCK_ITEMS_REMOVED;
		} else {
			$this->summaryAddText = "<br />\n".ML_EBAY_SUBMIT_ADD_TEXT_ZERO_STOCK_ITEMS_ONLY_UPDATED;
		}
		#if (getDBConfigValue(array('ebay.picturepack', 'val'), $this->_magnasession['mpID'], false)) {
		$this->summaryAddText .= "<br />\n<br />\n".ML_EBAY_SUBMIT_ADD_TEXT_ASYNC_EXPLANATION;
		#}
	}

	protected function setUpMLProduct() {
		parent::setUpMLProduct();

		MLProduct::gi()->useMultiDimensionalVariations(true);
		MLProduct::gi()->setOptions(array(
			'sameVariationsToAttributes' => false,
			'purgeVariations' => true,
			'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
		));
	}

	protected function generateRequestHeader() {
		# das Request braucht nur action, subsystem und data
		return array(
			'ACTION' => ($this->verify ? 'VerifyAddItems' : 'AddItems'),
			'SUBSYSTEM' => 'eBay'
		);
	}

	protected function initSelection($offset, $limit) {
		if ($this->verify) {
			# fuer Verify nur Artikel mit gueltiger Menge und Preis nehmen, ausser man findet keine
			$verifySelectionResult = MagnaDB::gi()->query('
			    SELECT ms.pID pID, ms.data data
			      FROM '.TABLE_MAGNA_SELECTION.' ms, '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
			     WHERE mpID="'.$this->_magnasession['mpID'].'" AND
			           selectionname="'.$this->settings['selectionName'].'" AND
			           session_id="'.session_id().'" AND
			           pd.language_id = "'.$this->settings['language'].'" AND
			           p.products_quantity > 0 AND p.products_price > 0.0 AND
			           p.products_id = ms.pID AND
			           pd.products_id = ms.pID
			  ORDER BY pd.products_name ASC
			     LIMIT '.$offset.','.$limit.'
			');
			$this->selection = array();
			while ($row = MagnaDB::gi()->fetchNext($verifySelectionResult)) {
				$this->selection[$row['pID']] = unserialize($row['data']);
			}
			if (!empty($this->selection)) {
				return;
			}
		}
		parent::initSelection($offset, $limit);
	}

	protected function getProduct($pID) {
		if (!$this->settings['mlProductsUseLegacy']) {
			// (pre)load prepare-data and check what listing type it is
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$where = "products_model = '".MagnaDB::gi()->escape(MagnaDB::gi()->fetchOne("SELECT products_model FROM ".TABLE_PRODUCTS." WHERE products_id = '".$pID."'"))."'";
			} else {
				$where = "products_id = '".$pID."'";
			}
			$this->properties = MagnaDB::gi()->fetchRow('
				SELECT * 
				FROM '.TABLE_MAGNA_EBAY_PROPERTIES.' 
				WHERE '.$where.' AND mpID = '.$this->_magnasession['mpID']
			);
			MLProduct::gi()->setPriceConfig(
				EbayHelper::getPriceSettingsByListingType($this->_magnasession['mpID'], $this->properties['ListingType'])
			);
			MLProduct::gi()->setQuantityConfig(
				EbayHelper::getQuantitySettingsByListingType($this->_magnasession['mpID'], $this->properties['ListingType'])
			);
			$tecDocKType = getDBConfigValue('ebay.tecdoc.column', $this->_magnasession['mpID'], false);
			if (is_array($tecDocKType) && !empty($tecDocKType['column']) && !empty($tecDocKType['table'])) {
				$pIDAlias = getDBConfigValue('ebay.tecdoc.alias', $this->_magnasession['mpID'], false);
				if (!$pIDAlias) {
					$pIDAlias = 'products_id';
				}
				MLProduct::gi()->setDbMatching('tecDocKType', array (
					'Table' => $tecDocKType['table'],
					'Column' => $tecDocKType['column'],
					'Alias' => $pIDAlias,
				));
			}
		}
		return parent::getProduct($pID);
	}


	protected function arrayPicture($value ,$product_id) {
		$blPicturePack = getDBConfigValue(array('ebay.picturepack', 'val'), $this->_magnasession['mpID']);
		if(empty($value)) {
			$aPictureUrls = MLProduct::gi()->getAllImagesByProductsId($product_id);
			if(!$blPicturePack){
				$aPictureUrls = current($aPictureUrls);
			}
		} else {
			$aPictureUrls = json_decode(fixBrokenJsonUmlauts($value),true);
		}
		$imagePath = getDBConfigValue('ebay.imagepath',$this->_magnasession['mpID']);
		if($blPicturePack && is_array($aPictureUrls)){
			foreach ($aPictureUrls as &$image) {
				$image = trim($imagePath.ebayEncodeImageUrl($image));
			}
			$value = $aPictureUrls;
		} else {
			if(is_array($aPictureUrls)){
				$value = $imagePath.current($aPictureUrls);
			} elseif (is_string($aPictureUrls)){
				$value = $imagePath.$aPictureUrls;
			}
			$value = ebayEncodeImageUrl(trim($value));
		}
		return $value;
	}

	private function appendMobileDescriptionIfConfigured(&$sDesc, $pID, $product, $data, $sWeight, $formatted_vpe) {
		if (getDBConfigValue('ebay.template.usemobile', $this->_magnasession['mpID'], false) === 'true') {
			$sMobileDescription = stringToUTF8(
				eBaySubstituteTemplate(
					$this->_magnasession['mpID'],
					$pID,
					getDBConfigValue('ebay.template.mobilecontent', $this->_magnasession['mpID']),
					array(
						'#TITLE#' => fixHTMLUTF8Entities($product['Title']),
						'#ARTNR#' => $product['ProductsModel'],
						'#PID#' => $pID,
						'#SKU#' => $data['submit']['SKU'],
						'#SHORTDESCRIPTION#' => html_entity_decode(fixHTMLUTF8Entities($product['ShortDescription'])),
						'#WEIGHT#' => $sWeight,
						'#DESCRIPTION#' => html_entity_decode(fixHTMLUTF8Entities($product['Description'])),
						'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency(),
						'#VPE#' => $formatted_vpe,
						'#BASEPRICE#' => $formatted_vpe,
					)
				)
			);
			EbayHelper::appendMobileDescription($sDesc, $sMobileDescription);
		}
	}

    /**
     * If non of the shop variations attributes are matched then it will submitted like in shop
     * @return bool
     */
    protected function shouldSendShopData() {
        return true;
    }

	/**
	 * @todo check if ((masterArticle && haveVariants)||normalArticle)
	 */
	protected function appendAdditionalData($pID, $product, &$data) {
		if ($this->settings['mlProductsUseLegacy']) {
			return $this->appendAdditionalDataOld($pID, $product, $data);
		}
		$propertiesRow = $this->properties;
		$data['submit']['SKU'] = magnaPID2SKU($pID);

		$listingMasterType = ($propertiesRow['ListingType'] == 'Chinese') ? 'chinese' : 'fixed';



		$isPicturePackActive = getDBConfigValue(array('ebay.picturepack', 'val'), $this->_magnasession['mpID']);
		$blBusinessPoliciesSet = geteBayBusinessPolicies();
		$defaultSellerProfiles = $blBusinessPoliciesSet
			? json_encode(array(
				'Payment'  => getDBConfigValue('ebay.default.paymentsellerprofile', $this->_magnasession['mpID']),
				'Shipping' => getDBConfigValue('ebay.default.shippingsellerprofile', $this->_magnasession['mpID']),
				'Return'   => getDBConfigValue('ebay.default.returnsellerprofile', $this->_magnasession['mpID'])
			))
			: '';
		//data which comes direct from variables
		foreach (array(
			array('var' => 'db',		'varKey' => 'ebay.country',		'submitKey' => 'Country',		'empty' => true),
			array('var' => 'property',	'varKey' => 'DispatchTimeMax',		'submitKey' => 'DispatchTimeMax',	'empty' => true, 'sanitize' => 'string'),
			array('var' => 'db',		'varKey' => 'ebay.site',			'submitKey' => 'Site',			'empty' => true),
			array('var' => 'db',		'varKey' => 'ebay.location',		'submitKey' => 'Location',		'empty' => true),
			array('var' => 'db',		'varKey' => 'ebay.postalcode',		'submitKey' => 'PostalCode',		'empty' => true),
			array('var' => 'product',	'varKey' => 'tecDocKType',		'submitKey' => 'tecDocKType',		'empty' => false),
			array('var' => 'property',	'varKey' => 'PaymentMethods',		'submitKey' => 'PaymentMethods',	'empty' => false, 'sanitize' => 'json'),
			array('var' => 'property',	'varKey' => 'PrimaryCategory',		'submitKey' => 'PrimaryCategory',		'empty' => true),
			array('var' => 'property',	'varKey' => 'ListingType',		'submitKey' => 'ListingType',		'empty' => true),
			array('var' => 'property',	'varKey' => 'ListingDuration',		'submitKey' => 'ListingDuration',		'empty' => true),
			array('var' => 'property',	'varKey' => 'PictureURL',		'submitKey' => 'PictureURL',		'empty' => true, 'sanitize' => 'arraypicture'),
			array('var' => 'property',	'varKey' => 'StartTime',		'submitKey' => 'StartTime',		'empty' => false),
			array('var' => 'db',		'varKey' => 'ebay.paypal.address',	                  'submitKey' => 'PayPalEmailAddress',	'empty' => false),
			# ShippingDetails will be manipulated later
			array('var' => 'property',	'varKey' => 'ShippingDetails',		'submitKey' => 'ShippingDetails',		'empty' => true, 'sanitize' => 'json'),
			array('var' => 'property',	'varKey' => 'SellerProfiles',		'submitKey' => 'SellerProfiles',		'default' => $defaultSellerProfiles, 'empty' => false, 'sanitize' => 'json'),
			array('var' => 'property',	'varKey' => 'Subtitle',			'submitKey' => 'ItemSubTitle',		'empty' => false),
			array('var' => 'property',	'varKey' => 'ConditionID',		'submitKey' => 'ConditionID',		'empty' => false),
			array('var' => 'property',	'varKey' => 'ConditionDescriptors',		'submitKey' => 'ConditionDescriptors',		'empty' => true),
			array('var' => 'property',	'varKey' => 'ConditionDescription',		'submitKey' => 'ConditionDescription',		'empty' => true),
			array('var' => 'property',	'varKey' => 'SecondaryCategory',	'submitKey' => 'SecondaryCategory',		'empty' => false),
			array('var' => 'property',	'varKey' => 'StrikePriceConf',		'submitKey' => 'StrikePriceConf',		'empty' => false, 'sanitize' => 'json'), 
			# Der Preis wurde mit der in der Config festgelegten Currency berechnet. Nicht die Currency aus der Vorbereitung nehmen, sondern aus der Config.
			array('var' => 'settings',	'varKey' => 'currency',			'submitKey' => 'currencyID',	                     'empty' => true),
			array('var' => 'property',	'varKey' => 'StoreCategory',		'submitKey' => 'StoreCategory',		   'empty' => false),
			array('var' => 'property',	'varKey' => 'StoreCategory2',		'submitKey' => 'StoreCategory2',		   'empty' => false),
			array('var' => 'property',	'varKey' => 'Attributes',                                   'submitKey' => 'Attributes',	                     'empty' => false, 'sanitize' => 'json'),
			array('var' => 'property',	'varKey' => 'ItemSpecifics',		'submitKey' => 'ItemSpecifics',		    'empty' => false, 'sanitize' => 'json'),
			array('var' => 'property',	'varKey' => 'GalleryType',		'submitKey' => 'GalleryType',	                      'default' => getDBConfigValue('ebay.gallery.type', $this->_magnasession['mpID'], 'Gallery')),
			array('var' => 'property',	'varKey' => 'PrivateListing',		'submitKey' => 'PrivateListing',		    'empty' => false, 'sanitize' => 'bool'),
			array('var' => 'property',	'varKey' => 'VariationDimensionForPictures',	'submitKey' => 'VariationDimensionForPictures', 'empty' => false, 'sanitize' => 'string'),
			array('var' => 'property',	'varKey' => 'eBayPicturePackPurge',                 'submitKey' => 'PurgePictures',                            'empty' => false, 'sanitize' => 'bool','condition'=>$isPicturePackActive ),
			array('var' => 'db',                          'varKey' => 'ebay.picturepack',		'submitKey' => 'PicturePack',                               'empty' => false, 'sanitize' => 'bool','condition'=>$isPicturePackActive ),
			array('var' => 'property',	'varKey' => 'ePID',			'submitKey' => 'ePID',		'empty' => false),
            array('var' => 'property',	'varKey' => 'mwst',		'submitKey' => 'Tax',	'default' => getDBConfigValue('ebay.mwst', $this->_magnasession['mpID'], 0)),

                 ) as $config) {
			if(isset($config['condition']) && !$config['condition'] ){
				continue;
			}
			switch ($config['var']) {
				case 'product' : {
					$var = $product;
					break;
				}
				case 'property' : {
					$var = $propertiesRow;
					break;
				}
				case 'db' : {
					$var = array ($config['varKey'] => getDBConfigValue($config['varKey'], $this->_magnasession['mpID']));
					break;
				}
				case 'settings' : {
					$var = $this->settings;
					break;
				}
				default : {
					break;
				}
			}
			$value = isset($var[$config['varKey']]) ? $var[$config['varKey']] : '';
			if (empty($value)) {
				if (isset($config['default'])) {
					$value = $config['default'];
				} elseif ($config['empty'] === false) {
					continue;
				}
			}
			if (isset($config['sanitize'])) {
				switch ($config['sanitize']) {
					case 'arraypicture' : {
						$value = $this->arrayPicture($value ,$pID);
						break;
					}
					case 'json' : {
						$value = json_decode(fixBrokenJsonUmlauts($value), true);
						break;
					}
					case 'bool' : {
						if(is_array($value)){
							$value = current($value);//['val':true]
						} else {
							$value = '1' == $value ? 'true' : 'false';
						}
						break;
					}
					case 'string' : {
						$value = (string) $value;
						break;
					}
				}
			}
			$data['submit'][$config['submitKey']] = $value;
		}
        if (0 == $data['submit']['Tax']) {
            if (getDBConfigValue(array('ebay.mwst.always', 'val'), $this->_magnasession['mpID'], false)) {
                $data['submit']['Tax'] = 'zero';
            }
        }
		//if picture was reset in ebay_properties_table
		$propertiesRow['PictureURL'] = $data['submit']['PictureURL'];
		// DispatchTimeMax: use default if property not set properly
		if ($data['submit']['DispatchTimeMax'] > 40) {
			$data['submit']['DispatchTimeMax'] = getDBConfigValue('ebay.DispatchTimeMax', $this->_magnasession['mpID'], 40);
		}

		if (!$this->verify) { // && ML_ShopAddOns::mlAddOnIsBooked('EbayPicturePack'))
			$data['submit']['Asynchronous'] = true;
		}

		if(    getDBConfigValue(array('ebay.picturepack', 'val'), $this->_magnasession['mpID'])
		    && array_key_exists('Variations', $product)
		    && is_array($product['Variations'])) {
			$imagePath = getDBConfigValue('ebay.imagepath',$this->_magnasession['mpID']);

			$configVariationDimensionForPictures = getDBConfigValue('ebay.variationdimensionforpictures', $this->_magnasession['mpID']);
			$data['submit']['VariationDimensionForPictures'] = $sVariationDimensionForPictures =
				empty($data['submit']['VariationDimensionForPictures']) ? $configVariationDimensionForPictures : $data['submit']['VariationDimensionForPictures'];

			// Support for Variation Images for Gambio Properties
            if (MAGNA_GAMBIO_VARIATIONS && getDBConfigValue('general.gambio.useproperties', '0', 'true') == 'true') {
                // In Gambio 4.1 - 4.5 have the path included in the DB entry. Just check if it is.
                $VarImagePath = $fullVarImagePath = getDBConfigValue('ebay.imagepath.variations', $this->_magnasession['mpID'], HTTP_CATALOG_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.'product_images/original_images/');
                $shortVarImagePath = HTTP_CATALOG_SERVER.DIR_WS_CATALOG;

                if (!empty($data['submit']['VariationDimensionForPictures'])) {
                    // find value matching for the dimension
                    $currVariationValueMatching = array();
                    foreach ($product['VariationPictures'] as $aVariation) {
                        foreach ($aVariation['Variation'] as $aVar) {
                            if ($aVar['NameId'] == $data['submit']['VariationDimensionForPictures']) {
                                $VariationDimensionForPicturesName = $aVar['Name'];
                                break;
                            }
                        }
                        break; // the 1st is enough
                    }
                    if (    isset($VariationDimensionForPicturesName)
                         && is_array($data['submit']['ItemSpecifics']['ShopVariation'][$VariationDimensionForPicturesName])
                         && is_array($data['submit']['ItemSpecifics']['ShopVariation'][$VariationDimensionForPicturesName]['Values'])) {
                        foreach ($data['submit']['ItemSpecifics']['ShopVariation'][$VariationDimensionForPicturesName]['Values'] as $v) {
                            $currVariationValueMatching[$v['Shop']['Value']] = $v['Marketplace']['Value'];
                        }
                    }
                    // iterate through the variation images
                    foreach ($product['VariationPictures'] as $aVariation) {
                        foreach ($aVariation['Variation'] as $aVar) {
                            // fallback for the case the value is not matched
                            if (!array_key_exists($aVar['Value'], $currVariationValueMatching)) {
                                $currVariationValueMatching[$aVar['Value']] = $aVar['Value'];
                            }
                            // if variation name matches the configured / prepared variation level name
                            if ($aVar['NameId'] == $data['submit']['VariationDimensionForPictures']) {
                                // Support for one Variation Image (if shop not support multiple variation images)
                                if (empty($aVariation['Images'])) {
                                    $aVariation['Images'] = array($aVariation['Image']);
                                }

                                // Support for Multiple Variation Images - see Fallback above if shop supports only one variation image
                                if (!empty($aVariation['Images'])) {
                                    foreach($aVariation['Images'] as $varImage) {
                                        if (strpos($varImage, '/') === false) {
                                            $VarImagePath = $fullVarImagePath;
                                        } else {
                                            $VarImagePath = $shortVarImagePath;
                                        }
                                        if (!empty($varImage)
                                            && (!is_array($data['submit']['VariationPictures'])
                                                || !array_key_exists($currVariationValueMatching[$aVar['Value']], $data['submit']['VariationPictures'])
                                                || !in_array($VarImagePath.$varImage, $data['submit']['VariationPictures'][$currVariationValueMatching[$aVar['Value']]])
                                            )
                                        ) {
                                            $data['submit']['VariationPictures'][$currVariationValueMatching[$aVar['Value']]][] = $VarImagePath.$varImage;
                                        }
                                    }
                                    $sVariationDimensionForPicturesName = $aVar['Name'];
                                }
                            }

                            if (!empty($aVariation['Image'])) {
                                if (strpos($aVariation['Image'], '/') === false) {
                                    $VarImagePath = $fullVarImagePath;
                                } else {
                                    $VarImagePath = $shortVarImagePath;
                                }
                            }
                            if (   $aVar['NameId'] == $data['submit']['VariationDimensionForPictures']
                                && !empty($aVariation['Image'])
                                && (!is_array($data['submit']['VariationPictures'])
                                    || !array_key_exists($currVariationValueMatching[$aVar['Value']], $data['submit']['VariationPictures'])
                                    || !in_array($VarImagePath.$aVariation['Image'], $data['submit']['VariationPictures'][$currVariationValueMatching[$aVar['Value']]])
                                )
                            ) {
                                $data['submit']['VariationPictures'][$currVariationValueMatching[$aVar['Value']]][] = $VarImagePath.$aVariation['Image'];
                                $sVariationDimensionForPicturesName = $aVar['Name'];
                            }
                        }
                        if (    is_array($data['submit']['VariationPictures'][$currVariationValueMatching[$aVar['Value']]])
                             && !empty($data['submit']['VariationPictures'][$currVariationValueMatching[$aVar['Value']]])) {
                            $data['submit']['VariationPictures'][$currVariationValueMatching[$aVar['Value']]] = array_unique($data['submit']['VariationPictures'][$currVariationValueMatching[$aVar['Value']]]);
                        }
                    }
                }
			} else if (MagnaDb::gi()->columnExistsInTable('attributes_image', TABLE_PRODUCTS_ATTRIBUTES)) {
				$VarImagePath = getDBConfigValue('ebay.variation.imagepath', $this->_magnasession['mpID'], $imagePath);
				$aVarAttrs = MagnaDb::gi()->fetchArray('SELECT variation_attributes
					 FROM '.TABLE_MAGNA_VARIATIONS.'
					WHERE products_id = '.$pID, true);
				$aOptionsValuesIds = array();
				foreach ($aVarAttrs as $sSingleVarAttrs) {
					$aSingleVarAttrs = explode('|', $sSingleVarAttrs);
					foreach ($aSingleVarAttrs as $sSingleAttr) {
						if (substr($sSingleAttr, 0, strpos($sSingleAttr, ',')) == $data['submit']['VariationDimensionForPictures']) {
							$aOptionsValuesIds[] = substr(strstr($sSingleAttr, ','), 1);
							break;
						}
					}
				}
				$aOptionsValuesIds = array_unique($aOptionsValuesIds, SORT_NUMERIC);
				$sOptionsValuesIds = implode(',', $aOptionsValuesIds);
				if (!empty($sOptionsValuesIds)) {
					$aVariationPictures = MagnaDb::gi()->fetchArray(eecho('
						SELECT pa.options_values_id, pov.products_options_values_name, pa.attributes_image
						  FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa, '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov
						 WHERE pa.products_id = '.$pID.'
						   AND pa.options_id = '.$data['submit']['VariationDimensionForPictures'].'
						   AND pa.options_values_id IN ('.$sOptionsValuesIds.')
						   AND pov.language_id = '.$this->settings['language'].'
						   AND pov.products_options_values_id = pa.options_values_id
						ORDER BY pa.options_values_id
					', false));
				} else {
					$aVariationPictures = false;
				}
				if (!empty($aVariationPictures) && is_array($aVariationPictures)) {
					$data['submit']['VariationPictures'] = array();
					foreach ($aVariationPictures as $aVariationPicture) {
						if (empty($aVariationPicture['attributes_image'])) continue;
						$data['submit']['VariationPictures'][stringToUTF8($aVariationPicture['products_options_values_name'])] = array ( rtrim($VarImagePath, '/').'/'.$aVariationPicture['attributes_image'] );
					}
					if (empty($data['submit']['VariationPictures'])) unset($data['submit']['VariationPictures']);
					$sVariationDimensionForPicturesName = MagnaDb::gi()->fetchOne('SELECT products_options_name
						 FROM '.TABLE_PRODUCTS_OPTIONS.'
						WHERE products_options_id = '.$data['submit']['VariationDimensionForPictures'].'
						  AND language_id = '.$this->settings['language']
					);
				}
			}
		}
		if(isset($data['submit']['VariationPictures'])){
			$data['submit']['VariationDimensionForPictures'] = $sVariationDimensionForPicturesName;
		}else{
			unset($data['submit']['VariationDimensionForPictures']);
		}
		// look for data in $data, then in $propertiesRow, then in $product
		$data['submit']['Quantity']        = (!empty($data['quantity'])) ? $data['quantity'] : $product['Quantity'];

		if (!empty($data['price']) && $data['price'] != 0) {
			$data['submit']['Price'] = $data['price'];
		} elseif (!empty($propertiesRow['Price']) && $propertiesRow['Price'] != 0  && ('chinese' === $listingMasterType)) {
			$data['submit']['Price'] = $propertiesRow['Price'];
		} else {
			$data['submit']['Price'] = $product['Price'][$listingMasterType];
			if (isset($product['PriceReduced'][$listingMasterType])) {
				$data['submit']['Price'] = $product['PriceReduced'][$listingMasterType];
			}
		}

		// if only Variations have prices, use the first found as main price
		if (    (    !array_key_exists('Price', $data['submit'])
		          || ((float)$data['submit']['Price'] < 1.0)
		        )
		     && array_key_exists('Variations', $product)
			 && is_array($product['Variations'])
		   ) {
			foreach ($product['Variations'] as $v) {
				if (    array_key_exists('Price', $v)
				     && array_key_exists('fixed', $v['Price']) //it's always fixed for Variations
				     && $v['Price']['fixed'] >= 1.0
				   ) {
					$data['submit']['Price'] = $v['Price']['fixed'];
					break;
				}
			}
		}

		// Strike Through Price
		// use 'while' so that we can 'break'
		while (    array_key_exists('StrikePriceConf', $data['submit'])
		     && !empty($data['submit']['StrikePriceConf'])
		     && isset($data['submit']['StrikePriceConf']['ebay.strike.price.kind'])) {
			if ($data['submit']['StrikePriceConf']['ebay.strike.price.kind'] == 'DontUse') break;
			if ((!empty($data['submit']['StrikePriceConf']['ebay.strike.price.isUVP'])) && ($data['submit']['StrikePriceConf']['ebay.strike.price.isUVP']['val'])) {
				$sStrikePrice = 'ManufacturersPrice';
			} else {
				$sStrikePrice = 'OldPrice';
			}
			$aStrikePrice = makePriceByStrikePriceSettings($pID, $data['submit']['StrikePriceConf']['ebay.strike.price.kind'], $data['submit']['StrikePriceConf']['ebay.strike.price.group']);
			$data['submit']['Price'] = $aStrikePrice['price'];
			if ($aStrikePrice['strikePrice'] > 0) {
				$data['submit'][$sStrikePrice] = $aStrikePrice['strikePrice'];
			}
			if (array_key_exists('Variations', $product)
			     && is_array($product['Variations'])) {
				foreach ($product['Variations'] as &$v) {
					if (    array_key_exists('Price', $v)
					     && array_key_exists('fixed', $v['Price'])
					     && array_key_exists('strike', $v['Price'])
					     && $v['Price']['strike'] > $v['Price']['fixed']
					   ) {
						$v[$sStrikePrice] = $v['Price']['strike'];
					}
				}
			}
			break;
		}
		if (array_key_exists('StrikePriceConf', $data['submit'])) {
			unset($data['submit']['StrikePriceConf']);
		}

		// ePIDs for Variations (if stored)
		$ePIDsForVariationsByKey = getEpidsForVariationsByKey($pID, $product['ProductsModel']);
		if (array_key_exists('Variations', $product)
		     && is_array($product['Variations'])
		     && ($ePIDsForVariationsByKey != false)) {
			$blKeytypeIsArtnr = (getDBConfigValue('general.keytype', '0') == 'artNr');
			foreach ($product['Variations'] as &$v) {
				if ($blKeytypeIsArtnr) $v['ePID'] = $ePIDsForVariationsByKey[$v['MarketplaceSku']];
				else $v['ePID'] = $ePIDsForVariationsByKey[$v['MarketplaceId']];
			}
		}

		if (!empty($product['BasePrice']) && !empty($product['BasePrice']['Value'])) {
			$formatted_vpe = $this->simpleprice->setPrice($data['submit']['Price'] * (1.0 / $product['BasePrice']['Value']))->format(). ' / '. $product['BasePrice']['Unit'];
			$data['submit']['BasePrice'] = $product['BasePrice'];
		} else {
			$formatted_vpe = '';
		}

		# Titel zurückgesetzt, live ermitteln
		if (empty($propertiesRow['Title'])) {
			$eBayTitleTemplate = getDBConfigValue('ebay.template.name',$this->_magnasession['mpID'], '#TITLE#');
			$substitution = array (
				'#TITLE#' => fixHTMLUTF8Entities($product['Title']),
				'#ARTNR#' => $product['ProductsModel'],
				);
			$propertiesRow['Title'] = eBaySubstituteTemplate(
				$this->_magnasession['mpID'], $pID, $eBayTitleTemplate, $substitution
			);
		}
		# Titel: Entferne komische nicht-druckbare Zeichen wie &curren; & ggf VPE einsetzen
		$data['submit']['Title'] = $this->restoreCutBaseprice(
			eBaySubstituteTemplate(
				$this->_magnasession['mpID'], $pID,
				html_entity_decode(fixHTMLUTF8Entities($propertiesRow['Title']), ENT_COMPAT, 'UTF-8'),
				array(
					'#VPE#' => $formatted_vpe,
					'#BASEPRICE#' => $formatted_vpe
				)
			),
			$formatted_vpe
		);

		if (isset($product['Weight'])) {
			if (is_array($product['Weight'])
			    && array_key_exists('Unit',  $product['Weight'])
			    && array_key_exists('Value', $product['Weight'])
			    && ((float)$product['Weight']['Value'] > 0.0)
			) {
				$data['submit']['Weight'] = $product['Weight'];
				$sWeight = $product['Weight']['Value']
				.' '.$product['Weight']['Unit'];
			} else {
				$sWeight = '';
			}
		} else {
			$fWeight = (float)MagnaDB::gi()->fetchOne("SELECT products_weight FROM ".TABLE_PRODUCTS." WHERE products_id='".$pID."'");
			if ($fWeight > 0.0) $sWeight = (string)$fWeight;
			else $sWeight = '';
		}

		if (!empty($data['submit']['Description'])) {
			$data['submit']['Description'] = stringToUTF8($data['submit']['Description']);
			if (array_key_exists('MobileDescription', $data['submit'])
			    && !empty($data['submit']['MobileDescription'])) {
				EbayHelper::appendMobileDescription($data['submit']['Description'], $data['submit']['MobileDescription']);
				unset($data['submit']['MobileDescription']);
			} else {
				$this->appendMobileDescriptionIfConfigured($data['submit']['Description'], $pID, $product, $data, $sWeight, $formatted_vpe);
			}
		} elseif (!empty($propertiesRow['Description']) && $this->verify) {
			$data['submit']['Description'] = stringToUTF8($propertiesRow['Description']);
			if (array_key_exists('MobileDescription', $propertiesRow)
			    && !empty($propertiesRow['MobileDescription'])) {
				EbayHelper::appendMobileDescription($data['submit']['Description'], $propertiesRow['MobileDescription']);
				unset($propertiesRow['MobileDescription']);
			}
		} elseif(!empty($propertiesRow['Description'])) {
			# Beim Uebermitteln Preis einsetzen
			$data['submit']['Description'] = eBaySubstituteTemplate(
				$this->_magnasession['mpID'],
				$pID,
				$propertiesRow['Description'],
				array(
					'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency(),
					'#VPE#' => $formatted_vpe,
					'#BASEPRICE#' => $formatted_vpe
				)
			);
			if (array_key_exists('MobileDescription', $propertiesRow)
			    && !empty($propertiesRow['MobileDescription'])) {
				$data['submit']['MobileDescription'] = eBaySubstituteTemplate(
					$this->_magnasession['mpID'],
					$pID,
					$propertiesRow['MobileDescription'],
					array(
						'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency()
					)
				);
				EbayHelper::appendMobileDescription($data['submit']['Description'], $data['submit']['MobileDescription']);
				unset($data['submit']['MobileDescription']);
			} else {
				$this->appendMobileDescriptionIfConfigured($data['submit']['Description'], $pID, $product, $data, $sWeight, $formatted_vpe);
			}
		} else {
			if (getDBConfigValue('gambio.tabs.display', 0, 'h1') == 'none') {
				if (strpos($product['Description'], '[TAB:')) {
					$product['Description'] = substr($product['Description'], 0, strpos($product['Description'], '[TAB:'));
				}
			} else {
			$product['Description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $product['Description']);
			}
			$data['submit']['Description'] = stringToUTF8(
				substitutePictures(
					eBaySubstituteTemplate(
						$this->_magnasession['mpID'],
						$pID,
						getDBConfigValue('ebay.template.content', $this->_magnasession['mpID']),
						array(
							'#TITLE#' => fixHTMLUTF8Entities($product['Title']),
							'#ARTNR#' => $product['ProductsModel'],
							'#PID#' => $pID,
							'#SKU#' => $data['submit']['SKU'],
							'#SHORTDESCRIPTION#' => html_entity_decode(fixHTMLUTF8Entities($product['ShortDescription']), null, iconv_get_encoding('output_encoding')),
							'#WEIGHT#' => $sWeight,
							'#DESCRIPTION#' => html_entity_decode(fixHTMLUTF8Entities($product['Description']), null, iconv_get_encoding('output_encoding')),
							'#PICTURE1#' => is_array($propertiesRow['PictureURL']) ? current($propertiesRow['PictureURL']) : $propertiesRow['PictureURL'],
							'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency(),
							'#VPE#' => $formatted_vpe,
							'#BASEPRICE#' => $formatted_vpe,
						)
					),
					$pID,
					getDBConfigValue('ebay.imagepath', $this->_magnasession['mpID'])
				)
			);
			$this->appendMobileDescriptionIfConfigured($data['submit']['Description'], $pID, $product, $data, $sWeight, $formatted_vpe);
			/*if (getDBConfigValue('ebay.template.usemobile', $_MagnaSession['mpID'], false) === 'true') {
				$data['submit']['MobileDescription'] = stringToUTF8(
					eBaySubstituteTemplate(
						$this->_magnasession['mpID'],
						$pID,
						getDBConfigValue('ebay.template.mobilecontent', $this->_magnasession['mpID']),
						array(
							'#TITLE#' => fixHTMLUTF8Entities($product['Title']),
							'#ARTNR#' => $product['ProductsModel'],
							'#PID#' => $pID,
							'#SKU#' => $data['submit']['SKU'],
							'#SHORTDESCRIPTION#' => html_entity_decode(fixHTMLUTF8Entities($product['ShortDescription'])),
							'#WEIGHT#' => $sWeight,
							'#DESCRIPTION#' => html_entity_decode(fixHTMLUTF8Entities($product['Description'])),
							'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency(),
							'#VPE#' => $formatted_vpe,
							'#BASEPRICE#' => $formatted_vpe,
						)
					)
				);
				EbayHelper::appendMobileDescription($data['submit']['Description'], $data['submit']['MobileDescription']);
				unset($data['submit']['MobileDescription']);
			}*/
		}
		// for adding products to catalog
		$data['submit']['rawDescription'] = stringToUTF8(html_entity_decode(fixHTMLUTF8Entities($product['Description']), null, iconv_get_encoding('output_encoding')));

		# Subtitel: Wird 1:1 weitergegeben. Wenn zurückgesetzt, gibts keinen (ist nicht vorkonfigurierbar)


		// listingtype depending data
		if ('Chinese' == $propertiesRow['ListingType']) {
			if (!empty($propertiesRow['BuyItNowPrice'])) {
				$data['submit']['BuyItNowPrice'] = $propertiesRow['BuyItNowPrice'];
			}
		}else{
			if ('1' == $propertiesRow['BestOfferEnabled']) {
				$data['submit']['BestOfferEnabled'] = 'true';
			}
			if ('1' == $propertiesRow['eBayPlus']) {
				$data['submit']['eBayPlus'] = 'true';
			} else {
				// eBayPlus checkbox off: check if booked, if yes, submit 'false'
				$eBayPlusSettings = geteBayPlusSettings();
				if ('true' === $eBayPlusSettings['eBayPlus']) {
					$data['submit']['eBayPlus'] = 'false';
				}
			}
		}

		# EAN, wenn aktiviert (default false)
		# compatibility for older revisions (removed from config ~ >5780)
		if (
			!empty($product['EAN'])
			&& getDBConfigValue(array($this->_magnasession['currentPlatform'].'.useean', 'val'), $this->_magnasession['mpID'], false)
		) {
			$data['submit']['EAN'] = $product['EAN'];
		}

		# IncludePrefilledItemInformation, wenn aktiviert (default false)
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.usePrefilledInfo', 'val'), $this->_magnasession['mpID'], false)) {
			$data['submit']['IncludePrefilledItemInformation'] = 'true';
		}


		// returnPolicy
		$data['submit']['ReturnPolicy'] = array();
		$data['submit']['ReturnPolicy']['ReturnsAcceptedOption'] = getDBConfigValue('ebay.returnpolicy.returnsaccepted', $this->_magnasession['mpID'], 'ReturnsAccepted');
		if ($value = getDBConfigValue('ebay.returnpolicy.description', $this->_magnasession['mpID'], null)) {
			$data['submit']['ReturnPolicy']['Description'] = $value;
		}
		if ($value = getDBConfigValue('ebay.returnpolicy.returnswithin', $this->_magnasession['mpID'], null)) {
			$data['submit']['ReturnPolicy']['ReturnsWithinOption'] = $value;
		}
		if ($value = getDBConfigValue('ebay.returnpolicy.shippingcostpaidby', $this->_magnasession['mpID'], null)) {
			$data['submit']['ReturnPolicy']['ShippingCostPaidByOption'] = $value;
		}
		if ('none' != ($value = getDBConfigValue('ebay.returnpolicy.warrantyduration', $this->_magnasession['mpID'], 'none'))) {
			$data['submit']['ReturnPolicy']['WarrantyDurationOption'] = $value;
		}

		$amConfiguration = array();
		if (!empty($data['submit']['ItemSpecifics']) && !empty($data['submit']['ItemSpecifics']['ShopVariation'])) {
			$amConfiguration = $data['submit']['ItemSpecifics']['ShopVariation'];
			$data['submit']['ItemSpecifics']['ShopVariation'] = EbayHelper::gi()->convertMatchingToNameValue(
				$data['submit']['ItemSpecifics']['ShopVariation'],
				$product
			);
		}

		$blDoprepareVariationDataForSubmitRequest = false;

		/**
		 * @todo check configValue
		 * add marketplacesku to variations
		 */
		if (
			('Chinese' <> $propertiesRow['ListingType'])
		    && getDBConfigValue(array($this->_magnasession['currentPlatform'].'.usevariations', 'val'), $this->_magnasession['mpID'], true)
		    && (    VariationsEnabled($data['submit']['PrimaryCategory'])
		         && (    empty($data['submit']['SecondaryCategory'])
		              || VariationsEnabled($data['submit']['SecondaryCategory'])
		            )
		       )
			&& !empty($product['Variations'])
		) {
			$skuField = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'MarketplaceSku' : 'MarketplaceId';
			$data['submit']['Variations'] = array();
			$data['submit']['Quantity'] = $product['QuantityTotal'];

			if (!empty($data['submit']['ItemSpecifics']) && isset($data['submit']['ItemSpecifics']['ShopVariation'])) {
				$data['submit']['IsSplit'] = false;
				$data['submit']['ItemTitle'] = $data['submit']['Title'];
				$data['HasVariations'] = count($product['Variations']) > 0;
				$variationThemeBlacklist = array();
				if (!empty($propertiesRow['VariationThemeBlacklist']) && ($propertiesRow['VariationThemeBlacklist'] != 'null')) {
					$variationThemeBlacklist = json_decode(fixBrokenJsonUmlauts($propertiesRow['VariationThemeBlacklist']), true);
				}

				$variationsForSubmit = array();
				$matchedAttributesNameIdValueId = $this->getMatchedVariationAttributesCodeValueId(
					$amConfiguration,
					array(),
					$variationThemeBlacklist
				);
				// if the variations have been matched, match also the variation dimension for pictures
				if (    is_array($matchedAttributesNameIdValueId)
				     && isset($data['submit']['VariationDimensionForPictures'])
				     && array_key_exists($sVariationDimensionForPictures, $matchedAttributesNameIdValueId)
				     && (strpos(current(array_keys($matchedAttributesNameIdValueId[$sVariationDimensionForPictures])), 'additional_attribute') === false)) {
					$data['submit']['VariationDimensionForPictures'] = $sVariationDimensionForPicturesName = current(array_keys($matchedAttributesNameIdValueId[$sVariationDimensionForPictures]));
				}

				$isFirstVariation = true;
				foreach ($product['Variations'] as $dbVariation) {
					$dbVariation['StartPrice'] = $dbVariation['Price'][$listingMasterType];
					if (isset($dbVariation['PriceReduced'][$listingMasterType])) {
						$dbVariation['StartPrice'] = $dbVariation['PriceReduced'][$listingMasterType];
					}
					$dbVariation['SKU'] = $dbVariation[$skuField];

					$variation = $dbVariation;
					$variation['ItemTitle'] = $data['submit']['Title'];
					$variation['Variation'] = array();

					$productDataForMatching = array_merge($product, $dbVariation);
					foreach ($dbVariation['Variation'] as $variationAttribute) {
						$productDataForMatching["variant_{$variationAttribute['NameId']}"] = $variationAttribute['ValueId'];
					}
					$variation['ItemSpecifics']['ShopVariation'] = EbayHelper::gi()->convertMatchingToNameValue(
						$amConfiguration,
						$productDataForMatching
					);

					if ($isFirstVariation) {
						$isFirstVariation = false;
						$data['submit']['ItemSpecifics'] = $variation['ItemSpecifics'];
					}

					$this->setAllVariationsDataAndMasterProductsSKUs(
						$dbVariation,
						$variation,
						$variationsForSubmit,
						$matchedAttributesNameIdValueId,
						$amConfiguration,
						$data['submit']['SKU'],
						$data,
						$product,
						$variationThemeBlacklist
					);
				}

				$blDoprepareVariationDataForSubmitRequest = true;
			} else {
				foreach ($product['Variations'] as $variation) {
					$variation['StartPrice'] = $variation['Price'][$listingMasterType];
					if (isset($variation['PriceReduced'][$listingMasterType])) {
						$variation['StartPrice'] = $variation['PriceReduced'][$listingMasterType];
					}
					$variation['SKU'] = $variation[$skuField];

					$data['submit']['Variations'][] = $variation;
					if (!isset ($product['QuantityTotal'])) {
						$data['submit']['Quantity'] += $variation['Quantity'];
					}
				}
			}
		}

		# Payment instructions
		if ($value = getDBConfigValue('ebay.paymentinstructions', $this->_magnasession['mpID'], null)) {
			$data['submit']['ShippingDetails']['PaymentInstructions'] = $value;
		}

		# =GEWICHT beruecksichtigen
		if (isset($data['submit']['ShippingDetails']['ShippingServiceOptions']) && is_array($data['submit']['ShippingDetails']['ShippingServiceOptions'])) {
			foreach ($data['submit']['ShippingDetails']['ShippingServiceOptions'] as &$options) {
				if ('=GEWICHT' == (string)$options['ShippingServiceCost']) {
					if (    array_key_exists('Weight', $product)
						 && array_key_exists('Value',  $product['Weight'])
						 && ($product['Weight']['Value'] > 0)
					) {
						$options['ShippingServiceCost'] = $product['Weight']['Value'];
						if(isset($options['FreeShipping'])) unset($options['FreeShipping']);
					} else {
						$options['ShippingServiceCost'] = 0;
					}
				}
			}
		}
		if (isset($data['submit']['ShippingDetails']['InternationalShippingServiceOption']) && is_array($data['submit']['ShippingDetails']['InternationalShippingServiceOption'])) {
			foreach ( $data['submit']['ShippingDetails']['InternationalShippingServiceOption'] as &$options) {
				if ('=GEWICHT' == (string)$options['ShippingServiceCost']) {
					if (    array_key_exists('Weight', $product)
				     	 && array_key_exists('Value',  $product['Weight'])
				     	 && ($product['Weight']['Value'] > 0)
					) {
						$options['ShippingServiceCost'] = $product['Weight']['Value'];
						if(isset($options['FreeShipping'])) unset($options['FreeShipping']);
					} else {
						$options['ShippingServiceCost'] = 0;
					}
				}
			}
		}

		# Versandprofil Inland
		if ( array_key_exists('LocalProfile', $data['submit']['ShippingDetails'])
			&& (0 != $data['submit']['ShippingDetails']['LocalProfile'])) {
			$data['submit']['ShippingDetails']['ShippingDiscountProfileID'] = $data['submit']['ShippingDetails']['LocalProfile'];
		} else if (!array_key_exists('LocalProfile', $data['submit']['ShippingDetails'])
					&& (0 != getDBConfigValue('ebay.default.shippingprofile.local',$this->_magnasession['mpID'], 0))) {
			$data['submit']['ShippingDetails']['ShippingDiscountProfileID'] = getDBConfigValue('ebay.default.shippingprofile.local',$this->_magnasession['mpID'], 0);
		}
		if (isset($data['submit']['ShippingDetails']['LocalProfile']))
			unset($data['submit']['ShippingDetails']['LocalProfile']);
		# Versandpauschale Inland
		if (   array_key_exists('LocalPromotionalDiscount', $data['submit']['ShippingDetails'])
			&& ('true' == $data['submit']['ShippingDetails']['LocalPromotionalDiscount'])) {
			$data['submit']['ShippingDetails']['PromotionalShippingDiscount'] = 'true';
		} else if (!array_key_exists('LocalPromotionalDiscount', $data['submit']['ShippingDetails'])) {
			$data['submit']['ShippingDetails']['PromotionalShippingDiscount'] = getDBConfigValue(array('ebay.shippingdiscount.local', 'val'), $this->_magnasession['mpID'], 'false');
		}
		if (isset($data['submit']['ShippingDetails']['LocalPromotionalDiscount']))
			unset($data['submit']['ShippingDetails']['LocalPromotionalDiscount']);
		# Versandprofil Ausland
		if (   array_key_exists('InternationalProfile', $data['submit']['ShippingDetails'])
			&& (0 != $data['submit']['ShippingDetails']['InternationalProfile'])) {
			$data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'] = $data['submit']['ShippingDetails']['InternationalProfile'];
		} else if (!array_key_exists('InternationalProfile', $data['submit']['ShippingDetails'])
					&& (0 != getDBConfigValue('ebay.default.shippingprofile.international',$this->_magnasession['mpID'], 0))) {
			$data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'] = getDBConfigValue('ebay.default.shippingprofile.international',$this->_magnasession['mpID'], 0);
		}
		if (isset($data['submit']['ShippingDetails']['InternationalProfile']))
			unset($data['submit']['ShippingDetails']['InternationalProfile']);
		# Versandpauschale Ausland
		if (   array_key_exists('InternationalPromotionalDiscount', $data['submit']['ShippingDetails'])
			&& ('true' == $data['submit']['ShippingDetails']['InternationalPromotionalDiscount'])) {
			$data['submit']['ShippingDetails']['InternationalPromotionalShippingDiscount'] = 'true';
		} else if (!array_key_exists('InternationalPromotionalDiscount', $data['submit']['ShippingDetails'])) {
			$data['submit']['ShippingDetails']['InternationalPromotionalShippingDiscount'] = getDBConfigValue(array('ebay.shippingdiscount.international', 'val'), $this->_magnasession['mpID'], 'false');
		}
		if (isset($data['submit']['ShippingDetails']['InternationalPromotionalDiscount']))
			unset($data['submit']['ShippingDetails']['InternationalPromotionalDiscount']);
		# ShippingServiceAdditionalCost aus den Profilen nehmen
		$shippingProfiles = getDBConfigValue('ebay.shippingprofiles', $this->_magnasession['mpID'], null);
		if (!empty($shippingProfiles))  {
			$localProfileID = isset($data['submit']['ShippingDetails']['ShippingDiscountProfileID'])
				? $data['submit']['ShippingDetails']['ShippingDiscountProfileID']
				: 0;
			$internationalProfileID = isset($data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'])
				? $data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID']
				: 0;
			if (isset($localProfileID) && $localProfileID !== 0) {
				$localAddCost = $shippingProfiles['Profiles']["$localProfileID"]['EachAdditionalAmount'];
			}
			if (isset($internationalProfileID) && $internationalProfileID !== 0) {
				$internationalAddCost = $shippingProfiles['Profiles']["$internationalProfileID"]['EachAdditionalAmount'];
			}
			foreach ( $data['submit']['ShippingDetails']['ShippingServiceOptions'] as &$options) {
				if(0 == $options['ShippingServiceCost']) {
					$options['ShippingServiceAdditionalCost'] = 0.0;
					continue;
				}
				if (isset($localAddCost)) {
					$options['ShippingServiceAdditionalCost'] = (float)max((float)$localAddCost, 0);
				} elseif (isset($options['ShippingServiceAdditionalCost'])) {
					unset($options['ShippingServiceAdditionalCost']);
				}
			}
			if (isset($data['submit']['ShippingDetails']['InternationalShippingServiceOption']) && is_array($data['submit']['ShippingDetails']['InternationalShippingServiceOption'])) {
				foreach ( $data['submit']['ShippingDetails']['InternationalShippingServiceOption'] as &$options) {
					if(0 == $options['ShippingServiceCost']) {
						$options['ShippingServiceAdditionalCost'] = 0.0;
						continue;
					}
					if (isset($internationalAddCost)) {
						$options['ShippingServiceAdditionalCost'] = (float)max((float)$internationalAddCost,0);
					} elseif (isset($options['ShippingServiceAdditionalCost'])) {
						unset($options['ShippingServiceAdditionalCost']);
					}
				}
			}
		}

		# RestrictedToBusiness, wenn in der Config aktiviert (default false)
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.restrictToBusiness', 'val'), $this->_magnasession['mpID'], false)) {
			$data['submit']['RestrictedToBusiness'] = 'true';
		}

		# RateTableDetails: possibly switchable-off by config in the future
		$data['submit']['ShippingDetails']['UseRateTables'] = 'true';
		//echo '<table><tr><td>$data[submit]</td><td>$product</td><td>$data</td><td>$propertiesRow (<em>'.$this->properties['ListingType'].'</em>)</td></tr><tr><td style="vertical-align:top">'.print_m($data['submit']).'</td><td style="vertical-align:top">'.print_m($product).'</td><td style="vertical-align:top">'.print_m($data).'</td><td style="vertical-align:top">'.print_m($propertiesRow).'</td></tr></table>';

		$aListingDetails = EbayHelper::getProductListingDetailsFromProduct($pID, $this->settings['language']);

        if (array_key_exists('ItemSpecifics', $data['submit']) && !empty($data['submit']['ItemSpecifics'])) {
            global $_MagnaSession;
            $aMatchingSwap = array(
                'specific2details' => array(),
                'details2specific' => array(),
            );
            foreach(array(
                'EAN' => array('EAN', 'ISBN', 'UPC'),
                'MPN' => array('Herstellernummer', 'MPN')
            ) as $sMatchingName => $aMatchingValues) {
                $aMatching = getDBConfigValue($_MagnaSession['currentPlatform'] . '.listingdetails.'.strtolower($sMatchingName).'.dbmatching.table', $_MagnaSession['mpID'], false);
                if(!is_array($aMatching) || empty($aMatching['column']) || empty($aMatching['table'])) {
                    $aMatchingSwap['specific2details'][$sMatchingName] = $aMatchingValues;
                } else {
                    $aMatchingSwap['details2specific'][$sMatchingName] = $aMatchingValues;
                }
            }
            foreach ($aMatchingSwap as $sMatchigSwapName => $aMatchingSwapValue) {
                foreach ($data['submit']['ItemSpecifics'] as &$aSpecifics) {
                    foreach ($aMatchingSwapValue as $sMatchingName => $aMatchingValues) {
                        foreach ($aMatchingValues as $sSearchMatchingName) {
                            if (array_key_exists($sSearchMatchingName, $aSpecifics)) {
                                if ($sMatchigSwapName === 'specific2details') {
                                    $aListingDetails[$sMatchingName] = $aSpecifics[$sSearchMatchingName];
                                } else if (     empty($aSpecifics[$sSearchMatchingName])
				            && !empty($aListingDetails[$sMatchingName] )  ) {
                                    $aSpecifics[$sSearchMatchingName] = $aListingDetails[$sMatchingName];
                                }
                            }
                        }
                    }
                    unset($aSpecifics);
                }
            }
        }
        if (!empty($data['submit']['ConditionDescriptors'])) {
            $data['submit']['ConditionDescriptors'] = json_decode($data['submit']['ConditionDescriptors'], true);
        }
		$data['submit'] = array_merge($data['submit'], $aListingDetails);
		if ($blDoprepareVariationDataForSubmitRequest) {
			$this->prepareVariationDataForSubmitRequest($variationsForSubmit, $data);
		}
	}

	/**
	 * @deprecated
	 */
	protected function appendAdditionalDataOld($pID, $product, &$data) {
		$propertiesRow = MagnaDB::gi()->fetchRow('SELECT * FROM '.TABLE_MAGNA_EBAY_PROPERTIES
				.' WHERE '
				.((getDBConfigValue('general.keytype', '0') == 'artNr')
				     ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
				     : 'products_id=\''.$pID.'\''
				).' AND mpID = '.$this->_magnasession['mpID']);
		require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');

		if (!empty($propertiesRow['Subtitle'])) $data['submit']['ItemSubTitle'] = $propertiesRow['Subtitle'];
		$shortdesc = '';
		#if (!empty($propertiesRow['Subtitle'])) {
		#	$shortdesc = $propertiesRow['Subtitle'];
		#} else
		if (array_key_exists('products_short_description', $product)) {
			$shortdesc = $product['products_short_description'];
		}
		$data['submit']['Price'] = (!empty($data['price'])) ? $data['price'] : $propertiesRow['Price'];
		if (0 == $data['submit']['Price']) { # preis nicht eingefroren bzw. gegeben, berechnen
			$data['submit']['Price'] = makePrice($pID, $propertiesRow['ListingType']);
		}
		# VPE
		if (isset($product['products_vpe_name'])
		    && (0 <> $product['products_vpe_value'])
		) {
			$formatted_vpe = $this->simpleprice->setPrice($data['submit']['Price'] * (1.0 / $product['products_vpe_value']))->format().' / '.fixHTMLUTF8Entities($product['products_vpe_name']);
		} else {
			$formatted_vpe = '';
		}
		# Titel: Entferne komische nicht-druckbare Zeichen wie &curren; & ggf VPE einsetzen
		$data['submit']['Title'] = $this->restoreCutBaseprice(eBaySubstituteTemplate($this->_magnasession['mpID'], $pID, html_entity_decode(fixHTMLUTF8Entities($propertiesRow['Title']),ENT_COMPAT,'UTF-8'), array(
						'#VPE#' => $formatted_vpe,
						'#BASEPRICE#' => $formatted_vpe
		)), $formatted_vpe);
		if ('1' == $propertiesRow['PrivateListing']) {
			$data['submit']['PrivateListing'] = 'true';
		}
		if (('1' == $propertiesRow['BestOfferEnabled']) && ('Chinese' != $propertiesRow['ListingType'])){
			$data['submit']['BestOfferEnabled'] = 'true';
		}
		if (('1' == $propertiesRow['eBayPlus']) && ('Chinese' != $propertiesRow['ListingType'])){
			$data['submit']['eBayPlus'] = 'true';
		}
		if (!empty($propertiesRow['StartTime'])) {
			$data['submit']['StartTime'] = $propertiesRow['StartTime'];
		}
		# RestrictedToBusiness, wenn in der Config aktiviert (default false)
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.restrictToBusiness', 'val'), $this->_magnasession['mpID'], false)) {
			$data['submit']['RestrictedToBusiness'] = 'true';
		}
		# Wenn nicht in der Maske gefuellt
		if (empty($data['submit']['Description'])) {
			if (!empty($propertiesRow['Description'])) {
				if($this->verify)
					$data['submit']['Description'] = stringToUTF8($propertiesRow['Description']);
				else
				# Beim Uebermitteln Preis einsetzen
					$data['submit']['Description'] = eBaySubstituteTemplate($this->_magnasession['mpID'], $pID, $propertiesRow['Description'], array(
						'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency(),
						'#VPE#' => $formatted_vpe,
						'#BASEPRICE#' => $formatted_vpe
					));
			} else {
				$eBayTemplate = getDBConfigValue('ebay.template.content', $this->_magnasession['mpID']);
				$imagePath    = getDBConfigValue('ebay.imagepath', $this->_magnasession['mpID']);
				$substitution = array(
					'#TITLE#' => fixHTMLUTF8Entities($product['products_name']),
					'#ARTNR#' => $product['products_model'],
					'#PID#' => $pID,
					'#SKU#' => magnaPID2SKU($pID),
					'#SHORTDESCRIPTION#' => stringToUTF8($shortdesc),
					'#DESCRIPTION#' => stripLocalWindowsLinks(stringToUTF8($product['products_description'])),
					'#PICTURE1#' => $propertiesRow['PictureURL'],
					'#PRICE#' => $this->simpleprice->setPrice($data['submit']['Price'])->formatWOCurrency(),
					'#VPE#' => $formatted_vpe,
					'#BASEPRICE#' => $formatted_vpe,
					'#WEIGHT#' => ((float)$product['products_weight']>0)?$product['products_weight']:'',
				);
				$data['submit']['Description'] = stringToUTF8(substitutePictures(eBaySubstituteTemplate(
					$this->_magnasession['mpID'], $pID, $eBayTemplate, $substitution
				), $pID, $imagePath));
			}
		} else {
			$data['submit']['Description'] = stringToUTF8($data['submit']['Description']);
		}
			$data['submit']['PictureURL'] = str_replace(array(' ','&'),array('%20','%26'), trim($propertiesRow['PictureURL']));

		if ($propertiesRow['ConditionID']) {
			$data['submit']['ConditionID'] = $propertiesRow['ConditionID'];
		}
		if (!empty($propertiesRow['ConditionDescriptors'])) {
			$data['submit']['ConditionDescriptors'] = json_decode($propertiesRow['ConditionDescriptors'], true);
		}
		if (!empty($propertiesRow['ConditionDescription'])) {
			$data['submit']['ConditionDescription'] = $propertiesRow['ConditionDescription'];
		}
		if (!empty($propertiesRow['BuyItNowPrice']) && 'Chinese' == $propertiesRow['ListingType']) {
			$data['submit']['BuyItNowPrice'] = $propertiesRow['BuyItNowPrice'];
		}
		$data['submit']['SKU'] = magnaPID2SKU($pID);
		# EAN, wenn aktiviert (default false)
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.useean', 'val'), $this->_magnasession['mpID'], false)) {
			$data['submit']['EAN'] = MagnaDB::gi()->fetchOne('SELECT products_ean FROM '.TABLE_PRODUCTS.' WHERE products_id = '.$pID);
			if (empty($data['submit']['EAN'])) unset($data['submit']['EAN']);
		}
		# IncludePrefilledItemInformation, wenn aktiviert (default false)
		if (getDBConfigValue(array($this->_magnasession['currentPlatform'].'.usePrefilledInfo', 'val'), $this->_magnasession['mpID'], false)) {
			$data['submit']['IncludePrefilledItemInformation'] = 'true';
		}
		# TecDoc KType, wenn aktiviert
		$tecDocKType = getDBConfigValue('ebay.tecdoc.column', $this->_magnasession['mpID'], false);
		if (is_array($tecDocKType) && !empty($tecDocKType['column']) && !empty($tecDocKType['table'])) {
			$pIDAlias = getDBConfigValue('ebay.tecdoc.alias', $this->_magnasession['mpID'], false);
			if (!$pIDAlias) {
				$pIDAlias = 'products_id';
			}
			$data['submit']['tecDocKType'] = MagnaDB::gi()->fetchOne('
				SELECT `'.$tecDocKType['column'].'`
				  FROM `'.$tecDocKType['table'].'`
				WHERE `'.$pIDAlias.'`=\''.MagnaDB::gi()->escape($pID).'\'
				LIMIT 1
			');
			if (!$data['submit']['tecDocKType']) unset($data['submit']['tecDocKType']);
		}
		$data['submit']['PrimaryCategory'] = $propertiesRow['PrimaryCategory'];
		if(!empty($propertiesRow['SecondaryCategory'])) $data['submit']['SecondaryCategory'] = $propertiesRow['SecondaryCategory'];
		if(!empty($propertiesRow['StoreCategory'])) $data['submit']['StoreCategory'] = $propertiesRow['StoreCategory'];
		if(!empty($propertiesRow['StoreCategory2'])) $data['submit']['StoreCategory2'] = $propertiesRow['StoreCategory2'];
		if(!empty($propertiesRow['Attributes'])) $data['submit']['Attributes'] = json_decode($propertiesRow['Attributes'], true);
		if(!empty($propertiesRow['ItemSpecifics'])) $data['submit']['ItemSpecifics'] = json_decode($propertiesRow['ItemSpecifics'], true);
		# Varianten: Nur bei Festpreis-Einstellungen,
		# default is true
		if (  ('Chinese' <> $propertiesRow['ListingType'])
		    && getDBConfigValue(array($this->_magnasession['currentPlatform'].'.usevariations', 'val'), $this->_magnasession['mpID'], true)
		    && (    VariationsEnabled($data['submit']['PrimaryCategory'])
		         && (    empty($data['submit']['SecondaryCategory'])
		              || VariationsEnabled($data['submit']['SecondaryCategory'])
		            )
		       )
		) {
			$data['submit']['Variations'] = getVariations($pID, $data['submit']['Price']);
			if (!$data['submit']['Variations']) unset($data['submit']['Variations']);
		}
		$data['submit']['ListingType']     = $propertiesRow['ListingType'];
		$data['submit']['ListingDuration'] = $propertiesRow['ListingDuration'];
		$data['submit']['Country']         = getDBConfigValue('ebay.country', $this->_magnasession['mpID']);
		$data['submit']['Site']            = getDBConfigValue('ebay.site', $this->_magnasession['mpID']);

		# Der Preis wurde mit der in der Config festgelegten Currency berechnet. Nicht die Currency aus der Vorbereitung nehmen, sondern aus der Config.
		//$data['submit']['currencyID']      = $propertiesRow['currencyID'];
		$data['submit']['currencyID']      = $this->settings['currency'];

		$data['submit']['Location']        = getDBConfigValue('ebay.location', $this->_magnasession['mpID']);
		$data['submit']['PostalCode']      = getDBConfigValue('ebay.postalcode', $this->_magnasession['mpID']);
		$data['submit']['Tax']      = getDBConfigValue('ebay.mwst', $this->_magnasession['mpID'], 0);
		$data['submit']['PaymentMethods']  = json_decode($propertiesRow['PaymentMethods'], true);
		$PayPalEmailAddress = getDBConfigValue('ebay.paypal.address', $this->_magnasession['mpID']);
		if(!empty($PayPalEmailAddress)) $data['submit']['PayPalEmailAddress']  = $PayPalEmailAddress;
		$data['submit']['Quantity']        = (!empty($data['quantity'])) ? $data['quantity']: makeQuantity($pID, $propertiesRow['ListingType']);
		$data['submit']['ReturnPolicy']    = array();
		# Return Policy, Details:
		$data['submit']['ReturnPolicy']['ReturnsAcceptedOption'] = getDBConfigValue('ebay.returnpolicy.returnsaccepted', $this->_magnasession['mpID'], 'ReturnsAccepted');
		$data['submit']['ReturnPolicy']['Description'] = getDBConfigValue('ebay.returnpolicy.description', $this->_magnasession['mpID'], null);
		if (empty($data['submit']['ReturnPolicy']['Description'])) unset($data['submit']['ReturnPolicy']['Description']);
		$data['submit']['ReturnPolicy']['ReturnsWithinOption'] = getDBConfigValue('ebay.returnpolicy.returnswithin', $this->_magnasession['mpID'], null);
		if (empty($data['submit']['ReturnPolicy']['ReturnsWithinOption'])) unset($data['submit']['ReturnPolicy']['ReturnsWithinOption']);
		$data['submit']['ReturnPolicy']['ShippingCostPaidByOption'] = getDBConfigValue('ebay.returnpolicy.shippingcostpaidby', $this->_magnasession['mpID'], null);
		if (empty($data['submit']['ReturnPolicy']['ShippingCostPaidByOption'])) unset($data['submit']['ReturnPolicy']['ShippingCostPaidByOption']);
		$data['submit']['ReturnPolicy']['WarrantyDurationOption'] = getDBConfigValue('ebay.returnpolicy.warrantyduration', $this->_magnasession['mpID'], 'none');
		if ('none' == $data['submit']['ReturnPolicy']['WarrantyDurationOption']) unset($data['submit']['ReturnPolicy']['WarrantyDurationOption']);
		$data['submit']['DispatchTimeMax'] = (string)getDBConfigValue('ebay.DispatchTimeMax', $this->_magnasession['mpID']);
		$data['submit']['ShippingDetails'] = json_decode($propertiesRow['ShippingDetails'], true);
		# Payment instructions
		$data['submit']['ShippingDetails']['PaymentInstructions'] = getDBConfigValue('ebay.paymentinstructions', $this->_magnasession['mpID'], null);
		if (empty($data['submit']['ShippingDetails']['PaymentInstructions'])) unset($data['submit']['ShippingDetails']['PaymentInstructions']);
		# =GEWICHT beruecksichtigen
		foreach ( $data['submit']['ShippingDetails']['ShippingServiceOptions'] as &$options) {
			if ('=GEWICHT' == (string)$options['ShippingServiceCost']) {
				$options['ShippingServiceCost'] = $product['products_weight'];
				if(isset($options['FreeShipping'])) unset($options['FreeShipping']);
			}
		}
		if(is_array($data['submit']['ShippingDetails']['InternationalShippingServiceOption'])) {
			foreach ( $data['submit']['ShippingDetails']['InternationalShippingServiceOption'] as &$options) {
				if ('=GEWICHT' == (string)$options['ShippingServiceCost']) {
					$options['ShippingServiceCost'] = $product['products_weight'];
					if(isset($options['FreeShipping'])) unset($options['FreeShipping']);
				}
			}
		}
		# Versandprofil Inland
		if (   array_key_exists('LocalProfile', $data['submit']['ShippingDetails'])
			&& (0 != $data['submit']['ShippingDetails']['LocalProfile'])) {
			$data['submit']['ShippingDetails']['ShippingDiscountProfileID'] = $data['submit']['ShippingDetails']['LocalProfile'];
		} else if (!array_key_exists('LocalProfile', $data['submit']['ShippingDetails'])
					&& (0 != getDBConfigValue('ebay.default.shippingprofile.local',$this->_magnasession['mpID'], 0))) {
			$data['submit']['ShippingDetails']['ShippingDiscountProfileID'] = getDBConfigValue('ebay.default.shippingprofile.local',$this->_magnasession['mpID'], 0);
		}
		if (isset($data['submit']['ShippingDetails']['LocalProfile']))
			unset($data['submit']['ShippingDetails']['LocalProfile']);
		# Versandpauschale Inland
		if (   array_key_exists('LocalPromotionalDiscount', $data['submit']['ShippingDetails'])
			&& ('true' == $data['submit']['ShippingDetails']['LocalPromotionalDiscount'])) {
			$data['submit']['ShippingDetails']['PromotionalShippingDiscount'] = 'true';
		} else if (!array_key_exists('LocalPromotionalDiscount', $data['submit']['ShippingDetails'])) {
			$data['submit']['ShippingDetails']['PromotionalShippingDiscount'] = getDBConfigValue(array('ebay.shippingdiscount.local', 'val'), $this->_magnasession['mpID'], 'false');
		}
		if (isset($data['submit']['ShippingDetails']['LocalPromotionalDiscount']))
			unset($data['submit']['ShippingDetails']['LocalPromotionalDiscount']);
		# Versandprofil Ausland
		if (   array_key_exists('InternationalProfile', $data['submit']['ShippingDetails'])
			&& (0 != $data['submit']['ShippingDetails']['InternationalProfile'])) {
			$data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'] = $data['submit']['ShippingDetails']['InternationalProfile'];
		} else if (!array_key_exists('InternationalProfile', $data['submit']['ShippingDetails'])
					&& (0 != getDBConfigValue('ebay.default.shippingprofile.international',$this->_magnasession['mpID'], 0))) {
			$data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'] = getDBConfigValue('ebay.default.shippingprofile.international',$this->_magnasession['mpID'], 0);
		}
		if (isset($data['submit']['ShippingDetails']['InternationalProfile']))
			unset($data['submit']['ShippingDetails']['InternationalProfile']);
		# Versandpauschale Ausland
		if (   array_key_exists('InternationalPromotionalDiscount', $data['submit']['ShippingDetails'])
			&& ('true' == $data['submit']['ShippingDetails']['InternationalPromotionalDiscount'])) {
			$data['submit']['ShippingDetails']['InternationalPromotionalShippingDiscount'] = 'true';
		} else if (!array_key_exists('InternationalPromotionalDiscount', $data['submit']['ShippingDetails'])) {
			$data['submit']['ShippingDetails']['InternationalPromotionalShippingDiscount'] = getDBConfigValue(array('ebay.shippingdiscount.international', 'val'), $this->_magnasession['mpID'], 'false');
		}
		if (isset($data['submit']['ShippingDetails']['InternationalPromotionalDiscount']))
			unset($data['submit']['ShippingDetails']['InternationalPromotionalDiscount']);
		# ShippingServiceAdditionalCost aus den Profilen nehmen
		$shippingProfiles = getDBConfigValue('ebay.shippingprofiles', $this->_magnasession['mpID'], null);
		if (!empty($shippingProfiles))  {
			$localProfileID = isset($data['submit']['ShippingDetails']['ShippingDiscountProfileID'])
				?$data['submit']['ShippingDetails']['ShippingDiscountProfileID']:0;
			$internationalProfileID = isset($data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID'])
				?$data['submit']['ShippingDetails']['InternationalShippingDiscountProfileID']:0;
			if(!empty($localProfileID)) {
				$localAddCost = $shippingProfiles['Profiles']["$localProfileID"]['EachAdditionalAmount'];
			}
			if(!empty($internationalProfileID)) {
				$internationalAddCost = $shippingProfiles['Profiles']["$internationalProfileID"]['EachAdditionalAmount'];
			}
			foreach ( $data['submit']['ShippingDetails']['ShippingServiceOptions'] as &$options) {
				if(0 == $options['ShippingServiceCost']) {
					$options['ShippingServiceAdditionalCost'] = 0.0;
					continue;
				}
				if(isset($localAddCost))
					$options['ShippingServiceAdditionalCost'] = (float)max((float)$localAddCost, 0);
				else
					if (isset($options['ShippingServiceAdditionalCost'])) unset($options['ShippingServiceAdditionalCost']);
			}
			if(is_array($data['submit']['ShippingDetails']['InternationalShippingServiceOption'])) {
				foreach ( $data['submit']['ShippingDetails']['InternationalShippingServiceOption'] as &$options) {
					if(0 == $options['ShippingServiceCost']) {
						$options['ShippingServiceAdditionalCost'] = 0.0;
						continue;
					}
					if(isset($internationalAddCost))
						$options['ShippingServiceAdditionalCost'] = (float)max((float)$internationalAddCost,0);
					else
						if (isset($options['ShippingServiceAdditionalCost'])) unset($options['ShippingServiceAdditionalCost']);
				}
			}
		}
		# RateTableDetails: possibly switchable-off by config in the future
		$data['submit']['ShippingDetails']['UseRateTables'] = 'true';
	}

	# Hilfsfunktion: Fuer den Fall dass am Ende des Titels ein #BASEPRICE# steht,
	# das durch die 80-Zeichen-Beschraenkung abgeschnitten wurde
	private function restoreCutBaseprice($str, $bp) {
		$maxLength = 80;
		if (strlen($str) < $maxLength) return $str;
		$lastHashPos = strrpos($str, '#');
		$lastPlaceholder = substr($str, $lastHashPos);
		if (0 === strpos('#BASEPRICE#', $lastPlaceholder)) {
			# wiederherstellen
			$str = substr($str, 0, $lastHashPos).'#BASEPRICE#';
		} else {
			return $str;
		}
		# ersetzen
		$str = str_replace('#BASEPRICE#', $bp, $str);

		if (strlen($str) > $maxLength) {
			# falls jetzt zu lang, kuerzen, aber Ersetzung erhalten
			$str = substr($str, 0, $maxLength - strlen($bp) -1) . ' '. $bp;
		}
		return $str;
	}

	protected function createVariantMasterProduct($dimensions, $variationMasterSku, $itemTitle, $productToClone) {
		if (count($dimensions) === 1 && isset($dimensions[0]['Variation']) && $dimensions[0]['Variation'] == array()) {
			// If everything is split and there are no variation dimensions set leave only data that should go to master product
			$dimensions[0] = array(
				'Quantity' => $dimensions[0]['Quantity'],
				'EAN' => $dimensions[0]['EAN'],
				'StartPrice' => $dimensions[0]['StartPrice'],
				'SKU' => $dimensions[0]['SKU'],
				'Variation' => array(),
			);
		}

		$masterProduct = parent::createVariantMasterProduct($dimensions, $variationMasterSku, $itemTitle, $productToClone);

		$masterProduct['Title'] = $masterProduct['ItemTitle'];
		unset($masterProduct['ItemTitle']);

        if (!empty($masterProduct['Variations'])) {
            $oFirstVariant = reset($masterProduct['Variations']);
            if (!empty($oFirstVariant['ItemSpecifics'])) {
                $masterProduct['ItemSpecifics'] = $oFirstVariant['ItemSpecifics'];
                foreach ($oFirstVariant['Variation'] as $variationDefinition) {
                    unset($masterProduct['ItemSpecifics']['ShopVariation'][$variationDefinition['Name']]);
                }
            }

            foreach ($masterProduct['Variations'] as $key => $variation) {
                unset($masterProduct['Variations'][$key]['ItemTitle']);
                unset($masterProduct['Variations'][$key]['ItemSpecifics']);
                if (!isset($masterProduct['QuantityTotal'])) {
                    $masterProduct['Quantity'] += $variation['Quantity'];
                }
            }
        }

		return $masterProduct;
	}

	protected function setProductVariant(&$productVariant, $varAttribute, $rawAmConfiguration, $variations)
	{
		$fixCatAttributes = EbayHelper::gi()->convertMatchingToNameValue($rawAmConfiguration, array(
			"variant_{$varAttribute['NameId']}" => $varAttribute['ValueId']
		), true);

		if (!empty($fixCatAttributes)) {
            $arrayKeys = array_keys($fixCatAttributes);
			$varAttribute['Name'] = array_pop($arrayKeys);
			$varAttribute['Value'] = array_pop($fixCatAttributes);
		}

		$productVariant['Variation'][] = $varAttribute;
	}

	protected function preSubmit(&$request) {
		MagnaConnector::gi()->setTimeOutInSeconds(600);

		$request['DATA'] = array();


		if (count($this->additionalSplitProducts) > 0) {
			foreach ($this->additionalSplitProducts as $additionalSplitProduct) {
				$request['DATA'][] = $additionalSplitProduct;
			}
		}

		foreach ($this->selection as $iProductId => &$aProduct) {
			// If product has variations, but all variations are skipped because none of the values
			// is matched, master product should not be sent at all.
			if (empty($aProduct['submit']['Variations']) && !empty($aProduct['HasVariations'])) {
				continue;
			}

			$request['DATA'][] = $aProduct['submit'];
		}
		if (empty($request['DATA'])) {
			$this->errorVariationMatchingFailed = true;
		}

		arrayEntitiesToUTF8($request['DATA']);
	}

	protected function postSubmit() {
		MagnaConnector::gi()->resetTimeOut();
	}

	protected function processSubmitResult($result) {
		$aResponseData = $result['RESPONSEDATA'];
		foreach ($aResponseData as $i => $itemResult) {
			if (!is_numeric($i)) {
				continue; # lass Header-Daten weg
			}
			$listing_data[$i] = array(
				'mpID'           => $itemResult['MARKETPLACEID'],
				'SKU'            => $itemResult['DATA']['SKU'],
				'products_id'    => magnaSKU2pID($itemResult['DATA']['SKU']),
				'products_model' => MagnaDB::gi()->escape(MagnaDB::gi()->fetchOne('
					SELECT products_model
					  FROM '.TABLE_PRODUCTS.'
					 WHERE products_id = '.magnaSKU2pID($itemResult['DATA']['SKU'])
				)),
				'Title'          => $itemResult['DATA']['ItemTitle'],
				'Price'          => $itemResult['DATA']['Price'],
				'currencyID'     => $itemResult['DATA']['Currency'],
				'CategoryID'     => $itemResult['DATA']['CategoryID'],
				'ListingType'    => $itemResult['DATA']['ListingType'],
				'Quantity'       => $itemResult['DATA']['Quantity']
			);

			if(!empty($itemResult['DATA']['ItemID'])) {
				$listing_data[$i]['ItemID']    = $itemResult['DATA']['ItemID'];
				$listing_data[$i]['StartTime'] = eBayTimeToTs($itemResult['DATA']['StartTime']);
				$listing_data[$i]['EndTime']   = eBayTimeToTs($itemResult['DATA']['EndTime']);
				if (array_key_exists('Fees',$listing_data[$i])) {
					$listing_data[$i]['Fees']      = serialize($itemResult['DATA']['Fees']);
				}
			}
			if(!empty($itemResult['ERRORS'])) {
				$listing_data[$i]['Errors'] = serialize($itemResult['ERRORS']);
			}

			if (!$this->verify) {
				MagnaDB::gi()->query('
					UPDATE '.TABLE_MAGNA_EBAY_PROPERTIES.'
				   	SET Transferred="'.(('ERROR' == $itemResult['STATUS']) ? 0 : 1).'" 
				 	WHERE  products_id ="'.$listing_data[$i]['products_id'].'"
				');
			}

			if ('ERROR' == $itemResult['STATUS']) {
		 		$listing_data[$i]['Timestamp'] = eBayTimeToTs($itemResult['DATA']['Timestamp']);
				MagnaDB::gi()->insert(TABLE_MAGNA_EBAY_ERRORLOG, $listing_data[$i]);
				$pID = $listing_data[$i]['products_id'];
				$this->badItems[] = $pID;
				unset($this->selection[$pID]);
			}
		}
	}

	public function makeSelectionFromErrorLog() {}

	protected function filterSelection() {
		# Anzahlen <=0 wegfiltern, soweit Nullbestandsführung nicht aktiv (dann sind Änderungen OK)
		foreach ($this->selection as $pID => &$data) {
			if (empty($data['submit']['Description'])) {
				unset($this->selection[$pID]);
				$this->badItems[] = $pID;
			} else if (    ((int)$data['submit']['Quantity'] <= 0)
			            && (getDBConfigValue('ebay.zerostockontrol', $this->mpID, false) !== 'true')) {
				unset($this->selection[$pID]);
				$this->disabledItems[] = $pID;
				$this->ajaxReply['ignoreErrors'] = true; // braucht man denke nicht
			}
		}
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode'   => 'listings',
			'view'   => ($state == 'fail') ? 'failed' : 'inventory'
		), true);
	}

	protected function processException($e) {
		$this->lastException = $e;
	}

	public function getLastException() {
		return $this->lastException;
	}

	protected function generateCustomErrorHTML() {
	// wird nur beim synchronen Hochladen verwendet, nicht beim Vorbereiten
		$exs = MagnaError::gi()->getExceptionCollection();
		$html = '';
		foreach ($exs as $ex) {
			if (!is_object($ex) || ($ex->getSubsystem() == 'PHP') || (($ex->getSubsystem() == 'Core'))) {
				continue;
			}
			$errors = $ex->getErrorArray();
			if (   !is_array($errors['RESPONSEDATA'])
				|| !is_array($errors['RESPONSEDATA'][0])
				|| !is_array($errors['RESPONSEDATA'][0]['ERRORS'])
				|| !is_array($errors['RESPONSEDATA'][0]['ERRORS'][0])
			) {
				continue;
			}
			if (!isset($errors['RESPONSEDATA'][0]['ERRORS'][0]['ERRORCODE'])) {
				continue;
			}

			/* ... als unkrittisch markieren. */
			$ex->setCriticalStatus(false);

			foreach ($errors['RESPONSEDATA'] as $ebayItemErrors) {
				foreach ($ebayItemErrors['ERRORS'] as $ebayError) {
					if (($ebayError['ERRORCLASS'] != 'RequestError') || ($ebayError['ERRORLEVEL'] != 'Error')) continue;
					if (    array_key_exists('ORIGIN',$ebayError)
					     && !empty($ebayError['ORIGIN'])         ) {
						$sMsgHead = $ebayError['ORIGIN'].' '.ML_ERROR_LABEL.' '.$ebayError['ERRORCODE'].': ';
					} else {
						$sMsgHead = sprintf(ML_EBAY_LABEL_EBAYERROR, $ebayError['ERRORCODE']);
					}
					$html .= '
					<div class="ebay errorBox">
						<div class="itemident">
							<span class="label">'.ML_LABEL_SKU.'</span>: '.$ebayItemErrors['DATA']['SKU'].', 
							<span class="label">'.ML_LABEL_TITLE.'</span>: '.$ebayItemErrors['DATA']['ItemTitle'].'
						</div>
						<span class="error">'.$sMsgHead.':</span> '.
						$ebayError['ERRORMESSAGE'].'
					</div>';
				}
			}
		}
		return $html;
	}

	public function verifyOneItem() {
		$this->verify = true;
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'].'Verify',
			'session_id' => session_id()
		));
		$item = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_SELECTION.' 
			 WHERE mpID="'.$this->_magnasession['mpID'].'" AND
			       selectionname="'.$this->settings['selectionName'].'" AND
			       session_id="'.session_id().'"
			 LIMIT 1
		');
		if (empty($item)) {
			return false;
		}
		$oldSelectionName = $this->settings['selectionName'];
		$this->settings['selectionName'] = $this->settings['selectionName'].'Verify';
		$item['selectionname'] = $this->settings['selectionName'];
		MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, $item);

		//echo print_m($this->settings, '$this->settings');

		$this->initSelection(0, 1);
		//echo print_m($this->selection, '$this->selection[1]');
		foreach ($this->selection as $pID => &$data) {
			$data['quantity'] = 1; // hack to get verification of chinese items working
		}
		$this->populateSelectionWithData();
		//echo print_m($this->selection, '$this->selection[2]');
		$result = $this->sendRequest();

		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id()
		));

		// restore selection name
		$this->settings['selectionName'] = $oldSelectionName;

		# Liste der pIDs um die ebay_properties upzudaten
		$selectedPidsArray = MagnaDB::gi()->fetchArray("
			SELECT DISTINCT pID
			  FROM ".TABLE_MAGNA_SELECTION."
			 WHERE mpID = '".$this->_magnasession['mpID']."'
			       AND selectionname = '".$this->settings['selectionName']."'
			       AND session_id = '".session_id()."'
		");
		$selectedPidsList = '';
		foreach ($selectedPidsArray as $pIDsRow) {
			if (is_numeric($pIDsRow['pID'])) $selectedPidsList .= $pIDsRow['pID'].', ';
		}
		$selectedPidsList = trim($selectedPidsList, ', ');
		// if we have only 1 Item, and we've got EPID from API, we can store it
		$storeEPID = '';
		$setproductRequired = '';
		if (    !((bool)strpos($selectedPidsList, ','))
		     && isset($result['RESPONSEDATA'][0]['DATA']['EPID'])) {
			$storeEPID = ',
				       ePID="'.(string)$result['RESPONSEDATA'][0]['DATA']['EPID'].'"';
			$setproductRequired = ',
				       productRequired="true"';
		}
		if (   ('SUCCESS' == $result['STATUS'])
			&& ('SUCCESS' == $result[0]['STATUS'])
		) {
			MagnaDB::gi()->query('
				UPDATE '.TABLE_MAGNA_EBAY_PROPERTIES.'
				   SET Verified="OK"'.$storeEPID.$setproductRequired.'
				 WHERE mpID = '.$this->_magnasession['mpID'].' 
				       AND products_id IN ('.$selectedPidsList.')
			');
		} else if ('ERROR' == $result['STATUS']) {
			// store also ErrorCode
			$Verified = 'ERROR';
			$ErrorCode = '';
			if (    isset($result['RESPONSEDATA'])
			     && isset($result['RESPONSEDATA'][0])
			     && isset($result['RESPONSEDATA']['ERRORS'])) {
				foreach($result['RESPONSEDATA'][0]['ERRORS'] as $aErr) {
					if ($aErr['ERRORLEVEL'] == 'Error') {
						$ErrorCode = $aErr['ERRORCODE'];
						break;
					}
				}
			}

			if ($ErrorCode == '21920000') {
				// if EPID required, we return OK and set EPID in upload.
				$setproductRequired = ',
				       productRequired="true"';
				$Verified = 'OK';
				$result['STATUS'] = 'SUCCESS';
				$result[0]['STATUS'] = 'SUCCESS';
			}
			MagnaDB::gi()->query('
				UPDATE '.TABLE_MAGNA_EBAY_PROPERTIES.'
				   SET Verified="'.$Verified.'",
				       ErrorCode="'.$ErrorCode.'"'.$storeEPID.$setproductRequired.'
				 WHERE mpID = '.$this->_magnasession['mpID'].'
				       AND products_id IN ('.$selectedPidsList.')
			');
		}
		// Special case: No variation could be assigned.
		if ($this->errorVariationMatchingFailed) {
			$result['OVERWRITE_ERRORS'] = array(
				array (
					'ERRORCODE' => '',
					'ERRORMESSAGE' => ML_EBAY_ERROR_VARMATCH_FAILED,
					'ERRORCLASS' => 'RequestError',
					'ERRORLEVEL' => 'Error',
					'ORIGIN' => 'magnalister'
				)
			);
		}

		return $result;
	}

	protected function isVariationInBlacklist($variationThemeBlacklist, $varAttribute, $rawAmConfiguration)
	{
		$mpVariation = EbayHelper::gi()->convertMatchingToNameValue($rawAmConfiguration, array(
			"variant_{$varAttribute['NameId']}" => $varAttribute['ValueId']
		), true);

		foreach ($mpVariation as $mpCode => $mpValue) {
			if (!in_array($mpCode, $variationThemeBlacklist)) {
				return false;
			}
		}

		return true;
	}
}
