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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/prepare/VariationMatching.php');

class TemuVariationMatching extends VariationMatching {

	protected function getAttributesMatchingHelper() {
		return TemuHelper::gi();
	}

	protected function getTopTenCategoriesHandler() {
		return new TemuTopTenCategories();
	}

	protected function getCategoryMatchingHandler() {
		return new TemuCategoryMatching();
	}

	protected function getVariantMatchingTable() {
		return TABLE_MAGNA_TEMU_VARIANTMATCHING;
	}

	protected function getPrepareTable() {
		return TABLE_MAGNA_TEMU_PREPARE;
	}

	protected function getPrepareLongtextTable() {
		return TABLE_MAGNA_TEMU_PREPARE_LONGTEXT;
	}

	public function process() {
		$this->oCategoryMatching = $this->getCategoryMatchingHandler();
		$categoryId = '';
		$this->saveMatching();
		if (count($this->aErrors) > 0) {
			$categoryId = $_POST['PrimaryCategory'];
		}
		// On a normal (re-)open there is no POST category — load the previously-saved
		// category so the dropdown is pre-filled instead of resetting to blank.
		// ONLY on the Product Preparation screen (TemuPrepareForm sets triggersPrepare()=true).
		// The standalone Attributes Matching tab (base TemuVariationMatching, triggersPrepare()=false)
		// must stay blank — pre-selecting a category there would wrongly drive its matching UI.
		if (empty($categoryId) && $this->triggersPrepare()) {
			$categoryId = $this->loadSavedPrimaryCategory();
		}
		$this->loadAvailableVariationGroups();
		echo $this->renderJs();
		// Render category matching dialog (popup tree for selecting categories).
		// Use output buffering to gracefully handle fatal die() from API errors during category import.
		$categoryDialogHtml = '';
		if ($this->oCategoryMatching) {
			ob_start();
			$obLevel = ob_get_level();
			try {
				$categoryDialogHtml = $this->oCategoryMatching->renderMatching();
			} catch (MagnaException $e) {
				$e->setCriticalStatus(false);
			} catch (Exception $e) {
				// silently skip
			}
			// Clean up any extra output buffers opened during error
			while (ob_get_level() > $obLevel) {
				ob_end_clean();
			}
			$extraOutput = ob_get_clean();
			// If there was error output captured, discard it
			if (strpos($extraOutput, 'errorBox') !== false) {
				$extraOutput = '';
			}
			echo $extraOutput;
		}
		echo $categoryDialogHtml;
		echo $this->renderMatchingTable($categoryId);
	}

