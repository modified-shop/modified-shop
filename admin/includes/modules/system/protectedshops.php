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

require_once(DIR_FS_EXTERNAL.'protectedshops/protectedshops_update.php');

class protectedshops {
  var $code;
  var $title;
  var $sort_order;
  var $enabled;
  var $description;
  var $extended_description;
  var $update;
  var $content;

  function __construct() {
    $this->code = 'protectedshops';
    $this->title = MODULE_PROTECTEDSHOPS_TEXT_TITLE;
    $this->description = MODULE_PROTECTEDSHOPS_TEXT_DESCRIPTION;
    $this->enabled = ((MODULE_PROTECTEDSHOPS_STATUS == 'true') ? true : false);
  }
 
  function init_ps() {
    $this->update = new protectedshops_update();
    $params = array('Request' => 'GetDocumentInfo',
                    'ShopId' => $this->update->token,
                    );
    $this->content = $this->update->request_document($params); 
  }
  
  function process() {
    if ($this->enabled === true && $_POST['export'] == 'yes') {
      $this->init_ps();
      $this->update->check_update();
    }
  }

  // display
  function display() {
    $interval_array = array(array('id' => '86400', 'text' => '24 Stunden'),
                            array('id' => '43200', 'text' => '12 Stunden'),
                            array('id' => '21600', 'text' => '6 Stunden'),
                           );
    
    return array('text' => '<br/><b>'.MODULE_PROTECTEDSHOPS_UPDATE_INTERVAL_TITLE.'</b>
                            <br/>'.MODULE_PROTECTEDSHOPS_UPDATE_INTERVAL_DESC.'<br/>'.
                            xtc_draw_pull_down_menu('configuration[MODULE_PROTECTEDSHOPS_UPDATE_INTERVAL]', $interval_array, MODULE_PROTECTEDSHOPS_UPDATE_INTERVAL).'<br />'.

                            '<br/><b>'.MODULE_PROTECTEDSHOPS_ACTION_TITLE.'</b><br/>'.
                            MODULE_PROTECTEDSHOPS_ACTION_DESC.'<br>'.
                          	xtc_draw_radio_field('export', 'no', true).TEXT_SAVE.'<br>'.
                            xtc_draw_radio_field('export', 'yes', false).TEXT_PROCESS.'<br>'.

                           '<br /><div align="center">' . xtc_button('OK') .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=protectedshops')) . "</div>");
  }

  // check
  function check() {    
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PROTECTEDSHOPS_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  // install
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_TOKEN', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_TYPE', 'Database',  '6', '4', 'xtc_cfg_select_option(array(\'File\', \'Database\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_FORMAT', 'Html',  '6', '5', 'xtc_cfg_select_option(array(\'Html\', \'HtmlLite\', \'Text\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_AUTOUPDATE', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_UPDATE_INTERVAL', '86400',  '6', '6', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_LAST_UPDATED', '',  '6', '6', '', now())");

    // dynamic
    $this->auto_install();
  }
  
  // autoinstall
  function auto_install() {
    $this->init_ps();
    
    // Documents
    if (isset($this->content['DocumentDate']) && is_array($this->content['DocumentDate'])) {
      foreach ($this->content['DocumentDate'] as $type => $date) {
        $check_type_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PROTECTEDSHOPS_TYPE_".strtoupper($type)."'");
        if (xtc_db_num_rows($check_type_query) < 1) {
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_TYPE_".strtoupper($type)."', '',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
        }
        $check_pdf_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PROTECTEDSHOPS_PDF_".strtoupper($type)."'");
        if (xtc_db_num_rows($check_pdf_query) < 1) {
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_PDF_".strtoupper($type)."', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        }
        $check_pdf_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PROTECTEDSHOPS_ERROR_COUNT_".strtoupper($type)."'");
        if (xtc_db_num_rows($check_pdf_query) < 1) {
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_ERROR_COUNT_".strtoupper($type)."', '0',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PROTECTEDSHOPS_ERROR_COUNT_PDF_".strtoupper($type)."', '0',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        }
      }
    }
  }
  
  // remove
  function remove() {
    $keys = $this->keys();
    $keys[] = 'MODULE_PROTECTEDSHOPS_UPDATE_INTERVAL';
    $keys[] = 'MODULE_PROTECTEDSHOPS_LAST_UPDATED';
    
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $keys) . "')");

  }

  // keys
  function keys() {
    // dynamic
    if ($this->check() > 0) {
      $this->auto_install();
    }
    
    $keys = array('MODULE_PROTECTEDSHOPS_STATUS', 
                  'MODULE_PROTECTEDSHOPS_TOKEN', 
                  'MODULE_PROTECTEDSHOPS_TYPE',
                  'MODULE_PROTECTEDSHOPS_FORMAT',                 
                 );
    
    if (isset($this->content['DocumentDate']) && is_array($this->content['DocumentDate'])) {
      $i=0;
      foreach ($this->content['DocumentDate'] as $type => $date) {
        define('MODULE_PROTECTEDSHOPS_TYPE_'.strtoupper($type).'_TITLE', '<hr noshade>' . (($i==0) ? 'Hinweis: </b>Die PDF Dateien k&ouml;nnen auch als Anhang zur Bestellbest&auml;tigung mitgesendet werden. Dazu einfach den Speicherort in den eMail Optionen bei "E-Mail Anh&auml;nge f&uuml;r Bestellungen" verwenden.<br/><br/><b>' : '') . 'Rechtstext '.$type);
        define('MODULE_PROTECTEDSHOPS_TYPE_'.strtoupper($type).'_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
        define('MODULE_PROTECTEDSHOPS_PDF_'.strtoupper($type).'_TITLE',  $type.' als PDF');
        define('MODULE_PROTECTEDSHOPS_PDF_'.strtoupper($type).'_DESC', 'Angabe ob der '.$type.' als PDF verf&uuml;gbar sein soll.<br/>Speicherort: /media/content/ps_'.strtolower($type).'.pdf');
        $keys[] = 'MODULE_PROTECTEDSHOPS_TYPE_'.strtoupper($type);
        $keys[] = 'MODULE_PROTECTEDSHOPS_PDF_'.strtoupper($type);
        $i++;
      }
    }

    $keys[] = 'MODULE_PROTECTEDSHOPS_AUTOUPDATE';
    
    return $keys;
  }
}

?>