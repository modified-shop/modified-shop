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
class Shopgate_Helper_Logging_ObfuscatorTest extends PHPUnit_Framework_TestCase
{
    /** @var Shopgate_Helper_Logging_Obfuscator */
    private $obfuscator;

    public function setUp()
    {
        $this->obfuscator = new Shopgate_Helper_Logging_Obfuscator();
    }

    public function testAddObfuscationFields()
    {
        $this->obfuscator->addObfuscationFields(array('test'));
        $data = array(
            'mytestData' => 'this must be readable',
            'user'       => 'this must be readable',
            'pass'       => 'this is secure',
            'test'       => 'this is secure',
        );

        $expected = array(
            'mytestData' => 'this must be readable',
            'user'       => 'this must be readable',
            'pass'       => 'XXXXXXXX',
            'test'       => 'XXXXXXXX',
        );
        $this->assertEquals(
            $expected,
            $this->obfuscator->cleanParamsForLog($data)
        );
    }

    /**
     * @param array  $data
     * @param string $expectedResult
     *
     * @dataProvider addRemoveFieldProvider
     */
    public function testAddRemoveFields($data, $expectedResult)
    {
        $this->obfuscator->addRemoveFields(array('pass'));
        $this->assertEquals(
            $expectedResult,
            $this->obfuscator->cleanParamsForLog($data)
        );
    }

    public function addRemoveFieldProvider()
    {
        return array(
            'remove pass' => array(
                array(
                    'user' => 'this must be readable',
                    'pass' => 'this shall be removed',
                ),
                array(
                    'user' => 'this must be readable',
                    'pass' => '<removed>',
                ),
            ),
            'remove cart' => array(
                array(
                    'user' => 'this must be readable',
                    'cart' => array(
                        'amount'    => 12.34,
                        'all infos' => 'in this array must be removed',
                    ),
                ),
                array(
                    'user' => 'this must be readable',
                    'cart' => '<removed>',
                ),
            ),
        );
    }

    /**
     * @param array  $data
     * @param string $resultString
     *
     * @dataProvider cleanParamsForLogDefaultProvider
     */
    public function testCleanParamsForLogDefault($data, $resultString)
    {
        $loggingResult = $this->obfuscator->cleanParamsForLog($data);

        $this->assertEquals($resultString, $loggingResult);
    }

    public function cleanParamsForLogDefaultProvider()
    {
        return array(
            'secure'      => array(
                array(
                    'username' => 'this must be readable',
                    'pass'     => 'this is secure',
                ),
                array(
                    'username' => 'this must be readable',
                    'pass'     => 'XXXXXXXX',
                ),
            ),
            'secure only' => array(
                array(
                    'pass' => 'this is secure',
                ),
                array(
                    'pass' => 'XXXXXXXX',
                ),
            ),
            'no secure'   => array(
                array(
                    'test' => 'this must be readable',
                ),
                array(
                    'test' => 'this must be readable',
                ),
            ),
        );
    }
}
