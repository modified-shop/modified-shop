<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  class json_ld {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;
    var $version;

    function __construct() {
      $this->version = '1.00';
      $this->code = 'json_ld';
      $this->title = MODULE_JSON_LD_TEXT_TITLE;
      $this->description = MODULE_JSON_LD_TEXT_DESCRIPTION;
      $this->sort_order = '';
      $this->enabled = ((defined('MODULE_JSON_LD_STATUS') && MODULE_JSON_LD_STATUS == 'true') ? true : false);
    }

    function process($file) {
    }

    function display() {
      return array('text' => '<br />'.xtc_button(BUTTON_SAVE).'&nbsp;'.
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set='.$_GET['set'].'&module='.$this->code))
                   );
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_JSON_LD_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value
                                         FROM ".TABLE_CONFIGURATION."
                                        WHERE configuration_key = 'MODULE_JSON_LD_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      $sort_order = 0;
      foreach ($this->defaults() as $key => $data) {
        $sort_order++;
        xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
                           VALUES ('".$key."', '".xtc_db_input($data['value'])."', '6', '".$sort_order."', '".xtc_db_input($data['set_function'])."', now())");
      }
    }

    function remove() {
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key IN ('".implode("', '", $this->keys())."')");
    }

    function keys() {
      return array_keys($this->defaults());
    }

    /**
     * The configuration of the module, in the order the administration shows it
     *
     * @return array
     */
    function defaults() {
      $boolean = 'xtc_cfg_select_option(array(\'true\', \'false\'), ';

      return array(
        'MODULE_JSON_LD_STATUS' => array('value' => 'true', 'set_function' => $boolean),
        'MODULE_JSON_LD_ORGANIZATION' => array('value' => 'true', 'set_function' => $boolean),
        'MODULE_JSON_LD_WEBSITE' => array('value' => 'true', 'set_function' => $boolean),
        'MODULE_JSON_LD_BREADCRUMB' => array('value' => 'true', 'set_function' => $boolean),
        'MODULE_JSON_LD_PRODUCT' => array('value' => 'true', 'set_function' => $boolean),
        'MODULE_JSON_LD_LISTING' => array('value' => 'summary', 'set_function' => 'xtc_cfg_select_option(array(\'false\', \'summary\', \'full\'), '),
        'MODULE_JSON_LD_DESCRIPTION_LENGTH' => array('value' => '5000', 'set_function' => ''),
        'MODULE_JSON_LD_PRETTY' => array('value' => 'false', 'set_function' => $boolean),
      );
    }
  }
