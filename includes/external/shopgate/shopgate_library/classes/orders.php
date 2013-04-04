<?php
class ShopgateOrder extends ShopgateContainer {
	const SHOPGATE = "SHOPGATE";
	const PREPAY = "PREPAY";
	const CC = "CC";
	const DT_CC = "DT_CC";
	const INVOICE = "INVOICE";
	const DEBIT = "DEBIT";
	const COD = "COD";
	const PAYPAL = "PAYPAL";
	const KLARNA_INV = "KLARNA_INV";
	const BILLSAFE = "BILLSAFE";


	protected $order_number;
	protected $customer_number;

	protected $external_order_number;
	protected $external_order_id;

	protected $external_customer_number;
	protected $external_customer_id;

	protected $mail;
	protected $phone;
	protected $mobile;

	protected $confirm_shipping_url;

	protected $created_time;

	protected $payment_method;
	protected $payment_group;

	protected $is_paid;

	protected $payment_time;
	protected $payment_transaction_number;
	protected $payment_infos;

	protected $is_shipping_blocked;
	protected $is_shipping_completed;
	protected $shipping_completed_time;

	protected $amount_items;
	protected $amount_shipping;
	protected $amount_shop_payment;
	protected $payment_tax_percent;
	protected $amount_shopgate_payment;
	protected $amount_complete;
	protected $currency;
	protected $is_test;
	protected $is_storno;
	protected $is_customer_invoice_blocked;

	protected $invoice_address;
	protected $delivery_address;

	protected $items;

	protected $delivery_notes;

	/**
	 * Set to true if shipping information should be updated.
	 *
	 * @var bool
	 */
	protected $update_shipping = false;

	/**
	 * Set to true if payment information should be updated.
	 *
	 * @var bool
	 */
	protected $update_payment = false;


	/**********
	 * Setter *
	 **********/

	/**
	 * The Shopgate order number
	 *
	 * Format: Exact 10 Digits
	 * Sample: 1012001234
	 *
	 * @param string $value
	 */
	public function setOrderNumber($value) { $this->order_number = $value; }

	/**
	 * The customer number by shopgate
	 *
	 * Format: Exact 5 Digits
	 * Sample: 101234
	 *
	 * @param string $value
	 */
	public function setCustomerNumber($value) { $this->customer_number = $value; }

	/**
	 * The order number in your system
	 *
	 * @param string $value
	 */
	public function setExternalOrderNumber($value) { $this->external_order_number = $value; }

	/**
	 * The order id in your system
	 *
	 * @param string $value
	 */
	public function setExternalOrderId($value) { $this->external_order_id = $value; }

	/**
	 * The customer number in your system
	 *
	 * @param string $value
	 */
	public function setExternalCustomerNumber($value) { $this->external_customer_number = $value; }

	/**
	 * The customer id in your system
	 *
	 * @param string $value
	 */
	public function setExternalCustomerId($value) { $this->external_customer_id = $value; }

	/**
	 * The eMail-Adress of the customer
	 *
	 * @param string $value
	 */
	public function setMail($value) { $this->mail = $value; }

	/**
	 * The phone-number of the cutsomer
	 *
	 * Sample: +49123456789
	 *
	 * @param string $value
	 */
	public function setPhone($value) { $this->phone = $value; }

	/**
	 * The mobile-number of the cutsomer
	 *
	 * Sample: +49123456789
	 *
	 * @param string $value
	 */
	public function setMobile($value) { $this->mobile = $value; }

	/**
	 * The confirm shipping url to confirm the shipping manual
	 *
	 * @param string $value
	 */
	public function setConfirmShippingUrl($value) { $this->confirm_shipping_url = $value; }

	/**
	 * The DateTime when the order was created
	 *
	 * If $format is empty, the default DateTime returne in ISO-8601 (date('c');)
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string $value
	 */
	public function setCreatedTime($value) { $this->created_time = $value; }

	/**
	 * The payment group for the order
	 *
	 * Sample: <ul><li>SHOPGATE</li><li>PREPAY</li><li>CC</li><li>INVOICE</li><li>DEBIT</li><li>COD</li><li>PAYPAL</li></ul>
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @param string $value
	 */
	public function setPaymentGroup($value) {
		$this->payment_group = $value;
	}

