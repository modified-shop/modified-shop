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

require_once(DIR_MAGNALISTER_MODULES.'hood/classes/HoodApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'hood/classes/HoodShippingDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'hood/classes/HoodTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES.'hood/prepare/HoodCategoryMatching.php');

class HoodPrepareView extends MagnaCompatibleBase {
	
	protected $catMatch = null;
	protected $topTen = null;
	protected $shopType = 'noShop';
	protected $businessSeller = false;
	
	protected function initCatMatching() {
		$params = array();
		foreach (array('mpID', 'marketplace', 'marketplaceName') as $attr) {
			if (isset($this->$attr)) {
				$params[$attr] = &$this->$attr;
			}
		}
	}

	protected function hasStore() {
		$info = HoodApiConfigValues::gi()->getHasStore();
		$this->shopType = $info['Info.ShopType'];
		#$this->shopType = 'noShop';
		if ('noShop' == $this->shopType) {
			$this->defaultListingType = 'buyItNow';
		} else {
			$this->defaultListingType = 'shopProduct';
		}
		$this->businessSeller = ('1' == $info['Info.Professional']);
	}
	
	protected function getSelection() {
		$shortDescColumnExists = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);
		
		$keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');
		
		# Daten aus magnalister_hood_properties (bereits frueher vorbereitet)
		$dbOldSelectionQuery = '
		    SELECT ep.products_id, ep.products_model,
		           ep.Manufacturer, ep.ManufacturerPartNumber,
		           ep.Title, ep.Subtitle, ep.Description, ep.StartPrice, ep.StartTime,
		           ep.ShortDescription, ep.ConditionType, ep.noIdentifierFlag,
		           ep.PrimaryCategory, ep.SecondaryCategory, ep.StoreCategory, ep.StoreCategory2, ep.StoreCategory3, 
		           ep.ShopVariation, ep.ListingType, ep.ListingDuration, ep.PaymentMethods, ep.ShippingServiceOptions,
		           pd.products_name, pd.products_description,
		           '.($shortDescColumnExists ? 'pd.products_short_description' : '"" AS products_short_description').',
		           ep.GalleryPictures, ep.Features, ep.FSK, ep.USK
		      FROM ' . TABLE_MAGNA_HOOD_PROPERTIES . ' ep
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
		
		# Daten fuer magnalister_hood_properties
		# die Namen schon fuer diese Tabelle
		# products_short_description nicht bei OsC, nur bei xtC, Gambio und Klonen
		$dbNewSelectionQuery = '
		    SELECT p.products_id, p.products_model,
		           p.products_price Price,
		           ms.mpID mpID, 
		           pd.products_name products_name,
		           '.($shortDescColumnExists ? 'pd.products_short_description' : '"" AS products_short_description').',
		           pd.products_description,
		           m.manufacturers_name Manufacturer
		      FROM ' . TABLE_PRODUCTS . ' p
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id 
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id
		 LEFT JOIN ' . TABLE_MANUFACTURERS . ' m ON p.manufacturers_id = m.manufacturers_id
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
		
		$mfrmd = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.table', $this->mpID, false);
		$loadMpn = (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table']));
		
		foreach ($dbSelection as &$current_row) {
			++$rowCount;
			// ManufacturerPartNumber
			if (!isset($current_row['ManufacturerPartNumber']) && $loadMpn) {
				$pIDAlias = getDBConfigValue($this->marketplace.'.checkin.manufacturerpartnumber.alias', $this->mpID);
				if (empty($pIDAlias)) {
					$pIDAlias = 'products_id';
				}
				$current_row['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne('
					SELECT `' . $mfrmd['column'] . '`
					  FROM `' . $mfrmd['table'] . '`
					 WHERE `' . $pIDAlias . '`="' . MagnaDB::gi()->escape($current_row['products_id']) . '"
					 LIMIT 1
				');
			}
			
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
			
			// Prepare the features
			$current_row['Features'] = isset($current_row['Features']) ? json_decode($current_row['Features'], true) : array();
			if (empty($current_row['Features'])) {
				$current_row['Features'] = array();
			}
			
			// Prepare items for not yet prepared and saved products
			if (!isset($current_row['PrimaryCategory'])) {
				$current_row['Subtitle'] = '';
				$current_row['ShortDescription'] = $current_row['products_short_description'];
			}
			
			// Strip tags from items that don't allow html
			$current_row['Subtitle'] = strip_tags($current_row['Subtitle']);
			$current_row['ShortDescription'] = strip_tags($current_row['ShortDescription']);
		}
		#echo print_m($dbSelection, 'dbS');
		
		// Only one item will be prepared. Prepare the description and title if they aren't set yet.
		if (1 == $rowCount) {
			#$dbSelection[0]['Description'] = '';
			if (empty($dbSelection[0]['Description'])) {
				$hoodTemplate = getDBConfigValue($this->marketplace.'.template.content', $this->mpID);
				# Template fuellen
				# bei mehreren Artikeln erst beim Speichern fuellen
				# Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
				$substitution = array(
					'#TITLE#' => fixHTMLUTF8Entities($dbSelection[0]['products_name']),
					'#ARTNR#' => $dbSelection[0]['products_model'],
					'#PID#' => $dbSelection[0]['products_id'],
					'#SKU#' => magnaPID2SKU($dbSelection[0]['products_id']),
					'#SHORTDESCRIPTION#' => fixHTMLUTF8Entities(stripLocalWindowsLinks($dbSelection[0]['products_short_description'])),
					'#DESCRIPTION#' => fixHTMLUTF8Entities(stripLocalWindowsLinks($dbSelection[0]['products_description'])),
				);
				
				$dbSelection[0]['Description'] = HoodHelper::getSubstitutePictures(HoodHelper::substituteTemplate(
					$this->mpID, $dbSelection[0]['products_id'], $hoodTemplate, $substitution
				), $dbSelection[0]['products_id'], $imagePath);
			}
			if (empty($dbSelection[0]['Title'])) {
				$hoodTitleTemplate = getDBConfigValue($this->marketplace.'.template.name', $this->mpID, '#TITLE#');
				# Titel-Template fuellen
				# bei mehreren Artikeln erst beim Speichern fuellen
				# Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
				$substitution = array(
					'#TITLE#' => fixHTMLUTF8Entities($dbSelection[0]['products_name']),
					'#ARTNR#' => $dbSelection[0]['products_model'],
				);
				$dbSelection[0]['Title'] = HoodHelper::substituteTemplate(
					$this->mpID, $dbSelection[0]['products_id'], $hoodTitleTemplate, $substitution
				);
			}
		}
		
		#echo print_m($dbSelection, __METHOD__);
		return $dbSelection;
	}
	
	/**
	 * Fetches the options for the top 20 category selectors
	 * @param string $type
	 *     Type of category (PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, StoreCategory3)
	 * @param string $selectedCat
	 *     the selected category (empty for newly prepared items)
	 * @param string $selectedCatName
	 *     the category path of the selected category
	 * @returns string
	 *     option tags for the select element
	 */
	protected function renderCategoryOptions($type, $selectedCat = null, $selectedCatName = null) {
		if ($this->topTen === null) {
			$this->topTen = new HoodTopTenCategories();
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
		$data['ListingType'] = isset($data['ListingType']) ? $data['ListingType'] : $this->defaultListingType;
		
		$productImagesHTML = '';
		if (!empty($data['GalleryPictures']['Images'])) {
			$maxImages = getDBConfigValue($this->marketplace.'.prepare.maximagecount', $this->mpID, 'all');
			$maxImages = $maxImages == 'all'
				? true
				: (int)$maxImages;
			
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
			$productImagesHTML .= '<br style="clear:both">'.ML_HOOD_PICTURE_PATH.': <input class="fullwidth" type="text" name="GalleryPictures[BaseUrl]" value="'.htmlspecialchars($data['GalleryPictures']['BaseUrl']).'">';
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
						<input class="fullwidth" type="text" maxlength="85" value="' . fixHTMLUTF8Entities($data['Title'], ENT_COMPAT) . '" name="Title" id="Title"/>
					</td>
					<td class="info">' . ML_HOOD_TITLE_MAX_CHARS . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_SUBTITLE . '</th>
					<td class="input">
						<input class="fullwidth blocked" type="text" maxlength="55" value="' . $data['Subtitle'] . '" name="Subtitle" id="Subtitle" disabled="disabled"/>
						<input type="hidden" name="enableSubtitle" value="false" />
						<input type="checkbox" name="enableSubtitle" value="true" /> ' . ML_HOOD_SUBTITLE_USE_YES_NO . '
					</td>
					<td class="info">' . ML_HOOD_SUBTITLE_MAX_CHARS . '<br><span style="color:red;"> ' . ML_HOOD_CAUSES_COSTS . '</span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_GENERIC_MANUFACTURER_NAME . '</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="55" value="' . fixHTMLUTF8Entities($data['Manufacturer']) . '" name="Manufacturer" id="Manufacturer" />
					</td>
					<td class="info"></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_GENERIC_MANUFACTURER_PARTNO . '</th>
					<td class="input">
						<input class="fullwidth" type="text" maxlength="55" value="' . (isset($data['ManufacturerPartNumber']) ? fixHTMLUTF8Entities($data['ManufacturerPartNumber']) : '') . '" name="ManufacturerPartNumber" id="ManufacturerPartNumber" />
					</td>
					<td class="info"></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_PICTURES . '</th>
					<td class="input">
						'.$productImagesHTML.'
					</td>
					<td class="info">' . ML_HOOD_PICTURES_DESC . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_SHORTDESCRIPTION . '</th>
					<td class="input">
						<textarea name="ShortDescription" class="fullwidth resizableVert" rows="10">'.$data['ShortDescription'].'</textarea>
					</td>
					<td class="info">' . ML_HOOD_SHORTDESCRIPTION_MAX_CHARS . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_DESCRIPTION . '</th>
					<td class="input">
						' . magna_wysiwyg(array(
							'id' => 'Description',
							'name' => 'Description',
							'class' => 'fullwidth',
							'cols' => '80',
							'rows' => '40',
							'wrap' => 'virtual'
						), $data['Description']) . '
					</td>
					<td class="info">
						' . ML_HOOD_PRODUCTS_DESCRIPTION . '<br />
						' . ML_HOOD_PLACEHOLDERS . ':
						<dl>
							<dt style="font-weight:bold; color:black">#TITLE#</dt>
								<dd>' . ML_HOOD_ITEM_NAME_TITLE . '</dd>
							<dt style="font-weight:bold; color:black">#ARTNR#</dt>
								<dd>' . ML_HOOD_ARTNO . '</dd>
							<dt style="font-weight:bold; color:black">#PID#</dt>
								<dd>' . ML_HOOD_PRODUCTS_ID . '</dd>
							<dt style="font-weight:bold; color:black">#SHORTDESCRIPTION#</dt>
								<dd>' . ML_HOOD_SHORTDESCRIPTION_FROM_SHOP . '</dd>
							<dt style="font-weight:bold; color:black">#DESCRIPTION#</dt>
								<dd>' . ML_HOOD_DESCRIPTION_FROM_SHOP . '</dd>
							<dt style="font-weight:bold; color:black">#PICTURE1#</dt>
								<dd>' . ML_HOOD_FIRST_PIC . '</dd>
							<dt style="font-weight:bold; color:black">#PICTURE2# etc.</dt>
								<dd>' . ML_HOOD_MORE_PICS . '</dd>
						</dl>
					</td>
				</tr>';
				
		
		$pConf = HoodHelper::loadPriceSettings($this->mpID);
		
		$allowBuyItNow = getDBConfigValue(array($this->marketplace.'.auction.buyitnowprice.active', 'val'), $this->mpID, false);
		if (isset($data['StartPrice']) && ((float)$data['StartPrice'] > 0)) {
			$auctionStartPrice = $data['StartPrice'];
		} else {
			$auctionStartPrice = $this->price
				->setFinalPriceFromDB($data['products_id'], $this->mpID, $pConf['Auction']['StartPrice'])
				->roundPrice()->getPrice();  // configurable
		}
		$auctionBuyItNowPrice = $this->price
			->setFinalPriceFromDB($data['products_id'], $this->mpID, $pConf['Auction']['BuyItNow'])
			->format();  // fixed (will only be displayed)
		$fixedPrice = $this->price
			->setFinalPriceFromDB($data['products_id'], $this->mpID, $pConf['Fixed'])
			->format();  // fixed (will only be displayed)
		$strikePrice = $this->price
			->setFinalPriceFromDB($data['products_id'], $this->mpID, $pConf['Strike'])
			->format();  // strikeprice (UVP)
		ob_start();
		?>
				<tr class="<?php echo (($oddEven = !$oddEven) ? 'odd' : 'even'); ?>">
					<th><?php echo ML_GENERIC_PRICE; ?></th>
					<td>
						<table id="priceAuction" class="lightstlye line15" style="display:none"><tbody>
							<?php if ($pConf['Auction']['BuyItNow']['UseBuyItNow']) { ?>
							<tr>
								<td><?php echo ML_HOOD_LABEL_STARTPRICE; ?>: </td>
								<td>
									<input type="text" length="55" maxlength="55" value="<?php
										echo number_format($auctionStartPrice, 2, '.', '');
									?>" name="StartPrice" id="StartPrice"/>
									<?php echo getDBConfigValue($this->marketplace.'.currency', $this->mpID); ?> 
								</td>
								<td></td>
							</tr>
							<?php } ?>
							<tr>
								<td><?php echo ML_HOOD_PRICE_CALCULATED.' '.ML_HOOD_BUYITNOW.''; ?> : </td>
								<td id="showCalcPrice" name="showCalcPrice">
									<?php echo $auctionBuyItNowPrice; ?> 
								</td>
								<td></td>
							</tr>
						</tbody></table>
						<table id="priceFixed" class="lightstlye line15" style="display:none"><tbody>
							<tr>
								<td><?php echo ML_HOOD_PRICE_CALCULATED ?> : </td>
								<td id="showCalcPrice" name="showCalcPrice">
									<?php echo $fixedPrice; ?> 
								</td>
								<td></td>
							</tr>
						</tbody></table>
						<?php if (getDBConfigValue('hood.strike.price.group', $this->mpID, -1) > -1): ?>
						<table id="priceStrike" class="lightstlye line15"><tbody>
							<tr>
								<td><?php echo ML_HOOD_STRIKEPRICE_CALCULATED ?> : </td>
								<td id="showCalcPrice" name="showCalcPrice">
									<?php echo $strikePrice; ?> 
								</td>
								<td></td>
							</tr>
						</tbody></table>
						<?php endif; ?>
					</td>
					<td class="info"><?php echo ML_HOOD_PRICE_FOR_HOOD ?></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
		<?php echo $this->renderMultiPrepareView(array($data)); ?>
		<?php
		$html .= ob_get_clean();
		return $html;
	}

    protected function renderAttributesTable() {
        $mpAttributeTitle = str_replace('%marketplace%', 'Hood', ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
        $mpOptionalAttributeTitle = str_replace('%marketplace%', 'Hood', ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
        $mpCustomAttributeTitle = str_replace('%marketplace%', 'Hood', ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

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

    protected function renderAttributesJS() {
        global $_url;
        ob_start();
        ?>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS; ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <script type="text/javascript"
                src="<?php echo DIR_MAGNALISTER_WS; ?>js/marketplaces/hood/variation_matching.js?<?php echo CLIENT_BUILD_VERSION ?>"></script>
        <script type="text/javascript">
            /*<![CDATA[*/
            var ml_vm_config = {
                url: '<?php echo toURL($_url, array('where' => 'HoodPrepareView', 'kind' => 'ajax'), true);?>',
                viewName: 'HoodPrepareView',
                secondaryCategory: false,
                formName: '#prepareForm',
                handleCategoryChange: false,
                i18n: <?php echo json_encode(HoodHelper::gi()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode(HoodHelper::gi()->getShopVariations()); ?>
            };
            /*]]>*/</script><?php
        $sAttrMatchJS = ob_get_contents();
        ob_end_clean();
        return $sAttrMatchJS;
    }
	
	/**
	 * @param $data	enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
	 */
	protected function renderMultiPrepareView($data) {
		// Check which values all prepared products have in common to preselect the values.
		$preSelected = array (
			'PrimaryCategory' => array(),
			'SecondaryCategory' => array(),
			'StoreCategory' => array(),
			'StoreCategory2' => array(),
			'StoreCategory3' => array(),
			'ListingType' => array(),
			'ListingDuration' => array(),
			'ConditionType' => array(),
			'PaymentMethods' => array(),
			'ShippingServiceOptions' => array(),
			'StartTime' => array(),
			'noIdentifierFlag' => array(),
			'FSK' => array(),
			'USK' => array(),
			'Features' => array(),
		);
		#echo print_m($data, '$data');
		
		$loadedPIds = array();
		foreach ($data as $row) {
			$loadedPIds[] = $row['products_id'];
			foreach ($preSelected as $field => $collection) {
				$preSelected[$field][] = isset($row[$field]) ? $row[$field] : '';
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
		if ($preSelected['ListingType'] === null) {
			$preSelected['ListingType'] = $this->defaultListingType;
		}
		if ($preSelected['ListingDuration'] === null) {
			$preSelected['ListingDuration'] = ($preSelected['ListingType'] == 'classic')
				? getDBConfigValue($this->marketplace.'.auction.duration', $this->mpID, '1')
				: getDBConfigValue($this->marketplace.'.fixed.duration', $this->mpID, '1');
		}
		if ($preSelected['ConditionType'] === null) {
			$preSelected['ConditionType'] = getDBConfigValue($this->marketplace.'.condition', $this->mpID);
		}
		if ($preSelected['noIdentifierFlag'] === null) {
			$preSelected['noIdentifierFlag'] = '0';
		}
		if ($preSelected['FSK'] === null) {
			$preSelected['FSK'] = '-1';
		}
		if ($preSelected['USK'] === null) {
			$preSelected['USK'] = '-1';
		}
		$preSelected['PaymentMethods'] = json_decode(($preSelected['PaymentMethods'] === null) ? '' : $preSelected['PaymentMethods'], true);
		if (!is_array($preSelected['PaymentMethods'])) {
			$preSelected['PaymentMethods'] = getDBConfigValue($this->marketplace.'.default.paymentmethod', $this->mpID);
			if (!is_array($preSelected['PaymentMethods'])) {
				$preSelected['PaymentMethods'] = array();
			}
		}
		$preSelected['ShippingServiceOptions'] = json_decode(($preSelected['ShippingServiceOptions'] === null) ? '' : $preSelected['ShippingServiceOptions'], true);
		if (!is_array($preSelected['ShippingServiceOptions'])) {
			$preSelected['ShippingServiceOptions'] = false;
		}
		if (!is_array($preSelected['Features'])) {
			$preSelected['Features'] = array();
		}
		
		// prepare the categories
		$categoryMatcher = new HoodCategoryMatching();
		foreach (array('PrimaryCategory', 'SecondaryCategory') as $kat) {
			if (($preSelected[$kat] === null) || !((int)$preSelected[$kat] > 0)) {
				$preSelected[$kat] = '';
				$preSelected[$kat.'Name'] = '';
			} else {
				$preSelected[$kat.'Name'] = $categoryMatcher->getHoodCategoryPath($preSelected[$kat]);
			}
		}
		foreach (array('StoreCategory', 'StoreCategory2', 'StoreCategory3') as $kat) {
			if (($preSelected[$kat] === null) || !((int)$preSelected[$kat] > 0)) {
				$preSelected[$kat] = '';
				$preSelected[$kat.'Name'] = '';
			} else {
				$preSelected[$kat.'Name'] = $categoryMatcher->getHoodCategoryPath($preSelected[$kat], true);
			}
		}
		
		#echo print_m($preSelected, '$preSelected');
		
		/*
		 * Feldbezeichner | Eingabefeld | Beschreibung
		 */
		$oddEven = false;
		$html = '
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>' . ML_HOOD_AUCTION_SETTINGS . '</h4></td>
				</tr>			
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_LISTING_TYPE . '</th>
					<td class="input">
						<div id="hood_ListingType">
						<select name="ListingType" id="ListingType">';
		if ('noShop' != $this->shopType) {
			$html .= '
							<option ' . ('shopProduct' == $preSelected['ListingType'] ? 'selected="selected"' : '') . ' value="shopProduct">' . ML_HOOD_LISTINGTYPE_SHOPPRODUCT . '</option>';
		}
		$html .= '
							<option ' . ('buyItNow' == $preSelected['ListingType'] ? 'selected="selected"' : '') . ' value="buyItNow">' . ML_HOOD_LISTINGTYPE_BUYITNOW . '</option>
							<option ' . ('classic' == $preSelected['ListingType'] ? 'selected="selected"' : '') . ' value="classic">' . ML_HOOD_LISTINGTYPE_CLASSIC . '</option>
						</select>
						</div>
					</td>
					<td class="info">' . ML_HOOD_LISTING_TYPE . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '" id="TrListingDuration">
					<th>' . ML_HOOD_DURATION_SHORT . '</th>
					<td class="input">
						<div id="hood_ListingDuration">
						<select name="ListingDuration" id="ListingDuration">
						</select>
						</div>
					</td>
					<td class="info">' . ML_HOOD_DURATION . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_PAYMENT_METHODS . '</th>
					<td class="input">
						<div id="hood_PaymentMethods">
						<select name="PaymentMethods[]" id="PaymentMethods" multiple>';
		$paymentMethods = HoodApiConfigValues::gi()->getHoodPaymentOptions();
		foreach ($paymentMethods as $method => $name) {
			$isSelected = in_array($method, $preSelected['PaymentMethods']) ? 'selected' : '';
			$html .= '
								<option ' . $isSelected . ' value="' . $method . '">' . $name . "</option>\n";
		}
		$html .= '
							</select>
						</div>
					</td>
					<td class="info">' . ML_HOOD_PAYMENT_METHODS_OFFERED . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_ITEM_CONDITION . '</th>
					<td class="input">
						<div id="hood_Condition">
						<select name="ConditionType" id="Condition">
							<option '.('new' == $preSelected['ConditionType'] ? 'selected' : '').' value="new">'.ML_HOOD_CONDITION_NEW.'</option>
							<option '.('likeNew' == $preSelected['ConditionType'] ? 'selected' : '').' value="likeNew">'.ML_HOOD_CONDITION_LIKE_NEW.'</option>
							<option '.('veryGood' == $preSelected['ConditionType'] ? 'selected' : '').' value="veryGood">'.ML_HOOD_CONDITION_VERY_GOOD.'</option>
							<option '.('usedGood' == $preSelected['ConditionType'] ? 'selected' : '').' value="usedGood">'.ML_HOOD_CONDITION_USED_GOOD.'</option>
							<option '.('acceptable' == $preSelected['ConditionType'] ? 'selected' : '').' value="acceptable">'.ML_HOOD_CONDITION_ACCEPTABLE.'</option>
							<option '.('refurbished' == $preSelected['ConditionType'] ? 'selected' : '').' value="refurbished">'.ML_HOOD_CONDITION_REFURBISHED.'</option>
							<option '.('defect' == $preSelected['ConditionType'] ? 'selected' : '').' value="defect">'.ML_HOOD_CONDITION_DEFECT.'</option>
						</select>
						</div>
					</td>
					<td class="info">' . ML_HOOD_ITEM_CONDITION_INFO . '</td>
				</tr>			
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_START_TIME_SHORT . '</th>
					<td class="input">
						' . renderDateTimePicker('startTime', $preSelected['StartTime'], true) . '
					</td>
					<td class="info">' . ML_HOOD_START_TIME . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_SPECIAL_MODEL . '</th>
					<td class="input">
						<div id="hood_noIdentifierFlag">
						<select name="noIdentifierFlag" id="noIdentifierFlag">
							<option ' . ('0' == $preSelected['noIdentifierFlag'] ? 'selected' : '') . ' value="0">' . ML_HOOD_SPECIAL_MODEL_NONE . '</option>
							<option ' . ('1' == $preSelected['noIdentifierFlag'] ? 'selected' : '') . ' value="1">' . ML_HOOD_SPECIAL_MODEL_YES . '</option>';
		if (!$this->businessSeller) {
			$html .= '
							<option ' . ('2' == $preSelected['noIdentifierFlag'] ? 'selected' : '') . ' value="2">' . ML_HOOD_SPECIAL_MODEL_YES_NOGBASE . '</option>';
		}
		$html .= '
						</select>
						</div>
					</td>
					<td class="info">
						' . ML_HOOD_SPECIAL_MODEL_INFO . ((!$this->businessSeller)? ' '.ML_HOOD_SPECIAL_MODEL_INFO_PRIVATE:''). '<br>
						<span style="color:red;"> ' . ML_HOOD_SPECIAL_MODEL_WARNING . '</span>
					</td>
				</tr>';
				
		$html .= '
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_AGE_PROTECTION . '</th>
					<td class="input">
						FSK: <select name="FSK">';
		$fskOptions = HoodApiConfigValues::gi()->getFskOptions();
		foreach ($fskOptions as $fskKey => $fskName) {
			$html .= '
							<option value="'.$fskKey.'" '.(($preSelected['FSK'] == $fskKey) ? 'selected' : '').'>'.$fskName.'</option>';
		}
		$html .= '
						</select>
						&nbsp;&nbsp;&nbsp;
						USK: <select name="USK">';
		$uskOptions = HoodApiConfigValues::gi()->getFskOptions();
		foreach ($uskOptions as $uskKey => $uskName) {
			$html .= '
							<option value="'.$uskKey.'" '.(($preSelected['USK'] == $uskKey) ? 'selected' : '').'>'.$uskName.'</option>';
		}
		$html .= '
						</select>
					</td>
					<td class="info">' . '' . '</td>
				</tr>';

		$features = array (
			'BoldTitle' => ML_HOOD_FEATURE_BOLD_TITLE,
			'BackGroundColor' => ML_HOOD_FEATURE_BACK_GROUND_COLOR,
			'Gallery' => ML_HOOD_FEATURE_GALLERY,
			'Category' => ML_HOOD_FEATURE_CATEGORY,
			'HomePage' => ML_HOOD_FEATURE_HOME_PAGE,
			'HomePageImage' => ML_HOOD_FEATURE_HOME_PAGE_IMAGE,
			'XXLImage' => ML_HOOD_FEATURE_XXLIMAGE,
			'NoAds' => ML_HOOD_FEATURE_NOADS,
		);
		$html .= '
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_ADDITIONAL_FEATURES . '</th>
					<td class="input">';
		foreach ($features as $featureKey => $featureDesc) {
			$featureId = 'feature_'.$featureKey;
			$selected = (isset($preSelected['Features'][$featureKey]) && $preSelected['Features'][$featureKey]);
			$html .= '
						<input type="hidden"   name="Features['.$featureKey.']" value="false">
						<input type="checkbox" name="Features['.$featureKey.']" value="true" id="'.$featureId.'" '.($selected ? 'checked' : '').'>
						&nbsp;
						<label for="'.$featureId.'">'.$featureDesc.'</label>
						<br>';
		}
		$html .= '
					</td>
					<td class="info">' . '<span style="color:red;"> ' . ML_HOOD_CAUSES_COSTS . '</span>' . '</td>
				</tr>';
				
		if (count($data) > 1) {
			$someItemsHaveSubtitles = (int)MagnaDB::gi()->fetchOne('
				SELECT count(*) 
				  FROM ' . TABLE_MAGNA_HOOD_PROPERTIES . '
				 WHERE mpID = "' . $this->mpID . '"
				       AND products_id IN (' . implode(', ', $loadedPIds) . ')
				       AND Subtitle <> ""
			') > 0;
			$html .= '
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_SUBTITLE . '</th>
					<td class="input">
						<input type="hidden" name="enableSubtitle" value="false" />
						<input type="checkbox" name="enableSubtitle" value="true" '.($someItemsHaveSubtitles ? ' checked="checked" ' : '').'/>
						'.ML_HOOD_SUBTITLE_USE_YES_NO.' <br><span style="color:#999; font-style:italic">'.ML_HOOD_SUBTITLE_MULTIPREPARE_NOTICE.'</span>
					</td>
					<td class="info">' . ML_HOOD_SUBTITLE_MAX_CHARS . '<br><span style="color:red;"> ' . ML_HOOD_CAUSES_COSTS . '</span></td>
				</tr>';
		}
		$html .= '
				<tr class="spacer">
					<td colspan="3">
							&nbsp;<input type="hidden" value="' . $data[0]['products_id'] . '" name="pID" id="pID"/>
					</td>
				</tr>
				<tr class="headline">
					<td colspan="3"><h4>' . ML_HOOD_CATEGORY . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_CATEGORY . '</th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<td class="label">' . ML_HOOD_PRIMARY_CATEGORY . ':</td>
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
							<tr>
								<td class="label">' . ML_HOOD_SECONDARY_CATEGORY . ':</td>
								<td>
									<div class="catVisual" id="SecondaryCategoryVisual">
										<select id="SecondaryCategory" name="SecondaryCategory" style="width:100%">
											' . $this->renderCategoryOptions('SecondaryCategory', $preSelected['SecondaryCategory'], $preSelected['SecondaryCategoryName']) . '
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin" type="button" value="' . ML_HOOD_CHOOSE . '" id="selectSecondaryCategory"/>
								</td>
							</tr>';
		if ('noShop' != $this->shopType) {
			$html .= '
							<tr>
								<td class="label">' . ML_HOOD_STORE_CATEGORY . ':</td>
								<td>
									<div class="catVisual" id="StoreCategoryVisual">
										<select id="StoreCategory" name="StoreCategory" style="width:100%">
											' . $this->renderCategoryOptions('StoreCategory', $preSelected['StoreCategory'], $preSelected['StoreCategoryName']) . '
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin" type="button" value="' . ML_HOOD_CHOOSE . '" id="selectStoreCategory"/>
								</td>
							</tr>
							<tr>
								<td class="label">' . ML_HOOD_SECONDARY_STORE_CATEGORY . ':</td>
								<td>
									<div class="hoodCatVisual" id="StoreCategory2Visual">
										<select id="StoreCategory2" name="StoreCategory2" style="width:100%">
											' . $this->renderCategoryOptions('StoreCategory2', $preSelected['StoreCategory2'], $preSelected['StoreCategory2Name']) . '
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin" type="button" value="' . ML_HOOD_CHOOSE . '" id="selectStoreCategory2"/>
								</td>
							</tr>
							<tr>
								<td class="label">' . ML_HOOD_TERTIARY_STORE_CATEGORY . ':</td>
								<td>
									<div class="hoodCatVisual" id="StoreCategory3Visual">
										<select id="StoreCategory3" name="StoreCategory3" style="width:100%">
											' . $this->renderCategoryOptions('StoreCategory3', $preSelected['StoreCategory3'], $preSelected['StoreCategory3Name']) . '
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin" type="button" value="' . ML_HOOD_CHOOSE . '" id="selectStoreCategory3"/>
								</td>
							</tr>';
		}
		$html .= '
						</tbody></table>
					</td>
					<td class="info"><span style="color:red;">' . ML_HOOD_CATEGORY_DESC . '</span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
					'. $this->renderAttributesTable() . '
			<tbody id="attr_1" style="display:none">
			</tbody>
			<tbody id="attr_2" style="display:none">
			</tbody>
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>' . ML_GENERIC_SHIPPING . '</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_SHIPPING_DOMESTIC . '</th>
					<td class="input">';
	
		$tmpURL = $this->resources['url'];
		$tmpURL['where'] = 'prepareView';
		$prefilledShippingDetailsArrayLocal = array();
		$prefilledShippingDetailsArrayInternational = array();
		if (is_array($preSelected['ShippingServiceOptions'])) {
			foreach ($preSelected['ShippingServiceOptions'] as $key => $value) {
				if (strpos($value['Service'], '_nat') !== false) {
					$prefilledShippingDetailsArrayLocal[$key] = $value;
				} else {
					$prefilledShippingDetailsArrayInternational[$key] = $value;
				}
			}
		}
		if (count($prefilledShippingDetailsArrayLocal)) {
			$shipProclocal = new HoodShippingDetailsProcessor(array(
				'content' => $prefilledShippingDetailsArrayLocal
			), $this->marketplace.'.default.shipping.local', $tmpURL);
		} else {
			$shipProclocal = new HoodShippingDetailsProcessor(array(
				'key' => $this->marketplace.'.default.shipping.local',
			), '', $tmpURL);
		}
	
		if (count($prefilledShippingDetailsArrayInternational) > 0) {
			$shipProcInter = new HoodShippingDetailsProcessor(array(
				'content' => $prefilledShippingDetailsArrayInternational
			), $this->marketplace.'.default.shipping.international', $tmpURL);
		} else if (!empty($preSelected['ShippingServiceOptions']) && !isset($prefilledShippingDetailsArray['InternationalShippingServiceOption'])) {
			$shipProcInter = new HoodShippingDetailsProcessor(array(
				'content' => array (array('Service' => '')),
			), $this->marketplace.'.default.shipping.international', $tmpURL);
		} else {
			$shipProcInter = new HoodShippingDetailsProcessor(array(
				'key' => $this->marketplace.'.default.shipping.international',
			), '', $tmpURL);
		}
	
		$html .= $shipProclocal->process() . '
					</td>
					<td class="info">' . ML_HOOD_SHIPPING_DOMESTIC_DESC . '</td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_HOOD_SHIPPING_INTL_OPTIONAL . '</th>
					<td class="input">';
	
		$html .= $shipProcInter->process();
	
		$html .= '
					</td>
					<td class="info">' . ML_HOOD_SHIPPING_INTL_DESC . '</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>';
		ob_start();
		?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ajaxStart(function() {
				myConsole.log('ajaxStart');
				jQuery.blockUI(blockUILoading);
			}).ajaxStop(function() {
				myConsole.log('ajaxStop');
				jQuery.unblockUI();
			});
			// Start blockui right now because the ajaxStart event gets registered to late.
			jQuery.blockUI(blockUILoading);
			
			function getListingDurations() {
				var preselectedDuration = '<?php echo $preSelected['ListingDuration']; ?>';
				jQuery.ajax({
					type: 'POST',
					url: '<?php echo toURL($this->resources['url'], array('where' => 'prepareView', 'kind' => 'ajax'), true); ?>',
					data: {
						'action': 'getListingDurations',
						'ListingType': $('#ListingType').val(),
						'preselected': preselectedDuration
					},
					success: function(data) {
						if ('' === data) {
							$('#TrListingDuration').css('display', 'none');
						} else {
							$('#TrListingDuration').css('display', 'table-row');
							$('#ListingDuration').html(data);
						}
					},
					error: function() {
					},
					dataType: 'html'
				});
			}
			
			function toggleFixedPriceAuction() {
				$('#priceAuction').css({'display': 'none'});
				$('#priceFixed').css({'display': 'table'});
			}
			
			function toggleClassicAuction() {
				$('#priceAuction').css({'display': 'table'});
				$('#priceFixed').css({'display': 'none'});
			}
			
			function onListingTypeChange() {
				getListingDurations();
				if ('classic' == $('#ListingType').val()) {
					toggleClassicAuction();
				} else {
					toggleFixedPriceAuction();
				}
			}
			
			$(document).ready(function() {
                $('input[name="enableSubtitle"]').change(function() {
                    var oSubtitleInput = $('input[name="Subtitle"]');

                    if ($(this).is(":checked")) {
                        oSubtitleInput.removeClass('blocked');
                        oSubtitleInput.prop('disabled', false);
                        return;
                    }

                    oSubtitleInput.prop('disabled', true);
                    oSubtitleInput.addClass('blocked');
                });

				$('#PrimaryCategoryVisual > select').change(function() {
					var cID = this.value;
					if (cID != '') {
						generateHoodCategoryPath(cID, $('#PrimaryCategoryVisual'));
						return true;
					} else {
						$('#attr_1').css({'display': 'none'});
					}
				});
				$('#PrimaryCategoryVisual > select').trigger('change');
	
				$('#SecondaryCategoryVisual > select').change(function() {
					var cID = this.value;
					if (cID != '') {
						$('#SecondaryCategory').val(cID);
						generateHoodCategoryPath(cID, $('#SecondaryCategoryVisual'));
						return true;
					} else {
						$('#attr_2').css({'display': 'none'});
					}
				});
				$('#SecondaryCategoryVisual > select').trigger('change');
	
				$('#selectPrimaryCategory').click(function() {
					startCategorySelector(function(cID) {
						$('#PrimaryCategory').val(cID);
						generateHoodCategoryPath(cID, $('#PrimaryCategoryVisual'));
					}, 'hood');
				});
				$('#selectSecondaryCategory').click(function() {
					startCategorySelector(function(cID) {
						$('#SecondaryCategory').val(cID);
						generateHoodCategoryPath(cID, $('#SecondaryCategoryVisual'));
					}, 'hood');
				});
				
				$('#selectStoreCategory').click(function() {
					startCategorySelector(function(cID) {
						$('#StoreCategory').val(cID);
						generateHoodCategoryPath(cID, $('#StoreCategoryVisual'));
					}, 'store');
				});
				$('#selectStoreCategory2').click(function() {
					startCategorySelector(function(cID) {
						$('#StoreCategory2').val(cID);
						generateHoodCategoryPath(cID, $('#StoreCategory2Visual'));
					}, 'store');
				});
				$('#selectStoreCategory3').click(function() {
					startCategorySelector(function(cID) {
						$('#StoreCategory3').val(cID);
						generateHoodCategoryPath(cID, $('#StoreCategory3Visual'));
					}, 'store');
				});
	
				$('#ListingType').change(onListingTypeChange);
				onListingTypeChange();
			});
			/*]]>*/</script><?php
		$html .= ob_get_contents();
		ob_end_clean();
	
		return $html;
	}
	
	protected function renderPrepareView($data) {
		$this->hasStore();
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
		$ycm = new HoodCategoryMatching('view');
		return $this->renderPrepareView($this->getSelection()).$ycm->render();
	}
	
	public function renderAjax() {
        $action = null;
        if (array_key_exists('action', $_POST)) {
            $action = $_POST['action'];
        } else if (array_key_exists('Action', $_POST)) {
            $action = $_POST['Action'];
        }
		if (isset($action)) {
			switch ($action) {
				case 'getListingDurations': {
					$html = '';
					if (isset($_POST['ListingType']) && ($_POST['ListingType'] == 'shopProduct')) {
						$html .= '<option selected="selected" value="-1">'.ML_LABEL_UNLIMITED.'</option>';
					} else {
						$listingDurations = HoodApiConfigValues::gi()->getListingDurations();
						if (!in_array($_POST['preselected'], $listingDurations)) {
							$highestKeyOfListingDurations = count($listingDurations) - 1;
							$_POST['preselected'] = $result['DATA']['ListingDurations'][$highestKeyOfListingDurations];
						}
						foreach ($listingDurations as $duration) {
							$define = 'ML_HOOD_LABEL_LISTINGDURATION_DAYS_' . strtoupper($duration);
							$selected = '';
							if ($_POST['preselected'] == $duration) {
								$selected = 'selected="selected"';
							}
							$html .= '
								<option '.$selected.' value="' . $duration . '">' . (defined($define) ? constant($define) : $duration) . '</option>';
						}
					}
					echo $html;
					break;
				}
				case 'extern': {
					$args = $_POST;
					unset($args['function']);
                    if (isset($args['action'])) {
                        unset($args['action']);
                    } else if (isset($args['Action'])) {
                        unset($args['Action']);
                    }
					global $_url;
					$tmpURL = $_url;
					$tmpURL['where'] = 'prepareView';
					if ('true' == $args['international']) {
						$shipProc = new HoodShippingDetailsProcessor($args, 'hood.default.shipping.international', $tmpURL);
					} else {
						$shipProc = new HoodShippingDetailsProcessor($args, 'hood.default.shipping.local', $tmpURL);
					}
					echo $shipProc->process();
					break;
				}
                case 'LoadMPVariations': {
                    if (isset($_POST['SelectValue'])) {
                        $select = $_POST['SelectValue'];
                    } else {
                        $select = $_POST['PrimaryCategory'];
                    }
                    $productModel = HoodHelper::gi()->getProductModel('prepare');
                    echo json_encode(HoodHelper::gi()->getMPVariations($select, $productModel, true));
                    break;
				}
                case 'DBMatchingColumns': {
                    $columns = MagnaDB::gi()->getTableCols($_POST['Table']);
                    $editedColumns = array();
                    foreach ($columns as $column) {
                        $editedColumns[$column] = $column;
                    }

                    echo json_encode($editedColumns, JSON_FORCE_OBJECT);
                    break;
				}
				default: {
					$ycm = new HoodCategoryMatching('ajax');
					echo $ycm->render();
					break;
				}
			}
		} else if ($_POST['prepare'] === 'prepare') {
            if (isset($_POST['SelectValue'])) {
                $select = $_POST['SelectValue'];
            } else {
                $select = $_POST['PrimaryCategory'];
            }
            $productModel = HoodHelper::gi()->getProductModel('prepare');
            return json_encode(HoodHelper::gi()->getMPVariations($select, $productModel, true));
        }
		
		#echo print_m($_POST);
	}
}
