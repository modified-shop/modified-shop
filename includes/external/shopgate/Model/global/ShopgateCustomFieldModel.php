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
            if (ShopgateWrapper::db_column_exists($table, $field->getInternalFieldName())) {
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
