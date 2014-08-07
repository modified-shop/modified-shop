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
 * @todo: Preise einfrieren funzt perfekt fuer Einzel-Artikel. Fuer Multi noch einzubauen.
 */

/**
 * Fetches the options for the top 20 category selectors
 * @param $type	Type of category (PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2)
 * @param $selectedCat	the selected category (empty for newly prepared items)
 * @param $selectedCatName	the category path of the selected category
 * @returns string	option tags for the select element
 */
function eBayRenderCategoryOptions($type = 'PrimaryCategory', $selectedCat, $selectedCatName) {
	global $_MagnaSession;
	# echo print_m(func_get_args(), __FUNCTION__);
	$opt = '<option value="">..</option>'."\n";
	require_once DIR_MAGNALISTER_FS.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'ebay'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'ebayTopTen.php';
	$oTopTen = new ebayTopTen();
	$oTopTen->setMarketPlaceId($_MagnaSession['mpID']);
	$aTopTenCatIds = $oTopTen->getTopTenCategories($type);
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

	#echo print_m($data);

	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
	$prepareViewPrice = new SimplePrice(null, getDBConfigValue('ebay.currency', $_MagnaSession['mpID']));
	$html = '
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4>'.ML_EBAY_PRODUCT_DETAILS.'</h4></td>
			</tr>			
			<tr class="odd">
				<th>'.ML_LABEL_PRODUCT_NAME.'</th>
				<td class="input">
					<input class="fullwidth" type="text" maxlength="80" value="'.fixHTMLUTF8Entities($data[0]['Title'], ENT_COMPAT).'" name="Title" id="Title"/>
				</td>
				<td class="info">'.ML_EBAY_MAX_80_CHARS.'</td>
			</tr>
			<tr class="even">
				<th>'.ML_EBAY_SUBTITLE.'</th>
				<td class="input">
					<input class="fullwidth" type="text" maxlength="55" value="'.$data[0]['Subtitle'].'" name="Subtitle" id="Subtitle" />
					<input type="checkbox" name="enableSubtitle" id="enableSubtitle" />'.ML_EBAY_LABEL_USE_SUBTITLE_YES_NO.'
				</td>
				<td class="info">'.ML_EBAY_SUBTITLE_MAX_55_CHARS.'<span style="color:red;"> '.ML_EBAY_CAUSES_COSTS.'</span></td>
			</tr>
			<tr class="odd">
				<th>'.ML_EBAY_PICTURE.'</th>
				<td class="input">
					<input type="text" class="fullwidth" value="'.$data[0]['PictureURL'].'" name="PictureURL" id="PictureURL"/><br />&nbsp;<br />
					<input type="text" class="fullwidth" value="'.$data[0]['GalleryURL'].'" name="GalleryURL" id="GalleryURL"/>
					<input type="checkbox" name="enableGallery" id="enableGallery" ';
	if (getDBConfigValue(array('ebay.gallery.active', 'val'), $_MagnaSession['mpID'])) {
		$html .= ' checked="checked" ';
	}
	$html .= '/>'.ML_EBAY_LABEL_USE_GALLERY_YES_NO.'
				</td>
				<td class="info">'.ML_EBAY_MAIN_PICTURE_COMPLETE_URL.'<br />
						'.ML_EBAY_MAIN_GALLERY_PICTURE_CAUSES_COSTS.'
				</td>
			</tr>
			<tr class="even">
				<th>'.ML_EBAY_DESCRIPTION.'</th>
				<td class="input">
					'.magna_wysiwyg(array (
						'id' => 'Description',
						'name' => 'Description',
						'class' => 'fullwidth',
						'cols'=>'80',
						'rows'=>'40',
						'wrap'=>'virtual'
					), $data[0]['Description']).'
				</td>
				<td class="info">
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

	$html .= '
						<dt style="font-weight:bold; color:black">#SHORTDESCRIPTION#</dt>
							<dd>'.ML_EBAY_SHORTDESCRIPTION_FROM_SHOP.'</dd>
						<dt style="font-weight:bold; color:black">#DESCRIPTION#</dt>
							<dd>'.ML_EBAY_DESCRIPTION_FROM_SHOP.'</dd>
						<dt style="font-weight:bold; color:black">#PICTURE1#</dt>
							<dd>'.ML_EBAY_FIRST_PIC.'</dd>
						<dt style="font-weight:bold; color:black">#PICTURE2# etc.</dt>
							<dd>'.ML_EBAY_MORE_PICS.'</dd>
					</dl>
				</td>
			</tr>';
			
	$prepareViewPrice->setPrice(makePrice($data[0]['products_id'], 'FixedPriceItem'));
	$fixedPrice    = $prepareViewPrice->formatWOCurrency();
	$prepareViewPrice->setPrice(makePrice($data[0]['products_id'], 'BuyItNowPrice', true));
	$buyItNowPrice = $prepareViewPrice->formatWOCurrency();

	$html .= '
			<tr class="odd">
				<th>eBay Preis</th>
				<td>
					<table class="lightstlye line15"><tbody>
						<tr>
							<td>'.ML_EBAY_PRICE_CALCULATED.': </td>
							<td id="showCalcPrice" name="showCalcPrice">
								'.$fixedPrice.' '.getDBConfigValue('ebay.currency', $_MagnaSession['mpID']).'
								<input type="hidden" value="'.$fixedPrice.'" name="Price" id="Price" />
							</td>
							<td></td>
						</tr>
						<tr id="changePrice">
							<td>
								<span id="bidPriceLabel" style="display:none;">'.ML_EBAY_YOUR_CHINESE_PRICE.':</span>
								<span id="buyNowPriceLabel" style="display:none;">'.ML_EBAY_YOUR_FIXED_PRICE.':</span>
							</td>
							<td>
								<input type="text" id="frozenPrice" name="frozenPrice" value="';
	if (isset($data[0]['priceFrozen']) && $data[0]['priceFrozen']) {
	    $prepareViewPrice->setPrice(makePrice($data[0]['products_id'], 'FixedPriceItem', $data[0]['priceFrozen']));
	    $html .= $prepareViewPrice->formatWOCurrency();
	} else {
		$html .= $fixedPrice;
	}
	$html .= '">
								<span class="iceCrystal" id="freezePrice" title="'.ML_EBAY_FREEZE_PRICE_TOOLTIP.'"></span>
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
									(getDBConfigValue(array('ebay.chinese.buyitnow.price.active', 'val'), $_MagnaSession['mpID']))
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
		'.renderMultiPrepareView($data).'
	';
	return $html;
}

