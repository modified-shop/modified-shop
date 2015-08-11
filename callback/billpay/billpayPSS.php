<?php
/**
 * Plugin Status Screen
 *
 * @param $_GET['paymentMethod']    'BILLPAY'   May switch different plugin log files.
 * @param $_GET['hideLog']          false       Disables loading log file ie. when it's too big.
 * @param $_GET['mid']              ''          Merchant ID
 * @param $_GET['pid']              ''          Portal ID
 * @param $_GET['bpsecure']         ''          Md5(bpsecure)
 */
chdir('../../');
/** @noinspection PhpIncludeInspection */
include('includes/application_top.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_WS_INCLUDES . 'external/billpay/base/billpayBase.php');

class BillpayPSS
{
    var $billpay;
    var $data = array();

    var $isEnabled = true;
    var $isShowLog = false;
    var $isShowFiles = true;
    var $isAllowRewrite = true;

    function BillpayPSS()
    {
        $paymentMethod = empty($_GET['paymentMethod']) ? 'BILLPAY' : $_GET['paymentMethod'];
        /** @var billpayBase $billpay */
        $this->billpay = billpayBase::PaymentInstance($paymentMethod);
        if (!$this->billpay->isTestMode) {
            $this->isEnabled = false;
        }
        if (!$this->isEnabled) {
            die('Plugin Status Screen disabled for production mode.');
        }
        if (!empty($_GET['showLog'])) {
            $this->isShowLog = true;
        }
        $this->data['auth'] = $_GET['auth'];
        $this->data['paymentMethod'] = $paymentMethod;
        $this->data['currentUrl'] = "/callback/billpay/billpayPSS.php?auth=".$this->data['auth'];
        $this->authorize();
    }

    function getAuthorizationString()
    {
        return md5("PluginStatusScreen:".$this->billpay->bp_merchant.":".$this->billpay->bp_portal.":".$this->billpay->bp_secure.":PluginStatusScreen");
    }

    function authorize()
    {
        $auth = $this->getAuthorizationString();
        if ($this->data['auth'] != $auth) {
            $this->billpay->_logError("Plugin Status Screen: Invalid authentication.");

            if (false) {
                $this->billpay->_logDebug("Should use: $auth\n");
            }
            die('Invalid authentication.');
        }
    }

    function execute()
    {
        $this->loadConfiguration();
        $this->loadLog();
        $this->loadFiles();
        $this->devActions();

        $this->render();
    }

    function loadConfiguration()
    {
        $table = TABLE_CONFIGURATION;
        $this->data['bpyConfigPayment']   = BillpayDB::DBFetchArray("SELECT * FROM $table WHERE configuration_key LIKE 'MODULE_PAYMENT_BILLPAY%'");
        foreach ($this->data['bpyConfigPayment'] as $key => $val) {
            if (in_array($val['configuration_key'], array('MODULE_PAYMENT_BILLPAY_GS_SECURE'))) {
                $this->data['bpyConfigPayment'][$key]['configuration_value'] = '*hidden*';
            }
        }
        $this->data['bpyConfigOT']        = BillpayDB::DBFetchArray("SELECT * FROM $table WHERE configuration_key LIKE 'MODULE_ORDER_TOTAL_BILLPAY%'");
        $this->data['bpyConfigOTPL']      = BillpayDB::DBFetchArray("SELECT * FROM $table WHERE configuration_key LIKE 'MODULE_ORDER_TOTAL_Z_PAYLATER_%'");

        // in some shop modifications, configuration_value has fixed length
        if (false) {
            $this->data['bpyConfigPaymentInstalled'] = BillpayDB::DBFetchValue("SELECT configuration_value FROM $table WHERE configuration_key LIKE 'MODULE_PAYMENT_INSTALLED'");
        }
    }

    function loadLog()
    {
        $this->data['log'] = '';
        if (!$this->isShowLog) {
            $this->data['log'] = 'Plugin log hidden, use &showLog=1';
            return;
        }
        /** @var string $pluginLogPath */
        $pluginLogPath = $this->billpay->_logPath;
        if ((function_exists('version_compare')) && (version_compare(PHP_VERSION, '5.0.0', '>='))) {
            $pluginLog = file_get_contents($pluginLogPath);
            // TODO: what if file is LARGE?
        } else {
            $handle = fopen($pluginLogPath, 'r');
            while (!feof($handle)) {
                $this->data['log'] .= fread($handle, 8192);
            }
            fclose($handle);
        }
        if (empty($pluginLog)) {
            $this->data['log'] = 'Cannot read plugin log.';
        }
    }

    function loadFiles()
    {
        /** @noinspection PhpIncludeInspection */
        include('includes/external/billpay/base/BillpayHooks.php');
        $billpayHooks = new BillpayHooks();
        $hookData = $billpayHooks->getHookData();
        $this->data['shop_identifier'] = $billpayHooks->getShopIdentifier();

        if ($this->isAllowRewrite && $_GET['hookRewrite']) {
            $this->rewriteFiles($hookData, $_GET['hookRewrite']);
            $hookData = $billpayHooks->getHookData(); // refresh hook data
        }

        foreach ($hookData as $file => $hooks) {
            $fileContent = file_get_contents($file);
            foreach ($hooks as $hookKey => $hook) {
                $hookData[$file][$hookKey]['isHooked'] = $isHooked = strpos($fileContent, $hook['hash']) !== false;
                if ($isHooked) {
                    continue;
                }
                $posPre = strpos($fileContent, $hook['pre']);
                $hookData[$file][$hookKey]['isHookPre'] = $isHookPre = $posPre !== false;
                if (!$isHookPre) {
                    continue;
                }
                $fileContentModified = substr($fileContent, $posPre + strlen($hook['pre']));
                $posPost = strpos($fileContentModified, $hook['post']);
                $hookData[$file][$hookKey]['isHookPost'] = $isHookPost = $posPost !== false;
                if (!$isHookPre) {
                    continue;
                }
                $fileContentModified = substr($fileContentModified, 0, $posPost);
                $hookData[$file][$hookKey]['currentContent'] = $fileContentModified;
            }
            $isBillpayModified = stripos($fileContent, 'billpay') !== false;
            if ($isBillpayModified) {
                $hooks[$file]['isBillpayModified']['isHooked'] = true;
            }
        }

        $this->data['hookData'] = $hookData;


        // MD5 of files
        $files = array();
        $fileContent = "";
        if (!empty($_GET['md5file']))
        {
            $this->data['md5file'] = preg_replace('@!@', '/', $_GET['md5file']);
            $md5_json = file_get_contents("billpayMD5.json");
            $files = json_decode($md5_json, true);
            foreach ($files as $key => $val) {
                $content = file_get_contents($key);
                if ($key == $this->data['md5file']) {
                    $fileContent = $content;
                }
                $files[$key]['len_current'] = mb_strlen($content);
                $files[$key]['md5_current'] = md5($content);
            }
        }
        $this->data['filesMd5'] = $files;
        $this->data['fileContent'] = $fileContent;

    }

    /**
     * Functions that helps in development, but are too dangerous to be enabled on merchant shop.
     */
    function devActions()
    {
        if (!empty($_GET['newOrderId'])) {
            // changing autoincrement IDs
            if (!in_array($this->billpay->bp_merchant, array(4441))) {
                die('Action allowed only on dev accounts.');
            }
            BillpayDB::DBFetchArray("ALTER TABLE orders AUTO_INCREMENT = ".(int)$_GET['newOrderId']);
        }
        if (!empty($_GET['newOrderPrefix'])) {
            if (!in_array($this->billpay->bp_merchant, array(4441))) {
                die('Action allowed only on dev accounts.');
            }
            $orderPrefix = $_GET['newOrderPrefix'];
            if (!preg_match('@^[a-zA-Z0-9\-\/]+$@', $orderPrefix)) {
                die('Invalid order prefix');
            }
            file_put_contents('includes/external/billpay/base/debug.php', '<?php $this->orderPrefix = "' . $orderPrefix . '";');
        }
    }

    function render()
    {
        $data = $this->data;

        /** @noinspection PhpIncludeInspection */
        include('includes/external/billpay/templates/plugin_status_screen.php');
        if (false) {
            print_r($data);
        }
        exit();
    }

    function rewriteFiles($hookData, $rewrite)
    {
        // GAMBIO: SEO module changes $_GET data, avoiding "/"
        // Commerce:Seo changes $_GET data, avoid almost everything (!,#,$,%,^,*,(,))
        $rewrite = preg_replace('/,/', '/', $rewrite);
        list($file, $label) = preg_split('/@/', $rewrite);
        $hook = $hookData[$file][$label];
        $fileContent = file_get_contents($file);
        $posPre = strpos($fileContent, $hook['pre']);
        if ($posPre === false) {
            $this->data['message'] = 'Cannot find pre hook.';
            return;
        }
        $posPost = strpos($fileContent, $hook['post'], $posPre);
        if ($posPost === false) {
            $this->data['message'] = 'Cannot find post hook.';
            return;
        }
        if (true) {
            // creating backup
            $fBackup = fopen($file."-backup-".date('Ymd-U').".php", "w");
            $iWritten = fwrite($fBackup, $fileContent);
            fclose($fBackup);
            if ($iWritten < strlen($fileContent)) {
                $this->data['message'] = 'Cannot create backup.';
                return;
            }
        }
        $fileContentModified = substr($fileContent, 0, $posPre + strlen($hook['pre']))
            ."\n\n"
            ."\n/** BILLPAY_INJECTION_START(".$label.":".$hook['hash'].") **/\n"
            .$hook['content']
            ."\n/** BILLPAY_INJECTION_END(".$label.":".$hook['hash'].") **/\n"
            ."\n\n"
            .substr($fileContent, $posPost);
        $fs = fopen($file, 'w');
        fwrite($fs, $fileContentModified);
        fclose($fs);
    }
}

// we are using class to group functions, not to extend it
$billpayPSS = new BillpayPSS();
$billpayPSS->execute();



