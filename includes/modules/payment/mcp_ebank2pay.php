<?php
$method_class_file = dirname(__FILE__).'/../../external/micropayment/class.micropayment_method.php';
if (file_exists($method_class_file)) {
    require_once($method_class_file);
} else {
    echo '<font color="red">Micropayment&trade; Modul ist nicht vollst&auml;ndig installiert!</font>';
    return false;
}

class mcp_ebank2pay extends micropayment_method {
    var $version = '1.0';
    var $code = 'mcp_ebank2pay';
    var $url = '/ebank2pay/event';

    function mcp_ebank2pay()
    {
        global $order, $language;
        $this->title       = MODULE_PAYMENT_MCP_EBANK2PAY_TEXT_TITLE;
        $this->title_extern = MODULE_PAYMENT_MCP_EBANK2PAY_TEXT_TITLE_EXTERN;
        $this->description = MODULE_PAYMENT_MCP_EBANK2PAY_TEXT_DESCRIPTION;
        $this->sort_order  = MODULE_PAYMENT_MCP_EBANK2PAY_SORT_ORDER;
        $this->info        = MODULE_PAYMENT_MCP_EBANK2PAY_TEXT_INFO;
        parent::micropayment_method();
    }

    function install() {
        if(!$this->check_is_service_installed()) {
            parent::install();
        }

        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_EBANK2PAY_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_EBANK2PAY_MINIMUM_AMOUNT', '0', '6', '0', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_EBANK2PAY_MAXIMUM_AMOUNT', '500', '6', '0', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_EBANK2PAY_SORT_ORDER', '3', '6', '0', NOW())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) values ('MODULE_PAYMENT_MCP_EBANK2PAY_ALLOWED', '', '6', '0', NOW())");


    }

    function keys() {
        $array = array(
            'MODULE_PAYMENT_MCP_EBANK2PAY_STATUS',
            'MODULE_PAYMENT_MCP_EBANK2PAY_MINIMUM_AMOUNT',
            'MODULE_PAYMENT_MCP_EBANK2PAY_MAXIMUM_AMOUNT',
            'MODULE_PAYMENT_MCP_EBANK2PAY_SORT_ORDER',
            'MODULE_PAYMENT_MCP_EBANK2PAY_ALLOWED'
        );
        $array = array_merge($array,parent::keys());
        return $array;
    }
    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` LIKE 'MODULE_PAYMENT_MCP_EBANK2PAY_%'");
        parent::remove();
    }
}
?>