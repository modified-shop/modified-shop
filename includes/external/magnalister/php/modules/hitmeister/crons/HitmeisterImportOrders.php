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

class HitmeisterImportOrders extends MagnaCompatibleImportOrders {
	
    public function __construct($mpID, $marketplace) {
        parent::__construct($mpID, $marketplace);
        if (getDBConfigValue('general.options', '0', 'old') != 'gambioProperties') {
            $this->multivariationsEnabled = true;
        } else {
            $this->gambioPropertiesEnabled = true;
        }
    }

    /**
     * Return all config keys.
     *
     * @return array
     */
	protected function getConfigKeys() {
		$keys = parent::getConfigKeys();
		$keys['OrderStatusOpen'] = array (
			'key' => 'orderstatus.open',
			'default' => '2',
		);
		$keys['FbkStatus'] = array(
			'key' => 'orderstatus.fbk',
			'default' => '3',
		);

		return $keys;
	}

    /**
     * Return the new order status depending on the fulfillment type.
     *
     * @return string
     */
	protected function getOrdersStatus() {
		if (is_array($this->o['magnaOrders']) &&
			array_key_exists('FulfillmentType', $this->o['magnaOrders']) &&
			'fulfilled_by_kaufland' == $this->o['magnaOrders']['FulfillmentType']
		) {
			return $this->config['FbkStatus'];
		}

		return $this->config['OrderStatusOpen'];
	}

    /**
     * Determines if the stock needs to be reduced due to settings.
     *
     * Either relative stock synchronization without fbk orders, or including them, otherwise not.
     *
     * @return bool
     */
    protected function hasReduceStock() {
        return
            ('rel' == $this->config['StockSync.FromMarketplace'] &&
                (!is_array($this->o['magnaOrders']) ||
                    !array_key_exists('FulfillmentType', $this->o['magnaOrders']) ||
                    'fulfilled_by_kaufland' != $this->o['magnaOrders']['FulfillmentType']))
            || 'fbk' == $this->config['StockSync.FromMarketplace'];
    }

    protected function additionalProductsIdentification() {
		$ean = $this->p['products_ean'];
		unset($this->p['products_ean']);
		if ($this->p['products_id'] == 0) {
			$pim = MagnaDB::gi()->fetchRow('
				SELECT products_id, products_model FROM '.TABLE_PRODUCTS.'
				 WHERE products_ean = "'.$ean.'"
			');
			if (false !== $pim) {
				$this->p['products_id'] = $pim['products_id'];
				$this->p['products_model'] = $pim['products_model'];
			}
		}
		if ((!isset($this->p['products_name']) || empty($this->p['products_name'])) && ($this->p['products_id'] != 0)) {
			$this->p['products_name'] = MagnaDB::gi()->fetchOne('
				SELECT pd.products_name
				  FROM '.TABLE_PRODUCTS_DESCRIPTION.'pd, '.TABLE_LANGUAGES.' l
				 WHERE pd.products_id = "'.$this->p['products_id'].'"
					   AND pd.language_id = l.languages_id
					   AND l.code = "'.strtolower($this->o['orderInfo']['BuyerCountryISO']).'"
			');
			if ($this->p['products_name'] == false) {
                # Fallback for default language
                if (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
                    $languageId = MagnaDB::gi()->fetchOne("
                        SELECT `languages_id`
                          FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c
                         WHERE     l.`code` = c.`value`
                               AND c.`key` = 'configuration/DEFAULT_LANGUAGE'
                    ");
                } else {
                    $languageId = MagnaDB::gi()->fetchOne("
                        SELECT `languages_id`
                          FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c 
                        WHERE l.`code` = c.`configuration_value` 
                        AND c.`configuration_key` = 'DEFAULT_LANGUAGE'
                    ");
                }
				$this->p['products_name'] = MagnaDB::gi()->fetchOne('
					SELECT products_name
					  FROM '.TABLE_PRODUCTS_DESCRIPTION.'
					 WHERE pd.products_id = "'.$this->p['products_id'].'"
						   AND language_id = '.$languageId
				);
			}
		}
		if (empty($this->p['products_name'])) {
			$this->p['products_name'] = $ean;
		}
	}
	
}
