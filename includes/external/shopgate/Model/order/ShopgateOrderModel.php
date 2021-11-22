<?php
/**
 *
 * Copyright Shopgate Inc.
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU General Public License, Version 2
 *
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
            ShopgateLogger::getInstance()->log(
                $error, ShopgateLogger::LOGTYPE_ERROR
            );
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
