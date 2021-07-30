<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_INC.'get_external_content.inc.php');
require_once (DIR_FS_INC.'xtc_php_mail.inc.php');

class protectedshops_update {

  var $enabled = false;
  var $token = false;
  var $url = 'https://www.protectedshops.de/api/';

  protected $result = '';
  
  
  function __construct() {
    $this->token = defined('MODULE_PROTECTEDSHOPS_TOKEN') ? MODULE_PROTECTEDSHOPS_TOKEN : '';
    $this->enabled = ((defined('MODULE_PROTECTEDSHOPS_STATUS') && MODULE_PROTECTEDSHOPS_STATUS == 'true') ? true : false);
  }
  
  
  function check_update() {
    if ($this->enabled === true) {
      if (((MODULE_PROTECTEDSHOPS_LAST_UPDATED + MODULE_PROTECTEDSHOPS_UPDATE_INTERVAL) <= time() && MODULE_PROTECTEDSHOPS_AUTOUPDATE == 'true') || defined('RUN_MODE_ADMIN')) {
        
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . (int) time() . "', last_modified = NOW() WHERE configuration_key='MODULE_PROTECTEDSHOPS_LAST_UPDATED'");
        
        $params = array('Request' => 'GetDocumentInfo',
                        'ShopId' => $this->token,
                        );
        $content = $this->request_document($params);
                
        // Documents
        if (isset($content['DocumentDate']) && is_array($content['DocumentDate'])) {
          foreach ($content['DocumentDate'] as $type => $date) {
            ((defined('MODULE_PROTECTEDSHOPS_TYPE_'.strtoupper($type)) && constant('MODULE_PROTECTEDSHOPS_TYPE_'.strtoupper($type)) > 0) ? $this->get_page_content($type, constant('MODULE_PROTECTEDSHOPS_TYPE_'.strtoupper($type))) : '');
            ((defined('MODULE_PROTECTEDSHOPS_PDF_'.strtoupper($type)) && constant('MODULE_PROTECTEDSHOPS_PDF_'.strtoupper($type)) == 'true') ? $this->get_page_content($type) : '');
          }
        }        
      }
    }
  }


  function get_page_content($name, $coID) {
    
    if ($this->enabled === false) {
      return false;
    }
     
    $params = array('Request' => 'GetDocument',
                    'ShopId' => $this->token,
                    'Document' => $name,
                    'Format' => MODULE_PROTECTEDSHOPS_FORMAT
                    );
    $content = $this->request_document($params);    
    
    // convert line breaks
    if (MODULE_PROTECTEDSHOPS_FORMAT == 'Text') {
      $content['Document'] = str_replace("\n", "\r\n", $content['Document']);
    }

    if (md5($content['Document']) != $content['MD5']) {
      $this->transfer_error($name);
      return false;
    }

    //reset Counter
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='0', last_modified = NOW() WHERE configuration_key='MODULE_PROTECTEDSHOPS_ERROR_COUNT_".strtoupper($name)."'");
      
    if (strtolower(MODULE_PROTECTEDSHOPS_TYPE) == 'database') {
      // update data in table
      $sql_data_array = array('content_text' => decode_utf8($content['Document']),
                              'content_file' => '');
            
      xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group='" . (int)$coID . "' and languages_id='2'");
    } else {
      // write content to file
      $format = ((MODULE_PROTECTEDSHOPS_FORMAT == 'Text') ? 'txt' : 'html');
      $file = DIR_FS_CATALOG . 'media/content/ps_'. strtolower($name) .'.'. $format;
      $fp = @fopen($file, 'w+');
      if (is_resource($fp)) {
        fwrite($fp, $content['Document']);
        fclose($fp);
      }
      
      // update data in table
      $sql_data_array = array('content_file' => 'ps_'. strtolower($name) .'.'. $format,
                              'content_text' => '');
      xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group='" . (int)$coID . "' and languages_id='2'");
    }
  }


  function get_page_pdf($name) {
    
    if ($this->enabled === false) {
      return false;
    }
    
    $params = array('Request' => 'GetDocument',
                    'ShopId' => $this->token,
                    'Document' => $name,
                    'Format' => 'Pdf'
                    );
    $content = $this->request_document($params);
    
    if (md5($content['Document']) != $content['MD5']) {
      $this->transfer_error($name);
      return false;
    }

    //reset Counter
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='0', last_modified = NOW() WHERE configuration_key='MODULE_PROTECTEDSHOPS_ERROR_COUNT_PDF_".strtoupper($name)."'");

    // write content to file
    $file = DIR_FS_CATALOG . 'media/content/ps_'. strtolower($name) .'.pdf';
    $fp = @fopen($file, 'w+');
    if (is_resource($fp)) {
      fwrite($fp, base64_decode($content['Document']));
      fclose($fp);
    }      
  }


  function request_document($params) {
    
    $this->result = get_external_content($this->url.'?'.http_build_query($params, '', '&'), '3', false);
    $xml = simplexml_load_string($this->result, null, LIBXML_NOCDATA);
    $json = json_encode($xml);
    $content = json_decode($json, true);

    return $content;
  }
  
    
  function transfer_error($type) {

    // update error counter
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . (int) (constant('MODULE_PROTECTEDSHOPS_ERROR_COUNT_'.strtoupper($type)) + 1) . "', last_modified = NOW() WHERE configuration_key='MODULE_PROTECTEDSHOPS_ERROR_COUNT_".strtoupper($type)."'");

    // eMail Notification
    if (constant('MODULE_PROTECTEDSHOPS_ERROR_COUNT_'.strtoupper($type)) >= '2') {
      $this->enabled = false;
      
      $email_text  = 'URL: '.HTTP_SERVER . DIR_WS_CATALOG . "\n";
      $email_text .= 'TOKEN: '.MODULE_PROTECTEDSHOPS_TOKEN . "\n";
      $email_text .= 'TYPE: '.$type . "\n";
      
      // create response file
      $attachment = DIR_FS_LOG.'response.xml';
      $fp = @fopen($attachment, 'w+');
      if (is_resource($fp)) {
        fwrite($fp, $this->result);
        fclose($fp);
      } else {
        $attachment = '';
        $email_text .= "\n".$request;
      }
      
      // forward to protectedshops      
      $forwarding = 'info@protectedshops.de' . ((EMAIL_SUPPORT_FORWARDING_STRING != '') ? ',' . EMAIL_SUPPORT_FORWARDING_STRING : '');
      
      xtc_php_mail(EMAIL_SUPPORT_ADDRESS, 
                   EMAIL_SUPPORT_NAME, 
                   $email_address, 
                   $name, 
                   $forwarding, 
                   EMAIL_SUPPORT_REPLY_ADDRESS, 
                   EMAIL_SUPPORT_REPLY_ADDRESS_NAME, 
                   $attachment, 
                   '', 
                   'Protectedshops Update Error', 
                   nl2br($email_text), 
                   $email_text);
    
      // update interval + 48h
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . (int) (time() + 172800) . "', last_modified = NOW() WHERE configuration_key='MODULE_PROTECTEDSHOPS_LAST_UPDATED'");

    } else {     

      // update interval in 1 hour
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . (int) ((time() + 3600) - MODULE_PROTECTEDSHOPS_UPDATE_INTERVAL) . "', last_modified = NOW() WHERE configuration_key='MODULE_PROTECTEDSHOPS_LAST_UPDATED'");
    }
  }  
}
?>