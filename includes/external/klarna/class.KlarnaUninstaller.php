<?php
/**
 * Module Uninstaller.
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
 * Handle the uninstallation of the module for xtCommerce
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KlarnaUninstaller
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
     * PClass type for this payment method
     */
    private $_type;

    /**
     * Uninstantiate the installer.
     *
     * @param string $option payment option
     */
    public function __construct($option)
    {
        $this->_option = $option;

        switch ($this->_option) {
        case KiTT::PART:
            $this->_moduleName = 'Part Payment';
            $this->_type = 'type <> 2';
            break;
        case KiTT::SPEC:
            $this->_moduleName = 'Campaign';
            $this->_type = 'type = 2';
            break;
        case KiTT::INVOICE:
            $this->_moduleName = 'Invoice';
            $this->_type = '';
            break;
        default:
            $this->_moduleName = '';
            $this->_type = '';
        }

        $this->_klarnaDB = new XtcKlarnaDB();
    }

    /**
     * Uninstall payment module.
     *
     * @param array $keys keys to remove from the database.
     *
     * @return void
     */
    public function uninstallModule($keys)
    {
        $this->_klarnaDB->query(
            "DELETE FROM " . TABLE_CONFIGURATION .
            " WHERE configuration_key in ('" .
            implode("', '", $keys) . "')"
        );

        if ($this->_moduleName !== '') {
            $this->_klarnaDB->query(
                "DELETE FROM " . TABLE_ORDERS_STATUS .
                " WHERE orders_status_name = 'Waiting [{$this->_moduleName}]'"
            );

            if ($this->_type !== '') {
                $this->_klarnaDB->query(
                    "DELETE FROM klarna_pclasses WHERE {$this->_type}"
                );
            }

            if ($this->_klarnaDB->query("SELECT * FROM klarna_pclasses")->count() == 0) {
                $this->_klarnaDB->query("DROP TABLE IF EXISTS klarna_pclasses");
            }
        }
    }
}
