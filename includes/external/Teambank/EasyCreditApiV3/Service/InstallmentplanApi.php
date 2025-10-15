<?php
/**
 * InstallmentplanApi
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
 * InstallmentplanApi Class
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 */
class InstallmentplanApi
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
     * Operation apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost
     *
     * Calculates the installmentplan
     *
     * @param  string $shopIdentifier Shop Identifier (required)
     * @param  \Teambank\EasyCreditApiV3\Model\InstallmentPlanRequest $installmentPlanRequest integration check request (optional)
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Teambank\EasyCreditApiV3\Model\InstallmentPlanResponse|\Teambank\EasyCreditApiV3\Model\ConstraintViolation|\Teambank\EasyCreditApiV3\Model\ConstraintViolation
     */
    public function apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost($shopIdentifier, $installmentPlanRequest = null)
    {
        list($response) = $this->apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPostWithHttpInfo($shopIdentifier, $installmentPlanRequest);
        return $response;
    }

    /**
     * Operation apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPostWithHttpInfo
     *
     * Calculates the installmentplan
     *
     * @param  string $shopIdentifier Shop Identifier (required)
     * @param  \Teambank\EasyCreditApiV3\Model\InstallmentPlanRequest $installmentPlanRequest integration check request (optional)
     *
     * @throws \Teambank\EasyCreditApiV3\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \Teambank\EasyCreditApiV3\Model\InstallmentPlanResponse|\Teambank\EasyCreditApiV3\Model\ConstraintViolation|\Teambank\EasyCreditApiV3\Model\ConstraintViolation, HTTP status code, HTTP response headers (array of strings)
     */
    public function apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPostWithHttpInfo($shopIdentifier, $installmentPlanRequest = null)
    {
        $request = $this->apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPostRequest($shopIdentifier, $installmentPlanRequest);

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
                    if ('\Teambank\EasyCreditApiV3\Model\InstallmentPlanResponse' === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                    }

                    return [
                        ObjectSerializer::deserialize($content, '\Teambank\EasyCreditApiV3\Model\InstallmentPlanResponse', []),
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
                case 404:
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
            }

            $returnType = '\Teambank\EasyCreditApiV3\Model\InstallmentPlanResponse';
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
                        '\Teambank\EasyCreditApiV3\Model\InstallmentPlanResponse',
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
                case 404:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Teambank\EasyCreditApiV3\Model\ConstraintViolation',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Create request for operation 'apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost'
     *
     * @param  string $shopIdentifier Shop Identifier (required)
     * @param  \Teambank\EasyCreditApiV3\Model\InstallmentPlanRequest $installmentPlanRequest integration check request (optional)
     *
     * @throws \InvalidArgumentException
     * @return Request
     */
    public function apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPostRequest($shopIdentifier, $installmentPlanRequest = null)
    {
        // verify the required parameter 'shopIdentifier' is set
        if ($shopIdentifier === null || (is_array($shopIdentifier) && count($shopIdentifier) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $shopIdentifier when calling apiRatenrechnerV3WebshopShopIdentifierInstallmentplansPost'
            );
        }

        $resourcePath = '/api/ratenrechner/v3/webshop/{shopIdentifier}/installmentplans';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;



        // path params
        if ($shopIdentifier !== null) {
            $resourcePath = str_replace(
                '{' . 'shopIdentifier' . '}',
                ObjectSerializer::toPathValue($shopIdentifier),
                $resourcePath
            );
        }

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
        if (isset($installmentPlanRequest)) {
            if ($headers['Content-Type'] === 'application/json') {
                $httpBody = \json_encode(ObjectSerializer::sanitizeForSerialization($installmentPlanRequest));
            } else {
                $httpBody = $installmentPlanRequest;
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
