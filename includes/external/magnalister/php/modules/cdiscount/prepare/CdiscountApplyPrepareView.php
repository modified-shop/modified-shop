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

require_once(DIR_MAGNALISTER_MODULES . 'cdiscount/prepare/CdiscountCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'cdiscount/classes/CdiscountTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES . 'cdiscount/CdiscountHelper.php');
require_once(DIR_MAGNALISTER_MODULES . 'cdiscount/configure.php');

class CdiscountApplyPrepareView extends MagnaCompatibleBase {
    const MARKETING_DESC_MAX_LENGTH = 5000;

    protected $catMatch = null;
    protected $prepareSettings = array();
    protected $price = null;

    /** @var $oCategoryMatching CdiscountCategoryMatching */
    protected $oCategoryMatching = null;

    public function process() {
        $oddEven = false;
        $this->oCategoryMatching = new CdiscountCategoryMatching();
        $attributeMatchingDialogHtml = $this->oCategoryMatching->renderMatching();
        $this->initCatMatching();
        $data = $this->getSelection();
        $preSelected = $this->getPreSelectedData($data);

        $conditions = CdiscountHelper::GetConditionTypes();
        $defaultCondition = $preSelected['ConditionType'];
        $defaultPreparationTime = $preSelected['PreparationTime'];
        $defaultShippingFeeStandard = $preSelected['ShippingFeeStandard'];
        $defaultShippingFeeExtraStandard = $preSelected['ShippingFeeExtraStandard'];
        $defaultShippingFeeTracked = $preSelected['ShippingFeeTracked'];
        $defaultShippingFeeExtraTracked = $preSelected['ShippingFeeExtraTracked'];
        $defaultShippingFeeRegistered = $preSelected['ShippingFeeRegistered'];
        $defaultShippingFeeExtraRegistered = $preSelected['ShippingFeeExtraRegistered'];
        $defaultShippingProfileName = $preSelected['ShippingProfileName'];
        $defaultShippingFee = $preSelected['ShippingFee'];
        $defaultShippingFeeAdditional = $preSelected['ShippingFeeAdditional'];



        $defaultMpCategory = isset($preSelected['PrimaryCategory']) ? $preSelected['PrimaryCategory'] : '0';
        $defaultComment = $preSelected['Comment'];

        $prepareView = (1 == count($data)) ? 'single' : 'multiple';

        $mpAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
        $mpOptionalAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
        $mpCustomAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

        $attributeMatchingTableHtml = '
			<tbody id="variationMatcher" class="attributesTable">
				<tr class="headline">
					<td colspan="3"><h4>' . str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERIC_MP_CATEGORY) . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_GENERIC_CATEGORIES_MARKETPLACE_CATEGORIE . '</th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<td>
									<div class="hoodCatVisual" id="PrimaryCategoryVisual">
										<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											' . $this->renderCategoryOptions('MarketplaceCategory', $defaultMpCategory) . '
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin mlbtn-action" type="button" value="' .
            ML_GENERIC_CATEGORIES_CHOOSE . '" id="selectPrimaryCategory" name="selectPrimaryCategory"/>
								</td>
							</tr>
						</tbody></table>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
				<tr class="headline">
					<td colspan="1"><h4>' . $mpAttributeTitle . '</h4></td>
					<td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB . '</h4></td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingInput" style="display:none;">
				<tr>
					<th></th>
					<td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
					<td class="info"></td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingOptionalHeadline" style="display:none;">
                <tr class="headline">
                    <td colspan="1"><h4>'.$mpOptionalAttributeTitle.'</h4></td>
                    <td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB . '</h4></td>
                </tr>
            </tbody>
            <tbody id="tbodyDynamicMatchingOptionalInput" style="display:none;">
                <tr>
                    <th></th>
                    <td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
                    <td class="info"></td>
                </tr>
            </tbody>
            <tbody id="tbodyDynamicMatchingCustomHeadline" style="display:none;">
                <tr class="headline">
                    <td colspan="1"><h4>'.$mpCustomAttributeTitle.'</h4></td>
                    <td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB . '</h4></td>
                </tr>
            </tbody>
            <tbody id="tbodyDynamicMatchingCustomInput" style="display:none;">
                <tr>
                    <th></th>
                    <td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
                    <td class="info"></td>
                </tr>
            </tbody>
			<tbody id="categoryInfo" style="display:none;">
				<tr class="spacer"><td colspan="3">' . ML_GENERAL_VARMATCH_CATEGORY_INFO . '</td></tr>
				<tr class="spacer"><td colspan="3">&nbsp;</td></tr>
			</tbody>';

        ob_start();
        ?>
        <script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS ?>js/marketplaces/cdiscount/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
        <script type="text/javascript">
            /*<![CDATA[*/
            var ml_vm_config = {
                url: '<?php echo toURL($this->resources['url'], array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
                viewName: 'prepareView',
                formName: '#prepareForm',
                handleCategoryChange: false,
                i18n: <?php echo json_encode(CdiscountHelper::gi()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode(CdiscountHelper::gi()->getShopVariations()); ?>
            };
            /*]]>*/

            $('#desc_1').click(function() {
                var d = $('#desc_1 span').html();
                $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
            });

            $('#desc_2').click(function() {
                var d = $('#desc_2 span').html();
                $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
            });
        </script>
        <?php
        $attributeMatchingTableHtml .= ob_get_contents();
        ob_end_clean();

        # multiple items: no pre-filling except default values

        $html = $attributeMatchingDialogHtml . '
			<form method="post" id="prepareForm" action="' . toURL($this->resources['url']) . '">
				<table class="attributesTable">' . $this->showProductDetails($data) . '
					' . $attributeMatchingTableHtml . '
					<tbody>
					<tr class="headline">
						<td colspan="3"><h4>' . ML_LABEL_GENERIC_SETTINGS . '</h4></td>
					</tr>';

        if ('single' === $prepareView) {
            $html .= '
				';
        }

        //Condition
        $html .= '<tr class="odd">
					<th>' . ML_GENERIC_CONDITION . '</th>
					<td class="input">
					<select name="condition_id" id="condition_id">';

        foreach ($conditions as $condID => $condName) {
            if ($condID == $defaultCondition) {
                $html .= '
					<option selected value="' . $condID . '">' . $condName . '</option>';
            } else {
                $html .= '
					<option value="' . $condID . '">' . $condName . '</option>';
            }
        }

        $html .= '
					</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>';

        // Preparation Time
        $html .= '
					<tr class="even">
						<th>' . ML_CDISCOUNT_LABEL_PREPARATION_TIME . '
						<div style="float: right; width: 16px; height: 16px; background: transparent url(\'' .
            DIR_MAGNALISTER_WS . 'images/information.png\') no-repeat 0 0;
							cursor: pointer; display: inline-block; vertical-align: top;" class="desc " id="desc_3" title="Infos">
							<span style="display: none">' . ML_CDISCOUNT_HELP_PREPARATION_TIME . '</span>
						</div>

						</th>
						<td class="input">
							<input type="text" name="PreparationTime" id="PreparationTime" value="' . $defaultPreparationTime . '"/>
						</td>
						<td class="info"></td>
					</tr>';

        $html .= '
            <tr class="">
                <th>Versandkosten</th>
                <td class="input">
                    '.$this->renderShippingCosts($defaultShippingProfileName, $defaultShippingFee, $defaultShippingFeeAdditional).'
                </td>
                <td class="info"></td>
            </tr>
        ';

        $html .= '
				<tr class="even">
					<th>' . ML_CDISCOUNT_COMMENT . '</th>
					<td class="input">
						<textarea name="comment">' . $defaultComment . '</textarea>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody></table>
			<script type="text/javascript">
				$(document).ready(function() {
					$(\'#desc_3\').click(function () {
						var d = $(\'#desc_3 span\').html();
						$(\'#infodiag\').html(d).jDialog({\'width\': (d.length > 1000) ? \'700px\' : \'500px\'});
					});
	
					$(\'#desc_4\').click(function () {
						var d = $(\'#desc_4 span\').html();
						$(\'#infodiag\').html(d).jDialog({\'width\': (d.length > 1000) ? \'700px\' : \'500px\'});
					});
					
					$(\'#desc_5\').click(function () {
						var d = $(\'#desc_5 span\').html();
						$(\'#infodiag\').html(d).jDialog({\'width\': (d.length > 1000) ? \'700px\' : \'500px\'});
					});
					
					$(\'#desc_6\').click(function () {
						var d = $(\'#desc_6 span\').html();
						$(\'#infodiag\').html(d).jDialog({\'width\': (d.length > 1000) ? \'700px\' : \'500px\'});
					});
				});
			</script>
			<table class="actions">
				<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
				<tbody>
					<tr><td>
						<table><tbody>
							<tr><td>
								<input type="submit" class="ml-button mlbtn-action" name="saveMatching" value="' .
            ML_BUTTON_LABEL_SAVE_DATA . '"/>
								<input type="hidden" name="saveMatching" value="true"/>
							</td></tr>
						</tbody></table>
					</td></tr>
				</tbody>
			</table>';

        $html .= '
			</form>';

        return $html;
    }

    protected function initCatMatching() {
        $this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
        $params = array();
        foreach (array('mpID', 'marketplace', 'marketplaceName', 'prepareSettings') as $attr) {
            if (isset($this->$attr)) {
                $params[$attr] = &$this->$attr;
            }
        }
    }

    protected function getSelection() {
        $sLanguageCode = getDBConfigValue($this->marketplace . '.lang', $this->mpID);
        $keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

        $dbOldSelectionQuery = '
			SELECT *
			FROM ' . TABLE_MAGNA_CDISCOUNT_PREPARE . ' dp
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

        $dbOldSelectionQuery .= '
		    WHERE selectionname = "apply"
				AND ms.mpID = "' . $this->mpID . '"
				AND session_id="' . session_id() . '"
				AND dp.products_id IS NOT NULL
				AND TRIM(dp.products_id) <> ""
				AND dp.PrepareType = "Apply"
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
					'.(MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION) ? 'pd.products_short_description' : '"" AS Subtitle').',
					pd.products_description as Description
			FROM ' . TABLE_PRODUCTS . ' p
			INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
			LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' .
            $sLanguageCode . '"
			WHERE ' . ($keytypeIsArtNr ? 'p.products_model' : 'p.products_id') . ' NOT IN ("' . implode('", "', $oldProducts) . '")
				AND ms.mpID = "' . $this->mpID . '"
				AND selectionname="apply"
				AND session_id="' . session_id() . '"
		';
        $dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);

        foreach ($dbNewSelection as &$productFromDB) {
            CdiscountHelper::setDescriptionAndMarketingDescription($productFromDB['products_id'], $productFromDB['Description'],
                $productFromDB['Description'], $productFromDB['MarketingDescription']);
        }

        $dbSelection = array_merge(is_array($dbOldSelection) ? $dbOldSelection : array(),
            is_array($dbNewSelection) ? $dbNewSelection : array());

        // For debug purpose
        if (false) {
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

    protected function getPreSelectedData($data) {
        // Check which values all prepared products have in common to preselect the values.
        $preSelected = array(
            'ConditionType' => null,
            'Comment' => null,
            'PictureUrl' => null,
            'PrimaryCategory' => null,
            'PreparationTime' => null,
            'ShippingFeeStandard' => null,
            'ShippingFeeExtraStandard' => null,
            'ShippingFeeTracked' => null,
            'ShippingFeeExtraTracked' => null,
            'ShippingFeeRegistered' => null,
            'ShippingFeeExtraRegistered' => null,
            'ShippingProfileName' => null,
            'ShippingFee' => null,
            'ShippingFeeAdditional' => null,
        );

        $defaults = array(
            'ConditionType' => getDBConfigValue($this->marketplace . '.itemcondition', $this->mpID),
            'Comment' => null,
            'PictureUrl' => null,
            'PrimaryCategory' => null,
            'PreparationTime' => getDBConfigValue($this->marketplace . '.preparationtime', $this->mpID),
            'ShippingFeeStandard' => getDBConfigValue($this->marketplace . '.shippingfeestandard', $this->mpID),
            'ShippingFeeExtraStandard' => getDBConfigValue($this->marketplace . '.shippingfeeextrastandard', $this->mpID),
            'ShippingFeeTracked' => getDBConfigValue($this->marketplace . '.shippingfeetracked', $this->mpID),
            'ShippingFeeExtraTracked' => getDBConfigValue($this->marketplace . '.shippingfeeextratracked', $this->mpID),
            'ShippingFeeRegistered' => getDBConfigValue($this->marketplace . '.shippingfeeregistered', $this->mpID),
            'ShippingFeeExtraRegistered' => getDBConfigValue($this->marketplace . '.shippingfeeextraregistered', $this->mpID),
            'ShippingProfileName' => getDBConfigValue($this->marketplace . '.shippingprofile.name', $this->mpID),
            'ShippingFee' => getDBConfigValue($this->marketplace . '.shippingprofile.fee', $this->mpID),
            'ShippingFeeAdditional' => getDBConfigValue($this->marketplace . '.shippingprofile.feeadditional', $this->mpID),

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
                $preSelected[$field] = isset($defaults[$field]) ? $defaults[$field] : null;
            }
        }

        return $preSelected;
    }

    /**
     * Fetches the options for the top 20 category selectors
     * @param string $sType
     *     Type of category (PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, StoreCategory3)
     * @param string $sCategory
     *     the selected category (empty for newly prepared items)
     * @returns string
     *     option tags for the select element
     */
    protected function renderCategoryOptions($sType, $sCategory) {
        if ($this->topTen === null) {
            $this->topTen = new CdiscountTopTenCategories();
            $this->topTen->setMarketPlaceId($this->mpID);
        }
        $opt = '<option value="">&mdash;</option>' . "\n";

        $aTopTenCatIds = $this->topTen->getTopTenCategories($sType, 'getMPCategoryPath');

        foreach ($aTopTenCatIds as $sKey => $sValue) {
            $blSelected = (!empty($sCategory) && ($sCategory == $sKey));
            $opt .= '<option value="' . $sKey . '"' . ($blSelected ? ' selected="selected"' : '') . '>' . $sValue . '</option>' .
                "\n";
        }

        return $opt;
    }

    protected function showProductDetails($data) {
        if (1 != count($data)) {
            return '';
        }
        $preSelected = $this->getPreSelectedData($data);
        $data = $data[0];
        $oddEven = false;
        $pictureUrls = array();
        $aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->marketplace . '.lang', $this->mpID))
            ->getProductById($data['products_id']);
        if (isset($data['PictureUrl']) && empty($data['PictureUrl']) === false) {
            $pictureUrls = json_decode($data['PictureUrl'], true);
        }

        if (empty($pictureUrls) || !is_array($pictureUrls)) {
            $pictureUrls = array();
            foreach ($aProduct['Images'] as $img) {
                $pictureUrls[$img] = 'true';
            }
        }

        foreach ($aProduct['Images'] as $img) {
            $img = fixHTMLUTF8Entities($img, ENT_COMPAT);
            $data['Images'][$img] = (isset($pictureUrls[$img]) && ($pictureUrls[$img] === 'true')) ? 'true' : 'false';
        }

        $this->price->setFinalPriceFromDB($data['products_id'], $this->mpID);
        $defaultPrice = $this->price->roundPrice()->getPrice();

        ob_start();
        ?>

        <tbody>
        <tr class="headline">
            <td colspan="3"><h4><?php echo ML_CDISCOUNT_PRODUCT_DETAILS ?></h4></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th><?php echo ML_CDISCOUNT_ITEM_NAME_TITLE ?></th>
            <td class="input">
                <input type="text" class="fullwidth" name="Title" id="Title"
                       maxlength="<?php echo CdiscountHelper::TITLE_MAX_LENGTH ?>"
                       value="<?php echo fixHTMLUTF8Entities(CdiscountHelper::cdiscountSanitizeTitle($data['Title']),
                           ENT_COMPAT) ?>"/>
            </td>
            <td class="info"><?php echo ML_CDISCOUNT_TITLE_VALIDATION ?></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th><?php echo ML_CDISCOUNT_SUBTITLE ?></th>
            <td class="input">
                <input type="text" class="fullwidth" name="Subtitle" id="Subtitle"
                       maxlength="<?php echo CdiscountHelper::SUBTITLE_MAX_LENGTH ?>"
                       value="<?php echo fixHTMLUTF8Entities(CdiscountHelper::cdiscountSanitizeSubtitle($data['Subtitle']),
                           ENT_COMPAT) ?>"/>
            </td>
            <td class="info"><?php echo ML_CDISCOUNT_SUBTITLE_VALIDATION ?></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th>
                <div style="float: left;"><?php echo ML_CDISCOUNT_DESCRIPTION ?></div>
                <div
                    style="float: right; width: 16px; height: 16px; background: transparent url('<?php echo DIR_MAGNALISTER_WS ?>images/information.png') no-repeat 0 0;
                        cursor: pointer; display: inline-block; vertical-align: top;" class="desc" id="desc_1" title="Infos">
                    <span style="display: none"><?php echo ML_CDISCOUNT_DESCRIPTION_HELP ?></span>
                </div>
            </th>
            <td class="input">
                <textarea class="fullwidth" name="Description" id="Description" rows="20"
                          cols="80"><?php echo fixHTMLUTF8Entities(CdiscountHelper::cdiscountSanitizeDesc($data['Description']),
                        ENT_COMPAT); ?></textarea>
            </td>
            <td class="info"><?php echo ML_CDISCOUNT_DESCRIPTION_VALIDATION ?></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th>
                <div style="float: left;"><?php echo ML_CDISCOUNT_MARKETING_DESCRIPTION ?></div>
                <div
                    style="float: right; width: 16px; height: 16px; background: transparent url('<?php echo DIR_MAGNALISTER_WS ?>images/information.png') no-repeat 0 0;
                        cursor: pointer; display: inline-block; vertical-align: top;" class="desc" id="desc_2" title="Infos">
                    <span style="display: none"><?php echo ML_CDISCOUNT_MARKETING_DESCRIPTION_HELP ?></span>
                </div>
            </th>
            <td class="input">
                <?php echo magna_wysiwyg(array(
                    'id' => 'MarketingDescription',
                    'name' => 'MarketingDescription',
                    'class' => 'fullwidth',
                    'cols' => '80',
                    'rows' => '20',
                    'wrap' => 'virtual',
                ), fixHTMLUTF8Entities(CdiscountHelper::truncateString($data['MarketingDescription'],
                    self::MARKETING_DESC_MAX_LENGTH), ENT_COMPAT)) ?>
            </td>
            <td class="info"><?php echo ML_CDISCOUNT_MARKETING_DESCRIPTION_VALIDATION ?></td>
        </tr>
        <tr class="even">
            <th><?php echo ML_GENERIC_PRICE ?></th>
            <td class="input">
                <table class="lightstlye line15">
                    <tbody>
                    <tr>
                        <td><?php echo ML_CDISCOUNT_LABEL_CDISCOUNT_PRICE ?>:</td>
                        <td>
                            <?php echo $defaultPrice . ' ' . ML_CDISCOUNT_CURRENCY ?>
                            <input type="hidden" value="'.$defaultPrice.'" name="Price" id="Price"/>
                        </td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td class="info"></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th><?php echo ML_LABEL_PRODUCTS_IMAGES ?></th>
            <td class="input">
                <input type="hidden" id="image_hidden" name="Images[]" value="false"/>
                <?php
                if (!empty($data['Images'])) :
                    foreach ($data['Images'] as $img => $checked) : ?>
                        <table class="imageBox">
                            <tbody>
                            <tr>
                                <td class="image"><label
                                        for="image_<?php echo $img ?>"><?php echo generateProductCategoryThumb($img, 60,
                                            60) ?></label></td>
                            </tr>
                            <tr>
                                <td class="cb"><input type="checkbox" id="image_<?php echo $img ?>"
                                                      name="Images[<?php echo urlencode($img) ?>]"
                                                      value="true" <?php echo $checked == 'true' ? 'checked="checked"' : '' ?> />
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <?php
                    endforeach;
                endif; ?>
            </td>
            <td class="info"><?php echo ML_CDISCOUNT_MAX_4_IMAGES ?></td>
        </tr>
        <tr class="spacer">
            <td colspan="3">&nbsp;</td>
        </tr>
        </tbody>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#desc_1').click(function() {
                    var d = $('#desc_1 span').html();
                    $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
                });

