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
class ShopgateCustomerModel extends ShopgateObject
{

    /**
     * @var ShopgateConfigModified $config
     */
    private $config;

    /**
     * @var int
     */
    private $languageId;

    /**
     * @param ShopgateConfigModified $config
     * @param int                    $languageId
     */
    public function __construct(ShopgateConfigModified $config, $languageId)
    {
        $this->languageId = $languageId;
        $this->config     = $config;
    }

    /**
     * return an array with all customer groups
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        $customerGroups = array();

        $query
                = "SELECT 
                        cs.customers_status_name AS name,
                        cs.customers_status_id AS id,
                        0 AS 'is_default'
                    FROM customers_status AS cs
                    WHERE cs.language_id = {$this->languageId}";
        $result = xtc_db_query($query);
        while ($customerGroup = xtc_db_fetch_array($result)) {
            foreach ($customerGroup as &$cgrp) {
                $this->stringToUtf8($cgrp, $this->config->getEncoding());
            }
            if ($customerGroup['id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
                $customerGroup['is_default'] = 1;
            }
            $customerGroup['customer_tax_class_key'] = 'default';
            $customerGroups[]                        = $customerGroup;
        }

        return $customerGroups;
    }

    /**
     * get the customer's token from the database
     *
     * @param string $internalCustomerId
     *
     * @return  bool | string
     */
    public function getCustomerToken($internalCustomerId)
    {
        $result = xtc_db_fetch_array(
            xtc_db_query(
                "
			SELECT customer_token
			FROM " . TABLE_CUSTOMERS_SHOPGATE_CUSTOMER . "
			WHERE customer_id = {$internalCustomerId}
		"
            )
        );

        return is_array($result) ? $result['customer_token'] : false;
    }

    /**
     * check if a customer already has a token
     *
     * @param int $internalCustomerId
     *
     * @return bool
     */
    public function hasCustomerToken($internalCustomerId)
    {
        return $this->getCustomerToken($internalCustomerId) ? true : false;
    }

    /**
     * store a token to a customer in the database
     *
     * @param int    $internalCustomerId
     * @param string $eMailAddress
     *
     * @return string
     */
    public function insertToken($internalCustomerId, $eMailAddress)
    {
        $token = md5($internalCustomerId . $eMailAddress . microtime());

        ShopgateWrapper::db_query(
            "INSERT INTO `" . TABLE_CUSTOMERS_SHOPGATE_CUSTOMER . "` " .
            "(`customer_id`, `customer_token`) VALUES " .
            "(" . xtc_db_input($internalCustomerId) . ", '" . xtc_db_input(
                $token
            ) . "')"
        );

        return $token;
    }

    /**
     * get customer id by the customer's token
     *
     * @param string $token
     *
     * @return bool | int
     */
    public function getCustomerIdByToken($token)
    {
        $result = ShopgateWrapper::db_fetch_array(
            ShopgateWrapper::db_query(
                "
			SELECT customer_id
			FROM " . TABLE_CUSTOMERS_SHOPGATE_CUSTOMER . "
			WHERE customer_token = '$token'
		"
            )
        );

        return isset($result['customer_id']) ? $result['customer_id'] : false;
    }

    /**
     * Determines whether the two addresses in array
     * are equal to each other
     *
     * @param ShopgateAddress[] $shopgateAddresses
     *
     * @return bool
     */
    public function areAddressesEqual(array $shopgateAddresses)
    {
        if (count($shopgateAddresses) == 2) {
            $whiteList = array(
                'gender', 'first_name', 'last_name', 'street_1', 'street_2',
                'zipcode', 'city', 'country',
                'custom_fields'
            );

            return $shopgateAddresses[0]->compare(
                $shopgateAddresses[0], $shopgateAddresses[1], $whiteList
            );
        }

        return false;
    }

    /**
     * read information to an customer from the database by uid
     *
     * @param int $customerId
     *
     * @return array|string
     */
    public function getCustomerById($customerId)
    {
        if (empty($customerId)) {
            return "";
        }
        $query = "SELECT * FROM `" . TABLE_CUSTOMERS
            . "` WHERE customers_id={$customerId}";

        return xtc_db_fetch_array(xtc_db_query($query));
    }
}
