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

/**
 * Helper for exporting orders.
 */
class ShopgateCustomerOrderModel
{
    
    const TOTAL_CLASS_BILLSAFE              = 'ot_billsafe';
    const TOTAL_CLASS_COD_FEE               = 'ot_cod_fee';
    const TOTAL_CLASS_COUPON                = 'ot_coupon';
    const TOTAL_CLASS_DISCOUNT              = 'ot_discount';
    const TOTAL_CLASS_GIFT_VOUCHER          = 'ot_gv';
    const TOTAL_CLASS_SHIPPING              = 'ot_shipping';
    const TOTAL_CLASS_KLARNA_FEE            = 'ot_klarna_fee';
    const TOTAL_CLASS_LOW_ORDER_FEE         = 'ot_loworderfee';
    const TOTAL_CLASS_PAYMENT               = 'ot_payment';
    const TOTAL_CLASS_PERSONAL_SHIPPING_FEE = 'ot_ps_fee';
    const TOTAL_CLASS_SOFORT                = 'ot_sofort';
    const TOTAL_CLASS_SUBTOTAL              = 'ot_subtotal';
    const TOTAL_CLASS_SUBTOTAL_NO_TAX       = 'ot_subtotal_no_tax';
    const TOTAL_CLASS_TAX                   = 'ot_tax';
    const TOTAL_CLASS_TOTAL                 = 'ot_total';
    
    /** @var ShopgateConfigModified */
    private $config;
    
    /** @var array */
    private $order;
    
    /** @var string */
    private $language;
    
    /** @var int|string */
    private $languageId;
    
    /**
     * @param ShopgateConfigModified $config
     * @param int|string             $languageId
     */
    public function __construct(ShopgateConfigModified $config, $languageId)
    {
        $this->config = $config;
        $this->languageId = $languageId;
    }
    
    /**
     * get all orders to an customer from the database
     * 
     * @param string $customerToken
     * @param string $customerLanguage
     * @param int    $limit
     * @param int    $offset
     * @param string $orderDateFrom
     * @param string $sortOrder
     *
     * @return array
     */
    public function getOrders($customerToken, $customerLanguage, $limit, $offset, $orderDateFrom, $sortOrder)
    {
        $this->language = $customerLanguage;
        switch ($sortOrder) {
            case 'created_asc':
                $orderBy = 'date_purchased';
                break;
            case 'created_desc':
            default:
                $orderBy = 'date_purchased DESC';
                break;
        }
        
        $customerModel = new ShopgateCustomerModel($this->config, $this->languageId);
        $customerId    = $customerModel->getCustomerIdByToken($customerToken);
        
        if (empty($customerId)) {
            return array();
        }
        
        $ordersQuery = ShopgateWrapper::db_query(
            "
			SELECT o.*, s.`shopgate_order_number`
			FROM      `" . TABLE_ORDERS . "` o
			LEFT JOIN `" . TABLE_ORDERS_SHOPGATE_ORDER . "` s
			ON    o.`orders_id` = s.`orders_id`
			WHERE o.`customers_id` = {$customerId}
			AND   o.`date_purchased` >= '{$orderDateFrom}'
			ORDER BY $orderBy
			LIMIT $limit
			OFFSET $offset
		"
        );
        
        $result = array();
        while ($order = ShopgateWrapper::db_fetch_array($ordersQuery)) {
            $result[] = $this->buildExternalOrder($order);
        }
        
        return $result;
    }
    
