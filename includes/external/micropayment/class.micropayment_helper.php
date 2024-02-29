<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2022 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH (TE) <support@micropayment.de>
 */
class micropayment_helper
{
    var $code;
    var $version;
    
    static $infoServiceDone = false;

    const HTTP_TIMEOUT = 5;
    const INFO_SERVICE_URL                   = 'http://webservices.micropayment.de/public/info/index.php';

    const CONFIG_NAME_CURRENT_VERSION         = 'MODULE_PAYMENT_MCP_SERVICE_CURRENT_VERSION';
    const CONFIG_NAME_REFRESH_INTERVAL        = 'MODULE_PAYMENT_MCP_SERVICE_REFRESH_INTERVAL';
    const CONFIG_NAME_BILLING_URL_CREDITCARD  = 'https://creditcard.micropayment.de/creditcard/event/';
    const CONFIG_NAME_BILLING_URL_DEBIT       = 'https://sepadirectdebit.micropayment.de/lastschrift/event/';
    const CONFIG_NAME_BILLING_URL_SOFORT      = 'https://directbanking.micropayment.de/sofort/event/';
    const CONFIG_NAME_BILLING_URL_PREPAY      = 'https://prepayment.micropayment.de/prepay/event/';
    const CONFIG_NAME_BILLING_URL_GIROPAY     = 'https://paydirekt.micropayment.de/paydirekt/event/';
    const CONFIG_NAME_BILLING_URL_PAYPAL      = 'https://paypal.micropayment.de/paypal/event/';
    const CONFIG_NAME_BILLING_URL_PAYSAFECARD = 'https://paysafecard.micropayment.de/paysafecard/event/';

    private function getShopSignatur()
    {
        require_once(DIR_FS_INC.'get_database_version.inc.php');
        $db_version = get_database_version();
        return 'modifiedshop:' . $db_version['full'] . ':' . $this->version;
    }

    function generateBillingUrl($order)
    {
        global $insert_id;
        $params = array(
            //'shop_version' => $this->getShopSignatur(),
            'currency'     => $order->info['currency'],
            'project'      => MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE,
            'amount'       => (round($order->info['pp_total'], 2) * 100),
            'orderid'      => $insert_id,
            'paytext'      => str_replace('#ORDER#',$insert_id,MODULE_PAYMENT_MCP_SERVICE_PAYTEXT),
            'theme'        => MODULE_PAYMENT_MCP_SERVICE_THEME,
            'MODsid'       => xtc_session_id(),
            'producttype'  => 'cart',
            'testmode'     => (bool)json_decode(strtolower(MODULE_PAYMENT_MCP_SERVICE_TESTMODE)),

            'mp_user_email'     => $order->customer['email_address'],
            'mp_user_firstname' => $order->customer['firstname'],
            'mp_user_surname'   => $order->customer['lastname'],
            'mp_user_address'   => $order->customer['street_address'],
            'mp_user_zip'       => $order->customer['postcode'],
            'mp_user_city'      => $order->customer['city']
        );

        if (defined('MODULE_PAYMENT_MCP_SERVICE_GFX') && MODULE_PAYMENT_MCP_SERVICE_GFX != null) {
            $params['gfx'] = MODULE_PAYMENT_MCP_SERVICE_GFX;
        }
        if (defined('MODULE_PAYMENT_MCP_SERVICE_BGGFX') && MODULE_PAYMENT_MCP_SERVICE_BGGFX != null) {
            $params['bggfx'] = MODULE_PAYMENT_MCP_SERVICE_BGGFX;
        }
        if (defined('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR') && MODULE_PAYMENT_MCP_SERVICE_BGCOLOR) {
            $params['bgcolor'] = MODULE_PAYMENT_MCP_SERVICE_BGCOLOR;
        }

        $urlParams = http_build_query($params, null, '&');
        $seal = md5($urlParams . MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY);
        $urlParams .= '&seal=' . $seal;

        switch($this->code) {
            case 'mcp_creditcard':
                $url = self::CONFIG_NAME_BILLING_URL_CREDITCARD;
                break;
            case 'mcp_debit':
                $url = self::CONFIG_NAME_BILLING_URL_DEBIT;
                break;
            case 'mcp_prepay':
                $url = self::CONFIG_NAME_BILLING_URL_PREPAY;
                break;
            case 'mcp_ebank2pay':
            case 'mcp_sofort':
                $url = self::CONFIG_NAME_BILLING_URL_SOFORT;
                break;
            case 'mcp_paypal':
                $url = self::CONFIG_NAME_BILLING_URL_PAYPAL;
                break;
            case 'mcp_paysafecard':
                $url = self::CONFIG_NAME_BILLING_URL_PAYSAFECARD;
                break;
            case 'mcp_giropay':
                $url = self::CONFIG_NAME_BILLING_URL_GIROPAY;
                break;
            default: throw new Exception('UNKNOWN PAYMODULE'); break;
        }
        $url .= '?' . $urlParams;

        return $url;
    }
    function addToMicropaymentOrders($order_id,$payment_method)
    {
        xtc_db_query(
            sprintf(
                'INSERT INTO micropayment_orders (`order_id`,`payment_method`,`createdon`) VALUES ("%s","%s",NOW())',
                xtc_db_prepare_input($order_id),
                xtc_db_prepare_input($payment_method)
            )
        );
    }

