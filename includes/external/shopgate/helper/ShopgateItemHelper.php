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
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain
 * unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class ShopgateItemHelper
{
    /**
     * @param int   $productsId
     * @param array $sgOrderInfo
     *
     * @return int
     */
    public function getProductQuantity($productsId, $sgOrderInfo = array())
    {
        $quantity = xtc_get_products_stock($productsId);
        $attributes = $this->filterOptionsFromOrderInfo($sgOrderInfo);
        foreach ($attributes as $attribute) {
            $quantity = min($quantity, $this->getAttributeStock($attribute['products_attributes_id']));
        }

        return $quantity;
    }

    /**
     * @param int $productsAttributesId
     *
     * @return int
     */
    public function getAttributeStock($productsAttributesId)
    {
        $sql    =
            "SELECT attributes_stock FROM `" . TABLE_PRODUCTS_ATTRIBUTES . "` WHERE products_attributes_id = "
            . $productsAttributesId;
        $query  = xtc_db_query($sql);
        $result = xtc_db_fetch_array($query);

        return $result['attributes_stock'];
    }

    /**
     * @param array $sgOrderInfo
     *
     * @return array
     */
    public static function filterOptionsFromOrderInfo($sgOrderInfo)
    {
        $attributeIds = array();
        foreach ($sgOrderInfo as $infoName => $infoValue) {
            if (strpos($infoName, 'attribute_') === 0
                && is_array($infoValue)
            ) {
                foreach ($infoValue as $attributeKey => $attributeArray) {
                    $attributeIds[] = array_merge(
                        array('products_attributes_id' => $attributeKey),
                        $attributeArray
                    );
                }
            }
        }

        return $attributeIds;
    }

    /**
     * Checks if product manufacturer columns is available
     *
     * @param string $modifiedVersion
     *
     * @return bool
     */
    public static function manufacturerColumnAvailable($modifiedVersion)
    {
        return version_compare($modifiedVersion, '1.06', '>=');
    }
}
