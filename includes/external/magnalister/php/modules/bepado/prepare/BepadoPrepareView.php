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

require_once(DIR_MAGNALISTER_MODULES.'bepado/classes/BepadoApiConfigValues.php');
require_once(DIR_MAGNALISTER_MODULES.'bepado/classes/BepadoShippingDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'bepado/classes/BepadoTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES.'bepado/prepare/BepadoCategoryMatching.php');

class BepadoPrepareView extends MagnaCompatibleBase {

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
		$shortDescColumnExists = MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION);
		$keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');

		$dbOldSelectionQuery = '
			SELECT *
			  FROM ' . TABLE_MAGNA_BEPADO_PROPERTIES. ' dp
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
		    SELECT ms.mpID mpID, p.products_id, p.products_model
		      FROM ' . TABLE_PRODUCTS . ' p
		INNER JOIN ' . TABLE_MAGNA_SELECTION . ' ms ON ms.pID = p.products_id
		     WHERE '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ("' . implode('", "', $oldProducts) . '")
		           AND selectionname="prepare"
		           AND session_id="' . session_id() . '"
		';
		$dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);
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
	protected function renderCategoryOptions($sType, $aCategories = array(), $sCategoryArrayKey = '') {
		switch ($sType) {
			case 'MarketplaceCategories':
				$sCMFunc = 'getMPCategoryPath';
				break;
			case 'StoreCategories':
				$sCMFunc = 'getShopCategoryPath';
				break;
			default:
				$sCMFunc = 'getMPCategoryPath';
				break;
		}

		if (isset($aCategories[$sCategoryArrayKey])) {
			$aCategory = array (
				'Id' => $aCategories[$sCategoryArrayKey],
				'Name' => $this->oCategoryMatching->$sCMFunc($aCategories[$sCategoryArrayKey]),
			);
		} else {
			$aCategory = array();
		}

		if ($this->topTen === null) {
			$this->topTen = new BepadoTopTenCategories();
			$this->topTen->setMarketPlaceId($this->mpID);
		}
		$opt = '<option value="">&mdash;</option>'."\n";

		$aTopTenCatIds = $this->topTen->getTopTenCategories($sType, $sCMFunc);

		if (!empty($aCategory) && !array_key_exists($aCategory['Id'], $aTopTenCatIds)) {
			$opt .= '<option value="'.$aCategory['Id'].'" selected="selected">'.$aCategory['Name'].'</option>'."\n";
		}

		foreach ($aTopTenCatIds as $sKey => $sValue) {
			$blSelected = (!empty($aCategory['Id']) && ($aCategory['Id'] == $sKey));
			$opt .= '<option value="'.$sKey.'"'.($blSelected ? ' selected="selected"' : '').'>'.$sValue.'</option>'."\n";
		}

		return $opt;
	}

	protected function renderPrepareView($data) {
		if (($hp = magnaContribVerify($this->marketplace.'PrepareView_renderPrepareView', 1)) !== false) {
			require($hp);
		}
		/**
		 * Check ob einer oder mehrere Artikel
		 */
		$prepareView = (1 == count($data)) ? 'single' : 'multiple';

		$renderedView = $this->oCategoryMatching->renderMatching().'
			<form method="post" action="' . toURL($this->resources['url']) . '">
				<table class="attributesTable">';
		if ('single' == $prepareView) {
			//$renderedView .= $this->renderSinglePrepareView($data[0]);
			//Only Multi for first release
			$renderedView .= $this->renderMultiPrepareView($data);
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

	/**
	 * @param $data
	 * 	enthaelt bereits vorausgefuellte daten aus Config oder User-eingaben
	 */
	protected function renderSinglePrepareView($data) {
		//no single for first release
	}

	/**
	 * @param $data
	 * 	enhealt bereits vorausgefuellte daten aus Config oder User-eingaben
	 */
	protected function renderMultiPrepareView($data) {
		#echo print_m($data, '$data');
		
		// Check which values all prepared products have in common to preselect the values.
		$preSelected = array (
			'MarketplaceCategories' => array(),
			'ShippingServiceOptions' => array(),
			'ShippingTime' => array(),
			'SubmitPurchasePrice' => array(),
		);
		
		$defaults = array (
			'ShippingServiceOptions' => getDBConfigValue($this->marketplace.'.shippingconfig', $this->mpID, array()),
			'MarketplaceCategories' => '[]',
			'ShippingTime' => getDBConfigValue($this->marketplace.'.checkin.leadtimetoship', $this->mpID, '0'),
			'SubmitPurchasePrice' => getDBConfigValue(array($this->marketplace.'.leadtimetoshipmatching.prefer', 'val'), $this->mpID, false),
		);
		
		$loadedPIds = array();
		foreach ($data as $row) {
			$loadedPIds[] = $row['products_id'];
			foreach ($preSelected as $field => $collection) {
				$preSelected[$field][] = isset($row[$field]) ? $row[$field] : null;
			}
		}
		#echo print_m($preSelected, '$preSelected{L:'.__LINE__.'}');
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
		#echo print_m($preSelected, '$preSelected{L:'.__LINE__.'}');
		$decode = array('MarketplaceCategories', 'ShippingServiceOptions');
		foreach ($decode as $decodeField) {
			$preSelected[$decodeField] = is_array($preSelected[$decodeField])
				? $preSelected[$decodeField]
				: json_decode($preSelected[$decodeField], true);
			if (!is_array($preSelected[$decodeField])) {
				$preSelected[$decodeField] = array();
			}
		}
		$preSelected['SubmitPurchasePrice'] = in_array($preSelected['SubmitPurchasePrice'], array(true, '1', 'true'), true)
			? true
			: false;
		
		#Show $preSelected
		#echo print_m($preSelected, '$preSelected{L:'.__LINE__.'}');
		
		// Feldbezeichner | Eingabefeld | Beschreibung
		$oddEven = false;
		$html = '
			<tbody>
				<tr class="headline">
					<td colspan="3"><h4>'.'Bepado Kategorie'.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_GENERIC_CATEGORIES_MARKETPLACE_CATEGORIE.'</th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect"><tbody>
							<tr>
								<td>
									<div class="hoodCatVisual" id="PrimaryCategoryVisual">
										<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											'.$this->renderCategoryOptions('MarketplaceCategories', $preSelected['MarketplaceCategories'], 'primary').'
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
					<td colspan="3"><h4>'.'Versand'.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.ML_GENERIC_CATEGORIES_MARKETPLACE_CATEGORIE.'</th>
					<td class="input">';
		$tmpURL = $this->resources['url'];
		$tmpURL['where'] = 'prepareView';
		
		if (count($preSelected['ShippingServiceOptions']) > 0) {
			$shipProc = new BepadoShippingDetailsProcessor(array(
				'key' => $this->marketplace.'.shippingconfig',
				'content' => $preSelected['ShippingServiceOptions']
			), 'ShippingServiceOptions', $tmpURL);
		} else {
			$shipProc = new BepadoShippingDetailsProcessor(array(
				'key' => $this->marketplace.'.shippingconfig',
			), 'ShippingServiceOptions', $tmpURL);
		}
		
		$html .= $shipProc->process() . '
		
					</td>
					<td class="info"><span style="color:red;"></span></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.'Zeit bis Versand'.'</th>
					<td class="input">';
		$aOpts = array_merge(array (
			'0' => '&mdash;',
		), range(1, 30));
		
		$html .= '
						<select name="ShippingTime">';
			foreach ($aOpts as $sKey => $sVal) {
				$html .= '
							<option value="'.$sKey.'" '.(
					($preSelected['ShippingTime'] == $sKey)
						? 'selected="selected"'
						: ''
					).'>'.$sVal.'</option>';
			}
			$html .= '
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
					<td colspan="3"><h4>'.'Angebotseinstellungen'.'</h4></td>
				</tr>
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
					<th>'.'Preis'.'</th>
					<td class="input">
						<input type="hidden" name="SubmitPurchasePrice" value="false">
						<input type="checkbox" name="SubmitPurchasePrice" value="true" id="id_SubmitPurchasePrice"'.($preSelected['SubmitPurchasePrice'] ? ' checked="checked"' : '').'>
						<label for="id_SubmitPurchasePrice">Einkaufspreis f&uuml;r Handelspartner &uuml;bertragen</label>
					</td>
					<td class="info">'.'Wenn aktiv, wird der Preis f&uuml;r B2B Handelspartner gem&auml;&szlig; Konfiguration &gt; &quot;Einkaufspreis (B2B)&quot; &uuml;bertragen'.'</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody>';
		ob_start();
		?>
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
				$('#selectSecondaryCategory').click(function() {
					mpCategorySelector.startCategorySelector(function(cID, categoryPath) {
						generateCategoryPath($('#SecondaryCategory'), categoryPath);
						$('#SecondaryCategory').trigger('change');
					}, 'mp');
				});
				$('#SecondaryCategory').change(function () {
					getMpCategoryAttributes($(this).val(), 'secondary', $('#secondaryPreselectedValues').val());
				});
				
				$('#selectStoreCategory').click(function() {
					mpCategorySelector.startCategorySelector(function(cID, categoryPath) {
						generateCategoryPath($('#StoreCategory'), cID, categoryPath);
					}, 'store');
				});
			});
			

		/*]]>*/</script>
		<?php
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}
	
	protected function processMagnaExceptions() {
		$ex = BepadoApiConfigValues::gi()->getMagnaExceptions();
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
		BepadoApiConfigValues::gi()->cleanMagnaExceptions();
		$this->price = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
		//$ycm = new BepadoCategoryMatching('view');
		//return $ycm->render().$this->renderPrepareView($this->getSelection());
		$this->oCategoryMatching = new BepadoCategoryMatching();
		
		$html = $this->renderPrepareView($this->getSelection());
		
		return $this->processMagnaExceptions().$html;
	}

	public function renderAjax() {
		if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
			$this->oCategoryMatching = new BepadoCategoryMatching();
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
					$shipProc = new BepadoShippingDetailsProcessor($args, 'ShippingServiceOptions', $tmpURL);
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
