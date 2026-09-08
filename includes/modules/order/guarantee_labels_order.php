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
   * Adds the GARAN label to the products of an order.
   *
   * The order class builds its products from the cart, so the checkout shows the same label
   * as the article. The system module guarantee_labels registers and removes this class
   * extension, so the shop owner only installs one module.
   */
  class guarantee_labels_order {

    var $code;
    var $name;
    var $title;
    var $description;
    var $enabled;
    var $sort_order;
    var $_check;

    function __construct() {
      $this->code = 'guarantee_labels_order';
      $this->name = 'MODULE_ORDER_'.strtoupper($this->code);
      // the system module instantiates this class before the language file is loaded
      $this->title = defined($this->name.'_TITLE') ? constant($this->name.'_TITLE') : $this->code;
      $this->description = defined($this->name.'_DESCRIPTION') ? constant($this->name.'_DESCRIPTION') : '';
      $this->enabled = (defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true') ? true : false;
      $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : '';
    }

    /**
     * The cart strips the products_ prefix from every column, so the GARAN data of the article
     * arrives here as garan_duration, manufacturers_model and manufacturers_id.
     *
     * @param array $products_data one product of the order
     * @param int $products_id
     * @return array
     */
    function cart_products($products_data, $products_id) {
      // a template can place {$data.GUARANTEE_LABEL} without asking whether the module is installed
      $products_data['GUARANTEE_LABEL'] = '';

      require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

      if (!guarantee_labels_active()) {
        return $products_data;
      }

      $product = array(
        'products_id' => $products_id,
        'products_garan_duration' => isset($products_data['garan_duration']) ? $products_data['garan_duration'] : null,
        'products_manufacturers_model' => isset($products_data['manufacturers_model']) ? $products_data['manufacturers_model'] : '',
        'manufacturers_id' => isset($products_data['manufacturers_id']) ? $products_data['manufacturers_id'] : 0,
      );

      // the cart id carries the chosen attributes, so the position decides and not the article
      if (!guarantee_labels_candidate($product, $products_id)) {
        return $products_data;
      }

      $names = guarantee_labels_manufacturer_names(array($product['manufacturers_id']));
      $products_data['GUARANTEE_LABEL'] = guarantee_labels_markup(guarantee_labels_product_label($product, $names, $products_id));

      return $products_data;
    }

    /**
     * Writes the guarantee of a position into the order data the mails and the order view use.
     *
     * getOrderData() runs its own query, so this hook is needed next to add_products().
     *
     * @param array $order_data one position of the order
     * @param array $order_data_values the row of orders_products
     * @param int $oID
     * @return array
     */
    function order_data($order_data, $order_data_values, $oID, $order_lang_id) {
      require_once(DIR_FS_INC.'guarantee_labels_order.inc.php');

      // always set, so a template can place the variables without asking for the module
      $order_data['GUARANTEE_HTML'] = '';
      $order_data['GUARANTEE_TXT'] = '';
      $order_data['GUARANTEE_TEXT_HTML'] = '';
      $order_data['GUARANTEE_TEXT_TXT'] = '';
      $order_data['GUARANTEE_LABEL'] = '';

      // an inactive module shows nothing, not even from a snapshot that is still there
      if (!guarantee_labels_order_active()) {
        return $order_data;
      }

      $guarantee = guarantee_labels_order_text($oID, $order_data_values['orders_products_id']);

      // the graphic of the order, built from the archived files of its snapshot
      $order_data['GUARANTEE_LABEL'] = guarantee_labels_order_label($oID, $order_data_values['orders_products_id']);

      if ($guarantee !== false) {
        // the wording alone, for every place that does not carry the document
        $order_data['GUARANTEE_TEXT_HTML'] = $guarantee['label']['html'];
        $order_data['GUARANTEE_TEXT_TXT'] = $guarantee['label']['txt'];

        // the confirmation names the attached conditions, they travel with it
        $order_data['GUARANTEE_HTML'] = $guarantee['label']['html'].
                                        (($guarantee['terms']['html'] !== '') ? '<br />'.$guarantee['terms']['html'] : '');
        $order_data['GUARANTEE_TXT'] = $guarantee['label']['txt'].
                                       (($guarantee['terms']['txt'] !== '') ? "\n".$guarantee['terms']['txt'] : '');
      }

      return $order_data;
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

    /**
     * The status key stays out of the mask on purpose. The system module guarantee_labels is
     * the only switch; a second one here could turn this extension off while the system module
     * still reports itself as active. The key itself has to exist, the module loader reads it.
     */
    function keys() {
      defined($this->name.'_SORT_ORDER_TITLE') OR define($this->name.'_SORT_ORDER_TITLE', TEXT_DEFAULT_SORT_ORDER_TITLE);
      defined($this->name.'_SORT_ORDER_DESC') OR define($this->name.'_SORT_ORDER_DESC', TEXT_DEFAULT_SORT_ORDER_DESC);

      return array(
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
