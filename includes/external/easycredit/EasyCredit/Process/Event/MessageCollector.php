<?php

namespace EasyCredit\Process\Event;

/**
 * Class MessageCollector
 *
 * @package EasyCredit\Process\Event
 */
class MessageCollector
{
    /**
     * array of collected messages (strings)
     *
     * @var array
     */
    protected $messages;

    /**
     * Returns all messages collected by this validator
     * Messages are stored as key-value pairs.
     * If no key has been used, expect a numerical one.
     *
     * @return array
     */
    public function getMessages()
    {
        if (! is_array($this->messages)) {
            $this->messages = array();
        }
        return $this->messages;
    }

    /**
     * Adds a new message to the message list
     * including a key for translations or further automatic processing.
     *
     * @param string $message
     * @param string|null $key
     *
     */
    public function addMessage($message, $key = null)
    {
        if (! is_array($this->messages)) {
            $this->messages = array();
        }

        if ($key !== null) {
            $this->messages[$key] = $message;
        } else {
            $this->messages[] = $message;
        }
    }

    /**
     * Adds multiple messages to the message list
     *
     * @param array $messages
     */
    public function addMessages(array $messages)
    {
        if (! is_array($this->messages)) {
            $this->messages = array();
        }
        $this->messages = array_merge($this->messages, $messages);
    }

    /**
     * Clears the message list
     */
    public function clearMessages()
    {
        $this->messages = array();
    }
}
