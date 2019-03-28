<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

spl_autoload_register(function ($class) {
  if (is_file(DIR_FS_EXTERNAL . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php')) {
    require_once(DIR_FS_EXTERNAL . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
  }
});

use Psr\Log\LogLevel;

class LoggingManager implements \Psr\Log\LoggerInterface
{
    /**
     * File name and path of log file.
     * @var string
     */
    private $logfile;

    /**
     * Log channel--namespace for log lines.
     * Used to identify and correlate groups of similar log lines.
     * @var string
     */
    private $channel;

    /**
     * Lowest log level to log.
     * @var int
     */
    private $loglevel;

    /**
     * Whether to log to standard out.
     * @var bool
     */
    private $stdout;

    /**
     * threshold default 1MB
     * @var int
     */
    private $threshold = 1048576;

    /**
     * Log fields separated by tabs to form a TSV (CSV with tabs).
     */
    const TAB = "\t";

    /**
     * Special minimum log level which will not log any log levels.
     */
    const LOG_LEVEL_NONE = 'none';

    /**
     * Log level hierachy
     */
    const LEVELS = [
        LogLevel::DEBUG      => 0,
        LogLevel::INFO       => 1,
        LogLevel::NOTICE     => 2,
        LogLevel::WARNING    => 3,
        LogLevel::ERROR      => 4,
        LogLevel::CRITICAL   => 5,
        LogLevel::ALERT      => 6,
        LogLevel::EMERGENCY  => 7,

        self::LOG_LEVEL_NONE => 8,
        'custom'             => 9,
    ];

    /**
     * Logger constructor
     *
     * @param string $logfile  File name and path of log file.
     * @param string $channel  Logger channel associated with this logger.
     * @param string $logfile  (optional) Lowest log level to log.
     */
    public function __construct($logfile, $channel, $loglevel = LogLevel::DEBUG)
    {
        $this->logfile = $logfile;
        $this->channel = $channel;
        $this->stdout  = false;
        
        $this->setLogLevel($loglevel);
    }

    /**
     * Set the lowest log level to log.
     *
     * @param string $loglevel
     */
    public function setLogLevel($loglevel)
    {
        if (!array_key_exists($loglevel, self::LEVELS)) {
            $loglevel = self::LOG_LEVEL_NONE;
        }

        $this->loglevel = self::LEVELS[$loglevel];
    }

    /**
     * Set the log channel which identifies the log line.
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Set the standard out option on or off.
     * If set to true, log lines will also be printed to standard out.
     *
     * @param bool $stdout
     */
    public function setOutput($stdout)
    {
        $this->stdout = $stdout;
    }

    /**
     * Log a debug message.
     * Fine-grained informational events that are most useful to debug an application.
     *
     * @param string $message Content of log event.
     * @param array  $context Associative array of contextual support data that goes with the log event.
     *
     * @throws \RuntimeException
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Log an info message.
     * Interesting events and informational messages that highlight the progress of the application at coarse-grained level.
     *
     * @param string $message Content of log event.
     * @param array  $context Associative array of contextual support data that goes with the log event.
     *
     * @throws \RuntimeException
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Log an notice message.
     * Normal but significant events.
     *
     * @param string $message Content of log event.
     * @param array  $context Associative array of contextual support data that goes with the log event.
     *
     * @throws \RuntimeException
     */
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Log a warning message.
     * Exceptional occurrences that are not errors--undesirable things that are not necessarily wrong.
     * Potentially harmful situations which still allow the application to continue running.
     *
     * @param string $message Content of log event.
     * @param array  $context Associative array of contextual support data that goes with the log event.
     *
     * @throws \RuntimeException
     */
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Log an error message.
     * Error events that might still allow the application to continue running.
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     *
     * @param string $message Content of log event.
     * @param array  $context Associative array of contextual support data that goes with the log event.
     *
     * @throws \RuntimeException
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Log a critical condition.
     * Application components being unavailable, unexpected exceptions, etc.
     *
     * @param string $message Content of log event.
     * @param array  $context Associative array of contextual support data that goes with the log event.
     *
     * @throws \RuntimeException
     */
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Log an alert.
     * This should trigger an email or SMS alert and wake you up.
     * Example: Entire site down, database unavailable, etc.
     *
     * @param string $message Content of log event.
     * @param array  $context Associative array of contextual support data that goes with the log event.
     *
     * @throws \RuntimeException
     */
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Log an emergency.
     * System is unsable.
     * This should trigger an email or SMS alert and wake you up.
     *
     * @param string $message Content of log event.
     * @param array  $context Associative array of contextual support data that goes with the log event.
     *
     * @throws \RuntimeException
     */
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Log a message.
     * Generic log routine that all severity levels use to log an event.
     *
     * @param string $level   Log level
     * @param string $message Content of log event.
     * @param array  $context Potentially multidimensional associative array of support data that goes with the log event.
     *
     * @throws \RuntimeException when log file cannot be opened for writing.
     */
    public function log($level, $message, array $context = array())
    {
        $level = strtolower($level);
             
        if ($this->logAtThisLevel($level)) 
        {
            // Build logline
            $pid                       = getmypid();
            list($exception, $context) = $this->handleException($context);
            $context                   = $context ? json_encode($context, \JSON_UNESCAPED_SLASHES) : '{}';
            $context                   = $context ?: '{}'; // Fail-safe incase json_encode fails.
            $logline                   = $this->formatLogLine($level, $pid, $message, $context, $exception);
            $logfile                   = sprintf($this->logfile, $level, date('Y-m-d'));
            
            // Log to file
            try {
                if (is_file($logfile)) {
                    $filesize = filesize($logfile);            
                    if ($filesize >= $this->threshold) {
                        $this->LoggerRotate($logfile);
                    }
                }

                file_put_contents($logfile, $logline, FILE_APPEND | LOCK_EX);
            } catch (\Throwable $e) {
                throw new \RuntimeException("Could not open log file {$logfile} for writing to SimpleLog channel {$this->channel}!", 0, $e);
            }
        }

        // Log to stdout if option set to do so.
        if ($this->stdout) {
            print($log_line);
        }
    }

    /**
     * Determine if the logger should log at a certain log level.
     *
     * @param  string $level
     *
     * @return bool True if we log at this level; false otherwise.
     */
    private function logAtThisLevel($level)
    {
        if (!array_key_exists($level, self::LEVELS)) {
          $level = self::LOG_LEVEL_NONE;
        }
        
        return self::LEVELS[$level] >= $this->loglevel;
    }

    /**
     * Handle an exception in the data context array.
     * If an exception is included in the data context array, extract it.
     *
     * @param  array  $context
     *
     * @return array  [exception, data (without exception)]
     */
    private function handleException(array $context = null)
    {
        $exception_data = '{}';
        if (array_key_exists('exception', $context)) {
            if ($context['exception'] instanceof \Throwable) {
                $exception      = $context['exception'];
                $exception_data = $this->buildExceptionData($exception);
            }
            unset($context['exception']);
        }
        
        return [$exception_data, $context];
    }

    /**
     * Build the exception log data.
     *
     * @param  \Throwable $e
     *
     * @return string JSON {message, code, file, line, trace}
     */
    private function buildExceptionData(\Throwable $e)
    {
        $exceptionData = json_encode(
            [
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTrace()
            ],
            \JSON_UNESCAPED_SLASHES
        );

        // Fail-safe in case json_encode failed
        return $exceptionData ?: '{"message":"' . $e->getMessage() . '"}';
    }

    /**
     * Format the log line.
     * YYYY-mm-dd HH:ii:ss  [loglevel]  [channel]  [pid:##]  Log message content  {"Optional":"JSON Contextual Support Data"}  {"Optional":"Exception Data"}
     *
     * @param  string $level
     * @param  int    $pid
     * @param  string $message
     * @param  string $context
     * @param  string $exception_data
     *
     * @return string
     */
    private function formatLogLine($level, $pid, $message, $context, $exception_data)
    {
        return
            "[{$this->getTime()}]"                        . self::TAB .
            "[$level]"                                    . self::TAB .
            "[{$this->channel}]"                          . self::TAB .
            "[pid:$pid]"                                  . self::TAB .
            str_replace(\PHP_EOL, '   ', trim($message))  . self::TAB .
            str_replace(\PHP_EOL, '   ', $context)        . self::TAB .
            str_replace(\PHP_EOL, '   ', $exception_data) . \PHP_EOL;
    }

    /**
     * Get current date time.
     * Format: YYYY-mm-dd HH:ii:ss
     *
     * @return string Date time
     */
    private function getTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Sets Logger Threshold
     *
     * @param int $treshold
     */
    public function setThreshold($treshold)
    {
        $this->threshold = $this->parseTreshold($treshold);
    }

    /**
     * Parse Logger Threshold
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
     * Rotate Logging File
     *
     * @param string $logfile  File name and path of log file.
     */
    private function LoggerRotate($logfile)
    {
        $counter = 0;
        foreach (new DirectoryIterator(dirname($logfile)) as $info) {
            if ($info->isDot() || !$info->isFile()) {
                continue;
            }            
            $fileinfo = pathinfo($info->getFilename());
            if ($fileinfo['filename'] == basename($logfile)) {
                if ($fileinfo['extension'] > $counter) {
                    $counter = $fileinfo['extension'];
                }
            }
        }
        $counter ++;
        
        // rotate
        rename($logfile, $logfile.'.'.$counter);
    }

}
?>