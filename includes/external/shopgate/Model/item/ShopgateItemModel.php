<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

class ShopgateItemModel extends Shopgate_Model_Catalog_Product{
	
	/**
	 * @var ShopgateLogger $log
	 */
	private $log;
	
	/**
	 * @var int
	 */
	private $languageId;
	
	/**
	 * @var int
	 */
	private $defaultCustomerPriceGroup;
	
	/**
	 * @var null|int
	 */
	private $exportOffset = null;
	/**
	 * @var null|int
	 */
	private $exportLimit = null;
	
	/**
	 * @var Shopgate_Helper_String $stringHelper
	 */
	private $stringHelper;
	
	const SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_OTHER = 0;
	const SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_TEXT_FIELD = 1;
	
	/**
	 * @param mixed $log
	 */
	public function setLog($log)
	{
		$this->log = $log;
	}
	
	/**
	 * @param Shopgate_Helper_String $stringHelper
	 */
	public function setStringHelper($stringHelper)
	{
		$this->stringHelper = $stringHelper;
	}
	
	/**
	 * @param mixed $languageId
	 */
	public function setLanguageId($languageId)
	{
		$this->languageId = $languageId;
	}
	
	/**
	 * @param mixed $defaultCustomerPriceGroup
	 */
	public function setDefaultCustomerPriceGroup($defaultCustomerPriceGroup)
	{
		$this->defaultCustomerPriceGroup = $defaultCustomerPriceGroup;
	}
	
	/**
	 * @param null $exportOffset
	 */
	public function setExportOffset($exportOffset)
	{
		$this->exportOffset = $exportOffset;
	}
	
	/**
	 * @param null $exportLimit
	 */
	public function setExportLimit($exportLimit)
	{
		$this->exportLimit = $exportLimit;
	}
	
	/**
	 * @return string
	 */
	public function getProductQuery()
	{
		$this->log("generate SQL get products ...", ShopgateLogger::LOGTYPE_DEBUG);
		
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
				p.products_discount_allowed,
				p.products_date_available
			FROM ".TABLE_PRODUCTS." p
			LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pdsc ON (p.products_id = pdsc.products_id AND pdsc.language_id = '".$this->languageId."')
			LEFT JOIN ".TABLE_SHIPPING_STATUS." shst ON (p.products_shippingtime = shst.shipping_status_id AND shst.language_id = '".$this->languageId."')
			LEFT JOIN ".TABLE_MANUFACTURERS." mf ON (mf.manufacturers_id = p.manufacturers_id)
			LEFT JOIN ".TABLE_SPECIALS." sp ON (sp.products_id = p.products_id AND sp.status = 1 AND (sp.expires_date > now() OR sp.expires_date = '0000-00-00 00:00:00' OR sp.expires_date IS NULL))
			LEFT JOIN ".TABLE_PRODUCTS_VPE." vpe ON (vpe.products_vpe_id = p.products_vpe AND vpe.language_id = pdsc.language_id)
			WHERE p.products_status = 1
			";
		
		// Code for enabling to download specific products (for debugging purposes only, at this time)
		if(!empty($_REQUEST['item_numbers']) && is_array($_REQUEST['item_numbers'])) {
			$qry .= " AND p.products_id IN ('".implode("', '", $_REQUEST['item_numbers'])."') ";
		}
		
		// Ahorn24 fix. 10 products were not found without sorting.
		$qry .= ' ORDER BY p.products_id ASC ';
		
		if(!is_null($this->exportLimit) && !is_null($this->exportOffset)){
			$qry .= " LIMIT {$this->exportOffset}, {$this->exportLimit}";
		}
		
		return $qry;
	}
	
	/**
	 * @return mixed
	 */
	public function getMaxProductUid()
	{
		$this->log("execute SQL get max_id ...", ShopgateLogger::LOGTYPE_DEBUG);
		$result = xtc_db_query("SELECT MAX(products_id) max_id FROM ".TABLE_PRODUCTS);
		$maxId = xtc_db_fetch_array( $result );
		return $maxId["max_id"];
	}
	
