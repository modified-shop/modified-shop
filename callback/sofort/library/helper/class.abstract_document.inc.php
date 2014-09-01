<?php
/// \cond
/**
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-09-05 14:27:56 +0200 (Wed, 05 Sep 2012) $
 * @version SofortLib 1.3.0  $Id: class.abstract_document.inc.php 3751 2012-10-10 08:36:20Z gtb-modified $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 * @internal
 *
 */
class PnagAbstractDocument {
	
	/**
	 * Holds all items associated with this kind of document (instance might be invoice, bank transfer, ...)
	 * @var array
	 */
	protected $_items = array();
	
	/**
	 *
	 * Holds the instance of PnagCustomer associated with this kind of document
	 * @var object
	 */
	protected $_customer = null;
	
	/**
	 *
	 * Holds the currency associated with this kind of document
	 * @var String
	 */
	protected $_currency = 'EUR';
	
	/**
	 * Holds the amount/total of this kind of document
	 * @var float
	 */
	protected $_amount = 0.00;
	
	/**
	 *
	 * Holds the refunded amount/total
	 * @var float
	 */
	protected $_amountRefunded = 0.00;
	
	
	/**
	 * puts the given article into $this->_items
	 * should only be used for the articles from the shopsystem
	 * @todo change VAT according to legislation
	 */
	public function setItem($itemId, $productNumber = 0, $productType = 0, $title = '', $description = '', $quantity = 0, $unitPrice = '', $tax = '19') {
		array_push($this->_items, new PnagArticle($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax));
		return $this;
	}
	
	
	/**
	 * Getter for items
	 * @return array $this->_items
	 */
	public function getItems() {
		return $this->_items;
	}
	
	
	/**
	 * searches in the before given shoparticles for the highest tax and returns it
	 * @return int/float - highest found taxvalue e.g. 0 or 7 or 19...
	 */
	public function getHighestShoparticleTax() {
		$highestTax = 0;
		
		foreach ($this->_items as $item) {
			if ($item->getTax() > $highestTax) {
				$highestTax = $item->getTax();
			}
		}
		
		return $highestTax;
	}
	
	
	/**
	 * Set the customer's credentials
	 * @param $name	string
	 * @param $lastname string
	 * @param $firstname string
	 * @param $company string
	 * @param $csID string customer id in shop
	 * @param $vatId string - customer's VAT ID
	 * @param $shopId - shop's ID
	 * @param $Id
	 * @param $cIP
	 * @param $streetAddress string
	 * @param $suburb string
	 * @param $city string
	 * @param $postcode string
	 * @param $state string
	 * @param $country	string
	 * @param $formatId string
	 * @param $telephone string
	 * @param $emailAddress string
	 */
	public function setCustomer($name = '', $lastname = '', $firstname = '', $company = '', $csID = '', $vatId = '', $shopId = '', $Id = '', $cIP = '', $streetAddress = '', $suburb = '', $city = '', $postcode = '', $state = '', $country = '', $formatId = '', $telephone = '', $emailAddress = '') {
		$this->_customer = new PnagCustomer($name, $lastname, $firstname, $company, $csID, $vatId, $shopId, $Id, $cIP, $streetAddress, $suburb, $city, $postcode, $state, $country, $formatId, $telephone, $emailAddress);
		return $this;
	}
	
	
	/**
	 *
	 * Setter for currency
	 * @param $currency string
	 */
	public function setCurrency($currency) {
		$this->_currency = $currency;
		return $this;
	}
	
	
	/**
	 * Calculate the total amount
	 * @private
	 * @return $object
	 */
	private function _calcAmount() {
		$this->_amount = 0.0;
		foreach($this->_items as $item) {
			$this->_amount += $item->unitPrice * $item->quantity;
		}
		return $this;
	}
	
	
	/**
	 * get the total amount
	 */
	public function getAmount() {
		return $this->_amount;
	}
}


/**
 *
 * Data object that encapsulates user's data
 * $Date: 2012-09-05 14:27:56 +0200 (Wed, 05 Sep 2012) $
 * $ID$
 *
 */
class PnagCustomer {
	
