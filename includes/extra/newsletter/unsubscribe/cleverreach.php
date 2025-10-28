<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  use CleverReach\ApiManager;
  use CleverReach\Http\Guzzle as HttpAdapter;
 
  if (defined('MODULE_CLEVERREACH_STATUS') && MODULE_CLEVERREACH_STATUS == 'true') {
    //include needed functions
    require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
    require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
    require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');
    
    require_once(DIR_FS_EXTERNAL.'CleverReach/autoload.php');
       
    $httpAdapter = new HttpAdapter();
    
    $response = $httpAdapter->authorize(MODULE_CLEVERREACH_CLIENT_ID, MODULE_CLEVERREACH_SECRET);
    
    if (isset($response['access_token'])) {
      $httpAdapter = new HttpAdapter(array('access_token' => $response['access_token']));
    
      $apiManager = new ApiManager($httpAdapter);
        
      $apiManager->deleteSubscriber($mail, MODULE_CLEVERREACH_GROUP);
    }
  }
