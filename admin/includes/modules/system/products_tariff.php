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

  class products_tariff {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;

    function __construct() {
      $this->code = 'products_tariff';
      $this->title = defined('MODULE_PRODUCTS_TARIFF_TEXT_TITLE') ? MODULE_PRODUCTS_TARIFF_TEXT_TITLE : '';
      $this->description = defined('MODULE_PRODUCTS_TARIFF_TEXT_DESCRIPTION') ? MODULE_PRODUCTS_TARIFF_TEXT_DESCRIPTION : '';
      $this->sort_order = ((defined('MODULE_PRODUCTS_TARIFF_SORT_ORDER')) ? MODULE_PRODUCTS_TARIFF_SORT_ORDER : '');
      $this->enabled = ((defined('MODULE_PRODUCTS_TARIFF_STATUS') && MODULE_PRODUCTS_TARIFF_STATUS == 'true') ? true : false);
    }

    function process($file) {
    }

    function display() {
      return array('text' => '<br>' . xtc_button(BUTTON_SAVE) . '&nbsp;' .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                   );
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_PRODUCTS_TARIFF_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_PRODUCTS_TARIFF_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PRODUCTS_TARIFF_STATUS', 'true', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");

      $table_array = array(
        array('table' => TABLE_PRODUCTS, 'column' => 'products_origin', 'default' => 'VARCHAR(2) NOT NULL'),
        array('table' => TABLE_PRODUCTS, 'column' => 'products_tariff', 'default' => 'VARCHAR(32) NOT NULL'),
        array('table' => TABLE_PRODUCTS, 'column' => 'products_tariff_title', 'default' => 'VARCHAR(256) NOT NULL'),
        array('table' => TABLE_ORDERS_PRODUCTS, 'column' => 'products_origin', 'default' => 'VARCHAR(2) NOT NULL'),
        array('table' => TABLE_ORDERS_PRODUCTS, 'column' => 'products_tariff', 'default' => 'VARCHAR(32) NOT NULL'),
        array('table' => TABLE_ORDERS_PRODUCTS, 'column' => 'products_tariff_title', 'default' => 'VARCHAR(256) NOT NULL'),
      );
      foreach ($table_array as $table) {
        $check_query = xtc_db_query("SHOW COLUMNS FROM ".$table['table']." LIKE '".xtc_db_input($table['column'])."'");
        if (xtc_db_num_rows($check_query) < 1) {
          xtc_db_query("ALTER TABLE ".$table['table']." ADD ".$table['column']." ".$table['default']."");
        }
      }
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_PRODUCTS_TARIFF_%'");
    }

    function keys() {
      return array(
        'MODULE_PRODUCTS_TARIFF_STATUS',
      );
    }    

  }
