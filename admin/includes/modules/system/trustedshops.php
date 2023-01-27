<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

// include needed functions
class trustedshops {
  var $code, $title, $description, $enabled;

  function __construct() {
    $this->version = '1.11';
    $this->code = 'trustedshops';
    $this->title = MODULE_TRUSTEDSHOPS_TEXT_TITLE;
    $this->description = MODULE_TRUSTEDSHOPS_TEXT_DESCRIPTION;
    $this->sort_order = defined('MODULE_TRUSTEDSHOPS_SORT_ORDER') ? MODULE_TRUSTEDSHOPS_SORT_ORDER : '';
    $this->enabled = ((defined('MODULE_TRUSTEDSHOPS_STATUS') && MODULE_TRUSTEDSHOPS_STATUS == 'true') ? true : false);
    
    if (defined('MODULE_TRUSTEDSHOPS_STATUS')) {
      $query_result = xtc_db_query("SHOW COLUMNS FROM `" . TABLE_TRUSTEDSHOPS . "`");
      $db_table_rows = array();
      while ($row = xtc_db_fetch_array($query_result)) {
        $db_table_rows[] = $row['Field'];
      }
    
      if (count($db_table_rows) > 0) {
        $table_array = array(
          array('action' => 'add', 'column' => 'trustbadge_offset_mobile', 'default' => "int(11) NOT NULL DEFAULT '0' AFTER trustbadge_position"),
          array('action' => 'add', 'column' => 'trustbadge_position_mobile', 'default' => "varchar(32) NOT NULL AFTER trustbadge_offset_mobile"),
          array('action' => 'add', 'column' => 'product_sticker_api_client', 'default' => "varchar(128) NOT NULL AFTER product_sticker_api"),
          array('action' => 'add', 'column' => 'product_sticker_api_secret', 'default' => "varchar(128) NOT NULL AFTER product_sticker_api_secret"),

          array('action' => 'delete', 'column' => 'snippets'),
          array('action' => 'delete', 'column' => 'widget'),
        );
    
        foreach ($table_array as $table) {
          if (!in_array($table['column'], $db_table_rows) && $table['action'] == 'add') {
            xtc_db_query("ALTER TABLE ".TABLE_TRUSTEDSHOPS." ADD ".$table['column']." ".$table['default']."");
          } elseif (in_array($table['column'], $db_table_rows) && $table['action'] == 'delete') {
            xtc_db_query("ALTER TABLE ".TABLE_TRUSTEDSHOPS." DROP COLUMN ".$table['column']);
          }
        }
      }
    }
  }

  function process($file) {
    if (isset($_POST['configuration']) && $_POST['configuration']['MODULE_TRUSTEDSHOPS_STATUS'] == 'true') {
      xtc_redirect(xtc_href_link('trustedshops.php'));
    }
  }

  function display() {
    return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=trustedshops')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_TRUSTEDSHOPS_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value 
                                       FROM " . TABLE_CONFIGURATION . " 
                                      WHERE configuration_key = 'MODULE_TRUSTEDSHOPS_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }
    
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_TRUSTEDSHOPS_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_TRUSTEDSHOPS." (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `trustedshops_id` varchar(64) NOT NULL,
                  `status` int(1) NOT NULL DEFAULT '1',
                  `languages_id` int(11) NOT NULL,
                  `trustbadge_variant` varchar(32) NOT NULL,
                  `trustbadge_offset` int(11) NOT NULL DEFAULT '0',
                  `trustbadge_position` varchar(32) NOT NULL,
                  `trustbadge_offset_mobile` int(11) NOT NULL DEFAULT '0',
                  `trustbadge_position_mobile` varchar(32) NOT NULL,
                  `trustbadge_code` text NOT NULL,
                  `product_sticker` text NOT NULL,
                  `product_sticker_status` int(1) NOT NULL DEFAULT '0',
                  `product_sticker_api` int(1) NOT NULL DEFAULT '0',
                  `product_sticker_api_client` varchar(128) NOT NULL,
                  `product_sticker_api_secret` varchar(128) NOT NULL,
                  `review_sticker` text NOT NULL,
                  `review_sticker_status` int(1) NOT NULL DEFAULT '0',
                  `date_added` datetime NOT NULL,
                  `last_modified` datetime NOT NULL,
                  PRIMARY KEY (`id`)
                )");
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
    xtc_db_query("DROP TABLE ".TABLE_TRUSTEDSHOPS);
  }

  function keys() {
    $key = array('MODULE_TRUSTEDSHOPS_STATUS');

    return $key;
  }
}
?>