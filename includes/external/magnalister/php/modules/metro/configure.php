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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');
class MetroConfigure extends MagnaCompatibleConfigure {

    /**
     * Extended to add some javascript
     */
    public function process() {
        parent::process();
        $this->configurationJavascript();
        echo $this->invoiceOptionJS();
    }

    /**
     * Currently used to block cross border trading
     */
    protected function configurationJavascript() {
        ob_start();
        ?>
        <script type="text/javascript">/*<!CDATA[*/
            $(document).ready(function() {
                $('#config_metro_shippingdestination').on('change', function () {
                    $('#config_metro_shippingorigin').val($(this).val());
                });

                $('#config_metro_shippingorigin').on('change', function () {
                    $('#config_metro_shippingdestination').val($(this).val());
                });
            });
            /*]]>*/</script>
        <?php
    }

    public static function shippingProfile($args, &$value = '') {
        global $_MagnaSession;
        $sHtml = '<table><tr>';
        $form = array();

        $cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_metro');
        foreach ($args['subfields'] as $item) {
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
            $sHtml .= '<td>'.$cG->renderLabel($item['label'], $idkey).':</td><td>'.$cG->renderInput($item, $value).'</td>';
        }
        $sHtml .= '</tr></table>';
        return $sHtml;
    }

    protected function getAuthValuesFromPost() {
        $nUser = trim($_POST['conf'][$this->marketplace.'.clientkey']);
        $nPass = trim($_POST['conf'][$this->marketplace.'.secretkey']);
        $nPass = $this->processPasswordFromPost('secretkey', $nPass);

        if (empty($nUser)) {
            unset($_POST['conf'][$this->marketplace.'.clientkey']);
        }
        if ($nPass === false) {
            unset($_POST['conf'][$this->marketplace.'.secretkey']);
            return false;
        }
        return array(
            'ClientId'  => $nUser,
            'SecretKey' => $nPass,
        );
    }

    protected function getFormFiles() {
        return array(
            'login', 'prepare', 'checkin', 'price',
            'inventorysync', 'orders', 'orderStatus', 'invoices'
        );
    }

    protected function loadChoiseValues() {
        parent::loadChoiseValues();
        if ($this->isAuthed) {
            $this->getCancellationReason();

            $this->form['prepare']['fields']['processingtime']['values'] = $this->renderProcessingTimeValues();
            $this->form['prepare']['fields']['maxprocessingtime']['values'] = $this->renderProcessingTimeValues();
            mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
            mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatus']);
        }

    }

    private function getCancellationReason() {
        try {
            $orderStatusConditions = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetCancellationReasons'));
        } catch (MagnaException $me) {
            $orderStatusConditions = array(
                'DATA' => array(
                )
            );
        }

        $this->form['orderSyncState']['fields']['cancelreason']['values'] = $orderStatusConditions['DATA'];
    }

    private function renderProcessingTimeValues() {
        $aValues = array();
        for ($i = 0; $i < 100; $i++) {
            $aValues[$i] = $i;
        }

        return $aValues;
    }

    protected function finalizeForm() {
        global $_MagnaSession;
        parent::finalizeForm();
        if (!$this->isAuthed) {
            $this->form = array(
                'login' => $this->form['login']
            );
            return;
        }
    }

    public static function invoicePreview($args, &$value = '') {
        global $_MagnaSession, $_url;
        return '<input class="ml-button" type="button" value="Vorschau" id="ml-amazon-invoice-preview"/>
	
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$(\'#ml-amazon-invoice-preview\').click(function() {
		jQuery.blockUI(blockUILoading);
		jQuery.ajax({
			\'method\': \'get\',
			\'url\': \''.toURL($_url, array('what' => 'TestInvoiceGeneration', 'kind' => 'ajax'), true).'\',
			\'success\': function (data) {
				if (data.indexOf(\'<style\') > 0) {
					data=data.substring(0, data.indexOf(\'<style\'));
				}
				jQuery.unblockUI();
				myConsole.log(\'ajax.success\', data);
				if (data === \'error\') {
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
/*]]>*/</script>';
    }
}
