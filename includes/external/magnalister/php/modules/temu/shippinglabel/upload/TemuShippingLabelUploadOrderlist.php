<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'temu/shippinglabel/classes/MLOrderlistTemuAbstract.php');

class TemuShippingLabelUploadOrderlist extends MLOrderlistTemuAbstract {

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
                'fieldname' => 'ImportDate',
                'content' => 'ML_TEMU_SHIPPINGLABEL_ORDERLIST_PURCHASEDATE',
                'sort' => array('param' => 'ImportDate', 'field' => 'ImportDate'),
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'MOrderID',
                'content' => 'ML_TEMU_SHIPPINGLABEL_ORDERLIST_MORDERID',
                'sort' => array('param' => 'MOrderID', 'field' => 'MOrderID'),
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
                'fieldname' => 'OrderPaymentType',
                'content' => 'ML_TEMU_SHIPPINGLABEL_ORDERLIST_PAYMENTTYPE',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'PackageSn',
                'content' => 'ML_TEMU_SHIPPINGLABEL_ORDERLIST_PACKAGESN',
            ),
            'field' => array('absolute_data'),
        ),
    );

    protected function buildRequest() {
        $this->oApiRequest = MLApiRequest::factoryApiRequestClass()->set(array(
            'ACTION' => 'GetOrdersAcknowledgeStateForDateRange',
            'BEGIN' => date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 30),
            'OrderStatus' => '2',
        ))->limit(($this->getCurrentPage() - 1) * $this->iRowsPerPage, $this->iRowsPerPage);
        return $this;
    }

    protected function addDependencies() {
        $this
            ->addDependency('MLOrderlistTemuDependencySearchFilter', array())
            ->addDependency('MLOrderlistTemuDependencySelectionAction', array(
                'selectionname' => $this->getSelectionName(),
                'selectiontablename' => $this->getSelectionTableName(),
            ))
            ->addDependency('MLOrderlistTemuDependencyOrderlistToFormAction', array())
        ;
    }

    protected function getSelectionKey() {
        return 'MOrderID';
    }

    protected function getSelectionName() {
        return 'temu_shippinglabel_orderlist';
    }

    protected function getMainTemplateName() {
        return 'orderlist';
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

    protected function getPreparedStatusIndicator($aRow) {
        if (!empty($aRow)) {
            return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', 'Shipped', 9, 9);
        }
        return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', 'Not shipped', 9, 9);
    }
}