	/**
	 * The payment method for the order
	 *
	 * Sample: <ul><li>SHOPGATE</li><li>PREPAY</li><li>DT_CC</li><li>KLARNA_INV</li><li>BILLSAFE</li><li>DEBIT</li><li>COD</li><li>PAYPAL</li></ul>
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @param string $value
	 */
	public function setPaymentMethod($value) { $this->payment_method = $value; }

	/**
	 * Is the order is payed
	 *
	 * @param bool $value
	 */
	public function setIsPaid($value) { $this->is_paid = $value; }

	/**
	 * The Time when the order was payed
	 *
	 * If $format is empty, the default DateTime returne in ISO-8601 (date('c');)
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string $value
	 */
	public function setPaymentTime($value) {
		$this->payment_time = $value;
	}

	/**
	 * The Transactioncode for some paymentproviders
	 *
	 * @param string $value
	 */
	public function setPaymentTransactionNumber($value) { $this->payment_transaction_number = $value; }

	/**
	 * @param mixed[] $value An array of additional information about the payment depending on the payment type
	 */
	public function setPaymentInfos($value) { $this->payment_infos = $value; }

	/**
	 * Is the shipping is blocked
	 *
	 * @param string $value
	 */
	public function setIsShippingBlocked($value) { $this->is_shipping_blocked = $value; }

	/**
	 * Is the Shipping is completed
	 */
	public function setIsShippingCompleted($value) { $this->is_shipping_completed = $value; }

	/**
	 * The Time when the Shipping was set completed
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string $value
	 */
	public function setShippingCompletedTime($value) {
		$this->shipping_completed_time = $value;
	}

	/**
	 * The full amount of Items
	 *
	 * @param float $value
	 */
	public function setAmountItems($value) { $this->amount_items = $value; }

	/**
	 * The shipping price
	 *
	 * @param float $value
	 */
	public function setAmountShipping($value) { $this->amount_shipping = $value; }

	/**
	 * Amount for Shop Payment
	 *
	 * @param float $value
	 */
	public function setAmountShopPayment($value) { $this->amount_shop_payment = $value; }

	/**
	 * Tax Percent for AmountShopPayment or AmountShopgatePayment
	 *
	 * @param float $value
	 */
	public function setPaymentTaxPercent($value) { $this->payment_tax_percent = $value; }
	
	/**
	 * Amount for Shopgate Payment
	 *
	 * @param float $value
	 */
	public function setAmountShopgatePayment($value) { $this->amount_shopgate_payment = $value; }
	
	/**
	 * Complete amount for the order
	 *
	 * @param float $value
	 */
	public function setAmountComplete($value) { $this->amount_complete = $value; }

	/**
	 * The currency for this order
	 *
	 * The currency ISO-Code from ISO-4217
	 *
	 * Sample: <ul><li>EUR</li><li>CHF</li></ul>
	 *
	 * @see http://de.wikipedia.org/wiki/ISO_4217
	 * @param string $value
	 */
	public function setCurrency($value) { $this->currency = $value; }

	/**
	 * If this flag is set to 1, the Order is a Test
	 *
	 * @param bool $value
	 */
	public function setIsTest($value) { $this->is_test = $value; }

	/**
	 * If this flag is set to 1 the order is cancelled
	 *
	 * @param bool $value
	 */
	public function setIsStorno($value) { $this->is_storno = $value; }

	/**
	 * If this flag is set to 1 the invoice is already sent to the customer. The merchant must not send the invoice
	 *
	 * @param bool $value
	 */
	public function setIsCustomerInvoiceBlocked($value) { $this->is_customer_invoice_blocked = $value; }

	/**
	 * If this flag is set to 1 the payment of the order must be updated
	 *
	 * @param bool $value
	 */
	public function setUpdatePayment($value) {
		$this->update_payment = $value;
	}

	/**
	 * If this flag is set to 1 the shipping of the order must be updated
	 *
	 * @param bool $value
	 */
	public function setUpdateShipping($value) {
		$this->update_shipping = $value;
	}


	/**
	 * The invoice address of the customer
	 *
	 * @param ShopgateAddress|mixed[] $value
	 */
	public function setInvoiceAddress($value) {
		if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
			$this->invoice_address = null;
			return;
		}

		if (is_array($value)) {
			$value = new ShopgateAddress($value);
		}

