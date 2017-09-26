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
class ShopgateCouponModel extends ShopgateObject
{
    /**
     * @var ShopgateConfigModified $config
     */
    private $config;
    
    /**
     * @var int $languageId
     */
    private $languageId;
    
    /**
     * @var string $language
     */
    private $language;
    
    /**
     * @var string $currencyCode
     */
    private $currencyCode;
    
    /**
     * @var int $countryId
     */
    private $countryId;
    
    const SG_COUPON_TYPE_GIFT          = 'G';
    const SG_COUPON_TYPE_PERCENTAGE    = 'P';
    const SG_COUPON_TYPE_FIX           = 'F';
    const SG_COUPON_TYPE_FREE_SHIPPING = 'S';
    const SG_COUPON_ACTIVE             = 'Y';
    
    /**
     * @param ShopgateConfigModified $config
     * @param int                    $languageId
     * @param string                 $language
     * @param array                  $currency
     * @param int                    $countryId
     */
    public function __construct(ShopgateConfigModified $config, $languageId, $language, $currency, $countryId)
    {
        $this->config       = $config;
        $this->languageId   = $languageId;
        $this->currencyCode = $currency['code'];
        $this->language     = $language;
        $this->countryId    = $countryId;
        $this->initializeCouponModule();
    }
    
    /**
     * check if a coupon is valid
     * there are different types of coupons
     * G = gift, this coupon will be created if a customer registered a new account in the shop
     * S = shipping, free shipping i guess
     * F = fixed, the coupon value is a fixed value, which needs to be subtracted from item(s)/ the whole cart,
     *     depending on the coupon's setting
     * P = Percentage, the coupon amount is a percentage value which needs to be used to calculate the amount to
     *     subtract for item(s)/ the whole cart, depending on the coupon's setting
     *
     * @param ShopgateCart          $cart
     * @param ShopgateItemCartModel $cartItemModel
     * @param array                 $coupon
     * @param float|string          $orderAmount
     *
     * @return string
     */
    public function validateCoupon(
        ShopgateCart $cart,
        ShopgateItemCartModel $cartItemModel,
        array $coupon,
        $orderAmount
    ) {
        $msg = "";
        if ($coupon['coupon_type'] != self::SG_COUPON_TYPE_GIFT) {
            
            if (empty($coupon) || $coupon['coupon_active'] !== self::SG_COUPON_ACTIVE) {
                $msg .= ShopgateLibraryException::COUPON_NOT_VALID . "\n";
            }
            
            $currentDate = date("Y-m-d H:i:s");
            if ($coupon['coupon_start_date'] >= $currentDate) {
                $msg .= ERROR_INVALID_STARTDATE_COUPON . "\n";
            }
            
            if ($coupon['coupon_expire_date'] <= $currentDate) {
                $msg .= ERROR_INVALID_FINISDATE_COUPON . "\n";
            }
            
            if ($coupon['coupon_minimum_order'] > $orderAmount) {
                $msg .= (defined("ERROR_MINIMUM_ORDER_COUPON_1")
                        ? ERROR_MINIMUM_ORDER_COUPON_1
                        : SHOPGATE_COUPON_ERROR_MINIMUM_ORDER_AMOUNT_NOT_REACHED)
                    . "(" . $coupon['coupon_minimum_order'] . ")\n";
            }
            
            if (!$this->checkCouponRedeemAmount($coupon)) {
                $msg .= ERROR_INVALID_USES_COUPON . $coupon['uses_per_coupon'] . "\n";
            }
            
            if (!$this->checkCouponRedeemAmountToCustomer($coupon, $cart->getExternalCustomerId())) {
                $msg .= ERROR_INVALID_USES_USER_COUPON . $coupon['uses_per_user'] . TIMES . "\n";
            }
            
            if ($coupon['restrict_to_products']
                && !$this->cartHasRestrictedProduct(
                    $coupon, $cart->getItems(), $cartItemModel
                )
            ) {
                $msg .= SHOPGATE_COUPON_ERROR_RESTRICTED_PRODUCTS . "\n";
            }
            
            if ($coupon['restrict_to_categories']
                && !$this->cartHasRestrictedProductToCategory(
                    $coupon, $cart->getItems(), $cartItemModel
                )
            ) {
                $msg .= SHOPGATE_COUPON_ERROR_RESTRICTED_CATEGORIES . "\n";
            }
        } elseif ($coupon['coupon_type'] == self::SG_COUPON_TYPE_GIFT) {
            // Seems to be a bug in modified. It's allowed to use the voucher, but there is no
            // price reduction on order. As workaround we set the coupon as invalid.
            $msg .= ERROR_NO_INVALID_REDEEM_COUPON . "\n";
        }
        
        return $msg;
    }
    