	public $name = '';
	
	public $lastname = '';
	
	public $firstname = '';
	
	public $company = '';
	
	public $csID = '';
	
	public $vatId = '';
	
	public $shopId = '';
	
	public $Id = '';
	
	public $cIP = '';
	
	public $streetAddress = '';
	
	public $suburb = '';
	
	public $city = '';
	
	public $postcode = '';
	
	public $state = '';
	
	public $country = '';
	
	public $formatId = '';
	
	public $telephone = '';
	
	public $emailAddress = '';
	
	
	/**
	 * Set the customer's credentials
	 * @param $name	string
	 * @param $lastname string
	 * @param $firstname string
	 * @param $company string
	 * @param $csID string customer id in shop
	 * @param $vatId string - customer's VAT ID
	 * @param $shopId - shop's ID
	 * @param $Id
	 * @param $cIP
	 * @param $streetAddress string
	 * @param $suburb string
	 * @param $city string
	 * @param $postcode string
	 * @param $state string
	 * @param $country	string
	 * @param $formatId string
	 * @param $telephone string
	 * @param $emailAddress string
	 */
	public function PnagCustomer($name = '', $lastname = '', $firstname = '', $company = '', $csID = '', $vatId = '', $shopId = '', $Id = '', $cIP = '', $streetAddress = '', $suburb = '', $city = '', $postcode = '', $state = '', $country = '', $formatId = '', $telephone = '', $emailAddress = '') {
		$this->name = $name;
		$this->lastname = $lastname;
		$this->firstname = $firstname;
		$this->company = $company;
		$this->csID = $csID;
		$this->vatId = $vatId;
		$this->shopId = $shopId;
		$this->Id = $Id;
		$this->cIP = $cIP;
		$this->street_address = $streetAddress;
		$this->suburb = $suburb;
		$this->city = $city;
		$this->postcode = $postcode;
		$this->state = $state;
		$this->country = $country;
		$this->formatId = $formatId;
		$this->telephone = $telephone;
		$this->emailAddress = $emailAddress;
	}
}


/**
 *
 * Data object that encapsulates article's data
 * $Date: 2012-09-05 14:27:56 +0200 (Wed, 05 Sep 2012) $
 * $ID$
 *
 */
class PnagArticle {
	
	public $itemId = '';
	
	public $productNumber = '';
	
	public $productType = '';
	
	public $title = '';
	
	public $description = '';
	
	public $quantity = '';
	
	public $unitPrice = '';
	
	public $tax = '';
	
	
	/**
	 * Constructor
	 * @param $itemId int
	 * @param $productNumber string
	 * @param $productType string
	 * @param $title string
	 * @param $description string
	 * @param $quantity int
	 * @param $unitPrice float
	 * @param $tax float
	 */
	public function __construct($itemId, $productNumber, $productType, $title, $description, $quantity, $unitPrice, $tax) {
		$this->itemId = $itemId;
		$this->productNumber = $productNumber;
		$this->productType = $productType;
		$this->title = $title;
		$this->description = $description;
		$this->quantity = $quantity;
		$this->unitPrice = $unitPrice;
		$this->tax = $tax;
	}
	
	
	public function getItemId () {
		return $this->itemId;
	}
	
	
	public function getQuantity() {
		return $this->quantity;
	}
	
	
	public function setQuantity($quantity) {
		$this->quantity = $quantity;
	}
	
	
	public function getUnitPrice() {
		return $this->unitPrice;
	}
	
	
	public function setUnitPrice($unitPrice) {
		$this->unitPrice = $unitPrice;
	}
	
	
	public function getTitle() {
		return $this->title;
	}
	
	
	public function getTax() {
		return $this->tax;
	}
	
	
	public function setTax($value) {
		$this->tax = $value;
	}
	
	
	public function setProductNumber($productNumber) {
		$this->productNumber = $productNumber;
	}
	
	
	public function getProductNumber() {
		return $this->productNumber;
	}
	
	
	public function setDescription($description) {
		$this->description = $description;
	}
	
	
	public function getDescription() {
		return $this->description;
	}
}
/// \endcond
?>