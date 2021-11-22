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
interface Shopgate_Helper_Logging_Strategy_LoggingInterface
{
    const LOGTYPE_ACCESS  = 'access';
    const LOGTYPE_REQUEST = 'request';
    const LOGTYPE_ERROR   = 'error';
    const LOGTYPE_DEBUG   = 'debug';

    /**
     * Enables logging messages to debug log file.
     */
    public function enableDebug();

    /**
     * Disables logging messages to debug log file.
     */
    public function disableDebug();

    /**
     * @return bool true if logging messages to debug log file is enabled, false otherwise.
     */
    public function isDebugEnabled();

    /**
     * Enables logging the stack trace, if available.
     */
    public function enableStackTrace();

    /**
     * Disables logging the stack trace.
     */
    public function disableStackTrace();

    /**
     * Logs a message to the according log file.
     *
     * Logging to LOGTYPE_DEBUG only is done after $this->enableDebug() has been called and $this->disableDebug() has
     * not been called after that. The debug log file will be truncated on opening by default. To prevent this call
     * $this->keepDebugLog(true).
     *
     * @param string $msg        The error message.
     * @param string $type       The log type, that would be one of the ShopgateLogger::LOGTYPE_* constants.
     * @param string $stackTrace The stack trace that led to the error, if available.
     *
     * @return bool true on success, false on error.
     */
    public function log($msg, $type = self::LOGTYPE_ERROR, $stackTrace = '');

    /**
     * Returns the requested number of lines of the requested log file's end.
     *
     * @param string $type  The log file to be read
     * @param int    $lines Number of lines to return
     *
     * @return string The requested log file content
     * @throws ShopgateLibraryException
     *
     * @see http://tekkie.flashbit.net/php/tail-functionality-in-php
     */
    public function tail($type = self::LOGTYPE_ERROR, $lines = 20);

    /**
     * If true the debug log of the last request won't be deleted and additional debug output will be appended
     *
     * @param bool $keep
     */
    public function keepDebugLog($keep);
}