    /**
     * redeem the coupon in the shop system
     *
     * @param ShopgateExternalCoupon $sgCoupon
     * @param int                    $customerId
     */
    public function redeemCoupon(ShopgateExternalCoupon $sgCoupon, $customerId)
    {
        $coupon = $this->getCouponByCode($sgCoupon->getCode());
        if ($coupon['coupon_type'] == self::SG_COUPON_TYPE_GIFT) {
            $this->proceedWelcomeVoucher($coupon, $customerId);
        } else {
            $this->insertRedeemInformation($coupon['coupon_id'], $customerId);
        }
    }
    
    /**
     * insert the order total value for coupons
     *
     * @param int                    $orderId
     * @param ShopgateExternalCoupon $sgCoupon
     *
     * @return float
     */
    public function insertOrderTotal($orderId, ShopgateExternalCoupon $sgCoupon)
    {
        $xtPrice      = new xtcPrice($this->currencyCode, "");
        $insertAmount = $xtPrice->xtcFormat($sgCoupon->getAmountGross() * (-1), true);
        
        $orderTotal = array(
            'orders_id'  => $orderId,
            'title'      => MODULE_ORDER_TOTAL_COUPON_TITLE . ' ' . $sgCoupon->getCode() . ':',
            'text'       => '<strong><span style="color:#ff0000">' . $insertAmount . '</span></strong>',
            'value'      => $insertAmount,
            'class'      => 'ot_coupon',
            'sort_order' => MODULE_ORDER_TOTAL_COUPON_SORT_ORDER,
        );
        
        xtc_db_perform(TABLE_ORDERS_TOTAL, $orderTotal);
        
        return $sgCoupon->getAmountGross();
    }
    
    /**
     * read the coupon data from the database by coupon code
     *
     * @param string $code
     *
     * @return array
     */
    public function getCouponByCode($code)
    {
        $code        = ShopgateWrapper::db_prepare_input($code);
        $couponQuery =
            "SELECT * FROM `" . TABLE_COUPONS . "` AS c " .
            "LEFT JOIN `" . TABLE_COUPONS_DESCRIPTION . "` AS cd ON cd.coupon_id=c.coupon_id " .
            "WHERE c.coupon_code='{$code}' AND cd.language_id={$this->languageId}";
        
        $coupon = xtc_db_fetch_array(xtc_db_query($couponQuery));
        // check if coupon is an gift voucher 
        if (empty($coupon)) {
            $couponQuery = "SELECT * FROM `" . TABLE_COUPONS . "` AS c WHERE c.coupon_code='{$code}'";
            $coupon      = xtc_db_fetch_array(xtc_db_query($couponQuery));
        }
        
        return $coupon;
    }
    
    /**
     * fill the ShopgateExternalCoupon object with data e.g. coupon amount.
     *
     * @param array                  $coupon
     * @param ShopgateExternalCoupon $sgCoupon
     * @param ShopgateCart           $cart
     * @param ShopgateItemCartModel  $cartItemModel
     * @param int                    $customerGroupId
     */
    public function setCouponData(
        array $coupon,
        ShopgateExternalCoupon $sgCoupon,
        ShopgateCart $cart,
        ShopgateItemCartModel $cartItemModel,
        $customerGroupId
    ) {
        $couponAmount = 0;
        $freeShipping = false;
        
        $applicableProducts = $this->getApplicableProducts(
            $cart->getItems(),
            $coupon['restrict_to_categories'],
            $coupon['restrict_to_products'],
            $cartItemModel
        );
        
        switch ($coupon['coupon_type']) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::SG_COUPON_TYPE_FREE_SHIPPING:
                $freeShipping = true;
            // for free shipping coupons, if an amount was provided it's always handled as if fixed, so fall through:
            
            case self::SG_COUPON_TYPE_FIX:
                $couponAmount = empty($applicableProducts)
                    ? 0
                    : (float)$coupon['coupon_amount'];
                break;
            
            case self::SG_COUPON_TYPE_PERCENTAGE:
                $couponAmount = $this->calculateCouponAmountPercentage(
                    $applicableProducts,
                    (float)$coupon['coupon_amount'],
                    $customerGroupId,
                    $cartItemModel
                );
                break;
            
            // Nothing to do here. The coupon will be marked as "accepted" by modified but there is
            // no price reduction
            case self::SG_COUPON_TYPE_GIFT:
            default :
                break;
        }
        
