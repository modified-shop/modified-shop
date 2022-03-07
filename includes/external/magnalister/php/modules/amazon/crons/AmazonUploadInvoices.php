<?php
/**
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
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleUploadInvoices.php');

class AmazonUploadInvoices extends MagnaCompatibleUploadInvoices {

    protected $sInvoiceOptionConfigKey = 'VCSInvoice';
    protected function getConfigKeys() {
        global $magnaConfig;
        if ($magnaConfig['db'][$this->mpID]['amazon.amazonvcs.invoice'] == 'erp') { 
            try {
                MLReceiptUpload::gi()->setConfig(array(
                     'SHIPMENT'             => getDBConfigValue($this->marketplace.'.invoice.erpinvoicesource', $this->mpID),
                     'SHIPMENT_DESTINATION' => getDBConfigValue($this->marketplace.'.invoice.erpinvoicedestination', $this->mpID),
                     'RETURN'               => getDBConfigValue($this->marketplace.'.invoice.erpreversalinvoicesource', $this->mpID),
                     'RETURN_DESTINATION'   => getDBConfigValue($this->marketplace.'.invoice.erpreversalinvoicedestination', $this->mpID),
                     'REFUND'               => getDBConfigValue($this->marketplace.'.invoice.erpreversalinvoicesource', $this->mpID),
                     'REFUND_DESTINATION'   => getDBConfigValue($this->marketplace.'.invoice.erpreversalinvoicedestination', $this->mpID),
                ));
            } catch (MagnaException $e) {
                if (MLReceiptUpload::$ReceiptUploadError == $e->getCode()) {
                    $this->out($e->getMessage());
                } else {
                    throw $e;
                }
            } catch (Exception $e) {
                $this->out($e->getMessage());
            }
        } else if (    $magnaConfig['db'][$this->mpID]['amazon.amazonvcs.invoice'] == 'off'
                    || empty($magnaConfig['db'][$this->mpID]['amazon.amazonvcs.invoice'])) {
                $this->out(str_replace(array('{#marketplace#}','{#mpID#}'), array($this->marketplace, $this->mpID), ML_NO_INVOICE_UPLOAD_FROM_SHOP));
        }
        $keys['VCSOption'] = array(
            'key'     => 'amazonvcs.option',
            'default' => 'off',
        );
        $keys[$this->sInvoiceOptionConfigKey] = array(
            'key'     => 'amazonvcs.invoice',
            'default' => 'off',
        );

        return $keys;
    }

    public function process() {
        if (in_array($this->config['VCSOption'], array('vcs-lite', 'off'), true)
            && in_array($this->config[$this->sInvoiceOptionConfigKey], array('webshop', 'erp', 'magna'), true)
        ) {
            $requestCount = 10;
            $offset = 0;
            $exitLoop = false;

            do {
                try {
                    $aResults = MagnaConnector::gi()->submitRequest(array(
                        'SUBSYSTEM' => $this->marketplace,
                        'MARKETPLACEID' => $this->mpID,
                        'ACTION' => 'GetOrdersToUploadInvoices',
                        'OFFSET' => array(
                            'COUNT' => $requestCount,
                            'START' => $offset
                        ),
                        'RequestVersion' => 2,
                        'NoShortCacheUsage' => microtime(),
                    ));
                    $aOrders = $aResults['DATA'];

                    $aDataToSubmit = array();
                    $this->out("\nProcessing orders to upload invoices {\n");
                    foreach ($aOrders as $aOrder) {
                        $this->out('    '.$aOrder['AmazonOrderId'].' : '.$aOrder['TransactionType']."\n");
                        $aMagnalisterOrder = $this->getMagnalisterOrderData($aOrder['AmazonOrderId']);
                        if (empty($aMagnalisterOrder['orders_id'])) {
                            $this->out('    '.'cannot find the order in magnalister_orders table, or the a sql error occurs '.$aOrder['AmazonOrderId']." \n");
                            continue;
                        }
                        $sPDFBase64 = $this->getInvoice($aMagnalisterOrder, $aOrder['TransactionType']);
                        if (!empty($sPDFBase64) || $this->config[$this->sInvoiceOptionConfigKey] === 'magna') {
                            $aDataToSubmit[] = array(
                                'TotalAmount' => $this->getTotalAmount($aMagnalisterOrder),
                                'TotalVAT' => $this->getTotalVat($aMagnalisterOrder),
                                'File' => $sPDFBase64,
                                'InvoiceNumber' => $this->getInvoiceNumber($aMagnalisterOrder, $aOrder['TransactionType']),
                                'TransactionId' => $aOrder['TransactionId'],
                                'AmazonOrderId' => $aOrder['AmazonOrderId'],
                            );
                        }

                        if (empty($sPDFBase64) && in_array($this->config[$this->sInvoiceOptionConfigKey], array('webshop', 'erp'), true)) {
                            $this->out('    '.'No pdf is available for '.$aOrder['special']." \n");
                        }
                    }
                    $this->out("}\n");
                    $this->out("\nConfirmation orders {\n");

                    $aResponse = MagnaConnector::gi()->submitRequest(array(
                        'ACTION' => 'UploadInvoices',
                        'SUBSYSTEM' => $this->marketplace,
                        'MARKETPLACEID' => $this->mpID,
                        'Invoices' => $aDataToSubmit,
                        'RequestVersion' => 2,
                    ));

                    if (count($aResponse['CONFIRMATIONS']) > 0) {
                        $this->setOrderAsProcessed($aResponse['CONFIRMATIONS']);
                    }

                } catch (\Exception $ex) {
                    echo print_m($ex->getMessage());
                    //                echo print_m(MagnaConnector::gi()->getLastRequest());
                    // May wrong permissions on destination folder
                    if ($ex->getCode() === MLReceiptUpload::$errorPermissionErpFolder) {
                        $exitLoop = true;
                    }
                }

                $offset += $requestCount - count($aResponse['CONFIRMATIONS']);
                $this->out("}\n");
            } while (count($aOrders) == $requestCount && !$exitLoop);
        }
    }

    protected function addErrorToErrorLog($sErrorMessage, $aAdditionalData, $sErrorCode = '') {
        $data = array (
            'mpID' => $this->mpID,
            'batchid' => '',
            'errorcode' => $sErrorCode,
            'errormessage' => $sErrorMessage,
            'additionaldata' => serialize($aAdditionalData),
        );
        if (!MagnaDB::gi()->recordExists(TABLE_MAGNA_AMAZON_ERRORLOG, $data)) {
            $data['dateadded'] = gmdate('Y-m-d H:i:s');
            MagnaDB::gi()->insert(TABLE_MAGNA_AMAZON_ERRORLOG, $data);
        }
    }

}
