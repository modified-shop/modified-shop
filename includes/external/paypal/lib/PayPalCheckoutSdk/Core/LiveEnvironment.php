<?php

namespace PayPalCheckoutSdk\Core;

class LiveEnvironment extends PayPalEnvironment
{
    public function __construct($clientId, $clientSecret)
    {
        parent::__construct($clientId, $clientSecret);
    }

    public function baseUrl()
    {
        return "https://api.paypal.com";
    }
}
