<?php

namespace EasyCredit\Http\Adapter;

use EasyCredit\Http\Request;
use EasyCredit\Http\Response;
use EasyCredit\Log\LogLevel;

/**
 * Class Socket
 *
 * @package EasyCredit\Http\Adapter
 */
class Socket extends AbstractAdapter
{

    /**
     * @return boolean
     */
    public function isAvailable()
    {
        return function_exists('stream_socket_client');
    }

    /**
     * @return string
     */
    public function prepareRequest()
    {
        $path = $this->preparePath();

        $this->addHeader('Connection: close');

        if ($this->getBody() !== null) {
            $this->setBody(json_encode($this->getBody()));
            $this->addHeader('Content-Length: '.strlen($this->getBody()));
        }

        $request = $this->getMethod().' /'.$path.' HTTP/1.1'."\r\n";
        $request .= 'Host: '.$this->getHost()."\r\n";

        if ($this->isProxyEnabled()) {
            $request = $this->prepareProxyRequest();
        }

        $request .= implode("\r\n", $this->getHeaders())."\r\n\r\n";
        $request .= ($this->getBody() !== null ? $this->getBody()."\r\n" : '');

        return $request;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return ($this->isSsl() ? 'ssl' : 'tcp').'://'.$this->getHost().':'.($this->isSsl() ? 443 : 80);
    }

    /**
     * @throws \Exception
     *
     * @return resource
     */
    public function createSocketClient()
    {
        $context = stream_context_create($this->getContextOptions());

        $socket = stream_socket_client($this->getUrl(), $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

        if ($socket === false) {
            $this->log(LogLevel::ERROR, "Request Failed: ".print_r($errno, true).print_r($errstr, true));
            throw new \Exception("Couldn't connect to server");
        }

        return $socket;
    }

    /**
     * @return Response
     */
    public function execute()
    {
        $socket = $this->createSocketClient();

        $request = $this->prepareRequest();

        $logMessage = "Request (URL: ".$this->getUrl()."):".print_r($request, true);
        $this->log(LogLevel::DEBUG, $logMessage);

        fwrite($socket, $request);

        list($httpCode, $body, $headers) = $this->parseResponse($socket);

        fclose($socket);

        $logLevel = LogLevel::DEBUG;
        if ($httpCode === null || $httpCode != 200) {
            $logLevel = LogLevel::ERROR;
        }

        $this->log($logLevel, "Response (HTTP-Code: ".$httpCode."): ".print_r($body, true).print_r($headers, true));

        return new Response($body, $httpCode, $headers);
    }

    /**
     * @return array
     */
    public function getContextOptions()
    {
        $contextOptions = array(
            'http' => array(
                'method' => $this->getMethod(),
                'request_fulluri' => true,
                'timeout' => 10,
            ),
        );

        return $contextOptions;
    }

    /**
     * @param resource $socket
     *
     * @return array
     */
    public function parseResponse($socket)
    {
        rewind($socket);
        $headers = array();
        $body = '';
        $processHeaders = true;
        while (!feof($socket)) {
            $line = fgets($socket);
            if ($line === "\r\n") {
                $processHeaders = false;
            } elseif ($processHeaders) {
                $headers[] = $line;
            } else {
                $body .= $line;
            }
        }

        $statusCode = $this->parseStatusCode($headers);
        $headers = $this->parseHeaders($headers);
        $body = $this->decodeBody($headers, $body);

        return array($statusCode, $body, $headers);
    }

    /**
     * @param array $metadata
     *
     * @return int|null
     */
    public function parseStatusCode($metadata)
    {
        $parts = explode(' ', $metadata[0], 2);

        if (!isset($parts[1])) {
            return null;
        }

        return (integer) $parts[1];
    }

    /**
     * @param array $metadata
     *
     * @return array
     */
    public function parseHeaders($metadata)
    {
        $headers = array();
        foreach ($metadata as $meta) {
            $meta = explode(':', $meta, 2);
            if (count($meta) === 2) {
                $headers[strtolower($meta[0])] = trim($meta[1]);
            }
        }

        return $headers;
    }

    /**
     * @return string
     */
    protected function preparePath()
    {
        return $this->getPath().(!$this->getParameters() ? '' : '?'.http_build_query($this->getParameters()));
    }

    /**
     * @return string
     */
    protected function prepareProxyRequest()
    {
        $url = $this->getHost().':'.($this->isSsl() ? 443 : 80).'/'.$this->preparePath();
        $request = $this->getMethod().' '.$url.' HTTP/1.1'."\r\n";
        $request .= 'Host: '.$this->getProxyHost().':'.$this->getProxyPort()."\r\n";

        return $request;
    }

    /**
     * @param array  $headers
     * @param string $body
     *
     * @return string
     */
    protected function decodeBody(array $headers, $body)
    {
        $headers = array_change_key_case($headers);
        if (isset($headers['transfer-encoding']) && $headers['transfer-encoding'] === 'chunked') {
            for ($decodedBody = ''; !empty($body); $body = trim($body)) {
                $pos = strpos($body, "\r\n");
                $length = hexdec(substr($body, 0, $pos));
                $decodedBody .= substr($body, $pos + 2, $length);
                $body = substr($body, $pos + $length + 2);
            }

            return $decodedBody;
        }

        return $body;
    }
}
