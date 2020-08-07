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

class AmazonUploadInvoices extends MagnaCompatibleCronBase {

    protected function getConfigKeys() {
        $keys['VCSOption'] = array(
            'key'     => 'amazonvcs.option',
            'default' => 'textfield',
        );
        $keys['VCSInvoice'] = array(
            'key'     => 'amazonvcs.invoice',
            'default' => 'amazon',
        );

        return $keys;
    }

    protected function getShopOrderData($sOrderMarketplaceId) {
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
        if (in_array($this->config['VCSOption'], array('vcs-lite', 'off'), true)
            && in_array($this->config['VCSInvoice'], array('webshop', 'magna'), true)
        ) {
            try {
                $aResults = MagnaConnector::gi()->submitRequest(array(
                    'SUBSYSTEM'     => $this->marketplace,
                    'MARKETPLACEID' => $this->mpID,
                    'ACTION'        => 'GetOrdersToUploadInvoices',
                    'OFFSET'        => array(
                        'COUNT' => 25,
                        'START' => 0
                    ),
                    'RequestVersion' => 2,
                ));
                $aOrders = $aResults['DATA'];

                $aDataToSubmit = array();
                $this->out("\nProcessing orders to upload invoices {\n");
                foreach ($aOrders as $aOrder) {
                    $this->out('    '.$aOrder['AmazonOrderId'].' : '.$aOrder['TransactionType']."\n");
                    $aMagnalisterOrder = $this->getShopOrderData($aOrder['AmazonOrderId']);
                    $sPDFBase64 = $this->getInvoice($aMagnalisterOrder, $aOrder['TransactionType']);
                    if (!empty($sPDFBase64) || $this->config['VCSInvoice'] === 'magna') {
                        $aDataToSubmit[] = array(
                            'TotalAmount'   => $this->getTotalAmount($aMagnalisterOrder),
                            'TotalVAT'      => $this->getTotalVat($aMagnalisterOrder),
                            'File'          => $sPDFBase64,
                            'InvoiceNumber' => $this->getInvoiceNumber($aMagnalisterOrder, $aOrder['TransactionType']),
                            'TransactionId' => $aOrder['TransactionId'],
                            'AmazonOrderId' => $aOrder['AmazonOrderId'],
                        );
                    } else if ($sPDFBase64 == '' && $this->config['VCSInvoice'] === 'webshop') {
                        $this->out('    '.'No pdf is available for '.$aOrder['special']." \n");
                    }
                }
                $this->out("}\n");
                $this->out("\nConfirmed orders {\n");

                $aResponse = MagnaConnector::gi()->submitRequest(array(
                    'ACTION'        => 'UploadInvoices',
                    'SUBSYSTEM'     => $this->marketplace,
                    'MARKETPLACEID' => $this->mpID,
                    'Invoices'      => $aDataToSubmit,
                    'RequestVersion' => 2,
                ));

                if (count($aResponse['CONFIRMATIONS']) > 0) {
                    foreach ($aResponse['CONFIRMATIONS'] as $sAmazonOrderId) {
                        $aOrder = $this->getShopOrderData($sAmazonOrderId);
                        $aOrderData = unserialize($aOrder['data']);
                        $aOrderData['Invoice'] = 'sent';
                        $sOrderData = serialize($aOrderData);
                        MagnaDB::gi()->update(TABLE_MAGNA_ORDERS,
                            $sOrderData,
                            array('special' => $sAmazonOrderId)
                        );
                        $this->out('    '.$sAmazonOrderId."\n");
                    }
                }

            } catch (\Exception $ex) {
                echo print_m($ex->getMessage());
                //                echo print_m(MagnaConnector::gi()->getLastRequest());
            }

            $this->out("}\n");
        }
    }

    protected function getInvoiceNumber($aOrder, $sType) {
        $sInvoiceNumber = '';
        if (MagnaDB::gi()->tableExists('invoices')) {
            if ($sType === 'SHIPMENT') {
                return MagnaDB::gi()->fetchOne(eecho('SELECT `invoice_number` FROM `invoices` WHERE `order_id` =\''.$aOrder['orders_id'].'\' AND `invoice_number` NOT LIKE "%_STORNO%" LIMIT 1', $this->_debugLevel >= self::DBGLV_HIGH));
            } elseif (in_array($sType, array('RETURN', 'REFUND'), true)) {
                return MagnaDB::gi()->fetchOne(eecho('SELECT `invoice_number` FROM `invoices` WHERE `order_id` =\''.$aOrder['orders_id'].'\' AND `invoice_number` LIKE "%_STORNO%" LIMIT 1', $this->_debugLevel >= self::DBGLV_HIGH));
            }
        }

        return $sInvoiceNumber;
    }

    protected function getInvoice($aOrder, $sType) {
        $sPdfBase64 = '';
        if (MagnaDB::gi()->tableExists('invoices')) {
            if ($sType === 'SHIPMENT') {
                $sPdfPath = MagnaDB::gi()->fetchOne(eecho('SELECT `invoice_file` FROM `invoices` WHERE `order_id` =\''.$aOrder['orders_id'].'\' AND `invoice_number` NOT LIKE "%_STORNO%" LIMIT 1', $this->_debugLevel >= self::DBGLV_HIGH));

                if (!empty($sPdfPath)) {
                    $basePath = DIR_FS_CATALOG.'export/invoice/'.$sPdfPath;
                    $sPdfBase64 = base64_encode(file_get_contents($basePath));
                }
            } elseif (in_array($sType, array('RETURN', 'REFUND'), true)) {
                $sPdfPath = MagnaDB::gi()->fetchOne(eecho('SELECT `invoice_file` FROM `invoices` WHERE `order_id` =\''.$aOrder['orders_id'].'\' AND `invoice_number` LIKE "%_STORNO%"', $this->_debugLevel >= self::DBGLV_HIGH));
                if (!empty($sPdfPath)) {
                    $basePath = DIR_FS_CATALOG.'export/invoice/'.$sPdfPath;
                    $sPdfBase64 = base64_encode(file_get_contents($basePath));
                }
            }
        }

        return $sPdfBase64;
    }

    /**
     * Total amount of invoice in Gambio could be get from "total_sum" field "invoices" table
     * here to have similar code in Gambio and other os-Commerce shop, we used orders_total
     * @param $aOrder
     * @return array|bool|mixed
     */
    protected function getTotalAmount($aOrder) {
        return MagnaDB::gi()->fetchOne(eecho('SELECT sum(value) FROM `'.TABLE_ORDERS_TOTAL.'` WHERE `orders_id` =\''.$aOrder['orders_id'].'\' AND `class` = \'ot_total\' LIMIT 1', $this->_debugLevel >= self::DBGLV_HIGH));
    }


    protected function getTotalVat($aOrder) {
        return MagnaDB::gi()->fetchOne(eecho('SELECT sum(value) FROM `'.TABLE_ORDERS_TOTAL.'` WHERE `orders_id` =\''.$aOrder['orders_id'].'\' AND `class` = \'ot_tax\' LIMIT 1', $this->_debugLevel >= self::DBGLV_HIGH));
    }
}
