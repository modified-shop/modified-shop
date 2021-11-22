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
class Shopgate_Helper_Error_Handling_ExceptionHandler
{
    /** @var Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface */
    protected $stackTraceGenerator;

    /** @var Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;

    /**
     * @param Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface $stackTraceGenerator
     * @param Shopgate_Helper_Logging_Strategy_LoggingInterface      $logging
     */
    public function __construct(
        Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface $stackTraceGenerator,
        Shopgate_Helper_Logging_Strategy_LoggingInterface $logging
    ) {
        $this->stackTraceGenerator = $stackTraceGenerator;
        $this->logging             = $logging;
    }

    /**
     * Handles uncaught exceptions of type ShopgateLibraryException.
     *
     * This handler will take any Exception or Throwable but will only act upon receiving a ShopgateLibraryException.
     * In that case it will log a stack trace to the error log. In all other cases it will return without doing
     * anything.
     *
     * @param Throwable|Exception $e Will accept Throwable for PHP 7 or Exception for PHP < 7.
     *
     * @see http://php.net/manual/en/function.set-exception-handler.php
     */
    public function handle($e)
    {
        if (!($e instanceof ShopgateLibraryException)) {
            return;
        }

        $this->logging->log(
            'FATAL: Uncaught ShopgateLibraryException',
            Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR,
            $this->stackTraceGenerator->generate($e)
        );
    }
}