    function addToMicropaymentLog($insert_id,$status)
    {
        xtc_db_query(
            sprintf(
                'INSERT INTO `micropayment_log` (`order_id`,`auth`,`amount`,`function`) VALUES ("%s","%s","%s","%s")',
                xtc_db_prepare_input($insert_id),
                xtc_db_prepare_input('no_auth'),
                xtc_db_prepare_input('0'),
                xtc_db_prepare_input('new')
            )
        );
    }

    function _createOrderStatus($id,$languageId,$title)
    {
        $check_query = xtc_db_query(
            sprintf(
                'SELECT `orders_status_id` FROM %s WHERE `language_id` = "%s" AND orders_status_name = "%s"',
                TABLE_ORDERS_STATUS,
                $languageId,
                $title
            )
        );
        $check_data = xtc_db_fetch_array($check_query);
        $exist = (isset($check_data['orders_status_id']))?$check_data['orders_status_id']:null;
        if(!$exist) {
            xtc_db_query(
                sprintf(
                    'INSERT INTO %s (`orders_status_id`,`language_id`,`orders_status_name`) VALUES ("%s","%s","%s")',
                    TABLE_ORDERS_STATUS,
                    $id,
                    $languageId,
                    $title
                )
            );
            return $id;
        } else {
            return $check_data['orders_status_id'];
        }
    }
    function getConfig($key)
    {
        $query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = '" . $key . "'");
        $result = xtc_db_fetch_array($query);
        if (!empty($result['configuration_value'])) {
            return $result['configuration_value'];
        } else {
            return null;
        }

    }

    // Return if the Submodul is the last vom Micropayment
    function isLastModul()
    {
        $check_query = xtc_db_query("SELECT configuration_key,configuration_value FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` LIKE 'MODULE_PAYMENT_MCP_%STATUS'");
        return (xtc_db_num_rows($check_query) > 1) ? false : true;

    }

    private function setConfig($name,$value)
    {
        xtc_db_query(
            'UPDATE `' . TABLE_CONFIGURATION . '`
                SET `configuration_value` = "' . xtc_db_prepare_input($value) . '" ,
                    `last_modified` = NOW()
            WHERE `configuration_key` = "' . $name . '"'
        );
    }

    function createConfigParameter(
        $configuration_key, $configuration_value, $configuration_group_id,$sort_order,
        $set_function = false,$use_function = false
    ) {

      // check if setting already exists
      $sql = 'SELECT * FROM `'.TABLE_CONFIGURATION.'` WHERE `configuration_key`= "'.addslashes($configuration_key).'" ';
      $res = xtc_db_query($sql);
      if (!empty($res->num_rows)) return true;

        if($set_function) {
            $queryTpl = '
              INSERT INTO `%s` (
                `configuration_key`,
                `configuration_value`,
                `configuration_group_id`,
                `sort_order`,
                `set_function`,
                `use_function`,
                `date_added`
              ) VALUES (
                "%s","%s","%s","%s","%s","%s",NOW()
            )';
            $query = sprintf(
                $queryTpl,
                TABLE_CONFIGURATION,
                $configuration_key,
                $configuration_value,
                $configuration_group_id,
                $sort_order,
                ($set_function)?$set_function:null,
                ($use_function)?$use_function:null
            );
        } else {
            $queryTpl = '
              INSERT INTO `%s` (
                `configuration_key`,
                `configuration_value`,
                `configuration_group_id`,
                `sort_order`,
                `date_added`
              ) VALUES (
                "%s","%s","%s","%s",NOW()
            )';
            $query = sprintf(
                $queryTpl,
                TABLE_CONFIGURATION,
                $configuration_key,
                $configuration_value,
                $configuration_group_id,
                $sort_order
            );
        }


        xtc_db_query($query);
    }

    function getLastEventFromMicropaymentLog($orderId)
    {
        $event = xtc_db_query(sprintf('SELECT `function` FROM `micropayment_log` WHERE `order_id` = "%s" ORDER BY `created` DESC LIMIT 1',xtc_db_prepare_input($orderId)));
        $event = xtc_db_fetch_array($event);
        if(count($event)>0) {
            return $event['function'];
        } else {
            return null;
        }
    }

}