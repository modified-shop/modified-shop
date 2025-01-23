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
 * (c) 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'etsy/EtsyHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'etsy/classes/EtsyApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'etsy/classes/EtsyShippingDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'etsy/classes/EtsyTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES.'etsy/prepare/EtsyCategoryMatching.php');

class EtsyPrepareView extends MagnaCompatibleBase {
	const ETSY_MAX_IMAGES = 10; # maximal image count allowed on Etsy
	protected $catMatch = null;
	protected $topTen = null;
	protected $shopType = 'noShop'; // hood
	protected $businessSeller = false;
	protected $defaultListingType = 'shopProduct'; // hood
	protected $defaultShippingProfile = '';
	
	protected function initCatMatching() {
		$params = array();
		foreach (array('mpID', 'marketplace', 'marketplaceName') as $attr) {
			if (isset($this->$attr)) {
				$params[$attr] = &$this->$attr;
			}
		}
	}

	public function __construct(&$params) {
		parent::__construct($params);
		$this->defaultShippingProfile = getDBConfigValue('etsy.ShippingProfile', $this->mpID);
	}
	
	protected function getSelection() {
		$shortDescColumnExists = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);
		
		$keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');
		
		# Daten aus magnalister_etsy_properties (bereits frueher vorbereitet)
		$dbOldSelectionQuery = '
		    SELECT ep.products_id, ep.products_model,
		           ep.Title, ep.Description, 
		           ep.PrimaryCategory, ep.ShopVariation,
		           ep.ShippingProfile, ep.Whomade, ep.Whenmade, ep.IsSupply, ep.Image,
		           pd.products_name, pd.products_description
		      FROM ' . TABLE_MAGNA_ETSY_PREPARE . ' ep
		';
		if ($keytypeIsArtNr) {
			$dbOldSelectionQuery .= '
		INNER JOIN ' . TABLE_PRODUCTS . ' p ON ep.products_model = p.products_model
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON  p.products_id = ms.pID AND ep.mpID = ms.mpID 
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id
			';
		} else {
			$dbOldSelectionQuery .= '
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ep.products_id = ms.pID AND ep.mpID = ms.mpID 
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = ep.products_id
			';
		}
		$dbOldSelectionQuery .='
		     WHERE pd.language_id = "' . getDBConfigValue($this->marketplace.'.lang', $this->mpID) . '"
		           AND selectionname="prepare" 
		           AND ms.mpID = "' . $this->mpID . '" 
		           AND session_id="' . session_id() . '" 
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
		
		# Daten fuer magnalister_etsy_properties
		# die Namen schon fuer diese Tabelle
		$dbNewSelectionQuery = '
		    SELECT p.products_id, p.products_model,
		           ms.mpID mpID, 
		           pd.products_name products_name,
		           pd.products_description
		      FROM ' . TABLE_PRODUCTS . ' p
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id 
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id
		     WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("' . implode('", "', $oldProducts) . '") 
		           AND pd.language_id = "' . getDBConfigValue($this->marketplace.'.lang', $this->mpID) . '" 
		           AND ms.mpID = "' . $this->mpID . '" 
		           AND selectionname="prepare" 
		           AND session_id="' . session_id() . '"
		';
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
		$imagePath = rtrim(getDBConfigValue($this->marketplace.'.imagepath', $this->mpID), '/').'/';
		
		
		foreach ($dbSelection as &$current_row) {
			++$rowCount;
			
			// Prepare the gallery
			$current_row['GalleryPictures'] = isset($current_row['GalleryPictures']) ? json_decode($current_row['GalleryPictures'], true) : array();
			if (!is_array($current_row['GalleryPictures'])
				|| !isset($current_row['GalleryPictures']['BaseUrl']) || !is_string($current_row['GalleryPictures']['BaseUrl']) || empty($current_row['GalleryPictures']['BaseUrl'])
				|| !isset($current_row['GalleryPictures']['Images'])  || !is_array($current_row['GalleryPictures']['Images'])   || empty($current_row['GalleryPictures']['Images'])
			) {
				$images = MLProduct::gi()->getAllImagesByProductsId($current_row['products_id']);
				$current_row['GalleryPictures'] = array (
					'BaseUrl' => $imagePath,
					'Images' => array(),
				);
				foreach ($images as $img) {
					$current_row['GalleryPictures']['Images'][$img] = true;
				}
			}
			
			// Prepare items for not yet prepared and saved products
			if (!isset($current_row['PrimaryCategory'])) {
				; //  TODO muss  man hier was tun?
				#$current_row['Subtitle'] = '';
				#$current_row['ShortDescription'] = $current_row['products_short_description'];
			}
			
		}
		#echo print_m($dbSelection, 'dbS');
		