/**
 * @param $data	enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
 */
function renderMultiPrepareView($data) {
	global $_MagnaSession, $_url;
	/* Ggf. Vorausfuellen der Kategorie */
	$prefilledCatsArray = array();
	$ListingTypeArray = array();
	$ListingDurationArray = array();
	$i = 0;
	$lastI = 0;

	$ConditionIDArray = array();
	$PaymentMethodsArray = array();
	$ShippingDetailsArray = array();
	
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
		if (isset($row['ListingType'])) {
			$ListingTypeArray[]     = $row['ListingType'];
			$ListingDurationArray[] = $row['ListingDuration'];
			$ConditionIDArray[] = $row['ConditionID'];
			$PaymentMethodsArray[] = $row['PaymentMethods'];
			$ShippingDetailsArray[] = $row['ShippingDetails'];
		}
		++$i;
	}
	/* nur vorausfuellen wenn fuer alle gleich */
	$PrimaryCategory = null;
	if (1 == count($prefilledCatsArray)) {
		$PrimaryCategory     = trim($prefilledCatsArray[$lastI]['PrimaryCategory']);
		$PrimaryCategoryName = (!empty($PrimaryCategory))
			? geteBayCategoryPath($PrimaryCategory) 
			: '';
		$SecondaryCategory   = trim($prefilledCatsArray[$lastI]['SecondaryCategory']);
		$SecondaryCategoryName = (!empty($SecondaryCategory))
			? geteBayCategoryPath($SecondaryCategory)
			: '';
		$StoreCategory       = trim($prefilledCatsArray[$lastI]['StoreCategory']);
		$StoreCategoryName   = (!empty($StoreCategory))
			? geteBayCategoryPath($StoreCategory, true)
			: '';
		$StoreCategory2      = trim($prefilledCatsArray[$lastI]['StoreCategory2']);
		$StoreCategory2Name  = (!empty($StoreCategory2))
			? geteBayCategoryPath($StoreCategory2, true)
			: '';

		if (!empty($data[$lastI]['ItemSpecifics'])) {
			$PrimaryPreselectedValues = $data[$lastI]['ItemSpecifics'];
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

	/*
	 * Feldbezeichner | Eingabefeld | Beschreibung
	 */
	$oddEven = false;
	$html = '
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
		if ($selectedSite != $site) continue; # Site-Auswahl nur in der Konfig
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
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_EBAY_PAYMENT_METHODS.'</th>
				<td class="input">
					<div id="ebay_PaymentMethods">
					<select name="PaymentMethods[]" id="PaymentMethods" multiple>';
	try {
		$PaymentMethods = geteBayPaymentOptions();
	} catch (MagnaException $e) {
		echo print_m($e->getErrorArray(), 'Error');
	}
	if (isset($prefilledPaymentMethods)) {
		$defaultPaymentMethods = json_decode($prefilledPaymentMethods);
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
						</select>
					</div>
				</td>
				<td class="info">'.ML_EBAY_PAYMENT_METHODS_OFFERED.'</td>
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
		'2000' => ML_EBAY_CONDITION_MANUF_REFURBISHED,
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
			</tr>
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
    ');
    if (1 == (int)MagnaDB::gi()->foundRows()) {
    	$StartTime = $StartTimeSet[0]['StartTime'];
    } else {
    	$StartTime = '';
    }

	$html .= '/>'.ML_EBAY_BESTPRICE_YES_NO.'
				</td>
				<td class="info">'.ML_EBAY_BESTPRICE.'</td>
			</tr>
			<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
				<th>'.ML_HITCOUNTER_SHORT.'</th>
				<td class="input">
					<select name="hitcounter" id="hitcounter">';
    $counterValues = array(
    	'NoHitCounter' => ML_EBAY_NO_HITCOUNTER,
	    'BasicStyle'   => ML_EBAY_BASIC_HITCOUNTER,
	    'RetroStyle'   => ML_EBAY_RETRO_HITCOUNTER,
	    'HiddenStyle'  => ML_EBAY_HIDDEN_HITCOUNTER,
	);
    $hitcounterSet = MagnaDB::gi()->fetchArray('
    	SELECT SQL_CALC_FOUND_ROWS DISTINCT HitCounter
          FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
         WHERE products_id IN ('.$products_id_list.')
    ');
    if (1 == (int)MagnaDB::gi()->foundRows()) {
        $defaultHitCounter = $hitcounterSet[0]['HitCounter'];
    } else {
        $defaultHitCounter =  getDBConfigValue('ebay.hitcounter',$_MagnaSession['mpID']);
    }
    foreach ($counterValues as $counter => $name) {
				$isSelected = ($counter == $defaultHitCounter? 'selected' : '');
				$html .= '
								<option '.$isSelected.' value="'.$counter.'">'.$name."</option>\n";
			}

			$html .= '
							</select>
						</td>
						<td class="info">&nbsp;</td>
					</tr>
					<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
						<th>'.ML_EBAY_START_TIME_SHORT.'</th>
						<td class="input">
							'.renderDateTimePicker('startTime', $StartTime, true).'
						</td>
						<td class="info">'.ML_EBAY_START_TIME.'</td>
					</tr>
					<tr class="spacer">
						<td colspan="3">
							&nbsp;<input type="hidden" value="'.$data[0]['products_id'].'" name="pID" id="pID"/>
						</td>
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
					') == count($data)
				) {
					$html .= ' checked="checked" ';
				}
				$html .= '/>'.ML_EBAY_LABEL_USE_SUBTITLE_YES_NO.'
						</td>
						<td class="info">'.ML_EBAY_SUBTITLE_MAX_55_CHARS.'<span style="color:red;"> '.ML_EBAY_CAUSES_COSTS.'</span></td>
					</tr>
					<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
						<th>'.ML_EBAY_GALLERY_PICTURES.'</th>
						<td class="input">
							<input type="checkbox" name="enableGallery" id="enableGallery" ';
				if (getDBConfigValue(array('ebay.gallery.active', 'val'), $_MagnaSession['mpID'])) {
					$html .= ' checked="checked" ';
				}
				$html .= '/>'.ML_EBAY_LABEL_USE_GALLERY_YES_NO.'
						</td>
						<td class="info">'.ML_EBAY_ENABLE_GALLERY_PICTURES.'</td>
					</tr>';
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
												? 'value=\''.$PrimaryPreselectedValues.'\''
												: ''
										).' />
										<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_EBAY_CHOOSE.'" id="selectPrimaryCategory"/>
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
										<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_EBAY_CHOOSE.'" id="selectSecondaryCategory"/>
										<input class="fullWidth ml-button smallmargin" type="button" value="'.ML_EBAY_DELETE.'" id="deleteSecondaryCategory"/>
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
				if (VariationsEnabled($PrimaryCategory))
					$html .= '<br />'.ML_EBAY_NOTE_VARIATIONS_ENABLED;
				else
					$html .= '<br />'.ML_EBAY_NOTE_VARIATIONS_DISABLED;
			}
			$html .= '
									</div>
								</td></tr>';

			$html .= '
							</tbody></table>
						</td>
						<td class="info">'.ML_EBAY_CATEGORY_DESC.'</td>
					</tr>
					<tr class="spacer">
						<td colspan="3">&nbsp;</td>
					</tr>
				</tbody>
				<tbody id="attr_1" style="display:none">
				</tbody>
				<tbody id="attr_2" style="display:none">
				</tbody>
				<tbody>
					<tr class="headline">
						<td colspan="3"><h4>'.ML_GENERIC_SHIPPING.'</h4></td>
					</tr>
					<tr class="odd">
						<th>'.ML_EBAY_SHIPPING_DOMESTIC.'</th>
						<td class="input">';

				$tmpURL = $_url;
				$tmpURL['where'] = 'prepareView';
				if (isset($prefilledShippingDetails)) {
					$prefilledShippingDetailsArray = json_decode($prefilledShippingDetails, true);
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

				$html .= '
						</td>
						<td class="info">
							'.ML_EBAY_SHIPPING_DOMESTIC_DESC.'<br /><br />
							Angabe "=GEWICHT"<br />
							bei den Versandkosten
							setzt diese gleich dem Artikelgewicht.
						</td>
					</tr>
					<tr class="even">
						<th>'.ML_EBAY_SHIPPING_PROFILE.'</th>
						<td class="input">
							<select name="localProfile" id="localProfile">';
				$shippingProfiles = geteBayShippingDiscountProfiles();
				if (isset($prefilledShippingDetailsArray)
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
				if (isset($prefilledShippingDetailsArray)
				    && array_key_exists('LocalPromotionalDiscount', $prefilledShippingDetailsArray)) {
					if ('true' == $prefilledShippingDetailsArray['LocalPromotionalDiscount']) {
						$html .= ' checked="checked" ';
					}
				} else if (getDBConfigValue(array('ebay.shippingdiscount.local', 'val'), $_MagnaSession['mpID'])) {
					$html .= ' checked="checked" ';
				}
				$html .= '/>'.ML_EBAY_SHIPPING_DISCOUNT.'
				</td>
				<td class="info">
					&nbsp;
				</td>
			</tr>
			<tr class="odd">
				<th>'.ML_EBAY_SHIPPING_INTL_OPTIONAL.'</th>
				<td class="input">';

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

		$html .= '
				</td>
				<td class="info">'.ML_EBAY_SHIPPING_INTL_DESC.'</td>
			</tr>
		<tr class="even">
			<th>'.ML_EBAY_SHIPPING_PROFILE.'</th>
			<td class="input">
				<select name="internationalProfile" id="internationalProfile">';
				if (isset($prefilledShippingDetailsArray)
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
		if (isset($prefilledShippingDetailsArray)
		    && array_key_exists('InternationalPromotionalDiscount', $prefilledShippingDetailsArray)) {
			if ('true' == $prefilledShippingDetailsArray['InternationalPromotionalDiscount']) {
				$html .= ' checked="checked" ';
			}
		} else if (getDBConfigValue(array('ebay.shippingdiscount.international', 'val'), $_MagnaSession['mpID'])) {
			$html .= ' checked="checked" ';
		}
				$html .= '/>'.ML_EBAY_SHIPPING_DISCOUNT.'
			</td>
			<td class="info">
				&nbsp;
			</td>
		</tr>
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
			$('#showCalcPrice').val(data);
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function getEBayCategoryAttributes(cID, aMode, preselectedValues) {
	jQuery.ajax({
		type: 'POST',
		url: '<?php echo toURL($_url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
		data: {
			'action': 'getEBayAttributes',
			'CategoryID': cID,
			'Mode': aMode,
			'preselectedValues': preselectedValues
		},
		success: function(data) {
			$('#attr_'+aMode).html(data+'');
			if (data == '') {
				$('#attr_'+aMode).css({'display':'none'});
			} else {
				$('#attr_'+aMode).css({'display':'table-row-group'});
			}
		},
		error: function() {
		},
		dataType: 'html'
	});
}

function toggleChineseAuction() {
	$('#bidPriceLabel').css({'display': 'inline'});
	$('#buyNowPriceLabel').css({'display': 'none'});
	$('#chinesePrice2').css({'display': 'table-row'});
}

function toggleFixedPriceAuction() {
	$('#bidPriceLabel').css({'display': 'none'});
	$('#buyNowPriceLabel').css({'display': 'inline'});
	$('#chinesePrice2').css({'display': 'none'});
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

$(document).ready(function() {
	$('#PrimaryCategoryVisual > select').change(function() {
		var cID = this.value;
		if (cID != '') {
			generateEbayCategoryPath(cID, $('#PrimaryCategoryVisual'));
			getEBayCategoryAttributes(cID, 1, $('#PrimaryPreselectedValues').val());
			VariationsEnabled(cID,$('#noteVariationsEnabled'));
			return true;
		} else {
			$('#attr_1').css({'display':'none'});
		}
	});
	$('#PrimaryCategoryVisual > select').trigger('change');
	
	$('#SecondaryCategoryVisual > select').change(function() {
		var cID = this.value;
		if (cID != '') {
			$('#SecondaryCategory').val(cID);
			generateEbayCategoryPath(cID, $('#SecondaryCategoryVisual'));
			getEBayCategoryAttributes(cID, 2, $('#SecondaryPreselectedValues').val());
			return true;
		} else {
			$('#attr_2').css({'display':'none'});
		}
	});
	$('#SecondaryCategoryVisual > select').trigger('change');
	
	$('#selectPrimaryCategory').click(function() {
		startCategorySelector(function(cID) {
			$('#PrimaryCategory').val(cID);
			generateEbayCategoryPath(cID, $('#PrimaryCategoryVisual'));
			getEBayCategoryAttributes(cID, 1, $('#PrimaryPreselectedValues').val());
			VariationsEnabled(cID,$('#noteVariationsEnabled'));
		}, 'eBay');
	});
	$('#selectSecondaryCategory').click(function() {
		startCategorySelector(function(cID) {
			$('#SecondaryCategory').val(cID);
			generateEbayCategoryPath(cID, $('#SecondaryCategoryVisual'));
			getEBayCategoryAttributes(cID, 2, $('#SecondaryPreselectedValues').val());
		}, 'eBay');
	});
	$('#deleteSecondaryCategory').click(function() {
		$('#SecondaryCategory').html(null);
		$('#SecondaryCategory').val(null);
		$('#SecondaryCategoryVisual').find('option').attr('selected', '');
		$('#SecondaryCategoryVisual').find('option')[0].selected = 'selected';
		
		$('#attr_2').css({'display': 'none'});
		$('#attr_2').html('');
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
	});
});
/*]]>*/</script><?php
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
	
	$renderedView .= '
		<form method="post" id="prepareForm" action="'.toURL($_url).'">
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
							<td class="firstChild">'.(($prepareView == 'single')
								? '<input class="ml-button" type="submit" name="unprepare" id="unprepare" value="'.ML_BUTTON_LABEL_REVERT.'"/>'
								: ''
							).'</td>
							<td class="lastChild">'.'<input class="ml-button" type="submit" name="savePrepareData" id="savePrepareData" value="'.ML_BUTTON_LABEL_SAVE_DATA.'"/>'.'</td>
						</tr></tbody></table>
					</td></tr>
				</tbody>
			</table>
		</form>';
	return $renderedView;
}
