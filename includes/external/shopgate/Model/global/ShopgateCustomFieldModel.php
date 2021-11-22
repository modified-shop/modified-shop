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
 * Handles all Shopgate custom field manipulation
 */
class ShopgateCustomFieldModel
{
    /**
     * @var bool
     */
    protected $printStarted = false;

    /**
     * Note! Destructive on object passed.
     * Removes custom fields from object that
     * will not be saved in the database.
     *
     * @param ShopgateOrder|ShopgateAddress|ShopgateCustomer $object
     * @param string                                         $table
     *
     * @return array
     */
    public function prepareCustomFields(&$object, $table = TABLE_ORDERS)
    {
        $orderData = $newFields = array();
        foreach ($object->getCustomFields() as $field) {
            if (ShopgateWrapper::db_column_exists(
                $table, $field->getInternalFieldName()
            )
            ) {
                $orderData[$field->getInternalFieldName()] = $field->getValue();
            } else {
                array_push($newFields, $field);
            }
        }
        $object->setCustomFields($newFields);

        return $orderData;
    }

    /**
     * Returns a customField history comment that
     * is ready to be printed
     *
     * @param ShopgateOrder|ShopgateAddress $object
     *
     * @return array
     */
    public function printShopgateCustomFields($object)
    {
        $print = '';
        if (!$this->printStarted) {
            $this->printStarted = true;
            $print              = SHOPGATE_ORDER_CUSTOM_FIELD . "\n";
        }

        $objectData = array();
        foreach ($object->getCustomFields() as $field) {
            $objectData[$field->getLabel()] = $field->getValue();
        }

        return empty($objectData) ? "" : $this->printArray($objectData, $print);
    }

    /**
     * Helper function to print arrays recursively
     *
     * @param array  $list - paymentInfo array
     * @param string $html - don't pass anything, recursive helper
     *
     * @return string
     */
    protected function printArray($list, $html = '')
    {
        if (is_array($list)) {
            foreach ($list as $_key => $_value) {
                if (is_array($_value)) {
                    return $this->printArray($_value, $html);
                } else {
                    $html .= $_key . ": " . $_value . "\n";
                }
            }
        } else {
            $html .= $list . "\n";
        }

        return $html;
    }
}