	/**
	 * @param $maxOrder
	 * @param $minOrder
	 * @param $addToOrderIndex
	 */
	public function getProductOrderValues(&$maxOrder, &$minOrder, &$addToOrderIndex)
	{
		$this->log("execute SQL min_order, max_order ...", ShopgateLogger::LOGTYPE_DEBUG);
		// order_index for the products
		$result = xtc_db_query("SELECT MIN(products_sort) AS 'min_order', MAX(products_sort) AS 'max_order' FROM ".TABLE_PRODUCTS);
		$orderIndices = xtc_db_fetch_array( $result );
		$maxOrder = $orderIndices["max_order"]+1;
		$minOrder = $orderIndices["min_order"];
		$addToOrderIndex = 0;
		
		if($minOrder < 0) {
			// make the sort_order positive
			$addToOrderIndex += abs($minOrder);
		}
	}

    /**
     * @param $productId
     * @return array|bool|mixed
     */
    public function getProductById($productId)
    {
        $productQuery    = "select p.products_tax_class_id,
                                      	p.products_id,
                                      	pd.products_name,
                                      	p.products_price,
                                      	sp.specials_quantity,
                                      	sp.specials_new_products_price,
                                      	sp.expires_date
										from products p
											LEFT JOIN products_description pd ON p.products_id = pd.products_id AND pd.language_id = {$this->languageId}
                                            LEFT JOIN specials sp ON  (sp.products_id = p.products_id AND sp.status = 1 AND (sp.expires_date > now() OR sp.expires_date = '0000-00-00 00:00:00' OR sp.expires_date IS NULL))
										where p.products_id = {$productId}";
        $dbProductResult = xtc_db_query($productQuery);
        $dbProduct       = xtc_db_fetch_array($dbProductResult);
        return $dbProduct;
    }


    /**
     * @param ShopgateOrderItem $sgOrderItem
     * @return string
     */
    public function getProductIdFromOrderItem(ShopgateOrderItem $sgOrderItem) {
        $parentId = $sgOrderItem->getParentItemNumber();
        if (empty($parentId)) {
            $id = $sgOrderItem->getItemNumber();
            if (strpos($id, "_") !== false) {
                $productIdArr = explode('_', $id);
                return $productIdArr[0];
            }
            return $id;
        }
        return $parentId;
    }

    /**
     * @param ShopgateOrderItem $item
     * @return array
     */
    public function getAttributesToProduct(ShopgateOrderItem $item)
    {
        $attributes   = $item->getAttributes();
        $dbAttributes = array();
        foreach ($attributes as $attribute) {

            $query  = "SELECT
					po.products_options_name AS `name`,
					pov.products_options_values_name AS `value`
					FROM products AS p
					LEFT JOIN products_attributes AS pa ON p.products_id = pa.products_id
					LEFT JOIN products_options AS po ON (pa.options_id = po.products_options_id AND po.language_id = 1)
					LEFT JOIN products_options_values AS pov ON (pa.options_values_id = pov.products_options_values_id AND po.language_id = pov.language_id)
					WHERE pa.products_id = {$this->getProductIdFromOrderItem(
				$item
			)} AND po.products_options_name = '{$attribute->getName(
			)}' AND pov.products_options_values_name = '{$attribute->getValue(
			)}'";
            $result = ShopgateWrapper::db_query($query);

