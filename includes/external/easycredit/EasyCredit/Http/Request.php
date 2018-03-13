<?php

namespace EasyCredit\Http;

use EasyCredit\Installment\Request\RequestInterface;

/**
 * Class Request
 *
 * @package EasyCredit\Http
 */
class Request
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PUT = 'PUT';

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var Adapter\AbstractAdapter
     */
    protected $adapter;

    /**
     * @var bool
     */
    protected $ssl = true;

    /**
     * Request constructor.
     *
     * @param string                  $host
     * @param integer                 $port
     * @param Adapter\AbstractAdapter $adapter
     */
    public function __construct($host, $port, Adapter\AbstractAdapter $adapter)
    {
        $this->host = $host;
        $this->port = $port;
        $this->adapter = $adapter->setHost($this->host)->setPort($this->port);
    }

    /**
     * @return Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param string      $path
     * @param array       $parameters
     * @param string|null $body
     * @param array       $headers
     *
     * @return Response
     */
    public function get($path, $parameters = array(), $body = null, $headers = array())
    {
        return $this->getAdapter()
            ->get($path)
            ->setHeaders($headers)
            ->setParameters($parameters)
            ->setBody($body)
            ->execute();
    }

    /**
     * @param string      $path
     * @param array       $parameters
     * @param string|null $body
     * @param array       $headers
     *
     * @return Response
     */
    public function post($path, $parameters = array(), $body = null, $headers = array())
    {
        return $this->getAdapter()
            ->post($path)
            ->setHeaders($headers)
            ->setParameters($parameters)
            ->setBody($body)
            ->execute();
    }

    /**
     * @param string      $path
     * @param array       $parameters
     * @param string|null $body
     * @param array       $headers
     *
     * @return Response
     */
    public function put($path, $parameters = array(), $body = null, $headers = array())
    {
        return $this->getAdapter()
            ->put($path)
            ->setHeaders($headers)
            ->setParameters($parameters)
            ->setBody($body)
            ->execute();
    }

    /**
     * @param string      $path
     * @param array       $parameters
     * @param string|null $body
     * @param array       $headers
     *
     * @return Response
     */
    public function delete($path, $parameters = array(), $body = null, $headers = array())
    {
        return $this->getAdapter()
            ->delete($path)
            ->setHeaders($headers)
            ->setParameters($parameters)
            ->setBody($body)
            ->execute();
    }

    /**
     * @param RequestInterface $request
     *
     * @return Response
     */
    public function doRequest($request)
    {
        $method = strtolower($request->getMethod());

        $response = $this->$method($request->getPath(), $request->getParameters(), $request->getBody(),
            $request->getHeaders());

        return $response;
    }
}
