<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once(DIR_FS_EXTERNAL.'phpmailer/OAuthTokenProvider.php');

use PHPMailer\PHPMailer\OAuthTokenProvider;

class oauth_token_provider implements OAuthTokenProvider
{
    protected $tokenEndpoint;
    protected $clientId;
    protected $clientSecret;
    protected $refreshToken;
    protected $userEmail;

    public function __construct($options)
    {
        $this->tokenEndpoint = $options['token_endpoint'];
        $this->clientId = $options['client_id'];
        $this->clientSecret = $options['client_secret'];
        $this->refreshToken = $options['refresh_token'];
        $this->userEmail = $options['user_email'];
    }

    public function getOauth64()
    {
        $accessToken = $this->fetchAccessToken();

        return base64_encode(
            'user=' . $this->userEmail .
            "\001auth=Bearer " . $accessToken .
            "\001\001"
        );
    }

    protected function fetchAccessToken()
    {
        $postFields = http_build_query(array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ));

        $ch = curl_init($this->tokenEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('OAuth token endpoint request failed: ' . $curlError);
        }

        $data = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300 || !isset($data['access_token'])) {
            throw new Exception('OAuth token endpoint returned an error (HTTP ' . $httpCode . '): ' . $response);
        }

        return $data['access_token'];
    }
}
