<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'temu/shippinglabel/classes/MLOrderlistTemuAbstract.php');

class TemuShippingLabelUploadSummary extends MLOrderlistTemuAbstract {

    protected $aListConfig = array(
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'MOrderID',
                'content' => 'ML_TEMU_SHIPPINGLABEL_ORDERLIST_MORDERID',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'ShippingDate',
                'content' => 'ML_TEMU_SHIPPINGLABEL_SUMMARY_SHIPPINGDATE',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'Weight',
                'content' => 'ML_TEMU_SHIPPINGLABEL_SUMMARY_WEIGHT',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'CarrierName',
                'content' => 'ML_TEMU_SHIPPINGLABEL_SUMMARY_CARRIERNAME',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'ShippingServiceName',
                'content' => 'ML_LABEL_MARKETPLACE_SHIPPING_METHOD',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => 'class="price"',
                'fieldname' => 'TotalPrice',
                'content' => 'ML_TEMU_SHIPPINGLABEL_SUMMARY_TOTALPRICE',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'EstimatedText',
                'content' => 'ML_TEMU_SHIPPINGLABEL_SUMMARY_ESTIMATE',
            ),
            'field' => array('absolute_data'),
        ),
    );

    public function __construct() {
        parent::__construct();
        $this->saveData();
    }

    /**
     * Save shipping service selection from Step 3 POST into globalselection
     */
    protected function saveData() {
        if ($this->getRequest('shippingserviceid') === null) {
            return $this;
        }
        foreach ($this->getRequest('shippingserviceid') as $sOrderId => $sShippingService) {
            $aShippingServiceInfo = json_decode($sShippingService, true);
            $aOrderData = $this->getSelectionData(array($this->getSelectionKey() => $sOrderId));
            if (!is_array($aOrderData) || !isset($aOrderData['data'])) {
                continue;
            }

            $aOrderData['data']['CarrierId'] = isset($aShippingServiceInfo['CarrierId']) ? $aShippingServiceInfo['CarrierId'] : '';
            $aOrderData['data']['ChannelId'] = isset($aShippingServiceInfo['ChannelId']) ? $aShippingServiceInfo['ChannelId'] : '';
            $aOrderData['data']['ServiceType'] = isset($aShippingServiceInfo['ServiceType']) ? $aShippingServiceInfo['ServiceType'] : '';
            $aOrderData['data']['globalinfo']['shippingservice'] = $aShippingServiceInfo;

            MagnaDB::gi()->query("
                UPDATE " . $this->getSelectionTableName() . "
                SET data = '" . MagnaDB::gi()->escape(json_encode($aOrderData['data'])) . "'
                WHERE
                    `session_id` = '" . session_id() . "'
                    AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
                    AND `selectionname` = '" . $this->getSelectionName() . "'
                    AND `element_id` = '" . MagnaDB::gi()->escape($sOrderId) . "'
            ");
        }
        return $this;
    }

    /**
     * Build summary display rows from globalselection data
     */
    protected function getOrders() {
        $aList = array();
        $aOrders = $this->getSelectionData();

        foreach ($aOrders as $aOrder) {
            $aData = $aOrder['data'];
            $sMOrderID = $aOrder['element_id'];

            $sCarrierName = '---';
            $sServiceName = '---';
            $sTotalPrice = '---';
            $sEstimatedText = '---';

            if (isset($aData['globalinfo']['shippingservice'])) {
                $aService = $aData['globalinfo']['shippingservice'];
                $sCarrierName = isset($aService['CarrierName']) ? $aService['CarrierName'] : '---';
                $sServiceName = isset($aService['ServiceType']) ? $aService['ServiceType']
                    : (isset($aService['ShippingServiceName']) ? $aService['ShippingServiceName'] : '---');
                if (isset($aService['Rate']['Amount'])) {
                    $sTotalPrice = $aService['Rate']['Amount'];
                } elseif (isset($aService['estimatedCost'])) {
                    $sTotalPrice = $aService['estimatedCost'];
                }
                if (isset($aService['EstimatedText'])) {
                    $sEstimatedText = $aService['EstimatedText'];
                }
            }

            $sWeight = isset($aData['Weight']['Value'])
                ? $aData['Weight']['Value'] . ' ' . (isset($aData['Weight']['Unit']) ? $aData['Weight']['Unit'] : 'kg')
                : '---';
            $sShippingDate = isset($aData['ShippingDate']) ? $aData['ShippingDate'] : '---';

            $aList[] = array(
                'MOrderID' => $sMOrderID,
                'ShippingDate' => $sShippingDate,
                'Weight' => $sWeight,
                'CarrierName' => $sCarrierName,
                'ShippingServiceName' => $sServiceName,
                'TotalPrice' => $sTotalPrice,
                'EstimatedText' => $sEstimatedText,
            );
        }
        return $aList;
    }

    /**
     * AJAX handler: confirm shipping for all selected orders
     */
    protected function renderAjax() {
        $timer = microtime(true);
        $aOrders = $this->getSelectionData();
        $iCount = count($aOrders);
        $iSuccess = 0;
        $iError = 0;

        foreach ($aOrders as $aOrder) {
            $aData = $aOrder['data'];
            $sMOrderID = $aOrder['element_id'];

            $aRequestData = array(
                'MOrderID' => $sMOrderID,
                'CarrierId' => isset($aData['CarrierId']) ? $aData['CarrierId'] : '',
                'ChannelId' => isset($aData['ChannelId']) ? $aData['ChannelId'] : '',
                'ServiceType' => isset($aData['ServiceType']) ? $aData['ServiceType'] : '',
            );
            if (isset($aData['Weight'])) {
                $aRequestData['Weight'] = $aData['Weight'];
            }
            if (isset($aData['PackageDimensions'])) {
                $aRequestData['PackageDimensions'] = $aData['PackageDimensions'];
                $aRequestData['PackageDimensions']['Unit'] = 'cm';
            }
            if (isset($aData['ShippingDate'])) {
                $aRequestData['ShippingDate'] = $aData['ShippingDate'];
            }
            if (isset($aData['ItemList'])) {
                $aRequestData['ItemList'] = $aData['ItemList'];
            }

            try {
                $aResponse = MagnaConnector::gi()->submitRequest(array(
                    'ACTION' => 'CreateShipment',
                    'SUBSYSTEM' => 'Temu',
                    'MARKETPLACEID' => $this->aMagnaSession['mpID'],
                    'DATA' => $aRequestData,
                ));
                if (isset($aResponse['STATUS']) && $aResponse['STATUS'] == 'SUCCESS') {
                    $iSuccess++;
                } else {
                    $iError++;
                }
            } catch (MagnaException $e) {
                $iError++;
            }
        }

        // Clear globalselection after all orders processed
        MagnaDB::gi()->query("
            DELETE FROM " . TABLE_MAGNA_GLOBAL_SELECTION . "
            WHERE
                `session_id` = '" . session_id() . "'
                AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
                AND `selectionname` = '" . $this->getSelectionName() . "'
        ");

        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('Content-type: application/json');

        $aAjax = array();
        $aAjax['itemsPerBatch'] = 10;
        $aAjax['ignoreErrors'] = true;
        $aAjax['state'] = array(
            'total' => $iCount,
            'submitted' => $iCount,
            'success' => $iSuccess,
            'failed' => $iError,
        );
        $aAjax['proceed'] = false;
        $aAjax['redirect'] = $this->getUrl(false, false, false, array('view' => 'overview', 'subview' => '', 'kind' => 'ajax'));
        $aAjax['finaldialogs'] = array();
        // Temu labels are async — show info message instead of download link
        $aAjax['showWithoutDialog'] = '<div class="ml-message ml-info">' . ML_TEMU_SHIPPINGLABEL_POPUP_AFTERCONFIRM . '</div>';
        $aAjax['timer'] = microtime2human(microtime(true) - $timer);
        $aAjax['memory'] = memory_usage();

        echo json_encode($aAjax);
    }

    protected function addDependencies() {
        $this
            ->addDependency('MLOrderlistTemuDependencySummaryToShippingmethodAction', array())
            ->addDependency('MLOrderlistTemuDependencySubmitSummaryAction', array())
        ;
    }

    protected function addRequestSort() {
        return $this;
    }

    protected function getSelectionKey() {
        return 'MOrderID';
    }

    protected function getSelectionName() {
        return 'temu_shippinglabel_orderlist';
    }

    protected function getMainTemplateName() {
        return 'summary';
    }

    /**
     * Override to use Temu breadcrumb
     */
    public function renderTemplate() {
        if (func_num_args() > 0 && func_get_arg(0) === 'breadcrumb') {
            if (func_num_args() > 1) {
                extract(func_get_arg(1));
            }
            include DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES . 'breadcrumb.php';
            return $this;
        }
        return call_user_func_array(array('parent', 'renderTemplate'), func_get_args());
    }
}
