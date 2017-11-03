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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/MagnaCompatibleBase.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');
require_once(DIR_MAGNALISTER_MODULES.'idealo/classes/IdealoApiConfigValues.php');

class IdealoPrepareView extends MagnaCompatibleBase {

    public function __construct(&$params) {
        parent::__construct($params);

        $this->marketplace = $params['session']['currentPlatform'];
        $this->mpID = $params['session']['mpID'];
        $this->resources = $params;
    }

    protected function getSelection() {
        $keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');
        $iLanguage = getDBConfigValue($this->marketplace . '.lang', $this->mpID);

        $dbOldSelectionQuery = '
			SELECT *
			  FROM ' . TABLE_MAGNA_IDEALO_PROPERTIES. ' dp
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
            foreach ($dbOldSelection as $row) {
                $oldProducts[] = MagnaDB::gi()->escape($keytypeIsArtNr ? $row['products_model'] : $row['products_id']);
            }
        }

        # Daten fuer properties Tabelle
        # die Namen schon fuer diese Tabelle
        # products_short_description nicht bei OsC, nur bei xtC, Gambio und Klonen
        $dbNewSelectionQuery = '
			SELECT	ms.mpID mpID,
					p.products_id,
					p.products_model,
					p.products_image as PictureUrl,
					pd.products_name as Title,
					pd.products_description as Description
			  FROM ' . TABLE_PRODUCTS . ' p
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = ' . $iLanguage . '
			 WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("' . implode('", "', $oldProducts) . '")
				   AND ms.mpID = "' . $this->mpID . '"
				   AND selectionname="prepare"
				   AND session_id="' . session_id() . '"
		';
        $dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);

        $dbSelection = array_merge(
            is_array($dbOldSelection) ? $dbOldSelection : array(),
            is_array($dbNewSelection) ? $dbNewSelection : array()
        );
        foreach ($dbSelection as &$dbSelectionRow) {
            $aPaymentMethods = json_decode($dbSelectionRow['PaymentMethod'], true);
            $dbSelectionRow['PaymentMethod'] = is_array($aPaymentMethods) ? $aPaymentMethods : (array)$dbSelectionRow['PaymentMethod'];
        }
        unset($dbSelectionRow);
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
        return $dbSelection;
    }

    protected function renderPrepareView($data) {
        if (($hp = magnaContribVerify($this->marketplace.'PrepareView_renderPrepareView', 1)) !== false) {
            require($hp);
        }

        $preSelected = $this->getPreSelectedData($data);

        /**
         * Check ob einer oder mehrere Artikel
         */
        $prepareView = (1 == count($data)) ? 'single' : 'multiple';

        $renderedView = '
            <form method="post" action="' . toURL($this->resources['url']) . '">
                <table class="attributesTable">';
        if ('single' == $prepareView) {
            $renderedView .= $this->renderSinglePrepareView($data[0], $preSelected);
            $renderedView .= $this->renderMultiPrepareView($data, $preSelected);
        } else {
            $renderedView .= $this->renderMultiPrepareView($data, $preSelected);
        }
        $renderedView .= '
				</table>
			    <table class="actions">
					<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
					<tbody>
						<tr class="firstChild"><td>
							<table><tbody><tr>
								<td class="firstChild">'.(
            ($prepareView == 'single')
                ? '<input class="ml-button" type="submit" name="unprepare" id="unprepare" value="' . ML_BUTTON_LABEL_REVERT . '"/>'
                : ''
            ).'
								</td>
								<td class="lastChild">
									<input class="ml-button mlbtn-action" type="submit" name="savePrepareData" id="savePrepareData" value="' . ML_BUTTON_LABEL_SAVE_DATA . '"/>
								</td>
							</tr></tbody></table>
						</td></tr>
					</tbody>
				</table>
			</form>';
        return $renderedView;
    }

    /**
     * Enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
     *
     * @param $data
     * @param $preSelected
     * @return string
     */
    protected function renderSinglePrepareView($data, $preSelected) {
        $oddEven = false;

        $aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->marketplace.'.lang', $this->mpID))->getProductById($data['products_id']);

        $pictureUrls = array();
        if (isset($preSelected['PictureUrl']) && empty($preSelected['PictureUrl']) === false) {
            $pictureUrls = json_decode($preSelected['PictureUrl'], true);
        }

        if (empty($pictureUrls) || !is_array($pictureUrls)) {
            $pictureUrls = array();
            $i = 0;
            foreach ($aProduct['Images'] as $img) {
                $pictureUrls[$img] = 'true';
            }
        }

        foreach ($aProduct['Images'] as $img) {
            $img = fixHTMLUTF8Entities($img, ENT_COMPAT);
            $data['Images'][$img] = (isset($pictureUrls[$img]) && ($pictureUrls[$img] === 'true')) ? 'true' : 'false';
        }

        $data['Title'] = html_entity_decode($data['Title'], ENT_COMPAT, 'UTF-8');

        ob_start();
        ?>
        <tbody>
        <tr class="headline">
            <td colspan="3"><h4><?php echo ML_IDEALO_PRODUCT_DETAILS ?></h4></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?> langde">
            <th><?php echo ML_IDEALO_ITEM_NAME_TITLE ?></th>
            <td class="input">
                <input type="text" class="fullwidth" name="Title" id="Title"  value="<?php echo fixHTMLUTF8Entities($data['Title'], ENT_COMPAT, 'UTF-8') ?>"/>
            </td>
            <td class="info"></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?> langde">
            <th><?php echo ML_IDEALO_DESCRIPTION ?></th>
            <td class="input">
                <?php echo magna_wysiwyg(array(
                    'id' => 'Description',
                    'name' => 'Description',
                    'class' => 'fullwidth',
                    'cols' => '80',
                    'rows' => '20',
                    'wrap' => 'virtual'
                ), fixHTMLUTF8Entities($data['Description'], ENT_COMPAT)) ?>
            </td>
            <td class="info"></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th><?php echo ML_LABEL_PRODUCTS_IMAGES ?></th>
            <td class="input">
                <input type="hidden" id="image_hidden" name="PictureUrl[]" value="false"/>
                <?php foreach ($data['Images'] as $img => $checked) : ?>
                    <table class="imageBox"><tbody>
                        <tr><td class="image"><label for="image_<?php echo $img ?>"><?php echo generateProductCategoryThumb($img, 60, 60) ?></label></td></tr>
                        <tr><td class="cb"><input type="checkbox" id="image_<?php echo $img ?>" name="PictureUrl[<?php echo urlencode($img) ?>]" value="true" <?php echo $checked == 'true' ? 'checked="checked"' : '' ?> /></td></tr>
                        </tbody></table>
                <?php endforeach; ?>
            </td>
            <td class="info">
                <?php echo ML_IDEALO_TEXT_APPLY_PRODUCTS_IMAGES ?>
            </td>
        </tr>
        <tr class="spacer">
            <td colspan="3">&nbsp;</td>
        </tr>
        </tbody>
        <?php
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    protected function getPreSelectedData($data) {
        // Check which values all prepared products have in common to preselect the values.
        $preSelected = array (
            'PictureUrl' => null,
            'Checkout' => null,
            'PaymentMethod' => null,
            'ShippingMethod' => null,
            'ShippingCountry' => null,
            'ShippingCostMethod' => null,
            'ShippingCost' => null,
        );

        $defaults = array (
            'PictureUrl' => null,
            'Checkout' => getDBConfigValue($this->marketplace . '.checkout.status', $this->mpID),
            'PaymentMethod' => getDBConfigValue($this->marketplace . '.payment.methods', $this->mpID),
            'ShippingMethod' => getDBConfigValue($this->marketplace . '.shipping.methods', $this->mpID),
            'ShippingCountry' => getDBConfigValue($this->marketplace . '.shipping.country', $this->mpID),
            'ShippingCostMethod' => getDBConfigValue($this->marketplace . '.shipping.method', $this->mpID),
            'ShippingCost' => getDBConfigValue($this->marketplace . '.shipping.cost', $this->mpID),
        );

        foreach ($data as $row) {
            foreach ($preSelected as $field => $collection) {
                $preSelected[$field][] = isset($row[$field]) ? $row[$field] : null;
            }
        }

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

        return $preSelected;
    }

    /**
     * Enhealt bereits vorausgefuellte daten aus Config oder User-eingaben
     *
     * @param $data
     * @param $preSelected
     * @return string
     */
    protected function renderMultiPrepareView($data, $preSelected) {
        try {
            $isAuthedResult = MagnaConnector::gi()->submitRequest(array(
                'SUBSYSTEM' => 'ComparisonShopping',
                'ACTION' => 'IsAuthed',
            ));
        } catch (MagnaException $ex) {
        }

        if (isset($isAuthedResult) && $isAuthedResult['STATUS'] === 'SUCCESS') {
            $paymentMethods = $this->getPaymentMethods();
            $shippingMethods = $this->getShippingMethods();
        } else {
            $paymentMethods = array();
            $shippingMethods = array();
        }

        $tmpURL = $this->resources['url'];
        $tmpURL['where'] = 'prepareView';
        $oddEven = false;

        if (!is_array($preSelected['Checkout'])) {
            $preSelected['Checkout'] = json_decode($preSelected['Checkout'], true);
        }

        $checkoutChecked = $preSelected['Checkout']['val'] ? 'checked' : '';

        ob_start();
        ?>
        <tbody>
            <tr class="headline">
                <td colspan="3"><h4><?php echo ML_LABEL_GENERIC_SETTINGS ?></h4></td>
            </tr>
            <?php if (isset($isAuthedResult) && $isAuthedResult['STATUS'] === 'SUCCESS') {?>
            <tr class="<?php (($oddEven = !$oddEven) ? 'odd' : 'even') ?>">
                <th>
                    <div style="float: left;"><?php echo ML_IDEALO_LABEL_CHECKOUT_ACTIVE?></div>
                    <div style="float: right; width: 16px; height: 16px; background: transparent url('<?php echo DIR_MAGNALISTER_WS?>images/information.png') no-repeat 0 0;
                        cursor: pointer; display: inline-block; vertical-align: top;" class="desc" id="desc_1" title="Infos">
                        <span style="display: none"><?php echo ML_IDEALO_INFO_CHECKOUT_ACTIVE ?></span>
                    </div>
                </th>
                <td class="input">
                    <input type="checkbox" id="Checkout" name="Checkout" <?php echo $checkoutChecked ?>/>
                    <label for="Checkout"><?php echo ML_IDEALO_LABEL_CHECKOUT_ACTIVE_2 ?></label>
                </td>
                <td class="info"></td>
            </tr>
            <tr class="<?php (($oddEven = !$oddEven) ? 'odd' : 'even') ?>">
                <th>
                    <div style="float: left;"><?php echo ML_IDEALO_LABEL_PAYMENT_METHOD ?></div>
                    <div style="float: right; width: 16px; height: 16px; background: transparent url('<?php echo DIR_MAGNALISTER_WS?>images/information.png') no-repeat 0 0;
							cursor: pointer; display: inline-block; vertical-align: top;" class="desc" id="desc_2" title="Infos">
                        <span style="display: none"><?php echo ML_IDEALO_INFO_PAYMENT_METHOD ?></span>
                    </div>
                </th>
                <td class="input">
                    <?php
                    $paymentMethodsSelect = '<select id="PaymentMethod" name="PaymentMethod[]" multiple="multiple" size="12">';
                    foreach ($paymentMethods as $label => $paymentMethodGroup) {
                        $paymentMethodsSelect .= '<optgroup label="'.$label.'">';
                        foreach ($paymentMethodGroup as $key => $paymentMethod) {
                            $paymentMethodsSelect .= '<option value="'.$key.'"'.(
                            (
                                    (is_array($preSelected['PaymentMethod']) && in_array($key, $preSelected['PaymentMethod']))
                                    ||
                                    ($preSelected['PaymentMethod'] == $key)
                            )
                            ? ' selected="selected"'
                            : ''
                            ).'>'.$paymentMethod.'</option>'."\n";
                        }
                        $paymentMethodsSelect .= '</optgroup>';
                    }

                    echo $paymentMethodsSelect;
                    ?>
                    </select>
                </td>
                <td class="info"></td>
            </tr>
            <tr class="<?php (($oddEven = !$oddEven) ? 'odd' : 'even') ?>">
                <th>
                    <div style="float: left;"><?php echo ML_IDEALO_LABEL_SHIPPING_METHOD ?></div>
                    <div style="float: right; width: 16px; height: 16px; background: transparent url('<?php echo DIR_MAGNALISTER_WS?>images/information.png') no-repeat 0 0;
							cursor: pointer; display: inline-block; vertical-align: top;" class="desc" id="desc_3" title="Infos">
                        <span style="display: none"><?php echo ML_IDEALO_INFO_SHIPPING_METHOD ?></span>
                    </div>
                </th>
                <td class="input">
                    <?php
                    $shippingMethodsSelect = '<select id="ShippingMethod" name="ShippingMethod">'
                        . '<option value="noselection">' . ML_AMAZON_LABEL_APPLY_PLEASE_SELECT . '</option>' . "\n";

                    foreach ($shippingMethods as $key => $shippingMethod) {
                        $shippingMethodsSelect .= '<option value="'.$key.'"'.(
                            ($preSelected['ShippingMethod'] == $key)
                                ? ' selected="selected"'
                                : ''
                            ).'>'.$shippingMethod.'</option>'."\n";
                    }

                    echo $shippingMethodsSelect;
                    ?>
                    </select>
                </td>
                <td class="info"></td>
            </tr>
            <?php }?>
            <tr class="<?php (($oddEven = !$oddEven) ? 'odd' : 'even') ?>">
                <th>
                    <?php echo ML_IDEALO_LABEL_SHIPPING_COUNTRY ?>
                </th>
                <td class="input">
                    <?php
                        $shippingCountrySelect = '<select id="ShippingCountry" name="ShippingCountry">';
                        $shippingCountries = array();
                        mlGetCountries($shippingCountries);

                        foreach ($shippingCountries['values'] as $key => $shippingCountry) {
                            $shippingCountrySelect .= '<option value="'.$key.'"'.(
                        ($preSelected['ShippingCountry'] == $key)
                        ? ' selected="selected"'
                        : ''
                        ).'>'.$shippingCountry.'</option>'."\n";
                        }

                        echo $shippingCountrySelect;
                    ?>
                    </select>
                </td>
                <td class="info"></td>
            </tr>
            <tr class="<?php (($oddEven = !$oddEven) ? 'odd' : 'even') ?>">
                <th>
                    <div style="float: left;"><?php echo ML_IDEALO_LABEL_SHIPPING_COST ?></div>
                    <div style="float: right; width: 16px; height: 16px; background: transparent url('<?php echo DIR_MAGNALISTER_WS?>images/information.png') no-repeat 0 0;
							cursor: pointer; display: inline-block; vertical-align: top;" class="desc" id="desc_4" title="Infos">
                        <span style="display: none"><?php echo ML_IDEALO_INFO_SHIPPING_COST  ?></span>
                    </div>
                </th>
                <td class="input">
                    <?php
                    $shippingCostMethodSelect = '<select id="ShippingCostMethod" name="ShippingCostMethod">';
                    $shippingCostMethods = array();
                    mlGetShippingMethods($shippingCostMethods);

                    foreach ($shippingCostMethods['values'] as $key => $shippingCostMethod) {
                        $shippingCostMethodSelect .= '<option value="'.$key.'"'.(
                            ($preSelected['ShippingCostMethod'] == $key)
                                ? ' selected="selected"'
                                : ''
                            ).'>'.$shippingCostMethod.'</option>'."\n";
                    }

                    echo $shippingCostMethodSelect;
                    ?>
                    </select>
                    <label><?php echo ML_IDEALO_LABEL_SHIPPING_COST ?>:</label>
                    <input type="text" name="ShippingCost" id="ShippingCost"
                           value="<?php echo isset($data['ShippingCost']) ? $data['ShippingCost'] : $preSelected['ShippingCost'] ?>"/>
                    <label><?php echo ML_IDEALO_LABEL_SHIPPING_CURRENCY ?></label>
                </td>
                <td class="info"></td>
            </tr>
            <tr class="spacer">
                <td colspan="3">&nbsp;</td>
            </tr>
        </tbody>
        <div id="infodiag" class="dialog2" title="<?php echo ML_LABEL_INFORMATION ?>"></div>
        <script type="text/javascript">
            /*<![CDATA[*/
            $(document).ready(function() {
                $('#desc_1').click(function () {
                    var d = $('#desc_1 span').html();
                    $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
                });

                $('#desc_2').click(function () {
                    var d = $('#desc_2 span').html();
                    $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
                });

                $('#desc_3').click(function () {
                    var d = $('#desc_3 span').html();
                    $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
                });

                $('#desc_4').click(function () {
                    var d = $('#desc_4 span').html();
                    $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
                });
            });
            /*]]>*/
        </script>
        <?php
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    protected function processMagnaExceptions() {
        $ex = IdealoApiConfigValues::gi()->getMagnaExceptions();
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
        IdealoApiConfigValues::gi()->cleanMagnaExceptions();

        $html = $this->renderPrepareView($this->getSelection());

        return $this->processMagnaExceptions().$html;
    }

    private function getPaymentMethods() {
        return json_decode(ML_IDEALO_PAYMENTMETHOD_OPTION_GROUPS, true);
    }

    private function getShippingMethods() {
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'SUBSYSTEM' => 'ComparisonShopping',
                'ACTION' => 'GetShippingMethods',
            ));

            if (isset($result['DATA'])) {
                return $result['DATA'];
            }
        } catch (MagnaException $e) {
        }
    }
}
