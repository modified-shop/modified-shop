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

  class afterbuy {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;
    var $version;

    function __construct() {
      $this->version = '1.00';
      $this->code = 'afterbuy';
      $this->title = MODULE_AFTERBUY_TEXT_TITLE;
      $this->description = MODULE_AFTERBUY_TEXT_DESCRIPTION;
      $this->sort_order = ((defined('MODULE_AFTERBUY_SORT_ORDER')) ? MODULE_AFTERBUY_SORT_ORDER : '');
      $this->enabled = ((defined('MODULE_AFTERBUY_STATUS') && MODULE_AFTERBUY_STATUS == 'true') ? true : false);
    }

    function process($file) {
      //do nothing
    }

    function display() {
      return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code)) . '</div>');
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_AFTERBUY_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value
                                         FROM " . TABLE_CONFIGURATION . "
                                        WHERE configuration_key = 'MODULE_AFTERBUY_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AFTERBUY_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_AFTERBUY_PARTNERID', '',  '6', '2', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_AFTERBUY_PARTNERPASS', '',  '6', '3', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_AFTERBUY_USERID', '',  '6', '4', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_AFTERBUY_ORDERSTATUS', '1',  '6', '5', 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AFTERBUY_DEALERS', '',  '6', '6', '" . xtc_db_input($this->customers_status_set_function()) . "', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AFTERBUY_IGNORE_GROUPS', '',  '6', '7', '" . xtc_db_input($this->customers_status_set_function()) . "', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AFTERBUY_ORDER_MAIL', 'true',  '6', '8', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");

      $this->migrate_legacy_configuration();
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_AFTERBUY_%'");
    }

    function keys() {
      $key = array(
        'MODULE_AFTERBUY_STATUS',
        'MODULE_AFTERBUY_PARTNERID',
        'MODULE_AFTERBUY_PARTNERPASS',
        'MODULE_AFTERBUY_USERID',
        'MODULE_AFTERBUY_ORDERSTATUS',
        'MODULE_AFTERBUY_DEALERS',
        'MODULE_AFTERBUY_IGNORE_GROUPS',
        'MODULE_AFTERBUY_ORDER_MAIL',
      );

      return $key;
    }

    /**
     * Option list of the customer groups, without the administration.
     *
     * The expression lands in set_function and is evaluated by admin/module_export.php, so it may
     * only use core functions. The argument to xtc_get_customers_statuses() keys the array by
     * customers_status_id, otherwise array_diff_key would drop whichever group sorts first.
     */
    function customers_status_set_function() {
      return 'xtc_cfg_multi_checkbox(array_diff_key(xtc_get_customers_statuses(true), array(0 => 0)), \'chr(44)\',';
    }

    /**
     * Carries the settings of the former configuration group "Afterbuy" over.
     *
     * A shop that ran the updater has them already, this covers the shop that installs the module
     * by hand while the old keys are still around.
     */
    function migrate_legacy_configuration() {
      $legacy = array(
        'AFTERBUY_ACTIVATED' => 'MODULE_AFTERBUY_STATUS',
        'AFTERBUY_PARTNERID' => 'MODULE_AFTERBUY_PARTNERID',
        'AFTERBUY_PARTNERPASS' => 'MODULE_AFTERBUY_PARTNERPASS',
        'AFTERBUY_USERID' => 'MODULE_AFTERBUY_USERID',
        'AFTERBUY_ORDERSTATUS' => 'MODULE_AFTERBUY_ORDERSTATUS',
        'AFTERBUY_DEALERS' => 'MODULE_AFTERBUY_DEALERS',
        'AFTERBUY_IGNORE_GROUPE' => 'MODULE_AFTERBUY_IGNORE_GROUPS',
      );

      foreach ($legacy as $old_key => $new_key) {
        $legacy_query = xtc_db_query("SELECT configuration_value
                                        FROM " . TABLE_CONFIGURATION . "
                                       WHERE configuration_key = '" . $old_key . "'");
        if (xtc_db_num_rows($legacy_query) > 0) {
          $legacy_value = xtc_db_fetch_array($legacy_query);
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                           SET configuration_value = '" . xtc_db_input($legacy_value['configuration_value']) . "'
                         WHERE configuration_key = '" . $new_key . "'");
          xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . $old_key . "'");
        }
      }

      // the group only goes once nothing else lives in it any more
      $group_query = xtc_db_query("SELECT configuration_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = '21' LIMIT 1");
      if (xtc_db_num_rows($group_query) == 0) {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = '21' AND configuration_group_title = 'Afterbuy'");
      }
    }

  }
