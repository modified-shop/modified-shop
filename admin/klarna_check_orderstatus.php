<?php

/**
 * File containing an optional class for enabling the checking of orderstatuses.
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

require_once DIR_FS_DOCUMENT_ROOT . 'includes/external/klarna/class.KlarnaCore.php';

/**
 * Optional class for enabling the checking of orderstatuses.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KlarnaCheckOrder
{

    /**
     * @var string
     */
    const APPROVED = "Approved";

    /**
     * @var string
     */
    const PENDING = "Pending";

    /**
     * @var string
     */
    const DENIED = "Denied";

    /**
     * create an instance of this object
     */
    public function __construct()
    {
        $this->_klarnaDB = new XtcKlarnaDB;
    }

    /**
     * Perform a checkOrder
     *
     * @param int    $id          order id
     * @param string $paymentCode payment option code
     *
     * @return void
     */
    public function checkOrder($id, $paymentCode)
    {
        global $xtPrice;

        $orderId = mysql_real_escape_string($_GET['oID']);

        $comments = $this->_getComments($orderId);

        // Don't update orderstatus if it is already updated to approved once.
        foreach ($comments as $comment) {
            if (strstr($comment, $this->_assembleOrderComment(self::APPROVED))) {
                $this->_showError("Klarna Status already updated and approved.");
                return;
            }
        }

        $ref = $this->_getRefNumber($orderId);

        if ($ref === null) {
            $this->_showError(
                "No matching reference found for order id {$orderId}."
            );
            return;
        }

        KlarnaUtils::configureKiTT(KlarnaConstant::getKiTTOption($paymentCode));
        KlarnaUtils::configureKlarna(
            KlarnaConstant::getKiTTOption($paymentCode)
        );

        $statusName = null;
        try {
            $statusName = $this->_getStatus(
                KiTT::api($this->_getOrderCountry()), $ref
            );
        } catch(Exception $e) {
            $this->_showError($e->getMessage() . " Is {$paymentCode} configured?");
        }

        if ($statusName === null) {
            return;
        }

        $newComment = $this->_assembleOrderComment($statusName);
        echo "<br /> {$newComment} <br />";
		$order_status_id = $this->_getPaymentStatusID($paymentCode,$statusName);
        $sql_data_arr = array(
                'orders_id' => $orderId,
                'orders_status_id' => $order_status_id,
                'comments' => $newComment,
                'customer_notified' => 0,
                'date_added' => date("Y-m-d H:i:s")
            );
        $this->_klarnaDB->perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_arr);
        xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$order_status_id."' WHERE orders_id='".$orderId."'");
    }

    /**
     * Get OrderStatusID for the order to Set by the Module.
     *
     * @param string $paymentCode Payment Code
     * @param string $statusName Status Name
     *
     * @return int of ID
     */
	private function _getPaymentStatusID($paymentCode,$statusName){
			$id = -1;
			if($statusName == self::DENIED){
				return $id;
			}
			if($statusName == self::APPROVED){
				$value = "MODULE_PAYMENT_".strtoupper($paymentCode)."_ORDER_STATUS_ID";
			}
			if($statusName == self::PENDING){
				$value = "MODULE_PAYMENT_".strtoupper($paymentCode)."_ORDER_STATUS_PENDING_ID";
			}
			$query = $this->_klarnaDB->query("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$value."'");
			if ($query->count() > 0) {
				$array = $query->getArray();
				$id = $array['configuration_value'];
			}
			return $id;
	}
	
    /**
     * Get all comments for the order.
     *
     * @param int $orderId order id
     *
     * @return array of comments
     */
    private function _getComments($orderId)
    {
        $query = $this->_klarnaDB->query(
            "SELECT orders_status_id, date_added, customer_notified, comments FROM ".
            TABLE_ORDERS_STATUS_HISTORY.
            " WHERE orders_id = '{$orderId}' ORDER BY date_added"
        );

        // Only get the comment that is the klarna orderstatus
        $array = array();
        if ($query->count() > 0) {
            while ($orders_history = $query->getArray()) {
                $array[] = $orders_history['comments'];
            }
        }
        return $array;
    }

    /**
     * Get the country for the order
     *
     * @return string
     */
    private function _getOrderCountry()
    {
        global $order;

        // get order country
        $id = $order->customer['ID'];
        $query = $this->_klarnaDB->query(
            "SELECT entry_country_id FROM `address_book` WHERE customers_id = {$id}"
        )->getArray();

        return KlarnaUtils::getCountryByID($query['entry_country_id']);
    }

    /**
     * Retrieve the OCR number to check order status on
     *
     * @param int $orderId xtCommerce order ID
     *
     * @return string
     */
    private function _getRefNumber($orderId)
    {
        $query = $this->_klarnaDB->query(
            "SELECT klarna_ref FROM klarna_ordernum WHERE orders_id = '{$orderId}'"
        )->getArray();
        if (array_key_exists('klarna_ref', $query)) {
            return $query['klarna_ref'];
        }
        return null;
    }

    /**
     * In case something goes wrong, show error
     *
     * @param string   $message error message
     * @param int|null $code    error code
     *
     * @return void
     */
    private function _showError($message, $code = null)
    {
        echo "Error: {$message}";
        if ($code !== null) {
            echo "<br /><br />Code: {$code}";
        }
    }

    /**
     * Build an order comment.
     *
     * @param string $orderStatus order status
     *
     * @return string
     */
    private function _assembleOrderComment($orderStatus)
    {
        return "Klarna Status Updated: {$orderStatus}";
    }

    /**
     * Get status code.
     *
     * @param Klarna $api Klarna API instance
     * @param string $ref klarna reference number
     *
     * @return string
     */
    private function _getStatus($api, $ref)
    {
        $statusName = "";
        try {
            $statusCode = $api->checkOrderStatus($ref);
            switch ($statusCode) {
            case 1:
                return self::APPROVED;
            case 2:
                return self::PENDING;
            case 3:
                return self::DENIED;
            }
        } catch(Exception $e) {
            $this->_showError(
                'Please visit <a href="http://online.klarna.com"> '.
                'Klarna Online</a> for more information.<br><br>',
                $e->getCode()
            );
        }
        return null;
    }
}
