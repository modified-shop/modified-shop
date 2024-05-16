<?php

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpClient;

class PayPalHttpClient extends HttpClient
{
    private $refreshToken;
    private $customers_id;
    public $authInjector;

    public function __construct(PayPalEnvironment $environment, $refreshToken = NULL, $customers_id = NULL)
    {
        parent::__construct($environment);
        $this->refreshToken = $refreshToken;
        $this->customers_id = $customers_id;
        $this->authInjector = new AuthorizationInjector($this, $environment, $refreshToken, $customers_id);
        $this->addInjector($this->authInjector);
        $this->addInjector(new GzipInjector());
        $this->addInjector(new FPTIInstrumentationInjector());
    }

    public function userAgent()
    {
        return UserAgent::getValue();
    }
}

