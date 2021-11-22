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
 * Global class (Singleton) to manage log files.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 * @deprecated
 */
class ShopgateLogger
{
    const OBFUSCATION_STRING = Shopgate_Helper_Logging_Obfuscator::OBFUSCATION_STRING;
    const REMOVED_STRING     = Shopgate_Helper_Logging_Obfuscator::REMOVED_STRING;
    const LOGTYPE_ACCESS     = Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ACCESS;
    const LOGTYPE_REQUEST    = Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_REQUEST;
    const LOGTYPE_ERROR      = Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR;
    const LOGTYPE_DEBUG      = Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_DEBUG;

    /** @var string */
    private $memoryAnalyserLoggingSizeUnit;

    /** @var ShopgateLogger */
    private static $singleton;

    /** @var Shopgate_Helper_Logging_Obfuscator */
    private $obfuscator;

    /** @var Shopgate_Helper_Logging_Strategy_LoggingInterface */
    private $loggingStrategy;

    /**
     * @param string $accessLogPath
     * @param string $requestLogPath
     * @param string $errorLogPath
     * @param string $debugLogPath
     *
     * @return ShopgateLogger
     */
    public static function getInstance(
        $accessLogPath = null,
        $requestLogPath = null,
        $errorLogPath = null,
        $debugLogPath = null
    ) {
        if (empty(self::$singleton)) {
            self::$singleton = new self();

            self::$singleton->setLoggingStrategy(
                new Shopgate_Helper_Logging_Strategy_DefaultLogging(
                    $accessLogPath,
                    $requestLogPath,
                    $errorLogPath,
                    $debugLogPath
                )
            );
        }

        if (self::$singleton->loggingStrategy instanceof Shopgate_Helper_Logging_Strategy_DefaultLogging) {
            /** @noinspection PhpUndefinedMethodInspection */
            self::$singleton->loggingStrategy->setLogFilePaths(
                $accessLogPath,
                $requestLogPath,
                $errorLogPath,
                $debugLogPath
            );
        }

        return self::$singleton;
    }

    public function __construct()
    {
        $this->obfuscator                    = new Shopgate_Helper_Logging_Obfuscator();
        $this->memoryAnalyserLoggingSizeUnit = 'MB';
    }

    /**
     * @param Shopgate_Helper_Logging_Strategy_LoggingInterface $loggingStrategy
     */
    public function setLoggingStrategy($loggingStrategy)
    {
        $this->loggingStrategy = $loggingStrategy;
    }

    /**
     * @param Shopgate_Helper_Logging_Obfuscator $obfuscator
     */
    public function setObfuscator($obfuscator)
    {
        $this->obfuscator = $obfuscator;
    }

    /**
     * @return Shopgate_Helper_Logging_Strategy_LoggingInterface
     */
    public function getLoggingStrategy()
    {
        return $this->loggingStrategy;
    }

    /**
     * @return Shopgate_Helper_Logging_Obfuscator
     */
    public function getObfuscator()
    {
        return $this->obfuscator;
    }

    /**
     * Enables logging messages to debug log file.
     */
    public function enableDebug()
    {
        $this->loggingStrategy->enableDebug();
    }

    /**
     * Disables logging messages to debug log file.
     */
    public function disableDebug()
    {
        $this->loggingStrategy->disableDebug();
    }

    /**
     * @return bool true if logging messages to debug log file is enabled, false otherwise.
     */
    public function isDebugEnabled()
    {
        return $this->loggingStrategy->isDebugEnabled();
    }

    /**
     * Logs a message to the according log file.
     *
     * Logging to LOGTYPE_DEBUG only is done after $this->enableDebug() has been called and $this->disableDebug() has
     * not been called after that. The debug log file will be truncated on opening by default. To prevent this call
     * $this->keepDebugLog(true).
     *
     * @param string $msg  The error message.
     * @param string $type The log type, that would be one of the ShopgateLogger::LOGTYPE_* constants.
     *
     * @return bool true on success, false on error.
     */
    public function log($msg, $type = ShopgateLogger::LOGTYPE_ERROR)
    {
        return $this->loggingStrategy->log($msg, $type);
    }

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
    public function tail($type = ShopgateLogger::LOGTYPE_ERROR, $lines = 20)
    {
        return $this->loggingStrategy->tail($type, $lines);
    }

    public function keepDebugLog($keep)
    {
        $this->loggingStrategy->keepDebugLog($keep);
    }

    /**
     * Sets the unit in which the memory usage logger outputs its values in
     *
     * @param string $sizeUnit ('MB', 'BYTES', 'GB', 'KB', ...)
     */
    public function setMemoryAnalyserLoggingSizeUnit($sizeUnit)
    {
        switch (strtoupper(trim($sizeUnit))) {
            case 'GB':
            case 'GIGABYTE':
            case 'GIGABYTES':
                $this->memoryAnalyserLoggingSizeUnit = 'GB';
                break;
            case 'MB':
            case 'MEGABYTE':
            case 'MEGABYTES':
                $this->memoryAnalyserLoggingSizeUnit = 'MB';
                break;
            case 'KB':
            case 'KILOBYTE':
            case 'KILOBYTES':
                $this->memoryAnalyserLoggingSizeUnit = 'KB';
                break;
            default:
                $this->memoryAnalyserLoggingSizeUnit = 'BYTES';
                break;
        }
    }

    /**
     * returns the unit in which the memory usage logger outputs its values in
     *
     * @return string
     */
    public function getMemoryAnalyserLoggingSizeUnit()
    {
        return $this->memoryAnalyserLoggingSizeUnit;
    }

    /**
     * Function to prepare the parameters of an API request for logging.
     *
     * Strips out critical request data like the password of a get_customer request.
     *
     * @param mixed[] $data The incoming request's parameters.
     *
     * @return string The cleaned parameters as string ready to log.
     */
    public function cleanParamsForLog($data)
    {
        return print_r($this->obfuscator->cleanParamsForLog($data), true);
    }
}
