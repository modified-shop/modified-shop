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

class ShopgateItemCartModel extends ShopgateObject
{
    
    /**
     * if the current order item (product) is an child product the item number is
     * generated in the schema <productId>_<attributeId>
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
            foreach ($options AS $option) {
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
                foreach ($info AS $key => $value) {
                    $attributeIdArray[] = $key;
                }
            }
        }
        
        return $attributeIdArray;
    }
    
    /**
     * if the current order item (product) is an child product the item number is
     * generated in the schema <productId>_<attributeId>
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
                $query = "SELECT SUM(CONCAT(weight_prefix, options_values_weight)) AS weight FROM "
                    . TABLE_PRODUCTS_ATTRIBUTES . " AS pa WHERE ";
                
                $conditions = array();
                if (count($optionIds) > 0) {
                    $conditions[] =
                        " (pa.products_id = {$pId} AND pa.options_values_id IN (" . implode(",", $optionIds) . ")) ";
                }
                if (count($attributeIds) > 0) {
                    $conditions[] =
                        " (pa.products_id = {$pId} AND pa.products_attributes_id IN (" . implode(",", $attributeIds)
                        . ")) ";
                }
                
                $query .= implode(' OR ', $conditions);
                $result = xtc_db_fetch_array(xtc_db_query($query));
                $weight += $result["weight"] * $product->getQuantity();
            }
            
            if (!empty($pId)) {
                // calculate the "base" product weight
                $result = xtc_db_fetch_array(
                    xtc_db_query("select products_weight from " . TABLE_PRODUCTS . " AS p where p.products_id = {$pId}")
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
            $itemAmount = ($item->getTaxPercent() > 0)
                ? $item->getUnitAmount() * (1 + ($item->getTaxPercent()/100))
                : $item->getUnitAmountWithTax();
            $completeAmount += $itemAmount * $item->getQuantity();
        }
        
        return $completeAmount;
    }
}
