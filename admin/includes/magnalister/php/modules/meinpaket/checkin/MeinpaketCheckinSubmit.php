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
 * $Id: MeinpaketCheckinSubmit.php 2437 2013-05-06 13:32:58Z tim.neumann $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');

class MeinpaketCheckinSubmit extends CheckinSubmit {
	private $checkinDetails = array();

	public function __construct($settings = array()) {
		global $_MagnaSession;
		/* Setzen der Currency nicht noetig, da Preisberechnungen bereits in 
		   der MeinpaketSummaryView Klasse gemacht wurden.
		 */
		$settings = array_merge(array(
			'language' => getDBConfigValue($settings['marketplace'].'.lang', $_MagnaSession['mpID']),
			'itemsPerBatch' => 100
		), $settings);

		parent::__construct($settings);   
	}   

	private function generateMPCategoryPath($id, $from = 'category', $langID, $categories_array = array(), $index = 0, $callCount = 0) {
		$descCol = '';
		if (MagnaDB::gi()->columnExistsInTable('categories_description', TABLE_CATEGORIES_DESCRIPTION)) {
			$descCol = 'categories_description';
		} else {
			$descCol = 'categories_name';
		}
		$trim = " \n\r\0\x0B\xa0\xc2"; # last 2 ones are utf8 &nbsp;
		if ($from == 'product') {
			$categories_query = MagnaDB::gi()->query('
				SELECT categories_id AS code
				  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = \''.$id.'\'
			');
			while ($categories = MagnaDB::gi()->fetchNext($categories_query)) {
				if ($categories['code'] != '0') {
					$category_query = MagnaDB::gi()->query('
						SELECT cd.categories_name AS `name`, cd.'.$descCol.' AS `desc`, c.parent_id AS `parent`
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = \''.$categories['code'].'\' 
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_id = \''.$langID.'\'
					');
					$category = MagnaDB::gi()->fetchNext($category_query);
					$c = array (
						'code' => $categories['code'],
						'name' => trim(html_entity_decode(strip_tags($category['name']), ENT_QUOTES, 'UTF-8'), $trim),
						'desc' => $category['desc'],
						'parent' => $category['parent'],
					);
					if ($c['parent'] == '0') {
						unset($c['parent']);
					}
					if ($c['desc'] == '') {
						$c['desc'] = $c['name'];
					}
					$categories_array[$index][] = $c;
					if (($category['parent'] != '') && ($category['parent'] != '0')) {
						$categories_array = $this->generateMPCategoryPath($category['parent'], 'category', $langID, $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category_query = MagnaDB::gi()->query('
				SELECT c.categories_id AS code, cd.categories_name AS `name`, cd.'.$descCol.' AS `desc`, c.parent_id AS `parent`
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = \''.$id.'\' 
				       AND c.categories_id = cd.categories_id
				       AND cd.language_id = \''.$langID.'\'
			');
			$category = MagnaDB::gi()->fetchNext($category_query);
			$c = array (
				'code' => $category['code'],
				'name' => trim(html_entity_decode(strip_tags($category['name']), ENT_QUOTES, 'UTF-8'), $trim),
				'desc' => $category['desc'],
				'parent' => $category['parent'],
			);
			if ($c['parent'] == '0') {
				unset($c['parent']);
			}
			if ($c['desc'] == '') {
				$c['desc'] = $c['name'];
			}
			$categories_array[$index][] = $c;
			if (($category['parent'] != '') && ($category['parent'] != '0')) {
				$categories_array = $this->generateMPCategoryPath($category['parent'], 'category', $langID, $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
	
		return $categories_array;
	}
	
	public function makeSelectionFromErrorLog() {}
	
	protected function generateRequestHeader() {
		return array(
			'ACTION' => 'AddItems',
			'MODE' => $this->submitSession['mode']
		);
	}
	
	protected function markAsFailed($sku) {
		MagnaDB::gi()->insert(
			TABLE_MAGNA_MEINPAKET_ERRORLOG,
			array (
				'mpID' => $this->_magnasession['mpID'],
				'errormessage' => ML_GENERIC_ERROR_UNABLE_TO_LOAD_PREPARE_DATA,
				'dateadded' => gmdate('Y-m-d H:i:s'),
				'additionaldata' => serialize(array(
					'SKU' => $sku
				))
			)
		);
		$this->badItems[] = $pID;
		unset($this->selection[$pID]);
	}

	protected function appendAdditionalData($pID, $product, &$data) {
		if ($data['quantity'] < 0) {
			$data['quantity'] = 0;
		}        
            
		$data['submit']['SKU'] = magnaPID2SKU($pID);
		$data['submit']['ItemTitle'] = $product['products_name'];
		$data['submit']['Price'] = $data['price'];
		$data['submit']['Quantity'] = $data['quantity'];        
            
		if (defined('MAGNA_FIELD_PRODUCTS_EAN')
			&& !empty($product[MAGNA_FIELD_PRODUCTS_EAN])
			&& getDBConfigValue(array('meinpaket.checkin.ean', 'submit'), $this->_magnasession['mpID'], true)
		) {
			$data['submit']['EAN'] = $product[MAGNA_FIELD_PRODUCTS_EAN];
		}

		$shortdescField = getDBConfigValue('meinpaket.checkin.shortdesc.field', $this->_magnasession['mpID'], '');
		if (!empty($shortdescField) && array_key_exists($shortdescField, $product)) {
			$data['submit']['ShortDescription']	= $product[$shortdescField];
		} else {
			$data['submit']['ShortDescription']	= $product['products_description'];
		}

		$longdescField = getDBConfigValue('meinpaket.checkin.longdesc.field', $this->_magnasession['mpID'], '');
		if (!empty($longdescField) && array_key_exists($longdescField, $product)) {
			$data['submit']['Description'] = $product[$longdescField];
		}
		/* Short-Desc ist leer, vielleicht ist die Lang-Desc ja nicht leer. */
		$longDesc = $product['products_description'];
		if (empty($data['submit']['ShortDescription']) && !empty($longDesc)) {
			$data['submit']['ShortDescription'] = $longDesc;
		}
		
		/* Falls Langbeschreibung leer, Kurzbeschreibung ebenfalls fuer Langbeschreibung verwenden. Ansonsten entfernt Meinpaket
		   zu viele HTML-Tags */
		if (!isset($data['submit']['Description']) || empty($data['submit']['Description'])) {
			$data['submit']['Description'] = $data['submit']['ShortDescription'];
		}

        #VPE    
        $showvpe = getDBConfigValue('meinpaket.checkin.showvpe', $this->_magnasession['mpID'], false);
        if ((isset($product['products_vpe_name'])) && (0 <> $product['products_vpe_value'])) {
            $formatted_vpe = floatval($product['products_vpe_value']);
            $data['submit']['BasePrice'] = array (       
                'Unit' => htmlspecialchars(trim($product['products_vpe_name'])),         // $product['products_vpe_name']
                'Value' => $formatted_vpe,                                               // $product['products_vpe_value']
                'showVPE' => $showvpe
                );
        } else {
            $data['submit']['BasePrice'] = array (               # falls in Zukunft meinpaket.de eigene Felder dafür definiert, base price = Grundpreis
                'Unit' => '',                                   // $product['products_vpe_name']
                'Value' => '',                                  // $product['products_vpe_value']
                'showVPE' => $showvpe    
                );
        }
        # Ende VPE               
        
        # Titel-Template 
        $title_template = getDBConfigValue('meinpaket.checkin.title_template', $this->_magnasession['mpID'], '');                     
        $data['submit']['TitleTemplate'] = strip_tags($title_template);
        # Ende Titel-Template
            
		$manufacturerName = '';
		if ($product['manufacturers_id'] > 0) {
			$manufacturerName = (string)MagnaDB::gi()->fetchOne(
				'SELECT manufacturers_name FROM '.TABLE_MANUFACTURERS.' WHERE manufacturers_id=\''.$product['manufacturers_id'].'\''
			);
		}
		if (empty($manufacturerName)) {
			$manufacturerName = getDBConfigValue(
				'meinpaket.checkin.manufacturerfallback',
				$this->_magnasession['mpID'],
				''
			);
		}
		$mfrmd = getDBConfigValue('meinpaket.checkin.manufacturerpartnumber.table', $this->_magnasession['mpID'], false);
		if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
			$pIDAlias = getDBConfigValue('meinpaket.checkin.manufacturerpartnumber.alias', $this->_magnasession['mpID']);
			if (empty($pIDAlias)) {
				$pIDAlias = 'products_id';
			}
			$data['submit']['Manufacturer'] = $manufacturerName;
			$data['submit']['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne('
				SELECT `'.$mfrmd['column'].'` 
				  FROM `'.$mfrmd['table'].'` 
				 WHERE `'.$pIDAlias.'`=\''.MagnaDB::gi()->escape($pID).'\'
				 LIMIT 1
			');
		}
		
		$taxMatch = getDBConfigValue('meinpaket.checkin.taxmatching', $this->_magnasession['mpID'], array());
		if (is_array($taxMatch) && array_key_exists($product['products_tax_class_id'], $taxMatch)) {
			$data['submit']['ItemTax'] = $taxMatch[$product['products_tax_class_id']];
		} else {
			$data['submit']['ItemTax'] = 'Standard';
		}

		$data['submit']['ShippingTime'] = getDBConfigValue('meinpaket.checkin.leadtimetoship', $this->_magnasession['mpID'], 3);


		$imageWSPath = getDBConfigValue('meinpaket.checkin.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
		$images = array();
		
		if (!empty($product['products_allimages'])) {
			foreach($product['products_allimages'] as $img) {
				$images[] = array('URL' => $imageWSPath.$img);
			}
		}
		$data['submit']['Images'] = $images;
		
		if (($catMatching = MagnaDB::gi()->fetchRow('
			SELECT mp_category_id, store_category_id 
			  FROM `'.TABLE_MAGNA_MEINPAKET_CATEGORYMATCHING.'`
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
			            ? 'products_model=\''.MagnaDB::gi()->escape($product['products_model']).'\''
			            : 'products_id=\''.$pID.'\''
			        ).' AND
			       mpID=\''.$this->_magnasession['mpID'].'\'
			 LIMIT 1
		')) === false) {
			$this->markAsFailed(magnaPID2SKU($pID));
			return;
		}
		$data['submit']['MarketplaceCategory'] = $catMatching['mp_category_id'];

		if (getDBConfigValue(array('meinpaket.catmatch.mpshopcats', 'val'), $this->_magnasession['mpID'], false)) {
			$cPath = $this->generateMPCategoryPath($pID, 'product', $this->settings['language']);
			$cPath = array_shift($cPath);
			$data['submit']['MarketplaceShopCategory'] = $cPath[count($cPath)-1]['code'];
			$data['submit']['MarketplaceShopCategoryStructure'] = $cPath;
		} else if (!empty($catMatching['store_category_id'])) {
			$data['submit']['MarketplaceShopCategory'] = $catMatching['store_category_id'];
		}

		$variationTheme = array();
		if (defined('MAGNA_FIELD_ATTRIBUTES_EAN') 
			&& MagnaDB::gi()->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES)
		) {
			$variationTheme = MagnaDB::gi()->fetchArray('
			    SELECT po.products_options_name AS VariationTitle,
			           pov.products_options_values_name AS VariationValue,
			           pa.products_attributes_id AS aID,
			           pa.options_values_price AS aPrice,
			           pa.price_prefix AS aPricePrefix,
			           pa.attributes_stock AS Quantity
			      FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa,
			           '.TABLE_PRODUCTS_OPTIONS.' po, 
			           '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov, 
			           '.TABLE_LANGUAGES.' l
			     WHERE pa.products_id = \''.$pID.'\'
			           AND po.language_id = l.languages_id
			           AND po.products_options_id = pa.options_id
			           AND po.products_options_name<>\'\'
			           AND pov.language_id = l.languages_id
			           AND pov.products_options_values_id = pa.options_values_id
			           AND pov.products_options_values_name<>\'\'
			           AND pa.attributes_stock IS NOT NULL
			           AND l.directory = \''.$_SESSION['language'].'\'
			');
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
		
			if (!empty($variationTheme)) {
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
					$this->simpleprice->setPrice($data['price'])->removeTax($tax);
	
					if ($item['aPricePrefix'] == '+') {
						$this->simpleprice->addLump($item['aPrice']);
					} else {
						$this->simpleprice->subLump($item['aPrice']);
					}
	
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
				}
			}
			$data['submit']['Variations'] = $variationTheme;
			#echo print_m($variationTheme);
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
					'mpID' => $this->_magnasession['mpID'],
					'errormessage' => $err['ERRORMESSAGE'],
					'dateadded' => gmdate('Y-m-d H:i:s'),
					'additionaldata' => serialize($ad),
				);
				MagnaDB::gi()->insert(TABLE_MAGNA_MEINPAKET_ERRORLOG, $err);
			}
		}
		magnaMeinpaketProcessCheckinResult($result, $this->_magnasession['mpID']);
	}

	protected function filterSelection() { }

	protected function preSubmit(&$request) {
		MagnaConnector::gi()->setTimeOutInSeconds(600);
	}

	protected function postSubmit() {
		MagnaConnector::gi()->resetTimeOut();
	}

	protected function generateRedirectURL($state) {
		return toURL(array(
			'mp' => $this->realUrl['mp'],
			'mode' => 'listings',
		), true);
	}

}