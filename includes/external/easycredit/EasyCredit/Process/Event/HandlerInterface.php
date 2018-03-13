<?php

namespace EasyCredit\Process\Event;

use EasyCredit\Process\Process;

/**
 * Interface HandlerInterface
 *
 * @package EasyCredit\Process\Event
 */
interface HandlerInterface
{
    /**
     * @param string $event
     * @param Process $process
     * @param array $payload
     */
    public function handleEvent($event, Process $process, $payload = array());
}
