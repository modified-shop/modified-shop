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

/**
 * Fetches the options for the top 20 category selectors
 * @param $type	Type of category (PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2)
 * @param $selectedCat	the selected category (empty for newly prepared items)
 * @param $selectedCatName	the category path of the selected category
 * @returns string	option tags for the select element
 */
function eBayRenderCategoryOptions($type = 'PrimaryCategory', $selectedCat = 0, $selectedCatName = '') {
	global $_MagnaSession;
	# echo print_m(func_get_args(), __FUNCTION__);
	require_once DIR_MAGNALISTER_FS.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'ebay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'ebayTopTen.php';
	$oTopTen = new ebayTopTen();
	$oTopTen->setMarketPlaceId($_MagnaSession['mpID']);
	$aTopTenCatIds = $oTopTen->getTopTenCategories($type);
	if (!empty($aTopTenCatIds)) {
		$opt = '<option value="">..</option>'."\n";
	} else {
		$opt = '<option value=""> -- '.ML_GENERIC_USE_CATEGORY_BUTTON.' -- &gt; </option>'."\n";
	}
	if (
		   !empty($selectedCat)
		&& !array_key_exists($selectedCat, $aTopTenCatIds)
	) {
	    $opt .= '<option value="'.$selectedCat.'" selected="selected">'.$selectedCatName.'</option>'."\n";
	}
	foreach ($aTopTenCatIds as $sKey => $sValue) {
	    $opt .= '<option value="'.$sKey.'"'.(
			(!empty($selectedCat) && ($selectedCat == $sKey))
				? ' selected="selected"'
				: ''
		).'>'.$sValue.'</option>'."\n";
	}
	return $opt;
}

/**
 * @param $data	enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
 */
function renderSinglePrepareView($data) {
	global $_MagnaSession;

	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
	$prepareViewPrice = new SimplePrice(null, getDBConfigValue('ebay.currency', $_MagnaSession['mpID']));
	if (!array_key_exists('VariationDimensionForPictures', $data[0])) {
		$iVariationDimensionForPictures = false;
	} else {
		$iVariationDimensionForPictures = $data[0]['VariationDimensionForPictures'];
	}
	$html = '
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4>'.ML_EBAY_PRODUCT_DETAILS.'</h4></td>
			</tr>
			<tr class="odd">
				<th>'.ML_LABEL_PRODUCT_NAME.'</th>
				<td class="input">
					<input class="fullwidth" type="text" maxlength="80" value="'.fixHTMLUTF8Entities(mb_substr(html_entity_decode($data[0]['Title'], ENT_COMPAT, 'UTF-8'), 0, 80, 'UTF-8'), ENT_COMPAT).'" name="Title" id="Title"/>
				</td>
				<td class="info">'.ML_EBAY_MAX_80_CHARS.'</td>
			</tr>
			<tr class="even">
				<th>'.ML_EBAY_SUBTITLE.'</th>
				<td class="input">
					<input class="fullwidth" type="text" maxlength="55" value="'.((array_key_exists('Subtitle', $data[0])) ? mb_substr($data[0]['Subtitle'], 0, 55, 'UTF-8') : '').'" name="Subtitle" id="Subtitle" />
					<input type="checkbox" name="enableSubtitle" id="enableSubtitle" />'.ML_EBAY_LABEL_USE_SUBTITLE_YES_NO.'
				</td>
				<td class="info">'.ML_EBAY_SUBTITLE_MAX_55_CHARS.'<span style="color:red;"> '.ML_EBAY_CAUSES_COSTS.'</span></td>
			</tr>
			<tr class="odd">
				<th colspan=3 class="input">
				<span id="sDescButtonRowStandard" name="sDescButtonRowStandard">
				<input class="ml-button" style="float: left; position: relative; top: 7px; border-radius: 3px 3px 0px 0px;background-color: #666;border-color: #666;color: #fff !important;text-shadow: none;" type="button" value="'.ML_EBAY_DESCRIPTION.'" name="sstandardTemplateButton" id="sstandardTemplateButton" />
				<input class="ml-button" style="float: none; position: relative; top: 7px; border-radius: 3px 3px 0px 0px;" type="button" value="'.ML_EBAY_MOBILE_DESCRIPTION.'" name="mobileTemplateButton" id="mobileTemplateButton"/>
				</span>
				<span id="sDescButtonRowMobile" name="sDescButtonRowMobile">
				<input class="ml-button" style="float: left; position: relative; top: 7px; border-radius: 3px 3px 0px 0px;" type="button" value="'.ML_EBAY_DESCRIPTION.'" name="standardTemplateButton" id="standardTemplateButton" />
				<input class="ml-button" style="float: none; position: relative; top: 7px; border-radius: 3px 3px 0px 0px;background-color: #666;border-color: #666;color: #fff !important;text-shadow: none;" type="button" value="'.ML_EBAY_MOBILE_DESCRIPTION.'" name="mmobileTemplateButton" id="mmobileTemplateButton"/>
				</span>
				</td>
			</tr>
			<tr class="even">
				<th>&nbsp;</th>
				<td class="input">
					<span name="sDescription" id="sDescription">
					'.magna_wysiwyg(array (
						'id' => 'Description',
						'name' => 'Description',
						'class' => 'fullwidth',
						'cols'=>'80',
						'rows'=>'40',
						'wrap'=>'virtual'
					), $data[0]['Description']).'
					</span><span name="sMobileDescription" id="sMobileDescription">
					'.magna_wysiwyg(array (
						'id' => 'MobileDescription',
						'name' => 'MobileDescription',
						'class' => 'fullwidth',
						'cols'=>'80',
						'rows'=>'40',
						'wrap'=>'virtual'
					), $data[0]['MobileDescription']).'
					</span>
				</td>
				<td class="info">
				<span name="sDescInfoColStandard" id="sDescInfoColStandard">
					'.ML_EBAY_PRODUCTS_DESCRIPTION.'<br />
					'.ML_EBAY_PLACEHOLDERS.':
					<dl>
						<dt style="font-weight:bold; color:black">#TITLE#</dt>
							<dd>'.ML_EBAY_ITEM_NAME_TITLE.'</dd>
						<dt style="font-weight:bold; color:black">#ARTNR#</dt>
							<dd>'.ML_EBAY_ARTNO.'</dd>
						<dt style="font-weight:bold; color:black">#PID#</dt>
							<dd>'.ML_EBAY_PRODUCTS_ID.'</dd>';

# Preis und VPE: Vorerst nicht anbieten, kann ja geaendert werden
#						<dt style="font-weight:bold; color:black">#PRICE#</dt>
#							<dd>'.ML_EBAY_PRICE.'</dd>';
#	if (MagnaDB::gi()->tableExists(TABLE_PRODUCTS_VPE)) {
#		$html .= '
#						<dt style="font-weight:bold; color:black">#VPE#</dt>
#							<dd>'.ML_EBAY_PRICE_PER_VPE.'</dd>';
#	}
	if (getDBConfigValue('ebay.template.usemobile', $_MagnaSession['mpID'], false) === 'true') {
		$html .= '
			<input type="hidden" id="usemobile" name="usemobile" value="true" />';
	}
	$html .= '
						<dt style="font-weight:bold; color:black">#SHORTDESCRIPTION#</dt>
							<dd>'.ML_EBAY_SHORTDESCRIPTION_FROM_SHOP.'</dd>
						<dt style="font-weight:bold; color:black">#DESCRIPTION#</dt>
							<dd>'.ML_EBAY_DESCRIPTION_FROM_SHOP.'</dd>
						<dt style="font-weight:bold; color:black">#MOBILEDESCRIPTION#</dt>
							<dd>'.ML_EBAY_MOBILEDESCRIPTION_IF_DEFINED.'</dd>
						<dt style="font-weight:bold; color:black">#WEIGHT#</dt>
							<dd>'.ML_EBAY_WEIGHT_FROM_SHOP.'</dd>
						<dt style="font-weight:bold; color:black">#PICTURE1#</dt>
							<dd>'.ML_EBAY_FIRST_PIC.'</dd>
						<dt style="font-weight:bold; color:black">#PICTURE2# etc.</dt>
							<dd>'.ML_EBAY_MORE_PICS.'</dd>
					</dl>
				</span>
				<span name="sDescInfoColMobile" id="sDescInfoColMobile">
					'.ML_EBAY_MOBILE_DESCRIPTION.'<br />
					'.ML_EBAY_PLACEHOLDERS.':
					<dl>
						<dt style="font-weight:bold; color:black">#TITLE#</dt>
							<dd>'.ML_EBAY_ITEM_NAME_TITLE.'</dd>
						<dt style="font-weight:bold; color:black">#ARTNR#</dt>
							<dd>'.ML_EBAY_ARTNO.'</dd>
						<dt style="font-weight:bold; color:black">#PID#</dt>
							<dd>'.ML_EBAY_PRODUCTS_ID.'</dd>
						<dt style="font-weight:bold; color:black">#SHORTDESCRIPTION#</dt>
							<dd>'.ML_EBAY_SHORTDESCRIPTION_FROM_SHOP.'</dd>
						<dt style="font-weight:bold; color:black">#DESCRIPTION#</dt>
							<dd>'.ML_EBAY_DESCRIPTION_FROM_SHOP.'</dd>
						<dt style="font-weight:bold; color:black">#WEIGHT#</dt>
							<dd>'.ML_EBAY_WEIGHT_FROM_SHOP.'</dd>
					</dl>
					'.ML_EBAY_NOTE_MOBILE_DESC_CONSTRAINTS.'
				</span>
				</td>
			</tr>';

	$prepareViewPrice->setPrice(makePrice($data[0]['products_id'], 'FixedPriceItem'));
	$fixedPrice    = $prepareViewPrice->formatWOCurrency();
	$blUseStrikePrice = false;
	$strikePrice   = $fixedPrice; 
	if (($sStrikePriceKind = getDBConfigValue('ebay.strike.price.kind', $_MagnaSession['mpID'], 'DontUse')) != 'DontUse') {
		$prepareViewPrice->setPrice(makePrice($data[0]['products_id'], 'StrikePrice'));
		$strikePrice    = $prepareViewPrice->formatWOCurrency();
		if ($strikePrice > $fixedPrice) {
			$blUseStrikePrice = true;
		}
	}
	$prepareViewPrice->setPrice(makePrice($data[0]['products_id'], 'BuyItNowPrice', true));
	$buyItNowPrice = $prepareViewPrice->formatWOCurrency();

	$html .= '
			<tr class="odd">
				<th>'.ML_EBAY_LABEL_EBAY_PRICE.'</th>
				<td>
					<table class="lightstlye line15"><tbody>
						<tr id="TrFixedPrice">
							<td>'.ML_EBAY_PRICE_CALCULATED.': </td>
							<td id="showCalcPrice" name="showCalcPrice">
								'.$fixedPrice.' '.getDBConfigValue('ebay.currency', $_MagnaSession['mpID']).'
								<input type="hidden" value="'.$fixedPrice.'" name="Price" id="Price" />
							</td>
							<td></td>
						</tr>';
