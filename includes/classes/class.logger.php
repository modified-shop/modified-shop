<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


class LoggingManager
{

    /**
     * Logging Level
     */
    const DEBUG = 4;
    const FINE = 3;
    const INFO = 2;
    const WARN = 1;
    const ERROR = 0;
    const DEFAULT_LEVEL = 0;

    /**
     * Logger Name
     * @var string
     */
    private $loggerName;

    /**
     * Log Enabled
     *
     * @var bool
     */
    private $isLoggingEnabled;

    /**
     * Configured Logging Level
     *
     * @var int|mixed
     */
    private $loggingLevel;

    /**
     * Configured Logging File
     *
     * @var string
     */
    private $loggerFile;
    private $loggerFileError;
    private $loggerFileWarning;
    private $loggerFileInfo;
    private $loggerFileFine;
    private $loggerFileDebug;

    /**
     * Configured Logging Path
     *
     * @var string
     */
    private $loggingPath;

    /**
     * Configured Logging Path
     *
     * @var boolean
     */
    private $splitLogging = false;

    /**
     * Configured Threshold
     *
     * @var int
     */
    private $loggerThreshold = 1048576;

    /**
     * Default Constructor
     */
    public function __construct($config = array())
    {
        $this->isLoggingEnabled = ((isset($config['FileName']) && $config['LogEnabled'] === true) ? true : false);
        $this->setLoggerName(((isset($config['LogName'])) ? $config['LogName'] : __CLASS__));

        if ($this->isLoggingEnabled === true) {
            if (isset($config['FileName'])) {
                $this->setLoggerFile($config['FileName']);
            }
            if (isset($config['FileName.error'])) {
                $this->setLoggerFileError($config['FileName.error']);
            }
            if (isset($config['FileName.warning'])) {
                $this->setLoggerFileWarning($config['FileName.warning']);
            }
            if (isset($config['FileName.info'])) {
                $this->setLoggerFileInfo($config['FileName.info']);
            }
            if (isset($config['FileName.fine'])) {
                $this->setLoggerFileFine($config['FileName.fine']);
            }
            if (isset($config['FileName.debug'])) {
                $this->setLoggerFileDebug($config['FileName.debug']);
            }
            if (isset($config['FileName.custom'])) {
                $this->setLoggerFileCustom($config['FileName.custom']);
            }
            if (isset($config['LogThreshold']) && $config['LogThreshold'] > 0) {
                $this->setLoggerThreshold($config['LogThreshold']);
            }
            if (isset($config['SplitLogging'])) {
                $this->setLoggerSplitLogging($config['SplitLogging']);
            }
                        
            $this->loggingPath = dirname($this->loggerFile);            
            
            $loggingLevel = ((isset($config['LogLevel'])) ? strtoupper($config['LogLevel']) : '');
            $this->loggingLevel = defined("LoggingManager::$loggingLevel") ? constant("LoggingManager::$loggingLevel") : LoggingManager::DEFAULT_LEVEL;
        }
    }

    /**
     * Sets Logger File Global
     *
     * @param string $loggerFile
     */
    public function setLoggerFile($loggerFile)
    {
        $this->loggerFile = $loggerFile;
    }

    /**
     * Sets Logger File Error
     *
     * @param string $loggerFile
     */
    public function setLoggerFileError($loggerFile)
    {
        $this->loggerFileError = $loggerFile;
    }

    /**
     * Sets Logger File Warning
     *
     * @param string $loggerFile
     */
    public function setLoggerFileWarning($loggerFile)
    {
        $this->loggerFileWarning = $loggerFile;
    }

    /**
     * Sets Logger File Info
     *
     * @param string $loggerFile
     */
    public function setLoggerFileInfo($loggerFile)
    {
        $this->loggerFileInfo = $loggerFile;
    }

