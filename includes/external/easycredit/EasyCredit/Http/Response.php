<?php

namespace EasyCredit\Http;

/**
 * Class Response
 *
 * @package EasyCredit\Http
 */
class Response
{

    /**
     * @var mixed
     */
    protected $body;

    /**
     * @var string
     */
    protected $bodyRaw;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var integer|null
     */
    protected $statusCode;

    /**
     * Response constructor.
     *
     * @param string       $body
     * @param integer|null $statusCode
     * @param array        $headers
     */
    public function __construct($body, $statusCode, array $headers)
    {
        $this->bodyRaw = $body;
        $this->headers = $headers;
        $this->statusCode = $statusCode;

        $this->extractHeaders();
        $this->parseBody();
    }

    /**
     * @return mixed
     */
    public function parseBody()
    {
        $body = $this->bodyRaw;
        if ($this->contentType === 'application/json') {
            $body = json_decode($body, true);
        }

        $this->body = $body;
    }

    /**
     * @return void
     */
    public function extractHeaders()
    {
        $this->contentType = $this->getHeader('content-type');
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * 
     * @return integer|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
