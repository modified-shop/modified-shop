<?php
/**
 * WebshopApi
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 *
 * Transaction-V3 API Definition
 * @author   NETZKOLLEKTIV GmbH
 * @link     https://netzkollektiv.com

 */

namespace Teambank\EasyCreditApiV3\Service;

use Psr\Http\Client\ClientInterface;

use Teambank\EasyCreditApiV3\ApiException;
use Teambank\EasyCreditApiV3\Configuration;
use Teambank\EasyCreditApiV3\HeaderSelector;
use Teambank\EasyCreditApiV3\ObjectSerializer;
use Teambank\EasyCreditApiV3\Client;
use GuzzleHttp\Psr7\Request;

/**
 * WebshopApi Class
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 */
class WebshopApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @var int Host index
     */
    protected $hostIndex;

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $hostIndex (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(
        ClientInterface $client = null,
        Configuration $config = null,
        HeaderSelector $selector = null,
        $hostIndex = 0
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $hostIndex;
    }

    /**
     * Set the host index
     *
     * @param int $hostIndex Host index (required)
     */
    public function setHostIndex($hostIndex): void
    {
        $this->hostIndex = $hostIndex;
    }

    /**
     * Get the host index
     *
     * @return int Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation apiPaymentV3WebshopGet
     *
     * Get the necessary information about the webshop
     *
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Teambank\EasyCreditApiV3\Model\WebshopResponse|\Teambank\EasyCreditApiV3\Model\AuthenticationError|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation
     */
    public function apiPaymentV3WebshopGet()
    {
        list($response) = $this->apiPaymentV3WebshopGetWithHttpInfo();
        return $response;
    }

    /**
     * Operation apiPaymentV3WebshopGetWithHttpInfo
     *
     * Get the necessary information about the webshop
     *
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \Teambank\EasyCreditApiV3\Model\WebshopResponse|\Teambank\EasyCreditApiV3\Model\AuthenticationError|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation, HTTP status code, HTTP response headers (array of strings)
     */
    public function apiPaymentV3WebshopGetWithHttpInfo()
    {
        $request = $this->apiPaymentV3WebshopGetRequest();

        try {
            // $options = $this->createHttpClientOption();
            try {
                $response = $this->client->sendRequest($request);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 200:
                    if ('\Teambank\EasyCreditApiV3\Model\WebshopResponse' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\WebshopResponse', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 401:
                    if ('\Teambank\EasyCreditApiV3\Model\AuthenticationError' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\AuthenticationError', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 403:
                    if ('\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = '\Teambank\EasyCreditApiV3\Model\WebshopResponse';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\WebshopResponse',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\AuthenticationError',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Create request for operation 'apiPaymentV3WebshopGet'
     *
     *
     * @throws \InvalidArgumentException
     * @return Request
     */
    public function apiPaymentV3WebshopGetRequest()
    {

        $resourcePath = '/api/payment/v3/webshop';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;




        /*
        */

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json', 'application/problem+json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json', 'application/problem+json'],
                []
            );
        }

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \http_build_query($formParams);
            }
        }

        // this endpoint requires HTTP basic authentication
        if (!empty($this->config->getUsername()) || !(empty($this->config->getPassword()))) {
            $headers['Authorization'] = 'Basic ' . base64_encode($this->config->getUsername() . ":" . $this->config->getPassword());
        }
        if (!empty($this->config->getAccessToken())) {
            $headers['Content-signature'] = 'hmacsha256=' . hash_hmac('sha256', $httpBody, $this->config->getAccessToken());
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = http_build_query($queryParams);
        return new Request(
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation apiPaymentV3WebshopIntegrationcheckPost
     *
     * Verifies the correctness of the merchant&#39;s authentication credentials and, if enabled, the body signature
     *
     * @param  \Teambank\EasyCreditApiV3\Model\IntegrationCheckRequest $integrationCheckRequest integration check request (optional)
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Teambank\EasyCreditApiV3\Model\IntegrationCheckResponse|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation|\Teambank\EasyCreditApiV3\Model\AuthenticationError|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation
     */
    public function apiPaymentV3WebshopIntegrationcheckPost($integrationCheckRequest = null)
    {
        list($response) = $this->apiPaymentV3WebshopIntegrationcheckPostWithHttpInfo($integrationCheckRequest);
        return $response;
    }

    /**
     * Operation apiPaymentV3WebshopIntegrationcheckPostWithHttpInfo
     *
     * Verifies the correctness of the merchant&#39;s authentication credentials and, if enabled, the body signature
     *
     * @param  \Teambank\EasyCreditApiV3\Model\IntegrationCheckRequest $integrationCheckRequest integration check request (optional)
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \Teambank\EasyCreditApiV3\Model\IntegrationCheckResponse|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation|\Teambank\EasyCreditApiV3\Model\AuthenticationError|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation, HTTP status code, HTTP response headers (array of strings)
     */
    public function apiPaymentV3WebshopIntegrationcheckPostWithHttpInfo($integrationCheckRequest = null)
    {
        $request = $this->apiPaymentV3WebshopIntegrationcheckPostRequest($integrationCheckRequest);

        try {
            // $options = $this->createHttpClientOption();
            try {
                $response = $this->client->sendRequest($request);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 200:
                    if ('\Teambank\EasyCreditApiV3\Model\IntegrationCheckResponse' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\IntegrationCheckResponse', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 400:
                    if ('\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 401:
                    if ('\Teambank\EasyCreditApiV3\Model\AuthenticationError' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\AuthenticationError', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 403:
                    if ('\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = '\Teambank\EasyCreditApiV3\Model\IntegrationCheckResponse';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\IntegrationCheckResponse',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 401:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\AuthenticationError',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 403:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Create request for operation 'apiPaymentV3WebshopIntegrationcheckPost'
     *
     * @param  \Teambank\EasyCreditApiV3\Model\IntegrationCheckRequest $integrationCheckRequest integration check request (optional)
     *
     * @throws \InvalidArgumentException
     * @return Request
     */
    public function apiPaymentV3WebshopIntegrationcheckPostRequest($integrationCheckRequest = null)
    {

        $resourcePath = '/api/payment/v3/webshop/integrationcheck';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;




        /*
        */

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/problem+json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/problem+json'],
                ['application/json']
            );
        }

        // for model (json/xml)
        if (isset($integrationCheckRequest)) {
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = \json_encode(ObjectSerializer::sanitizeForSerialization($integrationCheckRequest));
            } else {
                $httpBody = $integrationCheckRequest;
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \http_build_query($formParams);
            }
        }

        // this endpoint requires HTTP basic authentication
        if (!empty($this->config->getUsername()) || !(empty($this->config->getPassword()))) {
            $headers['Authorization'] = 'Basic ' . base64_encode($this->config->getUsername() . ":" . $this->config->getPassword());
        }
        if (!empty($this->config->getAccessToken())) {
            $headers['Content-signature'] = 'hmacsha256=' . hash_hmac('sha256', $httpBody, $this->config->getAccessToken());
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = http_build_query($queryParams);
        return new Request(
            'POST',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation apiPaymentV3WebshopWebshopIdGet
     *
     * Get the necessary information about the webshop
     *
     * @param  string $webshopId Identifier of a webshop (required)
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Teambank\EasyCreditApiV3\Model\WebshopResponse|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation
     */
    public function apiPaymentV3WebshopWebshopIdGet($webshopId)
    {
        list($response) = $this->apiPaymentV3WebshopWebshopIdGetWithHttpInfo($webshopId);
        return $response;
    }

    /**
     * Operation apiPaymentV3WebshopWebshopIdGetWithHttpInfo
     *
     * Get the necessary information about the webshop
     *
     * @param  string $webshopId Identifier of a webshop (required)
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \Teambank\EasyCreditApiV3\Model\WebshopResponse|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation, HTTP status code, HTTP response headers (array of strings)
     */
    public function apiPaymentV3WebshopWebshopIdGetWithHttpInfo($webshopId)
    {
        $request = $this->apiPaymentV3WebshopWebshopIdGetRequest($webshopId);

        try {
            // $options = $this->createHttpClientOption();
            try {
                $response = $this->client->sendRequest($request);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            switch($statusCode) {
                case 200:
                    if ('\Teambank\EasyCreditApiV3\Model\WebshopResponse' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\WebshopResponse', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 404:
                    if ('\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
            }

            $returnType = '\Teambank\EasyCreditApiV3\Model\WebshopResponse';
            if ($returnType === '\SplFileObject') {
                $content = $response->getBody(); //stream goes to serializer
            } else {
                $content = (string) $response->getBody();
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\WebshopResponse',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Create request for operation 'apiPaymentV3WebshopWebshopIdGet'
     *
     * @param  string $webshopId Identifier of a webshop (required)
     *
     * @throws \InvalidArgumentException
     * @return Request
     */
    public function apiPaymentV3WebshopWebshopIdGetRequest($webshopId)
    {
        // verify the required parameter 'webshopId' is set
        if ($webshopId === null || (is_array($webshopId) && count($webshopId) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $webshopId when calling apiPaymentV3WebshopWebshopIdGet'
            );
        }

        $resourcePath = '/api/payment/v3/webshop/{webshopId}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($webshopId !== null) {
            $resourcePath = str_replace(
                '{' . 'webshopId' . '}',
                ObjectSerializer::toPathValue($webshopId),
                $resourcePath
            );
        }

        /*
        */

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json', 'application/problem+json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json', 'application/problem+json'],
                []
            );
        }

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \http_build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = http_build_query($queryParams);
        return new Request(
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }
}
