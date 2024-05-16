<?php

namespace PayPalCheckoutSdk\Core;


class AccessToken
{
    public $token;
    public $tokenId;
    public $tokenType;
    public $expiresIn;
    private $createDate;

    public function __construct($token, $tokenId, $tokenType, $expiresIn)
    {
        $this->token = $token;
        $this->tokenId = $tokenId;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->createDate = time();
    }

    public function isExpired()
    {
        return time() >= $this->createDate + $this->expiresIn;
    }
}