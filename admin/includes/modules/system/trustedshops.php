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

  function trustedshops() {
     $this->code = 'trustedshops';
     $this->title = MODULE_TRUSTEDSHOPS_TEXT_TITLE;
     $this->description = MODULE_TRUSTEDSHOPS_TEXT_DESCRIPTION;
     $this->sort_order = defined('MODULE_TRUSTEDSHOPS_SORT_ORDER') ? MODULE_TRUSTEDSHOPS_SORT_ORDER : 0;
     $this->enabled = ((MODULE_TRUSTEDSHOPS_STATUS == 'true') ? true : false);
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
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM " . TABLE_CONFIGURATION . "
                                    WHERE configuration_key = 'MODULE_TRUSTEDSHOPS_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
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
                  `trustbadge_code` text NOT NULL,
                  `product_sticker_api` int(1) NOT NULL DEFAULT '0',
                  `product_sticker` text NOT NULL,
                  `product_sticker_status` int(1) NOT NULL DEFAULT '0',
                  `review_sticker` text NOT NULL,
                  `review_sticker_status` int(1) NOT NULL DEFAULT '0',
                  `snippets` varchar(32) NOT NULL,
                  `widget` int(1) NOT NULL DEFAULT '0',
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