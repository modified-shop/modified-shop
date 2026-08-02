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
    
  // include needed classes
  require_once(DIR_FS_CATALOG.'includes/classes/modified_api.php');

  class internetmarke {
    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $version;
    var $_check;
    var $properties;
    
    var $carrier_name = 'Deutsche Post';
    var $carrier_tracking_link = 'https://www.deutschepost.de/sendung/simpleQueryResult.html?form.sendungsnummer=$1&form.einlieferungsdatum_tag=$3&form.einlieferungsdatum_monat=$4&form.einlieferungsdatum_jahr=$5';
    
    function __construct() {          
      $this->version = '1.22';
      $this->code = 'internetmarke';
      $this->title = MODULE_INTERNETMARKE_TEXT_TITLE;
      $this->description = MODULE_INTERNETMARKE_TEXT_DESCRIPTION;
      $this->sort_order = ((defined('MODULE_INTERNETMARKE_SORT_ORDER')) ? MODULE_INTERNETMARKE_SORT_ORDER : '');
      $this->enabled = ((defined('MODULE_INTERNETMARKE_STATUS') && MODULE_INTERNETMARKE_STATUS == 'true') ? true : false);
      $this->properties = array();
      if (defined('MODULE_INTERNETMARKE_STATUS')) {
        $this->properties['button_update'] = xtc_draw_form(
          'internetmarke_module_update',
          FILENAME_MODULE_EXPORT,
          'set=system&module='.$this->code.'&action=update',
          'post',
          'style="display:inline;"'
        ).xtc_button(BUTTON_UPDATE).'</form>';
      }
      if ($this->enabled) {
          $this->description .= '<hr><br>'.MODULE_INTERNETMARKE_TEXT_DESCRIPTION_UPLOAD;
          $this->description .= '<br>'.xtc_draw_form(
            'internetmarke_price_update',
            FILENAME_MODULE_EXPORT,
            xtc_get_all_get_params(array('action', 'subaction', 'module')).'action=save&subaction=im_update&module='.$this->code,
            'post'
          ).xtc_button(BUTTON_IM_UPDATE).'</form><br><hr>';
          if (MODULE_INTERNETMARKE_CARRIER_STATUS != 'true') {
            $this->description .= '<br>'.MODULE_INTERNETMARKE_TEXT_DESCRIPTION_CARRIER;
            $this->description .= '<br>'.xtc_draw_form(
              'internetmarke_carrier_install',
              FILENAME_MODULE_EXPORT,
              xtc_get_all_get_params(array('action', 'subaction', 'module')).'action=save&subaction=im_install&module='.$this->code,
              'post'
            ).xtc_button(BUTTON_IM_INSTALL).'</form><br><hr>';
          }
      }

    }

    function process($file) {
      global $messageStack;
      
      if (isset($_POST)
          && count($_POST) > 0
          && !isset($_GET['subaction'])
          )
      {
        $pageformats = $this->normalizeIntegerArray(isset($_POST['pageformats']) ? $_POST['pageformats'] : array());
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = '".xtc_db_input(implode(',', $pageformats))."'
                       WHERE configuration_key = 'MODULE_INTERNETMARKE_PAGEFORMATS'");
        
        xtc_db_query("UPDATE `internetmarke` SET SEL = 0");
        $prices = $this->normalizeIntegerArray(isset($_POST['price']) ? $_POST['price'] : array());
        if (count($prices) > 0) {
           xtc_db_query("UPDATE `internetmarke`
                            SET SEL = 1
                          WHERE PROID IN (".implode(',', $prices).")");
         
        }

        if (isset($_POST['configuration']['MODULE_INTERNETMARKE_CARRIER'])) {
          $carrier_array = $this->normalizeIntegerArray(array($_POST['configuration']['MODULE_INTERNETMARKE_CARRIER']));
          $carrier_id = count($carrier_array) > 0 ? $carrier_array[0] : 0;
          if ($carrier_id > 0 && $this->carrierExists($carrier_id) !== true) {
            $carrier_id = 0;
          }
          $this->configureCarrier($carrier_id);
        }
      }
      
      if (isset($_GET['subaction'])) {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
          return;
        }

        switch ($_GET['subaction']) {
          case 'im_update':
            $filename = DIR_FS_CATALOG.'cache/ppl.csv';
            $downloaded = false;
            
            modified_api::reset();
            $response = modified_api::request('internetmarke/pplupdate');
            
            if ($response != null
                && is_array($response)
                && isset($response['requestURL'])
                && is_scalar($response['requestURL'])
                )
            {
              // include needed functions
              require_once (DIR_FS_INC.'get_external_content.inc.php');

              $request_url = trim((string)$response['requestURL']);
              $request_url_scheme = ($request_url != '') ? parse_url($request_url, PHP_URL_SCHEME) : false;
              if (filter_var($request_url, FILTER_VALIDATE_URL) !== false
                  && is_string($request_url_scheme)
                  && strtolower($request_url_scheme) == 'https'
                  )
              {
                $ppl_file_content = get_external_content($request_url, 3, false);
                if (is_string($ppl_file_content)
                    && trim($ppl_file_content) != ''
                    )
                {
                  $written_bytes = file_put_contents($filename, $ppl_file_content);
                  $downloaded = ($written_bytes === strlen($ppl_file_content));
                }
              }
            }

            $products = array();
            if ($downloaded === true && is_file($filename)) {
              if (($handle = fopen($filename, "r")) !== false) {
                while (($data = fgetcsv($handle, 4096, ";")) !== false) {
                  if (!isset($data[2], $data[4], $data[5])) {
                    continue;
                  }

                  $product_id = trim($data[2]);
                  $product_price = str_replace(',', '.', trim($data[5]));
                  if (!ctype_digit($product_id)
                      || (int)$product_id < 1
                      || !is_numeric($product_price)
                      || (float)$product_price <= 0
                      )
                  {
                    continue;
                  }

                  $products[(int)$product_id] = array(
                    'PROID' => (int)$product_id,
                    'PRODNAME' => encode_utf8($data[4], 'ISO-8859-15'),
                    'PROPR' => (float)$product_price,
                  );
                }
                fclose($handle);
              }

              if (count($products) > 0) {
                $import_success = true;
                foreach ($products as $product) {
                  $import_query = xtc_db_query("INSERT INTO `internetmarke` (`PROID`, `PRODNAME`, `PROPR`)
                                                VALUES ('".(int)$product['PROID']."',
                                                        '".xtc_db_input($product['PRODNAME'])."',
                                                        '".xtc_db_input($product['PROPR'])."')
                                                ON DUPLICATE KEY UPDATE
                                                  `PRODNAME` = VALUES(`PRODNAME`),
                                                  `PROPR` = VALUES(`PROPR`)");
                  if ($import_query === false) {
                    $import_success = false;
                    break;
                  }
                }

                if ($import_success === true) {
                  $cleanup_query = xtc_db_query("DELETE FROM `internetmarke`
                                                      WHERE PROID NOT IN (".implode(',', array_keys($products)).")");
                  $messageStack->add_session(
                    $cleanup_query !== false ? MODULE_INTERNETMARKE_TEXT_UPDATE_SUCCESS : MODULE_INTERNETMARKE_TEXT_UPDATE_ERROR,
                    $cleanup_query !== false ? 'success' : 'error'
                  );
                } else {
                  $messageStack->add_session(MODULE_INTERNETMARKE_TEXT_UPDATE_ERROR, 'error');
                }
              } else {
                $messageStack->add_session(MODULE_INTERNETMARKE_TEXT_UPDATE_ERROR, 'error');
              }
              unlink($filename);
            } else {
              $messageStack->add_session(MODULE_INTERNETMARKE_TEXT_UPDATE_ERROR, 'error');
            }
            break;
          
          case 'im_install':
            if (MODULE_INTERNETMARKE_CARRIER_STATUS != 'true') {
              $carrier_id = $this->getCarrierId();
              if ($carrier_id < 1) {
                $sql_data_array = array(
                  'carrier_name' => $this->carrier_name,
                  'carrier_tracking_link' => $this->carrier_tracking_link,
                  'carrier_date_added' => 'now()',
                );
                if (xtc_db_perform(TABLE_CARRIERS, $sql_data_array) !== false) {
                  $carrier_id = (int)xtc_db_insert_id();
                  if ($this->carrierExists($carrier_id) !== true) {
                    $carrier_id = 0;
                  }
                }
              }
              $this->configureCarrier($carrier_id);
            }
            break;
        }
      }
    }

    function display() {
      global $messageStack;

      $formats_string = '';
      if (MODULE_INTERNETMARKE_PORTO_USER != ''
          && MODULE_INTERNETMARKE_PORTO_PASS != ''
          )
      {
        require_once(DIR_FS_EXTERNAL.'dhl/DHLInternetmarke.php');
        $DHLInternetmarke = new DHLInternetmarke(array());
        $formats_array = explode(',', MODULE_INTERNETMARKE_PAGEFORMATS);
        $result = $DHLInternetmarke->getPageFormats();
                    
        if (isset($result['formats'])
            && is_array($result['formats'])
            && count($result['formats']) > 0
            )
        {
          foreach ($result['formats'] as $data) {
            $formats_string .= xtc_draw_checkbox_field('pageformats[]', $data['id'], in_array($data['id'], $formats_array)).' '.encode_htmlspecialchars($data['text']).'<br>';
          }
        } elseif (isset($result['message'])
                  && is_array($result['message'])
                  && count($result['message']) > 0
                  )
        {
          $formats_string .= '<div class="error_message">';
          foreach ($result['message'] as $error_array) {
            if (!is_array($error_array)) {
              continue;
            }
            foreach ($error_array as $error) {
              if (is_scalar($error)) {
                $formats_string .= encode_htmlspecialchars((string)$error).'<br>';
              }
            }
          }
          $formats_string .= '</div>';
        }
      }
      
      $price_string = '';
      $price_query = xtc_db_query("SELECT *
                                     FROM `internetmarke`");
      while ($price = xtc_db_fetch_array($price_query)) {
        $price_string .= xtc_draw_checkbox_field('price[]', $price['PROID'], ($price['SEL'] != 0)).' '.encode_htmlspecialchars($price['PRODNAME']).'<br>';
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

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_INTERNETMARKE_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_INTERNETMARKE_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_INTERNETMARKE_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_PORTO_USER', '',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_INTERNETMARKE_PORTO_PASS', '',  '6', '1', 'xtc_cfg_password_field_module(', 'xtc_cfg_display_password', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_CARRIER_STATUS', 'false',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_INTERNETMARKE_CARRIER', '',  '6', '1', 'xtc_cfg_select_carrier(', 'xtc_cfg_display_carrier', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_PAGEFORMATS', '',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_INTERNETMARKE_LOGLEVEL', 'NONE',  '6', '1', 'xtc_cfg_select_option(array(\'NONE\', \'INFO\', \'ERROR\'), ', now())");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_COMPANY', '',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_FIRSTNAME', '',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_LASTNAME', '',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_SUBURB', '',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_STREET', '',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_PLZ', '',  '6', '1', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_INTERNETMARKE_CITY', '',  '6', '1', now())");

      $this->installDatabase();

      $carrier_id = $this->getConfiguredCarrierId();
      if ($carrier_id < 1) {
        $carrier_id = $this->getCarrierId();
      }
      $this->configureCarrier($carrier_id);
    }

    function update() {
      if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
      }

      $check_query = xtc_db_query("SELECT configuration_key
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key = 'MODULE_INTERNETMARKE_LOGLEVEL'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_INTERNETMARKE_LOGLEVEL', 'NONE', '6', '1', 'xtc_cfg_select_option(array(\'NONE\', \'INFO\', \'ERROR\'), ', now())");
      }

      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET set_function = 'xtc_cfg_password_field_module(',
                           use_function = 'xtc_cfg_display_password'
                     WHERE configuration_key = 'MODULE_INTERNETMARKE_PORTO_PASS'");
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION."
                          WHERE configuration_key = 'MODULE_INTERNETMARKE_PARTNER_KEY_PHASE'");

      $this->installDatabase();

      $carrier_id = $this->getConfiguredCarrierId();
      if ($carrier_id < 1) {
        $carrier_id = $this->getCarrierId();
      }
      $this->configureCarrier($carrier_id);
    }

    function installDatabase() {
      xtc_db_query("CREATE TABLE IF NOT EXISTS `internetmarke` (
                      `PROID` int(11) NOT NULL,
                      `PRODNAME` varchar(128) NOT NULL,
                      `PROPR` double(15,4) NOT NULL,
                      `SEL` tinyint(1) NOT NULL DEFAULT '0',
                      UNIQUE KEY `PROID` (`PROID`),
                      KEY `SEL` (`SEL`)
                    )");

      $index_query = xtc_db_query("SHOW INDEX FROM `internetmarke` WHERE Key_name = 'SEL'");
      if (xtc_db_num_rows($index_query) < 1) {
        xtc_db_query("ALTER TABLE `internetmarke` ADD INDEX `SEL` (`SEL`)");
      }

      $table_array = array(
        array('column' => 'external', 'default' => 'INT(1) NOT NULL'),
        array('column' => 'im_orders_id', 'default' => 'VARCHAR(80) DEFAULT NULL'),
        array('column' => 'im_url', 'default' => 'VARCHAR(512)'),
        array('column' => 'im_voucher_id', 'default' => 'VARCHAR(80) DEFAULT NULL'),
        array('column' => 'im_retoure_transaction_id', 'default' => 'VARCHAR(80) DEFAULT NULL'),
        array('column' => 'im_retoure_id', 'default' => 'VARCHAR(80) DEFAULT NULL'),
      );
      foreach ($table_array as $table) {
        $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ORDERS_TRACKING." LIKE '".xtc_db_input($table['column'])."'");
        if (xtc_db_num_rows($check_query) < 1) {
          xtc_db_query("ALTER TABLE ".TABLE_ORDERS_TRACKING." ADD ".$table['column']." ".$table['default']);
        }
      }

      $cart_column_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ORDERS_TRACKING." LIKE 'im_orders_id'");
      if (xtc_db_num_rows($cart_column_query) > 0) {
        $cart_column = xtc_db_fetch_array($cart_column_query);
        if (!isset($cart_column['Type']) || strtolower($cart_column['Type']) != 'varchar(80)') {
          xtc_db_query("ALTER TABLE ".TABLE_ORDERS_TRACKING." MODIFY im_orders_id VARCHAR(80) DEFAULT NULL");
        }
      }
    }

    function getCarrierId() {
      $check_query = xtc_db_query("SELECT carrier_id
                                     FROM ".TABLE_CARRIERS."
                                    WHERE carrier_name = '".xtc_db_input($this->carrier_name)."'
                                          OR carrier_tracking_link = '".xtc_db_input($this->carrier_tracking_link)."'
                                    LIMIT 1");
      if (xtc_db_num_rows($check_query) > 0) {
        $carrier = xtc_db_fetch_array($check_query);
        return (int)$carrier['carrier_id'];
      }

      return 0;
    }

    function getConfiguredCarrierId() {
      if (!defined('MODULE_INTERNETMARKE_CARRIER') || (int)MODULE_INTERNETMARKE_CARRIER < 1) {
        return 0;
      }

      return $this->carrierExists((int)MODULE_INTERNETMARKE_CARRIER) ? (int)MODULE_INTERNETMARKE_CARRIER : 0;
    }

    function carrierExists($carrier_id) {
      $check_query = xtc_db_query("SELECT carrier_id
                                     FROM ".TABLE_CARRIERS."
                                    WHERE carrier_id = '".(int)$carrier_id."'");
      return xtc_db_num_rows($check_query) > 0;
    }

    function configureCarrier($carrier_id) {
      $carrier_id = (int)$carrier_id;
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".(($carrier_id > 0) ? $carrier_id : '')."'
                     WHERE configuration_key = 'MODULE_INTERNETMARKE_CARRIER'");
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".(($carrier_id > 0) ? 'true' : 'false')."'
                     WHERE configuration_key = 'MODULE_INTERNETMARKE_CARRIER_STATUS'");
    }

    function normalizeIntegerArray($values) {
      $result = array();
      if (!is_array($values)) {
        return $result;
      }

      foreach ($values as $value) {
        if (is_int($value)) {
          $value = (int)$value;
        } elseif (is_string($value) && ctype_digit($value)) {
          $value = (int)$value;
        } else {
          continue;
        }

        if ($value > 0) {
          $result[$value] = $value;
        }
      }

      return array_values($result);
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_INTERNETMARKE_%'");
      xtc_db_query("DROP TABLE `internetmarke`");
    }

    function keys() {
      return array(
        'MODULE_INTERNETMARKE_STATUS',
        'MODULE_INTERNETMARKE_PORTO_USER',
        'MODULE_INTERNETMARKE_PORTO_PASS',
        'MODULE_INTERNETMARKE_CARRIER',
        'MODULE_INTERNETMARKE_LOGLEVEL',

        'MODULE_INTERNETMARKE_COMPANY',
        'MODULE_INTERNETMARKE_FIRSTNAME',
        'MODULE_INTERNETMARKE_LASTNAME',
        'MODULE_INTERNETMARKE_STREET',
        'MODULE_INTERNETMARKE_SUBURB',
        'MODULE_INTERNETMARKE_PLZ',
        'MODULE_INTERNETMARKE_CITY',
      );
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
      if (xtc_db_num_rows($carriers_query) > 0) {
        $carrier = xtc_db_fetch_array($carriers_query);
        return $carrier['carrier_name'];
      }
    }
  }
