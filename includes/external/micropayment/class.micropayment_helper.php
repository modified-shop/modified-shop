<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2015 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH <shop-plugins@micropayment.de>
 */
class micropayment_helper
{
    static $infoServiceDone = false;
    const HTTP_TIMEOUT = 5;

    const INFO_SERVICE_URL                   = 'http://webservices.micropayment.de/public/info/index.php';

    const CONFIG_NAME_CURRENT_VERSION        = 'MODULE_PAYMENT_MCP_SERVICE_CURRENT_VERSION';
    const CONFIG_NAME_REFRESH_INTERVAL       = 'MODULE_PAYMENT_MCP_SERVICE_REFRESH_INTERVAL';
    const CONFIG_NAME_BILLING_URL_CREDITCARD = 'MODULE_PAYMENT_MCP_SERVICE_BILLING_URL_CREDITCARD';
    const CONFIG_NAME_BILLING_URL_DEBIT      = 'MODULE_PAYMENT_MCP_SERVICE_BILLING_URL_DEBIT';
    const CONFIG_NAME_BILLING_URL_SOFORT     = 'MODULE_PAYMENT_MCP_SERVICE_BILLING_URL_SOFORT';
    const CONFIG_NAME_BILLING_URL_PREPAY     = 'MODULE_PAYMENT_MCP_SERVICE_BILLING_URL_PREPAY';

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
            'shop_version' => $this->getShopSignatur(),
            'project'      => MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE,
            'amount'       => ($order->info['pp_total'] * 100),
            'orderid'      => $insert_id,
            'paytext'      => str_replace('#ORDER#',$insert_id,MODULE_PAYMENT_MCP_SERVICE_PAYTEXT),
            'theme'        => MODULE_PAYMENT_MCP_SERVICE_THEME,
            'currency'     => $order->info['currency'],
            'MODsid'       => xtc_session_id(),

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
                $url = constant(self::CONFIG_NAME_BILLING_URL_CREDITCARD);
                break;
            case 'mcp_debit':
                $url = constant(self::CONFIG_NAME_BILLING_URL_DEBIT);
                break;
            case 'mcp_prepay':
                $url = constant(self::CONFIG_NAME_BILLING_URL_PREPAY);
                break;
            case 'mcp_ebank2pay':
                $url = constant(self::CONFIG_NAME_BILLING_URL_SOFORT);
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

    function refreshShopModule()
    {
        if(self::$infoServiceDone) {
            return true;
        }
        $check = xtc_db_query('
          SELECT
              CASE WHEN ISNULL(`last_modified`) THEN
                  1
              ELSE
                  CASE
                      WHEN unix_timestamp(`last_modified`)+`configuration_value` <= unix_timestamp() THEN
                        1
                      ELSE
                        0
                  END
              END `result`
              FROM '.TABLE_CONFIGURATION.'
              WHERE `configuration_key` = "'.self::CONFIG_NAME_REFRESH_INTERVAL.'"');
        $check = xtc_db_fetch_array($check);

        if(!is_array($check) || (isset($check['result']) && $check['result'] != 1)) {
            return false;
        } else {
            if (!$this->getConfig('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID')) {
                return false;
            }
        }
        $data = (array) $this->callInfoService('ShopModulService');
        if(isset($data['current.version'])) {
            $this->setConfig(self::CONFIG_NAME_CURRENT_VERSION,$data['current.version']);
        }
        if(isset($data['refresh.interval'])) {
            $this->setConfig(self::CONFIG_NAME_REFRESH_INTERVAL,$data['refresh.interval']);
        }
        if(isset($data['billing.creditcard.url'])) {
            $this->setConfig(self::CONFIG_NAME_BILLING_URL_CREDITCARD,$data['billing.creditcard.url']);
        }
        if(isset($data['billing.debit.url'])) {
            $this->setConfig(self::CONFIG_NAME_BILLING_URL_DEBIT,$data['billing.debit.url']);
        }
        if(isset($data['billing.sofort.url'])) {
            $this->setConfig(self::CONFIG_NAME_BILLING_URL_SOFORT,$data['billing.sofort.url']);
        }
        if(isset($data['billing.prepay.url'])) {
            $this->setConfig(self::CONFIG_NAME_BILLING_URL_PREPAY,$data['billing.prepay.url']);
        }
        self::$infoServiceDone = true;
    }

    function callInfoService($modul,$params=null)
    {
        if (!$this->getConfig('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID')) {
            if ($this->check_is_service_installed()) {
                if ($this->rslcode) {
                    $url = 'https://' . $this->rslcode . '.micropayment.de';
                } else {
                    $url = 'https://www.micropayment.de';
                }
                echo sprintf(MODULE_PAYMENT_MCP_SERVICE_NO_ACCOUNT, MODULE_PAYMENT_MCP_SERVICE_CSS, $url);
            }
            return false;
        }
        $service_url = self::INFO_SERVICE_URL;

        $url_params = array(
            'action'     => $modul,
            'format'     => 'json',
            'account_id' => $this->getConfig('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID'),
            'shop_version' => $this->getShopSignatur()
        );

        if($params) {
            $url_params = array_merge($params,$url_params);
        }

        try {
            if (extension_loaded('curl')) {
                $r = curl_init($service_url);
                curl_setopt($r, CURLOPT_POST, 1);
                curl_setopt($r, CURLOPT_POSTFIELDS, $url_params);
                curl_setopt($r, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($r, CURLOPT_TIMEOUT, self::HTTP_TIMEOUT);
                $response = curl_exec($r);

                curl_close($r);
            } else {
                $url3 = parse_url($service_url);
                $host = $url3["host"];
                $path = $url3["path"];
                $fp = fsockopen($host, 80, $errno, $errstr, self::HTTP_TIMEOUT);
                if ($fp) {
                    fputs($fp, "GET " . $path . "?" . http_build_query($url_params) . " HTTP/1.0\nHost: " . $host . "\n\n");
                    $buf = null;
                    while (!feof($fp)) {
                        $buf .= fgets($fp, 128);
                    }
                    $lines = explode("\n", $buf);
                    $response = $lines[count($lines) - 1];
                    fclose($fp);
                }
            }
        } catch(Exception $e) {
            return false;
        }

        try {
            $json = json_decode($response);
        } catch (Exception $e) {
            return false;
        }

        if (is_object($json)) {
            return $json;
        } else {
            return false;
        }
    }
    function createConfigParameter(
        $configuration_key, $configuration_value, $configuration_group_id,$sort_order,
        $set_function = false,$use_function = false
    ) {
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