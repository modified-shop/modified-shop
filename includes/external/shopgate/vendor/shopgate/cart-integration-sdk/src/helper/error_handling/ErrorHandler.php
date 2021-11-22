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
 * @see http://php.net/manual/de/function.set-error-handler.php
 */
class Shopgate_Helper_Error_Handling_ErrorHandler
{
    /** @var Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface */
    protected $stackTraceGenerator;

    /** @var Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;

    /** @var array [int, string] */
    protected $severityMapping;

    /** @var bool */
    protected $skipInternalErrorHandler;

    /**
     * @param Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface $stackTraceGenerator
     * @param Shopgate_Helper_Logging_Strategy_LoggingInterface      $logging
     * @param bool                                                   $skipInternalErrorHandler
     */
    public function __construct(
        Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface $stackTraceGenerator,
        Shopgate_Helper_Logging_Strategy_LoggingInterface $logging,
        $skipInternalErrorHandler = false
    ) {
        $this->stackTraceGenerator      = $stackTraceGenerator;
        $this->logging                  = $logging;
        $this->skipInternalErrorHandler = $skipInternalErrorHandler;

        $this->severityMapping = array(
            E_NOTICE       => 'Notice',
            E_USER_NOTICE  => 'User Notice',
            E_WARNING      => 'Warning',
            E_USER_WARNING => 'User Warning',
            E_USER_ERROR   => 'User Error',
        );
    }

    /**
     * Handles non-fatal errors.
     *
     * This will generate a stack trace and log it to the error log on any error it receives, unless the line in which
     * the error occured was prefixed with an '@'.
     *
     * Severity of errors being logged depends on how this handler was set using set_error_handler() and types E_ERROR
     * E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING and most of E_STRICT cannot be handled
     * by this handler.
     *
     * @param int    $severity
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param array  $context
     *
     * @return bool
     *
     * @see http://php.net/manual/en/function.set-error-handler.php
     */
    public function handle(
        $severity,
        $message,
        $file,
        $line = -1,
        /** @noinspection PhpUnusedParameterInspection */
        array $context = array()
    ) {
        // on error supression with '@' do not log
        if ($severity === 0) {
            return $this->skipInternalErrorHandler;
        }

        $this->logging->log(
            $this->severityName($severity) . ': ' . $message . ' in ' . $file . ' on line ' . $line,
            Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR,
            $this->stackTraceGenerator->generate(
                new Exception('Wrapped around the actual error by Shopgate error handler.')
            )
        );

        return $this->skipInternalErrorHandler;
    }

    /**
     * @param int $severity
     *
     * @return string
     */
    private function severityName($severity)
    {
        return isset($this->severityMapping[$severity])
            ? $this->severityMapping[$severity]
            : 'Unknown (' . $severity . ')';
    }
}
