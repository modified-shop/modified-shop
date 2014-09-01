<?php

/**
 * Shogate Category Object
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 * @see http://wiki.shopgate.com/Merchant_API_get_categories/
 */
class ShopgateCategory extends ShopgateContainer {
	protected $category_number = null;
	protected $name = null;
	protected $parent_category_number = null;
	protected $url_image = null;
	protected $order_index = null;
	protected $is_active = null;

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitCategory($this);
	}

	/**
	 * The Category-Number of the Category on Shopgate
	 *
	 * Ideally, it is the same as the category-number or id in the Shopsystem
	 *
	 * @param string $value
	 */
	public function setCategoryNumber( $value ) {
		$this->category_number = $value;
	}

	/**
	 * The Name of the Category
	 *
	 * @param string $value
	 */
	public function setName( $value ) {
		$this->name = $value;
	}

	/**
	 * The Parent Category Number
	 *
	 * @param string $value
	 */
	public function setParentCategoryNumber( $value ) {
		$this->parent_category_number = $value;
	}

	/**
	 * The Image Url
	 *
	 * @param string $value
	 */
	public function setUrlImage( $value ) {
		$this->url_image = $value;
	}

	/**
	 * The Order Index
	 *
	 * Shopgate Use a descending order. The Category with the highest order is on top
	 *
	 * @param int $value
	 */
	public function setOrderIndex( $value ) {
		$this->order_index = $value;
	}

	/**
	 * Set the Category to Active or Inactive
	 *
	 * @param boolean $value
	 */
	public function setIsActive( $value ) {
		$this->is_active = $value;
	}

	/**
	 * The Category-Number of the Category on Shopgate
	 *
	 * Ideally, it is the same as the category-number or id in the Shopsystem
	 *
	 * @return string
	 */
	public function getCategoryNumber() {
		return $this->category_number;
	}

	/**
	 * The category name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * The Parent Category Number
	 *
	 * @return string
	 */
	public function getParentCategoryNumber() {
		return $this->parent_category_number;
	}

	public function getUrlImage() {
		return $this->url_image;
	}

	public function getOrderIndex() {
		return $this->order_index;
	}

	public function getIsActive() {
		return $this->is_active;
	}
}

