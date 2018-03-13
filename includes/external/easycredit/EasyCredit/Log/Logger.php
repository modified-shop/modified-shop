<?php

namespace EasyCredit\Log;

/**
 * Class Logger
 *
 * @package EasyCredit\Log
 */
class Logger
{
    /**
     * @var Handler\AbstractHandler
     */
    protected $handler;

    /**
     * @var string
     */
    protected $logLevel;


    /**
     * Logger constructor.
     *
     * @param Handler\AbstractHandler $handler
     * @param string                  $logLevel
     */
    public function __construct(Handler\AbstractHandler $handler, $logLevel = LogLevel::ERROR)
    {
        $this->handler = $handler;
        $this->logLevel = $logLevel;
    }

    /**
     * Log a Message
     *
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->logLevel === LogLevel::ERROR && $level === LogLevel::DEBUG) {
            return;
        }

        $this->handler->write($level, $message, $context);
    }

    /**
     * Log Error Message
     *
     * @param string $message
     * @param array  $context
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Log Debug Message
     *
     * @param string $message
     * @param array  $context
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
