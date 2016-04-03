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

require_once (DIR_FS_INC.'get_external_content.inc.php');

class janolaw_content {
  var $version = '3'; // version 3
  var $enabled = false;
  var $user_id;
  var $shop_id;
  var $format;
  
  
  function __construct() {
    $this->user_id = $this->get_configuration('MODULE_JANOLAW_USER_ID');
    $this->shop_id = $this->get_configuration('MODULE_JANOLAW_SHOP_ID');
    $this->enabled = $this->get_status();
    $this->format = strtolower($this->get_configuration('MODULE_JANOLAW_FORMAT'));

    $this->document_name = array(
      'DE' => array('legaldetails' => 'Impressum',
                    'terms' => 'AGB',
                    'revocation' => 'Widerrufsbelehrung',
                    'datasecurity' => 'Datenschutzerklaerung',
                    'withdrawal' => 'Muster-Widerrufsformular',
                    ),
      'GB' => array('legaldetails' => 'Imprint',
                    'terms' => 'General-Terms-and-Conditions',
                    'revocation' => 'Instructions-on-withdrawal',
                    'datasecurity' => 'Data-privacy-policy',
                    'withdrawal' => 'Model-withdrawal-form',
                    ),
      'FR' => array('legaldetails' => 'Mentions-legales',
                    'terms' => 'Conditions-Generales-de-Vente',
                    'revocation' => 'Informations-standardisees-sur-la-retractation',
                    'datasecurity' => 'Declaration-quant-a-la-protection-des-donnees',
                    'withdrawal' => 'Modele-de-formulaire-de-retractation',
                    ),
    );
    
    if ($this->enabled === true) {
      if (((MODULE_JANOLAW_LAST_UPDATED + MODULE_JANOLAW_UPDATE_INTERVAL) <= time()) || defined('RUN_MODE_ADMIN')) {
        
        $this->get_page_content('datasecurity', $this->get_configuration('MODULE_JANOLAW_TYPE_DATASECURITY'));
        $this->get_page_content('terms', $this->get_configuration('MODULE_JANOLAW_TYPE_TERMS'));
        $this->get_page_content('legaldetails', $this->get_configuration('MODULE_JANOLAW_TYPE_LEGALDETAILS'));
        $this->get_page_content('model-withdrawal-form', $this->get_configuration('MODULE_JANOLAW_TYPE_WITHDRAWL'));
        $this->get_page_content('revocation', $this->get_configuration('MODULE_JANOLAW_TYPE_REVOCATION'));
                
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='".xtc_db_input(time())."', last_modified = NOW() where configuration_key='MODULE_JANOLAW_LAST_UPDATED'");
      }
    }    
  }


  function get_status() {
    if (!defined('MODULE_JANOLAW_STATUS') || $this->get_configuration('MODULE_JANOLAW_STATUS') == 'False') {
      return false;
    }
    return true;
  }
  
  
  function get_configuration($key) {
    $configuration_query = xtc_db_query("SELECT configuration_value
                                           FROM ".TABLE_CONFIGURATION."
                                          WHERE configuration_key = '".$key."'");
    $configuration = xtc_db_fetch_array($configuration_query);
    
    return $configuration['configuration_value'];
  }
  
  
  function get_language($lang) {
    $avail_lang_array = array('de', 'gb', 'fr');
    
    $lang = str_replace('en', 'gb', $lang);
    if (!in_array($lang, $avail_lang_array)) {
      $lang = 'gb'; // default
    }
    
    return $lang;
  }


  function get_page_content($name, $coID = '') {
    global $lng;
        
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
        
        $language = $this->get_language($key);
        
        $url = 'http://www.janolaw.de/agb-service/shops/'.
               $this->user_id .'/'.
               $this->shop_id .'/'.
               $language .'/';

        $content = get_external_content($url.$name.$mode.$this->format, '3', false);
 
        if (strpos($content, '404 Not Found') === false) {
          
          // save pdf
          $content_pdf = '';
          $module_name = str_replace('model-withdrawal-form', 'withdrawal', $name);

          if ($module_name == 'withdrawal' && $this->get_configuration('MODULE_JANOLAW_WITHDRAWAL_COMBINE') == 'True') {
            $this->withdrawal_content[$key] = $content;
          }
          if ($module_name == 'revocation' && $this->get_configuration('MODULE_JANOLAW_WITHDRAWAL_COMBINE') == 'True') {
            $content .= '<br /><br />'.$this->withdrawal_content[$key];
          }  
          if ($this->get_configuration('MODULE_JANOLAW_PDF_'.strtoupper($module_name)) == 'True'
              || $this->get_configuration('MODULE_JANOLAW_MAIL_'.strtoupper($module_name)) == 'True'
              ) 
          {
            $content_pdf = get_external_content($url.$name.'.pdf', '3', false);
            if (strpos($content_pdf, '404 Not Found') !== false) {
              $content_pdf = '';
            } else {
              $filename = 'media/content/'. $this->document_name[strtoupper($language)][$module_name] . '.pdf';
              $fp = @fopen(DIR_FS_CATALOG.$filename, 'w+');
              if (is_resource($fp)) {
                fwrite($fp, $content_pdf);
                fclose($fp);
                if ($module_name == 'withdrawal' && $this->get_configuration('MODULE_JANOLAW_WITHDRAWAL_COMBINE') == 'True') {
                  $this->withdrawal_link[$key] = DIR_WS_CATALOG.$filename;
                }
                if (($this->format == 'html' 
                    && $this->get_configuration('MODULE_JANOLAW_PDF_'.strtoupper($module_name)) == 'True'
                    ) || isset($this->withdrawal_link[$key]))
                {
                  if ($module_name == 'revocation' 
                      && $this->get_configuration('MODULE_JANOLAW_WITHDRAWAL_COMBINE') == 'True' 
                      && isset($this->withdrawal_link[$key])
                      ) 
                  {
                    if ($this->get_configuration('MODULE_JANOLAW_PDF_'.strtoupper($module_name)) == 'True') {
                      $content .= '<br /><br /><a href="'.DIR_WS_CATALOG.$filename.'" target="_blank">PDF - '.$this->document_name[strtoupper($language)][$module_name].'</a>';
                    }
                    $content .= '<br /><a href="'.$this->withdrawal_link[$key].'" target="_blank">PDF - '.$this->document_name[strtoupper($language)]['withdrawal'].'</a>';
                  } else {
                    $content .= '<br /><br /><a href="'.DIR_WS_CATALOG.$filename.'" target="_blank">PDF - '.$this->document_name[strtoupper($language)][$module_name].'</a>';            
                  }
                }
              }
            }
          }
                    
          // save data
          if ($coID != '') {
            if (strtolower($this->get_configuration('MODULE_JANOLAW_TYPE')) == 'database') {
              // convert content
              $content = decode_utf8($content);

              // update data in table
              $sql_data_array = array('content_text' => $content,
                                      'content_file' => '');
              xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group='".(int)$coID."' and languages_id='".$value['id']."'");
            } else {
              // write content to file
              $filename = $this->document_name[strtoupper($language)][$name] . '.' . $this->format;
              $file = DIR_FS_CATALOG . 'media/content/'. $filename;
              $fp = @fopen($file, 'w+');
              if (is_resource($fp)) {
                fwrite($fp, $content);
                fclose($fp);
      
                // update data in table
                $sql_data_array = array('content_file' => $filename,
                                        'content_text' => '');
                xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group='".(int)$coID."' and languages_id='".$value['id']."'");
              }
            }
          }
        }
      }
    }
  }
  
}
?>