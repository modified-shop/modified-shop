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

/**
 * @group              Shopgate_Library
 *
 * @coversDefaultClass ShopgateConfig
 */
class Shopgate_ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /** @var ShopgateConfig $class */
    protected $class;

    public function setUp()
    {
        $this->class = new ShopgateConfig(array());
    }

    /**
     * Tests the setter and getter for exclude_item_ids because the setter has logic
     *
     * @dataProvider excludeItemIdsProvider
     * @covers ::setExcludeItemIds
     *
     * @param array|string $input
     * @param array        $expected
     */
    public function testExcludeItemIds($input, $expected)
    {
        $this->class->setExcludeItemIds($input);
        $returned = $this->class->getExcludeItemIds();
        $this->assertEquals($expected, $returned);
    }

    /**
     * @return array
     */
    public function excludeItemIdsProvider()
    {
        return array(
            'array with data to array with data' => array(
                array(1, 2),
                array(1, 2),
            ),
            'empty array to empty array'         => array(
                array(),
                array(),
            ),
            'empty string to empty array'        => array(
                '',
                array(),
            ),
            'JSON to array with data'            => array(
                '[4,9,5,7,345,9864,1]',
                array(4, 9, 5, 7, 345, 9864, 1),
            ),
        );
    }
}