	/**
	 * Override renderMatchingTable to use React component instead of legacy jQuery matching.
	 * Renders category selector + React attribute matching component.
	 *
	 * @param string $categoryId Category ID
	 * @return string HTML output
	 */
	/**
	 * Build category dropdown options directly from local DB.
	 * Avoids renderCategoryOptions() which calls getMPCategories/importAllCategories
	 * and can trigger a fatal die() if the API is unreachable.
	 */
	/**
	 * Build full category path (Root > ... > Leaf) by traversing ParentID chain.
	 * @param string $categoryId Leaf category ID
	 * @return string Full path or category ID as fallback
	 */
	protected function buildCategoryPath($categoryId) {
		$parts = array();
		$currentId = $categoryId;
		$maxDepth = 20;
		while ($currentId && $currentId != '0' && $maxDepth-- > 0) {
			$row = MagnaDB::gi()->fetchRow('
				SELECT CategoryName, ParentID
				  FROM ' . TABLE_MAGNA_TEMU_CATEGORIES . '
				 WHERE CategoryID = "' . MagnaDB::gi()->escape($currentId) . '"
			');
			if (!$row) break;
			array_unshift($parts, $row['CategoryName']);
			$currentId = $row['ParentID'];
		}
		return !empty($parts) ? implode(' > ', $parts) : (string)$categoryId;
	}

	protected function buildCategoryDropdownOptions($selectedCategoryId = '') {
		$opt = '<option value="">&mdash;</option>' . "\n";
		$addedCats = array();

		// Get categories from variantmatching table (previously matched categories)
		$matchedCats = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT MpIdentifier
			  FROM ' . TABLE_MAGNA_TEMU_VARIANTMATCHING . '
			 WHERE mpID = "' . MagnaDB::gi()->escape($this->mpId) . '"
			       AND MpIdentifier != "category_independent_attributes"
			       AND MpIdentifier != ""
			       AND MpIdentifier != "0"
			ORDER BY MpIdentifier ASC
			LIMIT 20
		', true);

		// Get top used categories from prepare table. Uses PrimaryCategory (always populated
		// by both the auto-fill and form save paths); the former topMarketplaceCategory column
		// was never written, so this query always returned nothing.
		$topCats = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT PrimaryCategory
			  FROM ' . TABLE_MAGNA_TEMU_PREPARE . '
			 WHERE PrimaryCategory != 0
			       AND PrimaryCategory != ""
			       AND mpID = "' . $this->mpId . '"
			GROUP BY PrimaryCategory
			ORDER BY COUNT(PrimaryCategory) DESC
			LIMIT 20
		', true);

		// Merge both lists (matched categories first, then prepare categories)
		$allCats = array_unique(array_merge((array)$matchedCats, (array)$topCats));

		foreach ($allCats as $catId) {
			if (empty($catId) || isset($addedCats[$catId])) {
				continue;
			}
			$catPath = $this->buildCategoryPath($catId);
			$selected = (!empty($selectedCategoryId) && $selectedCategoryId == $catId) ? ' selected="selected"' : '';
			$opt .= '<option value="' . htmlspecialchars($catId) . '"' . $selected . '>' . htmlspecialchars($catPath) . '</option>' . "\n";
			$addedCats[$catId] = true;
		}

		// If selected category is not in the merged list, add it
		if (!empty($selectedCategoryId) && !isset($addedCats[$selectedCategoryId])) {
			$catPath = $this->buildCategoryPath($selectedCategoryId);
			$opt .= '<option value="' . htmlspecialchars($selectedCategoryId) . '" selected="selected">' . htmlspecialchars($catPath) . '</option>' . "\n";
		}

		return $opt;
	}

	/**
	 * Load the previously-saved marketplace category for the products currently selected
	 * for preparation, so the category dropdown can be pre-filled when the prepare screen
	 * is re-opened. Returns the saved category ONLY when every selected product shares the
	 * same non-empty PrimaryCategory; otherwise returns '' (blank) to avoid mis-assigning
	 * a category to a mixed selection.
	 *
	 * @return string Saved category ID, or '' when none / ambiguous.
	 */
	protected function loadSavedPrimaryCategory() {
		$oDB = MagnaDB::gi();
		$aPIDs = $oDB->fetchArray("
			SELECT pID FROM ".TABLE_MAGNA_SELECTION."
			 WHERE mpID = '".(int)$this->mpId."'
			       AND selectionname = 'prepare'
			       AND session_id = '".$oDB->escape(session_id())."'
		", true);
		if (empty($aPIDs)) {
			return '';
		}
		$aPIDs = array_map('intval', $aPIDs);
		$aCategories = $oDB->fetchArray('
			SELECT DISTINCT PrimaryCategory
			  FROM '.TABLE_MAGNA_TEMU_PREPARE.'
			 WHERE mpID = "'.(int)$this->mpId.'"
			       AND products_id IN ('.implode(',', $aPIDs).')
			       AND PrimaryCategory != ""
		', true);
		// Prefill only when every selected product shares exactly one category.
		return (count($aCategories) === 1) ? (string)$aCategories[0] : '';
	}

	/**
	 * Whether to render the per-product product-data fieldsets (Title/Images/etc).
	 * Matching-only screen: false. Product Preparation subclass overrides to true.
	 * @return bool
	 */
	protected function showProductFieldsets() {
		return false;
	}

	/**
	 * Whether the "Save and Close" button should create+verify prepare rows
	 * (emits temu_apply_prepare and submits the form). Matching-only: false.
	 * @return bool
	 */
	protected function triggersPrepare() {
		return false;
	}

	protected function renderProductFieldsets() {
		$aPIDs = MagnaDB::gi()->fetchArray("
			SELECT pID FROM ".TABLE_MAGNA_SELECTION."
			 WHERE mpID = '".(int)$this->mpId."'
			       AND selectionname = 'prepare'
			       AND session_id = '".MagnaDB::gi()->escape(session_id())."'
		", true);
		if (empty($aPIDs)) {
			return '';
		}
		require_once(DIR_MAGNALISTER_MODULES.'temu/prepare/TemuProductDataView.php');
		$oView = new TemuProductDataView($this->mpId, $aPIDs);
		return $oView->render();
	}

	protected function renderMatchingTable($categoryId = '') {
		// Build category dropdown directly from DB — avoids fatal die() from API errors
		$categoryOptions = $this->buildCategoryDropdownOptions($categoryId);

		// Get main category
		$mainCategory = $categoryId;
		if (empty($mainCategory) && !empty($_POST['PrimaryCategory'])) {
			$mainCategory = $_POST['PrimaryCategory'];
		}

		// Load React-based variation matching renderer for Temu
		require_once(DIR_MAGNALISTER_MODULES . 'temu/application/applicationviews_react_temu.php');

		// Get React component HTML using TEMPLATE version (productID = 0, no product-specific data)
		$reactComponentHtml = '';
		try {
			$reactComponentHtml = renderTemuReactVariationMatchingTemplate($mainCategory, $this->resources['url']);
		} catch (MagnaException $e) {
			$reactComponentHtml = '<tbody><tr><td colspan="3"><p class="errorBox">' . $e->getMessage() . '</p></td></tr></tbody>';
			$e->setCriticalStatus(false);
		}

		$translate = array(
			'mpTitle'                  => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_TITLE),
			'mpAttributeTitle'         => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_ATTRIBUTE),
			'mpOptionalAttributeTitle' => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE),
			'mpCustomAttributeTitle'   => str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_CUSTOM_ATTRIBUTE),
		);

		// Build complete HTML: CI Attributes on top, then Category selector + React component
		ob_start();
		// The Product Preparation screen is reached from — and belongs to — the
		// "Create New Products" (view=apply) tab. Post the Save-and-Close submit back to
		// view=apply so the correct tab stays highlighted (and prepare.php can re-render the
		// prepare form on verification errors). The standalone Attributes Matching tab keeps
		// view=varmatch. React/reset AJAX use $this->resources['url'] directly and are unaffected.
		$aFormViewOverride = $this->triggersPrepare() ? array('view' => 'apply') : array();
		?>
		<form method="post" id="matchingForm" action="<?php echo toURL($this->resources['url'], $aFormViewOverride, true); ?>">

			<?php if ($this->showProductFieldsets()) { echo $this->renderProductFieldsets(); } ?>

			<!-- Category-Independent Attributes (Brand, EAN, Weight, etc.) — shown first -->
			<?php
			$ciHtml = '';
			try {
				$ciHtml = renderTemuCategoryIndependentAttributes($this->resources['url']);
			} catch (MagnaException $e) {
				$e->setCriticalStatus(false);
			} catch (Exception $e) {
				// silently skip CI attributes on error
			}
			if (!empty($ciHtml)) {
			?>
			<table id="ciAttributesMatcher" class="attributesTable">
				<tbody>
				<tr class="headline">
					<td colspan="3"><h4><?php echo str_replace('%marketplace%', ucfirst($this->marketplace), defined('ML_GENERAL_VARMATCH_CI_TITLE') ? ML_GENERAL_VARMATCH_CI_TITLE : '%marketplace% Category-Independent Attributes'); ?></h4></td>
				</tr>
				</tbody>
				<?php echo $ciHtml; ?>
			</table>
			<?php } ?>

			<!-- Category selector -->
			<table id="variationMatcher" class="attributesTable">
				<tbody>
				<tr class="headline">
					<td colspan="3"><h4><?php echo 'Select '.ucfirst($this->marketplace).' Category'; ?></h4></td>
				</tr>
				<tr id="mpVariationSelector">
					<th><?php echo ucfirst($this->marketplace).' Category' ?></th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect">
							<tbody>
							<tr>
								<td>
									<div class="hoodCatVisual" id="PrimaryCategoryVisual">
										<select title="" id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											<?php echo $categoryOptions ?>
										</select>
									</div>
								</td>
								<td class="buttons">
									<input type="button" class="ml-button" id="selectCategoryButton"
										   value="<?php echo 'Choose' ?>" />
								</td>
							</tr>
							</tbody>
						</table>
					</td>
					<td class="info">

					</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;
					</td>
				</tr>
				</tbody>
			</table>

			<!-- Variation Attributes + Category Attributes (split React sections) -->
			<table class="attributesTable">
				<?php echo $reactComponentHtml; ?>
			</table>

			<!-- Action Buttons (Footer) -->
			<table class="actions">
				<thead>
				<tr>
					<th><?php echo ML_LABEL_ACTIONS ?></th>
				</tr>
				</thead>
				<tbody>
				<tr class="firstChild">
					<td>
						<table>
							<tbody>
							<tr>
								<td class="firstChild">
									<button type="button" class="ml-button ml-reset-matching">
										<?php echo ML_GENERAL_VARMATCH_RESET_MATCHING ?>
									</button>
								</td>
								<td></td>
								<td class="lastChild">
									<input type="button" value="<?php echo ML_GENERAL_VARMATCH_SAVE_BUTTON ?>"
										   class="ml-button mlbtn-action">
								</td>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				</tbody>
				<tbody>

				<tr class="spacer">
					<td colspan="3">&nbsp;
					</td>
				</tr>
				</tbody>
			</table>
		</form>

		<?php

		return ob_get_clean();
	}

