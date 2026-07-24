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
    protected $oauthErrorConfigurationKey;
    protected $oauthError;
    protected static $tokenCache = array();
    protected static $tokenErrors = array();

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
        $this->oauthErrorConfigurationKey = isset($options['oauth_error_configuration_key'])
            ? $options['oauth_error_configuration_key']
            : '';
        $this->oauthError = isset($options['oauth_error']) ? $options['oauth_error'] : '';
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
        $cacheKey = $this->getCacheKey();

        if (isset(self::$tokenErrors[$cacheKey])) {
            throw new Exception(self::$tokenErrors[$cacheKey]);
        }

        if (isset(self::$tokenCache[$cacheKey])) {
            $this->refreshToken = self::$tokenCache[$cacheKey]['refresh_token'];
            if (self::$tokenCache[$cacheKey]['expires_at'] > (time() + 60)) {
                return self::$tokenCache[$cacheKey]['access_token'];
            }
        }

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

        $tokenResponse = $this->requestToken($postFields);
        $response = $tokenResponse['response'];
        $httpCode = $tokenResponse['http_code'];
        $curlError = $tokenResponse['curl_error'];

        if ($response === false) {
            $message = 'OAuth token endpoint request failed: ' . $curlError;
            $this->saveOauthError('request_failed');
            self::$tokenErrors[$cacheKey] = $message;
            throw new Exception($message);
        }

        $data = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300 || !isset($data['access_token'])) {
            $oauthError = (is_array($data) && isset($data['error'])) ? $data['error'] : 'invalid_response';
            $oauthError = $this->normalizeOauthError($oauthError);
            $message = 'OAuth token endpoint returned an error (HTTP ' . $httpCode . ', ' . $oauthError . ')';
            $this->saveOauthError($oauthError);
            self::$tokenErrors[$cacheKey] = $message;
            throw new Exception($message);
        }

        if (isset($data['refresh_token']) && $data['refresh_token'] != $this->refreshToken) {
            $this->saveRefreshToken($data['refresh_token']);
        }

        $this->clearOauthError();

        if (isset($data['expires_in']) && (int)$data['expires_in'] > 0) {
            self::$tokenCache[$cacheKey] = array(
                'access_token' => $data['access_token'],
                'refresh_token' => $this->refreshToken,
                'expires_at' => time() + (int)$data['expires_in'],
            );
        }

        return $data['access_token'];
    }

    protected function requestToken($postFields)
    {
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

        return array(
            'response' => $response,
            'http_code' => $httpCode,
            'curl_error' => $curlError,
        );
    }

    protected function saveRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        $this->saveConfigurationValue($this->refreshTokenConfigurationKey, $refreshToken);
    }

    protected function getCacheKey()
    {
        return hash(
            'sha256',
            implode("\0", array(
                $this->tokenEndpoint,
                $this->clientId,
                $this->clientSecret,
                $this->userEmail,
                $this->scope,
            ))
        );
    }

    protected function normalizeOauthError($oauthError)
    {
        if (!is_scalar($oauthError)) {
            return 'invalid_response';
        }
        $oauthError = strtolower((string)$oauthError);
        $oauthError = preg_replace('/[^a-z0-9_.-]/', '_', $oauthError);
        return ($oauthError != '' ? substr($oauthError, 0, 64) : 'invalid_response');
    }

    protected function saveOauthError($oauthError)
    {
        $this->oauthError = $this->normalizeOauthError($oauthError);
        $this->saveConfigurationValue($this->oauthErrorConfigurationKey, $this->oauthError);
    }

    protected function clearOauthError()
    {
        if ($this->oauthError != '') {
            $this->saveConfigurationValue($this->oauthErrorConfigurationKey, '');
            $this->oauthError = '';
        }
    }

    protected function saveConfigurationValue($configurationKey, $value)
    {
        if ($configurationKey == ''
            || !preg_match('/^[A-Z0-9_]+$/', $configurationKey)
            || !defined('TABLE_CONFIGURATION')
            || !function_exists('xtc_db_query')
            || !function_exists('xtc_db_input')
        ) {
            return;
        }

        xtc_db_query(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_value = '" . xtc_db_input($value) . "'
              WHERE configuration_key = '" . $configurationKey . "'"
        );
    }
}
