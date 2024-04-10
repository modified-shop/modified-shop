<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed classes
  require_once(DIR_WS_CLASSES.'language.php');
  require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'xtcPrice.php');
  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalZettle.php');

  class zettle_categories {

    var $code;
    var $name;
    var $title;
    var $description;
    var $enabled;
    var $sort_order;
    var $_check;

    var $language_id;
    var $customers_status;
    var $PayPalZettle;

    function __construct() {      
      $this->code = 'zettle_categories';
      $this->name = 'MODULE_CATEGORIES_'.strtoupper($this->code);
      $this->title = defined($this->name.'_TITLE') ? constant($this->name.'_TITLE') : '';
      $this->description = defined($this->name.'_DESCRIPTION') ? constant($this->name.'_DESCRIPTION') : '';
      $this->enabled = defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true' ? true : false;
      $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : ''; 
    
      if ($this->check() > 0) {
        $lng = new language(MODULE_CATEGORIES_ZETTLE_CATEGORIES_LANGUAGE);
        
        $this->language_id = $lng->language['languages_id'];
        $this->customers_status = MODULE_CATEGORIES_ZETTLE_CATEGORIES_CUSTOMERS_STATUS;

        $this->PayPalZettle = new PayPalZettle();
        
        if (MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY != '') {
          $account = $this->PayPalZettle->getAccountInfo();          
        }
                
        if (isset($account) 
            && is_array($account) 
            && isset($account['receiptName']) 
            && $account['receiptName']!= ''
            )
        {
          $this->description .= '
            <div style="margin: 15px 0 0 0; text-align:center;">
              <div style="background:#fff;border:1px solid #ccc;margin: 0px auto;width:100px;height:100px;border-radius:100px;overflow:hidden;">'.(($account['profileImageUrl'] != '') ? '<img style="width:100%;height:auto; vertical-align-top; object-fit:cover;" src="'.str_replace('[size]', 'm', $account['profileImageUrl']).'">' : '').'</div>          
              <div style="margin:10px 0px;">'.decode_utf8($account['receiptName']).'</div>
            </div>
          ';
          $this->description .= '<div style="margin: 15px 0 0 0; text-align:center;"><a class="button btnbox" href="'.xtc_href_link(FILENAME_MODULES, xtc_get_all_get_params(array('action', 'module')).'action=custom&module='.$this->code).'">'.BUTTON_UPDATE.'</a></div>';
        
          if (MODULE_CATEGORIES_ZETTLE_CATEGORIES_ORGANIZATION == '') {
            $this->custom();
          }
        } else {
          if (count($this->PayPalZettle->error) > 0) {
            $this->description .= '<div class="error_message">';
            foreach ($this->PayPalZettle->error as $error) {
              $this->description .= $error['error_description'].'<br>';
            }
            $this->description .= '</div>';
          }
          $this->description .= '
            <div style="margin: 15px 0 0 0; text-align:center;">
              <div class="cf" style="vertical-align:top">
                <img style="margin:10px 10px 0 0; max-width:120px;height:auto;" src="https://cdn.izettle.com/zettle-brand/Zettle_Simple_Positive.svg" />
              </div>
              <div style="margin:10px 0px;text-align:center;"><a class="button btnbox" target="_blank" href=" https://my.izettle.com/apps/api-keys?name='.$this->PayPalZettle->client_id.'&scopes=READ:FINANCE READ:PURCHASE READ:USERINFO READ:PRODUCT WRITE:PRODUCT">'.MODULE_CATEGORIES_ZETTLE_CATEGORIES_BUTTON_API.'</a></div>
            </div>
          ';
        }
      }
    }


    function custom() {
      $account = $this->PayPalZettle->getAccountInfo();
      
      if (isset($account['uuid']) && $account['uuid'] != '') {
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = '".xtc_db_input($account['uuid'])."'
                       WHERE configuration_key = 'MODULE_CATEGORIES_ZETTLE_CATEGORIES_ORGANIZATION'");
        
        $subscriptions = $this->PayPalZettle->getSubscriptions();

        if (isset($subscriptions[0])) {
          $this->PayPalZettle->updateSubscriptions($subscriptions[0]['uuid']);
        } else {
          $this->PayPalZettle->setSubscriptions();
        }
      }              
    }


    function check() {
      if (!isset($this->_check)) {
        if (defined($this->name.'_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '".$this->name."_STATUS'");
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
    
      $keys = array(
        $this->name.'_STATUS', 
        $this->name.'_BULK', 
        $this->name.'_SORT_ORDER',        
        $this->name.'_API_KEY',
        $this->name.'_LANGUAGE',
        $this->name.'_CUSTOMERS_STATUS',
      );
    
      return $keys;
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('".$this->name."_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$this->name."_SORT_ORDER', '10','6', '2', now())");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('".$this->name."_BULK', 'false','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$this->name."_API_KEY', '','6', '2', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$this->name."_ORGANIZATION', '','6', '2', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('".$this->name."_LANGUAGE', '".DEFAULT_LANGUAGE."','6', '1','xtc_cfg_pull_down_language_code(', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('".$this->name."_CUSTOMERS_STATUS', '".DEFAULT_CUSTOMERS_STATUS_ID."','6', '1','xtc_get_customers_status_name', 'xtc_cfg_pull_down_customers_status_list(', now())");
    
      xtc_db_query("CREATE TABLE IF NOT EXISTS `paypal_zettle_to_products` (
                      `zettle_id` int(11) NOT NULL AUTO_INCREMENT,
                      `products_uuid` varchar(64) NOT NULL,
                      `products_id` int(11) NOT NULL,
                      `stock` int(1) NOT NULL,
                      `status` int(1) NOT NULL,
                      `bulk` int(1) NOT NULL,
                      PRIMARY KEY (`zettle_id`),
                      KEY `idx_products_id` (`products_id`),
                      KEY `idx_products_uuid` (`products_uuid`),
                      KEY `idx_bulk` (`bulk`)
                    )");

      xtc_db_query("CREATE TABLE IF NOT EXISTS `paypal_zettle_import` (
                      `zettle_id` int(11) NOT NULL AUTO_INCREMENT,
                      `import_uuid` varchar(64) NOT NULL,
                      PRIMARY KEY (`zettle_id`),
                      KEY `idx_import_uuid` (`import_uuid`)
                    )");
      
      $check_query = xtc_db_query("SHOW COLUMNS FROM `paypal_zettle_to_products` LIKE 'zettle_id'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE `paypal_zettle_to_products` DROP PRIMARY KEY");
        xtc_db_query("ALTER TABLE `paypal_zettle_to_products` ADD `zettle_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
      }

      $check_query = xtc_db_query("DESCRIBE `paypal_zettle_to_products`");
      while ($check = xtc_db_fetch_array($check_query)) {
        if (in_array($check['Field'], array('products_uuid', 'bulk'))
            && $check['Key'] == ''
            )
        {
          xtc_db_query("ALTER TABLE `paypal_zettle_to_products` ADD INDEX `idx_".$check['Field']."` (`".$check['Field']."`)");
        }
      }      

      $check_query = xtc_db_query("DESCRIBE `paypal_zettle_import`");
      while ($check = xtc_db_fetch_array($check_query)) {
        if ($check['Field'] == 'zettle_id'
            && strtoupper($check['Extra']) != 'AUTO_INCREMENT'
            )
        {
          xtc_db_query("ALTER TABLE `paypal_zettle_import` DROP PRIMARY KEY");
          xtc_db_query("ALTER TABLE `paypal_zettle_import` MODIFY `zettle_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
        }
      }      
    }
  
  
    function remove() {  
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '".$this->name."_%'");

      //xtc_db_query("DROP TABLE IF EXISTS paypal_zettle_to_products");
      //xtc_db_query("DROP TABLE IF EXISTS paypal_zettle_import");
    }
  
  
    //--- BEGIN CUSTOM  CLASS METHODS ---//
    function remove_product($products_id) {
      global $messageStack;
      
      $check_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                                    WHERE products_id = '".(int)$products_id."'");
      if (xtc_db_num_rows($check_query) > 0) {
        $check = xtc_db_fetch_array($check_query);
      
        $response = $this->PayPalZettle->deleteProduct($check['products_uuid']);
        if (count($this->PayPalZettle->error) < 1) {
          xtc_db_query("DELETE FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." WHERE products_id = '".(int)$products_id."'");
        }

        if (count($this->PayPalZettle->error) > 0) {
          foreach ($this->PayPalZettle->error as $error) {
            $messageStack->add_session($error['error_description'], 'error');
          }
        }
      }  
    }
  
    function insert_product_after($products_data, $products_id) {
      $check_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                                    WHERE products_id = '".(int)$products_id."'");
      $num_rows = xtc_db_num_rows($check_query);
    
      if ($num_rows > 0) {
        $sql_data_array = array(
          'stock' => $products_data['products_zettle_stock']
        );
        xtc_db_perform(TABLE_PAYPAL_ZETTLE_TO_PRODUCTS, $sql_data_array, 'update', "products_id = '".(int)$products_id."'");
      
        if ($products_data['products_zettle_status'] == 0) {
          $this->remove_product($products_id);
        }
      } elseif ($products_data['products_zettle_status'] == 1) {
        $sql_data_array = array(
          'products_id' => $products_id,
          'stock' => $products_data['products_zettle_stock'],
          'status' => 1
        );
        xtc_db_perform(TABLE_PAYPAL_ZETTLE_TO_PRODUCTS, $sql_data_array);
      }
    }
  
    function insert_product_end($products_id) {
      global $messageStack;
      
      $check_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                                    WHERE products_id = '".(int)$products_id."'");
      if (xtc_db_num_rows($check_query) > 0) {
        $check = xtc_db_fetch_array($check_query);
        $products_uuid = $check['products_uuid'];
      
        $xtPrice = new xtcPrice(DEFAULT_CURRENCY, $this->customers_status);
      
        $products_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_PRODUCTS." p
                                          JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                               ON p.products_id = pd.products_id
                                                  AND pd.language_id = '".(int)$this->language_id."'
                                         WHERE p.products_id = '".(int)$products_id."'");
        $products = xtc_db_fetch_array($products_query);
      
        $products['products_currency'] = DEFAULT_CURRENCY;
        $products['products_tax'] = xtc_get_tax_rate($products['products_tax_class_id']);
        $products['products_price'] = $xtPrice->xtcGetPrice($products_id, false, 1, $products['products_tax_class_id'], $products['products_price']);
        $products['products_price'] *= 100;
      
        if ($products_uuid == '') {
          $result = $this->PayPalZettle->insertProduct($products);
        
          if (isset($result['uuid'])) {
            $products_uuid = $result['uuid'];
            xtc_db_query("UPDATE ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                             SET products_uuid = '".xtc_db_input($products_uuid)."'
                           WHERE products_id = '".(int)$products_id."'");
          }
        } else {
          $this->PayPalZettle->updateProduct($products_uuid, $products);
        }      

        if ($check['stock'] == 1) {
          $this->PayPalZettle->updateInventory($products_uuid, $products);
        } else {
          $this->PayPalZettle->deleteInventory($products_uuid);
        }
      }
      
      if (isset($this->PayPalZettle->error) 
          && isset($this->PayPalZettle->error[0])
          && isset($this->PayPalZettle->error[0]['errors'])
          && count($this->PayPalZettle->error[0]['errors']) > 0
          )
      {
        foreach ($this->PayPalZettle->error[0]['errors'] as $error) {
          $messageStack->add_session($error, 'error');
        }
      }
    }
    
    function insert_category_after($categories_data, $categories_id) {
      if (isset($categories_data['categories_zettle_bulk'])
          && $categories_data['categories_zettle_bulk'] == 1
          )
      {
        $subcategories_array = array ();
        if ($categories_data['categories_zettle_sub'] == 1) {
          require_once (DIR_FS_INC.'xtc_get_subcategories.inc.php');
          xtc_get_subcategories($subcategories_array, $categories_id);
        }
        $subcategories_array[] = $categories_id;
        
        $where = '';
        if ($categories_data['categories_zettle_active'] == 1) {
          $where = " WHERE p.products_status = '1' ";
        }
        $products_query = xtc_db_query("SELECT pz2p.*,
                                               p.products_id
                                          FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                          JOIN ".TABLE_PRODUCTS." p
                                               ON p2c.products_id = p.products_id
                                                  AND p2c.categories_id IN ('".implode("', '", $subcategories_array)."')
                                     LEFT JOIN ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS." pz2p
                                               ON pz2p.products_id = p.products_id
                                               ".$where);                                                          
        while ($products = xtc_db_fetch_array($products_query)) {
          if ($products['products_uuid'] == ''
              || $products['stock'] != $categories_data['categories_zettle_stock']
              || $products['status'] != $categories_data['categories_zettle_status']
              )
          {
            $sql_data_array = array(
              'products_id' => $products['products_id'],
              'stock' => $categories_data['categories_zettle_stock'],
              'status' => $categories_data['categories_zettle_status'],
              'bulk' => $categories_id
            );
            xtc_db_perform(TABLE_PAYPAL_ZETTLE_TO_PRODUCTS, $sql_data_array, (($products['zettle_id'] != '') ? 'update' : 'insert'), "zettle_id = '".xtc_db_input($products['zettle_id'])."'");
          }          
        }
      }
    }
    
  }