<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'temu/shippinglabel/classes/MLOrderlistTemuAbstract.php');

class TemuShippingLabelUploadForm extends MLOrderlistTemuAbstract {

    public function __construct() {
        parent::__construct();
        $languages = getDBConfigValue($this->aMagnaSession['currentPlatform'].'.lang', $this->aMagnaSession['mpID'], $_SESSION['languages_id']);
        MLProduct::gi()->setLanguage($languages);
    }

    protected $aListConfig = array(
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'ItemTitle',
                'content' => 'ML_TEMU_SHIPPINGLABEL_FORM_PRODUCTNAME',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'SKU',
                'content' => 'SKU',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => '',
                'fieldname' => 'QuantitySent',
                'content' => 'ML_TEMU_SHIPPINGLABEL_FORM_SENT',
            ),
            'field' => array('absolute_data'),
        ),
        array(
            'head' => array(
                'attributes' => 'class="price"',
                'fieldname' => 'Quantity',
                'content' => 'ML_TEMU_SHIPPINGLABEL_FORM_QUANTITY',
            ),
            'field' => array('quantity'),
        ),
    );

    protected function getAllSelectionIds() {
        $aOrderIds = array();
        $aSelectedOrder = MagnaDB::gi()->fetchArray("
            SELECT `element_id`
            FROM " . $this->getSelectionTableName() . "
            WHERE
                `session_id` = '" . session_id() . "'
                AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
                AND `selectionname` = '" . $this->getSelectionName() . "'
        ");
        foreach ($aSelectedOrder as $aOrderId) {
            $aOrderIds[] = $aOrderId['element_id'];
        }
        return $aOrderIds;
    }

    protected function buildRequest() {
        $this->oApiRequest = MLApiRequest::factoryApiRequestClass()->set(array(
            'ACTION' => 'GetOrdersForDateRange',
            'BEGIN' => date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 30),
            'MOrderIds' => $this->getAllSelectionIds(),
        ));
        return $this;
    }

    protected function getOrders() {
        $aList = $this->oApiRequest->getAll();
        if (!is_array($aList) || empty($aList)) {
            return array();
        }

        // The API returns normalized v2 keys (lowercase: orderInfo, products, customer, adress)
        // but the Temu form.php template expects PascalCase keys (MPSpecific, Products, AddressSets).
        // Map the data structure to match what the template expects.
        foreach ($aList as $iKey => $aOrder) {
            // Map orderInfo -> MPSpecific
            if (isset($aOrder['orderInfo']) && !isset($aOrder['MPSpecific'])) {
                $aList[$iKey]['MPSpecific'] = $aOrder['orderInfo'];
                // Ensure MOrderID is available
                if (!isset($aList[$iKey]['MPSpecific']['MOrderID']) && isset($aOrder['orderInfo']['MOrderID'])) {
                    $aList[$iKey]['MPSpecific']['MOrderID'] = $aOrder['orderInfo']['MOrderID'];
                }
            }
            // Map products -> Products and normalize each item's fields to the
            // PascalCase keys the Temu list config/template expect
            // (ItemTitle, SKU, Quantity, QuantitySent, MItemID). The API
            // delivers v2 product keys (products_name, products_model, products_quantity,
            // products_id), so without this the columns render empty, the weight
            // calculation multiplies by an undefined Quantity, and the per-item
            // quantity <select> gets an empty name.
            if (isset($aOrder['products']) && !isset($aOrder['Products'])) {
                $aProducts = array();
                foreach ($aOrder['products'] as $iProductKey => $aProduct) {
                    $sSku = isset($aProduct['products_model']) && $aProduct['products_model'] !== ''
                        ? $aProduct['products_model']
                        : (isset($aProduct['products_id']) ? $aProduct['products_id'] : '');
                    $sItemId = isset($aProduct['products_id']) && $aProduct['products_id'] !== ''
                        ? $aProduct['products_id']
                        : $sSku;
                    if (!isset($aProduct['ItemTitle'])) {
                        $aProduct['ItemTitle'] = isset($aProduct['products_name']) ? $aProduct['products_name'] : '';
                    }
                    if (!isset($aProduct['SKU'])) {
                        $aProduct['SKU'] = $sSku;
                    }
                    if (!isset($aProduct['Quantity'])) {
                        $aProduct['Quantity'] = isset($aProduct['products_quantity']) ? (int)$aProduct['products_quantity'] : 0;
                    }
                    if (!isset($aProduct['QuantitySent'])) {
                        $aProduct['QuantitySent'] = 0;
                    }
                    if (!isset($aProduct['MItemID'])) {
                        $aProduct['MItemID'] = $sItemId;
                    }
                    $aProducts[$iProductKey] = $aProduct;
                }
                $aList[$iKey]['Products'] = $aProducts;
            }
            // Map customer/adress -> AddressSets (prefer order block, fall back to
            // the normalized customer/adress blocks the API also provides)
            if (!isset($aOrder['AddressSets'])) {
                $sFirstname = isset($aOrder['order']['customers_firstname']) && $aOrder['order']['customers_firstname'] !== ''
                    ? $aOrder['order']['customers_firstname']
                    : (isset($aOrder['customer']['customers_firstname']) ? $aOrder['customer']['customers_firstname']
                        : (isset($aOrder['adress']['entry_firstname']) ? $aOrder['adress']['entry_firstname'] : ''));
                $sLastname = isset($aOrder['order']['customers_lastname']) && $aOrder['order']['customers_lastname'] !== ''
                    ? $aOrder['order']['customers_lastname']
                    : (isset($aOrder['customer']['customers_lastname']) ? $aOrder['customer']['customers_lastname']
                        : (isset($aOrder['adress']['entry_lastname']) ? $aOrder['adress']['entry_lastname'] : ''));
                $aList[$iKey]['AddressSets'] = array(
                    'Main' => array(
                        'Firstname' => $sFirstname,
                        'Lastname' => $sLastname,
                    ),
                );
            }
        }

        $fConfigWeight = (float)getDBConfigValue('temu.orderstatus.temu.buyshipping.weightamount', $this->aMagnaSession['mpID'], 0.5);
        $fConfigLength = (float)getDBConfigValue('temu.orderstatus.temu.buyshipping.length', $this->aMagnaSession['mpID'], 30);
        $fConfigWidth = (float)getDBConfigValue('temu.orderstatus.temu.buyshipping.width', $this->aMagnaSession['mpID'], 20);
        $fConfigHeight = (float)getDBConfigValue('temu.orderstatus.temu.buyshipping.height', $this->aMagnaSession['mpID'], 15);

        foreach ($aList as $iOrderKey => $aOrderData) {
            $fTotalWeight = 0;
            $blHasProductWeight = false;

            if (!empty($aOrderData['Products'])) {
                foreach ($aOrderData['Products'] as $iProductKey => $aProduct) {
                    // Adjust quantity for already sent items
                    if (isset($aProduct['QuantitySent']) && $aProduct['QuantitySent'] > 0) {
                        $aList[$iOrderKey]['Products'][$iProductKey]['Quantity'] = $aProduct['Quantity'] - $aProduct['QuantitySent'];
                    }

                    // Try to get weight from shop product
                    $fWeight = null;
                    if (!empty($aProduct['SKU']) && ($pID = magnaSKU2pID($aProduct['SKU'])) !== 0) {
                        $aShopProduct = MLProduct::gi()->getProductById($pID);
                        if (!empty($aShopProduct['Weight'])) {
                            $fWeight = mlConvertWeight($aShopProduct['Weight']['Value'], $aShopProduct['Weight']['Unit'], 'kg');
                            if ($fWeight !== null) {
                                $blHasProductWeight = true;
                            }
                        }
                    }
                    if ($fWeight === null) {
                        $fWeight = 0;
                    }
                    $aList[$iOrderKey]['Products'][$iProductKey]['Weight'] = $fWeight;
                    $fTotalWeight += $fWeight * $aList[$iOrderKey]['Products'][$iProductKey]['Quantity'];
                }
            }

            // Fallback to config weight if no product weights found
            if (!$blHasProductWeight || $fTotalWeight <= 0) {
                $fTotalWeight = $fConfigWeight;
            }

            $aList[$iOrderKey]['TotalWeight'] = round($fTotalWeight, 2);
            $aList[$iOrderKey]['DefaultLength'] = $fConfigLength;
            $aList[$iOrderKey]['DefaultWidth'] = $fConfigWidth;
            $aList[$iOrderKey]['DefaultHeight'] = $fConfigHeight;

            // Get MOrderID from the order data
            $sMOrderID = isset($aOrderData['MPSpecific']['MOrderID'])
                ? $aOrderData['MPSpecific']['MOrderID']
                : (isset($aOrderData['MOrderID']) ? $aOrderData['MOrderID'] : '');

            // Store order data in globalselection for later steps
            $sWhere = "WHERE
                `session_id` = '" . session_id() . "'
                AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
                AND `selectionname` = '" . $this->getSelectionName() . "'
                AND `element_id` = '" . MagnaDB::gi()->escape($sMOrderID) . "'
            ";

            if (empty($aOrderData['Products'])) {
                unset($aList[$iOrderKey]);
                MagnaDB::gi()->query("DELETE FROM " . $this->getSelectionTableName() . " " . $sWhere);
            } else {
                $aData = MagnaDB::gi()->fetchRow("SELECT data FROM " . $this->getSelectionTableName() . " " . $sWhere);
                $aData['data'] = json_decode($aData['data'], true);
                $aData['data']['globalinfo'] = $aOrderData;
                MagnaDB::gi()->query("
                    UPDATE " . $this->getSelectionTableName() . "
                    SET data = '" . MagnaDB::gi()->escape(json_encode($aData['data'])) . "'
                    " . $sWhere);
            }
        }
        return $aList;
    }

    protected function addDependencies() {
        $this
            ->addDependency('MLOrderlistTemuDependencyFormToOrderlistAction', array())
            ->addDependency('MLOrderlistTemuDependencyFormToShippingmethodAction', array())
        ;
    }

    protected function getSelectionKey() {
        return 'MOrderID';
    }

    protected function getSelectionName() {
        return 'temu_shippinglabel_orderlist';
    }

    protected function getMainTemplateName() {
        return 'form';
    }

    /**
     * Override to use Temu breadcrumb and Temu form/shippinginformation templates
     */
    public function renderTemplate() {
        $sTemplate = func_get_arg(0);
        $aVars = (func_num_args() > 1) ? func_get_arg(1) : array();

        // Use Temu-specific templates for breadcrumb, form, and form/shippinginformation
        $aTemuTemplates = array('breadcrumb', 'form', 'form/shippinginformation');
        if (in_array($sTemplate, $aTemuTemplates)) {
            if (!empty($aVars)) extract($aVars);
            include DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES . $sTemplate . '.php';
            return $this;
        }
        return call_user_func_array(array('parent', 'renderTemplate'), func_get_args());
    }
}