		// Only one item will be prepared. Prepare the description and title if they aren't set yet.
		if (1 == $rowCount) {
			if (empty($dbSelection[0]['Description'])) {
				$dbSelection[0]['Description'] = fixHTMLUTF8Entities(EtsyHelper::sanitizeDescription(stripLocalWindowsLinks($dbSelection[0]['products_description'])));
			}
			if (empty($dbSelection[0]['Title'])) {
				$dbSelection[0]['Title'] = fixHTMLUTF8Entities($dbSelection[0]['products_name']);
			}
		}
		
		#echo print_m($dbSelection, __METHOD__);
		return $dbSelection;
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
			$this->topTen = new EtsyTopTenCategories();
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
	
	/**
	 * @param $data	enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
	 */
	protected function renderSinglePrepareView($data) {
		$productImagesHTML = '';
		if (!empty($data['GalleryPictures']['Images'])) {
			$maxImages = (int)self::ETSY_MAX_IMAGES;
			
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
		$oddEven = false;
		$html = '
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>' . ML_HOOD_PRODUCT_DETAILS . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_LABEL_PRODUCT_NAME . '</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="80" value="' . fixHTMLUTF8Entities($data['Title'], ENT_COMPAT) . '" name="Title" id="Title"/>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_GENERIC_IMAGES . '</th>
					<td class="input">
						'.$productImagesHTML.'
					</td>
					<td class="info">' . ML_ETSY_MAX_PICTURES . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_GENERIC_ITEM_DESCRIPTION . '</th>
					<td class="input">
                        <textarea class="fullwidth" name="Description" id="Description" rows="40" cols="80">'.fixHTMLUTF8Entities($data['Description'], ENT_COMPAT).'</textarea>
					</td>
					<td class="info">
						' . ML_GENERIC_PRODUCTDESCRIPTION . '
					</td>
				</tr>';

		ob_start();
		?>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
		<?php echo $this->renderMultiPrepareView(array($data)); ?>
		<?php
		$html .= ob_get_clean();
		return $html;
#<span><?php echo "<br />\n".__METHOD__.' '.__LINE__."<br />\n"; ? ></span>
#echo "<br />\n".__METHOD__.' '.__LINE__."<br />\n";
	}
	
	/**
	 * @param $data	enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
	 */
	protected function renderMultiPrepareView($data) {
		// Check which values all prepared products have in common to preselect the values.
		$preSelected = array (
			'PrimaryCategory' => array(),
			'ShippingProfile' => array(),
			'Whomade' => array(),
			'Whenmade' => array(),
			'IsSupply' => array(),
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
		
		// add some usefull defaults in case of multiple selections
		if ($preSelected['ShippingProfile'] === null) {
			$preSelected['ShippingProfile'] = $this->defaultShippingProfile;
		}
		if ($preSelected['Whomade'] === null) {
			$preSelected['Whomade'] = getDBConfigValue('etsy.whomade', $this->mpID);
		}
		if ($preSelected['Whenmade'] === null) {
			$preSelected['Whenmade'] = getDBConfigValue('etsy.whenmade', $this->mpID);
		}
		if ($preSelected['IsSupply'] === null) {
			$preSelected['IsSupply'] = getDBConfigValue('etsy.issupply', $this->mpID);
		}
		


		// prepare the categories
		$categoryMatcher = new EtsyCategoryMatching();
		foreach (array('PrimaryCategory') as $kat) {
			if (($preSelected[$kat] === null) || !((int)$preSelected[$kat] > 0)) {
				$preSelected[$kat] = '';
				$preSelected[$kat.'Name'] = '';
			} else {
				$preSelected[$kat.'Name'] = $categoryMatcher->getEtsyCategoryPath($preSelected[$kat]);
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
							&nbsp;<input type="hidden" value="' . $data[0]['products_id'] . '" name="pID" id="pID"/>
					</td>
				</tr>
				<tr class="headline">
					<td colspan="3"><h4>' . ML_LABEL_CATEGORY . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_LABEL_CATEGORY . '</th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<!--td class="label"></td-->
								<td>
									<div class="catVisual" id="PrimaryCategoryVisual">
										<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											' . $this->renderCategoryOptions('PrimaryCategory', $preSelected['PrimaryCategory'], $preSelected['PrimaryCategoryName']) . '
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin mlbtn-action" type="button" value="' . ML_HOOD_CHOOSE . '" id="selectPrimaryCategory"/>
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

		$aWhoMadeValues = array(
			'i_did'        => ML_ETSY_WHO_MADE_I_DID,
			'collective'   => ML_ETSY_WHO_MADE_COLLECTIVE,
			'someone_else' => ML_ETSY_WHO_MADE_SOMEONE_ELSE,
		);
		$aWhenMadeValues = array(
			'made_to_order' => ML_ETSY_WHEN_MADE_MADE_TO_ORDER,
			'2020_'.date('Y') => '2020-'.date('Y'),
			'2010_2019'     => ML_ETSY_WHEN_MADE_2010_2019,
			'2004_2009'     => ML_ETSY_WHEN_MADE_2004_2009,
			'before_2004'   => ML_ETSY_WHEN_MADE_BEFORE_2004,
			'2000_2003'     => ML_ETSY_WHEN_MADE_2000_2003,
			'1990s'         => ML_ETSY_WHEN_MADE_1990S,
			'1980s'         => ML_ETSY_WHEN_MADE_1980S,
			'1970s'         => ML_ETSY_WHEN_MADE_1970S,
			'1960s'         => ML_ETSY_WHEN_MADE_1960S,
			'1950s'         => ML_ETSY_WHEN_MADE_1950S,
			'1940s'         => ML_ETSY_WHEN_MADE_1940S,
			'1930s'         => ML_ETSY_WHEN_MADE_1930S,
			'1920s'         => ML_ETSY_WHEN_MADE_1920S,
			'1910s'         => ML_ETSY_WHEN_MADE_1910S,
			'1900s'         => ML_ETSY_WHEN_MADE_1900S,
			'1800s'         => ML_ETSY_WHEN_MADE_1800S,
			'1700s'         => ML_ETSY_WHEN_MADE_1700S,
			'before_1700'   => ML_ETSY_WHEN_MADE_BEFORE_1700
		);
		$aIsSuplyValues = array(
			'false' => ML_ETSY_ISSUPLY_NO,
			'true'  => ML_ETSY_ISSUPLY_YES
		);
		$aShippingProfileValues = EtsyHelper::showShippingProfiles();
		$html .= '
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>' . ML_LABEL_GENERIC_SETTINGS . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_ETSY_WHO_MADE . '</th>
					<td class="input">
						<select name="whomade">';
				foreach ($aWhoMadeValues as $k =>$v) {
					if ($preSelected['Whomade'] == $k) $s = 'selected="selected"';
					else $s = '';
					$html .= '
							<option '.$s.' value='.$k.'>'.$v.'</option>';
				}
				$html .= '
						</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_ETSY_WHEN_MADE . '</th>
					<td class="input">
						<select name="whenmade">';
				foreach ($aWhenMadeValues as $k =>$v) {
					if ($preSelected['Whenmade'] == $k) $s = 'selected="selected"';
					else $s = '';
					$html .= '
							<option '.$s.' value='.$k.'>'.$v.'</option>';
				}
				$html .= '
						</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_ETSY_ISSUPLY . '</th>
					<td class="input">
						<select name="issuply">';
				foreach ($aIsSuplyValues as $k =>$v) {
					if ($preSelected['IsSupply'] == $k) $s = 'selected="selected"';
					else $s = '';
					$html .= '
							<option '.$s.' value='.$k.'>'.$v.'</option>';
				}
				$html .= '
						</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_ETSY_SHIPPING_TEMPLATE . '</th>
					<td class="input">
						<select name="shippingprofile">';
				foreach ($aShippingProfileValues as $k =>$v) {
					if ($preSelected['ShippingProfile'] == $k) $s = 'selected="selected"';
					else $s = '';
					$html .= '
							<option '.$s.' value='.$k.'>'.$v.'</option>';
				}
				$html .= '
						</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
			</tbody>';
		ob_start();
		?><script type="text/javascript">/*<![CDATA[*/
			$(document).ajaxStart(function() {
				myConsole.log('ajaxStart');
				jQuery.blockUI(blockUILoading);
			}).ajaxStop(function() {
				myConsole.log('ajaxStop');
				jQuery.unblockUI();
			});
			// Start blockui right now because the ajaxStart event gets registered to late.
			// jQuery.blockUI(blockUILoading);
			
			$(document).ready(function() {
				$('#PrimaryCategoryVisual > select').change(function() {
					var cID = this.value;
					if (cID != '') {
						generateEtsyCategoryPath(cID, $('#PrimaryCategoryVisual'));
						return true;
					} else {
						$('#attr_1').css({'display': 'none'});
					}
				});
				$('#PrimaryCategoryVisual > select').trigger('change');
	
				$('#selectPrimaryCategory').click(function() {
					startCategorySelector(function(cID) {
						$('#PrimaryCategory').val(cID);
						generateEtsyCategoryPath(cID, $('#PrimaryCategoryVisual'));
					}, 'etsy');
				});
			});
			/*]]>*/</script><?php
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;
	}

	protected function renderAttributesTable() {
		$mpAttributeTitle = str_replace('%marketplace%', 'Etsy', ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
		$mpOptionalAttributeTitle = str_replace('%marketplace%', 'Etsy', ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
		$mpCustomAttributeTitle = str_replace('%marketplace%', 'Etsy', ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

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
		return $html;
	}

	protected function renderAttributesJS() {
		global $_url;
		ob_start();
// TODO schauen wo man es hinschiebt damit es nicht die Seite zerschiesst
		?>
	<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
	<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/marketplaces/etsy/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
	<script type="text/javascript">
		/*<![CDATA[*/
		var ml_vm_config = {
			url: '<?php echo toURL($_url, array('where' => 'EtsyPrepareView', 'kind' => 'ajax'), true);?>',
			viewName: 'EtsyPrepareView',
			secondaryCategory: false,
			formName: '#prepareForm',
			handleCategoryChange: false,
			i18n: <?php echo json_encode(EtsyHelper::gi()->getVarMatchTranslations());?>,
			shopVariations : <?php echo json_encode(EtsyHelper::gi()->getShopVariations()); ?>
		};
		/*]]>*/</script><?php
		$sAttrMatchJS = ob_get_contents();
		ob_end_clean();
		return $sAttrMatchJS;
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
			<form method="post" id="prepareForm" action="' . toURL($this->resources['url']) . '">
				<table class="attributesTable">';
		if ('single' == $prepareView) {
			$renderedView .= $this->renderSinglePrepareView($data[0]);
		} else {
			$renderedView .= $this->renderMultiPrepareView($data);
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
	
	public function process() {
		$this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
		$ycm = new EtsyCategoryMatching('view');
		return $this->renderPrepareView($this->getSelection()).$ycm->render();
	}

    public function renderAjax() {
        if (isset($_GET['where']) && ($_GET['where'] == 'prepareView')) {
            $oCatMatching = new EtsyCategoryMatching('ajax');
            echo $oCatMatching->renderAjax();

        } else if ($_POST['prepare'] === 'prepare' || (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations'))) {
            if (isset($_POST['SelectValue'])) {
                $select = $_POST['SelectValue'];
            } else {
                $select = $_POST['PrimaryCategory'];
            }

            $productModel = EtsyHelper::gi()->getProductModel('prepare');
            return json_encode(EtsyHelper::gi()->getMPVariations($select, $productModel, true));
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