# Wenn Streichpreis-Konfig geändert wird, hier ändern
# (mit Ajax + Preisberechnung aus ebayFunctions)
#
	$html .= '
						<tr id="TrStrikePrice" '.($blUseStrikePrice ? '' : 'style="display:none"').'>
							<td>'.ML_EBAY_STRIKEPRICE_CALCULATED.': </td>
							<td id="showStrikePrice" name="showStrikePrice" style="text-decoration:line-through; color:red">
								'.$strikePrice.' '.getDBConfigValue('ebay.currency', $_MagnaSession['mpID']).'
								<input type="hidden" value="'.$strikePrice.'" name="StrikePrice" id="StrikePrice" />
							</td>
							<td></td>
						</tr>';
	$html .= '
						<tr id="chinesePrice" style="display:none">
							<td>
								<span id="bidPriceLabel">'.ML_EBAY_YOUR_CHINESE_PRICE.':</span>
							</td>
							<td>
								<input type="text" id="frozenPrice" name="frozenPrice" value="';
	if (isset($data[0]['priceFrozen']) && $data[0]['priceFrozen']) {
	    $prepareViewPrice->setPrice(makePrice($data[0]['products_id'], 'Chinese', $data[0]['priceFrozen']));
	    $html .= $prepareViewPrice->formatWOCurrency();
	} else {
		$prepareViewPrice->setPrice(makePrice($data[0]['products_id'], 'Chinese'));
		$html .= $prepareViewPrice->formatWOCurrency();
	}
	$html .= '">
								<input type="hidden" id="isPriceFrozen" name="isPriceFrozen" value="'.(
									(isset($data[0]['priceFrozen']) && $data[0]['priceFrozen'])
										? 'true'
										: 'false'
								).'">
							</td>
							<td></td>
						</tr>
						<tr id="chinesePrice2" style="display:none">
							<td>'.ML_EBAY_BUYITNOW_PRICE.': </td>
							<td>
								<input type="text" length="55" maxlength="55" value="'.$buyItNowPrice.'" name="BuyItNowPrice" id="BuyItNowPrice"/>
								<input type="checkbox" name="enableBuyItNowPrice" '.(
									(getDBConfigValue(array('ebay.chinese.buyitnow.price.active', 'val'), $_MagnaSession['mpID']) || (magnalisterEbayGetPriceByType($data[0]['products_id'], 'BuyItNowPrice') !== false))
										? ' checked="checked" '
										: ''
								).'/> aktiv
							</td>
							<td></td>
						</tr>
					</tbody></table>';
	ob_start();
	?>
					<script type="text/javascript">/*<![CDATA[*/
						if (jQuery('#isPriceFrozen').val() == 'true') {
							jQuery('#freezePrice').addClass('active');
						}
						jQuery('#freezePrice').click(function () {
							var ih = jQuery('#isPriceFrozen');
							jQuery(this).toggleClass('active');
							ih.val(jQuery(this).hasClass('active') ? 'true': 'false');
						});
						jQuery('#frozenPrice').bind('change keyup', function() {
							var valIn = jQuery.trim(jQuery(this).val()),
								valDefault = jQuery.trim(jQuery('#Price').val());
							if ((valIn == valDefault) || (valIn == '')) {
								jQuery('#freezePrice').removeClass('active');
								jQuery('#isPriceFrozen').val('false');
							} else {
								jQuery('#freezePrice').addClass('active');
								jQuery('#isPriceFrozen').val('true');
							}
						});
					/*]]>*/</script>
	<?php
	$html .= ob_get_clean();
	$html .= '
				</td>
				<td class="info">'.ML_EBAY_PRICE_FOR_EBAY.'</td>
			</tr>
		</tbody>
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4>'.ML_EBAY_PICTURE_SETTINGS.'</h4></td>
			</tr>
			'.ebayPictureUrlHtml($data[0]['products_id'],
				// wenn noch nicht vorbereitet UND PictureURL is array:
				// klicke alle Bilder an
				((     !array_key_exists('PrimaryCategory', $data[0])
				    && is_array($data[0]['PictureURL']))
				? array('__use_all__')
				: $data[0]['PictureURL']),
				'odd')
			 .ebayGalleryTypeHtml($data[0]['GalleryType'], true)
			 .ebayPicturePackPropertiesListHtml($data[0]['products_id'], $iVariationDimensionForPictures);
/*
	// Purge: immer an (zu viel Verwirrung sonst. Kann man noch nachpflegen.)
	//        Müßte man auch anders für Single und Multiple machen, zu viel Aufwand.
	if (getDBConfigValue(array('ebay.picturepack', 'val'), $_MagnaSession['mpID'])){
		$html .='<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_LABEL_PICTUREPACK_PURGE.'</th>
				<td class="input">
					<input type="checkbox" name="PicturePackPurge" id="PicturePackPurge" ';
		$picturePackPurge = MagnaDB::gi()->fetchArray(eecho('
			SELECT SQL_CALC_FOUND_ROWS DISTINCT eBayPicturePackPurge
			FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
			WHERE products_id IN ('.$products_id_list.')
			AND mpID = '.$_MagnaSession['mpID'].'
		'), true);

		if (1 == (int)MagnaDB::gi()->foundRows()) {
			if ('1' == $picturePackPurge[0]['eBayPicturePackPurge']) {
			    $html .= ' checked="checked" ';
			}
		} else {
			    $html .= ' checked="checked" ';
		}
		$html .= '/><label for="PicturePackPurge" >'.ML_EBAY_LABEL_PICTUREPACK_PURGE_TEXT.'</label>
					</td>
					<td class="info">'.ML_EBAY_LABEL_PICTUREPACK_PURGE_RIGHTTEXT.'</td>
				</tr>';
	}
*/
	$html .= '
		'.renderMultiPrepareView($data).'
	';
	return $html;
}

/**
 * @param $data	enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
 */
