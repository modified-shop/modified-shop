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
class ShopgateShippingModel
{
    /**
     * read the shipping configuration from the database to an specific
     * shipping class
     *
     * @param $className
     *
     * @return array
     */
    public function getShippingConfigurationValuesByClassName($className)
    {
        $query
            = "SELECT c.configuration_key, c.configuration_value 
             FROM " . TABLE_CONFIGURATION . " AS c 
             WHERE configuration_key LIKE \"MODULE_SHIPPING_" . strtoupper(
                $className
            ) . "%\" ;";

        $result         = xtc_db_query($query);
        $shippingConfig = array();

        while ($config = xtc_db_fetch_array($result)) {
            $shippingConfig[$config["configuration_key"]]
                = $config["configuration_value"];
        }

        return $shippingConfig;
    }
}
