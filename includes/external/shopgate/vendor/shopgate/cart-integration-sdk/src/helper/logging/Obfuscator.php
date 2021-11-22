<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
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
