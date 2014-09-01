<?php
define('SHOPGATE_PLUGIN_VERSION', '2.1.16');

/**
 * Modified eCommerce Plugin for Shopgate
 */
class ShopgateModifiedPlugin extends ShopgatePlugin {
	
	/**
	 * @var ShopgateConfigModified
	 */
	protected $config;

	private $languageId;
	private $countryId;
	private $zoneId;

	private $currency;

	private $language = "german";

	public function startup() {
		$requiredFiles = array(
			'inc/xtc_validate_password.inc.php',
			'inc/xtc_format_price_order.inc.php',
			'inc/xtc_db_prepare_input.inc.php',
			'includes/classes/xtcPrice.php',
			'inc/xtc_get_products_stock.inc.php',
		);

		foreach($requiredFiles as $file) {
			require_once(DIR_FS_CATALOG.$file);
		}
		
		// initialize configuration
		require_once(DIR_FS_CATALOG.'includes/external/shopgate/base/shopgate_config.php');
		$this->config = new ShopgateConfigModified();
		if (!isset($_REQUEST['shop_number'])) {
			$this->config->loadFile();
		} else {
			$this->config->loadByShopNumber($_REQUEST['shop_number']);
		}
		
		// fetch country
		$qry = "SELECT * FROM `".TABLE_COUNTRIES."` WHERE UPPER(countries_iso_code_2) = UPPER('".$this->config->getCountry()."')";
		$qry = xtc_db_fetch_array(xtc_db_query($qry));
		$this->countryId = !empty($qry['countries_id']) ? $qry['countries_id'] : 'DE';

		// fetch language
		$qry = "SELECT * FROM `".TABLE_LANGUAGES."` WHERE UPPER(code) = UPPER('".$this->config->getLanguage()."')";
		$qry = xtc_db_fetch_array(xtc_db_query($qry));
		$this->languageId = !empty($qry['languages_id']) ? $qry['languages_id'] : 2;
		$this->language = !empty($qry['directory']) ? $qry['directory'] : 'german';

		// fetch currency
		$qry = "SELECT * FROM `".TABLE_CURRENCIES."` WHERE UPPER(code) = UPPER('".$this->config->getCurrency()."')";
		$qry = xtc_db_fetch_array(xtc_db_query($qry));
		$this->exchangeRate = !empty($qry['value']) ? $qry['value'] : 1;
		$this->currencyId = !empty($qry['currencies_id']) ? $qry['currencies_id'] : 1;
		$this->currency = !empty($qry)
			? $qry
			: array('code' => 'EUR', 'symbol_left' => '', 'symbol_right' => ' EUR', 'decimal_point' => ',', 'thousands_point' => '.', 'decimal_places' => '2', 'value' => 1.0);

		$this->zoneId = $this->config->getTaxZoneId();

		if (!defined('DIR_FS_LANGUAGES')) {
			define('DIR_FS_LANGUAGES', rtrim(DIR_FS_CATALOG, '/'). '/lang/');
		}

//		$langFiles = array(
//			'admin/categories.php',
//			'admin/content_manager.php',
//		);

//		foreach ($langFiles as $langFile) {
//			include_once DIR_FS_LANGUAGES."/$this->language/$langFile";
//		}

		return true;
	}

	protected function createCategoriesCsv() {
		
		if($this->config->getReverseCategoriesSortOrder()){
			$maxOrder = 0;
		} else {
			$qry = "SELECT MAX( sort_order ) sort_order FROM " . TABLE_CATEGORIES;
			$maxOrder = xtc_db_fetch_array( xtc_db_query( $qry ) );
			$maxOrder = $maxOrder["sort_order"] + 1;
		}

		$this->_buildCategoriesTree(0, $maxOrder);
	}

	private function _buildCategoriesTree($parentId = 0, $maxOrder = 0 ) {
		$qry = "
		SELECT DISTINCT
		c.categories_id,
		c.parent_id,
		c.categories_image,
		c.categories_status,
		c.sort_order,
		cd.categories_name
		FROM ".TABLE_CATEGORIES." c
		LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON (c.categories_id = cd.categories_id
		AND cd.language_id = $this->languageId)
		WHERE c.parent_id = $parentId ORDER BY c.categories_id ASC
		";

		$qry = xtc_db_query( $qry );

		while( $item = xtc_db_fetch_array( $qry ) ) {

			$row = $this->buildDefaultCategoryRow();

			$row["category_number"] = $item["categories_id"];
			$row["parent_id"] = (empty($item["parent_id"]) || ($item['parent_id'] == $item['categories_id']))
				? ""
				: $item["parent_id"];
			$row["category_name"] = htmlentities($item["categories_name"], ENT_NOQUOTES, $this->config->getEncoding());

			if(!empty($item["categories_image"])){
				$row["url_image"] = HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES."categories/".$item["categories_image"];
			}

			if (!empty($item["sort_order"]) || ((string) $item['sort_order'] === '0')) {
				if($this->config->getReverseCategoriesSortOrder()){
					// reversed means the contrary to ordering system in shopgate - order_index is a priority system - high number = top position
					// so just taking over the values means reversing the order
					$row["order_index"] = $item["sort_order"];
				} else {
					$row["order_index"] = $maxOrder - $item["sort_order"];
				}
			}
			
			$row["is_active"] = $item["categories_status"];
			$row["url_deeplink"] = HTTP_SERVER.DIR_WS_CATALOG."index.php?cat=c" . $item["categories_id"];
			
			$this->addCategoryRow($row);
			
			if ($item['parent_id'] != $item['categories_id']) {
				$this->_buildCategoriesTree($item["categories_id"], $maxOrder);
			}
		}
	}

