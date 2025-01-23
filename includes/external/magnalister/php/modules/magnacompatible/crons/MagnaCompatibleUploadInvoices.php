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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleCronBase.php');
require_once(DIR_MAGNALISTER_FS.'php/lib/classes/MLReceiptUpload.php');

class MagnaCompatibleUploadInvoices extends MagnaCompatibleCronBase {

    /**
     * @var array
     */
    protected $aERPInvoiceDataCache = array();

    /**
     * @var string
     */
    protected $sInvoiceOptionConfigKey = 'Invoice.Option';

    protected function getConfigKeys() {
        global $magnaConfig;
        if ($magnaConfig['db'][$this->mpID][$this->marketplace.'.invoice.option'] == 'erp') {
            try {
                $config = array(
                    'Invoice' => getDBConfigValue($this->marketplace.'.invoice.erpinvoicesource', $this->mpID),
                    'Invoice_DESTINATION' => getDBConfigValue($this->marketplace.'.invoice.erpinvoicedestination',
                        $this->mpID),
                    'Reversal' => getDBConfigValue($this->marketplace.'.invoice.erpreversalinvoicesource', $this->mpID),
                    'Reversal_DESTINATION' => getDBConfigValue($this->marketplace.'.invoice.erpreversalinvoicedestination',
                        $this->mpID),
                );

                // Make sure the paths have a trailing slash
                foreach ($config as $key => $value) {
                    if ('/' != mb_substr($value, -1)) {
                        $config[$key] = $value.'/';
                    }
                }

                MLReceiptUpload::gi()->setConfig($config);
            } catch (MagnaException $e) {
                if (MLReceiptUpload::$ReceiptUploadError == $e->getCode()) {
                    $this->out($e->getMessage());
                } else {
                    throw $e;
                }
            } catch (Exception $e) {
                $this->out($e->getMessage());
            }

        } elseif (    $magnaConfig['db'][$this->mpID][$this->marketplace.'.invoice.option'] == 'off'
                    || empty($magnaConfig['db'][$this->mpID][$this->marketplace.'.invoice.option'])
        ) {
            $this->out(str_replace(array('{#marketplace#}','{#mpID#}'), array($this->marketplace, $this->mpID), ML_NO_INVOICE_UPLOAD_FROM_SHOP));
        }

        $keys[$this->sInvoiceOptionConfigKey] = array(
            'key'     => 'invoice.option',
            'default' => 'off',
        );
        return $keys;
    }

