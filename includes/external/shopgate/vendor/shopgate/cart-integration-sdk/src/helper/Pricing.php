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

class Shopgate_Helper_Pricing
{
    /**
     * Rounds and formats a price.
     *
     * @param float  $price          The price of an item.
     * @param int    $digits         The number of digits after the decimal separator.
     * @param string $decimalPoint   The decimal separator.
     * @param string $thousandPoints The thousands separator.
     *
     * @return float|string
     */
    public function formatPriceNumber($price, $digits = 2, $decimalPoint = ".", $thousandPoints = "")
    {
        $price = round($price, $digits);
        $price = number_format($price, $digits, $decimalPoint, $thousandPoints);

        return $price;
    }
}