	/**
	 * @see ShopgatePluginApi::createItemsCsv()
	 */
	protected function createItemsCsv() {
		$customerGroupMaxPriceDiscount = 0;
		$customerGroupDiscountAttributes = false;

		// get customer-group first
		$qry = "SELECT"
		. " status.customers_status_name,"
		. " status.customers_status_discount,"
		. " status.customers_status_discount_attributes"
		. " FROM " . TABLE_CUSTOMERS_STATUS . " AS status"
		. " WHERE status.customers_status_id = " . $this->config->getCustomerPriceGroup()
		. " AND status.language_id = " . $this->languageId
		. ";";

		// Check if the customer group exists (ignore if not)
		$queryResult = xtc_db_query($qry);
		if($queryResult) {
			$customerGroupResult = xtc_db_fetch_array($queryResult);
			if(!empty($customerGroupResult) && isset($customerGroupResult['customers_status_discount'])) {
				$customerGroupMaxPriceDiscount = $customerGroupResult['customers_status_discount'];
			}
			if(!empty($customerGroupResult) && isset($customerGroupResult['customers_status_discount'])) {
				$customerGroupDiscountAttributes = $customerGroupResult['customers_status_discount_attributes'] ? true : false;
			}
		}
		
		$qry = "
			SELECT DISTINCT
				p.products_id,
				p.products_model,
				p.products_ean,
				p.products_quantity,
				p.products_image,
				p.products_price,
				DATE_FORMAT(p.products_last_modified, '%Y-%m-%d') as products_last_modified,
				p.products_weight,
				p.products_status,
				sp.specials_new_products_price,
				sp.specials_quantity,
				pdsc.products_keywords,
				pdsc.products_name,
				pdsc.products_description,
				pdsc.products_short_description,
				shst.shipping_status_name,
				mf.manufacturers_name,
				p.products_tax_class_id,
				p.products_fsk18,
				p.products_vpe_status,
				p.products_vpe_value,
				vpe.products_vpe_name,
				p.products_sort,
				p.products_startpage,
				p.products_startpage_sort,
				p.products_discount_allowed
			FROM ".TABLE_PRODUCTS." p
			LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pdsc ON (p.products_id = pdsc.products_id AND pdsc.language_id = '".$this->languageId."')
			LEFT JOIN ".TABLE_SHIPPING_STATUS." shst ON (p.products_shippingtime = shst.shipping_status_id AND shst.language_id = '".$this->languageId."')
			LEFT JOIN ".TABLE_MANUFACTURERS." mf ON (mf.manufacturers_id = p.manufacturers_id)
			LEFT JOIN ".TABLE_SPECIALS." sp ON (sp.products_id = p.products_id AND sp.status = 1 AND (sp.expires_date > now() OR sp.expires_date = '0000-00-00 00:00:00' OR sp.expires_date IS NULL))
			LEFT JOIN ".TABLE_PRODUCTS_VPE." vpe ON (vpe.products_vpe_id = p.products_vpe AND vpe.language_id = pdsc.language_id)
			WHERE p.products_status = 1
				AND (p.products_date_available < NOW() OR p.products_date_available IS NULL)
			";
		
		// sp.specials_quantity > 0 deleted, to handle the check in the loop
		
		if(STOCK_CHECK == "true" && STOCK_ALLOW_CHECKOUT == 'false') {
			$qry .= " AND p.products_quantity > 0 ";
		}
		// Ahorn24 fix. 10 products were not found without sorting.
		$qry .= ' ORDER BY p.products_id ASC ';

		$maxId = xtc_db_fetch_array( xtc_db_query("SELECT MAX(products_id) max_id FROM ".TABLE_PRODUCTS) );
		$maxId = $maxId["max_id"];

		// order_index for the products
		$orderIndices = xtc_db_fetch_array( xtc_db_query("SELECT MIN(products_sort) AS 'min_order', MAX(products_sort) AS 'max_order' FROM ".TABLE_PRODUCTS) );
		$maxOrder = $orderIndices["max_order"]+1;
		$minOrder = $orderIndices["min_order"];
		$addToOrderIndex = 0;
		
		if($minOrder < 0) {
			// make the sort_order positive
			$addToOrderIndex += abs($minOrder);
		}
		
		if($this->splittedExport) $qry .= " LIMIT {$this->exportOffset}, {$this->exportLimit}";
		
		$query = xtc_db_query($qry);
		while($item = xtc_db_fetch_array($query)) {
			$itemArr = $this->buildDefaultItemRow();
			$orderInfos = array();
			
			$tax_rate = xtc_get_tax_rate($item["products_tax_class_id"], $this->countryId, $this->zoneId);
			
			// Get variantions and input fields
			$variations = $this->_getVariations($item["products_id"], $tax_rate);
			$inputFields = $this->_getInputFields($item["products_id"]);
			
			// Get categories
			$categories = $this->_getProductPath($item["products_id"]);
			
			// Get Image Urls
			$images = $this->_getProductsImages($item);
			
			$httpServer = HTTP_SERVER;
			if(empty($httpServer)) $httpServer = "http://".$_SERVER["HTTP_HOST"];
			$deeplink = $httpServer.DIR_WS_CATALOG."product_info.php?". xtc_product_link($item['products_id'], $item['products_name']);
			
			// Calculate the price
			$price = $item["products_price"];
			$oldPrice = '';
			
			// Special offers for a Customer group
			$pOffers = $this->_getPersonalOffersPrice($item, $tax_rate);
			if(!empty($pOffers) && round($pOffers, 2) > 0) {
				$price = $pOffers;
				// Ignore the "old price" if it is lower than the offer amount (xtc3 also tells the old price here, but it's not very intuitive)
				if($pOffers < $item["products_price"]) {
					$oldPrice = $item["products_price"];
				}
			}
			
			// General special offer or customer group price reduction
			$productDiscount = 0;
			if(!empty($item["specials_new_products_price"])) {
				if(STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT == 'false') {
					if($item["specials_quantity"] > 0){
						// Nur wenn die quantity > 0 ist dann specialprice setzen, ansonsten normalen Preis mit normalem Stock
						$item["products_quantity"] = $item["specials_quantity"] > $item["products_quantity"] ? $item["products_quantity"] : $item["specials_quantity"];
					}
				}
				// setting specialprice
				$oldPrice = $item["products_price"];
				$price = $item["specials_new_products_price"];
				
				$orderInfos['is_special_price'] = 1;
				
			} elseif(!empty($customerGroupMaxPriceDiscount) && round($customerGroupMaxPriceDiscount, 2) > 0
				  && !empty($item['products_discount_allowed']) && round($item['products_discount_allowed'], 2) > 0) {
				$productDiscount = round($item['products_discount_allowed'], 2);
				
				// Limit discount to the customer groups maximum discount
				if(round($customerGroupMaxPriceDiscount, 2) < $productDiscount) {
					$productDiscount = round($customerGroupMaxPriceDiscount, 2);
				}
				
				$oldPrice = $price;
				if($oldPrice < $item['products_price']) {
					$oldPrice = $item['products_price'];
				}
				
				// Reduce price to the discounted price
				$price = $this->_getDiscountPrice($price, $productDiscount);
				
				$orderInfos['is_special_price'] = 1;
			}
			
			$category_numbers = $this->_getProductCategoryNumbers($item, $maxOrder, $addToOrderIndex);
			
			$price *= $this->exchangeRate;
			$price = $price * ( 1 + ( $tax_rate / 100 ) );
			
			if (!empty($oldPrice)) {
				$oldPrice = $oldPrice * $this->exchangeRate;
				$oldPrice = $this->formatPriceNumber( $oldPrice * ( 1 + ( $tax_rate / 100 ) ) );
			}

			$description = $this->removeTagsFromString($item["products_description"]);
			
			// check if description is empty, use short description in that case
			// if the description is something like '<p>&nbsp;</p>' that is considered "empty". hence the dirty $dummyDescription
			$dummyDescription = trim(str_replace(array('&nbsp;'), array(''), $this->removeTagsFromString($description, array('P', 'p', 'br'))));
			if(empty($dummyDescription)){
				// if no "full"-description is available use the short_description
				$description = $this->removeTagsFromString($item["products_short_description"]);
			}
			
			$description = preg_replace("/\n|\r/", "", $description);
			
			$itemArr['item_number'] = $item["products_id"];
			$itemArr['item_number_public'] = $item['products_model'];
			$itemArr['manufacturer'] = $item["manufacturers_name"];
			$itemArr['item_name'] = trim( preg_replace('/<[^>]+>/',' ', $item["products_name"]) );
			$itemArr['description'] =  $description;
			$itemArr['unit_amount'] = $this->formatPriceNumber($price);
			$itemArr['currency'] = $this->currency["code"];
			$itemArr['is_available'] = $item["products_status"];
			$itemArr['available_text'] = (string) $item["shipping_status_name"];
			$itemArr['url_deeplink'] = $deeplink;
			$itemArr['urls_images'] = $images;
			$itemArr['categories'] = $categories;
			$itemArr['category_numbers'] = implode("||", $category_numbers);
			$itemArr['use_stock'] = (STOCK_ALLOW_CHECKOUT == 'true' || STOCK_CHECK != 'true') ? 0 : 1;
			$itemArr['stock_quantity'] = $item['products_quantity'];
			$itemArr['weight'] = $item["products_weight"]*1000;
			$itemArr['tags'] = trim($item["products_keywords"]);
			$itemArr['tax_percent'] = $tax_rate;
			$itemArr['shipping_costs_per_order'] = 0;
			$itemArr['additional_shipping_costs_per_unit'] = 0;
			$itemArr['ean'] = preg_replace("/\s+/i",'',$item["products_ean"]);
			$itemArr['last_update'] = $item["products_last_modified"];
			$itemArr['block_pricing'] =  $this->_getPackeges($item, $tax_rate);
			$itemArr['age_rating'] = $item["products_fsk18"] == 1 ? '18' : '';
			$itemArr['related_shop_item_numbers'] = $this->_getRelatedShopItems($item["products_id"]);
			$itemArr['basic_price'] = $this->_getProductVPE($item, $price);
			$itemArr['is_highlight'] = $item["products_startpage"];
			$itemArr['highlight_order_index'] = $item["products_startpage_sort"];
			
			if($this->config->getReverseItemsSortOrder()){
				// $addToOrderIndex to make positive sort_order
				$itemArr['sort_order'] = $item["products_sort"] + $addToOrderIndex;
			} else {
				$itemArr['sort_order'] = ($maxOrder - $item["products_sort"])+$addToOrderIndex;
			}
			
			if(!empty($orderInfos)){
				$itemArr['internal_order_info'] = $this->jsonEncode($orderInfos);
			}

			$itemNumber = 0 ;
			
			if(!empty($oldPrice) && round($oldPrice, 2) > 0) {
				$itemArr['old_unit_amount'] = $oldPrice;
			} else {
				$itemArr['old_unit_amount'] = '';
			}
			
			if($itemArr['available_text'] == 'Unbekannt') {
				$itemArr['is_available'] = 0;
			}
			
			if(!empty($inputFields)){
				$itemArr['has_input_fields'] = "1";
				$itemArr = array_merge($itemArr, $inputFields);
			}
			
			if(!empty($variations)) {
				if($variations["has_options"]) {
					$itemArr['has_options']=1;
					$this->addItemRow(array_merge($itemArr, $variations));
				} else {
					if(isset($variations['has_options'])){
						unset($variations['has_options']);
					}
					$itemArr['has_children']=1;
					$itemNumber = $itemArr["item_number"];
					$basePrice = round($itemArr["unit_amount"], 2);
					$baseOldPrice = round($itemArr["old_unit_amount"], 2);
					$baseWeight = $itemArr["weight"];

					$parentItemNumber = $itemArr["item_number"];
					$isFirst = true;

					// Kinder haben gleiche Textfelder
					if(!empty($inputFields)){
						$itemArr['has_input_fields'] = "1";
						$itemArr = array_merge($itemArr, $inputFields);
					}

					foreach ($variations as $key => $variation) {
						$price = 0;
						$weight = 0;
						// Offset amount including tax without discounts (but with exchange rate, of set)
						$originalOffsetAmount = 0;
						if(!empty($variation["offset_amount"])) {
							if(!empty($this->exchangeRate)) {
								$variation["offset_amount"] *= $this->exchangeRate;
								$variations[$key]["offset_amount"] = $variation["offset_amount"];
							}
							$originalOffsetAmount = $variation["offset_amount"]*(1+($tax_rate/100));
							
							// Variations also need to be discounted if products discount is set
							if(!empty($productDiscount) && round($productDiscount, 2) > 0) {
								if($customerGroupDiscountAttributes) { // Seems to be buggy in gambio so it is ignored here
									$variation["offset_amount"] = $this->_getDiscountPrice($variation["offset_amount"], $productDiscount);
									$variations[$key]["offset_amount"] = $variation["offset_amount"];
								}
							}
							$price = $variation["offset_amount"]*(1+($tax_rate/100));
						}


						if(isset($variation["offset_weight"]))
							$weight = $variation["offset_weight"] * 1000;

						$hash = "";

						for($i=1; $i < 10 && isset($variation["attribute_$i"]); $i++) {
							$hash .= $variation["attribute_$i"];
							$itemArr["attribute_$i"] = htmlentities($variation["attribute_$i"], ENT_NOQUOTES, $this->config->getEncoding());
						}

						$hash = md5($hash);
						$hash = substr($hash, 0, 5);
						if(empty($variation)) $variation = array("order_info" => array());

						// Set Order Info from parent product
						if(!empty($variation['order_info']) && is_array($variation['order_info'])){
							$variation["order_info"] = array_merge($orderInfos, $variation['order_info']);
						} else {
							$variation["order_info"] = $orderInfos;
						}
						
						$variation["order_info"]["base_item_number"] = $itemNumber;

						$itemArr['internal_order_info'] = $this->jsonEncode($variation["order_info"]);
						$itemArr["item_number"] = $itemNumber.($isFirst?"":"_".$hash);
						if(!empty($variation["item_number"])){
							$itemArr["item_number_public"] = $variation["item_number"];
						} else {
							$itemArr['item_number_public'] = $item['products_model'];
						}
						
						$itemArr["unit_amount"] = $this->formatPriceNumber($basePrice + $price);
						if(!empty($baseOldPrice) && round($baseOldPrice, 2) > 0) {
							$itemArr["old_unit_amount"] = $this->formatPriceNumber($baseOldPrice + $originalOffsetAmount);
						} else {
							$itemArr["old_unit_amount"] = '';
						}
						$itemArr["weight"]			= $baseWeight + $weight;
						
						if($isFirst == false){
							$itemArr["use_stock"]	= (STOCK_ALLOW_CHECKOUT == 'true' || ATTRIBUTE_STOCK_CHECK != 'true') ? 0 : 1;
						}
						
						// Overwrite stock only if its set up in the configuration
						if(ATTRIBUTE_STOCK_CHECK == 'true' && $isFirst == false){
							if(!empty($item["specials_new_products_price"]) && $item["specials_quantity"] > 0){
								$itemArr["stock_quantity"] = $variation["stock_quantity"] > $item["specials_quantity"] ? $item["specials_quantity"] : $variation["stock_quantity"];
							} else {
								$itemArr["stock_quantity"]		= $variation["stock_quantity"];
							}
						}

						$itemArr['properties'] = $this->_buildProperties($item, $itemArr);
						
						$this->addItemRow($itemArr);

						$isFirst = false;
						$itemArr['has_children']=0;
						$itemArr["parent_item_number"] = $parentItemNumber;
					}
				}
			} else {
				$itemArr['has_children']=0;
				$itemArr['properties'] = $this->_buildProperties($item, $itemArr);
				$this->addItemRow($itemArr);
			}
		}
	}

	private function _getPackeges($product, $tax_rate) {
		$customerStatusId = $this->config->getCustomerPriceGroup();
		if($customerStatusId > 0) return '';

		$qry = "
			SELECT *
			FROM ".TABLE_PERSONAL_OFFERS_BY."$customerStatusId
			WHERE products_id = '".$product["products_id"]."'
			  AND quantity > 1
			ORDER BY quantity
		";

		$specialOffers = array();
		$_specialOffers = xtc_db_query($qry);

		while($specialOffer = xtc_db_fetch_array($_specialOffers)) {
			$specialOffers[] = implode("=>", array(
				"qty" => $specialOffer["quantity"],
				"personal_offer" => round($specialOffer["personal_offer"] * (1+($tax_rate/100)), 2),
			));
		}

		return implode("||", $specialOffers);
	}

	private function _getProductVPE($product, $price) {
		$vpe = "";

		if(!empty($product["products_vpe_value"]) && !empty($product["products_vpe_name"]) && $product["products_vpe_value"] != 0.0000){

			if($product["products_vpe_status"] == 1){

				$factor = 1;
				switch(strtolower($product["products_vpe_name"])) {
					case "ml":
					case "mg":
						$factor = $product["products_vpe_value"]<250?100:1000;
						break;
				}
				
				$_price = ( $price / $product["products_vpe_value"] ) * $factor;

				$vpe  = $this->currency["symbol_left"];
				
				$vpe .= $this->formatPriceNumber(
					$_price,
					$this->currency["decimal_places"],
					$this->currency["decimal_point"],
					$this->currency["thousands_point"]
				);
				
				$vpe .= " " . trim($this->currency["symbol_right"]);
				$vpe .= ' pro '.(($factor == 1) ? '' : $factor.' ');
				$vpe .= $product["products_vpe_name"];
			}
		}

		return $vpe;
	}

	/**
	 * Exportiere alle Produktbilder
	 *
	 * @param string $product
	 */
	private function _getProductsImages($product) {
		$qry = "
			SELECT *
			FROM ".TABLE_PRODUCTS_IMAGES."
			WHERE products_id = '".$product["products_id"]."'
			ORDER BY image_nr
		";

		$images = array();

		if(!empty($product['products_image'])){
			if(file_exists(DIR_FS_CATALOG.DIR_WS_ORIGINAL_IMAGES.$product['products_image'])){
				$images[] = HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_ORIGINAL_IMAGES.$product['products_image'];
			}elseif(file_exists(DIR_FS_CATALOG.DIR_WS_POPUP_IMAGES.$product['products_image'])){
				$images[] = HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_POPUP_IMAGES.$product['products_image'];
			}
		}

		$query = xtc_db_query($qry);
		while($image = xtc_db_fetch_array($query)) {
			if(file_exists(DIR_FS_CATALOG.DIR_WS_ORIGINAL_IMAGES.$image['image_name'])){
				$images[] = HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_ORIGINAL_IMAGES.$image['image_name'];
			}elseif(file_exists(DIR_FS_CATALOG.DIR_WS_POPUP_IMAGES.$image['image_name'])){
				$images[] = HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_POPUP_IMAGES.$image['image_name'];
			}
		}

		$images = implode("||", $images);

		return $images;
	}

	private function _getProductCategoryNumbers($item, $maxId, $addToOrderIndex) {
		$category_numbers = array();
		
		$catsQry = "
			SELECT DISTINCT
				ptc.categories_id,
				c.products_sorting2
			FROM ".TABLE_PRODUCTS_TO_CATEGORIES." ptc
			INNER JOIN ".TABLE_CATEGORIES." c ON (ptc.categories_id = c.categories_id)
			WHERE ptc.products_id = '".$item["products_id"]."'
				AND c.categories_status = 1
			";
		$catsQuery = xtc_db_query($catsQry);
		
		while($category = xtc_db_fetch_array($catsQuery)) {
			if(empty($category["categories_id"])) {
				continue;
			}
			
			$catNumber = "";
			
			if($category["products_sorting2"] != "ASC"){
				
				if($this->config->getReverseItemsSortOrder()){
					$sort = $maxId - $item["products_sort"];
				} else {
					$sort = $item["products_sort"];
				}
				
			} else {
				
				if($this->config->getReverseItemsSortOrder()){
					$sort = $item["products_sort"];
				} else {
					$sort = $maxId - $item["products_sort"];
				}
			}
			
			if (!empty($sort) || ((string) $sort === '0')) {
				$sort += $addToOrderIndex;
				$catNumber = "=>".$sort;
			}
			$catNumber = $category["categories_id"].$catNumber;
			$category_numbers[] = $catNumber;
		}
		
		return $category_numbers;
	}

