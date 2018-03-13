<?php

namespace EasyCredit\Log\Handler;

/**
 * Class AbstractHandler
 *
 * @package EasyCredit\Log\Handler
 */
abstract class AbstractHandler
{

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    abstract public function write($level, $message, array $context = array());
}
