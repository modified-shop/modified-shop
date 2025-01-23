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

require_once(DIR_MAGNALISTER_MODULES.'otto/OttoHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/classes/OttoApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/classes/OttoTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/prepare/OttoCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/prepare/OttoIndependentAttributes.php');

class OttoPrepareView extends MagnaCompatibleBase {
    const OTTO_MAX_IMAGES = 10; # maximal image count allowed on Otto
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
        $this->currentLanguageCode = strtolower($_SESSION['language_code']);
        $this->defaultShippingTemplate = getDBConfigValue('otto.ShippingTemplate', $this->mpID);
        $this->url = $_url;
        $this->mpID = $_MagnaSession['mpID'];
        $this->marketplace = $_MagnaSession['currentPlatform'];
    }

    public function process() {
        $this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
        $ycm = new OttoCategoryMatching('view');
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
            $renderedView .= $this->renderMultiPrepareView($data, $prepareView);
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
        ?>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS; ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS; ?>js/marketplaces/otto/variation_matching.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo DIR_MAGNALISTER_WS; ?>css/select2/select2.min.css?<?php echo CLIENT_BUILD_VERSION?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo DIR_MAGNALISTER_WS; ?>css/select2/fix-select2.css?<?php echo CLIENT_BUILD_VERSION?>" />
        <script type="text/javascript">
            /*<![CDATA[*/``
            var ml_vm_config = {
                url: '<?php echo toURL($_url, array('where' => 'OttoPrepareView', 'kind' => 'ajax'), true);?>',
                viewName: 'OttoPrepareView',
                secondaryCategory: false,
                formName: '#prepareForm',
                handleCategoryChange: false,
                i18n: <?php echo json_encode(OttoHelper::gi()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode(OttoHelper::gi()->getShopVariations()); ?>
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

        $productImagesHTML = $productMainImagesHTML = '';
        if (!empty($data['GalleryPictures']['Images'])) {
            $maxImages = (int)self::OTTO_MAX_IMAGES;

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
            $productImagesHTML .= '<input type="hidden" name="GalleryPictures[BaseUrl]" value="'.htmlspecialchars($data['GalleryPictures']['BaseUrl']).'">';
            foreach ($data['GalleryPictures']['Images'] as $img => $checked) {
                if (empty($data['MainImage']) || !in_array($data['MainImage'], array_keys($data['GalleryPictures']['Images']))) {
                    $data['MainImage'] = key($data['GalleryPictures']['Images']);
                }
                $productMainImagesHTML .= '
					<table class="imageBox"><tbody>
						<tr><td class="image"><label for="mainimage_' . $img . '">' . generateProductCategoryThumb($img, 60, 60) . '</label></td></tr>
						<tr><td class="cb">
							<input type="radio" id="mainimage_' . $img . '" name="MainImage"
							       value="' . $img . '" ' . ($img === $data['MainImage'] ? 'checked="checked"' : '') . '/>
						</td></tr>
					</tbody></table>';
            }
//            $productImagesHTML .= '<input type="hidden" name="MainImage[BaseUrl]" value="'.htmlspecialchars($data['GalleryPictures']['BaseUrl']).'">';
        }
        if (empty($productImagesHTML)) {
            $productImagesHTML = '&mdash;';
        }
        if (empty($productMainImagesHTML)) {
            $productMainImagesHTML = '&mdash;';
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
					<td colspan="3"><h4>'.ML_OTTO_PRODUCT_DETAILS.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_OTTO_PRODUCT_TITLE.'</th>
					<td>'.ML_OTTO_PREPARE_PRODUCT_TITLE_INFO.'</td>
					<td></td>
				</tr>
				
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_GENERIC_ITEM_DESCRIPTION.'<span class="bull">&bull;</span></th>
					<td class="input">
						'.magna_wysiwyg(array(
							'id' => 'Description',
							'name' => 'Description',
							'class' => 'fullwidth',
							'cols' => '80',
							'rows' => '40',
							'wrap' => 'virtual',
					), fixHTMLUTF8Entities($data['Description'], ENT_COMPAT)).'
					</td>
					<td class="info">'.ML_OTTO_PREPARE_PRODUCT_DESCRIPTION_INFO.'</td>
				</tr>
				
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_OTTO_PRODUCT_MAINIMAGES . '<span class="bull">&bull;</span>
					<div style="float: right; width: 16px; height: 16px; background: transparent url(\'' . DIR_MAGNALISTER_WS . 'images/information.png\') no-repeat 0 0;cursor: pointer; display: inline-block; vertical-align: top;" class="desc" id="desc_1" title="Infos">
        <span style="display: none">' . ML_OTTO_PRODUCT_MAIN_IMAGE_INFO . '</span>
        </div>
        </th>
					<td class="input">
						' . $productMainImagesHTML . '
					</td>
					<td class="info">'.ML_OTTO_PREPARE_PRODUCT_IMAGES_INFO.'</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_OTTO_PRODUCT_IMAGES . '<span class="bull">&bull;</span></th>
					<td class="input">
						' . $productImagesHTML . '
					</td>
					<td class="info">' . ML_OTTO_PREPARE_PRODUCT_IMAGES_INFO . '</td>
				</tr>
				<tr class="spacer">
				    <td colspan="3">
							&nbsp;
					</td>
				</tr>
				<tr class="headline">
					<td colspan="3"><h4>'.ML_OTTO_PREPARE_PRODUCT_GENERAL_SETTINGS.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				'. $this->renderDelivery($data['DeliveryType'], $data['DeliveryTime']).'
				</tr>
			    <tr class="spacer">
			        <td colspan="3">
							&nbsp;
					</td>
				</tr>';
        ob_start();
        ?>
        </tbody>
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

    protected function renderDelivery($selectedDeliveryType = '', $selectedDeliveryTime = '') {
        if (empty($selectedDeliveryType) and  empty($selectedDeliveryTime)) {
            // if delivery type and time are not prepared load it from the configuration
            $selectedDeliveryType = getDBConfigValue($this->marketplace.'.delivery.type', $this->mpID);
            $selectedDeliveryTime = getDBConfigValue($this->marketplace.'.delivery.time', $this->mpID);
        }
        return '<th>'.ML_OTTO_PREPARE_PRODUCT_DELIVERY.'</th>
					<td class="input">
						<table><tbody>
							<tr>
								<td style="border: none" class="label">'.ML_OTTO_PREPARE_PRODUCT_DELIVERY_TYPE.':</td>
								<td style="border: none">
									<div class="catVisual">
										<select id="DeliveryType" name="DeliveryType" style="width:100%">
											'.$this->renderDeliveryType($selectedDeliveryType).'
										</select>
									</div>
								</td>
								<td style="border: none" class="label">'.ML_OTTO_PREPARE_PRODUCT_DELIVERY_TIME.':</td>
								<td style="border: none">
									<div class="catVisual">
										<select id="DeliveryType" name="DeliveryTime" style="width:100%">
											'.$this->renderDeliveryTime($selectedDeliveryTime).'
										</select>
									</div>
								</td>
							</tr>
						</tbody></table>
					</td>
					<td class="info">&nbsp;</td>';
    }

    protected function renderDeliveryType($selectedDeliveryType) {
        $deliveryTypes = array(
            "PARCEL" => ML_OTTO_PREPARE_PRODUCT_PARCEL,
            "FORWARDER_PREFERREDLOCATION" => ML_OTTO_PREPARE_PRODUCT_FORWARDER_PREFERREDLOCATION,
            "FORWARDER_CURBSIDE" => ML_OTTO_PREPARE_PRODUCT_FORWARDER_CURBSIDE);
        $opt = '';
        foreach ($deliveryTypes as $deliveryType => $translation) {
            $selected = '';
            if ($deliveryType == $selectedDeliveryType) {
                $selected = ' selected="selected" ';
            }
            $opt .= '<option '.$selected.' value="'.$deliveryType.'">'.$translation.'</option>'."\n";
        }

        return $opt;
    }

    protected function renderDeliveryTime($selectedDeliveryTime) {
        $opt = '';
        foreach (range(1, 90) as $value) {
            $selected = '';
            if ($value == $selectedDeliveryTime) {
                $selected = ' selected="selected"';
            }

            $opt .= '<option '.$selected.' value="'.$value.'">'.$value.'</option>'."\n";
        }

        return $opt;
    }

    public function render() {
        if ($this->request == 'ajax') {
            return $this->renderAjax();
        } else {
            return $this->renderView();
        }
    }

    /**
     * @param $data    enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
     */
    protected function renderMultiPrepareView($data, $prepareView) {
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
        $categoryMatcher = new OttoCategoryMatching();
        foreach (array('PrimaryCategory') as $kat) {
            if (($preSelected[$kat] === null)) {
                $preSelected[$kat] = '';
                $preSelected[$kat.'Name'] = '';
            } else {
                $preSelected[$kat.'Name'] = $categoryMatcher->getOttoCategoryName($preSelected[$kat]);
            }
        }

        $oddEven = false;
        $deliveryHtml = '';
        if ($prepareView == 'multiple') {
            $deliveryHtml = '
            	<tr class="headline">
					<td colspan="3"><h4>'.ML_OTTO_PREPARE_PRODUCT_GENERAL_SETTINGS.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				'. $this->renderDelivery().'
				</tr>
            ';
        }

        /*
         * Feldbezeichner | Eingabefeld | Beschreibung
         */
        
        $html = OttoIndependentAttributes::renderIndependentAttributesTable();

        $html .= '
			<tbody>
		        '.$deliveryHtml.'
				<tr class="spacer">
					<td colspan="3">
							&nbsp;<input type="hidden" value="'.$data[0]['products_id'].'" name="pID" id="pID"/>
					</td>
				</tr>
				<tr class="headline">
					<td colspan="3"><h4>'.ML_LABEL_CATEGORY.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_OTTO_LABEL_SELECT_CATEGORY.'<span class="bull">&bull;</span></th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<!--td class="label"></td-->
								<td>
									<div class="catVisual" id="PrimaryCategoryVisual">
										<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											'.$this->renderCategoryOptions('PrimaryCategory',
                                                $preSelected['PrimaryCategory'], 
                                                $preSelected['PrimaryCategoryName']).'
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

        $html .= '
			<tbody>
				<tr class="spacer"><td colspan="3">&nbsp;</td></tr>
				<tr class="spacer"><td colspan="3">'.ML_AMAZON_TEXT_APPLY_REQUIERD_FIELDS.'</td></tr>
				<tr class="spacer"><td colspan="3">&nbsp;</td></tr>
				</tbody>
		';

        ob_start();
        ?>
        <script type="text/javascript">/*<![CDATA[*/

            $(document).ready(function () {
                $('#selectPrimaryCategory').click(function () {
                    startCategorySelector(function (cID) {
                        $('#PrimaryCategory').val(cID);
                        generateOttoCategoryPath(cID, $('#PrimaryCategoryVisual'));
                    });
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
            $this->topTen = new OttoTopTenCategories();
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

        $html = '
		<tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
		        <tr class="headline">
                    <td class="ottoDarkGreyBackground" colspan="3"><h4>'.ML_OTTO_CATEGORY_ATTRIBUTES.'</h4>
                        <p>'.ML_OTTO_CATEGORY_ATTRIBUTES_INFO.'</p>
                    </td>
                </tr>
                <tr class="even">
                    <th class="ottoGreyBackground"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_REQUIRED.'</h4></th>
                    <td class="ottoGreyBackground" colspan="3"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_REQUIRED_INFO.'</h4></td>
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
                <tr class="even">
                    <th class="ottoGreyBackground"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_OTIONAL.'</h4></th>
                    <td class="ottoGreyBackground" colspan="3"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_OTIONAL_INFO.'</h4></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingOptionalInput" style="display:none;">
                    <tr>
                        <th></th>
                        <td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
                        <td class="info"></td>
                    </tr>
                </tbody>';
                
        return $html;
    }

    protected function getSelection() {
        $keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

        # Daten aus magnalister_otto_prepare (bereits frueher vorbereitet)
        $dbOldSelectionQuery = '
		    SELECT ep.products_id, ep.products_model, ep.Description, ep.DeliveryTime, ep.DeliveryType,
		           ep.PrimaryCategory, ep.PrimaryCategoryName, ep.ShopVariation, ep.CategoryIndependentShopVariation, 
		           ep.MainImage
		      FROM '.TABLE_MAGNA_OTTO_PREPARE.' ep
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

        if (defined('BOX_HEADING_GAMBIO')) {
            # Daten fuer magnalister_otto_prepare
            # die Namen schon fuer diese Tabelle
            $dbNewSelectionQuery = '
		    SELECT p.products_id, p.products_model, mf.manufacturers_name Manufacturer,
		           ms.mpID, p.products_ean, pic.code_mpn ManufacturerPartNumber, pic.brand_name Brand,
		           pd.products_name Title, p.products_price, pd.products_meta_description,
		           pd.products_description Description, pd.products_short_description ShortDescription
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
		           pd.products_description Description
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
            $current_row['GalleryPictures'] = isset($current_row['GalleryPictures']) ? json_decode($current_row['GalleryPictures'],
                true) : array();
            if (!is_array($current_row['GalleryPictures'])
                || !isset($current_row['GalleryPictures']['BaseUrl']) || !is_string($current_row['GalleryPictures']['BaseUrl']) || empty($current_row['GalleryPictures']['BaseUrl'])
                || !isset($current_row['GalleryPictures']['Images']) || !is_array($current_row['GalleryPictures']['Images']) || empty($current_row['GalleryPictures']['Images'])
            ) {
                $images = MLProduct::gi()->getAllImagesByProductsId($current_row['products_id']);
                $current_row['GalleryPictures'] = array(
                    'BaseUrl' => $imagePath,
                    'Images' => array(),
                );
                foreach ($images as $img) {
                    $current_row['GalleryPictures']['Images'][$img] = true;
                }
            }
        }
        $dbSelection = $this->fixOSCommerceMissingFields($dbSelection);
        unset($current_row);
        if (1 == $rowCount) {
            if (!empty($dbSelection[0]['Description'])) {
                $dbSelection[0]['Description'] = strip_tags($dbSelection[0]['Description'],
                    '<p><ul><ol><li><span><br><b>');

                // Filter JNH Tab
                if (getDBConfigValue('gambio.tabs.display', 0, 'h1') == 'none') {
                    if (strpos($dbSelection[0]['Description'], '[TAB:')) {
                        $dbSelection[0]['Description'] = substr($dbSelection[0]['Description'], 0, strpos($dbSelection[0]['Description'], '[TAB:'));
                    }
                } else {
                    $dbSelection[0]['Description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<b>${1}</b>', $dbSelection[0]['Description']);
                }

            }
            if (!empty($dbSelection[0]['ShortDescription'])) {
                $dbSelection[0]['ShortDescription'] = strip_tags($dbSelection[0]['ShortDescription'],
                    '<p><ul><ol><li><span><br><b>');
            }

            if (empty($dbSelection[0]['Feature'])) {
                $aMetaDescription = array_slice(explode(',', $dbSelection[0]['products_meta_description']), 0, 5);
                $dbSelection[0]['Feature'] = array_map('trim', $aMetaDescription);
            } else {
                $dbSelection[0]['Feature'] = unserialize($dbSelection[0]['Feature']);
            }

        }
        return $dbSelection;
    }

    private function fixOSCommerceMissingFields(array $dbSelection) {
        $missingFieldsInOSC = array(
            'products_meta_description',
            'ShortDescription',
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

    public function renderAjax($param = false) {
        if (!isset($_GET['Action']) && isset($_GET['where']) && ($_GET['where'] == 'prepareView')) {
            $oCatMatching = new OttoCategoryMatching('ajax');
            echo $oCatMatching->renderAjax();
        } else {
            if ($_POST['prepare'] === 'prepare' || (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations'))) {
                if ($param) {
                    $independentAttributesClass = new OttoIndependentAttributes;
                    $independentAttributes = $independentAttributesClass->getCategoryIndependentAttributes();

                    $productModel = OttoHelper::gi()->getProductModel('prepare');
                    return json_encode(OttoHelper::gi()->getCategoryIndependentAttributes($independentAttributes, $_POST['SelectValue'], $productModel, true));
                } else {
                    if (isset($_POST['SelectValue'])) {
                        $select = $_POST['SelectValue'];
                    } else {
                        $select = $_POST['PrimaryCategory'];
                    }

                    $productModel = OttoHelper::gi()->getProductModel('prepare');
                    return json_encode(OttoHelper::gi()->getMPVariations($select, $productModel, true));
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
}
