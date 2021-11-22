<?php
/**
 *
 * Copyright Shopgate Inc.
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU General Public License, Version 2
 *
 */
include_once DIR_FS_CATALOG
    . 'includes/external/shopgate/base/shopgate_config.php';
include_once DIR_FS_CATALOG
    . 'includes/external/shopgate/base/includes/modules/payment/ShopgateInstallHelper.php';

class shopgate
{
    public $code;
    public $title;
    public $description;
    public $enabled;
    public $sort_order;

    public function shopgate()
    {
        $this->code        = 'shopgate';
        $this->title       = MODULE_PAYMENT_SHOPGATE_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION;
        $this->enabled     = ((MODULE_PAYMENT_SHOPGATE_STATUS == 'True') ? true
            : false);
        $this->sort_order  = MODULE_PAYMENT_SHOPGATE_SORT_ORDER;
    }

    public function mobile_payment()
    {
        $this->code        = 'shopgate';
        $this->title       = MODULE_PAYMENT_SHOPGATE_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION;
        $this->enabled     = false;
        $this->sort_order  = MODULE_PAYMENT_SHOPGATE_SORT_ORDER;
    }

    public function update_status()
    {
    }

    public function javascript_validation()
    {
        return false;
    }

    public function selection()
    {
        return array('id'          => $this->code, 'module' => $this->title,
                     'description' => $this->info);
    }

    public function pre_confirmation_check()
    {
        return false;
    }

    public function confirmation()
    {
        return array('title' => MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION);
    }

    public function process_button()
    {
        return false;
    }

    public function before_process()
    {
        return false;
    }

    public function after_process()
    {
        global $insert_id;
        if ($this->order_status) {
            xtc_db_query(
                "UPDATE " . TABLE_ORDERS . " SET orders_status='"
                . $this->order_status . "' WHERE orders_id='"
                . $insert_id . "'"
            );
        }
    }

    public function get_error()
    {
        return false;
    }

    public function check()
    {
        if (!isset($this->_check)) {
            $check_query  = xtc_db_query(
                "select configuration_value from " . TABLE_CONFIGURATION
                . " where configuration_key = 'MODULE_PAYMENT_SHOPGATE_STATUS'"
            );
            $this->_check = xtc_db_num_rows($check_query);
        }

        return $this->_check ? true : false;
    }

    /**
     * install the module
     *
     * -- KEYS --:
     * MODULE_PAYMENT_SHOPGATE_STATUS - The state of the module ( true / false
     * )
     * MODULE_PAYMENT_SHOPGATE_ALLOWED - Is the module allowed on frontend
     * MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID - (DEPRECATED) keep it for old
     * installations
     */
    public function install()
    {
        xtc_db_query(
            "delete from " . TABLE_CONFIGURATION
            . " where configuration_key in ('MODULE_PAYMENT_SHOPGATE_STATUS', 'MODULE_PAYMENT_SHOPGATE_ALLOWED', 'MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID')"
        );
        xtc_db_query(
            "insert into " . TABLE_CONFIGURATION
            . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_SHOPGATE_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())"
        );
        xtc_db_query(
            "insert into " . TABLE_CONFIGURATION
            . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SHOPGATE_ALLOWED', '0', '6', '1', now())"
        );
        xtc_db_query(
            "insert into " . TABLE_CONFIGURATION
            . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SHOPGATE_SORT_ORDER', '0', '6', '1', now())"
        );
        $result = xtc_db_query(
            'select configuration_key,configuration_value from configuration as c where c.configuration_key = "'
            . ShopgateInstallHelper::SHOPGATE_DATABASE_CONFIG_KEY . '"'
        );
        $row    = xtc_db_fetch_array($result);
        if (empty($row)) {
            xtc_db_query(
                "insert into " . TABLE_CONFIGURATION
                . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_SHOPGATE_IDENT'  , '0',   '6', '"
                . $this->sort_order . "', now())"
            );
        }