	/**
	 *
	 * @param mixed[] $product
	 * @param mixed[] $tax
	 * @return float
	 */
	private function _getPersonalOffersPrice($product, $tax) {
		$customerStatusId = $this->config->getCustomerPriceGroup();
		if(empty($customerStatusId)) return false;

		$qry = "SELECT * FROM ".TABLE_PERSONAL_OFFERS_BY."$customerStatusId
		WHERE products_id = '".$product["products_id"]."'
		AND quantity = 1";
		
		$qry = xtc_db_query($qry);
		if(!$qry) return false;
		
		$specialOffer = xtc_db_fetch_array( $qry );
		
		return floatval($specialOffer["personal_offer"]);
	}

	/**
	 * Takes a price value and a discount percent value and returns the new discounted price
	 * @param float $price
	 * @param float $discountPercent
	 * @return float
	 */
	private function _getDiscountPrice($price, $discountPercent) {
		$discountedPrice = $price * (1-$discountPercent/100);
		return $discountedPrice;
	}
	
	/**
	 * Load all Categories of the product and build its category-path
	 *
	 * The categories are seperated by a =>. The Paths are seperated b< a double-pipe ||
	 *
	 * Example: kategorie_1=>kategorie_2||other_1=>other_2
	 * @param int $productId
	 * @return string
	 */
	private function _getProductPath($productId) {
		$catsQry = "
			SELECT DISTINCT ptc.categories_id
			FROM ".TABLE_PRODUCTS_TO_CATEGORIES." ptc
			INNER JOIN ".TABLE_CATEGORIES." c ON ptc.categories_id = c.categories_id
			WHERE ptc.products_id = '$productId'
			  AND c.categories_status = 1
			ORDER BY products_sorting
		";
		$catsQuery = xtc_db_query($catsQry);

		$categories = "";
		while($category = xtc_db_fetch_array($catsQuery)) {
			$cats = xtc_get_category_path($category["categories_id"]);
			$cats = preg_replace("/\_/", ",", $cats);

			$q = "
				SELECT DISTINCT cd.categories_name
				FROM ".TABLE_CATEGORIES_DESCRIPTION." cd
				WHERE cd.categories_id IN (".$cats.")
					AND cd.language_id = ".$this->languageId."
				ORDER BY find_in_set(cd.categories_id, '$cats')
			";

			$q = xtc_db_query($q);
			$cats = "";
			while($cd = xtc_db_fetch_array($q)) {
				if(!empty($cats))$cats.="=>";
				$cats.=$cd["categories_name"];
			}
			if(!empty($categories))$categories.="||";
			$categories.=$cats;
		}

		return $categories;
	}

	/**
	 * Returns a array with all Variations of the Product
	 * @param int $productId
	 */
	private function _getVariations($productId, $tax_rate) {
		$sg_prod_var = array();
		
		$qry = "
			SELECT
				pa.products_attributes_id,
				po.products_options_id,
				pov.products_options_values_id,
				po.products_options_name,
				pov.products_options_values_name,
				pa.attributes_model,
				pa.options_values_price,
				pa.price_prefix,
				pa.options_values_weight,
				pa.attributes_stock,
				pa.weight_prefix
			FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
			INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po ON (pa.options_id = po.products_options_id AND po.language_id = $this->languageId)
			INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov ON (pa.options_values_id = pov.products_options_values_id AND pov.language_id = $this->languageId)
			WHERE pa.products_id = '".$productId."'
				AND pov.products_options_values_name != 'TEXTFELD'
		";

		$qry .= " ORDER BY po.products_options_id, pa.sortorder ASC";

		$query = xtc_db_query($qry);

		//		$options = array_pad(array(), 5, "");
		$options = array();

		$i=-1;
		$old = null;
		while($variation = xtc_db_fetch_array($query)) {
			if($variation["products_options_id"] != $old || is_null($old)){
				$i++;
				$old = $variation["products_options_id"];
			}
			$options[$i][] = $variation;
		}

		if(empty($options)) return array();

		// Find and rename duplicate option-value names
		foreach($options as $optionIndex => $singleOption) {
			// Check all option-value names for duplicate names
			foreach($singleOption as $key => $optionVariation) {
				if(!empty($optionVariation)) {
					// Compare with following entries
					$indexNumber = 1;
					for($i = $key+1; $i < count($singleOption); $i++) {
						if(trim($singleOption[$i]['products_options_values_name']) == trim($optionVariation['products_options_values_name'])) {
							$indexNumber++;
							$options[$optionIndex][$i]['products_options_values_name'] .= " $indexNumber";
						}
					}
					// Add index 1 to the actual name if duplicate name-entries found
					if($indexNumber > 1) {
						$options[$optionIndex][$key]['products_options_values_name'] .= " 1";
		
						// Refresh the working variable for further operation
						$singleOption = $options[$optionIndex];
					}
				}
			}
		}
		
		$countVariations = 1;
		foreach($options as $option){
			$countVariations *= count($option);
		}

		if($countVariations > $this->config->getMaxAttributes()) {
			$this->_buildOptions($sg_prod_var, $options, $tax_rate);
			$sg_prod_var["has_options"] = 1;
		} else {
			$this->_buildAttributes($sg_prod_var, $options);
			$sg_prod_var["has_options"] = 0;
		}

		return $sg_prod_var;
	}


	/**
	 * Build the Productvariations as options
	 *
	 * @param &array $sg_prod_var
	 * @param array $variations
	 * @param float $tax_rate
	 */
	private function _buildOptions(&$sg_prod_var, $variations, $tax_rate) {
		$tmp=array();
		$i = 0;
		foreach($variations as $_variation) {
			$i++;
			$tmp["option_$i"] = strip_tags($_variation[0]["products_options_name"]);

			$options = array();
			foreach($_variation as $option) {
				// Currency and tax must be included here because the data is directly used for the item
				$optionOffsetPrice = $option["options_values_price"]*$this->exchangeRate*(1+($tax_rate/100)); // Include Tax
				$optionOffsetPrice = round($optionOffsetPrice * 100, 0); // get euro-cent

				$field  = strip_tags($option["products_options_values_id"])."=".strip_tags($option["products_options_values_name"]);
				$field .= ($option["options_values_price"] != 0)
					? "=>".$option["price_prefix"].$optionOffsetPrice
					: "";

				$options[] = $field;
			}
			$tmp["option_".$i."_values"] = implode("||", $options);
		}

		$sg_prod_var = $tmp;
	}

	/**
	 * Build the Productvariations recursively
	 *
	 * @param &array $sg_prod_var
	 * @param array $variations
	 * @param int $index
	 * @param array $tmp
	 */
	private function _buildAttributes(&$sg_prod_var, $variations, $index=0, $tmp=array()) {
		if($index == 0) {
			// Index 0 sind die Überschriften. Diese müssen als erstes hinzugefügt werden
			for($i=0;$i<count($variations);$i++){
				$sg_prod_var[0]["attribute_".($i+1)] = $variations[$i][0]["products_options_name"];
			}
		}
		
		foreach($variations[$index] as $variation) {
			if(count($variations) == 1){
				// only if 1 dimension
				$tmp["item_number"] = $variation["attributes_model"];
			}
			
			$tmp["attribute_".($index+1)] = $variation["products_options_values_name"];
			$tmp["order_info"]["attribute_".($index+1)] = $variation["products_attributes_id"];
			
			if (isset($tmp['stock_quantity'])) {
				$oldStock = $tmp['stock_quantity'];
			}
			if(isset($tmp["stock_quantity"]) && $variation["attributes_stock"] < $tmp["stock_quantity"] || !isset($tmp["stock_quantity"])){
				$tmp["stock_quantity"] = $variation["attributes_stock"];
			}

			// Kalkuliere den Preisunterschied (Steuern und Währung werden noch nicht hier berücksichtigt)
			$price = $variation["options_values_price"];
			if($variation["price_prefix"] == "-"){
				$price = -1 * $price;
			}
			if (empty($tmp['offset_amount'])) {
				$tmp['offset_amount'] = 0;
			}
			$tmp["offset_amount"] += $price;

			// Kalkuliere den Gewichtsunterschied
			$weight = (float) $variation["options_values_weight"];
			if($variation["weight_prefix"] == "-"){
				$weight = -1 * $weight;
			}
			if (empty($tmp['offset_weight'])) {
				$tmp['offset_weight'] = 0;
			}
			$tmp["offset_weight"] += (double) $weight;

			if($index < (count($variations)-1)) {
				// Fahre mit nächstem Attribute fort
				$this->_buildAttributes($sg_prod_var, $variations, $index+1, $tmp);
				unset($tmp["stock_quantity"]);
				unset($tmp["item_number"]);
			} else {
				// Wenn kein Attribut mehr existiert, dieses auf den Stack legen
				$sg_prod_var[] = $tmp;
			}

			// Preis und Gewicht und stock_quantity wieder rückgängig machen
			$tmp["offset_amount"] -= $price;
			$tmp["offset_weight"] -= $weight;
			if (!empty($oldStock)) {
				$tmp["stock_quantity"] = $oldStock;
			}
		}
	}

	private function _getRelatedShopItems($products_id) {
		$qry = "
			SELECT px.xsell_id
			FROM ".TABLE_PRODUCTS_XSELL." px
			INNER JOIN ".TABLE_PRODUCTS." p ON (px.products_id = p.products_id)
			WHERE p.products_id = '$products_id'
				AND (p.products_date_available < NOW() OR p.products_date_available IS NULL)
			ORDER BY px.sort_order
		";
		
		$xSellIds = array();
		
		$query = xtc_db_query($qry);
		for($i = 0; $i < xtc_db_num_rows($query); $i++) {
		$array = xtc_db_fetch_array($query);
		$xSellIds[] = $array["xsell_id"];
		}
		
		return implode("||", $xSellIds);
	}
	
	private function _buildProperties($product, $itemArr) {
		$properties = array();

		if(!empty($product["products_fsk18"]) && $product["products_fsk18"] == 1)
			$properties[] = "Altersbeschränkung=>18 Jahre";

		return implode("||", $properties);
	}


	private function _getInputFields($productId){
		$qry = "
			SELECT
				pa.products_attributes_id,
				po.products_options_id,
				pov.products_options_values_id,
				po.products_options_name,
				pov.products_options_values_name,
				pa.attributes_model,
				pa.options_values_price,
				pa.price_prefix,
				pa.options_values_weight,
				pa.attributes_stock,
				pa.weight_prefix
			FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
			INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po ON pa.options_id = po.products_options_id
			INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov ON (pa.options_values_id = pov.products_options_values_id AND pov.language_id = $this->languageId)
			WHERE pa.products_id = '$productId'
				AND pov.products_options_values_name = 'TEXTFELD'
			ORDER BY po.products_options_id, pa.sortorder
		";
		
		$query = xtc_db_query($qry);
		
		while($inputFields = xtc_db_fetch_array($query)) {
			if($inputFields["products_options_id"] != $old){
				$i++;
				$old = $inputFields["products_options_id"];
			}
			$inputFieldsAll[$i][] = $inputFields;
		}
		
		if(empty($inputFieldsAll)){
			return;
		}
		
		$sg_product_var = $this->_buildInputFields($inputFieldsAll);

		return $sg_product_var;

	}

	private function _buildInputFields($inputFieldsAll){
		$sg_product_var = array();
		$i = 0;
		foreach($inputFieldsAll as $inputField) {
			$i++;
			
			//			$sg_product_var["has_input_fields"] = 1;
			$sg_product_var["input_field_".$i."_type"] = 'text';
			$sg_product_var["input_field_".$i."_label"] = strip_tags($inputField[0]["products_options_name"]);
			$sg_product_var["input_field_".$i."_add_amount"] = ($inputField["options_values_price"] != 0)
				? "=>".$option["price_prefix"].round($inputField["options_values_price"], 2)
				: "";
			// keine Angabe möglich
			$sg_product_var["input_field_".$i."_infotext"] = '';
			$sg_product_var["input_field_".$i."_required"] = 0;
		}
		
		return $sg_product_var;
	}


	/**
	 * @see ShopgatePlugin::getCustomer()
	 */
	public function getCustomer($user, $pass) {
		// save the UTF-8 version for logging etc.
		$userUtf8 = $user;
		
		// decode the parameters if necessary to make them work with xtc_* functions
		$user = $this->stringFromUtf8($user, $this->config->getEncoding());
		$pass = $this->stringFromUtf8($pass, $this->config->getEncoding());

		// find customer
		$qry = "SELECT"

		// basic user information
		. " customer.customers_id,"
		. " customer.customers_cid,"
		. " status.customers_status_name,"
		. " status.customers_status_id,"
		. " customer.customers_gender,"
		. " customer.customers_firstname,"
		. " customer.customers_lastname,"
		. " date_format(customer.customers_dob,'%Y-%m-%d') as customers_birthday,"
		. " customer.customers_telephone,"
		. " customer.customers_email_address,"

		// additional information for password verification, default address etc.
		. " customer.customers_password,"
		. " customer.customers_default_address_id"

		. " FROM " . TABLE_CUSTOMERS . " AS customer"

		. " INNER JOIN " . TABLE_CUSTOMERS_STATUS . " AS status"
		. " ON customer.customers_status = status.customers_status_id"
		. " AND status.language_id = ".$this->languageId

		. " WHERE customers_email_address = '" . xtc_db_input($user) . "';";

		// user exists?
		$customerResult = xtc_db_query($qry);
		if (empty($customerResult)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD, 'User: '.$userUtf8);
		}

