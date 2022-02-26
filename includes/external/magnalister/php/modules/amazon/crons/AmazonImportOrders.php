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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

class AmazonImportOrders extends MagnaCompatibleImportOrders {
	
	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
		$this->gambioPropertiesEnabled = (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties');
	}
	
	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		
		// a random inconsistency appears...
		$keys['MwStFallback']['key'] = 'mwstfallback';
		
		$keys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '',
		);
		$keys['OrderStatusFba'] = array (
			'key' => 'orderstatus.fba',
			'default' => '',
		);
        $keys['OrderStatusMfnPrime'] = array (
            'key' => 'orderstatus.mfnprime',
            'default' => '',
        );
		
		$keys['ShippingMethodName']['default'] = 'amazon';
		$keys['PaymentMethodName']['default'] = 'amazon';
		
		$keys['ShippingMethodFBA'] = array (
			'key' => 'orderimport.fbashippingmethod',
			'default' => 'textfield',
		);
		$keys['ShippingMethodNameFBA'] = array (
			'key' => 'orderimport.fbashippingmethod.name',
			'default' => 'amazon',
		);
        $keys['ShippingMethodMFNPrime'] = array (
            'key' => 'orderimport.mfnprimeshippingmethod',
            'default' => 'textfield',
        );
        $keys['ShippingMethodNameMFNPrime'] = array (
            'key' => 'orderimport.mfnprimeshippingmethod.name',
            'default' => 'amazon',
        );
		$keys['PaymentMethodFBA'] = array (
			'key' => 'orderimport.fbapaymentmethod',
			'default' => 'textfield',
		);
		$keys['PaymentMethodNameFBA'] = array (
			'key' => 'orderimport.fbapaymentmethod.name',
			'default' => 'amazon',
		);
        $keys['AmazonPromotionsDiscountProductSKU'] = array (
            'key' => 'orderimport.amazonpromotionsdiscount.products_sku',
            'default' => '__AMAZON_DISCOUNT__',
        );
        $keys['AmazonPromotionsDiscountShippingSKU'] = array (
            'key' => 'orderimport.amazonpromotionsdiscount.shipping_sku',
            'default' => '__AMAZON_SHIPPING_DISCOUNT__',
        );
		return $keys;
	}
	
	protected function initConfig() {
		parent::initConfig();
		
		if ($this->config['ShippingMethodFBA'] == 'textfield') {
			$this->config['ShippingMethodFBA'] = trim($this->config['ShippingMethodNameFBA']);
		}
        if ($this->config['ShippingMethodMFNPrime'] == 'textfield') {
            $this->config['ShippingMethodMFNPrime'] = trim($this->config['ShippingMethodNameMFNPrime']);
        }
		if (empty($this->config['ShippingMethodFBA'])) {
			$k = $this->getConfigKeys();
			$this->config['ShippingMethodFBA'] = $k['ShippingMethodNameFBA']['default'];
		}
		if ($this->config['PaymentMethodFBA'] == 'textfield') {
			$this->config['PaymentMethodFBA'] = trim($this->config['PaymentMethodNameFBA']);
		}
		if (empty($this->config['PaymentMethodFBA'])) {
			$k = $this->getConfigKeys();
			$this->config['PaymentMethodFBA'] = $k['PaymentMethodNameFBA']['default'];
		}
	}

    protected function getPastTimeOffset() {
        return 60 * 60 * 24 * 14;
    }
	
	protected function getOrdersStatus() {
		return ($this->o['orderInfo']['FulfillmentChannel'] == 'AFN')
			? $this->config['OrderStatusFba']
			: $this->config['OrderStatusOpen'];
	}
	
	/**
	 * Returns the payment method for the current order.
	 * @return string
	 */
	protected function getPaymentMethod() {
		return ($this->o['orderInfo']['FulfillmentChannel'] == 'AFN')
			? $this->config['PaymentMethodFBA']
			: $this->config['PaymentMethod'];
	}
	
	/**
	 * Returns the shipping method for the current order.
	 * @return string
	 */
	protected function getShippingMethod() {
        switch ($this->o['orderInfo']['FulfillmentChannel']) {
            case 'AFN': {
                return $this->config['ShippingMethodFBA'];
            }
            case 'MFN-Prime': {
                return $this->config['ShippingMethodMFNPrime'];
            }
            default: {
                return $this->config['ShippingMethod'];
            }
        }
	}

	private function getMarketplaceTitle() {
		switch ($this->o['orderInfo']['FulfillmentChannel']) {
			case 'AFN':       return $this->marketplaceTitle.'FBA'; break;
			case 'MFN-Prime': return $this->marketplaceTitle.' Prime'; break;
			default:          return $this->marketplaceTitle; break;
		}
	}
	
	protected function generateOrderComment($blForce = false) {
		$comment = str_replace('GiftMessageText', ML_AMAZON_LABEL_GIFT_MESSAGE, $this->o['order']['comments']);
		if ($blForce || getDBConfigValue(array('general.order.information', 'val'), 0, true)) {
			$comment = trim(
				sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->getMarketplaceTitle())."\n".
				'AmazonOrderID: '.$this->getMarketplaceOrderID()."\n\n".
				$comment
			);
		}
		return $comment;
	}
	
	protected function generateOrdersStatusComment() {
		$comment = str_replace('GiftMessageText', ML_AMAZON_LABEL_GIFT_MESSAGE, $this->o['orderStatus']['comments']);
		$comment = trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP, $this->getMarketplaceTitle())."\n".
			'AmazonOrderID: '.$this->getMarketplaceOrderID()."\n\n".
			$comment
		);
		return $comment;
	}
	
	/**
	 * @return array
	 *     Associative array that will be stored serialized
	 *     in magnalister_orders.internaldata (Database)
	 */
	protected function doBeforeInsertMagnaOrder() {
		return array(
			'FulfillmentChannel' => $this->o['orderInfo']['FulfillmentChannel']
		);
	}
	
	protected function insertProduct() {
		$this->p['products_name'] = str_replace('GiftWrapType', ML_AMAZON_LABEL_GIFT_PAPER, $this->p['products_name']);

        /**
         * Amazon Discount and Shipping Discount - moved from old function -> doBeforeInsertOrdersProducts() to here insertProduct()
         * so when we import the product is uses correct vat and also reduces stock from shop
         */
        if ($this->p['products_model'] == '__AMAZON_DISCOUNT__') {
            $this->p['products_model'] = $this->config['AmazonPromotionsDiscountProductSKU'];
        }
        if ($this->p['products_model'] == '__AMAZON_SHIPPING_DISCOUNT__') {
            $this->p['products_model'] = $this->config['AmazonPromotionsDiscountShippingSKU'];
        }

		parent::insertProduct();
	}

	/*
	 * remove 'blacklisted-' from customer's e-mail address
	 * if configured so (not recommended)
	 */
	protected function insertCustomer() {
		if (!getDBConfigValue(array($this->marketplace . '.mailaddress.blacklist', 'val'), $this->mpID, true)) {
			if ($this->verbose) echo __FUNCTION__.": amazon.mailaddress.blacklist == false\n";
			$this->o['customer']['customers_email_address'] = str_replace('blacklisted-', '', $this->o['customer']['customers_email_address']);
		}
		return parent::insertCustomer();
	}
	
	/**
	 * Returns true if the stock of the imported and identified item has to be reduced.
	 * @return bool
	 */
	protected function hasReduceStock() {
		return (($this->config['StockSync.FromMarketplace'] != 'no')  && ($this->o['orderInfo']['FulfillmentChannel'] != 'AFN'))
			|| (($this->config['StockSync.FromMarketplace'] == 'fba') && ($this->o['orderInfo']['FulfillmentChannel'] == 'AFN'));
	}
	
	protected function generatePromoMailContent() {
		$aContent = parent::generatePromoMailContent();
		$aContent['#SHOPURL#'] = ''; /* @deprecated: amazon desperately hates this. */
		return $aContent;
	}

	protected function sendPromoMail() {
		if (($this->config['MailSend'] != 'true') || (get_class($this->db) == 'MagnaTestDB')) {
			// echo print_m($this->generatePromoMailContent());
			return;
		}
		// mail addresses for Amazon customers have a 'blacklisted-' added by us,
		// by default. Therefore, if the merchant wishes to send an e-mail, we have to
		// remove this prefix.
		sendSaleConfirmationMail(
			$this->mpID,
			str_replace('blacklisted-', '', $this->o['customer']['customers_email_address']),
			$this->generatePromoMailContent()
		);
	}

	/*
	 * remove 'blacklisted-' from customer's e-mail address
	 * (for the order data)
	 * if configured so (not recommended)
	 *
	 * (could be done in insertCustomer, but for the case the order of inserting data
	 *  will change, it's safer here)
	 */
	protected function doBeforeInsertOrder() {
		if (!getDBConfigValue(array($this->marketplace . '.mailaddress.blacklist', 'val'), $this->mpID, true)) {
			if ($this->verbose) echo __FUNCTION__.": amazon.mailaddress.blacklist == false\n";
			$this->o['order']['customers_email_address'] = str_replace('blacklisted-', '', $this->o['order']['customers_email_address']);
		}
	}

}
