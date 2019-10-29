<?php
/**
 * API handler for OnboardingHandler REST API calls
 */

namespace PayPal\Handler;

use PayPal\Common\PayPalUserAgent;
use PayPal\Core\PayPalConstants;
use PayPal\Core\PayPalHttpConfig;
use PayPal\Exception\PayPalConfigurationException;
use PayPal\Exception\PayPalInvalidCredentialException;
use PayPal\Exception\PayPalMissingCredentialException;

/**
 * Class OnboardingHandler
 */
class OnboardingHandler implements IPayPalHandler
{
    /**
     * Private Variable
     *
     * @var \Paypal\Rest\ApiContext $apiContext
     */
    private $apiContext;

    /**
     * Construct
     *
     * @param \Paypal\Rest\ApiContext $apiContext
     */
    public function __construct($apiContext)
    {
        $this->apiContext = $apiContext;
    }

    /**
     * @param PayPalHttpConfig $httpConfig
     * @param string                    $request
     * @param mixed                     $options
     * @return mixed|void
     * @throws PayPalConfigurationException
     * @throws PayPalInvalidCredentialException
     * @throws PayPalMissingCredentialException
     */
    public function handle($httpConfig, $request, $options)
    {
        $config = $this->apiContext->getConfig();

        $httpConfig->setUrl(
            rtrim(trim($this->_getEndpoint($config)), '/') .
            (isset($options['path']) ? $options['path'] : '')
        );

        $headers = array(
            "User-Agent"    => PayPalUserAgent::getValue(PayPalConstants::SDK_NAME, PayPalConstants::SDK_VERSION),
            "Accept"        => "*/*"
        );
        
        if (isset($options['sharedId'])) {
            $headers["Authorization"] = "Basic " . base64_encode($options['sharedId']);
        }

        if (isset($options['access_token'])) {
            $headers["Authorization"] = "Bearer " . $options['access_token'];
        }

        $httpConfig->setHeaders($headers);

        // Add any additional Headers that they may have provided
        $headers = $this->apiContext->getRequestHeaders();
        foreach ($headers as $key => $value) {
            $httpConfig->addHeader($key, $value);
        }
    }

    /**
     * End Point
     *
     * @param array $config
     *
     * @return string
     * @throws \PayPal\Exception\PayPalConfigurationException
     */
    private function _getEndpoint($config)
    {
        if (isset($config['service.EndPoint'])) {
            return $config['service.EndPoint'];
        } elseif (isset($config['mode'])) {
            switch (strtoupper($config['mode'])) {
                case 'SANDBOX':
                    return PayPalConstants::REST_SANDBOX_ENDPOINT;
                    break;
                case 'LIVE':
                    return PayPalConstants::REST_LIVE_ENDPOINT;
                    break;
                default:
                    throw new PayPalConfigurationException('The mode config parameter must be set to either sandbox/live');
                    break;
            }
        } else {
            // Defaulting to Sandbox
            return PayPalConstants::REST_SANDBOX_ENDPOINT;
        }
    }
}