		// password's correct?
		$customerData = xtc_db_fetch_array($customerResult);
		if (!xtc_validate_password($pass, $customerData['customers_password'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD, 'User: '.$userUtf8);
		}

		// fetch customers' addresses
		$qry = "SELECT"

		. " address.address_book_id,"
		. " address.entry_gender,"
		. " address.entry_firstname,"
		. " address.entry_lastname,"
		. " address.entry_company,"
		. " address.entry_street_address,"
		. " address.entry_postcode,"
		. " address.entry_city,"
		. " country.countries_iso_code_2,"
		. " zone.zone_code"


		. " FROM " . TABLE_ADDRESS_BOOK . " AS address"

		. " LEFT JOIN " . TABLE_COUNTRIES . " AS country"
		. " ON country.countries_id = address.entry_country_id"

		. " LEFT JOIN " . TABLE_ZONES . " AS zone"
		. " ON address.entry_zone_id = zone.zone_id"
		. " AND country.countries_id = zone.zone_country_id"

		. " WHERE address.customers_id = " . xtc_db_input($customerData['customers_id']) . ";";

		$addressResult = xtc_db_query($qry);
		if (empty($addressResult)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_ADDRESSES_FOUND, 'User: '.$userUtf8);
		}

		$addresses = array();
		while ($addressData = xtc_db_fetch_array($addressResult)) {
			try {
				$stateCode = ShopgateXtcMapper::getShopgateStateCode($addressData["countries_iso_code_2"], $addressData["zone_code"]);
			} catch (ShopgateLibraryException $e) {
				// if state code can't be mapped to ISO use xtc3 state code
				$stateCode = $addressData['zone_code'];
			}

			try {
				$address = new ShopgateAddress();
				$address->setId($addressData['address_book_id']);
				$address->setAddressType(ShopgateAddress::BOTH); // xtc3 doesn't make a difference
				$address->setGender($addressData["entry_gender"]);
				$address->setFirstName($addressData["entry_firstname"]);
				$address->setLastName($addressData["entry_lastname"]);
				$address->setCompany($addressData["entry_company"]);
				$address->setStreet1($addressData["entry_street_address"]);
				$address->setZipcode($addressData["entry_postcode"]);
				$address->setCity($addressData["entry_city"]);
				$address->setCountry($addressData["countries_iso_code_2"]);
				$address->setState($stateCode);
			} catch (ShopgateLibraryException $e) {
				// don't abort here
			}

			// put default address in front, append the others
			if ($address->getId() == $customerData['customers_default_address_id']) {
				array_unshift($addresses, $address);
			} else {
				$addresses[] = $address;
			}
		}

		try {
			$customer = new ShopgateCustomer();
			$customer->setCustomerId($customerData["customers_id"]);
			$customer->setCustomerNumber($customerData["customers_cid"]);
			$customer->setCustomerGroup($customerData['customers_status_name']);
			$customer->setCustomerGroupId($customerData['customers_status_id']);
			$customer->setGender($customerData["customers_gender"]);
			$customer->setFirstName($customerData["customers_firstname"]);
			$customer->setLastName($customerData["customers_lastname"]);
			$customer->setBirthday($customerData["customers_birthday"]);
			$customer->setPhone($customerData["customers_telephone"]);
			$customer->setMail($customerData["customers_email_address"]);
			$customer->setAddresses($addresses);

			// utf-8 encode the values recursively
			$customer = $customer->utf8Encode($this->config->getEncoding());
		} catch (ShopgateLibraryException $e) {
			// don't abort here
		}

