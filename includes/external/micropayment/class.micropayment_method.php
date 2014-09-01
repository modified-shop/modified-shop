<?php
class micropayment_method
{

    var $code;
    var $logo;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = true;
    var $info;
    var $version = '1.0';
    // var $form_action_url=null;
    var $_check;
    var $rslcode = 'r120';
    var $get_url_called = false;

    function micropayment_method()
    {
        $this->form_action_url = $this->getWebUrl();
        $this->tmpOrders = true;
        $this->tmpStatus = MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID;
        $this->check_enabled();
        $this->check();
    }

    function check()
    {
        if ($this->check_is_service_installed()) {
            $check_query = xtc_db_query("SELECT `configuration_value` FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = 'MODULE_PAYMENT_" . strtoupper($this->code) . "_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
            return $this->_check;
        }
        return false;
    }

    function check_enabled()
    {
        $check_query = xtc_db_query("SELECT `configuration_value` FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = 'MODULE_PAYMENT_" . strtoupper($this->code) . "_STATUS' AND configuration_value = 'True'");
        $check = xtc_db_num_rows($check_query);
        $this->enabled = ($check != 0) ? true : false;
        return $this->enabled;
    }

    function selection()
    {
        $selection = array(
            'id' => $this->code,
            'module' => (!empty($this->title_extern)) ? $this->title_extern : $this->title,
            'description' => $this->info
        );

        if (isset($_GET['orderid']) && is_numeric($_GET['orderid'])) {
            $check_query = xtc_db_query("SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$_GET['orderid'] . "' LIMIT 1");
            if ($result = xtc_db_fetch_array($check_query)) {
                if ($result['orders_status'] == MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID) {
                    $this->mcp_remove_order((int)$_GET['orderid'], true);
                    unset($_SESSION['tmp_oID']);
                }
            }
        }

        return $selection;
    }

    function confirmation()
    {
        $selection = array(
            'id' => $this->code,
            'module' => $this->title_extern,
            'description' => $this->info
        );
        return $selection;

    }

    function javascript_validation()
    {
        return true;
    }

    function process_button()
    {

    }

    function after_process()
    {
        return false;
    }

    public function payment_action()
    {
        global $insert_id;

        $order = new order($insert_id);
        $params = array(
            'project' => MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE,
            'amount' => $order->info['pp_total'] * 100,
            'orderid' => $insert_id,
            'title' => MODULE_PAYMENT_MCP_SERVICE_PAYTEXT,
            'theme' => MODULE_PAYMENT_MCP_SERVICE_THEME,
            'currency' => $order->info['currency'],
            xtc_session_name() => xtc_session_id(),

            'mp_user_email' => $order->customer['email_address'],
            'mp_user_firstname' => $order->customer['firstname'],
            'mp_user_surname' => $order->customer['lastname'],
            'mp_user_address' => $order->customer['street_address'],
            'mp_user_zip' => $order->customer['postcode'],
            'mp_user_city' => $order->customer['city'],
            'mp_user_country' => $order->customer['country']['iso_code_2']
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
        $url = $this->form_action_url . '?' . $urlParams;

        xtc_redirect($url);
    }

    function before_process()
    {
        return false;
    }

    function _after_process()
    {
        return false;
    }

    function update_status()
    {
        /**
         * @var $order order;
         */
        global $order;

        if (!$this->check()) {
            $this->enabled = false;
        }
        $minimumAmount = $this->getConfig('MODULE_PAYMENT_".$this->code."_MINIMUM_AMOUNT');
        $maximumAmount = $this->getConfig('MODULE_PAYMENT_".$this->code."_MAXIMUM_AMOUNT');
        $order_total = $order->info['total'];

        if (($minimumAmount > 0 && $order_total < $minimumAmount) || ($maximumAmount > 0 && $order_total > $maximumAmount)) {
            $this->enabled = false;
        }
    }

    function pre_confirmation_check()
    {
        if (empty($_SESSION['cart']->cartID)) {
            $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();
        }
        return false;
    }

    function install()
    {
        if (!$this->check_is_service_installed()) {
            $lastStatusArray = xtc_db_query('SELECT MAX(`orders_status_id`) last_id FROM ' . TABLE_ORDERS_STATUS);
            $t = xtc_db_fetch_array($lastStatusArray);
            $lastStatusId = $t['last_id'] + 1;
            xtc_db_query("INSERT INTO " . TABLE_ORDERS_STATUS . " (`orders_status_id`,`language_id`,`orders_status_name`) VALUES ('" . $lastStatusId . "',1,'Cancelled')");
            xtc_db_query("INSERT INTO " . TABLE_ORDERS_STATUS . " (`orders_status_id`,`language_id`,`orders_status_name`) VALUES ('" . $lastStatusId . "',2,'Storniert')");

            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID', '', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY', '', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE', '', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT', '', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_THEME', 'x1', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_GFX', '', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR', '', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_BGGFX', '', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD', '" . md5(rand(10293, 298437)) . "', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE', '" . md5(rand(10293, 298437)) . "', '6', '0', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID', '0',  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID', '0',  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', NOW())");
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_key`, `configuration_value`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) VALUES ('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID', '" . $lastStatusId . "',  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', NOW())");
        }
    }

    function check_is_service_installed()
    {
        $check_query = xtc_db_query("SELECT `configuration_value` FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = 'MODULE_PAYMENT_MCP_SERVICE_STATUS'");
        return (xtc_db_num_rows($check_query) > 0) ? true : false;
    }

    function keys()
    {
        return array(
            'MODULE_PAYMENT_MCP_SERVICE_STATUS',
            'MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY',
            'MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE',
            'MODULE_PAYMENT_MCP_SERVICE_PAYTEXT',
            'MODULE_PAYMENT_MCP_SERVICE_THEME',
            'MODULE_PAYMENT_MCP_SERVICE_GFX',
            'MODULE_PAYMENT_MCP_SERVICE_BGGFX',
            'MODULE_PAYMENT_MCP_SERVICE_BGCOLOR',
            'MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD',
            'MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID',
            'MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID'
        );
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

    function remove()
    {
        if ($this->isLastModul()) {
            xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` LIKE 'MODULE_PAYMENT_MCP_SERVICE_%'");
        }
    }

    function getWebUrl()
    {
        // only 1 Call needed to prevent multiple calls to micropayment servers


        if(defined('MODULE_PAYMENT_MCP_SERVICE_URL_CALL')) { return $this->form_action_url; }
        define('MODULE_PAYMENT_MCP_SERVICE_URL_CALL',true);

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

        if ($this->getConfig('MODULE_PAYMENT_MCP_SERVICE_URL')) {
            return $this->getConfig('MODULE_PAYMENT_MCP_SERVICE_URL') . $this->url;
        }
        $service_url = 'http://webservices.micropayment.de/public/info/index.php';

        $url_params = array(
            'action' => 'GenerateUrl',
            'format' => 'json',
            'account_id' => $this->getConfig('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID')
        );

        if (extension_loaded('curl')) {
            $r = curl_init($service_url);
            curl_setopt($r, CURLOPT_POST, 1);
            curl_setopt($r, CURLOPT_POSTFIELDS, $url_params);
            curl_setopt($r, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($r);

            curl_close($r);
        } else {
            // we using HTTP without curl

            // parse the url to get
            // host, path and query
            $url3 = parse_url($service_url);
            $host = $url3["host"];
            $path = $url3["path"];

            // open the connection
            $fp = fsockopen($host, 80, $errno, $errstr, 10);
            if ($fp) {
                // send the request
                fputs($fp, "GET " . $path . "?" . http_build_query($url_params) . " HTTP/1.0\nHost: " . $host . "\n\n");
                while (!feof($fp)) {
                    $buf .= fgets($fp, 128);
                }
                $lines = explode("\n", $buf);
                // get the content
                $response = $lines[count($lines) - 1];

                //close the connection
                fclose($fp);
            }
        }

        try {
            $json = json_decode($response);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

        if (!empty($json) && $json->billing) {
            $url = 'https://' . $json->billing;
            xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . ' (configuration_key,configuration_value,configuration_group_id,date_added) VALUES ("MODULE_PAYMENT_MCP_SERVICE_URL","' . $url . '",6,now())');
            return $url . $this->url;
        } else {
            echo 'Critical: Cannot get URL for Payment !!! Did you have inserted the Account-ID ?';
        }

        //return 'https://billing.micropayment.de';
    }

    function mcp_remove_order($order_id, $restock = false)
    {
        if ($restock) {
            $order_query = xtc_db_query("SELECT orders_products_id,
                                            products_id,
                                            products_quantity
                                       FROM " . TABLE_ORDERS_PRODUCTS . "
                                      WHERE orders_id = '" . (int)$order_id . "'");
            while ($order = xtc_db_fetch_array($order_query)) {
                xtc_db_query("UPDATE " . TABLE_PRODUCTS . "
                           SET products_quantity = products_quantity + " . $order['products_quantity'] . ",
                               products_ordered = products_ordered - " . $order['products_quantity'] . "
                         WHERE products_id = '" . (int)$order['products_id'] . "'");
                if (ATTRIBUTE_STOCK_CHECK == 'true') {
                    $orders_attributes_query = xtc_db_query("SELECT products_options,
                                                            products_options_values
                                                       FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                                                      WHERE orders_id = '" . (int)$order_id . "'
                                                        AND orders_products_id = '" . $order['orders_products_id'] . "'");
                    while ($orders_attributes = xtc_db_fetch_array($orders_attributes_query)) {
                        $attributes_query = xtc_db_query("SELECT pa.products_attributes_id
                                                  FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov,
                                                       " . TABLE_PRODUCTS_OPTIONS . " po,
                                                       " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                 WHERE po.products_options_name = '" . $orders_attributes['products_options'] . "'
                                                   AND po.products_options_id = pa.options_id
                                                   AND pov.products_options_values_id = pa.options_values_id
                                                   AND pov.products_options_values_name = '" . $orders_attributes['products_options_values'] . "'
                                                   AND pa.products_id = '" . $order['products_id'] . "'
                                                 LIMIT 1");
                        if (xtc_db_num_rows($attributes_query) == 1) {
                            $attributes = xtc_db_fetch_array($attributes_query);
                            xtc_db_query("UPDATE " . TABLE_PRODUCTS_ATTRIBUTES . "
                                 SET attributes_stock = attributes_stock + " . $order['products_quantity'] . "
                               WHERE products_attributes_id = '" . $attributes['products_attributes_id'] . "'");
                        }
                    }
                }
            }
        }

        xtc_db_query("DELETE FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$order_id . "'");
        xtc_db_query("DELETE FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = '" . (int)$order_id . "'");
        xtc_db_query("DELETE FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE orders_id = '" . (int)$order_id . "'");
        xtc_db_query("DELETE FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE orders_id = '" . (int)$order_id . "'");
        xtc_db_query("DELETE FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = '" . (int)$order_id . "'");
    }
}