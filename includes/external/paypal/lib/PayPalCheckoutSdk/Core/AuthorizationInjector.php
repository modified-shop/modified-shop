<?php

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpRequest;
use PayPalHttp\Injector;
use PayPalHttp\HttpClient;

class AuthorizationInjector implements Injector
{
    private $client;
    private $environment;
    private $refreshToken;
    private $customer_id;
    public $accessToken;

    public function __construct(HttpClient $client, PayPalEnvironment $environment, $refreshToken, $customer_id)
    {
        $this->client = $client;
        $this->environment = $environment;
        $this->refreshToken = $refreshToken;
        $this->customer_id = $customer_id;
    }

    public function inject($request)
    {
        if (!$this->hasAuthHeader($request) && !$this->isAuthRequest($request))
        {
            if (is_null($this->accessToken) || $this->accessToken->isExpired())
            {
                $this->accessToken = $this->fetchAccessToken();
            }
            $request->headers['Authorization'] = 'Bearer ' . $this->accessToken->token;
        }
    }

    private function fetchAccessToken()
    {
        $accessTokenResponse = $this->client->execute(new AccessTokenRequest($this->environment, $this->refreshToken, $this->customer_id));    
        $accessToken = $accessTokenResponse->result;
        return new AccessToken($accessToken->access_token, $accessToken->id_token, $accessToken->token_type, $accessToken->expires_in);
    }

    private function isAuthRequest($request)
    {
        return $request instanceof AccessTokenRequest || $request instanceof RefreshTokenRequest;
    }

    private function hasAuthHeader(HttpRequest $request)
    {
        return array_key_exists("Authorization", $request->headers);
    }
}
