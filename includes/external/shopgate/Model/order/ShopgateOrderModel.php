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
 * The purpose of this class is to handle all order related
 * operations. All logic concerning data pulled from orders
 * should be pulled in here.
 */
class ShopgateOrderModel
{
    /**
     * @var int $_orderId
     */
    protected $_orderId;
    
    /**
     * Simple getter
     *
     * @return int|string|null
     */
    public function getOrderId()
    {
        return $this->_orderId;
    }
    
    /**
     * Simple setter
     *
     * @param int|string $orderId
     *
     * @return int
     */
    public function setOrderId($orderId)
    {
        $this->_orderId = (int)$orderId;
    }
    
    /**
     * Save comment to order history
     *
     * @param string $status
     * @param string $comment
     *
     * @return ShopgateOrderModel
     * @throws Exception
     */
    public function saveHistory($status, $comment)
    {
        if (!$this->getOrderId()) {
            $error = 'Could not retrieve the proper id for the order';
            ShopgateLogger::getInstance()->log($error, ShopgateLogger::LOGTYPE_ERROR);
            throw new ShopgateLibraryException($error);
        }
        
        if (empty($comment)) {
            return $this;
        }
        
        $history = array(
            "orders_id"         => $this->getOrderId(),
            "orders_status_id"  => $status,
            "date_added"        => date('Y-m-d H:i:s'),
            "customer_notified" => false,
            "comments"          => ShopgateWrapper::db_prepare_input($comment),
        );
        
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $history);
        
        return $this;
    }
}
