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
        
      $data = $apiManager->getSubscriber($mail, MODULE_CLEVERREACH_GROUP);
      
      if (isset($data['error'])) {
        $newsletter_query = xtc_db_query("SELECT * 
                                            FROM ".TABLE_NEWSLETTER_RECIPIENTS." 
                                           WHERE customers_email_address = '".xtc_db_input($mail)."'");
        $newsletter = xtc_db_fetch_array($newsletter_query);

        $apiManager->createSubscriber(
          $mail,
          MODULE_CLEVERREACH_GROUP, 
          true,
          array(
            'firstname' => encode_utf8($newsletter['customers_firstname'], $_SESSION['language_charset'], true),
            'lastname' => encode_utf8($newsletter['customers_lastname'], $_SESSION['language_charset'], true)
          )
        );
      } elseif ($data['active'] !== true) {
        $apiManager->setSubscriberStatus($mail, MODULE_CLEVERREACH_GROUP, true);
      }
    }
  }
