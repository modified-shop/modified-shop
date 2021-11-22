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
class ShopgateLoggerTest extends PHPUnit_Framework_TestCase
{
    /** @var ShopgateLogger */
    private $shopgateLogger;

    public function setUp()
    {
        /** @noinspection PhpDeprecationInspection */
        $this->shopgateLogger = ShopgateLogger::getInstance();
    }

    public function testInstantiateShopgateLogger()
    {
        $this->assertInstanceOf('ShopgateLogger', $this->shopgateLogger);
        #$this->assertInstanceOf('LoggingInterface', $this->shopgateLogger);
    }

    public function testInjectLoggingStrategy()
    {
        $loggingStrategy = $this->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock();

        $this->shopgateLogger->setLoggingStrategy($loggingStrategy);

        $this->assertEquals($loggingStrategy, $this->shopgateLogger->getLoggingStrategy());
    }

    public function testInjectLoggingStrategyEnableDebug()
    {
        $loggingStrategy = $this->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock();
        $loggingStrategy->expects($this->once())->method('enableDebug')->willReturn(true);
        $loggingStrategy->expects($this->once())->method('isDebugEnabled')->willReturn(true);

        $this->shopgateLogger->setLoggingStrategy($loggingStrategy);

        $this->shopgateLogger->enableDebug();

        $this->assertEquals(true, $this->shopgateLogger->isDebugEnabled());
    }

    public function testInjectLoggingStrategyDisableDebug()
    {
        $loggingStrategy = $this->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock();
        $loggingStrategy->expects($this->once())->method('disableDebug')->willReturn(true);
        $loggingStrategy->expects($this->once())->method('isDebugEnabled')->willReturn(false);

        $this->shopgateLogger->setLoggingStrategy($loggingStrategy);

        $this->shopgateLogger->disableDebug();

        $this->assertEquals(false, $this->shopgateLogger->isDebugEnabled());
    }

    /**
     * testing log proxy
     *
     * @param string $msg
     * @param string $type
     *
     * @dataProvider logProvider
     */
    public function testInjectLoggingStrategyLog($msg, $type)
    {
        $loggingStrategy = $this->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock();

        $loggingStrategy->expects($this->once())->method('log')->with($msg, $type)->willReturn(true);

        $this->shopgateLogger->setLoggingStrategy($loggingStrategy);

        $success = $this->shopgateLogger->log($msg, $type);

        $this->assertTrue($success);
    }

    /**
     * testing tail proxy
     *
     * @param string $mockLogContent
     * @param string $type
     *
     * @dataProvider logProvider
     */
    public function testInjectLoggingStrategyTail($mockLogContent, $type)
    {
        $loggingStrategy = $this->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock();
        $loggingStrategy->expects($this->once())->method('tail')->with($type)->willReturn($mockLogContent);

        $this->shopgateLogger->setLoggingStrategy($loggingStrategy);

        $logs = $this->shopgateLogger->tail($type);

        $this->assertEquals($logs, $mockLogContent);
    }

    /**
     * @return array
     */
    public function logProvider()
    {
        return array(
            'log access'  => array(
                'this is a test access log message',
                ShopgateLogger::LOGTYPE_ACCESS,
            ),
            'log debug'   => array(
                'this is a test debug log message',
                ShopgateLogger::LOGTYPE_DEBUG,
            ),
            'log error'   => array(
                'this is a test error log message',
                ShopgateLogger::LOGTYPE_ERROR,
            ),
            'log request' => array(
                'this is a test request log message',
                ShopgateLogger::LOGTYPE_REQUEST,
            ),
        );
    }

    /**
     * testing keepDebug proxy
     */
    public function testInjectLoggingStrategyKeepDebugLog()
    {
        $loggingStrategy = $this->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock();

        $keepDebugLog = true;
        $loggingStrategy->expects($this->once())->method('keepDebugLog')->with($keepDebugLog);

        $this->shopgateLogger->setLoggingStrategy($loggingStrategy);

        $this->shopgateLogger->keepDebugLog($keepDebugLog);
    }

    /**
     * testing cleanParamsForLog proxy
     *
     * @param array $data
     *
     * @dataProvider cleanParamsForLogProvider
     */
    public function testCleanParamsForLog($data)
    {
        $obfuscator = $this->getMockBuilder('Shopgate_Helper_Logging_Obfuscator')
            ->getMock();
        $obfuscator->expects($this->once())->method('cleanParamsForLog')->with($data);
        $this->shopgateLogger->setObfuscator($obfuscator);
        $this->shopgateLogger->cleanParamsForLog($data);
    }

    public function cleanParamsForLogProvider()
    {
        return array(
            'secure'      => array(
                array(
                    'username' => 'this must be readable',
                    'pass'     => 'this is secure',
                ),
            ),
            'secure only' => array(
                array(
                    'pass' => 'this is secure',
                ),
            ),
            'no secure'   => array(
                array(
                    'test' => 'this must be readable',
                ),
            ),
        );
    }

    /**
     * @param string $parameterSize
     * @param string $expectedUnit
     *
     * @dataProvider setMemoryAnalyserLoggingSizeUnitProvider
     */
    public function testSetMemoryAnalyserLoggingSizeUnit($parameterSize, $expectedUnit)
    {
        $this->shopgateLogger->setMemoryAnalyserLoggingSizeUnit($parameterSize);

        $this->assertEquals($expectedUnit, $this->shopgateLogger->getMemoryAnalyserLoggingSizeUnit());
    }

    public function setMemoryAnalyserLoggingSizeUnitProvider()
    {
        return array(
            'bytes'     => array(
                'ByTes',
                'BYTES',
            ),
            'kilobytes' => array(
                'KiloBYtes',
                'KB',
            ),
            'kilobyte'  => array(
                'KiloBYte',
                'KB',
            ),
            'kb'        => array(
                'kB',
                'KB',
            ),
            'Megabytes' => array(
                'mEgaBytes',
                'MB',
            ),
            'Megabyte'  => array(
                'mEgaByte',
                'MB',
            ),
            'Mb'        => array(
                'Mb',
                'MB',
            ),
            'gIgaBytes' => array(
                'gIgaBytes',
                'GB',
            ),
            'gIgaByte'  => array(
                'gIgaByte',
                'GB',
            ),
            'Gb'        => array(
                'Gb',
                'GB',
            ),
        );
    }
}
