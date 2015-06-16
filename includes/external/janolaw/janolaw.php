<?php
/* -----------------------------------------------------------------------------------------
   $Id: janolaw.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2010 Gambio OHG (janolaw.php 2010-06-08 gambio)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// http://www.janolaw.de/agb-service/shops/<USER-ID>/<SHOP-ID>/<LÄNDERCODE>/<DATEI- NAME>>/<DATEI-FORMAT>
// http://www.janolaw.de/agb-service/shops/100284901/761296/de/terms_include.html

require_once (DIR_FS_INC.'get_external_content.inc.php');

class janolaw_content {
  var $version = '3'; // version 3
  var $enabled = false;
  var $user_id;
  var $shop_id;
  var $format;
  var $pdf;
  
  
  function janolaw_content() {
    $this->user_id = MODULE_JANOLAW_USER_ID;
    $this->shop_id = MODULE_JANOLAW_SHOP_ID;
    $this->enabled = $this->get_status();
    $this->format = strtolower(MODULE_JANOLAW_FORMAT);
    $this->pdf = ((MODULE_JANOLAW_PDF == 'True') ? true : false);

    if($this->enabled) {
      if (((MODULE_JANOLAW_LAST_UPDATED + MODULE_JANOLAW_UPDATE_INTERVAL) <= time()) || defined('RUN_MODE_ADMIN')) {
        
        $this->get_page_content('datasecurity', MODULE_JANOLAW_TYPE_DATASECURITY);
        $this->get_page_content('terms', MODULE_JANOLAW_TYPE_TERMS);
        $this->get_page_content('legaldetails', MODULE_JANOLAW_TYPE_LEGALDETAILS);
        $this->get_page_content('revocation', MODULE_JANOLAW_TYPE_REVOCATION);
        $this->get_page_content('model-withdrawal-form', MODULE_JANOLAW_TYPE_WITHDRAWL);
              
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . xtc_db_input(time()) . "', last_modified = NOW() where configuration_key='MODULE_JANOLAW_LAST_UPDATED'");
      }
    }    
  }


  function get_status() {
    if(!defined('MODULE_JANOLAW_STATUS') || MODULE_JANOLAW_STATUS == 'False') {
      return false;
    }
    return true;
  }


  function get_page_content($name, $coID = '') {
    global $lng;
    
    if ($coID == '') {
      return;
    }
    
    $mode = '.';
    if ($this->format == 'html') {
      $mode = '_include.';
    }

    if (!isset($lng) || (isset($lng) && !is_object($lng))) {
      require_once(DIR_WS_CLASSES . 'language.php');
      $lng = new language;
    }

    if (count($lng->catalog_languages) > 0) {
      reset($lng->catalog_languages);
      while (list($key, $value) = each($lng->catalog_languages)) {

        $url = 'http://www.janolaw.de/agb-service/shops/'.
               $this->user_id .'/'.
               $this->shop_id .'/'.
               str_replace('en', 'gb', $key) .'/';

        $content = get_external_content($url.$name.$mode.$this->format, '3', false);
        
        if (strpos($content, '404 Not Found') === false) {
          
          // save pdf
          $content_pdf = '';
          if ($this->pdf === true) {
            $content_pdf = get_external_content($url.$name.'.pdf', '3', false);
            if (strpos($content_pdf, '404 Not Found') !== false) {
              $content_pdf = '';
            } else {
              $filename = 'media/content/'. $value['directory'] . '_' . $name . '.pdf';
              $fp = @fopen(DIR_FS_CATALOG.$filename, 'w+');
              if (is_resource($fp)) {
                fwrite($fp, $content_pdf);
                fclose($fp);
                if ($this->format == 'html') {
                  $content .= '<br /><br /><a href="'.DIR_WS_CATALOG.$filename.'" target="_blank">PDF download</a>';            
                }            
              }
            }
          }
                    
          // save data
          if (strtolower(MODULE_JANOLAW_TYPE) == 'database') {
            // convert content
            $content = decode_utf8($content);

            // update data in table
            $sql_data_array = array('content_text' => $content,
                                    'content_file' => '');
            xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group='" . (int)$coID . "' and languages_id='".$value['id']."'");
          } else {
            // write content to file
            $filename = $key . '_' . $name . '.' . $this->format;
            $file = DIR_FS_CATALOG . 'media/content/'. $filename;
            $fp = @fopen($file, 'w+');
            if (is_resource($fp)) {
              fwrite($fp, $content);
              fclose($fp);
      
              // update data in table
              $sql_data_array = array('content_file' => $filename,
                                      'content_text' => '');
              xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group='" . (int)$coID . "' and languages_id='".$value['id']."'");
            }
          }
        }
      }
    }
  }
  
}
?>