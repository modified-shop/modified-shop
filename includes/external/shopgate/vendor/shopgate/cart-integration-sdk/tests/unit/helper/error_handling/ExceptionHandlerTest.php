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

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface */
    protected $stackTraceGenerator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;

    public function setUp()
    {
        $this->stackTraceGenerator = $this
            ->getMockBuilder('Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface')
            ->getMock();

        $this->logging = $this
            ->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock();
    }

    public function testStackTraceGeneratorAndLoggerCalledOnShopgateLibraryException()
    {
        /** @var \ShopgateLibraryException $exception */
        $exception = $this
            ->getMockBuilder('ShopgateLibraryException')
            ->disableOriginalConstructor()
            ->getMock();

        $stackTrace = 'Dummy Stack Trace';

        $this->stackTraceGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($exception)
            ->willReturn($stackTrace);

        $this->logging
            ->expects($this->once())
            ->method('log')
            ->with(
                new \PHPUnit_Framework_Constraint_IsAnything(), // message
                \Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR,
                $stackTrace
            )
            ->willReturn(true);

        $SUT = new \Shopgate_Helper_Error_Handling_ExceptionHandler($this->stackTraceGenerator, $this->logging);
        $SUT->handle($exception);
    }

    public function testStackTraceGeneratorAndLoggerNotCalledOnNonShopgateLibraryException()
    {
        $this->stackTraceGenerator->expects($this->never())->method('generate');
        $this->logging->expects($this->never())->method('log');

        $SUT = new \Shopgate_Helper_Error_Handling_ExceptionHandler($this->stackTraceGenerator, $this->logging);
        $SUT->handle(new \Exception());
    }
}