function renderMultiPrepareView($data) {
// DEBUG
#echo print_m($data, __LINE__.' '.__FUNCTION__.' $data');
/* $data :: Array
(
    [0] => Array
        (
            [products_id] => 9
            [products_model] => GT-S5230LKAXEB
    ...
            [ePID] => variations
*/
	global $_MagnaSession, $_url;
	/* Ggf. Vorausfuellen der Kategorie */
	$prefilledCatsArray = array();
	$ListingTypeArray = array();
	$ListingDurationArray = array();
	$i = 0;
	$lastI = 0;

	$ConditionIDArray          = array();
	$ConditionDescriptionArray = array();
	$ConditionDescriptorsArray = array();
	$StrikePriceConfArray      = array();
	$PaymentMethodsArray       = array();
	$ShippingDetailsArray      = array();
	$SellerProfilesArray       = array();
	$DispatchTimeMaxArray      = array();
	$blBusinessPoliciesSet     = geteBayBusinessPolicies();
	$ePidsFilled = 0;

	foreach ($data as $row) {
		if (   isset($row['PrimaryCategory'])
		    && (0 != $row['PrimaryCategory'])
		    && (     (0 == $i)
                  || ($prefilledCatsArray[$lastI]['PrimaryCategory']   != $row['PrimaryCategory'])
		          || ($prefilledCatsArray[$lastI]['SecondaryCategory'] != $row['SecondaryCategory'])
		          || ($prefilledCatsArray[$lastI]['StoreCategory']     != $row['StoreCategory'])
		          || ($prefilledCatsArray[$lastI]['StoreCategory2']    != $row['StoreCategory2'])
		       )
		) {
		    $prefilledCatsArray[$i] = array (
			    'PrimaryCategory'  => $row['PrimaryCategory'],
			    'SecondaryCategory'=> $row['SecondaryCategory'],
			    'StoreCategory'    => $row['StoreCategory'],
			    'StoreCategory2'   => $row['StoreCategory2']
		    );
		    $lastI = $i;
		}
		if (!empty($row['StrikePriceConf'])) { // brauchen wir wahrscheinlich nicht
			$StrikePriceConfArray[] = $row['StrikePriceConf'];
		}
		if (isset($row['ListingType'])) {
			$ListingTypeArray[]     = $row['ListingType'];
			$ListingDurationArray[] = $row['ListingDuration'];
			$ConditionIDArray[] = $row['ConditionID'];
			if(!empty($row['ConditionDescription'])) {
				$ConditionDescriptionArray[] = $row['ConditionDescription'];
			}
			if(!empty($row['ConditionDescriptors'])) {
				$ConditionDescriptorsArray[] = $row['ConditionDescriptors'];
			}
			$PaymentMethodsArray[] = $row['PaymentMethods'];
			$ShippingDetailsArray[] = $row['ShippingDetails'];
		}
		if ($row['DispatchTimeMax'] <= 40) { // gueltige Werte bis 40, table default == 99
			$DispatchTimeMaxArray[] = $row['DispatchTimeMax'];
		}
		if (!empty($row['SellerProfiles'])) {
			$SellerProfilesArray[] = $row['SellerProfiles'];
		}
		if (!empty($row['ePID'])) {
			$ePidsFilled++;
		}
		
        $lastI = $i;
		++$i;
	}
	/* wenn alle ePIDs ausgefuellt, muss die Warnung nicht angezeigt werden */
	if ($ePidsFilled == $i) {
		$ePidsFilled = true;
	} else {
		$ePidsFilled = false;
	}

	/* nur vorausfuellen wenn fuer alle gleich */
	$PrimaryCategory = null;
	if (1 == count($prefilledCatsArray)) {
		$currCatsKey = key($prefilledCatsArray);
		$PrimaryCategory     = trim($prefilledCatsArray[$currCatsKey]['PrimaryCategory']);
		$PrimaryCategoryName = (!empty($PrimaryCategory))
			? geteBayCategoryPath($PrimaryCategory)
			: '';
		$SecondaryCategory   = trim($prefilledCatsArray[$currCatsKey]['SecondaryCategory']);
		$SecondaryCategoryName = (!empty($SecondaryCategory))
			? geteBayCategoryPath($SecondaryCategory)
			: '';
		$StoreCategory       = trim($prefilledCatsArray[$currCatsKey]['StoreCategory']);
		$StoreCategoryName   = (!empty($StoreCategory))
			? geteBayCategoryPath($StoreCategory, true)
			: '';
		$StoreCategory2      = trim($prefilledCatsArray[$currCatsKey]['StoreCategory2']);
		$StoreCategory2Name  = (!empty($StoreCategory2))
			? geteBayCategoryPath($StoreCategory2, true)
			: '';

		if (!empty($data[$lastI]['ItemSpecifics'])) {
			$PrimaryPreselectedValues   = $data[$lastI]['ItemSpecifics'];
			$SecondaryPreselectedValues = $data[$lastI]['ItemSpecifics'];
		} else if (!empty($data[$lastI]['Attributes'])) {
			$PrimaryPreselectedValues = $data[$lastI]['Attributes'];
		} else {
			$PrimaryPreselectedValues = '';
		}
	} else {
		$PrimaryCategoryName      = '';
		$SecondaryCategory        = null;
		$SecondaryCategoryName    = '';
		$StoreCategory            = null;
		$StoreCategoryName        = '';
		$StoreCategory2           = null;
		$StoreCategory2Name       = '';
		$PrimaryPreselectedValues = '';
	}
	/* Listing-Typ, Dauer, ConditionID usw. fuer alle gleich?
	   Dann setzen (sonst default aus der Konfig)
	*/
	$ListingType = null;
	if (is_array($ListingTypeArray)) {
		$ListingTypeArray = array_unique($ListingTypeArray);
		if (1 == count($ListingTypeArray)) {
			$ListingType = $ListingTypeArray[0];
		}
	}
	$ListingDuration = (('Chinese' == $ListingType)
			? getDBConfigValue('ebay.chinese.duration', $_MagnaSession['mpID'], null)
			: getDBConfigValue('ebay.fixed.duration'  , $_MagnaSession['mpID'], null));
	if (is_array($ListingDurationArray)) {
		$ListingDurationArray = array_unique($ListingDurationArray);
		if (1 == count($ListingDurationArray)) {
			$ListingDuration = $ListingDurationArray[0];
		}
	}
	$ConditionID = null;
	if (is_array($ConditionIDArray)) {
		$ConditionIDArray = array_unique($ConditionIDArray);
		if (1 == count($ConditionIDArray)) {
			$ConditionID = $ConditionIDArray[0];
		}
	}
	$ConditionDescription = '';
	if (is_array($ConditionDescriptionArray)) {
		$ConditionDescriptionArray = array_unique($ConditionDescriptionArray);
		if (1 == count($ConditionDescriptionArray)) {
			$ConditionDescription = $ConditionDescriptionArray[0];
		}
	}
	$ConditionDescriptors = '';
	if (is_array($ConditionDescriptorsArray)) {
		$ConditionDescriptorsArray = array_unique($ConditionDescriptorsArray);
		if (1 == count($ConditionDescriptorsArray)) {
			$ConditionDescriptors = $ConditionDescriptorsArray[0];
		}
	}
	$prefilledPaymentMethods = null;
	if (is_array($PaymentMethodsArray)) {
		$PaymentMethodsArray = array_unique($PaymentMethodsArray);
		if (1 == count($PaymentMethodsArray)) {
			$prefilledPaymentMethods = $PaymentMethodsArray[0];
		}
	}
	$prefilledShippingDetails = null;
	if (is_array($ShippingDetailsArray)) {
		$ShippingDetailsArray = array_unique($ShippingDetailsArray);
		if (1 == count($ShippingDetailsArray)) {
			$prefilledShippingDetails = $ShippingDetailsArray[0];
		}
	}
	$prefilledSellerProfiles = null;
	if (is_array($SellerProfilesArray) && !empty($SellerProfilesArray)) {
		$SellerProfilesArray = array_unique($SellerProfilesArray);
		if (1 == count($SellerProfilesArray)) {
			$prefilledSellerProfiles = json_decode($SellerProfilesArray[0], true);
		}
	}
	$prefilledStrikePriceConf = null;
#echo print_m($StrikePriceConfArray, __LINE__.' $StrikePriceConfArray');
	if (is_array($StrikePriceConfArray) && !empty($StrikePriceConfArray)) {
		$StrikePriceConfArray = array_unique($StrikePriceConfArray);
		if (1 == count($StrikePriceConfArray)) {
			$prefilledStrikePriceConf = json_decode($StrikePriceConfArray[0], true);
#echo print_m($prefilledStrikePriceConf, __LINE__.' $prefilledStrikePriceConf');
		}
	}
	if (null == $prefilledStrikePriceConf) {
		$prefilledStrikePriceConf = json_decode(getStrikePriceConfigForPrepareTable(), true);
#echo print_m($prefilledStrikePriceConf, __LINE__.' $prefilledStrikePriceConf');
	}
	/*
	 * check if prefilled profiles are valid
	 */
	if (isset($prefilledSellerProfiles)) {
		foreach ($prefilledSellerProfiles as $sKind => &$profileID) {
			$getProfilesFunc = 'geteBaySeller'.$sKind.'Profiles';
			$aProfiles = $getProfilesFunc();
			if (!array_key_exists($profileID, $aProfiles)) {
				$profileID = getDBConfigValue('ebay.default.'.strtolower($sKind).'sellerprofile', $_MagnaSession['mpID'], 0);
			}
		}
	}
	if ( $blBusinessPoliciesSet
	     && !isset($prefilledSellerProfiles)) {
		$prefilledSellerProfiles = array (
			'Payment'  => getDBConfigValue('ebay.default.paymentsellerprofile' , $_MagnaSession['mpID']),
			'Shipping' => getDBConfigValue('ebay.default.shippingsellerprofile', $_MagnaSession['mpID']),
			'Return'   => getDBConfigValue('ebay.default.returnsellerprofile'  , $_MagnaSession['mpID'])
		);
	}
	$prefilledDispatchTimeMax = null;
	if (is_array($DispatchTimeMaxArray)) {
		$DispatchTimeMaxArray = array_unique($DispatchTimeMaxArray);
		if (1 == count($DispatchTimeMaxArray)) {
			$prefilledDispatchTimeMax = $DispatchTimeMaxArray[0];
		} else {
			$prefilledDispatchTimeMax = getDBConfigValue('ebay.DispatchTimeMax', $_MagnaSession['mpID'], 40);
		}
	}

	// PBSE: forward ePIDs, if available
	$html = '';
	foreach ($data as $row) {
		if (isset($row['ePID']) && !empty($row['ePID'])) {
			$html .= '
		<input type="hidden" value="'.$row['ePID'].'" name="ePID'.$row['products_id'].'" id="ePID'.$row['products_id'].'"/>';
		}
	}
	#if ($IsMatching) 
	#		$html .= '
	#	<input type="hidden" value="matching" name="prepare" id="prepare"/>';
	/*
	 * Feldbezeichner | Eingabefeld | Beschreibung
	 */
	$oddEven = false;
	$html .= eBayVATHTML($data).'
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4>'.ML_EBAY_AUCTION_SETTINGS.'</h4></td>
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>eBay-Site</th>
				<td class="input">
					<div id="ebay_Site">
					<select name="Site" id="Site">';
	$sites = array (
		'Australia' => ML_COUNTRY_AUSTRALIA,
		'Austria' => ML_COUNTRY_AUSTRIA,
		'Belgium_Dutch' => ML_COUNTRY_BELGIUM_DUTCH,
		'Belgium_French' => ML_COUNTRY_BELGIUM_FRENCH,
		'Canada' => ML_COUNTRY_CANADA,
		'CanadaFrench' => ML_COUNTRY_CANADA_FRENCH,
		'China' => ML_COUNTRY_CHINA,
		'France' => ML_COUNTRY_FRANCE,
		'Germany' => ML_COUNTRY_GERMANY,
		'HongKong' => ML_COUNTRY_HONGKONG,
		'India' => ML_COUNTRY_INDIA,
		'Ireland' => ML_COUNTRY_IRELAND,
		'Italy' => ML_COUNTRY_ITALY,
		'Malaysia' => ML_COUNTRY_MALAYSIA,
		'Netherlands' => ML_COUNTRY_NETHERLANDS,
		'Philippines' => ML_COUNTRY_PHILIPPINES,
		'Poland' => ML_COUNTRY_POLAND,
		'Singapore' => ML_COUNTRY_SINGAPORE,
		'Spain' => ML_COUNTRY_SPAIN,
		'Sweden' => ML_COUNTRY_SWEDEN,
		'Switzerland' => ML_COUNTRY_SWITZERLAND,
		'Taiwan' => ML_COUNTRY_TAIWAN,
		'UK' => ML_COUNTRY_UK,
		'US' => ML_COUNTRY_USA,
		'eBayMotors' => ML_EBAY_SITE_MOTORS
	);
	$selectedSite = getDBConfigValue('ebay.site', $_MagnaSession['mpID']);
	foreach ($sites as $site => $siteName) {
		if ($selectedSite != $site)
			continue; # Site-Auswahl nur in der Konfig
		$html .= '<option ';
		if ($selectedSite == $site) {
			$html .= 'selected ';
		}
		$html .= 'value="'.$site.'">'.$siteName.'</option>';
	}
	$html .='
					</select>
					</div>
				</td>
				<td class="info">'.ML_EBAY_SITE.'</td>
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_LISTING_TYPE.'</th>
				<td class="input">
					<div id="ebay_ListingType">
					<select name="ListingType" id="ListingType">';
	try {
		$eBayStoreData = MagnaConnector::gi()->submitRequest(array('ACTION' => 'HasStore'));
	} catch (MagnaException $e) {
		echo print_m($e->getErrorArray(), 'Error');
	}
	if('True' == $eBayStoreData['DATA']['Answer']) {
		$hasStore = true;
		$html .= '
					<option '.('StoresFixedPrice' == $ListingType ? 'selected="selected"':'').' value="StoresFixedPrice">'.ML_EBAY_LISTINGTYPE_STORESFIXEDPRICE.'</option>';
	} else {
		$hasStore = false;
	}
	$html .= '
						<option '.('FixedPriceItem' == $ListingType? 'selected="selected"':'').' value="FixedPriceItem">'.ML_EBAY_LISTINGTYPE_FIXEDPRICEITEM.'</option>
						<option '.('Chinese' == $ListingType? 'selected="selected"':'').' value="Chinese">'.ML_EBAY_LISTINGTYPE_CHINESE.'</option>
					</select>
					</div>
				</td>
				<td class="info">'.ML_EBAY_LISTING_TYPE.'</td>
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_DURATION_SHORT.'</th>
				<td class="input">
					<div id="ebay_ListingDuration">
					<select name="ListingDuration" id="ListingDuration">
					</select>
					</div>
				</td>
				<td class="info">'.ML_EBAY_DURATION.'</td>
			</tr>';
/*
	Anzeige:
	- Do in Konfig => immer anzeige
	- Dont in Konfig + Do in Vorbereitung => anzeige
	- Dont in Konfig + nicht vorbereitet o. dont => ausblende
	vorausfülle:
	- alle gleich => aus Vorbereitung
	- sonst aus Konfig
*/
	if ($prefilledStrikePriceConf['ebay.strike.price.kind'] === 'DontUse') {
		$blUseStrikePrice = false;
	} else {
		$blUseStrikePrice = true;
	}
	if (getDBConfigValue('ebay.strike.price.kind', $_MagnaSession['mpID'], 'DontUse') != 'DontUse') {
		$html .= '
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_STRIKE_PRICES.'</th>
				<td>
					<input type="checkbox" '.($blUseStrikePrice ? 'checked="checked"':'').' value="true" name="UseStrikePrice" id="UseStrikePrice" />'.ML_LABEL_ACTIVATE.'
					<input type="hidden" value="'.getDBConfigValue('ebay.strike.price.kind', $_MagnaSession['mpID'], 'DontUse').'" name="StrikePriceKind" id="StrikePriceKind" />
					<input type="hidden" value="'.getDBConfigValue('ebay.strike.price.group', $_MagnaSession['mpID'], 'DontUse').'" name="StrikePriceGroup" id="StrikePriceGroup" />
					<input type="hidden" value="'.getDBConfigValue(array('ebay.strike.price.isUVP', 'val'), $_MagnaSession['mpID'], 'false').'" name="StrikePriceIsUvp" id="StrikePriceIsUvp" />
				</td>
				<td class="info">'.ML_EBAY_STRIKE_PRICES.'</td>
			</tr>';
	}
	$html .= '
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">';
	if ($blBusinessPoliciesSet) {
		$html .= '
			<th>'.ML_EBAY_LABEL_BUSINESS_POLICIES_PAYMENT.'</th>
			<td class="input">
				<div id="ebay_PaymentSellerProfile">
				<select name="paymentsellerprofile" id="paymentsellerprofile">';
				$paymentProfiles = geteBaySellerPaymentProfiles();
		foreach($paymentProfiles as $profileID => $profileName) {
				$isSelected = ($profileID == $prefilledSellerProfiles['Payment']? 'selected' : '');
				$html .= '
						<option '.$isSelected.' value="'.$profileID.'">'.$profileName."</option>\n";
		}
		$html .= '
				</select>
				</div>
				</td>
				<td class="info">'.ML_EBAY_HINT_BUSINESS_POLICIES_PAYMENT.'</td>
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">';
	}
	$html .= '
			<th>'.ML_EBAY_PAYMENT_METHODS.'</th>
			<td class="input">
				<div id="ebay_PaymentMethods">';
	try {
		$PaymentMethods = geteBayPaymentOptions();
	} catch (MagnaException $e) {
		echo print_m($e->getErrorArray(), 'Error');
	}
	if (count($PaymentMethods) > 1) {
	$html .= '
				<select name="PaymentMethods[]" id="PaymentMethods" '.($blBusinessPoliciesSet? 'disabled="disabled" style="background-color:#dfdfdf" ':'').' multiple>';
	if ($blBusinessPoliciesSet) {
		$sellerProfileContents = getDBConfigValue('ebay.sellerprofile.contents', $_MagnaSession['mpID']);
		$defaultPaymentMethods = $sellerProfileContents['Payment'][$prefilledSellerProfiles['Payment']]['paymentmethod'];
	} else if (isset($prefilledPaymentMethods)) {
		$defaultPaymentMethods = json_decode(fixBrokenJsonUmlauts($prefilledPaymentMethods), true);
	} else {
		$defaultPaymentMethods = getDBConfigValue('ebay.default.paymentmethod', $_MagnaSession['mpID']);
	}
	foreach($PaymentMethods as $method => $name) {
		(is_array ($defaultPaymentMethods) && in_array ($method, $defaultPaymentMethods))
			? $isSelected = 'selected'
			: $isSelected = '';
		$html .= '
						<option '.$isSelected.' value="'.$method.'">'.$name."</option>\n";
	}
	$html .= '
					</select>';
	} else {
		$html .= '
					<input type="hidden" name="PaymentMethods" id="PaymentMethods" value="'.current(array_keys($PaymentMethods)).'"/>
					'.current($PaymentMethods).'
';
	}
	$html .= '
				</div>
			</td>
			<td class="info">'.ML_EBAY_PAYMENT_METHODS_OFFERED.'</td>';
	$html .= '
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_ITEM_CONDITION.'</th>
				<td class="input">
					<div id="ebay_Condition">
					<select name="ConditionID" id="ConditionID">';
	$conditions = array (
        '1000' => ML_EBAY_CONDITION_NEW,
        '1500' => ML_EBAY_CONDITION_NEW_OTHER,
        '1750' => ML_EBAY_CONDITION_NEW_WITH_DEFECTS,
        '2000' => ML_EBAY_CONDITION_CERTIFIED_REFURBISHED,
        '2010' => ML_EBAY_CONDITION_EXCELLENT_REFURBISHED,
        '2020' => ML_EBAY_CONDITION_VERY_GOOD_REFURBISHED,
        '2030' => ML_EBAY_CONDITION_GOOD_REFURBISHED,
        '2500' => ML_EBAY_CONDITION_SELLER_REFURBISHED,
        '2750' => ML_EBAY_CONDITION_AS_NEW,
        '3000' => ML_EBAY_CONDITION_USED,
        '4000' => ML_EBAY_CONDITION_VERY_GOOD,
        '5000' => ML_EBAY_CONDITION_GOOD,
        '6000' => ML_EBAY_CONDITION_ACCEPTABLE,
        '7000' => ML_EBAY_CONDITION_FOR_PARTS_OR_NOT_WORKING
	);
	if (isset($ConditionID)) {
		$defaultConditionID = $ConditionID;
	} else {
		$defaultConditionID = getDBConfigValue('ebay.condition',$_MagnaSession['mpID']);
	}
	foreach($conditions as $Condition => $name) {
		$isSelected = ($Condition == $defaultConditionID? 'selected' : '');
		$html .= '
						<option '.$isSelected.' value="'.$Condition.'">'.$name."</option>\n";
	}

	$html .= '
					</select>
					</div>
				</td>
				<td class="info">'.ML_EBAY_ITEM_CONDITION_INFO.'</td>
			</tr>';
	$html .= '
			<tr class="'.(($oddEven) ? 'odd' : 'even').'" id="ebay_ConditionDescriptors_tr" name="ebay_ConditionDescriptors_tr" >
				<th>'.ML_EBAY_ITEM_CONDITION_DESCRIPTORS.'</th>
				<td class="input">
				<div id="ebay_ConditionDescriptors">
				</div>
				</td>
				<td class="info">'.ML_EBAY_ITEM_CONDITION_DESCRIPTORS_INFO.'</td>
			</tr>';
	$html .= '
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_ITEM_CONDITION_DESCRIPTION.'</th>
				<td class="input">
					<input class="fullwidth" type="text" maxlength="1000" value="'.fixHTMLUTF8Entities($ConditionDescription, ENT_COMPAT).'" name="ConditionDescription" id="ConditionDescription"/>
				</td>
				<td class="info">'.ML_EBAY_ITEM_CONDITION_DESCRIPTION_INFO.'</td>
			</tr>';
	$html .= '
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_PRIVATE_LISTING_SHORT.'</th>
				<td class="input">
                    <input type="checkbox" name="privateListing" id="privateListing" ';
    $products_id_list = '';
    foreach ($data as $item) {
    	$products_id_list .= ', '.$item['products_id'];
    }
    $products_id_list = trim($products_id_list, ', ');
    $privateListingSet = MagnaDB::gi()->fetchArray('
    	SELECT SQL_CALC_FOUND_ROWS DISTINCT PrivateListing
          FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
         WHERE products_id IN ('.$products_id_list.')
		   AND mpID = '.$_MagnaSession['mpID'].'
    ');
    if (1 == (int)MagnaDB::gi()->foundRows()) {
        if ('1' == $privateListingSet[0]['PrivateListing']) {
            $html .= ' checked="checked" ';
        }
    } else {
        if (getDBConfigValue(array('ebay.privatelisting', 'val'), $_MagnaSession['mpID'])) {
            $html .= ' checked="checked" ';
        }
    }
	$html .= '/>'.ML_EBAY_PRIVATE_LISTING_YES_NO.'
				</td>
				<td class="info">'.ML_EBAY_PRIVATE_LISTING.'<span style="color:red;"> '.ML_EBAY_CAUSES_COSTS.'</span></td>
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'" name="bestOfferRow" id="bestOfferRow">
				<th>'.ML_EBAY_BESTPRICE_SHORT.'</th>
				<td class="input">
                    <input type="checkbox" name="bestOfferEnabled" id="bestOfferEnabled" ';
    $bestOfferEnabledSet = MagnaDB::gi()->fetchArray('
    	SELECT SQL_CALC_FOUND_ROWS DISTINCT BestOfferEnabled
          FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
         WHERE products_id IN ('.$products_id_list.')
		   AND mpID = '.$_MagnaSession['mpID'].'
    ');
    if (1 == (int)MagnaDB::gi()->foundRows()) {
        if ('1' == $bestOfferEnabledSet[0]['BestOfferEnabled']) {
            $html .= ' checked="checked" ';
        }
    } else {
        if (getDBConfigValue(array('ebay.bestprice', 'val'), $_MagnaSession['mpID'])) {
            $html .= ' checked="checked" ';
        }
    }
    $StartTimeSet = MagnaDB::gi()->fetchArray('
    	SELECT SQL_CALC_FOUND_ROWS DISTINCT StartTime
          FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
         WHERE products_id IN ('.$products_id_list.')
		   AND mpID = '.$_MagnaSession['mpID'].'
    ');
    if (1 == (int)MagnaDB::gi()->foundRows()) {
    	$StartTime = $StartTimeSet[0]['StartTime'];
    } else {
    	$StartTime = '';
    }
	$eBayPlusSettings = geteBayPlusSettings();
	$html .= '/>'.ML_EBAY_BESTPRICE_YES_NO.'
				</td>
				<td class="info">'.ML_EBAY_BESTPRICE.'</td>
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_PLUS_SHORT.'</th>
				<td class="input">
                    <input type="checkbox" name="plus" id="plus" ';
    $products_id_list = '';
    foreach ($data as $item) {
    	$products_id_list .= ', '.$item['products_id'];
    }
    $products_id_list = trim($products_id_list, ', ');
    $plusSet = MagnaDB::gi()->fetchArray('
    	SELECT SQL_CALC_FOUND_ROWS DISTINCT eBayPlus
          FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
         WHERE products_id IN ('.$products_id_list.')
		   AND mpID = '.$_MagnaSession['mpID'].'
    ');
	if (    ('false' == $eBayPlusSettings['eBayPlus'])
	     || ( false  == $eBayPlusSettings['eBayPlus'])) {
		$html .= ' disabled="disabled"  style="background-color:#dfdfdf" ';
	} else {
    	if (1 == (int)MagnaDB::gi()->foundRows()) {
	        if ('1' == $plusSet[0]['eBayPlus']) {
	            $html .= ' checked="checked" ';
	        }
	    } else {
	        if (getDBConfigValue(array('ebay.plus', 'val'), $_MagnaSession['mpID'])) {
	            $html .= ' checked="checked" ';
	        }
	    }
    }
	$html .= '/>'.ML_EBAY_PLUS_YES_NO.'
				</td>
				<td class="info">'.ML_EBAY_PLUS.'</td>
			</tr>';
	$html .= '<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
						<th>'.ML_EBAY_START_TIME_SHORT.'</th>
						<td class="input">
							'.renderDateTimePicker('startTime', $StartTime, true).'
						</td>
						<td class="info">'.ML_EBAY_START_TIME.'<input type="hidden" value="'.$data[0]['products_id'].'" name="pID" id="pID"/></td>
					</tr>';
			if (count($data) > 1) {
				if (MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION)) {
					# Subtitel aus products_short_description (in OsCommerce nicht vorhanden)
					$html .= '
					<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
						<th>'.ML_EBAY_SUBTITLE.'</th>
						<td class="input">
							<input type="checkbox" name="enableSubtitle" id="enableSubtitle" ';
				}
				if (
					MagnaDB::gi()->fetchOne('
						SELECT count(*)
						  FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
						 WHERE products_id IN ('.$products_id_list.')
							   AND Subtitle <> \'\'
							   AND mpID = '.$_MagnaSession['mpID'].'
					') == count($data)
				) {
					$html .= ' checked="checked" ';
				}
				$html .= '/>'.ML_EBAY_LABEL_USE_SUBTITLE_YES_NO.'
						</td>
						<td class="info">'.ML_EBAY_SUBTITLE_MAX_55_CHARS.'<span style="color:red;"> '.ML_EBAY_CAUSES_COSTS.'</span></td>
					</tr>
				</tbody>
				<tbody>
					<tr class="headline">
						<td colspan="3"><h4>'.ML_EBAY_PICTURE_SETTINGS.'</h4></td>
					</tr>'
					.ebayGalleryTypeHtml(getDBConfigValue('ebay.gallery.type', $_MagnaSession['mpID'], 'Gallery'), $oddEven)
					;
					$aProduct_ids = array();
					$aVarDims = array();
					foreach ($data as $d) {
						$aProduct_ids[] = $d['products_id'];
						if (    array_key_exists('VariationDimensionForPictures', $d)
						     && !in_array($d['VariationDimensionForPictures'], $aVarDims)) {
							$aVarDims[] = $d['VariationDimensionForPictures'];
						}
					}
					if (count($aVarDims) == 1) {
						$selected_property = array_pop($aVarDims);
					} else {
						$selected_property = false;
					}
					$html .= ebayPicturePackPropertiesListHtml($aProduct_ids, $selected_property );
			}
			$html .= '
					<tr class="spacer">
						<td colspan="3">&nbsp;</td>
					</tr>
				</tbody>
				<tbody>
					<tr class="headline">
						<td colspan="3"><h4>'.ML_EBAY_CATEGORY.'</h4></td>
					</tr>
					<tr class="even">
						<th>'.ML_EBAY_CATEGORY.'</th>
						<td class="input">
							<table class="inner middle fullwidth categorySelect"><tbody>
								<tr>
									<td class="label">'.ML_EBAY_PRIMARY_CATEGORY.':</td>
									<td>
										<div class="ebayCatVisual" id="PrimaryCategoryVisual">
											<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">';

			$html .= eBayRenderCategoryOptions('topPrimaryCategory', $PrimaryCategory, $PrimaryCategoryName);
			$html .= '
											</select>
										</div>
									</td>
									<td class="buttons">
										<input type="hidden" id="PrimaryPreselectedValues" name="PrimaryPreselectedValues" '.(
											(!empty($PrimaryPreselectedValues))
												? 'value=\''.str_replace("'","%27", $PrimaryPreselectedValues).'\''
												: ''
										).' />
										<input class="fullWidth ml-button smallmargin mlbtn-action" type="button" value="'.ML_EBAY_CHOOSE.'" id="selectPrimaryCategory"/>
									</td>
								</tr>
								<tr>
									<td class="label">'.ML_EBAY_SECONDARY_CATEGORY.':</td>
									<td>
										<div class="ebayCatVisual" id="SecondaryCategoryVisual">
											<select id="SecondaryCategory" name="SecondaryCategory" style="width:100%">';
			$html .= eBayRenderCategoryOptions('topSecondaryCategory', $SecondaryCategory, $SecondaryCategoryName);
			$html .= '
											</select>
										</div>
									</td>
									<td class="buttons">
										<input type="hidden" id="SecondaryPreselectedValues" name="SecondaryPreselectedValues" '.(
											(!empty($SecondaryPreselectedValues))
												? 'value=\''.str_replace("'","%27", $SecondaryPreselectedValues).'\''
												: ''
										).' />
										<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_EBAY_CHOOSE.'" id="selectSecondaryCategory"/>
									</td>
								</tr>';
			if ($hasStore) {
				$html .= '
								<tr>
									<td class="label">'.ML_EBAY_STORE_CATEGORY.':</td>
									<td>
										<div class="ebayCatVisual" id="StoreCategoryVisual">
											<select id="StoreCategory" name="StoreCategory" style="width:100%">';
				$html .= eBayRenderCategoryOptions('topStoreCategory1', $StoreCategory, $StoreCategoryName);
				$html .= '
											</select>
										</div>
									</td>
									<td class="buttons">
										<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_EBAY_CHOOSE.'" id="selectStoreCategory"/>
									</td>
								</tr>
								<tr>
									<td class="label">'.ML_EBAY_SECONDARY_STORE_CATEGORY.':</td>
									<td>
										<div class="ebayCatVisual" id="StoreCategory2Visual">
											<select id="StoreCategory2" name="StoreCategory2" style="width:100%">';
				$html .= eBayRenderCategoryOptions('topStoreCategory2', $StoreCategory2, $StoreCategory2Name);
				$html .= '
											</select>
										</div>
									</td>
									<td class="buttons">
										<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_EBAY_CHOOSE.'" id="selectStoreCategory2"/>
									</td>
								</tr>';
			}

			$html .= '
								<tr><td colspan=3>
									<div id="noteVariationsEnabled" name="noteVariationsEnabled">';
			if (is_numeric($PrimaryCategory) && getDBConfigValue(array($_MagnaSession['currentPlatform'].'.usevariations', 'val'), $_MagnaSession['mpID'], true)) {
				#if (VariationsEnabled($PrimaryCategory))
				if (    (    isset($SecondaryCategory) 
				          && is_numeric($SecondaryCategory)
				          && VariationsEnabled($PrimaryCategory)
				          && VariationsEnabled($SecondaryCategory))
				     || (    (!isset($SecondaryCategory) || empty($SecondaryCategory))
				          && VariationsEnabled($PrimaryCategory)))
					$html .= '<br />'.ML_EBAY_NOTE_VARIATIONS_ENABLED;
				else
					$html .= '<br />'.ML_EBAY_NOTE_VARIATIONS_DISABLED;
			}
			$html .= '
								<tr><td colspan=3>
									<div id="noteProductRequired" name="noteProductRequired">';
			if (is_numeric($PrimaryCategory)) {
				if (ProductRequired($PrimaryCategory)) {
#echo "<pre>LINE = ".__LINE__."\nePidsFilled = $ePidsFilled\n</pre>\n";
					if ($ePidsFilled)
						$html .= '<br />'.ML_EBAY_NOTE_PRODUCT_MATCHED;
					else
						$html .= '<br />'.ML_EBAY_NOTE_PRODUCT_REQUIRED_SHORT.'<div class="desc" id="desc_1" title="Infos"><span>'.ML_EBAY_NOTE_PRODUCT_REQUIRED.'</span></div>';
				}
			}
			$html .= '
									</div>
								</td></tr>';

			$mpAttributeTitle = str_replace('%marketplace%', 'eBay', ML_GENERAL_VARMATCH_MP_ATTRIBUTE);
            $mpOptionalAttributeTitle = str_replace('%marketplace%', 'eBay', ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);
            $mpCustomAttributeTitle = str_replace('%marketplace%', 'eBay', ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE);

			$html .= '
							</tbody></table>
						</td>
						<td class="info">'.ML_EBAY_CATEGORY_DESC.'</td>
					</tr>
					<tr class="spacer">
						<td colspan="3">&nbsp;</td>
					</tr>
				</tbody>
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
				</tbody>
				<tbody>
					<tr class="headline">
						<td colspan="3"><h4>'.ML_GENERIC_SHIPPING.'</h4></td>
					</tr>';
		if ($blBusinessPoliciesSet) {
			$html .= '
		<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
			<th>'.ML_EBAY_LABEL_BUSINESS_POLICIES_SHIPPING.'</th>
			<td class="input">
				<select name="shippingsellerprofile" id="shippingsellerprofile">';
				$shippingProfiles = geteBaySellerShippingProfiles();
		foreach($shippingProfiles as $profileID => $profileName) {
				$isSelected = ($profileID == $prefilledSellerProfiles['Shipping']? 'selected' : '');
				$html .= '
						<option '.$isSelected.' value="'.$profileID.'">'.$profileName."</option>\n";
		}
		$html .= '
				</select>
			</td>
			<td class="info">'.ML_EBAY_HINT_BUSINESS_POLICIES_SHIPPING.'</td>
		</tr>';
		}
			$html .= '
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_EBAY_SHIPPING_DOMESTIC.'</th>
					<td class="input">';

			$tmpURL = $_url;
			$tmpURL['where'] = 'prepareView';
			if ($blBusinessPoliciesSet) {
				$sellerProfileContents = getDBConfigValue('ebay.sellerprofile.contents', $_MagnaSession['mpID']);
				$html .= '<table id="ebay_default_shipping_local" class="shippingDetails inlinetable nowrap autoWidth"><tbody>'
				."\n".renderReadonlyShippingDetails($sellerProfileContents['Shipping'][$prefilledSellerProfiles['Shipping']]['shipping.local'], false)
				."\n</tbody></table>\n";
			} else {
				if (isset($prefilledShippingDetails)) {
					$prefilledShippingDetailsArray = json_decode(fixBrokenJsonUmlauts($prefilledShippingDetails), true);
					$shipProc = new eBayShippingDetailsProcessor(array(
						'key' => 'ebay.default.shipping.local',
						'content' => $prefilledShippingDetailsArray['ShippingServiceOptions'],
					), 'ebay.default.shipping.local', $tmpURL);
				} else {
					$shipProc = new eBayShippingDetailsProcessor(array(
						'key' => 'ebay.default.shipping.local',
					), '', $tmpURL);
				}
				$html .= $shipProc->process();
			}

			$html .= '
					</td>
					<td class="info">
						'.ML_EBAY_SHIPPING_DOMESTIC_DESC.'<br /><br />
						Angabe "=GEWICHT"<br />
						bei den Versandkosten
						setzt diese gleich dem Artikelgewicht.
					</td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_EBAY_DISPATCH_TIME.'</th>
					<td class="input">
						<select name="dispatchTime" id="dispatchTime" '.($blBusinessPoliciesSet? 'disabled="disabled" style="background-color:#dfdfdf" ':'').'>';
			$aDispatchTimeMaxAllowedValues = array (0,1,2,3,4,5,6,7,10,15,20,30,40);
			foreach($aDispatchTimeMaxAllowedValues as $days) {
				$isSelected = ($days == $prefilledDispatchTimeMax? 'selected' : '');
				switch ($days) {
					case (0): 
						$daysText = ML_EBAY_DISPATCH_ON_SAME_DAY;
						break;
					case (1):
						$daysText = ML_EBAY_DISPATCH_ONE_DAY;
						break;
					default :
						$daysText = $days.' '.ML_DAYS;
						break;
				}
				$html .= '
							<option '.$isSelected.' value="'.$days.'">'.$daysText."</option>\n";

			}
			$html .= '
						</select>
					</td>
					<td class="info">
						&nbsp;
					</td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_EBAY_SHIPPING_PROFILE.'</th>
					<td class="input">
						<select name="localProfile" id="localProfile" '.($blBusinessPoliciesSet? 'disabled="disabled" style="background-color:#dfdfdf" ':'').'>';
			$shippingProfiles = geteBayShippingDiscountProfiles();
			if ($blBusinessPoliciesSet) {
				$defaultLocalProfile = $sellerProfileContents['Shipping'][$prefilledSellerProfiles['Shipping']]['shippingprofile.local'];
			} else if (isset($prefilledShippingDetailsArray)
				&& array_key_exists('LocalProfile', $prefilledShippingDetailsArray)) {
				$defaultLocalProfile = $prefilledShippingDetailsArray['LocalProfile'];
			} else {
				$defaultLocalProfile = getDBConfigValue('ebay.default.shippingprofile.local',$_MagnaSession['mpID'], 0);
			}
			foreach($shippingProfiles as $profileID => $profileName) {
					$isSelected = ($profileID == $defaultLocalProfile? 'selected' : '');
					$html .= '
									<option '.$isSelected.' value="'.$profileID.'">'.$profileName."</option>\n";
			}
			$html .= '
						</select>
						<input type="checkbox" name="localPromotionalDiscount" id="localPromotionalDiscount" ';
			if ($blBusinessPoliciesSet) {
				if ('{"val":true}' == $sellerProfileContents['Shipping'][$prefilledSellerProfiles['Shipping']]['shippingdiscount.local']) {
					$html .= ' checked="checked" ';
				}
			} else if (isset($prefilledShippingDetailsArray)
			    && array_key_exists('LocalPromotionalDiscount', $prefilledShippingDetailsArray)) {
				if ('true' == $prefilledShippingDetailsArray['LocalPromotionalDiscount']) {
					$html .= ' checked="checked" ';
				}
			} else if (getDBConfigValue(array('ebay.shippingdiscount.local', 'val'), $_MagnaSession['mpID'])) {
				$html .= ' checked="checked" ';
			}
			if ($blBusinessPoliciesSet) {
				$html .= ' disabled="disabled"  style="background-color:#dfdfdf" ';
			}
			$html .= '/>'.ML_EBAY_SHIPPING_DISCOUNT.'
			</td>
			<td class="info">
				&nbsp;
			</td>
		</tr>
		<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
			<th>'.ML_EBAY_SHIPPING_INTL_OPTIONAL.'</th>
			<td class="input">';

		if ($blBusinessPoliciesSet) {
			$html .= '<table id="ebay_default_shipping_international" class="shippingDetails inlinetable nowrap autoWidth"><tbody>'
			."\n".renderReadonlyShippingDetails($sellerProfileContents['Shipping'][$prefilledSellerProfiles['Shipping']]['shipping.international'], true)
			."\n</tbody></table>\n";
		} else {
			if (isset($prefilledShippingDetails) && isset($prefilledShippingDetailsArray['InternationalShippingServiceOption'])) {
				$shipProc = new eBayShippingDetailsProcessor(array(
					'key' => 'ebay.default.shipping.international',
					'content' => $prefilledShippingDetailsArray['InternationalShippingServiceOption'],
				), 'ebay.default.shipping.international', $tmpURL);
			} else if (isset($prefilledShippingDetails) && !isset($prefilledShippingDetailsArray['InternationalShippingServiceOption'])) {
				$shipProc = new eBayShippingDetailsProcessor(array(
					'key' => 'ebay.default.shipping.international',
					'content' => array (array('ShippingService' => '', 'ShipToLocation' => 'None')),
				), 'ebay.default.shipping.international', $tmpURL);
			} else {
				$shipProc = new eBayShippingDetailsProcessor(array(
					'key' => 'ebay.default.shipping.international',
				), '', $tmpURL);
			}
			$html .= $shipProc->process();
		}

		$html .= '
				</td>
				<td class="info">'.ML_EBAY_SHIPPING_INTL_DESC.'</td>
			</tr>
		<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
			<th>'.ML_EBAY_SHIPPING_PROFILE.'</th>
			<td class="input">
				<select name="internationalProfile" id="internationalProfile" '.($blBusinessPoliciesSet? 'disabled="disabled" style="background-color:#dfdfdf" ':'').'>';
				if ($blBusinessPoliciesSet) {
					$defaultInternationalProfile = $sellerProfileContents['Shipping'][$prefilledSellerProfiles['Shipping']]['shippingprofile.international'];
				} else if (isset($prefilledShippingDetailsArray)
					&& array_key_exists('InternationalProfile', $prefilledShippingDetailsArray)) {
					$defaultInternationalProfile = $prefilledShippingDetailsArray['InternationalProfile'];
				} else {
					$defaultInternationalProfile = getDBConfigValue('ebay.default.shippingprofile.international',$_MagnaSession['mpID'], 0);
				}
		foreach($shippingProfiles as $profileID => $profileName) {
				$isSelected = ($profileID == $defaultInternationalProfile? 'selected' : '');
				$html .= '
						<option '.$isSelected.' value="'.$profileID.'">'.$profileName."</option>\n";
		}
		$html .= '
				</select>
				<input type="checkbox" name="internationalPromotionalDiscount" id="internationalPromotionalDiscount" ';
		if ($blBusinessPoliciesSet) {
			if ('{"val":true}' == $sellerProfileContents['Shipping'][$prefilledSellerProfiles['Shipping']]['shippingdiscount.international']) {
				$html .= ' checked="checked" ';
			}
		} else if (isset($prefilledShippingDetailsArray)
		    && array_key_exists('InternationalPromotionalDiscount', $prefilledShippingDetailsArray)) {
			if ('true' == $prefilledShippingDetailsArray['InternationalPromotionalDiscount']) {
				$html .= ' checked="checked" ';
			}
		} else if (getDBConfigValue(array('ebay.shippingdiscount.international', 'val'), $_MagnaSession['mpID'])) {
			$html .= ' checked="checked" ';
		}
		if ($blBusinessPoliciesSet) {
			$html .= ' disabled="disabled style="background-color:#dfdfdf" " ';
		}
				$html .= '/>'.ML_EBAY_SHIPPING_DISCOUNT.'
			</td>
			<td class="info">
				&nbsp;
			</td>
		</tr>';
		$html .= '
		<tr class="spacer">
			<td colspan="3">&nbsp;</td>
		</tr>
		</tbody>';
	ob_start();
?>
<style>
table.attributesTable table.inner,
table.attributesTable table.inlinetable {
	border: none;
	border-spacing: 0px;
	border-collapse: collapse;
}
table.attributesTable td.fullwidth {
	width: 100%;
}
table.attributesTable table.fullwidth {
	width: 100%;
}
table.attributesTable table.inner tr td {
	border: none;
	padding: 1px 2px;
}
table.attributesTable table.inner.middle tr td {
	vertical-align: middle;
}
table.attributesTable table.categorySelect tr td.ml-buttons {
	width: 6em;
}
table.attributesTable table.categorySelect tr td.label {
	width: 1em;
	white-space: nowrap;
}
table.attributesTable table.inlinetable tr td {
	border: none;
	padding: 0;
}
table.attributesTable table.shippingDetails {
	margin-bottom: 0.7em;
}
table.attributesTable table.shippingDetails:last-child {
	margin-bottom: 0;
}
table.lightstlye {
	border-collapse: collapse;
}
table.lightstlye td {
	border-left: none;
	border-right: none;
	border-top: 1px dashed #ccc;
	border-bottom: 1px dashed #ccc;
}
table.lightstlye tr:first-child td {
	border-top: none;
}
table.lightstlye tr:last-child td {
	border-bottom: none;
}
.line15 {
	line-height: 1.5em;
}
.iceCrystal {
	margin-left: 3px;
}
div.ebayCatVisual {
	display: inline-block;
	width: 100%;
	height: 1.5em;
	line-height: 1.5em;
	background: #fff;
	color: #000;
	border: 1px solid #999;
}
</style>
<script type="text/javascript">/*<![CDATA[*/

function getListingDurations() {
	var preselectedDuration='<?php echo $ListingDuration; ?>';
	if ($('#ListingType').val() != '<?php echo $ListingType; ?>') {
		if ('Chinese' == $('#ListingType').val()) {
			preselectedDuration='<?php echo getDBConfigValue('ebay.chinese.duration', $_MagnaSession['mpID']); ?>';
		} else {
			preselectedDuration='<?php echo getDBConfigValue('ebay.fixed.duration'  , $_MagnaSession['mpID']); ?>';
		}
	}
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($_url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'getListingDurations',
			'ListingType': $('#ListingType').val(),
			'preselected': preselectedDuration
		},
		success: function(data) {
			$('#ListingDuration').html(data);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function updatePrice() {
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($_url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'makePrice',
			'pID': $('#pID').val(),
			'ListingType': $('#ListingType').val()
		},
		success: function(data) {
			$('#Price').val(data);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function toggleChineseAuction() {
	$('#bidPriceLabel').css({'display': 'inline'});
	$('#buyNowPriceLabel').css({'display': 'none'});
	$('#TrFixedPrice').css({'display': 'none'});
	$('#TrStrikePrice').css({'display': 'none'});
	$('#chinesePrice').css({'display': 'table-row'});
	$('#chinesePrice2').css({'display': 'table-row'});
}

function toggleFixedPriceAuction() {
	$('#bidPriceLabel').css({'display': 'none'});
	$('#buyNowPriceLabel').css({'display': 'inline'});
	$('#TrFixedPrice').css({'display': 'table-row'});
	updateStrikePrice();
	$('#chinesePrice').css({'display': 'none'});
	$('#chinesePrice2').css({'display': 'none'});
}

function toggleConditionDescriptorsVisibility() {
	if ($('#ebay_ConditionDescriptors').html().length < 20) {
		$('#ebay_ConditionDescriptors_tr').hide();
	} else {
		$('#ebay_ConditionDescriptors_tr').show();
	}
	window.setTimeout('toggleConditionDescriptorsVisibility()', 1000);
}

function onListingTypeChange() {
	getListingDurations();
	if (typeof($('#Price')) != "undefined") {
		updatePrice();
	}
	if ('Chinese' == $('#ListingType').val()) {
		toggleChineseAuction();
	} else {
		toggleFixedPriceAuction();
	}
}

function updateStrikePrice() {
	if ($('#UseStrikePrice').length <= 0) {
		return;
	}
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($_url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'makePriceByStrikePriceSettings',
			'pID': $('#pID').val(),
			'UseStrikePrice': $('#UseStrikePrice').attr('checked'),
			'StrikePriceKind': $('#StrikePriceKind').val(),
			'StrikePriceGroup': $('#StrikePriceGroup').val()
		},
		success: function(data) {
			$('#showCalcPrice').html(data['price']);
			$('#showStrikePrice').html(data['strikePrice']);
			if (data['strikePrice'] == '0' || ($('input[id="UseStrikePrice"]').val() == false)) {
				$('#TrStrikePrice').css({'display': 'none'});
			} else {
				$('#TrStrikePrice').css({'display': 'table-row'});
			}
		},
		error: function() {
		},
		dataType: 'json'
	});
}

$('input[id="UseStrikePrice"]').change(function() {
    updateStrikePrice();
});

$('select[id="StrikePriceGroup"]').change(function() {
    $('select[id="StrikePriceGroup"]').data('ml-oldvalue', $('select[id="StrikePriceGroup"]').val());
    updateStrikePrice();
});

$('input[id="StrikePriceIsUvp"]').change(function() {
    $('input[id="StrikePriceIsUvp"]').data('ml-oldvalue', $('input[id="StrikePriceIsUvp"]').prop('checked'));
});


$('select[id="StrikePriceKind"]').change(function() {
    var sel=$(this);
    if(sel.val() == 'CustomerGroup') {
        $('select[id="StrikePriceGroup"]').val($('select[id="StrikePriceGroup"]').data('ml-oldvalue'));
        $('input[id="StrikePriceIsUvp"]').prop('checked', $('input[id="StrikePriceIsUvp"]').data('ml-oldvalue')); 
        $('select[id="StrikePriceGroup"]').prop('disabled', false);
        $('select[id="StrikePriceGroup"]').css('background-color','#fff');
        $('input[id="StrikePriceIsUvp"]').prop('disabled', false);
        $('input[id="StrikePriceIsUvp"]').css('background-color','#fff');
    } else {
        $('select[id="StrikePriceGroup"]').val('<?php echo ML_LABEL_DONT_USE;?>');
        $('input[id="StrikePriceIsUvp"]').prop('checked', false);
        $('select[id="StrikePriceGroup"]').prop('disabled', true);
        $('select[id="StrikePriceGroup"]').css('background-color','#dfdfdf');
        $('input[id="StrikePriceIsUvp"]').prop('disabled', true);
        $('input[id="StrikePriceIsUvp"]').css('background-color','#dfdfdf');
    }
    if (sel.val() == 'ManufacturersPrice') {
        // don't change ml-oldvalue here
        var ov=$('input[id="StrikePriceIsUvp"]').data('ml-oldvalue');
        $('input[id="StrikePriceIsUvp"]').prop('checked', true);
        $('input[id="StrikePriceIsUvp"]').data('ml-oldvalue', ov);
    }
    updateStrikePrice();
});

$(document).ready(function() {

    // strike through prices
    $('select[id="StrikePriceGroup"]').data('ml-oldvalue', $('select[id="StrikePriceGroup"]').val());
    $('input[id="StrikePriceIsUvp"]').data('ml-oldvalue', $('input[id="StrikePriceIsUvp"]').prop('checked'));
    if ($('select[id="StrikePriceKind"]').val() != 'CustomerGroup') {
        $('select[id="StrikePriceGroup"]').val('<?php echo ML_LABEL_DONT_USE;?>');
        $('input[id="StrikePriceIsUvp"]').prop('checked', false);
        $('select[id="StrikePriceGroup"]').prop('disabled', true);
        $('select[id="StrikePriceGroup"]').css('background-color','#dfdfdf');
        $('input[id="StrikePriceIsUvp"]').prop('disabled', true);
        $('input[id="StrikePriceIsUvp"]').css('background-color','#dfdfdf');
    }

	$('#PrimaryCategoryVisual > select').change(function() {
		var cID = this.value;
		if (cID != '') {
			generateEbayCategoryPath(cID, $('#PrimaryCategoryVisual'));
			if ($('#SecondaryCategory').val() != '') {
				VariationsEnabled2(cID, $('#SecondaryCategory').val(), $('#noteVariationsEnabled'));
			} else {
				VariationsEnabled(cID, $('#noteVariationsEnabled'));
			}
			ProductRequired(cID, $('#noteProductRequired'));
			GetConditionValues(cID, $('#ebay_Condition'), <?php if(isset($defaultConditionID))
			echo $defaultConditionID; else echo '1000'; ?>);
			GetConditionDescriptors(cID, <?php if(isset($defaultConditionID)) echo $defaultConditionID; else echo '1000'; ?>, $('#ebay_ConditionDescriptors'), <?php if(isset($ConditionDescriptors)) echo $ConditionDescriptors; else echo ''; ?>);
			toggleConditionDescriptorsVisibility();
			return true;
		}
	});

	$('#SecondaryCategoryVisual > select').change(function() {
		var cID = this.value;
		if (cID != '') {
			$('#SecondaryCategory').val(cID);
			generateEbayCategoryPath(cID, $('#SecondaryCategoryVisual'));
			if ($('#PrimaryCategory').val() != '') {
				VariationsEnabled2($('#PrimaryCategory').val(), cID, $('#noteVariationsEnabled'));
			} else {
				VariationsEnabled(cID, $('#noteVariationsEnabled'));
			}
			return true;
		}
	});
	$('#PrimaryCategoryVisual > select').trigger('change');
	$('#SecondaryCategoryVisual > select').trigger('change');

	$('#ebay_Condition').on('change', '#ConditionID', function() {
		var iConditionID = this.value;
		var iCategoryID = $('#PrimaryCategory').val();
		GetConditionDescriptors(iCategoryID, iConditionID, $('#ebay_ConditionDescriptors'), <?php if(isset($ConditionDescriptors)) echo $ConditionDescriptors; else echo ''; ?>);
		toggleConditionDescriptorsVisibility();
	});
	$('#ebay_Condition > select').trigger('change');

	$('#selectPrimaryCategory').click(function() {
		startCategorySelector(function(cID) {
			$('#PrimaryCategory').val(cID);
			generateEbayCategoryPath(cID, $('#PrimaryCategoryVisual'));
			if ($('#SecondaryCategory').val() != '') {
				VariationsEnabled2(cID, $('#SecondaryCategory').val(), $('#noteVariationsEnabled'));
			} else {
				VariationsEnabled(cID, $('#noteVariationsEnabled'));
			}
			ProductRequired(cID, $('#noteProductRequired'));
			GetConditionValues(cID, $('#ebay_Condition'), <?php if(isset($defaultConditionID)) echo $defaultConditionID; else echo '1000'; ?>);
			GetConditionDescriptors(cID, <?php if(isset($defaultConditionID)) echo $defaultConditionID; else echo '1000'; ?>, $('#ebay_ConditionDescriptors'), <?php if(isset($ConditionDescriptors)) echo $ConditionDescriptors; else echo ''; ?>);
			toggleConditionDescriptorsVisibility();
			return true;
		}, 'eBay');
	});
	$('#selectSecondaryCategory').click(function() {
		startCategorySelector(function(cID) {
			$('#SecondaryCategory').val(cID);
			generateEbayCategoryPath(cID, $('#SecondaryCategoryVisual'));
			if ($('#PrimaryCategory').val() != '') {
				VariationsEnabled2($('#PrimaryCategory').val(), cID, $('#noteVariationsEnabled'));
			} else {
				VariationsEnabled(cID, $('#noteVariationsEnabled'));
			}
		}, 'eBay');
	});
	$('#selectStoreCategory').click(function() {
		startCategorySelector(function(cID) {
			$('#StoreCategory').val(cID);
			generateEbayCategoryPath(cID, $('#StoreCategoryVisual'));
		}, 'store');
	});
	$('#selectStoreCategory2').click(function() {
		startCategorySelector(function(cID) {
			$('#StoreCategory2').val(cID);
			generateEbayCategoryPath(cID, $('#StoreCategory2Visual'));
		}, 'store');
	});

	$('#ListingType').change(onListingTypeChange);
	onListingTypeChange();

	$('#prepareForm').on('submit', function () {
		jQuery.blockUI(blockUILoading);
		$('select[id="PaymentMethods"]').prop('disabled', false);
		$('select[id="dispatchTime"]').prop('disabled', false);
		$('select[id="localProfile"]').prop('disabled', false);
		$('select[id="localPromotionalDiscount"]').prop('disabled', false);
		$('select[id="internationalProfile"]').prop('disabled', false);
		$('select[id="internationalPromotionalDiscount"]').prop('disabled', false);
	});
     $('select[id="GalleryType"]').data('ml-oldvalue', $('select[id="GalleryType"]').val());
});
$('select[id="GalleryType"]').change(function() {
      var sel=$(this);
      if (sel.val() != 'Plus') {
        sel.data('ml-oldvalue', sel.val());
        return true;
      }
      sel.val(sel.data('ml-oldvalue'));
		$('<div></div>').html('<?php echo ML_TEXT_WARNING_EBAY_GALLERY_PLUS_COSTS ?>').jDialog({
			title: '<?php echo ML_TITLE_EBAY_WARNING_GALLERY_PLUS_COST ?>',
			buttons: {
				'<?php echo ML_BUTTON_LABEL_NO; ?>': function() {
					jQuery(this).dialog('close');
				},
				'<?php echo ML_BUTTON_LABEL_YES; ?>': function() {
					sel.data('ml-oldvalue', 'Plus');
					sel.val('Plus');
					jQuery(this).dialog('close');
				}
			}
		})
});
$('select[id="paymentsellerprofile"]').change(function() {
	 var sel=$(this);
	 jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($_url, array('where' => 'prepareView', 'kind' => 'ajax'), true)?>',
		data: {
			'action': 'GetSellerProfileData',
			'value': sel.val()
		},
		dataType: 'json',
		success: function(data) {
			$('select[id="PaymentMethods"]').val(data['paymentmethod']);
		}
	 });
}).trigger('change');
$('select[id="shippingsellerprofile"]').change(function() {
	 var sel=$(this);
	 jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($_url, array('where' => 'prepareView', 'kind' => 'ajax'), true)?>',
		data: {
			'action': 'GetSellerProfileData',
			'value': sel.val()
		},
		dataType: 'json',
		success: function(data) {
			$('select[id="dispatchTime"]').val(data['DispatchTimeMax']);
			$('#ebay_default_shipping_local').html(data['ebay_default_shipping_local']);
			$('#ebay_default_shipping_international').html(data['ebay_default_shipping_international']);
			if(data['ebay_default_shipping_international'].length < 1) {
				$('#ebay_default_shipping_international').parent().parent().hide();
			}
			if ((typeof data['shippingprofile.local'] == "undefined") && (typeof data['shippingdiscount.local'] == "undefined")) {
				$('select[id="localProfile"]').parent().parent().hide();
			} else {
				$('select[id="localProfile"]').val(data['shippingprofile.local']);
				$('input[id="localPromotionalDiscount"]').prop('checked', ('{"val":true}' == data['shippingdiscount.local']));
			}
			if ((typeof data['shippingprofile.international'] == "undefined") && (typeof data['shippingdiscount.international'] == "undefined")) {
				$('select[id="internationalProfile"]').parent().parent().hide();
			} else {
				$('select[id="internationalProfile"]').val(data['shippingprofile.international']);
				$('input[id="internationalPromotionalDiscount"]').prop('checked', ('{"val":true}' == data['shippingdiscount.international']));
			}
		}
	 });
}).trigger('change');
$(document).ready(function() {
	if ($('input[id="usemobile"]').val()=='true') {
	var interval = window.setInterval(function() {
		if (
			typeof tinyMCE === "undefined"
			||
			(
				$('#sMobileDescription').find('.mce-tinymce.mce-container').length > 0
				&& $('#sDescription').find('.mce-tinymce.mce-container').length > 0
			)
		) {
			$('#mobileTemplateButton').on('click', function() {
				$('#sDescButtonRowMobile').show();
				$('#sDescButtonRowStandard').hide();
				$('#sMobileDescription').show();
				$('#sDescription').hide();
				$('#sDescInfoColMobile').show();
				$('#sDescInfoColStandard').hide();
			});
			$('#standardTemplateButton').on('click', function() {
				$('#sDescButtonRowMobile').hide();
				$('#sDescButtonRowStandard').show();
				$('#sMobileDescription').hide();
				$('#sDescription').show();
				$('#sDescInfoColMobile').hide();
				$('#sDescInfoColStandard').show();
			}).trigger('click');
			window.clearInterval(interval);
		}
	}, 300);
	} else {
				$('#sDescButtonRowMobile').hide();
				$('#sDescButtonRowStandard').hide();
				$('#sMobileDescription').hide();
				$('#sDescription').show();
				$('#sDescInfoColMobile').hide();
				$('#sDescInfoColStandard').show();
	}
});
/*]]>*/</script>
	<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
	<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/marketplaces/ebay/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
	<script type="text/javascript">
		/*<![CDATA[*/
		var ml_vm_config = {
			url: '<?php echo toURL($_url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
			viewName: 'prepareView',
			secondaryCategory: true,
			formName: '#prepareForm',
			handleCategoryChange: false,
			i18n: <?php echo json_encode(EbayHelper::gi()->getVarMatchTranslations());?>,
			shopVariations : <?php echo json_encode(EbayHelper::gi()->getShopVariations()); ?>
		};
		/*]]>*/
	</script>
<?php
	$html .= ob_get_contents();
	ob_end_clean();

	return $html;
}

