<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');


// used classes
use PayPal\Api\FlowConfig; 
use PayPal\Api\Presentation; 
use PayPal\Api\WebProfile; 
use PayPal\Api\InputFields; 
use PayPal\Api\Webhook;
use PayPal\Api\WebhookEventType;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;


class PayPalAdmin extends PayPalPayment {


	function __construct() {
    PayPalPayment::__construct('paypal');    
	}


  function list_profile() {
    
    // auth
    $apiContext = $this->apiContext();
    
    // set WebProfile
    $webProfile = new WebProfile();
    
    try {
      $webProfileList = $webProfile->get_list($apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
    
    // set array
    $list_array = array();
    
    if ($valid === true) {      
      for ($i=0, $n=count($webProfileList); $i<$n; $i++) {        
        $profile = $webProfileList[$i];   
        $flowConfig = $profile->getFlowConfig();
        $inputFields = $profile->getInputFields();
        $presentation = $profile->getPresentation();
        
        $list_array[] = array(
          'id' => $profile->getId(),
          'name' => $profile->getName(),
          'status' => (($this->get_config('PAYPAL_STANDARD_PROFILE') == $profile->getId()) ? true : false),
          'flow_config' => array(
            'landing_page_type' => ((is_object($flowConfig)) ? $flowConfig->getLandingPageType() : ''),
          ),
          'input_fields' => array(
            'allow_note' => ((is_object($inputFields)) ? $inputFields->getAllowNote() : ''),
            'no_shipping' => ((is_object($inputFields)) ? $inputFields->getNoShipping() : ''),
            'address_override' => ((is_object($inputFields)) ? $inputFields->getAddressOverride() : ''),
          ),
          'presentation' => array(
            'brand_name' => ((is_object($presentation)) ? $presentation->getBrandName() : ''),
            'logo_image' => ((is_object($presentation)) ? $presentation->getLogoImage() : ''),
            'locale_code' => ((is_object($presentation)) ? $presentation->getLocaleCode() : ''),
          ),
        );
      }
    }
        
    return $list_array;    
  }
  
  
  function create_profile($config) {
  
    // auth
    $apiContext = $this->apiContext();
    
    // set FlowConfig
    $flowConfig = new FlowConfig();
    $flowConfig->setLandingPageType($config['flow_config']['landing_page_type']);

    // set Presentation
    $presentation = new Presentation();
    if ($config['presentation']['logo_image'] != '') {
      $presentation->setLogoImage($config['presentation']['logo_image']);
    }
    if ($config['presentation']['brand_name'] != '') {
      $presentation->setBrandName($config['presentation']['brand_name']);
    }
    if ($config['presentation']['locale_code'] != '') {
      $presentation->setLocaleCode(strtoupper($config['presentation']['locale_code']));
    }
    
    $addess_override = (($config['input_fields']['address_override'] == '0') ? 1 : 0);
    
    // set InputFields
    $inputFields = new InputFields();
    $inputFields->setAllowNote(0)
                ->setNoShipping(0)
                ->setAddressOverride((int)$addess_override);

    // set WebProfile
    $webProfile = new WebProfile();
    $webProfile->setName((($config['name'] != '') ? $config['name'] : uniqid()))
               ->setFlowConfig($flowConfig)
               ->setPresentation($presentation)
               ->setInputFields($inputFields);

    try {
      $webProfile->create($apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
  }


  function update_profile($config) {
  
    // auth
    $apiContext = $this->apiContext();
    
    // set FlowConfig
    $flowConfig = new FlowConfig();
    $flowConfig->setLandingPageType($config['flow_config']['landing_page_type']);

    // set Presentation
    $presentation = new Presentation();
    if ($config['presentation']['logo_image'] != '') {
      $presentation->setLogoImage($config['presentation']['logo_image']);
    }
    if ($config['presentation']['brand_name'] != '') {
      $presentation->setBrandName($config['presentation']['brand_name']);
    }
    if ($config['presentation']['locale_code'] != '') {
      $presentation->setLocaleCode(strtoupper($config['presentation']['locale_code']));
    }

    $addess_override = (($config['input_fields']['address_override'] == '0') ? 1 : 0);

    // set InputFields
    $inputFields = new InputFields();
    $inputFields->setAllowNote(0)
                ->setNoShipping(0)
                ->setAddressOverride((int)$addess_override);

    // set WebProfile
    $webProfile = new WebProfile();
    $webProfile->setId($config['id'])
               ->setName($config['name'])
               ->setFlowConfig($flowConfig)
               ->setPresentation($presentation)
               ->setInputFields($inputFields);

    try {
      $webProfile->update($apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
      
      if ($ex instanceof \PayPal\Exception\PayPalConnectionException) {
        global $messageStack;

        $error_json = $ex->getData();
        $error = json_decode($error_json, true);

        $messageStack->add_session(((isset($error['name'])) ? '<b>'.$error['name'].':</b> ' : '') . $error['message'], 'warning');
        if (isset($error['details'])) {
          for ($i=0, $n=count($error['details']); $i<$n; $i++) {
            $messageStack->add_session($error['details'][$i]['field'].': '. $error['details'][$i]['issue'], 'warning');
          }
        }
      }
    }
        
    if ($config['status'] == '1') {
      $sql_data_array = array(
        array(
          'config_key' => 'PAYPAL_STANDARD_PROFILE',
          'config_value' => $config['id'],
        ),
      );
      $this->save_config($sql_data_array);
    } elseif ($config['id'] == $this->get_config('PAYPAL_STANDARD_PROFILE')) {
      $this->delete_config('PAYPAL_STANDARD_PROFILE');
    }

    $sql_data_array = array(
      array(
        'config_key' => strtoupper($config['id']).'_TIME', 
        'config_value' => time(),
      ),
      array(
        'config_key' => strtoupper($config['id']).'_ADDRESS', 
        'config_value' => $addess_override,
      ),          
    );
    $this->save_config($sql_data_array);
  }


  function delete_profile($id) {

    // auth
    $apiContext = $this->apiContext();

    // set WebProfile
    $webProfile = new WebProfile();
    $webProfile->setId($id);

    try {
      $webProfile->delete($apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
    
    if ($id == $this->get_config('PAYPAL_STANDARD_PROFILE')) {
      $this->delete_config('PAYPAL_STANDARD_PROFILE');
    }
  }


  function list_webhooks() {
  
    // auth
    $apiContext = $this->apiContext();

    // set webhooks
    $webhooks = new Webhook();
    
    try {
      $WebhookList = $webhooks->getAll($apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }

    // set array
    $list_array = array();

    if ($valid === true) {      
      $webhooks = $WebhookList->getWebhooks();
      
      for ($w=0, $z=count($webhooks); $w<$z; $w++) {   
        $eventtypes = $webhooks[$w]->getEventTypes();
        $list_array[$w]['id'] = $webhooks[$w]->getId();
        $list_array[$w]['url'] = $webhooks[$w]->getUrl();
            
        for ($i=0, $n=count($eventtypes); $i<$n; $i++) { 
      
          $list_array[$w]['data'][] = array(
            'name' => $eventtypes[$i]->getName(),
            'description' => $eventtypes[$i]->getDescription(),
            'orders_status' => $this->get_config($eventtypes[$i]->getName()),
          );
        }
      }
    }
        
    return $list_array;    
  }

  
  function create_webhook($data) {
        
    // auth
    $apiContext = $this->apiContext();
    
    
    $webhookEventTypes = array();
    
    for ($i=0, $n=count($data['data']); $i<$n; $i++) {
      if ($data['data'][$i]['name'] != '') {
        $webhookEvent = new WebhookEventType();
        $webhookEvent->setName($data['data'][$i]['name']);
      
        $webhookEventTypes[] = $webhookEvent;
      }
    }

    // set webhook
    $webhook = new Webhook();
    
    $webhook->setUrl(xtc_catalog_href_link('callback/paypal/webhook.php', '', 'SSL', false))
            ->setEventTypes($webhookEventTypes);

    try {
      $WebhookList = $webhook->create($apiContext);
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
    }    

    $sql_data_array = array();
    for ($i=0, $n=count($data['data']); $i<$n; $i++) {
      if ($data['data'][$i]['name'] != '') {
        $sql_data_array[] = array(
          'config_key' => $data['data'][$i]['name'],
          'config_value' => $data['data'][$i]['orders_status'],
        );
      }
    }
    $this->save_config($sql_data_array);
  }
  
  
  function update_webhook($data) {

    // auth
    $apiContext = $this->apiContext();

    // set webhooks
    $webhook = new Webhook();
    
    try {
      $WebhookList = $webhook->get($data['id'], $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }

    if ($valid === true) {      

      $webhookEventTypes = array();
    
      for ($i=0, $n=count($data['data']); $i<$n; $i++) {
        if ($data['data'][$i]['name'] != '') {
          $webhookEvent = new WebhookEventType();
          $webhookEvent->setName($data['data'][$i]['name']);
      
          $webhookEventTypes[] = $webhookEvent;
        }
      }

      $patch = new Patch();
      $patch->setOp("replace")
            ->setPath("/event_types")
            ->setValue($webhookEventTypes);

      $patchRequest = new PatchRequest();
      $patchRequest->addPatch($patch);
    }
    
    try {
      $WebhookList->update($patchRequest, $apiContext);
      $success = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $success = false;
    }
       
    $avaliable_data = $this->available_webhooks();
    for ($i=0, $n=count($avaliable_data); $i<$n; $i++) { 
      $this->delete_config($avaliable_data[$i]['name']);
    }
    
    $sql_data_array = array();
    for ($i=0, $n=count($data['data']); $i<$n; $i++) {
      if ($data['data'][$i]['name'] != '') {
        $sql_data_array[] = array(
          'config_key' => $data['data'][$i]['name'],
          'config_value' => $data['data'][$i]['orders_status'],
        );
      }
    }
    $this->save_config($sql_data_array);
  }
  
  
  function edit_webhook($id) {

    // auth
    $apiContext = $this->apiContext();
    
    // available
    $available_array = $this->available_webhooks();
    
    // set webhooks
    $webhook = new Webhook();
    
    try {
      $WebhookList = $webhook->get($id, $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }

    if ($valid === true) {      
      
      // set array
      $list_array = array();

      $eventtypes = $WebhookList->getEventTypes();
      for ($i=0, $n=count($eventtypes); $i<$n; $i++) { 
        $eventtype = $eventtypes[$i];
        $list_array[] = $eventtype->getName();
      }
      
      for ($i=0, $n=count($available_array); $i<$n; $i++) { 
        $available_array[$i]['status'] = ((in_array($available_array[$i]['name'], $list_array)) ? true : false);
        $available_array[$i]['orders_status'] = $this->get_config($available_array[$i]['name']);
      }    
    }
    
    return $available_array;
  }
  
  
  function delete_webhook($id) {

    // auth
    $apiContext = $this->apiContext();

    // set webhooks
    $webhook = new Webhook();
    
    try {
      $WebhookList = $webhook->get($id, $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }

    if ($valid === true) {      
      try {
        $WebhookList->delete($apiContext);
      } catch (Exception $ex) {
        $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      }
    }

    $avaliable_data = $this->available_webhooks();
    for ($i=0, $n=count($avaliable_data); $i<$n; $i++) { 
      $this->delete_config($avaliable_data[$i]['name']);
    }
  }


  function available_webhooks() {
  
    // auth
    $apiContext = $this->apiContext();

    // set webhooks
    $webhooks_event = new WebhookEventType();
    
    try {
      $WebhookList = $webhooks_event->availableEventTypes($apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }

    // set array
    $list_array = array();

    if ($valid === true) {           
      $eventtypes = $WebhookList->getEventTypes();
          
      for ($i=0, $n=count($eventtypes); $i<$n; $i++) { 
        $eventtype = $eventtypes[$i];
    
        $list_array[] = array(
          'name' => $eventtype->getName(),
          'description' => $eventtype->getDescription(),
        );
      }
    }
        
    return $list_array;    
  }

  
}
?>