        $this->installTable();
        $this->updateDatabase();
        $this->grantAdminAccess();
        $installHelper = new ShopgateInstallHelper();
        $installHelper->sendData();
    }

    /**
     * remove the shopgate module
     */
    public function remove()
    {
        // MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID - Keep this on removing for old installation
        xtc_db_query(
            "delete from " . TABLE_CONFIGURATION
            . " where configuration_key in ('MODULE_PAYMENT_SHOPGATE_STATUS', 'MODULE_PAYMENT_SHOPGATE_ALLOWED', 'MODULE_PAYMENT_SHOPGATE_SORT_ORDER', 'MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID')"
        );
    }

    /**
     * Keep the array empty to disable all configuration options
     *
     * @return multitype:
     */
    public function keys()
    {
        return array('MODULE_PAYMENT_SHOPGATE_STATUS',
                     'MODULE_PAYMENT_SHOPGATE_SORT_ORDER');
    }

    /**
     * set grant access to shopgate configuration
     * to the current user and main administrator
     */
    private function grantAdminAccess()
    {
        if ($this->checkColumn("shopgate", TABLE_ADMIN_ACCESS)) {
            // Create column shopgate in admin_access...
            xtc_db_query(
                "alter table " . TABLE_ADMIN_ACCESS
                . " ADD shopgate INT( 1 ) NOT NULL"
            );

            // ... grant access to to shopgate for main administrator
            xtc_db_query(
                "update " . TABLE_ADMIN_ACCESS
                . " SET shopgate=1 where customers_id=1 LIMIT 1"
            );

            if (!empty($_SESSION['customer_id'])
                && $_SESSION['customer_id'] != 1
            ) {
                // grant access also to current user
                xtc_db_query(
                    "update " . TABLE_ADMIN_ACCESS
                    . " SET shopgate = 1 where customers_id='"
                    . $_SESSION['customer_id']
                    . "' LIMIT 1"
                );
            }

            xtc_db_query(
                "update " . TABLE_ADMIN_ACCESS
                . " SET shopgate = 5 where customers_id = 'groups'"
            );
        }
    }

    /**
     * Install the shopgate order table
     */
    private function installTable()
    {
        xtc_db_query(
            "
			CREATE TABLE IF NOT EXISTS `" . TABLE_ORDERS_SHOPGATE_ORDER . "` (
					`shopgate_order_id` INT(11) NOT NULL AUTO_INCREMENT,
					`orders_id` INT(11) NOT NULL,
					`shopgate_order_number` BIGINT(20) NOT NULL,
					`shopgate_shop_number` BIGINT(20) UNSIGNED DEFAULT NULL,
					`is_paid` tinyint(1) UNSIGNED DEFAULT NULL,
					`is_shipping_blocked` tinyint(1) UNSIGNED DEFAULT NULL,
					`payment_infos` TEXT NULL,
					`is_sent_to_shopgate` tinyint(1) UNSIGNED DEFAULT NULL,
					`is_cancellation_sent_to_shopgate` tinyint(1) UNSIGNED DEFAULT NULL,
					`modified` datetime DEFAULT NULL,
					`created` datetime DEFAULT NULL,
					PRIMARY KEY (`shopgate_order_id`)
			); "
        );

        xtc_db_query(
            "
			CREATE TABLE IF NOT EXISTS `" . TABLE_CUSTOMERS_SHOPGATE_CUSTOMER . "` (
					`shopgate_customer_id` INT(11) NOT NULL AUTO_INCREMENT,
					`customer_id` INT(11) NOT NULL,
					`customer_token` VARCHAR(255) NULL,
					`modified` TIMESTAMP NULL DEFAULT NULL,
					`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`shopgate_customer_id`)
			);
		"
        );
    }

    /**
     * update existing database
     */
    private function updateDatabase()
    {
        if ($this->checkColumn('shopgate_shop_number')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD `shopgate_shop_number` BIGINT(20) UNSIGNED NULL AFTER `shopgate_order_number`;';
            xtc_db_query($qry);
        }

        if ($this->checkColumn('is_paid')) {
            $qry = 'ALTER TABLE  `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `is_paid` TINYINT(1) UNSIGNED NULL AFTER `shopgate_shop_number`;';
            xtc_db_query($qry);
        }

        if ($this->checkColumn('is_shipping_blocked')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `is_shipping_blocked` TINYINT(1) UNSIGNED NULL AFTER  `is_paid`;';
            xtc_db_query($qry);
        }

        if ($this->checkColumn('payment_infos')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `payment_infos` TEXT NULL AFTER  `is_shipping_blocked`;';
            xtc_db_query($qry);
        }

        if ($this->checkColumn('is_sent_to_shopgate')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `is_sent_to_shopgate` TINYINT(1) UNSIGNED NULL AFTER `payment_infos`;';
            xtc_db_query($qry);
        }

        if ($this->checkColumn('is_cancellation_sent_to_shopgate')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `is_cancellation_sent_to_shopgate` TINYINT(1) UNSIGNED NULL AFTER `is_sent_to_shopgate`;';
            xtc_db_query($qry);
        }

        if ($this->checkColumn('modified')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `modified` DATETIME NULL AFTER `is_cancellation_sent_to_shopgate`;';
            xtc_db_query($qry);
        }

        if ($this->checkColumn('created')) {
            $qry = 'ALTER TABLE `' . TABLE_ORDERS_SHOPGATE_ORDER
                . '` ADD  `created` DATETIME NULL AFTER `modified`;';
            xtc_db_query($qry);
        }

        $languages = xtc_db_query(
            'SELECT `languages_id`, `code` FROM `' . TABLE_LANGUAGES . '`;'
        );
        if (empty($languages)) {
            echo MODULE_PAYMENT_SHOPGATE_ERROR_READING_LANGUAGES;

            return;
        }

        try {
            $config = new ShopgateConfigModified();// load global configuration
            $config->loadFile();
        } catch (ShopgateLibraryException $e) {
            echo MODULE_PAYMENT_SHOPGATE_ERROR_LOADING_CONFIG;

            return;
        }

        $languageCodes   = array();
        $configFieldList = array('language', 'redirect_languages');
        while ($language = xtc_db_fetch_array($languages)) {
            // collect language codes to enable redirect
            $languageCodes[] = $language['code'];

            switch ($language['code']) {
                case 'de':
                    $statusNameNew    = 'Versand blockiert (Shopgate)';
                    $statusNameSearch = '%shopgate%';
                    break;
                case 'en':
                    $statusNameNew    = 'Shipping blocked (Shopgate)';
                    $statusNameSearch = '%shopgate%';
                    break;
                default:
                    continue 2;
            }

            $result               = xtc_db_query(
                "SELECT `orders_status_id`, `orders_status_name` " .
                "FROM `" . TABLE_ORDERS_STATUS . "` " .
                "WHERE LOWER(`orders_status_name`) LIKE '" . xtc_db_input(
                    $statusNameSearch
                ) . "' " .
                "AND `language_id` = " . xtc_db_input($language['languages_id'])
                . ";"
            );
            $checkShippingBlocked = xtc_db_fetch_array($result);

            if (!empty($checkShippingBlocked)) {
                $orderStatusShippingBlockedId
                    = $checkShippingBlocked['orders_status_id'];
            } else {
                // if no orders_status_id has been determined yet and the status could not be found, create a new one
                if (!isset($orderStatusShippingBlockedId)) {
                    $result                       = xtc_db_query(
                        "SELECT max(orders_status_id) AS orders_status_id FROM "
                        . TABLE_ORDERS_STATUS
                    );
                    $nextId                       = xtc_db_fetch_array($result);
                    $orderStatusShippingBlockedId = $nextId['orders_status_id']
                        + 1;
                }

                // insert the status into the database
                xtc_db_query(
                    "INSERT INTO `" . TABLE_ORDERS_STATUS . "` " .
                    "(`orders_status_id`, `language_id`, `orders_status_name`) VALUES "
                    .
                    "(" . xtc_db_input($orderStatusShippingBlockedId) . ", "
                    . xtc_db_input($language['languages_id'])
                    . ", '" . xtc_db_input($statusNameNew) . "');"
                );
            }

            // set global order status id
            if ($language['code'] == DEFAULT_LANGUAGE) {
                $config->setOrderStatusShippingBlocked(
                    $orderStatusShippingBlockedId
                );
                $configFieldList[] = 'order_status_shipping_blocked';
            }
        }

        // get the actual definition of the plugin version
        if (!defined("SHOPGATE_PLUGIN_VERSION")) {
            require_once(DIR_FS_CATALOG
                . 'includes/external/shopgate/plugin.php');
        }
        // shopgate table version equals to the SHOPGATE_PLUGIN_VERSION, save that version to the config file
        $config->setShopgateTableVersion(SHOPGATE_PLUGIN_VERSION);
        $configFieldList[] = 'shopgate_table_version';
        // save default language, order_status_id and redirect languages in the configuration
        try {
            $config->setLanguage(DEFAULT_LANGUAGE);
            $config->setRedirectLanguages($languageCodes);
            $config->saveFile($configFieldList);
        } catch (ShopgateLibraryException $e) {
            echo MODULE_PAYMENT_SHOPGATE_ERROR_SAVING_CONFIG;
        }
    }

    /**
     * Check if the column exists in the specified table
     *
     * @param string $columnName
     * @param string $table
     *
     * @return bool
     */
    private function checkColumn($columnName,
        $table = TABLE_ORDERS_SHOPGATE_ORDER
    ) {
        $result = xtc_db_query("show columns from `{$table}`");

        $exists = false;
        while ($field = xtc_db_fetch_array($result)) {
            if ($field['Field'] == $columnName) {
                $exists = true;
                break;
            }
        }

        return !$exists;
    }
}
