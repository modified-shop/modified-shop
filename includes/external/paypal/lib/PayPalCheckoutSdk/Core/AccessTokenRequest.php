<?php

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpRequest;

class AccessTokenRequest extends HttpRequest
{
    public function __construct(PayPalEnvironment $environment, $refreshToken = NULL, $customer_id = NULL)
    {
        parent::__construct("/v1/oauth2/token", "POST");
        $this->headers["Authorization"] = "Basic " . $environment->authorizationString();
        $body = [
            "grant_type" => "client_credentials",
            "response_type" => "id_token"
        ];
        
        if (!is_null($customer_id))
        {
            $body["target_customer_id"] = $customer_id;
        }
        
        if (!is_null($refreshToken))
        {
            $body["grant_type"] = "refresh_token";
            $body["refresh_token"] = $refreshToken;
        }

        $this->body = $body;
        $this->headers["Content-Type"] = "application/x-www-form-urlencoded";
    }
}

