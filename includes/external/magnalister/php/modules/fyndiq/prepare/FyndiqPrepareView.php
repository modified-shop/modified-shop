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

require_once(DIR_MAGNALISTER_MODULES.'fyndiq/catmatch/FyndiqCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'fyndiq/FyndiqHelper.php');

class FyndiqPrepareView extends MagnaCompatibleBase {
	const TITLE_MAX_LENGTH = 64;

	protected $catMatch = null;
	protected $prepareSettings = array();
	protected $price = null;

	protected function initCatMatching() {
		$this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
		$params = array();
		foreach (array('mpID', 'marketplace', 'marketplaceName', 'prepareSettings') as $attr) {
			if (isset($this->$attr)) {
				$params[$attr] = &$this->$attr;
			}
		}

		$this->catMatch = new FyndiqCategoryMatching($params);
	}

	protected function showProductDetails($data) {
		if (1 != count($data)) {
			return '';
		}

		$data = $data[0];
		$oddEven = false;
		$pictureUrls = array();
		$aProduct = MLProduct::gi()->setLanguage(getDBConfigValue($this->marketplace . '.lang', $this->mpID))->getProductById($data['products_id']);
		if (isset($data['PictureUrl']) && empty($data['PictureUrl']) === false) {
			$pictureUrls = json_decode($data['PictureUrl'], true);
		}

		if (empty($pictureUrls) || !is_array($pictureUrls)) {
			$pictureUrls = array();
			$i = 0;
			foreach ($aProduct['Images'] as $img) {
				$pictureUrls[$img] = 'true';
			}
		}

		foreach ($aProduct['Images'] as $img) {
			$img = fixHTMLUTF8Entities($img, ENT_COMPAT);
			$data['Images'][$img] = (isset($pictureUrls[$img]) && ($pictureUrls[$img] === 'true')) ? 'true' : 'false';
		}

		$data['Title'] = html_entity_decode($data['Title'], ENT_COMPAT, 'UTF-8');
		if (mb_strlen($data['Title'], 'UTF-8') > self::TITLE_MAX_LENGTH) {
			$data['Title'] = mb_substr($data['Title'], 0, self::TITLE_MAX_LENGTH, 'UTF-8');
		}

		$this->price->setFinalPriceFromDB($data['products_id'], $this->mpID);
		$defaultPrice = $this->price
			->roundPrice()
			->getPrice();

		ob_start();
		?>

		<tbody>
		<tr class="headline">
			<td colspan="3"><h4><?php echo ML_FYNDIQ_PRODUCT_DETAILS ?></h4></td>
		</tr>
		<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
			<th><?php echo ML_FYNDIQ_ITEM_NAME_TITLE ?></th>
			<td class="input">
				<input type="text" class="fullwidth" name="Title" id="Title" maxlength="<?php echo self::TITLE_MAX_LENGTH ?>" value="<?php echo fixHTMLUTF8Entities($data['Title'], ENT_COMPAT, 'UTF-8') ?>"/>
			</td>
			<td class="info"><?php echo ML_FYNDIQ_TITLE_MAXLENGTH ?></td>
		</tr>
		<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
			<th><?php echo ML_FYNDIQ_DESCRIPTION ?></th>
			<td class="input">
				<textarea class="fullwidth" name="Description" id="Description" rows="20" cols="80"><?php echo fixHTMLUTF8Entities(FyndiqHelper::fyndiqSanitizeDesc($data['Description']), ENT_COMPAT, 'UTF-8')?></textarea>
			</td>
			<td class="info"><?php echo ML_FYNDIQ_HTML_TAGS ?></td>
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
			<td class="info"></td>
		</tr>
		<tr class="<?php echo ($oddEven = !$oddEven) ? 'odd' : 'even' ?>">
			<th><?php echo ML_FYNDIQ_PRICE ?></th>
			<td class="input">
				<input type="text" name="Price" id="Price" value="<?php echo $defaultPrice ?>" disabled="true"/>
				<lable><?php echo ML_FYNDIQ_CURRENCY ?></lable>
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

	public function process() {
		$this->initCatMatching();
		$data = $this->getSelection();
		$preSelected = $this->getPreSelectedData($data);

		$html = '
			<p>'.ML_AMAZON_TEXT_APPLY_REQUIERD_FIELDS.'</p>
			<form method="post" action="'.toURL($this->resources['url']).'">
				'.$this->catMatch->renderMatching();

		$prepareView = (1 == count($data)) ? 'single' : 'multiple';
		if ('single' == $prepareView) {
			$defaultMpCategory     = $preSelected['MarketplaceCategory'];
			$defaultMpCategoryName = $this->catMatch->getMPCategory($defaultMpCategory);
			if (is_array($defaultMpCategoryName)) {
				$defaultMpCategoryName = fixHTMLUTF8Entities($defaultMpCategoryName['CategoryName']);
			}

			ob_start();
			?>
			<script type="text/javascript">
				/*<![CDATA[*/
				(function ($) {
					$(document).ready(function () {
						$('#mpCategory').val('<?php echo $defaultMpCategory; ?>').trigger('change');
						$('#mpCategoryName').val('<?php echo $defaultMpCategoryName; ?>');
						$('#mpCategoryVisual').html('<?php echo $this->catMatch->getMPCategoryPath($defaultMpCategory); ?>');
					});
				}(jQuery))
				/*]]>*/
			</script>
			<?php
			$html .= ob_get_contents();
			ob_end_clean();
		}

		# multiple items: no pre-filling except default values

		$html .= '
			<table class="attributesTable">'
			. $this->showProductDetails($data) .'
				<tbody>
					<tr class="headline">
						<td colspan="3"><h4>' . ML_FYNDIQ_SHIPPING . '</h4></td>
					</tr>
					<tr class="even">
						<th>' . ML_FYNDIQ_SHIPPING_COST . '</th>
						<td class="input">
							<input type="text" name="ShippingCost" id="ShippingCost" value="' . $preSelected['ShippingCost'] . '"/>
							<lable>' . ML_FYNDIQ_CURRENCY . '</lable>
						</td>
						<td class="info"></td>
					</tr>
					<tr class="spacer">
						<td colspan="3">&nbsp;</td>
					</tr>
				</tbody>
			</table>
			<table class="actions">
				<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
					<tbody>
						<tr class="firstChild"><td>
							<table><tbody><tr>
								<td class="firstChild">
								</td>
								<td class="lastChild">
									<input class="ml-button mlbtn-action" type="submit" name="savePrepareData" id="savePrepareData" value="' . ML_BUTTON_LABEL_SAVE_DATA . '"/>
								</td>
							</tr></tbody></table>
						</td></tr>
					</tbody>
			</table>';

		$html .= '
			</form>';

		return $html;
	}

	public function renderAjax() {
		if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
			$this->initCatMatching();
			return $this->catMatch->renderAjax();
		}
	}

	protected function getPreSelectedData($data) {
		// Check which values all prepared products have in common to preselect the values.
		$preSelected = array(
			'PictureUrl' => null,
			'MarketplaceCategory' => null,
			'ShippingCost' => null,
		);

		$defaults = array(
			'PictureUrl' => null,
			'MarketplaceCategory' => null,
			'ShippingCost' => getDBConfigValue($this->marketplace. '.shippingcost', $this->mpID),
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
		$sLanguageCode = getDBConfigValue($this->marketplace . '.lang', $this->mpID);
		$keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

		$dbOldSelectionQuery = '
			SELECT *
			FROM ' . TABLE_MAGNA_FYNDIQ_PROPERTIES . ' dp
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

		# Daten fuer properties Tabelle
		# die Namen schon fuer diese Tabelle
		# products_short_description nicht bei OsC, nur bei xtC, Gambio und Klonen
		$dbNewSelectionQuery = '
			SELECT	ms.mpID mpID,
					p.products_id,
					p.products_model,
					p.products_image as PictureUrl,
					pd.products_name as Title,
					pd.products_description as Description
			FROM ' . TABLE_PRODUCTS . ' p
			INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
			LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON pd.products_id = p.products_id AND pd.language_id = "' . $sLanguageCode . '"
			WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("' . implode('", "', $oldProducts) . '")
				AND ms.mpID = "' . $this->mpID . '"
				AND selectionname="prepare"
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

}
