<?php

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpRequest;

class GenerateClientTokenRequest extends HttpRequest
{
    function __construct()
    {
        parent::__construct("/v1/identity/generate-token?", "POST");
        $this->headers["Content-Type"] = "application/json";
    }
}