		return $customer;
	}

	/**
	 * @see ShopgatePluginCore::addOrder()
	 */
	public function addOrder(ShopgateOrder $order) {
		// save UTF-8 payment infos (to build proper json)
		$paymentInfosUtf8 = $order->getPaymentInfos();
		
		$this->log('start add_order()', ShopgateLogger::LOGTYPE_DEBUG);
		
		// data needs to be utf-8 decoded for äöüß and the like to be saved correctly
		$order = $order->utf8Decode($this->config->getEncoding());
		if ($order instanceof ShopgateOrder); // for Eclipse auto-completion

		$this->log('db: duplicate_order', ShopgateLogger::LOGTYPE_DEBUG);
		
		// check that the order is not imported already
		$qry = "
			SELECT
			o.*,
			so.shopgate_order_number
			FROM ".TABLE_ORDERS." o
			INNER JOIN ".TABLE_SHOPGATE_ORDERS." so ON (so.orders_id = o.orders_id)
			WHERE so.shopgate_order_number = '{$order->getOrderNumber()}'
		";
		$dbOrder = xtc_db_fetch_array( xtc_db_query( $qry ) );

		if(!empty($dbOrder)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DUPLICATE_ORDER, 'external_order_number: '. $dbOrder["orders_id"], true);
		}

		// retrieve address information
		$delivery = $order->getDeliveryAddress();
		$invoice = $order->getInvoiceAddress();

		$this->log('before ShopgateMapper', ShopgateLogger::LOGTYPE_DEBUG);
		
		// map state codes (called "zone id" in shopsystem)
		$invoiceStateCode  = (!$invoice->getState())  ? null : ShopgateXtcMapper::getXtcStateCode($invoice->getState());
		$deliveryStateCode = (!$delivery->getState()) ? null : ShopgateXtcMapper::getXtcStateCode($delivery->getState());

		// find customer
		$customerId = $order->getExternalCustomerId();

		$shopCustomer = array();
		if (!empty($customerId)) {
			$this->log('db: customer', ShopgateLogger::LOGTYPE_DEBUG);
			$shopCustomer = xtc_db_fetch_array(xtc_db_query("SELECT * FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '{$customerId}'"));
		}
		if (empty($shopCustomer)) {
			$this->log('create Guest User', ShopgateLogger::LOGTYPE_DEBUG);
			$shopCustomer = $this->_createGuestUser($order);
		}

		$phone_number = $order->getMobile();
		if(empty($phone_number)) {
			$phone_number = $order->getPhone();
		}

		$addressFormat = $this->_getAddressFormatId();

		$this->log('db: customer_status', ShopgateLogger::LOGTYPE_DEBUG);
		
		$cStatus = xtc_db_fetch_array(xtc_db_query("SELECT * FROM " . TABLE_CUSTOMERS_STATUS . " WHERE language_id = '{$this->languageId}' AND customers_status_id = '{$shopCustomer["customers_status"]}'"));
		if (empty($cStatus)) throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_NO_CUSTOMER_GROUP_FOUND, print_r($shopCustomer,true));

		$this->log('db: countries', ShopgateLogger::LOGTYPE_DEBUG);
		
		$deliveryCountry = xtc_db_fetch_array(xtc_db_query("SELECT * FROM ".TABLE_COUNTRIES." WHERE countries_iso_code_2 = '{$delivery->getCountry()}'"));
		$invoiceCountry = xtc_db_fetch_array(xtc_db_query("SELECT * FROM ".TABLE_COUNTRIES." WHERE countries_iso_code_2 = '{$invoice->getCountry()}'"));

		///////////////////////////////////////////////////////////////////////
		// Save order
		///////////////////////////////////////////////////////////////////////

		$orderData = array();
		$orderData["customers_id"]					= $shopCustomer["customers_id"];
		$orderData["customers_cid"]					= $shopCustomer["customers_cid"];
		$orderData["customers_vat_id"]				= $shopCustomer["customers_vat_id"];
		$orderData["customers_status"]				= $cStatus["customers_status_id"];
		$orderData["customers_status_name"]			= $cStatus["customers_status_name"];
		$orderData["customers_status_image"]		= $cStatus["customers_status_image"];
		$orderData["customers_status_discount"]		= 0;

		$orderData["customers_name"]				= $invoice->getFirstName()." ".$invoice->getLastName();
		$orderData["customers_firstname"]			= $invoice->getFirstName();
		$orderData["customers_lastname"]			= $invoice->getLastName();
		$orderData["customers_company"]				= $invoice->getCompany();
		$orderData["customers_street_address"]		= $invoice->getStreet1();
		$orderData["customers_suburb"]				= "";
		$orderData["customers_city"]				= $invoice->getCity();
		$orderData["customers_postcode"]			= $invoice->getZipcode();
		$orderData["customers_state"]				= $invoiceStateCode;
		$orderData["customers_country"]				= $invoiceCountry["countries_name"];
		$orderData["customers_telephone"]			= $phone_number;
		$orderData["customers_email_address"]		= $order->getMail();
		$orderData["customers_address_format_id"]	= $addressFormat;

		$orderData["delivery_name"]					= $delivery->getFirstName()." ".$delivery->getLastName();
		$orderData["delivery_firstname"]			= $delivery->getFirstName();
		$orderData["delivery_lastname"]				= $delivery->getLastName();
		$orderData["delivery_company"]				= $delivery->getCompany();
		$orderData["delivery_street_address"]		= $delivery->getStreet1();
		$orderData["delivery_suburb"]				= "";
		$orderData["delivery_city"]					= $delivery->getCity();
		$orderData["delivery_postcode"]				= $delivery->getZipcode();
		$orderData["delivery_state"]				= $deliveryStateCode;
		$orderData["delivery_country"]				= $deliveryCountry["countries_name"];
		$orderData["delivery_country_iso_code_2"]	= $delivery->getCountry();
		$orderData["delivery_address_format_id"]	= $addressFormat;

		$orderData["billing_name"]					= $invoice->getFirstName()." ".$invoice->getLastName();
		$orderData["billing_firstname"]				= $invoice->getFirstName();
		$orderData["billing_lastname"]				= $invoice->getLastName();
		$orderData["billing_company"]				= $invoice->getCompany();
		$orderData["billing_street_address"]		= $invoice->getStreet1();
		$orderData["billing_suburb"]				= "";
		$orderData["billing_city"]					= $invoice->getCity();
		$orderData["billing_postcode"]				= $invoice->getZipcode();
		$orderData["billing_state"]					= $invoiceStateCode;
		$orderData["billing_country"]				= $invoiceCountry["countries_name"];
		$orderData["billing_country_iso_code_2"]	= $invoice->getCountry();
		$orderData["billing_address_format_id"]		= $addressFormat;

		$orderData["shipping_method"]				= "Pauschal"; // TODO
		$orderData["shipping_class"]				= "flat_flat"; // TODO

		$orderData["cc_type"]						= "";
		$orderData["cc_owner"]						= "";
		$orderData["cc_number"]						= "";
		$orderData["cc_expires"]					= "";
		$orderData["cc_start"]						= "";
		$orderData["cc_issue"]						= "";
		$orderData["cc_cvv"]						= "";
		$orderData["comments"]						= "";

		$orderData["last_modified"]					= date( 'Y-m-d H:i:s' );
		$orderData["date_purchased"]				= $order->getCreatedTime( 'Y-m-d H:i:s' );

		$orderData["currency"]						= $this->currency["code"];
		$orderData["currency_value"]				= $this->exchangeRate;

		$orderData["account_type"]					= "";

		$orderData["payment_method"]				= "shopgate";
		$orderData["payment_class"]					= "shopgate";

		$orderData["customers_ip"]					= "";
		$orderData["language"]						= $this->language;

		$orderData["afterbuy_success"]				= 0;
		$orderData["afterbuy_id"]					= 0;

		$orderData["refferers_id"]					= 0;
		$orderData["conversion_type"]				= "2";

		$orderData["orders_status"]					= $this->config->getOrderStatusOpen();

		$orderData["orders_date_finished"] 			= null;

		$this->log('db: save order', ShopgateLogger::LOGTYPE_DEBUG);
		
		// Speichere die Bestellung
		xtc_db_perform(TABLE_ORDERS,$orderData);
		$dbOrderId = xtc_db_insert_id();

		$this->log('db: save', ShopgateLogger::LOGTYPE_DEBUG);
		
		$ordersShopgateOrder = array(
			"orders_id" => $dbOrderId,
			"shopgate_order_number" => $order->getOrderNumber(),
			"is_paid" => $order->getIsPaid(),
			"is_shipping_blocked" => $order->getIsShippingBlocked(),
			"payment_infos" => $this->jsonEncode($paymentInfosUtf8, true),
			"is_sent_to_shopgate" => 0,
			"modified" => "now()",
			"created" => "now()",
		);
		xtc_db_perform(TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder);
		
		$this->log('method: _insertStatusHistory() ', ShopgateLogger::LOGTYPE_DEBUG);
		$this->_insertStatusHistory($order, $dbOrderId, $orderData['orders_status']);
		
		$this->log('method: _setOrderPayment() ', ShopgateLogger::LOGTYPE_DEBUG);
		$this->_setOrderPayment($order, $dbOrderId, $orderData['orders_status']);
		
		$this->log('method: _insertOrderItems() ', ShopgateLogger::LOGTYPE_DEBUG);
		$this->_insertOrderItems($order, $dbOrderId, $orderData['orders_status']);
		
		$this->log('method: _insertOrderTotal() ', ShopgateLogger::LOGTYPE_DEBUG);
		$this->_insertOrderTotal($order, $dbOrderId);
		
		$this->log('db: update order ', ShopgateLogger::LOGTYPE_DEBUG);
		
		// Save status in order
		$orderUpdateData 					= array();
		$orderUpdateData["orders_status"]	= $orderData["orders_status"];
		$orderUpdateData["last_modified"]	= date( 'Y-m-d H:i:s' );
		xtc_db_perform(TABLE_ORDERS,$orderUpdateData, "update", "orders_id = {$dbOrderId}");
		
		$this->log('method: _pushOrderToAfterbuy', ShopgateLogger::LOGTYPE_DEBUG);
		$this->_pushOrderToAfterbuy($dbOrderId, $order);
		$this->log('method: _pushOrderToDreamRobot', ShopgateLogger::LOGTYPE_DEBUG);
		$this->_pushOrderToDreamRobot($dbOrderId, $order);
		
		$this->log('return: end addOrder()', ShopgateLogger::LOGTYPE_DEBUG);
		return array(
			'external_order_id'=>$dbOrderId,
			'external_order_number'=>$dbOrderId
		);
	}

	public function updateOrder(ShopgateOrder $order) {
		// save UTF-8 payment infos (to build proper json)
		$paymentInfosUtf8 = $order->getPaymentInfos();
		
		// data needs to be utf-8 decoded for äöüß and the like to be saved correctly
		$order = $order->utf8Decode($this->config->getEncoding());
		if ($order instanceof ShopgateOrder); // for Eclipse auto-completion
		
		$qry = "
		SELECT
			o.*,
			so.shopgate_order_id,
			so.shopgate_order_number,
			so.is_paid,
			so.is_shipping_blocked,
			so.payment_infos
		FROM ".TABLE_ORDERS." o
		INNER JOIN ".TABLE_SHOPGATE_ORDERS." so ON (so.orders_id = o.orders_id)
		WHERE so.shopgate_order_number = '{$order->getOrderNumber()}'
		";
		$dbOrder = xtc_db_fetch_array( xtc_db_query( $qry ) );

		if($dbOrder == false){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_NOT_FOUND, "Shopgate order number: '{$order->getOrderNumber()}'.");
		}

		$errorOrderStatusIsSent = false;
		$errorOrderStatusAlreadySet = array();
		$statusShoppingsystemOrderIsPaid = $dbOrder['is_paid'];
		$statusShoppingsystemOrderIsShippingBlocked = $dbOrder['is_shipping_blocked'];
		$status = $dbOrder["orders_status"];

		if( $order->getUpdatePayment() == 1 ) {

			if(!is_null($statusShoppingsystemOrderIsPaid) && $order->getIsPaid() == $statusShoppingsystemOrderIsPaid &&
				!is_null($dbOrder['payment_infos']) && $dbOrder['payment_infos'] == $this->jsonEncode($paymentInfosUtf8)){
				$errorOrderStatusAlreadySet[] = 'payment';
			}

			if(!is_null($statusShoppingsystemOrderIsPaid) && $order->getIsPaid() == $statusShoppingsystemOrderIsPaid){
				// do not update is_paid
			} else {
				if ($status == $this->config->getOrderStatusShipped()) {
					$errorOrderStatusIsSent = true;
				} else {
					// do not change status
				}

				// Save order status
				$orderStatus = array();
				$orderStatus["orders_id"]					= $dbOrder["orders_id"];
				$orderStatus["orders_status_id"]			= $status;
				$orderStatus["date_added"]					= date( 'Y-m-d H:i:s');
				$orderStatus["customer_notified"]			= false;
				if($order->getIsPaid()) {
					$orderStatus['comments'] = 'Bestellstatus von Shopgate geändert: Zahlung erhalten';
				} else {
					$orderStatus['comments'] = 'Bestellstatus von Shopgate geändert: Zahlung noch nicht erhalten';
				}
				
				$orderStatus['comments'] = $this->stringFromUtf8($orderStatus['comments'], $this->config->getEncoding());
				
				xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY,$orderStatus);

				// update the shopgate order status information
				$ordersShopgateOrder = array(
					"is_paid" => (int)$order->getIsPaid(),
					"modified" => "now()",
				);
				xtc_db_perform(TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "update", "shopgate_order_id = {$dbOrder['shopgate_order_id']}");

				// update var
				$statusShoppingsystemOrderIsPaid = $order->getIsPaid();

				// Save status in order
				$orderData 					= array();
				$orderData["orders_status"]	= $status;
				$orderData["last_modified"]	= date( 'Y-m-d H:i:s' );
				xtc_db_perform(TABLE_ORDERS,$orderData, "update", "orders_id = {$dbOrder['orders_id']}");

			}

			// update paymentinfos
			if(!is_null($dbOrder['payment_infos']) && $dbOrder['payment_infos'] != $this->jsonEncode($paymentInfosUtf8)){

				$dbPaymentInfos = $this->jsonDecode($dbOrder['payment_infos'], true);
				$paymentInfos = $order->getPaymentInfos();
				$histories = array();

				switch($order->getPaymentMethod()) {
					case ShopgateOrder::SHOPGATE:
					case ShopgateOrder::INVOICE:
					case ShopgateOrder::COD:
						break;
					case ShopgateOrder::PREPAY:

						if(isset($dbPaymentInfos['purpose']) && $paymentInfos['purpose'] != $dbPaymentInfos['purpose']){
							$comments  = $this->stringFromUtf8("Shopgate: Zahlungsinformationen wurden aktualisiert: \n\nDer Kunde wurde angewiesen Ihnen das Geld mit dem Verwendungszweck \"", $this->config->getEncoding());
							$comments .= $paymentInfos["purpose"];
							$comments .= $this->stringFromUtf8("\" auf Ihr Bankkonto zu überweisen", $this->config->getEncoding());
							
							// Order is not paid yet
							$histories[] = 	array(
								"orders_id"=> $dbOrder["orders_id"],
								"orders_status_id"=>$status,
								"date_added"=>date( 'Y-m-d H:i:s'),
								"customer_notified"=>false,
								"comments"=>xtc_db_prepare_input($comments)
							);
						}

						break;
					case ShopgateOrder::DEBIT:
						$qry = "
							SELECT
								*
							FROM banktransfer b
							WHERE b.orders_id = '{$dbOrder['orders_id']}'";
						$dbBanktransfer = xtc_db_fetch_array( xtc_db_query( $qry ) );

						if(!empty($dbBanktransfer)){
							$banktransferData = array();
							$banktransferData["banktransfer_owner"]		= $paymentInfos["bank_account_holder"];
							$banktransferData["banktransfer_number"]	= $paymentInfos["bank_account_number"];
							$banktransferData["banktransfer_bankname"]	= $paymentInfos["bank_name"];
							$banktransferData["banktransfer_blz"]		= $paymentInfos["bank_code"];
							xtc_db_perform("banktransfer", $banktransferData, "update", "orders_id = {$dbOrder['orders_id']}");

							$comments  = $this->stringFromUtf8("Shopgate: Zahlungsinformationen wurden aktualisiert: \n\n", $this->config->getEncoding());
							$comments .= $this->_createPaymentInfos($paymentInfos, $dbOrder['orders_id'], $status, false);
				
							$histories[] = 	array(
								"orders_id"=> $dbOrder["orders_id"],
								"orders_status_id"=>$status,
								"date_added"=>date( 'Y-m-d H:i:s'),
								"customer_notified"=>false,
								"comments"=>xtc_db_prepare_input($comments)
							);
						}

						break;
					case ShopgateOrder::PAYPAL:

						// Save paymentinfos in history
						$history = $this->_createPaymentInfos($paymentInfos, $dbOrder["orders_id"], $status);
						$history['comments'] = $this->stringFromUtf8("Shopgate: Zahlungsinformationen wurden aktualisiert: \n\n", $this->config->getEncoding()).$history['comments'];
						$histories[] = $history;

						break;
					default:
						// mobile_payment

						// Save paymentinfos in history
						$history = $this->_createPaymentInfos($paymentInfos, $dbOrder["orders_id"], $status);
						$history['comments'] = $this->stringFromUtf8("Shopgate: Zahlungsinformationen wurden aktualisiert: \n\n", $this->config->getEncoding()).$history['comments'];
						$histories[] = $history;

						break;
				}

				foreach($histories as $history){
					xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY,$history);
				}
			}

			$ordersShopgateOrder = array(
				"payment_infos" => $this->jsonEncode($paymentInfosUtf8),
				"modified" => "now()",
			);
			xtc_db_perform(TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "update", "shopgate_order_id = {$dbOrder['shopgate_order_id']}");

		}


		if($order->getUpdateShipping() == 1){

			if(!is_null($statusShoppingsystemOrderIsShippingBlocked) && $order->getIsShippingBlocked() == $statusShoppingsystemOrderIsShippingBlocked){
				$errorOrderStatusAlreadySet[] = 'shipping';
			} else {
				if($status == $this->config->getOrderStatusShipped()){
					$errorOrderStatusIsSent = true;
				} else {
					if($order->getIsShippingBlocked() == 1){
						$status = $this->config->getOrderStatusShippingBlocked();
					} else {
						$status = $this->config->getOrderStatusOpen();
					}
				}

				$orderStatus = array();
				$orderStatus["orders_id"]					= $dbOrder["orders_id"];
				$orderStatus["date_added"]					= date( 'Y-m-d H:i:s');
				$orderStatus["customer_notified"]			= false;
				$orderStatus['orders_status_id'] 			= $status;
				if($order->getIsShippingBlocked() == 0){
					$orderStatus["comments"] = "Bestellstatus von Shopgate geändert: Versand ist nicht mehr blockiert!";
				} else {
					$orderStatus['comments'] = 'Bestellstatus von Shopgate geändert: Versand ist blockiert!';
				}
				
				$orderStatus['comments'] = $this->stringFromUtf8($orderStatus['comments'], $this->config->getEncoding());
				
				xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY,$orderStatus);

				$ordersShopgateOrder = array(
					"is_shipping_blocked" => (int)$order->getIsShippingBlocked(),
					"modified" => "now()",
				);
				xtc_db_perform(TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "update", "shopgate_order_id = {$dbOrder['shopgate_order_id']}");

				$statusShoppingsystemOrderIsShippingBlocked = $order->getIsShippingBlocked();

				// Save status in order
				$orderData 					= array();
				$orderData["orders_status"]	= $status;
				$orderData["last_modified"]	= date( 'Y-m-d H:i:s' );
				xtc_db_perform(TABLE_ORDERS,$orderData, "update", "orders_id = {$dbOrder['orders_id']}");

				$this->_pushOrderToAfterbuy($dbOrder["orders_id"], $order);
				$this->_pushOrderToDreamRobot($dbOrder["orders_id"], $order);
			}
		}

		if($errorOrderStatusIsSent){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_STATUS_IS_SENT);
		}

		if(!empty($errorOrderStatusAlreadySet)){
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_ALREADY_UP_TO_DATE, implode(',', $errorOrderStatusAlreadySet), true);
		}

		return array(
			'external_order_id'=>$dbOrder["orders_id"],
			'external_order_number'=>$dbOrder["orders_id"]
		);
	}

	private function _insertStatusHistory(ShopgateOrder $order, $dbOrderId, &$currentOrderStatus) {
		///////////////////////////////////////////////////////////////////////
		// Speicher Kommentare zur Bestellung in der Historie
		///////////////////////////////////////////////////////////////////////

		$comment = "";
		if($order->getIsTest()){
			$comment .= "#### DIES IST EINE TESTBESTELLUNG ####\n";
		}
		$comment .= "Bestellung durch Shopgate hinzugefügt.";
		$comment .= "\nBestellnummer: ". $order->getOrderNumber();

		$paymentTransactionNumber = $order->getPaymentTransactionNumber();
		if(!empty($paymentTransactionNumber)){
			$comment .= "\nPayment-Transaktionsnummer: ". $paymentTransactionNumber."\n";
		}

		if($order->getIsShippingBlocked() == 0){
			$comment .= "\nHinweis: Der Versand der Bestellung ist bei Shopgate nicht blockiert!";
		} else {
			$comment .= "\nHinweis: Der Versand der Bestellung ist bei Shopgate blockiert!";
			$currentOrderStatus = $this->config->getOrderStatusShippingBlocked();
		}

		$comment = $this->stringFromUtf8($comment, $this->config->getEncoding());
		
		$histories = array(
			array(
				"orders_id"=> $dbOrderId,
				"orders_status_id"=>$currentOrderStatus,
				"date_added"=>date('Y-m-d H:i:s'),
				"customer_notified"=>false,
				"comments"=>xtc_db_prepare_input($comment),
			)
		);

		foreach($histories as $history){
			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY,$history);
		}
	}

	private function _setOrderPayment(ShopgateOrder $order, $dbOrderId, &$currentOrderStatus) {
		$payment = $order->getPaymentMethod();
		$paymentGroup = $order->getPaymentGroup();
		$paymentInfos = $order->getPaymentInfos();

		$orderData = array();
		$defaultPayment = 'mobile_payment';

		$histories = array();
		switch($payment) {
			case ShopgateOrder::SHOPGATE:
				$orderData["payment_method"] = "shopgate";
				$orderData["payment_class"] = "shopgate";

				break;
			case ShopgateOrder::PREPAY:
				$orderData["payment_method"] = "eustandardtransfer";
				$orderData["payment_class"] = "eustandardtransfer";

				if(!$order->getIsPaid()){
					$comments  = $this->stringFromUtf8("Der Kunde wurde angewiesen Ihnen das Geld mit dem Verwendungszweck \"", $this->config->getEncoding());
					$comments .= $paymentInfos['purpose'];
					$comments .= $this->stringFromUtf8("\" auf Ihr Bankkonto zu überweisen", $this->config->getEncoding());

					// Order is not paid yet
					$histories[] = 	array(
						"orders_id"=> $dbOrderId,
						"orders_status_id"=>$currentOrderStatus,
						"date_added"=>date( 'Y-m-d H:i:s'),
						"customer_notified"=>false,
						"comments"=>xtc_db_prepare_input($comments)
					);
				}

				break;
			case ShopgateOrder::INVOICE:
				$orderData["payment_method"] = "invoice";
				$orderData["payment_class"] = "invoice";

				break;
			case ShopgateOrder::COD:
				$orderData["payment_method"] = "cod";
				$orderData["payment_class"] = "cod";

				break;
			case ShopgateOrder::DEBIT:
				$orderData["payment_method"] = "banktransfer";
				$orderData["payment_class"] = "banktransfer";

				$banktransferData = array();
				$banktransferData["orders_id"]				= $dbOrderId;
				$banktransferData["banktransfer_owner"]		= $paymentInfos["bank_account_holder"];
				$banktransferData["banktransfer_number"]	= $paymentInfos["bank_account_number"];
				$banktransferData["banktransfer_bankname"]	= $paymentInfos["bank_name"];
				$banktransferData["banktransfer_blz"]		= $paymentInfos["bank_code"];
				$banktransferData["banktransfer_status"]	= "0";
				$banktransferData["banktransfer_prz"]		= $dbOrderId;
				$banktransferData["banktransfer_fax"]		= null;
				xtc_db_perform("banktransfer", $banktransferData);

				$comments  = $this->stringFromUtf8("Sie müssen nun den Geldbetrag per Lastschrift von dem Bankkonto des Kunden abbuchen: \n\n", $this->config->getEncoding());
				$comments .= $this->_createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus, false);
				
				$histories[] = 	array(
					"orders_id"=> $dbOrderId,
					"orders_status_id"=>$currentOrderStatus,
					"date_added"=>date( 'Y-m-d H:i:s'),
					"customer_notified"=>false,
					"comments"=>xtc_db_prepare_input($comments)
				);

				break;
			case ShopgateOrder::PAYPAL:
				$orderData["payment_method"] = "paypal";
				$orderData["payment_class"] = "paypal";

				// Save paymentinfos in history
				$histories[] = $this->_createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus);

				break;
			default:
				$orderData["payment_method"] = "mobile_payment";
				$orderData["payment_class"] = "shopgate";

				// Save paymentinfos in history
				$histories[] = $this->_createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus);

				break;
		}

		foreach($histories as $history){
			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $history);
		}

		xtc_db_perform(TABLE_ORDERS, $orderData, "update", "orders_id = {$dbOrderId}");
	}

	/**
	 * Parse the paymentInfo - array and get as output a array or a string
	 *
	 * @param Array $paymentInfos
	 * @param Integer $dbOrderId
	 * @param Integer $currentOrderStatus
	 *
	 * @return mixed History-Array or String
	 */
	private function _createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus, $asArray = true){
		$paymentInformation = '';
		foreach($paymentInfos as $key => $value){
			$paymentInformation .= $key.': '.$value."\n";
		}

		if($asArray){
			return array(
				"orders_id"=> $dbOrderId,
				"orders_status_id"=>$currentOrderStatus,
				"date_added"=>date('Y-m-d H:i:s'),
				"customer_notified"=>false,
				"comments"=>xtc_db_prepare_input($paymentInformation)
			);
		} else {
			return $paymentInformation;
		}
	}

	private function _insertOrderItems(ShopgateOrder $order, $dbOrderId, &$currentOrderStatus) {
		///////////////////////////////////////////////////////////////////////
		// Speicher die Produkte
		///////////////////////////////////////////////////////////////////////
		$errors = '';
		foreach($order->getItems() as $orderItem) {

			$order_infos = $orderItem->getInternalOrderInfo();
			$order_infos = $this->jsonDecode($order_infos, true);

			$item_number = $orderItem->getItemNumber();
			if(isset($order_infos["base_item_number"])){
				$item_number = $order_infos["base_item_number"];
			}

			$this->log('db: get product ', ShopgateLogger::LOGTYPE_DEBUG);
			
			$qry = xtc_db_query(
			"SELECT * FROM ".TABLE_PRODUCTS . " WHERE"
			. " products_id = '" . $item_number ."'"
			. " LIMIT 1");

			$dbProduct = xtc_db_fetch_array($qry);
			if(empty($dbProduct) && ($item_number == 'COUPON' || $item_number == 'PAYMENT_FEE')){
				$this->log('product is COUPON or PAYMENTFEE', ShopgateLogger::LOGTYPE_DEBUG);
				
				// workaround for shopgate coupons
				$dbProduct = array();
				$dbProduct['products_id'] = 0;
				$dbProduct['products_model'] = $item_number;
			} else if(empty($dbProduct)){
				$this->log('no product found', ShopgateLogger::LOGTYPE_DEBUG);
				
				$this->log(ShopgateLibraryException::buildLogMessageFor(ShopgateLibraryException::PLUGIN_ORDER_ITEM_NOT_FOUND, 'Shopgate-Order-Number: '. $order->getOrderNumber() .', DB-Order-Id: '. $dbOrderId .'; item (item_number: '.$products_model.'). The item will be skipped.'));
				$errors .= "\nItem (item_number: ".$item_number.") can not be found in your shoppingsystem. Please contact Shopgate. The item will be skipped.";

				$dbProduct['products_id'] = 0;
				$dbProduct['products_model'] = $item_number;
			}

			$this->log('db: orders_products', ShopgateLogger::LOGTYPE_DEBUG);
			
			$productData = array(
				"orders_id"				=>	$dbOrderId,
				"products_model"		=>	$dbProduct["products_model"],
				"products_id"			=>	$item_number,
				"products_name"			=>	xtc_db_prepare_input($orderItem->getName()),
				"products_price"		=> 	$orderItem->getUnitAmountWithTax(),
				"products_discount_made"=>	0,
				"final_price"			=> 	$orderItem->getQuantity() * ($orderItem->getUnitAmountWithTax()),
				"products_shipping_time"=> 	"",
				"products_tax"			=> 	$orderItem->getTaxPercent(),
				"products_quantity"		=> 	$orderItem->getQuantity(),
				"allow_tax"				=>	1,
			);

			$qry = xtc_db_perform(TABLE_ORDERS_PRODUCTS, $productData);
			$productsOrderId = xtc_db_insert_id();
			
			$options = $orderItem->getOptions();
			if(!empty($options)) {
				$this->log('process options', ShopgateLogger::LOGTYPE_DEBUG);
				foreach($options as $option) {
					$attribute_model = $option->getValueNumber();
		
					$this->log('db: get attributes', ShopgateLogger::LOGTYPE_DEBUG);
					
					// Hole das Attribut aus der Datenbank
					$qry = "
						SELECT
							po.products_options_name,
							pov.products_options_values_name,
							pa.options_values_price,
							pa.price_prefix
						FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
						INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po ON pa.options_id = po.products_options_id AND po.language_id = $this->languageId
						INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." povtpo ON povtpo.products_options_id = po.products_options_id
						INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov ON (povtpo.products_options_values_id = pov.products_options_values_id AND pa.options_values_id = pov.products_options_values_id AND pov.language_id = $this->languageId)
						WHERE pa.products_id = '".$dbProduct["products_id"]."'
						AND pa.options_values_id = ".$attribute_model."
						LIMIT 1
					";
		
					$qry = xtc_db_query($qry);
					$dbattribute = xtc_db_fetch_array($qry);
					if(empty($dbattribute)) continue; //Fehler
					
					$this->log('db: save order product attributes', ShopgateLogger::LOGTYPE_DEBUG);
					
					$productAttributeData = array(
						"orders_id"=>$dbOrderId,
						"orders_products_id"=>$productsOrderId,
						"products_options"=>$dbattribute['products_options_name'],
						"products_options_values"=>$dbattribute["products_options_values_name"],
						"options_values_price"=>$dbattribute["options_values_price"],
						"price_prefix"=>$dbattribute["price_prefix"],
					);
					xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $productAttributeData);
				}
			} else {
				
				$this->log('attributes?', ShopgateLogger::LOGTYPE_DEBUG);
				
				for($i=1;$i<=10;$i++) {
					if(!isset($order_infos["attribute_$i"])){
						break;
					}
					$attribute_model = $order_infos["attribute_$i"];
					
					$this->log('db: get attribute', ShopgateLogger::LOGTYPE_DEBUG);
					
					// Hole das Attribut aus der Datenbank
					$qry = "
						SELECT
							po.products_options_name,
							pov.products_options_values_name,
							pa.options_values_price,
							pa.price_prefix
						FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
						INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po ON pa.options_id = po.products_options_id AND po.language_id = $this->languageId
						INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." povtpo ON povtpo.products_options_id = po.products_options_id
						INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov ON (povtpo.products_options_values_id = pov.products_options_values_id AND pa.options_values_id = pov.products_options_values_id AND pov.language_id = $this->languageId)
						WHERE pa.products_id = '".$dbProduct["products_id"]."'
						AND pa.products_attributes_id = '".$attribute_model."'
						LIMIT 1
					";
					
					$qry = xtc_db_query($qry);
					$dbattribute = xtc_db_fetch_array($qry);
					if(empty($dbattribute)) continue; //Fehler
					
					$this->log('db: save order product attributes', ShopgateLogger::LOGTYPE_DEBUG);
					
					$productAttributeData = array(
						"orders_id"=>$dbOrderId,
						"orders_products_id"=>$productsOrderId,
						"products_options"=>$dbattribute["products_options_name"],
						"products_options_values"=>$dbattribute["products_options_values_name"],
						"options_values_price"=>$dbattribute["options_values_price"],
						"price_prefix"=>$dbattribute["price_prefix"],
					);
					xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $productAttributeData);
				}
			}
		}
		
		
		$this->log('method: updateItemsStock', ShopgateLogger::LOGTYPE_DEBUG);
		$this->updateItemsStock($order);

		if(!empty($errors)){
			$this->log('db: save errors in history', ShopgateLogger::LOGTYPE_DEBUG);
			$comments  = $this->stringFromUtf8('Es sind Fehler beim Importieren der Bestellung aufgetreten: ', $this->config->getEncoding());
			$comments .= $errors;

			$history = array(
				"orders_id"=> $dbOrderId,
				"orders_status_id"=>$currentOrderStatus,
				"date_added"=>date("Y-m-d H:i:s", time()-5),// "-5" Damit diese Meldung als erstes oben angezeigt wird
				"customer_notified"=>false,
				"comments"=>xtc_db_prepare_input($comments),
			);

			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY,$history);
		}
	}

	private function _insertOrderTotal(ShopgateOrder $order, $dbOrderId) {
		///////////////////////////////////////////////////////////////////////
		// Speicher den Gesamtbetrag
		///////////////////////////////////////////////////////////////////////
		$amountWithTax = $order->getAmountComplete();

		$shippingTaxRate = $this->_getOrderShippingTaxRate($order);
		$taxes = $this->_getOrderTaxes($order, $dbOrderId, $shippingTaxRate);
		$xtPrice = new xtcPrice($this->currency["code"], 1);
		$shippingCosts = $order->getAmountShipping();

		$sort = 10;

		$ordersTotal = array();
		$ordersTotal["orders_id"]		= $dbOrderId;
		$ordersTotal["title"]			= xtc_db_prepare_input("Zwischensumme:");
		$ordersTotal["text"]			= $xtPrice->xtcFormat($order->getAmountItems(), true);
		$ordersTotal["value"]			= $order->getAmountItems();
		$ordersTotal["class"]			= "ot_subtotal";
		$ordersTotal["sort_order"]		= $sort++;
		xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);

		$ordersTotal = array();
		$ordersTotal["orders_id"]		= $dbOrderId;
		$ordersTotal["title"]			= xtc_db_prepare_input("Versand:");
		$ordersTotal["text"]			= $xtPrice->xtcFormat($shippingCosts, true);
		$ordersTotal["value"]			= $shippingCosts;
		$ordersTotal["class"]			= "ot_shipping";
		$ordersTotal["sort_order"]		= $sort++;
		xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);

		// insert payment costs.
		//
		//WARNING: On modify: Change the taxes calculation too!
		if($order->getAmountShopPayment() != 0){
			$this->log('db: save payment fee', ShopgateLogger::LOGTYPE_DEBUG);
			
			$paymentInfos = $order->getPaymentInfos();
			
			$ordersTotal = array();
			$ordersTotal["orders_id"]		= $dbOrderId;
			$ordersTotal["title"]			= xtc_db_prepare_input('Zahlungsartkosten'. (!empty($paymentInfos['shopgate_payment_name']) ? ' ('.$paymentInfos['shopgate_payment_name'].'):' : ''));
			$ordersTotal["text"]			= $xtPrice->xtcFormat($order->getAmountShopPayment(), true);
			$ordersTotal["value"]			= $order->getAmountShopPayment();
			$ordersTotal["class"]			= "ot_shipping";
			$ordersTotal["sort_order"]		= $sort++;
			xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);
		
		}
		
		foreach($taxes as $percent => $tax_value) {
			$ordersTotal = array();
			$ordersTotal["orders_id"]		= $dbOrderId;
			$ordersTotal["title"]			= "inkl. UST {$percent} %";
			$ordersTotal["text"]			= $xtPrice->xtcFormat($tax_value, true);
			$ordersTotal["value"]			= $tax_value;
			$ordersTotal["class"]			= "ot_tax";
			$ordersTotal["sort_order"]		= $sort++;
			xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);
		}

		$ordersTotal = array();
		$ordersTotal["orders_id"]		= $dbOrderId;
		$ordersTotal["title"]			= "<b>Summe:</b>";
		$ordersTotal["text"]			= "<b>".$xtPrice->xtcFormat($amountWithTax, true)."</b>";
		$ordersTotal["value"]			= $amountWithTax;
		$ordersTotal["class"]			= "ot_total";
		$ordersTotal["sort_order"]		= $sort++;
		xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);

	}

	private function _getOrderTaxes(ShopgateOrder $order, $dbOrderId, $shippingTaxRate = 0) {
		$taxes = array();

		foreach($order->getItems() as $orderItem) {
			$tax		= $orderItem->getTaxPercent();
			$tax		= intval($tax*100)/100;
			$tax_value	= $orderItem->getUnitAmountWithTax() - $orderItem->getUnitAmount();

			if(!isset($taxes[$tax])) $taxes[$tax]= 0;
			$taxes[$tax] += $tax_value * $orderItem->getQuantity();
		}

		if(!empty($shippingTaxRate)) {
			$shippingTaxRate = intval($shippingTaxRate*100)/100;
			if(!isset($taxes[$shippingTaxRate])) $taxes[$shippingTaxRate]= 0;
			$taxes[$shippingTaxRate] += $order->getAmountShipping()-$this->_getOrderShippingAmountWithoutTax($order, $shippingTaxRate);
		}
		
		// set taxes for payment method
		if($order->getAmountShopPayment() != 0){
			$tax		= $order->getPaymentTaxPercent();
			$tax		= intval($tax*100)/100;
			$tax_value	= $order->getAmountShopPayment() - round(($order->getAmountShopPayment()*100)/($order->getPaymentTaxPercent()+100),2);

			if(!isset($taxes[$tax])) $taxes[$tax]= 0;
			$taxes[$tax] += $tax_value;
		}

		return $taxes;
	}

	private function _getOrderShippingTaxRate(ShopgateOrder $order) {
		$shippingTaxRate = 0;
	
		// Check if a shipping method is set in config
		$shippingMethod = $this->config->getShipping();
		$orderCountryCode2 = $order->getInvoiceAddress()->getCountry();
		
		if(!empty($shippingMethod)) {
			// Get tax value from shipping module
			$qry =
			"SELECT `c`.`configuration_value`, `tr`.`tax_rate` " .
			"FROM `" . TABLE_CONFIGURATION . "` AS `c` " .
			"INNER JOIN `" . TABLE_TAX_RATES . "` AS `tr` ON(`c`.`configuration_value`=`tr`.`tax_class_id`) " .
			"INNER JOIN `" . TABLE_ZONES_TO_GEO_ZONES . "` AS `geozones` ON(`tr`.`tax_zone_id`=`geozones`.`geo_zone_id`) " .
			"INNER JOIN `" . TABLE_COUNTRIES . "` AS `co` ON(`geozones`.`zone_country_id`=`co`.`countries_id`) " .
			"WHERE " .
			"`c`.`configuration_key` = 'MODULE_SHIPPING_".strtoupper($shippingMethod)."_TAX_CLASS' " .
			"AND " .
			"`co`.`countries_iso_code_2`='$orderCountryCode2';";
			$result = xtc_db_query($qry);
			$moduleTaxSetting = xtc_db_fetch_array($result);
			if(!empty($moduleTaxSetting) && !empty($moduleTaxSetting['configuration_value']) && !empty($moduleTaxSetting['tax_rate'])) {
				// get the tax rate for the shipping costs
				$shippingTaxRate = intval($moduleTaxSetting['tax_rate']*100)/100;
			}
		}
	
		return $shippingTaxRate;
	}
	
	private function _getOrderShippingAmountWithoutTax(ShopgateOrder $order, $shippingTaxRate = 0) {
		$shippingAmountWithoutTax = $order->getAmountShipping();
		
		// Check if a shipping method is set in config
		$shippingMethod = $this->config->getShipping();
		if(!empty($shippingTaxRate)) {
			// remove tax from shipping costs
			$shippingAmountWithoutTax /= 1+$shippingTaxRate/100;
		}
		
		return $shippingAmountWithoutTax;
	}
	
	private function updateItemsStock(ShopgateOrder $order) {
		if(STOCK_LIMITED != "true") return; /* STOCK_LIMITED ist vom System gegeben */

		foreach($order->getItems() as $item) {
			if($item->getItemNumber() == 'COUPON' || $item->getItemNumber() == 'PAYMENT_FEE'){
				continue;
			}

			$options = $this->jsonDecode($item->getInternalOrderInfo(), true);

			$productId = $item->getItemNumber();

			if(!empty($options) && isset($options["base_item_number"])) {
				$productId = $options["base_item_number"];
			}

			$stock = $this->xtc_get_products_stock($productId);
			$stock -= $item->getQuantity();

			$data = array(
				"products_quantity" => $stock,
			);

			if($stock <= 0 && STOCK_ALLOW_CHECKOUT == "false") {
				$data["products_status"] = 0;
			}
			xtc_db_perform(TABLE_PRODUCTS, $data, "update", "products_id = '$productId'");
			
			// Special price überprüfen
			$qry = "
			SELECT
				sp.specials_quantity,
				sp.specials_new_products_price,
				sp.status
			FROM ".TABLE_SPECIALS." sp
			WHERE sp.products_id = '".$productId."' AND sp.status = 1 AND (sp.expires_date > now() OR sp.expires_date = '0000-00-00 00:00:00' OR sp.expires_date IS NULL)
			LIMIT 1
			";
			$qry = xtc_db_query($qry);
			$productSpecials = xtc_db_fetch_array($qry);
			
			$orderInfo = $this->jsonDecode($item->getInternalOrderInfo(), true);
			if(!empty($productSpecials) && !empty($orderInfo['is_special_price'])){
				
				$stock = $productSpecials["specials_quantity"];
				$stock -= $item->getQuantity();
				
				$data = array(
					"specials_quantity" => $stock,
				);
				
				if($stock <= 0 && STOCK_ALLOW_CHECKOUT == "false"){
					$data["status"] = 0;
				}

				xtc_db_perform(TABLE_SPECIALS, $data, "update", "products_id = '$productId'");
			}
		}
	}

	protected function createReviewsCsv() {
		$sql = "
		SELECT
			r.reviews_id,
			r.products_id,
			r.customers_name,
			r.reviews_rating,
			r.date_added,
			rd.reviews_text
		FROM
		" . TABLE_REVIEWS . " as r
		INNER JOIN
		" . TABLE_REVIEWS_DESCRIPTION . " as rd ON r.reviews_id = rd.reviews_id
		WHERE rd.languages_id = '".$this->languageId."'
		ORDER BY r.products_id ASC";

		$limit 	= 10;
		$page  	= 1;
		$offset = ($page-1)*$limit;
		$pg 	= " LIMIT $offset,$limit";

		while($query = xtc_db_query($sql.$pg)) {
			$count = xtc_db_num_rows($query);
			if($count == 0) {
				break;
			}

			$reviews = array();
			while($entry = xtc_db_fetch_array($query)) {
				$review = $this->buildDefaultReviewRow();

				$review['item_number'] 		= $entry['products_id'] ;
				$review['update_review_id'] = $entry['reviews_id'];
				$review['score'] 			= $entry['reviews_rating']*2;
				$review['name'] 			= $entry['customers_name'];
				$review['date'] 			= $entry['date_added'];
				$review['title'] 			= '';
				$review['text'] 			= $entry['reviews_text'];

				$reviews[] = $review;
			}

			foreach($reviews as $review) {
				$this->addReviewRow($review);
			}

			$page++;
			$offset = ($page-1)*$limit;
			$pg		= " LIMIT $offset,$limit";
		}
	}

	protected function createPagesCsv() {
	}

	private function _getAddressFormatId() {
		$qry = "
			SELECT c.address_format_id
			FROM ".TABLE_COUNTRIES." c
			WHERE UPPER(c.countries_iso_code_2) = 'DE'
		";

		$item = xtc_db_fetch_array(xtc_db_query($qry));
		return $item["address_format_id"];
	}

	private function _createGuestUser(ShopgateOrder $order) {
		//		$order = new ShopgateOrder();
		$address = $order->getInvoiceAddress();

		$customerStatus = $this->config->getCustomersStatusId();
		if($customerStatus === -1) $customerStatus = DEFAULT_CUSTOMERS_STATUS_ID;

		$customer = array();
		$customer["customers_vat_id_status"] = 0;
		$customer["customers_status"] = $customerStatus;
		$customer["customers_gender"] =  $address->getGender();
		$customer["customers_firstname"] = $address->getFirstName();
		$customer["customers_lastname"] = $address->getLastName();
		$customer["customers_email_address"] = $order->getMail();
		$customer["customers_default_address_id"] = "";
		$customer["customers_telephone"] = $order->getPhone();
		$customer["customers_fax"] = "";
		$customer["customers_newsletter"] = 0;
		$customer["customers_newsletter_mode"] = 0;
		$customer["member_flag"] = 0;
		$customer["delete_user"] = 1;
		$customer["account_type"] = 0;
		$customer["refferers_id"] = 0;
		$customer["customers_date_added"] = date( 'Y-m-d H:i:s' );
		$customer["customers_last_modified"] = date( 'Y-m-d H:i:s' );

		xtc_db_perform(TABLE_CUSTOMERS, $customer);
		$customerId = xtc_db_insert_id();

		$qry = "SELECT countries_id FROM ".TABLE_COUNTRIES." WHERE UPPER(countries_iso_code_2) = UPPER('".$address->getCountry() ."')";
		$qry = xtc_db_query($qry);
		$country = xtc_db_fetch_array($qry);
		$country = $country["countries_id"];

		$_address = array(
			"customers_id" => $customerId,
			"entry_gender" => $address->getGender(),
			"entry_company" => $address->getCompany(),
			"entry_firstname" => $address->getFirstName(),
			"entry_lastname" => $address->getLastName(),
			"entry_street_address" => $address->getStreet1(),
			"entry_suburb" => "",
			"entry_postcode" => $address->getZipcode(),
			"entry_city" => $address->getCity(),
			"entry_state" => "",
			"entry_country_id" => 81,
			"entry_zone_id" => null,
			"address_date_added" => date( 'Y-m-d H:i:s' ),
			"address_last_modified" => date( 'Y-m-d H:i:s' ),
		);
		xtc_db_perform(TABLE_ADDRESS_BOOK, $_address);
		$addressId = xtc_db_insert_id();

		$customer = array(
			"customers_default_address_id" =>$addressId
		);
		xtc_db_perform(TABLE_CUSTOMERS, $customer, "update", "customers_id = $customerId");

		$_info = array (
			"customers_info_id" => $customerId,
			"customers_info_date_of_last_logon" => date( 'Y-m-d H:i:s' ),
			"customers_info_number_of_logons" => '1',
			"customers_info_date_account_created" => date( 'Y-m-d H:i:s' ),
			"customers_info_date_account_last_modified" => date( 'Y-m-d H:i:s' ),
			"global_product_notifications" => 0
		);
		xtc_db_perform(TABLE_CUSTOMERS_INFO, $_info);

		$customerMemo = array();
		$customerMemo["customers_id"] = $customerId;
		$customerMemo["memo_date"] = date( 'Y-m-d' );
		$customerMemo["memo_title"] = "Shopgate - Account angelegt";
		$customerMemo["memo_text"] = "Account wurde von Shopgate angelegt";
		$customerMemo["poster_id"] = null;
		xtc_db_perform("customers_memo", $customerMemo);

		$customer = xtc_db_fetch_array(xtc_db_query("SELECT * FROM ".TABLE_CUSTOMERS." WHERE customers_id = " . $customerId));
		return $customer;
	}
	
	private function _pushOrderToAfterbuy($iOrderId, ShopgateOrder $order) {
		if (!$order->getIsShippingBlocked() && defined('AFTERBUY_ACTIVATED') && AFTERBUY_ACTIVATED == 'true') {
			$this->log("START TO SEND ORDER TO AFTERBUY", ShopgateLogger::LOGTYPE_ACCESS);
	
			require_once (DIR_WS_CLASSES.'afterbuy.php');
			$aBUY = new xtc_afterbuy_functions( $iOrderId );
			if ($aBUY->order_send()) {
				$aBUY->process_order();
				$this->log("SUCCESSFUL ORDER SEND TO AFTERBUY", ShopgateLogger::LOGTYPE_ACCESS);
			} else {
				$this->log("ORDER ALREADY SEND TO AFTERBUY", ShopgateLogger::LOGTYPE_ACCESS);
			}
	
			$this->log("FINISH SEND ORDER TO AFTERBUY", ShopgateLogger::LOGTYPE_ACCESS);
		}
	}
	
	private function _pushOrderToDreamRobot($dbOrderId, ShopgateOrder $order) {
		if (!$order->getIsShippingBlocked() && file_exists(DIR_FS_CATALOG.'dreamrobot_checkout.inc.php')) {
			require_once(DIR_FS_CATALOG.'includes/classes/order.php');
			$this->log("START TO SEND ORDER TO DREAMROBOT", ShopgateLogger::LOGTYPE_ACCESS);
	
			$order = new order($dbOrderId);
			$_SESSION['tmp_oID'] = $dbOrderId;
			include_once ('./dreamrobot_checkout.inc.php');
			
			$this->log("FINISH SEND ORDER TO DREAMROBOT", ShopgateLogger::LOGTYPE_ACCESS);
		}
	}
	
	public function cron($jobname, $params, &$message, &$errorcount) {
		switch ($jobname) {
			case 'set_shipping_completed': $this->cronSetOrdersShippingCompleted($message, $errorcount); break;
			default: throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_CRON_UNSUPPORTED_JOB, 'Job name: "'.$jobname.'"', true);
		}
	}
	
	/**
	 * Marks shipped orders as "shipped" at Shopgate.
	 *
	 * This will find all orders that are marked "shipped" in the shop system but not at Shopgate yet and marks them "shipped" at Shopgate via
	 * Shopgate Merchant API.
	 *
	 * @param string $message Process log will be appended to this reference.
	 * @param int $errorcount This reference gets incremented on errors.
	 */
	protected function cronSetOrdersShippingCompleted(&$message, &$errorcount) {
		$query =
			"SELECT `sgo`.`orders_id`, `sgo`.`shopgate_order_number` ".
			"FROM `".TABLE_SHOPGATE_ORDERS."` sgo ".
			"INNER JOIN `".TABLE_ORDERS."` xto ON (`xto`.`orders_id` = `sgo`.`orders_id`) ".
			"INNER JOIN `".TABLE_LANGUAGES."` xtl ON (`xtl`.`directory` = `xto`.`language`) ".
			"WHERE `sgo`.`is_sent_to_shopgate` = 0 ".
				"AND `xto`.`orders_status` = ".xtc_db_input($this->config->getOrderStatusShipped())." ".
				"AND `xtl`.`code` = '".xtc_db_input($this->config->getLanguage())."';";
		$result = xtc_db_query($query);
		
		if (empty($result)) {
			return;
		}
		
		while ($shopgateOrder = xtc_db_fetch_array($result)) {
			if (!$this->setOrderShippingCompleted($shopgateOrder['shopgate_order_number'], $shopgateOrder['orders_id'], $this->merchantApi)) {
				$errorcount++;
				$message .= 'Shopgate order number "'.$shopgateOrder['shopgate_order_number'].'": error'."\n";
			}
		}
	}
	
	/**
	 * Set the shipping status for a list of order IDs.
	 *
	 * @param int[] $orderIds The IDs of the orders in the shop system.
	 * @param int $status The ID of the order status that has been set in the shopping system.
	 */
	public function updateOrdersStatus($orderIds, $status) {
		$query = xtc_db_input(
			"SELECT `sgo`.`orders_id`, `sgo`.`shopgate_order_number`, `xtl`.`code` ".
			"FROM `".TABLE_SHOPGATE_ORDERS."` sgo ".
			"INNER JOIN `".TABLE_ORDERS."` xto ON (`xto`.`orders_id` = `sgo`.`orders_id`) ".
			"INNER JOIN `".TABLE_LANGUAGES."` xtl ON (`xtl`.`directory` = `xto`.`language`) ".
			"WHERE `sgo`.`orders_id` IN (".xtc_db_input(implode(", ", $orderIds)).")");
		$result = xtc_db_query($query);
		
		if (empty($result)) {
			return;
		}

		$configurations = array();
		$merchantApis = array();
		while ($shopgateOrder = xtc_db_fetch_array($result)) {
			$language = $shopgateOrder['code'];
			
			if (empty($merchantApis[$language])) {
				try {
					$config = new ShopgateConfigModified();
					$config->loadByLanguage($language);
					$builder = new ShopgateBuilder($config);
					$merchantApis[$language] = &$builder->buildMerchantApi();
					$configurations[$language] = $config;
				} catch (ShopgateLibraryException $e) {
					// do not abort. the error will be logged
				}
			}
			
			if ($status != $configurations[$language]->getOrderStatusShipped()) {
				return;
			}
			
			$this->setOrderShippingCompleted($shopgateOrder['shopgate_order_number'], $shopgateOrder['orders_id'], $merchantApis[$language]);
		}
	}
	
	/**
	 * Sets the order status of a Shopgate order to "shipped" via Shopgate Merchant API
	 *
	 * @param string $shopgateOrderNumber The number of the order at Shopgate.
	 * @param int $orderId The ID of the order in the shop system.
	 * @param ShopgateMerchantApi The SMA object to use for the request.
	 * @return bool true on success, false on failure.
	 */
	protected function setOrderShippingCompleted($shopgateOrderNumber, $orderId, ShopgateMerchantApi &$merchantApi) {
		$success = false;
		
		// These are expected and should not be added to error count:
		$ignoreCodes = array(ShopgateMerchantApiException::ORDER_ALREADY_COMPLETED, ShopgateMerchantApiException::ORDER_SHIPPING_STATUS_ALREADY_COMPLETED);
		
		try {
			$merchantApi->setOrderShippingCompleted($shopgateOrderNumber);
			
			$statusArr = array(
					"orders_id" => $orderId,
					"orders_status_id" => $this->config->getOrderStatusShipped(),
					"date_added" => date( 'Y-m-d H:i:s' ),
					"customer_notified" => 1,
					"comments" => "[Shopgate] Bestellung wurde bei Shopgate als versendet markiert",
			);
			
			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $statusArr);
			
			$success = true;
		} catch (ShopgateLibraryException $e) {
			$response = $this->stringFromUtf8($e->getAdditionalInformation(), $this->config->getEncoding());
			
			$statusArr = array(
					"orders_id" => $orderId,
					"orders_status_id" => $this->config->getOrderStatusShipped(),
					"date_added" => date( 'Y-m-d H:i:s' ),
					"customer_notified" => 0,
					"comments" => "[Shopgate] Ein Fehler ist im Shopgate Modul aufgetreten ({$e->getCode()}): {$response}",
			);
			
			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $statusArr);
		} catch (ShopgateMerchantApiException $e) {
			$response = $this->stringFromUtf8($e->getMessage(), $this->config->getEncoding());
			
			$statusArr = array(
					"orders_id" => $orderId,
					"orders_status_id" => $this->config->getOrderStatusShipped(),
					"date_added" => date( 'Y-m-d H:i:s' ),
					"customer_notified" => 0,
					"comments" => "[Shopgate] Ein Fehler ist bei Shopgate aufgetreten ({$e->getCode()}): {$response}",
			);
			
			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $statusArr);
			
			$success = (in_array($e->getCode(), $ignoreCodes)) ? true : false;
		} catch (Exception $e) {
			$response = $this->stringFromUtf8($e->getMessage(), $this->config->getEncoding());
			
			$statusArr = array(
					"orders_id" => $orderId,
					"orders_status_id" => $this->config->getOrderStatusShipped(),
					"date_added" => date( 'Y-m-d H:i:s' ),
					"customer_notified" => 0,
					"comments" => "[Shopgate] Ein unbekannter Fehler ist aufgetreten ({$e->getCode()}): {$response}",
			);
			
			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $statusArr);
		}
		
		// Update shopgate order on success
		if($success) {
			$qry = 'UPDATE `'.TABLE_SHOPGATE_ORDERS.'` SET `is_sent_to_shopgate` = 1 WHERE `shopgate_order_number` = '.$shopgateOrderNumber.';';
			xtc_db_query($qry);
		}
		
		return $success;
	}
	
	private function xtc_get_products_stock($products_id) {
		$products_id = xtc_get_prid($products_id);
		$stock_query = xtc_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . $products_id . "'");
		$stock_values = xtc_db_fetch_array($stock_query);
		
		return $stock_values['products_quantity'];
	}
}

