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
class ShopgateItemXmlModel extends ShopgateItemModel
{
    /**
     * @var null|ShopgateItemXmlModel
     */
    private $parent = null;
    
    /**
     * @var string
     */
    private $orderInfo;
    
    /**
     * @var array
     */
    protected $item;
    
    /**
     * @var array
     */
    private $xtPricesByCustomerGroups;
    
    /**
     * cache for product data
     *
     * @var array
     */
    private $cache = array(
        'options'        => '',
        'variationCount' => '',
        'currentChild'   => '',
    );
    
    public function setUid()
    {
        if ($this->getIsChild()) {
            $hash = '';
            foreach ($this->cache['currentChild'] as $variation) {
                $hash .= $variation['products_options_values_name'];
            }
            $hash = md5($hash);
            $hash = substr($hash, 0, 5);
            parent::setUid($this->item['products_id'] . '_' . $hash);
        } else {
            parent::setUid($this->item['products_id']);
        }
    }
    
    /**
     * @param array $xtPricesByCustomerGroups
     */
    public function setXtPricesByCustomerGroups($xtPricesByCustomerGroups)
    {
        $this->xtPricesByCustomerGroups = $xtPricesByCustomerGroups;
    }
    
    public function setName()
    {
        parent::setName($this->generateItemName());
    }
    
    public function setTaxPercent()
    {
        $taxRate = (float)xtc_get_tax_rate($this->item['products_tax_class_id'], $this->countryId, $this->config->getTaxZoneId());
        parent::setTaxPercent(!empty($taxRate) ? $taxRate : 0.0);
    }
    
    public function setTaxClass()
    {
        parent::setTaxClass($this->getTaxClassTitleById($this->item['products_tax_class_id']));
    }
    
    public function setCurrency()
    {
        parent::setCurrency($this->config->getCurrency());
    }
    
    public function setDescription()
    {
        parent::setDescription($this->getDescriptionToProduct($this->item, $this->config->getExportDescriptionType()));
    }
    
    public function setDeeplink()
    {
        parent::setDeeplink($this->generateDeepLinkToProduct($this->item['products_id'], $this->item['products_name']));
    }
    
    public function setWeightUnit()
    {
        parent::setWeightUnit(self::DEFAULT_WEIGHT_UNIT_KG);
    }
    
    public function setInternalOrderInfo()
    {
        if ($this->getIsChild()) {
            $orderInfo = array();
            $i         = 0;
            
            foreach ($this->cache['currentChild'] as $variation) {
                ++$i;
                $orderInfo['attribute_' . $i] = array(
                    $variation['products_attributes_id'] => array(
                        'options_id'        => $variation['products_options_id'],
                        'options_values_id' => $variation['products_options_values_id']
                    )
                );
            }
            parent::setInternalOrderInfo($this->jsonEncode($orderInfo));
        }
        if (!empty($this->orderInfo)) {
            parent::setInternalOrderInfo($this->jsonEncode($this->orderInfo));
        }
    }
    
    
    public function setAgeRating()
    {
        parent::setAgeRating(empty($this->item['products_fsk18']) ? "" : "18");
    }
    
