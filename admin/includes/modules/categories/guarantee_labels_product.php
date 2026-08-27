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

  /**
   * Checks the GARAN data of an article while it is saved.
   *
   * The system module guarantee_labels registers and removes this class extension, so the
   * shop owner only installs one module. Its own status stays the functional switch: while
   * the system module is inactive this extension passes the data through unchanged.
   */
  class guarantee_labels_product {

    var $code;
    var $name;
    var $title;
    var $description;
    var $enabled;
    var $sort_order;
    var $_check;
    var $has_error;

    function __construct() {
      $this->code = 'guarantee_labels_product';
      $this->name = 'MODULE_CATEGORIES_'.strtoupper($this->code);
      // the system module instantiates this class before the language file is loaded
      $this->title = defined($this->name.'_TITLE') ? constant($this->name.'_TITLE') : $this->code;
      $this->description = defined($this->name.'_DESCRIPTION') ? constant($this->name.'_DESCRIPTION') : '';
      $this->enabled = (defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true') ? true : false;
      $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : '';
    }

    /**
     * Normalises and checks the guarantee duration before the article is written.
     *
     * @param array $sql_data_array the prepared product data
     * @param array $products_data the posted values
     * @return array the product data with a normalised duration
     */
    function insert_product_before($sql_data_array, $products_data) {
      global $messageStack;

      // one article save per call, a multi edit runs through here more than once
      $this->has_error = false;

      if (!defined('MODULE_GUARANTEE_LABELS_STATUS') || MODULE_GUARANTEE_LABELS_STATUS != 'true') {
        return $sql_data_array;
      }

      if (!isset($sql_data_array['products_garan_duration'])) {
        return $sql_data_array;
      }

      require_once(DIR_FS_INC.'guarantee_labels_validate_product.inc.php');

      $guarantee_labels = guarantee_labels_validate_product($sql_data_array, $products_data);

      // the article save redirects afterwards, so the message has to survive it
      foreach ($guarantee_labels['errors'] as $guarantee_labels_error) {
        $messageStack->add_session($guarantee_labels_error, 'error');
        $this->has_error = true;
      }

      return $guarantee_labels['data'];
    }

    /**
     * Reports the check result, so the article administration returns to the mask instead of
     * saving the article away with a message the shop owner may miss.
     *
     * @param bool $error the state collected so far
     * @return bool
     */
    function insert_product_error($error, $products_data, $products_id) {
      return ($error || $this->has_error);
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined($this->name.'_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value
                                         FROM ".TABLE_CONFIGURATION."
                                        WHERE configuration_key = '".$this->name."_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function keys() {
      defined($this->name.'_STATUS_TITLE') OR define($this->name.'_STATUS_TITLE', TEXT_DEFAULT_STATUS_TITLE);
      defined($this->name.'_STATUS_DESC') OR define($this->name.'_STATUS_DESC', TEXT_DEFAULT_STATUS_DESC);
      defined($this->name.'_SORT_ORDER_TITLE') OR define($this->name.'_SORT_ORDER_TITLE', TEXT_DEFAULT_SORT_ORDER_TITLE);
      defined($this->name.'_SORT_ORDER_DESC') OR define($this->name.'_SORT_ORDER_DESC', TEXT_DEFAULT_SORT_ORDER_DESC);

      return array(
        $this->name.'_STATUS',
        $this->name.'_SORT_ORDER'
      );
    }

    function install() {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('".$this->name."_STATUS', 'true', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$this->name."_SORT_ORDER', '10', '6', '2', now())");
    }

    function remove() {
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE '".$this->name."\\_%'");
    }

  }