		$this->invoice_address = $value;
	}

	/**
	 * The delivery address of the customer
	 *
	 * @param ShopgateAddress|mixed[] $value
	 */
	public function setDeliveryAddress($value) {
		if (!is_object($value) && !($value instanceof ShopgateAddress) && !is_array($value)) {
			$this->delivery_address = null;
			return;
		}

		if (is_array($value)) {
			$value = new ShopgateAddress($value);
		}

		$this->delivery_address = $value;
	}

	/**
	 * The list of itmes in the order
	 *
	 * @param ShopgateOrderItem[]|mixed[][] $value
	 */
	public function setItems($value) {
		if (!is_array($value)) {
			$this->items = null;
			return;
		}

		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItem)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateOrderItem($element);
			}
		}

		$this->items = $value;
	}

	/**
	 * The list of delivery Notes of the order
	 *
	 * @param ShopgateDeliveryNote[]|mixed[][] $value
	 */
	public function setDeliveryNotes($value) {
		if (empty($value)) {
			$this->delivery_notes = null;
			return;
		}

		if (!is_array($value)) {
			$this->delivery_notes = null;
			return;
		}

		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateDeliveryNote)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateDeliveryNote($element);
			}
		}

		$this->delivery_notes = $value;
	}


	/**********
	 * Getter *
	 **********/

	/**
	 * The Shopgate order number
	 *
	 * Format: Exact 10 Digits
	 * Sample: 1012001234
	 *
	 * @return string
	 */
	public function getOrderNumber() { return $this->order_number; }

	/**
	 * The customer number by shopgate
	 *
	 * Format: Exact 5 Digits
	 * Sample: 101234
	 *
	 * @return string
	 */
	public function getCustomerNumber() { return $this->customer_number; }

	/**
	 * The order number in your system
	 *
	 *  @return string
	 */
	public function getExternalOrderNumber() { return $this->external_order_number; }

	/**
	 * The order id in your system
	 *
	 * @return string
	 */
	public function getExternalOrderId() { return $this->external_order_id; }

	/**
	 * The customer number in your system
	 *
	 * @return string
	 */
	public function getExternalCustomerNumber() { return $this->external_customer_number; }

	/**
	 * The customer id in your system
	 *
	 * @return string
	 */
	public function getExternalCustomerId() { return $this->external_customer_id ; }

	/**
	 * The eMail-Adress of the customer
	 *
	 * @return string
	 */
	public function getMail() { return $this->mail; }

	/**
	 * The phone-number of the cutsomer
	 *
	 * Sample: +49123456789
	 *
	 * @return string
	 */
	public function getPhone() { return $this->phone; }

	/**
	 * The mobile-number of the cutsomer
	 *
	 * Sample: +49123456789
	 *
	 * @return string
	 */
	public function getMobile() { return $this->mobile; }

	/**
	 * The confirm shipping url to confirm the shipping manual
	 *
	 *  @return string
	 */
	public function getConfirmShippingUrl() { return $this->confirm_shipping_url ; }

	/**
	 * The DateTime when the order was created
	 *
	 * If $format is empty, the default DateTime returne in ISO-8601 (date('c');)
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string format
	 * @return string
	 */
	public function getCreatedTime($format = "") {
		$time = $this->created_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}

		return $time;
	}

	/**
	 * The payment method for the order
	 *
	 * Sample: <ul><li>SHOPGATE</li><li>PREPAY</li><li>DT_CC</li><li>BILLSAFE</li><li>KLARNA_INV</li><li>DEBIT</li><li>COD</li><li>PAYPAL</li></ul>
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @return string
	 */
	public function getPaymentMethod() {
		return $this->payment_method;
	}

	/**
	 * The payment group for the order
	 *
	 * Sample: <ul><li>SHOPGATE</li><li>PREPAY</li><li>CC</li><li>INVOICE</li><li>DEBIT</li><li>COD</li><li>PAYPAL</li></ul>
	 *
	 * @see http://wiki.shopgate.com/Merchant_API_payment_infos/
	 * @return string
	 */
	public function getPaymentGroup() { return $this->payment_group; }

	/**
	 * Is the order is payed
	 *
	 * @return bool
	 */
	public function getIsPaid() { return (bool) $this->is_paid; }

	/**
	 * The Time when the order was payed
	 *
	 * If $format is empty, the default DateTime returne in ISO-8601 (date('c');)
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string format
	 * @return string
	 */
	public function getPaymentTime($format="") {
		$time = $this->payment_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}

		return $time;
	}

	/**
	 * The Transactioncode for some paymentproviders
	 *
	 * @return string
	 */
	public function getPaymentTransactionNumber() { return $this->payment_transaction_number; }

	/**
	 * Information about the selected payment type (like e.g. bank account number)
	 *
	 * @return mixed[]
	 */
	public function getPaymentInfos() { return $this->payment_infos; }

	/**
	 * Is the shipping is blocked
	 *
	 * @return bool
	 */
	public function getIsShippingBlocked() { return (bool) $this->is_shipping_blocked; }

	/**
	 * Is the Shipping is completed
	 */
	public function getIsShippingCompleted() { return (bool) $this->is_shipping_completed; }

	/**
	 * The Time when the Shipping was set completed
	 *
	 * If $format is empty, the default DateTime returne in ISO-8601 (date('c');)
	 *
	 * Format: ISO-8601 - 2012-02-08T009:20:25+01:00
	 *
	 * @see http://www.php.net/manual/de/function.date.php
	 * @see http://en.wikipedia.org/wiki/ISO_8601
	 * @param string format
	 * @return string
	 */
	public function getShippingCompletedTime($format='') {
		$time = $this->shipping_completed_time;
		if(!empty($format)) {
			$timestamp = strtotime($time);
			$time = date($format, $timestamp);
		}

		return $time;
	}

	/**
	 * The full amount of Items
	 *
	 * @return float
	 */
	public function getAmountItems() { return $this->amount_items; }

	/**
	 * The shipping price
	 *
	 * @return float
	 */
	public function getAmountShipping() { return $this->amount_shipping; }

	/**
	 * Amount for Shop Payment
	 *
	 * @return float
	 */
	public function getAmountShopPayment() { return $this->amount_shop_payment; }

	/**
	 * Tax Percent for AmountShopPayment or AmountShopgatePayment
	 *
	 * @return float
	 */
	public function getPaymentTaxPercent() { return $this->payment_tax_percent; }

	/**
	 * Amount for Payment
	 *
	 * @return float
	 */
	public function getAmountShopgatePayment() { return $this->amount_shopgate_payment; }

	/**
	 * Complete amount for the order
	 *
	 * @return float
	 */
	public function getAmountComplete() { return $this->amount_complete; }

	/**
	 * The currency for this order
	 *
	 * The currency ISO-Code from ISO-4217
	 *
	 * Sample: <ul><li>EUR</li><li>CHF</li></ul>
	 *
	 * @see http://de.wikipedia.org/wiki/ISO_4217
	 * @return string
	 */
	public function getCurrency() { return $this->currency; }

	/**
	 * If this flag is set to 1, the Order is a Test
	 *
	 * @return bool
	 */
	public function getIsTest() { return (bool) $this->is_test; }

	/**
	 * If this flag is set to 1 the order is cancelled
	 *
	 * @return bool
	 */
	public function getIsStorno() { return (bool) $this->is_storno; }

	/**
	 * If this flag is set to 1 the invoice is already sent to the customer. The merchant must not send the invoice
	 *
	 * @return bool
	 */
	public function getIsCustomerInvoiceBlocked() { return (bool) $this->is_customer_invoice_blocked; }


	/**
	 * If this flag is set to 1 the payment of the order must be updated
	 *
	 * @return bool
	 */
	public function getUpdatePayment() {
		return (bool) $this->update_payment;
	}

	/**
	 * If this flag is set to 1 the shipping of the order must be updated
	 *
	 * @return bool
	 */
	public function getUpdateShipping() {
		return (bool) $this->update_shipping;
	}


	/**
	 * The invoice address of the customer
	 *
	 * @return ShopgateAddress
	 */
	public function getInvoiceAddress() { return $this->invoice_address; }

	/**
	 * The delivery address of the customer
	 *
	 * @return ShopgateAddress
	 */
	public function getDeliveryAddress() { return $this->delivery_address; }

	/**
	 * The list of itmes in the order
	 *
	 * @return ShopgateOrderItem[]
	 */
	public function getItems() { return $this->items; }

	/**
	 * The list of delivery Notes of the order
	 *
	 * @return ShopgateDeliveryNote[]
	 */
	public function getDeliveryNotes() { return $this->delivery_notes; }

	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrder($this);
	}
}