    public function setWeight()
    {
        if ($this->getIsChild()) {
            $weight = 0;
            foreach ($this->cache['currentChild'] as $variation) {
                $weight = ($variation['weight_prefix'] == '-')
                    ? $variation['options_values_weight'] * (-1)
                    : $variation['options_values_weight'];
            }
            
            parent::setWeight($this->item['products_weight'] + $weight);
        } else {
            $weight = $this->item['products_weight'];
            parent::setWeight($weight);
        }
    }
    
    
    public function setPrice()
    {
        $priceModel  = new Shopgate_Model_Catalog_Price();
        $taxRate     = xtc_get_tax_rate($this->item["products_tax_class_id"], $this->countryId, $this->zoneId);
        $priceHelper = $this->getHelper(ShopgateObject::HELPER_PRICING);
        
        $xtPrice                                               =
            new xtcPrice($this->config->getCurrency(), DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
        $xtPrice->cStatus['customers_status_graduated_prices'] = "0";

        $salePrice = $xtPrice->xtcGetPrice(
            $this->item['products_id'],
            false,
            1,
            $this->item['products_tax_class_id'],
            $this->item['products_price'],
            1
        );
        
        if ($taxRate > 0) {
            $priceType = Shopgate_Model_Catalog_Price::DEFAULT_PRICE_TYPE_GROSS;
            $price     = ($this->item['products_price'] * $this->exchangeRate) * (1 + ($taxRate / 100));
        } else {
            $priceType = Shopgate_Model_Catalog_Price::DEFAULT_PRICE_TYPE_NET;
            $price     = $this->item['products_price'] * $this->exchangeRate;
        }
        
        if ($this->getIsChild()) {
            $additionalPrice = $this->calculateVariationsAddAmount($xtPrice);
            $price += ($additionalPrice * $this->exchangeRate);
            $salePrice = $salePrice + ($additionalPrice * $this->exchangeRate);
        }

        $priceModel->setPrice($priceHelper->formatPriceNumber($price));
        $priceModel->setSalePrice($priceHelper->formatPriceNumber($salePrice));
        $priceModel->setType($priceType);
        $priceModel->setBasePrice($this->getProductVPE($this->item, $price));

        if (!$this->getIsChild()) {
            $this->addTierPricesTo($priceModel, $priceHelper);
        }

        parent::setPrice($priceModel);
    }
    
    public function setShipping()
    {
        // not supported by modified ecommerce
        $shipping = new Shopgate_Model_Catalog_Shipping();
        $shipping->setAdditionalCostsPerUnit(0.0);
        $shipping->setCostsPerOrder(0.0);
        $shipping->setIsFree(false);
        
        parent::setShipping($shipping);
    }

    public function setManufacturer()
    {
        $title   = $this->item['manufacturers_name'];
        $uid     = isset($this->item['manufacturers_id']) ? $this->item['manufacturers_id'] : 0;
        $modelId =
            isset($this->item['products_manufacturers_model']) ? $this->item['products_manufacturers_model'] : '';

        $manufacturerModel = new Shopgate_Model_Catalog_Manufacturer();
        $manufacturerModel->setUid($uid);
        $manufacturerModel->setTitle($title);
        $manufacturerModel->setItemNumber($modelId);

        parent::setManufacturer($manufacturerModel);
    }
    
    public function setVisibility()
    {
        $visibility = new Shopgate_Model_Catalog_Visibility();
        $visibility->setLevel(Shopgate_Model_Catalog_Visibility::DEFAULT_VISIBILITY_CATALOG_AND_SEARCH);
        $visibility->setMarketplace(true);
        
        parent::setVisibility($visibility);
    }
    
    public function setStock()
    {
        $useStock = $this->generateUseStock();
        $stockQty = $this->generateStockQuantity();
        
        if ($this->getIsChild()
            && ATTRIBUTE_STOCK_CHECK == 'true'
        ) {
            // The stocks of all variations are iterated and in the end the lowest is set.
            // Because there is no "real" stock management for every product in modified
            foreach ($this->cache['currentChild'] as $variation) {
                if ($variation['attributes_stock'] < $stockQty) {
                    $stockQty = $variation['attributes_stock'];
                }
            }
        }
        
        $stockModel = new Shopgate_Model_Catalog_Stock();
        $stockModel->setAvailabilityText($this->getAvailableText($this->item));
        $stockModel->setStockQuantity($stockQty);
        if ($useStock) {
            $isSalable = $stockQty > 0
                ? 1
                : 0;
            $stockModel->setIsSaleable($isSalable);
            $stockModel->setUseStock(1);
        } else {
            $stockModel->setIsSaleable(1);
            $stockModel->setUseStock(0);
        }
        
        parent::setStock($stockModel);
    }
    
    public function setImages()
    {
        $imageUrls = $this->generateImageUrls($this->item);
        $imageList = array();
        
        foreach ($imageUrls as $url) {
            $image = new Shopgate_Model_Media_Image();
            $image->setUrl($url);
            $imageList[] = $image;
        }
        
        parent::setImages($imageList);
    }
    
    public function setCategoryPaths()
    {
        $categories   = $this->getProductCategoryNumbers($this->item);
        $categoryData = array();
        foreach ($categories as $category) {
            $category = explode('=>', $category);
            $catModel = new Shopgate_Model_Catalog_CategoryPath();
            $catModel->setUid($category[0]);
            if (isset($category[1])) {
                $catModel->setSortOrder($category[1]);
            }
            $categoryData[] = $catModel;
        }
        
        parent::setCategoryPaths($categoryData);
    }
    
    public function setProperties()
    {
        $properties   = $this->generatePropertiesToProduct($this->item, true);
        $propertyData = array();
        foreach ($properties as $property) {
            $propertyModel = new Shopgate_Model_Catalog_Property();
            $propertyModel->setLabel($property['label']);
            $propertyModel->setValue($property['value']);
            $propertyData[] = $propertyModel;
        }
        
        parent::setProperties($propertyData);
    }
    
    public function setIdentifiers()
    {
        $identifierData = array();
        $ean            = null;
        $sku            = null;

        if ($this->getIsChild()) {
            foreach ($this->cache['currentChild'] as $variation) {
                $ean = $variation['attributes_ean'];
                $sku = $variation['attributes_model'];
            }
        } else {
            $ean = preg_replace("/\s+/i", '', $this->item["products_ean"]);
            $sku = $this->item['products_model'];
        }

        if (!empty($ean)) {
            $identifier = new Shopgate_Model_Catalog_Identifier();
            $identifier->setType("ean");
            $identifier->setValue($ean);
            $identifierData[] = $identifier;
        }
        
        if (!empty($sku)) {
            $identifierModel = new Shopgate_Model_Catalog_Identifier();
            $identifierModel->setType('sku');
            $identifierModel->setValue($sku);
            $identifierData[] = $identifierModel;
        }

        parent::setIdentifiers($identifierData);
    }
    
    public function setTags()
    {
        $result = array();
        $tags   = explode(',', trim($this->item['products_keywords']));
        
        foreach ($tags as $tag) {
            if (!ctype_space($tag) && !empty($tag)) {
                $tagItemObject = new Shopgate_Model_Catalog_Tag();
                $tagItemObject->setValue(trim($tag));
                $result[] = $tagItemObject;
            }
        }
        
        parent::setTags($result);
    }
    
    public function setRelations()
    {
        $result       = array();
        $crossSellIds = $this->getRelatedShopItems($this->item['products_id'], true);
        
        if (!empty($crossSellIds)) {
            $crossSellRelation = new Shopgate_Model_Catalog_Relation();
            $crossSellRelation->setType(Shopgate_Model_Catalog_Relation::DEFAULT_RELATION_TYPE_UPSELL);
            $crossSellRelation->setValues($crossSellIds);
            $result[] = $crossSellRelation;
        }
        
        parent::setRelations($result);
    }
    
    public function setInputs()
    {
        if ($this->getVariationCombinationCount() > $this->config->getMaxAttributes()) {
            $attributeCount = 0;
            $variations     = $this->getOptions();
            $priceHelper    = $this->getHelper(ShopgateObject::HELPER_PRICING);
            $inputResult    = array();
            $optionAsInput  = $this->config->getExportOptionAsInputField();
            $optionAsInput  = explode(",", $optionAsInput);
            $taxRate        = xtc_get_tax_rate($this->item["products_tax_class_id"], $this->countryId, $this->zoneId);
            
            foreach ($variations as $variationGroup) {
                $firstItem = reset($variationGroup);
                $input     = new Shopgate_Model_Catalog_Input();
                $options   = array();
                $input->setUid($firstItem['products_options_id']);
                $input->setLabel($firstItem['products_options_name']);
                ++$attributeCount;
                foreach ($variationGroup as $variation) {
                    
                    $price = $variation['price_prefix'] == "-"
                        ? $priceHelper->formatPriceNumber($variation['options_values_price'] * (-1), 2)
                        : $priceHelper->formatPriceNumber($variation['options_values_price'], 2);
                    $price = $price * (1 + $taxRate / 100);
                    
                    $this->orderInfo["attribute_{$attributeCount}"][$variation['products_attributes_id']][] = array(
                        'options_id'        => $variation['products_options_id'],
                        'options_values_id' => $variation['products_options_values_id'],
                    );
                    
                    
                    // the only way to use text fields in modified is to set the option value name to "TEXTFELD"
                    // also the option to text field mapping needs to be regarded
                    if (
                        (!empty($variation['products_options_values_name'])
                            && $variation['products_options_values_name'] == "TEXTFELD")
                        || (!empty($optionAsInput)
                            && in_array($variation['products_options_id'], $optionAsInput))
                    ) {
                        $input->setType(Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_TEXT);
                        $input->setAdditionalPrice($price);
                        $inputResult[] = $input;
                        continue;
                    } else {
                        // reset price, if previous item was a input field
                        $input->setAdditionalPrice("");
                        $input->setType(Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_SELECT);
                        
                        $option = new Shopgate_Model_Catalog_Option();
                        $option->setUid($variation['products_options_id']);
                        $option->setAdditionalPrice($price);
                        $option->setSortOrder($variation['sortorder']);
                        $option->setLabel($variation['products_options_values_name']);
                        
                        $options[] = $option;
                    }
                }
                
                if (count($options) > 0) {
                    $input->setType(Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_SELECT);
                    $input->setOptions($options);
                    $inputResult[] = $input;
                }
            }
            
            $parentOrderInfo = parent::getInternalOrderInfo();
            
            if (!empty($parentOrderInfo) && is_string($parentOrderInfo)) {
                $parentOrderInfo = $this->jsonDecode($parentOrderInfo);
            }
            
            if (!empty($parentOrderInfo)) {
                if (!empty($this->orderInfo)) {
                    $orderInfo = array_merge($parentOrderInfo, $this->orderInfo);
                    parent::setInternalOrderInfo($this->jsonEncode($orderInfo));
                }
            } else {
                if (!empty($this->orderInfo)) {
                    parent::setInternalOrderInfo($this->jsonEncode($this->orderInfo));
                }
            }
            
            parent::setInputs($inputResult);
        }
    }
    
    public function setChildren()
    {
        if ($this->getVariationCombinationCount() <= $this->config->getMaxAttributes() && !$this->getIsChild()) {
            $childData   = array();
            $inputData   = array();
            $childInputs = $this->getVariationInputs();
            $children    = $this->getVariationWithoutInputs();
            $priceHelper = $this->getHelper(ShopgateObject::HELPER_PRICING);
            
            foreach ($children as $child) {
                $childModel = clone $this;
                $childModel->setIsChild(true);
                $childModel->cache['currentChild'] = $child;
                $childModel->setParent($this);
                $childModel->setFireMethodsForChildren();
                $childModel->generateData();
                $childData[] = $childModel;
            }
            
            parent::setChildren($childData);
            
            foreach ($childInputs as $elements) {
                foreach ($elements as $childInput) {
                    $price = $childInput['price_prefix'] == "-"
                        ? $priceHelper->formatPriceNumber($childInput['options_values_price'] * (-1), 2)
                        : $priceHelper->formatPriceNumber($childInput['options_values_price'], 2);
                    $input = new Shopgate_Model_Catalog_Input();
                    $input->setUid($childInput['products_options_id']);
                    $input->setLabel($childInput['products_options_name']);
                    $input->setType(Shopgate_Model_Catalog_Input::DEFAULT_INPUT_TYPE_TEXT);
                    $input->setAdditionalPrice($price);
                    $inputData[] = $input;
                }
            }
            
            parent::setInputs($inputData);
        }
    }
    
    public function setDisplayType()
    {
        parent::setDisplayType(Shopgate_Model_Catalog_Product::DISPLAY_TYPE_DEFAULT);
    }
    
    public function setAttributes()
    {
        $parentAttGroups = array();
        if ($this->getVariationCombinationCount() <= $this->config->getMaxAttributes() && $this->getIsChild()) {
            
            $inputFields     = array();
            $parentAttGroups = $this->parent != null
                ? $this->parent->getAttributeGroups()
                : array();
            
            foreach ($this->cache['currentChild'] as $attribute) {
                $inputObject = new Shopgate_Model_Catalog_Attribute();
                $inputObject->setGroupUid($attribute['products_options_id']);
                $inputObject->setLabel($attribute['products_options_values_name']);
                $inputFields[] = $inputObject;
                
                if (!isset($parentAttGroups[$attribute['products_options_id']])) {
                    /* @var $attribute Shopgate_Model_Catalog_AttributeGroup */
                    $attributeGroupItem = new Shopgate_Model_Catalog_AttributeGroup();
                    $attributeGroupItem->setUid($attribute['products_options_id']);
                    $attributeGroupItem->setLabel($attribute['products_options_name']);
                    $parentAttGroups[$attribute['products_options_id']] = $attributeGroupItem;
                }
            }
            
            parent::setAttributes($inputFields);
        }
        
        if ($this->parent != null) {
            $this->parent->setAttributeGroups($parentAttGroups);
        }
    }
    
    public function setLastUpdate()
    {
        parent::setLastUpdate($this->item['products_last_modified']);
    }
    
    /**
     * set the methods which need to be called for every child product
     */
    public function setFireMethodsForChildren()
    {
        $this->fireMethods = array(
            'setUid',
            'setStock',
            'setInputs',
            'setAttributes',
            'setPrice',
            'setWeight',
            'setInternalOrderInfo'
        );
    }
    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * calculates the quantity of option combinations (cross product)
     *
     * @return int
     */
    private function getVariationCombinationCount()
    {
        return (!empty($this->cache['variationCount']))
            ? $this->cache['variationCount']
            : $this->cache['variationCount'] = $this->calculateVariationAmountByOptions($this->getOptions());
    }
    
    /**
     * read all variations of type "text field" to an products from the database
     *
     * @return array|array[][]
     */
    private function getVariationInputs()
    {
        return $this->generateAttributes($this->getAttributesInputFieldsToProductsQuery($this->item['products_id']));
    }
    
    /**
     * read all variations which are NOT of type "text field" to an products from the database
     *
     * @return array|array[][]
     */
    private function getVariationWithoutInputs()
    {
        return $this->generateAttributes($this->getAttributesToProductQuery($this->item['products_id']));
    }
    
    /**
     * read all options from the database and generate the cross product of them
     *
     * @return array|array[][]
     */
    private function getOptions()
    {
        return (!empty($this->cache['options']))
            ? $this->cache['options']
            : $this->cache['options'] =
                $this->generateAttributes($this->getAllAttributesToProductsQuery($this->item['products_id']), true);
    }
    
    /**
     * removes tags from the products name
     *
     * @return string
     */
    public function generateItemName()
    {
        return trim(preg_replace('/<[^>]+>/', '', $this->item['products_name']));
    }
    
    /**
     * calculate stock for given product
     *
     * @return mixed
     */
    public function generateStockQuantity()
    {
        $qty = $this->item['products_quantity'];
        if (!empty($this->item['specials_new_products_price'])
            && (STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT == 'false')
            && ($this->item['specials_quantity'] > 0)
        ) {
            $qty = $this->item['specials_quantity'] > $this->item['products_quantity']
                ? $this->item['products_quantity']
                : $this->item['specials_quantity'];
        }
        
        return $qty;
    }
    
    /**
     * add tier prices to the Shopgate_Model_Catalog_Price model
     *
     * @param Shopgate_Model_Catalog_Price $priceModel
     * @param Shopgate_Helper_Pricing      $priceHelper
     *
     * @throws ShopgateLibraryException
     */
    protected function addTierPricesTo(
        Shopgate_Model_Catalog_Price $priceModel, Shopgate_Helper_Pricing $priceHelper
    ) {
        /**
         * @var int      $customerGroupId
         * @var xtcPrice $xtPrice
         */
        foreach ($this->xtPricesByCustomerGroups as $customerGroupId => $xtPrice) {
            if ($xtPrice->cStatus['customers_status_show_price'] == '0') {
                continue;
            }

            $getDiscountsOnly = ($xtPrice->cStatus['customers_status_graduated_prices'] == '1')
                ? ''
                : ' AND `quantity` = 1 ';

            $quantitiesQuery = xtc_db_query(
                'SELECT `quantity` ' .
                'FROM `personal_offers_by_customers_status_' . ((int)$customerGroupId) . '` ' .
                'WHERE `products_id` = ' . ((int)$this->item['products_id']) . ' ' . $getDiscountsOnly .
                'ORDER BY `quantity`;'
            );
            
            while ($quantity = xtc_db_fetch_array($quantitiesQuery)) {
                
                $addAmount      = $this->calculateVariationsAddAmount($xtPrice);
                $graduatedPrice = $xtPrice->xtcGetPrice(
                    $this->item['products_id'],
                    false,
                    $quantity['quantity'],
                    $this->item['products_tax_class_id'],
                    $this->item['products_price'],
                    1
                );
                
                if ($addAmount > 0) {
                    $graduatedPrice += $addAmount -
                        (($xtPrice->cStatus['customers_status_discount_attributes'])
                            ? ($addAmount * ($xtPrice->xtcCheckDiscount($this->item['products_id']) / 100))
                            : 0);
                }
                
                $reduction = $priceModel->getSalePrice() - $graduatedPrice;

                $tierPriceModel = new Shopgate_Model_Catalog_TierPrice();
                $tierPriceModel->setFromQuantity($quantity['quantity']);
                $tierPriceModel->setReduction($priceHelper->formatPriceNumber($reduction, 6));
                $tierPriceModel->setReductionType(Shopgate_Model_Catalog_TierPrice::DEFAULT_TIER_PRICE_TYPE_FIXED);
                $tierPriceModel->setAggregateChildren(true); // tier prices are always aggregated in
                $tierPriceModel->setCustomerGroupUid($customerGroupId);

                if (round($reduction, 6) > 0) {
                    $priceModel->addTierPriceGroup($tierPriceModel);
                }
            }

            if ($xtPrice->cStatus['customers_status_ot_discount'] > 0) {

                $discount       = $xtPrice->xtcGetDC($priceModel->getSalePrice(), $xtPrice->cStatus['customers_status_ot_discount']);
                $tierPriceModel = new Shopgate_Model_Catalog_TierPrice();
                $tierPriceModel->setFromQuantity(1);
                $tierPriceModel->setReduction($priceHelper->formatPriceNumber($discount, 6));
                $tierPriceModel->setReductionType(Shopgate_Model_Catalog_TierPrice::DEFAULT_TIER_PRICE_TYPE_FIXED);
                $tierPriceModel->setAggregateChildren(true); // tier prices are always aggregated in
                $tierPriceModel->setCustomerGroupUid($customerGroupId);
                $priceModel->addTierPriceGroup($tierPriceModel);

            }
        }
    }
    
    /**
     * calculates the amount of all child items
     *
     * @param xtcPrice $xtPrice
     *
     * @return int
     */
    protected function calculateVariationsAddAmount(xtcPrice $xtPrice)
    {
        $additionalPrice = 0;
        foreach ($this->cache['currentChild'] as $variation) {
            $price = $xtPrice->xtcGetOptionPrice(
                $this->item['products_id'], $variation['products_options_id'], $variation['products_options_values_id']
            );
            $additionalPrice += $price['price'];
        }
        
        return $additionalPrice;
    }
}
