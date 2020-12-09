<?php

namespace EasyCredit\Http\Adapter;

use EasyCredit\Http\Request;
use EasyCredit\Http\Response;
use EasyCredit\Log\LogLevel;

/**
 * Class Curl
 *
 * @package EasyCredit\Http\Adapter
 */
class Curl extends AbstractAdapter
{
    /**
     * @var resource
     */
    protected $curlResource;

    /**
     * @var array
     */
    protected $curlOpts = array();

    /**
     * Curl constructor.
     */
    public function __construct()
    {
        if ($this->isAvailable()) {
            $this->curlResource = curl_init();
        }
    }

    /**
     * @return boolean
     */
    public function isAvailable()
    {
        return function_exists('curl_version');
    }

    /**
     * @return Response
     */
    public function execute()
    {
        $this->prepareCurlOptions();

        $logMessage = "Request (URL: ".$this->getUrl()."): ".print_r($this->curlOpts, true);
        $this->log(LogLevel::DEBUG, $logMessage);

        $response = $this->curlExec();
        
        $responseInfo = $this->curlGetInfo();

        $httpCode = intval(isset($responseInfo['http_code']) ? $responseInfo['http_code'] : null);

        $logLevel = LogLevel::DEBUG;
        if ($httpCode === null || $httpCode != 200) {
            $logLevel = LogLevel::ERROR;
        }

        $logMessage = "Response (HTTP-Code: ".$httpCode."): ".print_r($response, true).print_r($responseInfo, true);
        $this->log($logLevel, $logMessage);

        $this->curlClose();

        return new Response($response, $httpCode, array('content-type' => $responseInfo['content_type']));
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setCurlOption($key, $value)
    {
        $this->curlOpts[$key] = $value;
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        $this->curlClose();
    }

    /**
     * Prepare Curl Options
     */
    protected function prepareCurlOptions()
    {
        $this->curlOpts = array();
        $this->setCurlOption(CURLOPT_RETURNTRANSFER, true);
        $this->setCurlOption(CURLOPT_URL, $this->getUrl());
        $this->setCurlOption(CURLOPT_CUSTOMREQUEST, $this->getMethod());

        if ($this->getMethod() !== Request::METHOD_GET) {
            $postFields = ($this->getBody() ? json_encode($this->getBody()) : http_build_query($this->getParameters()));
            $this->setCurlOption(CURLOPT_POSTFIELDS, $postFields);
        }

        $this->prepareProxyRequest();

        $this->setCurlOption(CURLOPT_HTTPHEADER, $this->getHeaders());
    }

    /**
     * Prepare Curl Options for Proxy Connection
     */
    protected function prepareProxyRequest()
    {
        if ($this->isProxyEnabled()) {
            $this->setCurlOption(CURLOPT_HTTPPROXYTUNNEL, 1);
            $this->setCurlOption(CURLOPT_PROXY, $this->getProxyHost().':'.$this->getProxyPort());
            if ($this->getProxyUsername() && $this->getProxyPassword()) {
                $this->setCurlOption(CURLOPT_PROXYUSERPWD, $this->getProxyPort().':'.$this->getProxyPassword());
            }
        }
    }

    /**
     * @return string
     */
    protected function curlExec()
    {
        curl_setopt_array($this->curlResource, $this->curlOpts);

        return curl_exec($this->curlResource);
    }

    /**
     * @return array
     */
    protected function curlGetInfo()
    {
        return curl_getinfo($this->curlResource);
    }

    /**
     * Close the Curl Resource and create a new one
     */
    protected function curlClose()
    {
        if (is_object($this->curlResource) && stripos(get_class($this->curlResource), 'curl') !== false) {
            curl_close($this->curlResource);
            $this->curlResource = curl_init();
        }
    }
}