    /**
     * Sets Logger File Fine
     *
     * @param string $loggerFile
     */
    public function setLoggerFileFine($loggerFile)
    {
        $this->loggerFileFine = $loggerFile;
    }

    /**
     * Sets Logger File Debug
     *
     * @param string $loggerFile
     */
    public function setLoggerFileDebug($loggerFile)
    {
        $this->loggerFileDebug = $loggerFile;
    }

    /**
     * Sets Logger File Custom
     *
     * @param string $loggerFile
     */
    public function setLoggerFileCustom($loggerFile)
    {
        $this->loggerFileCustom = $loggerFile;
    }

    /**
     * Sets Logger Name. Generally defaulted to Logging Class
     *
     * @param string $loggerName
     */
    public function setLoggerName($loggerName = __CLASS__)
    {
        $this->loggerName = $loggerName;
    }

    /**
     * Sets Logger Threshold. Generally defaulted to 1MB
     *
     * @param int $treshold
     */
    public function setLoggerThreshold($treshold)
    {
        $this->loggerThreshold = $this->parseTreshold($treshold);
    }

    /**
     * Parse Logger Threshold. Generally defaulted to 1MB
     *
     * @param string|int $treshold
     */
    private function parseTreshold($treshold) {
      preg_match('/(.+)(.{2})$/', $treshold, $matches);
      list($treshold, $value, $unit) = $matches;
      $treshold = (int) ($value * pow(1024, array_search(strtolower($unit), array(1 => 'kb','mb','gb','tb'))));

      return $treshold;
    }

    /**
     * Sets Logger Split Logging
     *
     * @param boolean $splitLogging
     */
    public function setLoggerSplitLogging($splitLogging)
    {
        $this->splitLogging = (bool)$splitLogging;
    }

    /**
     * Default Logger
     *
     * @param string $message
     * @param int $level
     */
    public function log($message, $loggingLevel = 'FINE')
    {
        if ($this->isLoggingEnabled) {
            
            if ($this->loggerFile != '' && is_file($this->loggerFile)) {
                $filesize = filesize($this->loggerFile);            
                if ($filesize >= $this->loggerThreshold) {
                    $this->LoggerRotate($this->loggerFile);
                }
            }
            
            $parsedLoggingLevel = $this->getLoggingLevel($loggingLevel);
            if ($parsedLoggingLevel == 'CUSTOM' || constant("LoggingManager::$parsedLoggingLevel") <= $this->loggingLevel) {
                if ($this->splitLogging === true && $parsedLoggingLevel != 'DEFAULT_LEVEL') {
                  $func = strtolower($parsedLoggingLevel);
                  LoggingManager::$func($message, $loggingLevel);
                } else {
                  if ($this->loggerFile != '') {
                      error_log("[" . date('d-m-Y H:i:s') . "] $loggingLevel\t: " . $this->loggerName . ": $message\n", 3, $this->loggerFile);
                  } else {
                      error_log("[" . date('d-m-Y H:i:s') . "] $loggingLevel\t: " . $this->loggerName . ": $message\n");
                  }
                }
            }
        }
    }

    /**
     * Log Error
     *
     * @param string $message
     */
    public function error($message, $error = 'ERROR')
    {
        if ($this->loggerFileError != '') {
            if (is_file($this->loggerFileError)) {
                $filesize = filesize($this->loggerFileError);            
                if ($filesize >= $this->loggerThreshold) {
                    $this->LoggerRotate($this->loggerFileError);
                }
            }
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n", 3, $this->loggerFileError);
        } else {
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n");
        }
    }

    /**
     * Log Warning
     *
     * @param string $message
     */
    public function warn($message, $error = 'WARNING')
    {
        if ($this->loggerFileWarning != '') {
            if (is_file($this->loggerFileWarning)) {
                $filesize = filesize($this->loggerFileWarning);            
                if ($filesize >= $this->loggerThreshold) {
                    $this->LoggerRotate($this->loggerFileWarning);
                }
            }
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n", 3, $this->loggerFileWarning);
        } else {
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n");
        }
    }

