<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
  
  
  class internetmarke
  {
      var $code, $title, $description, $enabled;

      function __construct() 
      {          
          $this->code = 'internetmarke';
          $this->title = MODULE_INTERNETMARKE_TEXT_TITLE;
          $this->description = MODULE_INTERNETMARKE_TEXT_DESCRIPTION;
          $this->sort_order = ((defined('MODULE_INTERNETMARKE_SORT_ORDER')) ? MODULE_INTERNETMARKE_SORT_ORDER : '');
          $this->enabled = ((defined('MODULE_INTERNETMARKE_STATUS') && MODULE_INTERNETMARKE_STATUS == 'true') ? true : false);
          if ($this->enabled) {
              $this->description .= '<hr><br>'.MODULE_INTERNETMARKE_TEXT_DESCRIPTION_UPLOAD;
              $this->description .= '<br><a class="button" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, xtc_get_all_get_params(array('action', 'subaction', 'module')).'action=save&subaction=im_update&module='.$this->code).'" />'.BUTTON_IM_UPDATE.'</a><br><hr>';
              if (MODULE_INTERNETMARKE_CARRIER_STATUS != 'true') {
                $this->description .= '<br>'.MODULE_INTERNETMARKE_TEXT_DESCRIPTION_CARRIER;
                $this->description .= '<br><a class="button" href="'.xtc_href_link(FILENAME_MODULE_EXPORT, xtc_get_all_get_params(array('action', 'subaction', 'module')).'action=save&subaction=im_install&module='.$this->code).'" />'.BUTTON_IM_INSTALL.'</a><br><hr>';
              }
          }
      }

      function process($file) 
      {
          if (isset($_POST) && count($_POST) > 0) {
            if (isset($_POST['pageformats'])) {
              xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                               SET configuration_value = '".implode(',', $_POST['pageformats'])."'
                             WHERE configuration_key = 'MODULE_INTERNETMARKE_PAGEFORMATS'");
            }
            
            xtc_db_query("UPDATE `internetmarke` SET SEL = 0");
            if (isset($_POST['price'])) {
               xtc_db_query("UPDATE `internetmarke`
                                SET SEL = 1
                              WHERE PROID IN (".implode(',', $_POST['price']).")");
             
            }
          }
          
          if (isset($_GET['subaction'])) {
            switch ($_GET['subaction']) {
              case 'im_update':
                $ppl = get_external_content('https://www.modified-shop.org/downloads/internetmarke/ppl.csv', 3, false);
                file_put_contents(DIR_FS_CATALOG.'import/ppl.csv', $ppl);

                if (($handle = fopen(DIR_FS_CATALOG.'import/ppl.csv', "r")) !== false) {
                  xtc_db_query("TRUNCATE `internetmarke`");
                  while (($data = fgetcsv($handle, 4096, ";")) !== false) {
                    if ($data[2] != '' && is_numeric($data[2])) {
                      $sql_data_array = array(
                        'PROID' => (int)$data[2],
                        'PRODNAME' => utf8_encode($data[4]),
                        'PROPR' => str_replace(',', '.', $data[5]),
                      );
                      xtc_db_perform('internetmarke', $sql_data_array);             
                    }
                  }
                  fclose($handle);
                }
                unlink(DIR_FS_CATALOG.'import/ppl.csv');
                break;
              
              case 'im_install':
                if (MODULE_INTERNETMARKE_CARRIER_STATUS != 'true') {  
                  $sql_data_array = array(
                    'carrier_name' => 'Deutsche Post',
                    'carrier_tracking_link' => 'https://www.deutschepost.de/sendung/simpleQueryResult.html?form.sendungsnummer=$1&form.einlieferungsdatum_tag=$3&form.einlieferungsdatum_monat=$4&form.einlieferungsdatum_jahr=$5',
                    'carrier_date_added' => 'now()',
                  );
                  xtc_db_perform(TABLE_CARRIERS, $sql_data_array);
                  $carrier_id = xtc_db_insert_id();
            
                  xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                                   SET configuration_value = '".(int)$carrier_id."'
                                 WHERE configuration_key = 'MODULE_INTERNETMARKE_CARRIER'");

                  xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                                   SET configuration_value = 'true'
                                 WHERE configuration_key = 'MODULE_INTERNETMARKE_CARRIER_STATUS'");
                }
                break;
            }
          }
      }

      function display() 
      {
          global $messageStack;
          
          require_once(DIR_FS_EXTERNAL.'internetmarke/internetmarke.php');
          $internetmarke = new mod_internetmarke();
                    
          $formats_string = '';
          if (MODULE_INTERNETMARKE_PORTO_USER != ''
              && MODULE_INTERNETMARKE_PORTO_PASS != ''
              && !$internetmarke->getError()
              )
          {
            $formats_array = explode(',', MODULE_INTERNETMARKE_PAGEFORMATS);
            $PageFormats = $internetmarke->getPageFormats();
            foreach ($PageFormats as $data) {
              $formats_string .= xtc_draw_checkbox_field('pageformats[]', $data['id'], in_array($data['id'], $formats_array)).' '.$data['text'].'<br>';
            }
          }
          
          $price_string = '';
          $price_query = xtc_db_query("SELECT *
                                         FROM `internetmarke`");
          while ($price = xtc_db_fetch_array($price_query)) {
            $price_string .= xtc_draw_checkbox_field('price[]', $price['PROID'], ($price['SEL'] != 0)).' '.$price['PRODNAME'].'<br>';
          }
          
          return array(
            'text' => (($formats_string != '') ? 
                        MODULE_INTERNETMARKE_PAGEFORMAT_TITLE.
                        MODULE_INTERNETMARKE_PAGEFORMAT_DESC.
                        $formats_string : '').
                      (($price_string != '') ? 
                        MODULE_INTERNETMARKE_PRICE_TITLE.
                        MODULE_INTERNETMARKE_PRICE_DESC.
                        $price_string : '').
                      '<br>' . xtc_button(BUTTON_REVIEW_APPROVE) . '&nbsp;' .
                      xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
          );
      }

      function check() 
      {
          if(!isset($this->_check)) {
            $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_INTERNETMARKE_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
          }
          return $this->_check;
      }

      function install() 
      {
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_INTERNETMARKE_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_PARTNER_KEY_PHASE', '1',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_PORTO_USER', '',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_PORTO_PASS', '',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_CARRIER_STATUS', 'false',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_INTERNETMARKE_CARRIER', '',  '6', '1', 'xtc_cfg_select_carrier(', 'xtc_cfg_display_carrier', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_PAGEFORMATS', '',  '6', '1', now())");

          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_COMPANY', '',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_FIRSTNAME', '',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_LASTNAME', '',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_SUBURB', '',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_STREET', '',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_PLZ', '',  '6', '1', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_CITY', '',  '6', '1', now())");
          $this->install_db();
      }

      function remove()
      {
          xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_INTERNETMARKE_%'");
          $this->uninstall_db();
      }

      function keys() 
      {
          return array(
            'MODULE_INTERNETMARKE_STATUS',
            'MODULE_INTERNETMARKE_PORTO_USER',
            'MODULE_INTERNETMARKE_PORTO_PASS',
            'MODULE_INTERNETMARKE_CARRIER',

            'MODULE_INTERNETMARKE_COMPANY',
            'MODULE_INTERNETMARKE_FIRSTNAME',
            'MODULE_INTERNETMARKE_LASTNAME',
            'MODULE_INTERNETMARKE_STREET',
            'MODULE_INTERNETMARKE_SUBURB',
            'MODULE_INTERNETMARKE_PLZ',
            'MODULE_INTERNETMARKE_CITY',
          );
      }
      
      function install_db() 
      {
          xtc_db_query("CREATE TABLE IF NOT EXISTS `internetmarke` (
                          `PROID` int(11) NOT NULL,
                          `PRODNAME` varchar(128) NOT NULL,
                          `PROPR` double(15,4) NOT NULL,
                          `SEL` tinyint(1) NOT NULL DEFAULT '0',
                          UNIQUE KEY `PROID` (`PROID`)
                        )");

          $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ORDERS_TRACKING." LIKE 'im_%'");
          if (xtc_db_num_rows($check_query) < 1) {
            xtc_db_query("ALTER TABLE `" . TABLE_ORDERS_TRACKING . "` ADD `im_orders_id` INT(11);");
            xtc_db_query("ALTER TABLE `" . TABLE_ORDERS_TRACKING . "` ADD `im_url` VARCHAR(512);");
          }
      }
      
      function uninstall_db() 
      {
          xtc_db_query("DROP TABLE `internetmarke`");
          xtc_db_query("ALTER TABLE " . TABLE_ORDERS_TRACKING . " DROP `im_orders_id`;");
          xtc_db_query("ALTER TABLE " . TABLE_ORDERS_TRACKING . " DROP `im_url`;");
      }
  }
  
  if (!function_exists('xtc_cfg_select_carrier')) {
    function xtc_cfg_select_carrier($cfg_value, $cfg_key) {
      $carriers = array();
      $carriers_query = xtc_db_query("SELECT carrier_id, 
                                             carrier_name 
                                        FROM ".TABLE_CARRIERS." 
                                    ORDER BY carrier_sort_order ASC");
      while ($carrier = xtc_db_fetch_array($carriers_query)) {
        $carriers[] = array('id' => $carrier['carrier_id'], 'text' => $carrier['carrier_name']);
      }

      return xtc_draw_pull_down_menu('configuration['.$cfg_key.']', $carriers, $cfg_value);
    }    
  }

  if (!function_exists('xtc_cfg_display_carrier')) {
    function xtc_cfg_display_carrier($cfg_value) {
      $carriers = array();
      $carriers_query = xtc_db_query("SELECT carrier_name 
                                        FROM ".TABLE_CARRIERS." 
                                       WHERE carrier_id = '".(int)$cfg_value."'");
      $carrier = xtc_db_fetch_array($carriers_query);
      return $carrier['carrier_name'];
    }    
  }
?>