class ShopgateOrderItem extends ShopgateContainer {
	protected $item_number;

	protected $quantity;

	protected $name;

	protected $unit_amount;
	protected $unit_amount_with_tax;

	protected $tax_percent;

	protected $currency;

	protected $internal_order_info;

	protected $options = array();

	protected $inputs = array();


	/**********
	 * Setter *
	 **********/

	/**
	 * Sets the name value
	 *
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}

	/**
	 * Sets the item_number value
	 *
	 * @param string $value
	 */
	public function setItemNumber($value) {
		$this->item_number = $value;
	}

	/**
	 * Sets the unit_amount value
	 *
	 * @param string $value
	 */
	public function setUnitAmount($value) {
		$this->unit_amount = $value;
	}

	/**
	 * Sets the unit_amount_with_tax value
	 *
	 * @param float $value
	 */
	public function setUnitAmountWithTax($value) {
		$this->unit_amount_with_tax = $value;
	}

	/**
	 * Sets the quantity value
	 *
	 * @param int $value
	 */
	public function setQuantity($value) {
		$this->quantity = $value;
	}

	/**
	 * Sets the tax_percent value
	 *
	 * @param float $value
	 */
	public function setTaxPercent($value) {
		$this->tax_percent = $value;
	}

	/**
	 * Sets the currency value
	 *
	 * @param string $value
	 */
	public function setCurrency($value) {
		$this->currency = $value;
	}