            while ($dbProductAttributes =
                ShopgateWrapper::db_fetch_array($result)) {
                $sgAttribute = new ShopgateOrderItemAttribute();
                $sgAttribute->setName($dbProductAttributes["option_name"]);
                $sgAttribute->setValue($dbProductAttributes["value"]);
                $dbAttributes[] = $sgAttribute;
            }
        }
        return $dbAttributes;
    }

    /**
     * @param $productId
     * @param $attributeIds
     * @param $taxRate
     * @return array
     */
    public function getOptionsToProduct($productId, $attributeIds, $taxRate)
    {
        $resultAttributes = array();
        foreach ($attributeIds as $attributeId) {
            $query = "SELECT
						o.products_options_id AS `options_id`,
						ov.products_options_values_id AS `values_id`,
						ov.products_options_values_name AS `values_name`,
						pa.price_prefix AS `prefix`,
						pa.options_values_price AS `price`,
						o.products_options_name AS `name`
					FROM products AS p
						LEFT JOIN products_attributes 		AS pa ON p.products_id = pa.products_id
						LEFT JOIN products_options 			AS o  ON o.products_options_id = pa.options_id AND o.language_id = {$this->languageId}
						LEFT JOIN products_options_values 	AS ov ON (ov.products_options_values_id = pa.options_values_id AND o.language_id = ov.language_id)
					WHERE 	p.products_id 						= {$productId} AND
							pa.products_attributes_id 			= {$attributeId["products_attributes_id"]} AND
							o.products_options_id 				= {$attributeId["options_id"]} AND
							ov.products_options_values_id 	= {$attributeId["options_values_id"]}";

            $result       = xtc_db_query($query);
            $optionResult = xtc_db_fetch_array($result);
            $sgOption     = new ShopgateOrderItemOption();
            $sgOption->setName($this->stringToUtf8($optionResult["name"]));
            $sgOption->setOptionNumber($optionResult["options_id"]);
            $sgOption->setValue(
                $this->stringToUtf8($optionResult["values_name"])
            );
            $sgOption->setValueNumber($optionResult["values_id"]);

            if (!empty($optionResult["prefix"])) {
                $price =
                    ($optionResult["prefix"] == "-") ? ($optionResult["price"]
                        * (-1)) : $optionResult["price"];
            } else {
                $price = $optionResult["price"];
            }

            $sgOption->setAdditionalAmountWithTax(
                $price * (1 + ($taxRate / 100))
            );
            $resultAttributes[] = $sgOption;
        }
        return $resultAttributes;
    }

	/**
	 * @return mixed
	 */
	public function getCustomerGroups()
	{
		// get customer-group first
		$qry = "SELECT"
			. " status.customers_status_name,"
			. " status.customers_status_discount,"
			. " status.customers_status_discount_attributes"
			. " FROM " . TABLE_CUSTOMERS_STATUS . " AS status"
			. " WHERE status.customers_status_id = " . $this->defaultCustomerPriceGroup
			. " AND status.language_id = " . $this->languageId
			. ";";
		
		// Check if the customer group exists (ignore if not)
		return xtc_db_query($qry);
	}
	
	/**
	 * @param $customerGroupMaxPriceDiscount
	 * @param $customerGroupDiscountAttributes
	 */
	public function getDiscountToCustomerGroups(&$customerGroupMaxPriceDiscount, &$customerGroupDiscountAttributes)
	{
		if($queryResult = $this->getCustomerGroups()) {
			$customerGroupResult = xtc_db_fetch_array($queryResult);
			if(!empty($customerGroupResult) && isset($customerGroupResult['customers_status_discount'])) {
				$customerGroupMaxPriceDiscount = $customerGroupResult['customers_status_discount'];
			}
			if(!empty($customerGroupResult) && isset($customerGroupResult['customers_status_discount'])) {
				$customerGroupDiscountAttributes = $customerGroupResult['customers_status_discount_attributes'] ? true : false;
			}
		}
	}
	
	/**
	 * returns all sub categories including the given parent as a list that is a mapping from one category to a higher category if a given depth is exceeded
	 * @param int $maxDepth
	 * @param int $parentId
	 * @param int $copyId
	 * @param int $depth
	 * @throws ShopgateLibraryException
	 * @return array
	 */
	public function getCategoryReducementMap($maxDepth = null, $parentId = null, $copyId = null, $depth = null) {
		$this->log("execute _getCategoryReducementMap() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$circularDepthStop = 50;
		if(empty($depth)) {
			$depth = 1;
		} elseif($depth > $circularDepthStop) {// disallow circular category connections (detect by a maximum depth)
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
				'error on loading sub-categories: Categories-Depth exceedes a value of '.$circularDepthStop.
				'. Check if there is a circular connection (referenced categories ids: '.$parentId.'=>', true
			);
		}
		
		// select by parent id, if set
		$qry = "SELECT `categories_id` FROM `".TABLE_CATEGORIES."` WHERE" .
				(!empty($parentId) ? " (`parent_id` = '{$parentId}')" : " (`parent_id` IS NULL OR `parent_id` = 0 OR `parent_id` = '')");
		
		$qryResult = xtc_db_query($qry);
		if(!$qryResult) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'error on selecting categories', true);
		}
		
		// add all sub categories to a simple one-dimensional array
		$categoryMap = array();
		while($row = xtc_db_fetch_array($qryResult)) {
			// copy only if a maximum depth is set, yet
			if(!empty($maxDepth)) {
				if($depth == $maxDepth) {
					$copyId = $row['categories_id'];
				}
			}
			// Check if a mapping to a higher category needs to be applied
			if(!empty($copyId) && !empty($row['categories_id'])) {
				$categoryMap[$row['categories_id']] = $copyId;
			} else {
				// no mapping to other categories, map to itself!
				$categoryMap[$row['categories_id']] = $row['categories_id'];
			}
			
			$subCategories = $this->getCategoryReducementMap($maxDepth, $row['categories_id'], $copyId, $depth+1);
			if(!empty($subCategories)) {
				$categoryMap = $categoryMap+$subCategories;
			}
		}
		
		return $categoryMap;
	}
	
	/**
	 * @return string
	 */
	public function getProductsToNewCategoryQuery()
	{
		$group_check = '';
		$fsk_lock = '';
		$_SESSION['languages_id'] = $this->languageId;
		//logic taken from file products_new.php in dir /
		$date_new_products = date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
		$days = " and p.products_date_added > '".$date_new_products."' ";
		
		return "select distinct
									p.products_id,
									p.products_fsk18,
									pd.products_name,
									pd.products_short_description,
									p.products_image,
									p.products_price,
									p.products_vpe,
									p.products_vpe_status,
									p.products_vpe_value,
									p.products_tax_class_id,
									p.products_shippingtime,
									p.products_date_added,
									m.manufacturers_name
							from ".TABLE_PRODUCTS." p
							left join ".TABLE_MANUFACTURERS." m
							on p.manufacturers_id = m.manufacturers_id
							left join ".TABLE_PRODUCTS_DESCRIPTION." pd
									on p.products_id = pd.products_id,
									".TABLE_CATEGORIES." c,
									".TABLE_PRODUCTS_TO_CATEGORIES." p2c
							where pd.language_id = '".(int) $_SESSION['languages_id']."'
							and c.categories_status=1
							and p.products_id = p2c.products_id
							and c.categories_id = p2c.categories_id
							and products_status = '1'
								".$group_check."
								".$fsk_lock."
								".$days."
							order by p.products_date_added DESC ";
	}
	
	/**
	 * @return string
	 */
	public function getProductsToSpecialCategoryQuery(){
		$group_check = '';
		$fsk_lock = '';
		$_SESSION['languages_id'] = $this->languageId;
		//logic taken from file specials.php in dir /
		return    "select distinct
								pd.products_name,
								p.products_price,
								p.products_id,
								p.products_tax_class_id,
								p.products_shippingtime,
								p.products_image,
								p.products_vpe_status,
								p.products_vpe_value,
								p.products_vpe,
								p.products_fsk18,
								s.expires_date,
								s.specials_new_products_price
								from
								".TABLE_PRODUCTS." p
								left join ".TABLE_PRODUCTS_DESCRIPTION." pd
								on p.products_id = pd.products_id
								left join ".TABLE_SPECIALS." s	on p.products_id=s.products_id
								where p.products_status='1'
								and s.products_id=p.products_id
								and p.products_id=pd.products_id
								".$group_check."
								".$fsk_lock."
								and pd.language_id='".(int)$_SESSION['languages_id']."'
								and s.status='1'
								order by s.specials_date_added DESC";
	}
	
	/**
	 * @param $productId
	 * @param $type
	 * @param string $optionsAsInputFields Comma-separated list of option IDs that should be exported as input fields
	 *
	 * @return string|void
	 */
	private function getAttributeQuery($productId, $type, $optionsAsInputFields = ''){
		$optionsAsInputFields = trim($optionsAsInputFields, ',');
		$optionsAsInputFields = (!empty($optionsAsInputFields))
				? 'AND ({$condition} ('.$optionsAsInputFields.'))'
				: ''
		;
		
		switch($type){
			case self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_TEXT_FIELD:
				$optionsAsInputFields = empty($optionsAsInputFields)
					? ' AND pov.products_options_values_name = \'TEXTFELD\' '
					: str_replace('{$condition}', 'pov.products_options_values_name = \'TEXTFELD\' OR pa.options_id IN', $optionsAsInputFields);
				$query = $optionsAsInputFields." ORDER BY po.products_options_id, pa.sortorder";
			break;
			
			case self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_OTHER:
				$optionsAsInputFields = str_replace('{$condition}', 'pov.products_options_values_name != \'TEXTFELD\' AND pa.options_id NOT IN', $optionsAsInputFields);
				$query = $optionsAsInputFields." ORDER BY po.products_options_id, pa.sortorder ASC";
			break;
			
			default: return; break;
		}
		
		return "SELECT
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
			INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po ON (pa.options_id = po.products_options_id AND po.language_id = {$this->languageId})
			INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov ON (pa.options_values_id = pov.products_options_values_id AND pov.language_id = {$this->languageId})
			WHERE pa.products_id = '".$productId."' " . $query;
	}
	
	/**
	 * @param $productId
	 * @param string $optionsAsInputFields Comma-separated list of option IDs that should be exported as input fields
	 *
	 * @return string|void
	 */
	public function getAttributesToProductQuery($productId, $optionsAsInputFields = '')
	{
		return $this->getAttributeQuery($productId, self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_OTHER, $optionsAsInputFields);
	}
	
	/**
	 * @param $productId
	 * @param string $optionsAsInputFields Comma-separated list of option IDs that should be exported as input fields
	 *
	 * @return string|void
	 */
	public function getAttributesInputFieldsToProductsQuery($productId, $optionsAsInputFields = '')
	{
		return $this->getAttributeQuery($productId, self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_TEXT_FIELD, $optionsAsInputFields);
	}
	
	/**
	 * @param $productId
	 *
	 * @return string
	 */
	private function getImagesToProductQuery($productId)
	{
		return "SELECT *
				FROM ".TABLE_PRODUCTS_IMAGES."
				WHERE products_id = '".$productId."'
				ORDER BY image_nr";
	}
	
	/**
	 * @return string
	 */
	private function getLocalMainImagePath(){
		return DIR_FS_CATALOG.DIR_WS_ORIGINAL_IMAGES;
	}
	
	/**
	 * @return string
	 */
	private function getMainImageUrl(){
		return HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_ORIGINAL_IMAGES;
	}
	
	/**
	 * @return string
	 */
	private function getLocalThumbImagePath(){
		return DIR_FS_CATALOG.DIR_WS_POPUP_IMAGES;
	}
	
	/**
	 * @return string
	 */
	private function getThumbImageUrl(){
		return HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_POPUP_IMAGES;
	}
	
	/**
	 * @param $product
	 *
	 * @return array
	 */
	public function generateImageUrls($product)
	{
		$images = array();
		if(!empty($product['products_image'])){
			if(file_exists($this->getLocalMainImagePath().$product['products_image'])){
				$images[] = $this->getMainImageUrl().$product['products_image'];
			}elseif(file_exists($this->getLocalThumbImagePath().$product['products_image'])){
				$images[] = $this->getThumbImageUrl().$product['products_image'];
			}
		}
		
		$query = xtc_db_query($this->getImagesToProductQuery($product["products_id"]));
		while($image = xtc_db_fetch_array($query)) {
			if(file_exists($this->getLocalMainImagePath().$image['image_name'])){
				$images[] = $this->getMainImageUrl().$image['image_name'];
			}elseif(file_exists($this->getLocalThumbImagePath().$image['image_name'])){
				$images[] = $this->getThumbImageUrl().$image['image_name'];
			}
		}
		return $images;
	}
	
	/**
	 * @param $productId
	 * @param $productName
	 *
	 * @return mixed
	 */
	public function generateDeepLinkToProduct($productId, $productName)
	{
		return xtc_href_link('product_info.php', xtc_product_link($productId, $productName), 'NONSSL', false);
	}
	
	/**
	 * @param $item
	 * @param $tax_rate
	 * @param $customerGroupMaxPriceDiscount
	 * @param $customerGroupMaxPriceDiscount
	 * @param $price
	 * @param $oldPrice
	 */
	public function calculateProductPrice($item, $tax_rate, $customerGroupMaxPriceDiscount, $customerGroupMaxPriceDiscount, &$price, &$oldPrice)
	{
		// Special offers for a Customer group
		$pOffers = $this->getPersonalOffersPrice($item, $tax_rate);
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
			$price = $this->getDiscountPrice($price, $productDiscount);
		}
	}
	
	/**
	 * Takes a price value and a discount percent value and returns the new discounted price
	 * @param float $price
	 * @param float $discountPercent
	 * @return float
	 */
	public function getDiscountPrice($price, $discountPercent) {
		$discountedPrice = $price * (1-$discountPercent/100);
		return $discountedPrice;
	}
	
	/**
	 *
	 * @param mixed[] $product
	 * @param mixed[] $tax
	 * @return float
	 */
	private function getPersonalOffersPrice($product, $tax) {
		$this->log("execute _getPersonalOffersPrice() ...", ShopgateLogger::LOGTYPE_DEBUG);
		
		$customerStatusId = $this->defaultCustomerPriceGroup;
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
	 * @param $item
	 * @param $descriptionType
	 *
	 * @return mixed
	 */
	public function getDescriptionToProduct($item, $descriptionType)
	{
		// create the description, based on the settings
        $desc = $this->stringHelper->removeTagsFromString($item["products_description"]);
        $shortDesc = $this->stringHelper->removeTagsFromString($item["products_short_description"]);
		$description = '';
		switch($descriptionType) {
			case SHOPGATE_SETTING_EXPORT_DESCRIPTION:
				$description = $desc;
				break;
			case SHOPGATE_SETTING_EXPORT_SHORTDESCRIPTION:
				$description = $shortDesc;
				break;
			case SHOPGATE_SETTING_EXPORT_DESCRIPTION_SHORTDESCRIPTION:
				$description = $desc . "<br/><br/>" . $shortDesc;
				break;
			case SHOPGATE_SETTING_EXPORT_SHORTDESCRIPTION_DESCRIPTION:
				$description = $shortDesc . "<br/><br/>" . $desc;
				break;
		}
		
		return preg_replace("/\n|\r/", "", $description);
	}
	
	/**
	 * @param $product
	 *
	 * @return string
	 */
	public function generatePropertiesToProduct($product)
	{
		$properties = array();
		
		if(!empty($product["products_fsk18"]) && $product["products_fsk18"] == 1)
			$properties[] = "Altersbeschränkung=>18 Jahre";
		
		return implode("||", $properties);
	}
}