function renderPrepareView($data) {
	global $_url;
	global $_MagnaSession; // for hook
	/* {Hook} "EbayPrepareView_renderPrepareView": Is called before the data of the product in <code>$data</code> will shown.
		Usefull to manipulate some of the data.
		Variables that can be used:
		<ul>
			<li>$data: The data of a product for the preparation</li>
			<li>$_MagnaSession: magna session data (marketplace, mpID etc.)</li>
		</ul>
	*/
	if (($hp = magnaContribVerify('EbayPrepareView_renderPrepareView', 1)) !== false) {
		require($hp);
	}

	/**
	 * Check ob einer oder mehrere Artikel
	 */
	$prepareView = (1 == count($data)) ? 'single' : 'multiple';

	if (empty($data)) {
		return '<p class="errorBox">
		<span class="error bold larger">'.ML_ERROR_LABEL.':</span>
		'.ML_EBAY_ERROR_NO_SUITABLE_ITEMS_CHECK_LANGUAGE.'
		</p>';
	}


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

/*]]>*/</script><?php
	$renderedView = ob_get_clean();

	#if ($IsMatching) $_url['view'] = 'match';
	if (function_exists('thisIsMatching')) {
		$_murl = $_url;;
		$_murl['view'] = 'match';
	} else {
		$_murl = $_url;;
	}
#echo print_m($_murl, __LINE__.' $_url');
#echo print_m(toURL($_murl), __LINE__.' toURL($_url)');

	$renderedView .= '
		<form method="post" id="prepareForm" action="'.toURL($_murl).'">
			<table class="attributesTable">';
	if ('single' == $prepareView) {
		$renderedView .= renderSinglePrepareView($data);
	} else {
		$renderedView .= renderMultiPrepareView($data);
	}
	$renderedView .= '
			</table>
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody>
					<tr class="firstChild"><td>
						<table><tbody><tr>
							<td class="firstChild"></td>
							<td class="lastChild">'.'<input class="ml-button mlbtn-action" type="submit" name="savePrepareData" id="savePrepareData" value="'.ML_BUTTON_LABEL_SAVE_DATA.'"/>'.'</td>
						</tr></tbody></table>
					</td></tr>
				</tbody>
			</table>
		</form>';
	return $renderedView;
}

