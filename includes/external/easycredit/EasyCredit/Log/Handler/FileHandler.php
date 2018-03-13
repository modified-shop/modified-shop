<?php

namespace EasyCredit\Log\Handler;

/**
 * Class FileHandler
 *
 * @package EasyCredit\Log\Handler
 */
class FileHandler extends AbstractHandler
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * FileHandler constructor.
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function write($level, $message, array $context = array())
    {
        $logMessageFormat = "%s [%s]: %s Context: %s\n";
        $logMessage = sprintf($logMessageFormat, date('Y-m-d H:i:s'), $level, $message, implode("\n", $context));

        file_put_contents($this->filename, $logMessage, FILE_APPEND);
    }
}
