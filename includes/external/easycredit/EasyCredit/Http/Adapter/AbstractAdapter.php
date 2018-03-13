<?php

namespace EasyCredit\Http\Adapter;

use EasyCredit\Http\Request;
use EasyCredit\Http\Response;
use EasyCredit\Log\Logger;

/**
 * Class AbstractAdapter
 *
 * @package EasyCredit\Http\Adapter
 */
abstract class AbstractAdapter
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var integer
     */
    protected $port;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * @var string|null
     */
    protected $body;

    /**
     * @var bool
     */
    protected $ssl;

    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $proxyHost;

    /**
     * @var string
     */
    protected $proxyPort;

    /**
     * @var string
     */
    protected $proxyUsername;

    /**
     * @var string
     */
    protected $proxyPassword;

    /**
     * @var boolean
     */
    protected $proxyEnabled;

    /**
     * @return Response
     */
    abstract public function execute();

    /**
     * @return boolean
     */
    abstract public function isAvailable();

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function get($path)
    {
        $this->path = $path;
        $this->method = Request::METHOD_GET;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function post($path)
    {
        $this->path = $path;
        $this->method = Request::METHOD_POST;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function put($path)
    {
        $this->path = $path;
        $this->method = Request::METHOD_PUT;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function delete($path)
    {
        $this->path = $path;
        $this->method = Request::METHOD_DELETE;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string  $header
     * @param boolean $prepend
     */
    public function addHeader($header, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->headers, $header);
        } else {
            $this->headers[] = $header;
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $format = "%s://%s%s/%s%s";
        $scheme = 'http';

        if ($this->isSsl()) {
            $scheme = 'https';
        }
        $query = null;

        if ($this->method === Request::METHOD_GET) {
            $query = '?'.http_build_query($this->getParameters());
        }

        $port = ':'.$this->getPort();
        if (in_array($this->getPort(), array(443, 80, null))) {
            $port = '';
        }

        return sprintf($format, $scheme, $this->getHost(), $port, $this->getPath(), $query);
    }

    /**
     * @return bool
     */
    public function isSsl()
    {
        if ($this->ssl || $this->port === 443) {
            return $this->ssl = true;
        }

        return $this->ssl;
    }

    /**
     * @param boolean $ssl
     *
     * @return $this
     */
    public function setSsl($ssl)
    {
        $this->ssl = $ssl;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param integer $port
     *
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getProxyHost()
    {
        return $this->proxyHost;
    }

    /**
     * @param string $proxyHost
     */
    public function setProxyHost($proxyHost)
    {
        $this->proxyHost = $proxyHost;
    }

    /**
     * @return string
     */
    public function getProxyPort()
    {
        return $this->proxyPort;
    }

    /**
     * @param string $proxyPort
     */
    public function setProxyPort($proxyPort)
    {
        $this->proxyPort = $proxyPort;
    }

    /**
     * @return string
     */
    public function getProxyUsername()
    {
        return $this->proxyUsername;
    }

    /**
     * @param string $proxyUsername
     */
    public function setProxyUsername($proxyUsername)
    {
        $this->proxyUsername = $proxyUsername;
    }

    /**
     * @return string
     */
    public function getProxyPassword()
    {
        return $this->proxyPassword;
    }

    /**
     * @param string $proxyPassword
     */
    public function setProxyPassword($proxyPassword)
    {
        $this->proxyPassword = $proxyPassword;
    }

    /**
     * @return boolean
     */
    public function isProxyEnabled()
    {
        return $this->proxyEnabled;
    }

    /**
     * @param boolean $proxyEnabled
     */
    public function setProxyEnabled($proxyEnabled)
    {
        $this->proxyEnabled = $proxyEnabled;
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    protected function log($level, $message, $context = array())
    {
        if (!$this->logger) {
            return;
        }
        $this->logger->log($level, $message, $context);
    }
}
