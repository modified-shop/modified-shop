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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class HoodProductSaver {
	const DEBUG = false;
	
	public $aErrors = array();

	protected $magnaSession = array();
	protected $marketplace = '';
	protected $mpId = 0;
	
	protected $price = null;
	
	protected $config = array();
	
	public function __construct($magnaSession) {
		$this->magnaSession = &$magnaSession;
		$this->marketplace = $this->magnaSession['currentPlatform'];
		$this->mpId = $this->magnaSession['mpID'];
		
		$this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpId));
		
		$this->config['keytype'] = getDBConfigValue('general.keytype', '0');
		
		$this->config['lang'] = getDBConfigValue($this->marketplace.'.lang', $this->mpId, $_SESSION['languages_id']);
		$this->config['hasShortDesc'] = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);
		
		$this->config['imagepath'] = rtrim(getDBConfigValue($this->marketplace.'.imagepath', $this->mpId), '/').'/';
		if ($this->config['imagepath'] == '/') {
			$this->config['imagepath'] = '';
		}
		$this->config['templateContent'] = getDBConfigValue($this->marketplace.'.template.content', $this->mpId);
		$this->config['templateTitle']   = getDBConfigValue($this->marketplace.'.template.name', $this->mpId, '#TITLE#');
		
		$this->config['maxImages'] = getDBConfigValue($this->marketplace.'.prepare.maximagecount', $this->mpId, 'all');
		$this->config['maxImages'] = ($this->config['maxImages'] == 'all') ? true : (int)$this->config['maxImages'];
	}
	
	protected function insertPrepareData($data) {
		// Filter JNH Tab
		if (isset($data['Description'])) {
			if (getDBConfigValue('gambio.tabs.display', 0, 'h1') == 'none') {
				if (strpos($data['Description'], '[TAB:')) {
					$data['Description'] = substr($data['Description'], 0, strpos($data['Description'], '[TAB:'));
				}
			} else {
				$data['Description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $data['Description']);
			}
		}
		
		foreach (array('StoreCategory', 'StoreCategory2', 'StoreCategory3') as $shopCat) {
			if (!isset($data[$shopCat]) || empty($data[$shopCat])) {
				$data[$shopCat] = 0;
			}
		}
        /* {Hook} "hoodInsertPrepareData": Enables you to modify the prepared product data before it will be saved.<br>
            Variables that can be used:
            <ul>
             <li><code>$aData</code>: The data of a product.</li>
             <li>$this->mpID</code>: The ID of the marketplace.</li>
            </ul>
        */
		if (($hp = magnaContribVerify('hoodInsertPrepareData', 1)) !== false) {
			require($hp);
		}
		if (self::DEBUG) {
			echo print_m($data, __METHOD__);
			die();
		}
		
		MagnaDB::gi()->insert(TABLE_MAGNA_HOOD_PROPERTIES, $data, true);

		// ensure that we don't store multiple prepare rows for the same item
		switch($this->config['keytype']) {
			case('artNr'): {
				MagnaDB::gi()->query('DELETE FROM '.TABLE_MAGNA_HOOD_PROPERTIES.'
					WHERE mpID = '.$data['mpID'].'
					  AND products_model = \''.$data['products_model'].'\'
					  AND products_id <> \''.$data['products_id'].'\'');
				break;
			}
			default: {
				MagnaDB::gi()->query('DELETE FROM '.TABLE_MAGNA_HOOD_PROPERTIES.'
					WHERE mpID = '.$data['mpID'].'
					  AND products_id = \''.$data['products_id'].'\'
					  AND products_model <> \''.$data['products_model'].'\'');
				break;
			}
		}
	}
	
	protected function getManufacturerPartNumber(&$row) {
		$mfrmd = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.table', $this->mpId, false);
		if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
			$pIDAlias = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.alias', $this->mpId);
			if (empty($pIDAlias)) {
				$pIDAlias = 'products_id';
			}
			$row['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne('
				SELECT `' . $mfrmd['column'] . '`
				  FROM `' . $mfrmd['table'] . '`
				 WHERE `' . $pIDAlias . '`="' . MagnaDB::gi()->escape($row['products_id']) . '"
				 LIMIT 1
			');
		}
	}
	
	protected function prepareImages($pId) {
		$images = MLProduct::gi()->getAllImagesByProductsId($pId);
		$gallery = array (
			'BaseUrl' => $this->config['imagepath'],
			'Images' => array(),
		);
		$maxImages = $this->config['maxImages'];
		foreach ($images as $img) {
			$gallery['Images'][$img] = (int)$maxImages > 0;
			if ($maxImages !== true) {
				--$maxImages;
			}
		}
		return $gallery;
	}
	
	/**
	 * Hilfsfunktion fuer SaveHoodSingleProductProperties und SaveHoodMultipleProductProperties
	 * bereite die DB-Zeile vor mit allen Daten die sowohl fuer Single als auch Multiple inserts gelten
	 */
	protected function preparePropertiesRow($pID, $itemDetails) {
		$row = array();
		$row['mpID'] = $this->mpId;
		$row['products_id'] = $pID;
		$row['products_model'] = MagnaDB::gi()->fetchOne('
			SELECT products_model
			  FROM ' . TABLE_PRODUCTS . '
			 WHERE products_id =' . $pID
		);
		
		foreach (array ('PrimaryCategory', 'SecondaryCategory') as $kat) {
			$row[$kat] = $itemDetails[$kat] == NULL ? 0 : $itemDetails[$kat];
			if (empty($row[$kat])) {
				$row[$kat.'Name'] = '';
			} else {
				$row[$kat.'Name'] = MagnaDB::gi()->fetchOne('
					SELECT CategoryName
					  FROM ' . TABLE_MAGNA_HOOD_CATEGORIES . '
					 WHERE CategoryID =' . $row[$kat] . '
					 LIMIT 1
				');
			}
		}
		foreach (array ('StoreCategory', 'StoreCategory2', 'StoreCategory3') as $kat) {
			$row[$kat] = $itemDetails[$kat];
		}

        $row['topPrimaryCategory'] = $itemDetails['PrimaryCategory'] == NULL ? '' : $itemDetails['PrimaryCategory'];
        $row['topSecondaryCategory'] = $itemDetails['topSecondaryCategory'] == NULL ? '' : $itemDetails['SecondaryCategory'];

		$row['ListingType'] = $itemDetails['ListingType'];
		$row['ListingDuration']  = isset($itemDetails['ListingDuration']) ? $itemDetails['ListingDuration'] : '';
		$row['PaymentMethods']   = json_encode(isset($itemDetails['PaymentMethods']) ? $itemDetails['PaymentMethods'] : array());
	
		$row['ConditionType']    = $itemDetails['ConditionType'];
		$row['noIdentifierFlag'] = $itemDetails['noIdentifierFlag'];
		
		$row['Features'] = array();
		foreach ($itemDetails['Features'] as $featureKey => $featureValue) {
			$row['Features'][$featureKey] = $featureValue === 'true';
		}
		$row['Features'] = json_encode($row['Features']);
		
		$row['FSK'] = $itemDetails['FSK'];
		$row['USK'] = $itemDetails['USK'];
        $row['ShopVariation'] = $itemDetails['ShopVariation'];

        $shippingDetails = array();
        $this->formatShippingService($itemDetails['hood_default_shipping_local'], true, $shippingDetails);

        if (empty($shippingDetails) && is_array($itemDetails['hood.default.shipping.local'])) {
            $this->formatShippingService($itemDetails['hood.default.shipping.local'], true, $shippingDetails);
        }

		if (isset($itemDetails['conf'])
			&& is_array($itemDetails['conf'][$this->marketplace.'.default.shipping.international']) 
			&& is_array($itemDetails['hood_default_shipping_international'])
		) {
			foreach ($itemDetails['conf'][$this->marketplace.'.default.shipping.international'] as $key => $intlService) {
				$itemDetails['hood_default_shipping_international'][$key] = $intlService;
			}
		}
		if (is_array($itemDetails['hood_default_shipping_international'])) {
           $this->formatShippingService($itemDetails['hood_default_shipping_international'], false, $shippingDetails);
		}

        if (is_array($itemDetails['hood.default.shipping.international']) && !is_array($itemDetails['hood_default_shipping_international'])) {
            $this->formatShippingService($itemDetails['hood.default.shipping.international'], false, $shippingDetails);
        }

		if (0 == count($shippingDetails)) {
			unset($shippingDetails);
		}
		$row['ShippingServiceOptions'] = json_encode($shippingDetails);
		
		# Noch nicht verifiziert:
		$row['Verified'] = 'OPEN';
		return $row;
	}

    public function formatShippingService($data, $isLocal, &$shippingDetails) {
        if ($isLocal) {
            foreach ($data as $key => $localService) {
                $shippingDetails[$key] = array(
                    'Service' => $localService['Service'],
                    'Cost' => mlFloatalize($localService['Cost']),
                );
            }
        } else {
            foreach ($data as $key => $intlService) {
                if (empty($intlService['Service']))
                    break;
                $shippingDetails[$key] = $intlService;
            }
        }
    }
	
	public function saveSingleProductProperties($pID, $itemDetails) {
		#echo print_m(func_get_args(), __METHOD__);
		
		$row = $this->preparePropertiesRow($pID, $itemDetails);
		$row['Title'] = trim(strip_tags(html_entity_decode($itemDetails['Title'])));
		if (('true' == $itemDetails['enableSubtitle']) && !empty($itemDetails['Subtitle'])) {
			$row['Subtitle'] = trim($itemDetails['Subtitle']);
		}
		
		$row['Manufacturer'] = trim($itemDetails['Manufacturer']);
		if (isset($itemDetails['ManufacturerPartNumber']) && !empty($itemDetails['ManufacturerPartNumber'])) {
			$row['ManufacturerPartNumber'] = $itemDetails['ManufacturerPartNumber'];
		} else {
			$this->getManufacturerPartNumber($row);
		}
		
		$row['GalleryPictures'] = $itemDetails['GalleryPictures'];
		if (isset($row['GalleryPictures']['Images']) && is_array($row['GalleryPictures']['Images'])) {
			foreach ($row['GalleryPictures']['Images'] as $img => $imgChecked) {
				$row['GalleryPictures']['Images'][$img] = $imgChecked == 'true' ? true : false;
			}
		}
		$row['GalleryPictures'] = json_encode($row['GalleryPictures']);
		
		if (!empty($itemDetails['startTime'])) {
			$row['StartTime'] = $itemDetails['startTime'];
		}
		
		if (isset($itemDetails['StartPrice'])) {
			$row['StartPrice'] = mlFloatalize($itemDetails['StartPrice']);
		} else {
			$row['StartPrice'] = 0.0;
		}
		
		$row['ShortDescription'] = trim($itemDetails['ShortDescription']);
		$row['Description'] = trim($itemDetails['Description']);
		$row['PreparedTs'] = date('Y-m-d H:i:s');

		$this->insertPrepareData($row);
	}
	
	public function saveMultipleProductProperties($pIDs, $itemDetails) {
		#echo print_m(func_get_args(), __METHOD__);
		
		# Analog zu saveHoodSingleProductProperties, aber
		# Title, ShortDescription aus der Datenbank
		# und Descriptions zusammenbauen
		if (!is_array($pIDs)) {
			if (!empty($pIDs)) {
				$pIDs = array($pIDs);
			} else {
				return false;
			}
		}
		$data = MagnaDB::gi()->fetchArray(eecho('
		    SELECT p.products_id, p.products_model, pd.products_name,
		           '.($this->config['hasShortDesc'] ? 'pd.products_short_description' : '"" AS products_short_description').',
		           pd.products_description,
		           m.manufacturers_name Manufacturer,
		           ep.Subtitle
		      FROM ' . TABLE_PRODUCTS . ' p
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON p.products_id = pd.products_id
		 LEFT JOIN ' . TABLE_MANUFACTURERS . ' m ON p.manufacturers_id = m.manufacturers_id
		 LEFT JOIN ' . TABLE_MAGNA_HOOD_PROPERTIES . ' ep ON '.(($this->config['keytype'] == 'artNr')
		     ? 'ep.products_model = p.products_model'
		     : 'ep.products_id = p.products_id'
		 ).'
		     WHERE pd.language_id = "' . $this->config['lang'] . '"
		           AND p.products_id IN (' . implode(', ', $pIDs) . ')
		', false));
		#echo print_m($data);
		
		$priceConfig = HoodHelper::loadPriceSettings($this->mpId);
		$preparedTs = date('Y-m-d H:i:s');
		
		foreach ($data as $dataRow) {
			$row = $this->preparePropertiesRow($dataRow['products_id'], $itemDetails);
			$pID = $dataRow['products_id'];
			
			if (!empty($itemDetails['startTime'])) {
				$row['StartTime'] = $itemDetails['startTime'];
			}
			if ('classic' == $itemDetails['ListingType']) {
				$row['StartPrice'] = $this->price
					->setFinalPriceFromDB($dataRow['products_id'], $this->mpId, $priceConfig['Auction']['StartPrice'])
					->roundPrice()->getPrice();
			}
			
			$row['Manufacturer'] = $dataRow['Manufacturer'];
			$this->getManufacturerPartNumber($row);
			$row['GalleryPictures'] = json_encode($this->prepareImages($dataRow['products_id']));
			
			$row['Title'] = HoodHelper::substituteTemplate($this->mpId, $dataRow['products_id'], $this->config['templateTitle'], array(
				'#TITLE#' => strip_tags($dataRow['products_name']),
				'#ARTNR#' => $dataRow['products_model']
			));
			
			if ('true' != $itemDetails['enableSubtitle']) {
				$row['Subtitle'] = '';
			} else {
				$row['Subtitle'] = $dataRow['Subtitle'];
			}
			
			$row['ShortDescription'] = substr(trim(strip_tags($dataRow['products_short_description'])), 0, 5000);
			
			# Descriptions zusammenbauen
			$substitution = array(
				'#TITLE#' => fixHTMLUTF8Entities($dataRow['products_name']),
				'#ARTNR#' => $dataRow['products_model'],
				'#PID#' => $dataRow['products_id'],
				'#SKU#' => magnaPID2SKU($dataRow['products_id']),
				'#SHORTDESCRIPTION#' => stripLocalWindowsLinks($dataRow['products_short_description']),
				'#DESCRIPTION#' => stripLocalWindowsLinks($dataRow['products_description']),
			);
			$row['Description'] = HoodHelper::getSubstitutePictures(HoodHelper::substituteTemplate(
				$this->mpId, $dataRow['products_id'], $this->config['templateContent'], $substitution
			), $dataRow['products_id'], $this->config['imagepath']);
			$row['PreparedTs'] = $preparedTs;
			$this->insertPrepareData($row);
		}
	}
	
	public function resetProductProperties($pID) {
		$product = MagnaDB::gi()->fetchRow('
		    SELECT p.products_id products_id, p.products_model products_model, pd.products_name Title,
		         '.($this->config['hasShortDesc'] ? 'pd.products_short_description' : '""').' AS ShortDescription,
		           pd.products_description Description,
		           m.manufacturers_name Manufacturer
		      FROM ' . TABLE_PRODUCTS . ' p
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON p.products_id = pd.products_id
		 LEFT JOIN ' . TABLE_MANUFACTURERS . ' m ON p.manufacturers_id = m.manufacturers_id
		     WHERE pd.language_id = "' . $this->config['lang'] . '"
		           AND p.products_id = "'.$pID.'"
		');
		if (empty($product)) {
			return;
		}
		
		$this->getManufacturerPartNumber($product);
		$product['GalleryPictures'] = json_encode($this->prepareImages($pID));
		
		$title = $product['Title'];
		$product['Title'] = HoodHelper::substituteTemplate($this->mpId, $product['products_id'], $this->config['templateTitle'], array(
			'#TITLE#' => strip_tags($title),
			'#ARTNR#' => $product['products_model']
		));
		
		# Descriptions zusammenbauen
		$substitution = array(
			'#TITLE#' => fixHTMLUTF8Entities($title),
			'#ARTNR#' => $product['products_model'],
			'#PID#' => $product['products_id'],
			'#SKU#' => magnaPID2SKU($product['products_id']),
			'#SHORTDESCRIPTION#' => stripLocalWindowsLinks($product['ShortDescription']),
			'#DESCRIPTION#' => stripLocalWindowsLinks($product['Description']),
		);
		$product['Description'] = HoodHelper::getSubstitutePictures(HoodHelper::substituteTemplate(
			$this->mpId, $product['products_id'], $this->config['templateContent'], $substitution
		), $product['products_id'], $this->config['imagepath']);
		
		$product['ShortDescription'] = substr(trim(strip_tags($product['ShortDescription'])), 0, 5000);
		
		$where = ($this->config['keytype'] == 'artNr')
			? array ('products_model' => $product['products_model'])
			: array ('products_id' => $pID);
		$where['mpID'] = $this->mpId;
		
		MagnaDB::gi()->update(TABLE_MAGNA_HOOD_PROPERTIES, $product, $where);
	}
	
}
