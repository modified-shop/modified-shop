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
class Shopgate_Helper_Error_Handling_ShutdownHandler
{
    /** @var Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;

    /** @var Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider */
    protected $lastErrorProvider;

    /**
     * @param Shopgate_Helper_Logging_Strategy_LoggingInterface                 $logging
     * @param Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider $lastErrorProvider
     */
    public function __construct(
        Shopgate_Helper_Logging_Strategy_LoggingInterface $logging,
        Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider $lastErrorProvider
    ) {
        $this->logging           = $logging;
        $this->lastErrorProvider = $lastErrorProvider;
    }

    /**
     * Handles errors upon shutdown of PHP.
     *
     * This will look up if a fatal error caused PHP to shut down. If so, the error will be logged to the error log.
     */
    public function handle()
    {
        $error = $this->lastErrorProvider->get();

        if ($error === null) {
            return;
        }

        if (!($error['type'] & (E_ERROR | E_USER_ERROR))) {
            return;
        }

        $this->logging->log(
            'Script stopped due to FATAL error in ' . $error['file'] .
            ' in line ' . $error['line'] .
            ' with message: ' . $error['message']
        );
    }
}