	/**
	 * Sets the internal_order_info value
	 *
	 * @param string $value
	 */
	public function setInternalOrderInfo($value) { $this->internal_order_info = $value; }

	/**
	 * Sets the options value
	 *
	 * @param ShopgateOrderItemOption[]|mixed[][] $value
	 */
	public function setOptions($value) {
		if (empty($value) || !is_array($value)) {
			$this->options = array();
			return;
		}

		// convert sub-arrays into ShopgateOrderItemOption objects if necessary
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItemOption)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}

			if (is_array($element)) {
				$element = new ShopgateOrderItemOption($element);
			}
		}

		$this->options = $value;
	}

	/**
 	 * Sets the inputs value
 	 *
 	 * @param ShopgateOrderItemInput[]|mixed[][] $value
	 */
	public function setInputs($value) {
		if (empty($value) || !is_array($value)) {
			$this->inputs = array();
			return;
		}
		
		// convert sub-arrays into ShopgateOrderItemInputs objects if necessary
		foreach ($value as $index => &$element) {
			if ((!is_object($element) || !($element instanceof ShopgateOrderItemInput)) && !is_array($element)) {
				unset($value[$index]);
				continue;
			}
			
			if (is_array(($element))) {
				$element = new ShopgateOrderItemInput($element);
			}
		}
		
		$this->inputs = $value;
	}


	/**********
	 * Getter *
	 **********/

	/**
	 * Returns the name value
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the item_number value
	 *
	 * @return string
	 */
	public function getItemNumber() {
		return $this->item_number;
	}

	/**
	 * Returns the unit_amount value
	 *
	 * @return float
	 */
	public function getUnitAmount() {
		return $this->unit_amount;
	}

	/**
	 * Returns the unit_amount_with_tax value
	 *
	 * @return float
	 */
	public function getUnitAmountWithTax() {
		return $this->unit_amount_with_tax;
	}

	/**
	 * Returns the quantity value
	 *
	 * @return int
	 */
	public function getQuantity() {
		return $this->quantity;
	}

	/**
	* Returns the tax_percent value
	*
	* @return float
	*/
	public function getTaxPercent() {
		return $this->tax_percent;
	}

	/**
	 * Returns the currency value
	 *
	 * @return string
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * Returns the internal_order_info value
	 *
	 * @return string
	 */
	public function getInternalOrderInfo() { return $this->internal_order_info; }

	/**
	 * Returns the options value
	 *
	 * @return ShopgateOrderItemOption[]
	 */
	public function getOptions() { return $this->options; }

	/**
	 * Returns the inputs value
	 *
	 * @param ShopgateOrderItemInputs[]
	 */
	public function getInputs() {
		return $this->inputs;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItem($this);
	}
}

class ShopgateOrderItemOption extends ShopgateContainer {
	protected $name;
	protected $value;
	protected $additional_amount_with_tax;
	protected $value_number;
	protected $option_number;


	/**********
	 * Setter *
	 **********/

	/**
	 * Sets the name value
	 *
	 * @param string $value
	 */
	public function setName($value) {
		$this->name = $value;
	}

