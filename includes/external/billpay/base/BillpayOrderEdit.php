<?php
/**
 * Event fired when admin changes customer's cart
 */

/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG. 'includes/external/billpay/base/billpayBase.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG. 'includes/external/billpay/base/BillpayDB.php');

class BillpayOrderEdit {
    /** @var billpayBase */
    var $billpay;

    /** @var string */
    var $action;

    /** @var int */
    var $orderId;

    /** @var array */
    var $paymentMethod;

    /** @var bool */
    var $isBillpay = false;

    function BillpayOrderEdit() {
        $this->action = $_GET['action'];
        $this->orderId = (int)$_GET['oID'];
        if (empty($this)) {
            $this->orderId = (int)$_POST['orders_id'];
        }
        $billpayMethods = billpayBase::GetPaymentMethods();
        $table = TABLE_ORDERS;
        $orders_id = (int)$this->orderId;
        $this->paymentMethod = BillpayDB::DBFetchValue("SELECT payment_method FROM $table WHERE orders_id = '$orders_id'");
        $this->isBillpay = in_array($this->paymentMethod, $billpayMethods);
        if (!$this->isBillpay) return;
        $this->billpay = billpayBase::PaymentInstance($this->paymentMethod);
    }

    function onBeforeUpdate() {
        global $order;
        if (!$this->isBillpay) return true;

        /** @noinspection PhpIncludeInspection */
        require_once(DIR_FS_LANGUAGES.$_SESSION['language'].'/modules/payment/billpay.php');

        $actionsForbidden = array(
            'address_edit', // changing shipping address
            // 'product_ins',  // adding new product
            'payment_edit', // changing payment method
            'curr_edit',    // changing currency
            // 'ot_edit',      // editing order totals - discount, merchant fee, paylater fee
            'ot_delete',    // deleting order totals
        );
        if (in_array($this->action, $actionsForbidden)) {
            billpayBase::DisplayErrorAndExit(constant('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_GENERAL'));
        }

        if ($this->action === "ot_edit") {
            $class  = $_POST['class'];
            if (!in_array($class, array("ot_discount", "ot_shipping"))) {
                billpayBase::DisplayErrorAndExit(constant('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_GENERAL'));
            }
        }

        if ($this->action === "product_delete") {
            if ($this->_willOrderBeEmpty($_POST['oID'], $_POST['opID'])) {
                $this->_cancelOrder($_POST['oID']);
            }
        }

        if ($this->action === "product_edit") {

            $orderProductId 		= $_POST['opID'];
            $newQuantity		 	= $_POST['products_quantity'];
            $newProductsTax	 		= $_POST['products_tax'];
            $newProductsPrice		= $_POST['products_price'];
            $error = 'Product not found';
            foreach ($order->products as $product) {
                if ($product['opid'] == $orderProductId) {
                    if ($newQuantity < 0) {
                        $error = MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_QUANTITY;
                    } elseif ($newProductsTax > $product['tax']) {
                        $error = MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_TAX;
                    } elseif ($newProductsPrice > $product['price']) {
                        $error = MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRICE;
                    } else {
                        $error = null;
                    }
                }
            }
            if ($error) {
                billpayBase::DisplayErrorAndExit($error);
            }

            // if this is the last product and qty is 0, order should be cancelled
            if ($newQuantity == 0 && $this->_willOrderBeEmpty($_POST['oID'], $_POST['opID'])) {
                $this->_cancelOrder($_POST['oID']);
            }
        }

        if ($this->action === "shipping_edit") {
            $oldShippingValue = BillpayOrder::getOTById($_POST['oID'], 'ot_shipping');
            $newShippingValue = $_POST['value'];
            $billpayDelta = $oldShippingValue - $newShippingValue;

            if ($newShippingValue < 0) {
                $error = MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_SHIPPING;
                billpayBase::DisplayErrorAndExit($error);
            }
            if ($billpayDelta < 0) {
                $error = MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_INCREASED_SHIPPING;
                billpayBase::DisplayErrorAndExit($error);
            }
        }

        if ($this->action === "product_option_edit")
        {
            $table = TABLE_ORDERS_PRODUCTS_ATTRIBUTES;
            $orders_id = (int)$_POST['oID'];
            $attr_id = (int)$_POST['opAID'];

            $query = xtc_db_query("SELECT options_values_price, price_prefix FROM $table WHERE orders_id='$orders_id' AND orders_products_attributes_id='$attr_id'");
            if (xtc_db_num_rows($query)) {
                $data = xtc_db_fetch_array($query);
                $isOptionPriceDifferent = ($data['options_values_price'] != $_POST['options_values_price']);
                $isPrefixDifferent = ($data['price_prefix'] !=  $_POST['prefix']);
                if ($isOptionPriceDifferent || $isPrefixDifferent)
                {
                    $error = MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADJUST_CHARGEABLE;
                    billpayBase::DisplayErrorAndExit($error);
                }
            }
        }

        if ($this->action === "product_option_ins")
        {
            if ($_POST['options_values_price'] != 0)
            {
                $error = MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADD_CHARGEABLE;
                billpayBase::DisplayErrorAndExit($error);
            }
        }

        if ($this->action === "save_order")
        {
            $this->billpay->onSaveEditOrderBefore($this->orderId);
        }

        return true;
    }