        $sgCoupon->setName($coupon['coupon_name']);
        $sgCoupon->setCode($coupon['coupon_code']);
        $sgCoupon->setCurrency($this->currencyCode);
        $sgCoupon->setAmountGross($couponAmount);
        $sgCoupon->setIsFreeShipping($freeShipping);
        $sgCoupon->setDescription($coupon['coupon_description']);
    }
    
    /**
     * Finds out products the coupon applies to.
     *
     * If the coupon has no restrictions, all items in the cart are applicable and returned by this method.
     *
     * If the coupon is restricted to products, categories or both, it is applicable to any product that is either in
     * the list of allowed products or in one of the allowed categories (including sub-categories).
     *
     * @param ShopgateOrderItem[]   $cartProducts
     * @param string                $restrictedCategories A comma-separated list of categories this coupon is restricted to
     * @param string                $restrictedProducts   A comma-separated list of products this coupon is restricted to
     * @param ShopgateItemCartModel $shopgateItemCartModel
     *
     * @return ShopgateOrderItem[]
     */
    protected function getApplicableProducts(
        $cartProducts,
        $restrictedCategories,
        $restrictedProducts,
        ShopgateItemCartModel $shopgateItemCartModel
    ) {
        if (empty($restrictedCategories) && empty($restrictedProducts)) {
            return $cartProducts;
        }
        
        $restrictedCategories = empty($restrictedCategories)
            ? array()
            : array_flip(explode(',', $restrictedCategories));
        
        $restrictedProducts = empty($restrictedProducts)
            ? array()
            : array_flip(explode(',', $restrictedProducts));
        
        /** @var ShopgateOrderItem[] $applicableProducts */
        $applicableProducts = array();
        foreach ($cartProducts as $product) {
            $itemNumber = $shopgateItemCartModel->getProductIdFromCartItem($product);
            if (isset($restrictedProducts[$itemNumber])) {
                $applicableProducts[] = $product;
                continue; // product is valid for coupon, on to the next
            }
            
            // fetch the category numbers, including parent categories
            $categoryPath = xtc_get_product_path(xtc_get_prid($itemNumber));
            if (empty($categoryPath)) {
                // product has no category path (also happens when deactivated), on to the next
                continue;
            }
            
            $categoryNumbers = explode('_', $categoryPath);
            foreach ($categoryNumbers as $categoryNumber) {
                if (isset($restrictedCategories[$categoryNumber])) {
                    $applicableProducts[] = $product;
                    continue 2; // product is in a valid category for coupon, on to the next
                }
            }
        }
        
        return $applicableProducts;
    }
    
    /**
     * @param ShopgateOrderItem[]   $applicableItems
     * @param float                 $percentage
     * @param int                   $customerGroupId
     * @param ShopgateItemCartModel $itemCartModel
     *
     * @return float
     */
    protected function calculateCouponAmountPercentage(
        $applicableItems,
        $percentage,
        $customerGroupId,
        ShopgateItemCartModel $itemCartModel
    ) {
        $amount = 0;
        foreach ($applicableItems as $item) {
            $itemAmount = $this->getCartItemAmount(
                $item,
                $itemCartModel->getProductIdFromCartItem($item),
                $customerGroupId
            );
            
            $amount += ($itemAmount * $item->getQuantity()) * ($percentage / 100);
        }
        
        return (float)$amount;
    }
    
    /**
     * this logic was taken from the shop system to insert welcome voucher/gift on customer registration
     *
     * @param string $email
     * @param string $name
     */
    public function insertWelcomeVoucher($email, $name)
    {
        $smarty  = new Smarty;
        $xtPrice = new xtcPrice($this->currencyCode, "");
        // GV Code - CREDIT CLASS CODE BLOCK
        if (ACTIVATE_GIFT_SYSTEM == 'true') {
            if (NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
                $couponCode  = create_coupon_code();
                $insertQuery = xtc_db_query(
                    "INSERT INTO " . TABLE_COUPONS
                    . " (coupon_code, coupon_type, coupon_amount, date_created) VALUES ('" . $couponCode . "', '"
                    . self::SG_COUPON_TYPE_GIFT . "', '"
                    . NEW_SIGNUP_GIFT_VOUCHER_AMOUNT . "', now())"
                );
                $insertId    = xtc_db_insert_id($insertQuery);
                xtc_db_query(
                    "INSERT INTO " . TABLE_COUPON_EMAIL_TRACK
                    . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) VALUES ('" . $insertId
                    . "', '0', 'Admin', '" . $email . "', now() )"
                );
                
                $smarty->assign('SEND_GIFT', 'true');
                $smarty->assign('GIFT_AMMOUNT', $xtPrice->xtcFormat(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT, true));
                $smarty->assign('GIFT_CODE', $couponCode);
                $smarty->assign(
                    'GIFT_LINK', xtc_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $couponCode, 'NONSSL', false)
                );
            }
            if (NEW_SIGNUP_DISCOUNT_COUPON != '') {
                $couponCode             = NEW_SIGNUP_DISCOUNT_COUPON;
                $couponQuery            =
                    xtc_db_query("SELECT * FROM " . TABLE_COUPONS . " WHERE coupon_code = '" . $couponCode . "'");
                $coupon                 = xtc_db_fetch_array($couponQuery);
                $couponId               = $coupon['coupon_id'];
                $couponDescriptionQuery = xtc_db_query(
                    "SELECT * FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . $couponId
                    . "' AND language_id = '" . (int)$this->languageId . "'"
                );
                $couponDescription      = xtc_db_fetch_array($couponDescriptionQuery);
                xtc_db_query(
                    "INSERT INTO " . TABLE_COUPON_EMAIL_TRACK
                    . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) VALUES ('" . $couponId
                    . "', '0', 'Admin', '" . $email . "', now() )"
                );
                
                $smarty->assign('SEND_COUPON', 'true');
                $smarty->assign('COUPON_DESC', $couponDescription['coupon_description']);
                $smarty->assign('COUPON_CODE', $coupon['coupon_code']);
            }
        }
        
        $smarty->caching = 0;
        $htmlMail        =
            $smarty->fetch(CURRENT_TEMPLATE . '/mail/' . $_SESSION['language'] . '/create_account_mail.html');
        $txtMail         =
            $smarty->fetch(CURRENT_TEMPLATE . '/mail/' . $_SESSION['language'] . '/create_account_mail.txt');
        
        xtc_php_mail(
            EMAIL_SUPPORT_ADDRESS, EMAIL_SUPPORT_NAME, $email, $name, EMAIL_SUPPORT_FORWARDING_STRING,
            EMAIL_SUPPORT_REPLY_ADDRESS, EMAIL_SUPPORT_REPLY_ADDRESS_NAME, '', '', EMAIL_SUPPORT_SUBJECT, $htmlMail,
            $txtMail
        );
    }
    
    /**
     * include the language file to the shop module ot_coupon
     */
    private function initializeCouponModule()
    {
        if (!class_exists("ot_coupon")) {
            $couponModuleFile = DIR_FS_LANGUAGES . $this->language . '/modules/order_total/ot_coupon.php';
            if (file_exists($couponModuleFile)) {
                require_once($couponModuleFile);
            }
        }
    }
    
    /**
     * check redeem amount to a coupon
     *
     * @param array $coupon_result
     *
     * @return bool
     */
    private function checkCouponRedeemAmount($coupon_result)
    {
        $coupon_count = xtc_db_query(
            "SELECT coupon_id FROM " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id = '" . $coupon_result['coupon_id']
            . "'"
        );
        
        return (xtc_db_num_rows($coupon_count) >= $coupon_result['uses_per_coupon']
            && $coupon_result['uses_per_coupon'] > 0) ? false : true;
    }
    
    /**
     * check redeem amount to customer for a coupon
     *
     * @param array $coupon_result
     * @param int   $customerId
     *
     * @return bool
     */
    private function checkCouponRedeemAmountToCustomer($coupon_result, $customerId)
    {
        $coupon_count_customer = xtc_db_query(
            "SELECT coupon_id FROM " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id = '" . $coupon_result['coupon_id']
            . "' AND customer_id = '" . (int)$customerId . "'"
        );
        
        return (xtc_db_num_rows($coupon_count_customer) >= $coupon_result['uses_per_user']
            && $coupon_result['uses_per_user'] > 0) ? false : true;
    }
    
    /**
     * check if a product is in the cart which a coupon points to
     *
     * @param array                 $coupon
     * @param ShopgateOrderItem[]   $items
     * @param ShopgateItemCartModel $cartItemModel
     *
     * @return bool
     */
    private function cartHasRestrictedProduct(array $coupon, $items, ShopgateItemCartModel $cartItemModel)
    {
        $ids = explode(",", $coupon['restrict_to_products']);
        foreach ($items as $cartItem) {
            $id = $cartItemModel->getProductIdFromCartItem($cartItem);
            if (in_array($id, $ids)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * check if a product is in the cart which a coupon points to the products category
     *
     * @param array                 $coupon
     * @param ShopgateOrderItem[]   $items
     * @param ShopgateItemCartModel $cartItemModel
     *
     * @return bool
     */
    private function cartHasRestrictedProductToCategory(array $coupon, $items, ShopgateItemCartModel $cartItemModel)
    {
        $categoryIds = explode(",", $coupon['restrict_to_categories']);
        foreach ($items AS $item) {
            $categoryPath            = xtc_get_product_path(xtc_get_prid($item->getItemNumber()));
            $productCategoryIdsArray = explode("_", $categoryPath);
            for ($ii = 0, $nn = count($categoryIds); $ii < $nn; $ii++) {
                if (in_array($categoryIds[$ii], $productCategoryIdsArray)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * proceed the welcome voucher. This voucher will be disabled after one use
     *
     * @param array $coupon
     * @param int   $customerId
     */
    private function proceedWelcomeVoucher(array $coupon, $customerId)
    {
        $couponAmount          = $coupon['coupon_amount'];
        $gvCustomerAmount      = $this->getCustomerGvAmount($customerId);
        $totalGvCustomerAmount = $couponAmount + $gvCustomerAmount;
        
        $this->setCouponInactive($coupon['coupon_id']);
        $this->insertRedeemInformation($coupon['coupon_id'], $customerId);
        
        if ($gvCustomerAmount > 0) {
            // already has gv_amount so update
            $this->updateCustomerGvAmount($totalGvCustomerAmount, $customerId);
        } else {
            // no gv_amount so insert
            $this->insertCustomersGvAmount($totalGvCustomerAmount, $customerId);
        }
    }
    
    /**
     * read existing customers gv amount from database by customer's id
     *
     * @param int $customerId
     *
     * @return array|bool|int|mixed
     */
    private function getCustomerGvAmount($customerId)
    {
        $customerAmountQuery = xtc_db_query(
            "SELECT amount FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE customer_id = '" . $customerId . "'"
        );
        
        if ($customerAmount = xtc_db_fetch_array($customerAmountQuery)) {
            $customerAmount = !empty($customerAmount['amount']) ? $customerAmount['amount'] : 0;
        }
        
        return $customerAmount;
    }
    
    /**
     * set coupon as inactive in the database
     *
     * @param int $couponId
     */
    private function setCouponInactive($couponId)
    {
        xtc_db_query("UPDATE " . TABLE_COUPONS . " SET coupon_active = 'N' WHERE coupon_id = '" . $couponId . "'");
    }
    
    /**
     * insert redeem information into the database
     *
     * @param int $couponId
     * @param int $customerId
     */
    private function insertRedeemInformation($couponId, $customerId)
    {
        global $REMOTE_ADDR;
        xtc_db_query(
            "INSERT INTO  " . TABLE_COUPON_REDEEM_TRACK
            . " (coupon_id, customer_id, redeem_date, redeem_ip) VALUES ('"
            . $couponId . "', '" . $customerId . "', now(),'" . $REMOTE_ADDR . "')"
        );
    }
    
    /**
     * updates a customers gift value amount into the database by customer's id
     *
     * @param float|string $totalGvAmount
     * @param int          $customerId
     */
    private function updateCustomerGvAmount($totalGvAmount, $customerId)
    {
        xtc_db_query(
            "UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET amount = '" . $totalGvAmount
            . "' WHERE customer_id = '" . $customerId . "'"
        );
    }
    
    /**
     * stores a customers gift value amount into the database by customer's id
     *
     * @param float|string $totalGvAmount
     * @param int          $customerId
     */
    private function insertCustomersGvAmount($totalGvAmount, $customerId)
    {
        xtc_db_query(
            "INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) VALUES ('"
            . $customerId . "', '" . $totalGvAmount . "')"
        );
    }
    
    /**
     * get the amount to a cart item depending on the tax rate
     *
     * @param ShopgateOrderItem $item
     * @param int               $itemUid
     * @param int               $customerGroupId
     *
     * @return float
     */
    private function getCartItemAmount(ShopgateOrderItem $item, $itemUid, $customerGroupId)
    {
        $orderItemTaxClassId = xtc_get_tax_class_id($itemUid);
        $xtcPrice            = new xtcPrice($this->currencyCode, $customerGroupId);
        $priceWithTax        = $xtcPrice->xtcGetPrice(
            $itemUid,
            false,
            $item->getQuantity(),
            $orderItemTaxClassId,
            $item->getUnitAmount(),
            1
        );
        
        return $priceWithTax;
    }
}