    /**
     * fill the ShopgateExternalOrder object with the order information from the shop system 
     * 
     * @param array $modifiedOrder
     *
     * @return ShopgateExternalOrder
     */
    private function buildExternalOrder(array $modifiedOrder)
    {
        $this->order = $modifiedOrder;
        foreach ($this->order as &$value) {
            $value = utf8_encode($value);
        }
        
        $this->loadData('totals', TABLE_ORDERS_TOTAL);
        $this->loadData('products', TABLE_ORDERS_PRODUCTS);
        $this->loadData('attributes', TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
        $this->loadData('status_history', TABLE_ORDERS_STATUS_HISTORY);
        
        $result = new ShopgateExternalOrder();
        
        $result->setOrderNumber($this->getOrderNumber());
        $result->setExternalOrderNumber($this->getExternalOrderNumber());
        $result->setExternalOrderId($this->getExternalOrderId());
        $result->setStatusName($this->getStatusName());
        $result->setCreatedTime($this->getCreatedTime());
        $result->setMail($this->getMail());
        $result->setPhone($this->getPhone());
        $result->setMobile($this->getMobile());
        $result->setCustomFields($this->getCetCustomFields());
        $result->setInvoiceAddress($this->getInvoiceAddress());
        $result->setDeliveryAddress($this->getDeliveryAddress());
        $result->setCurrency($this->getCurrency());
        $result->setAmountComplete($this->getAmountComplete());
        $result->setIsPaid($this->getIsPaid());
        $result->setPaymentMethod($this->getPaymentMethod());
        $result->setPaymentTime($this->getPaymentTime());
        $result->setPaymentTransactionNumber($this->getPaymentTransactionNumber());
        $result->setIsShippingCompleted($this->getIsShippingCompleted());
        $result->setShippingCompletedTime($this->getShippingCompletedTime());
        $result->setDeliveryNotes($this->getDeliveryNotes());
        $result->setOrderTaxes($this->getOrderTaxes());
        $result->setExtraCosts($this->getExtraCosts());
        $result->setExternalCoupons($this->getExternalCoupons());
        $result->setItems($this->getItems());
        
        return $result;
    }
    
    /**
     * load order data from database regarding the order id
     * 
     * @param string $key
     * @param string $table
     */
    private function loadData($key, $table)
    {
        $this->order[$key] = array();
        
        $totalsQuery = xtc_db_query(
            "
            SELECT *
            FROM `$table`
            WHERE `orders_id` = {$this->order['orders_id']}
        "
        );
        while ($entry = xtc_db_fetch_array($totalsQuery)) {
            $this->order[$key][] = $entry;
        }
    }
    
    /**
     * load order status data from the database
     * 
     * @return string
     */
    private function getStatusName()
    {
        $languageId = ShopgatePluginInitHelper::getLanguageIdByIsoCode($this->language);
        $query      = ShopgateWrapper::db_query(
            "
			SELECT `orders_status_name`
			FROM `" . TABLE_ORDERS_STATUS . "`
			WHERE `orders_status_id` = {$this->order['orders_status']}
			AND `language_id` = {$languageId}
		"
        );
        $result     = ShopgateWrapper::db_fetch_array($query);
        
        return !empty($result['orders_status_name']) ? $result['orders_status_name'] : null;
    }
    
    /**
     * @return string
     */
    private function getOrderNumber()
    {
        return $this->order['shopgate_order_number'];
    }
    
    /**
     * @return string
     */
    private function getExternalOrderNumber()
    {
        return $this->order['orders_id'];
    }
    
    /**
     * @return string
     */
    private function getExternalOrderId()
    {
        return $this->order['orders_id'];
    }
    
    /**
     * @return string timestamp
     */
    private function getCreatedTime()
    {
        return $this->order['date_purchased'];
    }
    
    /**
     * @return string
     */
    private function getMail()
    {
        return $this->order['customers_email_address'];
    }
    
    /**
     * @return string
     */
    private function getPhone()
    {
        return $this->order['customers_telephone'];
    }
    
    /**
     * @return string
     */
    private function getMobile()
    {
        return null;
    }
    
    /**
     * @return ShopgateOrderCustomField[]
     */
    private function getCetCustomFields()
    {
        return array();
    }
    
    /**
     * @return ShopgateAddress
     */
    private function getInvoiceAddress()
    {
        $result = new ShopgateAddress();
        
        $result->setAddressType(ShopgateAddress::INVOICE);
        $result->setCity($this->order['delivery_city']);
        $result->setCompany($this->order['delivery_company']);
        $result->setCountry($this->order['delivery_country_iso_code_2']);
        $result->setFirstName($this->order['delivery_firstname']);
        $result->setLastName($this->order['delivery_lastname']);
        $result->setState($this->order['delivery_state']);
        $result->setStreet1($this->order['delivery_street_address']);
        $result->setStreet2($this->order['delivery_suburb']);
        $result->setZipcode($this->order['delivery_postcode']);
        
        return $result;
    }
    
    /**
     * @return ShopgateAddress
     */
    private function getDeliveryAddress()
    {
        $result = new ShopgateAddress();
        
        $result->setAddressType(ShopgateAddress::DELIVERY);
        $result->setCity($this->order['billing_city']);
        $result->setCompany($this->order['billing_company']);
        $result->setCountry($this->order['billing_country_iso_code_2']);
        $result->setFirstName($this->order['billing_firstname']);
        $result->setLastName($this->order['billing_lastname']);
        $result->setState($this->order['billing_state']);
        $result->setStreet1($this->order['billing_street_address']);
        $result->setStreet2($this->order['billing_suburb']);
        $result->setZipcode($this->order['billing_postcode']);
        
        return $result;
    }
    
    /**
     * @return string
     */
    private function getCurrency()
    {
        return $this->order['currency'];
    }
    
    /**
     * @return string
     */
    private function getAmountComplete()
    {
        foreach ($this->order['totals'] as $total) {
            if ($total['class'] == self::TOTAL_CLASS_TOTAL) {
                return $total['value'];
            }
        }
        
        return "0.0";
    }
    
    /**
     * @return int (0|1)
     */
    private function getIsPaid()
    {
        return null;
    }
    
    /**
     * @return string
     */
    private function getPaymentMethod()
    {
        $languageDir            = ShopgatePluginInitHelper::getLanguageDirectoryByIsoCode($this->language);
        $code                   = $this->order['payment_method'];
        $paymentClassFile       = DIR_WS_MODULES . "payment/$code.php";
        $paymentTranslationFile = DIR_FS_CATALOG . "lang/$languageDir/modules/payment/$code.php";
        
        if (file_exists($paymentClassFile) && file_exists($paymentTranslationFile)) {
            require_once($paymentClassFile);
            require_once($paymentTranslationFile);
            
            $class = new $code();
            
            return $class->title;
        }
        
        return $code;
    }
    
    /**
     * @return string
     */
    private function getPaymentTime()
    {
        return null;
    }
    
    /**
     * @return string
     */
    private function getPaymentTransactionNumber()
    {
        return null;
    }
    
    /**
     * @return bool
     */
    private function getIsShippingCompleted()
    {
        foreach ($this->order['status_history'] as $status) {
            if ($status['orders_status_id'] == $this->config->getOrderStatusShipped()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @return string
     */
    private function getShippingCompletedTime()
    {
        foreach ($this->order['status_history'] as $status) {
            if ($status['orders_status_id'] == $this->config->getOrderStatusShipped()) {
                return $status['date_added'];
            }
        }
        
        return null;
    }
    
    /**
     * @return ShopgateDeliveryNote[]
     */
    private function getDeliveryNotes()
    {
        return array();
    }
    
    /**
     * generate tax data to the current order
     * 
     * @return ShopgateExternalOrderTax[]
     */
    private function getOrderTaxes()
    {
        $result = array();
        
        foreach ($this->order['totals'] as $total) {
            $total['value'] = (float)$total['value'];
            if ($total['class'] != self::TOTAL_CLASS_TAX || empty($total['value'])) {
                continue;
            }
            
            $taxPercent = null;
            $matches    = array();
            if (preg_match('/(?P<tax>[0-9]+) ?%/', $total['title'], $matches)) {
                $taxPercent = $matches['tax'];
            }
            
            $tax = new ShopgateExternalOrderTax();
            $tax->setAmount($total['value']);
            $tax->setLabel(trim($total['title'], ':'));
            $tax->setTaxPercent($taxPercent);
            
            $result[] = $tax;
        }
        
        return $result;
    }
    
    /**
     * gather the extra costs to the current order
     * 
     * @return ShopgateExternalOrderExtraCost[]
     */
    private function getExtraCosts()
    {
        $mapping = array(
            self::TOTAL_CLASS_BILLSAFE              => ShopgateExternalOrderExtraCost::TYPE_PAYMENT,
            self::TOTAL_CLASS_COD_FEE               => ShopgateExternalOrderExtraCost::TYPE_PAYMENT,
            self::TOTAL_CLASS_KLARNA_FEE            => ShopgateExternalOrderExtraCost::TYPE_PAYMENT,
            self::TOTAL_CLASS_PAYMENT               => ShopgateExternalOrderExtraCost::TYPE_PAYMENT,
            self::TOTAL_CLASS_SOFORT                => ShopgateExternalOrderExtraCost::TYPE_PAYMENT,
            
            self::TOTAL_CLASS_PERSONAL_SHIPPING_FEE => ShopgateExternalOrderExtraCost::TYPE_SHIPPING,
            self::TOTAL_CLASS_SHIPPING              => ShopgateExternalOrderExtraCost::TYPE_SHIPPING,
            
            self::TOTAL_CLASS_DISCOUNT              => ShopgateExternalOrderExtraCost::TYPE_MISC,
            self::TOTAL_CLASS_LOW_ORDER_FEE         => ShopgateExternalOrderExtraCost::TYPE_MISC,
        );
        
        $result = array();
        
        foreach ($this->order['totals'] as $total) {
            if (!isset($mapping[$total['class']])) {
                continue;
            }
            $cost = new ShopgateExternalOrderExtraCost();
            $cost->setAmount($total['value']);
            $cost->setType($mapping[$total['class']]);
            $cost->setLabel(trim($total['title'], ':'));
            $result[] = $cost;
        }
        
        return $result;
    }
    
    /**
     * gather coupon data to the current order
     * 
     * @return ShopgateExternalCoupon[]
     */
    private function getExternalCoupons()
    {
        $result = array();
        
        foreach ($this->order['totals'] as $total) {
            if ($total['class'] != self::TOTAL_CLASS_COUPON) {
                continue;
            }
            $coupon = new ShopgateExternalCoupon();
            $coupon->setName($total['title']);
            $coupon->setAmountGross($total['value'] * -1);
            $coupon->setCurrency($this->order['currency']);
            $result[] = $coupon;
        }
        
        return $result;
    }
    
    /**
     * gather product data to the current order
     * 
     * @return ShopgateExternalOrderItem[]
     */
    private function getItems()
    {
        $result = array();
        
        $attributesByProduct = array();
        foreach ($this->order['attributes'] as $attribute) {
            $attributesByProduct[$attribute['orders_products_id']][] = $attribute;
        }
        
        foreach ($this->order['products'] as $product) {
            $name = $product['products_name'];
            foreach ($attributesByProduct[$product['orders_products_id']] as $attribute) {
                $name .= ", {$attribute['products_options']}: {$attribute['products_options_values']}";
            }
            
            $item = new ShopgateExternalOrderItem();
            $item->setItemNumber($product['products_id']);
            $item->setItemNumberPublic($product['products_model']);
            $item->setName($name);
            $item->setQuantity($product['products_quantity']);
            $item->setTaxPercent($product['products_tax']);
            $item->setUnitAmountWithTax($product['products_price']);
            $item->setCurrency($this->order['currency']);
            
            $result[] = $item;
        }
        
        return $result;
    }
}
