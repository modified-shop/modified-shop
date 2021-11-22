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
class Shopgate_Helper_Logging_Strategy_DefaultLogging implements Shopgate_Helper_Logging_Strategy_LoggingInterface
{
    /** @var bool */
    private $debug;

    /** @var bool */
    private $useStackTrace;

    /** @var mixed[] */
    private $logFiles = array(
        self::LOGTYPE_ACCESS  => array('path' => '', 'handle' => null, 'mode' => 'a+'),
        self::LOGTYPE_REQUEST => array('path' => '', 'handle' => null, 'mode' => 'a+'),
        self::LOGTYPE_ERROR   => array('path' => '', 'handle' => null, 'mode' => 'a+'),
        self::LOGTYPE_DEBUG   => array('path' => '', 'handle' => null, 'mode' => 'w+'),
    );

    public function __construct(
        $accessLogPath = null,
        $requestLogPath = null,
        $errorLogPath = null,
        $debugLogPath = null
    ) {
        // fall back to default log paths if none are specified
        if (empty($accessLogPath)) {
            $accessLogPath =
                SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . ShopgateConfigInterface::SHOPGATE_FILE_PREFIX
                . 'access.log';
        }
        if (empty($requestLogPath)) {
            $requestLogPath =
                SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . ShopgateConfigInterface::SHOPGATE_FILE_PREFIX
                . 'request.log';
        }
        if (empty($errorLogPath)) {
            $errorLogPath =
                SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . ShopgateConfigInterface::SHOPGATE_FILE_PREFIX
                . 'error.log';
        }
        if (empty($debugLogPath)) {
            $debugLogPath =
                SHOPGATE_BASE_DIR . DS . 'temp' . DS . 'logs' . DS . ShopgateConfigInterface::SHOPGATE_FILE_PREFIX
                . 'debug.log';
        }

        $this->setLogFilePaths($accessLogPath, $requestLogPath, $errorLogPath, $debugLogPath);

        $this->debug         = false;
        $this->useStackTrace = true;
    }

    public function enableDebug()
    {
        $this->debug = true;
    }

    public function disableDebug()
    {
        $this->debug = false;
    }

    public function isDebugEnabled()
    {
        return $this->debug;
    }

    public function enableStackTrace()
    {
        $this->useStackTrace = true;
    }

    public function disableStackTrace()
    {
        $this->useStackTrace = false;
    }

    public function log($msg, $type = self::LOGTYPE_ERROR, $stackTrace = '')
    {
        // build log message
        $msg = gmdate('d-m-Y H:i:s: ') . $msg . "\n" . ($this->useStackTrace
                ? $stackTrace . "\n\n"
                : '');

        // determine log file type and append message
        switch (strtolower($type)) {
            // write to error log if type is unknown
            default:
                $type = self::LOGTYPE_ERROR;

            // allowed types:
            // no break
            case self::LOGTYPE_ERROR:
            case self::LOGTYPE_ACCESS:
            case self::LOGTYPE_REQUEST:
            case self::LOGTYPE_DEBUG:
        }

        // if debug logging is requested but not activated, simply return
        if (($type === self::LOGTYPE_DEBUG) && !$this->debug) {
            return true;
        }

        // open log files if necessary
        if (!$this->openLogFileHandle($type)) {
            return false;
        }

        // try to log
        $success = false;
        if (fwrite($this->logFiles[$type]['handle'], $msg) !== false) {
            $success = true;
        }

        return $success;
    }

    public function tail($type = self::LOGTYPE_ERROR, $lines = 20)
    {
        if (!isset($this->logFiles[$type])) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_LOGTYPE, 'Type: ' . $type);
        }

        if (!$this->openLogFileHandle($type)) {
            throw new ShopgateLibraryException(ShopgateLibraryException::INIT_LOGFILE_OPEN_ERROR, 'Type: ' . $type);
        }

        if (empty($lines)) {
            $lines = 20;
        }

        $handle      = $this->logFiles[$type]['handle'];
        $lineCounter = $lines;
        $pos         = -2;
        $beginning   = false;
        $text        = '';

        while ($lineCounter > 0) {
            $t = '';
            while ($t !== "\n") {
                if (@fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = @fgetc($handle);
                $pos--;
            }

            $lineCounter--;
            if ($beginning) {
                @rewind($handle);
            }
            $text = @fgets($handle) . $text;
            if ($beginning) {
                break;
            }
        }

        return $text;
    }

    /**
     * Sets the paths to the log files.
     *
     * @param string $accessLogPath
     * @param string $requestLogPath
     * @param string $errorLogPath
     * @param string $debugLogPath
     */
    public function setLogFilePaths($accessLogPath, $requestLogPath, $errorLogPath, $debugLogPath)
    {
        if (!empty($accessLogPath)) {
            $this->logFiles[self::LOGTYPE_ACCESS]['path'] = $accessLogPath;
        }

        if (!empty($requestLogPath)) {
            $this->logFiles[self::LOGTYPE_REQUEST]['path'] = $requestLogPath;
        }

        if (!empty($errorLogPath)) {
            $this->logFiles[self::LOGTYPE_ERROR]['path'] = $errorLogPath;
        }

        if (!empty($debugLogPath)) {
            $this->logFiles[self::LOGTYPE_DEBUG]['path'] = $debugLogPath;
        }
    }

    /**
     * Opens log file handles for the requested log type if necessary.
     *
     * Already opened file handles will not be opened again.
     *
     * @param string $type The log type, that would be one of the self::LOGTYPE_* constants.
     *
     * @return bool true if opening succeeds or the handle is already open; false on error.
     */
    protected function openLogFileHandle($type)
    {
        // don't open file handle if already open
        if (!empty($this->logFiles[$type]['handle'])) {
            return true;
        }

        // set the file handle
        $this->logFiles[$type]['handle'] = @fopen($this->logFiles[$type]['path'], $this->logFiles[$type]['mode']);

        // if log files are not writeable continue silently to the next handle
        // TODO: This seems a bit too silent... How could we get notice of the error?
        if ($this->logFiles[$type]['handle'] === false) {
            return false;
        }

        return true;
    }

    public function keepDebugLog($keep)
    {
        if ($keep) {
            $this->logFiles[self::LOGTYPE_DEBUG]["mode"] = "a+";
        } else {
            $this->logFiles[self::LOGTYPE_DEBUG]["mode"] = "w+";
        }
    }

    /**
     * @return mixed[]
     */
    public function getLogFiles()
    {
        return $this->logFiles;
    }
}
