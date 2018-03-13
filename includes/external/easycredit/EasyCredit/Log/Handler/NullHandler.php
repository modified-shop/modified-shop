<?php

namespace EasyCredit\Log\Handler;

/**
 * Class NullHandler
 *
 * @package EasyCredit\Log\Handler
 */
class NullHandler extends AbstractHandler
{

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function write($level, $message, array $context = array())
    {
        // No, I will do nothing
    }
}
