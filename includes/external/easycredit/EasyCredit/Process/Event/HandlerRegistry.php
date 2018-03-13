<?php

namespace EasyCredit\Process\Event;

use EasyCredit\Process\Process;

/**
 * Class HandlerRegistry
 *
 * @package EasyCredit\Process\Event
 */
class HandlerRegistry
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * List of event handlers
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * EventHandlerRegistry constructor
     *
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * Adds an event handler to a specific event
     *
     * @param string $event
     * @param HandlerInterface $handler
     */
    public function addHandler($event, HandlerInterface $handler)
    {
        if (!array_key_exists($event, $this->handlers)) {
            $this->handlers[$event] = array();
        }

        $this->handlers[$event][] = $handler;
    }

    /**
     * Fires an event.
     *
     * @param string $event
     * @param array $payload
     */
    public function fire($event)
    {
        if (!array_key_exists($event, $this->handlers)) {
            return;
        }

        foreach ($this->handlers[$event] as $handler) {
            $handler->handleEvent($event, $this->process);
        }
    }
}
