<?php

namespace EasyCredit\Api\Request;

use EasyCredit\Config;

/**
 * Class AbstractRequest
 *
 * @package EasyCredit\Api\Request
 */
abstract class AbstractRequest
{

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var mixed
     */
    protected $body;

    /**
     * @var array
     */
    protected $headers = array('Accept: application/json');

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Config::EASYCREDIT_API_ROOT_URI.$this->path;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return null|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