function ebayPicturePackPropertiesListHtml($products_id, $selected_property ){
	global $_MagnaSession;
	$html ='';
	if (getDBConfigValue(array('ebay.picturepack', 'val'), $_MagnaSession['mpID'])){
	// properties are the normal case (but only for Gambio). The other, Attribute images, need a custom extension.
	$blUseProperties = true;
	if (!MAGNA_GAMBIO_VARIATIONS && MagnaDb::gi()->columnExistsInTable('attributes_image', TABLE_PRODUCTS_ATTRIBUTES)) {
		$blUseProperties = false;
	}

		$aProductProperties = array();
		if (is_array($products_id)) {
			foreach ($products_id as $id) {
				if ($blUseProperties) {
					$aTmpProductProperties = MLProduct::gi()->getProductPropertiesByProductid($id);
				} else {
					$aTmpProductProperties = MLProduct::gi()->getProductAttributesByProductId($id);
				}
				// must use a loop, cos array_merge would change the keys
				if (!empty($aTmpProductProperties)) {
					foreach ($aTmpProductProperties as $no => $prop) {
						$aProductProperties[$no] = $prop;
					}
				}
			}
		} else {
			if ($blUseProperties) {
				$aProductProperties = MLProduct::gi()->getProductPropertiesByProductid($products_id);
			} else {
				$aProductProperties = MLProduct::gi()->getProductAttributesByProductId($products_id);
			}
		}
		if (false == $selected_property) {
			$selected_property = getDBConfigValue('ebay.variationdimensionforpictures', $_MagnaSession['mpID'], 99999);
		}
		if(!empty($aProductProperties)){
				$aProductProperties[-1] = ML_EBAY_DO_NOT_USE_VARIATION_PICS;
				$html .='<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_EBAY_PICTUREPACK_PROPERTY.'</th>
					<td class="input">
						<select name="VariationDimensionForPictures" id="VariationDimensionForPictures">';
			foreach ($aProductProperties as $id => $property){
				$html .= '<option value="'.$id.'" '.($selected_property == $id ? 'selected=selected ': '').'>'.$property.'</option>';
			}
					$html .= '</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>';
		}
	}
	return $html;
}

