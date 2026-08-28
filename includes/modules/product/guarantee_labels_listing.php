<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * Adds the GARAN label to every article list built through the product class.
   *
   * The system module guarantee_labels registers and removes this class extension, so the
   * shop owner only installs one module. Its own status stays the functional switch: while
   * the system module is inactive the label stays empty.
   */
  class guarantee_labels_listing {

    var $code;
    var $name;
    var $title;
    var $description;
    var $enabled;
    var $sort_order;
    var $_check;

    function __construct() {
      $this->code = 'guarantee_labels_listing';
      $this->name = 'MODULE_PRODUCT_'.strtoupper($this->code);
      // the system module instantiates this class before the language file is loaded
      $this->title = defined($this->name.'_TITLE') ? constant($this->name.'_TITLE') : $this->code;
      $this->description = defined($this->name.'_DESCRIPTION') ? constant($this->name.'_DESCRIPTION') : '';
      $this->enabled = (defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true') ? true : false;
      $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : '';
    }

    /**
     * @param array $productData the data collected for the template
     * @param array $array the product row of the calling query
     * @param string $image the requested image size, info for the detail view
     * @return array
     */
    function buildDataArray($productData, $array, $image = 'thumbnail', $cache = true) {
      // a template can place {$GUARANTEE_LABEL} without asking whether the module is installed
      $productData['GUARANTEE_LABEL'] = '';

      // the detail view builds its label in includes/extra/modules/product_info_end/, it shows
      // one regardless of the buy now button and would otherwise render the same label twice
      if ($image === 'info') {
        return $productData;
      }

      // without the direct cart button an article list is not an ordering opportunity, so the
      // label is first needed on the detail page
      if (!defined('SHOW_BUTTON_BUY_NOW') || SHOW_BUTTON_BUY_NOW == 'false') {
        return $productData;
      }

      require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

      if (!guarantee_labels_active() || !guarantee_labels_candidate($array)) {
        return $productData;
      }

      $names = guarantee_labels_manufacturer_names(array($array['manufacturers_id']));
      $productData['GUARANTEE_LABEL'] = guarantee_labels_markup(guarantee_labels_product_label($array, $names));

      return $productData;
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
