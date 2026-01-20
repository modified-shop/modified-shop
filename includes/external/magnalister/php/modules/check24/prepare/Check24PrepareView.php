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
 * $Id$
 *
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

//defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'check24/classes/Check24ApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'check24/classes/Check24TopTenCategories.php');
class Check24PrepareView extends MagnaCompatibleBase {

    /**
     * @var null
     */
    protected $catMatch = null;

    /**
     * @var null
     */
    protected $topTen = null;

    /**
     * @var bool
     */
    protected $businessSeller = false;

    /**
     * @var mixed|string|null
     */
    protected $defaultShippingTemplate = '';

    /**
     * @var string
     */
    protected $currentLanguageCode = '';

    public function __construct(&$params) {
        global $_url, $_MagnaSession;

        parent::__construct($params);

        $this->url = $_url;
        $this->mpID = $_MagnaSession['mpID'];
        $this->marketplace = $_MagnaSession['currentPlatform'];
    }
    protected function initCatMatching() {
        $params = array();
        foreach (array('mpID', 'marketplace', 'marketplaceName') as $attr) {
            if (isset($this->$attr)) {
                $params[$attr] = &$this->$attr;
            }
        }
    }

    public function render() {
        if ($this->request == 'ajax') {
            return $this->renderAjax();
        } else {
            return $this->renderView();
        }
    }
    public function renderAjax($param = false) {
        if (!isset($_GET['Action']) && isset($_GET['where']) && ($_GET['where'] == 'prepareView')) {

        } else {
            if ($_POST['prepare'] === 'prepare' || (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations'))) {

                if ($param) {
                    $independentAttributesClass = new Check24IndependentAttributes;
                    $independentAttributes = $independentAttributesClass->getCategoryIndependentAttributes();

                    $productModel = Check24Helper::gi()->getProductModel('prepare');
                    return json_encode(Check24Helper::gi()->getCategoryIndependentAttributes($independentAttributes, $_POST['SelectValue'], $productModel, true));
                } else {
                    if (isset($_POST['SelectValue'])) {
                        $select = $_POST['SelectValue'];
                    } else {
                        $select = $_POST['PrimaryCategory'];
                    }

                    $productModel = Check24Helper::gi()->getProductModel('prepare');
                    return json_encode(Check24Helper::gi()->getMPVariations($select, $productModel, true));
                }
            } else {
                if (isset($_POST['Action']) && ($_POST['Action'] === 'DBMatchingColumns')) {
                    $columns = MagnaDB::gi()->getTableCols($_POST['Table']);
                    $editedColumns = array();
                    foreach ($columns as $column) {
                        $editedColumns[$column] = $column;
                    }

                    echo json_encode($editedColumns, JSON_FORCE_OBJECT);
                }
            }
        }
    }
    protected function getSelection() {
        $shortDescColumnExists = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);
        $keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

        $dbOldSelectionQuery = '
			SELECT *
			  FROM ' . TABLE_MAGNA_CHECK24_PROPERTIES. ' dp
		';
        if ($keytypeIsArtNr) {
            $dbOldSelectionQuery .= '
		INNER JOIN ' . TABLE_PRODUCTS . ' p ON dp.products_model = p.products_model
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON p.products_id = ms.pID AND dp.mpID = ms.mpID
			';
        } else {
            $dbOldSelectionQuery .= '
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON dp.products_id = ms.pID AND dp.mpID = ms.mpID
			';
        }
        $dbOldSelectionQuery .='
		     WHERE selectionname = "prepare"
		           AND ms.mpID = "' . $this->mpID . '"
		           AND session_id="' . session_id() . '"
		           AND dp.products_id IS NOT NULL
		           AND TRIM(dp.products_id) <> ""
		';
        $dbOldSelection = MagnaDB::gi()->fetchArray($dbOldSelectionQuery);
        $oldProducts = array();
        if (is_array($dbOldSelection)) {
            foreach ($dbOldSelection as &$row) {
                foreach (array('ItemHandlingData', 'GPSRData') as $sMoreData) {
                    if (!empty($row[$sMoreData])) {
                        $aMoreData = json_decode($row[$sMoreData], true);
                        if (is_array($aMoreData) && !empty($aMoreData)) {
                            foreach ($aMoreData as $mdKey => $mdValue) {
                                $row[$mdKey] = $mdValue;
                            }
                        }
                    }
                }
                $oldProducts[] = MagnaDB::gi()->escape($keytypeIsArtNr ? $row['products_model'] : $row['products_id']);
            }
        }

        # Daten fuer properties Tabelle
        # die Namen schon fuer diese Tabelle
        # products_short_description nicht bei OsC, nur bei xtC, Gambio und Klonen
        $dbNewSelectionQuery = '
		    SELECT ms.mpID mpID, p.products_id, p.products_model
		      FROM ' . TABLE_PRODUCTS . ' p
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
		     WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("' . implode('", "', $oldProducts) . '")
		           AND selectionname="prepare"
		           AND session_id="' . session_id() . '"
		';
        $dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);
        $dbSelection = array_merge(
                is_array($dbOldSelection) ? $dbOldSelection : array(),
                is_array($dbNewSelection) ? $dbNewSelection : array()
        );
        if (false) { # DEBUG
            echo '<span id="shMlDebug">X</span>';
            echo '<div id="mlDebug">';
            echo print_m("dbOldSelectionQuery == \n$dbOldSelectionQuery\n");
            echo print_m($dbOldSelection, '$dbOldSelection');

            echo print_m("dbNewSelectionQuery == \n$dbNewSelectionQuery\n");
            echo print_m($dbNewSelection, '$dbNewSelection');
            echo print_m($dbSelection, '$dbSelectionMerged');
            echo '</div>';
            ob_start();
            ?>
            <script type="text/javascript">/*<![CDATA[*/
                $('#mlDebug').fadeOut(0);
                $('#shMlDebug').on('click', function() {
                    $('#mlDebug:visible').fadeOut();
                    $('#mlDebug:hidden').fadeIn();
                });
                /*]]>*/</script>
            <?php
            $content = ob_get_contents();
            ob_end_clean();
            echo $content;
        }

