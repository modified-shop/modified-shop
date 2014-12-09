<?php
/**
 * Module Installer.
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

/**
 * Handle the installation of the module for xtCommerce
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KlarnaInstaller
{

    /**
     * Payment option identifier, KiTT constant.
     *
     * @var string
     */
    private $_option;

    /**
     * Literal name of the module used for the order status
     *
     * @var string
     */
    private $_moduleName;

    /**
     * Module prefix in database table
     *
     * @var string
     */
    private $_module;

    /**
     * ID of new order status
     *
     * @var int
     */
    private $_newId;

    /**
     * Default config array.
     *
     * @var array
     */
    private $_defaultArray = array(
        'configuration_key' => 'null',
        'configuration_value' => '',
        'configuration_group_id' => 'null',
        'sort_order' => 'null',
        'use_function' => '',
        'set_function' => '',
        'date_added' => 'now()'
    );

    /**
     * Instantiate the installer.
     *
     * @param string $option payment option
     */
    public function __construct($option)
    {
        $this->_option = $option;

        switch ($this->_option) {
        case KiTT::PART:
            $this->_module = "MODULE_PAYMENT_KLARNA_PARTPAYMENT_";
            $this->_moduleName = 'Part Payment';
            break;
        case KiTT::SPEC:
            $this->_module = "MODULE_PAYMENT_KLARNA_SPECCAMP_";
            $this->_moduleName = 'Campaign';
            break;
        case KiTT::INVOICE:
        default:
            $this->_module = "MODULE_PAYMENT_KLARNA_INVOICE_";
            $this->_moduleName = 'Invoice';
            break;
        }
        $this->_klarnaDB = new XtcKlarnaDB;
    }

    /**
     * Install the payment module
     *
     * @return void
     */
    public function installPaymentModule()
    {
        $this->_addOrderStatus();
        $this->_installModule();
    }

    /**
     * Add the order status for this payment option to the database
     *
     * @return void
     */
    private function _addOrderStatus()
    {
        $result = $this->_klarnaDB->query(
            "SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS .
            " ORDER BY orders_status_id DESC LIMIT 1"
        )->getArray();

        $this->_newId = (int) $result['orders_status_id'] + 1;

        $languages = xtc_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
			$language_id = $languages[$i]['id'];
        $this->_klarnaDB->query(
            "INSERT INTO " . TABLE_ORDERS_STATUS .
				" (orders_status_id, orders_status_name,language_id) ".
				"VALUES ('$this->_newId', 'Waiting [{$this->_moduleName}]','".$language_id."')"
        );
		}
    }

    /**
     * install module
     *
     * @return void
     */
    private function _installModule()
    {
        $this->_klarnaDB->query(
            'CREATE TABLE IF NOT EXISTS `klarna_ordernum` ('.
            '`orders_id` INT NOT NULL , '.
            '`klarna_ref` VARCHAR( 256 ) NOT NULL , '.
            'UNIQUE ( `orders_id` ) '.
            //~ 'UNIQUE ( `orders_id` ), '.
            //~ 'FOREIGN KEY ( `orders_id` ) REFERENCES ' .
            //~ TABLE_ORDERS . ' ( `orders_id` )' .
            ');'
        );
        $this->_klarnaDB->query("CREATE TABLE IF NOT EXISTS `klarna_pclasses` (
                `eid` int(10) unsigned NOT NULL,
                `id` int(10) unsigned NOT NULL,
                `type` tinyint(4) NOT NULL,
                `description` varchar(255) NOT NULL,
                `months` int(11) NOT NULL,
                `interestrate` decimal(11,2) NOT NULL,
                `invoicefee` decimal(11,2) NOT NULL,
                `startfee` decimal(11,2) NOT NULL,
                `minamount` decimal(11,2) NOT NULL,
                `country` int(11) NOT NULL,
                `expire` int(11) NOT NULL,
                KEY `id` (`id`)
            )");

        foreach ($this->_getConfigArray() as $config) {
            $merged = array_merge($this->_defaultArray, $config);
            $query_string = implode(', ', array_keys($merged));
            $query_values = implode(
                '", "', array_map(
                    "xtc_db_input", array_values($merged)
                )
            );

            $this->_klarnaDB->query(
                "INSERT INTO ". TABLE_CONFIGURATION .
                "({$query_string}) VALUES (\"{$query_values}\")"
            );
        }
    }

    /**
     * Get the configuration array to be inserted into the database.
     *
     * @return array configuration array for specified payment option
     */
    private function _getConfigArray()
    {
        $configs = array(
            array(
                'configuration_key' => "{$this->_module}STATUS",
                'configuration_value' => 'True',
                'configuration_group_id' => '6',
                'sort_order' => '0',
                'set_function' =>
                    'xtc_cfg_select_option(array(\'True\', \'False\'), '
            ),
            array(
                'configuration_key' => "{$this->_module}LATESTVERSION",
                'configuration_value' => 'True',
                'configuration_group_id' => '6',
                'sort_order' => '0',
                'set_function' =>
                    'xtc_cfg_select_option(array(\'True\', \'False\'), '
            ),
            array(
                'configuration_key' => "{$this->_module}ZONE",
                'configuration_value' => '0',
                'configuration_group_id' => '6',
                'sort_order' => '2',
                'use_function' => 'xtc_get_zone_class_title',
                'set_function' => 'xtc_cfg_pull_down_zone_classes('
            ),
            array(
                'configuration_key' => "{$this->_module}ARTNO",
                'configuration_value' => 'id',
                'configuration_group_id' => '6',
                'sort_order' => '8',
                'set_function' =>
                    'xtc_cfg_select_option(array(\'id\', \'model\'), '
            ),
            array(
                'configuration_key' => "{$this->_module}SORT_ORDER",
                'configuration_value' => '0',
                'configuration_group_id' => '6',
                'sort_order' => '20'
            ),
            array(
                'configuration_key' => "{$this->_module}ORDER_STATUS_ID",
                'configuration_value' => '0',
                'configuration_group_id' => '6',
                'sort_order' => '20',
                'set_function' => 'xtc_cfg_pull_down_order_statuses(',
                'use_function' => 'xtc_get_order_status_name'
            ),
            array(
                'configuration_key' =>
                    "{$this->_module}ORDER_STATUS_PENDING_ID",
                'configuration_value' => $this->_newId,
                'configuration_group_id' => '6',
                'sort_order' => '11',
                'set_function' => 'xtc_cfg_pull_down_order_statuses(',
                'use_function' => 'xtc_get_order_status_name'
            ),
            array(
                'configuration_key' => "{$this->_module}LIVEMODE",
                'configuration_value' => 'True',
                'configuration_group_id' => '6',
                'sort_order' => '21',
                'set_function' =>
                    'xtc_cfg_select_option(array(\'True\', \'False\'), '
            ),
            array(
                'configuration_key' =>
                    "{$this->_module}ALLOWED",
                'configuration_value' =>
                    implode(',', KiTT_CountryLogic::supportedCountries()),
                'configuration_group_id' => '6',
                'sort_order' => '14'
            ),

        );

        foreach (KiTT_CountryLogic::supportedCountries() as $country) {
            $flags = "<span class='klarna_flag_"
                . strtolower($country) . "'></span>";
            $configs[] = array(
                'configuration_key' => "{$this->_module}SECRET_{$country}",
                'configuration_group_id' => '6',
                'sort_order' => '3'
            );
            $configs[] = array(
                'configuration_key' => "{$this->_module}EID_{$country}",
                'configuration_value' => '0',
                'configuration_group_id' => '6',
                'sort_order' => '1'
            );
            if (KiTT_CountryLogic::needAGB($country)) {
                $configs[] = array(
                    'configuration_key' => "{$this->_module}AGB_LINK_{$country}",
                    'configuration_value' => 'http://',
                    'configuration_group_id' => '',
                    'sort_order' => ''
                );
            }
        }

        return $configs;
    }
}
