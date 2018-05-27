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

require_once(DIR_MAGNALISTER_MODULES.'tradoria/prepare/TradoriaCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'tradoria/classes/TradoriaTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES.'tradoria/TradoriaHelper.php');

class TradoriaApplyPrepareView extends MagnaCompatibleBase
{
    protected $prepareSettings = array();
    protected $price = null;

    protected $catMatch = null;
    protected $topTen = null;

    /** @var $oCategoryMatching TradoriaCategoryMatching */
    protected $oCategoryMatching = null;

    protected function initCatMatching() {
        $params = array();
        foreach (array('mpID', 'marketplace', 'marketplaceName') as $attr) {
            if (isset($this->$attr)) {
                $params[$attr] = &$this->$attr;
            }
        }
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
            $this->topTen = new TradoriaTopTenCategories();
            $this->topTen->setMarketPlaceId($this->mpID);
        }

        $aTopTenCatIds = $this->topTen->getTopTenCategories($sType, 'getMPCategoryPath');
        if (!empty($aTopTenCatIds)) {
            $opt = '<option value="">&mdash;</option>'."\n";
        } else {
            $opt = '<option value=""> -- '.ML_GENERIC_USE_CATEGORY_BUTTON.' -- &gt; </option>'."\n";
        }

        if (!empty($sCategory) && !array_key_exists($sCategory, $aTopTenCatIds)) {
            $sCategoryName = $this->oCategoryMatching->getMPCategoryPath($sCategory);
            $opt .= '<option value="' . $sCategory . '" selected="selected">' . $sCategoryName . '</option>' . "\n";
        }

        foreach ($aTopTenCatIds as $sKey => $sValue) {
            $blSelected = (!empty($sCategory) && ($sCategory == $sKey));
            $opt .= '<option value="' . $sKey . '"' . ($blSelected ? ' selected="selected"' : '') . '>' . $sValue . '</option>' . "\n";
        }

        return $opt;
    }

    public function renderAjax() {
        if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
            $this->initCatMatching();
            $this->oCategoryMatching = new TradoriaCategoryMatching();
            echo $this->oCategoryMatching->renderAjax();
        } else if ($_POST['prepare'] === 'prepare' || (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations'))) {
            if (isset($_POST['SelectValue'])) {
                $select = $_POST['SelectValue'];
            } else {
                $select = $_POST['PrimaryCategory'];
            }

            $productModel = TradoriaHelper::gi()->getProductModel('apply');

            return json_encode(TradoriaHelper::gi()->getMPVariations($select, $productModel, true));
        } else if (isset($_POST['Action']) && ($_POST['Action'] === 'DBMatchingColumns')) {
            $columns = MagnaDB::gi()->getTableCols($_POST['Table']);
            $editedColumns = array();
            foreach ($columns as $column) {
                $editedColumns[$column] = $column;
            }

            echo json_encode($editedColumns, JSON_FORCE_OBJECT);
        }
    }

    protected function getPreSelectedData($data) {
        // Check which values all prepared products have in common to preselect the values.
        $preSelected = array(
            'MarketplaceCategoriesName' => null,
        );

        $defaults = array(
            'MarketplaceCategoriesName' => null,
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

    protected function getSelection() {
        $sLanguageId = getDBConfigValue($this->marketplace . '.lang', $this->mpID);
        $keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

        $dbOldSelectionQuery = '
			SELECT *
			FROM ' . TABLE_MAGNA_TRADORIA_PREPARE . ' dp
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
			LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' . $sLanguageId . '"
			WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("' . implode('", "', $oldProducts) . '")
				AND ms.mpID = "' . $this->mpID . '"
				AND selectionname="apply"
				AND session_id="' . session_id() . '"
		';
        $dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);

        $dbSelection = array_merge(
            is_array($dbOldSelection) ? $dbOldSelection : array(),
            is_array($dbNewSelection) ? $dbNewSelection : array()
        );

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

    public function process()
    {
        $oddEven = false;
        $this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
        $this->oCategoryMatching = new TradoriaCategoryMatching();
        $attributeMatchingDialogHtml = $this->oCategoryMatching->renderMatching();

        $this->initCatMatching();
        $data = $this->getSelection();
        $preSelected = $this->getPreSelectedData($data);

        $defaultMpCategory = '0';

        $prepareView = (1 == count($data)) ? 'single' : 'multiple';
        if ('single' == $prepareView) {
            $defaultMpCategory = $preSelected['MarketplaceCategoriesName'];
        }

        $marketplaceName = 'Rakuten';

        $mpAttributeTitle = str_replace('%marketplace%', ucfirst($marketplaceName), ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
        $mpOptionalAttributeTitle = str_replace('%marketplace%', ucfirst($marketplaceName), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
        $mpCustomAttributeTitle = str_replace('%marketplace%', ucfirst($marketplaceName), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

        $attributeMatchingTableHtml = '
			<tbody id="variationMatcher" class="attributesTable">
				<tr class="headline">
					<td colspan="3"><h4>' . str_replace('%marketplace%',  ucfirst($marketplaceName), ML_GENERIC_MP_CATEGORY) . '</h4></td>
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
									<input class="fullWidth ml-button smallmargin mlbtn-action" type="button" value="' . ML_GENERIC_CATEGORIES_CHOOSE . '" id="selectPrimaryCategory"/>
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
					<td colspan="1"><h4>' . $mpOptionalAttributeTitle . '</h4></td>
					<td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB . '</h4></td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingOptionalInput" style="display:none;">
				<tr>
					<th></th>
					<td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
					<td class="info"></td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingCustomHeadline" style="display:none;">
				<tr class="headline">
					<td colspan="1"><h4>' . $mpCustomAttributeTitle . '</h4></td>
					<td colspan="2"><h4>' . ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB . '</h4></td>
				</tr>
			</tbody>
			<tbody id="tbodyDynamicMatchingCustomInput" style="display:none;">
				<tr>
					<th></th>
					<td class="input">' . ML_GENERAL_VARMATCH_SELECT_CATEGORY . '</td>
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
                src="<?php echo DIR_MAGNALISTER_WS ?>js/marketplaces/tradoria/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
        <script type="text/javascript">
            /*<![CDATA[*/
            var ml_vm_config = {
                url: '<?php echo toURL($this->resources['url'], array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
                viewName: 'prepareView',
                formName: '#prepareForm',
                handleCategoryChange: false,
                i18n: <?php echo json_encode(TradoriaHelper::gi()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode(TradoriaHelper::gi()->getShopVariations()); ?>
            };
            /*]]>*/
        </script>
        <?php
        $attributeMatchingTableHtml .= ob_get_contents();
        ob_end_clean();

        $html = $attributeMatchingDialogHtml . '
			<form method="post" id="prepareForm" action="' . toURL($this->resources['url']) . '">
				<table class="attributesTable">
					' . $attributeMatchingTableHtml . '
				</table>
			<table class="actions">
				<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
				<tbody>
					<tr><td>
						<table><tbody>
							<tr><td>
								<input type="submit" class="ml-button mlbtn-action" name="saveMatching" value="' . ML_BUTTON_LABEL_SAVE_DATA . '"/>
							</td></tr>
						</tbody></table>
					</td></tr>
				</tbody>
			</table>';

        $html .= '
			</form>';

        return $html;
    }
}