/**
 *
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
class ShopgateItem extends ShopgateContainer {
	protected $item_number = null;
	protected $name = null;
	protected $currency = null;
	protected $tax_percent = null;
	protected $unit_amount_with_tax = null;
	protected $old_unit_amount_with_tax = null;
	protected $category_numbers = array();
	protected $item_number_public = null;
	protected $parent_item_number = null;
	protected $manufacturer = null;
	protected $manufacturer_number = null;
	protected $description = null;
	protected $shipping_costs_per_order = null;
	protected $shipping_costs_per_unit = null;
	protected $is_free_shipping = null;
	protected $msrp = null;
	protected $tags = null;
	protected $age_rating = null;
	protected $weight = null;
	protected $ean = null;
	protected $isbn = null;
	protected $pzn = null;
	protected $amount_info_text = null;
	protected $internal_order_info = null;
	protected $use_stock = null;
	protected $stock_quantity = null;
	protected $is_highlight = null;
	protected $highlight_order_index = null;
	protected $is_available = null;
	protected $available_text = null;
	protected $has_image = null;
	protected $image_count = null;
	protected $is_marketplace = null;
	protected $is_active = null;
	protected $is_auto_update = null;
	protected $attribute_1 = null;
	protected $attribute_2 = null;
	protected $attribute_3 = null;
	protected $attribute_4 = null;
	protected $attribute_5 = null;
	protected $attribute_6 = null;
	protected $attribute_7 = null;
	protected $attribute_8 = null;
	protected $attribute_9 = null;
	protected $attribute_10 = null;
	protected $properties = array();
	protected $deeplink_onlineshop = null;
	protected $related_item_numbers = array();
	protected $options = array();
	protected $inputs = array();

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitItem($this);
	}

	/**
	 *
	 * @param $item_number
	 */
	public function setItemNumber($item_number)
	{
	    $this->item_number = $item_number;
	}

	/**
	 *
	 * @param $name
	 */
	public function setName($name)
	{
	    $this->name = $name;
	}

	/**
	 *
	 * @param $currency
	 */
	public function setCurrency($currency)
	{
	    $this->currency = $currency;
	}

	/**
	 *
	 * @param $tax_percent
	 */
	public function setTaxPercent($tax_percent)
	{
	    $this->tax_percent = $tax_percent;
	}

	/**
	 *
	 * @param $unit_amount_with_tax
	 */
	public function setUnitAmountWithTax($unit_amount_with_tax)
	{
	    $this->unit_amount_with_tax = $unit_amount_with_tax;
	}

	/**
	 *
	 * @param $old_unit_amount_with_tax
	 */
	public function setOldUnitAmountWithTax($old_unit_amount_with_tax)
	{
	    $this->old_unit_amount_with_tax = $old_unit_amount_with_tax;
	}

	/**
	 *
	 * @param $category_numbers
	 */
	public function setCategoryNumbers($category_numbers)
	{
	    $this->category_numbers = $category_numbers;
	}

	/**
	 *
	 * @param $item_number_public
	 */
	public function setItemNumberPublic($item_number_public)
	{
	    $this->item_number_public = $item_number_public;
	}

	/**
	 *
	 * @param $parent_item_number
	 */
	public function setParentItemNumber($parent_item_number)
	{
	    $this->parent_item_number = $parent_item_number;
	}

	/**
	 *
	 * @param $manufacturer
	 */
	public function setManufacturer($manufacturer)
	{
	    $this->manufacturer = $manufacturer;
	}

	/**
	 *
	 * @param $manufacturer_number
	 */
	public function setManufacturerNumber($manufacturer_number)
	{
	    $this->manufacturer_number = $manufacturer_number;
	}

	/**
	 *
	 * @param $description
	 */
	public function setDescription($description)
	{
	    $this->description = $description;
	}

	/**
	 *
	 * @param $shipping_costs_per_order
	 */
	public function setShippingCostsPerOrder($shipping_costs_per_order)
	{
	    $this->shipping_costs_per_order = $shipping_costs_per_order;
	}

	/**
	 *
	 * @param $shipping_costs_per_unit
	 */
	public function setShippingCostsPerUnit($shipping_costs_per_unit)
	{
	    $this->shipping_costs_per_unit = $shipping_costs_per_unit;
	}

	/**
	 *
	 * @param $is_free_shipping
	 */
	public function setIsFreeShipping($is_free_shipping)
	{
	    $this->is_free_shipping = $is_free_shipping;
	}

	/**
	 *
	 * @param $msrp
	 */
	public function setMsrp($msrp)
	{
	    $this->msrp = $msrp;
	}

	/**
	 *
	 * @param $tags
	 */
	public function setTags($tags)
	{
	    $this->tags = $tags;
	}

	/**
	 *
	 * @param $age_rating
	 */
	public function setAgeRating($age_rating)
	{
	    $this->age_rating = $age_rating;
	}

	/**
	 *
	 * @param $weight
	 */
	public function setWeight($weight)
	{
	    $this->weight = $weight;
	}

	/**
	 *
	 * @param $ean
	 */
	public function setEan($ean)
	{
	    $this->ean = $ean;
	}

	/**
	 *
	 * @param $isbn
	 */
	public function setIsbn($isbn)
	{
	    $this->isbn = $isbn;
	}

	/**
	 *
	 * @param $pzn
	 */
	public function setPzn($pzn)
	{
	    $this->pzn = $pzn;
	}

	/**
	 *
	 * @param $amount_info_text
	 */
	public function setAmount_info_text($amount_info_text)
	{
	    $this->amount_info_text = $amount_info_text;
	}

	/**
	 *
	 * @param $internal_order_info
	 */
	public function setInternalOrderInfo($internal_order_info)
	{
	    $this->internal_order_info = $internal_order_info;
	}

	/**
	 *
	 * @param $use_stock
	 */
	public function setUseStock($use_stock)
	{
	    $this->use_stock = $use_stock;
	}

	/**
	 *
	 * @param $stock_quantity
	 */
	public function setStockQuantity($stock_quantity)
	{
	    $this->stock_quantity = $stock_quantity;
	}

	/**
	 *
	 * @param $is_highlight
	 */
	public function setIsHighlight($is_highlight)
	{
	    $this->is_highlight = $is_highlight;
	}

	/**
	 *
	 * @param $highlight_order_index
	 */
	public function setHighlightOrderIndex($highlight_order_index)
	{
	    $this->highlight_order_index = $highlight_order_index;
	}

	/**
	 *
	 * @param $is_available
	 */
	public function setIsAvailable($is_available)
	{
	    $this->is_available = $is_available;
	}

	/**
	 *
	 * @param $available_text
	 */
	public function setAvailableText($available_text)
	{
	    $this->available_text = $available_text;
	}

	/**
	 *
	 * @param $has_image
	 */
	public function setHasImage($has_image)
	{
	    $this->has_image = $has_image;
	}

	/**
	 *
	 * @param $image_count
	 */
	public function setImageCount($image_count)
	{
	    $this->image_count = $image_count;
	}

	/**
	 *
	 * @param $is_marketplace
	 */
	public function setIsMarketplace($is_marketplace)
	{
	    $this->is_marketplace = $is_marketplace;
	}

	/**
	 *
	 * @param $is_active
	 */
	public function setIsActive($is_active)
	{
	    $this->is_active = $is_active;
	}

	/**
	 *
	 * @param $is_auto_update
	 */
	public function setIsAutoUpdate($is_auto_update)
	{
	    $this->is_auto_update = $is_auto_update;
	}

	/**
	 *
	 * @param $attribute_1
	 */
	public function setAttribute1($attribute_1)
	{
	    $this->attribute_1 = $attribute_1;
	}

	/**
	 *
	 * @param $attribute_2
	 */
	public function setAttribute2($attribute_2)
	{
	    $this->attribute_2 = $attribute_2;
	}

	/**
	 *
	 * @param $attribute_3
	 */
	public function setAttribute3($attribute_3)
	{
	    $this->attribute_3 = $attribute_3;
	}

	/**
	 *
	 * @param $attribute_4
	 */
	public function setAttribute4($attribute_4)
	{
	    $this->attribute_4 = $attribute_4;
	}

	/**
	 *
	 * @param $attribute_5
	 */
	public function setAttribute5($attribute_5)
	{
	    $this->attribute_5 = $attribute_5;
	}

	/**
	 *
	 * @param $attribute_6
	 */
	public function setAttribute6($attribute_6)
	{
	    $this->attribute_6 = $attribute_6;
	}

	/**
	 *
	 * @param $attribute_7
	 */
	public function setAttribute7($attribute_7)
	{
	    $this->attribute_7 = $attribute_7;
	}

	/**
	 *
	 * @param $attribute_8
	 */
	public function setAttribute8($attribute_8)
	{
	    $this->attribute_8 = $attribute_8;
	}

	/**
	 *
	 * @param $attribute_9
	 */
	public function setAttribute9($attribute_9)
	{
	    $this->attribute_9 = $attribute_9;
	}

	/**
	 *
	 * @param $attribute_10
	 */
	public function setAttribute10($attribute_10)
	{
	    $this->attribute_10 = $attribute_10;
	}

	/**
	 *
	 * @param $properties
	 */
	public function setProperties($properties)
	{
	    $this->properties = $properties;
	}

	/**
	 *
	 * @param $deeplink_onlineshop
	 */
	public function setDeeplinkOnlineshop($deeplink_onlineshop)
	{
	    $this->deeplink_onlineshop = $deeplink_onlineshop;
	}

	/**
	 *
	 * @param mixed[] $related_item_numbers
	 */
	public function setRelatedItemNumbers($related_item_numbers) {
		$this->related_item_numbers = $related_item_numbers;
	}

	public function setOptions($options) {
		if (empty($options)) {
			$this->options = null;
			return;
		}

		if (!is_array($options)) {
			$this->options = null;
			return;
		}

		foreach ($options as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateItemOption)) && !is_array($element)) {
				unset($options[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateItemOption($element);
			}
		}

		$this->options = $options;
	}

	public function setInputs($inputs) {
		if (empty($inputs)) {
			$this->inputs = null;
			return;
		}

		if (!is_array($inputs)) {
			$this->inputs = null;
			return;
		}

		foreach ($inputs as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateItemInput)) && !is_array($element)) {
				unset($options[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateItemInput($element);
			}
		}

		$this->inputs = $inputs;
	}

	/**
	 *
	 * @return
	 */
	public function getItemNumber()
	{
	    return $this->item_number;
	}

	/**
	 *
	 * @return
	 */
	public function getName()
	{
	    return $this->name;
	}

	/**
	 *
	 * @return
	 */
	public function getCurrency()
	{
	    return $this->currency;
	}

	/**
	 *
	 * @return
	 */
	public function getTaxPercent()
	{
	    return $this->tax_percent;
	}

	/**
	 *
	 * @return
	 */
	public function getUnitAmountWithTax()
	{
	    return $this->unit_amount_with_tax;
	}

	/**
	 *
	 * @return
	 */
	public function getOldUnitAmountWithTax()
	{
	    return $this->old_unit_amount_with_tax;
	}

	/**
	 *
	 * @return
	 */
	public function getCategoryNumbers()
	{
	    return $this->category_numbers;
	}

	/**
	 *
	 * @return
	 */
	public function getItemNumberPublic()
	{
	    return $this->item_number_public;
	}

	/**
	 *
	 * @return
	 */
	public function getParentItemNumber()
	{
	    return $this->parent_item_number;
	}

	/**
	 *
	 * @return
	 */
	public function getManufacturer()
	{
	    return $this->manufacturer;
	}

	/**
	 *
	 * @return
	 */
	public function getManufacturerNumber()
	{
	    return $this->manufacturer_number;
	}

	/**
	 *
	 * @return
	 */
	public function getDescription()
	{
	    return $this->description;
	}

	/**
	 *
	 * @return
	 */
	public function getShippingCostsPerOrder()
	{
	    return $this->shipping_costs_per_order;
	}

	/**
	 *
	 * @return
	 */
	public function getShippingCostsPerUnit()
	{
	    return $this->shipping_costs_per_unit;
	}

	/**
	 *
	 * @return
	 */
	public function getIsFreeShipping()
	{
	    return $this->is_free_shipping;
	}

	/**
	 *
	 * @return
	 */
	public function getMsrp()
	{
	    return $this->msrp;
	}

	/**
	 *
	 * @return
	 */
	public function getTags()
	{
	    return $this->tags;
	}

	/**
	 *
	 * @return
	 */
	public function getAgeRating()
	{
	    return $this->age_rating;
	}

	/**
	 *
	 * @return
	 */
	public function getWeight()
	{
	    return $this->weight;
	}

	/**
	 *
	 * @return
	 */
	public function getEan()
	{
	    return $this->ean;
	}

	/**
	 *
	 * @return
	 */
	public function getIsbn()
	{
	    return $this->isbn;
	}

	/**
	 *
	 * @return
	 */
	public function getPzn()
	{
	    return $this->pzn;
	}

	/**
	 *
	 * @return
	 */
	public function getAmountInfoText()
	{
	    return $this->amount_info_text;
	}

	/**
	 *
	 * @return
	 */
	public function getInternalOrderInfo()
	{
	    return $this->internal_order_info;
	}

	/**
	 *
	 * @return
	 */
	public function getUseStock()
	{
	    return $this->use_stock;
	}

	/**
	 *
	 * @return
	 */
	public function getStockQuantity()
	{
	    return $this->stock_quantity;
	}

	/**
	 *
	 * @return
	 */
	public function getIsHighlight()
	{
	    return $this->is_highlight;
	}

	/**
	 *
	 * @return
	 */
	public function getHighlightOrderIndex()
	{
	    return $this->highlight_order_index;
	}

	/**
	 *
	 * @return
	 */
	public function getIsAvailable()
	{
	    return $this->is_available;
	}

	/**
	 *
	 * @return
	 */
	public function getAvailableText()
	{
	    return $this->available_text;
	}

	/**
	 *
	 * @return
	 */
	public function getHasImage()
	{
	    return $this->has_image;
	}

	/**
	 *
	 * @return
	 */
	public function getImageCount()
	{
	    return $this->image_count;
	}

	/**
	 *
	 * @return
	 */
	public function getIsMarketplace()
	{
	    return $this->is_marketplace;
	}

	/**
	 *
	 * @return
	 */
	public function getIsActive()
	{
	    return $this->is_active;
	}

	/**
	 *
	 * @return
	 */
	public function getIsAutoUpdate()
	{
	    return $this->is_auto_update;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute1()
	{
	    return $this->attribute_1;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute2()
	{
	    return $this->attribute_2;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute3()
	{
	    return $this->attribute_3;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute4()
	{
	    return $this->attribute_4;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute5()
	{
	    return $this->attribute_5;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute6()
	{
	    return $this->attribute_6;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute7()
	{
	    return $this->attribute_7;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute8()
	{
	    return $this->attribute_8;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute9()
	{
	    return $this->attribute_9;
	}

	/**
	 *
	 * @return
	 */
	public function getAttribute10()
	{
	    return $this->attribute_10;
	}

	/**
	 *
	 * @return
	 */
	public function getProperties()
	{
	    return $this->properties;
	}

	/**
	 *
	 * @return
	 */
	public function getDeeplinkOnlineshop()
	{
	    return $this->deeplink_onlineshop;
	}

	/**
	 *
	 */
	public function getRelatedItemNumbers() {
		return $this->related_item_numbers;
	}

	public function getOptions() {
		return $this->options;
	}

	public function getInputs() {
		return $this->inputs;
	}
}

/**
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
class ShopgateItemOption extends ShopgateContainer {
	protected $option_number = null;
	protected $name = null;
	protected $order_index = null;
	protected $option_values = array();

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitItemOption($this);
	}

	/**
	 *
	 * @param $optionNumber
	 */
	public function setOptionNumber($option_number)
	{
	    $this->option_number = $option_number;
	}

	/**
	 *
	 * @param $name
	 */
	public function setName($name)
	{
	    $this->name = $name;
	}

	/**
	 *
	 * @param $orderIndex
	 */
	public function setOrderIndex($order_index)
	{
	    $this->order_index = $order_index;
	}

	public function setOptionValues($option_values) {

		if (empty($option_values)) {
			$this->option_values = null;
			return;
		}

		if (!is_array($option_values)) {
			$this->option_values = null;
			return;
		}

		foreach ($option_values as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateItemOptionValue)) && !is_array($element)) {
				unset($option_values[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateItemOptionValue($element);
			}
		}

		$this->option_values = $option_values;
	}

	/**
	 *
	 * @return
	 */
	public function getOptionNumber()
	{
	    return $this->option_number;
	}

	/**
	 *
	 * @return
	 */
	public function getName()
	{
	    return $this->name;
	}

	/**
	 *
	 * @return
	 */
	public function getOrderIndex()
	{
	    return $this->order_index;
	}

	public function getOptionValues() {
		return $this->option_values;
	}
}

/**
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
class ShopgateItemOptionValue extends ShopgateContainer {
	protected $value_number = null;
	protected $value = null;
	protected $order_index = null;
	protected $additional_amount_with_tax = null;

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitItemOptionValue($this);
	}

	/**
	 *
	 * @param $value_number
	 */
	public function setValueNumber($value_number)
	{
	    $this->value_number = $value_number;
	}

	/**
	 *
	 * @param $value
	 */
	public function setValue($value)
	{
	    $this->value = $value;
	}

	/**
	 *
	 * @param $order_index
	 */
	public function setOrderIndex($order_index)
	{
	    $this->order_index = $order_index;
	}

	/**
	 *
	 * @param $additional_amount_with_tax
	 */
	public function setAdditionalAmountWithTax($additional_amount_with_tax)
	{
	    $this->additional_amount_with_tax = $additional_amount_with_tax;
	}

	/**
	 *
	 * @return
	 */
	public function getValueNumber()
	{
	    return $this->value_number;
	}

	/**
	 *
	 * @return
	 */
	public function getValue()
	{
	    return $this->value;
	}

	/**
	 *
	 * @return
	 */
	public function getOrderIndex()
	{
	    return $this->order_index;
	}

	/**
	 *
	 * @return
	 */
	public function getAdditionalAmountWithTax()
	{
	    return $this->additional_amount_with_tax;
	}
}

/**
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
class ShopgateItemInput extends ShopgateContainer {
	const INPUT_TYPE_TEXT = "text";
	const INPUT_TYPE_IMAGE = "image";

	protected $input_number = null;
	protected $type = null;
	protected $additional_amount_with_tax = null;
	protected $label = null;
	protected $info_text = null;
	protected $is_required = null;

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitItemInput($this);
	}

	/**
	 *
	 * @param $value
	 */
	public function setInputNumber($value)
	{
	    $this->input_number = $value;
	}

	/**
	 *
	 * @param $type
	 */
	public function setType($type)
	{
	    $this->type = $type;
	}

	/**
	 *
	 * @param $additional_amount_with_tax
	 */
	public function setAdditionalAmountWithTax($additional_amount_with_tax)
	{
	    $this->additional_amount_with_tax = $additional_amount_with_tax;
	}

	/**
	 *
	 * @param $label
	 */
	public function setLabel($label)
	{
	    $this->label = $label;
	}

	/**
	 *
	 * @param $info_text
	 */
	public function setInfoText($info_text)
	{
	    $this->info_text = $info_text;
	}

	/**
	 *
	 * @param $is_required
	 */
	public function setIsRequired($is_required)
	{
	    $this->is_required = $is_required;
	}

	/**
	 *
	 * @return
	 */
	public function getInputNumber()
	{
	    return $this->input_number;
	}
	
	/**
	 *
	 * @return
	 */
	public function getType()
	{
	    return $this->type;
	}

	/**
	 *
	 * @return
	 */
	public function getAdditionalAmountWithTax()
	{
	    return $this->additional_amount_with_tax;
	}

	/**
	 *
	 * @return
	 */
	public function getLabel()
	{
	    return $this->label;
	}

	/**
	 *
	 * @return
	 */
	public function getInfoText()
	{
	    return $this->info_text;
	}

	/**
	 *
	 * @return
	 */
	public function getIsRequired()
	{
	    return $this->is_required;
	}
}