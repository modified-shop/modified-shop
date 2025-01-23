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
require_once __DIR__.'/classes/MetroCrossBordersConfiguration.php';
class MetroConfigure extends MagnaCompatibleConfigure {

    /**
     * Cache if the current metro tab can set stock options. (Cross borders limitation)
     *
     * @var bool|null
     */
    protected $canSetStockOptionsCache = null;

    /**
     * The mpID for the tab which has the active stock synchronisation setting.
     *
     * @var int|null
     */
    protected static $crossBordersStockOptionsMpid = null;

    /**
     * Extended to add some javascript
     */
    public function process() {
        parent::process();
        $this->configurationJavascript();
        echo $this->invoiceOptionJS();
        ?>
        <script type="application/javascript">
            (function ($) {
                $(document).ready(function () {
                    $('form#conf_magnacompat').on('submit', function () {
                        let $this = $(this);
                        $('input:disabled,select:disabled,textarea:disabled', $this).removeAttr('disabled');
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Processes the POST data for valid cross border configuration.
     *
     * If the current tab is the one responsible for stock synchronization, it needs to update all other tabs with the
     * quantity data.
     *
     * If the current tab is not responsible for stock synchronization, it needs to disable stock synchronization and
     * copy the quantity data from the responsible tab.
     *
     * @return void
     */
    private function processCrossBorderValues() {
        if (array_key_exists('conf', $_POST) && is_array($_POST['conf']) &&
            array_key_exists('metro.clientkey', $_POST['conf']) &&
            array_key_exists('metro.maxquantity', $_POST['conf']) &&
            array_key_exists('metro.quantity.type', $_POST['conf']) &&
            array_key_exists('metro.quantity.value', $_POST['conf']) &&
            array_key_exists('metro.shippingorigin', $_POST['conf']) &&
            array_key_exists('metro.stocksync.tomarketplace', $_POST['conf'])
        ) {
            // validate settings for current marketplace, which can't set the stock options
            if (!$this->canSetStockOptions(array(
                'metro.clientkey' => $_POST['conf']['metro.clientkey'],
                'metro.shippingorigin' => $_POST['conf']['metro.shippingorigin'],
                'metro.stocksync.tomarketplace' => $_POST['conf']['metro.stocksync.tomarketplace'],
            ))
            ) {
                $_POST['conf']['metro.stocksync.tomarketplace'] = 'no';

                $cross_border_settings = MetroCrossBordersConfiguration::gi()
                    ->getMarketplace(self::getCrossBordersStockOptionsMpid($this->mpID));
                $_POST['conf']['metro.maxquantity'] = $cross_border_settings['metro.maxquantity'];
                $_POST['conf']['metro.quantity.type'] = $cross_border_settings['metro.quantity.type'];
                $_POST['conf']['metro.quantity.value'] = $cross_border_settings['metro.quantity.value'];
            }

            // update data for warning and tooltip generation
            MetroCrossBordersConfiguration::gi()
                ->set($this->mpID, 'metro.maxquantity', $_POST['conf']['metro.maxquantity'])
                ->set($this->mpID, 'metro.quantity.type', $_POST['conf']['metro.quantity.type'])
                ->set($this->mpID, 'metro.quantity.value', $_POST['conf']['metro.quantity.value'])
                ->set($this->mpID, 'metro.shippingorigin', $_POST['conf']['metro.shippingorigin'])
                ->set($this->mpID, 'metro.stocksync.tomarketplace',
                    $_POST['conf']['metro.stocksync.tomarketplace']);

            // if this is the cross borders tab with the active stock synchronization, all other marketplaces with the
            // same cross border setting needs to be updated for the quantity settings
            if ($this->canSetStockOptions()) {
                $cross_border_settings = MetroCrossBordersConfiguration::gi()
                    ->getMarketplace($this->mpID);
                foreach (MetroCrossBordersConfiguration::gi()
                             ->iterateSameCrossBorderMarketplaces($this->mpID) as $marketplace
                ) {
                    // update settings in database
                    foreach (array('metro.maxquantity', 'metro.quantity.type', 'metro.quantity.value') as $key) {
                        MagnaDB::gi()->update('magnalister_config', array(
                            'value' => $cross_border_settings[$key]
                        ), array(
                            'mpID' => $marketplace['mpID'],
                            'mkey' => $key
                        ));
                    }

                    // update settings in api
                    MagnaConnector::gi()->submitRequest(array(
                        'MARKETPLACEID' => $marketplace['mpID'],
                        'ACTION' => 'SavePluginConfig',
                        'DATA' => loadDBConfig($marketplace['mpID']),
                    ));
                }
            }
        }
    }

    /**
     * Returns if the current metro tab can set stock options due to metro cross borders.
     *
     * Only the first metro tab can set the stock options, if they have the same account and origin settings.
     *
     * Optionally a shipping origin and stock sync setting can be passed to check against, this will not be cached.
     *
     * @param array{
     *     "metro.clientkey":string,
     *     "metro.shippingorigin":string,
     *     "metro.stocksync.tomarketplace":string
     * }|null $settings If set, it will not use the cache and validates the data on these.
     * @return bool
     */
    public function canSetStockOptions($settings = null) {
        if (null !== $this->canSetStockOptionsCache && !$settings) {
            return $this->canSetStockOptionsCache;
        }

        $cross_borders_conf = MetroCrossBordersConfiguration::gi();
        $marketplace_cnt = $cross_borders_conf->countMarketplaces();

        if (!$settings) {
            $this->canSetStockOptionsCache = true;
        }

        if (1 < $marketplace_cnt) {
            $current_marketplace = $cross_borders_conf->getMarketplace($this->mpID);
            foreach ($cross_borders_conf->iterateMarketplaces() as $marketplace) {
                $client_key = $settings['metro.clientkey'] ?: $current_marketplace['metro.clientkey'];
                $shipping_origin = $settings['metro.shippingorigin'] ?: $current_marketplace['metro.shippingorigin'];
                $stock_sync = $settings['metro.stocksync.tomarketplace'] ?: $marketplace['metro.stocksync.tomarketplace'];
                if ($marketplace['mpID'] != $this->mpID &&
                    $marketplace['metro.clientkey'] == $client_key &&
                    $marketplace['metro.shippingorigin'] == $shipping_origin &&
                    'auto' == $stock_sync
                ) {
                    if ($settings) {
                        return false;
                    }
                    $this->canSetStockOptionsCache = false;

                    break;
                }
            }
        }

        if ($settings) {
            return true;
        }

        return $this->canSetStockOptionsCache;
    }

    /**
     * Currently used to block cross border trading
     */
    protected function configurationJavascript() {
        ob_start();
        ?>
        <script type="text/javascript">/*<!CDATA[*/
            $(document).ready(function() {
                let $shipping_destination = $('#config_metro_shippingdestination');
                let $shipping_origin = $('#config_metro_shippingorigin');
                let cross_border_combinations = $shipping_destination.data('originDestinationCombinations');

                if ($shipping_destination.length && $shipping_origin.length && cross_border_combinations) {
                    $shipping_origin.on('change', function () {
                        let origin_key = $shipping_origin.val().replace(/_MAIN$/, '');

                        $('> option[value]', $shipping_destination).each(function () {
                            let $this = $(this);
                            let idx = cross_border_combinations[origin_key].indexOf(
                                $this.val().replace(/_MAIN$/, ''));
                            if (-1 !== idx) {
                                $this.removeAttr('disabled');
                            } else {
                                $this.attr('disabled', 'disabled');
                            }
                        });

                        if ($('> option:selected', $shipping_destination).attr('disabled')) {
                            $shipping_destination.val($shipping_origin.val());
                        }
                    });
                }
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
            'login', 'country', 'prepare', 'checkin', 'price',
            'inventorysync', 'orders', 'orderStatus', 'invoices'
        );
    }

    /**
     * Fetch all origin destination combinations from the API.
     *
     * @return array
     */
    protected function fetchOriginDestinationCombinations() {
        $response = MagnaConnector::gi()->submitRequest(array(
            'SUBSYSTEM' => 'METRO',
            'ACTION' => 'GetOriginDestinationCombinations'
        ));
        // we got no valid data, stop here
        if (!is_array($response) || !array_key_exists('STATUS', $response)
            || 'SUCCESS' != $response['STATUS']
        ) {
            return array();
        }

        return $response['DATA'];
    }

    protected function loadChoiseValues() {
        parent::loadChoiseValues();
        if ($this->isAuthed) {
            $this->getCancellationReason();
            $combinations = $this->fetchOriginDestinationCombinations();

            if (!array_key_exists('parameters', $this->form['country']['fields']['shippingdestination'])) {
                $this->form['country']['fields']['shippingdestination']['parameters'] = array();
            }
            $this->form['country']['fields']['shippingdestination']['parameters']['data-origin-destination-combinations'] = htmlspecialchars(json_encode($combinations));
            $this->form['prepare']['fields']['processingtime']['values'] = $this->renderProcessingTimeValues();
            $this->form['prepare']['fields']['maxprocessingtime']['values'] = $this->renderProcessingTimeValues();
            mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
            mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatus']);
        }
    }

    /**
     * Show cross borders warning and tooltips, if stock options can't be set.
     *
     * @return void
     */
    protected function loadChoiseValuesAfterProcessPOST() {
        global $magnaConfig;

        parent::loadChoiseValuesAfterProcessPOST();

        if (!$this->canSetStockOptions()) {
            $mpID = self::getCrossBordersStockOptionsMpid($this->mpID);
            $this->boxes .= sprintf('<div class="noticeBox">%s</span></div>', str_replace(array(
                '{#TAB_LABEL#}',
                '{#TAB_LINK#}'
            ), array(
                $magnaConfig['db'][0]['general.tabident'][$mpID],
                toURL(array('mp' => $mpID, 'mode' => 'conf'))
            ), ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_WARNING));

            // field quantity.type
            if (!array_key_exists('parameters', $this->form['checkin']['fields']['quantity'])) {
                $this->form['checkin']['fields']['quantity']['parameters'] = array();
            }
            $this->form['checkin']['fields']['quantity']['parameters']['disabled'] = 'disabled';
            $this->form['checkin']['fields']['quantity']['tooltip'] = ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_TOOLTIP;
            // field quantity.value
            if (!array_key_exists('parameters', $this->form['checkin']['fields']['quantity']['morefields']['quantity'])) {
                $this->form['checkin']['fields']['quantity']['morefields']['quantity']['parameters'] = array();
            }
            $this->form['checkin']['fields']['quantity']['morefields']['quantity']['parameters']['disabled'] = 'disabled';
            $this->form['checkin']['fields']['quantity']['morefields']['quantity']['tooltip'] = ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_TOOLTIP;

            // field maxquantity
            if (!array_key_exists('parameters', $this->form['checkin']['fields']['maxquantity'])) {
                $this->form['checkin']['fields']['maxquantity']['parameters'] = array();
            }
            $this->form['checkin']['fields']['maxquantity']['parameters']['disabled'] = 'disabled';
            $this->form['checkin']['fields']['maxquantity']['tooltip'] = ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_TOOLTIP;

            // field stocksync.tomarketplace
            if (!array_key_exists('parameters', $this->form['inventorysync']['fields']['stock_shop'])) {
                $this->form['inventorysync']['fields']['stock_shop']['parameters'] = array();
            }
            $this->form['inventorysync']['fields']['stock_shop']['parameters']['disabled'] = 'disabled';
            $this->form['inventorysync']['fields']['stock_shop']['tooltip'] = ML_METRO_CROSS_BORDERS_STOCK_LIMITATION_TOOLTIP;
            $this->form['inventorysync']['fields']['stock_shop']['default'] = 'no';
        }
    }

    /**
     * Return the mpID for the marketplace tab, where the stock synchronisation is active.
     *
     * @param int $marketplace_id
     * @return int|null
     */
    private static function getCrossBordersStockOptionsMpid($marketplace_id) {
        if (null === self::$crossBordersStockOptionsMpid) {
            self::$crossBordersStockOptionsMpid = MetroCrossBordersConfiguration::gi()
                ->getCrossBordersStockOptionsMarketplaceId($marketplace_id);
        }

        return self::$crossBordersStockOptionsMpid;
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

    /**
     * Render the shipping origin field with extra JavaScript.
     *
     * @param array $args
     * @param string $value
     * @return string
     */
    public static function getShippingOriginHtml($args, &$value = '') {
        global $_MagnaSession, $_modules, $magnaConfig;

        $form = null;
        $configurator = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_magnacompat');

        // get input config, so we can render the shipping origin field
        $params = array();
        $instance = new self($params);
        $config = $instance->loadConfigForm(array('metro/country.form' => array()), array(
            '_#_platform_#_' => $_MagnaSession['currentPlatform'],
            '_#_platformName_#_' => $_modules[$_MagnaSession['currentPlatform']]['title']
        ));
        // rewrite type to selection, so the select input will be rendered
        $config['country']['fields']['shippingorigin']['type'] = 'selection';
        // render select field
        $html = $configurator->renderInput($config['country']['fields']['shippingorigin']);

        $cross_borders = array();
        $mpID = (int)$_GET['mp'];
        $current_marketplace = MetroCrossBordersConfiguration::gi()->getMarketplace($mpID);
        foreach (MetroCrossBordersConfiguration::gi()->iterateMarketplaces() as $marketplace) {
            $key = $marketplace['metro.clientkey'].'|'.$marketplace['metro.shippingorigin'];
            if ('auto' == $marketplace['metro.stocksync.tomarketplace']
                && $marketplace['metro.clientkey'] == $current_marketplace['metro.clientkey']
                && $marketplace['mpID'] != $mpID
            ) {
                if (!array_key_exists($key, $cross_borders)) {
                    $stock_options_mpid = MetroCrossBordersConfiguration::gi()
                        ->getCrossBordersStockOptionsMarketplaceId($marketplace['mpID']);
                    $cross_borders[$key] = [
                        'origin' => $marketplace['metro.shippingorigin'],
                        'tab_label' => $magnaConfig['db'][0]['general.tabident'][$stock_options_mpid],
                        'tab_link' => toURL(array('mp' => $stock_options_mpid, 'mode' => 'conf'))
                    ];
                }
            }
        }

        // render additional javascript
        ob_start();
        ?>
        <script type="application/javascript">
            (function ($) {
                $(document).ready(function () {
                    let $clientKey = $('#config_metro_clientkey');
                    let $shippingOrigin = $('#config_metro_shippingorigin');
                    let cross_borders = <?php echo json_encode($cross_borders); ?>;
                    $clientKey.data('oldValue', $clientKey.val());
                    $shippingOrigin.data('oldValue', $shippingOrigin.val());
                    $shippingOrigin.on('change', function () {
                        if ($shippingOrigin.data('oldValue') === $shippingOrigin.val()
                            && $clientKey.data('oldValue') === $clientKey.val()
                        ) {
                            return;
                        }

                        let message = '';
                        let cross_borders_key = $clientKey.val() + '|' + $shippingOrigin.val();
                        if ($clientKey.data('oldValue') + '|' + $shippingOrigin.data('oldValue') in cross_borders
                            && !(cross_borders_key in cross_borders)
                        ) {
                            message = 'Durch die Änderung des Versandlandes werden die Lager-Einstellungen wieder ' +
                                'aktiviert, Sie sollten im Anschluss überprüfen, ob die Werte für die Felder ' +
                                '"Stückzahl Lagerbestand", "Stückzahl-Begrenzung" unter "Artikel hochladen: ' +
                                'Voreinstellungen", sowie "Lagerveränderung Shop" unter "Synchronisation des ' +
                                'Inventars" korrekt sind.';
                        } else if (cross_borders_key in cross_borders) {
                            message = 'Für das ausgewählte Versandland gibt es bereits eine Konfiguration in ' +
                                '<a href="{#TAB_LINK#}">Tab {#TAB_LABEL#}</a>. Wenn Sie zustimmen, wird für diese ' +
                                'Anbindung die Lager-Einstellungen deaktiviert, da diese über ' +
                                '<a href="{#TAB_LINK#}">Tab {#TAB_LABEL#}</a> gesteuert wird.';
                            message = message.replaceAll(/\{#TAB_LINK#}/g, cross_borders[cross_borders_key]['tab_link']);
                            message = message.replaceAll(/\{#TAB_LABEL#}/g, cross_borders[cross_borders_key]['tab_label']);
                        }

                        if (!message) {
                            return;
                        }

                        $('<div></div>').html(message).jDialog({
                            title: 'Metro Cross Borders Limitierung',
                            buttons: {
                                '<?php echo ML_BUTTON_LABEL_NO; ?>': function() {
                                    $shippingOrigin.val($shippingOrigin.data('oldValue'));
                                    $(this).dialog('close');
                                },
                                '<?php echo ML_BUTTON_LABEL_YES; ?>': function() {
                                    $('#conf_magnacompat').submit();
                                }
                            }
                        });
                    });
                });
            })(jQuery);
        </script>
        <?php
        $html .= ob_get_contents();
        ob_end_clean();

        return $html;
    }

    protected function finalizeForm() {
        parent::finalizeForm();

        if (!$this->isAuthed) {
            $this->form = array(
                'login' => $this->form['login']
            );

            return;
        }

        $this->processCrossBorderValues();
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
