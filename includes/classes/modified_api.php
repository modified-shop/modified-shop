<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  class modified_api {
  
    private static $_endpoint = 'https://api.modified-shop.org/';
    private static $_method = NULL;

    /**
     * instance
     *
     * @var Singleton
     */
    protected static $_instance = null;


    /**
     * get instance
     *
     * @return   Singleton
     */
    public static function getInstance() {

      if (null === self::$_instance) {
        self::$_instance = new self;
      }

      return self::$_instance;
    }
 
    
    /**
     * clone
     */
    protected function __clone() {}


    /**
     * constructor
     */
    protected function __construct() {}

    
    /**
     * setEndpoint
     */
    public function setEndpoint($endpoint) {
      self::$_endpoint = $endpoint;
    }


    /**
     * setEndpoint
     */
    public function setMethod($method) {
      self::$_method = $method;
    }


    /**
     * get_version
     */
    public static function get_version($version) {  
      $response = self::request('modified/version/');
      
      if ($response == null || !is_array($response) || !isset($response[$version])) {
        throw new Exception('Could not reach external host '.self::$_endpoint);
      } else {
        return $response[$version];
      }
    }


    /**
     * get_newsfeed
     */
    public static function get_newsfeed($version) {  
      $response = self::request('modified/news/'.$version);
      
      if ($response == null || !is_array($response) || !isset($response['channel'])) {
        throw new Exception('Could not reach external host '.self::$_endpoint);
      } else {
        return $response['channel'];
      }
    }


    /**
     * get_start_content
     */
    public static function get_start_content($language) {  
      $response = self::request('modified/start/'.$language);
      
      if ($response == null || !is_array($response) || !isset($response['content'])) {
        throw new Exception('Could not reach external host '.self::$_endpoint);
      } else {
        return $response['content'];
      }
    }


    /**
     * get_support_content
     */
    public static function get_support_content($language) {  
      $response = self::request('modified/support/'.$language);
      
      if ($response == null || !is_array($response)) {
        throw new Exception('Could not reach external host '.self::$_endpoint);
      } else {
        return $response;
      }
    }


    /**
     * get_support_content
     */
    public static function get_paypal_appinator($mode) {  
      $response = self::request('paypal/onboarding/'.$mode);
      
      if ($response == null || !is_array($response) || !isset($response[$mode])) {
        throw new Exception('Could not reach external host '.self::$_endpoint);
      } else {
        return $response[$mode];
      }
    }
    
    
    /**
     * clean
     */
    private static function clean($response) {
      if (is_array($response)) {
        foreach ($response as $key => $value) {
          $response[$key] = self::clean($value);
        }
      } else {
        $response = preg_replace('/<script(.*?)>(.*?)<\/script>/is', '', $response);
        $response = preg_replace('/<iframe(.*?)>(.*?)<\/iframe>/is', '', $response);
      }
      
      return $response;
    }


    /**
     * request
     */
    private static function request($path, $data = '', $timeout = 5) {
      $ch = curl_init(self::$_endpoint.$path);
      
      curl_setopt($ch, CURLOPT_URL, self::$_endpoint.$path);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLINFO_HEADER_OUT, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

      switch (self::$_method) {
        case 'POST':
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          break;
        case 'PUT':
        case 'PATCH':
        case 'DELETE':
          curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
          break;
      }

      if (self::$_method != null) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::$_method);
      }

      $result = curl_exec($ch);
      $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($httpStatus < 200 || $httpStatus >= 300) {
        throw new Exception('Could not reach external host. Status '.$httpStatus);
      }

      $response = json_decode($result, true);
    
      return self::clean($response);
    }
    
  }
