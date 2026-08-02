<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once(DIR_FS_CATALOG . 'includes/classes/class.logger.php');
require_once(DIR_FS_EXTERNAL . 'GuzzleHttp/functions_include.php');
require_once(DIR_FS_EXTERNAL . 'GuzzleHttp/Promise/functions_include.php');
require_once(DIR_FS_EXTERNAL . 'GuzzleHttp/Psr7/functions_include.php');

class oauth_http_client
{
    protected $client;
    protected $defaultOptions;

    public function __construct($client = null)
    {
        $this->client = $client ?: new \GuzzleHttp\Client();
        $this->defaultOptions = array(
            'connect_timeout' => 5,
            'timeout' => 15,
            'verify' => true,
            'http_errors' => false,
            'allow_redirects' => false,
        );
    }

    public function request($method, $url, $options = array())
    {
        try {
            $response = $this->client->request(
                $method,
                $url,
                array_replace($this->defaultOptions, $options)
            );

            return array(
                'response' => (string)$response->getBody(),
                'http_code' => $response->getStatusCode(),
                'error' => '',
            );
        } catch (\GuzzleHttp\Exception\GuzzleException $exception) {
            return array(
                'response' => false,
                'http_code' => 0,
                'error' => 'request_failed',
            );
        }
    }
}
