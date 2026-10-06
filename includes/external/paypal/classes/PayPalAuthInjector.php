<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// used classes
use PayPalCheckoutSdk\Core\AuthorizationInjector;
use PayPalCheckoutSdk\Core\PayPalEnvironment;
use PayPalCheckoutSdk\Core\AccessTokenRequest;
use PayPalCheckoutSdk\Core\RefreshTokenRequest;
use PayPalCheckoutSdk\Core\AccessToken;
use PayPalHttp\HttpClient;


class PayPalAuthInjector extends AuthorizationInjector {

  private $cache_client;
  private $cache_environment;
  private $cache_refreshToken;
  private $cache_customer_id;
  private $cache_file;
  private $cache_key;
  private $cache_enc_key = null;

  public function __construct(HttpClient $client, PayPalEnvironment $environment, $refreshToken, $customer_id, $mode, $client_id, $client_secret) {
    parent::__construct($client, $environment, $refreshToken, $customer_id);

    $this->cache_client = $client;
    $this->cache_environment = $environment;
    $this->cache_refreshToken = $refreshToken;
    $this->cache_customer_id = $customer_id;
    $this->cache_key = hash('sha256', $mode.'|'.$client_id.'|'.$client_secret);
    // no file cache without a secret or openssl, the cache dir can be publicly readable (nginx)
    if ((string)$client_secret !== ''
        && function_exists('openssl_encrypt')
        && function_exists('openssl_decrypt')
        && function_exists('random_bytes')
        )
    {
      $this->cache_enc_key = hash('sha256', $client_secret, true);
    }
    $this->cache_file = SQL_CACHEDIR.'pp_auth_v2_'.$mode.'.cache';
  }


  public function inject($request) {
    if (!array_key_exists('Authorization', $request->headers)
        && !($request instanceof AccessTokenRequest)
        && !($request instanceof RefreshTokenRequest)
        )
    {
      if (is_null($this->accessToken) || $this->accessToken->isExpired()) {
        $this->accessToken = $this->loadAccessToken();
      }
      $request->headers['Authorization'] = 'Bearer '.$this->accessToken->token;
    }
  }


  public function getAccessToken() {
    // used for the sdk user token (data-user-id-token), requires a valid id_token
    $this->accessToken = $this->loadAccessToken(true);
    return $this->accessToken;
  }


  private function loadAccessToken($require_id_token = false) {
    // the cache is only used for the generic client credentials token
    $use_cache = (is_null($this->cache_refreshToken) && is_null($this->cache_customer_id) && !is_null($this->cache_enc_key));

    if ($use_cache === true && is_file($this->cache_file)) {
      $cached = false;
      $cache_handle = fopen($this->cache_file, 'r');
      if ($cache_handle !== false) {
        if (flock($cache_handle, LOCK_SH)) {
          $cached = $this->decryptCache(stream_get_contents($cache_handle));
          flock($cache_handle, LOCK_UN);
        }
        fclose($cache_handle);
      }
      // a file that cannot be decrypted, e.g. plaintext from an older version, goes at once
      if ($cached === false) {
        @unlink($this->cache_file);
      }
      if (is_array($cached)
          && isset($cached['cache_key'])
          && hash_equals($this->cache_key, $cached['cache_key'])
          && isset($cached['access_token'])
          && array_key_exists('id_token', $cached)
          && isset($cached['token_type'])
          && isset($cached['expires_at'])
          && $cached['expires_at'] > time()
          && ($require_id_token === false
              || (isset($cached['id_expires_at']) && $cached['id_expires_at'] > time())
              )
          )
      {
        return new AccessToken($cached['access_token'], $cached['id_token'], $cached['token_type'], $cached['expires_at'] - time());
      }
    }

    $accessTokenResponse = $this->cache_client->execute(new AccessTokenRequest($this->cache_environment, $this->cache_refreshToken, $this->cache_customer_id));
    $accessToken = $accessTokenResponse->result;
    $id_token = ((isset($accessToken->id_token)) ? $accessToken->id_token : NULL);

    if ($use_cache === true && is_writeable(SQL_CACHEDIR)) {
      // a cached token should stay valid for at least the active customer session
      $expiry_buffer = ((defined('SESSION_LIFE_CUSTOMERS')) ? (int)SESSION_LIFE_CUSTOMERS : 1440);

      // the id_token is a jwt with its own (shorter) expiry
      $id_expires_at = 0;
      if (!is_null($id_token)) {
        $jwt_parts = explode('.', $id_token);
        if (count($jwt_parts) === 3) {
          $jwt_payload = json_decode(base64_decode(strtr($jwt_parts[1], '-_', '+/')), true);
          if (is_array($jwt_payload) && isset($jwt_payload['exp'])) {
            $id_expires_at = (int)$jwt_payload['exp'] - $expiry_buffer;
            if ($id_expires_at <= time()) {
              // id_token lives shorter than the buffer, keep a minimal reserve
              $id_expires_at = (int)$jwt_payload['exp'] - 60;
            }
          }
        }
      }

      $expires_at = time() + $accessToken->expires_in - $expiry_buffer;
      if ($expires_at <= time()) {
        $expires_at = time() + $accessToken->expires_in - 60;
      }

      $encrypted = $this->encryptCache(json_encode(array(
        'cache_key' => $this->cache_key,
        'access_token' => $accessToken->access_token,
        'id_token' => $id_token,
        'token_type' => $accessToken->token_type,
        'expires_at' => $expires_at,
        'id_expires_at' => $id_expires_at,
      )));
      if ($encrypted !== false) {
        file_put_contents($this->cache_file, $encrypted, LOCK_EX);
      }
    }

    return new AccessToken($accessToken->access_token, $id_token, $accessToken->token_type, $accessToken->expires_in);
  }


  private function encryptCache($json) {
    if (!is_string($json)) {
      return false;
    }
    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($json, 'aes-256-gcm', $this->cache_enc_key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($ciphertext === false || strlen($tag) !== 16) {
      return false;
    }
    return base64_encode($iv.$tag.$ciphertext);
  }


  // returns false on any error (plaintext, truncated or foreign file), which counts as cache miss
  private function decryptCache($content) {
    if (!is_string($content)) {
      return false;
    }
    $raw = base64_decode(trim($content), true);
    if ($raw === false || strlen($raw) <= 28) {
      return false;
    }
    $json = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $this->cache_enc_key, OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
    if ($json === false) {
      return false;
    }
    $data = json_decode($json, true);
    return ((is_array($data)) ? $data : false);
  }

}
