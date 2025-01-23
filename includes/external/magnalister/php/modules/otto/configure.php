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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class OttoConfigure extends MagnaCompatibleConfigure {

    /** @var array */
    protected $carriersSelectIds = array();

    /**
     * @var array
     * "Value" is the main field in the form under this field there are sub fields.
     * Sub fields are the "key" values and we do the validation by sub fields. Errors are shown on the main filed.
     * example (otto.orders.shipping.address is the main field,  in this field are shown all other address data fields
     * such as otto.orders.shipping.address.city, otto.orders.shipping.address.countrycode...)
     */
    protected $fieldsToCheck = array(
        'otto.lang' => 'otto.lang',
        'otto.shipping.status' => 'otto.orders.shipping.address',
        'otto.send.carrier' => 'otto.send.carrier',
        'otto.send.carrier.DBMatching.table' => 'otto.send.carrier.DBMatching.table',
        'otto.send.carrier.ottoToShopMatching' => 'otto.send.carrier.ottoToShopMatching',
        'otto.forwarding.carrier' => 'otto.forwarding.carrier',
        'otto.forwarding.carrier.DBMatching.table' => 'otto.forwarding.carrier.DBMatching.table',
        'otto.forwarding.carrier.ottoToShopMatching' => 'otto.forwarding.carrier.ottoToShopMatching',
        'otto.return.carrier' => 'otto.return.carrier',
        'otto.return.carrier.DBMatching.table' => 'otto.return.carrier.DBMatching.table',
        'otto.return.carrier.ottoToShopMatching' => 'otto.return.carrier.ottoToShopMatching',
        'otto.orders.shipping.address.city' => 'otto.orders.shipping.address',
        'otto.orders.shipping.address.countrycode' => 'otto.orders.shipping.address',
        'otto.orders.shipping.address.zip' => 'otto.orders.shipping.address',
        'otto.orders.return.tracking.key.DBMatching.table' => 'otto.orders.return.tracking.key.DBMatching.table',
    );

    public function process() {
        $this->form = $this->loadConfigForm(
            $this->getForms(),
            array(
                '_#_platform_#_' => $this->marketplace,
                '_#_platformName_#_' => $this->marketplaceTitle
            )
        );
        $this->processAuth();
        $this->loadChoiseValues();
        if ($this->isAuthed) {
            // adding ids for the html elements for all 3 types carriers (in order to show and hide them on the front end)
            foreach (array('send', 'forwarding', 'return') as $carrier) {
                if (isset($this->form['order']['fields'][$carrier.'.carrier'])) {
                    $this->carriersSelectIds[$carrier] = array(
                        'mainselect' => 'config_'.str_replace('.', '_', $this->form['order']['fields'][$carrier.'.carrier']['key']),
                        'dbmatch' => 'config_'.str_replace('.', '_', $this->form['order']['fields'][$carrier.'.carrier.DBMatch']['key']),
                        'shipmodulematch' => 'config_'.str_replace('.', '_', $this->form['order']['fields'][$carrier.'.carrier.ottoToShopMatch']['key']),
                    );
                }
            }
        }

        $this->finalizeForm();

        $cG = new MLConfigurator($this->form, $this->mpID, 'conf_magnacompat');
        $cG->setRenderTabIdent(true);

        $this->validateShippingAndReturnTracking($cG);
        $allCorrect = $cG->processPOST();
        $this->loadChoiseValuesAfterProcessPOST();

        if ($this->isAjax) {
            echo $cG->processAjaxRequest();
        } else {
            echo $this->boxes;
            if (array_key_exists('sendTestmail', $_POST)) {
                if ($allCorrect) {
                    if (sendTestMail($this->mpID)) {
                        echo '<p class="successBox">'.ML_GENERIC_TESTMAIL_SENT.'</p>';
                    } else {
                        echo '<p class="successBox">'.ML_GENERIC_TESTMAIL_SENT_FAIL.'</p>';
                    }
                } else {
                    echo '<p class="noticeBox">'.ML_GENERIC_NO_TESTMAIL_SENT.'</p>';
                }
            }
            $sConfigForm = $cG->renderConfigForm();
            $this->extendCarrierConfig($sConfigForm);
            echo $sConfigForm;
            echo $cG->exchangeRateAlert($this->exchangeRateField);

            //require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/ShopAddOns.php');
            //ML_ShopAddOns::generateConfigPopupOnCombobox('FastSyncInventory', "config_{$this->marketplaceTitle}_stocksync_tomarketplace", "#config_{$this->marketplaceTitle}", "$(this).val() == 'auto_fast'");
        }
        echo $this->carrierScript();
        echo $this->validationScript();
    }

    private function validateShippingAndReturnTracking($cG) {
        $result = true;
        $missingConfigKeys = array();
        $notCorrect = array();
        foreach ($this->fieldsToCheck as $subfield => $filed) {
            $correct = true;
            if (isset($_POST['conf'][$subfield])) {
                if (is_array($_POST['conf'][$subfield])) {
                    $explodeField = explode('.', $subfield);
                    $mainField = $explodeField[0] . '.' . $explodeField[1] . '.' . $explodeField[2];
                    //validation for carriers and sub fields for carriers
                    if (array_key_exists($mainField, $this->fieldsToCheck) &&
                        ($_POST['conf'][$mainField] == 'dbmatch' && $explodeField[3] == 'DBMatching') ||
                        ($_POST['conf'][$mainField] == 'shipmodulematch' && $explodeField[3] == 'ottoToShopMatching')) {
                        foreach ($_POST['conf'][$subfield] as $value) {
                            if (empty($value)) {
                                $correct = false;
                            }
                        }
                    //validation for return tracking key and shipping address
                    } elseif (!array_key_exists($mainField, $this->fieldsToCheck)) {
                        foreach ($_POST['conf'][$subfield] as $value) {
                            if (empty($value)) {
                                $correct = false;
                            }
                        }
                    }
                }  else if (empty($_POST['conf'][$subfield])) {
                    $correct = false;
                }

            }

            if (!$correct) {
                $notCorrect[$filed] = ML_CONFIG_NOT_EMPTY;
                $missingConfigKeys[] = $filed;
                $result = false;
            }
        }
        if (!empty($missingConfigKeys) && !empty($notCorrect)) {
            $cG->setMissingConfigKeys($missingConfigKeys);
            $cG->setNotCorrect($notCorrect);
        }
        return $result;
    }

    public static function validationScript() {
        ob_start();
        ?>
        <script>
            $(document).ready(function () {
                var errormessage = $('.successBoxBlue');
                if (errormessage.length > 0) {
                    errormessage.addClass('errorBox');
                    errormessage.removeClass('successBoxBlue');
                    $('.noticeBox').remove();
                }
            });
        </script>
        <?php
        $sPopup = ob_get_clean();
        return $sPopup;
    }

    /**
     * Helper function: Add id to carrier db matching + mp to shop matching
     * so that its visibility can be controlled via js
     * and the same for shipping method
     */
    public function extendCarrierConfig(&$sConfigForm) {
        //adds ids to the table row element in order to implement js
        foreach ($this->carriersSelectIds as $carrierSelectId) {
            foreach ($carrierSelectId as $elementId) {
                $sCarrierDBMatching_table_label_pos = strpos($sConfigForm, $elementId);
                $iTrPos = strrpos(substr($sConfigForm, 0, $sCarrierDBMatching_table_label_pos), '<tr');
                $sConfigForm = substr($sConfigForm, 0, $iTrPos + 3)
                    .' id="'.$elementId.'" '
                    .substr($sConfigForm, $iTrPos + 4);
            }
        }

        return true;
    }

    /* returns a list with extra options (show as optgroups) */
    public function loadCarrierCodesExtended($type, $mpID = false) {
        $carrierCodes = $this->loadCarrierCodes($type, $mpID);
        array_shift($carrierCodes); // remove the 'none' entry)
        $carrierSelection = array(
            '' => ML_LABEL_CHOOSE,
            ML_SELECT_MARKETPLACE_SUGGESTED_CARRIER => $carrierCodes,
            ML_ADDITIONAL_OPTIONS => array(
                'shipmodulematch' => ML_MATCH_OTTO_CARRIER_TO_SHIPPING_MODULE,
                'dbmatch' => ML_MATCH_CARRIER_TO_DB,
            ),
        );

        return $carrierSelection;
    }

    public static function carrierScript() {
        ob_start();
        ?>
        <script>
            // switch on/off carrier + shipping method extra fields
            var elementIdValues = $.parseJSON($('#config_carrier_key_values_otto').val());
            $(document).ready(function () {
                if (elementIdValues != null) {
                    $.each(elementIdValues, function (index, value) {
                        if ($('select[id="' + value.mainselect + '"]').val() != 'dbmatch') {
                            $('#' + value.dbmatch + '').css('visibility', 'collapse');
                        }
                        if ($('select[id="' + value.mainselect + '"]').val() != 'shipmodulematch') {
                            $('#' + value.shipmodulematch + '').css('visibility', 'collapse');
                        }
                    });
                }
            });
            // switch on/off carrier + shipping method extra fields
            if (elementIdValues != null) {
                $.each(elementIdValues, function (index, value) {
                    $('select[id="' + value.mainselect + '"]').change(function () {
                        if ($('select[id="' + value.mainselect + '"]').val() == 'dbmatch') {
                            $('#' + value.dbmatch + '').css('visibility', 'visible');
                            $('#' + value.shipmodulematch + '').css('visibility', 'collapse');
                        } else if ($('select[id="' + value.mainselect + '"]').val() == 'shipmodulematch') {
                            $('#' + value.dbmatch + '').css('visibility', 'collapse');
                            $('#' + value.shipmodulematch + '').css('visibility', 'visible');
                        } else {
                            $('#' + value.dbmatch + '').css('visibility', 'collapse');
                            $('#' + value.shipmodulematch + '').css('visibility', 'collapse');
                        }
                    });
                });
            }
        </script>
        <?php
        $sPopup = ob_get_clean();
        return $sPopup;
    }

    public static function OttoCarrierOttoToShopMatchConfig($args) {
        global $_MagnaSession;
        $sHtml = '<table><tr>';
        $form = array();
        $cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_otto');
        foreach ($args['subfields'] as $item) {
            $configValue = getDBConfigValue($item['key'], $_MagnaSession['mpID'], '');
            $value = '';
            if (isset($configValue[$args['currentIndex']])) {
                $value = $configValue[$args['currentIndex']];
            }
            $item['key'] .= '][';
            if (isset($item['params'])) {
                $item['params']['value'] = $value;
            }
            $sHtml .= '<td>'.$cG->renderInput($item, $value).'</td>';
        }
        $sHtml .= '</tr></table>';
        return $sHtml;
    }

   public static function OttoCarriersConfig($args) {
        $sHtml = '<select name="conf['.$args['key'].']">';
        foreach (OttoApiConfigValues::gi()->getOttoShippingSettings($args[1]) as $value => $name) {
            $sHtml .= '<option '.($args['value'] == $value ? 'selected=selected' : '').' value="'.$value.'">'.$name.'</option>';
        }
        $sHtml .= '</select>';
        return $sHtml;
    }

    public static function OttoShopCarriersConfig($args) {
        $aShopCarriers = array('values' => null);
        mlGetShippingModules($aShopCarriers);
        $aShopCarriers = $aShopCarriers['values'];
        if (empty($aShopCarriers))
            $aShopCarriers = array('&mdash;');
        $sHtml = '<select name="conf['.$args['key'].']">';
        foreach ($aShopCarriers as $ckey => $name) {
            $sHtml .= '<option '.($args['value'] == $ckey ? 'selected=selected' : '').' value="'.$ckey.'">'.fixHTMLUTF8Entities($name).'</option>';
        }
        $sHtml .= '</select>';
        return $sHtml;
    }

    private function loadCarrierCodes($type, $mpID = false) {
        if ($mpID === false) {
            global $_MagnaSession;
            $mpID = $_MagnaSession['mpID'];
        }
        $carrier = OttoApiConfigValues::gi()->getOttoShippingSettings($type);

        # OTTO Config Form
        if (array_key_exists('conf', $_POST) && array_key_exists('otto.orders.send.carrier.additional', $_POST['conf'])) {
            setDBConfigValue(
                'otto.orders.send.carrier.additional',
                $mpID,
                $_POST['conf']['otto.orders.send.carrier.additional']
            );
        }

        $addCarrier = explode(',', getDBConfigValue('otto.orders.send.carrier.additional', $mpID, ''));
        if (!empty($addCarrier)) {
            foreach ($addCarrier as $code => $val) {
                $val = trim($val);
                if (empty($val))
                    continue;
                $carrier[$code] = $val;
            }
        }
        $carrierValues = array('null' => ML_LABEL_CARRIER_NONE);
        if (!empty($carrier)) {
            foreach ($carrier as $code => $val) {
                if ($val == 'Other')
                    continue;
                $carrierValues[$code] = $val;
            }
        }

        return $carrierValues;
    }

    public static function productVATConfig($args, &$value = '') {

        global $_MagnaSession;
        $sHtml = '<table style="width: 80%;"><thead><tr>';
        $form = array();

        $cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_otto');
        foreach ($args['subfields'] as $item) {
            $idkey = '';

            $sHtml .= '<td style="width: 50%">'.$cG->renderLabel($item['label'], $idkey).'</td>';
        }
        $sHtml .= '</tr></thead><tbody>';

        $shopTaxes = MagnaDB::gi()->fetchArray(eecho('
            SELECT tax_class_id AS id, tax_class_title AS name
            FROM `'.TABLE_TAX_CLASS.'`
            WHERE tax_class_description != "EEL"
        ', false));


        foreach ($shopTaxes as $key => $tax) {
            $configValues = getDBConfigValue($item['key'], $_MagnaSession['mpID'], '');
            $value = '';
            if (isset($configValue[$args['currentIndex']])) {
                $value = $configValue[$args['currentIndex']];
            }
            if (isset($item['params'])) {
                $item['params']['value'] = $value;
            }

            $sHtml .= '
                <tr>
                    <td>'.$tax['name'].'</td>
                    <td><select style="width: 100%" name="conf['.$item['key'].']['.$tax['id'].']">';
            foreach ($item["values"] as $sKey => $sVal) {
                $sHtml .= '<option value="'.$sKey.'" '
                    .(isset($configValues[$tax['id']]) && ($configValues[$tax['id']] == $sKey) ? 'selected="selected"' : '')
                    .'>'.$sVal.'</option>';
            }
            $sHtml .= '</select></td></tr>';
        }
        $sHtml .= '</tbody></table>';

        return $sHtml;
    }

    public static function ottoShippingAddress($args, &$value = '') {
        global $_MagnaSession;
        $sHtml = '<table>';
        $form = array();

        $cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_otto');

        foreach ($args['subfields'] as $key => $item) {
            $idkey = str_replace('.', '_', $item['key']);
            $configValue = getDBConfigValue($item['key'], $_MagnaSession['mpID'], '');
            $value = '';
            if (isset($configValue[$args['currentIndex']])) {
                $value = $configValue[$args['currentIndex']];
            }

            $item['key'] .= '][';
            if (isset($item['params'])) {
                $item['params']['value'] = $value;
            }

            if($key == 'status') {
                $sHtml .= '<tr><td>'.$cG->renderLabel($item['label'], $idkey).':</td><td>'.$cG->renderInput($item, $value).'</td>';
            } else if ($key == 'city') {
                $sHtml .= '<td>'.$cG->renderLabel($item['label'], $idkey).':</td><td>'.$cG->renderInput($item, $value).'</td></tr>';
            } else {
                $sHtml .= '<tr><td></td><td></td>
                    <td>'.$cG->renderLabel($item['label'], $idkey).':</td><td>'.$cG->renderInput($item, $value).'</td>
                </tr>';
            }

        }
        $sHtml .= '</table>';
        return $sHtml;
    }

    protected function getAuthValuesFromPost() {
        return array();
    }

    protected function getFormFiles() {
        return array(
            'login', 'prepare', 'checkin', 'price',
            'inventorysync', 'orders'
        );
    }

    protected function loadChoiseValues() {
        parent::loadChoiseValues();
        if ($this->isAuthed) {
            $this->form['prepare']['fields']['delivery']['morefields']['delivery.time']['values'] =
                array_combine(range(1,99),range(1,99));
            mlGetOrderStatus($this->form['order']['fields']['orders.status.processable']);
            mlGetCustomersStatus($this->form['order']['fields']['customersgroup']);
            mlGetOrderStatus($this->form['order']['fields']['orders.cancel.with']);
            mlGetOrderStatus($this->form['order']['fields']['orders.shipping.address']['params']['subfields']['status']);

            $this->form['order']['fields']['orders.shipping.address']['params']['subfields']['countrycode']['values'] = OttoApiConfigValues::gi()->getOttoShippingSettings('countries');
            mlGetShippingModules($this->form['order']['fields']['orders.shipping.service']);
            mlGetPaymentModules($this->form['order']['fields']['orders.payment.methods']);
            // adding ids for element values to the hidden input, it will be used in js
            $this->form['order']['fields']['carrier.key.values']['formatstr'] = htmlspecialchars(json_encode($this->carriersSelectIds), ENT_QUOTES, 'UTF-8');
            //loads available group options for send, forwarding and return carrier
            $this->form['order']['fields']['send.carrier']['values'] = $this->loadCarrierCodesExtended('standard');
            $this->form['order']['fields']['forwarding.carrier']['values'] = $this->loadCarrierCodesExtended('forwarding');
            $this->form['order']['fields']['return.carrier']['values'] = $this->loadCarrierCodesExtended('return');
        }
    }

    protected function finalizeForm() {
        parent::finalizeForm();

        if (!$this->isAuthed) {
            $this->form = array(
                'login' => $this->form['login']
            );
            return;
        }
    }

    /**
     * Token Button + Popup functionality
     *
     * @param $args
     * @param $value
     * @return string
     */
    public static function OttoGetToken($args, &$value = '') {
        global $_url, $_modules;
        $apiRequest = 'GetOauthTokenCreationLink';
        $buttonId = 'requestToken';

        $content = '<input class="ml-button mlbtn-action" type="button" value="'.ML_BUTTON_TOKEN_NEW.'" id="'.$buttonId.'"/>';

        ob_start(); ?>

        <script type="text/javascript">/*<![CDATA[*/
            $(document).ready(function () {
                $('#<?php echo $buttonId; ?>').click(function () {
                    jQuery.blockUI(blockUILoading);
                    jQuery.ajax({
                        'method': 'get',
                        'url': '<?php echo toURL($_url, array('what' => $apiRequest, 'kind' => 'ajax'), true); ?>',
                        'success': function (data) {
                            // some shop systems attach error messages, warnings or even notices
                            // to the output, which would be fatal here, so we strip it away
                            if (data.indexOf('<style') > 0) {
                                data = data.substring(0, data.indexOf('<style'));
                            }
                            jQuery.unblockUI();
                            myConsole.log('ajax.success', data);
                            if (data == 'error') {
                                $('<div></div>')
                                    .attr('title', '<?php echo json_encode(str_replace('{#marketplace#}', $_modules[$_GET['module']]['title'], ML_ERROR_CREATE_TOKEN_LINK_HEADLINE)); ?>.')
                                    .html('<?php echo json_encode(str_replace('{#marketplace#}', $_modules[$_GET['module']]['title'], ML_ERROR_CREATE_TOKEN_LINK_HEADLINE)); ?>.')
                                    .jDialog();
                            } else {
                                var hwin = window.open(data, "popup", "resizable=yes,scrollbars=yes");
                                if (hwin.focus) {
                                    hwin.focus();
                                }
                            }
                        }
                    });
                });
            });
            /*]]>*/</script>

        <?php
        $content .= ob_get_contents();
        ob_end_clean();

        return $content;
    }
    protected function needToSetCredential() {
        return false;
    }
}

if (isset($_GET['what'])) {
    if ($_GET['what'] == 'GetOauthTokenCreationLink') {
        $iframeURL = 'error';
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetOauthTokenCreationLink'
            ));
            $iframeURL = $result['DATA']['TokenLink'];
        } catch (MagnaException $e) {
            echo print_m($e, '$e');
        }
        echo $iframeURL;
        exit();
    }
}
