<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'temu/shippinglabel/classes/MLOrderlistTemuAbstract.php');

class TemuShippingLabelUploadShippingmethod extends MLOrderlistTemuAbstract {

    protected $aListConfig = array(
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'CarrierName',
                'content' => 'ML_TEMU_SHIPPINGLABEL_FORM_CARRIERNAME',
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
                'fieldname' => 'Amount',
                'content' => 'ML_TEMU_SHIPPINGLABEL_SHIPPINGMETHOD_AMOUNT',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'EstimatedText',
                'content' => 'ML_TEMU_SHIPPINGLABEL_SHIPPINGMETHOD_ESTIMATE',
            ),
            'field' => array('absolute_data'),
        ),
    );

    protected function addRequestSort() {
        return $this;
    }

    public function __construct() {
        parent::__construct();
        $this->saveData();
    }

    protected function getOrders() {
        $aSelectionData = $this->getSelectionData();
        $aList = array();

        foreach ($aSelectionData as $aOrderRow) {
            $sOrderId = $aOrderRow['element_id'];
            $aData = is_array($aOrderRow['data']) ? $aOrderRow['data'] : json_decode($aOrderRow['data'], true);

            // Prepare API request data
            $aRequestData = array(
                'MOrderID' => $sOrderId,
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

            $aOrder = array('MOrderID' => $sOrderId);
            try {
                $aResponse = MagnaConnector::gi()->submitRequest(array(
                    'ACTION' => 'GetShippingServices',
                    'SUBSYSTEM' => 'Temu',
                    'MARKETPLACEID' => $this->aMagnaSession['mpID'],
                    'DATA' => $aRequestData,
                ));
                if (isset($aResponse['DATA']) && $aResponse['STATUS'] == 'SUCCESS' && is_array($aResponse['DATA'])) {
                    $aOrder['shippingservice'] = isset($aResponse['DATA']['ShippingServices'])
                        ? $aResponse['DATA']['ShippingServices']
                        : $aResponse['DATA'];
                } else {
                    $aOrder['shippingservice'] = array();
                }
            } catch (MagnaException $e) {
                $aOrder['shippingservice'] = array();
            }

            $aList[] = $aOrder;
        }

        return $aList;
    }

    /**
     * Save form data from Step 2 (Package Form) into globalselection
     */
    protected function saveData() {
        if ($this->getRequest('weight') === null) {
            return $this;
        }

        $aOrders = array();

        // Dimensions
        foreach (array('Length', 'Width', 'Height') as $sDimension) {
            $aRequestData = $this->getRequest(strtolower($sDimension));
            if (is_array($aRequestData)) {
                foreach ($aRequestData as $sOrderId => $sValue) {
                    $aOrders[$sOrderId]['PackageDimensions'][$sDimension] = (float)$sValue;
                }
            }
        }

        // Weight
        foreach ($this->getRequest('weight') as $sOrderId => $sValue) {
            $aOrders[$sOrderId]['Weight']['Value'] = (float)$sValue;
            $aOrders[$sOrderId]['Weight']['Unit'] = 'kg';
            $aOrders[$sOrderId]['MOrderID'] = $sOrderId;
        }

        // Shipping date
        $aRequestDate = $this->getRequest('date');
        if (is_array($aRequestDate)) {
            foreach ($aRequestDate as $sOrderId => $sValue) {
                $aOrders[$sOrderId]['ShippingDate'] = $sValue;
            }
        }

        // Item list
        $aRequestItems = $this->getRequest('ItemList');
        if (is_array($aRequestItems)) {
            foreach ($aRequestItems as $sOrderId => $aItems) {
                foreach ($aItems as $sItemId => $iQuantity) {
                    $aOrders[$sOrderId]['ItemList'][] = array(
                        'OrderItemId' => $sItemId,
                        'Quantity' => (float)$iQuantity,
                    );
                }
            }
        }

        // Save to globalselection (use REPLACE INTO to handle both insert and update)
        foreach ($aOrders as $sOrderId => $aData) {
            $aOrderData = $this->getSelectionData(array($this->getSelectionKey() => $sOrderId));
            if (isset($aOrderData['data']['globalinfo'])) {
                $aData['globalinfo'] = $aOrderData['data']['globalinfo'];
            }
            MagnaDB::gi()->query("
                REPLACE INTO " . $this->getSelectionTableName() . "
                    (`mpID`, `selectionname`, `session_id`, `element_id`, `data`)
                VALUES (
                    '" . $this->aMagnaSession['mpID'] . "',
                    '" . $this->getSelectionName() . "',
                    '" . session_id() . "',
                    '" . MagnaDB::gi()->escape($sOrderId) . "',
                    '" . MagnaDB::gi()->escape(json_encode($aData)) . "'
                )
            ");
        }

        return $this;
    }

    protected function addDependencies() {
        $this
            ->addDependency('MLOrderlistTemuDependencyShippingmethodToFormAction', array())
            ->addDependency('MLOrderlistTemuDependencyShippingmethodToSummaryAction', array(
                'selectionname' => $this->getSelectionName(),
                'selectiontablename' => $this->getSelectionTableName(),
            ))
        ;
    }

    protected function getSelectionKey() {
        return 'MOrderID';
    }

    protected function getSelectionName() {
        return 'temu_shippinglabel_orderlist';
    }

    protected function getMainTemplateName() {
        return 'shippingmethod';
    }

    /**
     * Override to use Temu-specific templates for breadcrumb and shippingmethod
     */
    public function renderTemplate() {
        $sTemplate = func_get_arg(0);
        $aTemuTemplates = array('breadcrumb', 'shippingmethod');
        if (in_array($sTemplate, $aTemuTemplates)) {
            $aVars = (func_num_args() > 1) ? func_get_arg(1) : array();
            if (!empty($aVars)) extract($aVars);
            include DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES . $sTemplate . '.php';
            return $this;
        }
        return call_user_func_array(array('parent', 'renderTemplate'), func_get_args());
    }
}
