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
        $quantity   = xtc_get_products_stock($productsId);
        $attributes = $this->filterOptionsFromOrderInfo($sgOrderInfo);
        foreach ($attributes as $attribute) {
            $quantity = min(
                $quantity,
                $this->getAttributeStock($attribute['products_attributes_id'])
            );
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
        $sql    = "SELECT attributes_stock FROM `" . TABLE_PRODUCTS_ATTRIBUTES
            . "` WHERE products_attributes_id = "
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