                $('#desc_2').click(function() {
                    var d = $('#desc_2 span').html();
                    $('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
                });
            });
        </script>

        <?php
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function renderAjax() {
        if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
            $this->initCatMatching();
            $this->oCategoryMatching = new CdiscountCategoryMatching();
            echo $this->oCategoryMatching->renderAjax();
        } else if ($_POST['prepare'] === 'prepare' || (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations'))) {
            if (isset($_POST['SelectValue'])) {
                $select = $_POST['SelectValue'];
            } else {
                $select = $_POST['PrimaryCategory'];
            }

            $productModel = CdiscountHelper::gi()->getProductModel('apply');

            return json_encode(CdiscountHelper::gi()->getMPVariations($select, $productModel, true));
        } else if (isset($_POST['Action']) && ($_POST['Action'] === 'DBMatchingColumns')) {
            $columns = MagnaDB::gi()->getTableCols($_POST['Table']);
            $editedColumns = array();
            foreach ($columns as $column) {
                $editedColumns[$column] = $column;
            }

            echo json_encode($editedColumns, JSON_FORCE_OBJECT);
        }
    }

    public function renderShippingCosts($var1, $var2, $var3) {
        $deliveryModes = CdiscountConfigure::getDeliveryModes();
        $array = array(
            'label' => 'Versandkosten',
            'key' => 'cdiscount.shippingprofile',
            'type' => 'duplicate',
            'skipRadio' => 'true',
            'subtype' => 'extern',
            'procFunc' => 'CdiscountConfigure::shippingProfile',
            'params' =>
                array('subfields' => array(
                    'method' => array(
                        'label' => 'Name des Versandprofils',
                        'key' => 'cdiscount.shippingprofile.name',
                        'name' => 'Name',
                        'type' => 'selection',
                        'values' => $deliveryModes,
                        'selectedValues' => $var1,
                        'cssClasses' => array('autoWidth')
                    ),
                    'fee' => array(
                        'label' => 'Versandgebühr (€)',
                        'key' => 'cdiscount.shippingprofile.fee',
                        'name' => 'Fee',
                        'type' => 'text',
                        'selectedValues' => $var2,
                        'cssClasses' => array('autoWidth')
                    ),
                    'feeadditional' => array(
                        'label' => 'Zusätzliche Versandgebühren (€)',
                        'key' => 'cdiscount.shippingprofile.feeadditional',
                        'name' => 'Fee2',
                        'type' => 'text',
                        'selectedValues' => $var3,
                        'cssClasses' => ('autoWidth')
                    ),
                ),
            ),
            'cssClasses' => array('orderConfig')
        );

        return $this->renderDuplicateField($array, 'cdiscount_shippingprofile', false);
    }

    protected function renderDuplicateField($item, $idKey, $blAjax = false) {
        global $magnaConfig;

        $config = &$magnaConfig['db'][$this->mpID];
        $idKey = str_replace('.', '_', $idKey);

        $html = '';
        ob_start();
        if ($blAjax) {
            $aValue = array('defaults' => array(''));
        } elseif (!isset($config[$item['key']]['defaults'])) {
            $aValue = array('defaults' => array('1'));
            if (isset($item['params']['subfields']['method']['selectedValues'])) {
                $aValue = array('defaults' => json_decode($item['params']['subfields']['method']['selectedValues']));
            }
        } else {
            if (!is_array($item['params']['subfields']['method']['selectedValues'])) {
                $aValue = array('defaults' => json_decode($item['params']['subfields']['method']['selectedValues']));
            } else {
                $aValue = $config[$item['key']];
            }
        }

        if (isset($item['params']['subfields']['method']['selectedValues']) && is_string($item['params']['subfields']['method']['selectedValues'])) {
            $item['params']['subfields']['method']['selectedValues'] = json_decode($item['params']['subfields']['method']['selectedValues']);
            $item['params']['subfields']['fee']['selectedValues'] = json_decode($item['params']['subfields']['fee']['selectedValues']);
            $item['params']['subfields']['feeadditional']['selectedValues'] = json_decode($item['params']['subfields']['feeadditional']['selectedValues']);
        }

        $cssClasses = !empty($item['cssClasses']) ? implode(' ', $item['cssClasses']) : '';
        ?>
    <div id="<?php echo $idKey ?>">
        <table class="<?php echo $idKey ?> nostyle nowrap valigntop <?php echo $cssClasses ?>" width="100%">
            <tbody>
            <?php
            if (isset($aValue['defaults'])) {
                for ($i = 0; $i < count($aValue['defaults']); $i++) { ?>
                    <tr class="row1 bottomDashed">
                        <td>
                            <?php
                            $field = $item;
                            $field['type'] = $item['subtype'];
                            if (isset($field['params'])) {
                                $field['params']['currentIndex'] = $i;
                            }

                            unset($field['subtype']);
                            $field['key'] = $item['key'].'][values][';
                            $value = null;
                            if (isset($aValue['values']) && isset($aValue['values'][$i])) {
                                $value = $aValue['values'][$i];
                            }

                            echo $this->renderInput($field, $value);
                            ?>
                        </td>
                        <td>
                            <input value="<?php echo $aValue['defaults'][$i]; ?>"
                                   name="<?php echo $item['key'].'[defaults][]' ?>" type="hidden"
                                   class="<?php echo $idKey ?>"/>
                            <input type="button" value="+" class="ml-button plus">
                            <input type="button" value="&#8211;" class="ml-button minus " <?php echo count($aValue['defaults']) == 1 ? 'disabled' : ''?>>
                        </td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>
        <?php if (!$blAjax) { ?>
        <script type="text/javascript">/*<![CDATA[*/
            $(document).ready(function () {
                $('#<?php echo $idKey; ?>').on('click', 'input.ml-button.plus', function () {
                    var $tableBox = $('#<?php echo $idKey; ?>');
                    if ($tableBox.parent('td').find('table').length == 1) {
                        $tableBox.find('input.ml-button.minus').fadeIn(0);
                        $tableBox.find('input.ml-button.minus').prop('disabled', true);
                    }

                    $tableBox.find('input.ml-button.minus').prop('disabled', false);

                    myConsole.log();
                    jQuery.blockUI(blockUILoading);
                    jQuery.ajax({
                        //mp=60868&mode=conf
                        type: 'POST',
                        url: '<?php echo toURL($this->url, array('mp' => $this->mpID,'mode' => 'prepare','kind' => 'ajax'), true); ?>',
                        data: <?php echo json_encode(array_merge(
                            array(
                                'item' => $item,
                                'action' => 'duplicate',
                                'kind' => 'ajax',
                            )
                        )); ?>,
                        success: function (data) {
                            jQuery.unblockUI();
                            $tableBox.append(data);
                        },
                        error: function () {
                            jQuery.unblockUI();
                        },
                        dataType: 'html'
                    });
                });
                $('#<?php echo $idKey; ?>').on('click', 'input.ml-button.minus', function () {
                    $(this).closest('tr').remove();
                    var hiddenInput = $(this).parent().find('input:hidden:first').attr('class');
                    var length = $('input.'+hiddenInput).length
                    if (length <= 1) {
                        $('input.'+hiddenInput).parent().find('input.ml-button.minus').prop('disabled', true).fadeIn(0)
                    } else {
                        $('input.'+hiddenInput).parent().find('input.ml-button.minus').prop('disabled', false).fadeIn(1)
                    }

                });
            });
            /*]]>*/</script></div><?php
        }

        $html .= ob_get_clean();

        return $html;
    }

    private function renderInput($item, $value = null) {
        # echo print_m($item);
        global $magnaConfig;
        $config = &$magnaConfig['db'][$this->mpID];

        if (!isset($item['key'])) {
            $item['key'] = '';
        }
        if($value === null){
            $value = '';
            if (array_key_exists($item['key'], $config)) {
                $value = $config[$item['key']];
                if (is_array($value) && isset($item['default']) && is_array($item['default'])) {
                    //echo print_m($item['default'], 'default'); echo print_m($value, 'config');
                    //var_dump(isNumericArray($item['default']), isNumericArray($value));
                    if (isNumericArray($item['default']) && isNumericArray($value)) {
                        foreach ($item['default'] as $k => $v) {
                            if (array_key_exists($k, $value)) continue;
                            $value[$k] = $item['default'][$k];
                        }
                    } else {
                        $value = array_merge($item['default'], $value);
                    }
                }
            } else if (isset($item['default'])) {
                $value = $item['default'];
            }
        }

        $item['__value'] = $value;

        $idkey = str_replace('.', '_', $item['key']);

        $parameters = '';
        if (isset($item['parameters'])) {
            foreach ($item['parameters'] as $key => $val) {
                $parameters .= ' '.$key.'="'.$val.'"';
            }
        }
        if (array_key_exists('ajaxlinkto', $item)) {
            $item['ajaxlinkto']['from'] = $item['key'];
            $item['ajaxlinkto']['fromid'] = 'config_'.$idkey;
            if (array_key_exists('key', $item['ajaxlinkto'])) {
                $item['ajaxlinkto']['toid'] = 'config_'.str_replace('.', '_', $item['ajaxlinkto']['key']);
                $ajaxUpdateFuncs[] = $item['ajaxlinkto'];
            } else { # mehrere ajaxlinkto eintraege
                foreach ($item['ajaxlinkto'] as $aLiTo) {
                    if (!is_array($aLiTo) || !array_key_exists('key', $aLiTo)) continue;
                    $aLiTo['toid'] = 'config_'.str_replace('.', '_', $aLiTo['key']);
                    $ajaxUpdateFuncs[] = $aLiTo;
                }
            }
        }

        if (!isset($item['cssClasses'])) {
            $item['cssClasses'] = array();
        }

        if (isset($item['cssStyles']) && is_array($item['cssStyles'])) {
            $style = ' style="'.implode(';', $item['cssStyles']).'" ';
        } else {
            $style = '';
        }

        $html = '';
        if(!isset($item['type'])){
            return $html;
        }
        switch ($item['type']) {
            case 'extern': {
                if (!is_callable($item['procFunc'])) {
                    if (is_array($item['procFunc'])) {
                        $item['procFunc'] = get_class($item['procFunc'][0]).'->'.$item['procFunc'][1];
                    }
                    $html .= 'Function <span class="tt">\''.$item['procFunc'].'\'</span> does not exists.';
                    break;
                }
                $html .= call_user_func($item['procFunc'], array_merge($item['params'], array('key' => $item['key'])));
                break;
            }
        }
        return $html;
    }

}