class ShopgateXtcMapper {

	/**
	 * The countries with non-ISO-3166-2 state codes in xt:Commerce 3 are mapped here.
	 * @var string[][]
	 */
	protected static $stateCodesByCountryCode = array(
		'DE' => array(
			"BW" => "BAW",
			"BY" => "BAY",
			"BE" => "BER",
			"BB" => "BRG",
			"HB" => "BRE",
			"HH" => "HAM",
			"HE" => "HES",
			"MV" => "MEC",
			"NI" => "NDS",
			"NW" => "NRW",
			"RP" => "RHE",
			"SL" => "SAR",
			"SN" => "SAS",
			"ST" => "SAC",
			"SH" => "SCN",
			"TH" => "THE",
		),
		"AT" => array(
			"1" => "BL",
			"2" => "KN",
			"3" => "NO",
			"4" => "OO",
			"5" => "SB",
			"6" => "ST",
			"7" => "TI",
			"8" => "VB",
			"9" => "WI",
		),
		//"CH" => ist in xt:commerce bereits korrekt
		//"US" => ist in xt:commerce bereits korrekt
	);

	/**
	 * Finds the corresponding Shopgate state code for a given xt:Commerce 3 state code (zone_code).
	 *
	 * @param string $countryCode The code of the country to which the state belongs
	 * @param string $xtcStateCode The code of the state / zone as found in the default "zones" table of xt:Commerce 3
	 * @return string The state code as defined at Shopgate Wiki
	 *
	 * @throws ShopgateLibraryException if one of the given codes is unknown
	 */
	public static function getShopgateStateCode($countryCode, $xtcStateCode) {
		$countryCode = strtoupper($countryCode);
		$xtcStateCode = strtoupper($xtcStateCode);

		if (!isset(self::$stateCodesByCountryCode[$countryCode])) {
			return $countryCode.'-'.$xtcStateCode;
		}

		$codes = array_flip(self::$stateCodesByCountryCode[$countryCode]);
		if (!isset($codes[$xtcStateCode])) {
			return $countryCode.'-'.$xtcStateCode;
		}

		$stateCode = $codes[$xtcStateCode];
		return $countryCode.'-'.$stateCode;
	}

	/**
	 * Finds the corresponding xt:Commerce 3 state code (zone_code) for a given Shopgate state code
	 *
	 * @param string $shopgateStateCode The Shopgate state code as defined at Shopgate Wiki
	 * @return string The zone code for xt:Commerce 3
	 *
	 * @throws ShopgateLibraryException if the given code is unknown
	 */
	public static function getXtcStateCode($shopgateStateCode) {
		$splitCodes = null;
		preg_match('/^([A-Z]{2})\-([A-Z]{2})$/', $shopgateStateCode, $splitCodes);

		if (empty($splitCodes) || empty($splitCodes[1]) || empty($splitCodes[2])){
			return null;
		}

		if(!isset(self::$stateCodesByCountryCode[$splitCodes[1]]) || !isset(self::$stateCodesByCountryCode[$splitCodes[1]][$splitCodes[2]])) {
			//throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_UNKNOWN_STATE_CODE, 'Code: '.$shopgateStateCode);
			return $splitCodes[2];
		} else {
			return self::$stateCodesByCountryCode[$splitCodes[1]][$splitCodes[2]];
		}
	}
}