function ebayPictureUrlHtml($products_id, $selected_images,$oddEven ){
	global $_MagnaSession;
	$html ='<tr class="'.$oddEven.'">
		<th>'.ML_EBAY_PICTURE.'</th>
		<td class="input">
		';
	$imagelist = '';
	$checkboxlist = '';

	if (is_array($selected_images)){
		$imagePath = getDBConfigValue('ebay.imagepath',$_MagnaSession['mpID']);
		$aShopProductImage = array_unique(MLProduct::gi()->getAllImagesByProductsId($products_id));
		if (current($selected_images) == '__use_all__')
			$selected_images = $aShopProductImage;
		foreach($aShopProductImage as $iImgNo=>$image){
			$imagelist .=
			'<td class="image">
			    <label for="PictureURL_'.$image.'">
				    <img height="40" width="40" alt="'.$image.'" src="'.$imagePath.$image.'" />
			    </label>
			</td>';
			$checkboxlist .= '<td class="checkboxofimage"><input type="checkbox" id="PictureURL_'.$image.'" value="'.$image.'" '.(in_array($image,$selected_images)?' checked="checked"':'').' name="PictureURL['.$iImgNo.']"></td>';
		}
		$html .=
		'<table>
			<tr>'.$imagelist.'</tr>
			<tr>'.$checkboxlist.'</tr>
		</table>';
	} else {
		$html .= '<input type="text" class="fullwidth" value="'.$selected_images.'" name="PictureURL" id="PictureURL"/><br />&nbsp;<br />';

	}
	$html .= '</td>';
	if (getDBConfigValue(array('ebay.picturepack', 'val'), $_MagnaSession['mpID'])) {
		$html .= '
			<td class="info">'.ML_EBAY_MAIN_PICTURES;
	} else {
		$html .= '
			<td class="info">'.ML_EBAY_MAIN_PICTURE_COMPLETE_URL_PP_FOR_MORE;
	}
	$html .= '
			</td>
		</tr>';
	return $html;
}

