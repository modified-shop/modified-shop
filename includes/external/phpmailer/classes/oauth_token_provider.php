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
    protected $scope;
    protected $refreshTokenConfigurationKey;

    public function __construct($options)
    {
        $this->tokenEndpoint = $options['token_endpoint'];
        $this->clientId = $options['client_id'];
        $this->clientSecret = $options['client_secret'];
        $this->refreshToken = $options['refresh_token'];
        $this->userEmail = $options['user_email'];
        $this->scope = isset($options['scope']) ? $options['scope'] : '';
        $this->refreshTokenConfigurationKey = isset($options['refresh_token_configuration_key'])
            ? $options['refresh_token_configuration_key']
            : '';
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
        $parameters = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        );
        if ($this->scope != '') {
            $parameters['scope'] = $this->scope;
        }

        $postFields = http_build_query($parameters);

        $ch = curl_init($this->tokenEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
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
            $oauthError = (is_array($data) && isset($data['error'])) ? $data['error'] : 'invalid_response';
            throw new Exception(
                'OAuth token endpoint returned an error (HTTP ' . $httpCode . ', ' . $oauthError . ')'
            );
        }

        if (isset($data['refresh_token']) && $data['refresh_token'] != $this->refreshToken) {
            $this->saveRefreshToken($data['refresh_token']);
        }

        return $data['access_token'];
    }

    protected function saveRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        if ($this->refreshTokenConfigurationKey == ''
            || !preg_match('/^[A-Z0-9_]+$/', $this->refreshTokenConfigurationKey)
            || !defined('TABLE_CONFIGURATION')
            || !function_exists('xtc_db_query')
            || !function_exists('xtc_db_input')
        ) {
            return;
        }

        xtc_db_query(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_value = '" . xtc_db_input($refreshToken) . "'
              WHERE configuration_key = '" . $this->refreshTokenConfigurationKey . "'"
        );
    }
}