    protected function getMagnalisterOrderData($sOrderMarketplaceId) {
        $mReturn = MagnaDB::gi()->fetchRow(eecho('
		    SELECT *
		      FROM `'.TABLE_MAGNA_ORDERS.'` mo
		     WHERE mpID = "'.$this->mpID.'"
		           AND mo.special =\''.$sOrderMarketplaceId.'\'
		  ORDER BY mo.orders_id DESC
		', $this->_debugLevel >= self::DBGLV_HIGH));

        return is_array($mReturn) ? $mReturn : array();
    }

    public function process() {
        if (in_array($this->config[$this->sInvoiceOptionConfigKey], array('webshop', 'erp', 'magna'), true)) {
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
                        'NoShortCacheUsage' => microtime(),
                    ));
                    $aOrders = $aResults['DATA'];

                    $aDataToSubmit = array();
                    $this->out("\nProcessing orders to upload invoices {\n");
                    foreach ($aOrders as $aOrder) {
                        $this->out('    '.$aOrder['MarketplaceOrderId'].' : '.$aOrder['InvoiceType']."\n");
                        $aMagnalisterOrder = $this->getMagnalisterOrderData($aOrder['MarketplaceOrderId']);
                        if (empty($aMagnalisterOrder['orders_id'])) {
                            $this->out('    '.'cannot find the order in magnalister_orders table, or the a sql error occurs '.$aOrder['MarketplaceOrderId']." \n");
                            continue;
                        }
                        $sPDFBase64 = $this->getInvoice($aMagnalisterOrder, $aOrder['InvoiceType']);
                        if (!empty($sPDFBase64) && $this->config[$this->sInvoiceOptionConfigKey] !== 'magna') {
                            $aDataToSubmit[] = array(
                                'TotalAmount' => $this->getTotalAmount($aMagnalisterOrder),
                                'TotalVAT' => $this->getTotalVat($aMagnalisterOrder),
                                'File' => $sPDFBase64,
                                'InvoiceNumber' => $this->getInvoiceNumber($aMagnalisterOrder, $aOrder['InvoiceType']),
                                'InvoiceType' => $aOrder['InvoiceType'],
                                'MarketplaceOrderId' => $aOrder['MarketplaceOrderId'],
                            );
                        }

                        if (empty($sPDFBase64) && in_array($this->config[$this->sInvoiceOptionConfigKey], array('webshop', 'erp'), true)) {
                            $this->out('    '.'No pdf is available for '.$aOrder['special']." \n");
                        }
                    }
                    $this->out("}\n");
                    $this->out("\nConfirmed orders {\n");

                    $aResponse = MagnaConnector::gi()->submitRequest(array(
                        'ACTION' => 'UploadInvoices',
                        'SUBSYSTEM' => $this->marketplace,
                        'MARKETPLACEID' => $this->mpID,
                        'Invoices' => $aDataToSubmit
                    ));

                    if (count($aResponse['CONFIRMATIONS']) > 0) {
                        $this->setOrderAsProcessed($aResponse['CONFIRMATIONS']);
                    }

                } catch (\Exception $ex) {
                    echo print_m($ex->getMessage());
                    // May wrong permissions on destination folder
                    if ($ex->getCode() === MLReceiptUpload::$errorPermissionErpFolder) {
                        $exitLoop = true;
                    }
                    //                echo print_m(MagnaConnector::gi()->getLastRequest());
                }

                $offset += $requestCount - count($aResponse['CONFIRMATIONS']);
                $this->out("}\n");
            } while (count($aOrders) == $requestCount && !$exitLoop);
        }
    }

    protected function getInvoiceNumber($aOrder, $sType) {
        $sInvoiceNumber = '';
        if ($this->config[$this->sInvoiceOptionConfigKey] === 'erp') {
            $aData = $this->getERPInvoiceData($aOrder, $sType);
            $sInvoiceNumber = $aData['OrderInvoiceNumber'];
        } else if (MagnaDB::gi()->tableExists('invoices')) {
            if ($this->isInvoiceType($sType)) {
                return MagnaDB::gi()->fetchOne(eecho("
                        SELECT `invoice_number` 
                          FROM `invoices` 
                         WHERE      `order_id` = '".$aOrder['orders_id']."'
                                AND `invoice_number` NOT LIKE '%_STORNO%'
                      ORDER BY invoice_date DESC
                         LIMIT 1
                    ", $this->_debugLevel >= self::DBGLV_HIGH));
            } elseif ($this->isReversalInvoiceType($sType)) {
                return MagnaDB::gi()->fetchOne(eecho("
                        SELECT `invoice_number` 
                          FROM `invoices` 
                         WHERE      `order_id` = '".$aOrder['orders_id']."'
                                AND `invoice_number` LIKE '%_STORNO%'
                      ORDER BY invoice_date DESC
                         LIMIT 1
                    ", $this->_debugLevel >= self::DBGLV_HIGH));            }
        }

        return $sInvoiceNumber;
    }

    protected function getInvoice($aOrder, $sType) {
        $sPdfBase64 = '';
        if ($this->config[$this->sInvoiceOptionConfigKey] === 'erp') {
            $aData = $this->getERPInvoiceData($aOrder, $sType);
            $sPdfBase64 = $aData['OrderInvoiceFile'];
        } else {
            if (MagnaDB::gi()->tableExists('invoices')) {
                if ($this->isInvoiceType($sType)) {
                    $sPdfPath = MagnaDB::gi()->fetchOne(eecho("
                        SELECT `invoice_file` 
                          FROM `invoices` 
                         WHERE      `order_id` = '".$aOrder['orders_id']."'
                                AND `invoice_number` NOT LIKE '%_STORNO%'
                      ORDER BY invoice_date DESC
                         LIMIT 1
                    ", $this->_debugLevel >= self::DBGLV_HIGH));

                    if (!empty($sPdfPath)) {
                        $basePath = DIR_FS_CATALOG.'export/invoice/'.$sPdfPath;
                        $sPdfBase64 = base64_encode(file_get_contents($basePath));
                    }
                } elseif ($this->isReversalInvoiceType($sType)) {
                    $sPdfPath = MagnaDB::gi()->fetchOne(eecho("
                        SELECT `invoice_file` 
                          FROM `invoices` 
                         WHERE      `order_id` = '".$aOrder['orders_id']."'
                                AND `invoice_number` LIKE '%_STORNO%'
                      ORDER BY invoice_date DESC
                         LIMIT 1
                    ", $this->_debugLevel >= self::DBGLV_HIGH));
                    if (!empty($sPdfPath)) {
                        $basePath = DIR_FS_CATALOG.'export/invoice/'.$sPdfPath;
                        $sPdfBase64 = base64_encode(file_get_contents($basePath));
                    }
                }
            }
        }

        return $sPdfBase64;
    }

    protected function isInvoiceType($sType) {
        return in_array($sType, array('SHIPMENT', 'Invoice'), true);
    }

    protected function isReversalInvoiceType($sType) {
        return in_array($sType, array('RETURN', 'REFUND', 'Reversal'), true);
    }

    /**
     * Total amount of invoice in Gambio could be get from "total_sum" field "invoices" table
     * here to have similar code in Gambio and other os-Commerce shop, we used orders_total
     * @param $aOrder
     * @return array|bool|mixed
     */
    protected function getTotalAmount($aOrder) {
        return MagnaDB::gi()->fetchOne(eecho("
            SELECT sum(value) 
              FROM `".TABLE_ORDERS_TOTAL."` 
             WHERE     `orders_id` = '".$aOrder['orders_id']."' 
                   AND `class` = 'ot_total' 
             LIMIT 1
        ", $this->_debugLevel >= self::DBGLV_HIGH));
    }


    /**
     * Return the total vat amount
     *
     * @param $aOrder
     * @return false|mixed
     */
    protected function getTotalVat($aOrder) {
        return MagnaDB::gi()->fetchOne(eecho("
            SELECT sum(value) 
              FROM `".TABLE_ORDERS_TOTAL."` 
             WHERE     `orders_id` = '".$aOrder['orders_id']."'
                   AND `class` = 'ot_tax' 
             LIMIT 1
        ", $this->_debugLevel >= self::DBGLV_HIGH));
    }


    protected function getERPInvoiceData($aOrderData, $sInvoiceType) {
        $sShopOrderID = $aOrderData['orders_id'];
        $sMarketplaceOrderID = $aOrderData['special'];
        if (!isset($this->aERPInvoiceDataCache[$sShopOrderID][$sInvoiceType])) {
            try {
                $receipt = MLReceiptUpload::gi()->processReceipt($sShopOrderID, $sInvoiceType);
                $sOrderInvoiceNumber = $receipt['receiptNr'];
                $sOrderInvoiceFile = $receipt['file'];
            } catch (Exception $e) {
                $this->out($e->getMessage());
                $this->addErrorToErrorLog($e->getMessage(), array('MOrderID'=> $sMarketplaceOrderID), $e->getCode());
                $sOrderInvoiceNumber = null;
                $sOrderInvoiceFile = null;
            }
            $this->aERPInvoiceDataCache[$sShopOrderID][$sInvoiceType] = array(
                'OrderInvoiceNumber' => $sOrderInvoiceNumber,
                'OrderInvoiceFile'   => $sOrderInvoiceFile,
            );
        }
        return $this->aERPInvoiceDataCache[$sShopOrderID][$sInvoiceType];
    }

    /**
     * @param $CONFIRMATIONS
     * @param $orders_id
     * @throws Exception
     */
    protected function setOrderAsProcessed($CONFIRMATIONS) {
        foreach ($CONFIRMATIONS as $sMarketplaceOrderId) {
            $aOrder = $this->getMagnalisterOrderData($sMarketplaceOrderId);
            $aOrderData = unserialize($aOrder['data']);
            $aOrderData['Invoice'] = 'sent';
            $sOrderData = serialize($aOrderData);
            MagnaDB::gi()->update(TABLE_MAGNA_ORDERS,
                array('data' => $sOrderData),
                array('special' => $sMarketplaceOrderId)
            );
            $this->out('    '.$sMarketplaceOrderId."\n");
            if($this->config[$this->sInvoiceOptionConfigKey] === 'erp') {
                MLReceiptUpload::gi()->markOrderAsProcessed($aOrder['orders_id']);
            }
        }
    }

    protected function addErrorToErrorLog($sErrorMessage, $aAdditionalData, $sErrorCode = ''){
        $data = array (
            'mpID' => $this->mpID,
            'errormessage' => $sErrorMessage,
            'additionaldata' => serialize($aAdditionalData),
        );
        if(!MagnaDB::gi()->recordExists(TABLE_MAGNA_COMPAT_ERRORLOG, $data)) {
            $data['dateadded'] = gmdate('Y-m-d H:i:s');
            MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $data);
        }
    }
}
