<?php
/**
 * DocumentApi
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
 * DocumentApi Class
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 */
class DocumentApi
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
     * Operation apiMerchantV3DocumentsGet
     *
     * Download billing documents of a merchant.
     *
     * @param  \DateTime $billingDateFrom set by default to the last month if not specified (optional)
     * @param  \DateTime $billingDateTo set by default to billingDateFrom + one month if not specified (optional)
     * @param  string[] $documentType set by default to all options if not specified (optional)
     * @param  string[] $fileType set by default to all options if not specified (optional)
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \SplFileObject|\Teambank\EasyCreditApiV3\Model\ConstraintViolation|\Teambank\EasyCreditApiV3\Model\AuthenticationError|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation
     */
    public function apiMerchantV3DocumentsGet($billingDateFrom = null, $billingDateTo = null, $documentType = null, $fileType = null)
    {
        list($response) = $this->apiMerchantV3DocumentsGetWithHttpInfo($billingDateFrom, $billingDateTo, $documentType, $fileType);
        return $response;
    }

    /**
     * Operation apiMerchantV3DocumentsGetWithHttpInfo
     *
     * Download billing documents of a merchant.
     *
     * @param  \DateTime $billingDateFrom set by default to the last month if not specified (optional)
     * @param  \DateTime $billingDateTo set by default to billingDateFrom + one month if not specified (optional)
     * @param  string[] $documentType set by default to all options if not specified (optional)
     * @param  string[] $fileType set by default to all options if not specified (optional)
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \SplFileObject|\Teambank\EasyCreditApiV3\Model\ConstraintViolation|\Teambank\EasyCreditApiV3\Model\AuthenticationError|\Teambank\EasyCreditApiV3\Model\PaymentConstraintViolation, HTTP status code, HTTP response headers (array of strings)
     */
    public function apiMerchantV3DocumentsGetWithHttpInfo($billingDateFrom = null, $billingDateTo = null, $documentType = null, $fileType = null)
    {
        $request = $this->apiMerchantV3DocumentsGetRequest($billingDateFrom, $billingDateTo, $documentType, $fileType);

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
                    if ('\SplFileObject' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\SplFileObject', []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                case 400:
                    if ('\Teambank\EasyCreditApiV3\Model\ConstraintViolation' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\ConstraintViolation', []),
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

            $returnType = '\SplFileObject';
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
                        '\SplFileObject',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\ConstraintViolation',
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
     * Create request for operation 'apiMerchantV3DocumentsGet'
     *
     * @param  \DateTime $billingDateFrom set by default to the last month if not specified (optional)
     * @param  \DateTime $billingDateTo set by default to billingDateFrom + one month if not specified (optional)
     * @param  string[] $documentType set by default to all options if not specified (optional)
     * @param  string[] $fileType set by default to all options if not specified (optional)
     *
     * @throws \InvalidArgumentException
     * @return Request
     */
    public function apiMerchantV3DocumentsGetRequest($billingDateFrom = null, $billingDateTo = null, $documentType = null, $fileType = null)
    {

        $resourcePath = '/api/merchant/v3/documents';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if ($billingDateFrom !== null) {
            if('form' === 'form' && is_array($billingDateFrom)) {
                foreach($billingDateFrom as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['billingDateFrom'] = $billingDateFrom;
            }
        }
        // query params
        if ($billingDateTo !== null) {
            if('form' === 'form' && is_array($billingDateTo)) {
                foreach($billingDateTo as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['billingDateTo'] = $billingDateTo;
            }
        }
        // query params
        if ($documentType !== null) {
            if('form' === 'form' && is_array($documentType)) {
                foreach($documentType as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['documentType'] = $documentType;
            }
        }
        // query params
        if ($fileType !== null) {
            if('form' === 'form' && is_array($fileType)) {
                foreach($fileType as $key => $value) {
                    $queryParams[$key] = $value;
                }
            }
            else {
                $queryParams['fileType'] = $fileType;
            }
        }



        /*
        */

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/zip', 'application/problem+json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/zip', 'application/problem+json'],
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
