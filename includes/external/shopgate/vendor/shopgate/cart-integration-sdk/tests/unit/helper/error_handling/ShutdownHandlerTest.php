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

namespace shopgate\cart_integration_sdk\tests\unit\error_handling;

class ShutdownHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider */
    protected $lastErrorProvider;

    public function setUp()
    {
        $this->logging = $this
            ->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock();

        $this->lastErrorProvider = $this
            ->getMockBuilder('Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider')
            ->setMethods(array('get'))
            ->getMock();
    }

    public function testLoggerCalledOnShutdownFatalErrors()
    {
        $this->logging
            ->expects($this->exactly(2))
            ->method('log')
            ->with(
                new \PHPUnit_Framework_Constraint_IsAnything(), // message
                \Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR,
                ''
            )
            ->willReturnOnConsecutiveCalls(true);

        // E_ERROR
        $this->lastErrorProvider
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                array(
                    'type'    => E_ERROR,
                    'message' => 'Call to member function on a non-object.',
                    'file'    => '/var/www/failing_script.php',
                    'line'    => 99,
                ),
                array(
                    'type'    => E_USER_ERROR,
                    'message' => 'Call to member function on a non-object.',
                    'file'    => '/var/www/failing_script.php',
                    'line'    => 99,
                )
            );

        $SUT = new \Shopgate_Helper_Error_Handling_ShutdownHandler($this->logging, $this->lastErrorProvider);
        $SUT->handle(); // E_ERROR
        $SUT->handle(); // E_USER_ERROR
    }

    public function testLoggerNotCalledOnShutdownNonFatalError()
    {
        $this->logging->expects($this->never())->method('log');

        $this->lastErrorProvider
            ->expects($this->once())
            ->method('get')
            ->willReturn(
                array(
                    'type'    => E_WARNING,
                    'message' => 'Illegal string offset \'bla\'.',
                    'file'    => '/var/www/failing_script.php',
                    'line'    => 99,
                )
            );

        $SUT = new \Shopgate_Helper_Error_Handling_ShutdownHandler($this->logging, $this->lastErrorProvider);
        $SUT->handle();
    }

    public function testLoggerNotCalledOnRegularShutdownOrErrorGetLastNotAvailable()
    {
        $this->logging->expects($this->never())->method('log');

        $this->lastErrorProvider
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $SUT = new \Shopgate_Helper_Error_Handling_ShutdownHandler($this->logging, $this->lastErrorProvider);
        $SUT->handle();
    }
}
