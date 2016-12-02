<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// autoload
require(DIR_FS_EXTERNAL.'paypal/classes/PayPalBootstrap.php');
$bootstrap = new PayPalBootstrap();
$bootstrap->init();


// used classes
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;


class PayPalAuth {


  function __construct() {

  }
  
  
  protected function apiContext() {

    $credential = new OAuthTokenCredential(
      $this->get_config('PAYPAL_CLIENT_ID_'.strtoupper($this->get_config('PAYPAL_MODE'))),
      $this->get_config('PAYPAL_SECRET_'.strtoupper($this->get_config('PAYPAL_MODE')))
    );
    $credential::$expiryBufferTime = ((defined('SESSION_LIFE_CUSTOMERS')) ? SESSION_LIFE_CUSTOMERS : 1440);
    
    $apiContext = new ApiContext($credential);
    
    $auth_cache_file = SQL_CACHEDIR.'pp_auth_'.$this->get_config('PAYPAL_MODE').'.cache';
    if (!is_file($auth_cache_file)) {
      file_put_contents($auth_cache_file, '');
    }
    
    $apiContext->setConfig(
      array(
        'mode' => $this->get_config('PAYPAL_MODE'),
        'log.LogEnabled' => (($this->get_config('PAYPAL_LOG_ENALBLED') == '1') ? true : false),
        'log.FileName' => DIR_FS_LOG.'mod_paypal_'.$this->get_config('PAYPAL_MODE').'_'.date('Y-m-d') .'.log',
        'log.LogLevel' => $this->loglevel,
        'validation.level' => 'log',
        'cache.enabled' => ((is_writeable(SQL_CACHEDIR)) ? true : false),
        'cache.FileName' => $auth_cache_file
      )
    );
    
    if (strpos($this->code, 'paypalplus') !== false) {
      $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', 'ModifiedeCommerce_Cart_REST_Plus');
    } elseif (strpos($this->code, 'paypalinstallment') !== false) {
      $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', 'ModifiedeCommerce_Cart_Inst');
    } else {
      $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', 'ModifiedeCommerce_Cart_REST_EC');
    }
    
    return $apiContext;
  }
  
}
?>