function ebayGalleryTypeHtml($selected_type, $oddEven ){
	$html = '<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
		<th>'.ML_EBAY_GALLERY_PICTURES.'</th>'
		. '<td><select name="GalleryType" id="GalleryType">';
	foreach (array (
		'None'=>ML_GENERIC_NO_IMAGE,
		'Gallery'=>'Standard',
		'Plus' =>'Plus'
	) as $value => $name){
		$html .= '<option value="'.$value.'" '.(($selected_type == $value)?'selected=selected':'').'>'.$name.'</option>'."\n";
	}
	$html .= '</select></td><td class="info">'.ML_EBAY_ENABLE_GALLERY_PICTURES.'</td>';
	return $html;
}

function eBayVATHTML($data) {
    global $_MagnaSession;

    $VAT = getDBConfigValue('ebay.mwst', $_MagnaSession['mpID'], 0);
    if (isset($data[0])) {
        $firstVAT = $data[0]['mwst'];
        $blIsDifferent = false;
        foreach ($data as $item) {
            if ($firstVAT !== $item['mwst']) {
                $blIsDifferent = true;
                break;
            }
        }
        if (!$blIsDifferent) {
            $VAT = $firstVAT;
        }
    }
    return
        '<tbody>
			<tr class="headline">
				<td colspan="3"><h4>'.ML_EBAY_VAT.'</h4></td>
			</tr>
			<tr class="odd">
				<th>'.ML_EBAY_VAT.'</th>
				<td class="input">
					<input class="fullwidth" type="text" value="'.fixHTMLUTF8Entities($VAT, ENT_COMPAT).'" name="mwst" id="VAT"/>
				</td>
				<td class="info">'.ML_EBAY_VAT_HINT.'</td>
			</tr>
			</tbody>';
}