        #echo print_m($dbSelection, __METHOD__);
        return $dbSelection;
    }
    protected function renderAttributesJS() {

        global $_url;
        ob_start();
        ?>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS; ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS; ?>js/marketplaces/check24/variation_matching.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo DIR_MAGNALISTER_WS; ?>css/select2/select2.min.css?<?php echo CLIENT_BUILD_VERSION?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo DIR_MAGNALISTER_WS; ?>css/select2/fix-select2.css?<?php echo CLIENT_BUILD_VERSION?>" />
        <script type="text/javascript">
            /*<![CDATA[*/``
            var ml_vm_config = {
                url: '<?php echo toURL($_url, array('where' => 'Check24PrepareView', 'kind' => 'ajax'), true);?>',
                viewName: 'Check24PrepareView',
                secondaryCategory: false,
                formName: '#prepareForm',
                handleCategoryChange: false,
                i18n: <?php echo json_encode(Check24Helper::gi()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode(Check24Helper::gi()->getShopVariations()); ?>
            };
            /*]]>*/</script><?php
        $sAttrMatchJS = ob_get_contents();
        ob_end_clean();

        return $sAttrMatchJS;
    }
    protected function renderSinglePrepareView($data) {
        //preparation form

        $html = '';
        ob_start();
        ?>

        <div id="infodiag" class="dialog2" title="<?php echo ML_LABEL_INFORMATION ?>"></div>
        <script type="text/javascript">
            /*<![CDATA[*/
            $(document).ready(function () {
                $('#desc_1').click(function () {
                    var d = $('#desc_1 span').html();
                    $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
                });

            });
            /*]]>*/
        </script>

        <?php echo $this->renderMultiPrepareView(array($data), 'single'); ?>
        <?php
        $html .= ob_get_clean();
        return $html;
    }

    protected function renderPrepareView($data) {
        #$this->hasStore();
        if (($hp = magnaContribVerify($this->marketplace.'PrepareView_renderPrepareView', 1)) !== false) {
            require($hp);
        }
        /**
         * Check ob einer oder mehrere Artikel
         */
        $prepareView = (1 == count($data)) ? 'single' : 'multiple';

        $renderedView = $this->renderAttributesJS();

        $renderedView .= '
			<form method="post" id="prepareForm" action="'.toURL($this->resources['url']).'">
				<table class="attributesTable">';
        if ('single' == $prepareView) {
            $renderedView .= $this->renderSinglePrepareView($data[0]);
        } else {
            $renderedView .= $this->renderMultiPrepareView($data);
        }
        $renderedView .= '
				</table>
				<table class="actions">
					<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
					<tbody>
						<tr class="firstChild"><td>
							<table><tbody><tr>
								<td class="firstChild">'.(
                ($prepareView == 'single')
                        ? '<input class="ml-button" type="submit" name="unprepare" id="unprepare" value="'.ML_BUTTON_LABEL_REVERT.'"/>'
                        : ''
                ).'
								</td>
								<td class="lastChild">
									<input class="ml-button mlbtn-action" type="submit" name="savePrepareData" id="savePrepareData" value="'.ML_BUTTON_LABEL_SAVE_DATA.'"/>
								</td>
							</tr></tbody></table>
						</td></tr>
					</tbody>
				</table>
			</form>';
        return $renderedView;
    }

    /**
     * @param $data
     * 	enhealt bereits vorausgefuellte daten aus Config oder User-eingaben
     */
    protected function renderMultiPrepareView($data) {
        #echo print_m($data, '$data');

        // Check which values all prepared products have in common to preselect the values.
        $preSelected = array (
                'ShippingTime' => array(),
                'ShippingCost' => array(),
                'DeliveryMode' => array(),
                'DeliveryModeText' => array(),
                '2MenHandling' => array(),
                'InstallationService' => array(),
                'RemovalOldItem' => array(),
                'RemovalPackaging' => array(),
                'AvailableServiceProductIds' => array(),
                'LogisticsProvider' => array(),
                'CustomTariffsNumber' => array(),
                'ReturnShippingCosts' => array(),
        );

        $defaults = array (
                'ShippingTime' => getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID, 1),
                'ShippingCost' => getDBConfigValue($this->marketplace.'.shippingcost', $this->mpID, 0),
                'DeliveryMode' => getDBConfigValue($this->marketplace.'.delivery_mode', $this->mpID, '-'),
                'DeliveryModeText' => getDBConfigValue($this->marketplace.'.delivery_mode.text', $this->mpID, ''),
                '2MenHandling' => getDBConfigValue($this->marketplace.'.2men_handling', $this->mpID, ''),
                'InstallationService' => getDBConfigValue($this->marketplace.'.installation_service', $this->mpID, ''),
                'RemovalOldItem' => getDBConfigValue($this->marketplace.'.removal_old_item', $this->mpID, ''),
                'RemovalPackaging' => getDBConfigValue($this->marketplace.'.removal_packaging', $this->mpID, ''),
                'AvailableServiceProductIds' => getDBConfigValue($this->marketplace.'.available_service_product_ids', $this->mpID, ''),
                'LogisticsProvider' => getDBConfigValue($this->marketplace.'.logistics_provider', $this->mpID, ''),
                'CustomTariffsNumber' => getDBConfigValue($this->marketplace.'.custom_tariffs_number.dbmatching.table', $this->mpID, ''),
                'ReturnShippingCosts' => getDBConfigValue($this->marketplace.'.return_shipping_costs', $this->mpID, ''),
        );


        // CustomTariffsNumber comes from the DB: Show only if single preparation
        if (count($data) == 1) {
            $sCurrKey = key($data);
            if (    !array_key_exists('CustomTariffsNumber', $data[$sCurrKey])
                    || empty($data[$sCurrKey]['CustomTariffsNumber'])) {
                if (is_array($defaults['CustomTariffsNumber'])
                        && !empty($defaults['CustomTariffsNumber']['table'])
                        && !empty($defaults['CustomTariffsNumber']['column'])) {
                    $data[$sCurrKey]['CustomTariffsNumber'] = MagnaDB::gi()->fetchOne('SELECT '.$defaults['CustomTariffsNumber']['column'].' FROM '.$defaults['CustomTariffsNumber']['table'].' WHERE products_id = '.$data[$sCurrKey]['products_id'].' LIMIT 1');
                } else {
                    $data[$sCurrKey]['CustomTariffsNumber'] = '';
                }
            }
            $blMulti = false;
        } else {
            $blMulti = true;
        }

        $loadedPIds = array();
        foreach ($data as $row) {
            $loadedPIds[] = $row['products_id'];
            global  $_MagnaSession;
            $aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_CHECK24_PROPERTIES.'
			 WHERE products_id = "'.$row['products_id'].'" AND mpID = '. $_MagnaSession['mpID'] );
            //echo print_m($aPropertiesRow);
            if (isset($aPropertiesRow)) {
                if (isset($aPropertiesRow['GPSRData']) && !empty($aPropertiesRow['GPSRData'])) {
                    if (isset($aPropertiesRow['CategoryIndependentShopVariation']) && empty($aPropertiesRow['CategoryIndependentShopVariation'])) {
                        $GPSRDATA = json_decode($aPropertiesRow['GPSRData'], true);
                        $variationShopGenerator = array();
                        foreach ($GPSRDATA as $key => $value) {

                            if (!in_array($key, array('Verantwortliche_Person_fuer_EU_Name', 'Verantwortliche_Person_fuer_EU_Strasse_Hausnummer', 'Verantwortliche_Person_fuer_EU_PLZ', 'Verantwortliche_Person_fuer_EU_Stadt', 'Verantwortliche_Person_fuer_EU_Land', 'Verantwortliche_Person_fuer_EU_Email', 'Verantwortliche_Person_fuer_EU_Telefonnummer'))) {
                                if($key == 'Hersteller_Strasse_Hausnummer') {
                                    $variationShopGenerator['manufacturer street'] = array(
                                            'Code' => "freetext",
                                            'Kind' => "FreeText",
                                            'Required' => true,
                                            'AttributeName' => "GPSR - Hersteller Straße und Hausnummer",
                                            'Values' => $value,
                                            "Error" => false
                                    );
                                }else{
                                    if($key == 'Marke'){
                                        $variationShopGenerator['brand'] = array(
                                                'Code' => "freetext",
                                                'Kind' => "FreeText",
                                                'Required' => true,
                                                'AttributeName' => "GPSR - Brand",
                                                'Values' => $value,
                                                "Error" => false
                                        );
                                    }elseif ($key == 'Hersteller_Name') {
                                        $variationShopGenerator['manufacturer name'] = array(
                                                'Code' => "freetext",
                                                'Kind' => "FreeText",
                                                'Required' => true,
                                                'AttributeName' => "GPSR - Hersteller Name",
                                                'Values' => $value,
                                                "Error" => false
                                        );
                                    }elseif ($key == 'Hersteller_PLZ') {
                                        $variationShopGenerator['manufacturer postcode'] = array(
                                                'Code' => "freetext",
                                                'Kind' => "FreeText",
                                                'Required' => true,
                                                'AttributeName' => "GPSR - Hersteller PLZ",
                                                'Values' => $value,
                                                "Error" => false
                                        );
                                    }elseif ($key == 'Hersteller_Stadt') {
                                        $variationShopGenerator['manufacturer city'] = array(
                                                'Code' => "freetext",
                                                'Kind' => "FreeText",
                                                'Required' => true,
                                                'AttributeName' => "GPSR - Hersteller Stadt",
                                                'Values' => $value,
                                                "Error" => false
                                        );
                                    }elseif ($key == 'Hersteller_Land') {
                                        $variationShopGenerator['manufacturer country'] = array(
                                                'Code' => "freetext",
                                                'Kind' => "FreeText",
                                                'Required' => true,
                                                'AttributeName' => "GPSR - Hersteller Land",
                                                'Values' => $value,
                                                "Error" => false
                                        );
                                    }elseif ($key == 'Hersteller_Email') {
                                        $variationShopGenerator['manufacturer email'] = array(
                                                'Code' => "freetext",
                                                'Kind' => "FreeText",
                                                'Required' => true,
                                                'AttributeName' => "GPSR - Hersteller Email",
                                                'Values' => $value,
                                                "Error" => false
                                        );
                                    }elseif ($key == 'Hersteller_Telefonnummer') {
                                        $variationShopGenerator['manufacturer phone number'] = array(
                                                'Code' => "freetext",
                                                'Kind' => "FreeText",
                                                'Required' => true,
                                                'AttributeName' => "Hersteller Telefonnummer",
                                                'Values' => $value,
                                                "Error" => false
                                        );
                                    }
                                }

                            }
                        }
                        MagnaDB::gi()->update(TABLE_MAGNA_CHECK24_PROPERTIES, array(
                                'CategoryIndependentShopVariation' => json_encode($variationShopGenerator),
                        ), array(
                                'mpID' => $_MagnaSession['mpID'],
                                'products_id' => $row['products_id'],
                        ));
                    }
                }
            }
            foreach ($preSelected as $field => $collection) {
                $preSelected[$field][] = isset($row[$field]) ? $row[$field] : null;
            }
        }
        #echo print_m($preSelected, '$preSelected{L:'.__LINE__.'}');
        foreach ($preSelected as $field => $collection) {
            $collection = array_unique($collection);
            if (count($collection) == 1) {
                $preSelected[$field] = array_shift($collection);
                if (($preSelected[$field] === null) && isset($defaults[$field])) {
                    $preSelected[$field] = $defaults[$field];
                }
            } else {
                $preSelected[$field] = isset($defaults[$field])
                        ? $defaults[$field]
                        : null;
            }
        }

        $oddEven = false;
        $html = '			
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>' . ML_CHECK24_SHIPPING . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_RICARDO_AVAILABILITY . '</th>
					<td class="input">
						<select name="ShippingTime">';
        foreach (array_slice(range(0, 30), 1, null, true) as $sKey => $sVal) {
            $html .= '
							<option value="' . $sKey . '" ' . (
                    ($preSelected['ShippingTime'] == $sKey)
                            ? 'selected="selected"'
                            : ''
                    ) . '>' . $sVal . '</option>';
        }
        $html .= '
						</select>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_SHIPPING_COST . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="ShippingCost" value="' . $preSelected['ShippingCost'] . '" class="fullwidth" />
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>';
        $html .= '
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr class="headline">
					<th colspan="3">' . ML_CHECK24_OPTIONAL_SHIPPING_DATA . '</th>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_DELIVERY_MODE . '</th>
					<td class="input">
						<select name="DeliveryMode">';
        foreach (array('-' => '-',
                         'Paket' => 'Paket',
                         'Warensendung' => 'Warensendung',
                         'Spedition' => 'Spedition',
                         'Sperrgut' => 'Sperrgut',
                         'EigeneAngaben' => 'Eigene Angaben') as $sKey => $sVal) {
            $html .= '
							<option value="' . $sKey . '" ' . (
                    ($preSelected['DeliveryMode'] == $sKey)
                            ? 'selected="selected"'
                            : ''
                    ) . '>' . $sVal . '</option>';
        }
        $html .= '
						</select>
					<input style="padding-left: 2px;" type="text" name="DeliveryModeText" value="' . $preSelected['DeliveryModeText'] . '" />
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'td.input > select option[value="EigeneAngaben"], td.input > select option[value="EigeneAngaben"]\').closest("select").on("change", function() {
					var self = $(this);
					if (self.val() == "EigeneAngaben") {
						self.closest("td").find(" > * ").not(self).show();
					} else {
						self.closest("td").find(" > * ").not(self).hide();
					}
					}).trigger("change");
				});
				/*]]>*/</script>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_2MEN_HANDLING . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="2MenHandling" value="' . $preSelected['2MenHandling'] . '" class="fullwidth" />
					</td>
					<td class="info">' . ML_CHECK24_2MEN_HANDLING_INFO. '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_INSTALLATION_SERVICE . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="InstallationService" value="' . $preSelected['InstallationService'] . '" class="fullwidth" />
					</td>	
					<td class="info">' . ML_CHECK24_INSTALLATION_SERVICE_INFO . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_REMOVAL_OLD_ITEM . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="RemovalOldItem" value="' . $preSelected['RemovalOldItem'] . '" class="fullwidth" />
					</td>	
					<td class="info">' . ML_CHECK24_REMOVAL_OLD_ITEM_INFO . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_REMOVAL_PACKAGING . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="removalPackaging" value="' . $preSelected['removalPackaging'] . '" class="fullwidth" />
					</td>	
					<td class="info">' . ML_CHECK24_REMOVAL_PACKAGING_INFO . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_AVAILABLE_SERVICE_PRODUCT_IDS . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="AvailableServiceProductIds" value="' . $preSelected['AvailableServiceProductIds'] . '" class="fullwidth" />
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_LOGISTICS_PROVIDER . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="LogisticsProvider" value="' . $preSelected['LogisticsProvider'] . '" class="fullwidth" />
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>';
        if (!$blMulti) {
            $html .= '
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_CUSTOM_TARIFFS_NUMBER . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="CustomTariffsNumber" value="' . $preSelected['CustomTariffsNumber'] . '" class="fullwidth" />
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>';
        }
        $html .= '
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_CHECK24_RETURN_SHIPPING_COSTS . '</th>
					<td class="input">
						<input style="padding-left: 2px;" type="text" name="ReturnShippingCosts" value="' . $preSelected['ReturnShippingCosts'] . '" class="fullwidth" />
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>

			</tbody>';
        $html .= Check24IndependentAttributes::renderIndependentAttributesTable();
        ob_start();
        $html .= ob_get_contents();
        ob_end_clean();
        return $html;
    }






    protected function renderCategoryOptions($type, $selectedCat = null, $selectedCatName = null) {
        $opt = '<option value="1" selected="selected"'.'>1</option>';
        return $opt;
    }


    protected function processMagnaExceptions() {
        $ex = Check24ApiConfigValues::gi()->getMagnaExceptions();
        $html = '';
        foreach ($ex as $e) {
            if (in_array($e->getSubsystem(), array('Core', 'PHP', 'Database'))) {
                continue;
            }
            $html .= '<p class="errorBox">'.fixHTMLUTF8Entities($e->getMessage()).'</p>';
            $e->setCriticalStatus(false);
        }
        return $html;
    }

    public function process() {
        Check24ApiConfigValues::gi()->cleanMagnaExceptions();
        $this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));

        $html = $this->renderPrepareView($this->getSelection());

        return $this->processMagnaExceptions().$html;
    }


}