	/**
	 * Sets the value value
	 *
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Sets the additional_amount_with_tax value
	 *
	 * @param string $value
	 */
	public function setAdditionalAmountWithTax($value) {
		$this->additional_amount_with_tax = $value;
	}

	/**
	 * Sets the value_number value
	 *
	 * @param string $value
	 */
	public function setValueNumber($value) {
		$this->value_number = $value;
	}

	/**
	 * Sets the option_number value
	 *
	 * @param string $value
	 */
	public function setOptionNumber($value) {
		$this->option_number = $value;
	}


	/**********
	 * Getter *
	 **********/

	/**
	 * Returns the name value
	 *
	 * @return String
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the value value
	 *
	 * @return String
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Returns the additional_amount_with_tax value
	 *
	 * @return int
	 */
	public function getAdditionalAmountWithTax() {
		return $this->additional_amount_with_tax;
	}

	/**
	 * Returns the value_number value
	 *
	 * @return String
	 */
	public function getValueNumber() {
		return $this->value_number;
	}

	/**
	 * Returns the option_number value
	 *
	 * @return String
	 */
	public function getOptionNumber() {
		return $this->option_number;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItemOption($this);
	}
}

class ShopgateOrderItemInput extends ShopgateContainer {
	protected $input_number;
	protected $type;
	protected $additional_amount_with_tax;
	protected $label;
	protected $user_input;
	protected $info_text;
	
	/**********
	 * Setter *
	 **********/
	
	public function setInputNumber($value) {
		$this->input_number = $value;
	}
	
	public function setType($value) {
		$this->type = $value;
	}
	
	public function setAdditionalAmountWithTax($value) {
		$this->additional_amount_with_tax = $value;
	}
	
	public function setLabel($value) {
		$this->label = $value;
	}
	
	public function setUserInput($value) {
		$this->user_input = $value;
	}
	
	public function setInfoText($value) {
		$this->info_text = $value;
	}
	
	/**********
	 * Getter *
	 **********/
	
	public function getInputNumber() {
		return $this->input_number;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getAdditionalAmountWithTax() {
		return $this->additional_amount_with_tax;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function getUserInput() {
		return $this->user_input;
	}
	
	public function getInfoText() {
		return $this->info_text;
	}
	
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderItemInput($this);
	}
}

class ShopgateDeliveryNote extends ShopgateContainer {
	const DHL = "DHL"; // DHL
	const DHLEXPRESS = "DHLEXPRESS"; // DHLEXPRESS
	const DP = "DP"; // Deutsche Post
	const DPD = "DPD"; // Deutscher Paket Dienst
	const FEDEX = "FEDEX"; // FedEx
	const GLS = "GLS"; // GLS
	const HLG = "HLG"; // Hermes
	const OTHER = "OTHER"; // Anderer Lieferant
	const TNT = "TNT"; // TNT
	const TOF = "TOF"; // Trnas-o-Flex
	const UPS = "UPS"; // UPS

	protected $shipping_service_id = ShopgateDeliveryNote::DHL;
	protected $tracking_number = "";
	protected $shipping_time = null;

	/**********
	 * Setter *
	 **********/

	/**
	 * Sets the shipping_service_id value
	 *
	 * @param string $value
	 */
	public function setShippingServiceId($value) {
		$this->shipping_service_id = $value;
	}

	/**
	 * Sets the tracking_number value
	 *
	 * @param string $value
	 */
	public function setTrackingNumber($value) {
		$this->tracking_number = $value;
	}

	/**
	 * Sets the tracking_number value
	 *
	 * @param string $value
	 */
	public function setShippingTime($value) {
		$this->shipping_time = $value;
	}


	/**********
	 * Getter *
	 **********/

	/**
	 * Returns the shipping_service_id value
	 *
	 * @return string
	 */
	public function getShippingServiceId() {
		return $this->shipping_service_id;
	}

	/**
	 * Returns the tracking_number value
	 *
	 * @return string
	 */
	public function getTrackingNumber() {
		return $this->tracking_number;
	}

	/**
	 * Returns the tracking_number value
	 *
	 * @return string
	 */
	public function getShippingTime() {
		return $this->shipping_time;
	}


	public function accept(ShopgateContainerVisitor $v) {
		$v->visitOrderDeliveryNote($this);
	}
}