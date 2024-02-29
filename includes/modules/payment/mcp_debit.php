<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2022 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH (TE) <support@micropayment.de>
 */
if(!class_exists('micropayment_method',false)) {
    $method_class_file = dirname(__FILE__).'/../../external/micropayment/class.micropayment_method.php';
    require_once($method_class_file);
}
class mcp_debit extends micropayment_method
{
    var $code;
    var $title;
    var $title_extern;
    var $info;
    var $description;
    var $sort_order;
    var $url;

    function __construct()
    {
        global $order, $language;
        $this->code         = 'mcp_debit';
        $this->title        = MODULE_PAYMENT_MCP_DEBIT_TEXT_TITLE;
        $this->title_extern = MODULE_PAYMENT_MCP_DEBIT_TEXT_TITLE_EXTERN;
        $this->description  = MODULE_PAYMENT_MCP_DEBIT_TEXT_DESCRIPTION;
        $this->sort_order   = ((defined('MODULE_PAYMENT_MCP_DEBIT_SORT_ORDER')) ? MODULE_PAYMENT_MCP_DEBIT_SORT_ORDER : '');
        $this->info         = MODULE_PAYMENT_MCP_DEBIT_TEXT_INFO;
        $this->url          = '/lastschrift/event/';
        parent::__construct();
    }

    function install()
    {
        //if(!$this->check_is_service_installed()) {
            parent::install();
        //}

        $this->createConfigParameter('MODULE_PAYMENT_MCP_DEBIT_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\',\'False\'),');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_DEBIT_MINIMUM_AMOUNT', '0', '6', '0');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_DEBIT_MAXIMUM_AMOUNT', '500', '6', '0');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_DEBIT_SORT_ORDER', '2', '6', '0');
        $this->createConfigParameter('MODULE_PAYMENT_MCP_DEBIT_ALLOWED', '', '6', '0');
    }

    function keys() {
        $array = array(
            'MODULE_PAYMENT_MCP_DEBIT_STATUS',
            'MODULE_PAYMENT_MCP_DEBIT_MINIMUM_AMOUNT',
            'MODULE_PAYMENT_MCP_DEBIT_MAXIMUM_AMOUNT',
            'MODULE_PAYMENT_MCP_DEBIT_SORT_ORDER',
            'MODULE_PAYMENT_MCP_DEBIT_ALLOWED'
        );
        $array = array_merge($array,parent::keys());
        return $array;
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` LIKE 'MODULE_PAYMENT_MCP_DEBIT_%'");
        parent::remove();
    }
}