    /**
     * Log Info
     *
     * @param string $message
     */
    public function info($message, $error = 'INFO')
    {
        if ($this->loggerFileInfo != '') {
            if (is_file($this->loggerFileInfo)) {
                $filesize = filesize($this->loggerFileInfo);            
                if ($filesize >= $this->loggerThreshold) {
                    $this->LoggerRotate($this->loggerFileInfo);
                }
            }
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n", 3, $this->loggerFileInfo);
        } else {
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n");
        }
    }

    /**
     * Log Fine
     *
     * @param string $message
     */
    public function fine($message, $error = 'FINE')
    {
        if ($this->loggerFileFine != '') {
            if (is_file($this->loggerFileFine)) {
                $filesize = filesize($this->loggerFileFine);            
                if ($filesize >= $this->loggerThreshold) {
                    $this->LoggerRotate($this->loggerFileFine);
                }
            }
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n", 3, $this->loggerFileFine);
        } else {
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n");
        }
    }

    /**
     * Log Fine
     *
     * @param string $message
     */
    public function debug($message, $error = 'DEBUG')
    {
        if ($this->loggerFileDebug != '') {
            if (is_file($this->loggerFileDebug)) {
                $filesize = filesize($this->loggerFileDebug);            
                if ($filesize >= $this->loggerThreshold) {
                    $this->LoggerRotate($this->loggerFileDebug);
                }
            }
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n", 3, $this->loggerFileDebug);
        } else {
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n");
        }
    }

    /**
     * Log Fine
     *
     * @param string $message
     */
    public function custom($message, $error = 'CUSTOM')
    {
        if ($this->loggerFileCustom != '') {
            if (is_file($this->loggerFileCustom)) {
                $filesize = filesize($this->loggerFileCustom);            
                if ($filesize >= $this->loggerThreshold) {
                    $this->LoggerRotate($this->loggerFileCustom);
                }
            }
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n", 3, $this->loggerFileCustom);
        } else {
            error_log("[" . date('d-m-Y H:i:s') . "] $error\t: " . $this->loggerName . ": $message\n");
        }
    }

    /**
     * Rotate Logging File
     *
     */
    private function LoggerRotate($file)
    {
        if ($this->loggingPath == '') {
            $this->loggingPath = dirname($file);
        }
        $counter = 0;
        foreach (new DirectoryIterator($this->loggingPath) as $info) {
            if ($info->isDot() || !$info->isFile()) {
                continue;
            }            
            $fileinfo = pathinfo($info->getFilename());
            if ($fileinfo['filename'] == basename($file)) {
                if ($fileinfo['extension'] > $counter) {
                    $counter = $fileinfo['extension'];
                }
            }
        }
        $counter ++;
        
        // rotate
        rename($file, $file.'.'.$counter);
    }

    /**
     * Gets Logging Level
     *
     * @param string $level
     */
    private function getLoggingLevel($level)
    {
        switch($level) {
            case 'ERROR':
            case 'E_PARSE':
            case 'E_ERROR':
            case 'E_CORE_ERROR':
            case 'E_USER_ERROR':
            case 'E_RECOVERABLE_ERROR':
            case 'UNDEFINED_ERROR':
                return 'ERROR';

            case 'WARN':
            case 'E_WARNING':
            case 'E_CORE_WARNING':
            case 'E_USER_WARNING':
                return 'WARN';

            case 'INFO':
            case 'E_STRICT':
                return 'INFO';

            case 'FINE':
            case 'E_DEPRECATED':
            case 'E_USER_DEPRECATED':
                return 'FINE';

            case 'DEBUG':
            case 'E_NOTICE':
                return 'DEBUG';

            case 'E_USER_NOTICE':
                return 'CUSTOM';
        }
        return 'DEFAULT_LEVEL';
    }
}
?>