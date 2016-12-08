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
class Shopgate_Helper_Logging_Obfuscator
{
    const OBFUSCATION_STRING = 'XXXXXXXX';
    const REMOVED_STRING     = '<removed>';
    
    /** @var string[] Names of the fields that should be obfuscated on logging. */
    private $obfuscationFields;
    
    /** @var string Names of the fields that should be removed from logging. */
    private $removeFields;
    
    public function __construct()
    {
        $this->obfuscationFields = array('pass');
        $this->removeFields      = array('cart');
    }
    
    /**
     * Adds field names to the list of fields that should be obfuscated in the logs.
     *
     * @param string[] $fieldNames
     */
    public function addObfuscationFields(array $fieldNames)
    {
        $this->obfuscationFields = array_merge($fieldNames, $this->obfuscationFields);
    }
    
    /**
     * Adds field names to the list of fields that should be removed from the logs.
     *
     * @param string[] $fieldNames
     */
    public function addRemoveFields(array $fieldNames)
    {
        $this->removeFields = array_merge($fieldNames, $this->removeFields);
    }
    
    /**
     * Function to prepare the parameters of an API request for logging.
     *
     * Strips out critical request data like the password of a get_customer request.
     *
     * @param mixed[] $data The incoming request's parameters.
     *
     * @return mixed[] The cleaned parameters.
     */
    public function cleanParamsForLog($data)
    {
        foreach ($data as $key => &$value) {
            if (in_array($key, $this->obfuscationFields)) {
                $value = self::OBFUSCATION_STRING;
            }
            
            if (in_array($key, $this->removeFields)) {
                $value = self::REMOVED_STRING;
            }
        }
        
        return $data;
    }
}