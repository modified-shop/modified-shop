<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

define('MODULE_API_IT_RECHT_KANZLEI_TEXT_TITLE', 'IT-Recht Kanzlei Auto Updater');
define('MODULE_API_IT_RECHT_KANZLEI_TEXT_DESCRIPTION', 'IT-Recht Kanzlei - Auto Updater f&uuml;r automatische Rechtstexte<br/><br/><b>WICHTIG:</b> vor der Nutzung des Moduls muss die Zuordnung der Content Seiten gemacht werden.<hr noshade>');
define('MODULE_API_IT_RECHT_KANZLEI_STATUS_TITLE', 'Status');
define('MODULE_API_IT_RECHT_KANZLEI_STATUS_DESC', 'Modulstatus');
define('MODULE_API_IT_RECHT_KANZLEI_TOKEN_TITLE', 'Authentifizierungs-Token');
define('MODULE_API_IT_RECHT_KANZLEI_TOKEN_DESC', 'Authentifizierungs-Token den Sie der IT-Recht Kanzlei mitteilen.');
define('MODULE_API_IT_RECHT_KANZLEI_VERSION_TITLE', 'API Version');
define('MODULE_API_IT_RECHT_KANZLEI_VERSION_DESC', 'Diese ist nur zu &auml;ndern, wenn sie von der IT-Recht Kanzlei dazu aufgefordert werden. (Standard: 1.0)');
define('MODULE_API_IT_RECHT_KANZLEI_TYPE_AGB_TITLE', '<hr noshade>Rechtstext AGB');
define('MODULE_API_IT_RECHT_KANZLEI_TYPE_AGB_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_API_IT_RECHT_KANZLEI_TYPE_DSE_TITLE', 'Rechtstext Datenschutz');
define('MODULE_API_IT_RECHT_KANZLEI_TYPE_DSE_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_API_IT_RECHT_KANZLEI_TYPE_WRB_TITLE', 'Rechtstext Widerruf');
define('MODULE_API_IT_RECHT_KANZLEI_TYPE_WRB_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_API_IT_RECHT_KANZLEI_TYPE_IMP_TITLE', 'Rechtstext Impressum');
define('MODULE_API_IT_RECHT_KANZLEI_TYPE_IMP_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_API_IT_RECHT_KANZLEI_PDF_AGB_TITLE', '<hr noshade>Auswahl AGB PDF Rechtstext');
define('MODULE_API_IT_RECHT_KANZLEI_PDF_AGB_DESC', 'Angabe ob AGB als PDF verf&uuml;gbar sein soll.');
define('MODULE_API_IT_RECHT_KANZLEI_PDF_DSE_TITLE', 'Auswahl Datenschutz PDF Rechtstext');
define('MODULE_API_IT_RECHT_KANZLEI_PDF_DSE_DESC', 'Angabe ob der Datenschutztext als PDF verf&uuml;gbar sein soll.');
define('MODULE_API_IT_RECHT_KANZLEI_PDF_WRB_TITLE', 'Auswahl Widerruf PDF Rechtstext');
define('MODULE_API_IT_RECHT_KANZLEI_PDF_WRB_DESC', 'Angabe ob der Widerrufstext als PDF verf&uuml;gbar sein soll.');
define('MODULE_API_IT_RECHT_KANZLEI_PDF_FILE_TITLE', '<hr noshade>Speicherort PDF');
define('MODULE_API_IT_RECHT_KANZLEI_PDF_FILE_DESC', 'Angabe des Speicherorts der PDF Rechtstexte.');


class it_recht_kanzlei {
  var $code;
  var $title;
  var $sort_order;
  var $enabled;
  var $description;
  var $extended_description;

  function __construct() {
    $this->code = 'it_recht_kanzlei';
    $this->title = MODULE_API_IT_RECHT_KANZLEI_TEXT_TITLE;
    $this->description = MODULE_API_IT_RECHT_KANZLEI_TEXT_DESCRIPTION;
    $this->enabled = ((MODULE_API_IT_RECHT_KANZLEI_STATUS == 'true') ? true : false);
  }
 
  function process() {}

  // display
  function display() {
    return array('text' => '<br />' . 
                           '<br />' . 
                           xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=it_recht_kanzlei'))
                );
  }

  // check
  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_API_IT_RECHT_KANZLEI_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  // install
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TOKEN', '".md5(time() . rand(0,99999))."',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_VERSION', '1.0',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TYPE_AGB', '3',  '6', '1', 'xtc_cfg_select_content(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TYPE_DSE', '2',  '6', '1', 'xtc_cfg_select_content(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TYPE_WRB', '9',  '6', '1', 'xtc_cfg_select_content(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TYPE_IMP', '4',  '6', '1', 'xtc_cfg_select_content(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_PDF_AGB', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_PDF_DSE', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_PDF_WRB', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_PDF_FILE', '/media/content/',  '6', '1', '', now())");
  }

  // remove
  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
  }

  // keys
  function keys() {
    return array('MODULE_API_IT_RECHT_KANZLEI_STATUS', 
                 'MODULE_API_IT_RECHT_KANZLEI_TOKEN', 
                 'MODULE_API_IT_RECHT_KANZLEI_VERSION',
                 'MODULE_API_IT_RECHT_KANZLEI_TYPE_AGB', 
                 'MODULE_API_IT_RECHT_KANZLEI_TYPE_DSE', 
                 'MODULE_API_IT_RECHT_KANZLEI_TYPE_WRB', 
                 'MODULE_API_IT_RECHT_KANZLEI_TYPE_IMP', 
                 'MODULE_API_IT_RECHT_KANZLEI_PDF_AGB', 
                 'MODULE_API_IT_RECHT_KANZLEI_PDF_DSE', 
                 'MODULE_API_IT_RECHT_KANZLEI_PDF_WRB', 
                 'MODULE_API_IT_RECHT_KANZLEI_PDF_FILE', 
                 );
  }
}

// additional function
function xtc_cfg_select_content($configuration, $key) {
  $content_query = xtc_db_query("SELECT content_group, content_title FROM ".TABLE_CONTENT_MANAGER." WHERE languages_id = '".$_SESSION['languages_id']."'");
  while ($content = xtc_db_fetch_array($content_query)) {
    $content_array[] = array('id' => $content['content_group'], 'text' => $content['content_title']);
  }
  return xtc_draw_pull_down_menu('configuration['.$key.']', $content_array, $configuration);
}

function xtc_cfg_display_content($content_group) {
  $content_query = xtc_db_query("SELECT content_title FROM ".TABLE_CONTENT_MANAGER." WHERE languages_id = '".$_SESSION['languages_id']."' AND content_group = '".$content_group."'");
  $content = xtc_db_fetch_array($content_query);
  return $content['content_title'];
}

?>