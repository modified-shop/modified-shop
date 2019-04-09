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

require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoShippingDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoPaymentDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoBuyingModeProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoWarrantyProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/classes/RicardoTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES.'ricardo/prepare/RicardoCategoryMatching.php');

class RicardoPrepareView extends MagnaCompatibleBase {
	const TITLE_MAX_LENGTH = 40;
	const SUBTITLE_MAX_LENGTH = 60;
	
	protected $catMatch = null;
	protected $topTen = null;
	protected $oCategoryMatching = null;

	protected function initCatMatching() {
		$params = array();
		foreach (array('mpID', 'marketplace', 'marketplaceName') as $attr) {
			if (isset($this->$attr)) {
				$params[$attr] = &$this->$attr;
			}
		}
	}

	protected function getSelection() {
		$bShortDescColumnExists = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);
		$keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

		$dbOldSelectionQuery = '
			SELECT *
			  FROM ' . TABLE_MAGNA_RICARDO_PROPERTIES. ' dp
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

		$lang = getDBConfigValue($this->marketplace.'.lang', $this->mpID);

		# Daten fuer properties Tabelle
		# die Namen schon fuer diese Tabelle
		# products_short_description nicht bei OsC, nur bei xtC, Gambio und Klonen
		$dbNewSelectionQuery = '
			SELECT	ms.mpID mpID,
					p.products_id,
					p.products_model,
					p.products_image as PictureUrl,
					pdde.products_name as TitleDe,
					pdfr.products_name as TitleFr,
					'.(($bShortDescColumnExists) ? 'pdde.products_short_description' : "''").' as SubtitleDe,
					'.(($bShortDescColumnExists) ? 'pdfr.products_short_description' : "''").' as SubtitleFr,
					pdde.products_description as DescriptionDe,
					pdfr.products_description as DescriptionFr
			  FROM ' . TABLE_PRODUCTS . ' p
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pdde ON pdde.products_id = p.products_id AND pdde.language_id = "' . $lang['DE'] . '"
		 LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pdfr ON pdfr.products_id = p.products_id AND pdfr.language_id = "' . $lang['FR'] . '"
			 WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("' . implode('", "', $oldProducts) . '")
				   AND ms.mpID = "' . $this->mpID . '"
				   AND selectionname="prepare"
				   AND session_id="' . session_id() . '"
		';
		$dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);
		
		if (!empty($dbNewSelection)) {
			RicardoHelper::getTitleAndDescription('De', $dbNewSelection, $this->mpID);
			RicardoHelper::getTitleAndDescription('Fr', $dbNewSelection, $this->mpID);
		}
		
		$dbSelection = array_merge(
			is_array($dbOldSelection) ? $dbOldSelection : array(),
			is_array($dbNewSelection) ? $dbNewSelection : array()
		);
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
	protected function renderCategoryOptions($sType, $sCategory) {
		if ($this->topTen === null) {
			$this->topTen = new RicardoTopTenCategories();
			$this->topTen->setMarketPlaceId($this->mpID);
		}
		$opt = '<option value="">&mdash;</option>'."\n";

		$aTopTenCatIds = $this->topTen->getTopTenCategories($sType, 'getMPCategoryPath');

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

	protected function renderPrepareView($data) {
		if (($hp = magnaContribVerify($this->marketplace.'PrepareView_renderPrepareView', 1)) !== false) {
			require($hp);
		}

		$preSelected = $this->getPreSelectedData($data);

		/**
		 * Check ob einer oder mehrere Artikel
		 */
		$prepareView = (1 == count($data)) ? 'single' : 'multiple';

		$renderedView = $this->oCategoryMatching->renderMatching().'
			<form method="post" action="' . toURL($this->resources['url']) . '">
				<table class="attributesTable">' . $this->renderListingLanguage($data, $preSelected);
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

	protected function renderListingLanguage($data, $preSelected) {
		$langDeChecked = $preSelected['LangDe'] === 'true' ? 'checked' : '';
		$langFrChecked = $preSelected['LangFr'] === 'true' ? 'checked' : '';
		$oddEven = false;

		ob_start();
		?>
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4><?php echo ML_RICARDO_LANGUAGE ?></h4></td>
			</tr>
			<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
				<th><?php echo ML_RICARDO_LABEL_LANGUAGE_2 ?></th>
				<td class="input langCheckBoxes">
					<input type="checkbox" id="LangDe" name="LangDe" <?php echo $langDeChecked ?>/><label for="LangDe">DE</label>
					<input type="checkbox" id="LangFr" name="LangFr" <?php echo $langFrChecked ?>/><label for="LangFr">FR</label>
				</td>
				<td class="info"></td>
			</tr>
			<tr class="spacer">
				<td colspan="3">&nbsp;</td>
			</tr>
		</tbody>
		<script type="text/javascript">
			$(document).on("click", ".langCheckBoxes input", function(e) {
				if ($(".langCheckBoxes :checked").length === 0 && $(this).prop('checked') === false) {
					e.preventDefault();
				} else {
					if ($('#LangDe').prop('checked')) {
						$('.langde').show();
					} else {
						$('.langde').hide();
					}

					if ($('#LangFr').prop('checked')) {
						$('.langfr').show();
					} else {
						$('.langfr').hide();
					}
				}
			});

			$(document).ready(function() {
				<?php if ($langDeChecked === '') : ?>
				$('.langde').hide();
				<?php endif; ?>
				<?php if ($langFrChecked === '') : ?>
				$('.langfr').hide();
				<?php endif; ?>
			});
		</script>
		<?php
		$renderedView = ob_get_contents();
		ob_end_clean();

		return $renderedView;
	}

	/**
	 * @param $data
	 * 	enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
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
			$data['Images'][$img] = (isset($pictureUrls[$img]) && ($pictureUrls[$img] === 'true')) ? 'true' : 'false';
		}
		
		$data['TitleDe'] = html_entity_decode($data['TitleDe'], ENT_COMPAT, 'UTF-8');
		$data['TitleFr'] = html_entity_decode($data['TitleFr'], ENT_COMPAT, 'UTF-8');
		if (mb_strlen($data['TitleDe'], 'UTF-8') > self::TITLE_MAX_LENGTH) {
			$data['TitleDe'] = mb_substr($data['TitleDe'], 0, self::TITLE_MAX_LENGTH, 'UTF-8');
		}
		
		if (mb_strlen($data['TitleFr'], 'UTF-8') > self::TITLE_MAX_LENGTH) {
			$data['TitleFr'] = mb_substr($data['TitleFr'], 0, self::TITLE_MAX_LENGTH, 'UTF-8');
		}

		ob_start();
		?>
		<tbody>
			<tr class="headline">
				<td colspan="3"><h4><?php echo ML_RICARDO_PRODUCT_DETAILS ?></h4></td>
			</tr>
			<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?> langde">
				<th><?php echo ML_RICARDO_ITEM_NAME_TITLE ?> (<?php echo ML_RICARDO_LANGUAGE_GERMAN ?>)</th>
				<td class="input">
					<input type="text" class="fullwidth" name="TitleDe" id="TitleDe" maxlength="<?php echo self::TITLE_MAX_LENGTH ?>" value="<?php echo fixHTMLUTF8Entities($data['TitleDe'], ENT_COMPAT, 'UTF-8') ?>"/>
				</td>
				<td class="info"><?php echo ML_RICARDO_TITLE_MAXLENGTH ?></td>
			</tr>
			<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?> langde">
				<th><?php echo ML_RICARDO_SUBTITLE ?> (<?php echo ML_RICARDO_LANGUAGE_GERMAN ?>)</th>
				<td class="input">
					<input type="text" class="fullwidth" name="SubtitleDe" id="SubtitleDe" 
						   maxlength="<?php echo self::SUBTITLE_MAX_LENGTH ?>" 
						   value="<?php echo fixHTMLUTF8Entities(RicardoHelper::ricardoSanitizeSubtitle($data['SubtitleDe']), ENT_COMPAT, 'UTF-8') ?>"/>
				</td>
				<td class="info"><?php echo ML_RICARDO_SUBTITLE_MAXLENGTH ?></td>
			</tr>
			<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?> langde">
				<th><?php echo ML_RICARDO_DESCRIPTION ?> (<?php echo ML_RICARDO_LANGUAGE_GERMAN ?>)</th>
				<td class="input">
					<?php echo magna_wysiwyg(array(
						'id' => 'DescriptionDe',
						'name' => 'DescriptionDe',
						'class' => 'fullwidth',
						'cols' => '80',
						'rows' => '20',
						'wrap' => 'virtual'
					), fixHTMLUTF8Entities($data['DescriptionDe'], ENT_COMPAT)) ?>
				</td>
				<td class="info">
					<?php echo ML_EBAY_PRODUCTS_DESCRIPTION ?>
					<br>
					<?php echo ML_EBAY_PLACEHOLDERS ?> :
					<dl>
						<dt style="font-weight:bold; color:black">#TITLE#</dt>
							<dd><?php echo ML_EBAY_ITEM_NAME_TITLE ?></dd>
						<dt style="font-weight:bold; color:black">#VARIATIONDETAILS#</dt>
							<dd><?php echo ML_RICARDO_VARIATIONDETAILS_TEMPLATE ?></dd>
						<dt style="font-weight:bold; color:black">#ARTNR#</dt>
							<dd><?php echo ML_EBAY_ARTNO ?></dd>
						<dt style="font-weight:bold; color:black">#PID#</dt>
							<dd><?php echo ML_EBAY_PRODUCTS_ID ?></dd>';
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
			<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?> langfr">
				<th><?php echo ML_RICARDO_ITEM_NAME_TITLE ?> (<?php echo ML_RICARDO_LANGUAGE_FRENCH ?>)</th>
				<td class="input">
					<input type="text" class="fullwidth" name="TitleFr" id="TitleFr" maxlength="<?php echo self::TITLE_MAX_LENGTH ?>" value="<?php echo fixHTMLUTF8Entities($data['TitleFr'], ENT_COMPAT, 'UTF-8') ?>"/>
				</td>
				<td class="info"><?php echo ML_RICARDO_TITLE_MAXLENGTH ?></td>
			</tr>
			<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?> langfr">
				<th><?php echo ML_RICARDO_SUBTITLE ?> (<?php echo ML_RICARDO_LANGUAGE_FRENCH ?>)</th>
				<td class="input">
					<input type="text" class="fullwidth" name="SubtitleFr" id="SubtitleFr" 
						   maxlength="<?php echo self::SUBTITLE_MAX_LENGTH ?>" 
						   value="<?php echo fixHTMLUTF8Entities(RicardoHelper::ricardoSanitizeSubtitle($data['SubtitleFr']), ENT_COMPAT, 'UTF-8') ?>"/>
				</td>
				<td class="info"><?php echo ML_RICARDO_SUBTITLE_MAXLENGTH ?></td>
			</tr>
			<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?> langfr">
				<th><?php echo ML_RICARDO_DESCRIPTION ?> (<?php echo ML_RICARDO_LANGUAGE_FRENCH ?>)</th>
				<td class="input">
					<?php echo magna_wysiwyg(array(
						'id' => 'DescriptionFr',
						'name' => 'DescriptionFr',
						'class' => 'fullwidth',
						'cols' => '80',
						'rows' => '20',
						'wrap' => 'virtual'
					), fixHTMLUTF8Entities($data['DescriptionFr'], ENT_COMPAT)) ?>
				</td>
				<td class="info">
					<?php echo ML_EBAY_PRODUCTS_DESCRIPTION ?>
					<br>
					<?php echo ML_EBAY_PLACEHOLDERS ?> :
					<dl>
						<dt style="font-weight:bold; color:black">#TITLE#</dt>
							<dd><?php echo ML_EBAY_ITEM_NAME_TITLE ?></dd>
						<dt style="font-weight:bold; color:black">#VARIATIONDETAILS#</dt>
							<dd><?php echo ML_RICARDO_VARIATIONDETAILS_TEMPLATE ?></dd>
						<dt style="font-weight:bold; color:black">#ARTNR#</dt>
							<dd><?php echo ML_EBAY_ARTNO ?></dd>
						<dt style="font-weight:bold; color:black">#PID#</dt>
							<dd><?php echo ML_EBAY_PRODUCTS_ID ?></dd>';
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
			<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
				<th><?php echo ML_LABEL_PRODUCTS_IMAGES ?></th>
				<td class="input">
					<input type="hidden" id="image_hidden" name="Images[]" value="false"/>
				<?php foreach ($data['Images'] as $img => $checked) : ?>
					<table class="imageBox"><tbody>
						<tr><td class="image"><label for="image_<?php echo $img ?>"><?php echo generateProductCategoryThumb($img, 60, 60) ?></label></td></tr>
						<tr><td class="cb"><input type="checkbox" id="image_<?php echo $img ?>" name="Images[<?php echo urlencode($img) ?>]" value="true" <?php echo $checked == 'true' ? 'checked="checked"' : '' ?> /></td></tr>
					</tbody></table>
				<?php endforeach; ?>
				</td>
				<td class="info">
					<?php echo ML_RICARDO_TEXT_APPLY_PRODUCTS_IMAGES ?>
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
			'DescriptionTemplate' => null,
			'ArticleCondition' => null,
			'Warranty' => null,
			'WarrantyDescriptionDe' => null,
			'WarrantyDescriptionFr' => null,
			'WarrantyReference' => null,
			'BuyingMode' => null,
			'PaymentDetails' => null,
			'PaymentdetailsDescriptionDe' => null,
			'PaymentdetailsDescriptionFr' => null,
			'ShippingDetails' => null,
			'PackageSize' => null,
			'ShippingCost' => null,
			'ShippingCumulative' => null,
			'ShippingDescriptionDe' => null,
			'ShippingDescriptionFr' => null,
			'Availability' => null,
			'FirstPromotion' => null,
			'SecondPromotion' => null,
			'LangDe' => null,
			'LangFr' => null,
			'PreferShippingMatching' => null,
			'PictureUrl' => null,
			'MarketplaceCategories' => null,
			'StartDate' => null,
			'EndTime' => null,
			'MaxRelistCount' => null,
			'Duration' => null,
			'BuyNowPrice' => null,
			'EnableBuyNowPrice' => null,
			'StartPrice' => null,
			'Increment' => null,
		);

		$defaults = array (
			'DescriptionTemplate' => getDBConfigValue($this->marketplace.'.checkin.descriptiontemplate', $this->mpID),
			'ArticleCondition' => getDBConfigValue($this->marketplace.'.checkin.paymentdetails', $this->mpID),
			'Warranty' => getDBConfigValue($this->marketplace.'.checkin.warranty', $this->mpID),
			'WarrantyDescriptionDe' => getDBConfigValue($this->marketplace.'.checkin.warranty.description.de', $this->mpID),
			'WarrantyDescriptionFr' => getDBConfigValue($this->marketplace.'.checkin.warranty.description.fr', $this->mpID),
			'WarrantyReference' => getDBConfigValue($this->marketplace.'.checkin.warranty.reference', $this->mpID),
			'BuyingMode' => getDBConfigValue($this->marketplace.'.checkin.buyingmode', $this->mpID),
			'PaymentDetails' => getDBConfigValue($this->marketplace.'.checkin.paymentdetails', $this->mpID),
			'PaymentdetailsDescriptionDe' => getDBConfigValue($this->marketplace.'.checkin.paymentdetails.description.de', $this->mpID, null),
			'PaymentdetailsDescriptionFr' => getDBConfigValue($this->marketplace.'.checkin.paymentdetails.description.fr', $this->mpID, null),
			'ShippingDetails' => getDBConfigValue($this->marketplace.'.checkin.shippingdetails', $this->mpID, 1),
			'PackageSize' => getDBConfigValue($this->marketplace.'.checkin.shippingdetails.packagesize', $this->mpID, null),
			'ShippingCost' => getDBConfigValue($this->marketplace.'.checkin.shippingdetails.shippingcost', $this->mpID, 0),
			'ShippingCumulative' => getDBConfigValue($this->marketplace.'.checkin.shippingdetails.shippingcumulative', $this->mpID),
			'ShippingDescriptionDe' => getDBConfigValue($this->marketplace.'.checkin.shippingdetails.description.de', $this->mpID, null),
			'ShippingDescriptionFr' => getDBConfigValue($this->marketplace.'.checkin.shippingdetails.description.fr', $this->mpID, null),
			'Availability' => getDBConfigValue($this->marketplace.'.checkin.availability', $this->mpID),
			'FirstPromotion' => getDBConfigValue($this->marketplace.'.checkin.firstpromotion', $this->mpID),
			'SecondPromotion' => getDBConfigValue($this->marketplace.'.checkin.secondpromotion', $this->mpID),
			'LangDe' => getDBConfigValue($this->marketplace.'.listinglang.de', $this->mpID),
			'LangFr' => getDBConfigValue($this->marketplace.'.listinglang.fr', $this->mpID),
			'PreferShippingMatching' => getDBConfigValue($this->marketplace.'.leadtimetoshipmatching.prefer', $this->mpID),
			'PictureUrl' => null,
			'MarketplaceCategories' => null,
			'StartDate' => null,
			'EndTime' => null,
			'Duration' => getDBConfigValue($this->marketplace.'.checkin.duration', $this->mpID),
			'BuyNowPrice' => null,
			'EnableBuyNowPrice' => null,
			'StartPrice' => getDBConfigValue($this->marketplace.'.checkin.startprice', $this->mpID),
			'Increment' => getDBConfigValue($this->marketplace.'.checkin.auctionincrement', $this->mpID),
			'MaxRelistCount' => getDBConfigValue($this->marketplace.'.checkin.maxrelistcount', $this->mpID),
		);

		$defaults['LangDe'] = $defaults['LangDe']['val'] === true ? 'true' : 'false';
		$defaults['LangFr'] = $defaults['LangFr']['val'] === true ? 'true' : 'false';

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

		// if StartDate in the past, set it to now
		// (otherwise the DatePicker shows today's date and the old hour)
		if (    !empty($preSelected['StartDate'])
		     && ($preSelected['StartDate'] < date('Y-m-d H:i:s')) ) {
			$preSelected['StartDate'] = date('Y-m-d H:i:s');
		}

		return $preSelected;
	}

	/**
	 * @param $data
	 * 	enhealt bereits vorausgefuellte daten aus Config oder User-eingaben
	 */
	protected function renderMultiPrepareView($data, $preSelected) {
		#echo print_m($data, '$data');

		$result = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetListingStartTimeAndDurationOptions'));
		$listingStartTimeAndDurationOptions = $result['DATA'];

		$maxRelistCountResult = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetMaxRelistCount'));
		$maxRelistCount = $maxRelistCountResult['DATA']['MaxRelistCount'];

		$price = 0;
		$ricardoPrice = 0;

		if (count($data) === 1) {
			$aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->marketplace.'.lang', $this->mpID))->getProductById($data[0]['products_id']);
			$productTax = SimplePrice::getTaxByPID($aProduct['ProductId']);
			$taxFromConfig = getDBConfigValue($this->marketplace . '.checkin.mwst', $this->mpID);
			$priceSignal = getDBConfigValue($this->marketplace . '.price.signal', $this->mpID);

			$this->price->setFinalPriceFromDB($aProduct['ProductId'], $this->mpID);
			if (isset($taxFromConfig) && $taxFromConfig !== '') {
				$this->price
					->removeTax($productTax)
					->addTax($taxFromConfig)
					->makeSignalPrice($priceSignal);
			}
			
			$ricardoPrice = $this->price
					->roundPrice()
					->getPrice();

			$price = isset($preSelected['BuyNowPrice']) ? $preSelected['BuyNowPrice'] : $ricardoPrice;
		}

		if ($preSelected['Duration'] === '10') {
			$endTimeVisibility = 'display: none;';
		} else {
			$endTimeVisibility = '';
		}

		$enablePriceVisibility = '';

		if ($preSelected['BuyingMode'] === 'auction') {
			$priceVisibility = 'style="float: left;"';
			$priceEnabled = '';
		} else {
			$priceVisibility = 'style="float: left; display: none;"';
			if (isset($aProduct) === false) {
				$enablePriceVisibility = 'style="display: none;"';
			}
			$priceEnabled = 'disabled';
		}

		$enableBuyNowPriceValue = $preSelected['EnableBuyNowPrice'] === 'on' ? 'checked' : '';

		$tmpURL = $this->resources['url'];
		$tmpURL['where'] = 'prepareView';

		// Feldbezeichner | Eingabefeld | Beschreibung
		$oddEven = false;
		$html = '			
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>'.ML_RICARDO_LABEL_DESCRIPTIONTEMPLATES.'</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>'.ML_RICARDO_LABEL_TEMPLATES.'</th>
					<td class="input">';
					$descriptionTemplates = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetTemplates'));

					$descriptionTemplatesSelect = '<select id="DescriptionTemplate" name="DescriptionTemplate">'
							. '<option value="-1">' . ML_RICARDO_LABEL_NOTEMPLATES . '</option>' . "\n";

					foreach ($descriptionTemplates['DATA'] as $key => $descriptionTemplate) {
						$descriptionTemplatesSelect .= '<option value="'.$key.'"'.(
							($preSelected['DescriptionTemplate'] == $key)
								? ' selected="selected"'
								: ''
						).'>'.$descriptionTemplate.'</option>'."\n";
					}

					$html .= $descriptionTemplatesSelect . '
						</select>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>' . str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERIC_MP_CATEGORY) . '</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_GENERIC_CATEGORIES_MARKETPLACE_CATEGORIE.'</th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<td>
									<div class="hoodCatVisual" id="PrimaryCategoryVisual">
										<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											'.$this->renderCategoryOptions('MarketplaceCategories', $preSelected['MarketplaceCategories']).'
										</select>
									</div>
								</td>
								<td class="buttons">
									<input class="fullWidth ml-button smallmargin mlbtn-action" type="button" value="'.ML_GENERIC_CATEGORIES_CHOOSE.'" id="selectPrimaryCategory"/>
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
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>'.'Preis & Dauer'.'</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>'.ML_RICARDO_BUYING_MODE.'</th>
					<td class="input">';

		$buyProc = new RicardoBuyingModeProcessor(array(
				'key' => $this->marketplace.'.checkin.buyingmode',
				'content' => $preSelected
			), 'BuyingMode', $tmpURL);

		$html .= $buyProc->process() . '

					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . ' visibilityprice"' . $enablePriceVisibility . '>
					<th>' . ML_RICARDO_PRICE . '</th>
					<td class="input">';

		if (isset($aProduct)) {
			$html .=	   '<div style="float: left;"><label style="margin-left: 5px;" for="config_' . $this->marketplace . '_price_buy_now_price">' . ML_RICARDO_BUY_NOW_PRICE . ': </label><input ' . $priceEnabled . ' type="text" id="config_' . $this->marketplace . '_price_buy_now_price" name="BuyNowPrice" value="' . $price . '" /><span> ' . ML_RICARDO_CURRENCY . '</span></div>';
		} else {
			$html .=	   '<div style="float: left;"><input type="checkbox" id="config_' . $this->marketplace . '_enable_price_buy_now_price" name="EnableBuyNowPrice" ' . $enableBuyNowPriceValue . '/><label for="config_' . $this->marketplace . '_enable_price_buy_now_price">' . ML_RICARDO_ENABLE_BUY_NOW_PRICE_LABEL . '</label></div>';
		}

		$html .=	   '<div ' . $priceVisibility . '><label style="margin-left: 5px;" for="config_' . $this->marketplace . '_price_start_price">' . ML_RICARDO_START_PRICE . ': </label><input type="text" id="config_' . $this->marketplace . '_price_start_price" name="StartPrice" value="' . $preSelected['StartPrice'] . '" /><span> ' . ML_RICARDO_CURRENCY . '</span></div>
						<div ' . $priceVisibility . '><label style="margin-left: 5px;" for="config_' . $this->marketplace . '_price_increment">' . ML_RICARDO_INCREMENT . ': </label><input type="text" id="config_' . $this->marketplace . '_price_increment" name="Increment" value="' . $preSelected['Increment'] . '" /><span> ' . ML_RICARDO_CURRENCY . '</span></div>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>'.ML_RICARDO_START_DATE.'</th>
					<td class="input">
						<input type="text" id="config_' . $this->marketplace . '_start_date_visual" value="" readonly="readonly" class="autoWidth rightSpacer"/>
						<input type="hidden" id="config_' . $this->marketplace . '_start_date" name="conf[' . $this->marketplace . '.start_date]" value="' . $preSelected['StartDate'] . '"/>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>'.ML_RICARDO_DURATION.'</th>
					<td class="input">';
						$durationSelect = '<div style="float: left;">'
								. '<select id="config_' . $this->marketplace . 'checkin_duration" name="conf[' . $this->marketplace . '.checkin.duration]">';

						for ($index = 1; $index <= intval($listingStartTimeAndDurationOptions['Duration']); $index++) {
							$durationSelect .= '<option value="'.$index.'"'.(
								($preSelected['Duration'] == $index)
									? ' selected="selected"'
									: ''
							).'>'.$index . ' ' . ML_RICARDO_DAYS.'</option>'."\n";
						}

						$html .= $durationSelect .
						' </select>
						</div>

						<div style="float: left;' . $endTimeVisibility . '" ><label style="margin-left: 5px;" for="config_' . $this->marketplace . '_end_time">' . ML_RICARDO_END_TIME . ': </label><input type="text" id="config_' . $this->marketplace . '_end_time" name="conf[' . $this->marketplace . '.end_time]" value="' . $preSelected['EndTime'] . '" readonly="readonly" class="autoWidth rightSpacer"/></div>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>' . ML_RICARDO_MAX_RELIST_COUNT . '</th>
					<td class="input">
						<label>' . ML_RICARDO_MAX_RELIST_COUNT_LABEL . '</label>
						<select id="config_ricardo_checkin_maxrelistcount" name="conf[ricardo.checkin.maxrelistcount]" class="">';

		for ($i = 0; $i <= $maxRelistCount; $i++) {
			$selected = '';
			if ($preSelected['MaxRelistCount'] == $i) {
				$selected = 'selected';
			}
			$html .= "<option value='$i' $selected>$i x</option>";
		}

		if ($preSelected['BuyingMode'] === 'buy_it_now') {
			$html .= '<option value="2147483647" ' .
					(($preSelected['MaxRelistCount'] === '2147483647') ? 'selected' : '') .
					'>'.ML_RICARDO_MAX_RELIST_COUNT_UNLIMITED.'</option>';
		}

		$html .=		'</select>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>'.ML_RICARDO_PAYMENT_DETAILS.'</th>
					<td class="input">';

		$payProc = new RicardoPaymentDetailsProcessor(array(
				'key' => $this->marketplace.'.checkin.paymentdetails',
				'content' => $preSelected
			), 'PaymentDetails', $tmpURL);

		$html .= $payProc->process() . '

					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>'.'Versand'.'</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>'.ML_RICARDO_SHIPPING_DETAILS.'</th>
					<td class="input">';

		$shipProc = new RicardoShippingDetailsProcessor(array(
				'key' => $this->marketplace.'.checkin.shippingdetails',
				'content' => $preSelected
			), 'ShippingDetails', $tmpURL);

		$html .= $shipProc->process() . '

					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>';

		if ($preSelected['PreferShippingMatching']['val'] === false) {
			$html .=	'<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
							<th>'.ML_RICARDO_AVAILABILITY.'</th>
							<td class="input">
								<label for="Availability">' . ML_RICARDO_AVAILABILITY_LABEL . ' </label>';

				$availabilities = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetArticleAvailability'));

				$availabilitySelect = '<select id="Availability" name="Availability">';

				foreach ($availabilities['DATA'] as $key => $availability) {
					$availabilitySelect .= '<option value="'.$key.'"'.(
						($preSelected['Availability'] == $key)
							? ' selected="selected"'
							: ''
					).'>'.$availability.'</option>'."\n";
				}

				$html .= $availabilitySelect . '
								</select>
							</td>
							<td class="info"><span style="color:red;"></span></td>
						</tr>';
		}
		$firstPromotions = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetFirstPromotion'));
		$html .=	'<tr class="spacer">
						<td colspan="3">&nbsp;</td>
					</tr>
			</tbody>
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>'.'Promotion'.'</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>
						<div style="float: left;">'.ML_RICARDO_FIRST_PROMOTION.'</div>
						<div style="float: right; width: 16px; height: 16px; background: transparent url(\''.DIR_MAGNALISTER_WS.'images/information.png\') no-repeat 0 0;
							cursor: pointer; display: inline-block; vertical-align: top;" class="desc" id="desc_1" title="Infos">
							<span style="display: none">'. $firstPromotions['DATA']['Text'] .'</span>
						</div>
					</th>
					<td class="input">';

		$firstPromotionSelect = '<select id="FirstPromotion" name="FirstPromotion">';

		foreach ($firstPromotions['DATA']['Combobox'] as $key => $firstPromotion) {
			$firstPromotionSelect .= '<option value="'.$key.'"'.(
				($preSelected['FirstPromotion'] == $key)
					? ' selected="selected"'
					: ''
			).'>'.$firstPromotion.'</option>'."\n";
		}

		$secondPromotions = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetSecondPromotion'));

		$html .= $firstPromotionSelect . '
						</select>
					</td>
					<td class="info"><span style="color:red;">'.ML_RICARDO_PROMOTION_COST.'</span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>
						<div style="float: left;">'.ML_RICARDO_SECOND_PROMOTION.'</div>
						<div style="float: right; width: 16px; height: 16px; background: transparent url(\''.DIR_MAGNALISTER_WS.'images/information.png\') no-repeat 0 0;
							cursor: pointer; display: inline-block; vertical-align: top;" class="desc " id="desc_2" title="Infos">
							<span style="display: none">'. $secondPromotions['DATA']['Text'] .'</span>
						</div>
					</th>
					<td class="input">';

		$secondPromotionSelect = '<select id="SecondPromotion" name="SecondPromotion">';

		foreach ($secondPromotions['DATA']['Combobox'] as $key => $secondPromotion) {
			$secondPromotionSelect .= '<option value="'.$key.'"'.(
				($preSelected['SecondPromotion'] == $key)
					? ' selected="selected"'
					: ''
			).'>'.$secondPromotion.'</option>'."\n";
		}

		$html .= $secondPromotionSelect . '
						</select>
					</td>
					<td class="info"><span style="color:red;">'.ML_RICARDO_PROMOTION_COST.'</span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>'.'Weitere Eigenschaften'.'</h4></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>'.ML_RICARDO_ARTICLE_CONDITION.'</th>
					<td class="input">';

		$articleConditions = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetArticleConditions'));

		$articleConditionSelect = '<select id="ArticleCondition" name="ArticleCondition">';

		foreach ($articleConditions['DATA'] as $key => $articleCondition) {
			$articleConditionSelect .= '<option value="'.$key.'"'.(
				($preSelected['ArticleCondition'] == $key)
					? ' selected="selected"'
					: ''
			).'>'.$articleCondition.'</option>'."\n";
		}

		$html .= $articleConditionSelect . '
						</select>
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<th>'.ML_RICARDO_WARRANTY.'</th>
					<td class="input">';

		$warrantyProc = new RicardoWarrantyProcessor(array(
				'key' => $this->marketplace.'.checkin.warranty',
				'content' => $preSelected
			), 'Warranty', $tmpURL);

		$html .= $warrantyProc->process() . '

					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>
			<div id="infodiag" class="dialog2" title="'.ML_LABEL_INFORMATION.'"></div>';
		ob_start();
		?>
		<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery-ui-timepicker-addon.js"></script>
		<script type="text/javascript">/*<![CDATA[*/
			function getMpCategoryAttributes(cID, aMode, preselectedValues) {
				jQuery.ajax({
					type: 'POST',
					url: '<?php echo toURL($this->resources['url'], array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
					data: {
						'action': 'GetMpCategoryAttributes',
						'cId': cID,
						'mode': aMode,
						'preselectedValues': preselectedValues || {}
					},
					success: function(data) {
						$('#ml-js-attributes'+aMode).html(data+'');
						if (data == '') {
							$('#ml-js-attributes'+aMode).css({'display':'none'});
						} else {
							$('#ml-js-attributes'+aMode).css({'display':'table-row-group'});
						}
					},
					error: function() {
					},
					dataType: 'html'
				});
			}

			function generateCategoryPath(dropDown, categoryPath) {
				dropDown.find('option').attr('selected', '');
				if (dropDown.find('[value='+cID+']').length > 0) {
					dropDown.find('[value='+cID+']').attr('selected','selected');
				} else {
					dropDown.append('<option selected="selected" value="'+cID+'">'+categoryPath+'</option>');
				}
			}

			var ajaxRunning = 0;
			$(document).ajaxStart(function() {
				if (ajaxRunning == 0) {
					jQuery.blockUI(blockUILoading);
				}
				++ajaxRunning;
			}).ajaxStop(function() {
				--ajaxRunning;
				if (ajaxRunning == 0) {
					jQuery.unblockUI();
				}
			});

			$(document).ready(function() {
				$('#selectPrimaryCategory').click(function() {
					mpCategorySelector.startCategorySelector(function(cID, categoryPath) {
						generateCategoryPath($('#PrimaryCategory'), categoryPath);
						$('#PrimaryCategory').trigger('change');
					}, 'mp');
				});

				$('#PrimaryCategory').change(function () {
					getMpCategoryAttributes($(this).val(), 'primary', $('#primaryPreselectedValues').val());
				});

				jQuery.datepicker.setDefaults(jQuery.datepicker.regional['']);
				jQuery.timepicker.setDefaults(jQuery.timepicker.regional['']);
				$("#config_<?php echo $this->marketplace ?>_start_date_visual").datetimepicker(
					jQuery.extend(
						jQuery.datepicker.regional['<?php echo isset($_SESSION['magna']['selected_language']) ? $_SESSION['magna']['selected_language'] : '' ?>'],
						jQuery.timepicker.regional['<?php echo isset($_SESSION['magna']['selected_language']) ? $_SESSION['magna']['selected_language'] : '' ?>']
					)
				).datetimepicker("option", {
					onClose:  function(dateText, inst) {
						var d = $("#config_<?php echo $this->marketplace ?>_start_date_visual").datetimepicker("getDate");
						if (d !== null) {
							var s = jQuery.datepicker.formatDate("yy-mm-dd", d) + ' ' +
								jQuery.datepicker.formatTime("HH:mm:ss", {
									hour: d.getHours(),
									minute: d.getMinutes(),
									second: d.getSeconds()
								}, { ampm: false });
							$("#config_<?php echo $this->marketplace ?>_start_date").val(s);
						}
					}
				}).datetimepicker(
					"option", "minDate", 0
				).datetimepicker(
					"option", "maxDate", <?php echo $listingStartTimeAndDurationOptions['MaxStartDate'] ?>
				)<?php if ($preSelected['StartDate'] !== null) : ?>.datetimepicker(
					"setDate", new Date('<?php echo $preSelected['StartDate'] ?>')
				)<?php endif ?>;

				jQuery.timepicker.setDefaults(jQuery.timepicker.regional['']);
				$("#config_<?php echo $this->marketplace ?>_end_time").timepicker(
					jQuery.timepicker.regional['<?php echo isset($_SESSION['magna']['selected_language']) ? $_SESSION['magna']['selected_language'] : '' ?>']
				).datetimepicker("option", {
					onClose:  function(dateText, inst) {
						var t = $("#config_<?php echo $this->marketplace ?>_end_time").val();
						var tArray = t.split(':');
						if ((t !== null) && (tArray.length === 2)) {
							$("#config_<?php echo $this->marketplace ?>_end_time").val(t + ':00');
						}
					}
				});

				$('#config_<?php echo $this->marketplace ?>_duration').change(function() {
					if (this.value == 10) {
						$('#config_<?php echo $this->marketplace ?>_end_time').parent().hide();
					} else {
						$('#config_<?php echo $this->marketplace ?>_end_time').parent().show();
					}
				});
				
				$('#config_<?php echo $this->marketplace ?>_checkin_buyingmode').change(function() {
					if (this.value === 'auction') {
						$('#config_<?php echo $this->marketplace ?>_price_start_price').parent().show();
						$('#config_<?php echo $this->marketplace ?>_price_increment').parent().show();
						<?php if (isset($aProduct)) : ?>
						$('#config_<?php echo $this->marketplace ?>_price_buy_now_price').prop('disabled', false);
						<?php else : ?>
						$('#config_<?php echo $this->marketplace ?>_enable_price_buy_now_price').closest('.visibilityprice').show();
						<?php endif ?>
					} else {
						$('#config_<?php echo $this->marketplace ?>_price_start_price').parent().hide();
						$('#config_<?php echo $this->marketplace ?>_price_increment').parent().hide();
						<?php if (isset($aProduct)) : ?>
						$('#config_<?php echo $this->marketplace ?>_price_buy_now_price').prop('disabled', true);
						$('#config_<?php echo $this->marketplace ?>_price_buy_now_price').val('<?php echo $ricardoPrice ?>');
						<?php else : ?>
						$('#config_<?php echo $this->marketplace ?>_enable_price_buy_now_price').closest('.visibilityprice').hide();
						<?php endif ?>
					}
				});

				$('#desc_1').click(function () {
					var d = $('#desc_1 span').html();
					$('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
				});

				$('#desc_2').click(function () {
					var d = $('#desc_2 span').html();
					$('#infodiag').html(d).jDialog({'width': (d.length > 1000) ? '700px' : '500px'});
				});
			});
		/*]]>*/</script>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}

	protected function processMagnaExceptions() {
		$ex = RicardoApiConfigValues::gi()->getMagnaExceptions();
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
		RicardoApiConfigValues::gi()->cleanMagnaExceptions();
		$this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
		//$ycm = new RicardoCategoryMatching('view');
		//return $ycm->render().$this->renderPrepareView($this->getSelection());
		$this->oCategoryMatching = new RicardoCategoryMatching();

		$html = $this->renderPrepareView($this->getSelection());

		return $this->processMagnaExceptions().$html;
	}

	public function renderAjax() {
		if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
			$this->oCategoryMatching = new RicardoCategoryMatching();
			echo $this->oCategoryMatching->renderAjax();
		} else if (array_key_exists('action', $_POST)) {
			switch ($_POST['action']) {
				case 'extern': {
					$args = $_POST;
					unset($args['function']);
					unset($args['action']);
					global $_url;
					$tmpURL = $_url;
					$tmpURL['where'] = 'prepareView';
					$shipProc = new RicardoShippingDetailsProcessor($args, 'ShippingDetail', $tmpURL);
					echo $shipProc->process();
					break;
				}
				default: {
					break;
				}
			}
		}
	}
}
