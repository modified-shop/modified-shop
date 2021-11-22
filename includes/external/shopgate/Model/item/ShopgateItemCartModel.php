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
class ShopgateItemCartModel extends ShopgateObject
{

    /**
     * if the current order item (product) is an child product the item number
     * is generated in the schema <productId>_<attributeId>
     *
     * this function returns the id, the product has in the shop system
     *
     * @param ShopgateOrderItem $product
     *
     * @return mixed
     */
    public function getProductsIdFromCartItem($product)
    {
        $id = $product->getParentItemNumber();
        if (empty($id)) {
            $info = json_decode($product->getInternalOrderInfo(), true);
            if (!empty($info) && isset($info["base_item_number"])) {
                $id = $info["base_item_number"];
            }
        }

        return !empty($id) ? $id : $product->getItemNumber();
    }

    /**
     * gather all uids from options to an product
     *
     * @param ShopgateOrderItem $product
     *
     * @return array
     */
    public function getCartItemOptionIds($product)
    {
        $optionIdArray = array();
        $options       = $product->getOptions();
        if (!empty($options)) {
            foreach ($options as $option) {
                $optionIdArray[] = $option->getValueNumber();
            }
        }

        return $optionIdArray;
    }

    /**
     * gather all uids from attributes to an product
     *
     * @param ShopgateOrderItem $product
     *
     * @return array
     */
    public function getCartItemAttributeIds($product)
    {
        $attributeIdArray = array();
        $orderInfos       = json_decode($product->getInternalOrderInfo(), true);

        if (empty($orderInfos)) {
            return $attributeIdArray;
        }

        foreach ($orderInfos as $info) {
            if (is_array($info)) {
                foreach ($info as $key => $value) {
                    $attributeIdArray[] = $key;
                }
            }
        }

        return $attributeIdArray;
    }

    /**
     * if the current order item (product) is an child product the item number
     * is generated in the schema <productId>_<attributeId>
     *
     * this function returns the id, the product has in the shop system
     *
     * @param ShopgateOrderItem $orderItem
     *
     * @return string
     */
    public function getProductIdFromCartItem(ShopgateOrderItem $orderItem)
    {
        $parentId = $orderItem->getParentItemNumber();
        if (empty($parentId)) {
            $id = $orderItem->getItemNumber();
            if (strpos($id, "_") !== false) {
                $productIdArr = explode('_', $id);

                return $productIdArr[0];
            }

            return $id;
        }

        return $parentId;
    }

    /**
     * calculate the weight to an product regarding the weight of options
     *
     * @param ShopgateOrderItem[] $products
     *
     * @return mixed
     */
    public function getProductsWeight($products)
    {
        $calculatedWeight = 0;
        foreach ($products as $product) {
            $weight       = 0;
            $optionIds    = $this->getCartItemOptionIds($product);
            $attributeIds = $this->getCartItemAttributeIds($product);
            $pId          = $this->getProductsIdFromCartItem($product);

            if (count($optionIds) != 0 || count($attributeIds) != 0) {
                // calculate the additional attribute/option  weight
                $query
                    = "SELECT SUM(CONCAT(weight_prefix, options_values_weight)) AS weight FROM "
                    . TABLE_PRODUCTS_ATTRIBUTES . " AS pa WHERE ";

                $conditions = array();
                if (count($optionIds) > 0) {
                    $conditions[]
                        = " (pa.products_id = {$pId} AND pa.options_values_id IN ("
                        . implode(",", $optionIds) . ")) ";
                }
                if (count($attributeIds) > 0) {
                    $conditions[]
                        = " (pa.products_id = {$pId} AND pa.products_attributes_id IN ("
                        . implode(",", $attributeIds)
                        . ")) ";
                }

                $query  .= implode(' OR ', $conditions);
                $result = xtc_db_fetch_array(xtc_db_query($query));
                $weight += $result["weight"] * $product->getQuantity();
            }

            if (!empty($pId)) {
                // calculate the "base" product weight
                $result = xtc_db_fetch_array(
                    xtc_db_query(
                        "select products_weight from " . TABLE_PRODUCTS
                        . " AS p where p.products_id = {$pId}"
                    )
                );

                $weight += $result["products_weight"] * $product->getQuantity();
            }

            $calculatedWeight += $weight;
        }

        return $calculatedWeight;
    }

    /**
     * calculate the complete amount of all items a cart object has
     *
     * @param ShopgateCart $cart
     *
     * @return float|int
     */
    public function getCompleteAmount(ShopgateCart $cart)
    {
        $completeAmount = 0;
        foreach ($cart->getItems() as $item) {
            $itemAmount     = ($item->getTaxPercent() > 0)
                ? $item->getUnitAmount() * (1 + ($item->getTaxPercent() / 100))
                : $item->getUnitAmountWithTax();
            $completeAmount += $itemAmount * $item->getQuantity();
        }

        return $completeAmount;
    }
}