	/**
	 * Override renderJs to prevent loading legacy variation_matching.js scripts.
	 * React component includes its own JavaScript.
	 *
	 * @return string JavaScript for form submit handling
	 */
	protected function renderJs() {
		// Don't load legacy variation_matching.js scripts
		// React component handles all JavaScript internally

		ob_start();
		?>
		<script type="text/javascript">
			(function ($) {
				$(document).ready(function () {
					// Handle category selector button click
					$('#selectCategoryButton').on('click', function () {
						if (typeof mpCategorySelector !== 'undefined' && typeof mpCategorySelector.startCategorySelector === 'function') {
							mpCategorySelector.startCategorySelector(function (cID, categoryPath) {
								var sel = $('#PrimaryCategory');
								sel.find('option').removeAttr('selected');
								if (sel.find('option[value="' + cID + '"]').length > 0) {
									sel.find('option[value="' + cID + '"]').attr('selected', 'selected');
								} else {
									sel.append('<option selected="selected" value="' + cID + '">' + (categoryPath || cID) + '</option>');
								}
								sel.val(cID).trigger('change');
							}, 'mp');
						} else {
							alert('Category selector not available.');
						}
					});

					// Handle submit button click to trigger batch save
					$('.mlbtn-action[type="button"]').on('click', function () {

						// Check if all-optional variation checkboxes need at least one selected
						if (window.temuAllVariationOptional && window.temuVariationCheckboxContainerId) {
							var checkboxContainer = document.getElementById(window.temuVariationCheckboxContainerId);
							if (checkboxContainer) {
								var checkboxes = checkboxContainer.querySelectorAll('.temu-variation-checkbox');
								var checkedCount = 0;
								for (var ci = 0; ci < checkboxes.length; ci++) {
									if (checkboxes[ci].checked) checkedCount++;
								}
								if (checkboxes.length > 0 && checkedCount === 0) {
									var errorId = 'temu-variation-checkbox-error';
									if (!document.getElementById(errorId)) {
										var errorDiv = document.createElement('div');
										errorDiv.id = errorId;
										errorDiv.style.cssText = 'padding: 8px 15px; color: #e31a1c; font-size: 13px; font-weight: bold;';
										errorDiv.textContent = '<?php echo defined('ML_TEMU_LABEL_VARIATION_SPECDETAILS_REQUIRED') ? addslashes(ML_TEMU_LABEL_VARIATION_SPECDETAILS_REQUIRED) : 'Please select at least one variation attribute.'; ?>';
										checkboxContainer.parentNode.insertBefore(errorDiv, checkboxContainer.nextSibling);
									}
									checkboxContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
									return;
								} else {
									var existingError = document.getElementById('temu-variation-checkbox-error');
									if (existingError) existingError.parentNode.removeChild(existingError);
								}
							}
						}

						// Flush EVERY React instance on the page (variation + category +
						// category-independent) before submit. Each instance exposes an
						// instance-scoped save fn window['magnalisterSaveAmazonVariations_' + containerId];
						// calling one shared global only flushed a single instance and lost the
						// others' pending changes (e.g. Handling Time) when Save was clicked before
						// the debounced auto-save fired.
						var tvmNamespaces = ['temu-variation-root', 'temu-catattr-root', 'temu-ci-attributes-root'];
						var tvmSaveFns = [];
						for (var tvmI = 0; tvmI < tvmNamespaces.length; tvmI++) {
							var tvmFn = window['magnalisterSaveAmazonVariations_' + tvmNamespaces[tvmI]];
							if (typeof tvmFn === 'function') { tvmSaveFns.push(tvmFn); }
						}
						// Backward-compat fallback: no scoped fns → the single shared global.
						if (tvmSaveFns.length === 0 && typeof window.magnalisterSaveAmazonVariations === 'function') {
							tvmSaveFns.push(window.magnalisterSaveAmazonVariations);
						}

						function tvmAfterAllSaved() {
							if (<?php echo $this->triggersPrepare() ? 'true' : 'false'; ?>) {
								// Product Preparation: submit the form so the server runs
								// savePrepare() (create prepare rows + VerifyAddItems).
								var tvmForm = $('#matchingForm');
								if (tvmForm.find('input[name="temu_apply_prepare"]').length === 0) {
									tvmForm.append('<input type="hidden" name="temu_apply_prepare" value="1" />');
								}
								tvmForm.get(0).submit();
							} else {
								// Attributes Matching (matching-only): reload to show saved state.
								window.location.reload();
							}
						}

						if (tvmSaveFns.length === 0) {
							console.error('[TemuVariationMatching] React save function not found');
							tvmAfterAllSaved();
						} else {
							// Await ALL instance saves (each takes a completion callback), then submit.
							// Since BUG-019 the callback receives the flush result ('ok' | 'failed');
							// 'failed' is only reported in strictSave mode when a save did NOT persist.
							var tvmPromises = tvmSaveFns.map(function (fn) {
								return new Promise(function (resolve) {
									try { fn(resolve); } catch (e) { console.error('[TemuVariationMatching] save fn failed', e); resolve('failed'); }
								});
							});
							Promise.all(tvmPromises).then(function (tvmResults) {
								var tvmAnyFailed = false;
								for (var tvmR = 0; tvmR < tvmResults.length; tvmR++) {
									if (tvmResults[tvmR] === 'failed') { tvmAnyFailed = true; break; }
								}
								if (tvmAnyFailed) {
									// Data did NOT reach the server — do not submit/reload, the
									// navigation would discard the unsaved changes. The failing
									// React instance shows the red error toast; the failed changes
									// stay pending and retry on the next Save click / auto-flush.
									console.error('[TemuVariationMatching] Save aborted - at least one section failed to persist');
									return;
								}
								tvmAfterAllSaved();
							});
						}
					});

					// Handle reset button click to clear all matched attributes
					$('.ml-reset-matching').on('click', function () {

						var selectedCategory = $('#PrimaryCategory').val();
						if (!selectedCategory || selectedCategory === 'null' || selectedCategory === '') {
							alert('<?php echo addslashes(defined("ML_GENERAL_VARMATCH_SELECT_CATEGORY") ? ML_GENERAL_VARMATCH_SELECT_CATEGORY : "Please select a category first"); ?>');
							return;
						}

						if (!confirm('<?php echo addslashes(defined("ML_GENERAL_VARMATCH_RESET_MATCHING_CONFIRM") ? ML_GENERAL_VARMATCH_RESET_MATCHING_CONFIRM : "Reset all matched attributes for this category?"); ?>')) {
							return;
						}

						// Send AJAX request to delete record from database
						$.ajax({
							url: '<?php echo toURL($this->resources["url"], array("kind" => "ajax"), true); ?>',
							type: 'POST',
							dataType: 'json',
							data: {
								'ml[action]': 'resetAttributeMatching',
								'ml[variationGroup]': selectedCategory,
								'mpID': <?php echo $this->mpId; ?>
							},
							success: function (response) {
								if (response.success) {
									alert('<?php echo addslashes(defined("ML_GENERAL_VARMATCH_RESET_SUCCESS") ? ML_GENERAL_VARMATCH_RESET_SUCCESS : "Attribute matching reset successfully"); ?>');
									$('#PrimaryCategory').trigger('change');
								} else {
									console.error('[TemuVariationMatching] Reset failed:', response.message);
									alert('<?php echo addslashes(defined("ML_GENERAL_VARMATCH_RESET_ERROR") ? ML_GENERAL_VARMATCH_RESET_ERROR : "Error"); ?>: ' + response.message);
								}
							},
							error: function (xhr, status, error) {
								console.error('[TemuVariationMatching] AJAX error:', error);
								alert('<?php echo addslashes(defined("ML_GENERAL_VARMATCH_RESET_ERROR") ? ML_GENERAL_VARMATCH_RESET_ERROR : "Error"); ?>: ' + error);
							}
						});
					});

					// Prevent accidental (Enter-key) submits; the Save button submits programmatically via form.submit().
					$('#matchingForm').on('submit', function (e) {
						e.preventDefault();
						return false;
					});
				});
			})(jQuery);
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Override saveMatching to handle React component saves (V3 format).
	 *
	 * @param bool $redirect Whether to redirect after save
	 */
	protected function saveMatching($redirect = true) {
		// Check if this is a React component save (V3 format)
		if (isset($_POST['ml']['action']) && ($_POST['ml']['action'] === 'saveAttributeMatching' || $_POST['ml']['action'] === 'saveAttributeMatchingBatch')) {

			// Load React save handler
			require_once(DIR_MAGNALISTER_MODULES . 'temu/application/applicationviews_react_temu.php');

			if ($_POST['ml']['action'] === 'saveAttributeMatchingBatch') {
				handleTemuSaveAttributeMatchingBatch();
			} else {
				handleTemuSaveAttributeMatching();
			}
			return;
		}

		// Fall back to parent implementation for legacy format
		parent::saveMatching($redirect);
	}

	/**
	 * Override renderAjax to handle React component AJAX calls.
	 * Routes React calls to dedicated handlers, legacy calls to parent.
	 */
	public function renderAjax() {
		// Check if this is a React component AJAX call
		// React component uses $_POST['ml']['action'] for save operations and $_POST['type'] for get operations
		if (isset($_POST['ml']['action']) || isset($_POST['type'])) {
			$this->handleReactAjax();
			return;
		}

		// Fall back to parent implementation for legacy AJAX calls
		parent::renderAjax();
	}

	/**
	 * Handle React component AJAX calls.
	 * Routes actions to the appropriate handler functions in applicationviews_react_temu.php.
	 */
	private function handleReactAjax() {
		global $_MagnaSession;

		// Load React AJAX handlers
		require_once(DIR_MAGNALISTER_MODULES . 'temu/application/applicationviews_react_temu.php');

		// Check for ml[action] format (save operations)
		if (isset($_POST['ml']['action'])) {
			$action = $_POST['ml']['action'];

			// Handle resetAttributeMatching (delete all matched attributes for category)
			if ($action === 'resetAttributeMatching') {
				if (!isset($_POST['ml']['variationGroup']) && !isset($_POST['mpID'])) {
					die(json_encode(array('success' => false, 'message' => 'Missing parameters')));
				}

				$variationGroup = isset($_POST['ml']['variationGroup']) ? $_POST['ml']['variationGroup'] : '';
				$mpID = isset($_POST['mpID']) ? (int)$_POST['mpID'] : $this->mpId;

				// Validate variationGroup is not empty
				if (empty($variationGroup) || $variationGroup === 'none') {
					die(json_encode(array(
						'success' => false,
						'message' => 'Please select a valid category'
					)));
				}

				try {
					// Delete record from temu variantmatching table
					MagnaDB::gi()->delete(TABLE_MAGNA_TEMU_VARIANTMATCHING, array(
						'MpId'         => $mpID,
						'MpIdentifier' => $variationGroup
					));

					die(json_encode(array(
						'success' => true,
						'message' => 'Attribute matching reset successfully'
					)));
				} catch (Exception $e) {
					die(json_encode(array(
						'success' => false,
						'message' => 'Failed to reset: ' . $e->getMessage()
					)));
				}
			}

			$variationGroup = isset($_POST['ml']['variationGroup']) ? $_POST['ml']['variationGroup'] : '';

			// Route category-independent attribute saves to dedicated handler
			if ($variationGroup === 'category_independent_attributes'
				&& in_array($action, array('saveAttributeMatching', 'saveAttributeMatchingBatch'))
			) {
				handleTemuSaveCategoryIndependentMatching();
				return;
			}

			// Validate mainCategory/variationGroup is not "none" before any save operation
			if (empty($variationGroup) || $variationGroup === 'none') {
				die(json_encode(array(
					'success' => false,
					'message' => 'Please select a valid category before saving'
				)));
			}

			// Handle saveAttributeMatching (V3 format)
			if ($action === 'saveAttributeMatching') {
				handleTemuSaveAttributeMatching();
				return;
			}

			// Handle saveAttributeMatchingBatch (V3 format)
			if ($action === 'saveAttributeMatchingBatch') {
				handleTemuSaveAttributeMatchingBatch();
				return;
			}
		}

		// Check for type format (get operations)
		if (isset($_POST['type'])) {
			$type = $_POST['type'];

			// Handle getReactComponentData (category change)
			if ($type === 'getReactComponentData') {
				handleTemuGetReactComponentData();
				return;
			}
		}

		// Unknown AJAX action
		die(json_encode(array('success' => false, 'message' => 'Unknown AJAX action')));
	}
}
