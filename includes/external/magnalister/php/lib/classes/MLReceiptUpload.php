<?php
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class MLReceiptUpload {

    /**
     * @var int
     */
    public static $ReceiptUploadError = 45678765;

    public static $errorPermissionErpFolder = 1646220537;

    /**
     * @var MLReceiptUpload|null
     */
    private static $instance = null;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $processedOrders = array();

    /**
     * Returns the instance
     * @return MLReceiptUpload
     */
    public static function gi() {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Required function to set the directories where to pull and place the receipts for and after processing
     *
     * @param $config
     * @throws Exception
     */
    public function setConfig($config) {
        global $_MagnaSession;
        $sMarketplaceName = constant('ML_MODULE_'.strtoupper($_MagnaSession['currentPlatform']));
        $this->config = $config;
        foreach ($this->config as $key => &$value) {
            $value = rtrim($value, DIRECTORY_SEPARATOR);
            $value .= DIRECTORY_SEPARATOR;

            if (!file_exists($value)) {
                $outputString = str_replace(array('{#ConfigPath#}', '{#ConfigFieldLabel#}', '{#marketplace#}'), array($value, $this->getTranslationOfConfigurationType($key), $sMarketplaceName), ML_UPLOADINVOICE_ERROR_PATHNOTEXISTS);
                throw new Exception($outputString, self::$ReceiptUploadError);
            }
        }
    }

    private function getTranslationOfConfigurationType($type) {
        global $_magnaLanguage;
        $sJson = file_get_contents(DIR_MAGNALISTER_FS.'config/'.$_magnaLanguage.'/modules/invoices.form');
        $aConfig = json_decode($sJson, true);
        $aFields = $aConfig['erpinvoice']['fields'];
        $sFiledLabel = '';
        switch ($type) {
            case 'Invoice':
            case 'SHIPMENT':
            {
                $sFiledLabel = $aFields['invoice.erpInvoiceSource']['label'];
                break;
            }
            case 'SHIPMENT_DESTINATION':
            case 'Invoice_DESTINATION':
            {
                $sFiledLabel = $aFields['invoice.erpInvoiceDestination']['label'];
                break;
            }
            case 'RETURN':
            case 'REFUND':
            case 'Reversal':
            {
                $sFiledLabel = $aFields['invoice.erpReversalInvoiceSource']['label'];
                break;
            }
            case 'RETURN_DESTINATION':
            case 'REFUND_DESTINATION':
            case 'Reversal_DESTINATION':
            {
                $sFiledLabel = $aFields['invoice.erpReversalInvoiceDestination']['label'];
                break;
            }
        }

        return $sFiledLabel;
    }

    /**
     * Returns the all necessary data for an receipt
     *
     * @param $orderId
     * @param $type
     * @return array
     * @throws MagnaException
     * @throws Exception
     */
    public function processReceipt($orderId, $type) {
        if (!array_key_exists($type, $this->config)) {
            throw new Exception('Config for type "'.$type.'" is not set!'.json_indent(json_encode($this->config)), self::$ReceiptUploadError);
        }

        $file = $this->getReceiptByOrderId($orderId, $type);

        $this->processedOrders[$orderId] = array(
            'fileName' => $file['fileName'],
            'type'     => $type,
        );

        return array(
            'file'      => $file['fileContent'],
            'receiptNr' => $this->getReceiptNr($file['fileName'], $orderId),
        );
    }

    /**
     * Returns the Path, Content and Name of a File if success
     *  otherwise it throws an Exception that the invoice could not be found
     *
     * @param $orderId
     * @param $type
     * @return array
     * @throws Exception
     */
    private function getReceiptByOrderId($orderId, $type) {
        global $_MagnaSession;
        $sMarketplaceName = constant('ML_MODULE_'.strtoupper($_MagnaSession['currentPlatform']));
        $files = preg_grep('/^'.$orderId.'_.*\.(pdf|PDF)$/', scandir($this->config[$type]));
        if (empty($files)) {
            if (file_exists($this->config[$type].$orderId.'.pdf')) {
                $files = array($orderId.'.pdf');
            } elseif (file_exists($this->config[$type].$orderId.'.PDF')) {
                $files = array($orderId.'.PDF');
            }
        }

        if (!is_array($files) || count($files) === 0 || empty($files)) {
            $outputString = str_replace(array('{#ShopOrderId#}', '{#ConfigFieldLabel#}', '{#marketplace#}'), array($orderId, $this->getTranslationOfConfigurationType($type), $sMarketplaceName), ML_UPLOADINVOICE_ERROR_NORECEIPTSFORONEORDER);
            throw new Exception($outputString, self::$ReceiptUploadError);
        }
        if (is_array($files) && count($files) > 1) {
            var_dump($files);
            $outputString = str_replace(array('{#ShopOrderId#}', '{#ConfigFieldLabel#}'), array($orderId, $this->getTranslationOfConfigurationType($type)), ML_UPLOADINVOICE_ERROR_MULTIPLERECEIPTSFORONEORDER);
            throw new Exception($outputString, self::$ReceiptUploadError);
        }

        $fileName = current($files);

        return array(
            'fileContent' => base64_encode(file_get_contents($this->config[$type].$fileName)),
            'filePath'    => $this->config[$type].$fileName,
            'fileName'    => $fileName,
        );
    }

    /**
     * Returns the ReceiptsNr
     *  There are two options
     *      - "1.pdf" (1 stands for the order id and is then also the ReceiptNr)
     *      - "1_10001R.pdf" (1 stands for the order id and all after the underscore (_) stands for the ReceiptNr)
     *
     * @param $fileName
     * @param $orderId
     * @return string
     */
    private function getReceiptNr($fileName, $orderId) {
        $fileName = str_replace(array('.pdf', '.PDF'), '', $fileName);
        $receiptNr = $orderId;

        if (strpos($fileName, '_') !== false) {
            $receipt = explode('_', $fileName);

            if ($receipt[0] == $orderId) {
                unset($receipt[0]);
                $receiptNr = implode('_', $receipt);
            }
        }

        return $receiptNr;
    }

    /**
     * Moves the receipt from the processing directory to the final directory
     *
     * @param $orderId
     * @throws Exception
     */
    public function markOrderAsProcessed($orderId) {
        if (array_key_exists($orderId, $this->processedOrders)) {
            $key = $this->processedOrders[$orderId]['type'].'_DESTINATION';
            if (array_key_exists($key, $this->config)) {
                $fileOld = $this->config[$this->processedOrders[$orderId]['type']].$this->processedOrders[$orderId]['fileName'];
                $fileNew = $this->config[$key].$this->processedOrders[$orderId]['fileName'];
                $success = rename($fileOld, $fileNew);

                if (!$success) {
                    $outputString = str_replace(array('{#ReceiptFileName#}', '{#ConfigDestinationPath#}'), array($this->processedOrders[$orderId]['fileName'], $this->config[$key]), ML_UPLOADINVOICE_ERROR_MOVETODESTINATIONDIRECTORY_FAILED);
                    throw new Exception($outputString, self::$errorPermissionErpFolder);
                }
            }
        }
    }
}
