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
  var $m_user_id = false;
  var $m_shop_id = false;
  var $enabled = false;
  
  function janolaw_content() {
    if(defined('MODULE_JANOLAW_USER_ID')) {
      $this->m_user_id = xtc_cleanName(MODULE_JANOLAW_USER_ID);
    }
    if(defined('MODULE_JANOLAW_SHOP_ID')) {
      $this->m_shop_id = xtc_cleanName(MODULE_JANOLAW_SHOP_ID);
    }
    $this->enabled = ((MODULE_JANOLAW_STATUS == 'True') ? true : false);
    
    if($this->enabled) {
      if (((MODULE_JANOLAW_LAST_UPDATED + MODULE_JANOLAW_UPDATE_INTERVAL) <= time()) || defined('RUN_MODE_ADMIN')) {
        $this->get_page_content('datenschutzerklaerung', 2);
        $this->get_page_content('agb', 3);
        $this->get_page_content('impressum', 4);
        $this->get_page_content('widerrufsbelehrung', REVOCATION_ID);
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . xtc_db_input(time()) . "', last_modified = NOW() where configuration_key='MODULE_JANOLAW_UPDATED'");
      }
    }
  }


  function get_status() {
    if(!defined('MODULE_JANOLAW_STATUS') || MODULE_JANOLAW_STATUS == 'False') {
      return false;
    }
    return true;
  }


  function get_page_content($name, $coID='') {
    
    $mode = '';
    $format = strtolower(MODULE_JANOLAW_FORMAT);
    if ($format == 'html') {
      $mode = '_include';
    }
    
    $url = 'http://www.janolaw.de/agb-service/shops/'.
           $this->m_user_id .'/'.
           $this->m_shop_id .'/'.
           $name.
           $mode.'.'.
           $format;

    $content = get_external_content($url, '3', false);
        
    if (strtolower(MODULE_JANOLAW_TYPE) == 'database') {
      // update data in table
      $sql_data_array = array('content_text' => $content,
                              'content_file' => '');
      xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group='" . (int)$coID . "' and languages_id='2'");
    } else {
      // write content to file
      $file = DIR_FS_CATALOG . 'media/content/'. $name .'.'. $format;
      $fp = @fopen($file, 'w+');
      if (is_resource($fp)) {
        fwrite($fp, $content);
        fclose($fp);
      }
      
      // update data in table
      $sql_data_array = array('content_file' => $name .'.'. $format,
                              'content_text' => '');
      xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_group='" . (int)$coID . "' and languages_id='2'");
    }
  }
}
?>