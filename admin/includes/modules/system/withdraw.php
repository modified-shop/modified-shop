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
  
  class withdraw {
  
    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;
    var $version;
  
    function __construct() {
      $this->version = '1.02';
      $this->code = 'withdraw';
      $this->title = MODULE_WITHDRAW_TEXT_TITLE;
      $this->description = MODULE_WITHDRAW_TEXT_DESCRIPTION;
      $this->enabled = ((defined('MODULE_WITHDRAW_STATUS') && MODULE_WITHDRAW_STATUS == 'true') ? true : false);
    }
    
    function process($file) {
      // the status is already saved when this runs, so read it from the database instead of the stale constant
      $status_query = xtc_db_query("SELECT configuration_value
                                      FROM ".TABLE_CONFIGURATION."
                                     WHERE configuration_key = 'MODULE_WITHDRAW_STATUS'");
      $status = xtc_db_fetch_array($status_query);
      $content_status = ((isset($status['configuration_value']) && $status['configuration_value'] == 'true') ? '1' : '0');

      xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                       SET content_status = '".$content_status."'
                     WHERE content_file = 'withdraw.php'");
    }
    
    function display() {
      return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=withdraw')) . "</div>");
    }
    
    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_WITHDRAW_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_WITHDRAW_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }
    
    function install() {
      // preselect the shipped content page so the module is ready right after activation
      $content_group = '';
      $content_query = xtc_db_query("SELECT content_group
                                       FROM ".TABLE_CONTENT_MANAGER."
                                      WHERE content_file = 'withdraw.php'
                                   ORDER BY content_group
                                      LIMIT 1");
      if (xtc_db_num_rows($content_query) > 0) {
        $content = xtc_db_fetch_array($content_query);
        $content_group = (int)$content['content_group'];
      }

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_WITHDRAW_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_WITHDRAW_CAPTCHA', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_WITHDRAW_CONTENT', '".$content_group."',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");

      // the content page ships hidden and only shows up in the footer boxes while the module is installed
      xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                       SET content_status = '1'
                     WHERE content_file = 'withdraw.php'");

      xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_ORDERS_WITHDRAW." (
                     `orders_withdraw_id` int(11) NOT NULL AUTO_INCREMENT,
                     `orders_id` int(11) NOT NULL,
                     `date_added` datetime NOT NULL,
                     PRIMARY KEY (`orders_withdraw_id`),
                     KEY `idx_orders_id` (`orders_id`)
                     )");

      xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_ORDERS_WITHDRAW_PRODUCTS." (
                     `orders_withdraw_products_id` int(11) NOT NULL AUTO_INCREMENT,
                     `orders_withdraw_id` int(11) NOT NULL,
                     `orders_id` int(11) NOT NULL,
                     `orders_products_id` int(11) NOT NULL,
                     `products_id` int(11) NOT NULL,
                     `products_quantity` int(11) NOT NULL,
                     PRIMARY KEY (`orders_withdraw_products_id`),
                     KEY `idx_orders_withdraw_id` (`orders_withdraw_id`),
                     KEY `idx_orders_products_id` (`orders_products_id`)
                     )");

      // CREATE TABLE IF NOT EXISTS does not add a new index to an existing table.
      $index_query = xtc_db_query("SHOW KEYS
                                     FROM ".TABLE_ORDERS_WITHDRAW_PRODUCTS."
                                    WHERE Key_name = 'idx_orders_products_id'");
      if (xtc_db_num_rows($index_query) < 1) {
        xtc_db_query("ALTER TABLE ".TABLE_ORDERS_WITHDRAW_PRODUCTS."
                           ADD KEY `idx_orders_products_id` (`orders_products_id`)");
      }
    
      // the confirmation link is opened in another browser, so the token cannot live in the session
      xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_ORDERS_WITHDRAW_TOKEN." (
                     `orders_withdraw_token_id` int(11) NOT NULL AUTO_INCREMENT,
                     `orders_id` int(11) NOT NULL,
                     `token` varchar(32) NOT NULL,
                     `date_added` datetime NOT NULL,
                     `date_expires` datetime NOT NULL,
                     PRIMARY KEY (`orders_withdraw_token_id`),
                     UNIQUE KEY `idx_token` (`token`),
                     UNIQUE KEY `idx_orders_id` (`orders_id`)
                     )");

      $check_query = xtc_db_query("SHOW TABLES LIKE 'orders_products_withdraw'");
      if (xtc_db_num_rows($check_query) > 0) {
        $migrate_query = xtc_db_query("SELECT withdraw_id,
                                              orders_id
                                         FROM `orders_products_withdraw`
                                     GROUP BY withdraw_id,
                                              orders_id");
        while ($migrate = xtc_db_fetch_array($migrate_query)) {
          $sql_data_array = array(
            'orders_id' => $migrate['orders_id'],
            'date_added' => 'now()',
          );
          xtc_db_perform(TABLE_ORDERS_WITHDRAW, $sql_data_array);
          
          $orders_withdraw_id = xtc_db_insert_id();
          $migrate_products_query = xtc_db_query("SELECT *
                                                    FROM `orders_products_withdraw`
                                                   WHERE withdraw_id = '".(int)$migrate['withdraw_id']."'");
          while ($migrate_products = xtc_db_fetch_array($migrate_products_query)) {
            $sql_data_array = array(
              'orders_withdraw_id' => $orders_withdraw_id,
              'orders_id' => $migrate_products['orders_id'],
              'orders_products_id' => $migrate_products['orders_products_id'],
              'products_id' => $migrate_products['products_id'],
              'products_quantity' => $migrate_products['products_quantity'],
            );
            xtc_db_perform(TABLE_ORDERS_WITHDRAW_PRODUCTS, $sql_data_array);
          }
        }
        xtc_db_query("DROP TABLE `orders_products_withdraw`");
      }
    }
    
    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_WITHDRAW_%'");

      xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                       SET content_status = '0'
                     WHERE content_file = 'withdraw.php'");
    }
    
    function keys() {
      // MODULE_WITHDRAW_CAPTCHA is deliberately absent, it stays a hidden setting
      $key = array(
        'MODULE_WITHDRAW_STATUS',
        'MODULE_WITHDRAW_CONTENT',
      );
  
      return $key;
    }
    
  }