    function onAfterUpdate() {
        if (!$this->isBillpay) return true;

        // Warning: Patchfile injects this function only for save_order
        if ($this->action === "save_order")
        {
            if ($this->_isOrderCancelled($this->orderId)) {
                return true; // we don't change cancelled order
            }

            // send editCartContent request, even if something else was changed
            $success = $this->billpay->reqEditCartContent($this->orderId);
            if (!$success) {
                $this->billpay->setOrderBillpayState(billpayBase::STATE_ERROR, $this->orderId, billpayBase::EnsureString($this->billpay->error));
            } else {
                $table = TABLE_ORDERS;
                $orders_id = (int)$this->orderId;
                $currentOrderStatus = BillpayDB::DBFetchValue("SELECT orders_status FROM $table WHERE orders_id = '$orders_id'");
                if ($currentOrderStatus === $this->billpay->getOrderStatusFromBillpayState(billpayBase::STATE_ERROR)) {
                    // find last good status
                    $goodStatusesArr = array(
                        $this->billpay->getOrderStatusFromBillpayState(billpayBase::STATE_PENDING),
                        $this->billpay->getOrderStatusFromBillpayState(billpayBase::STATE_APPROVED),
                        $this->billpay->getOrderStatusFromBillpayState(billpayBase::STATE_COMPLETED),
                        $this->billpay->getOrderStatusFromBillpayState(billpayBase::STATE_CANCELLED),
                    );
                    $goodStatuses = join(', ', $goodStatusesArr);
                    $table = TABLE_ORDERS_STATUS_HISTORY;
                    $orders_id = (int)$this->orderId;
                    $lastGoodStatus = BillpayDB::DBFetchValue("SELECT orders_status_id FROM $table WHERE orders_id = '$orders_id' AND orders_status_id IN ($goodStatuses) ORDER BY date_added DESC LIMIT 1");
                } else {
                    $lastGoodStatus = $currentOrderStatus;
                }
                if ($lastGoodStatus) {
                    $this->billpay->setOrderStatus($lastGoodStatus, $this->orderId, constant('MODULE_PAYMENT_BILLPAY_HISTORY_INFO_EDIT_CART_CONTENT'));
                }
            }
        }

        return true;
    }

    /**
     * Method checks, if after deleting selected product, order will be empty and should be cancelled instead of editCartContent.
     *
     * @param int $orderId
     * @param int $lastOrderProductId
     * @return bool
     */
    function _willOrderBeEmpty($orderId, $lastOrderProductId)
    {
        $table = TABLE_ORDERS_PRODUCTS;
        $orders_id = (int)$orderId;
        $orders_product_id = (int)$lastOrderProductId;
        $sql = "SELECT SUM(products_quantity) FROM $table WHERE orders_id = $orders_id AND orders_products_id != $orders_product_id";
        $product_count = (int)BillpayDB::DBFetchValue($sql);
        if ($product_count < 1) {
            return true;
        }
        return false;
    }


    /**
     * @param int $orderId
     * @return bool
     */
    function _isOrderCancelled($orderId)
    {
        $table = TABLE_ORDERS;
        $orders_id = (int)$orderId;
        $sql = "SELECT orders_status FROM $table WHERE orders_id='$orders_id'";
        $currentStatus = (int)BillpayDB::DBFetchValue($sql);
        $cancelledStatus = (int)$this->billpay->getOrderStatusFromBillpayState(billpayBase::STATE_CANCELLED);
        if ($currentStatus === $cancelledStatus) {
            return true;
        }
        return false;
    }

    /**
     * Cancels remote order then cancels local one.
     * @param $orderId
     */
    function _cancelOrder($orderId)
    {
        $success = $this->billpay->reqCancel($orderId);
        if ($success) {
            $cancelledStatus = $this->billpay->getOrderStatusFromBillpayState(billpayBase::STATE_CANCELLED);
            $qry = 'UPDATE ' . TABLE_ORDERS . '
                        SET orders_status = '.(int)$cancelledStatus.'
                        WHERE orders_id = ' . (int)$orderId . '
                        LIMIT 1';
            BillpayDB::DBFetchValue($qry);
        }
    }
}
