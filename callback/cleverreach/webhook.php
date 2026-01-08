<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  chdir('../../');
  include('includes/application_top.php');

  use CleverReach\ApiManager;
  use CleverReach\Http\Guzzle as HttpAdapter;
     
  if (defined('MODULE_CLEVERREACH_STATUS') && MODULE_CLEVERREACH_STATUS == 'true') {
    if (isset($_GET['secret'])) {
      echo sprintf('%s %s', MODULE_CLEVERREACH_WEBHOOK_VERIFY, $_GET['secret']);
    } elseif (isset($_SERVER['HTTP_X_CR_CALLTOKEN'])
              && $_SERVER['HTTP_X_CR_CALLTOKEN'] == MODULE_CLEVERREACH_WEBHOOK_TOKEN
              )
    {
      //include needed functions
      require_once(DIR_FS_INC.'get_external_content.inc.php');
      require_once(DIR_FS_EXTERNAL.'GuzzleHttp/functions_include.php');
      require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Promise/functions_include.php');
      require_once(DIR_FS_EXTERNAL.'GuzzleHttp/Psr7/functions_include.php');

      require_once(DIR_FS_EXTERNAL.'CleverReach/autoload.php');
      
      // include needed classes
      require_once (DIR_WS_CLASSES.'class.newsletter.php');

      $request_json = get_external_content('php://input', 3, false);
      $request = json_decode($request_json, true);
        
      if (isset($request['event'])) {
        $httpAdapter = new HttpAdapter();
        $response = $httpAdapter->authorize(MODULE_CLEVERREACH_CLIENT_ID, MODULE_CLEVERREACH_SECRET);
      
        if (isset($response['access_token'])) {
          $httpAdapter = new HttpAdapter(array('access_token' => $response['access_token']));
          $apiManager = new ApiManager($httpAdapter);
  
          switch ($request['event']) {
            case 'receiver.unsubscribed':              
            case 'receiver.deleted':
              if (isset($request['payload']['pool_id'])) {
                $data = $apiManager->getSubscriber($request['payload']['pool_id'], MODULE_CLEVERREACH_GROUP);
                $mail = $data['email'];
              } else {
                $mail = $apiManager->decode($request['payload']['email'], MODULE_CLEVERREACH_WEBHOOK_SECRET);
              }              

              $check_query = xtc_db_query("SELECT customers_email_address
                                             FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                            WHERE customers_email_address = '".xtc_db_input($mail)."'");
              if (xtc_db_num_rows($check_query) > 0) {      
                $sql_data_array = array (
                  'mail_status' => '2',
                  'mail_key' => '',
                  'date_added' => '0000-00-00 00:00:00',
                  'ip_date_added' => '',
                  'date_confirmed' => '0000-00-00 00:00:00',
                  'ip_date_confirmed' => '',
                );
                xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array, 'update', "customers_email_address = '".xtc_db_input($mail)."'");

                $sql_data_array = array(
                  'customers_email_address' => $mail,
                  'customers_action' => 'unsubscribe',
                  'ip_address' => 'Cleverreach',
                  'date_added' => 'now()'
                );
                xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS_HISTORY, $sql_data_array);
              }
              break;
          }
        }
      }
    }
  }
