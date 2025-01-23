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

require_once(DIR_MAGNALISTER_MODULES.'metro/MetroHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/classes/MetroApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/classes/MetroTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES.'metro/prepare/MetroCategoryMatching.php');

class MetroPrepareView extends MagnaCompatibleBase {
    const METRO_MAX_IMAGES = 10; # maximal image count allowed on Metro
    protected $catMatch = null;
    protected $topTen = null;
    protected $businessSeller = false;

    public function __construct(&$params) {
        parent::__construct($params);
    }

    public function process() {
        $this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
        $ycm = new MetroCategoryMatching('view');
        return $this->renderPrepareView($this->getSelection()).$ycm->render();
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

    protected function renderAttributesJS() {
        global $_url;
        ob_start();
// TODO schauen wo man es hinschiebt damit es nicht die Seite zerschiesst
        ?>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS; ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS; ?>js/marketplaces/metro/variation_matching.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <script type="text/javascript">
            /*<![CDATA[*/
            var ml_vm_config = {
                url: '<?php echo toURL($_url, array('where' => 'MetroPrepareView', 'kind' => 'ajax'), true);?>',
                viewName: 'MetroPrepareView',
                secondaryCategory: false,
                formName: '#prepareForm',
                handleCategoryChange: false,
                i18n: <?php echo json_encode(MetroHelper::gi()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode(MetroHelper::gi()->getShopVariations()); ?>
            };
            /*]]>*/</script><?php
        $sAttrMatchJS = ob_get_contents();
        ob_end_clean();
        return $sAttrMatchJS;
    }

    /**
     * @param $data    enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
     */
    protected function renderSinglePrepareView($data) {
        $productImagesHTML = '';
        if (!empty($data['GalleryPictures']['Images'])) {
            $maxImages = (int)self::METRO_MAX_IMAGES;

            foreach ($data['GalleryPictures']['Images'] as $img => $checked) {
                if ((int)$maxImages <= 0) {
                    $checked = false;
                }
                $productImagesHTML .= '
					<table class="imageBox"><tbody>
						<tr><td class="image"><label for="image_'.$img.'">'.generateProductCategoryThumb($img, 60, 60).'</label></td></tr>
						<tr><td class="cb">
							<input type="hidden" name="GalleryPictures[Images]['.$img.']" value="false"/>
							<input type="checkbox" id="image_'.$img.'" name="GalleryPictures[Images]['.$img.']" 
							       value="true" '.($checked ? 'checked="checked"' : '').'/>
						</td></tr>
					</tbody></table>';
                if ($checked && ($maxImages !== true)) {
                    --$maxImages;
                }
            }
            #$productImagesHTML .= '<br style="clear:both">'.ML_HOOD_PICTURE_PATH.': <input class="fullwidth" type="text" name="GalleryPictures[BaseUrl]" value="'.htmlspecialchars($data['GalleryPictures']['BaseUrl']).'">';
            $productImagesHTML .= '<input type="hidden" name="GalleryPictures[BaseUrl]" value="'.htmlspecialchars($data['GalleryPictures']['BaseUrl']).'">';
        }
        if (empty($productImagesHTML)) {
            $productImagesHTML = '&mdash;';
        }

        $features = '';
        for ($i = 0; $i < 5; $i++) {
            $features .= '<input class="fullwidth" type="text" maxlength="100" value="'.fixHTMLUTF8Entities(isset($data['Feature'][$i]) ? $data['Feature'][$i] : '',
                    ENT_COMPAT).'" name="Feature['.$i.']" id="Feature"/>';
        }

        $oddEven = false;
        $html = '
            <style>
                input.fullwidth, textarea.fullwidth {
                    margin: 2px 0;
                }
            </style>
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>'.ML_METRO_PRODUCT_DETAILS.'</h4></td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_PRODUCT_TITLE.'<span class="bull">&bull;</span></th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="150" value="'.fixHTMLUTF8Entities($data['Title'],
                ENT_COMPAT).'" name="Title" id="Title"/>
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_TITLE_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_SHORTDESCRIPTION.'</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="150" value="'.fixHTMLUTF8Entities($data['ShortDescription'],
                ENT_COMPAT).'" name="ShortDescription" id="ShortDescription"/>
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_SHORTDESCRIPTION_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_GENERIC_ITEM_DESCRIPTION.'<span class="bull">&bull;</span></th>
					<td class="input">
                        <textarea class="fullwidth" name="Description" id="Description" rows="40" cols="100">'.fixHTMLUTF8Entities($data['Description'],
                ENT_COMPAT).'</textarea>
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_DESCRIPTION_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_PRODUCT_IMAGES.'<span class="bull">&bull;</span></th>
					<td class="input">
						'.$productImagesHTML.'
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_IMAGES_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_PRODUCT_GTIN.'</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="14" value="'.fixHTMLUTF8Entities($data['GTIN'],
                ENT_COMPAT).'" name="GTIN" id="GTIN"/>
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_GTIN_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_PRODUCT_MANUFACTURER.'</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="100" value="'.fixHTMLUTF8Entities($data['Manufacturer'],
                ENT_COMPAT).'" name="Manufacturer" id="Manufacturer"/>
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_MANUFACTURER_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_PRODUCT_MPN.'</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="100" value="'.fixHTMLUTF8Entities($data['ManufacturerPartNumber'],
                ENT_COMPAT).'" name="ManufacturerPartNumber" id="ManufacturerPartNUmber"/>
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_MPN_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_PRODUCT_BRAND.'</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="100" value="'.fixHTMLUTF8Entities($data['Brand'],
                ENT_COMPAT).'" name="Brand" id="Brand"/>
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_BRAND_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_PRODUCT_KEYFEATURE.'</th>
					<td class="input"> '.$features.'
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_KEY_FEATURE_INFO.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_PRODUCT_MSRP.'</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="100" value="'.fixHTMLUTF8Entities($data['MSRP'],
                ENT_COMPAT).'" name="MSRP" id="MSRP"/>
					</td>
					<td class="info">'.ML_METRO_PREPARE_PRODUCT_MSRP_INFO.'</td>
				</tr>
				';

        ob_start();
        ?>
        </tbody>
        <?php echo $this->renderMultiPrepareView(array($data)); ?>
        <?php
        $html .= ob_get_clean();
        return $html;
    }

    /**
     * @param $data    enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
     */
    protected function renderMultiPrepareView($data) {
        // Check which values all prepared products have in common to preselect the values.
        $preSelected = array(
            'PrimaryCategory' => array(),
        );

        $loadedPIds = array();
        foreach ($data as $row) {
            $loadedPIds[] = $row['products_id'];
            foreach ($preSelected as $field => $collection) {
                if (isset($row[$field])) {
                    $preSelected[$field][] = $row[$field];
                }
            }
        }
        foreach ($preSelected as $field => $collection) {
            $collection = array_unique($collection);
            if (count($collection) == 1) {
                $preSelected[$field] = array_shift($collection);
            } else {
                $preSelected[$field] = null;
            }
        }


        // prepare the categories
        $categoryMatcher = new MetroCategoryMatching();
        foreach (array('PrimaryCategory') as $kat) {
            if (($preSelected[$kat] === null)) {
                $preSelected[$kat] = '';
                $preSelected[$kat.'Name'] = '';
            } else {
                $preSelected[$kat.'Name'] = $categoryMatcher->getMetroCategoryPath($preSelected[$kat]);
            }
        }


        /*
         * Feldbezeichner | Eingabefeld | Beschreibung
         */
        $oddEven = false;
        $html = '
			<tbody>
				<tr class="spacer">
					<td colspan="3">
							&nbsp;<input type="hidden" value="'.$data[0]['products_id'].'" name="pID" id="pID"/>
					</td>
				</tr>
				<tr class="headline">
					<td colspan="3"><h4>'.ML_LABEL_CATEGORY.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_LABEL_SELECT_CATEGORY.'<span class="bull">&bull;</span></th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<!--td class="label"></td-->
								<td>
									<div class="catVisual" id="PrimaryCategoryVisual">
										<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											'.$this->renderCategoryOptions('PrimaryCategory',
                $preSelected['PrimaryCategory'], $preSelected['PrimaryCategoryName']).'
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin mlbtn-action" type="button" value="'.ML_HOOD_CHOOSE.'" id="selectPrimaryCategory"/>
								</td>
							</tr>
						</tbody></table>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
			<tbody id="attr_1" style="display:none">
			</tbody>';
        $html .= $this->renderAttributesTable();

        if (   isset($data[0]['ShippingProfile'])
            || '0' === $data[0]['ShippingProfile']) {
            $aShippingProfiles = MetroHelper::gi()->getShippingProfilesHtml($data[0]['ShippingProfile']);
        } else {
            $aShippingProfiles = MetroHelper::gi()->getShippingProfilesHtml();
        }
        if (   isset($data[0]['ShippingGroup'])
            || '0' === $data[0]['ShippingGroup']) {
            $aShippingGroups = MetroHelper::gi()->getShippingGroupsHtml($data[0]['ShippingGroup']);
        } else {
            $aShippingGroups = MetroHelper::gi()->getShippingGroupsHtml();
        }
        $html .= '
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>'.ML_LABEL_GENERIC_SETTINGS.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_LABEL_PROCESSINGTIME.'</th>
					<td class="input">';
        $html .= $this->renderProcessingTime($data);
        $html .= '</td>
				  <td class="info">'.ML_METRO_LABEL_PROCESSINGTIME_HELP.'</td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_LABEL_MAXPROCESSINGTIME.'</th>
					<td class="input">';
        $html .= $this->renderProcessingTime($data, true);
        $html .= '</td>
				  <td class="info">'.ML_METRO_LABEL_MAXPROCESSINGTIME_HELP.'</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_LABEL_BUSINESSMODEL.'</th>
					<td class="input">';
        $html .= $this->renderBusinessModel($data);
        $html .= '</td>
					<td class="info">&nbsp;</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_LABEL_FREIGHTFORWARDING.'</th>
					<td class="input">';
        $html .= $this->renderFreightForwarding($data);
        $html .= ' </td>
					<td class="info">Geben Sie an, ob Ihr Produkt per Spedition versendet wird.</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_METRO_LABEL_SHIPPINGPROFILE.'</th>
					<td class="input">
						<select name="ShippingProfile" style="width:100%">';
        $html .= $aShippingProfiles;

        $html .= '
						</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_AMAZON_SHIPPING_TEMPLATE.'</th>
					<td class="input">
						<select name="ShippingGroup" style="width:100%">';
        $html .= $aShippingGroups;

        $html .= '
						</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				
				<tr class="spacer"><td colspan="3">&nbsp;</td></tr>
				<tr class="spacer"><td colspan="3">'.ML_AMAZON_TEXT_APPLY_REQUIERD_FIELDS.'</td></tr>
				<tr class="spacer"><td colspan="3">&nbsp;</td></tr>
				</tbody>
		';

        ob_start();
        ?>
        <script type="text/javascript">/*<![CDATA[*/
            $(document).ajaxStart(function () {
                myConsole.log('ajaxStart');
                jQuery.blockUI(blockUILoading);
            }).ajaxStop(function () {
                myConsole.log('ajaxStop');
                jQuery.unblockUI();
            });
            // Start blockui right now because the ajaxStart event gets registered to late.
            // jQuery.blockUI(blockUILoading);

            $(document).ready(function () {
                $('#PrimaryCategoryVisual > select').change(function () {
                    var cID = this.value;
                    if (cID != '') {
                        generateMetroCategoryPath(cID, $('#PrimaryCategoryVisual'));
                        return true;
                    } else {
                        $('#attr_1').css({'display': 'none'});
                    }
                });
                $('#PrimaryCategoryVisual > select').trigger('change');

                $('#selectPrimaryCategory').click(function () {
                    startCategorySelector(function (cID) {
                        $('#PrimaryCategory').val(cID);
                        generateMetroCategoryPath(cID, $('#PrimaryCategoryVisual'));
                    }, 'metro');
                });
            });
            /*]]>*/</script><?php
        $html .= ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Fetches the options for the top 20 category selectors
     * @param string $type
     *     Type of category (PrimaryCategory)
     * @param string $selectedCat
     *     the selected category (empty for newly prepared items)
     * @param string $selectedCatName
     *     the category path of the selected category
     * @returns string
     *     option tags for the select element
     */
    protected function renderCategoryOptions($type, $selectedCat = null, $selectedCatName = null) {
        if ($this->topTen === null) {
            $this->topTen = new MetroTopTenCategories();
            $this->topTen->setMarketPlaceId($this->mpID);
        }

        $aTopTenCatIds = $this->topTen->getTopTenCategories($type);
        if (!empty($aTopTenCatIds)) {
            $opt = '<option value="">&mdash;</option>'."\n";
        } else {
            $opt = '<option value=""> -- '.ML_GENERIC_USE_CATEGORY_BUTTON.' -- &gt; </option>'."\n";
        }

        if (!empty($selectedCat) && !array_key_exists($selectedCat, $aTopTenCatIds)) {
            $opt .= '<option value="'.$selectedCat.'" selected="selected">'.$selectedCatName.'</option>'."\n";
        }

        foreach ($aTopTenCatIds as $sKey => $sValue) {
            $blSelected = (!empty($selectedCat) && ($selectedCat == $sKey));
            $opt .= '<option value="'.$sKey.'"'.($blSelected ? ' selected="selected"' : '').'>'.$sValue.'</option>'."\n";
        }

        return $opt;
    }

    protected function renderAttributesTable() {
        $mpAttributeTitle = str_replace('%marketplace%', 'Metro', ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
        $mpOptionalAttributeTitle = str_replace('%marketplace%', 'Metro', ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
        $mpCustomAttributeTitle = str_replace('%marketplace%', 'Metro', ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

        $html = '
		<tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
			<tr class="headline">
				<td colspan="1"><h4>'.$mpAttributeTitle.'</h4></td>
				<td colspan="2"><h4>'.ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB.'</h4></td>
			</tr>
		</tbody>
		<tbody id="tbodyDynamicMatchingInput" style="display:none;">
			<tr>
				<th></th>
				<td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
				<td class="info"></td>
			</tr>
		</tbody>
		<tbody id="tbodyDynamicMatchingOptionalHeadline" style="display:none;">
           <tr class="headline">
               <td colspan="1"><h4>'.$mpOptionalAttributeTitle.'</h4></td>
               <td colspan="2"><h4>'.ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB.'</h4></td>
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
                <td colspan="2"><h4>'.ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB.'</h4></td>
            </tr>
        </tbody>
        <tbody id="tbodyDynamicMatchingCustomInput" style="display:none;">
            <tr>
                <th></th>
                <td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
                <td class="info"></td>
            </tr>
        </tbody>
        ';

        return $html;
    }

    protected function renderProcessingTime($data, $isMaxProcessingTime = false) {
        $key = (($isMaxProcessingTime) ? 'Max': '').'ProcessingTime';
        $html = '<select name="'.$key.'" style="width:100%">';
        for ($i = 0; $i < 100; $i++) {
            // max processing time should be at least 1 - 0 is not allowed
            if ($isMaxProcessingTime && $i === 0) {
                continue;
            }
            $sel = '';
            if (!isset($data[0][$key])) {
                $data[0][$key] = getDBConfigValue('metro.'.(($isMaxProcessingTime) ? 'maxprocessingtime': 'processingtime'), $this->mpID);
            }
            if ($i == $data[0][$key]) {
                $sel = 'selected="selected"';
            }
            $html .= sprintf('<option value=%d %s>%d</option>', $i, $sel, $i);
        }

        return $html.'</select>';
    }

    protected function renderBusinessModel($data) {
        if (!isset($data[0]['BusinessModel'])) {
            $data[0]['BusinessModel'] = getDBConfigValue('metro.businessmodel', $this->mpID);
        }
        $html = '<select name="BusinessModel" style="width:100%">';
        $html .= '<option value="" '.(('' === $data[0]['BusinessModel']) ? 'selected="selected"' : '').'>B2B/B2C</option>';
        $html .= '<option value="B2B" '.(('B2B' === $data[0]['BusinessModel']) ? 'selected="selected"' : '').'>B2B</option>';
        return $html.'</select>';
    }

    protected function renderFreightForwarding($data) {
        if (!isset($data[0]['FreightForwarding'])) {
            $data[0]['FreightForwarding'] = getDBConfigValue('metro.freightforwarding', $this->mpID);
        }
        return '
            <input type="radio" name="FreightForwarding" value="true" '.(($data[0]['FreightForwarding'] == 'true' ? 'checked="checked"' : '')).'/>Ja
		    <input type="radio" name="FreightForwarding" value="false" '.(($data[0]['FreightForwarding'] == 'false' ? 'checked="checked"' : '')).'/>Nein
        ';
    }

    protected function getSelection() {
        $shortDescColumnExists = MagnaDB::gi()->columnExistsInTable('products_short_description',
            TABLE_PRODUCTS_DESCRIPTION);

        $keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

        # Daten aus magnalister_metro_prepare (bereits frueher vorbereitet)
        $dbOldSelectionQuery = '
		    SELECT ep.products_id, ep.products_model, ep.Manufacturer, ep.Feature, ep.MSRP, ep.ShortDescription,
		           ep.GTIN, ep.ManufacturerPartNumber, ep.Brand, ep.BusinessModel, ep.FreightForwarding, ep.ShippingProfile, ep.ShippingGroup,
		           ep.Title, ep.Description, ep.Images, ep.MSRP, ep.ProcessingTime, ep.MaxProcessingTime,
		           ep.PrimaryCategory, ep.PrimaryCategoryName, ep.ShopVariation
		      FROM '.TABLE_MAGNA_METRO_PREPARE.' ep
		';
        if ($keytypeIsArtNr) {
            $dbOldSelectionQuery .= '
		INNER JOIN '.TABLE_PRODUCTS.' p ON ep.products_model = p.products_model
		INNER JOIN '.TABLE_MAGNA_SELECTION.' ms ON  p.products_id = ms.pID AND ep.mpID = ms.mpID 
		 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pd.products_id = p.products_id
			';
        } else {
            $dbOldSelectionQuery .= '
		 INNER JOIN '.TABLE_MAGNA_SELECTION.' ms ON ep.products_id = ms.pID AND ep.mpID = ms.mpID 
		 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pd.products_id = ep.products_id
			';
        }
        $dbOldSelectionQuery .= '
		     WHERE pd.language_id = "'.getDBConfigValue($this->marketplace.'.lang', $this->mpID).'"
		           AND selectionname="prepare" 
		           AND ms.mpID = "'.$this->mpID.'" 
		           AND session_id="'.session_id().'" 
		           AND ep.products_id IS NOT NULL 
		           AND TRIM(ep.products_id) <> ""
		';
        $dbOldSelection = MagnaDB::gi()->fetchArray($dbOldSelectionQuery);
        $oldProducts = array();
        if (is_array($dbOldSelection)) {
            foreach ($dbOldSelection as $row) {
                $oldProducts[] = MagnaDB::gi()->escape($keytypeIsArtNr ? $row['products_model'] : $row['products_id']);
            }
        }

        $moreData = '';
        /* {Hook} "metroPrepareView_GetSelection_MoreData": Get more data from the shop tables TABLE_PRODUCTS and TABLE_PRODUCTS_DESCRIPTION, if you want to use other data source fields for METRO data
            The file MUST look like that:
            <?php
                $moreData = 'p.product_type AS ptype, pd.products_keywords, pd.products_url,';

             That means, it must only define a string variable '$moreData', containing field names from TABLE_PRODUCTS preceded with 'p.' and TABLE_PRODUCTS_DESCRIPTION preceded with 'pd.', optionally with an AS name, and must end with a comma.
         */
        if (($hp = magnaContribVerify($this->marketplace.'PrepareView_GetSelection_MoreData', 1)) !== false) {
            require($hp);
        }

        if (defined('BOX_HEADING_GAMBIO')) {
            # Daten fuer magnalister_metro_prepare
            # die Namen schon fuer diese Tabelle
            $dbNewSelectionQuery = '
		    SELECT p.products_id, p.products_model, mf.manufacturers_name Manufacturer,
		           ms.mpID, p.products_ean GTIN, pic.code_mpn ManufacturerPartNumber, pic.brand_name Brand,
		           pd.products_name Title, p.products_price, pd.products_meta_description,
		           '.$moreData.' pd.products_description Description, pd.products_short_description ShortDescription
		      FROM '.TABLE_PRODUCTS.' p
		INNER JOIN '.TABLE_MAGNA_SELECTION.' ms ON ms.pID = p.products_id 
		 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pd.products_id = p.products_id
		 LEFT JOIN '.TABLE_MANUFACTURERS.' mf ON mf.manufacturers_id = p.manufacturers_id
		 LEFT JOIN products_item_codes pic ON pic.products_id = p.products_id
		     WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("'.implode('", "',
                    $oldProducts).'") 
		           AND pd.language_id = "'.getDBConfigValue($this->marketplace.'.lang', $this->mpID).'" 
		           AND ms.mpID = "'.$this->mpID.'" 
		           AND selectionname="prepare" 
		           AND session_id="'.session_id().'"
		';
        } else {
            $dbNewSelectionQuery = '
		    SELECT p.products_id, p.products_model, mf.manufacturers_name Manufacturer,
		           ms.mpID, pd.products_name Title, p.products_price,
		           '.$moreData.' pd.products_description Description
		           '.((MagnaDB::gi()->columnExistsInTable('products_ean', TABLE_PRODUCTS)) ? ', p.products_ean GTIN' : '').'
		           '.((MagnaDB::gi()->columnExistsInTable('products_manufacturers_model', TABLE_PRODUCTS)) ? ', p.products_manufacturers_model ManufacturerPartNumber' : '').'
		      FROM '.TABLE_PRODUCTS.' p
		INNER JOIN '.TABLE_MAGNA_SELECTION.' ms ON ms.pID = p.products_id 
		 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pd.products_id = p.products_id
		 LEFT JOIN '.TABLE_MANUFACTURERS.' mf ON mf.manufacturers_id = p.manufacturers_id
		     WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("'.implode('", "',
                    $oldProducts).'") 
		           AND pd.language_id = "'.getDBConfigValue($this->marketplace.'.lang', $this->mpID).'" 
		           AND ms.mpID = "'.$this->mpID.'" 
		           AND selectionname="prepare" 
		           AND session_id="'.session_id().'"
		';
        }
        $dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);
        $dbSelection = array_merge(
            is_array($dbOldSelection) ? $dbOldSelection : array(),
            is_array($dbNewSelection) ? $dbNewSelection : array()
        );
        if (false) { # DEBUG
            echo print_m("dbOldSelectionQuery == \n$dbOldSelectionQuery\n");
            echo print_m($dbOldSelection, '$dbOldSelection');

            echo print_m("dbNewSelectionQuery == \n$dbNewSelectionQuery\n");
            echo print_m($dbNewSelection, '$dbNewSelection');
            echo print_m($dbSelection, '$dbSelectionMerged');
        }

        $rowCount = 0;
        $imagePath = getDBConfigValue($this->marketplace.'.imagepath', $this->mpID, '');
        if (empty($imagePath)) {
            $imagePath = SHOP_URL_POPUP_IMAGES;
        }

        foreach ($dbSelection as &$current_row) {
            ++$rowCount;
            // Prepare the gallery
            $images = MLProduct::gi()->getAllImagesByProductsId($current_row['products_id']);
            $current_row['GalleryPictures'] = array(
                'BaseUrl' => $imagePath,
                'Images' => array(),
            );
            // if not prepared, preclick all images
            $aImagesPrepared = isset($current_row['Images']) ? json_decode($current_row['Images'], true) : $images;
            foreach ($images as $img) {
            // in prepare table, imagePath is included, in getAllImagesByProductsId not, therefore check both cases
                $current_row['GalleryPictures']['Images'][$img] = in_array($imagePath.$img, $aImagesPrepared) || in_array($img, $aImagesPrepared);
            }
        }
        $dbSelection = $this->fixOSCommerceMissingFields($dbSelection);
        unset($current_row);
        if (1 == $rowCount) {
            if (!empty($dbSelection[0]['Description'])) {
                $dbSelection[0]['Description'] = MetroHelper::MetroStripTagsFromDescription($dbSelection[0]['Description']);
            }
            if (!empty($dbSelection[0]['ShortDescription'])) {
                $dbSelection[0]['ShortDescription'] = MetroHelper::MetroStripTagsFromDescription($dbSelection[0]['ShortDescription']);
            }
            if (!isset($dbSelection[0]['MSRP'])) {
                $dbSelection[0]['MSRP'] = '';
            } else {
                $dbSelection[0]['MSRP'] = number_format((float)$dbSelection[0]['MSRP'], 2, '.', '');
            }
            if (empty($dbSelection[0]['Feature'])) {
                $aMetaDescription = array_slice(explode(',', $dbSelection[0]['products_meta_description']), 0, 5);
                $dbSelection[0]['Feature'] = array_map('trim', $aMetaDescription);
            } else {
                $dbSelection[0]['Feature'] = unserialize($dbSelection[0]['Feature']);
            }

            // check for shipping profile
            if (!isset($dbSelection[0]['ShippingProfile'])) {
                $aDefaultProfile = getDBConfigValue('metro.shippingprofile', $this->mpID);
                $dbSelection[0]['ShippingProfile'] = isset($aDefaultProfile['defaults']) && is_array($aDefaultProfile['defaults']) ? array_search('1', $aDefaultProfile['defaults']) : null;
            }
            // check for shipping group
            if (!isset($dbSelection[0]['ShippingGroup'])) {
                $aDefaultGroup = getDBConfigValue('metro.shipping.group', $this->mpID);
                $dbSelection[0]['ShippingGroup'] = is_array($aDefaultGroup) ? array_search('1', $aDefaultGroup) : null;
            }

        }
        /* {Hook} "metroPrepareView_PostGetSelection": Called at the end of MetroPrepareView:getSelection().
            Here you can modify the data needed in METRO preparation, e.g. using the additional data defined in MetroPrepareView_GetSelection_MoreData contrib.
            Variables that can be used:
            <ul>
                <li>$dbSelection: The output data from getSelection(), including data defined in MetroPrepareView_GetSelection_MoreData (if used).
            </ul>
         */
        if (($hp = magnaContribVerify($this->marketplace.'PrepareView_PostGetSelection', 1)) !== false) {
            require($hp);
        }
        return $dbSelection;
    }

    private function fixOSCommerceMissingFields(array $dbSelection) {
        $missingFieldsInOSC = array(
            'products_meta_description',
            'ShortDescription',
            'GTIN',
            'ManufacturerPartNumber',
            'Brand',

        );

        foreach ($missingFieldsInOSC as $missingField) {
            if (!in_array($missingField, $dbSelection[0]) && empty($dbSelection[0])) {
                $dbSelection[0][$missingField] = '';
            }
        }

        return $dbSelection;
    }

    public function renderAjax() {
        if (isset($_GET['where']) && ($_GET['where'] == 'prepareView')) {
            $oCatMatching = new MetroCategoryMatching('ajax');
            echo $oCatMatching->renderAjax();

        } else {
            if ($_POST['prepare'] === 'prepare' || (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations'))) {
                if (isset($_POST['SelectValue'])) {
                    $select = $_POST['SelectValue'];
                } else {
                    $select = $_POST['PrimaryCategory'];
                }

                $productModel = MetroHelper::gi()->getProductModel('prepare');
                return json_encode(MetroHelper::gi()->getMPVariations($select, $productModel, true));
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

    protected function initCatMatching() {
        $params = array();
        foreach (array('mpID', 'marketplace', 'marketplaceName') as $attr) {
            if (isset($this->$attr)) {
                $params[$attr] = &$this->$attr;
            }
        }
    }
}
