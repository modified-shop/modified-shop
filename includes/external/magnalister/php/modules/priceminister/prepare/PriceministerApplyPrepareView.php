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

require_once(DIR_MAGNALISTER_MODULES . 'priceminister/prepare/PriceministerCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'priceminister/classes/PriceministerTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES . 'priceminister/PriceministerHelper.php');

class PriceministerApplyPrepareView extends MagnaCompatibleBase {

    protected $prepareSettings = array();
    protected $price = null;

    protected $catMatch = null;
    protected $topTen = null;

    /** @var $oCategoryMatching PriceministerCategoryMatching */
    protected $oCategoryMatching = null;

    public function process() {
        $oddEven = false;
        $this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
        $this->oCategoryMatching = new PriceministerCategoryMatching();
        $attributeMatchingDialogHtml = $this->oCategoryMatching->renderMatching();

        $this->initCatMatching();
        $data = $this->getSelection();
        $preSelected = $this->getPreSelectedData($data);

        $conditions = PriceministerHelper::GetConditionTypes();
        $defaultCondition = $preSelected['ConditionType'];
        $defaultMpCategory = is_array($preSelected['MarketplaceCategories']) ? '0' : $preSelected['MarketplaceCategories'];

        $mpAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
        $mpOptionalAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
        $mpCustomAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

        $attributeMatchingTableHtml = '
			<tbody id="variationMatcher" class="attributesTable">
				<tr class="headline">
					<td colspan="3"><h4>' . str_replace('%marketplace%', 'PriceMinister', ML_GENERIC_MP_CATEGORY) . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_GENERIC_CATEGORIES_MARKETPLACE_CATEGORIE . '</th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<td>
									<div class="hoodCatVisual" id="PrimaryCategoryVisual">
										<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											' . $this->renderCategoryOptions('MarketplaceCategories', $defaultMpCategory) . '
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin mlbtn-action" type="button" value="' .
            ML_GENERIC_CATEGORIES_CHOOSE . '" id="selectPrimaryCategory"/>
								</td>
							</tr>
						</tbody></table>
					</td>
					<td class="info"></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
            </tbody>
            <tbody id="tbodySubcategoriesHeadline" class="attributesTable" style="display:none">
                <tr class="headline">
                    <td colspan="3"><h4>' . ML_PRICEMINISTER_SUBCATEGORIES . '</h4></td>
                </tr>                
            </tbody>
            <tbody id="tbodySubcategoriesInput" class="attributesTable" style="display:none">                              
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
        <script type="text/javascript" src="includes/magnalister/js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
        <script type="text/javascript" src="includes/magnalister/js/marketplaces/priceminister/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
        <script type="text/javascript">
            /*<![CDATA[*/
            var ml_vm_config = {
                url: '<?php echo toURL($this->resources['url'], array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
                viewName: 'prepareView',
                formName: '#prepareForm',
                handleCategoryChange: false,
                i18n: <?php echo json_encode(PriceministerHelper::gi()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode(PriceministerHelper::gi()->getShopVariations()); ?>
            };
            /*]]>*/
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
					</tr>
					<tr class="odd">
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

        $html .= '
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody></table>
			<table class="actions">
				<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
				<tbody>
					<tr><td>
						<table><tbody>
							<tr><td>
								<input type="submit" class="ml-button mlbtn-action" name="saveMatching" value="' .
            ML_BUTTON_LABEL_SAVE_DATA . '"/>
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
        $params = array();
        foreach (array('mpID', 'marketplace', 'marketplaceName') as $attr) {
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
			FROM ' . TABLE_MAGNA_PRICEMINISTER_PREPARE . ' dp
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
					p.products_ean as EAN,
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
        if (!empty($dbNewSelection)) {
            PriceministerHelper::getTitleAndDescription($dbNewSelection, $this->mpID);
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
            'ShippingTime' => null,
            'Location' => null,
            'Comment' => null,
            'PictureUrl' => null,
            'MarketplaceCategories' => null,
        );

        $defaults = array(
            'ConditionType' => getDBConfigValue($this->marketplace . '.itemcondition', $this->mpID),
            'ShippingTime' => getDBConfigValue($this->marketplace . '.shippingtime', $this->mpID),
            'Location' => getDBConfigValue($this->marketplace . '.itemcountry', $this->mpID),
            'Comment' => null,
            'PictureUrl' => null,
            'MarketplaceCategories' => null,
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
            $this->topTen = new PriceministerTopTenCategories();
            $this->topTen->setMarketPlaceId($this->mpID);
        }
        $opt = '<option value="">&mdash;</option>' . "\n";

        $aTopTenCatIds = $this->topTen->getTopTenCategories($sType, 'getMPCategoryPath');

        if (!empty($sCategory) && !array_key_exists($sCategory, $aTopTenCatIds)) {
            $sCategoryName = $this->oCategoryMatching->getMPCategoryPath($sCategory);
            $opt .= '<option value="' . $sCategory . '" selected="selected">' . $sCategoryName . '</option>' . "\n";
        }

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
            <td colspan="3"><h4><?php echo ML_PRICEMINISTER_PRODUCT_DETAILS ?></h4></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th><?php echo ML_PRICEMINISTER_ITEM_NAME_TITLE ?></th>
            <td class="input">
                <input type="text" class="fullwidth" name="Title" id="Title"
                       maxlength="<?php echo PriceministerHelper::$TITLE_MAX_LENGTH ?>"
                       value="<?php echo fixHTMLUTF8Entities(PriceministerHelper::sanitizeTitle($data['Title'],
                           PriceministerHelper::$TITLE_MAX_LENGTH), ENT_COMPAT) ?>">
            </td>
            <td class="info"><?php echo ML_PRICEMINISTER_TITLE_MAXLENGTH ?></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th><?php echo ML_PRICEMINISTER_DESCRIPTION ?></th>
            <td class="input">
                <?php echo magna_wysiwyg(array(
                    'id' => 'Description',
                    'name' => 'Description',
                    'class' => 'fullwidth',
                    'cols' => '80',
                    'rows' => '20',
                    'wrap' => 'virtual',
                    'maxlength' => PriceministerHelper::$DESC_MAX_LENGTH,
                ), fixHTMLUTF8Entities(PriceministerHelper::truncateString($data['Description'],
                        PriceministerHelper::$DESC_MAX_LENGTH), ENT_COMPAT)); ?>
            </td>
            <td class="info">
                <?php echo ML_PRICEMINISTER_DESCRIPTION_MAXLENGTH ?>
                <br>
                <?php echo ML_EBAY_PRODUCTS_DESCRIPTION ?>
                <br>
                <?php echo ML_EBAY_PLACEHOLDERS ?> :
                <dl>
                    <dt style="font-weight:bold; color:black">#TITLE#</dt>
                    <dd><?php echo ML_EBAY_ITEM_NAME_TITLE ?></dd>
                    <dt style="font-weight:bold; color:black">#ARTNR#</dt>
                    <dd><?php echo ML_EBAY_ARTNO ?></dd>
                    <dt style="font-weight:bold; color:black">#PID#</dt>
                    <dd><?php echo ML_EBAY_PRODUCTS_ID ?></dd>
                    ';
                    <dt style="font-weight:bold; color:black">#SHORTDESCRIPTION#</dt>
                    <dd><?php echo ML_EBAY_SHORTDESCRIPTION_FROM_SHOP ?></dd>
                    <dt style="font-weight:bold; color:black">#DESCRIPTION#</dt>
                    <dd><?php echo ML_EBAY_DESCRIPTION_FROM_SHOP ?></dd>
                    <dt style="font-weight:bold; color:black">#PICTURE1#</dt>
                    <dd><?php echo ML_EBAY_FIRST_PIC ?></dd>
                    <dt style="font-weight:bold; color:black">#PICTURE2# etc.</dt>
                    <dd><?php echo ML_EBAY_MORE_PICS ?></dd>
                </dl>
            </td>
        </tr>
        <tr class="even">
            <th><?php echo ML_PRICEMINISTER_PRICE ?></th>
            <td class="input">
                <table class="lightstlye line15">
                    <tbody>
                    <tr>
                        <td><?php echo ML_PRICEMINISTER_LABEL_PRICEMINISTER_PRICE ?>:</td>
                        <td>
                            <?php echo $defaultPrice . ' ' . ML_PRICEMINISTER_CURRENCY ?>
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
                <?php if (!empty($data['Images'])) { ?>
                <?php foreach ($data['Images'] as $img => $checked) : ?>
                    <table class="imageBox">
                        <tbody>
                        <tr>
                            <td class="image"><label for="image_<?php echo $img ?>"><?php echo generateProductCategoryThumb($img,
                                        60, 60) ?></label></td>
                        </tr>
                        <tr>
                            <td class="cb"><input type="checkbox" id="image_<?php echo $img ?>"
                                                  name="Images[<?php echo urlencode($img) ?>]" value="true" <?php echo $checked ==
                                'true' ? 'checked="checked"' : '' ?> /></td>
                        </tr>
                        </tbody>
                    </table>
                <?php endforeach; ?>
                <?php } ?>
            </td>
            <td class="info"><?php echo ML_PRICEMINISTER_PICTURE_SIZE ?></td>
        </tr>
        <tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
            <th><?php echo ML_PRICEMINISTER_EAN ?></th>
            <td class="input">
                <input type="text" class="fullwidth" name="EAN" id="EAN"
                       value="<?php echo fixHTMLUTF8Entities($data['EAN'], ENT_COMPAT) ?>">
            </td>
            <td class="info"></td>
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

    public function renderAjax() {
        if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
            $this->initCatMatching();
            $this->oCategoryMatching = new PriceministerCategoryMatching();
            echo $this->oCategoryMatching->renderAjax();
        } else if ($_POST['prepare'] === 'prepare' || (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations'))) {
            if (isset($_POST['SelectValue'])) {
                $select = $_POST['SelectValue'];
            } else {
                $select = $_POST['PrimaryCategory'];
            }

            $productModel = PriceministerHelper::gi()->getProductModel('apply');

            $aMarketplaceVariations = PriceministerHelper::gi()->getMPVariations($select, $productModel, true);
            $aNotice = array('notice' => $_POST['notice']);
            $aFinal = array_merge($aMarketplaceVariations, $aNotice);
            return json_encode($aFinal);
        } else if (isset($_POST['Action']) && ($_POST['Action'] === 'DBMatchingColumns')) {
            $columns = MagnaDB::gi()->getTableCols($_POST['Table']);
            $editedColumns = array();
            foreach ($columns as $column) {
                $editedColumns[$column] = $column;
            }

            echo json_encode($editedColumns, JSON_FORCE_OBJECT);
        }
    }
}
