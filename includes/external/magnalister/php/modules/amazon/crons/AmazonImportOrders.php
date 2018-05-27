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
 * $Id$
 *
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');

/**
 *
 */
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
		$keys['PaymentMethodFBA'] = array (
			'key' => 'orderimport.fbapaymentmethod',
			'default' => 'textfield',
		);
		$keys['PaymentMethodNameFBA'] = array (
			'key' => 'orderimport.fbapaymentmethod.name',
			'default' => 'amazon',
		);
		return $keys;
	}
	
	protected function initConfig() {
		parent::initConfig();
		
		if ($this->config['ShippingMethodFBA'] == 'textfield') {
			$this->config['ShippingMethodFBA'] = trim($this->config['ShippingMethodNameFBA']);
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
	 * Returs the payment method for the current order.
	 * @return string
	 */
	protected function getPaymentMethod() {
		return ($this->o['orderInfo']['FulfillmentChannel'] == 'AFN')
			? $this->config['PaymentMethodFBA']
			: $this->config['PaymentMethod'];
	}
	
	/**
	 * Returs the shipping method for the current order.
	 * @return string
	 */
	protected function getShippingMethod() {
		return ($this->o['orderInfo']['FulfillmentChannel'] == 'AFN')
			? $this->config['ShippingMethodFBA']
			: $this->config['ShippingMethod'];
	}

	private function getMarketplaceTitle() {
		switch ($this->o['orderInfo']['FulfillmentChannel']) {
			case 'AFN':       return $this->marketplaceTitle.'FBA'; break;
			case 'MFN-Prime': return $this->marketplaceTitle.' Prime'; break;
			default:          return $this->marketplaceTitle; break;
		}
	}
	
	protected function generateOrderComment() {
		$comment = str_replace('GiftMessageText', ML_AMAZON_LABEL_GIFT_MESSAGE, $this->o['order']['comments']);
		$comment = trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->getMarketplaceTitle())."\n".
			'AmazonOrderID: '.$this->getMarketplaceOrderID()."\n\n".
			$comment
		);
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
		parent::insertProduct();
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
	
}
