<?php
/*
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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleImportOrders.php');
require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');

class EbayImportOrders extends MagnaCompatibleImportOrders {

    public function __construct($mpID, $marketplace) {
        parent::__construct($mpID, $marketplace);
        if (getDBConfigValue('general.options', '0', 'old') != 'gambioProperties') {
            $this->multivariationsEnabled = true;
        } else {
            $this->gambioPropertiesEnabled = true;
        }
    }

    protected function initImport() {
        parent::initImport();
        MagnaConnector::gi()->setTimeOutInSeconds(10);
    }

    protected function completeImport() {
        MagnaConnector::gi()->resetTimeOut();
    }

    protected function getConfigKeys() {
        $aConfigKeys = parent::getConfigKeys();
        $aConfigKeys['PaymentMethodName']['default'] = 'ebay';

        return array_merge($aConfigKeys, array(
            'OrderStatusOpen' => array (
                'key' => 'orderstatus.open',
                'default' => '',
            ),
            'OrderStatusClosed' => array (
                'key' => 'orderstatus.closed',
                'default' => array(),
            ),
            'ImportOnlyPaid' => array (
                'key' => 'order.importonlypaid',
                'default' => false,
            ),
            'ShippingMethod' => array (
                'key' => 'order.shippingmethod',
                'default' => 'textfield',
            ),
            'ShippingMethodName' => array (
                'key' => 'order.shippingmethod.name',
                'default' => 'ebay',
            ),
            'ShippingProfiles' => array (
                'key' => 'shippingprofiles',
                'default' => null,
            ),
            'ShippingProfileIdLocal' => array (
                'key' => 'default.shippingprofile.local',
                'default' => 0,
            ),
            'ShippingProfileIdIternational' => array (
                'key' => 'default.shippingprofile.international',
                'default' => 0,
            ),
            'ShippingProfileDiscountUseLocal' => array (
                'key' => array('shippingdiscount.local', 'val'),
                'default' => true,
            ),
            'ShippingProfileDiscountUseIternational' => array (
                'key' => array('shippingdiscount.international', 'val'),
                'default' => true,
            ),
        ));
    }

    protected function initConfig() {
        parent::initConfig();
        $this->config['OrderStatusClosed'] = (is_array($this->config['OrderStatusClosed']))
            ? $this->config['OrderStatusClosed']
            : array()
        ;
        // case: Old MwStFallback mkey 'ebay.mwstfallback' (changed in form file a time ago, but was still used here)
        if (    $this->config['MwStFallback'] == 0
             && ($iOldMwStFallback = getDBConfigValue('ebay.mwstfallback', $this->mpID, 0)) != 0) {
            $this->config['MwStFallback'] = $iOldMwStFallback;
            if (false == MagnaDB::gi()->fetchOne('SELECT `value` FROM '.TABLE_MAGNA_CONFIG.' WHERE `mkey` = \'ebay.mwst.fallback\' AND `mpID` = '.$this->mpID)) {
                MagnaDB::gi()->query('UPDATE '.TABLE_MAGNA_CONFIG.' SET `mkey` = \'ebay.mwst.fallback\' WHERE `mkey` = \'ebay.mwstfallback\' AND `mpID` = '.$this->mpID);
            } else {
                MagnaDB::gi()->query('UPDATE '.TABLE_MAGNA_CONFIG.' SET `value` = \''.$iOldMwStFallback.'\' WHERE `mkey` = \'ebay.mwst.fallback\' AND `mpID` = '.$this->mpID);
                MagnaDB::gi()->query('DELETE FROM '.TABLE_MAGNA_CONFIG.' WHERE `mkey` = \'ebay.mwstfallback\' AND `mpID` = '.$this->mpID);
            }
        }
    }

    protected function doBeforeInsertProduct() {
        $this->p['products_shipping_time'] = (isset($this->p['products_shipping_time']))
            ? $this->p['products_shipping_time']
            : 0
        ;
        if (empty($this->p['products_shipping_time'])) {
            $this->p['products_shipping_time'] = getDBConfigValue('ebay.DispatchTimeMax', $this->mpID, 0).(
                'de' == strtolower($this->config['StoreLanguage'])
                    ? 'Werktage'
                    : 'days'
                );
        }
    }

    /**
     * Load some basic info, e.g. country etc from DB
     */
    protected function prepareOrderInfo() {
        /* {Hook} "GeteBayOrders_PreOrderImport": Is called before the eBay order in <code>$order</code> is imported.
            Variables that can be used:
            <ul><li>$order: The order that is going to be imported. The order is an
                    associative array representing the structures of the order and customer related shop tables.</li>
                <li>$mpID: The ID of the marketplace.</li>
                <li>$MagnaDB: Instance of the magnalister database class. USE THIS for accessing the database during the
                    order import. DO NOT USE the shop functions for database access or MagnaDB::gi()!</li>
            </ul>
        */
        if (($hp = magnaContribVerify('GeteBayOrders_PreOrderImport', 1)) !== false) {
            // ensure it works like in old get_ebay_orders
            $order   = $this->o;
            $mpID    = $this->mpID;
            $MagnaDB = $this->db;
            require($hp);
            $this->o = $order; // can be modified by a contrib
        }

        $sBillingCountryCode = (isset($this->o['order']['billing_country_iso_code_2']))
            ? $this->o['order']['billing_country_iso_code_2']
            : false
        ;
        $sDeliveryCountryCode = (isset($this->o['order']['delivery_country_iso_code_2']))
            ? $this->o['order']['delivery_country_iso_code_2']
            : $sBillingCountryCode
        ;
        // for the parent class
        $this->o['orderInfo']['BuyerCountryISO'] = $sBillingCountryCode;
        $this->o['orderInfo']['ShippingCountryISO'] = $sDeliveryCountryCode;

        $this->cur['BuyerCountry'] = $this->getCountryByISOCode($sBillingCountryCode, false);
        $this->cur['ShippingCountry'] = $this->getCountryByISOCode($sDeliveryCountryCode, false);
    }

    /**
     * How many hours, days, weeks or whatever we go back in time to request older orders?
     * @return int - time in seconds
     */
    protected function getPastTimeOffset() {
        return 60 * 60 * 24 * 14;
    }

    protected function getMarketplaceOrderID() {
        return $this->o['orderInfo']['eBayOrderID'];
    }

    protected function getExtendedOrderID() {
        return (isset($this->o['orderInfo']['ExtendedOrderID']))
            ? "\nExtendedOrderID: ".$this->o['orderInfo']['ExtendedOrderID']
            : ''
            ;
    }

    /**
     * last function called in processSingleOrder,
     * adding the old GeteBayOrders_PostOrderImport Hook-Point here
     */
    protected function addCurrentOrderToProcessed() {
        // different for eBay: NumberOfImportedPosition
        $this->processedOrders[] = array (
            'MOrderID' => $this->getMarketplaceOrderID(),
            'ShopOrderID' => $this->cur['OrderID'],
            'NumberOfImportedPosition' => (int)MagnaDB::gi()->fetchOne('SELECT COUNT(*) FROM '.TABLE_ORDERS_PRODUCTS.' WHERE orders_id = '.$this->cur['OrderID'])
        );
        $this->out($this->marketplace.' ('.$this->mpID.') order '.$this->getMarketplaceOrderID().' imported with No. '.$this->cur['OrderID']."\n");
        /* {Hook} "GeteBayOrders_PostOrderImport": Is called after the eBay order in <code>$order</code> is imported.
            Variables that can be used: Same as for GeteBayOrders_PreOrderImport.
        */
        if (($hp = magnaContribVerify('GeteBayOrders_PostOrderImport', 1)) !== false) {
            // ensure it works like in old get_ebay_orders
            $order   = $this->o;
            $mpID    = $this->mpID;
            $MagnaDB = $this->db;
            require($hp);
            $this->o = $order; // can be modified by a contrib
        }
    }

    protected function orderExists() {
        $mOID = $this->getMarketplaceOrderID();
        $oID = MagnaDB::gi()->fetchOne(eecho('
			SELECT orders_id
			  FROM '.TABLE_MAGNA_ORDERS.'
			 WHERE mpID = '.$this->mpID.'
			   AND special LIKE "%'.MagnaDB::gi()->escape($mOID).'%"
			 LIMIT 1
		', false));
        if ($oID === false) {
            return false;
        }
        if ($this->verbose) echo 'orderExists(MOrderID: '.$mOID.', OrderID: '.$oID.')'."\n";
        $this->cur['OrderID'] = $oID;

        /* Ack again */
        $this->addCurrentOrderToProcessed();
        return true;
    }

    protected function getOrdersStatus() {
        return $this->config['OrderStatusOpen'];
    }

    private function getEbayBuyerUserName () {
        return (empty($this->o['orderInfo']['eBayBuyerUsername']))
            ? ''
            : "\neBay User:   ".$this->o['orderInfo']['eBayBuyerUsername']
            ;
    }

    /**
     * In new version of Gambio it is not possible to use html in comment of status
     * @return string
     */
    private function getEbayRefundUrl () {
        return (empty($this->o['orderInfo']['EbayRefundUrl']))
            ? ''
            : sprintf(ML_EBAY_ORDER_DETAIL_INFORMATION_TO_EBAY_SELLER_HUB, $this->o['orderInfo']['EbayRefundUrl'])
            ;
    }

    private function getEbayPlus () {
        return (empty($this->o['orderInfo']['eBayPlus']))
            ? ''
            : "\neBayPlus"
            ;
    }

    private function getEbaySalesRecordNumber() {
        return (0 != $this->o['orderInfo']['eBaySalesRecordNumber'])
            ? "\n".ML_LABEL_EBAY_SALES_RECORD_NUMBER.': '.$this->o['orderInfo']['eBaySalesRecordNumber']
            : ''
            ;
    }

    protected function generateOrderComment($blForce = false) {
        if (!$blForce && !getDBConfigValue(array('general.order.information', 'val'), 0, true)) {
            return '';
        }
        return trim(
            sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
            'eBayOrderID: '.$this->getMarketplaceOrderID().
            $this->getExtendedOrderID().
            $this->getEbaySalesRecordNumber().
            $this->getEbayBuyerUserName()."\n\n".
            $this->comment
        );
    }

    protected function generateOrdersStatusComment() {
        if ('true' === $this->config['ImportOnlyPaid']) {
            if (strpos($this->o['orderComment'], 'PayPal')) {
                /*
                 * eBay PUI is PayPal with instruction
                 * If the table orders_payment_instruction exists, use it
                 */
                $blPaymentInstructionSet = false;
                if (MagnaDB::gi()->tableExists('orders_payment_instruction')) {
                    $blPaymentInstructionSet = fillOrdersPaymentInstructionTable($this->o['orderComment'], $this->cur['OrderID']);
                }
                if (!$blPaymentInstructionSet) {
                    $PUIcomment = ML_EBAY_PUI_MSG_TO_BUYER.
                        $this->o['orderComment'];
                }
            }
        }
        return trim(
            sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP, $this->marketplaceTitle)."\n".
            'eBayOrderID: '.$this->getMarketplaceOrderID().
            $this->getExtendedOrderID().
            $this->getEbayBuyerUserName().
            $this->getEbayPlus()."\n\n".
            $this->comment . (isset($PUIcomment)?$PUIcomment:'')
        //            ."\n\n".
        //            $this->getEbayRefundUrl()
        );
    }

    /**
     * Returns the shipping method for the current order.
     * @return string
     */
    protected function getShippingMethod() {
        if ($this->config['ShippingMethod'] == '__ml_lump' || $this->config['ShippingMethod'] == 'textfield') {
            // we need to use "ebay" because shop do not allow free text for this
            return $this->o['order']['shipping_class'];
        }
        return $this->config['ShippingMethod'];
    }

    protected function getPaymentMethod() {
        if ($this->config['PaymentMethod'] == 'matching') {
            return getPaymentClassForEbayPaymentMethod($this->o['order']['payment_method']);
        }
        return $this->config['PaymentMethod'];
    }

    protected function insertOrder() {
        $this->comment = array_key_exists('comments', $this->o['order'])
            ? $this->o['order']['comments'] : '';
        $this->o['order']['customers_id'] = $this->cur['customer']['ID'];

        $this->o['order']['customers_address_format_id'] =
        $this->o['order']['billing_address_format_id'] =
            $this->getAddressFormatID($this->cur['BuyerCountry']);
        $this->o['order']['delivery_address_format_id'] =
            $this->getAddressFormatID($this->cur['ShippingCountry']);

        $this->o['order']['orders_status'] = $this->getOrdersStatus();

        $this->o['order']['customers_country'] = $this->cur['BuyerCountry']['Name'];
        $this->o['order']['delivery_country'] = $this->cur['ShippingCountry']['Name'];
        $this->o['order']['billing_country'] = $this->cur['BuyerCountry']['Name'];

        if (SHOPSYSTEM != 'oscommerce') {
            if (isset($this->cur['customer']['CID'])) {
                $this->o['order']['customers_cid'] = $this->cur['customer']['CID'];
            }
            $this->o['order']['customers_status'] = $this->config['CustomerGroup'];
            $this->o['order']['language'] = $this->language;
            $this->o['order']['comments'] = $this->generateOrderComment();
        }

        if ($this->config['DBColumnExists']['orders.customers_status_name']) {
            $this->o['order']['customers_status_name'] = $this->config['CustomerGroupProperties']['customers_status_name'];
        }
        if ($this->config['DBColumnExists']['orders.customers_status_image']) {
            $this->o['order']['customers_status_image'] = $this->config['CustomerGroupProperties']['customers_status_image'];
        }
        if ($this->config['DBColumnExists']['orders.gm_send_order_status']) {
            $this->o['order']['gm_send_order_status'] = 1;
        }
        if ($this->config['DBColumnExists']['orders.customers_status_discount']) {
            $this->o['order']['customers_status_discount'] = '0.0';
        }
        if ($this->config['DBColumnExists']['orders.orders_hash']) {
            $this->o['order']['orders_hash'] = md5(strtotime($this->o['order']['date_purchased']) + mt_rand());
        }

        /* Change Shipping and Payment Methods */
        $this->o['order']['payment_method'] = $this->getPaymentMethod();
        if (SHOPSYSTEM != 'oscommerce') {
            $this->o['order']['payment_class'] = $this->o['order']['payment_method'];
            $this->o['order']['shipping_class'] = $this->o['order']['shipping_method'] = $this->getShippingMethod();
        }
        // set currency_value
        $this->o['order']['currency_value'] = $this->allCurrencies[$this->o['order']['currency']];

        $this->doInsertOrder();
        # Statuseintrag fuer Historie vornehmen.
        $this->o['orderStatus']['orders_id'] = $this->cur['OrderID'];
        $this->o['orderStatus']['orders_status_id'] = $this->o['order']['orders_status'];

        $this->o['orderStatus']['comments'] = $this->generateOrdersStatusComment();

        $this->doBeforeInsertOrderHistory();
        $this->insert(TABLE_ORDERS_STATUS_HISTORY, $this->o['orderStatus']);
        // echo 'DELETE FROM '.TABLE_ORDERS_STATUS_HISTORY.' WHERE orders_id="'.$this->cur['OrderID'].'";'."\n\n";

    }

    // special case (bug):
    // ImportOnlyPaid = true, but an order with the same ExtendedOrderID has not been considered
    // so we should add it (if the already imported order has still the start status)
    private function isExtendedOrderToAdd() {
        if ($this->verbose) {
            echo "\n".__FUNCTION__."\n";
        }
        if (!isset($this->o['orderInfo']['ExtendedOrderID'])) return false;
        if (!$this->config['ImportOnlyPaid']) return false;
        return (boolean)MagnaDB::gi()->fetchOne(eecho('SELECT COUNT(*)
			 FROM '.TABLE_ORDERS.'
			WHERE comments LIKE \'%ExtendedOrderID: '.$this->o['orderInfo']['ExtendedOrderID'].'%\'
			  AND orders_status = '.$this->config['OrderStatusOpen'], $this->verbose));
    }

    protected function doInsertOrder() {
        $this->doBeforeInsertOrder();

        $blIsExtendedOrderToAdd = $this->isExtendedOrderToAdd();
        if (   (   ( empty($this->config['OrderStatusClosed']))
                || ('true' === $this->config['ImportOnlyPaid']))
            && (!$blIsExtendedOrderToAdd)                       ) {
            # don't merge if "don't megre" array empty, or if we import only complete orders
            $existingOpenOrder = false;
        } else {
            if (   MagnaDB::gi()->columnExistsInTable('delivery_firstname', TABLE_ORDERS)
                && MagnaDB::gi()->columnExistsInTable('delivery_lastname', TABLE_ORDERS)) {
                $sAndDeliveryName =
                    'AND o.delivery_firstname = \''.$this->o['order']['delivery_firstname'].'\' 
			           AND o.delivery_lastname = \''.MagnaDB::gi()->escape($this->o['order']['delivery_lastname']).'\'';
            } elseif (MagnaDB::gi()->columnExistsInTable('delivery_name', TABLE_ORDERS)) {
                $sAndDeliveryName =
                    'AND o.delivery_name = \''.$this->o['order']['delivery_name'].'\'';
            }
            if (!$blIsExtendedOrderToAdd) {
                $sAndOrdersStatus = 'AND o.orders_status NOT IN ("'.implode('", "', $this->config['OrderStatusClosed']).'")';
            } else {
                $sAndOrdersStatus = 'AND o.orders_status = '.$this->config['OrderStatusOpen'].'
			           AND o.comments LIKE \'%ExtendedOrderID: '.$this->o['orderInfo']['ExtendedOrderID'].'%\'';
            }
            $existingOpenOrder = MagnaDB::gi()->fetchRow(eecho('
			    SELECT o.orders_id, mo.special, mo.data, mo.internaldata
			      FROM '.TABLE_ORDERS.' o, '.TABLE_MAGNA_ORDERS.' mo
			     WHERE o.customers_email_address = \''.$this->o['order']['customers_email_address'].'\' 
			           '.$sAndDeliveryName.'
			           AND substring(o.delivery_street_address,1,64) = \''.substr(MagnaDB::gi()->escape($this->o['order']['delivery_street_address']), 0, 64).'\' 
			           AND o.delivery_postcode = \''.$this->o['order']['delivery_postcode'].'\' 
			           AND substring(o.delivery_city,1,32) = \''.substr(MagnaDB::gi()->escape($this->o['order']['delivery_city']), 0,32).'\' 
			           '.$sAndOrdersStatus.'
			           AND o.currency = \''.$this->o['order']['currency'].'\'
			           AND mo.mpID = '.$this->mpID.'
			           AND o.orders_id = mo.orders_id 
			  ORDER BY o.orders_id DESC LIMIT 1
			', $this->verbose));
        }

        if ($this->verbose) {
            echo var_dump_pre($existingOpenOrder, '$existingOpenOrder');
        }

        foreach (array('billing_country_iso_code_2', 'delivery_country_iso_code_2', 'shipping_module') as $sCol) {
            // osCommerce shops don't have the columns
            if (!MagnaDB::gi()->columnExistsInTable($sCol, TABLE_ORDERS)) {
                unset($this->o['order'][$sCol]);
            }
        }
        # If magna order is found we add this order to it.
        if (false == $existingOpenOrder) {
            # We didn't find an order to which we can add this order.
            MagnaDB::gi()->validateDataLength($this->o['order'], TABLE_ORDERS);
            MagnaDB::gi()->addNonNullableEntries($this->o['order'], TABLE_ORDERS);
            # filter keys (if hooks have changed sth.)
            $this->db->insert(TABLE_ORDERS, array_filter_keys($this->o['order'], MagnaDB::gi()->getTableColumns(TABLE_ORDERS)));
            $this->cur['OrderID'] = $this->db->getLastInsertID();
            # Falls es doch verlorengeht (passiert)
            if (empty($this->cur['OrderID'])) {
                $iInsertId = (int)$this->db->fetchOne("SELECT LAST_INSERT_ID()");
                $sOrderId = (int)$this->db->fetchOne("
					SELECT orders_id
					  FROM ".TABLE_ORDERS."
					 WHERE customers_email_address = '".MagnaDB::gi()->escape($this->o['order']['customers_email_address'])."'
					ORDER BY orders_id DESC
					LIMIT 1
				");
                if ($iInsertId == $sOrderId) {
                    $this->cur['OrderID'] = $iInsertId;
                }
            }
            $magnaOrdersData = serialize($this->o['magnaOrders']);
            $magnaOrdersSpecial = $this->getMarketplaceOrderID();
            $this->o['internaldata'] = $this->calculateInternalData($this->o['orderTotal']['Shipping']['value']);
        } else {
            # We found the order to which we can add this order and make it merged.
            $this->cur['OrderID'] = (int)$existingOpenOrder['orders_id'];
            $magnaOrdersDataArr = unserialize($existingOpenOrder['data']);

            # Merge order to merged or single order.
            foreach (array('eBayOrderID', 'ExtendedOrderID', 'eBaySalesRecordNumber') as $sOrderKey) {
                if (!array_key_exists($sOrderKey, $this->o['magnaOrders'])) {
                    continue;
                }
                if (!is_array($magnaOrdersDataArr[$sOrderKey])) {
                    $magnaOrdersDataArr[$sOrderKey] = array(
                        $magnaOrdersDataArr[$sOrderKey],
                        $this->o['magnaOrders'][$sOrderKey]
                    );
                } else {
                    $magnaOrdersDataArr[$sOrderKey][] = $this->o['magnaOrders'][$sOrderKey];
                }
            }
            # eBayPlus or other additional info
            $aNewMagnaOrdersDataKeys = array_keys($this->o['magnaOrders']);
            foreach ($aNewMagnaOrdersDataKeys as $newKey) {
                if (!array_key_exists($newKey, $magnaOrdersDataArr)) {
                    $magnaOrdersDataArr[$newKey] = $this->o['magnaOrders'][$newKey];
                }
            }
            $magnaOrdersData = serialize($magnaOrdersDataArr);
            $magnaOrdersSpecial = $existingOpenOrder['special']."\n".$this->getMarketplaceOrderID();

            # Update the shipping method
            if (MagnaDB::gi()->columnExistsInTable('shipping_class', TABLE_ORDERS)) {
                $this->db->update(TABLE_ORDERS, array (
                    'shipping_class' => $this->o['order']['shipping_method'] = $this->getShippingMethod(),
                ), array (
                    'orders_id' => $this->cur['OrderID'],
                ));
            }
            $this->o['internaldata'] = $this->calculateInternalData($this->o['orderTotal']['Shipping']['value'], $existingOpenOrder['internaldata']);
        }
        $this->db->insert(TABLE_MAGNA_ORDERS, array(
            'mpID' => $this->mpID,
            'orders_id' => $this->cur['OrderID'],
            'orders_status' => $this->o['order']['orders_status'],
            'data' => $magnaOrdersData,
            'internaldata' => $this->o['internaldata'],
            'special' => $magnaOrdersSpecial,
            'platform' => $this->marketplace
        ), true);
    }

    /*
     * only set products_price as it's not provided by API
     */
    protected function additionalProductsIdentification() {
        $this->p['products_quantity'] = (
        ! $this->p['products_quantity']/* cannot happen, but if so, prevent division by zero */
            ? 1
            : $this->p['products_quantity']
        );
        $this->p['products_price'] = isset($this->p['final_price'])
            ? round($this->p['final_price'] / $this->p['products_quantity'], 2)
            : $this->p['products_price']
        ;
        /* {Hook} "GeteBayOrders_additionalProductsIdentification": Allows own product identification (e.g. by a dedicated column in the shop database. Called after the default identification by SKU.
            Variables that can be used:
            <ul>
               <li>$this->marketplace (here always 'ebay')</li>
               <li>$this->mpID the ID of the marketplace</li>
               <li>$this->p Array with product data.</li>
            </ul>
            <p>$this->p['products_id'] and $this->p['products_model'] contain the data determined by the default identification.<br />
            If the default identification didn't find anything, $this->p['products_id'] = 0 and $this->p['products_model'] contains the SKU obtained from the marketplace.<br />
            After a successful identification, $this->p['products_id'] and $this->p['products_model'] should contain the product identifiers.
        */
        if (($hp = magnaContribVerify('GeteBayOrders_additionalProductsIdentification', 1)) !== false) {
            require($hp);
        }
    }

    /*
     * not in use (use parent function)
     * the case that the customer orders exactly the same product several times
     * happens rarely, the complexity is high
     * can be used in the future if the customers want it
     */
    protected function oldFooInsertProduct() {
        parent::insertProduct();
        // merge products if we have several of the same kind
        /* algorithm:
           - check if we have multiple products with the same products_id and our orders_id in orders_products
             - if no, return
             - if yes:
               - check if there's orders_products_attributes or orders_products_properties rows
                 for our product
                 - if no, merge products
                 (means for OsCommerce, sum quantity and delete all but the first,
                  for xtC + other: sum quantity and final_price, and delete all but the first)
                 - if yes:
                   - for gambio properties:
                     - check if the orders_products_properties for the "same" products
                       have the same products_properties_combis_id
                       - if no, continue to the next product,
                       - if yes, merge products and delete the orders_products_properties rows
                         for the deleted orders_products rows
                   - for attributes:
                     - build a string which represents all attributes for each oders_products_id,
                     - compare all the strings to the last one (cos only 1 new product comes per order),
                     - if found, merge oders_products rows
                       and delete orders_products_attributes rows for the last one
            TODO gambio properties case should be tested
         */
        // check if we have multiple products with the same products_id and our orders_id in orders_products
        $aRepeatedProducts = MagnaDB::gi()->fetchArray(eecho("SELECT products_id, products_price, COUNT(*) cnt
			 FROM ".TABLE_ORDERS_PRODUCTS." 
			WHERE orders_id = ".$this->cur['OrderID']."
			  AND products_id <> 0
			GROUP BY products_id, products_price
			HAVING cnt>1", $this->verbose));
        if (empty($aRepeatedProducts)) {
            // no repeated products, return
            return;
        }
        foreach ($aRepeatedProducts as $row) {
            // check if there's orders_products_attributes or orders_products_properties rows
            // first the orders_products_id's
            $aOrdersProductsIds = MagnaDB::gi()->fetchArray(eecho("SELECT DISTINCT orders_products_id
				 FROM ".TABLE_ORDERS_PRODUCTS."
				WHERE orders_id = ".$this->cur['OrderID']."
				  AND products_id = ".$row['products_id']."
				  AND products_price = ".$row['products_price']."
				ORDER BY orders_products_id", $this->verbose), true);
            $sOrdersProductsIds = implode(', ', $aOrdersProductsIds);
            if ($this->gambioPropertiesEnabled) {
                $aPropCounts = MagnaDB::gi()->fetchArray(eecho("SELECT products_properties_combis_id, COUNT(*) cnt
					 FROM orders_products_properties
					WHERE orders_products_id IN ($sOrdersProductsIds)
					GROUP BY products_properties_combis_id", $this->verbose));
                if (empty($aPropCounts)) {
                    // no products_properties_combis found, so we have only the main product, merge it
                    $aOrdersProductsSums = MagnaDB::gi()->fetchRow(eecho("SELECT MIN(orders_products_id) mopi, SUM(final_price) sfp, SUM(products_quantity) spq
						 FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id'], $this->verbose));
                    // update
                    $this->db->update(TABLE_ORDERS_PRODUCTS, array(
                        'final_price' => $aOrdersProductsSums['sfp'],
                        'products_quantity' => $aOrdersProductsSums['spq']
                    ), array (
                            'orders_id' => $this->cur['OrderID'],
                            'products_id' => $row['products_id']
                        )
                    );
                    // delete surplus cols
                    $this->db->query(eecho("DELETE FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND products_name = '".$row['products_name']."'
						  AND orders_products_id <> ".$aOrdersProductsSums['mopi']
                        , $this->verbose));
                    // and continue to te next product
                    unset($aOrdersProductsIds);
                    unset($sOrdersProductsIds);
                    unset($aPropCounts);
                    unset($aOrdersProductsSums);
                    continue;
                }
                foreach ($aPropCounts as $prop) {
                    if (1 == $prop['cnt']) {
                        // can't merge this property, take the next
                        continue;
                    }
                    $aOrdersProductsIdsForProp = MagnaDB::gi()->fetchArray(eecho("SELECT DISTINCT orders_products_id
						 FROM orders_products_properties
						WHERE products_properties_combis_id = ".$prop['products_properties_combis_id']."
						  AND orders_products_id in ($sOrdersProductsIds)"
                        , $this->verbose), true);
                    $sOrdersProductsIdsForProp = implode(', ', $aOrdersProductsIdsForProp);
                    // merge products with the same products_properties_combis_id
                    $aOrdersProductsSums = MagnaDB::gi()->fetchRow(eecho("SELECT MIN(orders_products_id) mopi, SUM(final_price) sfp, SUM(products_quantity) spq
						 FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND orders_products_id IN ($sOrdersProductsIdsForProp)",$this->verbose));
                    // update
                    $this->db->update(TABLE_ORDERS_PRODUCTS, array(
                        'final_price' => $aOrdersProductsSums['sfp'],
                        'products_quantity' => $aOrdersProductsSums['spq']
                    ), array (
                            'orders_id' => $this->cur['OrderID'],
                            'products_id' => $row['products_id'],
                            'orders_products_id' => $aOrdersProductsSums['mopi']
                        )
                    );
                    // delete surplus cols
                    $this->db->query(eecho("DELETE FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND orders_products_id IN ($sOrdersProductsIdsForProp)
						  AND orders_products_id <> ".$aOrdersProductsSums['mopi']
                        ,$this->verbose));
                    $this->db->query(eecho("DELETE FROM orders_products_properties
						WHERE orders_products_id IN ($sOrdersProductsIdsForProp)
						  AND orders_products_id <> ".$aOrdersProductsSums['mopi']
                        ,$this->verbose));
                }
            } else { // not $this->gambioPropertiesEnabled
                $aAttrByOrdersProductsId = array();
                foreach ($aOrdersProductsIds as $sOrdersProductsId) {
                    // create an atribute string for each attribute combination given
                    $aOpts = MagnaDB::gi()->fetchArray(eecho("SELECT CONCAT(products_options,':',products_options_values) AS opt
						 FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND orders_products_id = $sOrdersProductsId", $this->verbose), true);
                    asort($aOpts);
                    $sOpts = implode(',', $aOpts);
                    $aAttrByOrdersProductsId[$sOrdersProductsId] = $sOpts;
                    $sLastOrdersProductsId = $sOrdersProductsId;
                }
                $iMatch = 0;
                foreach ($aAttrByOrdersProductsId as $opid => $attr) {
                    if ($opid == $sLastOrdersProductsId) continue;
                    if ($attr == $aAttrByOrdersProductsId[$sLastOrdersProductsId]) {
                        $iMatch = $opid;
                        break;
                    }
                }
                if ($iMatch != 0) {
                    // merge products with the same attributes
                    $aOrdersProductsSums = MagnaDB::gi()->fetchRow(eecho("SELECT SUM(final_price) sfp, SUM(products_quantity) spq
						 FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND orders_products_id IN ($sLastOrdersProductsId, $iMatch)",$this->verbose));
                    // update
                    $aUpdateCols = array (
                        'products_quantity' => $aOrdersProductsSums['spq']
                    );
                    if (SHOPSYSTEM != 'oscommerce') {
                        $aUpdateCols['final_price'] = $aOrdersProductsSums['sfp'];
                    }
                    $this->db->update(TABLE_ORDERS_PRODUCTS, $aUpdateCols,
                        array (
                            'orders_id' => $this->cur['OrderID'],
                            'products_id' => $row['products_id'],
                            'orders_products_id' => $iMatch
                        )
                    );
                    // delete surplus cols
                    $this->db->query(eecho("DELETE FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND orders_products_id = $sLastOrdersProductsId", $this->verbose));
                    $this->db->query(eecho("DELETE FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND orders_products_id = $sLastOrdersProductsId", $this->verbose));
                }
            } // if $this->gambioPropertiesEnabled - else
        } // foreach ($aRepeatedProducts as $row)
    }

    /*
     * Special case (bug): ImportOnlyPaid order adds already existing positions
     * remove the surplus positions
     */
    protected function insertProduct() {
        parent::insertProduct();
        if(!$this->isExtendedOrderToAdd()) {
            return;
        }
        $aRepeatedProducts = MagnaDB::gi()->fetchArray(eecho("SELECT products_id, products_model, products_price, COUNT(*) cnt
			 FROM ".TABLE_ORDERS_PRODUCTS." 
			WHERE orders_id = ".$this->cur['OrderID']."
			  AND (products_id <> 0 OR LENGTH(products_model) > 0)
			GROUP BY products_id, products_model, products_price
			HAVING cnt>1", $this->verbose));
        if (empty($aRepeatedProducts)) {
            // no repeated products, return
            return;
        }
        foreach($aRepeatedProducts as $row) {
            $aOrdersProductsIds = MagnaDB::gi()->fetchArray(eecho("SELECT DISTINCT orders_products_id
				 FROM ".TABLE_ORDERS_PRODUCTS."
				WHERE orders_id = ".$this->cur['OrderID']."
				  AND products_id = ".$row['products_id']."
				  AND products_model = '".$row['products_model']."'
				  AND products_price = ".$row['products_price']."
				ORDER BY orders_products_id", $this->verbose), true);
            $sOrdersProductsIds = implode(', ', $aOrdersProductsIds);
            if ($this->gambioPropertiesEnabled) {
                $aPropCounts = MagnaDB::gi()->fetchArray(eecho("SELECT products_properties_combis_id, COUNT(*) cnt
					 FROM orders_products_properties
					WHERE orders_products_id IN ($sOrdersProductsIds)
					GROUP BY products_properties_combis_id", $this->verbose));
                if (empty($aPropCounts)) {
                    // no products_properties_combis found, just delete surplus cols
                    $this->db->query(eecho("DELETE FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND products_model = '".$row['products_model']."'
						  AND products_name = '".$row['products_name']."'
						  ORDER BY orders_products_id DESC LIMIT ".(int)($row['cnt'] - 1)
                        , $this->verbose));
                    // and continue to te next product
                    unset($sOrdersProductsIds);
                    unset($aPropCounts);
                    continue;
                }
                foreach ($aPropCounts as $prop) {
                    if (1 == $prop['cnt']) {
                        // single, take the next
                        continue;
                    }
                    $aOrdersProductsIdsForProp = MagnaDB::gi()->fetchArray(eecho("SELECT DISTINCT orders_products_id
						 FROM orders_products_properties
						WHERE products_properties_combis_id = ".$prop['products_properties_combis_id']."
						  AND orders_products_id in ($sOrdersProductsIds)"
                        , $this->verbose), true);
                    $sOrdersProductsIdsForProp = implode(', ', $aOrdersProductsIdsForProp);
                    // delete surplus cols
                    $this->db->query(eecho("DELETE FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND products_model = '".$row['products_model']."'
						  AND products_name = '".$row['products_name']."'
						  AND orders_products_id IN ($sOrdersProductsIdsForProp)
						  ORDER BY orders_products_id DESC LIMIT ".(int)($prop['cnt'] - 1)
                        ,$this->verbose));
                    $this->db->query(eecho("DELETE FROM orders_products_properties
						WHERE orders_products_id IN ($sOrdersProductsIdsForProp)
						  ORDER BY orders_products_id DESC LIMIT ".(int)($prop['cnt'] - 1)
                        ,$this->verbose));
                }
            } else { // not $this->gambioPropertiesEnabled
                $aAttrByOrdersProductsId = array();
                foreach ($aOrdersProductsIds as $sOrdersProductsId) {
                    // create an atribute string for each attribute combination given
                    $aOpts = MagnaDB::gi()->fetchArray(eecho("SELECT CONCAT(products_options,':',products_options_values) AS opt
						 FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND orders_products_id = $sOrdersProductsId", $this->verbose), true);
                    asort($aOpts);
                    $sOpts = implode(',', $aOpts);
                    $aAttrByOrdersProductsId[$sOrdersProductsId] = $sOpts;
                    $sLastOrdersProductsId = $sOrdersProductsId;
                }
                $iMatch = 0;
                foreach ($aAttrByOrdersProductsId as $opid => $attr) {
                    if ($opid == $sLastOrdersProductsId) continue;
                    if ($attr == $aAttrByOrdersProductsId[$sLastOrdersProductsId]) {
                        $iMatch = $opid;
                        break;
                    }
                }
                if ($iMatch != 0) {
                    // delete surplus cols
                    $this->db->query(eecho("DELETE FROM ".TABLE_ORDERS_PRODUCTS."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND orders_products_id = $sLastOrdersProductsId", $this->verbose));
                    $this->db->query(eecho("DELETE FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
						WHERE orders_id = ".$this->cur['OrderID']."
						  AND products_id = ".$row['products_id']."
						  AND orders_products_id = $sLastOrdersProductsId", $this->verbose));
                }
            } // if $this->gambioPropertiesEnabled - else
        } // foreach ($aRepeatedProducts as $row)
    }

    protected function isDomestic($countryISO) {
        if ($this->verbose) {
            echo "isDomestic($countryISO): StoreCountry is ".$this->config['StoreCountry']."\n";
        }
        return strtolower($countryISO) == strtolower($this->config['StoreCountry']);
    }

    /**
     * Recalculates the shipping cost for orders that are going to be merged.
     * 	$existingShippingCost float - shipping cost pull from orders total
     * 	$currItemShippingCost float - ShippingServiceCost from eBay (data from API if not changed before)
     * 	$totalNumberOfItems int - total number of products (quantity of all)
     * 	$totalPriceWOShipping float - price sum of all products
     * 	$currProductsCount int - numbers of items that will now proceed
     */
    protected function calculateShippingCost($existingShippingCost, $currItemShippingCost, $totalNumberOfItems, $totalPriceWOShipping, $currProductsCount) {
        if ('true' === $this->config['ImportOnlyPaid']) {
            // special case (bug):
            // ImportOnlyPaid = true, but an order with the same ExtendedOrderID has not been considered:
            // in magnalister DB, one of the items has the shipping costs for all, the others 0
            return max($existingShippingCost, $currItemShippingCost);
        }
        $internaldataArray = unserialize($this->o['internaldata']);

        if (array_key_exists('addCost', $internaldataArray)) {# $addCost gesetzt
            $addCost = $internaldataArray['addCost'];
            # existingAddCost: ausser dem ersten Item und aktueller Bestellung
            $existingAddCost = ($totalNumberOfItems - 1 - $currProductsCount) * $addCost;
            $firstItemShippingCost = $existingShippingCost - $existingAddCost;
            # currSingleItemShippingCost: erstes Stueck der aktuellen Bestellung
            $currSingleItemShippingCost = $currItemShippingCost - (($currProductsCount - 1) * $addCost);
            $totalAddCost = $existingAddCost + ($currProductsCount * $addCost);
            if ($firstItemShippingCost >= $currSingleItemShippingCost) {
                $totalShippingCost = $firstItemShippingCost + $totalAddCost;
            } else {
                $totalShippingCost = $currSingleItemShippingCost + $totalAddCost;
            }
        } else {# kein $addCost, alles voll berechnen
            $totalShippingCost = $existingShippingCost + $currItemShippingCost;
        }

        $minAmountForDiscount = (array_key_exists('minAmountForDiscount', $internaldataArray))
            ? $internaldataArray['minAmountForDiscount']
            : 0
        ;
        $minItemCountForDiscount = (array_key_exists('minItemCountForDiscount', $internaldataArray))
            ? $internaldataArray['minItemCountForDiscount']
            : 2
        ;
        if (
            array_key_exists('maxCostPerOrder', $internaldataArray)
            && ($totalPriceWOShipping >= $minAmountForDiscount)
            && ($totalNumberOfItems   >= $minItemCountForDiscount)
        ) {
            $totalShippingCost = min($totalShippingCost, $internaldataArray['maxCostPerOrder']);
        }
        if ($totalShippingCost < 0) {
            $totalShippingCost = 0;
        }
        return $totalShippingCost;
    }

    /**
     * Calculates the shipping costs if an existing order will be merged
     * before calculating the shipping tax.
     */
    protected function processShippingTax() {
        // will never happen because API will returns always this field
        if (!array_key_exists('Shipping', $this->o['orderTotal'])) {
            $this->o['orderTotal']['Shipping'] = array(
                'value' => 0.0,
                'title' => $this->marketplaceTitle,
                'class' => 'ot_shipping',
                'sort_order' => defined('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER') ? MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER : 50
            );
        }

        $existingShippingCost = (float)MagnaDB::gi()->fetchOne(eecho('
		    SELECT value
		      FROM '.TABLE_ORDERS_TOTAL.'
		     WHERE orders_id = '.$this->cur['OrderID'].'
		           AND class = "ot_shipping"
		  ORDER BY value DESC 
		     LIMIT 1
		', $this->verbose));

        $totalNumberOfItems = (int)MagnaDB::gi()->fetchOne(eecho('
			SELECT SUM(products_quantity)
			  FROM '.TABLE_ORDERS_PRODUCTS.'
			 WHERE orders_id = '.$this->cur['OrderID'].'
		', $this->verbose));

        $totalPriceWOShipping = (float)MagnaDB::gi()->fetchOne(eecho('
		    SELECT SUM(final_price)
		      FROM '.TABLE_ORDERS_PRODUCTS.'
		     WHERE orders_id = '.$this->cur['OrderID'].'
		', $this->verbose));

        if (($existingShippingCost > 0) || ($totalNumberOfItems > $this->o['_processingData']['ProductsCount'])) {
            /* Merged order */
            $this->o['orderTotal']['Shipping']['value'] = $this->calculateShippingCost(
                $existingShippingCost,
                $this->o['orderTotal']['Shipping']['value'],
                $totalNumberOfItems,
                $totalPriceWOShipping,
                $this->o['_processingData']['ProductsCount']
            );
            $this->o['_processingData']['mergedOrders'] = true;
            if ($this->verbose) {
                echo "\n".'Merged ShippingCost: '.$this->o['orderTotal']['Shipping']['value']."\n";
            }
        }
        parent::processShippingTax();
    }

    protected function insertOrdersTotal() {
        if (array_key_exists('mergedOrders', $this->o['_processingData'])) {
            $fSubTotal = $fShippingTax = $fNetto = 0.0;
            // different style of storing prices in TABLE_ORDERS_PRODUCTS
            // between osCommerce (+ clones) and xt:Commerce 3 (+ clones):
            // for osCommerce, final_price = products_price,
            // for the other, tax and quantity are already included
            if ('oscommerce' == SHOPSYSTEM) {
                $identifyFinalPrice = '(final_price * (100 + products_tax) * products_quantity) / 100';
            } else {
                $identifyFinalPrice = 'final_price';
            }
            $aTaxes = MagnaDB::gi()->fetchArray("
				SELECT sum($identifyFinalPrice) as final_price, products_tax
				FROM ".TABLE_ORDERS_PRODUCTS."
				WHERE orders_id = '".$this->cur['OrderID']."'
				GROUP BY products_tax
				ORDER BY products_tax
			");
            foreach ($aTaxes as $Tax) {
                $fSubTotal += $Tax['final_price'];
                $fShippingTax = max($fShippingTax, $Tax['products_tax']);
            }
            /*//{search: 1427198983}
                $fShippingTax = $this->config['MwStShipping'] <= 0 ? $this->config['MwStShipping'] : 0;
            //*/
            $fShippingValue = $this->o['orderTotal']['Shipping']['value'];
            $fShippingTaxValue = $fShippingValue - ($fShippingValue / (1 + $fShippingTax / 100));
            $iTaxSortOrder = 0;
            $orders_total_id_netto = 0;
            $aTotals = array();
            foreach (MagnaDB::gi()->fetchArray("
				SELECT * 
				FROM ".TABLE_ORDERS_TOTAL."
				WHERE orders_id = '".$this->cur['OrderID']."'
				ORDER BY sort_order
			") as $aDbTotal) {
                foreach ($this->o['orderTotal'] as $aCurrentTotal) {
                    if (isset($aCurrentTotal['class']) && $aDbTotal['class'] == $aCurrentTotal['class']) {
                        if ($aDbTotal['class'] == 'ot_subtotal') {
                            $aTotals['ot_subtotal'] = array_merge($aDbTotal, array(
                                'value' => $fSubTotal,
                                'text' =>  $this->simplePrice->setPrice($fSubTotal)->format()
                            ));
                            break;
                        } elseif ($aDbTotal['class'] == 'ot_shipping') {
                            $aTotals['ot_shipping'] = array_merge($aDbTotal, array(
                                'value' => $aCurrentTotal['value'],
                                'title' => MAGNA_LABEL_ORDERS_SHIPPING,
                                'text' => $this->simplePrice->setPrice($aCurrentTotal['value'])->format(),
                            ));
                            break;
                        } elseif ($aDbTotal['class'] == 'ot_tax') {
                            if (!defined('MODULE_ORDER_TOTAL_TAX_STATUS') || (MODULE_ORDER_TOTAL_TAX_STATUS != 'true')) {
                                break;
                            }
                            $iTaxSortOrder = defined('MODULE_ORDER_TOTAL_TAX_SORT_ORDER') ? MODULE_ORDER_TOTAL_TAX_SORT_ORDER : max($aDbTotal['sort_order'], $iTaxSortOrder);
                            $aTax = array_pop($aTaxes);
                            $fTax = $aTax['products_tax'];
                            $fValue = $aTax['final_price'] - ($aTax['final_price'] / (1 + $fTax / 100)) + ($fTax == $fShippingTax ? $fShippingTaxValue : 0);
                            $aTotals['ot_tax'.$fTax] = array_merge($aDbTotal, array(
                                'title' => ML_LABEL_INCL.' '.round($fTax, 2).'% '.MAGNA_LABEL_ORDERS_TAX,
                                'value' => $fValue,
                                'text' =>  $this->simplePrice->setPrice($fValue)->format(),
                            ));
                            $fNetto -= $aTotals['ot_tax'.$fTax]['value'];
                            break;
                        } elseif ($aDbTotal['class'] == 'ot_total') {
                            $aTotals['ot_total'] = array_merge($aDbTotal, array(
                                'value' => $fSubTotal + $fShippingValue,
                                'text' => $this->simplePrice->setPrice($fSubTotal + $fShippingValue)->format(),
                            ));
                            $fNetto += $aTotals['ot_total']['value'];
                            break;
                        } elseif ($aDbTotal['class'] == 'ot_total_netto') {
                            $orders_total_id_netto = $aDbTotal['orders_total_id'];
                            break;
                        }
                    }
                }
            }
            if (defined('MODULE_ORDER_TOTAL_TAX_STATUS') && (MODULE_ORDER_TOTAL_TAX_STATUS == 'true')) {
                while ($aTax = array_pop($aTaxes)) { // add missing taxes
                    $fTax = round($aTax['products_tax'], 2);
                    $fValue = $aTax['final_price'] - ($aTax['final_price'] / (1 + $fTax / 100)) + ($fTax == $fShippingTax ? $fShippingTaxValue : 0);
                    $aTotals['ot_tax'.$fTax] = array(
                        'title' => ML_LABEL_INCL.' '.$fTax.'% '.MAGNA_LABEL_ORDERS_TAX,
                        'value' => $fValue,
                        'text' =>  $this->simplePrice->setPrice($fValue)->format(),
                        'class' => 'ot_tax',
                        'sort_order' => $iTaxSortOrder
                    );
                    $fNetto -= $aTotals['ot_tax'.$fTax]['value'];
                }
                // netto for Gambio (only if parent class inserted it in the first single-order)
                if (   ('gambio' == SHOPSYSTEM)
                    && isset($orders_total_id_netto)) {
                    $aTotals['ot_total_netto'] = array (
                        'value' => $fNetto,
                        'text' =>  $this->simplePrice->setPrice($fNetto)->format(),
                        'orders_total_id' => $orders_total_id_netto,
                    );
                }
            }
            foreach ($aTotals as $aTotal) {
                $aTotal['orders_id'] = $this->cur['OrderID'];
                if (isset($aTotal['orders_total_id'])) {
                    $this->db->update(TABLE_ORDERS_TOTAL, $aTotal, array('orders_total_id' => $aTotal['orders_total_id']));
                } else {
                    $this->insert(TABLE_ORDERS_TOTAL, $aTotal);
                }
            }
        } else {
            foreach ($this->o['orderTotal'] as $key => &$entry) {
                $entry['title'] = ('ot_shipping' == $entry['class'])
                    ? MAGNA_LABEL_ORDERS_SHIPPING
                    : $entry['title']
                ;
            }
            parent::insertOrdersTotal();
        }
    }


    private function calculateInternalData($shippingCost, $existingInternalData = false) {
        $products_id = magnaSKU2pID($this->o['products'][0]['products_id'], true);
        if ('artNr' == getDBConfigValue('general.keytype', '0')) {
            // API gives the main SKU in products_id field, and variation SKU in products_model (same if least not given)
            $products_model = $this->o['products'][0]['products_id'];
        } else {
            $products_model = trim(MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.'
				WHERE products_id=\''.$products_id.'\' LIMIT 1'
            ));
        }
        $domestic = $this->isDomestic($this->o['orderInfo']['ShippingCountryISO']);
        $shippingProfiles = getDBConfigValue('ebay.shippingprofiles', $this->mpID, null);

        $currProductsShippingDetails = false;
        $useDiscount = true;

        if (0 != $products_id) {
            $currProductsShippingDetailsAndSellerProfiles = MagnaDB::gi()->fetchRow(eecho("
				SELECT ShippingDetails, SellerProfiles
				  FROM ".TABLE_MAGNA_EBAY_PROPERTIES."
				 WHERE     ".(('artNr' != getDBConfigValue('general.keytype', '0'))
                    ? "products_id = '".$products_id."'"
                    : "products_model = '".MagnaDB::gi()->escape($products_model)."'"
                )."
				       AND mpID = '".$this->mpID."'",
                false
            ));
            if (!empty($currProductsShippingDetailsAndSellerProfiles['SellerProfiles'])) {
                $this->takeShippingDetailsFromSellerProfile($currProductsShippingDetailsAndSellerProfiles);
            }
            $currProductsShippingDetails = $currProductsShippingDetailsAndSellerProfiles['ShippingDetails'];
            if (false != $currProductsShippingDetails) {
                $currProductsShippingDetailsArr = json_decode($currProductsShippingDetails, true);
                if ($domestic) {
                    if (array_key_exists('LocalProfile', $currProductsShippingDetailsArr)) {
                        $profileID = $currProductsShippingDetailsArr['LocalProfile'];
                        $useDiscount = $currProductsShippingDetailsArr['LocalPromotionalDiscount'];
                    } else {
                        $profileID   = getDBConfigValue('ebay.default.shippingprofile.local',$this->mpID, 0);
                        $useDiscount = getDBConfigValue(array('ebay.shippingdiscount.local', 'val'), $this->mpID, true);
                    }
                } else {
                    if (array_key_exists('InternationalProfile', $currProductsShippingDetailsArr)) {
                        $profileID = $currProductsShippingDetailsArr['InternationalProfile'];
                        $useDiscount = $currProductsShippingDetailsArr['InternationalPromotionalDiscount'];
                    } else {
                        $profileID   = getDBConfigValue('ebay.default.shippingprofile.international',$this->mpID, 0);
                        $useDiscount = getDBConfigValue(array('ebay.shippingdiscount.international', 'val'), $this->mpID, true);
                    }
                }
            }
        }
        if ((0 == $products_id) || (false == $currProductsShippingDetails)) {
            if ($domestic) {
                $profileID = getDBConfigValue('ebay.default.shippingprofile.local', $this->mpID, 0);
                $useDiscount = getDBConfigValue(array('ebay.shippingdiscount.local', 'val'), $this->mpID, true);
            } else {
                $profileID = getDBConfigValue('ebay.default.shippingprofile.international', $this->mpID, 0);
                $useDiscount = getDBConfigValue(array('ebay.shippingdiscount.international', 'val'), $this->mpID, true);
            }
        }

        $newInternaldataArray = array (
            'singleShippingCost' => $shippingCost,
        );

        // add the additional costs to internal array
        if (   (null !== $shippingProfiles)
            && (!empty($profileID))
            && (!empty($shippingProfiles['Profiles']))
            && array_key_exists($profileID, $shippingProfiles['Profiles'])
            && array_key_exists('EachAdditionalAmount', $shippingProfiles['Profiles']["$profileID"])
        ) {
            if ($shippingProfiles['Profiles']["$profileID"]['EachAdditionalAmount'] >= 0) {
                $newInternaldataArray['addCost'] = trim($shippingProfiles['Profiles']["$profileID"]['EachAdditionalAmount']);
            } else {
                # negative EachAdditionalAmount (i.e. EachAdditionalAmountOff)
                # => take my ShippingCost minus (plus the negative) EachAdditionalAmount
                # as described in
                # http://developer.ebay.com/devzone/xml/docs/reference/ebay/types/DiscountNameCodeType.html
                $newInternaldataArray['addCost'] = max(0, ($shippingCost + trim($shippingProfiles['Profiles']["$profileID"]['EachAdditionalAmount'])));
            }
        }

        // update singleShippingCost if quantity > 1
        if (    ($this->o['products'][0]['products_quantity'] > 1)
            && ($newInternaldataArray['singleShippingCost'] > 0)
        ) {
            // if there are addition costs remove them from singeShippingCost ((ProductsQuantity - 1) * addCost)
            if (array_key_exists('addCost', $newInternaldataArray)) {
                $newInternaldataArray['singleShippingCost'] -= (($this->o['products'][0]['products_quantity'] -1) * $newInternaldataArray['addCost']);
            } else {
                $newInternaldataArray['singleShippingCost'] = ($newInternaldataArray['singleShippingCost'] / $this->o['products'][0]['products_quantity']);
            }
        }

        // useDiscount can be string, make it proper boolean if needed
        if ('false' === $useDiscount) {
            $useDiscount = false;
        }


        // if shippingProfiles are available, useDiscount is set, and a Discount is defined in eBay account
        if (    isset($shippingProfiles)
            && $useDiscount
            && array_key_exists('PromotionalShippingDiscount', $shippingProfiles)
        ) {
            # use newInternalData
            # addCost or maxShippingCost (if value > x)
            switch ($shippingProfiles['PromotionalShippingDiscount']['DiscountName']) {
                case ('MaximumShippingCostPerOrder'): {
                    $newInternaldataArray['maxCostPerOrder'] = trim($shippingProfiles['PromotionalShippingDiscount']['ShippingCost']);
                    break;
                }
                case ('ShippingCostXForAmountY'): {
                    $newInternaldataArray['minAmountForDiscount'] = trim($shippingProfiles['PromotionalShippingDiscount']['OrderAmount']);
                    $newInternaldataArray['maxCostPerOrder'] = trim($shippingProfiles['PromotionalShippingDiscount']['ShippingCost']);
                    break;
                }
                case ('ShippingCostXForItemCountN'): {
                    $newInternaldataArray['minItemCountForDiscount'] = trim($shippingProfiles['PromotionalShippingDiscount']['ItemCount']);
                    $newInternaldataArray['maxCostPerOrder'] = trim($shippingProfiles['PromotionalShippingDiscount']['ShippingCost']);
                }
                default: break;
            }
        }

        if (false !== $existingInternalData) {
            // always use the rules of the Item with the biggest ShippingCost
            // (so, if the current one is smaller, use the old one)
            // if ShippingCosts are equal, use the old one
            $existingInternalDataArray = unserialize($existingInternalData);
            if (
                is_array($existingInternalDataArray)
                && array_key_exists('singleShippingCost', $existingInternalDataArray)
                && $existingInternalDataArray['singleShippingCost'] >= $newInternaldataArray['singleShippingCost']
            ) {
                return $existingInternalData;
            }
        }
        // no existingInternalData or existing singleShippingCost < current singleShippingCost
        $newInternaldata = serialize($newInternaldataArray);
        return $newInternaldata;
    }

    private function takeShippingDetailsFromSellerProfile(&$currProductsShippingDetailsAndSellerProfiles) {
        $aShippingDetails = json_decode($currProductsShippingDetailsAndSellerProfiles['ShippingDetails'], true);
        $aSellerProfiles  = json_decode($currProductsShippingDetailsAndSellerProfiles['SellerProfiles'], true);
        if (!isset($aSellerProfiles['Shipping'])) return;
        $aSellerProfileContents = getDBConfigValue('ebay.sellerprofile.contents', $this->mpID, '');
        if(empty($aSellerProfileContents)) return;
        if (    !is_array($aSellerProfileContents)
            || !array_key_exists('Shipping', $aSellerProfileContents)
            || !array_key_exists($aSellerProfiles['Shipping'], $aSellerProfileContents['Shipping'])) {
            return;
        }
        $jShippingProfile = str_replace(array(
            'shipping.local',
            'shipping.international',
            'location',
            'service',
            'cost',
            'shippingprofile.local',
            'shippingprofile.international',
            'shippingdiscount.local',
            'shippingdiscount.international',
            '{\"val\":true}',
            '{\"val\":false}'),
            array('ShippingServiceOptions',
                'InternationalShippingServiceOption',
                'ShipToLocation',
                'ShippingService',
                'ShippingServiceCost',
                'LocalProfile',
                'InternationalProfile',
                'LocalPromotionalDiscount',
                'InternationalPromotionalDiscount',
                'true',
                'false'),
            json_encode($aSellerProfileContents['Shipping'][$aSellerProfiles['Shipping']]));
        if ($this->verbose) {
            echo print_m(json_decode($jShippingProfile, true), 'takeShippingDetailsFromSellerProfile, Details updated');
        }
        $currProductsShippingDetailsAndSellerProfiles['ShippingDetails'] = $jShippingProfile;
    }

    /**
     * Returns an array with the replacement keys and the content for the promotion mail.
     * @return array
     */
    protected function generatePromoMailContent() {
        $aContent = parent::generatePromoMailContent();
        $aeBaySpecificMailFields = array(
            '#FIRSTNAME#' => $this->o['customer']['customers_firstname'],
            '#LASTNAME#'  => $this->o['customer']['customers_lastname'],
            '#MORDERID#'  => $this->o['orderInfo']['eBayOrderID'],
            '#EORDERID#'  => '', // set below, only if available
            '#SHOPURL#'   => '', // remove if set (eBay doesn't allow it)
        );
        if (isset($this->o['orderInfo']['ExtendedOrderID'])) {
            $aeBaySpecificMailFields['#EORDERID#'] = $this->o['orderInfo']['ExtendedOrderID'];
        }
        return array_merge($aContent, $aeBaySpecificMailFields);
    }

    /*
     * For newer Gambio versions: set total weight in the orders table
     * Keep the old weight for combined orders
     */
    protected function setOrderWeight() {
        if (!MagnaDB::gi()->columnExistsInTable('order_total_weight', TABLE_ORDERS)) {
            return;
        }
        $iExistingWeight = $this->db->fetchOne('SELECT order_total_weight
			 FROM '.TABLE_ORDERS.'
			WHERE orders_id = '.$this->cur['OrderID']);
        parent::setOrderWeight();
        if ($iExistingWeight > 0) {
            $this->db->query('UPDATE '.TABLE_ORDERS.'
				SET order_total_weight = order_total_weight + '.$iExistingWeight.'
				WHERE orders_id = '.$this->cur['OrderID']);
        }
    }

    protected function insertProductAttribute($iProductsId, $aOption, $sSKU) {
        if (empty($aOption['options_name'])) {
            return;
        }

        $aOrderProductsAttribute = array(
            'orders_id' => $this->p['orders_id'],
            'orders_products_id' => $iProductsId,
            'products_options' => $aOption['options_name'],
            'products_options_values' => $aOption['options_values_name'],
            'options_values_price' => 0.0,
            'price_prefix' => ''
        );

        if ($this->config['DBColumnExists']['orders_products_attributes.products_options_id']) {
            $aOrderProductsAttribute['products_options_id'] = $aOption['options_id'];
            $aOrderProductsAttribute['products_options_values_id'] = $aOption['options_values_id'];
        }
        /**
         * ebay supports multiple variations so attribute-sku will be calculated like this
         */
        if ($this->config['DBColumnExists']['orders_products_attributes.products_attributes_model']) {
            $aOrderProductsAttribute['products_attributes_model'] = $this->getProductAttributeData((int) $this->p['products_id'], (int) $aOption['options_id'], (int) $aOption['options_values_id'], 'attributes_model');
        }

        if ($this->config['DBColumnExists']['orders_products_attributes.attributes_model']) {//modified 2.0.0
            $aOrderProductsAttribute['attributes_model'] = $this->getProductAttributeData((int) $this->p['products_id'], (int) $aOption['options_id'], (int) $aOption['options_values_id'], 'attributes_model');
        }

        if ($this->config['DBColumnExists']['orders_products_attributes.attributes_ean']) {//modified 2.0.0
            $aOrderProductsAttribute['attributes_ean'] = $this->getProductAttributeData((int)$this->p['products_id'],(int)$aOption['options_id'], (int)$aOption['options_values_id'], 'attributes_ean');
        }

        if ($this->config['DBColumnExists']['orders_products_attributes.products_attributes_id']) {
            $aOrderProductsAttribute['products_attributes_id'] = ($aOption['id'] == false) ? 0 : $aOption['id'];
        }

        $this->insert(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $aOrderProductsAttribute);
    }

    /**
     * add 'blacklisted-' to customer's e-mail address
     *  if configured so (not recommended)
     *
     * @return array
     */
    protected function insertCustomer() {
        if (getDBConfigValue(array($this->marketplace . '.mailaddress.blacklist', 'val'), $this->mpID, false)) {
            if ($this->verbose) echo __FUNCTION__.": ebay.mailaddress.blacklist == true\n";
            $this->o['customer']['customers_email_address'] = 'blacklisted-'.$this->o['customer']['customers_email_address'];
        }

        return parent::insertCustomer();
    }

    /**
     * Remove "blacklisted-" from mail if present and used from customer to ensure to send the promotion mail
     */
    protected function sendPromoMail() {
        if (($this->config['MailSend'] != 'true') || (get_class($this->db) == 'MagnaTestDB')) {
            return;
        }
        // mail addresses for eBay customers can have a 'blacklisted-' added by us,
        // if the customer configured so. Therefore, if the merchant wishes to send an e-mail, we have to
        // remove this prefix.
        sendSaleConfirmationMail(
            $this->mpID,
            str_replace('blacklisted-', '', $this->o['customer']['customers_email_address']),
            $this->generatePromoMailContent()
        );
    }

    /**
     * add 'blacklisted-' to customer's e-mail address
     *  if configured so (not recommended)
     */
    protected function doBeforeInsertOrder() {
        if (getDBConfigValue(array($this->marketplace . '.mailaddress.blacklist', 'val'), $this->mpID, false)) {
            if ($this->verbose) echo __FUNCTION__.": ebay.mailaddress.blacklist == true\n";
            $this->o['order']['customers_email_address'] = 'blacklisted-'.$this->o['order']['customers_email_address'];
        }
    }

}
