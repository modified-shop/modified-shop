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

class avalex_update {

  var $enabled = false;
  var $apikey = false;
  var $domain = false;
  var $url = 'https://avalex.de/';
  
  
  function __construct() {
    $this->apikey = defined('MODULE_AVALEX_API') ? MODULE_AVALEX_API : '';
    $this->domain = defined('MODULE_AVALEX_DOMAIN') ? MODULE_AVALEX_DOMAIN : '';
    $this->enabled = ((defined('MODULE_AVALEX_STATUS') && MODULE_AVALEX_STATUS == 'True') ? true : false);

    $this->map = array(
      'AGB' => 'bedingungen',
      'DSE' => 'datenschutzerklaerung',
      'WRB' => 'widerruf',
      'IMP' => 'impressum',
    );
  }
  
  
  function check_update() {
    if ($this->enabled === true) {
      if ((((int)MODULE_AVALEX_LAST_UPDATED + 21600) <= time()) || defined('RUN_MODE_ADMIN')) {        
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                         SET configuration_value='" . (int) time() . "', 
                             last_modified = NOW() 
                       WHERE configuration_key='MODULE_AVALEX_LAST_UPDATED'");

        $this->update_content();
      }
    }
  }


  function update_content() {
    $content_groups_array = array();
    foreach ($this->map as $map => $endpoint) {
      $content_groups_array[] = constant('MODULE_AVALEX_TYPE_'.strtoupper($map));
    }

    $lang_query = xtc_db_query("SELECT *
                                  FROM ".TABLE_LANGUAGES."
                                 WHERE status = 1");
    while ($lang = xtc_db_fetch_array($lang_query)) {
      $content_array = array();
      $content_query = xtc_db_query("SELECT *
                                       FROM ".TABLE_CONTENT_MANAGER."
                                      WHERE content_group IN (".implode(',', $content_groups_array).")
                                        AND languages_id = '".(int)$lang['languages_id']."'");
      while ($content = xtc_db_fetch_array($content_query)) {
        $content_array[$content['content_group']] = $content;
      }

      foreach ($this->map as $map => $endpoint) {
        $result = get_external_content($this->url.'avx-'.$endpoint.'?apikey='.$this->apikey.'&lang='.$lang['code'].'&domain='.$this->domain, '3', false);
        
        if (trim($result) != '') {
          $content = $content_array[constant('MODULE_AVALEX_TYPE_'.strtoupper($map))];
        
          $sql_data_array = array(
            'content_file' => '',
            'content_text' => decode_utf8($result)
          );

          if (MODULE_AVALEX_TYPE == 'File') {
            $file = DIR_FS_CATALOG . 'media/content/avalex_'.strtolower($endpoint).'_'.$lang['code'].'.html';
            $fp = @fopen($file, 'w+');
            if (is_resource($fp)) {
              fwrite($fp, $result);
              fclose($fp);
            }
            $sql_data_array = array(
              'content_text' => '',
              'content_file' => 'avalex_'.strtolower($endpoint).'_'.$lang['code'].'.html'
            );
          }
          xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_id = '".$content['content_id']."'");
        }
      }
    }
    
    return true;
  }
      
}
