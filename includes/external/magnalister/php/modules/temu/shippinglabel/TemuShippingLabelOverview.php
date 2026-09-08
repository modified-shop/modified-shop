<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'temu/shippinglabel/classes/MLOrderlistTemuAbstract.php');

class TemuShippingLabelOverview extends MLOrderlistTemuAbstract {

    protected $iRowsPerPage = 10;
    protected $sMessage = null;
    protected $sDownloadLink = null;

    protected $aListConfig = array(
        array(
            'head' => array(
                'attributes' => 'class="nowrap edit"',
                'content' => '',
            ),
            'field' => array('selection')
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'CreatedDate',
                'content' => 'ML_TEMU_SHIPPINGLABEL_OVERVIEW_CREATEDDATE',
            ),
            'field' => array('absolute_data'),
        ),
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
                'fieldname' => 'ShopOrderId',
                'content' => 'ML_TEMU_SHIPPINGLABEL_ORDERLIST_SHOPORDERID',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'ShippingLabelStatus',
                'content' => 'ML_TEMU_SHIPPINGLABEL_OVERVIEW_SHIPPINGSTATUS',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'CarrierInfo',
                'content' => 'ML_TEMU_SHIPPINGLABEL_OVERVIEW_SENDERANDTRACKINGID',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'CommentDisplay',
                'content' => 'ML_TEMU_SHIPPINGLABEL_OVERVIEW_COMMENT',
            ),
            'field' => array('absolute_data'),
        ),
    );

    protected function buildRequest() {
        $this->oApiRequest = MLApiRequest::factoryApiRequestClass()->set(array(
            'ACTION' => 'GetShipmentList',
        ))->limit(($this->getCurrentPage() - 1) * $this->iRowsPerPage, $this->iRowsPerPage);
        return $this;
    }

    protected function getOrders() {
        $aOrders = parent::getOrders();
        if (!is_array($aOrders)) {
            return array();
        }

        foreach ($aOrders as &$aOrder) {
            // ShipmentId fallback
            $sShipmentId = isset($aOrder['ShipmentId']) ? $aOrder['ShipmentId']
                : (isset($aOrder['packageSn']) ? $aOrder['packageSn'] : '');
            if (!isset($aOrder['MOrderID']) || empty($aOrder['MOrderID'])) {
                $aOrder['MOrderID'] = $sShipmentId;
            }
            $aOrder['ShipmentId'] = $sShipmentId;

            // CarrierInfo: CarrierName + TrackingId
            $aOrder['CarrierInfo'] = isset($aOrder['CarrierName']) ? $aOrder['CarrierName'] : '---';
            if (isset($aOrder['TrackingId']) && $aOrder['TrackingId'] != '') {
                $aOrder['CarrierInfo'] .= '<br>' . htmlspecialchars($aOrder['TrackingId']);
            }

            // ShippingLabelStatus fallback
            if (!isset($aOrder['ShippingLabelStatus'])) {
                $aOrder['ShippingLabelStatus'] = '---';
            }

            // ShopOrderId fallback
            if (!isset($aOrder['ShopOrderId'])) {
                $aOrder['ShopOrderId'] = '---';
            }

            // CommentDisplay: ERROR status shows red error message
            $sStatus = isset($aOrder['ShippingLabelStatus']) ? $aOrder['ShippingLabelStatus'] : '';
            if ($sStatus === 'ERROR') {
                $sErrorMsg = isset($aOrder['ErrorMsg']) ? $aOrder['ErrorMsg']
                    : (isset($aOrder['Comment']) ? $aOrder['Comment'] : '');
                $aOrder['CommentDisplay'] = ($sErrorMsg !== '' ? htmlspecialchars($sErrorMsg) : '');
//                    . ML_TEMU_SHIPPINGLABEL_OVERVIEW_ERRORNOTE
//                    . '</span>';
                $aOrder['hasError'] = true;
            } else {
                $aOrder['CommentDisplay'] = isset($aOrder['Comment']) ? htmlspecialchars($aOrder['Comment']) : '---';
                $aOrder['hasError'] = false;
            }
        }

        return $aOrders;
    }

    public function render() {
        $sMethod = $this->getRequest('method');
        if ($sMethod !== null && method_exists($this, $sMethod . 'Shipping')) {
            $this->sMessage = $this->{$sMethod . 'Shipping'}();
        }
        return parent::render();
    }

    protected function downloadShipping() {
        $aOrders = $this->getSelectionData();
        $aDataOrders = array();
        foreach ($aOrders as $aOrder) {
            $sElementId = $aOrder['element_id'];
            $aData = is_array($aOrder['data']) ? $aOrder['data'] : json_decode($aOrder['data'], true);
            $sShipmentId = isset($aData['ShipmentId']) ? $aData['ShipmentId'] : $sElementId;
            $aDataOrders[] = array(
                'MOrderID' => $sElementId,
                'ShipmentId' => $sShipmentId,
            );
        }

        try {
            $aResponse = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'downloadShippingLabel',
                'SUBSYSTEM' => 'Temu',
                'MARKETPLACEID' => $this->aMagnaSession['mpID'],
                'DATA' => array('Orders' => $aDataOrders),
            ));
            if (isset($aResponse['DATA']['DownloadLink'])) {
                $this->sDownloadLink = $aResponse['DATA']['DownloadLink'];
            } elseif (isset($aResponse['DATA'][0]['DownloadLink'])) {
                $this->sDownloadLink = $aResponse['DATA'][0]['DownloadLink'];
            }
        } catch (MagnaException $e) {
            // Fall through — no download link
        }

        return null;
    }

    protected function cancelShipping() {
        $aOrders = $this->getSelectionData();
        $aOrderIds = array();
        foreach ($aOrders as $aOrder) {
            $aOrderIds[] = $aOrder['element_id'];
        }
        try {
            MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'CancelShipment',
                'SUBSYSTEM' => 'Temu',
                'MARKETPLACEID' => $this->aMagnaSession['mpID'],
                'DATA' => array('ShipmentIds' => $aOrderIds),
            ));
            return ML_TEMU_SHIPPINGLABEL_OVERVIEW_CANCELSHIPPINGLABEL;
        } catch (MagnaException $e) {
        }
        return null;
    }

    protected function deleteShipping() {
        $aOrders = $this->getSelectionData();
        $aOrderIds = array();
        foreach ($aOrders as $aOrder) {
            $aOrderIds[] = $aOrder['element_id'];
        }
        try {
            MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'DeleteShipmentFromList',
                'SUBSYSTEM' => 'Temu',
                'MARKETPLACEID' => $this->aMagnaSession['mpID'],
                'DATA' => array('ShipmentIds' => $aOrderIds),
            ));
            return ML_TEMU_SHIPPINGLABEL_OVERVIEW_DELETESHIPPINGLABEL;
        } catch (MagnaException $e) {
        }
        return null;
    }

    public function getDownloadLink() {
        return $this->sDownloadLink;
    }

    public function getMessage() {
        return $this->sMessage;
    }

    protected function addDependencies() {
        $this
            ->addDependency('MLOrderlistTemuDependencySelectionAction', array(
                'selectionname' => $this->getSelectionName(),
                'selectiontablename' => $this->getSelectionTableName(),
            ))
            ->addDependency('MLOrderlistTemuDependencyDownloadAction', array())
            ->addDependency('MLOrderlistTemuDependencyCancelAction', array())
            ->addDependency('MLOrderlistTemuDependencyDeleteAction', array())
        ;
    }

    protected function getSelectionKey() {
        return 'MOrderID';
    }

    protected function getSelectionName() {
        return 'temu_shippinglabel_overview';
    }

    protected function getMainTemplateName() {
        return 'overview';
    }

    /**
     * Override to use Temu breadcrumb template while reusing shared orderlist templates for everything else.
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
